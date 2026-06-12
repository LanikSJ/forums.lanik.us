var dark_mode = localStorage.getItem('night_mode');
	if (dark_mode == 'dark') {
		$('body').prepend("<div id='overlay'></div>");
		$('#overlay').css("display","block");
		$('.nightmodeoff').css("display","none");
		$('.nightmodeon').css("display","block");
	};
