<?php

/**
 * @author    MarkusWME <markuswme@pcgamingfreaks.at>
 * @copyright 2017 MarkusWME
 * @license   http://opensource.org/gpl-2.0.php GNU General Public License v2
 * @version   1.0.0
 */

if (!defined('IN_PHPBB'))
{
    exit;
}

if (empty($lang) || !is_array($lang))
{
    $lang = array();
}

// Merge AJAX Registration Check language data to the existing language data
$lang = array_merge($lang, array(
    'PCGF_AJAXREGISTRATIONCHECK_INVALID_QUERY'        => 'A consulta é inválida!',
    'PCGF_AJAXREGISTRATIONCHECK_USERNAME_OK'          => 'O nome de usuário fornecido pode ser usado.',
    'PCGF_AJAXREGISTRATIONCHECK_EMAIL_INVALID'        => 'A entrada não é um endereço de e-mail válido!',
    'PCGF_AJAXREGISTRATIONCHECK_EMAIL_OK'             => 'O endereço de e-mail fornecido pode ser usado.',
    'PCGF_AJAXREGISTRATIONCHECK_CONFIRM_PASSWORD_OK'  => 'As senhas fornecidas são as mesmas.',
    'PCGF_AJAXREGISTRATIONCHECK_PASSWORD_STRENGTH'    => 'Força da senha',
    'PCGF_AJAXREGISTRATIONCHECK_PASSWORD_VERY_WEAK'   => 'Muito fraco',
    'PCGF_AJAXREGISTRATIONCHECK_PASSWORD_WEAK'        => 'Fraco',
    'PCGF_AJAXREGISTRATIONCHECK_PASSWORD_NORMAL'      => 'Normal',
    'PCGF_AJAXREGISTRATIONCHECK_PASSWORD_STRONG'      => 'Forte',
    'PCGF_AJAXREGISTRATIONCHECK_PASSWORD_VERY_STRONG' => 'Muito forte',
));
