(function() {
	'use strict';

	var emojiRegex;
	try {
		// An emoji unit is a modifier base with a skin tone (text-default bases such
		// as U+261D take the tone without VS16), a codepoint that renders as emoji by
		// default (Emoji_Presentation, keeping a redundant VS16 with it), or a
		// text-default pictograph forced to emoji presentation with VS16 (U+FE0F).
		// Bare text symbols like © ® ™ are excluded.
		var mod = '[\\u{1F3FB}-\\u{1F3FF}]';
		var ep = '(?:\\p{Emoji_Modifier_Base}' + mod + '|\\p{Emoji_Presentation}\\uFE0F?|\\p{Extended_Pictographic}\\uFE0F)';
		var zwjSeq = ep + '(?:\\u200D' + ep + ')*';
		// A subdivision flag (England, Scotland, Wales) is U+1F3F4 followed by tag
		// characters and a cancel tag, matched whole so the tags stay with the flag.
		var tagSeq = '\\u{1F3F4}[\\u{E0020}-\\u{E007E}]+\\u{E007F}';
		// A flag is a pair of regional indicators. Matched as a single unit so the
		// two halves are not split into separate spans, which breaks flag rendering.
		var flag = '[\\u{1F1E6}-\\u{1F1FF}]{2}';
		// A keycap is a digit / # / * optionally followed by VS16, then U+20E3.
		var keycap = '[0-9#*]\\uFE0F?\\u20E3';
		emojiRegex = new RegExp('(?:' + tagSeq + '|' + flag + '|' + keycap + '|' + zwjSeq + ')', 'gu');
	} catch (e) {
		return;
	}

	// Elements whose contents must not be wrapped: already-wrapped emoji, code
	// blocks, so emoji inside [code]/[c] keep their plain text size, and raw-text
	// elements, where a span would drop the emoji from a textarea's value or
	// break a script or style.
	function isSkippable(el) {
		var cls = (typeof el.className === 'string') ? el.className : '';
		if (cls.indexOf('stoker-emoji') !== -1 || cls.indexOf('codebox') !== -1) {
			return true;
		}
		var tag = el.nodeName;
		return tag === 'CODE' || tag === 'PRE' || tag === 'TEXTAREA' || tag === 'SCRIPT' || tag === 'STYLE';
	}

	function walkAndWrap(node) {
		var child = node.firstChild;
		while (child) {
			var next = child.nextSibling;
			if (child.nodeType === 1) {
				if (!isSkippable(child)) {
					walkAndWrap(child);
				}
			} else if (child.nodeType === 3 && child.nodeValue) {
				replaceTextNode(child);
			}
			child = next;
		}
	}

	// The fragment is only created once a match is found, so a text node with no
	// emoji costs a single regex pass and no DOM work at all.
	function replaceTextNode(textNode) {
		var text = textNode.nodeValue;
		var frag = null;
		var lastIndex = 0;
		var m;

		emojiRegex.lastIndex = 0;
		while ((m = emojiRegex.exec(text)) !== null) {
			if (frag === null) {
				frag = document.createDocumentFragment();
			}
			if (m.index > lastIndex) {
				frag.appendChild(document.createTextNode(text.substring(lastIndex, m.index)));
			}
			var span = document.createElement('span');
			span.className = 'stoker-emoji';
			span.appendChild(document.createTextNode(m[0]));
			frag.appendChild(span);
			lastIndex = m.index + m[0].length;
		}

		if (frag === null) {
			return;
		}

		if (lastIndex < text.length) {
			frag.appendChild(document.createTextNode(text.substring(lastIndex)));
		}

		textNode.parentNode.replaceChild(frag, textNode);
	}

	var nodes = document.querySelectorAll('.content, .signature');
	for (var i = 0; i < nodes.length; i++) {
		walkAndWrap(nodes[i]);
	}
}());
