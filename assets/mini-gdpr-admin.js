/**
 * Mini WP GDPR : CF7 Integration
 */
(function ($) {
  'use strict';
  $(window).on('load', function () {
    console.log('Mini WP GDPR : load');

    $('[data-reset-all-consents]').click(function (event) {
      const button = $(this);
      const container = $(button).parent();
      const spinner = $(container).find('img');
      const args = $(button).data('reset-all-consents');

      const isConfirmed = !args.confirmMessage || confirm(args.confirmMessage);

      if (isConfirmed) {
        const request = {
          action: args.action,
          nonce: args.nonce,
        };

        spinner.show();

        $.post(ajaxurl, request)
          .done(function (response) {
            if (response.message) {
              alert(response.message);
            }
          })
          .always(function () {
            if (spinner) {
              spinner.fadeOut();
            }

            button.prop('disabled', false);
          });
      }
    });
  });
})(jQuery);
