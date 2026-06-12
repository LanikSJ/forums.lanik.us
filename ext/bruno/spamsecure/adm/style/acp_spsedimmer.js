/* acp_spsedimmer.js
------------------------------------*/
var spamsecureACP = {};

spamsecureACP.setConfig = function () {
	'use strict';
	const enabled = "1.0";
	const disabled = "0.35";

	$('#dim_spse_coustom').css('opacity', (
			$('input[name="spamsecure_invalid_chars_lang_custom"]').prop('checked') == true
		) ? enabled : disabled
	);

	$('#dim_spse_counter').css('opacity', (
			$('input[name="spamsecure_invalid_chars_counter_switch"]').prop('checked') == true
		) ? enabled : disabled
	);

	$('#dim_spse_regex').css('opacity', (
			$('input[name="spamsecure_regex_individual_check"]').prop('checked') == true
		) ? enabled : disabled
	);

};

$(window).ready(function() {
	'use strict';

	spamsecureACP.setConfig();
	$('input[name="spamsecure_invalid_chars_lang_custom"]'			).on('change'	, spamsecureACP.setConfig);
	$('input[name="spamsecure_invalid_chars_counter_switch"]'		).on('change'	, spamsecureACP.setConfig);
	$('input[name="spamsecure_regex_individual_check"]'				).on('change'	, spamsecureACP.setConfig);

});
