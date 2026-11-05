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
	'ACP_GUESTPRIVACY_TITLE'      => 'Gastpseudoniemen',
	'ACP_GUESTPRIVACY_SETTINGS'   => 'Instellingen',
	'ACP_GUESTPRIVACY_EXPLAIN'    => 'Bepaal hoe gebruikersnamen van leden verborgen worden voor gasten en zoekmachines, en welke keuzes leden hebben over hun eigen bijnaam voor gasten.',

	'GP_ENABLE'                   => 'Gastpseudoniemen inschakelen',
	'GP_ENABLE_EXPLAIN'           => 'Als dit is ingeschakeld ziet elke bezoeker die niet is ingelogd — gasten én zoekmachine-bots — een nepbijnaam in plaats van de gebruikersnaam van elk lid.',
	'GP_RESOLVE_NAMES'            => 'Namen in citaten en vermeldingen uit het account halen',
	'GP_RESOLVE_NAMES_EXPLAIN'    => 'Voor ingelogde leden tonen citaten en @-vermeldingen de huidige gebruikersnaam van het betreffende lid, opgehaald uit het account in plaats van de naam die in het bericht is opgeslagen. Als je een gebruiker hernoemt (bijvoorbeeld naar Anon20260101 bij een verzoek om accountverwijdering) worden oude citaten en vermeldingen dan automatisch bijgewerkt, zonder aanpassingen in de database. Uit: citaten behouden de naam zoals die is geschreven.',

	'GP_MEMBER_CONTROLS'          => 'Keuzes voor leden',
	'GP_ALLOW_REGENERATE'         => 'Leden mogen hun bijnaam opnieuw maken',
	'GP_ALLOW_REGENERATE_EXPLAIN' => 'Leden kunnen via hun Persoonlijke Instellingen een andere bijnaam voor gasten aanmaken.',
	'GP_ALLOW_OPT_OUT'            => 'Leden mogen zichzelf tonen',
	'GP_ALLOW_OPT_OUT_EXPLAIN'    => 'Laat leden op hun Naamprivacy-pagina kiezen om hun echte gebruikersnaam aan gasten en zoekmachines te tonen — en, als ze willen, ook hun handtekening. Als dit uit staat blijft elk lid beschermd (bijnaam, handtekening verborgen), zonder keuze.',
	'GP_REMINDER_MONTHS'          => 'Herinneringsinterval afmelding (maanden)',
	'GP_REMINDER_MONTHS_EXPLAIN'  => 'Herinner afgemelde leden zo vaak dat hun echte naam openbaar is. Zet op 0 om herinneringen uit te schakelen.',
	'GP_AUTOREVERT'               => 'Weer verbergen als een herinnering wordt genegeerd',
	'GP_AUTOREVERT_EXPLAIN'       => 'Als een afgemeld lid na een herinnering niet opnieuw bevestigt, wordt de naam automatisch weer verborgen (veilige standaard).',

	'GUESTPSEUDONYMS_ACP_SAVED'   => 'De instellingen voor gastpseudoniemen zijn opgeslagen.',

	// Admin log entry written when these settings are saved.
	'LOG_GUESTPSEUDONYMS_SETTINGS' => 'Instellingen voor gastpseudoniemen gewijzigd',
]);
