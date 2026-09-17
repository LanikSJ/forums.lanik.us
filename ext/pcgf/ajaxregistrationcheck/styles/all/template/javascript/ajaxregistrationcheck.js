(function() {
    'use strict';

    // Literal regexes only - the board's rule key is mapped with explicit comparisons,
    // so there is never a dynamic RegExp compilation or a variable-key lookup.
    function getRegexFor(rule) {
        if (rule === 'USERNAME_ALPHA_ONLY') return /^[a-z0-9]+$/i;
        if (rule === 'USERNAME_ALPHA_SPACERS') return /^[a-z0-9\-+_[\] ]+$/i;
        if (rule === 'USERNAME_LETTER_NUM') return /^[\p{L}\p{N}]+$/u;
        if (rule === 'USERNAME_LETTER_NUM_SPACERS') return /^[-+_ [\]\p{L}\p{N}]+$/u;
        if (rule === 'USERNAME_ASCII') return /^[\x20-\x7E]+$/; // printable ASCII (server side is authoritative)
        return /.+/; // USERNAME_CHARS_ANY and unknown keys: permissive (server side is authoritative)
    }

    var RE_EMAIL = /^[^\s@]+@[^\s@]+\.[^\s@]{2,}$/i;

    function setInvalid(message, messageField, field) {
        messageField.removeClass('valid password-strength').addClass('invalid').text(message);
        field.get(0).setCustomValidity(message);
    }

    function setValid(message, messageField, field) {
        messageField.removeClass('invalid password-strength').addClass('valid').text(message);
        field.get(0).setCustomValidity('');
    }

    function setLoading(message, messageField, field) {
        var wrapper = document.createElement('div');
        wrapper.className = 'loading-circle';
        for (var i = 1; i <= 12; i++) {
            var circle = document.createElement('div');
            circle.className = 'circle' + i + ' circle';
            wrapper.appendChild(circle);
        }
        messageField.removeClass('invalid valid password-strength').empty()
            .append(wrapper, document.createTextNode('\u00A0\u00A0\u00A0' + message));
        field.get(0).setCustomValidity('');
    }

    function serverCheck(value, cfg, url, messageField, field) {
        setLoading(cfg.loading, messageField, field);
        $.ajax({
            url: url,
            type: 'POST',
            data: {'search': value},
            success: function(result) {
                if (result[0] === 'OK') {
                    setValid(result[1], messageField, field);
                } else if (result[0] === 'INVALID QUERY') {
                    setLoading(result[1], messageField, field);
                } else {
                    setInvalid(result[1], messageField, field);
                }
            }
        });
    }

    function isPasswordValid(value, cfg, matches) {
        if (value.length < cfg.pwdMin) return false;
        if (cfg.pwdRule <= 0) return true;
        if (!matches.lower || !matches.upper) return false;
        if (cfg.pwdRule <= 10) return true;
        if (!matches.number) return false;
        if (cfg.pwdRule <= 100) return true;
        return !!matches.symbol;
    }

    function computeStrength(value, matches, usernameField, emailField) {
        var percentage = 0;
        if (matches.lower) percentage += Math.min(matches.lower.length, 5) * 5;
        if (matches.upper) percentage += Math.min(matches.upper.length, 3) * 7;
        if (matches.number) percentage += Math.min(matches.number.length, 2) * 10;
        if (matches.symbol) percentage += Math.min(matches.symbol.length, 2) * 14;
        if ((usernameField.val() === '' || value.indexOf(usernameField.val()) < 0) &&
            (emailField.val() === '' || value.indexOf(emailField.val()) < 0)) {
            percentage += 6; // bonus: password doesn't contain username or email
        }
        return percentage;
    }

    function ensureStrengthMeter(cfg, messageField) {
        if (document.getElementById('pcgf-ajaxregistrationcheck-security')) return;
        var label = document.createElement('span');
        label.textContent = cfg.strengthLabel + ' ';
        var bar = document.createElement('div');
        bar.className = 'progressbar';
        var fill = document.createElement('div');
        fill.id = 'pcgf-ajaxregistrationcheck-security';
        fill.appendChild(document.createTextNode('\u00A0'));
        bar.appendChild(fill);
        var text = document.createElement('span');
        text.id = 'pcgf-ajaxregistrationcheck-strength';
        // Built with DOM APIs (textContent) so config values are never parsed as HTML
        messageField.removeClass('invalid valid').addClass('password-strength').empty().append(label, bar, text);
    }

    function setStrengthIndicator(percentage, cfg) {
        var fill = $('#pcgf-ajaxregistrationcheck-security');
        var text = $('#pcgf-ajaxregistrationcheck-strength');
        fill.stop().animate({width: percentage + '%', overflow: 'overflow'}, 800);
        if (percentage >= 95) {
            text.text(cfg.veryStrong);
            fill.removeClass().addClass('very-strong');
        } else if (percentage >= 85) {
            text.text(cfg.strong);
            fill.removeClass().addClass('strong');
        } else if (percentage >= 60) {
            text.text(cfg.normal);
            fill.removeClass().addClass('normal');
        } else if (percentage >= 45) {
            text.text(cfg.weak);
            fill.removeClass().addClass('weak');
        } else {
            text.text(cfg.veryWeak);
            fill.removeClass().addClass('very-weak');
        }
    }

    function validatePassword(cfg, passwordField, passwordMessage, usernameField, emailField) {
        var value = passwordField.val();
        var matches = {
            lower: value.match(/[a-z]/g),
            upper: value.match(/[A-Z]/g),
            number: value.match(/[0-9]/g),
            symbol: value.match(/[^a-zA-Z0-9]/g)
        };
        if (!isPasswordValid(value, cfg, matches)) {
            setInvalid(cfg.pwdInvalid, passwordMessage, passwordField);
            return;
        }
        // No success message exists for this field in the extension language files;
        // mirror the original behavior: clear validity, mark valid, show the meter
        passwordMessage.removeClass('invalid password-strength').addClass('valid').text('');
        passwordField.get(0).setCustomValidity('');
        ensureStrengthMeter(cfg, passwordMessage);
        setStrengthIndicator(computeStrength(value, matches, usernameField, emailField), cfg);
    }

    function bindConfirm(cfg, passwordField, confirmField, confirmMessage) {
        if (!confirmField.length || !confirmMessage.length) return;
        confirmMessage.insertAfter(confirmField);
        confirmField.on('keyup', function() {
            if ($(this).val() === passwordField.val()) {
                setValid(cfg.pwdConfirmValid, confirmMessage, $(this));
            } else {
                setInvalid(cfg.pwdConfirmInvalid, confirmMessage, $(this));
            }
        });
        confirmField.trigger('keyup');
    }

    function bindPassword(cfg, passwordField, confirmField, passwordMessage, usernameField, emailField) {
        if (!passwordField.length || !passwordMessage.length) return;
        passwordMessage.insertAfter(passwordField);
        passwordField.on('keyup', function() {
            confirmField.trigger('keyup');
            validatePassword(cfg, passwordField, passwordMessage, usernameField, emailField);
        });
        passwordField.trigger('keyup');
    }

    function bindUsername(cfg, usernameField, passwordField, usernameMessage) {
        if (!usernameField.length || !usernameMessage.length) return;
        usernameMessage.insertAfter(usernameField);
        usernameField.on('keyup', function() {
            passwordField.trigger('keyup');
            var value = $(this).val();
            if (value.length < cfg.usernameMin || value.length > cfg.usernameMax || value.match(getRegexFor(cfg.usernameRule)) === null) {
                setInvalid(cfg.usernameInvalid, usernameMessage, $(this));
            } else {
                serverCheck(value, cfg, cfg.usernameCheckLink, usernameMessage, $(this));
            }
        });
        usernameField.trigger('keyup');
    }

    function bindEmail(cfg, emailField, passwordField, emailMessage) {
        if (!emailField.length || !emailMessage.length) return;
        emailMessage.insertAfter(emailField);
        emailField.on('keyup', function() {
            passwordField.trigger('keyup');
            var value = $(this).val();
            if (value.match(RE_EMAIL) === null) {
                setInvalid(cfg.emailInvalid, emailMessage, $(this));
            } else {
                serverCheck(value, cfg, cfg.emailCheckLink, emailMessage, $(this));
            }
        });
        emailField.trigger('keyup');
    }

    $(document).ready(function() {
        var cfg = window.pcgfAJAXRegistrationCheck || {};
        var passwordField = $('#new_password');
        var confirmField = $('#password_confirm');
        var usernameField = $('#username');
        var emailField = $('#email');
        bindConfirm(cfg, passwordField, confirmField, $('#pcgf-ajaxregistrationcheck-confirm-password'));
        bindPassword(cfg, passwordField, confirmField, $('#pcgf-ajaxregistrationcheck-password'), usernameField, emailField);
        bindUsername(cfg, usernameField, passwordField, $('#pcgf-ajaxregistrationcheck-username'));
        bindEmail(cfg, emailField, passwordField, $('#pcgf-ajaxregistrationcheck-email'));
    });
})();