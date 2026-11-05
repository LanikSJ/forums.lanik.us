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
	'ACP_GUESTPRIVACY_TITLE'      => 'Seudónimos para invitados',
	'ACP_GUESTPRIVACY_SETTINGS'   => 'Ajustes',
	'ACP_GUESTPRIVACY_EXPLAIN'    => 'Controla cómo se ocultan los nombres de usuario de los miembros a los invitados y a los motores de búsqueda, y qué opciones tienen los miembros sobre su propio apodo de invitado.',

	'GP_ENABLE'                   => 'Activar los seudónimos para invitados',
	'GP_ENABLE_EXPLAIN'           => 'Cuando está activado, cada visitante que no ha iniciado sesión — tanto invitados como bots de motores de búsqueda — ve un apodo ficticio en lugar del nombre de usuario de cada miembro.',
	'GP_RESOLVE_NAMES'            => 'Resolver los nombres de citas y menciones desde la cuenta',
	'GP_RESOLVE_NAMES_EXPLAIN'    => 'Para los miembros con sesión iniciada, las citas y las @menciones muestran el nombre de usuario actual del miembro referenciado, obtenido desde su cuenta en lugar del nombre almacenado en el mensaje. Al renombrar a un usuario (por ejemplo, a Anon20260101 en una solicitud de eliminación de cuenta), las citas y menciones antiguas se actualizan automáticamente, sin editar la base de datos. Cuando está desactivado, las citas conservan el nombre tal como se escribió.',

	'GP_MEMBER_CONTROLS'          => 'Opciones de los miembros',
	'GP_ALLOW_REGENERATE'         => 'Permitir a los miembros regenerar su apodo',
	'GP_ALLOW_REGENERATE_EXPLAIN' => 'Los miembros pueden generar un apodo de invitado distinto desde su Panel de Control del Usuario.',
	'GP_ALLOW_OPT_OUT'            => 'Permitir que los miembros se muestren',
	'GP_ALLOW_OPT_OUT_EXPLAIN'    => 'Permite que los miembros elijan, en su página de Privacidad del nombre, mostrar su nombre de usuario real a los invitados y a los motores de búsqueda — y, si quieren, también su firma. Si está desactivado, cada miembro permanece protegido (apodo, firma oculta), sin opción.',
	'GP_REMINDER_MONTHS'          => 'Intervalo de recordatorio de renuncia (meses)',
	'GP_REMINDER_MONTHS_EXPLAIN'  => 'Recordar con esta frecuencia a los miembros que han renunciado que su nombre real es público. Establece 0 para desactivar los recordatorios.',
	'GP_AUTOREVERT'               => 'Volver a proteger si se ignora un recordatorio',
	'GP_AUTOREVERT_EXPLAIN'       => 'Si un miembro que ha renunciado no vuelve a confirmar tras un recordatorio, ocultar de nuevo su nombre automáticamente (a prueba de fallos).',

	'GUESTPSEUDONYMS_ACP_SAVED'   => 'Se ha guardado la configuración de los seudónimos para invitados.',

	// Admin log entry written when these settings are saved.
	'LOG_GUESTPSEUDONYMS_SETTINGS' => 'Se ha cambiado la configuración de apodos de invitado',
]);
