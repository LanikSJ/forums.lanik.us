<?php
/**
 *
 * Guest pseudonyms. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2026 Emma Laurijssens van Engelenhoven
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 * Nederlandse woordenlijsten voor de nepnaam-generator. Namen krijgen de vorm
 * Bijvoeglijk_Zelfstandig_Zelfstandig; de delen worden met liggende streepjes
 * verbonden, dus verbuiging/grammatica doet er niet toe.
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
		'Amber', 'Azuren', 'Blauw', 'Blij', 'Bont', 'Braaf', 'Bronzen', 'Dapper',
		'Donker', 'Edel', 'Eerlijk', 'Fel', 'Fier', 'Fris', 'Geel', 'Gouden',
		'Groen', 'Groot', 'Grijs', 'Helder', 'Hoog', 'Kalm', 'Klein', 'Koel',
		'Koen', 'Kwiek', 'Licht', 'Lief', 'Lila', 'Luchtig', 'Machtig', 'Mild',
		'Mistig', 'Moedig', 'Nobel', 'Olijk', 'Paars', 'Pienter', 'Prachtig',
		'Puur', 'Rap', 'Rein', 'Rood', 'Roze', 'Rustig', 'Sierlijk', 'Slim',
		'Sluw', 'Snel', 'Sober', 'Statig', 'Sterk', 'Stil', 'Stoer', 'Stormig',
		'Tenger', 'Trots', 'Vief', 'Vlot', 'Vrij', 'Vrolijk', 'Vurig', 'Waardig',
		'Warm', 'Wijs', 'Wild', 'Wit', 'Zacht', 'Zilveren', 'Zonnig', 'Zwart',
		'Zwierig',
	],
	'GUESTPSEUDONYMS_NOUNS' => [
		'Otter', 'Vos', 'Das', 'Reiger', 'Havik', 'Uil', 'Mus', 'Zwaan', 'Egel',
		'Bever', 'Ree', 'Hert', 'Vlinder', 'Libel', 'Kikker', 'Salamander',
		'Eekhoorn', 'Marter', 'Hermelijn', 'Bunzing', 'Konijn', 'Haas', 'Mol',
		'Spreeuw', 'Vink', 'Lijster', 'Specht', 'Buizerd', 'Valk', 'Zwaluw',
		'Gans', 'Eend', 'Zeehond', 'Bruinvis', 'Dolfijn', 'Zeester', 'Krab',
		'Duin', 'Heide', 'Bos', 'Woud', 'Meer', 'Beek', 'Rivier', 'Berg',
		'Vallei', 'Bron', 'Weide', 'Moeras', 'Rots', 'Kust', 'Strand', 'Eiland',
		'Baai', 'Gletsjer', 'Struik', 'Wilg', 'Riet', 'Klif', 'Gracht', 'Polder',
		'Dijk', 'Ven', 'Kreek', 'Delta', 'Kaap', 'Vijver',
	],
]);
