<?php
/**
 *
 * Guest pseudonyms. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2026 Emma Laurijssens van Engelenhoven
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 * Listas de palavras para o gerador de nomes fictícios (Story #21). Ao residir
 * no pacote de idioma, os pseudónimos são gerados no idioma do leitor: o idioma
 * predefinido do fórum para nomes automáticos/preenchidos, e o próprio idioma do
 * membro quando este regenera a partir do UCP. Os nomes usam o esquema
 * Adjetivo_Substantivo_Substantivo, pelo que ambas as listas são combinadas e o
 * conjunto é adjetivos x substantivos x (substantivos - 1).
 *
 * Mantenha as entradas como palavras simples e isoladas (apenas letras), cada
 * uma utilizável como parte de um nome de utilizador. Tradutores: substituam por
 * palavras naturais ao idioma; a gramática/concordância não importa, uma vez que
 * as partes são unidas com sublinhados.
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
		'Âmbar', 'Azul', 'Ousado', 'Corajoso', 'Brilhante', 'Calmo', 'Esperto', 'Cósmico',
		'Carmesim', 'Curioso', 'Audaz', 'Ágil', 'Sereno', 'Dourado', 'Prateado', 'Nobre',
		'Gentil', 'Humilde', 'Honesto', 'Livre', 'Alegre', 'Amável', 'Sábio', 'Radiante',
		'Tranquilo', 'Manso', 'Suave', 'Puro', 'Vivaz', 'Solar', 'Lunar', 'Estelar',
		'Polar', 'Nevado', 'Célere', 'Rápido', 'Veloz', 'Ligeiro', 'Firme', 'Valente',
		'Verde', 'Esmeralda', 'Turquesa', 'Rubi', 'Jade', 'Coral', 'Marfim', 'Índigo',
		'Violeta', 'Escarlate', 'Púrpura', 'Cobalto', 'Bronze', 'Cristalino', 'Luminoso', 'Reluzente',
		'Fulgente', 'Cintilante', 'Ditoso', 'Contente', 'Jovial', 'Feliz', 'Cordial', 'Afável',
		'Meigo', 'Terno', 'Doce', 'Ameno', 'Plácido', 'Quieto', 'Silencioso', 'Discreto',
		'Modesto', 'Digno', 'Leal', 'Fiel', 'Justo', 'Grato', 'Ledo', 'Garboso',
		'Airoso', 'Elegante', 'Gracioso', 'Belo', 'Formoso', 'Verdejante', 'Frondoso', 'Florido',
		'Vernal', 'Matinal', 'Boreal', 'Austral', 'Celeste', 'Etéreo', 'Marinho', 'Campestre',
		'Silvestre', 'Montês', 'Sincero', 'Bondoso',
	],
	'GUESTPSEUDONYMS_NOUNS' => [
		'Lontra', 'Falcão', 'Texugo', 'Marmota', 'Garça', 'Lince', 'Panda', 'Corvo',
		'Pardal', 'Morsa', 'Bisonte', 'Naja', 'Furão', 'Osga', 'Lebre', 'Íbis',
		'Chacal', 'Coala', 'Lémure', 'Suricata', 'Tritão', 'Jaguar', 'Foca', 'Tapir',
		'Víbora', 'Doninha', 'Iaque', 'Zebra', 'Tentilhão', 'Gaivota', 'Mocho', 'Toupeira',
		'Arminho', 'Esquilo', 'Raposa', 'Gamo', 'Corça', 'Golfinho', 'Andorinha', 'Rouxinol',
		'Rochedo', 'Penedo', 'Duna', 'Brasa', 'Fiorde', 'Geleira', 'Enseada', 'Ilha',
		'Selva', 'Lagoa', 'Prado', 'Nebulosa', 'Oásis', 'Pradaria', 'Recife', 'Cume',
		'Vale', 'Salgueiro', 'Desfiladeiro', 'Bosque', 'Ribeiro', 'Colina', 'Serra', 'Cascata',
		'Riacho', 'Trevo', 'Musgo', 'Loureiro', 'Carvalho', 'Pinheiro',
	],
]);
