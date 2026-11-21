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
use phpbb\log\log_interface;
use phpbb\request\request_interface;
use phpbb\template\template;
use phpbb\user;
use stoker\topicban\service\topicban_manager;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Ban form in the MCP post details page
 */
class mcp_listener implements EventSubscriberInterface
{
	/** @var array Allowed ban lengths in minutes, 0 = permanent, -1 = until date and time */
	protected const LENGTHS = [0, 60, 360, 1440, 10080, 43200, -1];

	/** @var int Highest ban end that fits an INT4 TIMESTAMP column (PostgreSQL, MSSQL) */
	protected const MAX_TIMESTAMP = 2147483647;

	/** @var int Maximum reason length in characters, as entered */
	protected const REASON_LENGTH = 200;

	/** @var int Maximum stored reason length, the ban_reason column size */
	protected const REASON_STORE_LENGTH = 255;

	/** @var auth */
	protected $auth;

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

	/** @var topicban_manager */
	protected $manager;

	/** @var string */
	protected $phpbb_root_path;

	/** @var string */
	protected $php_ext;

	/**
	 * @param auth              $auth
	 * @param language          $language
	 * @param log_interface     $log
	 * @param request_interface $request
	 * @param template          $template
	 * @param user              $user
	 * @param topicban_manager  $manager
	 * @param string            $phpbb_root_path
	 * @param string            $php_ext
	 */
	public function __construct(auth $auth, language $language, log_interface $log, request_interface $request, template $template, user $user, topicban_manager $manager, string $phpbb_root_path, string $php_ext)
	{
		$this->auth = $auth;
		$this->language = $language;
		$this->log = $log;
		$this->request = $request;
		$this->template = $template;
		$this->user = $user;
		$this->manager = $manager;
		$this->phpbb_root_path = $phpbb_root_path;
		$this->php_ext = $php_ext;
	}

	/**
	 * @return array
	 */
	public static function getSubscribedEvents()
	{
		return [
			'core.mcp_post_template_data'		=> 'post_template_data',
			'core.mcp_post_additional_options'	=> 'post_additional_options',
		];
	}

	/**
	 * Show ban status and form
	 *
	 * @param data $event
	 * @return void
	 */
	public function post_template_data(data $event): void
	{
		$post_info = $event['post_info'];
		$forum_id = (int) $post_info['forum_id'];
		$poster_id = (int) $post_info['poster_id'];

		if ($poster_id === ANONYMOUS || !$this->auth->acl_get('m_topicban', $forum_id))
		{
			return;
		}

		$this->language->add_lang('common', 'stoker/topicban');

		$protected = $this->manager->is_protected_user($poster_id, $forum_id, $post_info);
		$ban = $this->manager->get_ban((int) $post_info['topic_id'], $poster_id);
		$ban_end = $ban ? (int) $ban['ban_end'] : 0;

		if ($protected)
		{
			$status = $this->language->lang(($ban === null) ? 'TOPICBAN_STATUS_PROTECTED' : 'TOPICBAN_STATUS_PROTECTED_STALE');
		}
		else if ($ban === null)
		{
			$status = $this->language->lang('TOPICBAN_STATUS_NONE');
		}
		else if ($ban_end === 0)
		{
			$status = $this->language->lang('TOPICBAN_STATUS_PERMANENT');
		}
		else
		{
			$status = $this->language->lang('TOPICBAN_STATUS_UNTIL', $this->user->format_date($ban_end));
		}

		$this->template->assign_vars([
			'S_TOPICBAN_CAN'		=> true,
			'S_TOPICBAN_PROTECTED'	=> $protected,
			'S_TOPICBAN_ACTIVE'		=> $ban !== null,
			'S_TOPICBAN_UNTIL'		=> $ban_end > 0,
			'TOPICBAN_STATUS'		=> $status,
			'TOPICBAN_REASON'		=> $ban ? $ban['ban_reason'] : '',
			'TOPICBAN_UNTIL'		=> $ban_end ? $this->user->create_datetime()->setTimestamp($ban_end)->format('Y-m-d\TH:i') : '',
			'TOPICBAN_MIN_DATE'		=> $this->user->create_datetime()->format('Y-m-d\TH:i'),
			'TOPICBAN_MAX_DATE'		=> $this->user->create_datetime()->setTimestamp(self::MAX_TIMESTAMP)->format('Y-m-d\TH:i'),
		]);

		$event['s_additional_opts'] = true;
	}

	/**
	 * Handle ban and unban
	 *
	 * @param data $event
	 * @return void
	 */
	public function post_additional_options(data $event): void
	{
		$action = (string) $event['action'];

		if ($action !== 'topicban' && $action !== 'topicunban')
		{
			return;
		}

		$post_info = $event['post_info'];
		$forum_id = (int) $post_info['forum_id'];
		$topic_id = (int) $post_info['topic_id'];
		$post_id = (int) $post_info['post_id'];
		$poster_id = (int) $post_info['poster_id'];

		$this->language->add_lang('common', 'stoker/topicban');

		if (!check_form_key('mcp_post_details'))
		{
			trigger_error('FORM_INVALID', E_USER_WARNING);
		}

		if (!$this->auth->acl_get('m_topicban', $forum_id))
		{
			trigger_error('NOT_AUTHORISED', E_USER_WARNING);
		}

		if ($poster_id === ANONYMOUS)
		{
			trigger_error('TOPICBAN_ERROR_PROTECTED', E_USER_WARNING);
		}

		if ($action === 'topicunban')
		{
			if (!$this->manager->unban($topic_id, $poster_id))
			{
				trigger_error('TOPICBAN_ERROR_NOT_BANNED', E_USER_WARNING);
			}

			$log_key = 'LOG_TOPICBAN_REMOVED';
			$message = 'TOPICBAN_REMOVED';
		}
		else
		{
			if ($this->manager->is_protected_user($poster_id, $forum_id, $post_info))
			{
				trigger_error('TOPICBAN_ERROR_PROTECTED', E_USER_WARNING);
			}

			$ban_end = $this->get_ban_end();
			$reason = $this->get_reason();
			$was_active = $this->manager->get_ban($topic_id, $poster_id) !== null;

			$this->manager->ban($topic_id, $poster_id, (int) $this->user->data['user_id'], $ban_end, $reason);

			$notification_data = [
				'topic_id'		=> $topic_id,
				'forum_id'		=> $forum_id,
				'user_id'		=> $poster_id,
				'topic_title'	=> $post_info['topic_title'],
				'ban_end'		=> $ban_end,
				'ban_reason'	=> $reason,
			];

			if ($was_active)
			{
				$this->manager->update_ban_notification($notification_data);
			}
			else
			{
				$this->manager->notify_ban($notification_data);
			}

			$log_key = $was_active ? 'LOG_TOPICBAN_UPDATED' : 'LOG_TOPICBAN_ADDED';
			$message = $was_active ? 'TOPICBAN_UPDATED' : 'TOPICBAN_ADDED';
		}

		$this->log->add('mod', (int) $this->user->data['user_id'], $this->user->ip, $log_key, false, [
			'forum_id'	=> $forum_id,
			'topic_id'	=> $topic_id,
			'post_id'	=> $post_id,
			$post_info['username'],
			$post_info['topic_title'],
		]);

		$this->log->add('user', (int) $this->user->data['user_id'], $this->user->ip, $log_key, false, [
			'reportee_id' => $poster_id,
			$post_info['username'],
			$post_info['topic_title'],
		]);

		$redirect = append_sid("{$this->phpbb_root_path}mcp.{$this->php_ext}", 'i=main&amp;mode=post_details&amp;p=' . $post_id);
		meta_refresh(3, $redirect);
		trigger_error($this->language->lang($message) . '<br><br>' . $this->language->lang('RETURN_POST', '<a href="' . $redirect . '">', '</a>'));
	}

	/**
	 * Ban end timestamp from the form, 0 = permanent
	 *
	 * @return int
	 */
	protected function get_ban_end(): int
	{
		$length = $this->request->variable('topicban_length', 0);

		if (!in_array($length, self::LENGTHS, true))
		{
			trigger_error('TOPICBAN_ERROR_LENGTH', E_USER_WARNING);
		}

		if ($length === 0)
		{
			return 0;
		}

		if ($length > 0)
		{
			return time() + $length * 60;
		}

		// datetime-local value in the user's timezone: Y-m-dTH:i, seconds optional
		$date = $this->request->variable('topicban_until', '');

		if (!preg_match('#^(\d{4})-(\d{2})-(\d{2})T(\d{2}):(\d{2})(?::\d{2})?$#', $date, $match)
			|| !checkdate((int) $match[2], (int) $match[3], (int) $match[1])
			|| (int) $match[4] > 23
			|| (int) $match[5] > 59)
		{
			trigger_error('TOPICBAN_ERROR_DATE', E_USER_WARNING);
		}

		$ban_end = $this->user->create_datetime()
			->setDate((int) $match[1], (int) $match[2], (int) $match[3])
			->setTime((int) $match[4], (int) $match[5], 0)
			->getTimestamp();

		if ($ban_end <= time() || $ban_end > self::MAX_TIMESTAMP)
		{
			trigger_error('TOPICBAN_ERROR_DATE', E_USER_WARNING);
		}

		return $ban_end;
	}

	/**
	 * Ban reason from the form, 4-byte characters encoded as NCRs for utf8 tables
	 *
	 * @return string
	 */
	protected function get_reason(): string
	{
		$input = $this->request->variable('topicban_reason', '', true);
		$reason = '';

		// NCRs are longer than the characters they replace, shorten until the result fits the column
		for ($max_length = self::REASON_LENGTH; $max_length > 0; $max_length--)
		{
			$reason = utf8_encode_ucr(truncate_string($input, $max_length, self::REASON_STORE_LENGTH));

			if (utf8_strlen($reason) <= self::REASON_STORE_LENGTH)
			{
				break;
			}
		}

		return $reason;
	}
}
