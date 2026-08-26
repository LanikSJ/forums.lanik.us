<?php
/**
 *
 * stoker/agreementdelay
 *
 * @package stoker\agreementdelay
 * @copyright (c) 2026 stoker
 * @license GPL-2.0-only
 *
 */

namespace stoker\agreementdelay\event;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use phpbb\config\config;
use phpbb\request\request;
use phpbb\language\language;
use phpbb\user;

class main_listener implements EventSubscriberInterface
{
	/** @var config */
	protected $config;

	/** @var request */
	protected $request;

	/** @var language */
	protected $language;

	/** @var user */
	protected $user;

	/**
	 * Constructor
	 *
	 * @param config	$config
	 * @param request	$request
	 * @param language	$language
	 * @param user		$user
	 */
	public function __construct(config $config, request $request, language $language, user $user)
	{
		$this->config = $config;
		$this->request = $request;
		$this->language = $language;
		$this->user = $user;
	}

	/**
	 * Assign functions to be called on corresponding events
	 *
	 * @return array
	 */
	public static function getSubscribedEvents()
	{
		return [
			'core.ucp_register_agreement_modify_template_data' => 'inject_hidden_fields',
			'core.ucp_register_requests_after' => 'validate_submission',
			'core.acp_board_config_edit_add' => 'acp_board_config_edit_add',
		];
	}

	/**
	 * Inject hidden timestamp and hash fields into agreement form
	 *
	 * @param \phpbb\event\data $event
	 * @return void
	 */
	public function inject_hidden_fields($event)
	{
		$timestamp = time();
		$hash = hash_hmac('sha256', $timestamp . $this->user->session_id, $this->config['agreementdelay_secret']);

		$s_hidden_fields = $event['s_hidden_fields'];
		$s_hidden_fields['agreement_delay_timestamp'] = $timestamp;
		$s_hidden_fields['agreement_delay_hash'] = $hash;
		$event['s_hidden_fields'] = $s_hidden_fields;

		$template_vars = $event['template_vars'];
		$template_vars['S_AGREEMENTDELAY_NOTICE'] = true;
		$template_vars['AGREEMENTDELAY_SECONDS'] = (int) $this->config['agreementdelay_seconds'] ?: 15;
		$event['template_vars'] = $template_vars;
	}

	 
	/**
	 * Validate timestamp and hash on agreement submission
	 *
	 * @param \phpbb\event\data $event
	 * @return void
	 */
	public function validate_submission($event)
	{
		// Only validate on agreement page submission, not registration form submission
		if (!$event['agreed'] || $event['submit'])
		{
			return;
		}

		$timestamp = $this->request->variable('agreement_delay_timestamp', 0);
		$hash = $this->request->variable('agreement_delay_hash', '');
		
		// Early exit if invalid data
		if (!$timestamp || !$hash)
		{
			$event['agreed'] = false;
			return;
		}

		$delay = (int) $this->config['agreementdelay_seconds'] ?: 15;
		
		// Check time first (cheaper than hash comparison)
		if ((time() - $timestamp) < $delay)
		{
			$event['agreed'] = false;
			return;
		}

		$expected_hash = hash_hmac('sha256', $timestamp . $this->user->session_id, $this->config['agreementdelay_secret']);

		if (!hash_equals($expected_hash, $hash))
		{
			$event['agreed'] = false;
		}
	}

	/**
	 * Add countdown seconds config to board registration settings
	 *
	 * @param \phpbb\event\data $event
	 * @return void
	 */
	public function acp_board_config_edit_add($event)
	{
		if ($event['mode'] === 'registration')
		{
			$this->language->add_lang('common', 'stoker/agreementdelay');
			
			$display_vars = $event['display_vars'];
			$new_vars = [];

			foreach ($display_vars['vars'] as $key => $value)
			{
				$new_vars[$key] = $value;

				if ($key === 'chg_passforce')
				{
					$new_vars['agreementdelay_seconds'] = [
						'lang'		=> 'AGREEMENTDELAY_SECONDS',
						'validate'	=> 'int:5:120',
						'type'		=> 'number:5:120',
						'explain'	=> true,
						'append'	=> ' ' . $this->language->lang('AD_SECONDS'),
					];
				}
			}

			$display_vars['vars'] = $new_vars;
			$event['display_vars'] = $display_vars;
		}
	}
}