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
	'UCP_GUESTPRIVACY'                  => 'Naamprivacy',

	'GUESTPSEUDONYMS_YOUR_NICKNAME'     => 'Je bijnaam voor gasten',
	'GUESTPSEUDONYMS_NICKNAME_EXPLAIN'  => 'Dit is de naam die wordt getoond aan bezoekers die niet zijn ingelogd (en aan zoekmachines), in plaats van je gebruikersnaam. Je ziet hem zelf zelden.',
	'GUESTPSEUDONYMS_REGENERATE'        => 'Maak een nieuwe bijnaam',
	'GUESTPSEUDONYMS_REGENERATE_EXPLAIN' => 'Wil je liever een andere bijnaam, maak er dan een nieuwe aan. Deze wordt in jouw taal gemaakt.',
	'GUESTPSEUDONYMS_VISIBILITY'         => 'Wat gasten en zoekmachines zien',
	'GUESTPSEUDONYMS_VISIBILITY_EXPLAIN' => 'Kies hoeveel van je identiteit zichtbaar is voor bezoekers die niet zijn ingelogd — gasten en zoekmachines. Ingelogde leden zien altijd je echte gebruikersnaam en handtekening.',
	'GUESTPSEUDONYMS_LEVEL_1'           => 'Verberg mijn naam én handtekening',
	'GUESTPSEUDONYMS_LEVEL_2'           => 'Toon mijn echte gebruikersnaam, maar verberg mijn handtekening',
	'GUESTPSEUDONYMS_LEVEL_3'           => 'Toon mijn echte gebruikersnaam én handtekening',

	'GUESTPSEUDONYMS_SAVED'             => 'Je naamprivacy-instellingen zijn opgeslagen.',
	'GUESTPSEUDONYMS_REGENERATED'       => 'Er is een nieuwe bijnaam voor je gemaakt.',

	// Herinneringsbanner afmelding (Story #24)
	'GUESTPSEUDONYMS_REMINDER_TITLE'    => 'Herinnering:',
	'GUESTPSEUDONYMS_REMINDER'          => 'je echte gebruikersnaam wordt momenteel getoond aan gasten en zoekmachines.',
	'GUESTPSEUDONYMS_REMINDER_LINK'     => 'Bekijk je naamprivacy-instellingen',

	// Console-opdracht (bin/phpbbcli.php esp:guestpseudonyms:backfill)
	'CLI_DESCRIPTION_GUESTPSEUDONYMS_BACKFILL' => 'Maak een bijnaam voor gasten aan voor elk lid dat er nog geen heeft.',
	'CLI_GUESTPSEUDONYMS_BACKFILL_DONE' => [
		0 => 'Elk lid heeft al een bijnaam voor gasten.',
		1 => '%d bijnaam voor gasten aangemaakt.',
		2 => '%d bijnamen voor gasten aangemaakt.',
	],
]);
