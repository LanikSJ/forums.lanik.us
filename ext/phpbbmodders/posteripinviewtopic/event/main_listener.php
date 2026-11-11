<?php
/**
 *
 * Poster IP in Viewtopic extension for the phpBB Forum Software package
 *
 * @copyright (c) 2026, phpBB Modders, https://www.phpbbmodders.com/
 * @copyright (c) 2016, Rich McGirr (RMcGirr83)
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace phpbbmodders\posteripinviewtopic\event;

/**
 * @ignore
 */
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Poster IP in Viewtopic event listener
 */
class main_listener implements EventSubscriberInterface
{
	/** @var \phpbb\auth\auth */
	protected $auth;

	/** @var \phpbb\language\language */
	protected $language;

	/** @var \phpbb\template\template */
	protected $template;

	/** @var \phpbb\controller\helper */
	protected $helper;

	/**
	 * Constructor
	 *
	 * @param \phpbb\auth\auth          $auth
	 * @param \phpbb\language\language  $language
	 * @param \phpbb\template\template  $template
	 * @param \phpbb\controller\helper  $helper
	 *
	 * @access public
	 */
	public function __construct(\phpbb\auth\auth $auth, \phpbb\language\language $language, \phpbb\template\template $template, \phpbb\controller\helper $helper)
	{
		$this->auth = $auth;
		$this->language = $language;
		$this->template = $template;
		$this->helper = $helper;
	}

	/**
	 * Map phpBB core events to the listener methods that should handle those events
	 *
	 * @return array
	 */
	public static function getSubscribedEvents()
	{
		return [
			'core.viewtopic_modify_post_row'	=> 'viewtopic_modify_post_row',
			'core.viewtopic_post_rowset_data'	=> 'viewtopic_post_rowset_data',
		];
	}

	/**
	 * Display posterip on each topic
	 *
	 * @param \phpbb\event\data $event
	 * @access public
	 */
	public function viewtopic_modify_post_row($event)
	{
		// We only use poster_ip due to the anonymizer extension
		$poster_ip = $event['row']['poster_ip'];

		// Used for checking auths of forum mods
		$forum_id = $event['row']['forum_id'];

		// Pass the post_id to the controller
		$post_id = $event['row']['post_id'];

		if (($this->auth->acl_gets('a_', 'm_') || $this->auth->acl_get('m_', (int) $forum_id)) && (!empty($poster_ip) && $poster_ip != '127.0.0.1'))
		{
			$query_url = $this->helper->route('phpbbmodders_posteripinviewtopic_controller_freegeoip', ['post_id' => (int) $post_id]);

			$event['post_row'] = array_merge($event['post_row'], [
				'POSTER_IP_VISIBLE'	=> true,
				'POSTER_IP'			=> $poster_ip,

				'QUERY_URL'	=> $query_url,
			]);
		}
	}

	/**
	 * Add posterip into rowset
	 *
	 * @param \phpbb\event\data $event
	 * @access public
	 */
	public function viewtopic_post_rowset_data($event)
	{
		$row = $event['row'];

		$event->update_subarray('rowset_data', 'poster_ip', $row['poster_ip']);
	}
}
