/**
 * pp-public.js
 */
(function ($) {
  'use strict';
  $(window).on('load', function () {
    // console.log('Power Plugins Public : load');

    const popupTtl = 2000;

    $('[data-click-to-copy]').click(function (event) {
      event.preventDefault();

      const container = $(this);
      const args = $(container).data('click-to-copy');

      navigator.clipboard.writeText(args.textToCopy);

      addQuickPopupMessage(container, args.messageWhenCopied);
    });

    function addQuickPopupMessage(container, text) {
      const popup = $(`<div class="pp-quick-popup-overlay"><div class="pp-quick-popup">${text}</div></div>`);
      $(container).append(popup);

      setTimeout(function () {
        $(popup).fadeOut(300, function () {
          $(this).remove();
        });
      }, popupTtl);
    }
  });
})(jQuery);
