document.addEventListener('DOMContentLoaded', function() {
    jQuery(function($){
    // $('body').attr('data-barba', 'wrapper');


        $('figure#back').on('click', function(e){
            e.preventDefault();
            window.history.back();
        });
  
    var mywindow = $(window);
    var mypos = mywindow.scrollTop();

    mywindow.scroll(function() {
    if (mypos > 10) {
        if(mywindow.scrollTop() > mypos) {
            $('header.wp-block-template-part').addClass('headerup');
            $('header.wp-block-template-part').removeClass('fullheader');
            // $('.single-products .apertura').removeClass('fixed');
        } else {
            $('header.wp-block-template-part').removeClass('headerup');
            $('header.wp-block-template-part').addClass('fullheader');
            // $('.single-products .apertura').addClass('fixed');      
        } if (mypos = 0) {
            $('header.wp-block-template-part').removeClass('fullheader');
            }
        }
        mypos = mywindow.scrollTop();
        });
    });    
});
