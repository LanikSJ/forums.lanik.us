/**
 * WebAuthn passkey login — authentication flow
 *
 * @copyright (c) 2026 dmzx, https://www.dmzx-web.net
 * @license GNU General Public License, version 2 (GPL-2.0)
 */
(function () {
	'use strict';

	if (!window.PublicKeyCredential) {
		console.info('[WebAuthn] Passkeys not supported in this browser.');
		return;
	}

	var cfg = null;

	function b64urlToBuffer(b64url) {
		var base64 = b64url.replace(/-/g, '+').replace(/_/g, '/');
		var pad    = base64.length % 4;
		if (pad) { base64 += '===='.slice(pad); }
		var binary = atob(base64);
		var buf    = new Uint8Array(binary.length);
		for (var i = 0; i < binary.length; i++) { buf[i] = binary.charCodeAt(i); }
		return buf.buffer;
	}

	function bufferToB64url(buffer) {
		var bytes  = new Uint8Array(buffer);
		var binary = '';
		for (var i = 0; i < bytes.length; i++) { binary += String.fromCharCode(bytes[i]); }
		return btoa(binary).replace(/\+/g, '-').replace(/\//g, '_').replace(/=/g, '');
	}

	var errorEl = null;

	function showError(msg) {
		if (errorEl) { errorEl.textContent = msg; errorEl.hidden = false; }
	}

	function hideError() {
		if (errorEl) { errorEl.hidden = true; }
	}

	function rememberMeMode() {
		return cfg.rememberMeMode || 'off';
	}

	function getRedirectValue() {
		var el = document.querySelector('input[name="redirect"]');
		return el ? el.value : '';
	}

	function setBtnState(state) {
		var btn = document.getElementById('webauthn-login-btn');
		if (!btn) { return; }
		btn.classList.remove('webauthn-btn--loading');
		switch (state) {
			case 'init':
				btn.disabled  = true;
				btn.classList.add('webauthn-btn--loading');
				btn.innerHTML = '<span class="webauthn-spinner" aria-hidden="true"></span>';
				btn.appendChild(document.createTextNode(cfg.strButtonPreparing));
				break;
			case 'ready':
				btn.disabled  = false;
				btn.textContent = '\uD83D\uDD11 ' + cfg.strLoginButton;
				break;
			case 'working':
				btn.disabled  = true;
				btn.classList.add('webauthn-btn--loading');
				btn.innerHTML = '<span class="webauthn-spinner" aria-hidden="true"></span>';
				btn.appendChild(document.createTextNode(cfg.strButtonWorking));
				break;
		}
	}

	var CHALLENGE_TTL_MS  = 55000;
	var _cachedOptions    = null;
	var _challengePromise = null;
	var _fetchedAt        = 0;

	function fetchChallenge() {
		_fetchedAt        = Date.now();
		_cachedOptions    = null;
		_challengePromise = fetch(cfg.challengeUrl, {
			method  : 'POST',
			headers : { 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
			body    : '{}'
		})
		.then(function (r) { return r.json(); })
		.then(function (opts) {
			if (!opts.error) { _cachedOptions = opts; }
			return opts;
		})
		.catch(function () { _cachedOptions = null; _challengePromise = null; });
		return _challengePromise;
	}

	function getChallengeOptions() {
		var age = Date.now() - _fetchedAt;
		if (_cachedOptions && age < CHALLENGE_TTL_MS) {
			var opts = _cachedOptions; _cachedOptions = null; return Promise.resolve(opts);
		}
		if (_challengePromise && age < CHALLENGE_TTL_MS) {
			return _challengePromise.then(function (opts) { _cachedOptions = null; return opts; });
		}
		return fetchChallenge().then(function (opts) { _cachedOptions = null; return opts; });
	}

	var allowList    = [];
	var _silentRetry = true;

	async function loginWithPasskey() {
		hideError();
		setBtnState('working');

		try {
			var options = await getChallengeOptions();
			if (!options || options.error) {
				throw new Error(options ? options.error : 'challenge_request_failed');
			}

			allowList = (options.allowCredentials || []).map(function (c) {
				return { type: c.type, id: b64urlToBuffer(c.id) };
			});

			var publicKey = {
				challenge        : b64urlToBuffer(options.challenge),
				rpId             : options.rpId,
				timeout          : options.timeout || 60000,
				userVerification : options.userVerification || 'preferred',
				allowCredentials : allowList
			};

			fetchChallenge();

			var credential = await navigator.credentials.get({ publicKey: publicKey });

			var remember = false;
			var mode     = rememberMeMode();
			if (mode === 'always') {
				remember = true;
			} else if (mode === 'choice') {
				var rememberBox = document.getElementById('webauthn-remember-me');
				remember = !!(rememberBox && rememberBox.checked);
			}

			var verifyResp = await fetch(cfg.verifyUrl, {
				method  : 'POST',
				headers : { 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
				body    : JSON.stringify({
					id      : credential.id,
					rawId   : bufferToB64url(credential.rawId),
					type    : credential.type,
					remember: remember,
					redirect: getRedirectValue(),
					response: {
						authenticatorData : bufferToB64url(credential.response.authenticatorData),
						clientDataJSON    : bufferToB64url(credential.response.clientDataJSON),
						signature         : bufferToB64url(credential.response.signature),
						userHandle        : credential.response.userHandle
							? bufferToB64url(credential.response.userHandle) : null
					}
				})
			});

			var result = await verifyResp.json();

			if (result.success) {
				window.location.href = result.redirect;
				return;
			}

			console.error('[WebAuthn] Server verify failed:', result.error || '(no detail)');
			if (_silentRetry) {
				_silentRetry = false;
				fetchChallenge().then(function () { setBtnState('ready'); });
				return;
			}

			_silentRetry = true;
			showError(cfg.strErrorFailed);
			fetchChallenge();

		} catch (err) {
			if (err.name === 'NotAllowedError') {
				_silentRetry = true;
				showError(cfg.strErrorCancelledOrNone);
			} else {
				console.warn('[WebAuthn] Transient error (will retry silently):', err.name, err.message);
				if (_silentRetry) {
					_silentRetry = false;
					fetchChallenge().then(function () { setBtnState('ready'); });
					return;
				}
				_silentRetry = true;
				console.error('[WebAuthn] Persistent error:', err);
				showError(cfg.strErrorFailed);
			}
			fetchChallenge();

		} finally {
			setBtnState('ready');
		}
	}

	function injectButton() {
		var configEl = document.getElementById('webauthn-login-config');
		if (!configEl) { return; }
		cfg = configEl.dataset;

		var form = document.querySelector('form[action*="mode=login"]')
				|| document.querySelector('#login')
				|| document.querySelector('form.login-form')
				|| document.querySelector('form[name="login"]');
		if (!form) { return; }

		var wrapper = document.createElement('div');
		wrapper.id  = 'webauthn-login-wrapper';
		wrapper.setAttribute('role', 'region');
		wrapper.setAttribute('aria-label', cfg.strAriaRegion);

		var card = document.createElement('div');
		card.className = 'webauthn-login-card';

		var btn = document.createElement('button');
		btn.id        = 'webauthn-login-btn';
		btn.type      = 'button';
		btn.className = 'button1 webauthn-btn';
		btn.addEventListener('click', loginWithPasskey);

		card.appendChild(btn);

		if (rememberMeMode() === 'choice') {
			var rememberRow = document.createElement('label');
			rememberRow.className = 'webauthn-remember-row';

			var rememberBox = document.createElement('input');
			rememberBox.type = 'checkbox';
			rememberBox.id   = 'webauthn-remember-me';

			rememberRow.appendChild(rememberBox);
			rememberRow.appendChild(document.createTextNode(' ' + cfg.strRememberMe));

			card.appendChild(rememberRow);
		}

		var subtext = document.createElement('div');
		subtext.className   = 'webauthn-subtext';
		subtext.textContent = cfg.strLoginSubtext;
		card.appendChild(subtext);

		errorEl = document.createElement('p');
		errorEl.id        = 'webauthn-login-error';
		errorEl.className = 'webauthn-error';
		errorEl.setAttribute('role', 'alert');
		errorEl.setAttribute('aria-live', 'assertive');
		errorEl.hidden = true;

		var sep     = document.createElement('div');
		sep.className = 'webauthn-separator';
		var sepText = document.createElement('span');
		sepText.textContent = cfg.strOr;
		sep.appendChild(sepText);

		wrapper.appendChild(card);
		wrapper.appendChild(errorEl);
		wrapper.appendChild(sep);
		form.insertBefore(wrapper, form.firstChild);

		setBtnState('init');
		fetchChallenge()
			.then(function ()  { setBtnState('ready'); })
			.catch(function () { setBtnState('ready'); });
	}

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', injectButton);
	} else {
		injectButton();
	}

}());
