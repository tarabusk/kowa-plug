jQuery(document).ready(function($) {

    // Back to top
    var offset    = 200;
    var speed    = 350;
    var duration = 500;
	   $(window).scroll(function(){
            if ($(this).scrollTop() < offset) {
			     $('.kw_bcktop') .fadeOut(duration);
            } else {
			     $('.kw_bcktop') .fadeIn(duration);
            }
        });
	  $('.kw_bcktop').on('click', function(){
  		$('html, body').animate({scrollTop:0}, speed);
  		return false;
		});


  // Sommaire
  $(document).on('click','#kowala-sommaire a',function(){
        var h = $(this).attr('href');

        $('body,html').animate({
            scrollTop:$(h).offset().top
        }, 500);
        return false;
  });
});
