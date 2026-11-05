<?php
/**
 *
 * Guest pseudonyms. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2026 Emma Laurijssens van Engelenhoven
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 * Listes de mots pour le générateur de noms fictifs (Story #21). Le fait de
 * vivre dans le pack de langue signifie que les pseudonymes sont générés dans
 * la langue du lecteur : la langue par défaut du forum pour les noms
 * automatiques ou générés a posteriori, et la propre langue du membre lorsqu’il
 * en régénère un depuis l’UCP. Les noms suivent le schéma Adjectif_Nom_Nom :
 * les deux listes sont donc combinées et le vivier vaut adjectifs x noms x (noms - 1).
 *
 * Conservez des entrées composées d’un seul mot simple (lettres uniquement),
 * chacune utilisable comme partie d’un nom d’utilisateur. Traducteurs :
 * remplacez par des mots naturels dans la langue ; la grammaire et l’accord
 * importent peu puisque les éléments sont reliés par des tirets bas.
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
		'Ambré', 'Azuré', 'Doré', 'Argenté', 'Cuivré', 'Bronzé', 'Vermeil', 'Pourpre',
		'Écarlate', 'Cramoisi', 'Turquoise', 'Corail', 'Rubis', 'Saphir', 'Émeraude', 'Ivoire',
		'Jade', 'Opalin', 'Nacré', 'Perlé', 'Cristallin', 'Limpide', 'Brillant', 'Lumineux',
		'Éclatant', 'Radieux', 'Vif', 'Vivace', 'Vivant', 'Pétillant', 'Enjoué', 'Joyeux',
		'Gai', 'Content', 'Souriant', 'Aimable', 'Gentil', 'Bienveillant', 'Généreux', 'Courtois',
		'Loyal', 'Fidèle', 'Honnête', 'Franc', 'Sincère', 'Humble', 'Modeste', 'Discret',
		'Prudent', 'Sage', 'Vigilant', 'Alerte', 'Agile', 'Preste', 'Rapide', 'Leste',
		'Adroit', 'Habile', 'Malin', 'Curieux', 'Audacieux', 'Brave', 'Vaillant', 'Hardi',
		'Fier', 'Noble', 'Robuste', 'Puissant', 'Stable', 'Paisible', 'Calme', 'Serein',
		'Tranquille', 'Placide', 'Silencieux', 'Doux', 'Tendre', 'Moelleux', 'Velouté', 'Lisse',
		'Frais', 'Pur', 'Libre', 'Léger', 'Beau', 'Élégant', 'Chaleureux', 'Chanceux',
		'Fabuleux', 'Céleste', 'Cosmique', 'Lunaire', 'Solaire', 'Stellaire', 'Étoilé', 'Polaire',
		'Alpin', 'Marin', 'Champêtre', 'Rustique', 'Fleuri', 'Verdoyant', 'Boisé', 'Ensoleillé',
		'Neigeux', 'Brumeux',
	],
	'GUESTPSEUDONYMS_NOUNS' => [
		'Loutre', 'Faucon', 'Blaireau', 'Marmotte', 'Héron', 'Lynx', 'Panda', 'Corbeau',
		'Moineau', 'Morse', 'Bison', 'Cobra', 'Dingo', 'Furet', 'Gecko', 'Lièvre',
		'Ibis', 'Chacal', 'Koala', 'Lémur', 'Suricate', 'Triton', 'Ocelot', 'Macareux',
		'Merle', 'Phoque', 'Tapir', 'Vipère', 'Belette', 'Yack', 'Zèbre', 'Pinson',
		'Mouette', 'Hérisson', 'Écureuil', 'Renard', 'Cerf', 'Chouette', 'Castor', 'Cygne',
		'Rocher', 'Cactus', 'Dune', 'Braise', 'Fjord', 'Glacier', 'Rivage', 'Île',
		'Forêt', 'Lagon', 'Prairie', 'Vallée', 'Saule', 'Canyon', 'Delta', 'Bosquet',
		'Marais', 'Crête', 'Ruisseau', 'Crique', 'Vallon', 'Colline', 'Source', 'Clairière',
		'Cascade', 'Sommet', 'Falaise', 'Roseau', 'Sentier', 'Étang', 'Récif', 'Mésa',
	],
]);
