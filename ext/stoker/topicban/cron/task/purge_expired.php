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

namespace stoker\topicban\cron\task;

use phpbb\config\config;
use stoker\topicban\service\topicban_manager;

/**
 * Daily purge of expired topic bans
 */
class purge_expired extends \phpbb\cron\task\base
{
	/** @var int Seconds between runs */
	protected const INTERVAL = 86400;

	/** @var config */
	protected $config;

	/** @var topicban_manager */
	protected $manager;

	/**
	 * @param config           $config
	 * @param topicban_manager $manager
	 */
	public function __construct(config $config, topicban_manager $manager)
	{
		$this->config = $config;
		$this->manager = $manager;
	}

	/**
	 * @return void
	 */
	public function run()
	{
		$this->config->set('stoker_topicban_last_purge', time(), false);
		$this->manager->purge_expired();
	}

	/**
	 * @return bool
	 */
	public function should_run()
	{
		return (int) $this->config['stoker_topicban_last_purge'] < time() - self::INTERVAL;
	}
}
