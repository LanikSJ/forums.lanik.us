<?php
/**
 * Aurora Tech TFA — TOTP Two Factor Authentication extension for phpBB.
 *
 * @copyright (c) 2026 Aurora Tech Inc.
 * @license GPL-2.0-only
 */

namespace aurora_tech\tfa\acp;

use aurora_tech\tfa\service\policy_service;

/**
 * ACP module: policy, statistics and audit.
 *
 * phpBB p_master modules receive no dependency injection; services are
 * fetched from the global container. Business logic lives in the
 * unit-tested policy_service — this class is glue and reporting queries.
 */
class main_module
{
	/** @var string Template file assigned by phpBB module framework. */
	public $tpl_name;

	/** @var string Page title language key. */
	public $page_title;

	/** @var string Module action URL provided by p_master. */
	public $u_action;

	/**
	 * Module entry point, dispatched by phpBB's p_master.
	 *
	 * @param int    $id   Module id.
	 * @param string $mode Module mode (settings|stats|audit).
	 *
	 * @return void
	 */
	public function main($id, $mode)
	{
		global $phpbb_container;

		/** @var \phpbb\language\language $language */
		$language = $phpbb_container->get('language');
		/** @var \phpbb\template\template $template */
		$template = $phpbb_container->get('template');

		$language->add_lang('tfa', 'aurora_tech/tfa');

		$this->tpl_name = 'acp_aurora_tfa';
		$this->page_title = 'ACP_AURORA_TFA_TITLE';

		switch ($mode)
		{
			case 'stats':
				$this->stats($phpbb_container, $template);
				break;

			case 'audit':
				$this->audit($phpbb_container, $template);
				break;

			case 'settings':
			default:
				$this->settings($phpbb_container, $template, $language);
				break;
		}
	}

	/**
	 * Policy configuration screen.
	 *
	 * @param \Symfony\Component\DependencyInjection\ContainerInterface $container Service container.
	 * @param \phpbb\template\template                                  $template  Template engine.
	 * @param \phpbb\language\language                                  $language  Language service.
	 *
	 * @return void
	 */
	protected function settings($container, $template, $language)
	{
		/** @var \phpbb\request\request_interface $request */
		$request = $container->get('request');
		/** @var policy_service $policy */
		$policy = $container->get('aurora_tech.tfa.policy');
		/** @var \phpbb\db\driver\driver_interface $db */
		$db = $container->get('dbal.conn');
		/** @var \phpbb\group\helper $group_helper */
		$group_helper = $container->get('group_helper');
		/** @var \phpbb\user $user */
		$user = $container->get('user');
		/** @var \phpbb\log\log_interface $log */
		$log = $container->get('log');

		add_form_key('aurora_tfa_acp');

		if ($request->is_set_post('submit'))
		{
			if (!check_form_key('aurora_tfa_acp'))
			{
				trigger_error($language->lang('FORM_INVALID') . adm_back_link($this->u_action), E_USER_WARNING);
			}

			$new_mode = $request->variable('aurora_tfa_mode', policy_service::MODE_OPTIONAL);
			$group_ids = $request->variable('aurora_tfa_groups', [0]);

			$policy->set_policy($new_mode, $group_ids);

			$log->add('admin', (int) $user->data['user_id'], $user->ip, 'LOG_AURORA_TFA_CONFIG', time());

			trigger_error($language->lang('CONFIG_UPDATED') . adm_back_link($this->u_action));
		}

		$current_mode = $policy->get_mode();
		$required = $this->required_group_ids_raw($container);

		$sql = 'SELECT group_id, group_name, group_type
			FROM ' . GROUPS_TABLE . "
			WHERE group_name <> 'BOTS' AND group_name <> 'GUESTS'
			ORDER BY group_name ASC";
		$result = $db->sql_query($sql);

		while ($row = $db->sql_fetchrow($result))
		{
			$template->assign_block_vars('groups', [
				'ID'		=> (int) $row['group_id'],
				'NAME'		=> $group_helper->get_name($row['group_name']),
				'S_SELECTED'	=> in_array((int) $row['group_id'], $required, true),
			]);
		}
		$db->sql_freeresult($result);

		$template->assign_vars([
			'S_AURORA_TFA_MODE'				=> 'settings',
			'S_MODE_DISABLED'				=> $current_mode === policy_service::MODE_DISABLED,
			'S_MODE_OPTIONAL'				=> $current_mode === policy_service::MODE_OPTIONAL,
			'S_MODE_REQUIRED'				=> $current_mode === policy_service::MODE_REQUIRED_GROUPS,
			'MODE_DISABLED_VALUE'			=> policy_service::MODE_DISABLED,
			'MODE_OPTIONAL_VALUE'			=> policy_service::MODE_OPTIONAL,
			'MODE_REQUIRED_VALUE'			=> policy_service::MODE_REQUIRED_GROUPS,
			'U_ACTION'						=> $this->u_action,
		]);
	}

	/**
	 * Raw configured group ids, regardless of the current mode (so the
	 * selection is preserved when the admin toggles modes).
	 *
	 * @param \Symfony\Component\DependencyInjection\ContainerInterface $container Service container.
	 *
	 * @return int[] Group ids.
	 */
	protected function required_group_ids_raw($container)
	{
		/** @var \phpbb\config\db_text $config_text */
		$config_text = $container->get('config_text');

		$decoded = json_decode((string) $config_text->get(policy_service::CONFIG_REQUIRED_GROUPS), true);

		return is_array($decoded) ? array_map('intval', $decoded) : [];
	}

	/**
	 * Statistics screen: enrolment counts, board-wide and per group.
	 *
	 * @param \Symfony\Component\DependencyInjection\ContainerInterface $container Service container.
	 * @param \phpbb\template\template                                  $template  Template engine.
	 *
	 * @return void
	 */
	protected function stats($container, $template)
	{
		/** @var \phpbb\db\driver\driver_interface $db */
		$db = $container->get('dbal.conn');
		/** @var \phpbb\group\helper $group_helper */
		$group_helper = $container->get('group_helper');
		/** @var \phpbb\config\config $config */
		$config = $container->get('config');

		$table_prefix = $container->getParameter('core.table_prefix');
		$totp_table = $table_prefix . 'aurora_tfa_totp';

		$sql = 'SELECT COUNT(user_id) AS enrolled FROM ' . $totp_table;
		$result = $db->sql_query($sql);
		$enrolled = (int) $db->sql_fetchfield('enrolled');
		$db->sql_freeresult($result);

		$sql = 'SELECT g.group_id, g.group_name, COUNT(DISTINCT ug.user_id) AS members, COUNT(DISTINCT t.user_id) AS enrolled
			FROM ' . GROUPS_TABLE . ' g
			LEFT JOIN ' . USER_GROUP_TABLE . ' ug ON ug.group_id = g.group_id AND ug.user_pending = 0
			LEFT JOIN ' . $totp_table . " t ON t.user_id = ug.user_id
			WHERE g.group_name <> 'BOTS' AND g.group_name <> 'GUESTS'
			GROUP BY g.group_id, g.group_name
			ORDER BY g.group_name ASC";
		$result = $db->sql_query($sql);

		while ($row = $db->sql_fetchrow($result))
		{
			$members = (int) $row['members'];
			$group_enrolled = (int) $row['enrolled'];

			$template->assign_block_vars('group_stats', [
				'NAME'		=> $group_helper->get_name($row['group_name']),
				'MEMBERS'	=> $members,
				'ENROLLED'	=> $group_enrolled,
				'PERCENT'	=> $members > 0 ? round(100 * $group_enrolled / $members) : 0,
			]);
		}
		$db->sql_freeresult($result);

		$template->assign_vars([
			'S_AURORA_TFA_MODE'		=> 'stats',
			'AURORA_TFA_ENROLLED'	=> $enrolled,
			'AURORA_TFA_TOTAL'		=> (int) $config['num_users'],
		]);
	}

	/**
	 * Audit screen: recent TFA events from the phpBB log.
	 *
	 * @param \Symfony\Component\DependencyInjection\ContainerInterface $container Service container.
	 * @param \phpbb\template\template                                  $template  Template engine.
	 *
	 * @return void
	 */
	protected function audit($container, $template)
	{
		/** @var \phpbb\db\driver\driver_interface $db */
		$db = $container->get('dbal.conn');
		/** @var \phpbb\user $user */
		$user = $container->get('user');
		/** @var \phpbb\language\language $language */
		$language = $container->get('language');

		$sql = 'SELECT l.log_operation, l.log_time, l.log_ip, l.reportee_id, u.username
			FROM ' . LOG_TABLE . ' l
			LEFT JOIN ' . USERS_TABLE . " u ON u.user_id = l.reportee_id
			WHERE l.log_operation " . $db->sql_like_expression('LOG_AURORA_TFA' . $db->get_any_char()) . '
			ORDER BY l.log_time DESC';
		$result = $db->sql_query_limit($sql, 100);

		while ($row = $db->sql_fetchrow($result))
		{
			$template->assign_block_vars('audit', [
				'OPERATION'	=> $language->is_set($row['log_operation']) ? strip_tags($language->lang($row['log_operation'])) : $row['log_operation'],
				'USERNAME'	=> $row['username'] ?: '-',
				'IP'		=> $row['log_ip'],
				'TIME'		=> $user->format_date($row['log_time']),
			]);
		}
		$db->sql_freeresult($result);

		$template->assign_vars([
			'S_AURORA_TFA_MODE'	=> 'audit',
		]);
	}
}
