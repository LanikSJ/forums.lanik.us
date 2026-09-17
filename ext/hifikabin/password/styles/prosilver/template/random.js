function randString(id) {
	var dataSet = $(id).attr('data-character-set').split(',');
	var possible = '';
	if ($.inArray('a', dataSet) >= 0) possible += 'abcdefghijklmnopqrstuvwxyz';
	if ($.inArray('A', dataSet) >= 0) possible += 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
	if ($.inArray('0', dataSet) >= 0) possible += '0123456789';
	if ($.inArray('#', dataSet) >= 0) possible += '![]{}()%&*$#^<>~@|';

	var size = parseInt($(id).attr('data-size'), 10) || 0;
	var text = '';
	var randomValues = new Uint32Array(size);
	crypto.getRandomValues(randomValues);
	for (var i = 0; i < size; i++) {
		text += possible.charAt(randomValues[i] % possible.length);
	}
	return text;
}


// Create a new password
$(".getNewPass").click(function () {
	var field = $(this).closest('div').find('input[rel="pw-generator"]');
	field.val(randString(field));
});

// Auto Select Pass On Focus
$('input[rel="pw-generator"]').on("click", function () {
	$(this).select();
});
