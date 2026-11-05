<?php
/**
 *
 * Guest pseudonyms. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2026 Emma Laurijssens van Engelenhoven
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 * Word lists for the bogus-name generator (Story #21). Living in the language
 * pack means pseudonyms are generated in the reader's language: the board
 * default for automatic/backfilled names, the member's own language when they
 * regenerate from the UCP. Names use the Adjective_Noun_Noun scheme, so both
 * lists are combined and the pool is adjectives x nouns x (nouns - 1).
 *
 * Keep the entries single plain words (letters only), each usable as part of a
 * username. Translators: replace with words natural to the language; grammar/
 * agreement does not matter since parts are joined with underscores.
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
		'Amber', 'Azure', 'Bold', 'Brave', 'Bright', 'Brisk', 'Calm', 'Clever',
		'Cosmic', 'Crimson', 'Curious', 'Daring', 'Dawn', 'Deft', 'Dusky', 'Eager',
		'Earnest', 'Easy', 'Emerald', 'Fabled', 'Fair', 'Fancy', 'Fierce', 'Fleet',
		'Fond', 'Free', 'Fresh', 'Gentle', 'Gilded', 'Glad', 'Golden', 'Grand',
		'Happy', 'Hardy', 'Hazel', 'Hidden', 'Honest', 'Humble', 'Ivory', 'Jade',
		'Jolly', 'Keen', 'Kind', 'Lively', 'Lucid', 'Lucky', 'Lunar', 'Mellow',
		'Merry', 'Mighty', 'Mild', 'Misty', 'Modest', 'Noble', 'Nimble', 'Olive',
		'Placid', 'Plucky', 'Polar', 'Proud', 'Pure', 'Quaint', 'Quiet', 'Rapid',
		'Regal', 'Ruby', 'Rustic', 'Sable', 'Sage', 'Scarlet', 'Serene', 'Sharp',
		'Silent', 'Silver', 'Sleek', 'Sleepy', 'Smooth', 'Snowy', 'Solar', 'Spry',
		'Steady', 'Stellar', 'Stormy', 'Sunny', 'Swift', 'Teal', 'Tidy', 'Trusty',
		'Umber', 'Upbeat', 'Valiant', 'Velvet', 'Verdant', 'Vivid', 'Warm', 'Wise',
		'Witty', 'Zany', 'Zesty', 'Zippy',
	],
	'GUESTPSEUDONYMS_NOUNS' => [
		'Otter', 'Falcon', 'Badger', 'Marmot', 'Heron', 'Lynx', 'Panda', 'Raven',
		'Sparrow', 'Walrus', 'Bison', 'Cobra', 'Dingo', 'Ferret', 'Gecko', 'Hare',
		'Ibis', 'Jackal', 'Koala', 'Lemur', 'Meerkat', 'Newt', 'Ocelot', 'Puffin',
		'Quokka', 'Robin', 'Seal', 'Tapir', 'Urchin', 'Viper', 'Weasel', 'Yak',
		'Zebra', 'Finch', 'Gull', 'Moth', 'Wren', 'Vole', 'Stoat', 'Pika',
		'Boulder', 'Cactus', 'Dune', 'Ember', 'Fjord', 'Glacier', 'Harbor', 'Island',
		'Jungle', 'Kelp', 'Lagoon', 'Meadow', 'Nebula', 'Oasis', 'Prairie', 'Quarry',
		'Reef', 'Summit', 'Thicket', 'Valley', 'Willow', 'Canyon', 'Delta', 'Grove',
		'Marsh', 'Ridge', 'Brook', 'Cove', 'Hollow', 'Mesa',
	],
]);
