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

namespace stoker\topicban;

/**
 * Extension base class
 */
class ext extends \phpbb\extension\base
{
	public const NOTIFICATION_TYPE = 'stoker.topicban.notification.type.topic_ban';

	/**
	 * @return bool
	 */
	public function is_enableable()
	{
		$config = $this->container->get('config');

		return phpbb_version_compare($config['version'], '3.3.4', '>=')
			&& phpbb_version_compare($config['version'], '4.0.0-dev', '<')
			&& version_compare(PHP_VERSION, '7.4.0', '>=');
	}

	/**
	 * @param mixed $old_state
	 * @return mixed
	 */
	public function enable_step($old_state)
	{
		if ($old_state === false)
		{
			$this->container->get('notification_manager')->enable_notifications(self::NOTIFICATION_TYPE);

			return 'notification';
		}

		return parent::enable_step($old_state);
	}

	/**
	 * @param mixed $old_state
	 * @return mixed
	 */
	public function disable_step($old_state)
	{
		if ($old_state === false)
		{
			$this->container->get('notification_manager')->disable_notifications(self::NOTIFICATION_TYPE);

			return 'notification';
		}

		return parent::disable_step($old_state);
	}

	/**
	 * @param mixed $old_state
	 * @return mixed
	 */
	public function purge_step($old_state)
	{
		if ($old_state === false)
		{
			$this->container->get('notification_manager')->purge_notifications(self::NOTIFICATION_TYPE);

			return 'notification';
		}

		return parent::purge_step($old_state);
	}
}
