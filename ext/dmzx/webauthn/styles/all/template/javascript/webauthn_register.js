/**
 * WebAuthn passkey management — registration and rename
 *
 * @copyright (c) 2026 dmzx, https://www.dmzx-web.net
 * @license GNU General Public License, version 2 (GPL-2.0)
 */
(function () {
	'use strict';

	var cfg = null;

	function b64urlToBuffer(b64url) {
		var base64 = b64url.replace(/-/g, '+').replace(/_/g, '/');
		var pad    = base64.length % 4;
		if (pad) { base64 += '===='.slice(pad); }
		var binary = atob(base64);
		var buf    = new Uint8Array(binary.length);
		for (var i = 0; i < binary.length; i++) {
			buf[i] = binary.charCodeAt(i);
		}
		return buf.buffer;
	}

	function bufferToB64url(buffer) {
		var bytes  = new Uint8Array(buffer);
		var binary = '';
		for (var i = 0; i < bytes.length; i++) {
			binary += String.fromCharCode(bytes[i]);
		}
		return btoa(binary).replace(/\+/g, '-').replace(/\//g, '_').replace(/=/g, '');
	}

	function getCsrfFields() {
		var wrap          = document.getElementById('webauthn-csrf-token');
		var creationInput = wrap ? wrap.querySelector('input[name="creation_time"]') : null;
		var tokenInput    = wrap ? wrap.querySelector('input[name="form_token"]')    : null;
		return {
			creation_time : creationInput ? creationInput.value : '',
			form_token    : tokenInput    ? tokenInput.value    : ''
		};
	}

	function showMessage(type, msg) {
		var el = document.getElementById('webauthn-msg');
		if (!el) { return; }
		el.className   = 'webauthn-msg webauthn-msg-' + type;
		el.textContent = msg;
		el.hidden      = false;
		el.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
	}

	async function registerPasskey() {
		var btn       = document.getElementById('webauthn-register-btn');
		var nameInput = document.getElementById('webauthn-key-name');
		if (btn) { btn.disabled = true; }

		try {
			var csrf          = getCsrfFields();
			var challengeResp = await fetch(cfg.registerChallengeUrl, {
				method  : 'POST',
				headers : {
					'Content-Type'     : 'application/json',
					'X-Requested-With' : 'XMLHttpRequest'
				},
				body    : JSON.stringify({
					creation_time : csrf.creation_time,
					form_token    : csrf.form_token
				})
			});

			var options = await challengeResp.json();

			if (options.error) {
				showMessage('error', options.error);
				return;
			}

			if (!challengeResp.ok) {
				throw new Error('challenge_request_failed');
			}

			var publicKey = {
				challenge              : b64urlToBuffer(options.challenge),
				rp                     : options.rp,
				user                   : {
					id          : b64urlToBuffer(options.user.id),
					name        : options.user.name,
					displayName : options.user.displayName
				},
				pubKeyCredParams       : options.pubKeyCredParams,
				timeout                : options.timeout || 60000,
				excludeCredentials     : (options.excludeCredentials || []).map(function (c) {
					return { type: c.type, id: b64urlToBuffer(c.id) };
				}),
				authenticatorSelection : options.authenticatorSelection || {
					userVerification : 'preferred',
					residentKey      : 'preferred'
				},
				attestation            : options.attestation || 'none'
			};

			var credential = await navigator.credentials.create({ publicKey: publicKey });

			var keyName    = (nameInput && nameInput.value.trim()) || '';
			var verifyResp = await fetch(cfg.registerVerifyUrl, {
				method  : 'POST',
				headers : {
					'Content-Type'     : 'application/json',
					'X-Requested-With' : 'XMLHttpRequest'
				},
				body    : JSON.stringify({
					id            : credential.id,
					rawId         : bufferToB64url(credential.rawId),
					type          : credential.type,
					name          : keyName,
					creation_time : csrf.creation_time,
					form_token    : csrf.form_token,
					response      : {
						clientDataJSON    : bufferToB64url(credential.response.clientDataJSON),
						attestationObject : bufferToB64url(credential.response.attestationObject)
					}
				})
			});

			var result = await verifyResp.json();

			if (result.success) {
				showMessage('success', cfg.strSuccessRegistered);
				setTimeout(function () { window.location.reload(); }, 1500);
			} else {
				showMessage('error', result.error || cfg.strErrorFailed);
			}

		} catch (err) {
			if (err.name === 'NotAllowedError') {
				showMessage('error', cfg.strErrorCancelled);
			} else if (err.name === 'InvalidStateError') {
				showMessage('error', cfg.strErrorAlreadyRegistered);
			} else {
				showMessage('error', cfg.strErrorRegisterFailed);
				console.error('[WebAuthn]', err);
			}
		} finally {
			if (btn) { btn.disabled = false; }
		}
	}

	function bindEvents() {
		var configEl = document.getElementById('webauthn-register-config');
		if (!configEl) { return; }
		cfg = configEl.dataset;

		var regBtn = document.getElementById('webauthn-register-btn');

		if (!window.PublicKeyCredential) {
			var noSupport = document.getElementById('webauthn-no-support');
			if (noSupport) { noSupport.hidden = false; }
			if (regBtn)    { regBtn.hidden    = true;  }
			return;
		}

		if (regBtn) {
			regBtn.addEventListener('click', registerPasskey);
		}

		var renameBtns = document.querySelectorAll('.webauthn-rename-btn');
		for (var j = 0; j < renameBtns.length; j++) {
			(function (btn) {
				btn.addEventListener('click', function () {
					var row = btn.closest('tr');
					if (!row) { return; }
					var nameSpan = row.querySelector('.webauthn-key-name');
					var form     = row.querySelector('.webauthn-rename-form');
					if (nameSpan) { nameSpan.hidden = true;  }
					if (form)     { form.hidden     = false; }
				});
			}(renameBtns[j]));
		}

		var cancelBtns = document.querySelectorAll('.webauthn-rename-cancel');
		for (var k = 0; k < cancelBtns.length; k++) {
			(function (btn) {
				btn.addEventListener('click', function () {
					var row = btn.closest('tr');
					if (!row) { return; }
					var nameSpan = row.querySelector('.webauthn-key-name');
					var form     = row.querySelector('.webauthn-rename-form');
					if (form)     { form.hidden     = true;  }
					if (nameSpan) { nameSpan.hidden = false; }
				});
			}(cancelBtns[k]));
		}
	}

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', bindEvents);
	} else {
		bindEvents();
	}

}());
