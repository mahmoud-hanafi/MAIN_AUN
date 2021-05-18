/**
 * @file
 * Add JS here for the module.
 */

(function ($) {
  'use strict';
  Drupal.behaviors.cancelRedirect = {
    attach: function (context, settings) {
      $('.cancel-button').click(function () {
        return confirm('Do you want to abort this process?');
      });
    }
  };
})(jQuery);
