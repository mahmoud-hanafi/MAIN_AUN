document.addEventListener('DOMContentLoaded', function () {

   var mediaElements = document.querySelectorAll('video.video-embed, audio'), i, total = mediaElements.length;

   for (i = 0; i < total; i++) {
      new MediaElementPlayer(mediaElements[i], {
         stretching: 'auto',
         pluginPath: '../build/',
         features: [],
         success: function (media) {
            var renderer = document.getElementById(media.id + '-rendername');

            media.addEventListener('loadedmetadata', function () {
               var src = media.originalNode.getAttribute('src').replace('&amp;', '&');
               
            });
         }
      });
   }
});

document.addEventListener('DOMContentLoaded', function () {

   var mediaElements = document.querySelectorAll('video.video-upload, audio'), i, total = mediaElements.length;

   for (i = 0; i < total; i++) {
      new MediaElementPlayer(mediaElements[i], {
         stretching: 'auto',
         pluginPath: '../build/',
         success: function (media) {
            var renderer = document.getElementById(media.id + '-rendername');

            media.addEventListener('loadedmetadata', function () {
               var src = media.originalNode.getAttribute('src').replace('&amp;', '&');
               
            });
         }
      });
   }
});