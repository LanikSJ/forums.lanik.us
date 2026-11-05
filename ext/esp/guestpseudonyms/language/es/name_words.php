<?php
/**
 *
 * Guest pseudonyms. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2026 Emma Laurijssens van Engelenhoven
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 * Listas de palabras para el generador de nombres ficticios (Story #21). Al
 * vivir en el paquete de idioma, los seudónimos se generan en el idioma del
 * lector: el idioma predeterminado del foro para los nombres automáticos o
 * rellenados, y el idioma del propio miembro cuando lo regenera desde el UCP.
 * Los nombres usan el esquema Adjetivo_Sustantivo_Sustantivo, así que ambas
 * listas se combinan y el conjunto es adjetivos x sustantivos x (sustantivos - 1).
 *
 * Mantén las entradas como palabras simples (solo letras), cada una utilizable
 * como parte de un nombre de usuario. Traductores: sustituid por palabras
 * naturales del idioma; la gramática y la concordancia no importan, ya que las
 * partes se unen con guiones bajos.
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
		'Ambarino', 'Azul', 'Dorado', 'Plateado', 'Verde', 'Carmesí', 'Escarlata', 'Turquesa',
		'Índigo', 'Violeta', 'Nacarado', 'Cobrizo', 'Rosado', 'Azulado', 'Verdoso', 'Perlado',
		'Amable', 'Valiente', 'Audaz', 'Sereno', 'Sabio', 'Noble', 'Alegre', 'Feliz',
		'Vivaz', 'Ágil', 'Astuto', 'Brillante', 'Radiante', 'Sincero', 'Humilde', 'Fiel',
		'Leal', 'Cordial', 'Gentil', 'Tranquilo', 'Plácido', 'Manso', 'Dulce', 'Fresco',
		'Cálido', 'Suave', 'Puro', 'Claro', 'Límpido', 'Lúcido', 'Grácil', 'Airoso',
		'Garboso', 'Ligero', 'Rápido', 'Veloz', 'Raudo', 'Vigoroso', 'Robusto', 'Fornido',
		'Firme', 'Constante', 'Osado', 'Intrépido', 'Bravo', 'Recio', 'Digno', 'Regio',
		'Majestuoso', 'Espléndido', 'Grandioso', 'Ilustre', 'Célebre', 'Fabuloso', 'Mítico', 'Estelar',
		'Cósmico', 'Lunar', 'Solar', 'Polar', 'Boreal', 'Silvestre', 'Florido', 'Frondoso',
		'Risueño', 'Jovial', 'Contento', 'Dichoso', 'Afable', 'Bondadoso', 'Generoso', 'Cortés',
		'Discreto', 'Modesto', 'Prudente', 'Sensato', 'Apacible', 'Quieto', 'Callado', 'Nítido',
		'Diáfano', 'Luminoso', 'Brioso', 'Vívido',
	],
	'GUESTPSEUDONYMS_NOUNS' => [
		'Nutria', 'Halcón', 'Tejón', 'Marmota', 'Garza', 'Lince', 'Panda', 'Cuervo',
		'Gorrión', 'Bisonte', 'Hurón', 'Liebre', 'Chacal', 'Koala', 'Lémur', 'Suricata',
		'Ocelote', 'Frailecillo', 'Petirrojo', 'Foca', 'Tapir', 'Comadreja', 'Cebra', 'Pinzón',
		'Gaviota', 'Chochín', 'Armiño', 'Búho', 'Lechuza', 'Ciervo', 'Corzo', 'Alce',
		'Reno', 'Erizo', 'Ardilla', 'Castor', 'Delfín', 'Golondrina', 'Alondra', 'Ruiseñor',
		'Peñasco', 'Cactus', 'Duna', 'Fiordo', 'Glaciar', 'Bahía', 'Isla', 'Selva',
		'Laguna', 'Pradera', 'Nebulosa', 'Oasis', 'Llanura', 'Arrecife', 'Cumbre', 'Valle',
		'Sauce', 'Cañón', 'Arboleda', 'Marisma', 'Arroyo', 'Cala', 'Meseta', 'Colina',
		'Cascada', 'Manantial', 'Bosque', 'Roble', 'Abeto', 'Helecho',
	],
]);
