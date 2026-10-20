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
use phpbb\language\language;
use phpbb\request\request_interface;
use phpbb\template\template;
use phpbb\user;
use stoker\maintenance\ext;

class maintenance_controller
{
	/** @var int Length in seconds of the window invented for a preview that
	 * has no real one to show */
	protected const PREVIEW_DURATION = 300;

	/** @var int How long after a window opens the closing message is still
	 * shown, in seconds. Wide enough to cover the reload the disable
	 * timer fires plus its transit time, and narrow enough that
	 * nobody arriving later is told the board is closing when it
	 * closed some time ago */
	protected const CLOSING_GRACE = 15;

	/** @var service */
	protected $cache;

	/** @var config */
	protected $config;

	/** @var db_text */
	protected $config_text;

	/** @var language */
	protected $language;

	/** @var request_interface */
	protected $request;

	/** @var template */
	protected $template;

	/** @var user */
	protected $user;

	/** @var bool */
	protected $preview = false;

	/**
	 * @param service			$cache
	 * @param config			$config
	 * @param db_text			$config_text
	 * @param language			$language
	 * @param request_interface	$request
	 * @param template			$template
	 * @param user				$user
	 */
	public function __construct(service $cache, config $config, db_text $config_text, language $language, request_interface $request, template $template, user $user)
	{
		$this->cache = $cache;
		$this->config = $config;
		$this->config_text = $config_text;
		$this->language = $language;
		$this->request = $request;
		$this->template = $template;
		$this->user = $user;
	}

	/**
	 * Render the next display() as a preview rather than a real outage.
	 *
	 * @param bool $preview
	 *
	 * @return void
	 */
	public function set_preview(bool $preview): void
	{
		$this->preview = $preview;
	}

	/**
	 * Render the maintenance page and terminate the request.
	 *
	 * @return void
	 */
	public function display(): void
	{
		$this->language->add_lang('common', 'stoker/maintenance');

		$start = (int) $this->config['stoker_maintenance_start'];
		$end = (int) $this->config['stoker_maintenance_end'];
		$now = time();

		// A preview with nothing scheduled - or a window that has already run -
		// would show the page stripped of everything the countdown drives, which
		// is most of what there is to look at. A short window is invented for
		// the occasion. A real window still ahead is left alone, so the preview
		// shows what visitors would actually get.
		if ($this->preview && $end <= $now)
		{
			$start = $now;
			$end = $now + self::PREVIEW_DURATION;
		}

		$heading = utf8_decode_ncr((string) $this->config['stoker_maintenance_heading']);
		$message = utf8_decode_ncr($this->get_message());

		$heading = $heading !== '' ? $heading : $this->language->lang('STOKER_MAINTENANCE_DEFAULT_HEADING');
		$message = $message !== '' ? $message : $this->language->lang('STOKER_MAINTENANCE_DEFAULT_MESSAGE');

		// Core answers an AJAX request with JSON rather than a page. Handing
		// back markup instead would leave the calling script with a response it
		// cannot read, so the same shape msg_handler() uses is sent here.
		if ($this->request->is_ajax())
		{
			$json_response = new \phpbb\json_response();
			$json_response->send([
				'MESSAGE_TITLE'		=> $heading,
				'MESSAGE_TEXT'		=> $message,
				'S_USER_WARNING'	=> false,
				'S_USER_NOTICE'		=> false,
				'REFRESH_DATA'		=> null,
			]);
		}

		// Left until here on purpose. A non-200 status makes phpBB's ajax.js
		// discard the JSON above and show its own generic error instead, and a
		// preview is not an outage at all - the board is up and answering.
		if (!$this->preview)
		{
			send_status_line(503, 'Service Unavailable');

			// Tells crawlers and monitoring when the board is expected back
			// rather than leaving them to guess. Only meaningful with an end
			// time still ahead of us; an open-ended outage says nothing. Sent
			// here and not earlier so it never rides along on the 200 the AJAX
			// branch above returns, where it would mean nothing at all.
			if ($end > $now)
			{
				header('Retry-After: ' . ($end - $now));
			}
		}

		// The countdown needs an end still ahead of us and nothing more, so a
		// board taken offline before the window opens can still say when it
		// expects to be back. With no end time there is nothing to count.
		$countdown = ($end > $now);

		// The progress bar additionally needs the window to be open, or it
		// would measure a negative elapsed time against the start.
		$progress = ($countdown && $start > 0 && $end > $start && $now >= $start);

		// A reload would land back on this same URL - the preview parameter and
		// all - and start the whole thing over. The countdown simply stops at
		// zero instead.
		$reload = !$this->preview;

		// The counterpart to the re-opening message: shown for a moment by
		// anyone landing here as the window opens, the visitors the disable
		// timer reloads among them. Time-boxed, because unlike the re-opening
		// message - which can only ever fire as the countdown runs out - this
		// one is decided on page load, and someone arriving an hour into the
		// outage must not be told the board is closing.
		//
		// Never in a preview. The window invented above starts at this very
		// moment, which would put every preview in the closing state and hide
		// behind it the countdown and progress bar the preview exists to show.
		$closing = !$this->preview && $countdown && $start > 0 && $now >= $start && ($now - $start) <= self::CLOSING_GRACE;

		$this->template->assign_vars([
			'STOKER_MAINTENANCE_HEADING'		=> $heading,
			'STOKER_MAINTENANCE_MESSAGE'		=> $message,
			'STOKER_MAINTENANCE_START'			=> $start,
			'STOKER_MAINTENANCE_END'			=> $end,
			'STOKER_MAINTENANCE_NOW'			=> $now,
			'STOKER_MAINTENANCE_START_DATE'		=> $countdown && $start > 0 ? $this->user->format_date($start, '', true) : '',
			'STOKER_MAINTENANCE_END_DATE'		=> $countdown ? $this->user->format_date($end, '', true) : '',
			'S_STOKER_MAINTENANCE_CLOSING'		=> $closing,
			'S_STOKER_MAINTENANCE_PROGRESS'		=> $progress,
			'S_STOKER_MAINTENANCE_RELOAD'		=> $reload,
			'S_STOKER_MAINTENANCE_ASSETS'		=> true,
			'S_STOKER_MAINTENANCE_COUNTDOWN'	=> $countdown,
		]);

		page_header($this->language->lang('STOKER_MAINTENANCE_PAGE_TITLE'), false);

		$this->template->set_filenames([
			'body' => '@stoker_maintenance/maintenance_page.html',
		]);

		// Cron has no business running on a board that is offline, and the
		// task image would have every visitor firing a request at it.
		page_footer(false);
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
}
