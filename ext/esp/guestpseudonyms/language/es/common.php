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
	'UCP_GUESTPRIVACY'                       => 'Privacidad del nombre',

	'GUESTPSEUDONYMS_YOUR_NICKNAME'          => 'Tu apodo de invitado',
	'GUESTPSEUDONYMS_NICKNAME_EXPLAIN'       => 'Este es el nombre que se muestra a los visitantes que no han iniciado sesión (y a los motores de búsqueda), en lugar de tu nombre de usuario. Rara vez lo verás tú mismo.',
	'GUESTPSEUDONYMS_REGENERATE'             => 'Generar un apodo nuevo',
	'GUESTPSEUDONYMS_REGENERATE_EXPLAIN'     => 'Si prefieres un apodo distinto, genera uno nuevo. Se creará en tu idioma.',
	'GUESTPSEUDONYMS_VISIBILITY'             => 'Lo que ven los invitados y los motores de búsqueda',
	'GUESTPSEUDONYMS_VISIBILITY_EXPLAIN'     => 'Elige cuánto de tu identidad pueden ver los visitantes que no han iniciado sesión — invitados y motores de búsqueda. Los miembros que han iniciado sesión siempre ven tu nombre de usuario real y tu firma.',
	'GUESTPSEUDONYMS_LEVEL_1'                => 'Ocultar mi nombre y mi firma',
	'GUESTPSEUDONYMS_LEVEL_2'                => 'Mostrar mi nombre de usuario real, pero ocultar mi firma',
	'GUESTPSEUDONYMS_LEVEL_3'                => 'Mostrar mi nombre de usuario real y mi firma',

	'GUESTPSEUDONYMS_SAVED'                  => 'Se ha guardado tu configuración de privacidad del nombre.',
	'GUESTPSEUDONYMS_REGENERATED'            => 'Se ha generado un nuevo apodo de invitado para ti.',

	// Opt-out reminder banner (Story #24)
	'GUESTPSEUDONYMS_REMINDER_TITLE'         => 'Recordatorio:',
	'GUESTPSEUDONYMS_REMINDER'               => 'tu nombre de usuario real se muestra actualmente a los invitados y a los motores de búsqueda.',
	'GUESTPSEUDONYMS_REMINDER_LINK'          => 'Revisar tu configuración de privacidad del nombre',

	// Comando de consola (bin/phpbbcli.php esp:guestpseudonyms:backfill)
	'CLI_DESCRIPTION_GUESTPSEUDONYMS_BACKFILL' => 'Genera un apodo de invitado para cada miembro que aún no tenga uno.',
	'CLI_GUESTPSEUDONYMS_BACKFILL_DONE'      => [
		0 => 'Todos los miembros ya tienen un apodo de invitado.',
		1 => 'Se ha generado %d apodo de invitado.',
		2 => 'Se han generado %d apodos de invitado.',
	],
]);
