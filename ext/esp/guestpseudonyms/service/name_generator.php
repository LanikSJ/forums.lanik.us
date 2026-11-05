<?php
/**
 *
 * Guest pseudonyms. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2026 Emma Laurijssens van Engelenhoven
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace esp\guestpseudonyms\service;

use phpbb\language\language_file_loader;

/**
 * Story #21 — generates Adjective_Noun_Noun bogus names from word lists that
 * live in the language pack, so a name is produced in a chosen language:
 * the board default for automatic/backfilled names, the member's own language
 * when they regenerate from the UCP.
 *
 * Uniqueness is enforced by the pseudonym_manager; this class only produces
 * candidate strings. Seed the RNG via seed() for reproducible output in tests.
 */
class name_generator
{
	const EXTENSION = 'esp/guestpseudonyms';

	/** @var language_file_loader */
	protected $loader;

	/** @var array<string,array{0:string[],1:string[]}> per-ISO [adjectives, nouns] */
	protected $words = [];

	/**
	 * @param language_file_loader $loader
	 */
	public function __construct(language_file_loader $loader)
	{
		$this->loader = $loader;
	}

	/**
	 * Seed the RNG for reproducible output (tests).
	 *
	 * @param int $seed
	 */
	public function seed($seed)
	{
		mt_srand((int) $seed);
	}

	/**
	 * Produce one candidate name in the given language, e.g. "Bright_Otter_Canyon".
	 *
	 * @param string $iso Language ISO code (falls back to English).
	 * @return string
	 */
	public function generate($iso)
	{
		list($adjectives, $nouns) = $this->words_for($iso);

		if (empty($adjectives) || count($nouns) < 2)
		{
			// Safety net if the word lists could not be loaded.
			return 'Member_' . mt_rand(1000, 99999999);
		}

		$adjective = $adjectives[mt_rand(0, count($adjectives) - 1)];
		$first = mt_rand(0, count($nouns) - 1);
		do
		{
			$second = mt_rand(0, count($nouns) - 1);
		}
		while ($second === $first);

		return $adjective . '_' . $nouns[$first] . '_' . $nouns[$second];
	}

	/**
	 * Load and cache the [adjectives, nouns] word lists for a language.
	 *
	 * @param string $iso
	 * @return array{0:string[],1:string[]}
	 */
	protected function words_for($iso)
	{
		if (!isset($this->words[$iso]))
		{
			$lang = [];
			// [$iso, 'en'] => use the requested language, fall back to English.
			$this->loader->load_extension(self::EXTENSION, 'name_words', [$iso, 'en'], $lang);

			$this->words[$iso] = [
				$this->word_list($lang, 'GUESTPSEUDONYMS_ADJECTIVES'),
				$this->word_list($lang, 'GUESTPSEUDONYMS_NOUNS'),
			];
		}

		return $this->words[$iso];
	}

	/**
	 * @param array  $lang
	 * @param string $key
	 * @return string[]
	 */
	protected function word_list(array $lang, $key)
	{
		return (isset($lang[$key]) && is_array($lang[$key])) ? array_values($lang[$key]) : [];
	}
}
