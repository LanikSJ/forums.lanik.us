<?php
/**
 *
 * Maintenance extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2026 stoker - https://phpbb3bbcodes.com/
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

declare(strict_types=1);

namespace stoker\maintenance\controller;

use phpbb\cache\service;
use phpbb\config\config;
use phpbb\config\db_text;
use phpbb\extension\manager;
use phpbb\file_downloader;
use phpbb\language\language;
use phpbb\log\log_interface;
use phpbb\request\request_interface;
use phpbb\template\template;
use phpbb\user;
use phpbb\version_helper;
use stoker\maintenance\ext;

class acp_controller
{
	/** @var int Storage limit of the config_value column */
	protected const CONFIG_VALUE_MAX = 255;

	/** @var int Cap on the stored body text, in bytes. Comfortably inside what
	 * the config_text column holds - MTEXT is mediumtext on MySQL and
	 * roomier still on the other databases - so this ceiling is ours,
	 * not the column's. A maintenance notice has no business running
	 * past it, and it keeps a runaway paste out of the database. */
	protected const CONFIG_TEXT_MAX = 65535;

	/** @var string Format used by the datetime-local inputs. The trailing pipe
	 * resets the fields the format does not carry (seconds), which
	 * DateTime::createFromFormat() would otherwise take from the
	 * current clock. */
	protected const INPUT_DATE_FORMAT = 'Y-m-d\TH:i|';

	/** @var string Format used when writing a stored timestamp back to the form */
	protected const OUTPUT_DATE_FORMAT = 'Y-m-d\TH:i';

	/** @var int Upper bound of the banner lead time, matching the input field */
	protected const BANNER_LEAD_MAX = 720;

	/** @var int Fallback banner lead time, matching the migration default */
	protected const BANNER_LEAD_DEFAULT = 0;

	/** @var service */
	protected $cache;

	/** @var config */
	protected $config;

	/** @var db_text */
	protected $config_text;

	/** @var manager */
	protected $ext_manager;

	/** @var file_downloader */
	protected $file_downloader;

	/** @var language */
	protected $language;

	/** @var log_interface */
	protected $log;

	/** @var request_interface */
	protected $request;

	/** @var template */
	protected $template;

	/** @var user */
	protected $user;

	/** @var string */
	protected $root_path;

	/** @var string */
	protected $php_ext;

	/** @var string */
	protected $u_action;

	/**
	 * @param service			$cache
	 * @param config			$config
	 * @param db_text			$config_text
	 * @param manager			$ext_manager
	 * @param file_downloader	$file_downloader
	 * @param language			$language
	 * @param log_interface		$log
	 * @param request_interface	$request
	 * @param template			$template
	 * @param user				$user
	 * @param string			$root_path
	 * @param string			$php_ext
	 */
	public function __construct(service $cache, config $config, db_text $config_text, manager $ext_manager, file_downloader $file_downloader, language $language, log_interface $log, request_interface $request, template $template, user $user, string $root_path, string $php_ext)
	{
		$this->cache = $cache;
		$this->config = $config;
		$this->config_text = $config_text;
		$this->ext_manager = $ext_manager;
		$this->file_downloader = $file_downloader;
		$this->language = $language;
		$this->log = $log;
		$this->request = $request;
		$this->template = $template;
		$this->user = $user;
		$this->root_path = $root_path;
		$this->php_ext = $php_ext;
	}

	/**
	 * @param string $u_action
	 *
	 * @return void
	 */
	public function set_page_url(string $u_action): void
	{
		$this->u_action = $u_action;
	}

	/**
	 * @return void
	 */
	public function display_settings(): void
	{
		$this->language->add_lang('acp_maintenance', 'stoker/maintenance');

		// Handle AJAX toggle
		if ($this->request->variable('action', '') === 'toggle')
		{
			$this->ajax_toggle_setting();
			return;
		}

		add_form_key('stoker_maintenance_acp');

		if ($this->request->is_set_post('submit'))
		{
			if (!check_form_key('stoker_maintenance_acp'))
			{
				trigger_error($this->language->lang('FORM_INVALID') . adm_back_link($this->u_action), E_USER_WARNING);
			}

			$this->save_settings();
			return;
		}

		$this->assign_form_vars();
	}

	/**
	 * @return void
	 */
	protected function save_settings(): void
	{
		$start_input = $this->request->variable('stoker_maintenance_start', '');
		$end_input = $this->request->variable('stoker_maintenance_end', '');

		// Characters outside the Basic Multilingual Plane (emoji) are stored as
		// numeric character references, so they survive a three-byte utf8
		// database. utf8_decode_ncr() turns them back on the way out.
		$heading = utf8_encode_ucr($this->request->variable('stoker_maintenance_heading', '', true));
		$message = utf8_encode_ucr($this->request->variable('stoker_maintenance_message', '', true));
		$banner_url = utf8_encode_ucr($this->request->variable('stoker_maintenance_banner_url', '', true));
		$banner_lead_hours = $this->request->variable('stoker_maintenance_banner_lead_hours', self::BANNER_LEAD_DEFAULT);
		$banner_lead_hours = max(0, min(self::BANNER_LEAD_MAX, $banner_lead_hours));

		// Values echoed back if one of the checks below fails, so the admin
		// does not lose what was typed.
		$submitted = [
			'STOKER_MAINTENANCE_START'				=> $start_input,
			'STOKER_MAINTENANCE_END'				=> $end_input,
			'STOKER_MAINTENANCE_HEADING'			=> utf8_decode_ncr($heading),
			'STOKER_MAINTENANCE_MESSAGE'			=> utf8_decode_ncr($message),
			'STOKER_MAINTENANCE_BANNER_URL'			=> utf8_decode_ncr($banner_url),
			'STOKER_MAINTENANCE_BANNER_LEAD_HOURS'	=> $banner_lead_hours,
		];

		$start = $this->parse_input_date($start_input);
		$end = $this->parse_input_date($end_input);

		if ($start === false || $end === false)
		{
			$this->show_error('STOKER_MAINTENANCE_ERROR_INVALID_DATE', $submitted);
			return;
		}

		// An end time on its own drives nothing: the schedule never runs and
		// no banner is shown, yet visitors would still be counted down to it.
		if ($end > 0 && $start <= 0)
		{
			$this->show_error('STOKER_MAINTENANCE_ERROR_END_WITHOUT_START', $submitted);
			return;
		}

		if ($end > 0 && $end <= $start)
		{
			$this->show_error('STOKER_MAINTENANCE_ERROR_END_BEFORE_START', $submitted);
			return;
		}

		// Both http and https are accepted; anything else is rejected outright
		// (rules out javascript:/data: URLs), and what is left must still be a
		// well-formed URL - otherwise stray characters such as a quote could
		// break out of the href attribute the link is rendered into.
		if ($banner_url !== '' && (!preg_match('#^https?://#i', $banner_url) || filter_var($banner_url, FILTER_VALIDATE_URL) === false))
		{
			$this->show_error('STOKER_MAINTENANCE_ERROR_INVALID_URL', $submitted);
			return;
		}

		// Emoji expand to roughly 8 characters each once encoded, so the
		// stored length can exceed the column even when the typed text did not.
		// Counted in characters, not bytes, because the column is sized in
		// characters and a byte count would reject valid accented text.
		// Only the heading and the announcement URL live in config_value.
		if (utf8_strlen($heading) > self::CONFIG_VALUE_MAX || utf8_strlen($banner_url) > self::CONFIG_VALUE_MAX)
		{
			$this->show_error('STOKER_MAINTENANCE_ERROR_TOO_LONG', $submitted);
			return;
		}

		// The body text lives in config_text. Its MTEXT column is far larger
		// than anything a maintenance notice needs, so the ceiling applied here
		// is our own rather than the column's - see CONFIG_TEXT_MAX. Counted in
		// bytes to match how such columns are sized, and because the escaping
		// applied above turns a single typed character into several bytes often
		// enough to matter.
		if (strlen($message) > self::CONFIG_TEXT_MAX)
		{
			$this->show_error('STOKER_MAINTENANCE_ERROR_MESSAGE_TOO_LONG', $submitted);
			return;
		}

		$schedule_changed = (int) $this->config['stoker_maintenance_start'] !== $start
			|| (int) $this->config['stoker_maintenance_end'] !== $end;

		$settings = [
			'stoker_maintenance_start'				=> $start,
			'stoker_maintenance_end'				=> $end,
			'stoker_maintenance_heading'			=> $heading,
			'stoker_maintenance_banner_lead_hours'	=> $banner_lead_hours,
			'stoker_maintenance_banner_url'			=> $banner_url,
		];

		// Each set() is an UPDATE, so only the values that actually changed are
		// collected here.
		$changed = [];

		foreach ($settings as $key => $value)
		{
			if ((string) $this->config[$key] !== (string) $value)
			{
				$changed[$key] = $value;
			}
		}

		// A schedule that has been changed is a new one, and the marker only
		// ever says that the outage currently running belongs to the window it
		// was set for. Three ways that plays out:
		//
		// board up - the marker is left over from a window that is
		// already over. Left behind, the listener would
		// read the new window as one that had already
		// been cancelled and switch the board straight
		// back on
		// new window open - the outage that is running is now the new
		// window's, so marker and outage both stand.
		// This is what happens when a running window is
		// extended
		// new window closed - the outage belongs to no window any more and
		// nothing is left to lift it: the listener only
		// acts inside a window, and the admin did not
		// ask for an outage outside one. It is lifted
		// below, so postponing maintenance postpones the
		// outage rather than stretching it across the
		// whole postponement.
		$orphaned = false;

		if ($schedule_changed && (int) $this->config['stoker_maintenance_disabled_by_schedule'] !== 0)
		{
			if ((int) $this->config['board_disable'] === 0)
			{
				$changed['stoker_maintenance_disabled_by_schedule'] = 0;
			}
			else if (!$this->window_is_open($start, $end))
			{
				$orphaned = true;
			}
		}

		// Every set() that is allowed to use the cache throws away the whole
		// config cache, so all but the last write skip it - including the two
		// extra writes below when the schedule has been orphaned - and one
		// purge covers the lot. The keys all exist already, so no write takes
		// the insert path where the flag would also decide is_dynamic.
		$remaining = count($changed) + ($orphaned ? 2 : 0);

		foreach ($changed as $key => $value)
		{
			$this->config->set($key, $value, --$remaining === 0);
		}

		// Done after the new window is stored, so the listener reads that one
		// rather than the window being replaced, and board_disable before the
		// marker: a request that dies between the two leaves the board up with
		// a spent marker, which the listener clears on the next page view. The
		// marker goes either way - a board somebody re-enabled in the meantime
		// leaves it just as stale.
		if ($orphaned)
		{
			$this->config->set_atomic('board_disable', '1', '0', --$remaining === 0);
			$this->config->set('stoker_maintenance_disabled_by_schedule', 0, --$remaining === 0);
		}

		// Write only when the body text actually changed. Compared against what
		// is stored rather than the cached copy: an entry left stale by a purge
		// behind our back would otherwise skip the write and leave the old text
		// in the database while the cache showed the new one.
		if ((string) $this->config_text->get('stoker_maintenance_message') !== $message)
		{
			$this->config_text->set('stoker_maintenance_message', $message);
		}

		// Refresh the cache from what is now stored. Unconditional, so an
		// entry purged behind our back is put back rather than left missing.
		$this->cache->put(ext::MESSAGE_CACHE_KEY, ['value' => $message]);

		$this->log->add('admin', (int) $this->user->data['user_id'], $this->user->ip, 'LOG_ACP_STOKER_MAINTENANCE_SETTINGS');

		trigger_error($this->language->lang('CONFIG_UPDATED') . adm_back_link($this->u_action));
	}

	/**
	 * Convert a datetime-local field into a timestamp in the admin's timezone.
	 *
	 * @param string $input
	 *
	 * @return int|false Timestamp, 0 when the field was left empty, false when unparsable.
	 */
	protected function parse_input_date(string $input)
	{
		if ($input === '')
		{
			return 0;
		}

		// A browser only sends seconds when the field asks for them, but a
		// hand-built request may carry them - drop them rather than reject the
		// value for the wrong reason.
		if (preg_match('/^(\d{4}-\d{2}-\d{2}T\d{2}:\d{2}):\d{2}$/', $input, $match))
		{
			$input = $match[1];
		}

		$timestamp = $this->user->get_timestamp_from_format(self::INPUT_DATE_FORMAT, $input);

		if ($timestamp === false)
		{
			return false;
		}

		// createFromFormat() rolls impossible dates over - 31 February becomes
		// 3 March - so the parse is only trusted when formatting the result
		// gives back exactly what was submitted. This also rejects local times
		// that do not exist because a DST change skipped them.
		return $this->format_input_date((int) $timestamp) === $input ? (int) $timestamp : false;
	}

	/**
	 * Convert a stored timestamp into a datetime-local field value.
	 *
	 * @param int $timestamp
	 *
	 * @return string
	 */
	protected function format_input_date(int $timestamp): string
	{
		if ($timestamp <= 0)
		{
			return '';
		}

		// DateTime ignores the timezone argument for an "@" timestamp, so the
		// zone has to be applied afterwards or the form would show UTC.
		$date = new \DateTime('@' . $timestamp);
		$date->setTimezone($this->user->create_timezone());

		return $date->format(self::OUTPUT_DATE_FORMAT);
	}

	/**
	 * Redisplay the form with an error message.
	 *
	 * @param string	$lang_key
	 * @param array		$overrides
	 *
	 * @return void
	 */
	protected function show_error(string $lang_key, array $overrides = []): void
	{
		$this->template->assign_vars([
			'S_ERROR'	=> true,
			'ERROR_MSG'	=> $this->language->lang($lang_key),
		]);

		$this->assign_form_vars($overrides);
	}

	/**
	 * @param array $overrides
	 *
	 * @return void
	 */
	protected function assign_form_vars(array $overrides = []): void
	{
		$this->assign_version_check_vars();

		$vars = [
			'STOKER_MAINTENANCE_START'				=> $this->format_input_date((int) $this->config['stoker_maintenance_start']),
			'STOKER_MAINTENANCE_END'				=> $this->format_input_date((int) $this->config['stoker_maintenance_end']),
			'STOKER_MAINTENANCE_HEADING'			=> utf8_decode_ncr((string) $this->config['stoker_maintenance_heading']),
			'STOKER_MAINTENANCE_MESSAGE'			=> utf8_decode_ncr($this->get_message()),
			'STOKER_MAINTENANCE_BANNER_URL'			=> utf8_decode_ncr((string) $this->config['stoker_maintenance_banner_url']),
			'STOKER_MAINTENANCE_BANNER_LEAD_HOURS'	=> (int) $this->config['stoker_maintenance_banner_lead_hours'],
			'STOKER_MAINTENANCE_ENABLED'			=> (bool) $this->config['stoker_maintenance_enabled'],
			'STOKER_MAINTENANCE_BANNER_ENABLED'		=> (bool) $this->config['stoker_maintenance_banner_enabled'],
			'STOKER_MAINTENANCE_BOARD_DISABLE'		=> (bool) $this->config['board_disable'],
			'U_ACTION'								=> $this->u_action,
			'U_STOKER_MAINTENANCE_PREVIEW'			=> append_sid($this->root_path . 'index.' . $this->php_ext, 'stoker_maintenance_preview=1'),
			'U_STOKER_MAINTENANCE_PREVIEW_BANNER'	=> append_sid($this->root_path . 'index.' . $this->php_ext, 'stoker_maintenance_preview_banner=1'),
			'U_TOGGLE_ENABLED'						=> $this->u_action . '&amp;action=toggle&amp;setting=stoker_maintenance_enabled&amp;hash=' . generate_link_hash('togglestoker_maintenance_enabled'),
			'U_TOGGLE_BANNER_ENABLED'				=> $this->u_action . '&amp;action=toggle&amp;setting=stoker_maintenance_banner_enabled&amp;hash=' . generate_link_hash('togglestoker_maintenance_banner_enabled'),
			'U_TOGGLE_BOARD_DISABLE'				=> $this->u_action . '&amp;action=toggle&amp;setting=board_disable&amp;hash=' . generate_link_hash('toggleboard_disable'),
		];

		$this->template->assign_vars(array_merge($vars, $overrides));
	}

	/**
	 * @return void
	 */
	private function ajax_toggle_setting(): void
	{
		$setting = $this->request->variable('setting', '');

		$valid_settings = [
			'stoker_maintenance_enabled',
			'stoker_maintenance_banner_enabled',
			'board_disable',
		];

		if (!in_array($setting, $valid_settings, true))
		{
			trigger_error($this->language->lang('FORM_INVALID'), E_USER_WARNING);
		}

		if (!check_link_hash($this->request->variable('hash', ''), 'toggle' . $setting))
		{
			trigger_error($this->language->lang('FORM_INVALID'), E_USER_WARNING);
		}

		$old_value = (string) $this->config[$setting];
		$new_value = (int) $old_value ? 0 : 1;

		// Conditional on the value the page was drawn with. board_disable is
		// not ours alone - the schedule writes it too, and core's Board
		// Settings page writes it as well - so a blind inverse of a value read
		// a moment ago can quietly undo somebody else's change. A request that
		// loses here writes nothing and asks the page to redraw itself, which
		// puts the switch back in the state the board is really in.
		if (!$this->config->set_atomic($setting, $old_value, (string) $new_value))
		{
			$this->send_toggle_response(true);
			return;
		}

		// Set when a second setting was changed as a consequence of this one,
		// so the page is reloaded rather than left showing a stale toggle.
		$refresh = false;

		// Switching the extension off hands the board back to core, which knows
		// nothing about our schedule. An outage the schedule started would be
		// stranded behind core's message with nothing left running to lift it,
		// so it is lifted here. A board taken offline by hand is none of our
		// business and stays offline, exactly as it would have if this
		// extension had never been installed. Read before clear_schedule(),
		// which drops the marker that says whose outage it is. Any marker other
		// than zero counts: a disable that got as far as taking the board
		// offline is ours whether or not it got as far as saying so.
		if ($setting === 'stoker_maintenance_enabled' && $new_value === 0)
		{
			$ours = (int) $this->config['stoker_maintenance_disabled_by_schedule'] !== 0;

			// Lifted before the schedule is dropped, and conditionally, so a
			// board somebody else took offline in the meantime is left alone.
			// A request that dies between the two writes leaves the board up
			// with a schedule that no longer runs, which is harmless; the
			// other order would leave it offline with nothing left saying the
			// outage was ours.
			if ($ours && $this->config->set_atomic('board_disable', '1', '0'))
			{
				$refresh = true;
			}

			$refresh = $this->clear_schedule() || $refresh;
		}

		// Switching the board back on by hand ends the maintenance. Without
		// this the listener would find the window still open and disable the
		// board again on the next page view. A window that has not opened yet
		// is left alone: it drives the banner and the countdown, nothing has
		// run it yet, and throwing it away here would lose a schedule the
		// admin had not finished with.
		if ($setting === 'board_disable' && $new_value === 0)
		{
			if (!$this->schedule_is_pending())
			{
				$refresh = $this->clear_schedule() || $refresh;
			}
			else if ((int) $this->config['stoker_maintenance_disabled_by_schedule'] !== 0)
			{
				// The window stands - but the marker has to go, or it would
				// later be read as an outage that had already been cancelled.
				$this->config->set('stoker_maintenance_disabled_by_schedule', 0);
			}
		}

		$this->log->add('admin', (int) $this->user->data['user_id'], $this->user->ip, 'LOG_ACP_STOKER_MAINTENANCE_SETTINGS');

		$this->send_toggle_response($refresh);
	}

	/**
	 * Answer an AJAX toggle and end the request.
	 *
	 * @param bool $refresh Whether the page has to redraw itself, because a
	 * second setting changed as a consequence of this one
	 * or because nothing changed at all.
	 *
	 * @return void
	 */
	private function send_toggle_response(bool $refresh): void
	{
		if ($this->request->is_ajax())
		{
			$json_response = new \phpbb\json_response();
			$json_response->send([
				'success' => true,
				'refresh' => $refresh,
			]);
		}

		redirect($this->u_action);
	}

	/**
	 * Whether the stored maintenance window has yet to open.
	 *
	 * @return bool
	 */
	protected function schedule_is_pending(): bool
	{
		$start = (int) $this->config['stoker_maintenance_start'];

		return $start > 0 && time() < $start;
	}

	/**
	 * Whether a maintenance window covers this moment.
	 *
	 * @param int $start
	 * @param int $end
	 *
	 * @return bool
	 */
	protected function window_is_open(int $start, int $end): bool
	{
		if ($start <= 0)
		{
			return false;
		}

		$now = time();

		// An end time at or before the start means no end time was given, and
		// such a window never closes on its own.
		return $now >= $start && ($end <= $start || $now < $end);
	}

	/**
	 * Read the body text, preferring the cache over a config_text query.
	 *
	 * @return string
	 */
	protected function get_message(): string
	{
		$cached = $this->cache->get(ext::MESSAGE_CACHE_KEY);

		if (is_array($cached) && array_key_exists('value', $cached))
		{
			return (string) $cached['value'];
		}

		$message = (string) $this->config_text->get('stoker_maintenance_message');

		// Wrapped in an array because the cache reports a miss as false, which
		// an empty body text would otherwise be indistinguishable from.
		$this->cache->put(ext::MESSAGE_CACHE_KEY, ['value' => $message]);

		return $message;
	}

	/**
	 * Drop the stored maintenance window.
	 *
	 * @return bool Whether a start or end time was cleared. The marker is not
	 * counted: it is not shown on the form, so clearing it alone
	 * gives the page nothing to redraw.
	 */
	protected function clear_schedule(): bool
	{
		$keys = [
			'stoker_maintenance_start'					=> true,
			'stoker_maintenance_end'					=> true,
			'stoker_maintenance_disabled_by_schedule'	=> false,
		];

		$clear = [];
		$visible_change = false;

		foreach ($keys as $key => $on_form)
		{
			if ((int) $this->config[$key] !== 0)
			{
				$clear[] = $key;

				$visible_change = $visible_change || $on_form;
			}
		}

		// Every set() that is allowed to use the cache throws away the whole
		// config cache, so all but the last write skip it.
		$remaining = count($clear);

		foreach ($clear as $key)
		{
			$this->config->set($key, 0, --$remaining === 0);
		}

		return $visible_change;
	}

	/**
	 * Assign version check template variables
	 *
	 * @return void
	 */
	private function assign_version_check_vars(): void
	{
		$md_manager = $this->ext_manager->create_extension_metadata_manager('stoker/maintenance');

		try
		{
			$meta = $md_manager->get_metadata('all');
		}
		catch (\Exception $e)
		{
			$this->template->assign_vars([
				'VERSIONCHECK_INSTALLED'		=> '',
				'S_VERSIONCHECK_UNAVAILABLE'	=> true,
			]);
			return;
		}

		$version_data = $meta['extra']['version-check'] ?? null;
		$current_version = $meta['version'] ?? '';

		$this->template->assign_vars([
			'VERSIONCHECK_INSTALLED' => $current_version,
		]);

		if (!is_array($version_data) || empty($version_data['host']) || empty($version_data['directory']) || empty($version_data['filename']) || $current_version === '')
		{
			$this->template->assign_vars([
				'S_VERSIONCHECK_UNAVAILABLE' => true,
			]);
			return;
		}

		$version_helper = new version_helper($this->cache, $this->config, $this->file_downloader);
		$version_helper->set_current_version($current_version);
		$version_helper->set_file_location(
			(string) $version_data['host'],
			(string) $version_data['directory'],
			(string) $version_data['filename'],
			!empty($version_data['ssl'])
		);

		try
		{
			$update = $version_helper->get_ext_update_on_branch();
		}
		catch (\Exception $e)
		{
			$this->template->assign_vars([
				'S_VERSIONCHECK_UNAVAILABLE' => true,
			]);
			return;
		}

		if (empty($update))
		{
			$this->template->assign_vars([
				'S_VERSIONCHECK_UP_TO_DATE'	=> true,
				'VERSIONCHECK_CURRENT'		=> $current_version,
			]);
			return;
		}

		$this->template->assign_vars([
			'S_VERSIONCHECK_OUTDATED'	=> true,
			'VERSIONCHECK_CURRENT'		=> $current_version,
			'VERSIONCHECK_LATEST'		=> (string) ($update['current'] ?? ''),
			'U_VERSIONCHECK_DOWNLOAD'	=> (string) ($update['download'] ?? ''),
		]);
	}
}
