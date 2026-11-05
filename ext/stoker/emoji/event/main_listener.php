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

namespace stoker\emoji\event;

use phpbb\auth\auth;
use phpbb\cache\service;
use phpbb\config\config;
use phpbb\db\driver\driver_interface;
use phpbb\language\language;
use phpbb\template\template;
use phpbb\user;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Emoji V2 event listener
 */
class main_listener implements EventSubscriberInterface
{
	/** @var auth */
	protected $auth;

	/** @var service */
	protected $cache;

	/** @var config */
	protected $config;

	/** @var driver_interface */
	protected $db;

	/** @var language */
	protected $language;

	/** @var template */
	protected $template;

	/** @var user */
	protected $user;

	/** @var string */
	protected $emoji_table;

	/** @var array|null */
	protected $emojis = null;

	/** @var bool */
	protected $signature_editor = false;

	/**
	 * @param auth				$auth
	 * @param service			$cache
	 * @param config			$config
	 * @param driver_interface	$db
	 * @param language			$language
	 * @param template			$template
	 * @param user				$user
	 * @param string			$emoji_table
	 */
	public function __construct(auth $auth, service $cache, config $config, driver_interface $db, language $language, template $template, user $user, string $emoji_table)
	{
		$this->auth = $auth;
		$this->cache = $cache;
		$this->config = $config;
		$this->db = $db;
		$this->language = $language;
		$this->template = $template;
		$this->user = $user;
		$this->emoji_table = $emoji_table;
	}

	/**
	 * @return array
	 */
	public static function getSubscribedEvents()
	{
		return [
			'core.page_header'									=> 'set_emoji_flag',
			'core.posting_modify_template_vars'					=> 'assign_posting_picker',
			'core.viewtopic_modify_quick_reply_template_vars'	=> 'assign_quick_reply_picker',
			'core.ucp_pm_compose_template'						=> 'assign_pm_picker',
			'core.ucp_profile_modify_signature'					=> 'assign_signature_picker',
			'core.text_formatter_s9e_configure_after'			=> 'configure_emoji',
		];
	}

	/**
	 * Set global template flags based on the current emoji mode.
	 *
	 * @return void
	 */
	public function set_emoji_flag(): void
	{
		// ucp_profile.php fills the signature editor's smiley box from
		// allow_sig_smilies, so with smilies off board-wide it is emptied
		// here, as page_header runs after generate_smilies().
		if ($this->signature_editor && empty($this->config['allow_smilies']))
		{
			$this->template->destroy_block_vars('smiley');
			$this->template->assign_var('S_SHOW_SMILEY_LINK', false);
		}

		$mode = (int) $this->config['stoker_emoji_mode'];

		if ($mode === 0)
		{
			return;
		}

		$emoji_font = $mode === 2 || $mode === 3;

		$vars = [
			'S_STOKER_EMOJI'		=> true,
			'S_STOKER_EMOJI_FONT'	=> $emoji_font,
			'STOKER_EMOJI_SIZE'		=> $this->clamp_size($emoji_font ? $this->config['stoker_emoji_noto_size'] : $this->config['stoker_emoji_size']),
		];

		if ($emoji_font)
		{
			// Mode 2 bundles Noto, mode 3 Twemoji. The subset of common emoji
			// is the default, the full font is an ACP choice shared by both.
			// All are bundled, so assets_version keeps the cached copy current
			// across extension updates.
			$font = $mode === 3 ? 'TwemojiColor.woff2' : 'NotoColorEmoji.woff2';

			if (!empty($this->config['stoker_emoji_noto_full']))
			{
				$font = 'full_' . $font;
			}

			$vars['STOKER_EMOJI_FONT_URL'] = generate_board_url() . '/ext/stoker/emoji/fonts/' . $font . '?assets_version=' . (int) $this->config['assets_version'];
		}

		$this->template->assign_vars($vars);
	}

	/**
	 * Assign the emoji template variables on the quick reply editor. With the
	 * quick reply picker switched off only the shortcode map is emitted, so
	 * typed shortcodes still convert there exactly as on the posting page.
	 *
	 * @param \phpbb\event\data $event
	 * @return void
	 */
	public function assign_quick_reply_picker($event): void
	{
		// Emoji take the place of smilies, so where smilies are denied, or
		// switched off in the user's own preferences, there is neither the
		// picker nor shortcode conversion.
		if (!$this->user->optionget('smilies') || !$this->auth->acl_get('f_smilies', (int) $event['topic_data']['forum_id']))
		{
			return;
		}

		$this->assign_emoji_vars(empty($this->config['stoker_quickreply_emoji']));
	}

	/**
	 * Assign the emoji picker template variables on the posting editor.
	 *
	 * @param \phpbb\event\data $event
	 * @return void
	 */
	public function assign_posting_picker($event): void
	{
		if (!$this->user->optionget('smilies') || !$this->auth->acl_get('f_smilies', (int) $event['forum_id']))
		{
			return;
		}

		// posting.php assigns page_data after this event, so the status line
		// has to be replaced in the event data, not through the template.
		if ($this->assign_emoji_vars(false))
		{
			$page_data = $event['page_data'];
			$page_data['SMILIES_STATUS'] = $this->language->lang('STOKER_EMOJI_ARE_ON');
			$event['page_data'] = $page_data;
		}
	}

	/**
	 * Assign the emoji picker template variables on the private message editor.
	 *
	 * @param \phpbb\event\data $event
	 * @return void
	 */
	public function assign_pm_picker($event): void
	{
		if (empty($this->config['auth_smilies_pm']) || !$this->user->optionget('smilies') || !$this->auth->acl_get('u_pm_smilies'))
		{
			return;
		}

		// ucp_pm_compose.php assigns template_ary after this event.
		if ($this->assign_emoji_vars(false))
		{
			$template_ary = $event['template_ary'];
			$template_ary['SMILIES_STATUS'] = $this->language->lang('STOKER_EMOJI_ARE_ON');
			$event['template_ary'] = $template_ary;
		}
	}

	/**
	 * Assign the emoji picker template variables on the signature editor.
	 *
	 * @return void
	 */
	public function assign_signature_picker(): void
	{
		$this->signature_editor = true;

		if (!$this->user->optionget('smilies'))
		{
			return;
		}

		$this->assign_emoji_vars(false);
	}

	/**
	 * Assign the emoji template variables, optionally without the picker markup.
	 *
	 * @param bool $map_only
	 * @return bool True when the picker markup was assigned
	 */
	protected function assign_emoji_vars(bool $map_only): bool
	{
		$mode = (int) $this->config['stoker_emoji_mode'];

		// The mode is the single source of truth: display_settings() mirrors it
		// into allow_smilies, so testing that config here only ever diverges
		// when it is edited outside this extension.
		if ($mode === 0)
		{
			return false;
		}

		$emojis = $this->load_emojis();

		if (empty($emojis))
		{
			return false;
		}

		$has_more = false;

		if (!$map_only)
		{
			$this->language->add_lang('emoji', 'stoker/emoji');

			foreach ($emojis as $entry)
			{
				if (!$entry['posting'])
				{
					$has_more = true;
					break;
				}
			}
		}

		$map = [];
		foreach ($emojis as $entry)
		{
			if (!$map_only)
			{
				$emoji_vars = [
					'CHAR'		=> $entry['char'],
					'INSERT'	=> $entry['char'],
					'TITLE'		=> $entry['emotion'],
				];

				if ($entry['posting'])
				{
					$this->template->assign_block_vars('emoji_bar', $emoji_vars);
				}

				// The "More" popup mirrors phpBB's smiley window: it lists every
				// emoji, on-posting ones included, not just the leftovers. Only
				// populated when a non-posting emoji exists, so the popup renders.
				if ($has_more)
				{
					$this->template->assign_block_vars('emoji_more', $emoji_vars);
				}
			}

			foreach ($entry['codes'] as $code)
			{
				$map[$code] = $entry['char'];
			}
		}

		// Nothing to insert and nothing to substitute, so the container and its
		// script would be dead weight.
		if ($map_only && empty($map))
		{
			return false;
		}

		$this->template->assign_vars([
			'S_STOKER_EMOJI'		=> true,
			'S_STOKER_EMOJI_FONT'	=> $mode === 2 || $mode === 3,
			'S_STOKER_EMOJI_PICKER'	=> true,
			'S_STOKER_EMOJI_MORE'	=> $has_more,
			'STOKER_EMOJI_MAP'		=> json_encode($map, JSON_UNESCAPED_UNICODE),
		]);

		return !$map_only;
	}

	/**
	 * Render emoji as a styled raw character instead of a Twemoji CDN image.
	 *
	 * @param \phpbb\event\data $event
	 * @return void
	 */
	public function configure_emoji($event): void
	{
		if ((int) $this->config['stoker_emoji_mode'] === 0)
		{
			return;
		}

		$configurator = $event['configurator'];

		// Dropping the plugin stops new text producing EMOJI tags, but the tag
		// itself was registered by phpBB's factory before this event and keeps
		// its Twemoji CDN template, so posts parsed before the switch would go
		// on rendering as remote images. The tag goes with it; text already
		// stored inside the tag is then rendered as the plain character.
		unset($configurator->Emoji);
		unset($configurator->tags['EMOJI']);
	}

	/**
	 * Clamp a configured emoji size to a safe float (0.5–5.0) and format it
	 * locale-independently for direct, unescaped output into a CSS rule.
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
	 * Load the emoji set as a list of {char, codes[], emotion, posting} entries (cached).
	 *
	 * @return array
	 */
	protected function load_emojis(): array
	{
		if ($this->emojis !== null)
		{
			return $this->emojis;
		}

		$cached = $this->cache->get('_stoker_emoji_list');

		if ($cached !== false)
		{
			$this->emojis = $cached;
			return $this->emojis;
		}

		$emojis = [];

		$sql = 'SELECT emoji_char, emoji_codes, emoji_emotion, display_on_posting
			FROM ' . $this->emoji_table . '
			ORDER BY emoji_order ASC, emoji_id ASC';
		$result = $this->db->sql_query($sql);
		while ($row = $this->db->sql_fetchrow($result))
		{
			$char = \utf8_decode_ncr((string) $row['emoji_char']);

			if ($char === '')
			{
				continue;
			}

			$codes = [];
			foreach (preg_split('/\s+/', \utf8_decode_ncr((string) $row['emoji_codes'])) as $code)
			{
				if ($code !== '')
				{
					$codes[] = $code;
				}
			}

			$emojis[] = [
				'char'		=> $char,
				'codes'		=> $codes,
				'emotion'	=> \utf8_decode_ncr((string) $row['emoji_emotion']),
				'posting'	=> (bool) $row['display_on_posting'],
			];
		}
		$this->db->sql_freeresult($result);

		$this->cache->put('_stoker_emoji_list', $emojis);
		$this->emojis = $emojis;

		return $this->emojis;
	}
}
