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
	'ACP_GUESTPRIVACY_TITLE'      => 'Gäste-Pseudonyme',
	'ACP_GUESTPRIVACY_SETTINGS'   => 'Einstellungen',
	'ACP_GUESTPRIVACY_EXPLAIN'    => 'Legen Sie fest, wie die Benutzernamen der Mitglieder vor Gästen und Suchmaschinen verborgen werden und welche Wahlmöglichkeiten die Mitglieder bei ihrem eigenen Gäste-Spitznamen haben.',

	'GP_ENABLE'                   => 'Gäste-Pseudonyme aktivieren',
	'GP_ENABLE_EXPLAIN'           => 'Wenn diese Option aktiviert ist, sieht jeder nicht angemeldete Besucher — Gäste wie auch Suchmaschinen-Bots — anstelle des Benutzernamens jedes Mitglieds einen erfundenen Spitznamen.',
	'GP_RESOLVE_NAMES'            => 'Namen in Zitaten und Erwähnungen aus dem Konto auflösen',
	'GP_RESOLVE_NAMES_EXPLAIN'    => 'Für angemeldete Mitglieder zeigen Zitate und @-Erwähnungen den aktuellen Benutzernamen des genannten Mitglieds an, der aus dessen Konto aufgelöst wird und nicht aus dem im Beitrag gespeicherten Namen. Wird ein Benutzer umbenannt (zum Beispiel in Anon20260101 bei einer Anfrage zur Kontolöschung), werden alte Zitate und Erwähnungen dadurch automatisch aktualisiert, ohne Eingriffe in die Datenbank. Ist die Option deaktiviert, behalten Zitate den Namen so bei, wie er geschrieben wurde.',

	'GP_MEMBER_CONTROLS'          => 'Wahlmöglichkeiten der Mitglieder',
	'GP_ALLOW_REGENERATE'         => 'Mitgliedern erlauben, ihren Spitznamen neu zu erzeugen',
	'GP_ALLOW_REGENERATE_EXPLAIN' => 'Mitglieder können über ihr Benutzer-Kontrollzentrum einen anderen Gäste-Spitznamen erzeugen.',
	'GP_ALLOW_OPT_OUT'            => 'Mitgliedern erlauben, sich zu zeigen',
	'GP_ALLOW_OPT_OUT_EXPLAIN'    => 'Mitglieder können auf ihrer Namensschutz-Seite wählen, Gästen und Suchmaschinen ihren echten Benutzernamen zu zeigen — und, wenn sie möchten, auch ihre Signatur. Ist dies deaktiviert, bleibt jedes Mitglied geschützt (Spitzname, Signatur verborgen), ohne Wahl.',
	'GP_REMINDER_MONTHS'          => 'Erinnerungsintervall beim Abwählen (Monate)',
	'GP_REMINDER_MONTHS_EXPLAIN'  => 'Mitglieder, die abgewählt haben, in diesem Abstand daran erinnern, dass ihr echter Name öffentlich ist. Auf 0 setzen, um Erinnerungen zu deaktivieren.',
	'GP_AUTOREVERT'               => 'Erneut schützen, wenn eine Erinnerung ignoriert wird',
	'GP_AUTOREVERT_EXPLAIN'       => 'Wenn ein abgewähltes Mitglied nach einer Erinnerung keine erneute Bestätigung gibt, wird sein Name automatisch wieder verborgen (fail-closed).',

	'GUESTPSEUDONYMS_ACP_SAVED'   => 'Die Einstellungen für Gäste-Pseudonyme wurden gespeichert.',

	// Admin log entry written when these settings are saved.
	'LOG_GUESTPSEUDONYMS_SETTINGS' => 'Einstellungen für Gäste-Pseudonyme geändert',
]);
