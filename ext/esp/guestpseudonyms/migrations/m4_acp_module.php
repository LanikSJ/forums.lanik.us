<?php
/**
 *
 * Guest pseudonyms. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2026 Emma Laurijssens van Engelenhoven
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace esp\guestpseudonyms\migrations;

/**
 * Story #14 — the ACP settings module and its configuration.
 */
class m4_acp_module extends \phpbb\db\migration\migration
{
	public function effectively_installed()
	{
		return isset($this->config['guestpseudonyms_enable']);
	}

	public static function depends_on()
	{
		return ['\esp\guestpseudonyms\migrations\m3_ucp_module'];
	}

	public function update_data()
	{
		return [
			['config.add', ['guestpseudonyms_enable', 1]],
			['config.add', ['guestpseudonyms_apply_to_bots', 1]],
			['config.add', ['guestpseudonyms_optout_reminder_months', 6]],
			['config.add', ['guestpseudonyms_optout_autorevert', 0]],
			// A category group under Extensions, then the module inside it — this
			// is what makes it show up in the ACP Extensions menu.
			['module.add', ['acp', 'ACP_CAT_DOT_MODS', 'ACP_GUESTPRIVACY_TITLE']],
			['module.add', [
				'acp',
				'ACP_GUESTPRIVACY_TITLE',
				[
					'module_basename' => '\esp\guestpseudonyms\acp\main_module',
					'modes'           => ['settings'],
				],
			]],
		];
	}

	public function revert_data()
	{
		return [
			['config.remove', ['guestpseudonyms_enable']],
			['config.remove', ['guestpseudonyms_apply_to_bots']],
			['config.remove', ['guestpseudonyms_optout_reminder_months']],
			['config.remove', ['guestpseudonyms_optout_autorevert']],
			['module.remove', [
				'acp',
				'ACP_GUESTPRIVACY_TITLE',
				[
					'module_basename' => '\esp\guestpseudonyms\acp\main_module',
				],
			]],
			['module.remove', ['acp', 'ACP_CAT_DOT_MODS', 'ACP_GUESTPRIVACY_TITLE']],
		];
	}
}
