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

namespace stoker\urlrewrite\helper;

use phpbb\auth\auth;
use phpbb\config\config;
use phpbb\content_visibility;
use phpbb\db\driver\driver_interface;
use phpbb\user;

class url_helper
{
	/** Transliteration methods */
	public const METHOD_OFF = 0;
	public const METHOD_STATIC = 1;
	public const METHOD_INTL = 2;

	/** @var auth */
	protected $auth;

	/** @var config */
	protected $config;

	/** @var content_visibility */
	protected $content_visibility;

	/** @var driver_interface */
	protected $db;

	/** @var user */
	protected $user;

	/** @var array<int,array|false> */
	protected $post_topics = [];

	/** @var array<int,bool> */
	protected $forum_readable = [];

	/** @var array<int,bool>|null */
	protected $password_forums = null;

	/** @var array<int,bool>|null */
	protected $unlocked_forums = null;

	/** @var \Transliterator|null|false */
	protected $transliterator = false;

	/**
	 * Per-request slug cache, keyed by the source text.
	 *
	 * Both maps are pure for the life of a request: slug() depends only on its
	 * argument and the transliteration config, and censor_text() only on the
	 * censor word list. A single topic page emits the same title through
	 * topic_path()/post_path() thirty to sixty times, once per generated link.
	 *
	 * @var array<string,string>
	 */
	protected $slug_cache = [];

	/** @var array<string,string> */
	protected $censor_cache = [];

	/**
	 * Multi-character pre-map applied for both Static and intl methods.
	 *
	 * Lowercase keys only - slug() folds case before it reaches this map.
	 *
	 * @var array<string,string>
	 */
	protected static $premap = [
		'æ' => 'ae', 'ø' => 'oe', 'å' => 'aa', 'œ' => 'oe', 'ß' => 'ss',
		'ð' => 'd', 'þ' => 'th',
	];

	/**
	 * Single-character accent map for the Static method.
	 *
	 * Lowercase keys only - slug() folds case before it reaches this map.
	 *
	 * @var array<string,string>
	 */
	protected static $accents = [
		'à' => 'a', 'á' => 'a', 'â' => 'a', 'ã' => 'a', 'ä' => 'a', 'ā' => 'a', 'ă' => 'a', 'ą' => 'a',
		'ç' => 'c', 'ć' => 'c', 'č' => 'c', 'ĉ' => 'c', 'ċ' => 'c',
		'è' => 'e', 'é' => 'e', 'ê' => 'e', 'ë' => 'e', 'ē' => 'e', 'ĕ' => 'e', 'ė' => 'e', 'ę' => 'e', 'ě' => 'e',
		'ì' => 'i', 'í' => 'i', 'î' => 'i', 'ï' => 'i', 'ī' => 'i', 'ĭ' => 'i', 'į' => 'i', 'ı' => 'i',
		'ñ' => 'n', 'ń' => 'n', 'ň' => 'n', 'ņ' => 'n',
		'ò' => 'o', 'ó' => 'o', 'ô' => 'o', 'õ' => 'o', 'ö' => 'o', 'ō' => 'o', 'ŏ' => 'o', 'ő' => 'o',
		'ù' => 'u', 'ú' => 'u', 'û' => 'u', 'ü' => 'u', 'ū' => 'u', 'ŭ' => 'u', 'ů' => 'u', 'ű' => 'u', 'ų' => 'u',
		'ý' => 'y', 'ÿ' => 'y', 'ŷ' => 'y',
		'ł' => 'l', 'ĺ' => 'l', 'ľ' => 'l', 'ļ' => 'l',
		'ś' => 's', 'š' => 's', 'ş' => 's', 'ŝ' => 's',
		'ź' => 'z', 'ž' => 'z', 'ż' => 'z',
		'ť' => 't', 'ţ' => 't',
		'ŕ' => 'r', 'ř' => 'r',
		'ġ' => 'g', 'ğ' => 'g', 'ĝ' => 'g',
		'ĥ' => 'h', 'ŵ' => 'w',
	];

	/**
	 * @param auth					$auth
	 * @param config				$config
	 * @param content_visibility	$content_visibility
	 * @param driver_interface		$db
	 * @param user					$user
	 */
	public function __construct(auth $auth, config $config, content_visibility $content_visibility, driver_interface $db, user $user)
	{
		$this->auth = $auth;
		$this->config = $config;
		$this->content_visibility = $content_visibility;
		$this->db = $db;
		$this->user = $user;
	}

	/**
	 * May this visitor be shown content from this forum at all?
	 *
	 * f_read on its own is not phpBB's gate: on a password protected forum
	 * f_read is granted to everyone and the password is what actually holds the
	 * content back, so a title slugged behind an f_read only check leaks through
	 * the Location header and the canonical tag before the password is entered.
	 * The unlock is the row login_forum_box() looks for.
	 *
	 * @param int $forum_id
	 * @return bool
	 */
	public function forum_readable(int $forum_id): bool
	{
		if (isset($this->forum_readable[$forum_id]))
		{
			return $this->forum_readable[$forum_id];
		}

		// forum_id 0 is a global announcement - it has no single forum to test,
		// so fall back to the board-wide check phpBB uses. It carries no
		// password either.
		if ($forum_id <= 0)
		{
			return $this->forum_readable[$forum_id] = (bool) $this->auth->acl_getf_global('f_read');
		}

		$readable = (bool) $this->auth->acl_get('f_read', $forum_id) && !$this->password_locked($forum_id);

		return $this->forum_readable[$forum_id] = $readable;
	}

	/**
	 * May this visitor be shown this topic's title?
	 *
	 * The row must carry the columns phpbb\content_visibility reads. Core's own
	 * service is used rather than a comparison against ITEM_APPROVED, so
	 * display_unapproved_posts and the core.phpbb_content_visibility_is_visible
	 * event behave exactly as they do in viewtopic.php.
	 *
	 * @param array $row
	 * @return bool
	 */
	public function topic_readable(array $row): bool
	{
		$forum_id = isset($row['forum_id']) ? (int) $row['forum_id'] : 0;

		if (!$this->forum_readable($forum_id))
		{
			return false;
		}

		// A row without the visibility columns cannot be judged, so it counts as
		// withheld rather than being assumed harmless.
		if (!isset($row['topic_visibility'], $row['topic_poster']))
		{
			return false;
		}

		return (bool) $this->content_visibility->is_visible('topic', $forum_id, $row);
	}

	/**
	 * Is this forum password protected and still locked for this session?
	 *
	 * Both lookups are lazy: a board with no password protected forum never runs
	 * the second one, and never pays for the first beyond one cache read.
	 *
	 * @param int $forum_id
	 * @return bool
	 */
	protected function password_locked(int $forum_id): bool
	{
		if ($this->password_forums === null)
		{
			$this->password_forums = [];

			// Ids only - the hash is never read, so it never reaches the SQL
			// cache. acp_forums.php purges that cache on every forum change
			// ($cache->destroy('sql', FORUMS_TABLE) on add, edit, delete, move
			// and sync), so a forum that gains or loses a password is picked up
			// on the next request.
			$sql = 'SELECT forum_id
				FROM ' . FORUMS_TABLE . "
				WHERE forum_password <> ''";
			$result = $this->db->sql_query($sql, 7200);

			while ($row = $this->db->sql_fetchrow($result))
			{
				$this->password_forums[(int) $row['forum_id']] = true;
			}

			$this->db->sql_freeresult($result);
		}

		if (!isset($this->password_forums[$forum_id]))
		{
			return false;
		}

		if ($this->unlocked_forums === null)
		{
			$this->unlocked_forums = [];

			// The row login_forum_box() returns true on. Never cached: it is
			// per session and disappears with it.
			$sql = 'SELECT forum_id
				FROM ' . FORUMS_ACCESS_TABLE . '
				WHERE user_id = ' . (int) $this->user->data['user_id'] . "
					AND session_id = '" . $this->db->sql_escape((string) $this->user->session_id) . "'";
			$result = $this->db->sql_query($sql);

			while ($row = $this->db->sql_fetchrow($result))
			{
				$this->unlocked_forums[(int) $row['forum_id']] = true;
			}

			$this->db->sql_freeresult($result);
		}

		return !isset($this->unlocked_forums[$forum_id]);
	}

	/**
	 * Resolve the active transliteration method, failing safe when intl is absent.
	 *
	 * @return int
	 */
	protected function get_method(): int
	{
		$method = (int) ($this->config['stoker_urlrewrite_translit'] ?? self::METHOD_STATIC);

		if ($method === self::METHOD_INTL && !extension_loaded('intl'))
		{
			$method = self::METHOD_STATIC;
		}

		return $method;
	}

	/**
	 * Build a URL-safe slug from arbitrary text.
	 *
	 * @param string $text
	 * @return string
	 */
	public function slug(string $text): string
	{
		if ($text === '')
		{
			return '';
		}

		if (isset($this->slug_cache[$text]))
		{
			return $this->slug_cache[$text];
		}

		return $this->slug_cache[$text] = $this->build_slug($text);
	}

	/**
	 * The slug build itself, called once per distinct string per request.
	 *
	 * @param string $text
	 * @return string
	 */
	protected function build_slug(string $text): string
	{
		$text = html_entity_decode($text, ENT_QUOTES, 'UTF-8');

		// Lowercase here, not after the maps: strtolower() is ASCII-only, so any
		// uppercase non-ASCII the maps do not cover would reach the byte-wise
		// [^a-z0-9] strip and be dropped whole ("Řeka" -> "eka", while "řeka"
		// -> "reka"). Folding first means every character meets a lowercase key.
		$text = mb_strtolower($text, 'UTF-8');

		$method = $this->get_method();

		if ($method === self::METHOD_OFF)
		{
			// \p{M} is kept. Case folding decomposes a few letters - mb_strtolower()
			// turns 'I' with a dot into 'i' plus U+0307 - and several scripts write
			// their vowels as combining marks, so treating a mark as punctuation cut
			// a word in two ('Istanbul' became 'i-stanbul').
			$text = preg_replace('/[^\p{L}\p{N}\p{M}]+/u', '-', $text);

			// Percent-encode: what survives here may be multi-byte, which is not
			// valid unencoded in a URL path or in a Location header. Unreserved
			// ASCII and the '-' separator pass through untouched.
			return rawurlencode(trim((string) $text, '-'));
		}

		$text = strtr($text, self::$premap);

		if ($method === self::METHOD_INTL)
		{
			$text = $this->intl_transliterate($text);
		}
		else
		{
			$text = strtr($text, self::$accents);
		}

		// The same decomposition as above, but these two methods produce an ASCII
		// slug, so the marks are dropped rather than kept. After the maps, so a
		// precomposed character still meets its own key first. Left alone when the
		// subject is not valid UTF-8; that costs the mark handling, not the slug.
		$stripped = preg_replace('/\p{M}+/u', '', $text);

		if ($stripped !== null)
		{
			$text = $stripped;
		}

		// Transliteration output is ASCII, but not necessarily lowercase.
		$text = strtolower($text);
		$text = preg_replace('/[^a-z0-9]+/', '-', $text);

		return trim((string) $text, '-');
	}

	/**
	 * Romanize remaining non-Latin characters via the intl extension.
	 *
	 * @param string $text
	 * @return string
	 */
	protected function intl_transliterate(string $text): string
	{
		if ($this->transliterator === false)
		{
			$this->transliterator = \Transliterator::create('Any-Latin; Latin-ASCII');
		}

		if ($this->transliterator !== null)
		{
			$result = $this->transliterator->transliterate($text);

			if ($result !== false)
			{
				return $result;
			}
		}

		return strtr($text, self::$accents);
	}

	/**
	 * Word-censor a topic title on its way to a slug.
	 *
	 * phpBB censors every topic title it displays - viewtopic.php and
	 * viewforum.php before assigning it, the notification types before putting
	 * it in an email - so without this the censored word reappears in the
	 * address bar, in the canonical tag and in the Location header.
	 *
	 * Applied at this single point rather than at each caller: titles reach the
	 * extension from a dozen different events, some already censored by the
	 * script that fired them and some not, and the two divergent forms produced
	 * different slugs for one topic on one page.
	 *
	 * Forum names are deliberately left alone: phpBB does not censor them
	 * either, and reducing an admin-chosen name to the replacement string would
	 * strip the whole forum slug rather than one word from it.
	 *
	 * @param string $topic_title
	 * @return string
	 */
	protected function censor_title(string $topic_title): string
	{
		if ($topic_title === '')
		{
			return '';
		}

		if (isset($this->censor_cache[$topic_title]))
		{
			return $this->censor_cache[$topic_title];
		}

		return $this->censor_cache[$topic_title] = (string) censor_text($topic_title);
	}

	/**
	 * Friendly route path for a forum (no anchor).
	 *
	 * @param int		$forum_id
	 * @param string	$forum_name
	 * @return string
	 */
	public function forum_path(int $forum_id, string $forum_name): string
	{
		$slug = $this->slug($forum_name);

		return (($slug !== '') ? $slug : 'forum') . '-f' . $forum_id;
	}

	/**
	 * Friendly route path for a topic (no anchor).
	 *
	 * @param int		$topic_id
	 * @param string	$topic_title
	 * @return string
	 */
	public function topic_path(int $topic_id, string $topic_title): string
	{
		$slug = $this->slug($this->censor_title($topic_title));

		return (($slug !== '') ? $slug : 'topic') . '-t' . $topic_id;
	}

	/**
	 * Friendly route path for a post (no anchor).
	 *
	 * The #p<post_id> fragment is intentionally NOT included here: phpBB appends
	 * it itself after append_sid(), and full-URL builders add it explicitly.
	 *
	 * @param int		$post_id
	 * @param int		$topic_id
	 * @param string	$topic_title
	 * @return string
	 */
	public function post_path(int $post_id, int $topic_id = 0, string $topic_title = ''): string
	{
		if ($topic_id > 0)
		{
			$slug = $this->slug($this->censor_title($topic_title));

			return (($slug !== '') ? $slug : 'topic') . '-t' . $topic_id . '-p' . $post_id;
		}

		return 'post-p' . $post_id;
	}

	/**
	 * Resolve several posts in one query, memoising the results.
	 *
	 * Nothing is returned: this only primes the cache post_topic() reads, so a
	 * caller holding a batch of post ids avoids one query per id.
	 *
	 * @param array $post_ids
	 * @return void
	 */
	public function prime_post_topics(array $post_ids): void
	{
		$missing = [];

		foreach ($post_ids as $post_id)
		{
			$post_id = (int) $post_id;

			if ($post_id > 0 && !isset($this->post_topics[$post_id]))
			{
				$missing[$post_id] = $post_id;
			}
		}

		// A single id is left to post_topic(): batching it would cost the same query.
		if (count($missing) < 2)
		{
			return;
		}

		$sql = 'SELECT p.post_id, t.topic_id, t.topic_title, t.topic_visibility, t.topic_poster, p.forum_id
			FROM ' . POSTS_TABLE . ' p, ' . TOPICS_TABLE . ' t
			WHERE ' . $this->db->sql_in_set('p.post_id', $missing) . '
				AND t.topic_id = p.topic_id';
		$result = $this->db->sql_query($sql);

		while ($row = $this->db->sql_fetchrow($result))
		{
			$post_id = (int) $row['post_id'];
			unset($missing[$post_id], $row['post_id']);
			$this->post_topics[$post_id] = $row;
		}

		$this->db->sql_freeresult($result);

		// Memoise the misses too, so post_topic() does not re-query them one by one.
		foreach ($missing as $post_id)
		{
			$this->post_topics[$post_id] = false;
		}
	}

	/**
	 * Resolve the topic a post belongs to (memoised per request).
	 *
	 * The row carries the columns topic_readable() needs; callers decide whether
	 * the title may be used.
	 *
	 * @param int $post_id
	 * @return array|false
	 */
	public function post_topic(int $post_id)
	{
		if (isset($this->post_topics[$post_id]))
		{
			return $this->post_topics[$post_id];
		}

		$sql = 'SELECT t.topic_id, t.topic_title, t.topic_visibility, t.topic_poster, p.forum_id
			FROM ' . POSTS_TABLE . ' p, ' . TOPICS_TABLE . ' t
			WHERE p.post_id = ' . (int) $post_id . '
				AND t.topic_id = p.topic_id';
		$result = $this->db->sql_query($sql);
		$row = $this->db->sql_fetchrow($result);
		$this->db->sql_freeresult($result);

		return $this->post_topics[$post_id] = ($row !== false) ? $row : false;
	}
}
