<?php
/**
 *
 * Guest pseudonyms. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2026 Emma Laurijssens van Engelenhoven
 * @license GNU General Public License, version 2 (GPL-2.0)
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
	'ACP_GUESTPRIVACY_TITLE'      => 'Pseudónimos de visitante',
	'ACP_GUESTPRIVACY_SETTINGS'   => 'Definições',
	'ACP_GUESTPRIVACY_EXPLAIN'    => 'Controle como os nomes de utilizador dos membros são ocultados dos visitantes e dos motores de busca, e que opções os membros têm sobre a sua própria alcunha de visitante.',

	'GP_ENABLE'                   => 'Ativar pseudónimos de visitante',
	'GP_ENABLE_EXPLAIN'           => 'Quando ativado, cada visitante que não iniciou sessão — tanto visitantes como bots de motores de busca — vê uma alcunha fictícia em vez do nome de utilizador de cada membro.',
	'GP_RESOLVE_NAMES'            => 'Resolver nomes de citações e menções a partir da conta',
	'GP_RESOLVE_NAMES_EXPLAIN'    => 'Para membros com sessão iniciada, as citações e @menções mostram o nome de utilizador atual do membro referenciado, resolvido a partir da sua conta em vez do nome guardado na mensagem. Renomear um utilizador (por exemplo para Anon20260101 num pedido de eliminação de conta) atualiza então automaticamente as citações e menções antigas, sem edições à base de dados. Quando desativado, as citações mantêm o nome tal como foi escrito.',

	'GP_MEMBER_CONTROLS'          => 'Opções dos membros',
	'GP_ALLOW_REGENERATE'         => 'Permitir que os membros regenerem a sua alcunha',
	'GP_ALLOW_REGENERATE_EXPLAIN' => 'Os membros podem gerar uma alcunha de visitante diferente a partir do seu Painel de Controlo do Utilizador.',
	'GP_ALLOW_OPT_OUT'            => 'Permitir que os membros se mostrem',
	'GP_ALLOW_OPT_OUT_EXPLAIN'    => 'Permita que os membros escolham, na sua página de Privacidade do nome, mostrar o seu nome de utilizador real aos visitantes e aos motores de busca — e, se quiserem, também a sua assinatura. Quando desativado, cada membro permanece protegido (alcunha, assinatura oculta), sem escolha.',
	'GP_REMINDER_MONTHS'          => 'Intervalo do lembrete de exclusão (meses)',
	'GP_REMINDER_MONTHS_EXPLAIN'  => 'Lembrar os membros que optaram por não participar, com esta frequência, de que o seu nome real é público. Defina como 0 para desativar os lembretes.',
	'GP_AUTOREVERT'               => 'Voltar a proteger se um lembrete for ignorado',
	'GP_AUTOREVERT_EXPLAIN'       => 'Se um membro que optou por não participar não voltar a confirmar após um lembrete, ocultar automaticamente o seu nome de novo (fail-closed).',

	'GUESTPSEUDONYMS_ACP_SAVED'   => 'As definições dos pseudónimos de visitante foram guardadas.',

	// Admin log entry written when these settings are saved.
	'LOG_GUESTPSEUDONYMS_SETTINGS' => 'Definições das alcunhas de visitante alteradas',
]);
