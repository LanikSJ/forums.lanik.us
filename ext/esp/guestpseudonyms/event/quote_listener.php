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
 * Story D3 — keep real names out of the reply box when a guest quotes a post.
 *
 * The render hook (content_listener) rewrites how stored posts are *displayed*,
 * but the Quote button prefills the reply box with the post's raw BBCode source,
 * which bypasses rendering. On a forum that lets guests reply, a guest (or
 * crawler) could hit posting.php?mode=quote and read real names from that
 * prefill — both the quoted member's name and any names nested in the body.
 *
 * Every name shown to a guest is replaced by that user's pseudonym — the same
 * substitution they see in rendered posts — so the quoted body reads exactly as
 * the guest sees it on screen. Three hooks, guests only:
 *   - core.posting_modify_quote_attributes: set the top-level quote author to the
 *     quoted user's display name via get_username_string() (which routes through
 *     our username hook, so it is the pseudonym for guests). This mirrors what
 *     the drag-to-quote button already does (POSTER_QUOTE) and avoids phpBB's
 *     raw quote_username, which would otherwise leak the real name.
 *   - core.posting_modify_template_vars: rewrite the assembled prefill body —
 *     map every nested [quote=…] author and mention to the user's pseudonym.
 *     Runs before the mention extension's own quote handler (higher priority) so
 *     mentions are still [smention u=…] and can be mapped by user id.
 *   - core.topic_review_modify_row: the topic-review panel below the reply box
 *     stashes each post's raw source in a hidden div (for drag-to-quote). That
 *     bypasses rendering too, so rewrite DECODED_MESSAGE the same way.
 *
 * Stored data is never touched; a member quoting gets the normal, real-name
 * quote. If a guest submits, the quote is pseudonymised for everyone.
 */
class quote_listener implements EventSubscriberInterface
{
	/** @var viewer */
	protected $viewer;

	/** @var pseudonym_manager */
	protected $manager;

	/**
	 * @param viewer            $viewer
	 * @param pseudonym_manager $manager
	 */
	public function __construct(viewer $viewer, pseudonym_manager $manager)
	{
		$this->viewer = $viewer;
		$this->manager = $manager;
	}

	/**
	 * @return array
	 */
	public static function getSubscribedEvents()
	{
		return [
			'core.posting_modify_quote_attributes' => 'disguise_quote_author',
			// Positive priority so this runs ahead of listeners registered at
			// the default priority 0. Mention tags in the prefill are mapped by
			// user id, and another extension may well rewrite or strip them;
			// going first means we read them while they are still [smention u=…].
			'core.posting_modify_template_vars' => ['sanitise_quote_body', 5],
			'core.topic_review_modify_row' => 'sanitise_review_row',
			'core.posting_modify_message_text' => 'strip_unknown_quote_author',
		];
	}

	/**
	 * Keep a disguised viewer from using a quote author as a name lookup.
	 *
	 * On a board where guests may post, a guest could type [quote="alice"],
	 * press Preview, and read Alice's pseudonym back out of the rendered quote
	 * — the same real-name-to-pseudonym oracle as searching by author, just
	 * spelled differently.
	 *
	 * The fix is applied to the text the viewer typed, before it is parsed, and
	 * only for disguised viewers. A name they could legitimately know — a
	 * pseudonym, or the real name of a member who opted out — is left alone, so
	 * quoting by the name actually on screen still attributes properly.
	 * Anything else loses its author and renders as a plain quote.
	 *
	 * Stored posts are untouched: the render hook still maps the real names in
	 * existing content to pseudonyms, which is the whole point of the
	 * extension. Only what this viewer is typing right now is affected.
	 *
	 * @param \phpbb\event\data $event
	 */
	public function strip_unknown_quote_author($event)
	{
		if (!$this->viewer->is_disguised())
		{
			return;
		}

		$message_parser = $event['message_parser'];

		if (empty($message_parser->message) || strpos($message_parser->message, '[quote=') === false)
		{
			return;
		}

		$manager = $this->manager;

		$message_parser->message = preg_replace_callback(
			'#\[quote=([^\]]*)\]#ui',
			function ($m) use ($manager) {
				$author = $m[1];
				if (preg_match('#^(.*?)\s+[a-z_]+=.*$#is', $author, $parts))
				{
					$author = $parts[1];
				}

				$name = trim(html_entity_decode($author, ENT_QUOTES, 'UTF-8'), " \"");

				// A name this viewer can see resolves, and the quote is kept
				// exactly as written, attributes and all. Anything else — a
				// real username above all — becomes a plain [quote]: dropping
				// the attributes too, since a quote tag carrying user_id but
				// no author is not something phpBB parses.
				return $manager->get_user_id_by_display_name($name) > 0 ? $m[0] : '[quote]';
			},
			$message_parser->message);

		$event['message_parser'] = $message_parser;
	}

	/**
	 * Show the quoted user's pseudonym (not their real name) as the top-level
	 * quote author, for disguised viewers.
	 *
	 * @param \phpbb\event\data $event
	 */
	public function disguise_quote_author($event)
	{
		if (!$this->viewer->is_disguised())
		{
			return;
		}

		$post_data = $event['post_data'];
		$attributes = $event['quote_attributes'];
		// get_username_string() routes through core.modify_username_string, so for
		// a guest viewer it returns the poster's pseudonym; user_id is preserved.
		$attributes['author'] = get_username_string(
			'username',
			(int) $post_data['poster_id'],
			isset($post_data['username']) ? (string) $post_data['username'] : '',
			'',
			isset($post_data['post_username']) ? (string) $post_data['post_username'] : ''
		);
		$event['quote_attributes'] = $attributes;
	}

	/**
	 * Pseudonymise nested quote authors and mentions in the prefilled reply
	 * body, for disguised viewers quoting a post.
	 *
	 * @param \phpbb\event\data $event
	 */
	public function sanitise_quote_body($event)
	{
		if ($event['submit'] || $event['preview'] || $event['refresh']
			|| $event['mode'] !== 'quote'
			|| !isset($event['page_data']['MESSAGE'])
			|| !$this->viewer->is_disguised())
		{
			return;
		}

		$page_data = $event['page_data'];
		$page_data['MESSAGE'] = $this->disguise_source($page_data['MESSAGE']);
		$event['page_data'] = $page_data;
	}

	/**
	 * Sanitise the raw post source the topic-review panel hides for drag-quote.
	 *
	 * @param \phpbb\event\data $event
	 */
	public function sanitise_review_row($event)
	{
		if (!$this->viewer->is_disguised())
		{
			return;
		}

		$post_row = $event['post_row'];
		if (empty($post_row['DECODED_MESSAGE']))
		{
			return;
		}

		$post_row['DECODED_MESSAGE'] = $this->disguise_source($post_row['DECODED_MESSAGE']);
		$event['post_row'] = $post_row;
	}

	/**
	 * Pseudonymise quote authors and mentions in BBCode source: replace each
	 * real name with the referenced user's pseudonym, keeping the markup intact.
	 *
	 * @param string $message
	 * @return string
	 */
	protected function disguise_source($message)
	{
		$manager = $this->manager;

		// User mentions carry a user id -> map straight to that user's pseudonym.
		$message = preg_replace_callback(
			'#\[smention u=([0-9]+)\](.*?)\[/smention\]#uis',
			function ($m) use ($manager) {
				return '@' . $manager->get_pseudonym((int) $m[1]);
			},
			$message);

		// Name-only mentions -> pseudonym if the name is a real member, else keep.
		$message = preg_replace_callback(
			'#\[mention\](.*?)\[/mention\]#uis',
			function ($m) use ($manager) {
				$alias = $manager->get_pseudonym_by_name($m[1]);
				return '@' . ($alias !== null ? $alias : $m[1]);
			},
			$message);

		// Quote authors -> pseudonym. Quotes made with the Quote button carry a
		// user_id (map by id); typed [quote=Name] quotes are matched by name.
		// A name that is not a current member (or a pseudonym) is left untouched.
		return preg_replace_callback(
			'#\[quote=([^\]]*)\]#ui',
			function ($m) use ($manager) {
				$named = '';
				$author = $m[1];
				if (preg_match('#^(.*?)(\s+[a-z_]+=.*)$#is', $m[1], $parts))
				{
					$author = $parts[1];
					$named = $parts[2];
				}

				if (preg_match('#\buser_id=([0-9]+)#', $named, $u) && (int) $u[1] > 0 && (int) $u[1] !== ANONYMOUS)
				{
					return '[quote=' . $manager->get_pseudonym((int) $u[1]) . $named . ']';
				}

				// The prefill and the topic-review source are HTML escaped by
				// the time this runs, so a typed [quote="Name"] arrives as
				// [quote=&quot;Name&quot;]. Decode before trimming, or the
				// lookup is made with the entities attached, finds nobody and
				// leaves the real name in place.
				$author = html_entity_decode($author, ENT_QUOTES, 'UTF-8');

				$alias = $manager->get_pseudonym_by_name(trim($author, " \""));
				return ($alias !== null) ? '[quote=' . $alias . $named . ']' : $m[0];
			},
			$message);
	}
}
