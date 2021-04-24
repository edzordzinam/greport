$(function()
{
	$('input[type="checkbox"]:checked').addClass('checked');
	
	$('input[type="checkbox"]').on('click', function()
	{
		$(this).toggleClass('checked');
	});
});