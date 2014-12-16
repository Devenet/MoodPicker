$(function() {
    $('#back_top').on('click', function() { $('html,body').animate({scrollTop: 0}, 'slow'); return false; });
    $('.navigate a, a.navigate').on('click', function() { $('html,body').animate({scrollTop: $($(this).attr('href')).offset().top}); });
    $('a[rel="external"]').click(function() { window.open($(this).attr('href')); return false; });
	$('.tip').tooltip();
	var alert_message = $('.alert.message .close');
	function closeMessage() { alert_message.parent().slideUp(500, function(){ location.href = location.pathname; }); }
	if(alert_message.length > 0) { setTimeout(closeMessage, 5000); }
	alert_message.on('click', closeMessage);
});