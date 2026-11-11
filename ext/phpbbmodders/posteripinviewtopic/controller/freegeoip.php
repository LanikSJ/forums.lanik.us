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

namespace phpbbmodders\posteripinviewtopic\controller;

/**
 * @ignore
 */
use phpbb\exception\http_exception;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * Poster IP in Viewtopic IP lookup controller
 */
class freegeoip
{
	/** @var \phpbb\auth\auth */
	protected $auth;

	/** @var \phpbb\db\driver\driver_interface */
	protected $db;

	/** @var \phpbb\language\language */
	protected $language;

	/** @var \phpbb\request\request */
	protected $request;

	/** @var array */
	protected $tables;

	/** @var string */
	protected $err = '';

	/**
	 * Constructor
	 *
	 * @param \phpbb\auth\auth                   $auth
	 * @param \phpbb\db\driver\driver_interface  $db
	 * @param \phpbb\language\language           $language
	 * @param \phpbb\request\request             $request
	 * @param array                              $tables
	 *
	 * @access public
	 */
	public function __construct(\phpbb\auth\auth $auth, \phpbb\db\driver\driver_interface $db, \phpbb\language\language $language, \phpbb\request\request $request, $tables)
	{
		$this->auth = $auth;
		$this->db = $db;
		$this->language = $language;
		$this->request = $request;
		$this->tables = $tables;
	}

	/**
	 * Populate a popup to display information retrieved
	 *
	 * @param $post_id
	 * @return json response
	 * @access public
	 */
	public function freegeoip($post_id = 0)
	{
		if ($this->request->is_ajax())
		{
			$this->language->add_lang('common', 'phpbbmodders/posteripinviewtopic');
			$this->language->add_lang('mcp');

			$sql= 'SELECT poster_ip, forum_id
				FROM ' . $this->tables['posts'] . '
				WHERE post_id = ' . (int) $post_id;
			$result = $this->db->sql_query_limit($sql, 1);
			$row = $this->db->sql_fetchrow($result);
			$this->db->sql_freeresult($result);

			// Sanity check in case someone is just inserting post_ids at random
			if (!$row)
			{
				throw new http_exception(404, $this->language->lang('POST_NOT_EXIST'));
			}

			// Same check as the listener: admins, global moderators, or moderators of the post's forum
			if (!$this->auth->acl_gets('a_', 'm_') && !$this->auth->acl_get('m_', (int) $row['forum_id']))
			{
				throw new http_exception(403, $this->language->lang('NOT_AUTHORISED'));
			}

			$response = $this->freegeoip_api($row['poster_ip']);

			if (!empty($this->err))
			{
				$data = [
					'MESSAGE_TITLE'	=> $this->language->lang('ERROR'),
					'MESSAGE_TEXT'	=> $this->language->lang('ERROR_FROM_SERVER', $this->err),
				];

				$json_response = new JsonResponse($data);

				return $json_response;
			}

			$json_decode = json_decode($response, true);
			$message = '';

			foreach ($json_decode as $key => $value)
			{
				if (!in_array($key, ['as', 'org', 'status']) && !empty($value))
				{
					$message .= '<pre>' . $this->language->lang('PIPIV_' . strtoupper($key), ucfirst($value)) . '</pre>';
				}
			}

			$data = [
				'MESSAGE_TITLE'	=> $this->language->lang('PAGE_TITLE'),
				'MESSAGE_TEXT'	=> $message,

				'success'	=> true,
			];

			$json_response = new JsonResponse($data);

			return $json_response;
		}

		throw new http_exception(405, $this->language->lang('EXTENSION_REQUIRES_JAVASCRIPT'));
	}

	/**
	 * Query the freegoip database
	 *
	 * @param $posterip
	 * @return string
	 * @access private
	 */
	private function freegeoip_api($poster_ip)
	{
		$url = 'http://ip-api.com/json/' . $poster_ip;

		$curl = curl_init();

		curl_setopt_array($curl, [
			CURLOPT_URL => $url,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_ENCODING => "",
			CURLOPT_MAXREDIRS => 10,
			CURLOPT_TIMEOUT => 30,
			CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
			CURLOPT_CUSTOMREQUEST => "GET",
			CURLOPT_HTTPHEADER => [
				"accept: application/json",
				"content-type: application/json"
			],
		]);

		$response = curl_exec($curl);
		$err = curl_error($curl);
		curl_close($curl);

		if (!empty($err))
		{
			$this->err = $err;
		}
		else
		{
			return $response;
		}
	}
}
