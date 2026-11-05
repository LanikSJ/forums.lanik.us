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
	// UCP "Name privacy" page (Story #23)
	'UCP_GUESTPRIVACY'                       => 'Privacidade do nome',

	'GUESTPSEUDONYMS_YOUR_NICKNAME'          => 'A sua alcunha de visitante',
	'GUESTPSEUDONYMS_NICKNAME_EXPLAIN'       => 'Este é o nome mostrado aos visitantes que não iniciaram sessão (e aos motores de busca), em vez do seu nome de utilizador. Raramente o verá a si próprio.',
	'GUESTPSEUDONYMS_REGENERATE'             => 'Gerar uma nova alcunha',
	'GUESTPSEUDONYMS_REGENERATE_EXPLAIN'     => 'Se preferir uma alcunha diferente, gere uma nova. Será criada no seu idioma.',
	'GUESTPSEUDONYMS_VISIBILITY'             => 'O que os visitantes e os motores de busca veem',
	'GUESTPSEUDONYMS_VISIBILITY_EXPLAIN'     => 'Escolha quanto da sua identidade os visitantes que não iniciaram sessão — visitantes e motores de busca — podem ver. Os membros com sessão iniciada veem sempre o seu nome de utilizador real e a sua assinatura.',
	'GUESTPSEUDONYMS_LEVEL_1'                => 'Ocultar o meu nome e a minha assinatura',
	'GUESTPSEUDONYMS_LEVEL_2'                => 'Mostrar o meu nome de utilizador real, mas ocultar a minha assinatura',
	'GUESTPSEUDONYMS_LEVEL_3'                => 'Mostrar o meu nome de utilizador real e a minha assinatura',

	'GUESTPSEUDONYMS_SAVED'                  => 'As suas definições de privacidade do nome foram guardadas.',
	'GUESTPSEUDONYMS_REGENERATED'            => 'Foi gerada uma nova alcunha de visitante para si.',

	// Opt-out reminder banner (Story #24)
	'GUESTPSEUDONYMS_REMINDER_TITLE'         => 'Lembrete:',
	'GUESTPSEUDONYMS_REMINDER'               => 'o seu nome de utilizador real está atualmente visível para os visitantes e os motores de busca.',
	'GUESTPSEUDONYMS_REMINDER_LINK'          => 'Rever as suas definições de privacidade do nome',

	// Comando de consola (bin/phpbbcli.php esp:guestpseudonyms:backfill)
	'CLI_DESCRIPTION_GUESTPSEUDONYMS_BACKFILL' => 'Gera uma alcunha de visitante para cada membro que ainda não tenha uma.',
	'CLI_GUESTPSEUDONYMS_BACKFILL_DONE'      => [
		0 => 'Todos os membros já têm uma alcunha de visitante.',
		1 => 'Foi gerada %d alcunha de visitante.',
		2 => 'Foram geradas %d alcunhas de visitante.',
	],
]);
