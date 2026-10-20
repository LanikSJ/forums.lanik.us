(function($) {
	'use strict';

	function toggleSwitch($toggle) {
		if ($toggle.hasClass('ajax-busy')) {
			return;
		}

		var confirmMsg = $toggle.data('toggle-confirm');

		// Only asked when the box is about to be checked - reversing the
		// setting (e.g. re-enabling the board) needs no confirmation, only
		// turning it on does.
		if (confirmMsg) {
			var currentCheckbox = $toggle.find('input[type="checkbox"]')[0];
			if (currentCheckbox && !currentCheckbox.checked && !window.confirm(confirmMsg)) {
				return;
			}
		}

		$toggle.addClass('ajax-busy');
		var url = $toggle.data('toggle-url');
		var $indicator = phpbb.loadingIndicator();
		$.get(url, function(res) {
			if (!res || !res.success) {
				return;
			}
			// A toggle that changed a second setting as a side effect leaves
			// the other switch on this page showing a stale state, so the page
			// is reloaded rather than flipped in place.
			if (res.refresh) {
				window.location.reload();
				return;
			}
			var checkbox = $toggle.find('input[type="checkbox"]')[0];
			if (checkbox) {
				checkbox.checked = !checkbox.checked;
				$toggle.attr('aria-checked', checkbox.checked);
			}
		}, 'json').fail(function() {
			phpbb.alert('', $toggle.data('toggle-error'));
		}).always(function() {
			$toggle.removeClass('ajax-busy');
			if ($indicator) {
				$indicator.fadeOut(100);
			}
		});
	}

	$(document).on('click', '.toggle-switch[data-toggle-url]', function(e) {
		e.preventDefault();
		toggleSwitch($(this));
	});

	$(document).on('keydown', '.toggle-switch[data-toggle-url]', function(e) {
		if (e.key === ' ' || e.key === 'Enter') {
			e.preventDefault();
			toggleSwitch($(this));
		}
	});

	// A reset puts every control in the form back to the state it was rendered
	// in, including the AJAX toggles - which were never part of the submission
	// and have already been saved. Left alone they would show the old state
	// while the board runs on the new one, so they are put back afterwards.
	// aria-checked is the source of truth: it is kept in step with the checkbox
	// on every successful toggle and a reset does not touch attributes.
	$(document).on('reset', 'form', function() {
		var $toggles = $(this).find('.toggle-switch[data-toggle-url]');

		if (!$toggles.length) {
			return;
		}

		// The controls are only restored once the reset itself has run.
		window.setTimeout(function() {
			$toggles.each(function() {
				var $toggle = $(this);
				var checkbox = $toggle.find('input[type="checkbox"]')[0];
				if (checkbox) {
					checkbox.checked = $toggle.attr('aria-checked') === 'true';
				}
			});
		}, 0);
	});
})(jQuery);
