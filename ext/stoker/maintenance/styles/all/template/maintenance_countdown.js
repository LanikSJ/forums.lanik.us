(function() {
	'use strict';

	var el = document.getElementById('stoker-maintenance-countdown');
	if (!el) {
		return;
	}

	var start = parseInt(el.getAttribute('data-start'), 10) * 1000;
	var end = parseInt(el.getAttribute('data-end'), 10) * 1000;
	var serverNow = parseInt(el.getAttribute('data-now'), 10) * 1000;
	var canReload = el.getAttribute('data-reload') === '1';

	// Offset between the server's clock (at page load) and this browser's clock,
	// so the countdown is correct even if the visitor's system clock is wrong.
	var clockOffset = serverNow - Date.now();

	// How long the closing message stands in for the countdown, in
	// milliseconds. Matches the pause the re-opening message takes before the
	// reload, so the two read as a pair.
	var CLOSING_DISPLAY = 2000;

	var liveEl = document.getElementById('stoker-maintenance-live');
	var closingEl = document.getElementById('stoker-maintenance-closing');
	var reopenEl = document.getElementById('stoker-maintenance-reopening');
	var progressEl = el.querySelector('.stoker-maintenance-progress');
	var progressBar = el.querySelector('.stoker-maintenance-progress-bar');
	var valueEls = {
		days: el.querySelector('[data-unit="days"]'),
		hours: el.querySelector('[data-unit="hours"]'),
		minutes: el.querySelector('[data-unit="minutes"]'),
		seconds: el.querySelector('[data-unit="seconds"]')
	};

	var intervalId = null;
	var finished = false;

	function pad(num) {
		return num < 10 ? '0' + num : String(num);
	}

	function finish() {
		if (finished) {
			return;
		}

		finished = true;

		if (intervalId !== null) {
			window.clearInterval(intervalId);
			intervalId = null;
		}

		if (!canReload) {
			valueEls.days.textContent = '00';
			valueEls.hours.textContent = '00';
			valueEls.minutes.textContent = '00';
			valueEls.seconds.textContent = '00';

			// The progress bar is only rendered once the window has opened.
			if (progressBar && progressEl) {
				progressBar.style.transform = 'scaleX(1)';
				progressEl.setAttribute('aria-valuenow', '100');
			}

			return;
		}

		if (liveEl) {
			liveEl.hidden = true;
		}

		if (reopenEl) {
			reopenEl.hidden = false;
		}

		window.setTimeout(function() {
			window.location.reload();
		}, 2000);
	}

	function tick() {
		var now = Date.now() + clockOffset;
		var remaining = end - now;

		if (remaining <= 0) {
			finish();
			return;
		}

		var totalSeconds = Math.floor(remaining / 1000);
		var days = Math.floor(totalSeconds / 86400);
		var hours = Math.floor((totalSeconds % 86400) / 3600);
		var minutes = Math.floor((totalSeconds % 3600) / 60);
		var seconds = totalSeconds % 60;

		valueEls.days.textContent = pad(days);
		valueEls.hours.textContent = pad(hours);
		valueEls.minutes.textContent = pad(minutes);
		valueEls.seconds.textContent = pad(seconds);

		if (!progressBar || !progressEl) {
			return;
		}

		var totalDuration = end - start;
		var elapsed = now - start;
		var fraction = totalDuration > 0 ? Math.min(1, Math.max(0, elapsed / totalDuration)) : 0;
		progressBar.style.transform = 'scaleX(' + fraction + ')';
		progressEl.setAttribute('aria-valuenow', String(Math.round(fraction * 100)));
	}

	function run() {
		tick();

		if (!finished) {
			intervalId = window.setInterval(tick, 1000);
		}
	}

	// The markup is only emitted in the first seconds of a window, and the
	// server hides the countdown for as long as it stands there. Nothing is
	// counted in the meantime: the swap comes first, so the timer never has to
	// tick into a panel the visitor cannot see.
	if (closingEl && liveEl) {
		window.setTimeout(function() {
			closingEl.hidden = true;
			liveEl.hidden = false;
			run();
		}, CLOSING_DISPLAY);

		return;
	}

	run();
})();
