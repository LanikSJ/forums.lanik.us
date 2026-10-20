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

namespace stoker\maintenance;

class ext extends \phpbb\extension\base
{
	/** @var string Cache key holding the body text, written by the ACP and read
	 *              by the maintenance page. Public: acp_controller and
	 *              maintenance_controller share this single definition rather
	 *              than each declaring their own copy of the literal. */
	public const MESSAGE_CACHE_KEY = '_stoker_maintenance_message';

	/**
	 * @return bool
	 */
	public function is_enableable()
	{
		return version_compare(PHP_VERSION, '7.4.0', '>=');
	}

	/**
	 * Lift a scheduled outage before the listener stops running.
	 *
	 * @param mixed $old_state
	 *
	 * @return mixed
	 */
	public function disable_step($old_state)
	{
		/** @var \phpbb\config\config $config */
		$config = $this->container->get('config');

		// With the extension disabled the board falls back to core, which knows
		// nothing about our schedule. An outage the schedule started would be
		// stranded behind core's message with nothing left running to lift it.
		// A board taken offline by hand is none of our business and stays
		// offline. Read before the schedule is dropped, since that clears the
		// marker saying whose outage it is. Any marker other than zero counts:
		// a disable that got as far as taking the board offline is ours whether
		// or not it got as far as saying so.
		$clear = [];

		if ((int) $config['stoker_maintenance_disabled_by_schedule'] !== 0 && (int) $config['board_disable'] !== 0)
		{
			$clear[] = 'board_disable';
		}

		$keys = [
			'stoker_maintenance_start',
			'stoker_maintenance_end',
			'stoker_maintenance_disabled_by_schedule',
		];

		foreach ($keys as $key)
		{
			if ((int) $config[$key] !== 0)
			{
				$clear[] = $key;
			}
		}

		// Every set() that is allowed to use the cache throws away the whole
		// config cache, so all but the last write skip it and one purge covers
		// the lot.
		$remaining = count($clear);

		foreach ($clear as $key)
		{
			$config->set($key, 0, --$remaining === 0);
		}

		return parent::disable_step($old_state);
	}

	/**
	 * Drop the cached body text once the stored one has gone.
	 *
	 * @param mixed $old_state
	 *
	 * @return mixed
	 */
	public function purge_step($old_state)
	{
		$state = parent::purge_step($old_state);

		// Done only on the last step: the migrations have run in reverse by
		// then, so nothing can read the row back out of the database and put
		// the cache entry back. Left behind, it would be served as the message
		// of whoever installs the extension next.
		if ($state === false)
		{
			$this->container->get('cache')->destroy(self::MESSAGE_CACHE_KEY);
		}

		return $state;
	}
}
