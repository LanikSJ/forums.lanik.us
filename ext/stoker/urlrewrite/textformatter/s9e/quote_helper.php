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

namespace stoker\urlrewrite\textformatter\s9e;

use phpbb\config\config;
use phpbb\textformatter\s9e\quote_helper as core_quote_helper;
use phpbb\user;
use stoker\urlrewrite\helper\url_helper;

class quote_helper extends core_quote_helper
{
	/** @var config */
	protected $config;

	/** @var core_quote_helper */
	protected $inner;

	/** @var url_helper */
	protected $url_helper;

	/** @var array<int,string> */
	protected $cache = [];

	/**
	 * parent::__construct() is deliberately not called. All it does is build the
	 * post/msg/profile URL templates with three append_sid() calls, and nothing
	 * here reads them: inject_metadata() is overridden and delegates to $inner,
	 * which holds its own. $user is still assigned because it is the parent's own
	 * property, and this class has to remain a real quote_helper - the core
	 * type-hints it in renderer::configure_quote_helper().
	 *
	 * $root_path and $php_ext are gone from the signature with them; the matching
	 * arguments are removed from services.yml.
	 *
	 * @param user				$user
	 * @param config			$config
	 * @param url_helper		$url_helper
	 * @param core_quote_helper	$inner
	 */
	public function __construct(user $user, config $config, url_helper $url_helper, core_quote_helper $inner)
	{
		$this->user = $user;
		$this->config = $config;
		$this->url_helper = $url_helper;
		$this->inner = $inner;
	}

	/**
	 * Delegate to the decorated service rather than to parent::, so a second
	 * extension decorating text_formatter.s9e.quote_helper stays in the chain
	 * instead of being bypassed when this decorator ends up on the outside.
	 *
	 * @param string $xml
	 * @return string
	 */
	public function inject_metadata($xml)
	{
		$xml = $this->inner->inject_metadata($xml);

		if (empty($this->config['stoker_urlrewrite_enable']))
		{
			return $xml;
		}

		// Resolve every quoted post in one query instead of one per QUOTE tag.
		$post_ids = \s9e\TextFormatter\Utils::getAttributeValues($xml, 'QUOTE', 'post_id');

		if (!empty($post_ids))
		{
			$this->url_helper->prime_post_topics($post_ids);
		}

		return \s9e\TextFormatter\Utils::replaceAttributes(
			$xml,
			'QUOTE',
			function ($attributes)
			{
				if (isset($attributes['post_id']))
				{
					$url = $this->get_friendly_post_url((int) $attributes['post_id']);

					if ($url !== '')
					{
						$attributes['post_url'] = $url;
					}
				}

				return $attributes;
			}
		);
	}

	/**
	 * Full clickable URL for a quoted post, including the #p<id> anchor.
	 *
	 * The post_id on a QUOTE tag is author-supplied and unchecked, so the slug is
	 * built from the topic title only when the viewer may see that title: f_read
	 * on the owning forum, the forum password entered if it has one, and the
	 * topic visible under phpBB's own content visibility rules. Otherwise the
	 * id-only form is used, which routes to exactly the same place without
	 * disclosing the title. phpBB's own quote_helper does a plain string
	 * substitution and never reads the database, so this check exists only
	 * because resolving the title is something this extension added.
	 *
	 * @param int $post_id
	 * @return string
	 */
	protected function get_friendly_post_url(int $post_id): string
	{
		if (isset($this->cache[$post_id]))
		{
			return $this->cache[$post_id];
		}

		$row = $this->url_helper->post_topic($post_id);

		$url = '';

		if ($row !== false)
		{
			$path = $this->url_helper->topic_readable($row)
				? $this->url_helper->post_path($post_id, (int) $row['topic_id'], (string) $row['topic_title'])
				: $this->url_helper->post_path($post_id);

			$url = generate_board_url() . '/' . $path . '#p' . $post_id;
		}

		return $this->cache[$post_id] = $url;
	}
}
