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

namespace stoker\emoji;

/**
 * Emoji V2 extension base
 */
class ext extends \phpbb\extension\base
{
	/**
	 * Enable extension if minimum requirements are met.
	 *
	 * - Requires phpBB >= 3.3.0
	 * - Requires PHP  >= 7.4.0
	 * - Requires any 1.0.x install to be removed first
	 *
	 * @return bool|array
	 */
	public function is_enableable()
	{
		$config = $this->container->get('config');

		$required_phpbb = '3.3.0';
		$required_php = '7.4.0';

		$errors = [];

		if (!phpbb_version_compare($config['version'], $required_phpbb, '>='))
		{
			$errors[] = ['EMOJI_REQUIRE_PHPBB', $required_phpbb, $config['version']];
		}

		if (!version_compare(PHP_VERSION, $required_php, '>='))
		{
			$errors[] = ['EMOJI_REQUIRE_PHP', $required_php, PHP_VERSION];
		}

		if (isset($config['stoker_emoji_quickreply']))
		{
			$errors[] = ['EMOJI_REQUIRE_REMOVE'];
		}

		if (empty($errors))
		{
			return true;
		}

		$language = $this->container->get('language');
		$language->add_lang('acp_emoji', 'stoker/emoji');

		return array_map(
			function (array $error) use ($language)
			{
				return $language->lang(...$error);
			},
			$errors
		);
	}
}
