<?php
/**
 * Aurora Tech TFA — TOTP Two Factor Authentication extension for phpBB.
 *
 * Portuguese (Portugal) language pack.
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
	'AURORA_TFA_TITLE'						=> 'Autenticação de dois fatores',

	// UCP — overview
	'UCP_AURORA_TFA_TITLE'					=> 'Autenticação de dois fatores',
	'UCP_AURORA_TFA_MANAGE'					=> 'Gerir a autenticação de dois fatores',
	'UCP_AURORA_TFA_EXPLAIN'				=> 'A autenticação de dois fatores protege a sua conta com um segundo passo de verificação no início de sessão: um código de seis dígitos gerado por uma aplicação de autenticação no seu dispositivo (como o Aegis, o Google Authenticator, o Authy, o 1Password ou o Bitwarden).',
	'UCP_AURORA_TFA_STATUS_ENABLED'			=> 'A autenticação de dois fatores está <strong>ativada</strong> na sua conta.',
	'UCP_AURORA_TFA_STATUS_DISABLED'		=> 'A autenticação de dois fatores está <strong>desativada</strong> na sua conta.',
	'UCP_AURORA_TFA_ACTIVATED_AT'			=> 'Ativada desde',
	'UCP_AURORA_TFA_LAST_USED_AT'			=> 'Última utilização',
	'UCP_AURORA_TFA_NEVER_USED'				=> 'Nunca utilizada',
	'UCP_AURORA_TFA_CODES_REMAINING'		=> 'Códigos de recuperação restantes',
	'UCP_AURORA_TFA_ENABLE_BUTTON'			=> 'Ativar a autenticação de dois fatores',

	// UCP — activation
	'UCP_AURORA_TFA_ENABLE_TITLE'			=> 'Ativar a autenticação de dois fatores',
	'UCP_AURORA_TFA_APPS_HINT'				=> 'Vai precisar de uma aplicação de autenticação no seu telemóvel. Se ainda não tiver nenhuma: no iPhone, a aplicação <strong>Palavras-passe</strong> integrada serve, ou instale o <strong>2FAS Auth</strong> (gratuito); no Android, o <strong>Aegis</strong> ou o <strong>2FAS Auth</strong> (gratuitos). O Google Authenticator, o Authy, o 1Password e o Bitwarden também funcionam.',
	'UCP_AURORA_TFA_SCAN_EXPLAIN'			=> 'Leia este código QR com a sua aplicação de autenticação e, em seguida, introduza o código de seis dígitos apresentado para confirmar a configuração.',
	'UCP_AURORA_TFA_MANUAL_ENTRY'			=> 'Se não conseguir ler o código QR, introduza manualmente esta chave secreta na sua aplicação:',
	'UCP_AURORA_TFA_FIRST_CODE'				=> 'Código de verificação',
	'UCP_AURORA_TFA_FIRST_CODE_EXPLAIN'		=> 'O código de seis dígitos apresentado neste momento pela sua aplicação de autenticação.',
	'UCP_AURORA_TFA_CONFIRM_BUTTON'			=> 'Verificar e ativar',
	'UCP_AURORA_TFA_BAD_FIRST_CODE'			=> 'O código introduzido não é válido. Verifique se leu o código QR e se o relógio do seu dispositivo está certo e, em seguida, tente novamente com o código atual.',
	'UCP_AURORA_TFA_PENDING_EXPIRED'		=> 'A sessão de ativação expirou. Foi gerado um novo código QR — leia-o novamente na sua aplicação de autenticação.',

	// UCP — backup codes
	'UCP_AURORA_TFA_CODES_TITLE'			=> 'Os seus códigos de recuperação',
	'UCP_AURORA_TFA_ENABLED_SUCCESS'		=> 'A autenticação de dois fatores está agora ativada na sua conta.',
	'UCP_AURORA_TFA_REGENERATED_SUCCESS'	=> 'Foi gerado um novo conjunto de códigos de recuperação. Os seus códigos anteriores deixaram de funcionar.',
	'UCP_AURORA_TFA_CODES_EXPLAIN'			=> 'Cada código de recuperação pode ser utilizado <strong>uma única vez</strong> para iniciar sessão caso perca o acesso à sua aplicação de autenticação. Guarde-os num local seguro — só são apresentados desta vez e não poderão voltar a ser mostrados.',
	'UCP_AURORA_TFA_CODES_DOWNLOAD'			=> 'Transferir como ficheiro de texto',
	'UCP_AURORA_TFA_CODES_DONE'				=> 'Guardei os meus códigos de recuperação',

	// UCP — deactivation
	'UCP_AURORA_TFA_DISABLE_TITLE'			=> 'Desativar a autenticação de dois fatores',
	'UCP_AURORA_TFA_DISABLE_EXPLAIN'		=> 'Introduza a palavra-passe da sua conta para confirmar. O seu registo e todos os códigos de recuperação serão removidos.',
	'UCP_AURORA_TFA_PASSWORD'				=> 'Palavra-passe da conta',
	'UCP_AURORA_TFA_DISABLE_BUTTON'			=> 'Desativar a autenticação de dois fatores',
	'UCP_AURORA_TFA_BAD_PASSWORD'			=> 'A palavra-passe introduzida não está correta.',
	'UCP_AURORA_TFA_DISABLED_SUCCESS'		=> 'A autenticação de dois fatores foi desativada na sua conta.',

	// UCP — backup code regeneration
	'UCP_AURORA_TFA_REGENERATE_TITLE'		=> 'Gerar novos códigos de recuperação',
	'UCP_AURORA_TFA_REGENERATE_EXPLAIN'		=> 'Introduza o código atual da sua aplicação de autenticação para confirmar. Os seus códigos de recuperação anteriores deixarão de funcionar.',
	'UCP_AURORA_TFA_CURRENT_CODE'			=> 'Código atual da aplicação',
	'UCP_AURORA_TFA_REGENERATE_BUTTON'		=> 'Gerar novos códigos',
	'UCP_AURORA_TFA_BAD_CODE'				=> 'O código introduzido não é válido.',

	// Login verification page
	'AURORA_TFA_VERIFY_TITLE'				=> 'Verificação em dois passos',
	'AURORA_TFA_VERIFY_INTRO'				=> 'Esta conta está protegida por autenticação de dois fatores. Além da sua palavra-passe, o início de sessão exige um código curto que só você pode gerar — assim, uma palavra-passe roubada não é suficiente para entrar.',
	'AURORA_TFA_VERIFY_EXPLAIN'				=> 'Abra a aplicação de autenticação no seu telemóvel (a que configurou para esta conta) e introduza o código de seis dígitos que apresenta. O código muda a cada 30 segundos.',
	'AURORA_TFA_BACKUP_EXPLAIN'				=> 'Introduza um dos seus códigos de recuperação. Cada código de recuperação só funciona uma vez.',
	'AURORA_TFA_CODE'						=> 'Código de verificação',
	'AURORA_TFA_SUBMIT'						=> 'Verificar',
	'AURORA_TFA_LOST_PHONE'					=> 'Perdi o meu telemóvel — utilizar um código de recuperação',
	'AURORA_TFA_USE_AUTHENTICATOR'			=> 'Utilizar antes a minha aplicação de autenticação',
	'AURORA_TFA_BAD_LOGIN_CODE'				=> 'O código introduzido não é válido. Tente novamente.',
	'AURORA_TFA_CHALLENGE_EXPIRED'			=> 'Esta sessão de verificação expirou. Inicie sessão novamente.',
	'AURORA_TFA_SESSION_FAILED'				=> 'Não foi possível criar a sessão. Inicie sessão novamente.',

	// ACP
	'ACP_AURORA_TFA_TITLE'					=> 'Autenticação de dois fatores',
	'ACP_AURORA_TFA_SETTINGS'				=> 'Definições',
	'ACP_AURORA_TFA_STATS'					=> 'Estatísticas',
	'ACP_AURORA_TFA_AUDIT'					=> 'Registo de auditoria',
	'ACP_AURORA_TFA_SETTINGS_EXPLAIN'		=> 'Política de autenticação de dois fatores para todo o fórum.',
	'ACP_AURORA_TFA_POLICY'					=> 'Política',
	'ACP_AURORA_TFA_MODE'					=> 'Modo',
	'ACP_AURORA_TFA_MODE_EXPLAIN'			=> 'No modo «obrigatório», os membros dos grupos selecionados não podem navegar no fórum enquanto não ativarem a autenticação de dois fatores.',
	'ACP_AURORA_TFA_MODE_DISABLED'			=> 'Desativada — a autenticação de dois fatores está desligada em todo o fórum',
	'ACP_AURORA_TFA_MODE_OPTIONAL'			=> 'Opcional — os utilizadores podem ativá-la no seu painel de controlo',
	'ACP_AURORA_TFA_MODE_REQUIRED'			=> 'Obrigatória para os grupos selecionados, opcional para os restantes',
	'ACP_AURORA_TFA_GROUPS'					=> 'Grupos sujeitos à autenticação de dois fatores obrigatória',
	'ACP_AURORA_TFA_GROUPS_EXPLAIN'			=> 'Aplica-se apenas no modo «obrigatório». Mantenha premido Ctrl (ou Cmd) para selecionar vários grupos.',
	'ACP_AURORA_TFA_STATS_GLOBAL'			=> 'Utilizadores com a autenticação de dois fatores ativada',
	'ACP_AURORA_TFA_STATS_MEMBERS'			=> 'Membros',
	'ACP_AURORA_TFA_STATS_ENROLLED'			=> 'Inscritos',
	'ACP_AURORA_TFA_AUDIT_EXPLAIN'			=> 'Eventos recentes de autenticação de dois fatores (últimos 100): ativações, desativações, regenerações de códigos de recuperação e falhas de verificação no início de sessão.',
	'ACP_AURORA_TFA_AUDIT_EVENT'			=> 'Evento',
	'ACP_AURORA_TFA_AUDIT_EMPTY'			=> 'Ainda não há eventos de autenticação de dois fatores registados.',

	// Audit log entries (viewable in ACP)
	'LOG_AURORA_TFA_ENABLED'				=> '<strong>Autenticação de dois fatores ativada</strong>',
	'LOG_AURORA_TFA_DISABLED'				=> '<strong>Autenticação de dois fatores desativada</strong>',
	'LOG_AURORA_TFA_CODES_REGENERATED'		=> '<strong>Códigos de recuperação de autenticação de dois fatores regenerados</strong>',
	'LOG_AURORA_TFA_LOGIN_FAILED'			=> '<strong>Falha na autenticação de dois fatores no início de sessão</strong>',
	'LOG_AURORA_TFA_CONFIG'					=> '<strong>Política de autenticação de dois fatores atualizada</strong>',
]);
