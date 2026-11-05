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
 * Serve the member live search from pseudonyms instead of real usernames.
 *
 * memberlist.php?mode=livesearch answers with JSON built straight from the
 * username column:
 *
 *   'result' => html_entity_decode($row['username']),
 *
 * Only username_full and display go through get_username_string(), so the
 * username hook cannot reach 'result', and the query itself matches on
 * username_clean — meaning a guest could both read real names and confirm that
 * one exists. That branch of memberlist.php fires no event, so there is nothing
 * to filter after the fact.
 *
 * Instead the request is answered before memberlist.php reaches it, from
 * core.user_setup_after — the last hook that runs before the mode switch. Only
 * disguised viewers (guests and bots) are intercepted; for everyone else phpBB
 * carries on and answers normally. The two gates memberlist.php puts in front
 * of live search are re-checked here, and if either says no we simply stand
 * aside and let core produce its own error or login box.
 */
class livesearch_listener implements EventSubscriberInterface
{
	/** Matches core's own live-search cap. */
	const LIMIT = 10;

	/** @var viewer */
	protected $viewer;

	/** @var pseudonym_manager */
	protected $manager;

	/** @var \phpbb\config\config */
	protected $config;

	/** @var \phpbb\user */
	protected $user;

	/** @var \phpbb\auth\auth */
	protected $auth;

	/** @var \phpbb\request\request_interface */
	protected $request;

	/** @var string */
	protected $php_ext;

	/**
	 * @param viewer                           $viewer
	 * @param pseudonym_manager                $manager
	 * @param \phpbb\config\config             $config
	 * @param \phpbb\user                      $user
	 * @param \phpbb\auth\auth                 $auth
	 * @param \phpbb\request\request_interface $request
	 * @param string                           $php_ext
	 */
	public function __construct(viewer $viewer, pseudonym_manager $manager, \phpbb\config\config $config, \phpbb\user $user, \phpbb\auth\auth $auth, \phpbb\request\request_interface $request, $php_ext)
	{
		$this->viewer = $viewer;
		$this->manager = $manager;
		$this->config = $config;
		$this->user = $user;
		$this->auth = $auth;
		$this->request = $request;
		$this->php_ext = $php_ext;
	}

	/**
	 * @return array
	 */
	public static function getSubscribedEvents()
	{
		return [
			'core.user_setup_after' => 'answer_livesearch',
		];
	}

	/**
	 * Answer a disguised viewer's live-search request with pseudonyms, and end
	 * the request. Anything that is not exactly a live-search request from a
	 * disguised viewer falls through untouched.
	 */
	public function answer_livesearch()
	{
		if (!$this->is_disguised_livesearch())
		{
			return;
		}

		$chars = $this->request->variable('username', '', true);
		$results = [];

		foreach ($this->manager->search_display_names($chars, [USER_NORMAL, USER_FOUNDER], self::LIMIT) as $row)
		{
			$user_id = (int) $row['user_id'];
			$name = (string) $row['display_name'];

			$results[] = [
				'user_id'		=> $user_id,
				'result'		=> html_entity_decode($name),
				'username_full'	=> get_username_string('full', $user_id, $name, $row['user_colour']),
				'display'		=> get_username_string('no_profile', $user_id, $name, $row['user_colour']),
			];
		}

		$json_response = new \phpbb\json_response();
		$json_response->send([
			'keyword' => $chars,
			'results' => $results,
		]);
	}

	/**
	 * @return bool True if this request is a live search that must be disguised.
	 */
	protected function is_disguised_livesearch()
	{
		if ($this->request->variable('mode', '') !== 'livesearch')
		{
			return false;
		}

		// user_setup_after fires on every page; only memberlist.php serves live search.
		if (empty($this->user->page['page_name']) || $this->user->page['page_name'] !== 'memberlist.' . $this->php_ext)
		{
			return false;
		}

		if (!$this->viewer->is_disguised())
		{
			return false;
		}

		// Both gates memberlist.php applies before it answers. If either fails,
		// stand aside so core shows its own error / login box.
		if (empty($this->config['allow_live_searches']))
		{
			return false;
		}

		return (bool) $this->auth->acl_gets('u_viewprofile', 'a_user', 'a_useradd', 'a_userdel');
	}
}
