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

namespace stoker\urlrewrite;

/**
 * Extension enable check
 */
class ext extends \phpbb\extension\base
{
	/**
	 * Enable extension if minimum requirements are met.
	 *
	 * - Requires phpBB >= 3.3.15
	 * - Requires PHP  >= 7.4.0
	 *
	 * @return array|bool True if enableable, otherwise an array of error messages
	 */
	public function is_enableable()
	{
		$config = $this->container->get('config');

		$required_phpbb = '3.3.15';
		$required_php = '7.4.0';

		$errors = [];

		if (!phpbb_version_compare($config['version'], $required_phpbb, '>='))
		{
			$errors[] = ['URLREWRITE_REQUIRE_PHPBB', $required_phpbb, $config['version']];
		}

		if (!version_compare(PHP_VERSION, $required_php, '>='))
		{
			$errors[] = ['URLREWRITE_REQUIRE_PHP', $required_php, PHP_VERSION];
		}

		if (empty($errors))
		{
			return true;
		}

		$language = $this->container->get('language');
		$language->add_lang('acp_urlrewrite', 'stoker/urlrewrite');

		return array_map(
			function (array $error) use ($language)
			{
				return $language->lang(...$error);
			},
			$errors
		);
	}
}
