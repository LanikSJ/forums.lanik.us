<?php
/**
 *
 * Guest pseudonyms. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2026 Emma Laurijssens van Engelenhoven
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace esp\guestpseudonyms\console;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use esp\guestpseudonyms\service\pseudonym_manager;

/**
 * Story #4 (B3) — CLI: generate a nickname for every member that does not have
 * one yet (in the board default language).
 *
 *   php bin/phpbbcli.php esp:guestpseudonyms:backfill
 */
class backfill extends \phpbb\console\command\command
{
	/** @var \phpbb\db\driver\driver_interface */
	protected $db;

	/** @var pseudonym_manager */
	protected $manager;

	/** @var string */
	protected $table;

	/** @var string */
	protected $users_table;

	public function __construct(\phpbb\user $user, \phpbb\db\driver\driver_interface $db, pseudonym_manager $manager, $table_prefix)
	{
		$this->db = $db;
		$this->manager = $manager;
		$this->table = $table_prefix . 'pseudonyms';
		$this->users_table = $table_prefix . 'users';

		// configure() runs from the parent constructor, so the extension's
		// language file has to be in place before that.
		$user->add_lang_ext('esp/guestpseudonyms', 'common');

		parent::__construct($user);
	}

	protected function configure()
	{
		$this
			->setName('esp:guestpseudonyms:backfill')
			->setDescription($this->user->lang('CLI_DESCRIPTION_GUESTPSEUDONYMS_BACKFILL'));
	}

	protected function execute(InputInterface $input, OutputInterface $output)
	{
		$sql = $this->db->sql_build_query('SELECT', [
			'SELECT'    => 'u.user_id',
			'FROM'      => [$this->users_table => 'u'],
			'LEFT_JOIN' => [
				[
					'FROM' => [$this->table => 'p'],
					'ON'   => 'u.user_id = p.user_id',
				],
			],
			// USER_IGNORE is the bot accounts. They are shown under their own
			// bot name and never need a nickname, so generating one for each
			// just inflates the table and the count the command reports.
			'WHERE'     => 'p.user_id IS NULL
				AND u.user_id <> ' . ANONYMOUS . '
				AND u.user_type <> ' . USER_IGNORE . "
				AND u.username <> ''",
		]);
		$result = $this->db->sql_query($sql);

		$count = 0;
		while ($row = $this->db->sql_fetchrow($result))
		{
			$this->manager->get_bogus((int) $row['user_id']);
			$count++;
		}
		$this->db->sql_freeresult($result);

		$output->writeln('<info>' . $this->user->lang('CLI_GUESTPSEUDONYMS_BACKFILL_DONE', $count) . '</info>');

		return 0;
	}
}
