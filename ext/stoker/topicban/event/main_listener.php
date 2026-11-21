<?php
/**
 *
 * Topic Ban extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2026 stoker - https://phpbb3bbcodes.com/
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

declare(strict_types=1);

namespace stoker\topicban\event;

use phpbb\auth\auth;
use phpbb\event\data;
use phpbb\language\language;
use phpbb\report\exception\report_permission_denied_exception;
use phpbb\request\request_interface;
use phpbb\template\template;
use phpbb\user;
use stoker\topicban\service\topicban_manager;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Enforces topic bans in posting, reporting, polls, quickmod, viewtopic and the email topic form
 */
class main_listener implements EventSubscriberInterface
{
	/** @var array Quickmod actions a topic poster can use without moderator permissions */
	protected const USER_QUICKMOD = ['lock', 'make_normal', 'make_sticky', 'make_announce', 'make_global'];

	/** @var auth */
	protected $auth;

	/** @var language */
	protected $language;

	/** @var request_interface */
	protected $request;

	/** @var template */
	protected $template;

	/** @var user */
	protected $user;

	/** @var topicban_manager */
	protected $manager;

	/** @var string */
	protected $php_ext;

	/** @var array Banned state of the current user per topic */
	protected $viewer_banned = [];

	/** @var array Ban notice per poster, key "topic_id:user_id" */
	protected $poster_notices = [];

	/**
	 * @param auth              $auth
	 * @param language          $language
	 * @param request_interface $request
	 * @param template          $template
	 * @param user              $user
	 * @param topicban_manager  $manager
	 * @param string            $php_ext
	 */
	public function __construct(auth $auth, language $language, request_interface $request, template $template, user $user, topicban_manager $manager, string $php_ext)
	{
		$this->auth = $auth;
		$this->language = $language;
		$this->request = $request;
		$this->template = $template;
		$this->user = $user;
		$this->manager = $manager;
		$this->php_ext = $php_ext;
	}

	/**
	 * @return array
	 */
	public static function getSubscribedEvents()
	{
		return [
			'core.permissions'									=> 'add_permission',
			'core.user_setup_after'								=> 'email_topic',
			'core.modify_posting_auth'							=> 'posting_auth',
			'core.report_post_auth'								=> 'report_auth',
			'core.mcp_main_before'								=> 'mcp_permissions',
			'core.viewtopic_before_f_read_check'				=> 'topic_actions',
			'core.viewtopic_add_quickmod_option_before'			=> 'quickmod_options',
			'core.viewtopic_modify_poll_data'					=> 'poll_data',
			'core.viewtopic_modify_post_data'					=> 'post_data',
			'core.viewtopic_modify_post_row'					=> 'post_row',
			'core.viewtopic_modify_quick_reply_template_vars'	=> 'quick_reply',
			'core.viewtopic_modify_page_title'					=> 'page_title',
			'core.delete_topics_before_query'					=> 'delete_topics',
			'core.delete_forum_content_before_query'			=> 'delete_forum_content',
			'core.delete_user_after'							=> 'delete_users',
			'core.ucp_display_module_before'					=> 'ucp_language',
		];
	}

	/**
	 * @param data $event
	 * @return void
	 */
	public function add_permission(data $event): void
	{
		$permissions = $event['permissions'];
		$permissions['m_topicban'] = ['lang' => 'ACL_M_TOPICBAN', 'cat' => 'misc'];
		$event['permissions'] = $permissions;
	}

	/**
	 * Block the email topic form, memberlist has no event in that mode
	 *
	 * @return void
	 */
	public function email_topic(): void
	{
		if (defined('IN_ERROR_HANDLER')
			|| ($this->user->page['page_name'] ?? '') !== 'memberlist.' . $this->php_ext
			|| $this->request->variable('mode', '') !== 'email'
			|| $this->request->variable('u', 0)
			|| !$this->is_viewer_banned($this->request->variable('t', 0)))
		{
			return;
		}

		$this->load_language();

		send_status_line(403, 'Forbidden');
		trigger_error('TOPICBAN_EMAIL_DENIED');
	}

	/**
	 * Block reply, quote, edit, delete and bump
	 *
	 * @param data $event
	 * @return void
	 */
	public function posting_auth(data $event): void
	{
		if (!in_array($event['mode'], ['reply', 'quote', 'edit', 'delete', 'soft_delete', 'bump'], true))
		{
			return;
		}

		$topic_id = (int) $event['topic_id'];

		if (!$event['is_authed'] || !$this->is_viewer_banned($topic_id))
		{
			return;
		}

		$this->load_language();

		$ban = $this->manager->get_ban($topic_id, (int) $this->user->data['user_id']);
		$ban_end = (int) $ban['ban_end'];

		$error = $event['error'];
		$error[] = $ban_end ? $this->language->lang('TOPICBAN_POSTING_DENIED_UNTIL', $this->user->format_date($ban_end)) : $this->language->lang('TOPICBAN_POSTING_DENIED');
		$event['error'] = $error;
		$event['is_authed'] = false;
	}

	/**
	 * Block reporting
	 *
	 * @param data $event
	 * @return void
	 * @throws report_permission_denied_exception
	 */
	public function report_auth(data $event): void
	{
		if ($this->is_viewer_banned((int) $event['report_data']['topic_id']))
		{
			$this->load_language();

			throw new report_permission_denied_exception('TOPICBAN_REPORT_DENIED');
		}
	}

	/**
	 * Block topic poster actions (lock, topic type) in mcp_main, quickmod and topic lists
	 *
	 * @param data $event
	 * @return void
	 */
	public function mcp_permissions(data $event): void
	{
		if (!in_array($event['action'], self::USER_QUICKMOD, true))
		{
			return;
		}

		// Same topic source as mcp_main
		$topic_ids = $event['quickmod'] ? [$this->request->variable('t', 0)] : $this->request->variable('topic_id_list', [0]);
		$topic_ids = array_unique(array_map('intval', $topic_ids));

		// One query for all topics instead of one per topic
		$this->manager->preload_bans($topic_ids, (int) $this->user->data['user_id']);

		foreach ($topic_ids as $topic_id)
		{
			if ($this->is_viewer_banned($topic_id))
			{
				send_status_line(403, 'Forbidden');
				trigger_error('NOT_AUTHORISED');
			}
		}
	}

	/**
	 * Block subscribing to and bookmarking the topic, removing both stays allowed
	 *
	 * @param data $event
	 * @return void
	 */
	public function topic_actions(data $event): void
	{
		$topic_data = $event['topic_data'];

		// Only the topic subscription, viewtopic also handles watch=forum
		$watch = $this->request->variable('watch', '', false, request_interface::GET) === 'topic' && !isset($topic_data['notify_status']);
		$bookmark = $this->request->variable('bookmark', 0) && empty($topic_data['bookmarked']);

		if ((!$watch && !$bookmark) || !$this->is_viewer_banned((int) $event['topic_id']))
		{
			return;
		}

		$this->load_language();

		send_status_line(403, 'Forbidden');
		trigger_error('TOPICBAN_ACTION_DENIED');
	}

	/**
	 * Hide topic poster quickmod actions in viewtopic
	 *
	 * @param data $event
	 * @return void
	 */
	public function quickmod_options(data $event): void
	{
		if (!$this->is_viewer_banned((int) $event['topic_id']))
		{
			return;
		}

		$quickmod_array = $event['quickmod_array'];
		foreach (self::USER_QUICKMOD as $option)
		{
			if (isset($quickmod_array[$option]))
			{
				$quickmod_array[$option][1] = false;
			}
		}
		$event['quickmod_array'] = $quickmod_array;
	}

	/**
	 * Block voting and show the results
	 *
	 * @param data $event
	 * @return void
	 */
	public function poll_data(data $event): void
	{
		if ($event['s_can_vote'] && $this->is_viewer_banned((int) $event['topic_id']))
		{
			$event['s_can_vote'] = false;
			$event['s_display_results'] = true;
		}
	}

	/**
	 * Load the bans of all posters on the page with one query instead of one per poster
	 *
	 * @param data $event
	 * @return void
	 */
	public function post_data(data $event): void
	{
		$this->manager->preload_topic_bans((int) $event['topic_id'], array_keys($event['user_cache']));
	}

	/**
	 * Show the ban notice in the posts of banned users, remove post buttons and contact links for a banned viewer
	 *
	 * @param data $event
	 * @return void
	 */
	public function post_row(data $event): void
	{
		$topic_id = (int) $event['topic_data']['topic_id'];
		$poster_id = (int) $event['poster_id'];
		$post_row = $event['post_row'];

		$post_row['TOPICBAN_POST_NOTICE'] = $this->get_poster_notice($topic_id, $poster_id, (string) $post_row['POST_AUTHOR_FULL'], $event['row']);

		if ($this->is_viewer_banned($topic_id))
		{
			$post_row['U_QUOTE'] = $post_row['U_EDIT'] = $post_row['U_DELETE'] = $post_row['U_REPORT'] = $post_row['U_PM'] = $post_row['U_EMAIL'] = $post_row['U_JABBER'] = '';

			$user_cache = $event['user_cache'];
			if (isset($user_cache[$poster_id]))
			{
				$user_cache[$poster_id]['email'] = $user_cache[$poster_id]['jabber'] = '';
				$event['user_cache'] = $user_cache;
			}

			$cp_row = $event['cp_row'];
			if (!empty($cp_row['blockrow']))
			{
				$cp_row['blockrow'] = array_values(array_filter($cp_row['blockrow'], function ($field) {
					return empty($field['S_PROFILE_CONTACT']);
				}));
				$event['cp_row'] = $cp_row;
			}
		}

		$event['post_row'] = $post_row;
	}

	/**
	 * Ban notice for a poster, empty if not banned or protected
	 *
	 * @param int    $topic_id
	 * @param int    $poster_id
	 * @param string $author_full
	 * @param array  $user_row Post row with the poster's user columns
	 * @return string
	 */
	protected function get_poster_notice(int $topic_id, int $poster_id, string $author_full, array $user_row): string
	{
		$key = $topic_id . ':' . $poster_id;

		if (!isset($this->poster_notices[$key]))
		{
			$notice = '';
			$ban = $this->manager->get_ban($topic_id, $poster_id);

			if ($ban !== null && !$this->manager->is_protected_user($poster_id, (int) $ban['forum_id'], $user_row))
			{
				$this->load_language();

				$ban_end = (int) $ban['ban_end'];
				$notice = $ban_end
					? $this->language->lang('TOPICBAN_POST_NOTICE_UNTIL', $author_full, $this->user->format_date($ban_end))
					: $this->language->lang('TOPICBAN_POST_NOTICE', $author_full);
			}

			$this->poster_notices[$key] = $notice;
		}

		return $this->poster_notices[$key];
	}

	/**
	 * @param data $event
	 * @return void
	 */
	public function quick_reply(data $event): void
	{
		if ($this->is_viewer_banned((int) $event['topic_data']['topic_id']))
		{
			$tpl_ary = $event['tpl_ary'];
			$tpl_ary['S_QUICK_REPLY'] = false;
			$event['tpl_ary'] = $tpl_ary;
		}
	}

	/**
	 * Remove reply, bump, email, subscribe and bookmark links, assign the banner
	 *
	 * @param data $event
	 * @return void
	 */
	public function page_title(data $event): void
	{
		$topic_data = $event['topic_data'];
		$topic_id = (int) $topic_data['topic_id'];

		if (!$this->is_viewer_banned($topic_id))
		{
			return;
		}

		$this->load_language();

		$ban = $this->manager->get_ban($topic_id, (int) $this->user->data['user_id']);
		$ban_end = (int) $ban['ban_end'];

		$template_vars = [
			'S_DISPLAY_REPLY_INFO'	=> false,
			'U_POST_REPLY_TOPIC'	=> '',
			'U_BUMP_TOPIC'			=> '',
			'U_EMAIL_TOPIC'			=> '',

			'S_TOPICBAN_BANNED'		=> true,
			'TOPICBAN_NOTICE'		=> $ban_end ? $this->language->lang('TOPICBAN_NOTICE_UNTIL', $this->user->format_date($ban_end)) : $this->language->lang('TOPICBAN_NOTICE'),
			'TOPICBAN_REASON'		=> $ban['ban_reason'],
		];

		// Keep the unsubscribe and remove bookmark links
		if (!isset($topic_data['notify_status']))
		{
			$template_vars['U_WATCH_TOPIC'] = $template_vars['U_WATCH_TOPIC_TOGGLE'] = '';
		}

		if (empty($topic_data['bookmarked']))
		{
			$template_vars['U_BOOKMARK_TOPIC'] = '';
		}

		$this->template->assign_vars($template_vars);
	}

	/**
	 * Only topics with ban rows are handled, other topic deletions cost one indexed lookup
	 *
	 * @param data $event
	 * @return void
	 */
	public function delete_topics(data $event): void
	{
		$this->manager->delete_topic_bans($this->manager->get_banned_topics($event['topic_ids']));
	}

	/**
	 * Forum content deleted in the ACP, the event's topic_ids only holds topics with attachments
	 *
	 * @param data $event
	 * @return void
	 */
	public function delete_forum_content(data $event): void
	{
		$this->manager->delete_forum_bans((int) $event['forum_id']);
	}

	/**
	 * @param data $event
	 * @return void
	 */
	public function delete_users(data $event): void
	{
		$this->manager->delete_user_bans($event['user_ids']);
	}

	/**
	 * Language for the notification type in UCP notification options
	 *
	 * @param data $event
	 * @return void
	 */
	public function ucp_language(data $event): void
	{
		if ($event['mode'] === 'notification_options')
		{
			$this->load_language();
		}
	}

	/**
	 * @param int $topic_id
	 * @return bool
	 */
	protected function is_viewer_banned(int $topic_id): bool
	{
		if (!isset($this->viewer_banned[$topic_id]))
		{
			$this->viewer_banned[$topic_id] = $this->user->data['is_registered']
				&& !$this->user->data['is_bot']
				&& !$this->auth->acl_get('a_')
				&& !$this->auth->acl_get('m_')
				&& $this->manager->is_banned($topic_id, (int) $this->user->data['user_id']);
		}

		return $this->viewer_banned[$topic_id];
	}

	/**
	 * @return void
	 */
	protected function load_language(): void
	{
		$this->language->add_lang('common', 'stoker/topicban');
	}
}
