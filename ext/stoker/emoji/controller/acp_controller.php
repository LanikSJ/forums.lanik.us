<?php
/**
 *
 * Emoji V2 extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2026 stoker - https://phpbb3bbcodes.com/
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

declare(strict_types=1);

namespace stoker\emoji\controller;

use phpbb\cache\service;
use phpbb\config\config;
use phpbb\db\driver\driver_interface;
use phpbb\extension\manager;
use phpbb\file_downloader;
use phpbb\language\language;
use phpbb\log\log_interface;
use phpbb\request\request_interface;
use phpbb\template\template;
use phpbb\textformatter\cache_interface;
use phpbb\user;
use phpbb\version_helper;

/**
 * Emoji V2 ACP controller
 */
class acp_controller
{
	/** @var string Whitespace stripped from submitted values: \s plus NUL, as trim() strips in PHP 8.6 */
	private const TRIM_CHARS = " \t\n\r\0\x0B\f";

	/** @var int Seconds before a failed version check is retried */
	protected const VERSIONCHECK_RETRY_TTL = 3600;

	/** @var service */
	protected $cache;

	/** @var config */
	protected $config;

	/** @var driver_interface */
	protected $db;

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

	/** @var cache_interface */
	protected $text_formatter_cache;

	/** @var user */
	protected $user;

	/** @var string */
	protected $emoji_table;

	/** @var string */
	protected $u_action;

	/**
	 * @param service			$cache
	 * @param config			$config
	 * @param driver_interface	$db
	 * @param manager			$ext_manager
	 * @param file_downloader	$file_downloader
	 * @param language			$language
	 * @param log_interface		$log
	 * @param request_interface	$request
	 * @param template			$template
	 * @param cache_interface	$text_formatter_cache
	 * @param user				$user
	 * @param string			$emoji_table
	 */
	public function __construct(service $cache, config $config, driver_interface $db, manager $ext_manager, file_downloader $file_downloader, language $language, log_interface $log, request_interface $request, template $template, cache_interface $text_formatter_cache, user $user, string $emoji_table)
	{
		$this->cache = $cache;
		$this->config = $config;
		$this->db = $db;
		$this->ext_manager = $ext_manager;
		$this->file_downloader = $file_downloader;
		$this->language = $language;
		$this->log = $log;
		$this->request = $request;
		$this->template = $template;
		$this->text_formatter_cache = $text_formatter_cache;
		$this->user = $user;
		$this->emoji_table = $emoji_table;
	}

	/**
	 * @param string $u_action
	 * @return void
	 */
	public function set_page_url(string $u_action): void
	{
		$this->u_action = $u_action;
	}

	/**
	 * Display the emoji settings page (mode, sizes, quick reply, version check)
	 *
	 * @return void
	 */
	public function display_settings(): void
	{
		$this->language->add_lang('acp_emoji', 'stoker/emoji');

		if ($this->request->variable('action', '') === 'toggle')
		{
			$this->ajax_toggle_setting();
			return;
		}

		$this->assign_version_check_vars();

		add_form_key('stoker_emoji_settings');

		if ($this->request->is_set_post('submit'))
		{
			if (!check_form_key('stoker_emoji_settings'))
			{
				trigger_error($this->language->lang('FORM_INVALID'), E_USER_WARNING);
				return;
			}

			$mode = (int) $this->request->variable('stoker_emoji_mode', 0);
			$mode = in_array($mode, [0, 1, 2, 3], true) ? $mode : 0;
			$old_mode = (int) $this->config['stoker_emoji_mode'];

			$this->config->set('stoker_emoji_mode', $mode);
			$this->config->set('allow_smilies', $mode === 0 ? 1 : 0);
			$this->config->set('stoker_emoji_noto_full', $this->request->variable('stoker_emoji_noto_full', 0) ? 1 : 0);

			$this->config->set('stoker_emoji_size', $this->clamp_size($this->request->variable('stoker_emoji_size', '1.3')));
			$this->config->set('stoker_emoji_noto_size', $this->clamp_size($this->request->variable('stoker_emoji_noto_size', '1.2')));

			// Whether the s9e Emoji plugin is removed is decided at configure
			// time and baked into the cached parser and renderer. Only mode 0
			// keeps the plugin, so switching between the emoji modes leaves
			// the configurator identical and needs no regeneration.
			if (($old_mode === 0) !== ($mode === 0))
			{
				$this->text_formatter_cache->invalidate();
			}

			$this->log->add('admin', (int) $this->user->data['user_id'], $this->user->ip, 'LOG_ACP_STOKER_EMOJI');

			trigger_error($this->language->lang('ACP_STOKER_EMOJI_SETTINGS_SAVED') . adm_back_link($this->u_action));
		}

		$this->template->assign_vars([
			'U_ACTION'					=> $this->u_action,
			'STOKER_EMOJI_MODE'			=> (int) $this->config['stoker_emoji_mode'],
			'S_STOKER_EMOJI_NOTO_FULL'	=> (bool) $this->config['stoker_emoji_noto_full'],
			'STOKER_EMOJI_SIZE'			=> $this->clamp_size($this->config['stoker_emoji_size']),
			'STOKER_EMOJI_NOTO_SIZE'	=> $this->clamp_size($this->config['stoker_emoji_noto_size']),
			'S_QUICKREPLY'				=> (bool) $this->config['stoker_quickreply_emoji'],
			'U_TOGGLE_QUICKREPLY'		=> $this->u_action . '&amp;action=toggle&amp;setting=stoker_quickreply_emoji&amp;hash=' . generate_link_hash('togglestoker_quickreply_emoji'),
		]);
	}

	/**
	 * Display the emoji list management page (the editable table)
	 *
	 * @return void
	 */
	public function display_emojis(): void
	{
		$this->language->add_lang('acp_emoji', 'stoker/emoji');

		$this->assign_version_check_vars();

		add_form_key('stoker_emoji_emojis');

		// PHP stops reading POST data at max_input_vars and keeps what came
		// before, so a cut request always loses the trailing fields. The marker
		// is the last field in the form, so it is missing wherever the cut falls,
		// including between the submit button and the form token.
		if ($this->request->is_set_post('emoji_char') && !$this->request->is_set_post('stoker_emoji_complete'))
		{
			trigger_error($this->language->lang('ACP_STOKER_EMOJI_TRUNCATED', (int) ini_get('max_input_vars')) . adm_back_link($this->u_action), E_USER_WARNING);
			return;
		}

		if ($this->request->is_set_post('submit'))
		{
			if (!check_form_key('stoker_emoji_emojis'))
			{
				trigger_error($this->language->lang('FORM_INVALID'), E_USER_WARNING);
				return;
			}

			$skipped_emoji = 0;
			$skipped_codes = 0;

			$this->save_emojis($this->parse_emoji_table($skipped_emoji, $skipped_codes));

			$this->log->add('admin', (int) $this->user->data['user_id'], $this->user->ip, 'LOG_ACP_STOKER_EMOJI');

			// Entries are dropped when they duplicate an existing one or do not
			// fit the column, so the admin is told what did not make it in.
			$message = $this->language->lang('ACP_STOKER_EMOJI_SAVED');

			if ($skipped_emoji > 0)
			{
				$message .= '<br>' . $this->language->lang('ACP_STOKER_EMOJI_SKIPPED_EMOJI', $skipped_emoji);
			}

			if ($skipped_codes > 0)
			{
				$message .= '<br>' . $this->language->lang('ACP_STOKER_EMOJI_SKIPPED_CODES', $skipped_codes);
			}

			trigger_error($message . adm_back_link($this->u_action));
		}

		$index = 0;

		$sql = 'SELECT emoji_id, emoji_char, emoji_codes, emoji_emotion, display_on_posting
			FROM ' . $this->emoji_table . '
			ORDER BY emoji_order ASC, emoji_id ASC';
		$result = $this->db->sql_query($sql);
		while ($row = $this->db->sql_fetchrow($result))
		{
			$this->template->assign_block_vars('emoji_rows', [
				'INDEX'		=> $index++,
				'EMOJI'		=> \utf8_decode_ncr((string) $row['emoji_char']),
				'CODES'		=> \utf8_decode_ncr((string) $row['emoji_codes']),
				'EMOTION'	=> \utf8_decode_ncr((string) $row['emoji_emotion']),
				'S_POSTING'	=> (bool) $row['display_on_posting'],
			]);
		}
		$this->db->sql_freeresult($result);

		$this->template->assign_vars([
			'U_ACTION'	=> $this->u_action,
		]);
	}

	/**
	 * Parse the submitted emoji table into an ordered list of entries.
	 *
	 * @param int $skipped_emoji	Set to the number of discarded emoji rows
	 * @param int $skipped_codes	Set to the number of discarded shortcodes
	 * @return array
	 */
	private function parse_emoji_table(int &$skipped_emoji, int &$skipped_codes): array
	{
		$chars = $this->request->variable('emoji_char', [''], true);
		$codes = $this->request->variable('emoji_codes', [''], true);
		$posting = $this->request->variable('emoji_posting', [0 => 0]);
		$emotions = $this->request->variable('emoji_emotion', [''], true);

		$emojis = [];
		$seen = [];
		$seen_codes = [];

		$skipped_emoji = 0;
		$skipped_codes = 0;

		foreach ($chars as $k => $emoji)
		{
			$emoji = trim($this->decode_request((string) $emoji), self::TRIM_CHARS);

			// A blank row is the admin adding a row and leaving it alone, so it
			// is dropped without being reported.
			if ($emoji === '')
			{
				continue;
			}

			if (isset($seen[$emoji]))
			{
				$skipped_emoji++;
				continue;
			}

			// Values are stored NCR-encoded, which expands every 4-byte
			// character to an ASCII reference, so it is the encoded form that
			// has to fit the VCHAR_UNI columns. Those hold 255 characters, not
			// 255 bytes, so the length is measured with utf8_strlen().
			$char = $this->encode_stored($emoji);

			if ($char === '' || \utf8_strlen($char) > 255)
			{
				$skipped_emoji++;
				continue;
			}

			$raw_codes = isset($codes[$k]) ? $this->decode_request((string) $codes[$k]) : '';
			$parts = preg_split('/\s+/', $raw_codes);
			$shortcodes = [];
			$codes_length = 0;

			foreach ($parts as $i => $code)
			{
				if ($code === '')
				{
					continue;
				}

				// A shortcode may only ever map to one emoji, board-wide.
				if (isset($seen_codes[$code]))
				{
					$skipped_codes++;
					continue;
				}

				// Encoding is per character, so encoding each shortcode and
				// joining with a space gives the same string as encoding the
				// joined list, without rebuilding it on every iteration.
				$encoded = $this->encode_stored($code);
				$length = \utf8_strlen($encoded) + (empty($shortcodes) ? 0 : 1);

				if ($codes_length + $length > 255)
				{
					// The budget is spent, so this shortcode and every one
					// after it is discarded.
					foreach (array_slice($parts, $i) as $rest)
					{
						if ($rest !== '')
						{
							$skipped_codes++;
						}
					}

					break;
				}

				$shortcodes[] = $encoded;
				$codes_length += $length;
				$seen_codes[$code] = true;
			}

			$emotion = isset($emotions[$k]) ? trim($this->decode_request((string) $emotions[$k]), self::TRIM_CHARS) : '';

			$seen[$emoji] = true;
			$emojis[] = [
				'emoji_char'			=> $char,
				'emoji_codes'			=> implode(' ', $shortcodes),
				'emoji_emotion'			=> $this->encode_truncated($emotion, 255),
				'display_on_posting'	=> !empty($posting[$k]) ? 1 : 0,
			];
		}

		return $emojis;
	}

	/**
	 * Reverse the htmlspecialchars() that type_cast_helper::set_var() applies to
	 * request values. Only the four entities it produces are undone, so text the
	 * admin typed literally, such as &copy; or &#128512;, is kept as typed.
	 *
	 * @param string $value
	 * @return string
	 */
	private function decode_request(string $value): string
	{
		return strtr($value, [
			'&lt;'		=> '<',
			'&gt;'		=> '>',
			'&quot;'	=> '"',
			'&amp;'		=> '&',
		]);
	}

	/**
	 * Escape an ampersand that starts something utf8_decode_ncr() would read as
	 * a numeric character reference. Stored values are decoded again on the way
	 * out, so without this a literally typed &#38; would come back as a bare &.
	 *
	 * @param string $value
	 * @return string
	 */
	private function escape_ncr(string $value): string
	{
		return preg_replace('/&(?=#(?:[0-9]{1,6}|x[0-9A-F]{1,5});)/i', '&#38;', $value);
	}

	/**
	 * Prepare a value for storage: protect literal references, then NCR-encode
	 * the 4-byte characters.
	 *
	 * @param string $value
	 * @return string
	 */
	private function encode_stored(string $value): string
	{
		return \utf8_encode_ucr($this->escape_ncr($value));
	}

	/**
	 * Encode a string for storage and cut it to a maximum encoded character
	 * length, one piece at a time so a reference is never split in half.
	 *
	 * @param string $value
	 * @param int $max_length
	 * @return string
	 */
	private function encode_truncated(string $value, int $max_length): string
	{
		$encoded = $this->encode_stored($value);

		if (\utf8_strlen($encoded) <= $max_length)
		{
			return $encoded;
		}

		$result = '';
		$length = 0;

		// An escaped reference counts as one piece, so truncation lands either
		// side of it and never inside it.
		preg_match_all('/&#(?:[0-9]{1,6}|x[0-9A-F]{1,5});|./ius', $this->escape_ncr($value), $matches);

		foreach ($matches[0] as $character)
		{
			$piece = \utf8_encode_ucr($character);
			$piece_length = \utf8_strlen($piece);

			if ($length + $piece_length > $max_length)
			{
				break;
			}

			$result .= $piece;
			$length += $piece_length;
		}

		return $result;
	}

	/**
	 * Replace the stored emoji set with the supplied ordered list and refresh the cache.
	 *
	 * @param array $emojis
	 * @return void
	 */
	private function save_emojis(array $emojis): void
	{
		$this->db->sql_transaction('begin');

		$this->db->sql_query('DELETE FROM ' . $this->emoji_table);

		if (!empty($emojis))
		{
			$rows = [];
			$order = 0;

			foreach ($emojis as $entry)
			{
				$entry['emoji_order'] = ++$order;
				$rows[] = $entry;
			}

			$this->db->sql_multi_insert($this->emoji_table, $rows);
		}

		$this->db->sql_transaction('commit');

		$this->cache->destroy('_stoker_emoji_list');
	}

	/**
	 * Clamp a submitted emoji size to a safe float (0.5–5.0), snapped to one
	 * decimal and formatted locale-independently for storage.
	 *
	 * @param mixed $value
	 * @return string
	 */
	private function clamp_size($value): string
	{
		$value = round((float) $value, 1);

		if ($value < 0.5)
		{
			$value = 0.5;
		}
		else if ($value > 5.0)
		{
			$value = 5.0;
		}

		return number_format($value, 1, '.', '');
	}

	/**
	 * Handle AJAX boolean toggle for simple on/off settings.
	 *
	 * @return void
	 */
	private function ajax_toggle_setting(): void
	{
		$setting = $this->request->variable('setting', '');

		$valid_settings = [
			'stoker_quickreply_emoji',
		];

		if (!in_array($setting, $valid_settings, true))
		{
			trigger_error($this->language->lang('FORM_INVALID'), E_USER_WARNING);
			return;
		}

		if (!check_link_hash($this->request->variable('hash', ''), 'toggle' . $setting))
		{
			trigger_error($this->language->lang('FORM_INVALID'), E_USER_WARNING);
			return;
		}

		$new_value = ((int) $this->config[$setting]) ? 0 : 1;
		$this->config->set($setting, $new_value);

		$this->log->add('admin', (int) $this->user->data['user_id'], $this->user->ip, 'LOG_ACP_STOKER_EMOJI');

		if ($this->request->is_ajax())
		{
			$json_response = new \phpbb\json_response();
			$json_response->send(['success' => true]);
		}

		redirect($this->u_action);
	}

	/**
	 * Assign version check template variables
	 *
	 * @return void
	 */
	private function assign_version_check_vars(): void
	{
		$md_manager = $this->ext_manager->create_extension_metadata_manager('stoker/emoji');

		$current_version = '';
		$version_data = null;

		try
		{
			$meta = $md_manager->get_metadata('all');
			$current_version = (string) ($meta['version'] ?? '');
			$version_data = $meta['extra']['version-check'] ?? null;
		}
		catch (\Exception $e)
		{
			// Fall through, the unavailable branch below handles it
		}

		// Always assigned, the ACP badge reads this on every path
		$this->template->assign_vars([
			'VERSIONCHECK_INSTALLED'	=> $current_version,
		]);

		if (!is_array($version_data) || empty($version_data['host']) || empty($version_data['directory']) || empty($version_data['filename']) || $current_version === '')
		{
			$this->template->assign_vars([
				'S_VERSIONCHECK_UNAVAILABLE'	=> true,
			]);
			return;
		}

		$failed_key = '_stoker_emoji_versioncheck_failed';

		// version_helper only caches successful fetches, a failed one is not retried on every page view
		if ($this->cache->get($failed_key))
		{
			$this->template->assign_vars([
				'S_VERSIONCHECK_UNAVAILABLE'	=> true,
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
			$this->cache->put($failed_key, true, self::VERSIONCHECK_RETRY_TTL);

			$this->template->assign_vars([
				'S_VERSIONCHECK_UNAVAILABLE'	=> true,
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
