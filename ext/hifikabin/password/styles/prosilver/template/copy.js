(function () {
	'use strict';
	document.body.addEventListener('click', copy, true);

	function copy(e) {
		var t = e.target;
		var c = t.dataset.copytarget;
		var inp = c ? document.querySelector(c) : null;
		if (!inp || !inp.value) return;

		navigator.clipboard.writeText(inp.value).catch(function (err) {
			console.error('Copy failed:', err);
		});
	}
})();