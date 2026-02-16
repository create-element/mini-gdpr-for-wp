/**
 * Mini GDPR
 */
(function ($) {
  'use strict';
  $(window).on('load', function () {
    console.log('Mini GDPR : load');

    if (typeof miniWpGdpr != 'undefined') {
      $('form .mini-gdpr-checkbox')
        .closest('form')
        .submit(function (event) {
          if (!$(event.target).find('.mini-gdpr-checkbox').prop('checked')) {
            event.preventDefault();

            alert(miniWpGdpr.termsNotAccepted);
          }
        });

      if (miniWpGdpr.acceptAction) {
        $('.mini-gdpr-form .mini-gdpr-checkbox').change(function (event) {
          if ($(this).prop('checked')) {
            var container = $(this).closest('.mini-gdpr-form');
            $(this).delay(250).prop('disabled', true);
            // $(this).css('cursor', 'wait');

            var request = {
              action: miniWpGdpr.acceptAction,
              nonce: miniWpGdpr.acceptNonce,
              terms: $(this).prop('checked') ? '1' : '0',
            };

            $.post(miniWpGdpr.ajaxUrl, request)
              .done(function (response) {
                // OK
                if (response.success === '1') {
                  if (!response.message) {
                    response.message = 'OK';
                  }
                  $(container).fadeOut(400);
                  $(`<p>${response.message}</p>`).insertAfter(container).hide().delay(400).fadeIn();
                }
              })
              .fail(function (response) {
                console.log(response);
              })
              .always(function (response) {
                // alert("always");
              });
          }
        });
      }
    }
  });
})(jQuery);
