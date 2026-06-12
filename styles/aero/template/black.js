var colorswitch = $.cookie('aerocolor');
	var url_greenbg = "styles/aero/theme/images/green/bg_green.gif";
	var url_greenheader = "styles/aero/theme/images/green/header_green.png";
	var url_greenheader_rtl = "styles/aero/theme/images/green/header_green_rtl.png";
	var url_greenheaderbg = "styles/aero/theme/images/green/headerbg_green.gif";
	
	var url_bluebg = "styles/aero/theme/images/blue/bg_blue.gif";
	var url_blueheader = "styles/aero/theme/images/blue/header_blue.png";
	var url_blueheader_rtl = "styles/aero/theme/images/blue/header_blue_rtl.png";
	var url_blueheaderbg = "styles/aero/theme/images/blue/headerbg_blue.gif";
	
	var url_blackbg = "styles/aero/theme/images/black/bg_black.gif";
	var url_blackheader = "styles/aero/theme/images/black/header_black.png";
	var url_blackheader_rtl = "styles/aero/theme/images/black/header_black_rtl.png";
	var url_blackheaderbg = "styles/aero/theme/images/black/headerbg_black.gif";
	
	var url_pinkbg = "styles/aero/theme/images/pink/bg_pink.gif";
	var url_pinkheader = "styles/aero/theme/images/pink/header_pink.png";
	var url_pinkheader_rtl = "styles/aero/theme/images/pink/header_pink_rtl.png";
	var url_pinkheaderbg = "styles/aero/theme/images/pink/headerbg_pink.gif";
	
	var url_purplebg = "styles/aero/theme/images/purple/bg_purple.gif";
	var url_purpleheader = "styles/aero/theme/images/purple/header_purple.png";
	var url_purpleheader_rtl = "styles/aero/theme/images/purple/header_purple_rtl.png";
	var url_purpleheaderbg = "styles/aero/theme/images/purple/headerbg_purple.gif";
	
	var url_redbg = "styles/aero/theme/images/red/bg_red.gif";
	var url_redheader = "styles/aero/theme/images/red/header_red.png";
	var url_redheader_rtl = "styles/aero/theme/images/red/header_red_rtl.png";
	var url_redheaderbg = "styles/aero/theme/images/red/headerbg_red.gif";
	
	// Set the user's selection
	if (colorswitch == 'blue') {
		$('body').css("background-color","#CCE3F3").css("background-image","url(" + url_bluebg + ")");
		$('.headerbar').css("background-image","url(" + url_blueheader + ")");
		$('.rtl .headerbar').css("background-image","url(" + url_blueheader_rtl + ")");
		$('.headerbg').css("background-color","#032942").css("background-image","url(" + url_blueheaderbg + ")");
		$('.action-bar .coloredbutton').css("border-color","#002A46").css("background-color","#95B5CB").css("background-image","-webkit-linear-gradient(top, #95B5CB 0%, #00263F 100%)").css("background-image","linear-gradient(to bottom, #95B5CB 0%,#00263F 100%)").css("filter","progid:DXImageTransform.Microsoft.gradient( startColorstr='#95B5CB', endColorstr='#00263F',GradientType=0 )");
		$('.action-bar .coloredbutton').mouseenter(function() {
			$(this).css("background-color","#00263F").css("background-image","-webkit-linear-gradient(top, #00263F 0%, #95B5CB 100%)").css("background-image","linear-gradient(to bottom, #00263F 0%,#95B5CB 100%)").css("filter","progid:DXImageTransform.Microsoft.gradient( startColorstr='#00263F', endColorstr='#95B5CB',GradientType=0 )");
		}).mouseleave(function() {
			$(this).css("border-color","#002A46").css("background-color","#95B5CB").css("background-image","-webkit-linear-gradient(top, #95B5CB 0%, #00263F 100%)").css("background-image","linear-gradient(to bottom, #95B5CB 0%,#00263F 100%)").css("filter","progid:DXImageTransform.Microsoft.gradient( startColorstr='#95B5CB', endColorstr='#00263F',GradientType=0 )");
		});
    };
	
	if (colorswitch == 'green') {
		$('body').css("background-color","#CCF3D9").css("background-image","url(" + url_greenbg + ")");
		$('.headerbar').css("background-image","url(" + url_greenheader + ")");
		$('.rtl .headerbar').css("background-image","url(" + url_greenheader_rtl + ")");
		$('.headerbg').css("background-color","#003803").css("background-image","url(" + url_greenheaderbg + ")");
		$('.action-bar .coloredbutton').css("border-color","#004605").css("background-color","#95CB99").css("background-image","-webkit-linear-gradient(top, #95CB99 0%, #004005 100%)").css("background-image","linear-gradient(to bottom, #95CB99 0%,#004005 100%)").css("filter","progid:DXImageTransform.Microsoft.gradient( startColorstr='#95CB99', endColorstr='#004005',GradientType=0 )");
		$('.action-bar .coloredbutton').mouseenter(function() {
			$(this).css("background-color","#004005").css("background-image","-webkit-linear-gradient(top, #004005 0%, #95CB99 100%)").css("background-image","linear-gradient(to bottom, #004005 0%,#95CB99 100%)").css("filter","progid:DXImageTransform.Microsoft.gradient( startColorstr='#004005', endColorstr='#95CB99',GradientType=0 )");
		}).mouseleave(function() {
			$(this).css("border-color","#004605").css("background-color","#95CB99").css("background-image","-webkit-linear-gradient(top, #95CB99 0%, #004005 100%)").css("background-image","linear-gradient(to bottom, #95CB99 0%,#004005 100%)").css("filter","progid:DXImageTransform.Microsoft.gradient( startColorstr='#95CB99', endColorstr='#004005',GradientType=0 )");
		});
    };
	
	if (colorswitch == 'pink') {
		$('body').css("background-color","#F3CCE3").css("background-image","url(" + url_pinkbg + ")");
		$('.headerbar').css("background-image","url(" + url_pinkheader + ")");
		$('.rtl .headerbar').css("background-image","url(" + url_pinkheader_rtl + ")");
		$('.headerbg').css("background-color","#390023").css("background-image","url(" + url_pinkheaderbg + ")");
		$('.action-bar .coloredbutton').css("border-color","#46002A").css("background-color","#CB95B5").css("background-image","-webkit-linear-gradient(top, #CB95B5 0%, #400026 100%)").css("background-image","linear-gradient(to bottom, #CB95B5 0%,#400026 100%)").css("filter","progid:DXImageTransform.Microsoft.gradient( startColorstr='#CB95B5', endColorstr='#400026',GradientType=0 )");
		$('.action-bar .coloredbutton').mouseenter(function() {
			$(this).css("background-color","#400026").css("background-image","-webkit-linear-gradient(top, #400026 0%, #CB95B5 100%)").css("background-image","linear-gradient(to bottom, #400026 0%,#CB95B5 100%)").css("filter","progid:DXImageTransform.Microsoft.gradient( startColorstr='#400026', endColorstr='#CB95B5',GradientType=0 )");
		}).mouseleave(function() {
			$(this).css("border-color","#46002A").css("background-color","#CB95B5").css("background-image","-webkit-linear-gradient(top, #CB95B5 0%, #400026 100%)").css("background-image","linear-gradient(to bottom, #CB95B5 0%,#400026 100%)").css("filter","progid:DXImageTransform.Microsoft.gradient( startColorstr='#CB95B5', endColorstr='#400026',GradientType=0 )");
		});
    };
	
	if (colorswitch == 'purple') {
		$('body').css("background-color","#DCCCF3").css("background-image","url(" + url_purplebg + ")");
		$('.headerbar').css("background-image","url(" + url_purpleheader + ")");
		$('.rtl .headerbar').css("background-image","url(" + url_purpleheader_rtl + ")");
		$('.headerbg').css("background-color","#150037").css("background-image","url(" + url_purpleheaderbg + ")");
		$('.action-bar .coloredbutton').css("border-color","#1C0046").css("background-color","#AB95CB").css("background-image","-webkit-linear-gradient(top, #AB95CB 0%, #1A0040 100%)").css("background-image","linear-gradient(to bottom, #AB95CB 0%,#1A0040 100%)").css("filter","progid:DXImageTransform.Microsoft.gradient( startColorstr='#AB95CB', endColorstr='#1A0040',GradientType=0 )");
		$('.action-bar .coloredbutton').mouseenter(function() {
			$(this).css("background-color","#1A0040").css("background-image","-webkit-linear-gradient(top, #1A0040 0%, #AB95CB 100%)").css("background-image","linear-gradient(to bottom, #1A0040 0%,#AB95CB 100%)").css("filter","progid:DXImageTransform.Microsoft.gradient( startColorstr='#1A0040', endColorstr='#AB95CB',GradientType=0 )");
		}).mouseleave(function() {
			$(this).css("border-color","#1C0046").css("background-color","#AB95CB").css("background-image","-webkit-linear-gradient(top, #AB95CB 0%, #1A0040 100%)").css("background-image","linear-gradient(to bottom, #AB95CB 0%,#1A0040 100%)").css("filter","progid:DXImageTransform.Microsoft.gradient( startColorstr='#AB95CB', endColorstr='#1A0040',GradientType=0 )");
		});
    };
	
	if (colorswitch == 'red') {
		$('body').css("background-color","#EEEEEE").css("background-image","url(" + url_redbg + ")");
		$('.headerbar').css("background-image","url(" + url_redheader + ")");
		$('.rtl .headerbar').css("background-image","url(" + url_redheader_rtl + ")");
		$('.headerbg').css("background-color","#310004").css("background-image","url(" + url_redheaderbg + ")");
		$('.action-bar .coloredbutton').css("border-color","#460500").css("background-color","#CB9995").css("background-image","-webkit-linear-gradient(top, #CB9995 0%, #420400 100%)").css("background-image","linear-gradient(to bottom, #CB9995 0%,#420400 100%)").css("filter","progid:DXImageTransform.Microsoft.gradient( startColorstr='#CB9995', endColorstr='#420400',GradientType=0 )");
		$('.action-bar .coloredbutton').mouseenter(function() {
			$(this).css("background-color","#420400").css("background-image","-webkit-linear-gradient(top, #420400 0%, #CB9995 100%)").css("background-image","linear-gradient(to bottom, #420400 0%,#CB9995 100%)").css("filter","progid:DXImageTransform.Microsoft.gradient( startColorstr='#420400', endColorstr='#CB9995',GradientType=0 )");
		}).mouseleave(function() {
			$(this).css("border-color","#460500").css("background-color","#CB9995").css("background-image","-webkit-linear-gradient(top, #CB9995 0%, #420400 100%)").css("background-image","linear-gradient(to bottom, #CB9995 0%,#420400 100%)").css("filter","progid:DXImageTransform.Microsoft.gradient( startColorstr='#CB9995', endColorstr='#420400',GradientType=0 )");
		});
    };

$(document).ready(function() {
	// When blue is clicked:
    $('.colorblue').click(function() {
		$('body').css("background-color","#CCE3F3").css("background-image","url(" + url_bluebg + ")");
		$('.headerbar').css("background-image","url(" + url_blueheader + ")");
		$('.rtl .headerbar').css("background-image","url(" + url_blueheader_rtl + ")");
		$('.headerbg').css("background-color","#032942").css("background-image","url(" + url_blueheaderbg + ")");
		$('.action-bar .coloredbutton').css("border-color","#002A46").css("background-color","#95B5CB").css("background-image","-webkit-linear-gradient(top, #95B5CB 0%, #00263F 100%)").css("background-image","linear-gradient(to bottom, #95B5CB 0%,#00263F 100%)").css("filter","progid:DXImageTransform.Microsoft.gradient( startColorstr='#95B5CB', endColorstr='#00263F',GradientType=0 )");
		$('.action-bar .coloredbutton').mouseenter(function() {
			$(this).css("background-color","#00263F").css("background-image","-webkit-linear-gradient(top, #00263F 0%, #95B5CB 100%)").css("background-image","linear-gradient(to bottom, #00263F 0%,#95B5CB 100%)").css("filter","progid:DXImageTransform.Microsoft.gradient( startColorstr='#00263F', endColorstr='#95B5CB',GradientType=0 )");
		}).mouseleave(function() {
			$(this).css("border-color","#002A46").css("background-color","#95B5CB").css("background-image","-webkit-linear-gradient(top, #95B5CB 0%, #00263F 100%)").css("background-image","linear-gradient(to bottom, #95B5CB 0%,#00263F 100%)").css("filter","progid:DXImageTransform.Microsoft.gradient( startColorstr='#95B5CB', endColorstr='#00263F',GradientType=0 )");
		});
		$.cookie('aerocolor', 'blue', { expires: 365, path: '/' });
		return false;
    });
	
	// When black is clicked:
    $('.colorblack').click(function() {
		$('body').css("background-color","#DFDFDF").css("background-image","url(" + url_blackbg + ")");
		$('.headerbar').css("background-image","url(" + url_blackheader + ")");
		$('.rtl .headerbar').css("background-image","url(" + url_blackheader_rtl + ")");
		$('.headerbg').css("background-color","#0B0B0E").css("background-image","url(" + url_blackheaderbg + ")");
		$('.action-bar .coloredbutton').css("border-color","#1D1D1D").css("background-color","#8F8F8F").css("background-image","-webkit-linear-gradient(top, #8F8F8F 0%, #1D1D1D 100%)").css("background-image","linear-gradient(to bottom, #8F8F8F 0%,#1D1D1D 100%)").css("filter","progid:DXImageTransform.Microsoft.gradient( startColorstr='#8F8F8F', endColorstr='#1D1D1D',GradientType=0 )");
		$('.action-bar .coloredbutton').mouseenter(function() {
			$(this).css("background-color","#1D1D1D").css("background-image","-webkit-linear-gradient(top, #1D1D1D 0%, #8F8F8F 100%)").css("background-image","linear-gradient(to bottom, #1D1D1D 0%,#8F8F8F 100%)").css("filter","progid:DXImageTransform.Microsoft.gradient( startColorstr='#1D1D1D', endColorstr='#8F8F8F',GradientType=0 )");
		}).mouseleave(function() {
			$(this).css("border-color","#1D1D1D").css("background-color","#8F8F8F").css("background-image","-webkit-linear-gradient(top, #8F8F8F 0%, #1D1D1D 100%)").css("background-image","linear-gradient(to bottom, #8F8F8F 0%,#1D1D1D 100%)").css("filter","progid:DXImageTransform.Microsoft.gradient( startColorstr='#8F8F8F', endColorstr='#1D1D1D',GradientType=0 )");
		});
		$.cookie('aerocolor', null, { path: '/' });
		return false;
    });
	
	// When green is clicked:
    $('.colorgreen').click(function() {
		$('body').css("background-color","#CCF3D9").css("background-image","url(" + url_greenbg + ")");
		$('.headerbar').css("background-image","url(" + url_greenheader + ")");
		$('.rtl .headerbar').css("background-image","url(" + url_greenheader_rtl + ")");
		$('.headerbg').css("background-color","#003803").css("background-image","url(" + url_greenheaderbg + ")");
		$('.action-bar .coloredbutton').css("border-color","#004605").css("background-color","#95CB99").css("background-image","-webkit-linear-gradient(top, #95CB99 0%, #004005 100%)").css("background-image","linear-gradient(to bottom, #95CB99 0%,#004005 100%)").css("filter","progid:DXImageTransform.Microsoft.gradient( startColorstr='#95CB99', endColorstr='#004005',GradientType=0 )");
		$('.action-bar .coloredbutton').mouseenter(function() {
			$(this).css("background-color","#004005").css("background-image","-webkit-linear-gradient(top, #004005 0%, #95CB99 100%)").css("background-image","linear-gradient(to bottom, #004005 0%,#95CB99 100%)").css("filter","progid:DXImageTransform.Microsoft.gradient( startColorstr='#004005', endColorstr='#95CB99',GradientType=0 )");
		}).mouseleave(function() {
			$(this).css("border-color","#004605").css("background-color","#95CB99").css("background-image","-webkit-linear-gradient(top, #95CB99 0%, #004005 100%)").css("background-image","linear-gradient(to bottom, #95CB99 0%,#004005 100%)").css("filter","progid:DXImageTransform.Microsoft.gradient( startColorstr='#95CB99', endColorstr='#004005',GradientType=0 )");
		});
		$.cookie('aerocolor', 'green', { expires: 365, path: '/' });
		return false;
    });
	
	// When pink is clicked:
    $('.colorpink').click(function() {
		$('body').css("background-color","#F3CCE3").css("background-image","url(" + url_pinkbg + ")");
		$('.headerbar').css("background-image","url(" + url_pinkheader + ")");
		$('.rtl .headerbar').css("background-image","url(" + url_pinkheader_rtl + ")");
		$('.headerbg').css("background-color","#390023").css("background-image","url(" + url_pinkheaderbg + ")");
		$('.action-bar .coloredbutton').css("border-color","#46002A").css("background-color","#CB95B5").css("background-image","-webkit-linear-gradient(top, #CB95B5 0%, #400026 100%)").css("background-image","linear-gradient(to bottom, #CB95B5 0%,#400026 100%)").css("filter","progid:DXImageTransform.Microsoft.gradient( startColorstr='#CB95B5', endColorstr='#400026',GradientType=0 )");
		$('.action-bar .coloredbutton').mouseenter(function() {
			$(this).css("background-color","#400026").css("background-image","-webkit-linear-gradient(top, #400026 0%, #CB95B5 100%)").css("background-image","linear-gradient(to bottom, #400026 0%,#CB95B5 100%)").css("filter","progid:DXImageTransform.Microsoft.gradient( startColorstr='#400026', endColorstr='#CB95B5',GradientType=0 )");
		}).mouseleave(function() {
			$(this).css("border-color","#46002A").css("background-color","#CB95B5").css("background-image","-webkit-linear-gradient(top, #CB95B5 0%, #400026 100%)").css("background-image","linear-gradient(to bottom, #CB95B5 0%,#400026 100%)").css("filter","progid:DXImageTransform.Microsoft.gradient( startColorstr='#CB95B5', endColorstr='#400026',GradientType=0 )");
		});
		$.cookie('aerocolor', 'pink', { expires: 365, path: '/' });
		return false;
    });
	
	// When purple is clicked:
    $('.colorpurple').click(function() {
		$('body').css("background-color","#DCCCF3").css("background-image","url(" + url_purplebg + ")");
		$('.headerbar').css("background-image","url(" + url_purpleheader + ")");
		$('.rtl .headerbar').css("background-image","url(" + url_purpleheader_rtl + ")");
		$('.headerbg').css("background-color","#150037").css("background-image","url(" + url_purpleheaderbg + ")");
		$('.action-bar .coloredbutton').css("border-color","#1C0046").css("background-color","#AB95CB").css("background-image","-webkit-linear-gradient(top, #AB95CB 0%, #1A0040 100%)").css("background-image","linear-gradient(to bottom, #AB95CB 0%,#1A0040 100%)").css("filter","progid:DXImageTransform.Microsoft.gradient( startColorstr='#AB95CB', endColorstr='#1A0040',GradientType=0 )");
		$('.action-bar .coloredbutton').mouseenter(function() {
			$(this).css("background-color","#1A0040").css("background-image","-webkit-linear-gradient(top, #1A0040 0%, #AB95CB 100%)").css("background-image","linear-gradient(to bottom, #1A0040 0%,#AB95CB 100%)").css("filter","progid:DXImageTransform.Microsoft.gradient( startColorstr='#1A0040', endColorstr='#AB95CB',GradientType=0 )");
		}).mouseleave(function() {
			$(this).css("border-color","#1C0046").css("background-color","#AB95CB").css("background-image","-webkit-linear-gradient(top, #AB95CB 0%, #1A0040 100%)").css("background-image","linear-gradient(to bottom, #AB95CB 0%,#1A0040 100%)").css("filter","progid:DXImageTransform.Microsoft.gradient( startColorstr='#AB95CB', endColorstr='#1A0040',GradientType=0 )");
		});
		$.cookie('aerocolor', 'purple', { expires: 365, path: '/' });
		return false;
    });
	
	// When red is clicked:
    $('.colorred').click(function() {
		$('body').css("background-color","#EEEEEE").css("background-image","url(" + url_redbg + ")");
		$('.headerbar').css("background-image","url(" + url_redheader + ")");
		$('.rtl .headerbar').css("background-image","url(" + url_redheader_rtl + ")");
		$('.headerbg').css("background-color","#310004").css("background-image","url(" + url_redheaderbg + ")");
		$('.action-bar .coloredbutton').css("border-color","#460500").css("background-color","#CB9995").css("background-image","-webkit-linear-gradient(top, #CB9995 0%, #420400 100%)").css("background-image","linear-gradient(to bottom, #CB9995 0%,#420400 100%)").css("filter","progid:DXImageTransform.Microsoft.gradient( startColorstr='#CB9995', endColorstr='#420400',GradientType=0 )");
		$('.action-bar .coloredbutton').mouseenter(function() {
			$(this).css("background-color","#420400").css("background-image","-webkit-linear-gradient(top, #420400 0%, #CB9995 100%)").css("background-image","linear-gradient(to bottom, #420400 0%,#CB9995 100%)").css("filter","progid:DXImageTransform.Microsoft.gradient( startColorstr='#420400', endColorstr='#CB9995',GradientType=0 )");
		}).mouseleave(function() {
			$(this).css("border-color","#460500").css("background-color","#CB9995").css("background-image","-webkit-linear-gradient(top, #CB9995 0%, #420400 100%)").css("background-image","linear-gradient(to bottom, #CB9995 0%,#420400 100%)").css("filter","progid:DXImageTransform.Microsoft.gradient( startColorstr='#CB9995', endColorstr='#420400',GradientType=0 )");
		});
		$.cookie('aerocolor', 'red', { expires: 365, path: '/' });
		return false;
    });
});