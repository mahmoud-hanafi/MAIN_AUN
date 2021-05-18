(function ($, drupalSettings) {
  Drupal.behaviors.drupalSlider = {
    attach: function (context, settings) {
      var values = settings.drupalSlider;
      var autoplay = values.autoplay ? true : false;
      var arrows = values.arrows ? true : false;
      var buttons = values.buttons ? true : false;
      var shuffle = values.shuffle ? true : false;
      var full_screen = values.full_screen ? true : false;
      var fade = values.fade ? true : false;
      var loop = values.loop ? true : false;
      var orientation = values.orientation ? 'vertical' : 'horizontal';
      var thumbnails_position = values.thumbnails_position;
      if (values.carousel) {
        $('#'+values.id, context).once('drupalSliderBehavior').sliderPro({     
          arrows: arrows,
          buttons: buttons,
          loop: loop,
          fullScreen: full_screen,
          shuffle: shuffle,
          autoplay: autoplay,
          smallSize: 500,
          mediumSize: 1000,
          largeSize: 3000,   
          waitForLayers: true,
          autoScaleLayers: true,  
          visibleSize: '100%',    
        });
      } else {
        $('#'+values.id, context).once('drupalSliderBehavior').sliderPro({
          width: values.width,
          height: values.height,       
          orientation: orientation,
          thumbnailsPosition: thumbnails_position, 
          loop: loop,
          fade: fade,
          arrows: arrows,
          buttons: buttons,
          fullScreen: full_screen,
          shuffle: shuffle,
          autoplay: autoplay,
          smallSize: 500,
          mediumSize: 1000,
          largeSize: 3000,   
          waitForLayers: false,
          autoScaleLayers: false,           
        });  
      }
    }
  };

})(jQuery, drupalSettings);