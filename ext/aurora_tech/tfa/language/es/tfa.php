<?php
/**
 * Aurora Tech TFA — TOTP Two Factor Authentication extension for phpBB.
 *
 * Spanish (formal address — "usted") language pack.
 *
 * @copyright (c) 2026 Aurora Tech Inc.
 * @license GPL-2.0-only
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
	// Shared
	'AURORA_TFA_TITLE'						=> 'Autenticación de dos factores',

	// UCP — overview
	'UCP_AURORA_TFA_TITLE'					=> 'Autenticación de dos factores',
	'UCP_AURORA_TFA_MANAGE'					=> 'Gestionar la autenticación de dos factores',
	'UCP_AURORA_TFA_EXPLAIN'				=> 'La autenticación de dos factores protege su cuenta con un segundo paso de verificación al iniciar sesión: un código de seis dígitos generado por una aplicación de autenticación en su dispositivo (como Aegis, Google Authenticator, Authy, 1Password o Bitwarden).',
	'UCP_AURORA_TFA_STATUS_ENABLED'			=> 'La autenticación de dos factores está <strong>activada</strong> en su cuenta.',
	'UCP_AURORA_TFA_STATUS_DISABLED'		=> 'La autenticación de dos factores está <strong>desactivada</strong> en su cuenta.',
	'UCP_AURORA_TFA_ACTIVATED_AT'			=> 'Activada desde',
	'UCP_AURORA_TFA_LAST_USED_AT'			=> 'Último uso',
	'UCP_AURORA_TFA_NEVER_USED'				=> 'Nunca utilizada',
	'UCP_AURORA_TFA_CODES_REMAINING'		=> 'Códigos de respaldo restantes',
	'UCP_AURORA_TFA_ENABLE_BUTTON'			=> 'Activar la autenticación de dos factores',

	// UCP — activation
	'UCP_AURORA_TFA_ENABLE_TITLE'			=> 'Activar la autenticación de dos factores',
	'UCP_AURORA_TFA_APPS_HINT'				=> 'Necesitará una aplicación de autenticación en su teléfono. Si aún no tiene ninguna: en iPhone, la aplicación <strong>Contraseñas</strong> integrada sirve, o instale <strong>2FAS Auth</strong> (gratuita); en Android, <strong>Aegis</strong> o <strong>2FAS Auth</strong> (gratuitas). Google Authenticator, Authy, 1Password y Bitwarden también funcionan.',
	'UCP_AURORA_TFA_SCAN_EXPLAIN'			=> 'Escanee este código QR con su aplicación de autenticación y, a continuación, introduzca el código de seis dígitos que muestra para confirmar la configuración.',
	'UCP_AURORA_TFA_MANUAL_ENTRY'			=> 'Si no puede escanear el código QR, introduzca esta clave secreta manualmente en su aplicación:',
	'UCP_AURORA_TFA_FIRST_CODE'				=> 'Código de verificación',
	'UCP_AURORA_TFA_FIRST_CODE_EXPLAIN'		=> 'El código de seis dígitos que muestra en este momento su aplicación de autenticación.',
	'UCP_AURORA_TFA_CONFIRM_BUTTON'			=> 'Verificar y activar',
	'UCP_AURORA_TFA_BAD_FIRST_CODE'			=> 'El código introducido no es válido. Compruebe que ha escaneado el código QR y que el reloj de su dispositivo está en hora, y vuelva a intentarlo con el código actual.',
	'UCP_AURORA_TFA_PENDING_EXPIRED'		=> 'La sesión de activación ha caducado. Se ha generado un nuevo código QR: escanéelo de nuevo en su aplicación de autenticación.',

	// UCP — backup codes
	'UCP_AURORA_TFA_CODES_TITLE'			=> 'Sus códigos de respaldo',
	'UCP_AURORA_TFA_ENABLED_SUCCESS'		=> 'La autenticación de dos factores ya está activada en su cuenta.',
	'UCP_AURORA_TFA_REGENERATED_SUCCESS'	=> 'Se ha generado un nuevo conjunto de códigos de respaldo. Sus códigos anteriores ya no funcionan.',
	'UCP_AURORA_TFA_CODES_EXPLAIN'			=> 'Cada código de respaldo puede utilizarse <strong>una sola vez</strong> para iniciar sesión si pierde el acceso a su aplicación de autenticación. Guárdelos en un lugar seguro: solo se muestran esta vez y no podrán volver a mostrarse.',
	'UCP_AURORA_TFA_CODES_DOWNLOAD'			=> 'Descargar como archivo de texto',
	'UCP_AURORA_TFA_CODES_DONE'				=> 'He guardado mis códigos de respaldo',

	// UCP — deactivation
	'UCP_AURORA_TFA_DISABLE_TITLE'			=> 'Desactivar la autenticación de dos factores',
	'UCP_AURORA_TFA_DISABLE_EXPLAIN'		=> 'Introduzca la contraseña de su cuenta para confirmar. Se eliminarán su registro y todos los códigos de respaldo.',
	'UCP_AURORA_TFA_PASSWORD'				=> 'Contraseña de la cuenta',
	'UCP_AURORA_TFA_DISABLE_BUTTON'			=> 'Desactivar la autenticación de dos factores',
	'UCP_AURORA_TFA_BAD_PASSWORD'			=> 'La contraseña introducida no es correcta.',
	'UCP_AURORA_TFA_DISABLED_SUCCESS'		=> 'La autenticación de dos factores se ha desactivado en su cuenta.',

	// UCP — backup code regeneration
	'UCP_AURORA_TFA_REGENERATE_TITLE'		=> 'Generar nuevos códigos de respaldo',
	'UCP_AURORA_TFA_REGENERATE_EXPLAIN'		=> 'Introduzca el código actual de su aplicación de autenticación para confirmar. Sus códigos de respaldo anteriores dejarán de funcionar.',
	'UCP_AURORA_TFA_CURRENT_CODE'			=> 'Código actual de la aplicación',
	'UCP_AURORA_TFA_REGENERATE_BUTTON'		=> 'Generar nuevos códigos',
	'UCP_AURORA_TFA_BAD_CODE'				=> 'El código introducido no es válido.',

	// Login verification page
	'AURORA_TFA_VERIFY_TITLE'				=> 'Verificación en dos pasos',
	'AURORA_TFA_VERIFY_INTRO'				=> 'Esta cuenta está protegida con autenticación de dos factores. Además de su contraseña, para iniciar sesión se requiere un código corto que solo usted puede generar, de modo que una contraseña robada no basta para entrar.',
	'AURORA_TFA_VERIFY_EXPLAIN'				=> 'Abra la aplicación de autenticación en su teléfono (la que configuró para esta cuenta) e introduzca el código de seis dígitos que muestra. El código cambia cada 30 segundos.',
	'AURORA_TFA_BACKUP_EXPLAIN'				=> 'Introduzca uno de sus códigos de respaldo. Cada código de respaldo solo funciona una vez.',
	'AURORA_TFA_CODE'						=> 'Código de verificación',
	'AURORA_TFA_SUBMIT'						=> 'Verificar',
	'AURORA_TFA_LOST_PHONE'					=> 'He perdido mi teléfono: usar un código de respaldo',
	'AURORA_TFA_USE_AUTHENTICATOR'			=> 'Usar mi aplicación de autenticación',
	'AURORA_TFA_BAD_LOGIN_CODE'				=> 'El código introducido no es válido. Inténtelo de nuevo.',
	'AURORA_TFA_CHALLENGE_EXPIRED'			=> 'Esta sesión de verificación ha caducado. Inicie sesión de nuevo.',
	'AURORA_TFA_SESSION_FAILED'				=> 'No se ha podido crear la sesión. Inicie sesión de nuevo.',

	// ACP
	'ACP_AURORA_TFA_TITLE'					=> 'Autenticación de dos factores',
	'ACP_AURORA_TFA_SETTINGS'				=> 'Ajustes',
	'ACP_AURORA_TFA_STATS'					=> 'Estadísticas',
	'ACP_AURORA_TFA_AUDIT'					=> 'Registro de auditoría',
	'ACP_AURORA_TFA_SETTINGS_EXPLAIN'		=> 'Política de autenticación de dos factores para todo el foro.',
	'ACP_AURORA_TFA_POLICY'					=> 'Política',
	'ACP_AURORA_TFA_MODE'					=> 'Modo',
	'ACP_AURORA_TFA_MODE_EXPLAIN'			=> 'En el modo «obligatorio», los miembros de los grupos seleccionados no pueden navegar por el foro hasta que activen la autenticación de dos factores.',
	'ACP_AURORA_TFA_MODE_DISABLED'			=> 'Desactivada: la autenticación de dos factores está apagada en todo el foro',
	'ACP_AURORA_TFA_MODE_OPTIONAL'			=> 'Opcional: los usuarios pueden activarla en su panel de control',
	'ACP_AURORA_TFA_MODE_REQUIRED'			=> 'Obligatoria para los grupos seleccionados, opcional para los demás',
	'ACP_AURORA_TFA_GROUPS'					=> 'Grupos sujetos a la autenticación de dos factores obligatoria',
	'ACP_AURORA_TFA_GROUPS_EXPLAIN'			=> 'Solo se aplica en el modo «obligatorio». Mantenga pulsada la tecla Ctrl (o Cmd) para seleccionar varios grupos.',
	'ACP_AURORA_TFA_STATS_GLOBAL'			=> 'Usuarios con la autenticación de dos factores activada',
	'ACP_AURORA_TFA_STATS_MEMBERS'			=> 'Miembros',
	'ACP_AURORA_TFA_STATS_ENROLLED'			=> 'Inscritos',
	'ACP_AURORA_TFA_AUDIT_EXPLAIN'			=> 'Eventos recientes de autenticación de dos factores (últimos 100): activaciones, desactivaciones, regeneraciones de códigos de respaldo y fallos de verificación al iniciar sesión.',
	'ACP_AURORA_TFA_AUDIT_EVENT'			=> 'Evento',
	'ACP_AURORA_TFA_AUDIT_EMPTY'			=> 'Todavía no hay eventos de autenticación de dos factores registrados.',

	// Audit log entries (viewable in ACP)
	'LOG_AURORA_TFA_ENABLED'				=> '<strong>Autenticación de dos factores activada</strong>',
	'LOG_AURORA_TFA_DISABLED'				=> '<strong>Autenticación de dos factores desactivada</strong>',
	'LOG_AURORA_TFA_CODES_REGENERATED'		=> '<strong>Códigos de respaldo de autenticación de dos factores regenerados</strong>',
	'LOG_AURORA_TFA_LOGIN_FAILED'			=> '<strong>Fallo de la autenticación de dos factores al iniciar sesión</strong>',
	'LOG_AURORA_TFA_CONFIG'					=> '<strong>Política de autenticación de dos factores actualizada</strong>',
]);
