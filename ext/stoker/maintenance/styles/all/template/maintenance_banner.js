(function() {
	'use strict';

	var el = document.getElementById('stoker-maintenance-banner');
	if (!el) {
		return;
	}

	var noticeEl = document.getElementById('stoker-maintenance-banner-notice');
	var countdownEl = document.getElementById('stoker-maintenance-banner-countdown');
	var clockEl = document.getElementById('stoker-maintenance-banner-clock');

	if (!noticeEl || !countdownEl || !clockEl) {
		return;
	}

	var start = parseInt(el.getAttribute('data-start'), 10) * 1000;
	var serverNow = parseInt(el.getAttribute('data-now'), 10) * 1000;

	// How long the banner spends as a countdown before the board goes offline.
	// Read from the server rather than kept here, so the moment a page already
	// open switches into the closing state is the same one a page loaded then
	// would be served in.
	var redPhase = parseInt(el.getAttribute('data-closing'), 10) * 1000;

	if (isNaN(start) || isNaN(serverNow) || isNaN(redPhase)) {
		return;
	}

	// Offset between the server's clock (at page load) and this browser's clock,
	// so the countdown is correct even if the visitor's system clock is wrong.
	var clockOffset = serverNow - Date.now();

	var intervalId = null;

	function pad(num) {
		return num < 10 ? '0' + num : String(num);
	}

	function tick() {
		var remaining = start - (Date.now() + clockOffset);

		if (remaining < 0) {
			remaining = 0;
		}

		var total = Math.ceil(remaining / 1000);

		clockEl.textContent = pad(Math.floor(total / 60)) + ':' + pad(total % 60);

		// At zero the board is going offline and the reload armed alongside
		// this countdown is on its way, so the digits simply stand until it
		// lands rather than counting into negatives.
		if (remaining <= 0 && intervalId !== null) {
			window.clearInterval(intervalId);
			intervalId = null;
		}
	}

	function begin() {
		// The server serves a page loaded inside the closing phase in that
		// state already, so this repeats what is on the page rather than
		// changing it. classList, so the class is not added twice.
		noticeEl.hidden = true;
		countdownEl.hidden = false;
		el.classList.add('stoker-maintenance-banner-closing');

		// Started before the first tick, so a tick that finds nothing left to
		// count can clear it again straight away.
		intervalId = window.setInterval(tick, 1000);

		tick();
	}

	var lead = start - (Date.now() + clockOffset) - redPhase;

	// The markup is emitted for a wider window than the countdown runs for, so
	// most visitors wait here first. One that loaded the page inside the
	// closing phase was served in that state already and starts straight away.
	if (lead <= 0) {
		begin();
	} else {
		window.setTimeout(begin, lead);
	}
})();
