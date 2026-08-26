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

if (!defined('IN_PHPBB'))
{
    exit;
}

if (empty($lang) || !is_array($lang))
{
    $lang = [];
}

$lang = array_merge($lang, [
    'AGREEMENTDELAY_SECONDS'            => 'Agreement delay seconds',
	'AD_SECONDS'						=> 'Seconds',
    'AGREEMENTDELAY_SECONDS_EXPLAIN'    => 'Number of seconds users must wait before accepting the registration agreement. Minimum 5, maximum 120.',
]);