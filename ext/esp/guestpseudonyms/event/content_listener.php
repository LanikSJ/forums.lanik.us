<?php
/**
 *
 * Guest pseudonyms. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2026 Emma Laurijssens van Engelenhoven
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace esp\guestpseudonyms\event;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use esp\guestpseudonyms\service\pseudonym_manager;
use esp\guestpseudonyms\service\viewer;

/**
 * Stories D1 + D2 — swap the names inside quotes and mentions at render time.
 *
 * Post content is stored as s9e TextFormatter XML and rendered per request, so
 * we rewrite the parsed XML on core.text_formatter_s9e_render_before. What we
 * write depends on the viewer:
 *   - Guests / crawlers: show each referenced user's PSEUDONYM.
 *       QUOTE[@user_id] -> pseudonym (@author)                           (D1)
 *       SMENTION[@u]    -> pseudonym (visible text)                      (D1)
 *       QUOTE with only a name -> that user's pseudonym if the name matches,
 *         otherwise the author is dropped (anonymous quote)              (D2)
 *   - Logged-in members, when guestpseudonyms_resolve_names is on: show each
 *       referenced user's CURRENT username, resolved from user_id. This keeps
 *       quotes/@mentions pointing at the account, so renaming a user (e.g. to
 *       Anon20260101 on an account-deletion request) updates old quotes with no
 *       database search-and-replace. Only references that carry a user_id can be
 *       resolved; typed [quote=Name] quotes are left as written.
 * Stored data is never touched; only the copy rendered for this request.
 */
class content_listener implements EventSubscriberInterface
{
	/** @var viewer */
	protected $viewer;

	/** @var pseudonym_manager */
	protected $manager;

	/** @var \phpbb\config\config */
	protected $config;

	/**
	 * @param viewer               $viewer
	 * @param pseudonym_manager    $manager
	 * @param \phpbb\config\config $config
	 */
	public function __construct(viewer $viewer, pseudonym_manager $manager, \phpbb\config\config $config)
	{
		$this->viewer = $viewer;
		$this->manager = $manager;
		$this->config = $config;
	}

	/**
	 * @return array
	 */
	public static function getSubscribedEvents()
	{
		return [
			'core.text_formatter_s9e_render_before' => 'disguise_content',
		];
	}

	/**
	 * @param \phpbb\event\data $event
	 */
	public function disguise_content($event)
	{
		$disguised = $this->viewer->is_disguised();

		// Guests get pseudonyms; logged-in members get current names only when
		// name-resolution is enabled. Otherwise there is nothing to do.
		if (!$disguised && (empty($this->config['guestpseudonyms_enable']) || empty($this->config['guestpseudonyms_resolve_names'])))
		{
			return;
		}

		$xml = (string) $event['xml'];

		// Plain-text posts ("<t>...") carry no tags; skip the DOM work entirely.
		if (strpos($xml, '<QUOTE') === false && strpos($xml, '<SMENTION') === false)
		{
			return;
		}

		$dom = new \DOMDocument();
		$previous = libxml_use_internal_errors(true);
		$loaded = $dom->loadXML($xml);
		libxml_clear_errors();
		libxml_use_internal_errors($previous);

		if (!$loaded)
		{
			return;
		}

		$xpath = new \DOMXPath($dom);
		$changed = false;

		foreach ($xpath->query('//QUOTE') as $node)
		{
			$user_id = $node->hasAttribute('user_id') ? (int) $node->getAttribute('user_id') : 0;

			// Quotes made with the Quote button carry the quoted user's id (D1).
			if ($user_id > 0 && $user_id !== ANONYMOUS)
			{
				$name = $this->name_for($user_id, $disguised);
				if ($name !== '' && $node->getAttribute('author') !== $name)
				{
					$node->setAttribute('author', $name);
					$changed = true;
				}
				continue;
			}

			// Manual [quote=Name] quotes carry only a name (D2): for guests, use
			// that user's pseudonym if the name matches one, otherwise drop the
			// author so it renders as an anonymous quote (per the A3 policy).
			// For logged-in members there is no id to resolve, so leave it.
			if ($disguised && $node->hasAttribute('author'))
			{
				$pseudonym = $this->manager->get_pseudonym_by_name($node->getAttribute('author'));
				if ($pseudonym !== null)
				{
					$node->setAttribute('author', $pseudonym);
				}
				else
				{
					$node->removeAttribute('author');
				}
				$changed = true;
			}
		}

		// Mentions carry the mentioned user's id in @u; the display text is the
		// tag's content (the <s>/<e> children are source markers, not rendered).
		foreach ($xpath->query('//SMENTION[@u]') as $node)
		{
			$user_id = (int) $node->getAttribute('u');
			if ($user_id > 0 && $user_id !== ANONYMOUS)
			{
				$name = $this->name_for($user_id, $disguised);
				if ($name === '')
				{
					continue;
				}
				while ($node->firstChild !== null)
				{
					$node->removeChild($node->firstChild);
				}
				$node->appendChild($dom->createTextNode($name));
				$changed = true;
			}
		}

		if ($changed)
		{
			$event['xml'] = $dom->saveXML($dom->documentElement);
		}
	}

	/**
	 * The name to show for a user id: their pseudonym for a disguised (guest)
	 * viewer, or their current real username for a logged-in one. Returns '' for
	 * an unknown/deleted user, so the caller can leave the stored name in place.
	 *
	 * @param int  $user_id
	 * @param bool $disguised
	 * @return string
	 */
	protected function name_for($user_id, $disguised)
	{
		return $disguised
			? $this->manager->get_pseudonym($user_id)
			: $this->manager->get_username($user_id);
	}
}
