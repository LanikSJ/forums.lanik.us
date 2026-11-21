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

namespace stoker\topicban\controller;

use phpbb\auth\auth;
use phpbb\config\config;
use phpbb\language\language;
use phpbb\log\log_interface;
use phpbb\pagination;
use phpbb\request\request_interface;
use phpbb\template\template;
use phpbb\user;
use stoker\topicban\service\topicban_manager;

/**
 * MCP list of active topic bans
 */
class mcp_controller
{
	/** @var auth */
	protected $auth;

	/** @var config */
	protected $config;

	/** @var language */
	protected $language;

	/** @var log_interface */
	protected $log;

	/** @var pagination */
	protected $pagination;

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
	 * @param config            $config
	 * @param language          $language
	 * @param log_interface     $log
	 * @param pagination        $pagination
	 * @param request_interface $request
	 * @param template          $template
	 * @param user              $user
	 * @param topicban_manager  $manager
	 * @param string            $phpbb_root_path
	 * @param string            $php_ext
	 */
	public function __construct(auth $auth, config $config, language $language, log_interface $log, pagination $pagination, request_interface $request, template $template, user $user, topicban_manager $manager, string $phpbb_root_path, string $php_ext)
	{
		$this->auth = $auth;
		$this->config = $config;
		$this->language = $language;
		$this->log = $log;
		$this->pagination = $pagination;
		$this->request = $request;
		$this->template = $template;
		$this->user = $user;
		$this->manager = $manager;
		$this->phpbb_root_path = $phpbb_root_path;
		$this->php_ext = $php_ext;
	}

	/**
	 * Display the ban list
	 *
	 * @param string $u_action
	 * @return void
	 */
	public function display(string $u_action): void
	{
		$this->language->add_lang('common', 'stoker/topicban');

		$forum_ids = array_values(array_intersect(
			array_map('intval', array_keys($this->auth->acl_getf('m_topicban', true))),
			array_map('intval', array_keys($this->auth->acl_getf('f_read', true)))
		));

		if (empty($forum_ids))
		{
			trigger_error('NOT_AUTHORISED', E_USER_WARNING);
		}

		add_form_key('stoker_topicban_mcp');

		$limit = max(1, (int) $this->config['topics_per_page']);

		if ($this->request->is_set_post('unban'))
		{
			$this->unban_marked($forum_ids, $limit, $u_action);
		}

		$total = $this->manager->count_bans($forum_ids);
		$start = $this->pagination->validate_start($this->request->variable('start', 0), $limit, $total);

		foreach ($this->manager->get_bans($forum_ids, $start, $limit) as $row)
		{
			$ban_end = (int) $row['ban_end'];
			$banned_by = (int) $row['banned_by'];

			$this->template->assign_block_vars('bans', [
				'KEY'			=> (int) $row['topic_id'] . '_' . (int) $row['user_id'],
				'TOPIC_TITLE'	=> censor_text($row['topic_title']),
				'U_TOPIC'		=> append_sid("{$this->phpbb_root_path}viewtopic.{$this->php_ext}", 't=' . (int) $row['topic_id']),
				'USERNAME_FULL'	=> get_username_string('full', (int) $row['user_id'], $row['username'], $row['user_colour']),
				'BANNED_BY'		=> ($row['banner_name'] !== null) ? get_username_string('full', $banned_by, $row['banner_name'], $row['banner_colour']) : $this->language->lang('TOPICBAN_DELETED_USER'),
				'BAN_TIME'		=> $this->user->format_date((int) $row['ban_time']),
				'BAN_END'		=> $ban_end ? $this->user->format_date($ban_end) : $this->language->lang('TOPICBAN_PERMANENT'),
				'REASON'		=> $row['ban_reason'],
			]);
		}

		$this->pagination->generate_template_pagination($u_action, 'pagination', 'start', $total, $limit, $start);

		$this->template->assign_vars([
			'U_ACTION'		=> $u_action,
			'TOTAL_BANS'	=> $this->language->lang('TOPICBAN_TOTAL', $total),
		]);
	}

	/**
	 * Unban the marked bans, at most one page of the list
	 *
	 * @param array  $forum_ids
	 * @param int    $limit
	 * @param string $u_action
	 * @return void
	 */
	protected function unban_marked(array $forum_ids, int $limit, string $u_action): void
	{
		if (!check_form_key('stoker_topicban_mcp'))
		{
			trigger_error('FORM_INVALID', E_USER_WARNING);
		}

		$pairs = [];
		foreach ($this->request->variable('mark', ['']) as $key)
		{
			$parts = explode('_', $key);

			if (count($parts) === 2 && (int) $parts[0] > 0 && (int) $parts[1] > 0)
			{
				$pairs[(int) $parts[0] . '_' . (int) $parts[1]] = [(int) $parts[0], (int) $parts[1]];

				if (count($pairs) >= $limit)
				{
					break;
				}
			}
		}

		if (empty($pairs))
		{
			trigger_error('TOPICBAN_ERROR_NONE_MARKED', E_USER_WARNING);
		}

		$rows = $this->manager->unban_pairs(array_values($pairs), $forum_ids);

		foreach ($rows as $row)
		{
			$this->log->add('mod', (int) $this->user->data['user_id'], $this->user->ip, 'LOG_TOPICBAN_REMOVED', false, [
				'forum_id'	=> (int) $row['forum_id'],
				'topic_id'	=> (int) $row['topic_id'],
				$row['username'],
				$row['topic_title'],
			]);

			$this->log->add('user', (int) $this->user->data['user_id'], $this->user->ip, 'LOG_TOPICBAN_REMOVED', false, [
				'reportee_id' => (int) $row['user_id'],
				$row['username'],
				$row['topic_title'],
			]);
		}

		meta_refresh(3, $u_action);
		trigger_error($this->language->lang('TOPICBAN_REMOVED_COUNT', count($rows)) . '<br><br>' . $this->language->lang('RETURN_PAGE', '<a href="' . $u_action . '">', '</a>'));
	}
}
