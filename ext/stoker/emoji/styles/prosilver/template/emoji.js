(function($) {
	'use strict';

	// Guard against the script being included more than once on a page,
	// which would double-bind every delegated handler.
	if (window.stokerEmojiPickerInit) {
		return;
	}
	window.stokerEmojiPickerInit = true;

	// The currently open panel and the button that opened it, so the panel can
	// be re-anchored to its editor while the page scrolls.
	var openPanel = null;
	var openBtn = null;
	var repositionQueued = false;

	// The editor a picker belongs to, used as the panel's anchor and as the
	// insertion target.
	function findTextarea(el) {
		var $form = $(el).closest('form');
		return $form.find('textarea[name="message"]')[0] || $form.find('textarea')[0] || null;
	}

	// Notify listeners (draft autosave, character counters, etc.) that the
	// textarea changed, since assigning .value fires nothing by itself.
	function fireInput(textarea) {
		textarea.dispatchEvent(new Event('input', { bubbles: true }));
	}

	function insertEmoji(textarea, text) {
		var start = textarea.selectionStart || 0;
		var end = textarea.selectionEnd || 0;
		var val = textarea.value;

		// The emoji character is inserted as-is. Unlike phpBB's smilies, which
		// insert a shortcode that has to be recognised again on submit, nothing
		// downstream needs the character delimited, so no spaces are added.
		textarea.value = val.slice(0, start) + text + val.slice(end);
		var pos = start + text.length;
		textarea.selectionStart = textarea.selectionEnd = pos;
		textarea.focus();

		fireInput(textarea);
	}

	function closePanels(except) {
		$('.stoker-emoji-panel').each(function() {
			if (this !== except && !this.hidden) {
				this.hidden = true;
				$(this).css({ position: '', top: '', left: '' });
				$(this).closest('.stoker-emoji-more').find('.stoker-emoji-more-btn').attr('aria-expanded', 'false');
			}
		});

		if (openPanel && openPanel !== except) {
			openPanel = null;
			openBtn = null;
		}
	}

	// Emoji items ('menuitem's) inside a single panel.
	function panelItems(panel) {
		return Array.prototype.slice.call(panel.querySelectorAll('.stoker-emoji-ins'));
	}

	// Move focus to the item at index (wrapping), keeping one roving tabstop.
	function focusPanelItem(items, index) {
		if (!items.length) {
			return;
		}
		index = (index % items.length + items.length) % items.length;
		for (var i = 0; i < items.length; i++) {
			items[i].tabIndex = (i === index) ? 0 : -1;
		}
		items[index].focus({ preventScroll: true });
	}

	function buildEmojiMap($picker) {
		try {
			var raw = $picker.attr('data-emoji-map') || '{}';
			return JSON.parse(raw);
		} catch (e) {
			return {};
		}
	}

	function substituteShortcodes(text, map) {
		if (!Object.keys(map).length) {
			return text;
		}

		var codes = Object.keys(map).sort(function(a, b) { return b.length - a.length; });
		var escaped = codes.map(function(c) {
			return c.replace(/[-[\]{}()*+?.,\\^$|#\s]/g, '\\$&');
		});
		var re = new RegExp('(^|\\s)(' + escaped.join('|') + ')(?=\\s|$)', 'g');

		// Spans of [code] / [code=...] / [c] blocks; an unclosed block runs to the
		// end. Shortcodes are matched against the whole text, so the whitespace
		// boundary also applies next to a code tag, and matches inside a block are
		// left alone.
		var blocks = [];
		var blockRe = /\[(?:code(?:=[^\]]+)?|c)\][\s\S]*?(?:\[\/(?:code|c)\]|$)/gi;
		var m;

		while ((m = blockRe.exec(text)) !== null) {
			blocks.push([m.index, m.index + m[0].length]);
		}

		return text.replace(re, function(full, pre, code, offset) {
			var start = offset + pre.length;

			for (var i = 0; i < blocks.length; i++) {
				if (start >= blocks[i][0] && start < blocks[i][1]) {
					return full;
				}
			}

			return pre + (map[code] || code);
		});
	}

	// Insert emoji on click.
	$(document).on('click', '.stoker-emoji-ins', function(e) {
		e.preventDefault();
		var textarea = findTextarea(this);
		if (textarea) {
			insertEmoji(textarea, $(this).attr('data-ins'));
		}
	});

	function positionPanel(panel, btn) {
		var $panel = $(panel);
		var textarea = findTextarea(btn);

		// Un-hide first so we can measure the panel's rendered size.
		$panel.css({ position: 'fixed', visibility: 'hidden', top: 0, left: 0 });
		panel.hidden = false;

		var pw = panel.offsetWidth;
		var ph = panel.offsetHeight;
		var vw = window.innerWidth;
		var vh = window.innerHeight;
		var margin = 6;

		var cx, top;

		if (textarea) {
			var tr = textarea.getBoundingClientRect();
			// Center horizontally over the textarea.
			cx = tr.left + tr.width / 2;
			// Prefer above the textarea; fall back to below if not enough room.
			if (tr.top - ph - margin >= margin) {
				top = tr.top - ph - margin;
			} else {
				top = tr.bottom + margin;
			}
		} else {
			// No textarea found: fall back to centering near the button.
			var br = btn.getBoundingClientRect();
			cx = br.left + br.width / 2;
			top = br.top - ph - margin;
			if (top < margin) {
				top = br.bottom + margin;
			}
		}

		// Clamp horizontally so the panel never leaves the viewport.
		var left = cx - pw / 2;
		left = Math.max(margin, Math.min(left, vw - pw - margin));
		// Clamp vertically.
		top = Math.max(margin, Math.min(top, vh - ph - margin));

		$panel.css({ position: 'fixed', visibility: '', top: top, left: left });
	}

	// Re-anchor the open panel to its editor. Called from scroll and resize,
	// throttled to one run per animation frame. If the anchor has scrolled
	// entirely out of view there is nothing sensible to point at, so close.
	function repositionOpenPanel() {
		if (!openPanel || openPanel.hidden || !openBtn) {
			return;
		}

		var anchor = findTextarea(openBtn) || openBtn;
		var rect = anchor.getBoundingClientRect();

		if (rect.bottom <= 0 || rect.top >= window.innerHeight) {
			closePanels(null);
			return;
		}

		positionPanel(openPanel, openBtn);
	}

	function queueReposition() {
		if (repositionQueued || !openPanel) {
			return;
		}
		repositionQueued = true;
		window.requestAnimationFrame(function() {
			repositionQueued = false;
			repositionOpenPanel();
		});
	}

	// Popup open/close.
	$(document).on('click', '.stoker-emoji-more-btn', function(e) {
		e.preventDefault();
		e.stopPropagation();
		var panel = $(this).closest('.stoker-emoji-more').find('.stoker-emoji-panel')[0];
		if (!panel) {
			return;
		}
		var show = panel.hidden;
		closePanels(show ? panel : null);
		if (show) {
			openPanel = panel;
			openBtn = this;
			positionPanel(panel, this);
			// Move focus into the panel and establish a single roving tabstop.
			focusPanelItem(panelItems(panel), 0);
		} else {
			panel.hidden = true;
			openPanel = null;
			openBtn = null;
		}
		$(this).attr('aria-expanded', show ? 'true' : 'false');
	});

	$(document).on('click', '.stoker-emoji-panel', function(e) {
		e.stopPropagation();
	});

	// Keyboard navigation within an open panel.
	$(document).on('keydown', '.stoker-emoji-panel', function(e) {
		var items = panelItems(this);
		if (!items.length) {
			return;
		}
		var current = items.indexOf(document.activeElement);

		switch (e.key) {
			case 'ArrowRight':
			case 'ArrowDown':
				e.preventDefault();
				focusPanelItem(items, current < 0 ? 0 : current + 1);
				break;
			case 'ArrowLeft':
			case 'ArrowUp':
				e.preventDefault();
				focusPanelItem(items, current < 0 ? 0 : current - 1);
				break;
			case 'Home':
				e.preventDefault();
				focusPanelItem(items, 0);
				break;
			case 'End':
				e.preventDefault();
				focusPanelItem(items, items.length - 1);
				break;
			case ' ':
			case 'Spacebar':
				// Enter already activates the link natively; handle Space too.
				e.preventDefault();
				if (current >= 0) {
					items[current].click();
				}
				break;
		}
	});

	$(document).on('click', function() {
		closePanels(null);
	});

	// Keep the fixed-position panel glued to its editor while the page scrolls
	// or the viewport changes, instead of dismissing it on the first scroll.
	$(window).on('scroll resize', queueReposition);

	$(document).on('keydown', function(e) {
		if (e.key === 'Escape' || e.keyCode === 27) {
			var active = document.activeElement;
			var $btn = active ? $(active).closest('.stoker-emoji-more').find('.stoker-emoji-more-btn') : $();
			closePanels(null);
			// Return focus to the trigger if Escape came from within a panel.
			if ($btn.length) {
				$btn.trigger('focus');
			}
		}
	});

	// Substitute typed shortcodes to Unicode on submit.
	$(document).on('submit', 'form', function() {
		var $picker = $(this).find('.stoker-emoji[data-emoji-map]');
		if (!$picker.length) {
			return;
		}
		var map = buildEmojiMap($picker);
		if (!Object.keys(map).length) {
			return;
		}
		var $ta = $(this).find('textarea[name="message"], textarea[name="signature"]');
		$ta.each(function() {
			this.value = substituteShortcodes(this.value, map);
		});
	});
})(jQuery);
