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
	'ACP_GUESTPRIVACY_TITLE'      => 'Pseudonymes pour invités',
	'ACP_GUESTPRIVACY_SETTINGS'   => 'Paramètres',
	'ACP_GUESTPRIVACY_EXPLAIN'    => 'Contrôlez la manière dont les noms d’utilisateur des membres sont masqués aux invités et aux moteurs de recherche, ainsi que les choix dont disposent les membres concernant leur propre pseudonyme public.',

	'GP_ENABLE'                   => 'Activer les pseudonymes pour invités',
	'GP_ENABLE_EXPLAIN'           => 'Lorsque cette option est activée, chaque visiteur non connecté — invités comme robots des moteurs de recherche — voit un pseudonyme fictif à la place du nom d’utilisateur de chaque membre.',
	'GP_RESOLVE_NAMES'            => 'Résoudre les noms des citations et des mentions à partir du compte',
	'GP_RESOLVE_NAMES_EXPLAIN'    => 'Pour les membres connectés, les citations et les @mentions affichent le nom d’utilisateur actuel du membre référencé, résolu à partir de son compte plutôt que le nom enregistré dans le message. Renommer un utilisateur (par exemple en Anon20260101 lors d’une demande de suppression de compte) met alors à jour automatiquement les anciennes citations et mentions, sans modification de la base de données. Lorsque cette option est désactivée, les citations conservent le nom tel qu’il a été écrit.',

	'GP_MEMBER_CONTROLS'          => 'Choix des membres',
	'GP_ALLOW_REGENERATE'         => 'Autoriser les membres à régénérer leur pseudonyme',
	'GP_ALLOW_REGENERATE_EXPLAIN' => 'Les membres peuvent générer un pseudonyme public différent depuis leur panneau de contrôle de l’utilisateur.',
	'GP_ALLOW_OPT_OUT'            => 'Autoriser les membres à se montrer',
	'GP_ALLOW_OPT_OUT_EXPLAIN'    => 'Laissez les membres choisir, sur leur page Confidentialité du nom, d’afficher leur véritable nom d’utilisateur aux invités et aux moteurs de recherche — et, s’ils le souhaitent, leur signature aussi. Lorsque cette option est désactivée, chaque membre reste protégé (pseudonyme, signature masquée), sans choix.',
	'GP_REMINDER_MONTHS'          => 'Intervalle de rappel de désinscription (mois)',
	'GP_REMINDER_MONTHS_EXPLAIN'  => 'Rappeler aux membres désinscrits, à cette fréquence, que leur véritable nom est public. Réglez sur 0 pour désactiver les rappels.',
	'GP_AUTOREVERT'               => 'Reprotéger si un rappel est ignoré',
	'GP_AUTOREVERT_EXPLAIN'       => 'Si un membre désinscrit ne confirme pas de nouveau après un rappel, masquer automatiquement son nom (fermeture sécurisée).',

	'GUESTPSEUDONYMS_ACP_SAVED'   => 'Les paramètres des pseudonymes pour invités ont été enregistrés.',

	// Admin log entry written when these settings are saved.
	'LOG_GUESTPSEUDONYMS_SETTINGS' => 'Paramètres des pseudonymes publics modifiés',
]);
