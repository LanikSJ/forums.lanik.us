<?php
/**
 * AJAX Registration Check - Portuguese (Brazil)
 */

if (!defined('IN_PHPBB'))
{
    exit;
}

if (empty($lang) || !is_array($lang))
{
    $lang = array();
}

$lang = array_merge($lang, array(
    'PCGF_AJAXREGISTRATIONCHECK_INVALID_QUERY'        => 'A consulta é inválida!',
    'PCGF_AJAXREGISTRATIONCHECK_USERNAME_OK'          => 'O nome de usuário informado pode ser usado.',
    'PCGF_AJAXREGISTRATIONCHECK_EMAIL_INVALID'        => 'O valor informado não é um endereço de e-mail válido!',
    'PCGF_AJAXREGISTRATIONCHECK_EMAIL_OK'             => 'O endereço de e-mail informado pode ser usado.',
    'PCGF_AJAXREGISTRATIONCHECK_CONFIRM_PASSWORD_INVALID' => 'As senhas informadas não coincidem.',
    'PCGF_AJAXREGISTRATIONCHECK_CONFIRM_PASSWORD_OK'  => 'As senhas informadas são iguais.',
    'PCGF_AJAXREGISTRATIONCHECK_PASSWORD_STRENGTH'    => 'Força da senha',
    'PCGF_AJAXREGISTRATIONCHECK_PASSWORD_VERY_WEAK'   => 'Muito fraca',
    'PCGF_AJAXREGISTRATIONCHECK_PASSWORD_WEAK'        => 'Fraca',
    'PCGF_AJAXREGISTRATIONCHECK_PASSWORD_NORMAL'      => 'Normal',
    'PCGF_AJAXREGISTRATIONCHECK_PASSWORD_STRONG'      => 'Forte',
    'PCGF_AJAXREGISTRATIONCHECK_PASSWORD_VERY_STRONG' => 'Muito forte',
));
