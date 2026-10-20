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

namespace stoker\urlrewrite\migrations;

class install extends \phpbb\db\migration\migration
{
	/**
	 * @return bool
	 */
	public function effectively_installed()
	{
		return isset($this->config['stoker_urlrewrite_enable']);
	}

	/**
	 * @return array
	 */
	public static function depends_on()
	{
		return ['\phpbb\db\migration\data\v330\v330'];
	}

	/**
	 * @return array
	 */
	public function update_data()
	{
		return [
			['config.add', ['stoker_urlrewrite_enable', 0]],
			['config.add', ['stoker_urlrewrite_redirect_enable', 0]],
			['config.add', ['stoker_urlrewrite_translit', 1]],

			['module.add', ['acp', 'ACP_CAT_DOT_MODS', 'ACP_URLREWRITE_TITLE']],
			['module.add', ['acp', 'ACP_URLREWRITE_TITLE', [
				'module_basename'	=> '\stoker\urlrewrite\acp\main_module',
				'modes'				=> ['settings', 'server'],
			]]],
		];
	}
}
