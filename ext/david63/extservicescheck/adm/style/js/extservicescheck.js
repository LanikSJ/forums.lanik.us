;(function($, document)
{
	'use strict';

	$(document).ready(function()
	{
		$('.file_hide').hide();
     	$('.showkey').on('click', function()
		{
     		var key = $(this).attr('key');
			$('#' + key).toggle();

			if ($('.files_open' + key).is(':hidden'))
			{
				$('.files' + key).hide();
				$('.files_open' + key).show();
			}
			else
			{
				$('.files_open' + key).hide();
				$('.files' + key).show();
			}
		});
  	});

	$("a[name=copy_pre]").click(function()
	{
		var id 		= $(this).attr('id');
		var el 		= document.getElementById(id);
		var range	= document.createRange();

		range.selectNodeContents(el);
		var sel = window.getSelection();
		sel.removeAllRanges();
		sel.addRange(range);
		document.execCommand('copy');

		return false;
	});
})(jQuery, document);
