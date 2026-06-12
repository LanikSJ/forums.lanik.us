<?php
/**
*
* @package VC
* @version $Id: phpbb_keycaptcha 2011-01-21 KeyCAPTCHA $
* @copyright (c) 2010 ITNP, Jsc (https://www.keycaptcha.com). All rights reserved.
* @license http://opensource.org/licenses/gpl-license.php GNU General Public License v2
* This software is designed to work with phpBB versions from 3.0.6 to 3.0.8
*
*/

if(!defined( 'IN_PHPBB' ))
{
	exit;
}

if(!class_exists( 'KeyCAPTCHA_CLASS' ))
{
	class KeyCAPTCHA_CLASS
		{			
			private $c_kc_keyword = 'accept';
			private $p_kc_visitor_ip = '';
			private $p_kc_session_id = '';
			private $p_kc_web_server_sign = '';
			private $p_kc_web_server_sign2 = '';
			private $p_kc_js_code = '';
			public $p_kc_private_key = '';
		
			public function get_web_server_sign( $use_visitor_ip=0 )
			{
				return md5( $this->p_kc_session_id.(($use_visitor_ip) ? ($this->p_kc_visitor_ip) :('')).$this->p_kc_private_key );
			}

			function __construct( $a_private_key='')	
			{
				global $user;
				if($a_private_key!='') {
					$set = explode("0",trim($a_private_key),2);
					if (sizeof($set)>1){
						$this->p_kc_private_key = trim($set[0]);
						$this->p_kc_userID = (int)$set[1];
						$this->p_kc_js_code =
"<!-- KeyCAPTCHA code (www.keycaptcha.com)-->
<script type=\"text/javascript\">
	var s_s_c_user_id = '".$this->p_kc_userID."';
	var s_s_c_session_id = '#KC_SESSION_ID#';
	var s_s_c_captcha_field_id = 'kc_response_field';
	var s_s_c_submit_button_id = 'submit,preview,post';
	var s_s_c_web_server_sign = '#KC_WSIGN#';
	var s_s_c_web_server_sign2 = '#KC_WSIGN2#';
</script>
<script type=\"text/javascript\" src=\"http://backs.keycaptcha.com/swfs/cap.js\"></script>
<!-- end of KeyCAPTCHA code-->";
					}
				}
				$this->p_kc_session_id = uniqid() . '-2.0.2.002';
				$this->p_kc_visitor_ip = $user->ip;
			}
		
			function http_get( $path )
			{
				$arr = parse_url( $path );
				$host = $arr['host'];
				$page = $arr['path'];
				if($page=='')
				{ 
					$page='/'; 
				}
				if(isset( $arr['query'] ))
				{ 
					$page.='?'.$arr['query']; 
				}
				$errno = 0;
				$errstr = '';
				$fp = fsockopen( $host, 80, $errno, $errstr, 30 );
				if(!$fp)
				{
					return '';
				}
				$request = "GET $page HTTP/1.0\r\n";
				$request .= "Host: $host\r\n";
				$request .= "Connection: close\r\n";
				$request .= "Cache-Control: no-store, no-cache\r\n";
				$request .= "Pragma: no-cache\r\n";
				$request .= "User-Agent: KeyCAPTCHA\r\n";
				$request .= "\r\n";
			
				fwrite( $fp, $request );
				$out = '';

				while (!feof( $fp ))
				{
					$out .= fgets( $fp, 250 );
				}
				fclose( $fp );
				$ov = explode( "close\r\n\r\n", $out );

				return $ov[1];
			}
	
			public function check_result( $response )
			{
				$kc_vars = explode ( '|', $response );
				if(sizeof( $kc_vars )<4)
				{
					return false;
				}
				if($kc_vars[0]==md5( $this->c_kc_keyword.$kc_vars[1].$this->p_kc_private_key.$kc_vars[2] ))
				{
					$this->p_kc_session_id = $kc_vars[3];				
					$this->p_kc_web_server_sign = $this->get_web_server_sign( 1 );

					if($kc_vars[1]==$this->p_kc_web_server_sign)
					{
						if(stripos( $kc_vars[2], 'http://' )!==0)
						{					
							$kc_current_time = time();
							$kc_var_time = split( '[/ :]', $kc_vars[2] );
							$kc_submit_time = gmmktime( $kc_var_time[3], $kc_var_time[4], $kc_var_time[5], $kc_var_time[1], $kc_var_time[2], $kc_var_time[0] );
							if(($kc_current_time - $kc_submit_time)<15)
							{
								return true;
							}
							return false;
						}
						else
						{	
							if($this->http_get( $kc_vars[2] . $this->p_kc_web_server_sign )=='1')
							{
								return true;
							}
							else
							{
								return false;
							}
						}
					}
					return false;
				}
				return false;
			}

			public function render_js ()
			{
				if ( isset( $_SERVER['HTTPS'] )&&( $_SERVER['HTTPS']=='on' ) )
				{
					$this->p_kc_js_code = str_replace( 'http://', 'https://', $this->p_kc_js_code );
				}
				$this->p_kc_js_code = str_replace( '#KC_SESSION_ID#', $this->p_kc_session_id, $this->p_kc_js_code );
				$this->p_kc_js_code = str_replace( '#KC_WSIGN#', $this->get_web_server_sign( 1 ), $this->p_kc_js_code );
				$this->p_kc_js_code = str_replace( '#KC_WSIGN2#', $this->get_web_server_sign(), $this->p_kc_js_code );
				return $this->p_kc_js_code;	
			}
		}
}

if(!class_exists( 'phpbb_default_captcha' ))
{
	include( $phpbb_root_path . 'includes/captcha/plugins/captcha_abstract.' . $phpEx );
}

class phpbb_keycaptcha extends phpbb_default_captcha
{
	function init( $type )
	{
		global $config, $db, $user;

		$user->add_lang( 'mods/captcha_keycaptcha' );
		parent::init( $type );
	}

	function get_instance()
	{
		$instance = new phpbb_keycaptcha();
		return $instance;
	}

	function is_available()
	{
		global $config, $user;
		$user->add_lang( 'mods/captcha_keycaptcha' );
		return( (isset( $config['keycaptcha_site_private_key'] ))&&(!empty( $config['keycaptcha_site_private_key'] )) );
	}

	function has_config()
	{
		return true;
	}

	function get_name()
	{
		return 'CAPTCHA_KEYCAPTCHA';
	}

	function get_class_name()
	{
		return 'phpbb_keycaptcha';
	}

	function acp_page( $id, $module )
	{
		global $config, $db, $template, $user;

		$captcha_vars=array(
			'keycaptcha_code'		=> 'KEYCAPTCHA_CODE',
			'keycaptcha_site_private_key'	=> 'KEYCAPTCHA_SITE_PRIVATE',
		);
		
		$max_len_str_with_code=1530;
		$code_substr_len=255;
		$module->tpl_name='captcha_keycaptcha_acp';
		$module->page_title='ACP_VC_SETTINGS';
		$form_key='acp_captcha';
		add_form_key( $form_key );

		$submit=request_var( 'submit', '' );

		if ( ($submit)&&(check_form_key( $form_key )) )
		{
			$captcha_vars=array_keys( $captcha_vars );
			foreach($captcha_vars as $captcha_var)
			{
				$value=request_var( $captcha_var, '' );
				if($value)
				{
					set_config( $captcha_var, $value );
				}
			}

			add_log( 'admin', 'LOG_CONFIG_VISUAL' );
			trigger_error( $user->lang['CONFIG_UPDATED'].adm_back_link( $module->u_action ) );
		}
		else if( $submit )
		{
			trigger_error( $user->lang['FORM_INVALID'].adm_back_link( $module->u_action ) );
		}
		else
		{
			foreach($captcha_vars as $captcha_var => $template_var)
			{
				$var=(isset( $_REQUEST[$captcha_var] ))?request_var( $captcha_var, '' ):((isset( $config[$captcha_var] ))?$config[$captcha_var]:'');
				$template->assign_var($template_var, $var);
			}

			$template->assign_vars(array(
				'CAPTCHA_PREVIEW'	=> $this->get_demo_template($id),
				'CAPTCHA_NAME'		=> $this->get_class_name(),
				'U_ACTION'		=> $module->u_action,));
		}
	}

	function execute_demo()
	{
	}

	function execute()
	{
	}

	function get_template()
	{
		global $config, $user, $template;

		if($this->is_solved())
		{
			return false;
		}
		else
		{
			$kc_o=new KeyCAPTCHA_CLASS( (isset( $config['keycaptcha_site_private_key'] ))?$config['keycaptcha_site_private_key']:'' );
			$kc_code=$kc_o->render_js();
			$template->assign_vars( array(
				'S_KEYCAPTCHA_AVAILABLE'	=> $this->is_available(),
				'S_CONFIRM_CODE'			=> true,
				'S_TYPE'					=> $this->type,
				'KEYCAPTCHA_CODE'			=> $kc_code,
				) );
			$template->assign_vars(
				array(
				'CONFIRM_IMAGE'				=> $kc_code.'<input type="hidden" name="kc_response_field" id="kc_response_field" value="false" />
				<script type="text/javascript">
					var kc_check_fld_visibility = function(){
						var e = document.getElementById("confirm_code");
						if (e!=undefined){
							e.parentNode.removeChild( e );
						}else {
							if ((document.readyState=="loaded")||(document.readyState=="complete")||(document.readyState=="interactive")) {
								return;
							}
							setTimeout (kc_check_fld_visibility,200);
						}						
					}
					kc_check_fld_visibility();					
				
				</script>',		
				
				
				'CONFIRM_IMG'				=> $kc_code.'<input type="hidden" name="kc_response_field" id="kc_response_field" value="false" />
				<script type="text/javascript">
				// <![CDATA[ 
					document.getElementsByAttribute = function( attrib, value, context_node, tag ) {
						var nodes = [];
						if ( context_node == null )
						context_node = this;
						if ( tag == null )
							tag = \'*\';
						var elems = context_node.getElementsByTagName(tag);
						for ( var i = 0; i < elems.length; i += 1 ) {
							if ( value ) {
								if ( elems[i].hasAttribute(attrib) && elems[i].getAttribute(attrib) == value )
								nodes.push(elems[i]);
							} else {
								if ( elems[i].hasAttribute(attrib) )
									nodes.push(elems[i]);
							}
						}
						return nodes;
					}
				
					var kc_check_fld_visibility = function(){
						var e = document.getElementById("confirm_code");
						if (e!=undefined){
							e.parentNode.removeChild( e );
						}else {
							if ((document.readyState=="loaded")||(document.readyState=="complete")||(document.readyState=="interactive")) {
								return;
							}
							setTimeout (kc_check_fld_visibility,200);
						}						
					}
					kc_check_fld_visibility();
					var kc_check_btn_visibility = function(){
						var e = document.getElementsByAttribute("value","kc_check_button")[0];
						if (e!=undefined){
							e.parentNode.removeChild( e );
						}else {
							if ((document.readyState=="loaded")||(document.readyState=="complete")||(document.readyState=="interactive")) {
								return;
							}
							setTimeout (kc_check_btn_visibility,200);
						}						
					}					
					
					kc_check_btn_visibility();
				//]]>
				</script>',
				'L_CONFIRM_CODE'			=> $user->lang['KEYCAPTCHA_TASK_HEADER']."<br />".$user->lang['KEYCAPTCHA_TASK_EXPLAIN'],
				'L_CONFIRM_EXPLAIN'			=> "",
				'L_VC_REFRESH_EXPLAIN'		=> "",
				'L_CONFIRM_CODE_EXPLAIN'	=> "",
				'L_VC_REFRESH'				=> "kc_check_button"
				)
			);
			
			if(file_exists( $template->root.'/captcha_keycaptcha.html' ))
			{
				return 'captcha_keycaptcha.html';
			}
			else if(file_exists( $template->root.'/../../prosilver/template/captcha_keycaptcha_default.html' ))
			{
				return '../../prosilver/template/captcha_keycaptcha_default.html';
			}
		}
	}

	function get_demo_template( $id )
	{
		global $config, $template;
			$template->assign_vars( array(
				'S_KEYCAPTCHA_AVAILABLE'	=> $this->is_available(),
				'KEYCAPTCHA_SITE_PRIVATE_KEY'	=> (isset( $config['keycaptcha_site_private_key'] ))?$config['keycaptcha_site_private_key']:'',) );
			if(file_exists( $template->root.'/captcha_keycaptcha.html' ))
			{
				return 'captcha_keycaptcha.html';
			}

	}

	function get_hidden_fields()
	{
		$hidden_fields=array();

		// this is required for posting.php - otherwise we would forget about the captcha being already solved
		if($this->solved)
		{
			$hidden_fields['confirm_code']=$this->code;
		}
		$hidden_fields['confirm_id']=$this->confirm_id;
		return $hidden_fields;
	}

	function uninstall()
	{
		$this->garbage_collect( 0 );
	}

	function install()
	{
		return;
	}

	function validate()
	{
		global $config, $user;
		if(!parent::validate())
		{
			return false;
		}
		else
		{
			$kc_o=new KeyCAPTCHA_CLASS( (isset( $config['keycaptcha_site_private_key'] ))?$config['keycaptcha_site_private_key']:'' );
			if($kc_o->check_result( request_var( 'kc_response_field', '' ) ))
			{
				$this->solved=true;
				return false;
			}
			else
			{
				return $user->lang['KEYCAPTCHA_MESSAGE_INCORRECT'];
			}
		}
	}
}
?>
