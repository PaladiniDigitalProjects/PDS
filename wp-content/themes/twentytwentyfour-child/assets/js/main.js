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


/* POP UP */

window.addEventListener("load", () => {
    let button = document.querySelector(".click");
    let buttonDonwload = document.querySelector(".clickDonwload");
    let close = document.querySelector(".close");
    let body = document.querySelector("body");
    let hide = document.getElementById("wpforms-5359-field_18");
    let box = document.querySelector(".download-pager");
  
    close.addEventListener("click", () => {
        body.classList.remove("show");
    });
  
    button.addEventListener("click", () => {
        event.preventDefault();
        // var href = button.querySelector('a').href;
        // console.log(href);
        if (hide) {
             hide.value = href;
         }
         body.classList.add("show");
    });

    buttonDonwload.addEventListener("click", () => {
        event.preventDefault();
        box.classList.add("display-box");
    });

});