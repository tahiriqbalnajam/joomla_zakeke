jQuery(document).ready(function($) {

	$('body').tooltip({
		selector: '[data-bs-toggle="tooltip"]'
	});

	// Toogle search display on mobiles
	$('#search-toggle').click(function(){
		$('.main-search').slideToggle();
	});

	// Back-top
	$(window).scroll(function () {
		if ($(this).scrollTop() > 100) {
			$('.back-to-top-link').fadeIn();
		} else {
			$('.back-to-top-link').fadeOut();
		}
	});

	$('.back-to-top-link').click(function(e){
		$('html, body').animate({scrollTop:0}, 'slow');
		e.preventDefault();
	});

    // Offcanvas
    $('.offcanvas-body a[href="#"], .offcanvas-body .mod-menu__separator').click(function(){
        $(this).next('.subtoggle').click();
    });

    $('.subtoggle').click(function(e){
         e.preventDefault();
         $(this).toggleClass('open').next('ul').stop().slideToggle().parents('li').siblings('li').find('ul').slideUp().end().find('.open').removeClass('open');
    });

});