/**
 *
 * Topic Ban extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2026 stoker - https://phpbb3bbcodes.com/
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

(function() {
	'use strict';

	var length = document.getElementById('topicban_length');
	var until = document.getElementById('topicban_until');

	if (!length || !until) {
		return;
	}

	function toggleUntil(focus) {
		var enabled = length.value === '-1';

		until.style.display = enabled ? '' : 'none';
		until.required = enabled;

		if (enabled && focus) {
			until.focus();
		}
	}

	// Field is rendered visible so it works without JavaScript
	toggleUntil(false);

	length.addEventListener('change', function() {
		toggleUntil(true);
	});
})();
