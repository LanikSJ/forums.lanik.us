<?php
/**
*
* @package phpBB Extension - Spamsecure from 69bruno
* @copyright (c) 2021 (cruiser-lounge.de)
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

namespace bruno\spamsecure\event;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class listener implements EventSubscriberInterface
{

	const SP_KYRILL		=	'а,б,в,г,д,е,ё,ж,з,и,й,к,л,м,н,о,п,р,с,т,у,ф,х,ц,ч,ш,щ,ъ,ы,ь,э,ю,я,';
	const SP_CHINESE	=	'阿,玻,疵,的,鹅,佛,哥,哈,衣,基,科,勒,摸,呢,喔,坡,七,日,丝,特,乌,维,西,一,资,迂,';
	const SP_HINDI		=	'अ,आ,इ,ई,उ,ऊ,ए,ऐ,ओ,औ,अं,अः,ऋ,ॠा,ि,ी,ु,ू,ृ,ॄ,ॅ,ॆ,े,ै,ॉ,ॊ,ो,ौ,क,ख,ग,घ,ङ,च,छ,ज,झ,ञ,ट,ठ,ड,ढ,ण,त,थ,द,ध,न,प,फ,ब,भ,म,य,र,ल,व,श,ष,स,ह,क्ष,त्र,ज्ञ,०,१,२,३,४,५,६,७,८,९,';
	const SP_NO_LINKS	=	'((((?i)http|(?i)https):\/\/)|((?i)www\.))[a-zA-Z0-9\-\.]+\.[a-zA-Z]{2,}(:[0-9]+)?(\/\S*)?';

	protected $user;
	protected $language;
	protected $config;
	protected $auth;
	protected $request;
	protected $template;

	public function __construct(
		\phpbb\user $user,
		\phpbb\language\language $language,
		\phpbb\config\config $config,
		\phpbb\auth\auth $auth,
		\phpbb\request\request $request,
		\phpbb\template\template $template
	)
	{
		$this->user 	= $user;
		$this->language = $language;
		$this->config   = $config;
		$this->auth 	= $auth;
		$this->request	= $request;
		$this->template	= $template;
	}

	public static function getSubscribedEvents()
	{
		return [
			'core.user_setup'								=> 'load_language_on_setup',
			'core.page_header_after'						=> 'permission_for_url_bbcode',
			'core.permissions'								=> 'permissions',
			'core.message_parser_check_message'				=> 'show_spamsecure_message',
			'core.message_admin_form_submit_before'			=> 'show_spamsecure_original_phpbb_contact_form',
			'rmcgirr83.contactadmin.modify_data_and_error'	=> 'show_spamsecure_rmcgirr83_contactadmin_contact_form',
		];
	}

	public function load_language_on_setup($event)
	{
		$lang_set_ext 	= $event['lang_set_ext'];
		$lang_set_ext[] = [
			'ext_name'  => 'bruno/spamsecure',
			'lang_set'  => 'spamsecure',
		];

		$event['lang_set_ext'] = $lang_set_ext;
	}

	public function permissions($event)
	{
		$permissions = $event['permissions'];
		$permissions['u_view_spamsecure_invalid_chars'] = ['lang' => 'ACL_U_VIEW_SPAMSECURE_INVALID_CHARS', 'cat' => 'misc'];
		$permissions['u_view_spamsecure_invalid_regex'] = ['lang' => 'ACL_U_VIEW_SPAMSECURE_INVALID_REGEX', 'cat' => 'misc'];
		$event['permissions'] = $permissions;
	}

	public function permission_for_url_bbcode()
	{
		$this->template->assign_vars([
			'S_LINKS_ALLOWED'	=> (!$this->auth->acl_get('u_view_spamsecure_invalid_regex') && $this->config['spamsecure_no_links']) ? false : true,
		]);
	}

	public function show_spamsecure_message($event)
	{
		$for_check = [
			'username'	=> $this->request->variable('username', '', true),
			'subject'	=> $this->request->variable('subject','', true),
			'email'		=> '',
			'email2'	=> '',
			'message'	=> $event['message'],
		];

		$warn_msg = $this->spamsecure_core($for_check);

		if ($warn_msg)
		{
			$event['warn_msg'] = $warn_msg;
		}
	}


	public function show_spamsecure_original_phpbb_contact_form($event)
	{
		$for_check = [
			'username'	=> $this->request->variable('name', '', true),
			'subject'	=> $event['subject'],
			'email'		=> $this->request->variable('email','', true),
			'email2'	=> '',
			'message'	=> $event['body'],
		];

		$warn_msg = $this->spamsecure_core($for_check);

		if (count($warn_msg))
		{
			$event['errors'] = $warn_msg;
		}
	}

	public function show_spamsecure_rmcgirr83_contactadmin_contact_form($event)
	{
		$for_check = [
			'username'	=> $event['data']['username'],
			'subject'	=> $event['data']['contact_subject'],
			'email'		=> $event['data']['email'],
			'email2'	=> $event['data']['email_confirm'],
			'message'	=> $event['data']['contact_message'],
		];

		$warn_msg = $this->spamsecure_core($for_check);

		if (count($warn_msg))
		{
			$event['error'] = $warn_msg;
		}
	}

	public function spamsecure_core($for_check)
	{
		$warn_msg = [];
		$all_fields_for_check = implode($for_check);

		if (!$this->auth->acl_get('u_view_spamsecure_invalid_chars'))
		{
			$searchchars = ($this->config['spamsecure_invalid_chars_lang_kyrill'] 	== 1 ? self::SP_KYRILL : '')
						 . ($this->config['spamsecure_invalid_chars_lang_chinese'] 	== 1 ? self::SP_CHINESE : '' )
						 . ($this->config['spamsecure_invalid_chars_lang_hindi'] 	== 1 ? self::SP_HINDI : '')
						 . ($this->config['spamsecure_invalid_chars_lang_custom'] 	== 1 ? $this->config['spamsecure_invalid_chars_input_custom'] : '');
			$searchchars = trim($searchchars, ',');
			$counter = 0;
			$checkarray = explode(',', $searchchars);
			$acp_counter = $this->config['spamsecure_invalid_chars_counter'];
			$matches = [];

			if (!empty($searchchars))
			{
				foreach ($checkarray as $checkchar)
				{
					if ($this->config['spamsecure_search_complete'])
					{
						if (strpos($all_fields_for_check, $checkchar, 0) !== false)
						{
							$counter++;
							$matches[] = $checkchar;
						}
					}
					else
					{
						if (strpos($for_check['message'], $checkchar, 0) !== false)
						{
							$counter++;
							$matches[] = $checkchar;
						}
					}
				}
			}

			if ($counter <= $acp_counter && $counter  >= 1)
			{
				if ($this->config['spamsecure_invalid_chars_counter_switch'] == 1)
				{
					if ($this->config['spamsecure_search_complete'])
					{
						$warn_msg[] = ($this->language->lang('SPAMSECURE_INVALID_CHARS_WARNING_COMPLETE') . $counter . $this->language->lang('SPAMSECURE_INVALID_CHARS_INFO') . implode(',', $matches));
					}
					else
					{
						$warn_msg[] = ($this->language->lang('SPAMSECURE_INVALID_CHARS_WARNING') . $counter . $this->language->lang('SPAMSECURE_INVALID_CHARS_INFO') . implode(',', $matches));
					}
				}
				else
				{
					if ($this->config['spamsecure_search_complete'])
					{
						$warn_msg[] = ($this->language->lang('SPAMSECURE_INVALID_CHARS_WARNING_COMPLETE') . $counter);
					}
					else
					{
						$warn_msg[] = ($this->language->lang('SPAMSECURE_INVALID_CHARS_WARNING') . $counter);
					}
				}
			}
			else if ($counter > $acp_counter)
			{
				$warn_msg[] = ($this->language->lang('SPAMSECURE_INVALID_CHARS_WARNING') . $counter);
			}
		}

		if (!$this->auth->acl_get('u_view_spamsecure_invalid_regex'))
		{
			$searchstrg 	= '';

			$custom_regex	= ltrim($this->config['spamsecure_invalid_regex'],  '/');

			if ($this->config['spamsecure_no_links'] == 1 && $this->config['spamsecure_regex_individual_check']	== 0)
			{
				$searchstrg = '/' . self::SP_NO_LINKS . '/i';
			}
			else if ($this->config['spamsecure_no_links'] == 1 && $this->config['spamsecure_regex_individual_check']	== 1)
			{
				$searchstrg = '/' . self::SP_NO_LINKS . '|' . $custom_regex;
			}
			else if ($this->config['spamsecure_no_links'] == 0 && $this->config['spamsecure_regex_individual_check']	== 1)
			{
				$searchstrg = '/' . $custom_regex;
			}

			if (!empty($searchstrg))
			{
				if ($this->config['spamsecure_search_complete'])
				{
					if (preg_match($searchstrg, $all_fields_for_check))
					{
						$warn_msg[] = ($this->language->lang('SPAMSECURE_INVALID_WARNING_ALL_FIELDS'));
					}
				}
				else
				{
					if (preg_match($searchstrg, $for_check['message']))
					{
						$warn_msg[] = ($this->language->lang('SPAMSECURE_INVALID_REGEX_WARNING'));
					}
				}
			}
		}
		return $warn_msg;
	}
}
