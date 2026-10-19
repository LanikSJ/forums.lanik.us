/* global jQuery, phpbb, document, navigator, window */

// AJAX toggle switches (settings page)
(function ($) {
	'use strict';

	if (typeof $ === 'undefined') {
		return;
	}

	function toggleSwitch($toggle) {
		if ($toggle.hasClass('ajax-busy')) {
			return;
		}
		$toggle.addClass('ajax-busy');
		var url = $toggle.data('toggle-url');
		var $indicator = phpbb.loadingIndicator();
		$.get(url, function (res) {
			if (res && res.success) {
				var checkbox = $toggle.find('input[type="checkbox"]')[0];
				if (checkbox) {
					checkbox.checked = !checkbox.checked;
					$toggle.attr('aria-checked', checkbox.checked);
				}
			}
		}, 'json').fail(function () {
			phpbb.alert('', $toggle.data('toggle-error'));
		}).always(function () {
			$toggle.removeClass('ajax-busy');
			if ($indicator) {
				$indicator.fadeOut(100);
			}
		});
	}

	$(document).on('click', '.toggle-switch[data-toggle-url]', function (e) {
		e.preventDefault();
		toggleSwitch($(this));
	});

	$(document).on('keydown', '.toggle-switch[data-toggle-url]', function (e) {
		if (e.key === ' ' || e.key === 'Enter') {
			e.preventDefault();
			toggleSwitch($(this));
		}
	});
})(typeof jQuery !== 'undefined' ? jQuery : undefined);

// Server configuration: server-type toggle + copy buttons (vanilla)
(function () {
	'use strict';

	function ready(fn) {
		if (document.readyState !== 'loading') {
			fn();
		} else {
			document.addEventListener('DOMContentLoaded', fn);
		}
	}

	function legacyCopy(target) {
		try {
			target.removeAttribute('readonly');
			target.focus();
			target.select();
			var ok = document.execCommand('copy');
			window.getSelection().removeAllRanges();
			return ok;
		} catch (e) {
			return false;
		} finally {
			// Restored here, not after execCommand: a throw anywhere above would
			// otherwise leave the textarea editable for the rest of the page.
			target.setAttribute('readonly', 'readonly');
		}
	}

	ready(function () {
		var selector = document.getElementById('stoker_server_type');
		var apache = document.getElementById('stoker-server-apache');
		var nginx = document.getElementById('stoker-server-nginx');

		if (selector && apache && nginx) {
			selector.addEventListener('change', function () {
				var isNginx = this.value === 'nginx';
				apache.classList.toggle('stoker-hidden', isNginx);
				nginx.classList.toggle('stoker-hidden', !isNginx);
			});
		}

		var buttons = document.querySelectorAll('.stoker-copy');

		Array.prototype.forEach.call(buttons, function (button) {
			button.addEventListener('click', function () {
				var target = document.getElementById(button.getAttribute('data-copy'));

				if (!target) {
					return;
				}

				var original = button.getAttribute('data-label-copy');
				var copied = button.getAttribute('data-label-copied');
				var failed = button.getAttribute('data-label-failed');

				var done = function (ok) {
					button.textContent = ok ? copied : failed;
					window.setTimeout(function () {
						button.textContent = original;
					}, 2000);
				};

				if (navigator.clipboard && navigator.clipboard.writeText) {
					navigator.clipboard.writeText(target.value).then(function () {
						done(true);
					}, function () {
						done(legacyCopy(target));
					});
				} else {
					done(legacyCopy(target));
				}
			});
		});
	});
})();
