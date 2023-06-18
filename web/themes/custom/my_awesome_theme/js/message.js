(function($) {
  Drupal.behaviors.welcomeMessage = {
    attach: function(context, settings) {
      $(once('DOMContentLoaded','html')).each(function() {
        console.log(`Hi ${settings.welcomeMessage.userName}, welcome to my site!`);
      });
    }
  };
})(jQuery);

