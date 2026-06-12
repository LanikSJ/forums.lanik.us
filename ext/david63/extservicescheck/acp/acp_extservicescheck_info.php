<?php
/**
*
* @package Extension .yml Check Extension
* @copyright (c) 2019 david63
* @license GNU General Public License, version 2 (GPL-2.0)
*
*/

namespace david63\extservicescheck\acp;

class acp_extservicescheck_info
{
	function module()
	{
		return array(
			'filename'	=> '\david63\extservicescheck\acp\acp_extservicescheck_module',
			'title'		=> 'EXT_YML_CHECK',
			'modes'		=> array(
				'main'	=> array('title' => 'EXT_YML_CHECK', 'auth' => 'ext_david63/extservicescheck && acl_a_extensions', 'cat' => array('ACP_EXTENSION_MANAGEMENT')),
			),
		);
	}
}
