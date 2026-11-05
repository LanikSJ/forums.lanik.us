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
	'UCP_GUESTPRIVACY'                       => 'Namensschutz',

	'GUESTPSEUDONYMS_YOUR_NICKNAME'          => 'Dein Gäste-Spitzname',
	'GUESTPSEUDONYMS_NICKNAME_EXPLAIN'       => 'Dieser Name wird Besuchern angezeigt, die nicht angemeldet sind (und Suchmaschinen), anstelle deines Benutzernamens. Du selbst wirst ihn nur selten zu Gesicht bekommen.',
	'GUESTPSEUDONYMS_REGENERATE'             => 'Neuen Spitznamen erzeugen',
	'GUESTPSEUDONYMS_REGENERATE_EXPLAIN'     => 'Wenn dir ein anderer Spitzname lieber wäre, erzeuge einfach einen neuen. Er wird in deiner Sprache erstellt.',
	'GUESTPSEUDONYMS_VISIBILITY'             => 'Was Gäste und Suchmaschinen sehen',
	'GUESTPSEUDONYMS_VISIBILITY_EXPLAIN'     => 'Wähle, wie viel von deiner Identität für nicht angemeldete Besucher — Gäste und Suchmaschinen — sichtbar ist. Angemeldete Mitglieder sehen immer deinen echten Benutzernamen und deine Signatur.',
	'GUESTPSEUDONYMS_LEVEL_1'                => 'Meinen Namen und meine Signatur verbergen',
	'GUESTPSEUDONYMS_LEVEL_2'                => 'Meinen echten Benutzernamen zeigen, aber meine Signatur verbergen',
	'GUESTPSEUDONYMS_LEVEL_3'                => 'Meinen echten Benutzernamen und meine Signatur zeigen',

	'GUESTPSEUDONYMS_SAVED'                  => 'Deine Namensschutz-Einstellungen wurden gespeichert.',
	'GUESTPSEUDONYMS_REGENERATED'            => 'Es wurde ein neuer Gäste-Spitzname für dich erzeugt.',

	// Opt-out reminder banner (Story #24)
	'GUESTPSEUDONYMS_REMINDER_TITLE'         => 'Erinnerung:',
	'GUESTPSEUDONYMS_REMINDER'               => 'dein echter Benutzername wird derzeit Gästen und Suchmaschinen angezeigt.',
	'GUESTPSEUDONYMS_REMINDER_LINK'          => 'Deine Namensschutz-Einstellungen überprüfen',

	// Konsolenbefehl (bin/phpbbcli.php esp:guestpseudonyms:backfill)
	'CLI_DESCRIPTION_GUESTPSEUDONYMS_BACKFILL' => 'Erzeugt für jedes Mitglied ohne Gäste-Spitznamen einen solchen.',
	'CLI_GUESTPSEUDONYMS_BACKFILL_DONE'      => [
		0 => 'Alle Mitglieder haben bereits einen Gäste-Spitznamen.',
		1 => '%d Gäste-Spitzname erzeugt.',
		2 => '%d Gäste-Spitznamen erzeugt.',
	],
]);
