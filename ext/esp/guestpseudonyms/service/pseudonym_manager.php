<?php
/**
 *
 * Guest pseudonyms. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2026 Emma Laurijssens van Engelenhoven
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace esp\guestpseudonyms\service;

/**
 * Story B4 — resolves and stores per-user bogus names (the lookup table).
 *
 * The real username is never stored here, only the generated bogus identity.
 * Names are generated lazily on first request for a user and cached for the
 * lifetime of the request, so a page that shows one user many times costs a
 * single query.
 */
class pseudonym_manager
{
	/** @var \phpbb\db\driver\driver_interface */
	protected $db;

	/** @var name_generator */
	protected $generator;

	/** @var \phpbb\config\config */
	protected $config;

	/** @var string */
	protected $table;

	/** @var string */
	protected $users_table;

	/** @var array<int,array{bogus:?string,opted_out:bool,confirmed_at:int,show_signature:bool}> Per-request cache by user id */
	protected $cache = [];

	/** @var array<int,string> Per-request cache: user_id => real username */
	protected $realname_cache = [];

	/** @var array<string,int|false> Per-request cache: clean name => user_id (or false) */
	protected $name_cache = [];

	/**
	 * @param \phpbb\db\driver\driver_interface $db
	 * @param name_generator                    $generator
	 * @param \phpbb\config\config              $config
	 * @param string                            $table_prefix
	 */
	public function __construct(\phpbb\db\driver\driver_interface $db, name_generator $generator, \phpbb\config\config $config, $table_prefix)
	{
		$this->db = $db;
		$this->generator = $generator;
		$this->config = $config;
		$this->table = $table_prefix . 'pseudonyms';
		$this->users_table = $table_prefix . 'users';
	}

	/**
	 * Return the name to display for a user: their bogus name, or their REAL
	 * name if they have opted out (and opt-out is allowed). Because opt-out is
	 * resolved here, every listener honours it with no changes of its own.
	 *
	 * @param int $user_id
	 * @return string
	 */
	public function get_pseudonym($user_id)
	{
		$user_id = (int) $user_id;

		if (!isset($this->cache[$user_id]))
		{
			$this->prime([$user_id]);
		}

		if ($this->cache[$user_id]['opted_out'] && $this->opt_out_allowed())
		{
			return $this->real_username($user_id);
		}

		return $this->get_bogus($user_id);
	}

	/**
	 * Return a user's current real username (cached), or '' if the user no
	 * longer exists. Used to resolve quote/mention authors from user_id for
	 * logged-in viewers, so a rename propagates without touching stored posts.
	 *
	 * @param int $user_id
	 * @return string
	 */
	public function get_username($user_id)
	{
		return $this->real_username((int) $user_id);
	}

	/**
	 * Return a user's bogus name (creating one if needed), regardless of their
	 * opt-out state. The UCP page shows this so a member can see/regenerate it.
	 *
	 * @param int $user_id
	 * @return string
	 */
	public function get_bogus($user_id)
	{
		$user_id = (int) $user_id;

		if (!isset($this->cache[$user_id]))
		{
			$this->prime([$user_id]);
		}

		if ($this->cache[$user_id]['bogus'] === null)
		{
			return $this->create($user_id);
		}

		return $this->cache[$user_id]['bogus'];
	}

	/**
	 * Generate a fresh bogus name for a user (their choice, from the UCP). The
	 * opt-out state is preserved.
	 *
	 * @param int         $user_id
	 * @param string|null $iso     Language to generate in; board default if null.
	 * @return string
	 */
	public function regenerate($user_id, $iso = null)
	{
		$user_id = (int) $user_id;

		if (!isset($this->cache[$user_id]))
		{
			$this->prime([$user_id]);
		}

		$name = $this->next_unique_name($user_id, $iso);

		if ($this->cache[$user_id]['bogus'] === null)
		{
			$name = $this->store($user_id, $name);
		}
		else
		{
			$sql = 'UPDATE ' . $this->table . '
				SET ' . $this->db->sql_build_array('UPDATE', [
					'bogus_username'       => $name,
					'bogus_username_clean' => utf8_clean_string($name),
				]) . '
				WHERE user_id = ' . (int) $user_id;
			$this->db->sql_query($sql);
		}

		$this->cache[$user_id]['bogus'] = $name;

		return $name;
	}

	/**
	 * Whether a user has opted out of pseudonymisation.
	 *
	 * @param int $user_id
	 * @return bool
	 */
	public function is_opted_out($user_id)
	{
		$user_id = (int) $user_id;

		if (!isset($this->cache[$user_id]))
		{
			$this->prime([$user_id]);
		}

		return $this->cache[$user_id]['opted_out'];
	}

	/**
	 * Whether this member's signature should be shown to non-logged-in visitors.
	 * Only at level 3 (real name + signature), and only while opt-out is allowed
	 * board-wide; otherwise the signature is hidden from guests and bots.
	 *
	 * @param int $user_id
	 * @return bool
	 */
	public function shows_signature($user_id)
	{
		$user_id = (int) $user_id;

		if (!isset($this->cache[$user_id]))
		{
			$this->prime([$user_id]);
		}

		return $this->cache[$user_id]['show_signature'] && $this->opt_out_allowed();
	}

	/**
	 * Set a member's three-way guest-privacy choice and stamp the confirmation
	 * time (used by the reminder, #24). Ensures the member has a pseudonym row
	 * first. A signature can only be public once the name is, so a hidden name
	 * with a shown signature is coerced back to a hidden signature.
	 *
	 *   ($opted_out=false, *)           level 1: pseudonym, signature hidden
	 *   ($opted_out=true,  $show=false) level 2: real name, signature hidden
	 *   ($opted_out=true,  $show=true)  level 3: real name, signature shown
	 *
	 * @param int  $user_id
	 * @param bool $opted_out
	 * @param bool $show_signature
	 */
	public function set_choice($user_id, $opted_out, $show_signature)
	{
		$user_id = (int) $user_id;

		if (!isset($this->cache[$user_id]))
		{
			$this->prime([$user_id]);
		}
		if ($this->cache[$user_id]['bogus'] === null)
		{
			$this->create($user_id);
		}

		$opted_out = $opted_out ? 1 : 0;
		$show_signature = ($opted_out && $show_signature) ? 1 : 0;
		$now = time();

		$sql = 'UPDATE ' . $this->table . '
			SET ' . $this->db->sql_build_array('UPDATE', [
				'opted_out'            => $opted_out,
				'opt_out_confirmed_at' => $now,
				'show_signature'       => $show_signature,
			]) . '
			WHERE user_id = ' . (int) $user_id;
		$this->db->sql_query($sql);

		$this->cache[$user_id]['opted_out'] = (bool) $opted_out;
		$this->cache[$user_id]['confirmed_at'] = $now;
		$this->cache[$user_id]['show_signature'] = (bool) $show_signature;
	}

	/**
	 * When the user last confirmed their opt-out (used by the reminder, #24).
	 *
	 * @param int $user_id
	 * @return int Unix timestamp (0 if never)
	 */
	public function get_opt_out_confirmed_at($user_id)
	{
		$user_id = (int) $user_id;

		if (!isset($this->cache[$user_id]))
		{
			$this->prime([$user_id]);
		}

		return $this->cache[$user_id]['confirmed_at'];
	}

	/**
	 * Re-stamp the opt-out confirmation time (the user has acknowledged it),
	 * without changing whether they are opted out.
	 *
	 * @param int $user_id
	 */
	public function touch_opt_out($user_id)
	{
		$user_id = (int) $user_id;
		$now = time();

		$this->db->sql_query('UPDATE ' . $this->table . '
			SET ' . $this->db->sql_build_array('UPDATE', ['opt_out_confirmed_at' => $now]) . '
			WHERE user_id = ' . (int) $user_id);

		if (isset($this->cache[$user_id]))
		{
			$this->cache[$user_id]['confirmed_at'] = $now;
		}
	}

	/**
	 * @return bool Whether opt-out is enabled board-wide (ACP setting).
	 */
	protected function opt_out_allowed()
	{
		return !empty($this->config['guestpseudonyms_allow_opt_out']);
	}

	/**
	 * @param int $user_id
	 * @return string The user's real username.
	 */
	protected function real_username($user_id)
	{
		if (!isset($this->realname_cache[$user_id]))
		{
			$result = $this->db->sql_query('SELECT username FROM ' . $this->users_table . '
				WHERE user_id = ' . (int) $user_id);
			$row = $this->db->sql_fetchrow($result);
			$this->db->sql_freeresult($result);
			$this->realname_cache[$user_id] = $row ? $row['username'] : '';
		}

		return $this->realname_cache[$user_id];
	}

	/**
	 * Resolve a bogus name from a (real) username, or null if it is not a
	 * current user. Used for manual [quote=Name] quotes that carry no user_id
	 * (Story D2): a name that matches a current user gets that user's pseudonym;
	 * anything else is unverifiable and the caller anonymises it.
	 *
	 * @param string $name
	 * @return string|null
	 */
	public function get_pseudonym_by_name($name)
	{
		$clean = utf8_clean_string((string) $name);
		if ($clean === '')
		{
			return null;
		}

		if (!array_key_exists($clean, $this->name_cache))
		{
			$escaped = $this->db->sql_escape($clean);
			$result = $this->db->sql_query_limit(
				'SELECT user_id FROM ' . $this->users_table . " WHERE username_clean = '$escaped'", 1);
			$row = $this->db->sql_fetchrow($result);
			$this->db->sql_freeresult($result);
			$this->name_cache[$clean] = $row ? (int) $row['user_id'] : false;
		}

		$user_id = $this->name_cache[$clean];
		if ($user_id === false || $user_id <= 0 || $user_id === ANONYMOUS)
		{
			return null;
		}

		return $this->get_pseudonym($user_id);
	}

	/**
	 * Resolve a name a disguised viewer sees back to its user id: a pseudonym,
	 * or a real username if that member opted out. Returns 0 for anything else
	 * — in particular for the real username of a member who is pseudonymised,
	 * so a guest cannot confirm a real name through a lookup that takes one.
	 *
	 * @param string $name
	 * @return int
	 */
	public function get_user_id_by_display_name($name)
	{
		$clean = utf8_clean_string((string) $name);
		if ($clean === '')
		{
			return 0;
		}

		$escaped = $this->db->sql_escape($clean);

		if ($this->opt_out_allowed())
		{
			$sql = $this->db->sql_build_query('SELECT', [
				'SELECT' => 'u.user_id',
				'FROM'   => [
					$this->users_table => 'u',
					$this->table       => 'gp',
				],
				'WHERE'  => "u.username_clean = '" . $this->db->sql_escape($clean) . "'
					AND gp.user_id = u.user_id
					AND gp.opted_out = 1",
			]);
			$result = $this->db->sql_query_limit($sql, 1);
			$row = $this->db->sql_fetchrow($result);
			$this->db->sql_freeresult($result);

			if ($row)
			{
				return (int) $row['user_id'];
			}
		}

		$sql = 'SELECT user_id FROM ' . $this->table . " WHERE bogus_username_clean = '$escaped'";
		if ($this->opt_out_allowed())
		{
			$sql .= ' AND opted_out = 0';
		}

		$result = $this->db->sql_query_limit($sql, 1);
		$row = $this->db->sql_fetchrow($result);
		$this->db->sql_freeresult($result);

		return $row ? (int) $row['user_id'] : 0;
	}

	/**
	 * Prefix-search the names a disguised viewer sees (the live-search feed).
	 * Matches pseudonyms, plus the real names of members who opted out, and
	 * never the real name of a member who is pseudonymised.
	 *
	 * @param string $chars     What the visitor has typed so far.
	 * @param int[]  $user_types User types to include.
	 * @param int    $limit
	 * @return array[] Rows of user_id, name, user_colour.
	 */
	public function search_display_names($chars, array $user_types, $limit = 10)
	{
		$prefix = utf8_clean_string((string) $chars) . $this->db->get_any_char();

		// Both halves are fixed SQL chosen by the board setting; the only
		// visitor-supplied value is $prefix, which reaches the query solely
		// through sql_like_expression() below.
		if ($this->opt_out_allowed())
		{
			$select = 'CASE WHEN gp.opted_out = 1 THEN u.username ELSE gp.bogus_username END';
			$match = '(gp.opted_out = 1 AND u.username_clean ' . $this->db->sql_like_expression($prefix) . ')
				OR (gp.opted_out = 0 AND gp.bogus_username_clean ' . $this->db->sql_like_expression($prefix) . ')';
		}
		else
		{
			$select = 'gp.bogus_username';
			$match = 'gp.bogus_username_clean ' . $this->db->sql_like_expression($prefix);
		}

		// Built through sql_build_query so the join is emitted in whatever form
		// the board's DBMS expects, rather than hand-written SQL.
		$sql = $this->db->sql_build_query('SELECT', [
			'SELECT'    => 'u.user_id, u.user_colour, ' . $select . ' AS display_name',
			'FROM'      => [$this->users_table => 'u'],
			'LEFT_JOIN' => [
				[
					'FROM' => [$this->table => 'gp'],
					'ON'   => 'gp.user_id = u.user_id',
				],
			],
			'WHERE'     => $this->db->sql_in_set('u.user_type', $user_types) . ' AND (' . $match . ')',
		]);
		$result = $this->db->sql_query_limit($sql, (int) $limit);
		$rows = $this->db->sql_fetchrowset($result);
		$this->db->sql_freeresult($result);

		return $rows;
	}

	/**
	 * Bulk-load bogus names for a set of user ids into the cache (one query).
	 *
	 * @param int[] $user_ids
	 */
	public function prime(array $user_ids)
	{
		$missing = [];
		foreach (array_unique(array_map('intval', $user_ids)) as $id)
		{
			if (!isset($this->cache[$id]))
			{
				// bogus === null marks "looked up, no row yet"
				$this->cache[$id] = ['bogus' => null, 'opted_out' => false, 'confirmed_at' => 0, 'show_signature' => false];
				$missing[] = $id;
			}
		}

		if (empty($missing))
		{
			return;
		}

		$sql = 'SELECT user_id, bogus_username, opted_out, opt_out_confirmed_at, show_signature
			FROM ' . $this->table . '
			WHERE ' . $this->db->sql_in_set('user_id', $missing);
		$result = $this->db->sql_query($sql);
		while ($row = $this->db->sql_fetchrow($result))
		{
			$this->cache[(int) $row['user_id']] = [
				'bogus'          => $row['bogus_username'],
				'opted_out'      => (bool) $row['opted_out'],
				'confirmed_at'   => (int) $row['opt_out_confirmed_at'],
				'show_signature' => (bool) $row['show_signature'],
			];
		}
		$this->db->sql_freeresult($result);
	}

	/**
	 * Generate a unique bogus name for a user and store it as a new row.
	 *
	 * @param int         $user_id
	 * @param string|null $iso
	 * @return string
	 */
	protected function create($user_id, $iso = null)
	{
		$name = $this->store($user_id, $this->next_unique_name($user_id, $iso));
		$this->cache[$user_id] = ['bogus' => $name, 'opted_out' => false, 'confirmed_at' => 0, 'show_signature' => false];

		return $name;
	}

	/**
	 * Produce a bogus name not already used by a real user or another pseudonym.
	 *
	 * @param int         $user_id
	 * @param string|null $iso     Language to generate in; board default if null.
	 * @return string
	 */
	protected function next_unique_name($user_id, $iso = null)
	{
		if ($iso === null)
		{
			$iso = (string) $this->config['default_lang'];
		}

		for ($attempt = 0; $attempt < 50; $attempt++)
		{
			$candidate = $this->generator->generate($iso);
			// After a few clashes, disambiguate with a number.
			if ($attempt >= 5)
			{
				$candidate .= '_' . mt_rand(2, 9999);
			}

			if (!$this->clean_taken(utf8_clean_string($candidate)))
			{
				return $candidate;
			}
		}

		// Last resort: guaranteed unique by user_id.
		return $this->generator->generate($iso) . '_' . $user_id;
	}

	/**
	 * Is this clean name already used by a real user or another pseudonym?
	 *
	 * @param string $clean
	 * @return bool
	 */
	protected function clean_taken($clean)
	{
		$clean = $this->db->sql_escape($clean);

		foreach ([$this->users_table => 'username_clean', $this->table => 'bogus_username_clean'] as $table => $column)
		{
			$result = $this->db->sql_query_limit('SELECT 1 FROM ' . $table . " WHERE $column = '$clean'", 1);
			$taken = (bool) $this->db->sql_fetchrow($result);
			$this->db->sql_freeresult($result);
			if ($taken)
			{
				return true;
			}
		}

		return false;
	}

	/**
	 * Persist a pseudonym, and return the name that is actually stored.
	 *
	 * Two concurrent first views of the same member both generate a name and
	 * both try to insert it; one of them loses on the primary key (or on the
	 * unique clean-name index, if the two happened to generate the same name).
	 * The loser re-reads the row so it returns — and caches — the name the
	 * winner stored, instead of a name that exists nowhere.
	 *
	 * @param int    $user_id
	 * @param string $name
	 * @param int    $attempt Recursion guard for repeated clean-name clashes.
	 * @return string The stored name (the winner's, if this insert lost).
	 */
	protected function store($user_id, $name, $attempt = 0)
	{
		$user_id = (int) $user_id;

		$data = [
			'user_id'              => $user_id,
			'bogus_username'       => $name,
			'bogus_username_clean' => utf8_clean_string($name),
			'created_at'           => time(),
		];

		$this->db->sql_return_on_error(true);
		$this->db->sql_query('INSERT INTO ' . $this->table . ' ' . $this->db->sql_build_array('INSERT', $data));
		$failed = (bool) $this->db->get_sql_error_triggered();
		$this->db->sql_return_on_error(false);

		if (!$failed)
		{
			return $name;
		}

		$result = $this->db->sql_query('SELECT bogus_username FROM ' . $this->table . '
			WHERE user_id = ' . (int) $user_id);
		$row = $this->db->sql_fetchrow($result);
		$this->db->sql_freeresult($result);

		// No row means the clash was on the clean-name index, not the primary
		// key: another user took this name. Try again with a fresh one.
		if (!$row)
		{
			return ($attempt < 3) ? $this->store($user_id, $this->next_unique_name($user_id), $attempt + 1) : $name;
		}

		return $row['bogus_username'];
	}
}
