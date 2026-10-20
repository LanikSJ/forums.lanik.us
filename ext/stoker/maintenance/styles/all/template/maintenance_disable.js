(function() {
	'use strict';

	var el = document.getElementById('stoker-maintenance-disable-timer');
	if (!el) {
		return;
	}

	var start = parseInt(el.getAttribute('data-start'), 10) * 1000;
	var serverNow = parseInt(el.getAttribute('data-now'), 10) * 1000;

	if (isNaN(start) || isNaN(serverNow)) {
		return;
	}

	// Both timestamps come from the server, so the wait is the distance between
	// them and the visitor's own clock never enters into it. It is measured from
	// when the page was generated rather than from now, which leaves the reload
	// a page load's worth of transit time late - the safe direction, since by
	// then the board is offline rather than still up.
	var delay = start - serverNow;

	// The markup is only emitted while the start time is still ahead, so a
	// non-positive delay means something is off - leave the page alone rather
	// than reload into a board that is still up.
	if (delay <= 0) {
		return;
	}

	window.setTimeout(function() {
		window.location.reload();
	}, delay);
})();
