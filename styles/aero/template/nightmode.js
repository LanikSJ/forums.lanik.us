var nightmodeswitch = $.cookie('aeronightmode');
	
	// Set the user's selection
	if (nightmodeswitch == 'nightmodeon') {
		$('.nightmodeoff').css("display","none");
		$('.nightmodeon').css("display","block");
		$('#overlay').css("display","block");
    };
	
$(document).ready(function() {
	// When nightmodeon is clicked:
    $('.nightmodeon').click(function() {
		$('.nightmodeoff').css("display","block");
		$('.nightmodeon').css("display","none");
		$('#overlay').css("display","none");
		$.cookie('aeronightmode', null, { path: '/' });
		return false;
    });
	// When nightmodeoff is clicked:
    $('.nightmodeoff').click(function() {
		$('.nightmodeoff').css("display","none");
		$('.nightmodeon').css("display","block");
		$('#overlay').css("display","block");
		$.cookie('aeronightmode', 'nightmodeon', { expires: 365, path: '/' });
		return false;
    });
});