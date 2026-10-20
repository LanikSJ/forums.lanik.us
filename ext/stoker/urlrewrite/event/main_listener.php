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

namespace stoker\urlrewrite\event;

use phpbb\auth\auth;
use phpbb\config\config;
use phpbb\db\driver\driver_interface;
use phpbb\request\request;
use phpbb\template\template;
use phpbb\user;
use stoker\urlrewrite\helper\url_helper;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class main_listener implements EventSubscriberInterface
{
	/** @var config */
	protected $config;

	/** @var driver_interface */
	protected $db;

	/** @var auth */
	protected $auth;

	/** @var request */
	protected $request;

	/** @var user */
	protected $user;

	/** @var template */
	protected $template;

	/** @var url_helper */
	protected $url_helper;

	/** @var string */
	protected $root_path;

	/** @var string */
	protected $php_ext;

	/** @var array<int,string> */
	protected $forum_names = [];

	/** @var array<int,array|false> */
	protected $topic_rows = [];

	/** @var bool */
	protected $forums_loaded = false;

	/** @var string|null */
	protected $board_path = null;

	/** @var int|null */
	protected $page_start = null;

	/** @var bool */
	protected $redirect_checked = false;

	/**
	 * post_id => ['topic_id' => int, 'topic_title' => string], primed from data
	 * phpBB already holds. Consulted by topic_route() only: it is deliberately
	 * kept out of url_helper::$post_topics, which also carries forum_id and is
	 * read by permission checks in quote_helper and redirect_canonical().
	 *
	 * @var array<int,array>
	 */
	protected $post_topic_hints = [];

	/**
	 * post_id => true for posts whose topic title must not become a slug, because
	 * the page emitting the link is one where phpBB itself withheld the subject.
	 * Read by topic_route() before anything else, so neither a hint nor a lookup
	 * can put the title back.
	 *
	 * @var array<int,bool>
	 */
	protected $suppress_slug = [];

	/**
	 * topic_id => topic_title, primed where the owning forum_id on the row cannot
	 * be trusted to be the topic's own. Titles only: nothing here reaches
	 * can_read_topic(), which keeps reading get_topic_row() for itself.
	 *
	 * @var array<int,string>
	 */
	protected $topic_titles = [];

	/**
	 * @param config			$config
	 * @param driver_interface	$db
	 * @param auth				$auth
	 * @param request			$request
	 * @param user				$user
	 * @param template			$template
	 * @param url_helper		$url_helper
	 * @param string			$root_path
	 * @param string			$php_ext
	 */
	public function __construct(config $config, driver_interface $db, auth $auth, request $request, user $user, template $template, url_helper $url_helper, string $root_path, string $php_ext)
	{
		$this->config = $config;
		$this->db = $db;
		$this->auth = $auth;
		$this->request = $request;
		$this->user = $user;
		$this->template = $template;
		$this->url_helper = $url_helper;
		$this->root_path = $root_path;
		$this->php_ext = $php_ext;
	}

	/**
	 * @return array
	 */
	public static function getSubscribedEvents(): array
	{
		return [
			'core.append_sid' => 'rewrite_url',
			'core.page_header_after' => [['redirect_canonical'], ['fix_script_name']],
			'core.page_footer' => 'fix_canonical',
			'core.approve_posts_after' => 'fix_approve_redirect',
			'core.disapprove_posts_after' => 'fix_disapprove_redirect',
			'core.viewtopic_assign_template_vars_before' => 'redirect_canonical',
			'core.viewtopic_modify_page_title' => [['fix_action_urls'], ['capture_start']],
			'core.viewforum_modify_page_title' => 'capture_start',
			'core.viewforum_modify_sort_data_sql' => 'reset_start_on_sort',
			'core.viewtopic_modify_post_row' => 'fix_post_row_urls',
			'core.viewtopic_before_f_read_check' => 'prime_topic_row',
			'core.viewtopic_modify_post_data' => 'prime_topic_posts',
			'core.viewforum_modify_topics_data' => 'prime_forum_topics',
			'core.search_modify_rowset' => 'prime_search_posts',
			'core.display_forums_before' => 'prime_forum_last_posts',
			'stoker.urlrewrite.prime_rows' => 'prime_external_rows',
			'core.modify_notification_message' => 'rewrite_notification_message',
			'core.ucp_pm_compose_template' => 'rewrite_pm_quote_link',
			'core.make_jumpbox_modify_tpl_ary' => 'rewrite_jumpbox_link',
		];
	}

	/**
	 * Rewrite generated viewtopic/viewforum URLs to friendly URLs.
	 *
	 * @param \phpbb\event\data $event
	 * @return void
	 */
	public function rewrite_url($event): void
	{
		if ((defined('IN_ADMIN') && IN_ADMIN) || empty($this->config['stoker_urlrewrite_enable']))
		{
			return;
		}

		// append_sid() initialises this to false. A non-false value means another
		// listener already produced the URL; replacing it would silently drop it.
		if ($event['append_sid_overwrite'] !== false)
		{
			return;
		}

		$url = (string) $event['url'];

		$is_topic = strpos($url, 'viewtopic.' . $this->php_ext) !== false;
		$is_forum = strpos($url, 'viewforum.' . $this->php_ext) !== false;

		if (!$is_topic && !$is_forum)
		{
			return;
		}

		$params = $event['params'];

		// Preserve an anchor only if one is genuinely present; never synthesise one.
		$anchor = $this->extract_anchor($url, $params);

		$query = $this->normalise_params($params);
		unset($query['#']);

		$path = $is_topic ? $this->topic_route($query) : $this->forum_route($query);

		if ($path === '')
		{
			return;
		}

		// Re-attach leftover parameters (start, hilit, ...) so pagination keeps working.
		unset($query['f'], $query['t'], $query['p']);

		// Setting append_sid_overwrite makes append_sid() return immediately, before
		// it appends $_EXTRA_URL and the session id. Neither is reachable through a
		// service, so mirror phpBB's own handling here: extra params first, caller
		// params second (they win on collision), session id last.
		$query = $this->apply_session($query, $event['session_id']);

		// Emit a root-relative URL (e.g. /board/path/slug-t1) so it resolves against
		// the board root from any page - including routed controllers such as
		// /post/{id}/report, whose request path would otherwise be prepended to a
		// bare relative URL by the browser. When the caller supplied a full URL the
		// scheme and host are kept: phpbb\feed\helper::append_sid() prepends
		// generate_board_url(), and feed links must stay absolute.
		$event['append_sid_overwrite'] = $this->absolute_prefix($url) . $this->board_path() . $path . $this->build_query($query, (bool) $event['is_amp']) . $anchor;
	}

	/**
	 * Redirect a raw viewtopic/viewforum hit to its friendly URL. 301, or 302 when
	 * a session id has to ride along and the response must not be cached.
	 *
	 * @param \phpbb\event\data|null $event
	 * @return void
	 */
	public function redirect_canonical($event = null): void
	{
		// core.viewtopic_assign_template_vars_before carries the offset
		// viewtopic.php settled on - validate_start() has already run, and
		// nothing reassigns it before the page title event where capture_start()
		// would otherwise pick it up. Record it here so the block below can use
		// it. core.page_header_after carries no offset, so viewforum is
		// unaffected: capture_start() has already run there.
		if ($event !== null && isset($event['start']))
		{
			$this->page_start = (int) $event['start'];
		}

		// The same event carries the topic viewtopic.php resolved for this hit and
		// the forum id it settled on: $forum_id is assigned from topic_data before
		// core.viewtopic_modify_forum_id, $topic_id likewise, and the f_read check
		// has already run against that id by the time this fires. Reading them here
		// answers the p= branch below without post_topic() querying for a row
		// viewtopic.php has already loaded - and checks permission against the id
		// phpBB itself checked, rather than second-guessing it with p.forum_id.
		$event_post_id = 0;
		$event_topic_id = 0;
		$event_forum_id = 0;
		$event_title = '';

		if ($event !== null && isset($event['topic_data'], $event['topic_id'], $event['forum_id'], $event['post_id']))
		{
			$topic_data = $event['topic_data'];

			$event_post_id = (int) $event['post_id'];
			$event_topic_id = (int) $event['topic_id'];
			$event_forum_id = (int) $event['forum_id'];
			$event_title = (is_array($topic_data) && isset($topic_data['topic_title'])) ? (string) $topic_data['topic_title'] : '';
		}

		// Subscribed to two events so viewtopic redirects before it builds the
		// whole topic. Whichever fires first is the only one that does the work;
		// without this the second re-runs the topic and permission lookup.
		if ($this->redirect_checked)
		{
			return;
		}

		$this->redirect_checked = true;

		if ((defined('IN_ADMIN') && IN_ADMIN) || empty($this->config['stoker_urlrewrite_enable']) || empty($this->config['stoker_urlrewrite_redirect_enable']))
		{
			return;
		}

		// HEAD is GET without the body, so it gets the same canonical answer -
		// crawlers that probe with HEAD would otherwise never see the redirect.
		$method = strtoupper((string) $this->request->server('REQUEST_METHOD'));

		if (headers_sent() || ($method !== 'GET' && $method !== 'HEAD'))
		{
			return;
		}

		$page_name = isset($this->user->page['page_name']) ? (string) $this->user->page['page_name'] : '';
		$is_topic = ($page_name === 'viewtopic.' . $this->php_ext);
		$is_forum = ($page_name === 'viewforum.' . $this->php_ext);

		if (!$is_topic && !$is_forum)
		{
			return;
		}

		$request_uri = (string) $this->request->server('REQUEST_URI');
		$qpos = strpos($request_uri, '?');
		$request_path = ($qpos !== false) ? substr($request_uri, 0, $qpos) : $request_uri;

		// A friendly URL never contains the script name; only redirect raw hits.
		// Only the path is examined: a query value such as ?hilit=index.php would
		// otherwise look like a raw hit and redirect a friendly URL onto itself.
		if (strpos($request_path, '.' . $this->php_ext) === false)
		{
			return;
		}

		// Functional viewtopic modes (unread/next/previous/print/viewpoll) depend on
		// phpBB processing the raw URL in place; canonicalising them discards the intent
		// - e.g. an email's view=unread link would land on the first post. Mark-read
		// requests are handled by viewforum.php after page_header(), so redirecting
		// here would drop the action outright.
		//
		// watch/unwatch is the same case: viewforum.php calls watch_topic_forum()
		// after page_header(), and the unsubscribe links in the notification emails
		// (phpbb\notification\type\forum and \topic) are built by concatenation, so
		// they arrive as raw viewforum.php URLs. Redirecting one drops the action and
		// the visitor is never unsubscribed. viewtopic.php handles it before this
		// fires, but the parameter is excluded on both scripts for consistency.
		// bookmark is the same case seen from the other end: viewtopic.php acts on
		// it before this fires, then reports the result through trigger_error(),
		// which calls page_header(). Without this the confirmation page is replaced
		// by a redirect, and the bookmark URL - link hash and all - is cached as
		// permanently moved.
		if ($this->request->variable('view', '') !== '' || $this->request->variable('mark', '') !== ''
			|| $this->request->variable('watch', '') !== '' || $this->request->variable('unwatch', '') !== ''
			|| $this->request->variable('bookmark', 0) !== 0)
		{
			return;
		}

		$path = '';
		$anchor = '';

		if ($is_topic)
		{
			$topic_id = $this->request->variable('t', 0);
			$post_id = $this->request->variable('p', 0);

			if ($post_id > 0)
			{
				// The event already resolved this post to its topic. The read check
				// is kept even though viewtopic.php ran one before firing: an
				// extension may set overrides_f_read_check on
				// core.viewtopic_before_f_read_check, in which case core never did.
				if ($event_post_id === $post_id && $event_topic_id > 0 && $event_title !== '')
				{
					if (!$this->can_read_forum($event_forum_id))
					{
						return;
					}

					$path = $this->url_helper->post_path($post_id, $event_topic_id, $event_title);
					$anchor = '#p' . $post_id;
				}
				else
				{
					// No event: this is core.page_header_after, reached on the paths
					// that never get as far as the template vars event - NO_TOPIC, the
					// forum password prompt, an f_read denial. The row and its
					// permission have to be resolved here.
					$info = $this->url_helper->post_topic($post_id);

					// A missing row means phpBB is on its way to a NO_POST 404 - leave
					// the status line alone. A locked forum or a topic phpBB would
					// hide means the title must not reach the Location header.
					if ($info === false || !$this->url_helper->topic_readable($info))
					{
						return;
					}

					$path = $this->url_helper->post_path($post_id, (int) $info['topic_id'], (string) $info['topic_title']);
					$anchor = '#p' . $post_id;
				}
			}
			else if ($topic_id > 0)
			{
				if (!$this->can_read_topic($topic_id))
				{
					return;
				}

				$path = $this->url_helper->topic_path($topic_id, $this->get_topic_title($topic_id));
			}
		}
		else
		{
			$forum_id = $this->request->variable('f', 0);

			if ($forum_id > 0 && $this->forum_exists($forum_id) && $this->can_read_forum($forum_id))
			{
				$path = $this->url_helper->forum_path($forum_id, $this->get_forum_name($forum_id));
			}
		}

		if ($path === '')
		{
			return;
		}

		$leftover = [];

		if ($qpos !== false)
		{
			// request->server() runs REQUEST_URI through htmlspecialchars(ENT_COMPAT),
			// so decode with the same flags before parsing. Replacing &amp; alone left
			// &quot; &lt; &gt; in values such as hilit, which then reached the Location
			// header double-encoded - and an HTML-encoded separator hides real keys,
			// e.g. t=9 parses as junk "amp;t".
			parse_str(html_entity_decode(substr($request_uri, $qpos + 1), ENT_COMPAT, 'UTF-8'), $leftover);
			$leftover = $this->filter_leftover($leftover, $is_topic);
		}

		// The offset phpBB settled on is known on both scripts by now, so use it
		// instead of the raw request value - an out-of-range start= would otherwise
		// be baked into a permanent redirect. On viewforum capture_start() supplied
		// it (core.viewforum_modify_page_title fires before page_header(), and
		// validate_start() before that); on viewtopic it came from the event this
		// handler was called with.
		if ($this->page_start !== null)
		{
			if ($this->page_start > 0)
			{
				$leftover['start'] = $this->page_start;
			}
			else
			{
				unset($leftover['start']);
			}
		}

		// Deliberately not apply_session(): a redirect target is a response header,
		// not a link. $_EXTRA_URL would put style= back after filter_leftover()
		// dropped it, re-creating the duplicate-url family the allowlist exists to
		// stop - and baking it into a permanent redirect at that.
		//
		// The session id is the other way round: dropping it signs out a cookie-less
		// visitor who lands on a raw url, since filter_leftover() already discarded
		// the incoming one. Carry it, but never in a cacheable response:
		// session_create() sets $_SID for every non-bot that has no cookie yet, so a
		// stored 301 would hand a live session to whoever replays it.
		// isset(): append_sid() can run before session_begin() populates $_SID.
		global $_SID;

		$sid = isset($_SID) ? (string) $_SID : '';

		if ($sid !== '')
		{
			$leftover['sid'] = $sid;
		}

		$extra = !empty($leftover) ? '?' . http_build_query($leftover, '', '&') : '';

		$target = generate_board_url() . '/' . $path . $extra . $anchor;

		if ($sid === '')
		{
			send_status_line(301, 'Moved Permanently');

			// The target is built from a title this visitor was allowed to see -
			// can_read_topic()/can_read_forum() above decide whether it is emitted at
			// all. A shared cache keys on the URL alone, so without this it could
			// hand the title-bearing Location to someone who has no read access.
			// page_header() has not sent its own headers yet at this point (they go
			// out after core.page_header_after), so there is nothing to inherit.
			header('Cache-Control: private');
		}
		else
		{
			send_status_line(302, 'Found');
			header('Cache-Control: no-store');
		}

		header('Location: ' . $target);

		// Mirror phpBB's redirect(): without this the cache driver is never
		// unloaded (so nothing it collected this request is written back) and the
		// database connection is not closed.
		garbage_collection();

		exit_handler();
	}

	/**
	 * Record the page offset phpBB settled on, for fix_canonical().
	 *
	 * Both events fire after validate_start() (viewtopic.php also recomputes the
	 * offset from the post id on a p= hit), so this is the offset actually rendered.
	 *
	 * @param \phpbb\event\data $event
	 * @return void
	 */
	public function capture_start($event): void
	{
		$this->page_start = (int) $event['start'];
	}

	/**
	 * Drop the captured offset when viewforum.php is about to reset it.
	 *
	 * core.viewforum_modify_page_title fires before viewforum.php sets $start = 0
	 * on a posted sort, so capture_start() records the pre-reset value and the
	 * canonical would point at a page the visitor is not on. This event only fires
	 * inside the if ($sort_days) branch that guards the reset, so its firing plus a
	 * posted 'sort' reproduces phpBB's condition exactly.
	 *
	 * @return void
	 */
	public function reset_start_on_sort(): void
	{
		if ($this->request->is_set_post('sort'))
		{
			$this->page_start = 0;
		}
	}

	/**
	 * Replace phpBB's canonical URL with the friendly equivalent.
	 *
	 * phpBB builds U_CANONICAL as generate_board_url() . '/' . append_sid(...),
	 * and append_sid returns our root-relative override verbatim, producing a
	 * doubled slash (and a duplicated board path on subdirectory installs).
	 * Rebuild it from the board URL plus the friendly path. Runs before the
	 * template is displayed, so the reassignment wins.
	 *
	 * The offset comes from capture_start() rather than the request: viewtopic.php
	 * derives it from the post id on a p= hit and both scripts clamp it through
	 * validate_start(), so the request value alone would point the canonical at the
	 * wrong page - or at a page that does not exist.
	 *
	 * @return void
	 */
	public function fix_canonical(): void
	{
		if ((defined('IN_ADMIN') && IN_ADMIN) || empty($this->config['stoker_urlrewrite_enable']))
		{
			return;
		}

		$page_name = isset($this->user->page['page_name']) ? (string) $this->user->page['page_name'] : '';
		$is_topic = (strpos($page_name, 'viewtopic.' . $this->php_ext) === 0);
		$is_forum = (strpos($page_name, 'viewforum.' . $this->php_ext) === 0);

		if (!$is_topic && !$is_forum)
		{
			return;
		}

		$start = ($this->page_start !== null) ? $this->page_start : $this->request->variable('start', 0);
		$path = '';

		if ($is_topic)
		{
			$topic_id = $this->request->variable('t', 0);

			if ($topic_id <= 0)
			{
				$post_id = $this->request->variable('p', 0);
				$info = ($post_id > 0) ? $this->url_helper->post_topic($post_id) : false;
				$topic_id = $info ? (int) $info['topic_id'] : 0;
			}

			if ($topic_id > 0 && $this->can_read_topic($topic_id))
			{
				$path = $this->url_helper->topic_path($topic_id, $this->get_topic_title($topic_id));
			}
		}
		else
		{
			$forum_id = $this->request->variable('f', 0);

			if ($forum_id > 0 && $this->forum_exists($forum_id) && $this->can_read_forum($forum_id))
			{
				$path = $this->url_helper->forum_path($forum_id, $this->get_forum_name($forum_id));
			}
		}

		if ($path === '')
		{
			return;
		}

		$this->template->assign_vars([
			'U_CANONICAL' => generate_board_url() . '/' . $path . (($start > 0) ? '?start=' . $start : ''),
		]);
	}

	/**
	 * Restore the SCRIPT_NAME template variable on a rewritten page.
	 *
	 * extract_current_page() appends the request path info to page_name whenever
	 * it is not '/', which a rewritten URL always makes it: a hit on /slug-t5
	 * arrives as 'viewtopic.php/slug-t5'. page_header() derives SCRIPT_NAME from
	 * that, so the 'section-{SCRIPT_NAME}' body class prosilver and most styles
	 * emit becomes 'section-viewtopic/slug-t5', and every rule a style scopes on
	 * .section-viewtopic or .section-viewforum stops matching.
	 *
	 * Put back the value core itself assigns for the same page unrewritten. Only
	 * the template variable is touched: $user->page['page_name'] is left as core
	 * built it, because the session and viewonline read it.
	 *
	 * Fires after the assignment in page_header(), so this reassignment wins.
	 *
	 * @return void
	 */
	public function fix_script_name(): void
	{
		if ((defined('IN_ADMIN') && IN_ADMIN) || empty($this->config['stoker_urlrewrite_enable']))
		{
			return;
		}

		$page_name = isset($this->user->page['page_name']) ? (string) $this->user->page['page_name'] : '';

		foreach (['viewtopic', 'viewforum'] as $script)
		{
			if (strpos($page_name, $script . '.' . $this->php_ext . '/') === 0)
			{
				$this->template->assign_var('SCRIPT_NAME', $script);
				return;
			}
		}
	}

	/**
	 * Repair the post-approval redirect when its source URL was rewritten.
	 *
	 * viewtopic.php builds the inline approve action's redirect by appending
	 * "&p=<id>#p<id>" directly onto the already-rewritten friendly topic URL,
	 * e.g. "/slug-t3717&p=23549#p23549". With no query string present the "&p="
	 * becomes part of the path and matches no rewrite rule, so phpBB's router
	 * rejects it. Convert the glued post part to the friendly "-p<id>" form.
	 *
	 * @param \phpbb\event\data $event
	 * @return void
	 */
	public function fix_approve_redirect($event): void
	{
		if (empty($this->config['stoker_urlrewrite_enable']))
		{
			return;
		}

		$fixed = preg_replace('/(-t[0-9]+)&(?:amp;)?p=([0-9]+)/', '$1-p$2', (string) $event['redirect']);

		if ($fixed !== null)
		{
			$event['redirect'] = $fixed;
		}
	}

	/**
	 * Repair the post-disapproval redirect when its source URL was rewritten.
	 *
	 * phpBB only sends moderators back to the topic - or its forum, when the topic
	 * itself was disapproved - if it recognises the redirect as a viewtopic URL via
	 * a literal 'viewtopic.php' prefix check (mcp_queue.php). Our rewrite removes
	 * that prefix, so the check fails and phpBB redirects to a dead friendly URL.
	 * Reproduce phpBB's own intent here, keyed off the rewritten URL instead.
	 *
	 * @param \phpbb\event\data $event
	 * @return void
	 */
	public function fix_disapprove_redirect($event): void
	{
		if (empty($this->config['stoker_urlrewrite_enable']))
		{
			return;
		}

		$redirect = (string) $event['redirect'];

		// Only act on one of our rewritten topic URLs carrying an appended post part.
		if (!preg_match('/-t[0-9]+.*&(?:amp;)?p=[0-9]+/', $redirect))
		{
			return;
		}

		if ((int) $event['num_disapproved_topics'] > 0)
		{
			// The topic no longer exists - fall back to its forum, matching phpBB.
			// mcp_queue.php reads $post_data left over from its foreach, i.e. the
			// last row, so take the last entry here too.
			$post_info = $event['post_info'];
			$row = is_array($post_info) ? end($post_info) : false;
			$forum_id = ($row !== false && isset($row['forum_id'])) ? (int) $row['forum_id'] : 0;

			if ($forum_id > 0)
			{
				// Re-enters append_sid, so it returns as the friendly forum URL.
				$event['redirect'] = append_sid($this->root_path . 'viewforum.' . $this->php_ext, 'f=' . $forum_id);
				return;
			}

			// No forum id to fall back to. Drop through: the topic is gone either
			// way, and a URL that 404s beats one whose glued '&p=' sits in the path
			// and matches no rewrite rule at all.
		}

		// Topic survives - drop the post part and return to it.
		$fixed = preg_replace('/&(?:amp;)?p=[0-9]+(?:#p[0-9]+)?$/', '', $redirect);

		if ($fixed !== null)
		{
			$event['redirect'] = $fixed;
		}
	}

	/**
	 * Repair the viewtopic action links that phpBB builds by concatenation.
	 *
	 * U_PRINT_TOPIC, U_BOOKMARK_TOPIC and U_VIEW_RESULTS are formed as
	 * $viewtopic_url . '&amp;...' in viewtopic.php. Once $viewtopic_url is a
	 * friendly URL with no query string (a bare topic at start 0), the glued '&'
	 * lands in the path and matches no rewrite rule. Promote that first separator
	 * to '?' - keeping phpBB's own params (start, sort, hilit, hash) untouched.
	 *
	 * @return void
	 */
	public function fix_action_urls(): void
	{
		if (empty($this->config['stoker_urlrewrite_enable']))
		{
			return;
		}

		foreach (['U_PRINT_TOPIC', 'U_BOOKMARK_TOPIC', 'U_VIEW_RESULTS'] as $var)
		{
			$url = (string) $this->template->retrieve_var($var);

			// Empty means phpBB withheld the link (no permission) - leave it.
			// A '?' already present means the glued '&' is valid - leave it.
			if ($url === '' || strpos($url, '?') !== false)
			{
				continue;
			}

			$fixed = preg_replace('/&(?:amp;)?/', '?', $url, 1);

			if ($fixed !== null && $fixed !== $url)
			{
				$this->template->assign_var($var, $fixed);
			}
		}
	}

	/**
	 * Repair the "display post" link on hidden posts.
	 *
	 * viewtopic.php builds U_MINI_POST_VIEW as append_sid(..., 'p=<id>') with
	 * '&amp;view=show#p<id>' concatenated onto the result, so the glued '&' lands
	 * in the path of the friendly URL and matches no rewrite rule. It is a postrow
	 * block variable, so it has to be corrected here rather than in
	 * fix_action_urls(), which only reaches root template variables.
	 *
	 * @param \phpbb\event\data $event
	 * @return void
	 */
	public function fix_post_row_urls($event): void
	{
		if (empty($this->config['stoker_urlrewrite_enable']))
		{
			return;
		}

		$post_row = $event['post_row'];

		if (empty($post_row['U_MINI_POST_VIEW']))
		{
			return;
		}

		$url = (string) $post_row['U_MINI_POST_VIEW'];

		// A '?' already present means the URL was not rewritten and the glued '&'
		// is valid - leave it.
		if (strpos($url, '?') !== false)
		{
			return;
		}

		$fixed = preg_replace('/&(?:amp;)?/', '?', $url, 1);

		if ($fixed !== null && $fixed !== $url)
		{
			$post_row['U_MINI_POST_VIEW'] = $fixed;
			$event['post_row'] = $post_row;
		}
	}

	/**
	 * Prime the row for the topic viewtopic.php is about to render.
	 *
	 * Every viewtopic page emits at least one t= link, and redirect_canonical() and
	 * fix_canonical() both need the title and owning forum, so get_topic_row() runs
	 * one query for data this event already carries.
	 *
	 * topic_data comes from a SELECT of 't.*, f.*' whose FROM chain ends with the
	 * topics table, so topic_title and forum_id are the topic's own columns - the
	 * same two values get_topic_row() would have read for itself.
	 *
	 * Fires before the f_read check, which is before the first append_sid() on the
	 * topic and before core.viewtopic_assign_template_vars_before, where
	 * redirect_canonical() runs.
	 *
	 * @param \phpbb\event\data $event
	 * @return void
	 */
	public function prime_topic_row($event): void
	{
		if (empty($this->config['stoker_urlrewrite_enable']))
		{
			return;
		}

		$topic_data = $event['topic_data'];
		$topic_id = (int) $event['topic_id'];

		if ($topic_id <= 0 || !is_array($topic_data) || isset($this->topic_rows[$topic_id])
			|| !isset($topic_data['topic_title'], $topic_data['forum_id'], $topic_data['topic_visibility'], $topic_data['topic_poster']))
		{
			return;
		}

		$this->topic_rows[$topic_id] = [
			'topic_title' => (string) $topic_data['topic_title'],
			'forum_id' => $topic_data['forum_id'],
			'topic_visibility' => $topic_data['topic_visibility'],
			'topic_poster' => $topic_data['topic_poster'],
		];
	}

	/**
	 * Prime the topic of every post viewtopic.php is about to render.
	 *
	 * viewtopic.php builds U_MINI_POST and U_MINI_POST_VIEW for every post row as
	 * append_sid(..., 'p=<id>') - no t= - so topic_route() would resolve each post
	 * with its own query. Every one of them belongs to the topic being viewed, and
	 * this event carries both post_list and topic_data, so the whole page is
	 * satisfied without a single query.
	 *
	 * Fires immediately before the post output loop.
	 *
	 * @param \phpbb\event\data $event
	 * @return void
	 */
	public function prime_topic_posts($event): void
	{
		if (empty($this->config['stoker_urlrewrite_enable']))
		{
			return;
		}

		$topic_data = $event['topic_data'];
		$topic_id = (int) $event['topic_id'];
		$title = (is_array($topic_data) && isset($topic_data['topic_title'])) ? (string) $topic_data['topic_title'] : '';

		if ($topic_id <= 0 || $title === '')
		{
			return;
		}

		foreach ((array) $event['post_list'] as $post_id)
		{
			$this->hint_post((int) $post_id, $topic_id, $title);
		}
	}

	/**
	 * Prime both rewritten links viewforum.php builds for every topic row.
	 *
	 * U_VIEW_TOPIC is append_sid(..., 't=<topic_id>'), which topic_route() resolves
	 * through get_topic_row(), and U_LAST_POST is append_sid(..., 'p=<topic_last_post_id>'),
	 * which it resolves through post_topic(). Both are one query per row. The
	 * rowset already holds everything either of them needs.
	 *
	 * The topic rows are primed verbatim from the same table and columns
	 * get_topic_row() would have selected - viewforum's queries are SELECT t.*
	 * against phpbb_topics and pull no forum_id of their own - so can_read_topic()
	 * sees exactly the value it would have read itself.
	 *
	 * Fires before the topic output loop.
	 *
	 * @param \phpbb\event\data $event
	 * @return void
	 */
	public function prime_forum_topics($event): void
	{
		if (empty($this->config['stoker_urlrewrite_enable']))
		{
			return;
		}

		foreach ((array) $event['rowset'] as $row)
		{
			// A shadow topic's row is the target topic's data with the shadow's
			// title merged back over it (viewforum.php), so priming it would slug
			// the target id with a title that can have diverged. Leave those to the
			// lookups, which read the target topic itself.
			if (!is_array($row) || !empty($row['topic_moved_id']))
			{
				continue;
			}

			$topic_id = isset($row['topic_id']) ? (int) $row['topic_id'] : 0;
			$topic_title = isset($row['topic_title']) ? (string) $row['topic_title'] : '';

			if ($topic_id > 0 && !isset($this->topic_rows[$topic_id])
				&& isset($row['forum_id'], $row['topic_visibility'], $row['topic_poster']))
			{
				$this->topic_rows[$topic_id] = [
					'topic_title' => $topic_title,
					'forum_id' => $row['forum_id'],
					'topic_visibility' => $row['topic_visibility'],
					'topic_poster' => $row['topic_poster'],
				];
			}

			$this->hint_post(
				isset($row['topic_last_post_id']) ? (int) $row['topic_last_post_id'] : 0,
				$topic_id,
				$topic_title
			);
		}
	}

	/**
	 * Prime the topic of every post link search.php is about to render.
	 *
	 * Topic mode builds U_LAST_POST from topic_last_post_id; post mode builds
	 * U_VIEW_POST from post_id. Both rowsets carry the owning topic id and title -
	 * the post-mode query joins the topics table - so neither needs a query.
	 *
	 * Fires before the results loop.
	 *
	 * @param \phpbb\event\data $event
	 * @return void
	 */
	public function prime_search_posts($event): void
	{
		if (empty($this->config['stoker_urlrewrite_enable']))
		{
			return;
		}

		$key = ((string) $event['show_results'] === 'topics') ? 'topic_last_post_id' : 'post_id';

		foreach ((array) $event['rowset'] as $row)
		{
			if (!is_array($row))
			{
				continue;
			}

			$topic_id = isset($row['topic_id']) ? (int) $row['topic_id'] : 0;
			$topic_title = isset($row['topic_title']) ? (string) $row['topic_title'] : '';

			// Title only, deliberately: the two search modes select their columns
			// in opposite order - posts mode ends with t.*, topics mode ends with
			// f.forum_id - so which table $row['forum_id'] came from depends on the
			// mode. topic_title comes from t.* either way.
			if ($topic_id > 0 && $topic_title !== '' && !isset($this->topic_titles[$topic_id]))
			{
				$this->topic_titles[$topic_id] = $topic_title;
			}

			$this->hint_post(
				isset($row[$key]) ? (int) $row[$key] : 0,
				$topic_id,
				$topic_title
			);
		}
	}

	/**
	 * Prime the last post of every forum row the board index is about to render.
	 *
	 * display_forums() builds each row's last-post link as append_sid(..., 'p=<id>'),
	 * so topic_route() would resolve one post per forum with a query of its own.
	 * This event carries every row the loop will output, so one batched query
	 * covers the whole page - index.php and the subforum block on viewforum.
	 *
	 * The same pass applies phpBB's own rule for that link: it blanks the last
	 * post subject when the owning forum carries a password or the viewer holds
	 * neither f_read nor f_list_topics on it, yet still emits the URL. Slugging
	 * those would put the withheld title straight back into the address bar, so
	 * their post ids are recorded for topic_route() to render id-only - and they
	 * are kept out of the batch, since their title is never used. acl_gets() ORs
	 * its options, so the two permissions are an either/or here, as in phpBB.
	 *
	 * Fires before the forum output loop.
	 *
	 * @param \phpbb\event\data $event
	 * @return void
	 */
	public function prime_forum_last_posts($event): void
	{
		if (empty($this->config['stoker_urlrewrite_enable']))
		{
			return;
		}

		$post_ids = [];

		foreach ((array) $event['forum_rows'] as $row)
		{
			if (!is_array($row) || empty($row['forum_last_post_id']))
			{
				continue;
			}

			$post_id = (int) $row['forum_last_post_id'];

			// Both keys are set on every forum_rows entry by display_forums()
			// itself; a missing one is treated as withheld rather than assumed
			// harmless.
			$visible = isset($row['forum_password_last_post'], $row['forum_id_last_post'])
				&& $row['forum_password_last_post'] === ''
				&& $this->auth->acl_gets('f_read', 'f_list_topics', (int) $row['forum_id_last_post']);

			if ($visible)
			{
				$post_ids[$post_id] = $post_id;
			}
			else
			{
				$this->suppress_slug[$post_id] = true;
			}
		}

		if (!empty($post_ids))
		{
			$this->url_helper->prime_post_topics($post_ids);
		}
	}

	/**
	 * Prime topic rows handed over by another extension.
	 *
	 * Public entry point. An extension that holds a topic rowset dispatches
	 * 'stoker.urlrewrite.prime_rows' with it before it builds its links, and the
	 * rows below then cost no query. Dispatching an event nothing listens to is a
	 * no-op, so the caller needs no dependency on this extension and no guard.
	 *
	 * Rows are primed, never trusted: they land in $topic_rows and still go
	 * through slug_title() before a title reaches a URL, so a caller that hands
	 * over rows its viewer may not read cannot leak a title through this. For the
	 * same reason no hint_post() is recorded here - hints bypass that gate, which
	 * is safe only for the core rowsets phpBB filtered itself. A p= link from an
	 * external rowset keeps its lookup unless the caller also puts t= on it.
	 *
	 * @param \phpbb\event\data $event
	 * @return void
	 */
	public function prime_external_rows($event): void
	{
		if (empty($this->config['stoker_urlrewrite_enable']))
		{
			return;
		}

		$post_ids = [];

		foreach ((array) $event['rows'] as $row)
		{
			if (!is_array($row))
			{
				continue;
			}

			// Collected before the checks below and resolved by one query rather
			// than one per row. This is not the caller being trusted: the ids only
			// decide which rows post_topic() loads from the database, and every
			// title still passes topic_readable() before it can reach a URL. A
			// shadow row is harmless here too - its last post belongs to the target
			// topic, which is the row the query reads anyway.
			foreach (['topic_last_post_id', 'post_id'] as $key)
			{
				if (!empty($row[$key]))
				{
					$post_ids[(int) $row[$key]] = (int) $row[$key];
				}
			}

			// topic_moved_id must be present AND zero. A shadow row carries the
			// target topic's data under the shadow's own id, so priming one slugs
			// one id with the other's title - and an absent column cannot be read
			// as "not a shadow".
			if (!isset($row['topic_moved_id']) || !empty($row['topic_moved_id']))
			{
				continue;
			}

			// A row without the visibility columns is worse than no row at all:
			// topic_readable() counts it as withheld, so priming it would not save
			// a query, it would drop the slug and emit the id-only URL. Skipping
			// leaves the lookup to run and the slug intact.
			if (!isset($row['topic_id'], $row['topic_title'], $row['forum_id'], $row['topic_visibility'], $row['topic_poster']))
			{
				continue;
			}

			$topic_id = (int) $row['topic_id'];

			if ($topic_id <= 0 || isset($this->topic_rows[$topic_id]))
			{
				continue;
			}

			$this->topic_rows[$topic_id] = [
				'topic_title' => (string) $row['topic_title'],
				'forum_id' => $row['forum_id'],
				'topic_visibility' => $row['topic_visibility'],
				'topic_poster' => $row['topic_poster'],
			];
		}

		if (!empty($post_ids))
		{
			$this->url_helper->prime_post_topics($post_ids);
		}
	}

	/**
	 * Rewrite the board URLs in an outgoing email.
	 *
	 * The notification types build U_VIEW_POST, U_TOPIC, U_VIEW_TOPIC and U_FORUM
	 * by concatenating generate_board_url() with a raw script URL (see
	 * phpbb\notification\type\post and \topic), so append_sid() never runs and
	 * rewrite_url() never sees them. This fires on the finished message body,
	 * after the email template has been parsed.
	 *
	 * Gated. core.modify_notification_message is not notification-only: it fires
	 * from messenger::send() for every message the board sends, including the
	 * ones whose body a user typed - {MESSAGE} in profile_send_email.txt,
	 * email_notify.txt and contact_admin.txt. phpbb\message\message::cc_sender()
	 * then puts that same rendered body in the sender's own inbox, so an ungated
	 * lookup would hand anyone the title of any topic id they cared to paste in
	 * as a raw viewtopic URL, read access or not.
	 *
	 * Testing this session is right rather than merely convenient: the queue
	 * stores the finished string (functions_messenger.php puts 'msg' => $this->msg
	 * after this event), so this never runs under cron, and on the notification
	 * paths the session belongs to the poster, moderator or reporter who just
	 * acted on the content - they hold f_read on it, so the slug survives.
	 *
	 * @param \phpbb\event\data $event
	 * @return void
	 */
	public function rewrite_notification_message($event): void
	{
		if (empty($this->config['stoker_urlrewrite_enable']))
		{
			return;
		}

		$message = (string) $event['message'];
		$fixed = $this->rewrite_board_urls($message);

		if ($fixed !== $message)
		{
			$event['message'] = $fixed;
		}
	}

	/**
	 * Rewrite the quoted-post link ucp_pm_compose.php builds.
	 *
	 * The link is concatenated as generate_board_url() . "/viewtopic.php?p=<id>",
	 * both inside the [url] BBCode and in the plain-text variant, so append_sid()
	 * never runs. It lands in the MESSAGE template variable, which is the text
	 * the composer is about to submit.
	 *
	 * Gated, unlike the email path: this runs in the composer's own session, so
	 * their permissions are the right ones to test.
	 *
	 * @param \phpbb\event\data $event
	 * @return void
	 */
	public function rewrite_pm_quote_link($event): void
	{
		if (empty($this->config['stoker_urlrewrite_enable']))
		{
			return;
		}

		$template_ary = $event['template_ary'];

		if (!is_array($template_ary) || empty($template_ary['MESSAGE']))
		{
			return;
		}

		$message = (string) $template_ary['MESSAGE'];
		$fixed = $this->rewrite_board_urls($message);

		if ($fixed !== $message)
		{
			$template_ary['MESSAGE'] = $fixed;
			$event['template_ary'] = $template_ary;
		}
	}

	/**
	 * Rewrite the forum links on the jumpbox rows.
	 *
	 * make_jumpbox() builds these with $phpbb_path_helper->append_url_params()
	 * rather than append_sid(), so rewrite_url() never sees them.
	 *
	 * tpl_ary is the list of block rows for this forum, not a single row: on the
	 * first iteration it holds two entries, because the "all forums"/"select
	 * forum" entry is prepended there, and one on every iteration after. Every
	 * entry has to be walked.
	 *
	 * @param \phpbb\event\data $event
	 * @return void
	 */
	public function rewrite_jumpbox_link($event): void
	{
		if ((defined('IN_ADMIN') && IN_ADMIN) || empty($this->config['stoker_urlrewrite_enable']))
		{
			return;
		}

		$tpl_ary = $event['tpl_ary'];

		if (!is_array($tpl_ary))
		{
			return;
		}

		$changed = false;

		foreach ($tpl_ary as $key => $row)
		{
			// The jumpbox is also built for mcp.php and other targets; only a
			// viewforum action routes to a friendly forum URL.
			if (!is_array($row) || empty($row['LINK'])
				|| strpos((string) $row['LINK'], 'viewforum.' . $this->php_ext) === false)
			{
				continue;
			}

			$query = $this->normalise_params(strstr((string) $row['LINK'], '?'));
			$forum_id = isset($query['f']) ? (int) $query['f'] : 0;

			// The "all forums" and "select forum" entries carry f=0, or no id at
			// all when make_jumpbox() was called without one. No read check:
			// make_jumpbox() has already filtered the list, and the forum name it
			// is about to display alongside this link is the very thing the slug
			// is built from - gating here would only produce a raw URL next to a
			// name that is shown regardless.
			if ($forum_id <= 0 || !$this->forum_exists($forum_id))
			{
				continue;
			}

			unset($query['f']);

			$tpl_ary[$key]['LINK'] = $this->board_path()
				. $this->url_helper->forum_path($forum_id, $this->get_forum_name($forum_id))
				. $this->build_query($query, true);

			$changed = true;
		}

		if ($changed)
		{
			$event['tpl_ary'] = $tpl_ary;
		}
	}

	/**
	 * Rewrite every absolute board URL in a block of text.
	 *
	 * Used by the paths where phpBB concatenates a URL instead of calling
	 * append_sid(). Only this board's own URLs are touched; a link to another
	 * board is left exactly as it was.
	 *
	 * This session's read permissions are always applied to the title: both
	 * callers run in a session whose permissions are the right ones to test -
	 * the composer's own on the PM path, and the poster's, moderator's or
	 * reporter's on the notification path.
	 *
	 * @param string $text
	 * @return string
	 */
	protected function rewrite_board_urls(string $text): string
	{
		if ($text === '' || (strpos($text, 'viewtopic.' . $this->php_ext) === false
			&& strpos($text, 'viewforum.' . $this->php_ext) === false))
		{
			return $text;
		}

		$board_url = generate_board_url();

		// The final character class refuses sentence punctuation, so a URL
		// written mid-sentence keeps its trailing '.' or ',' in the text instead
		// of losing it into the query string. Core's email templates put every
		// URL on a line of its own, but a custom or translated one need not.
		$pattern = '~' . preg_quote($board_url, '~') . '/(viewtopic|viewforum)\.'
			. preg_quote($this->php_ext, '~') . '\?[^\s<>"\'\]\)]*[^\s<>"\'\]\)\.,;:!?]~';

		$fixed = preg_replace_callback(
			$pattern,
			function (array $match) use ($board_url)
			{
				return $this->rewrite_absolute_url($match[0], $match[1], $board_url);
			},
			$text
		);

		return ($fixed !== null) ? $fixed : $text;
	}

	/**
	 * Friendly equivalent of one absolute board URL, or the original unchanged.
	 *
	 * @param string	$url
	 * @param string	$script		'viewtopic' or 'viewforum'
	 * @param string	$board_url
	 * @return string
	 */
	protected function rewrite_absolute_url(string $url, string $script, string $board_url): string
	{
		$anchor = '';
		$bare = $url;

		if (($pos = strpos($bare, '#')) !== false)
		{
			$anchor = substr($bare, $pos);
			$bare = substr($bare, 0, $pos);
		}

		$query = $this->normalise_params(strstr($bare, '?'));

		// Functional URLs depend on phpBB processing the raw request in place:
		// view=unread lands on a specific post, and the unsubscribe links in the
		// notification emails carry unwatch plus the uid that authorises it.
		// These are the same parameters redirect_canonical() refuses to
		// canonicalise, for the same reason.
		foreach (['view', 'watch', 'unwatch', 'mark', 'uid', 'e'] as $key)
		{
			if (isset($query[$key]))
			{
				return $url;
			}
		}

		$is_topic = ($script === 'viewtopic');
		$path = '';

		if ($is_topic)
		{
			$topic_id = isset($query['t']) ? (int) $query['t'] : 0;
			$post_id = isset($query['p']) ? (int) $query['p'] : 0;

			if ($post_id > 0)
			{
				$info = $this->url_helper->post_topic($post_id);

				if ($info === false || !$this->url_helper->topic_readable($info))
				{
					// Still worth the friendly id-only form: it routes to the
					// same post without disclosing the title.
					$path = $this->url_helper->post_path($post_id);
				}
				else
				{
					$path = $this->url_helper->post_path($post_id, (int) $info['topic_id'], (string) $info['topic_title']);
				}
			}
			else if ($topic_id > 0)
			{
				$path = $this->url_helper->topic_path($topic_id, $this->slug_title($topic_id));
			}
		}
		else
		{
			$forum_id = isset($query['f']) ? (int) $query['f'] : 0;

			if ($forum_id > 0 && $this->forum_exists($forum_id) && $this->can_read_forum($forum_id))
			{
				$path = $this->url_helper->forum_path($forum_id, $this->get_forum_name($forum_id));
			}
		}

		if ($path === '')
		{
			return $url;
		}

		unset($query['f'], $query['t'], $query['p']);

		return $board_url . '/' . $path . $this->build_query($query, false) . $anchor;
	}

	/**
	 * Record the topic a post belongs to, without querying for it.
	 *
	 * @param int		$post_id
	 * @param int		$topic_id
	 * @param string	$topic_title
	 * @return void
	 */
	protected function hint_post(int $post_id, int $topic_id, string $topic_title): void
	{
		if ($post_id > 0 && $topic_id > 0 && $topic_title !== '' && !isset($this->post_topic_hints[$post_id]))
		{
			$this->post_topic_hints[$post_id] = [
				'topic_id' => $topic_id,
				'topic_title' => $topic_title,
			];
		}
	}

	/**
	 * Re-apply the $_EXTRA_URL params and session id that append_sid() skips.
	 *
	 * @param array			$query
	 * @param bool|string	$session_id
	 * @return array
	 */
	protected function apply_session(array $query, $session_id): array
	{
		global $_SID, $_EXTRA_URL;

		// isset(): append_sid() can run before session_begin() populates $_SID.
		$sid = ($session_id !== false) ? (string) $session_id : (isset($_SID) ? (string) $_SID : '');

		// Only fall back to a caller-supplied sid when phpBB has none of its own.
		if ($sid === '' && isset($query['sid']))
		{
			$sid = (string) $query['sid'];
		}

		unset($query['sid']);

		if (!empty($_EXTRA_URL))
		{
			$extra = [];
			parse_str(implode('&', $_EXTRA_URL), $extra);
			$query = array_merge($extra, $query);
		}

		if ($sid !== '')
		{
			$query['sid'] = $sid;
		}

		return $query;
	}

	/**
	 * Scheme and authority of an absolute url, or an empty string when relative.
	 *
	 * @param string $url
	 * @return string
	 */
	protected function absolute_prefix(string $url): string
	{
		if (strpos($url, '//') === false)
		{
			return '';
		}

		return preg_match('~^(?:[a-z][a-z0-9+.-]*:)?//[^/?#]+~i', $url, $match) ? $match[0] : '';
	}

	/**
	 * Root-relative path to the board, with trailing slash (cached per request).
	 *
	 * @return string
	 */
	protected function board_path(): string
	{
		if ($this->board_path === null)
		{
			$path = (string) parse_url(generate_board_url(), PHP_URL_PATH);
			$this->board_path = rtrim($path, '/') . '/';
		}

		return $this->board_path;
	}

	/**
	 * Extract an existing anchor from the url or string params.
	 *
	 * @param string	$url
	 * @param mixed		$params
	 * @return string
	 */
	protected function extract_anchor(string $url, $params): string
	{
		if (($pos = strpos($url, '#')) !== false)
		{
			return substr($url, $pos);
		}

		if (is_string($params) && ($pos = strpos($params, '#')) !== false)
		{
			return substr($params, $pos);
		}

		if (is_array($params) && isset($params['#']))
		{
			return '#' . $params['#'];
		}

		return '';
	}

	/**
	 * Normalise append_sid params (string or array) into an associative array.
	 *
	 * @param mixed $params
	 * @return array
	 */
	protected function normalise_params($params): array
	{
		if (is_array($params))
		{
			// append_sid() writes an array param as "$key=$value" and encodes
			// nothing, so the value arrives already in wire form. build_query()
			// puts the result through http_build_query(), which does encode, so
			// decode here to survive the round trip rather than be encoded twice
			// - "a+b" would otherwise reach the URL as "a%2Bb".
			$query = [];

			foreach ($params as $key => $value)
			{
				$query[$key] = is_string($value) ? urldecode($value) : $value;
			}

			return $query;
		}

		$params = str_replace('&amp;', '&', (string) $params);
		$params = ltrim($params, '?');

		if (($pos = strpos($params, '#')) !== false)
		{
			$params = substr($params, 0, $pos);
		}

		$query = [];
		parse_str($params, $query);

		return $query;
	}

	/**
	 * Rebuild a leftover query string with the correct separator.
	 *
	 * @param array	$query
	 * @param bool	$is_amp
	 * @return string
	 */
	protected function build_query(array $query, bool $is_amp): string
	{
		if (empty($query))
		{
			return '';
		}

		$string = http_build_query($query, '', $is_amp ? '&amp;' : '&');

		return ($string !== '') ? '?' . $string : '';
	}

	/**
	 * Reduce leftover query params to those worth carrying onto a friendly URL:
	 * pagination and sort, plus search highlight on topics. Routing ids, session
	 * id, style and mangled keys are dropped - they do not belong in the URL.
	 * Used for incoming-hit redirects; generated links keep their functional
	 * params via the rewrite path instead.
	 *
	 * The sort keys are carried rather than dropped because start is relative to
	 * them: sk/sd re-order the set start counts into, and st (sort_days) filters
	 * it outright, so dropping them while keeping start points the redirect at
	 * different content - and with st, at an offset that can be past the end of
	 * the default set. Consolidating the sort variants is U_CANONICAL's job, and
	 * fix_canonical() already emits it on every one of these pages; a 301 must
	 * still resolve to the resource that was asked for.
	 *
	 * @param array	$query
	 * @param bool	$is_topic
	 * @return array
	 */
	protected function filter_leftover(array $query, bool $is_topic): array
	{
		$allowed = $is_topic ? ['start', 'hilit', 'sk', 'sd', 'st'] : ['start', 'sk', 'sd', 'st'];
		$out = [];

		foreach ($allowed as $key)
		{
			if (!isset($query[$key]) || $query[$key] === '')
			{
				continue;
			}

			if ($key === 'start')
			{
				$start = (int) $query[$key];

				if ($start <= 0)
				{
					continue;
				}

				// Emit the cast value - "10abc" passes the check above and must not
				// reach the Location header verbatim.
				$out[$key] = $start;
				continue;
			}

			$out[$key] = $query[$key];
		}

		return $out;
	}

	/**
	 * @param array $query
	 * @return string
	 */
	protected function topic_route(array $query): string
	{
		$topic_id = isset($query['t']) ? (int) $query['t'] : 0;
		$post_id = isset($query['p']) ? (int) $query['p'] : 0;

		if ($post_id > 0)
		{
			// The page emitting this link withheld the post's subject, so the
			// title must not reappear as a slug. The id-only form routes to
			// exactly the same place.
			if (isset($this->suppress_slug[$post_id]))
			{
				return $this->url_helper->post_path($post_id);
			}

			$title = ($topic_id > 0) ? $this->slug_title($topic_id) : '';

			// Primed by the prime_* handlers from data the current page already
			// loaded, which is the common case: phpBB emits p=-only links for every
			// post row, topic row and search hit on the page.
			if ($title === '' && isset($this->post_topic_hints[$post_id]))
			{
				$topic_id = (int) $this->post_topic_hints[$post_id]['topic_id'];
				$title = (string) $this->post_topic_hints[$post_id]['topic_title'];
			}

			if ($title === '')
			{
				$info = $this->url_helper->post_topic($post_id);

				// Withheld: fall through with an empty title, which routes to the
				// same place in the id-only form.
				if ($info !== false && $this->url_helper->topic_readable($info))
				{
					$topic_id = (int) $info['topic_id'];
					$title = (string) $info['topic_title'];
				}
			}

			return $this->url_helper->post_path($post_id, $topic_id, $title);
		}

		if ($topic_id > 0)
		{
			return $this->url_helper->topic_path($topic_id, $this->slug_title($topic_id));
		}

		return '';
	}

	/**
	 * @param array $query
	 * @return string
	 */
	protected function forum_route(array $query): string
	{
		$forum_id = isset($query['f']) ? (int) $query['f'] : 0;

		return ($forum_id > 0) ? $this->url_helper->forum_path($forum_id, $this->get_forum_name($forum_id)) : '';
	}

	/**
	 * @return void
	 */
	protected function load_forums(): void
	{
		if ($this->forums_loaded)
		{
			return;
		}

		$sql = 'SELECT forum_id, forum_name FROM ' . FORUMS_TABLE;
		$result = $this->db->sql_query($sql, 7200);

		while ($row = $this->db->sql_fetchrow($result))
		{
			$this->forum_names[(int) $row['forum_id']] = (string) $row['forum_name'];
		}

		$this->db->sql_freeresult($result);
		$this->forums_loaded = true;
	}

	/**
	 * @param int $forum_id
	 * @return bool
	 */
	protected function forum_exists(int $forum_id): bool
	{
		$this->load_forums();

		return isset($this->forum_names[$forum_id]);
	}

	/**
	 * Read access to a forum: f_read plus the forum password gate.
	 *
	 * @param int $forum_id
	 * @return bool
	 */
	protected function can_read_forum(int $forum_id): bool
	{
		return $this->url_helper->forum_readable($forum_id);
	}

	/**
	 * Read access to a topic: the forum gate above plus content visibility, so a
	 * title phpBB would withhold behind NO_TOPIC never becomes a slug.
	 *
	 * @param int $topic_id
	 * @return bool
	 */
	protected function can_read_topic(int $topic_id): bool
	{
		$row = $this->get_topic_row($topic_id);

		return ($row !== false) && $this->url_helper->topic_readable($row);
	}

	/**
	 * @param int $forum_id
	 * @return string
	 */
	protected function get_forum_name(int $forum_id): string
	{
		$this->load_forums();

		return $this->forum_names[$forum_id] ?? '';
	}

	/**
	 * Title and owning forum of a topic, or false when it does not exist.
	 *
	 * @param int $topic_id
	 * @return array|false
	 */
	protected function get_topic_row(int $topic_id)
	{
		if (isset($this->topic_rows[$topic_id]))
		{
			return $this->topic_rows[$topic_id];
		}

		// Primary-key lookup, memoised above for the request: caching it in the SQL
		// cache would write one cache entry per topic id for a cosmetic slug.
		$sql = 'SELECT topic_title, forum_id, topic_visibility, topic_poster
			FROM ' . TOPICS_TABLE . '
			WHERE topic_id = ' . (int) $topic_id;
		$result = $this->db->sql_query($sql);
		$row = $this->db->sql_fetchrow($result);
		$this->db->sql_freeresult($result);

		return $this->topic_rows[$topic_id] = ($row !== false) ? $row : false;
	}

	/**
	 * Title for a generated link's slug, or an empty string when this visitor may
	 * not see it - post_path()/topic_path() then emit the id-only form, which
	 * routes to exactly the same place.
	 *
	 * @param int $topic_id
	 * @return string
	 */
	protected function slug_title(int $topic_id): string
	{
		// Primed from a rowset phpBB filtered itself (search results), so the
		// title is one this visitor has already been shown on this very page.
		if (isset($this->topic_titles[$topic_id]))
		{
			return $this->topic_titles[$topic_id];
		}

		$row = $this->get_topic_row($topic_id);

		return ($row !== false && $this->url_helper->topic_readable($row)) ? (string) $row['topic_title'] : '';
	}

	/**
	 * Raw title, for the paths that have already applied their own gate.
	 *
	 * @param int $topic_id
	 * @return string
	 */
	protected function get_topic_title(int $topic_id): string
	{
		// A primed title answers the slug without a query. The row is still read
		// on demand by can_read_topic(), which needs a forum_id this never carries.
		if (isset($this->topic_titles[$topic_id]))
		{
			return $this->topic_titles[$topic_id];
		}

		$row = $this->get_topic_row($topic_id);

		return ($row !== false) ? (string) $row['topic_title'] : '';
	}
}
