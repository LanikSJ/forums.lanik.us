(function() {
    'use strict';

    // Wait for jQuery to be available. This handles the case where Cloudflare
    // Rocket Loader (or any async script loader) reorders scripts and loads
    // jQuery AFTER this file. Without this guard, the IIFE would throw
    // "jQuery is not defined" pointing at this file.
    function pcgfAJAXRegistrationCheckWaitForJQuery() {
        if (typeof jQuery === 'undefined') {
            setTimeout(pcgfAJAXRegistrationCheckWaitForJQuery, 50);
            return;
        }

        (function($) {
            'use strict';

            var pcgfAJAXRegistrationCheckUsername = $('#pcgf-ajaxregistrationcheck-username');
            var pcgfAJAXRegistrationCheckEMail = $('#pcgf-ajaxregistrationcheck-email');
            var pcgfAJAXRegistrationCheckPassword = $('#pcgf-ajaxregistrationcheck-password');
            var pcgfAJAXRegistrationCheckConfirmPassword = $('#pcgf-ajaxregistrationcheck-confirm-password');
            var pcgfAJAXRegistrationCheckEvents = 'input keyup change blur';

            var pcgfAJAXRegistrationCheckEMailRuleRE = /^[^\s@]+@[^\s@]+\.[^\s@]+$/i;
            var pcgfAJAXRegistrationCheckUsernameRuleRE = /^.+$/i;

            // Compile the configured regexes only once the inline config globals exist.
            // This keeps this file independent of script execution order (e.g. when
            // Cloudflare Rocket Loader defers/reorders scripts).
            function pcgfAJAXRegistrationCheckCompileRules() {
                if (typeof pcgfAJAXRegistrationCheckEMailRule !== 'undefined') {
                    try {
                        pcgfAJAXRegistrationCheckEMailRuleRE = new RegExp(pcgfAJAXRegistrationCheckEMailRule, 'i');
                    } catch (e) { /* keep default */ }
                }
                if (typeof pcgfAJAXRegistrationCheckUsernameRule !== 'undefined') {
                    try {
                        pcgfAJAXRegistrationCheckUsernameRuleRE = new RegExp(pcgfAJAXRegistrationCheckUsernameRule, 'i');
                    } catch (e) { /* keep default */ }
                }
            }

            function getField(primarySelector, fallbackName) {
                var field = $(primarySelector);
                if (!field.length) {
                    field = $('[name="' + fallbackName + '"]');
                }
                return field.first();
            }

            function setValidity(field, message) {
                if (field.length && field.get(0).setCustomValidity) {
                    field.get(0).setCustomValidity(message);
                }
            }

            function setInvalid(message, messageField, field) {
                messageField.removeClass('valid password-strength').addClass('invalid');
                messageField.text(message);
                setValidity(field, message);
            }

            function setValid(message, messageField, field) {
                messageField.removeClass('invalid password-strength').addClass('valid');
                messageField.text(message);
                setValidity(field, '');
            }

            function setLoading(message, messageField, field) {
                messageField.removeClass('invalid valid password-strength');
                messageField.html('<div class="loading-circle"><div class="circle1 circle"></div><div class="circle2 circle"></div><div class="circle3 circle"></div><div class="circle4 circle"></div><div class="circle5 circle"></div><div class="circle6 circle"></div><div class="circle7 circle"></div><div class="circle8 circle"></div><div class="circle9 circle"></div><div class="circle10 circle"></div><div class="circle11 circle"></div><div class="circle12 circle"></div></div>&nbsp;&nbsp;&nbsp;' + message);
                setValidity(field, '');
            }

            function buildPasswordStrengthMarkup() {
                pcgfAJAXRegistrationCheckPassword.removeClass('invalid valid').addClass('password-strength');
                pcgfAJAXRegistrationCheckPassword.html(
                    '<span class="pcgf-ajaxregistrationcheck-strength-label"></span>' +
                    '<div class="progressbar"><div id="pcgf-ajaxregistrationcheck-security">&nbsp;</div></div>' +
                    '<span id="pcgf-ajaxregistrationcheck-strength" class="pcgf-ajaxregistrationcheck-strength-text"></span>'
                );
                pcgfAJAXRegistrationCheckPassword.find('.pcgf-ajaxregistrationcheck-strength-label').text(pcgfAJAXRegistrationCheckPasswordStrength + ' ');
            }

            function validateConfirmPassword(passwordField, passwordConfirmationField) {
                if (!passwordConfirmationField.length || !passwordField.length) {
                    return;
                }

                if (passwordConfirmationField.val() === passwordField.val()) {
                    setValid(pcgfAJAXRegistrationCheckConfirmPasswordValid, pcgfAJAXRegistrationCheckConfirmPassword, passwordConfirmationField);
                } else {
                    setInvalid(pcgfAJAXRegistrationCheckConfirmPasswordInvalid, pcgfAJAXRegistrationCheckConfirmPassword, passwordConfirmationField);
                }
            }

            function validatePassword(passwordField, passwordConfirmationField) {
                if (!passwordField.length) {
                    return;
                }

                validateConfirmPassword(passwordField, passwordConfirmationField);

                var value = passwordField.val();
                var containsLowerCase = value.match(/[a-z]/g);
                var containsUpperCase = value.match(/[A-Z]/g);
                var containsNumber = value.match(/[0-9]/g);
                var containsSymbol = value.match(/[^a-zA-Z0-9]/g);
                var valid = false;

                if (value.length < pcgfAJAXRegistrationCheckPasswordMin) {
                    setInvalid(pcgfAJAXRegistrationCheckPasswordInvalid, pcgfAJAXRegistrationCheckPassword, passwordField);
                } else if (pcgfAJAXRegistrationCheckPasswordRule <= 0) {
                    valid = true;
                } else if (containsLowerCase && containsUpperCase) {
                    if (pcgfAJAXRegistrationCheckPasswordRule <= 10) {
                        valid = true;
                    } else if (containsNumber) {
                        if (pcgfAJAXRegistrationCheckPasswordRule <= 100) {
                            valid = true;
                        } else if (containsSymbol) {
                            valid = true;
                        } else {
                            setInvalid(pcgfAJAXRegistrationCheckPasswordInvalid, pcgfAJAXRegistrationCheckPassword, passwordField);
                        }
                    } else {
                        setInvalid(pcgfAJAXRegistrationCheckPasswordInvalid, pcgfAJAXRegistrationCheckPassword, passwordField);
                    }
                } else {
                    setInvalid(pcgfAJAXRegistrationCheckPasswordInvalid, pcgfAJAXRegistrationCheckPassword, passwordField);
                }

                if (!valid) {
                    return;
                }

                // Mark the password as valid once it passes all rules
                setValid(pcgfAJAXRegistrationCheckPasswordValid, pcgfAJAXRegistrationCheckPassword, passwordField);

                var percentage = 0;
                if (containsLowerCase) {
                    percentage += (containsLowerCase.length > 5 ? 5 : containsLowerCase.length) * 5;
                }
                if (containsUpperCase) {
                    percentage += (containsUpperCase.length > 3 ? 3 : containsUpperCase.length) * 7;
                }
                if (containsNumber) {
                    percentage += (containsNumber.length > 2 ? 2 : containsNumber.length) * 10;
                }
                if (containsSymbol) {
                    percentage += (containsSymbol.length > 2 ? 2 : containsSymbol.length) * 14;
                }

                var usernameField = getField('#username', 'username');
                var eMailField = getField('#email', 'email');
                if ((usernameField.val() === '' || value.indexOf(usernameField.val()) < 0) && (eMailField.val() === '' || value.indexOf(eMailField.val()) < 0)) {
                    percentage += 6;
                }

                if (!$('#pcgf-ajaxregistrationcheck-security').length || !$('#pcgf-ajaxregistrationcheck-strength').length) {
                    buildPasswordStrengthMarkup();
                }

                setValidity(passwordField, '');

                var securityPB = $('#pcgf-ajaxregistrationcheck-security');
                var strengthText = $('#pcgf-ajaxregistrationcheck-strength');
                securityPB.stop().animate({width: percentage + '%'}, 800);

                if (percentage >= 95) {
                    strengthText.text(pcgfAJAXRegistrationCheckPasswordVeryStrong);
                    securityPB.removeClass().addClass('very-strong');
                } else if (percentage >= 85) {
                    strengthText.text(pcgfAJAXRegistrationCheckPasswordStrong);
                    securityPB.removeClass().addClass('strong');
                } else if (percentage >= 60) {
                    strengthText.text(pcgfAJAXRegistrationCheckPasswordNormal);
                    securityPB.removeClass().addClass('normal');
                } else if (percentage >= 45) {
                    strengthText.text(pcgfAJAXRegistrationCheckPasswordWeak);
                    securityPB.removeClass().addClass('weak');
                } else {
                    strengthText.text(pcgfAJAXRegistrationCheckPasswordVeryWeak);
                    securityPB.removeClass().addClass('very-weak');
                }
            }

            var pcgfAJAXRegistrationCheckInitialized = false;
            // Guard against Cloudflare Rocket Loader (or any async script loader)
            // reordering the inline configuration script vs. this file. We wait until
            // all required globals are defined before binding the event handlers.
            function initAJAXRegistrationCheck() {
                if (pcgfAJAXRegistrationCheckInitialized) {
                    return;
                }

                pcgfAJAXRegistrationCheckCompileRules();

                if (typeof pcgfAJAXRegistrationCheckUsernameMin === 'undefined' ||
                    typeof pcgfAJAXRegistrationCheckUsernameMax === 'undefined' ||
                    typeof pcgfAJAXRegistrationCheckUsernameRule === 'undefined' ||
                    typeof pcgfAJAXRegistrationCheckUsernameInvalidBoundaries === 'undefined' ||
                    typeof pcgfAJAXRegistrationCheckUsernameCheckLink === 'undefined' ||
                    typeof pcgfAJAXRegistrationCheckEMailRule === 'undefined' ||
                    typeof pcgfAJAXRegistrationCheckEMailInvalid === 'undefined' ||
                    typeof pcgfAJAXRegistrationCheckEMailCheckLink === 'undefined' ||
                    typeof pcgfAJAXRegistrationCheckPasswordMin === 'undefined' ||
                    typeof pcgfAJAXRegistrationCheckPasswordRule === 'undefined' ||
                    typeof pcgfAJAXRegistrationCheckPasswordInvalid === 'undefined' ||
                    typeof pcgfAJAXRegistrationCheckPasswordValid === 'undefined' ||
                    typeof pcgfAJAXRegistrationCheckConfirmPasswordValid === 'undefined' ||
                    typeof pcgfAJAXRegistrationCheckConfirmPasswordInvalid === 'undefined' ||
                    typeof pcgfAJAXRegistrationCheckPasswordStrength === 'undefined' ||
                    typeof pcgfAJAXRegistrationCheckPasswordVeryWeak === 'undefined' ||
                    typeof pcgfAJAXRegistrationCheckPasswordWeak === 'undefined' ||
                    typeof pcgfAJAXRegistrationCheckPasswordNormal === 'undefined' ||
                    typeof pcgfAJAXRegistrationCheckPasswordStrong === 'undefined' ||
                    typeof pcgfAJAXRegistrationCheckPasswordVeryStrong === 'undefined' ||
                    typeof pcgfAJAXRegistrationCheckLoading === 'undefined') {
                    // Configuration globals not ready yet; retry shortly.
                    setTimeout(initAJAXRegistrationCheck, 50);
                    return;
                }

                pcgfAJAXRegistrationCheckInitialized = true;

                var passwordField = getField('#new_password', 'new_password');
                var passwordConfirmationField = getField('#password_confirm', 'password_confirm');
                var usernameField = getField('#username', 'username');
                var eMailField = getField('#email', 'email');

                if (passwordConfirmationField.length) {
                    pcgfAJAXRegistrationCheckConfirmPassword.insertAfter(passwordConfirmationField);
                    passwordConfirmationField.on(pcgfAJAXRegistrationCheckEvents, function() {
                        validateConfirmPassword(passwordField, passwordConfirmationField);
                    });
                }

                if (passwordField.length) {
                    pcgfAJAXRegistrationCheckPassword.insertAfter(passwordField);
                    passwordField.on(pcgfAJAXRegistrationCheckEvents, function() {
                        validatePassword(passwordField, passwordConfirmationField);
                    });
                    validatePassword(passwordField, passwordConfirmationField);
                }

                if (usernameField.length) {
                    pcgfAJAXRegistrationCheckUsername.insertAfter(usernameField);
                    usernameField.on(pcgfAJAXRegistrationCheckEvents, function() {
                        if (passwordField.length) {
                            validatePassword(passwordField, passwordConfirmationField);
                        }

                        var value = usernameField.val();
                        if (value.length < pcgfAJAXRegistrationCheckUsernameMin || value.length > pcgfAJAXRegistrationCheckUsernameMax || value.match(pcgfAJAXRegistrationCheckUsernameRuleRE) === null) {
                            setInvalid(pcgfAJAXRegistrationCheckUsernameInvalidBoundaries, pcgfAJAXRegistrationCheckUsername, usernameField);
                            return;
                        }

                        setLoading(pcgfAJAXRegistrationCheckLoading, pcgfAJAXRegistrationCheckUsername, usernameField);
                        $.ajax({
                            url: pcgfAJAXRegistrationCheckUsernameCheckLink,
                            type: 'POST',
                            dataType: 'json',
                            headers: {'X-Requested-With': 'XMLHttpRequest'},
                            data: {'search': value, 'ajax': 1},
                            success: function(result) {
                                if (result[0] === 'OK') {
                                    setValid(result[1], pcgfAJAXRegistrationCheckUsername, usernameField);
                                } else if (result[0] === 'INVALID QUERY') {
                                    setInvalid(result[1], pcgfAJAXRegistrationCheckUsername, usernameField);
                                } else {
                                    setInvalid(result[1], pcgfAJAXRegistrationCheckUsername, usernameField);
                                }
                            },
                            error: function() {
                                setInvalid(pcgfAJAXRegistrationCheckLoading, pcgfAJAXRegistrationCheckUsername, usernameField);
                            }
                        });
                    });
                    usernameField.triggerHandler('input');
                }

                if (eMailField.length) {
                    pcgfAJAXRegistrationCheckEMail.insertAfter(eMailField);
                    eMailField.on(pcgfAJAXRegistrationCheckEvents, function() {
                        if (passwordField.length) {
                            validatePassword(passwordField, passwordConfirmationField);
                        }

                        var value = eMailField.val();
                        if (value.match(pcgfAJAXRegistrationCheckEMailRuleRE) === null) {
                            setInvalid(pcgfAJAXRegistrationCheckEMailInvalid, pcgfAJAXRegistrationCheckEMail, eMailField);
                            return;
                        }

                        setLoading(pcgfAJAXRegistrationCheckLoading, pcgfAJAXRegistrationCheckEMail, eMailField);
                        $.ajax({
                            url: pcgfAJAXRegistrationCheckEMailCheckLink,
                            type: 'POST',
                            dataType: 'json',
                            headers: {'X-Requested-With': 'XMLHttpRequest'},
                            data: {'search': value, 'ajax': 1},
                            success: function(result) {
                                if (result[0] === 'OK') {
                                    setValid(result[1], pcgfAJAXRegistrationCheckEMail, eMailField);
                                } else if (result[0] === 'INVALID QUERY') {
                                    setInvalid(result[1], pcgfAJAXRegistrationCheckEMail, eMailField);
                                } else {
                                    setInvalid(result[1], pcgfAJAXRegistrationCheckEMail, eMailField);
                                }
                            },
                            error: function() {
                                setInvalid(pcgfAJAXRegistrationCheckLoading, pcgfAJAXRegistrationCheckEMail, eMailField);
                            }
                        });
                    });
                    eMailField.triggerHandler('input');
                }

                $('#ucp').on('submit', function() {
                    if (pcgfAJAXRegistrationCheckUsername.hasClass('invalid') || pcgfAJAXRegistrationCheckEMail.hasClass('invalid') || pcgfAJAXRegistrationCheckPassword.hasClass('invalid') || pcgfAJAXRegistrationCheckConfirmPassword.hasClass('invalid')) {
                        return false;
                    }
                    return true;
                });
            }

            $(function() {
                initAJAXRegistrationCheck();
                if (!pcgfAJAXRegistrationCheckInitialized && typeof pcgfAJAXRegistrationCheckUsernameMin !== 'undefined') {
                    initAJAXRegistrationCheck();
                }
            });
        })(jQuery);
    }

    pcgfAJAXRegistrationCheckWaitForJQuery();
})();