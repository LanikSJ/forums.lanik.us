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
	'UCP_GUESTPRIVACY'                       => 'Confidentialité du nom',

	'GUESTPSEUDONYMS_YOUR_NICKNAME'          => 'Votre pseudonyme public',
	'GUESTPSEUDONYMS_NICKNAME_EXPLAIN'       => 'Il s’agit du nom affiché aux visiteurs non connectés (et aux moteurs de recherche), à la place de votre nom d’utilisateur. Vous ne le verrez que rarement vous-même.',
	'GUESTPSEUDONYMS_REGENERATE'             => 'Générer un nouveau pseudonyme',
	'GUESTPSEUDONYMS_REGENERATE_EXPLAIN'     => 'Si vous préférez un pseudonyme différent, générez-en un nouveau. Il sera créé dans votre langue.',
	'GUESTPSEUDONYMS_VISIBILITY'             => 'Ce que voient les invités et les moteurs de recherche',
	'GUESTPSEUDONYMS_VISIBILITY_EXPLAIN'     => 'Choisissez la part de votre identité visible par les visiteurs non connectés — invités et moteurs de recherche. Les membres connectés voient toujours votre véritable nom d’utilisateur et votre signature.',
	'GUESTPSEUDONYMS_LEVEL_1'                => 'Masquer mon nom et ma signature',
	'GUESTPSEUDONYMS_LEVEL_2'                => 'Afficher mon véritable nom d’utilisateur, mais masquer ma signature',
	'GUESTPSEUDONYMS_LEVEL_3'                => 'Afficher mon véritable nom d’utilisateur et ma signature',

	'GUESTPSEUDONYMS_SAVED'                  => 'Vos paramètres de confidentialité du nom ont été enregistrés.',
	'GUESTPSEUDONYMS_REGENERATED'            => 'Un nouveau pseudonyme public a été généré pour vous.',

	// Opt-out reminder banner (Story #24)
	'GUESTPSEUDONYMS_REMINDER_TITLE'         => 'Rappel :',
	'GUESTPSEUDONYMS_REMINDER'               => 'votre véritable nom d’utilisateur est actuellement affiché aux invités et aux moteurs de recherche.',
	'GUESTPSEUDONYMS_REMINDER_LINK'          => 'Vérifier vos paramètres de confidentialité du nom',

	// Commande console (bin/phpbbcli.php esp:guestpseudonyms:backfill)
	'CLI_DESCRIPTION_GUESTPSEUDONYMS_BACKFILL' => 'Génère un pseudonyme public pour chaque membre qui n’en a pas encore.',
	'CLI_GUESTPSEUDONYMS_BACKFILL_DONE'      => [
		0 => 'Tous les membres ont déjà un pseudonyme public.',
		1 => '%d pseudonyme public généré.',
		2 => '%d pseudonymes publics générés.',
	],
]);
