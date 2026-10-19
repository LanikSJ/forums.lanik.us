<?php
/**
 *
 * URL Rewrite extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2026 stoker - https://phpbb3bbcodes.com/
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

declare(strict_types=1);

namespace stoker\urlrewrite\controller;

use phpbb\cache\service;
use phpbb\config\config;
use phpbb\extension\manager;
use phpbb\file_downloader;
use phpbb\language\language;
use phpbb\log\log;
use phpbb\request\request;
use phpbb\template\template;
use phpbb\user;
use phpbb\version_helper;
use stoker\urlrewrite\helper\url_helper;

class acp_controller
{
	/** @var config */
	protected $config;

	/** @var language */
	protected $language;

	/** @var request */
	protected $request;

	/** @var template */
	protected $template;

	/** @var log */
	protected $log;

	/** @var user */
	protected $user;

	/** @var service */
	protected $cache;

	/** @var manager */
	protected $ext_manager;

	/** @var file_downloader */
	protected $file_downloader;

	/** @var string */
	protected $php_ext;

	/** @var string */
	protected $u_action = '';

	/** @var string */
	protected $page_title = '';

	/** @var string[] Config keys that may be toggled via AJAX */
	protected const TOGGLES = [
		'stoker_urlrewrite_enable',
		'stoker_urlrewrite_redirect_enable',
	];

	/**
	 * @param config			$config
	 * @param language			$language
	 * @param request			$request
	 * @param template			$template
	 * @param log				$log
	 * @param user				$user
	 * @param service			$cache
	 * @param manager			$ext_manager
	 * @param file_downloader	$file_downloader
	 * @param string			$php_ext
	 */
	public function __construct(config $config, language $language, request $request, template $template, log $log, user $user, service $cache, manager $ext_manager, file_downloader $file_downloader, string $php_ext)
	{
		$this->config = $config;
		$this->language = $language;
		$this->request = $request;
		$this->template = $template;
		$this->log = $log;
		$this->user = $user;
		$this->cache = $cache;
		$this->ext_manager = $ext_manager;
		$this->file_downloader = $file_downloader;
		$this->php_ext = $php_ext;

		$this->language->add_lang('acp_urlrewrite', 'stoker/urlrewrite');
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
	 * @return string
	 */
	public function get_page_title(): string
	{
		return $this->page_title;
	}

	/**
	 * General settings page (AJAX toggles + transliteration select).
	 *
	 * @return void
	 */
	public function handle_settings(): void
	{
		$this->page_title = $this->language->lang('ACP_URLREWRITE_SETTINGS');

		if ($this->request->variable('action', '') === 'toggle')
		{
			$this->ajax_toggle();
			return;
		}

		$this->assign_version_check_vars();

		$form_key = 'stoker_urlrewrite_settings';
		add_form_key($form_key);

		$intl_available = extension_loaded('intl');

		if ($this->request->is_set_post('submit'))
		{
			if (!check_form_key($form_key))
			{
				trigger_error($this->language->lang('FORM_INVALID') . adm_back_link($this->u_action), E_USER_WARNING);
			}

			$method = $this->request->variable('stoker_urlrewrite_translit', url_helper::METHOD_STATIC);

			if (!in_array($method, [url_helper::METHOD_OFF, url_helper::METHOD_STATIC, url_helper::METHOD_INTL], true) || ($method === url_helper::METHOD_INTL && !$intl_available))
			{
				$method = url_helper::METHOD_STATIC;
			}

			$this->config->set('stoker_urlrewrite_translit', $method);

			$this->log->add('admin', (int) $this->user->data['user_id'], $this->user->ip, 'ACP_URLREWRITE_LOG_SETTINGS');

			trigger_error($this->language->lang('CONFIG_UPDATED') . adm_back_link($this->u_action));
		}

		// Show the method that is actually in force. url_helper::get_method() falls
		// back to Static when intl is missing, and the intl option is rendered
		// disabled - without this the select would have nothing selected, display
		// "Off", and write 0 to the config on the next plain submit.
		$translit = (int) ($this->config['stoker_urlrewrite_translit'] ?? url_helper::METHOD_STATIC);

		if ($translit === url_helper::METHOD_INTL && !$intl_available)
		{
			$translit = url_helper::METHOD_STATIC;
		}

		// Same module, other mode: swapping the token keeps the sid and icat that
		// functions_module.php put on u_action.
		$u_server = str_replace('mode=settings', 'mode=server', $this->u_action);

		$this->template->assign_vars([
			'U_ACTION'							=> $this->u_action,
			'IMPORTANT_NOTICE'					=> $this->language->lang('ACP_URLREWRITE_IMPORTANT', '<a href="' . $u_server . '">', '</a>'),
			'S_INTL_AVAILABLE'					=> $intl_available,
			'STOKER_URLREWRITE_ENABLE'			=> (int) ($this->config['stoker_urlrewrite_enable'] ?? 0),
			'STOKER_URLREWRITE_REDIRECT_ENABLE'	=> (int) ($this->config['stoker_urlrewrite_redirect_enable'] ?? 0),
			'STOKER_URLREWRITE_TRANSLIT'		=> $translit,
			'U_TOGGLE_ENABLE'					=> $this->u_action . '&amp;action=toggle&amp;setting=stoker_urlrewrite_enable&amp;hash=' . generate_link_hash('togglestoker_urlrewrite_enable'),
			'U_TOGGLE_REDIRECT'					=> $this->u_action . '&amp;action=toggle&amp;setting=stoker_urlrewrite_redirect_enable&amp;hash=' . generate_link_hash('togglestoker_urlrewrite_redirect_enable'),
		]);
	}

	/**
	 * Flip a boolean config value, used by the AJAX toggle switches.
	 *
	 * @return void
	 */
	private function ajax_toggle(): void
	{
		$setting = $this->request->variable('setting', '');

		if (!in_array($setting, self::TOGGLES, true))
		{
			trigger_error($this->language->lang('FORM_INVALID'), E_USER_WARNING);
		}

		if (!check_link_hash($this->request->variable('hash', ''), 'toggle' . $setting))
		{
			trigger_error($this->language->lang('FORM_INVALID'), E_USER_WARNING);
		}

		$this->config->set($setting, (int) $this->config[$setting] ? 0 : 1);

		$this->log->add('admin', (int) $this->user->data['user_id'], $this->user->ip, 'ACP_URLREWRITE_LOG_SETTINGS');

		if ($this->request->is_ajax())
		{
			$json_response = new \phpbb\json_response();
			$json_response->send(['success' => true]);
		}

		redirect($this->u_action);
	}

	/**
	 * Server configuration (copy-paste rules) page.
	 *
	 * @return void
	 */
	public function handle_server(): void
	{
		$this->page_title = $this->language->lang('ACP_URLREWRITE_SERVER');

		$this->assign_version_check_vars();

		$this->template->assign_vars([
			'APACHE_FORWARD'	=> $this->apache_rules(false),
			'APACHE_REVERSE'	=> $this->apache_rules(true),
			'NGINX_FORWARD'		=> $this->nginx_rules(false),
			'NGINX_REVERSE'		=> $this->nginx_rules(true),
		]);
	}

	/**
	 * Assign version check template variables
	 *
	 * @return void
	 */
	private function assign_version_check_vars(): void
	{
		$md_manager = $this->ext_manager->create_extension_metadata_manager('stoker/urlrewrite');

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
				'S_VERSIONCHECK_UP_TO_DATE' => true,
				'VERSIONCHECK_CURRENT'      => $current_version,
			]);
			return;
		}

		$this->template->assign_vars([
			'S_VERSIONCHECK_OUTDATED'     => true,
			'VERSIONCHECK_CURRENT'        => $current_version,
			'VERSIONCHECK_LATEST'         => (string) ($update['current'] ?? ''),
			'U_VERSIONCHECK_DOWNLOAD'     => (string) ($update['download'] ?? ''),
		]);
	}

	/**
	 * One '#' comment line for the generated server rules.
	 *
	 * The '#' and the newline are syntax, so they stay here; only the prose is
	 * translated. Rendered into a textarea escaped with |e, so the strings behind
	 * these keys must stay plain text.
	 *
	 * @param string $key
	 * @return string
	 */
	protected function rule_comment(string $key): string
	{
		return '# ' . $this->language->lang($key) . "\n";
	}

	/**
	 * Root-relative path prefix to the board (no trailing slash; empty at root).
	 *
	 * @return string
	 */
	protected function board_path(): string
	{
		return rtrim((string) parse_url(generate_board_url(), PHP_URL_PATH), '/');
	}

	/**
	 * Apache rules.
	 *
	 * RewriteCond binds to the single RewriteRule that follows it, so the pair is
	 * repeated before every rule rather than written once at the top - otherwise
	 * only the first rule is guarded against real files and directories.
	 *
	 * @param bool $reverse
	 * @return string
	 */
	protected function apache_rules(bool $reverse): string
	{
		$guard = "RewriteCond %{REQUEST_FILENAME} !-f\nRewriteCond %{REQUEST_FILENAME} !-d\n";
		$prefix = $reverse ? $this->board_path() . '/' : '';
		$topic = $prefix . 'viewtopic.' . $this->php_ext;
		$forum = $prefix . 'viewforum.' . $this->php_ext;
		$flags = $reverse ? '[QSA,R=301,L]' : '[QSA,L]';

		$header = $reverse
			? $this->rule_comment('STOKER_URLREWRITE_RULES_APACHE_REVERSE') . $this->rule_comment('STOKER_URLREWRITE_RULES_UNINSTALL_NOTE')
			: $this->rule_comment('STOKER_URLREWRITE_RULES_APACHE_FORWARD') . $this->rule_comment('STOKER_URLREWRITE_RULES_APACHE_NOTE');

		return $header
			. $guard . "RewriteRule ^[^/]*-t([0-9]+)-p([0-9]+)$ {$topic}?t=$1&p=$2 {$flags}\n"
			. $guard . "RewriteRule ^post-p([0-9]+)$ {$topic}?p=$1 {$flags}\n"
			. $guard . "RewriteRule ^[^/]*-t([0-9]+)$ {$topic}?t=$1 {$flags}\n"
			. $guard . "RewriteRule ^[^/]*-f([0-9]+)$ {$forum}?f=$1 {$flags}";
	}

	/**
	 * @param bool $reverse
	 * @return string
	 */
	protected function nginx_rules(bool $reverse): string
	{
		$path = $this->board_path();

		// preg_quote() also escapes '-', which is only a metacharacter inside a
		// character class. Every other escape it adds is needed, so keep it and
		// undo just that one - these rules are read and edited by hand, and a
		// board in a hyphenated directory would otherwise get '/my\-board' in
		// its config file for no reason.
		$path_re = str_replace('\-', '-', preg_quote($path));
		$flag = $reverse ? 'permanent' : 'last';
		$header = $reverse
			? $this->rule_comment('STOKER_URLREWRITE_RULES_NGINX_REVERSE') . $this->rule_comment('STOKER_URLREWRITE_RULES_UNINSTALL_NOTE')
			: $this->rule_comment('STOKER_URLREWRITE_RULES_NGINX_FORWARD');

		return $header
			. $this->rule_comment('STOKER_URLREWRITE_RULES_NGINX_NOTE')
			. "rewrite ^{$path_re}/[^/]*-t([0-9]+)-p([0-9]+)$ {$path}/viewtopic.{$this->php_ext}?t=$1&p=$2 {$flag};\n"
			. "rewrite ^{$path_re}/post-p([0-9]+)$ {$path}/viewtopic.{$this->php_ext}?p=$1 {$flag};\n"
			. "rewrite ^{$path_re}/[^/]*-t([0-9]+)$ {$path}/viewtopic.{$this->php_ext}?t=$1 {$flag};\n"
			. "rewrite ^{$path_re}/[^/]*-f([0-9]+)$ {$path}/viewforum.{$this->php_ext}?f=$1 {$flag};";
	}
}
