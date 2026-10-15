<?php
/**
 * Aurora Tech TFA — TOTP Two Factor Authentication extension for phpBB.
 *
 * Portuguese (Brazil) language pack.
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
	'UCP_AURORA_TFA_MANAGE'					=> 'Gerenciar a autenticação de dois fatores',
	'UCP_AURORA_TFA_EXPLAIN'				=> 'A autenticação de dois fatores protege a sua conta com uma segunda etapa de verificação no login: um código de seis dígitos gerado por um aplicativo autenticador no seu dispositivo (como Aegis, Google Authenticator, Authy, 1Password ou Bitwarden).',
	'UCP_AURORA_TFA_STATUS_ENABLED'			=> 'A autenticação de dois fatores está <strong>ativada</strong> na sua conta.',
	'UCP_AURORA_TFA_STATUS_DISABLED'		=> 'A autenticação de dois fatores está <strong>desativada</strong> na sua conta.',
	'UCP_AURORA_TFA_ACTIVATED_AT'			=> 'Ativada desde',
	'UCP_AURORA_TFA_LAST_USED_AT'			=> 'Último uso',
	'UCP_AURORA_TFA_NEVER_USED'				=> 'Nunca usada',
	'UCP_AURORA_TFA_CODES_REMAINING'		=> 'Códigos de backup restantes',
	'UCP_AURORA_TFA_ENABLE_BUTTON'			=> 'Ativar a autenticação de dois fatores',

	// UCP — activation
	'UCP_AURORA_TFA_ENABLE_TITLE'			=> 'Ativar a autenticação de dois fatores',
	'UCP_AURORA_TFA_APPS_HINT'				=> 'Você vai precisar de um aplicativo autenticador no seu celular. Se ainda não tiver um: no iPhone, o aplicativo <strong>Senhas</strong> integrado funciona, ou instale o <strong>2FAS Auth</strong> (gratuito); no Android, o <strong>Aegis</strong> ou o <strong>2FAS Auth</strong> (gratuitos). Google Authenticator, Authy, 1Password e Bitwarden também funcionam.',
	'UCP_AURORA_TFA_SCAN_EXPLAIN'			=> 'Escaneie este QR code com o seu aplicativo autenticador e, em seguida, digite o código de seis dígitos exibido para confirmar a configuração.',
	'UCP_AURORA_TFA_MANUAL_ENTRY'			=> 'Se não conseguir escanear o QR code, digite esta chave secreta manualmente no seu aplicativo:',
	'UCP_AURORA_TFA_FIRST_CODE'				=> 'Código de verificação',
	'UCP_AURORA_TFA_FIRST_CODE_EXPLAIN'		=> 'O código de seis dígitos exibido neste momento pelo seu aplicativo autenticador.',
	'UCP_AURORA_TFA_CONFIRM_BUTTON'			=> 'Verificar e ativar',
	'UCP_AURORA_TFA_BAD_FIRST_CODE'			=> 'O código digitado não é válido. Verifique se você escaneou o QR code e se o relógio do seu dispositivo está certo e tente novamente com o código atual.',
	'UCP_AURORA_TFA_PENDING_EXPIRED'		=> 'A sessão de ativação expirou. Um novo QR code foi gerado — escaneie-o novamente no seu aplicativo autenticador.',

	// UCP — backup codes
	'UCP_AURORA_TFA_CODES_TITLE'			=> 'Os seus códigos de backup',
	'UCP_AURORA_TFA_ENABLED_SUCCESS'		=> 'A autenticação de dois fatores agora está ativada na sua conta.',
	'UCP_AURORA_TFA_REGENERATED_SUCCESS'	=> 'Um novo conjunto de códigos de backup foi gerado. Os seus códigos anteriores não funcionam mais.',
	'UCP_AURORA_TFA_CODES_EXPLAIN'			=> 'Cada código de backup pode ser usado <strong>uma única vez</strong> para entrar caso você perca o acesso ao seu aplicativo autenticador. Guarde-os em um lugar seguro — eles são exibidos apenas desta vez e não poderão ser mostrados novamente.',
	'UCP_AURORA_TFA_CODES_DOWNLOAD'			=> 'Baixar como arquivo de texto',
	'UCP_AURORA_TFA_CODES_DONE'				=> 'Salvei os meus códigos de backup',

	// UCP — deactivation
	'UCP_AURORA_TFA_DISABLE_TITLE'			=> 'Desativar a autenticação de dois fatores',
	'UCP_AURORA_TFA_DISABLE_EXPLAIN'		=> 'Digite a senha da sua conta para confirmar. O seu registro e todos os códigos de backup serão removidos.',
	'UCP_AURORA_TFA_PASSWORD'				=> 'Senha da conta',
	'UCP_AURORA_TFA_DISABLE_BUTTON'			=> 'Desativar a autenticação de dois fatores',
	'UCP_AURORA_TFA_BAD_PASSWORD'			=> 'A senha digitada não está correta.',
	'UCP_AURORA_TFA_DISABLED_SUCCESS'		=> 'A autenticação de dois fatores foi desativada na sua conta.',

	// UCP — backup code regeneration
	'UCP_AURORA_TFA_REGENERATE_TITLE'		=> 'Gerar novos códigos de backup',
	'UCP_AURORA_TFA_REGENERATE_EXPLAIN'		=> 'Digite o código atual do seu aplicativo autenticador para confirmar. Os seus códigos de backup anteriores deixarão de funcionar.',
	'UCP_AURORA_TFA_CURRENT_CODE'			=> 'Código atual do aplicativo',
	'UCP_AURORA_TFA_REGENERATE_BUTTON'		=> 'Gerar novos códigos',
	'UCP_AURORA_TFA_BAD_CODE'				=> 'O código digitado não é válido.',

	// Login verification page
	'AURORA_TFA_VERIFY_TITLE'				=> 'Verificação em duas etapas',
	'AURORA_TFA_VERIFY_INTRO'				=> 'Esta conta está protegida por autenticação de dois fatores. Além da sua senha, o login exige um código curto que só você pode gerar — assim, uma senha roubada não é suficiente para entrar.',
	'AURORA_TFA_VERIFY_EXPLAIN'				=> 'Abra o aplicativo autenticador no seu celular (o que você configurou para esta conta) e digite o código de seis dígitos que ele exibe. O código muda a cada 30 segundos.',
	'AURORA_TFA_BACKUP_EXPLAIN'				=> 'Digite um dos seus códigos de backup. Cada código de backup funciona apenas uma vez.',
	'AURORA_TFA_CODE'						=> 'Código de verificação',
	'AURORA_TFA_SUBMIT'						=> 'Verificar',
	'AURORA_TFA_LOST_PHONE'					=> 'Perdi o meu celular — usar um código de backup',
	'AURORA_TFA_USE_AUTHENTICATOR'			=> 'Usar o meu aplicativo autenticador',
	'AURORA_TFA_BAD_LOGIN_CODE'				=> 'O código digitado não é válido. Tente novamente.',
	'AURORA_TFA_CHALLENGE_EXPIRED'			=> 'Esta sessão de verificação expirou. Faça login novamente.',
	'AURORA_TFA_SESSION_FAILED'				=> 'Não foi possível criar a sessão. Faça login novamente.',

	// ACP
	'ACP_AURORA_TFA_TITLE'					=> 'Autenticação de dois fatores',
	'ACP_AURORA_TFA_SETTINGS'				=> 'Configurações',
	'ACP_AURORA_TFA_STATS'					=> 'Estatísticas',
	'ACP_AURORA_TFA_AUDIT'					=> 'Registro de auditoria',
	'ACP_AURORA_TFA_SETTINGS_EXPLAIN'		=> 'Política de autenticação de dois fatores para todo o fórum.',
	'ACP_AURORA_TFA_POLICY'					=> 'Política',
	'ACP_AURORA_TFA_MODE'					=> 'Modo',
	'ACP_AURORA_TFA_MODE_EXPLAIN'			=> 'No modo "obrigatório", os membros dos grupos selecionados não podem navegar no fórum enquanto não ativarem a autenticação de dois fatores.',
	'ACP_AURORA_TFA_MODE_DISABLED'			=> 'Desativada — a autenticação de dois fatores está desligada em todo o fórum',
	'ACP_AURORA_TFA_MODE_OPTIONAL'			=> 'Opcional — os usuários podem ativá-la no seu painel de controle',
	'ACP_AURORA_TFA_MODE_REQUIRED'			=> 'Obrigatória para os grupos selecionados, opcional para os demais',
	'ACP_AURORA_TFA_GROUPS'					=> 'Grupos sujeitos à autenticação de dois fatores obrigatória',
	'ACP_AURORA_TFA_GROUPS_EXPLAIN'			=> 'Aplica-se apenas no modo "obrigatório". Mantenha pressionado Ctrl (ou Cmd) para selecionar vários grupos.',
	'ACP_AURORA_TFA_STATS_GLOBAL'			=> 'Usuários com a autenticação de dois fatores ativada',
	'ACP_AURORA_TFA_STATS_MEMBERS'			=> 'Membros',
	'ACP_AURORA_TFA_STATS_ENROLLED'			=> 'Inscritos',
	'ACP_AURORA_TFA_AUDIT_EXPLAIN'			=> 'Eventos recentes de autenticação de dois fatores (últimos 100): ativações, desativações, regenerações de códigos de backup e falhas de verificação no login.',
	'ACP_AURORA_TFA_AUDIT_EVENT'			=> 'Evento',
	'ACP_AURORA_TFA_AUDIT_EMPTY'			=> 'Ainda não há eventos de autenticação de dois fatores registrados.',

	// Audit log entries (viewable in ACP)
	'LOG_AURORA_TFA_ENABLED'				=> '<strong>Autenticação de dois fatores ativada</strong>',
	'LOG_AURORA_TFA_DISABLED'				=> '<strong>Autenticação de dois fatores desativada</strong>',
	'LOG_AURORA_TFA_CODES_REGENERATED'		=> '<strong>Códigos de backup de autenticação de dois fatores gerados novamente</strong>',
	'LOG_AURORA_TFA_LOGIN_FAILED'			=> '<strong>Falha na autenticação de dois fatores no login</strong>',
	'LOG_AURORA_TFA_CONFIG'					=> '<strong>Política de autenticação de dois fatores atualizada</strong>',
]);
