(function($) {
  "use strict";
/* ----------------------------------
 @ bCurrency
 @ Version: 1.0
 @ Author: DynamicWebLab
 @Email: dynamicweblab@gmail.com
 -------------------------------------*/
//Make sure jQuery has been loaded before main.js
if (typeof jQuery === "undefined") {
    throw new Error("CTO requires jQuery to work on!");
}

/* ctoObj
 *
 * @type Object
 * @description $.ctoObj Will hold options and functions.
 */
$.ctoObj = {};

/* ---------------------
 * /// bCurrency Options ///
 * ---------------------
 * You can enable disable functions here
 */
$.ctoObj.options = {

};

/* meanMenu()
 * ==========
 * Mean Menu for mobile
 *
 * @type selector
 * @usage: $.ctoObj.meanMenu(selector)
 */
$.ctoObj.meanMenu = function (selector) {

    if ($(selector).length) {
        $(selector).meanmenu({
          meanMenuContainer: '#cto-meanmenu',
          meanScreenWidth: '991',
          mobileLogo: $( '.logo' ).clone().html(), //clone the logo section from desktop version
        });
    }
};

/* counter()
 * ==========
 * counterUp for Fun Fact
 *
 * @type selector
 * @usage: $.ctoObj.counter(selector)
 */
$.ctoObj.Counter = function (selector) {


    if ($(selector).length) {
        $(selector).counterUp({
            time: 1500,
        });
    }
};

/* BgImage()
 * ==========
 * Will position image from data attr
 *
 * @type selector
 * @usage: $.ctoObj.BgImage(selector)
 */
$.ctoObj.BgImage = function (selector) {

    if($(selector).length){


        $(selector).each(function() {
            var image = $(this).data('bg');
            $(this).css({
                'background-image' : 'url(' + image + ')'
            });
        });
    }


};


/* ProgressBar()
 * ==========
 * Create progress bar
 *
 * @type selector
 * @usage: $.ctoObj.ProgressBar(selector)
 */
$.ctoObj.ProgressBar = function (selector) {

    if ($(selector).length) {

        $(selector).each(function () {
            var progress = $(this).data("progress");
            var prog_width = progress + '%';
            if (progress <= 100) {
                $(this).append('<div class="bar-inner" style="width:' + prog_width + '"><span>' + prog_width + '</span></div>');
            } else {
                $(this).append('<div class="bar-inner" style="width:100%"><span>' + prog_width + '</span></div>');
            }
        });

    }

};



/* TestimonialCarousel()
 * ==========
 * Adds Carousel functionality for testimonial.
 *
 * @type Function
 * @usage: $.ctoObj.TestimonialCarousel('#testimonial-carousel')
 */
$.ctoObj.TestimonialCarousel = function(selector) {

  if ($(selector).length) {
    $(selector).owlCarousel({
      dots: false,
      nav: true,
      loop: true,
      smartSpeed: 700,
      items: 1,
      responsiveClass:true,
      responsive:{
          0:{
              items:1,
          },
          600:{
              items:1,
          },
          800:{
              items:1,
          }
      },
      navText: ['<i class="fa fa-arrow-left"></i>', '<i class="fa fa-arrow-right"></i>']
    });
  }


};

/* ContactForm()
 * ==========
 * Ajax Contact Form
 *
 * @type Function
 * @usage: $.ctoObj.ContactForm(selector)
 */
$.ctoObj.ContactForm = function(selector) {

  if ($(selector).length) {

    var $result = $(selector).find("#result");

    // init the validator

    $(selector).validate({
      rules: {
          name: {
              required: true,
          },
          email: {
              required: true,
              email: true
          },
          message: {
              required: true,
          }
      },
      submitHandler: function(form) {

        $.ajax({
            type: "POST",
            dataType: "json",
            url: 'mail/contact.php',
            data: $(form).serialize(),
            success: function (data){

              if(data.status == "1") {
                $result.empty().html('<span class="alert alert-success">'+data.msg+'</span>');
                $(selector)[0].reset();
              }else{

                $result.empty().html('<span class="alert alert-danger">'+data.msg+'</span>');

              }

            }
        });

      }
    });

  }

};

/* QuoteForm()
 * ==========
 * Ajax Quote Form
 *
 * @type Function
 * @usage: $.ctoObj.QuoteForm(selector)
 */
$.ctoObj.QuoteForm = function(selector) {

  if ($(selector).length) {

    var $result = $(selector).find("#result");
    // init the validator

    $(selector).validate({
      rules: {
          name: {
              required: true,
          },
          email: {
              required: true,
              email: true
          },
          subject: {
              required: true,
          },
          message: {
              required: true,
          }
      },
      submitHandler: function(form) {

        $.ajax({
            type: "POST",
            dataType: "json",
            url: 'mail/quote.php',
            data: $(form).serialize(),
            success: function (data){

              if(data.status == "1") {
                $result.empty().html('<span class="alert alert-success">'+data.msg+'</span>');
                $(selector)[0].reset();
              }else{

                $result.empty().html('<span class="alert alert-danger">'+data.msg+'</span>');

              }

            }
        });

      }
    });

  }

};

/* PreLoader()
 * ==========
 * Create progress bar
 *
 * @type selector
 * @usage: $.ctoObj.PreLoader(selector)
 */
$.ctoObj.PreLoader = function (selector) {

    if ($(selector).length) {

      $(selector).fadeOut("slow");

    }

};

//Activate meanMenu
$.ctoObj.meanMenu('.main-menu');

 //Activate Testimonial Carousel
  $.ctoObj.TestimonialCarousel('.owl-carousel');


//Activate Counter
$.ctoObj.Counter('.currency-count');

//Activate Progress Bar
$.ctoObj.ProgressBar('.progress-bar-style');

//Activate BG IMAGE
$.ctoObj.BgImage('.intro-section');

//Activate contact form
$.ctoObj.ContactForm('#contact-form2');

//Activate Quote form
$.ctoObj.QuoteForm('#quote-form');


$(window).on('load', function () {

  //Activate preloder
  $.ctoObj.PreLoader('#preloder');

});


})(jQuery);
