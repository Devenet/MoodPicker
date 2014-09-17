$(function() {
    $('#back_top').on('click', function() { $('html,body').animate({scrollTop: 0}, 'slow'); return false; });
    $('.navigate a, a.navigate').on('click', function() { $('html,body').animate({scrollTop: $($(this).attr('href')).offset().top}); });
    $('a[rel="external"]').click(function() { window.open($(this).attr('href')); return false; });
	$('.tip').tooltip();
});