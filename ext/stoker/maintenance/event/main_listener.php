<?php
/**
 *
 * Maintenance extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2026 stoker - https://phpbb3bbcodes.com/
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

declare(strict_types=1);

namespace stoker\maintenance\event;

use phpbb\auth\auth;
use phpbb\config\config;
use phpbb\language\language;
use phpbb\log\log_interface;
use phpbb\request\request_interface;
use phpbb\template\template;
use phpbb\user;
use stoker\maintenance\controller\maintenance_controller;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class main_listener implements EventSubscriberInterface
{
	/** @var int Hours before the start time at which the reload timer is armed */
	protected const DISABLE_TIMER_LEAD = 2;

	/** @var int Minutes before the start time at which the banner countdown is
	 * armed. Comfortably wider than the closing phase below, so a
	 * visitor sitting on a page loaded before the switch still sees
	 * it happen */
	protected const BANNER_TIMER_LEAD = 30;

	/** @var int Minutes before the start time over which the banner shows the
	 * countdown rather than the notice. Passed to the banner script,
	 * which makes the same switch on a page that was loaded before
	 * it */
	protected const BANNER_CLOSING_LEAD = 5;

	/** @var string Request parameter that renders the maintenance page for one
	 * request without taking the board offline */
	protected const PREVIEW_PARAM = 'stoker_maintenance_preview';

	/** @var string Request parameter that renders the pre-maintenance banner
	 * for one request without a real schedule */
	protected const PREVIEW_BANNER_PARAM = 'stoker_maintenance_preview_banner';

	/** @var int Seconds ahead of now used to invent a window for the banner
	 * preview when there is nothing real to show - comfortably past
	 * the countdown lead below, so the preview shows the everyday
	 * notice rather than dropping straight into the closing countdown */
	protected const PREVIEW_BANNER_LEAD = 3600;

	/** @var int Marker: the board is not offline because of the schedule */
	protected const MARKER_NONE = 0;

	/** @var int Marker: the board is offline because of the schedule */
	protected const MARKER_DISABLED = 1;

	/** @var int Marker: a disable is in flight - the intent has been recorded
	 * but board_disable has not been written yet */
	protected const MARKER_DISABLING = 2;

	/** @var auth */
	protected $auth;

	/** @var config */
	protected $config;

	/** @var language */
	protected $language;

	/** @var log_interface */
	protected $log;

	/** @var request_interface */
	protected $request;

	/** @var template */
	protected $template;

	/** @var user */
	protected $user;

	/** @var maintenance_controller */
	protected $maintenance_controller;

	/** @var bool|null Cached result of is_exempt(), computed at most once per
	 * request. Both assign_banner_vars() and assign_disable_timer_vars() can
	 * call it for the same request during the pre-maintenance window where
	 * their lead times overlap, and the answer cannot change mid-request. */
	protected $exempt;

	/**
	 * @param auth						$auth
	 * @param config					$config
	 * @param language					$language
	 * @param log_interface				$log
	 * @param request_interface			$request
	 * @param template					$template
	 * @param user						$user
	 * @param maintenance_controller	$maintenance_controller
	 */
	public function __construct(auth $auth, config $config, language $language, log_interface $log, request_interface $request, template $template, user $user, maintenance_controller $maintenance_controller)
	{
		$this->auth = $auth;
		$this->config = $config;
		$this->language = $language;
		$this->log = $log;
		$this->request = $request;
		$this->template = $template;
		$this->user = $user;
		$this->maintenance_controller = $maintenance_controller;
	}

	/**
	 * @return array
	 */
	public static function getSubscribedEvents(): array
	{
		return [
			'core.user_setup_after' => 'check_maintenance',
		];
	}

	/**
	 * Intercept board_disable before core's own check, or assign the
	 * pre-maintenance banner vars when the board is still live.
	 *
	 * @return void
	 */
	public function check_maintenance(): void
	{
		// core.user_setup_after also fires when user::setup() is called from
		// msg_handler(), where rendering a second page would break the message.
		if (defined('IN_ERROR_HANDLER') || defined('IN_INSTALL'))
		{
			return;
		}

		// Switched off in the ACP: the board is handed back to core, which
		// serves its own message when board_disable is set. Nothing below runs,
		// so the stylesheet is not loaded either.
		if (!$this->config['stoker_maintenance_enabled'])
		{
			return;
		}

		$disabled = $this->apply_schedule((bool) $this->config['board_disable']);

		// The ACP is never taken offline by us, but the schedule above still
		// had to run: without it an admin working only in the ACP would see a
		// stale "Disable board" state until someone hit the front end.
		if (defined('ADMIN_START'))
		{
			return;
		}

		// Same exemptions core applies in user::setup(). The permission checks
		// are last so they are skipped entirely on a board that is up.
		$skip_check = defined('IN_LOGIN') || defined('SKIP_CHECK_DISABLED');

		if (!$skip_check && $this->is_preview_request())
		{
			$this->maintenance_controller->set_preview(true);
			$this->maintenance_controller->display();
			// maintenance_controller::display() exits; return is unreachable in practice.
			return;
		}

		// Renders the banner on top of whatever page this request would
		// otherwise have served, rather than taking the board over, so the
		// admin sees it exactly as a visitor browsing the live board would.
		if (!$skip_check && $this->is_preview_banner_request())
		{
			$this->assign_preview_banner_vars();
			return;
		}

		if ($disabled && !$skip_check && !$this->is_exempt())
		{
			$this->maintenance_controller->display();
			// maintenance_controller::display() exits; return is unreachable in practice.
			return;
		}

		$this->assign_banner_vars();

		// Only worth arming while the board is still up; anyone already on the
		// maintenance page has the countdown instead.
		if (!$disabled)
		{
			$this->assign_disable_timer_vars($skip_check);
		}
	}

	/**
	 * Whether this request is an admin previewing the maintenance page.
	 *
	 * The preview renders the page for this one request only and touches
	 * nothing: board_disable, the schedule and the stored marker are all left
	 * exactly as they were.
	 *
	 * @return bool
	 */
	protected function is_preview_request(): bool
	{
		// The permission check is last so it is skipped entirely on the
		// overwhelming majority of requests, which carry no such parameter.
		return $this->request->variable(self::PREVIEW_PARAM, 0) === 1 && $this->auth->acl_get('a_board');
	}

	/**
	 * Whether this request is an admin previewing the pre-maintenance banner.
	 *
	 * Touches nothing: the schedule and its settings are left exactly as they
	 * were, whether or not a real one exists.
	 *
	 * @return bool
	 */
	protected function is_preview_banner_request(): bool
	{
		// The permission check is last so it is skipped entirely on the
		// overwhelming majority of requests, which carry no such parameter.
		return $this->request->variable(self::PREVIEW_BANNER_PARAM, 0) === 1 && $this->auth->acl_get('a_board');
	}

	/**
	 * Whether the visitor is one of those core lets through a disabled board.
	 *
	 * @return bool
	 */
	protected function is_exempt(): bool
	{
		if ($this->exempt === null)
		{
			$this->exempt = $this->auth->acl_gets('a_', 'm_') || $this->auth->acl_getf_global('m_');
		}

		return $this->exempt;
	}

	/**
	 * Bring board_disable in line with the maintenance window.
	 *
	 * @param bool $disabled Current board_disable state.
	 *
	 * This runs before any permission check, on requests from anonymous
	 * visitors. It is safe because it reads nothing but stored config and the
	 * clock - never introduce request input here.
	 *
	 * @return bool State of board_disable once the schedule has been applied.
	 */
	protected function apply_schedule(bool $disabled): bool
	{
		$start = (int) $this->config['stoker_maintenance_start'];

		// A marker only ever says that the outage currently running is this
		// extension's, so one sitting there while the board is up is stale and
		// must not be allowed to stand: read later it would have this
		// extension owning an outage somebody else caused.
		//
		// A "disabling" marker is always stale here - an earlier request
		// recorded the intent to take the board offline and never got as far
		// as doing it, so nothing came of it and it is started over.
		//
		// A "disabled" marker is stale unless the window it refers to is one
		// that could still be running. It cannot be if:
		//
		// no window - an open-ended run below keeps its marker after the
		// times are dropped, and it is spent the moment the
		// board comes back up
		// not opened - a window still ahead of us has never run, so no
		// outage of ours can belong to it
		//
		// Anything else is left alone: inside the window the marker is how a
		// manual re-enable is recognised.
		if (!$disabled)
		{
			$marker = (int) $this->config['stoker_maintenance_disabled_by_schedule'];

			if ($marker === self::MARKER_DISABLING)
			{
				$this->clear_marker();
			}
			else if ($marker === self::MARKER_DISABLED && ($start <= 0 || time() < $start))
			{
				$this->clear_marker();
			}
		}

		// Nothing scheduled, so a manual disable stands.
		if ($start <= 0)
		{
			return $disabled;
		}

		$end = (int) $this->config['stoker_maintenance_end'];
		$now = time();

		// Before the window: leave the board as it is, the banner covers this.
		if ($now < $start)
		{
			return $disabled;
		}

		// No end time given: switch the board off once, then retire the
		// schedule. Nothing switches it back on, and a later manual re-enable
		// is not undone on the next page view.
		//
		// Only the times are dropped. The marker stays, because the outage is
		// still ours and still running: without it, switching the extension
		// off - or disabling it outright - would leave the board stranded
		// behind core's message, which is the opposite of what the ACP says
		// happens. It is cleared at the top of this method once the board is
		// back up.
		if ($end <= $start)
		{
			if (!$disabled)
			{
				if (!$this->claim_disable())
				{
					// Another request holds the disable and has not finished
					// it yet. The times have to stand until it has: dropping
					// them here would leave nothing to run the window should
					// that request die between claiming and switching the
					// board off, and the marker it left behind is cleared at
					// the top of this method precisely so the next request can
					// start the disable over. It needs a schedule to start it
					// from.
					return true;
				}

				$this->switch_board('0', '1');

				$this->set_marker(self::MARKER_DISABLED);

				$this->log->add('admin', ANONYMOUS, '', 'LOG_ACP_STOKER_MAINTENANCE_AUTO_DISABLE');
			}
			else if ((int) $this->config['stoker_maintenance_disabled_by_schedule'] === self::MARKER_DISABLING)
			{
				// As inside a window: a disable that got as far as taking the
				// board offline but not as far as saying so is finished off
				// here. It matters more on this path, since the times are
				// about to be dropped and the top of this method only clears
				// a stale "disabling" once the board is back up.
				$this->set_marker(self::MARKER_DISABLED);
			}

			// Reached only once the outage is established - either this
			// request just made it, or it was already running. Claimed before
			// the times are dropped, for the same reason the marker is claimed
			// above: a schedule opens for every visitor at the same instant,
			// and only one of them should do the work.
			if ($this->config->set_atomic('stoker_maintenance_start', (string) $start, '0'))
			{
				$this->clear_schedule(true);
			}

			return true;
		}

		// Inside the window: the board belongs offline.
		if ($now < $end)
		{
			if ($disabled)
			{
				// A disable that got as far as taking the board offline but not
				// as far as saying so is finished off here, rather than left
				// half-written for the length of the window. Only a marker
				// already at "disabling" is promoted: a board someone took
				// offline by hand mid-window carries no marker and must not be
				// handed one.
				if ((int) $this->config['stoker_maintenance_disabled_by_schedule'] === self::MARKER_DISABLING)
				{
					$this->set_marker(self::MARKER_DISABLED);
				}

				return true;
			}

			// The board is up mid-window. If we are the ones who switched it
			// off, someone has since switched it back on - from Board Settings,
			// say - and that ends the maintenance. Without this the board would
			// be pulled offline again on the very next page view. A marker left
			// at "disabling" cannot reach this far: it is cleared at the top of
			// this method while the board is up.
			if ((int) $this->config['stoker_maintenance_disabled_by_schedule'] === self::MARKER_DISABLED)
			{
				// The start time is claimed before the schedule is retired, so
				// that of the visitors who arrive on the page view after the
				// re-enable only one does the clearing. The rest already know
				// the answer and would repeat the same writes, each one purging
				// the board-wide config cache again.
				if ($this->config->set_atomic('stoker_maintenance_start', (string) $start, '0'))
				{
					$this->clear_schedule();
				}

				return false;
			}

			// The outage and the marker saying the outage is ours are two
			// separate rows and cannot be written as one. A transaction does
			// not close the gap either: phpBB commits whatever is still open
			// when the connection is closed, so a request killed part way
			// through has its half-finished work landed rather than unwound.
			//
			// The intent is therefore recorded first, which leaves every
			// half-finished state saying plainly what happened:
			//
			// "disabling", board up - nothing came of it; cleared at the
			// top of this method and started over
			// "disabling", board down - the outage is ours, and is lifted at
			// the end of the window like any other
			//
			// Writing board_disable first would instead leave an outage with no
			// marker at all, which nothing would ever switch back on.
			if ($this->claim_disable())
			{
				$this->switch_board('0', '1');

				$this->set_marker(self::MARKER_DISABLED);

				$this->log->add('admin', ANONYMOUS, '', 'LOG_ACP_STOKER_MAINTENANCE_AUTO_DISABLE');
			}

			return true;
		}

		// Past the window: back online, but only if we are the ones who took
		// the board offline. A disable made by hand before the window opened
		// was never ours to lift, so it stands. Either way the spent schedule
		// is dropped, so a later manual disable is not undone on the next page
		// view.
		// Any marker other than "none" counts: a disable that got as far as
		// taking the board offline is ours whether or not it got as far as
		// saying so.
		$reenable = $disabled && (int) $this->config['stoker_maintenance_disabled_by_schedule'] !== self::MARKER_NONE;

		// The log entry belongs to the request that actually flipped the
		// switch. A window runs out for every visitor at the same instant - and
		// the countdown sends the idle ones back at that instant deliberately -
		// so without this gate every request arriving together would write its
		// own entry for the one transition. The disable paths above are gated
		// the same way, by claim_disable().
		//
		// $reenable stands either way: a request that loses here still reports
		// the board as back up, which is what it now is.
		if ($reenable && $this->switch_board('1', '0'))
		{
			$this->log->add('admin', ANONYMOUS, '', 'LOG_ACP_STOKER_MAINTENANCE_AUTO_ENABLE');
		}

		// A window runs out for every visitor at the same instant - and the
		// countdown sends the idle ones back at that instant deliberately - so
		// the end time is claimed before the schedule is retired. Only the
		// request that claims it does the clearing; the rest have already read
		// everything they need and would repeat the same writes, each one
		// purging the board-wide config cache again. switch_board() above keeps
		// its own copy in step either way, so a request that loses here still
		// reports the board as it now is.
		if ($this->config->set_atomic('stoker_maintenance_end', (string) $end, '0'))
		{
			$this->clear_schedule();
		}

		return $disabled && !$reenable;
	}

	/**
	 * Flip board_disable, tolerating another request having got there first.
	 *
	 * @param string $from Expected current value.
	 * @param string $to Value to write.
	 *
	 * @return bool Whether this request is the one that made the change.
	 */
	protected function switch_board(string $from, string $to): bool
	{
		// Conditional update: only the first request through here writes.
		if ($this->config->set_atomic('board_disable', $from, $to))
		{
			return true;
		}

		// Another request already made the change and already purged the config
		// cache for it. Nothing is left to write: this request only has to
		// agree with core's own check further down, and array access does
		// exactly that and no more. config\db does not override offsetSet(), so
		// the value goes no further than this request's copy - no query, no
		// purge.
		//
		// set() would be the wrong tool even with caching turned off: it routes
		// through set_atomic() with no expected value, so it issues an
		// unconditional UPDATE - one per request that loses this race, each of
		// them able to overwrite a board_disable somebody changed in the
		// meantime.
		$this->config['board_disable'] = $to;

		return false;
	}

	/**
	 * Record the intent to take the board offline, unless another request
	 * already has.
	 *
	 * The first write of the disable sequence doubles as the gate over it. A
	 * schedule opens for every visitor at the same instant - and the reload
	 * timer sends the idle ones back at that instant deliberately - so without
	 * this every request arriving together would run the whole sequence, each
	 * one an update plus a purge of the board-wide config cache.
	 *
	 * A request that loses here does nothing further: the winner is taking the
	 * board offline, and the caller reports it as offline either way. Should
	 * the winner die before writing board_disable, the marker it left behind is
	 * cleared at the top of apply_schedule() and the next request starts over.
	 *
	 * @return bool Whether this request is the one that claimed the disable.
	 */
	protected function claim_disable(): bool
	{
		return $this->config->set_atomic('stoker_maintenance_disabled_by_schedule', (string) self::MARKER_NONE, (string) self::MARKER_DISABLING);
	}

	/**
	 * Record how far the schedule has got with taking the board offline.
	 *
	 * @param int $state One of the MARKER_* constants.
	 *
	 * @return void
	 */
	protected function set_marker(int $state): void
	{
		if ((int) $this->config['stoker_maintenance_disabled_by_schedule'] !== $state)
		{
			$this->config->set('stoker_maintenance_disabled_by_schedule', $state);
		}
	}

	/**
	 * Drop the marker, unless another request has moved it on in the meantime.
	 *
	 * Conditional on the value this request read, because the marker is the
	 * one thing standing between an outage and the code that lifts it. A
	 * request judging a marker stale has read it a moment earlier; by the time
	 * it writes, the request that owns that marker may have promoted it to a
	 * live outage, and an unconditional write would take that away and leave
	 * the board offline with nothing left to bring it back.
	 *
	 * A request that loses here leaves the marker alone. Its own copy is the
	 * stale one, and nothing reads it again afterwards.
	 *
	 * @return void
	 */
	protected function clear_marker(): void
	{
		$marker = (int) $this->config['stoker_maintenance_disabled_by_schedule'];

		if ($marker === self::MARKER_NONE)
		{
			return;
		}

		$this->config->set_atomic('stoker_maintenance_disabled_by_schedule', (string) $marker, (string) self::MARKER_NONE);
	}

	/**
	 * @param bool $keep_marker Whether to leave the marker in place, for an
	 * outage that is still running once its schedule
	 * has been retired.
	 *
	 * @return void
	 */
	protected function clear_schedule(bool $keep_marker = false): void
	{
		$keys = [
			'stoker_maintenance_start',
			'stoker_maintenance_end',
		];

		$clear = [];

		foreach ($keys as $key)
		{
			if ((int) $this->config[$key] !== 0)
			{
				$clear[] = $key;
			}
		}

		// Every set() that is allowed to use the cache throws away the whole
		// config cache, so all but the last write skip it and one purge covers
		// the lot.
		$remaining = count($clear);

		foreach ($clear as $key)
		{
			$this->config->set($key, 0, --$remaining === 0);
		}

		// Last, and through clear_marker() rather than the batch above, so it
		// stays conditional on the value this request read. The times are gone
		// by this point either way; a request that dies here leaves an outage
		// that still says it is ours, which is recoverable, where the reverse
		// order would leave one nothing would ever lift.
		if (!$keep_marker)
		{
			$this->clear_marker();
		}
	}

	/**
	 * Assign the pre-maintenance banner, composed from the schedule so it
	 * follows each visitor's language and date format.
	 *
	 * @return void
	 */
	protected function assign_banner_vars(): void
	{
		if (!$this->config['stoker_maintenance_banner_enabled'])
		{
			return;
		}

		$start = (int) $this->config['stoker_maintenance_start'];
		$now = time();

		// Nothing scheduled, or the window has already opened.
		if ($start <= 0 || $now >= $start)
		{
			return;
		}

		$lead_hours = (int) $this->config['stoker_maintenance_banner_lead_hours'];

		// A lead time of zero means "as soon as a start time is set", so the
		// lower bound is only applied when one was given.
		if ($lead_hours > 0 && $now < ($start - ($lead_hours * 3600)))
		{
			return;
		}

		// Normal board pages do not load this extension's language file.
		$this->language->add_lang('common', 'stoker/maintenance');

		$end = (int) $this->config['stoker_maintenance_end'];

		$this->template->assign_vars($this->build_banner_vars($start, $end));
	}

	/**
	 * Render the pre-maintenance banner for one request, using the real
	 * schedule where it would already show something and an invented window
	 * otherwise. Touches nothing: the schedule and its settings are left
	 * exactly as they were.
	 *
	 * @return void
	 */
	protected function assign_preview_banner_vars(): void
	{
		$this->language->add_lang('common', 'stoker/maintenance');

		$start = (int) $this->config['stoker_maintenance_start'];
		$now = time();

		// A real window still ahead of us is shown as it is. Anything else -
		// none set, or one that has already opened - gets a window invented
		// for the occasion.
		if ($start <= 0 || $now >= $start)
		{
			$start = $now + self::PREVIEW_BANNER_LEAD;
		}

		$end = (int) $this->config['stoker_maintenance_end'];

		if ($end <= $start)
		{
			$end = 0;
		}

		$this->template->assign_vars($this->build_banner_vars($start, $end));
	}

	/**
	 * Compose the banner template vars for a given window.
	 *
	 * @param int $start
	 * @param int $end
	 *
	 * @return array
	 */
	protected function build_banner_vars(int $start, int $end): array
	{
		$now = time();

		$message = $end > $start
			? $this->language->lang('STOKER_MAINTENANCE_BANNER_WINDOW', $this->user->format_date($start, '', true), $this->user->format_date($end, '', true))
			: $this->language->lang('STOKER_MAINTENANCE_BANNER_PLANNED', $this->user->format_date($start, '', true));

		$vars = [
			'S_STOKER_MAINTENANCE_ASSETS'		=> true,
			'S_STOKER_MAINTENANCE_BANNER'		=> true,
			'STOKER_MAINTENANCE_BANNER_MESSAGE'	=> $message,
			'U_STOKER_MAINTENANCE_BANNER'		=> (string) $this->config['stoker_maintenance_banner_url'],
		];

		// Over the last minutes the banner gives way to a countdown. Armed
		// ahead of the switch rather than at it, so a visitor already sitting
		// on the page sees it happen; held back until the window is close, so
		// the script is absent from almost every page view that carries a
		// banner - the lead time above may be a month, the countdown is
		// minutes of it.
		if ($now >= ($start - (self::BANNER_TIMER_LEAD * 60)))
		{
			$vars += [
				'S_STOKER_MAINTENANCE_BANNER_TIMER'			=> true,
				'STOKER_MAINTENANCE_BANNER_START'			=> $start,
				'STOKER_MAINTENANCE_BANNER_NOW'				=> $now,
				'STOKER_MAINTENANCE_BANNER_CLOSING_LEAD'	=> self::BANNER_CLOSING_LEAD * 60,
				'STOKER_MAINTENANCE_BANNER_COUNTDOWN'		=> $this->language->lang('STOKER_MAINTENANCE_BANNER_CLOSING', '<span id="stoker-maintenance-banner-clock" aria-live="off">' . $this->format_clock($start - $now) . '</span>'),

				// A page loaded inside the closing phase is served in that
				// state rather than switched into it once the script runs,
				// which would show the notice for a frame first.
				'S_STOKER_MAINTENANCE_BANNER_CLOSING'		=> $now >= ($start - (self::BANNER_CLOSING_LEAD * 60)),
			];
		}

		return $vars;
	}

	/**
	 * Format a number of seconds as the countdown's mm:ss, so the banner is
	 * rendered with the digits it will carry rather than a placeholder.
	 *
	 * @param int $seconds
	 *
	 * @return string
	 */
	protected function format_clock(int $seconds): string
	{
		$seconds = max(0, min(self::BANNER_CLOSING_LEAD * 60, $seconds));

		return sprintf('%02d:%02d', intdiv($seconds, 60), $seconds % 60);
	}

	/**
	 * Arm a client-side reload shortly before the board goes offline, so a
	 * visitor sitting on an idle page lands on the maintenance page without
	 * having to click first.
	 *
	 * @param bool $skip_check Whether core would let this request through a
	 * disabled board regardless of permissions.
	 *
	 * @return void
	 */
	protected function assign_disable_timer_vars(bool $skip_check): void
	{
		$start = (int) $this->config['stoker_maintenance_start'];
		$now = time();

		// Nothing scheduled, or the window has already opened.
		if ($start <= 0 || $now >= $start)
		{
			return;
		}

		// Held back until the window is close, so the markup is absent from
		// almost every page view the board ever serves.
		if ($now < ($start - (self::DISABLE_TIMER_LEAD * 3600)))
		{
			return;
		}

		// Nobody who will still be able to read the board is reloaded. The
		// permission checks are last so they only ever run inside the lead
		// window, not on every page view the board serves.
		if ($skip_check || $this->is_exempt())
		{
			return;
		}

		$this->template->assign_vars([
			'S_STOKER_MAINTENANCE_DISABLE_TIMER'	=> true,
			'STOKER_MAINTENANCE_DISABLE_START'		=> $start,
			'STOKER_MAINTENANCE_DISABLE_NOW'		=> $now,
		]);
	}
}
