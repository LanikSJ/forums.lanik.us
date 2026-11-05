(function($) {
	'use strict';

	function toggleSwitch($toggle) {
		if ($toggle.hasClass('ajax-busy')) {
			return;
		}
		$toggle.addClass('ajax-busy');
		var url = $toggle.data('toggle-url');
		var $indicator = phpbb.loadingIndicator();
		$.get(url, function(res) {
			if (res && res.success) {
				var checkbox = $toggle.find('input[type="checkbox"]')[0];
				if (checkbox) {
					checkbox.checked = !checkbox.checked;
					$toggle.attr('aria-checked', checkbox.checked);
				}
			} else {
				phpbb.alert('', $toggle.data('toggle-error'));
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

	// Emoji table: client-side add/remove/reorder; the whole table is saved on form submit,
	// with the stored order taken from the visual row order.
	var $emojiTable = $('#stoker-emoji-table');
	if ($emojiTable.length) {
		var $emojiRows = $('#stoker-emoji-rows');
		var emojiTpl = document.getElementById('stoker-emoji-row-tpl');
		// The server-rendered rows, kept so the form's reset button can undo the
		// row additions, deletions and moves that a native reset leaves alone.
		var originalRowsHtml = $emojiRows.html();
		var originalRowCount = $emojiRows.children('tr').length;
		var nextEmojiIndex = originalRowCount;

		$('#stoker-emoji-add').on('click', function() {
			if (!emojiTpl) {
				return;
			}
			var html = emojiTpl.innerHTML.replace(/__INDEX__/g, String(nextEmojiIndex));
			nextEmojiIndex++;
			var $row = $(html.trim());
			$emojiRows.append($row);
			$row.find('.stoker-emoji-char').trigger('focus');
		});

		$emojiRows.on('click', '.stoker-emoji-del', function() {
			$(this).closest('tr').remove();
		});

		$emojiRows.on('change', '.stoker-emoji-posting-box', function() {
			$(this).siblings('.stoker-emoji-posting-val').val(this.checked ? '1' : '0');
		});

		$emojiRows.on('click', '.stoker-emoji-up', function() {
			var $row = $(this).closest('tr');
			var $prev = $row.prev('tr');
			if ($prev.length) {
				$row.insertBefore($prev);
			}
		});

		$emojiRows.on('click', '.stoker-emoji-down', function() {
			var $row = $(this).closest('tr');
			var $next = $row.next('tr');
			if ($next.length) {
				$row.insertAfter($next);
			}
		});

		$emojiTable.closest('form').on('reset', function() {
			$emojiRows.html(originalRowsHtml);
			nextEmojiIndex = originalRowCount;
		});
	}
})(jQuery);
