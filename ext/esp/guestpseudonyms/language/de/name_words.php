<?php
/**
 *
 * Guest pseudonyms. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2026 Emma Laurijssens van Engelenhoven
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 * Wortlisten für den Generator erfundener Namen (Story #21). Da sie im
 * Sprachpaket liegen, werden Pseudonyme in der Sprache des Lesers erzeugt: die
 * Standardsprache des Forums für automatische/nachträglich erzeugte Namen, die
 * eigene Sprache des Mitglieds, wenn es im UCP neu erzeugt. Die Namen folgen
 * dem Schema Adjektiv_Substantiv_Substantiv, daher werden beide Listen
 * kombiniert und der Pool umfasst Adjektive x Substantive x (Substantive - 1).
 *
 * Die Einträge bleiben einzelne einfache Wörter (nur Buchstaben), jedes als
 * Teil eines Benutzernamens verwendbar. Übersetzer: durch Wörter ersetzen, die
 * in der jeweiligen Sprache natürlich klingen; Grammatik/Übereinstimmung spielt
 * keine Rolle, da die Teile mit Unterstrichen verbunden werden.
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
	'GUESTPSEUDONYMS_ADJECTIVES' => [
		'Golden', 'Silbern', 'Goldig', 'Sanft', 'Munter', 'Heiter', 'Freundlich', 'Tapfer',
		'Mutig', 'Klug', 'Weise', 'Fröhlich', 'Ruhig', 'Still', 'Leise', 'Flink',
		'Schnell', 'Rasch', 'Hell', 'Klar', 'Frisch', 'Kühn', 'Edel', 'Fein',
		'Zart', 'Warm', 'Mild', 'Sonnig', 'Wach', 'Emsig', 'Fleißig', 'Gütig',
		'Herzlich', 'Freudig', 'Glücklich', 'Lustig', 'Frei', 'Rein', 'Schlicht', 'Bescheiden',
		'Ehrlich', 'Treu', 'Standhaft', 'Geduldig', 'Neugierig', 'Verspielt', 'Lebhaft', 'Lebendig',
		'Prächtig', 'Stattlich', 'Königlich', 'Glänzend', 'Schimmernd', 'Strahlend', 'Leuchtend', 'Funkelnd',
		'Blühend', 'Grünend', 'Duftend', 'Frostig', 'Verschneit', 'Neblig', 'Wolkig', 'Windig',
		'Stürmisch', 'Sommerlich', 'Herbstlich', 'Winterlich', 'Waldig', 'Blumig', 'Grasgrün', 'Himmelblau',
		'Meergrün', 'Türkis', 'Purpurn', 'Scharlachrot', 'Rosig', 'Smaragdgrün', 'Saphirblau', 'Schneeweiß',
		'Rubinrot', 'Kupfern', 'Bronzen', 'Kristallklar', 'Gelassen', 'Besonnen', 'Aufmerksam', 'Aufgeweckt',
		'Wagemutig', 'Beherzt', 'Redlich', 'Anmutig', 'Zierlich', 'Behände', 'Geschwind', 'Hurtig',
		'Wohlgemut', 'Gutmütig', 'Liebenswert', 'Großzügig',
	],
	'GUESTPSEUDONYMS_NOUNS' => [
		'Otter', 'Fuchs', 'Dachs', 'Reh', 'Hirsch', 'Hase', 'Igel', 'Eule',
		'Falke', 'Reiher', 'Rabe', 'Specht', 'Fink', 'Amsel', 'Drossel', 'Meise',
		'Schwalbe', 'Star', 'Lerche', 'Kranich', 'Storch', 'Schwan', 'Möwe', 'Luchs',
		'Wiesel', 'Marder', 'Biber', 'Murmeltier', 'Eichhörnchen', 'Robbe', 'Delfin', 'Seehund',
		'Pinguin', 'Kolibri', 'Hummel', 'Libelle', 'Falter', 'Käfer', 'Molch', 'Frosch',
		'Fels', 'Berg', 'Hügel', 'Gipfel', 'Tal', 'Fluss', 'Bach', 'Quelle',
		'Teich', 'See', 'Bucht', 'Insel', 'Strand', 'Düne', 'Wald', 'Hain',
		'Wiese', 'Moor', 'Heide', 'Schlucht', 'Höhle', 'Gletscher', 'Lichtung', 'Farn',
		'Klee', 'Moos', 'Eiche', 'Birke', 'Tanne', 'Weide',
	],
]);
