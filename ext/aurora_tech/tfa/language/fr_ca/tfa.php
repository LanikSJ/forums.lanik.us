<?php
/**
 * Aurora Tech TFA — TOTP Two Factor Authentication extension for phpBB.
 *
 * French (Canada) language pack — Quebec French, OQLF-aligned wording.
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
	'AURORA_TFA_TITLE'						=> 'Authentification à deux facteurs',

	// UCP — overview
	'UCP_AURORA_TFA_TITLE'					=> 'Authentification à deux facteurs',
	'UCP_AURORA_TFA_MANAGE'					=> 'Gérer l’authentification à deux facteurs',
	'UCP_AURORA_TFA_EXPLAIN'				=> 'L’authentification à deux facteurs protège votre compte par une seconde étape de vérification à l’ouverture de session : un code à six chiffres généré par une application d’authentification sur votre appareil (comme Aegis, Google Authenticator, Authy, 1Password ou Bitwarden).',
	'UCP_AURORA_TFA_STATUS_ENABLED'			=> 'L’authentification à deux facteurs est <strong>activée</strong> sur votre compte.',
	'UCP_AURORA_TFA_STATUS_DISABLED'		=> 'L’authentification à deux facteurs est <strong>désactivée</strong> sur votre compte.',
	'UCP_AURORA_TFA_ACTIVATED_AT'			=> 'Activée depuis',
	'UCP_AURORA_TFA_LAST_USED_AT'			=> 'Dernière utilisation',
	'UCP_AURORA_TFA_NEVER_USED'				=> 'Jamais utilisée',
	'UCP_AURORA_TFA_CODES_REMAINING'		=> 'Codes de secours restants',
	'UCP_AURORA_TFA_ENABLE_BUTTON'			=> 'Activer l’authentification à deux facteurs',

	// UCP — activation
	'UCP_AURORA_TFA_ENABLE_TITLE'			=> 'Activer l’authentification à deux facteurs',
	'UCP_AURORA_TFA_APPS_HINT'				=> 'Vous aurez besoin d’une application d’authentification sur votre téléphone. Si vous n’en avez pas encore : sur iPhone, l’application intégrée <strong>Mots de passe</strong> convient, ou installez <strong>2FAS Auth</strong> (gratuite) ; sur Android, <strong>Aegis</strong> ou <strong>2FAS Auth</strong> (gratuites). Google Authenticator, Authy, 1Password et Bitwarden fonctionnent aussi.',
	'UCP_AURORA_TFA_SCAN_EXPLAIN'			=> 'Balayez ce code QR avec votre application d’authentification, puis saisissez le code à six chiffres affiché pour confirmer la configuration.',
	'UCP_AURORA_TFA_MANUAL_ENTRY'			=> 'Si vous ne pouvez pas balayer le code QR, saisissez cette clé secrète manuellement dans votre application :',
	'UCP_AURORA_TFA_FIRST_CODE'				=> 'Code de vérification',
	'UCP_AURORA_TFA_FIRST_CODE_EXPLAIN'		=> 'Le code à six chiffres actuellement affiché par votre application d’authentification.',
	'UCP_AURORA_TFA_CONFIRM_BUTTON'			=> 'Vérifier et activer',
	'UCP_AURORA_TFA_BAD_FIRST_CODE'			=> 'Le code saisi n’est pas valide. Vérifiez que vous avez bien balayé le code QR et que l’horloge de votre appareil est à l’heure, puis réessayez avec le code actuel.',
	'UCP_AURORA_TFA_PENDING_EXPIRED'		=> 'La session d’activation a expiré. Un nouveau code QR a été généré — balayez-le de nouveau dans votre application d’authentification.',

	// UCP — backup codes
	'UCP_AURORA_TFA_CODES_TITLE'			=> 'Vos codes de secours',
	'UCP_AURORA_TFA_ENABLED_SUCCESS'		=> 'L’authentification à deux facteurs est maintenant activée sur votre compte.',
	'UCP_AURORA_TFA_REGENERATED_SUCCESS'	=> 'Un nouveau jeu de codes de secours a été généré. Vos anciens codes ne fonctionnent plus.',
	'UCP_AURORA_TFA_CODES_EXPLAIN'			=> 'Chaque code de secours peut être utilisé <strong>une seule fois</strong> pour ouvrir une session si vous perdez l’accès à votre application d’authentification. Conservez-les en lieu sûr — ils ne sont affichés qu’une seule fois et ne pourront pas être réaffichés.',
	'UCP_AURORA_TFA_CODES_DOWNLOAD'			=> 'Télécharger en fichier texte',
	'UCP_AURORA_TFA_CODES_DONE'				=> 'J’ai sauvegardé mes codes de secours',

	// UCP — deactivation
	'UCP_AURORA_TFA_DISABLE_TITLE'			=> 'Désactiver l’authentification à deux facteurs',
	'UCP_AURORA_TFA_DISABLE_EXPLAIN'		=> 'Saisissez le mot de passe de votre compte pour confirmer. Votre enrôlement et tous vos codes de secours seront supprimés.',
	'UCP_AURORA_TFA_PASSWORD'				=> 'Mot de passe du compte',
	'UCP_AURORA_TFA_DISABLE_BUTTON'			=> 'Désactiver l’authentification à deux facteurs',
	'UCP_AURORA_TFA_BAD_PASSWORD'			=> 'Le mot de passe saisi n’est pas exact.',
	'UCP_AURORA_TFA_DISABLED_SUCCESS'		=> 'L’authentification à deux facteurs a été désactivée sur votre compte.',

	// UCP — backup code regeneration
	'UCP_AURORA_TFA_REGENERATE_TITLE'		=> 'Générer de nouveaux codes de secours',
	'UCP_AURORA_TFA_REGENERATE_EXPLAIN'		=> 'Saisissez le code actuel de votre application d’authentification pour confirmer. Vos anciens codes de secours cesseront de fonctionner.',
	'UCP_AURORA_TFA_CURRENT_CODE'			=> 'Code actuel de l’application',
	'UCP_AURORA_TFA_REGENERATE_BUTTON'		=> 'Générer de nouveaux codes',
	'UCP_AURORA_TFA_BAD_CODE'				=> 'Le code saisi n’est pas valide.',

	// Login verification page
	'AURORA_TFA_VERIFY_TITLE'				=> 'Vérification en deux étapes',
	'AURORA_TFA_VERIFY_INTRO'				=> 'Ce compte est protégé par l’authentification à deux facteurs. En plus de votre mot de passe, l’ouverture de session exige un code court que vous seul pouvez générer — ainsi, un mot de passe volé ne suffit pas pour entrer.',
	'AURORA_TFA_VERIFY_EXPLAIN'				=> 'Ouvrez l’application d’authentification sur votre téléphone (celle que vous avez configurée pour ce compte) et saisissez le code à six chiffres qu’elle affiche. Le code change toutes les 30 secondes.',
	'AURORA_TFA_BACKUP_EXPLAIN'				=> 'Saisissez l’un de vos codes de secours. Chaque code de secours ne fonctionne qu’une seule fois.',
	'AURORA_TFA_CODE'						=> 'Code de vérification',
	'AURORA_TFA_SUBMIT'						=> 'Vérifier',
	'AURORA_TFA_LOST_PHONE'					=> 'J’ai perdu mon téléphone — utiliser un code de secours',
	'AURORA_TFA_USE_AUTHENTICATOR'			=> 'Utiliser plutôt mon application d’authentification',
	'AURORA_TFA_BAD_LOGIN_CODE'				=> 'Le code saisi n’est pas valide. Veuillez réessayer.',
	'AURORA_TFA_CHALLENGE_EXPIRED'			=> 'Cette session de vérification a expiré. Veuillez ouvrir une nouvelle session.',
	'AURORA_TFA_SESSION_FAILED'				=> 'La session n’a pas pu être créée. Veuillez ouvrir une nouvelle session.',

	// ACP
	'ACP_AURORA_TFA_TITLE'					=> 'Authentification à deux facteurs',
	'ACP_AURORA_TFA_SETTINGS'				=> 'Paramètres',
	'ACP_AURORA_TFA_STATS'					=> 'Statistiques',
	'ACP_AURORA_TFA_AUDIT'					=> 'Journal d’audit',
	'ACP_AURORA_TFA_SETTINGS_EXPLAIN'		=> 'Politique d’authentification à deux facteurs pour l’ensemble du forum.',
	'ACP_AURORA_TFA_POLICY'					=> 'Politique',
	'ACP_AURORA_TFA_MODE'					=> 'Mode',
	'ACP_AURORA_TFA_MODE_EXPLAIN'			=> 'En mode « obligatoire », les membres des groupes sélectionnés ne peuvent pas naviguer sur le forum tant qu’ils n’ont pas activé l’authentification à deux facteurs.',
	'ACP_AURORA_TFA_MODE_DISABLED'			=> 'Désactivée — l’authentification à deux facteurs est coupée pour tout le forum',
	'ACP_AURORA_TFA_MODE_OPTIONAL'			=> 'Optionnelle — les utilisateurs peuvent l’activer dans leur panneau de contrôle',
	'ACP_AURORA_TFA_MODE_REQUIRED'			=> 'Obligatoire pour les groupes sélectionnés, optionnelle pour les autres',
	'ACP_AURORA_TFA_GROUPS'					=> 'Groupes soumis à l’authentification à deux facteurs obligatoire',
	'ACP_AURORA_TFA_GROUPS_EXPLAIN'			=> 'Ne s’applique qu’en mode « obligatoire ». Maintenez Ctrl (ou Cmd) pour sélectionner plusieurs groupes.',
	'ACP_AURORA_TFA_STATS_GLOBAL'			=> 'Utilisateurs avec l’authentification à deux facteurs activée',
	'ACP_AURORA_TFA_STATS_MEMBERS'			=> 'Membres',
	'ACP_AURORA_TFA_STATS_ENROLLED'			=> 'Enrôlés',
	'ACP_AURORA_TFA_AUDIT_EXPLAIN'			=> 'Événements récents d’authentification à deux facteurs (100 derniers) : activations, désactivations, régénérations de codes de secours et échecs de vérification à l’ouverture de session.',
	'ACP_AURORA_TFA_AUDIT_EVENT'			=> 'Événement',
	'ACP_AURORA_TFA_AUDIT_EMPTY'			=> 'Aucun événement d’authentification à deux facteurs enregistré pour le moment.',

	// Audit log entries (viewable in ACP)
	'LOG_AURORA_TFA_ENABLED'				=> '<strong>Authentification à deux facteurs activée</strong>',
	'LOG_AURORA_TFA_DISABLED'				=> '<strong>Authentification à deux facteurs désactivée</strong>',
	'LOG_AURORA_TFA_CODES_REGENERATED'		=> '<strong>Codes de secours d’authentification à deux facteurs régénérés</strong>',
	'LOG_AURORA_TFA_LOGIN_FAILED'			=> '<strong>Échec de l’authentification à deux facteurs à l’ouverture de session</strong>',
	'LOG_AURORA_TFA_CONFIG'					=> '<strong>Politique d’authentification à deux facteurs mise à jour</strong>',
]);
