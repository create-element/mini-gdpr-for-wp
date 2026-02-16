/**
 * Mini WP GDPR : CF7 Integration
 */
(function ($) {
  'use strict';
  $(window).on('load', function () {
    console.log('Mini WP GDPR : CF7 : load');

    // if (typeof mwgCF7Data != 'undefined') {
    // console.log('Mini WP GDPR : CF7 : init');

    var spinnerOffTimeout = null;
    // var spinner = $('.mwg-cf7-integration .mwg-spinner');

    // $('.mwg-cf7-form-list .install-consent').click(function(event) {
    // 	event.preventDefault();
    // 	console.log('click');
    // });

    $('[data-mwg-cf7-forms]').each(function (index, el) {
      var container = $(this);
      var meta = container.data('mwg-cf7-forms');
      var table = $(this).find('table');
      var tableBody = table.find('tbody');

      console.log(meta);
      var formCount = 0;

      for (const key in meta.forms) {
        console.log(`${key}: ${meta.forms[key]}`);
        var row = $(`<tr data-form-name="${key}"></tr>`);
        // <span class="dashicons dashicons-no"></span>

        var cell = $(`<td class="align-left">${meta.forms[key].title}</td>`);
        row.append(cell);

        cell = $(
          `<td class="align-centre"><span class="dashicons dashicons-yes" style="display:none;"></span><span class="dashicons dashicons-no" style="display:none;"></span></td>`,
        );
        row.append(cell);

        cell = $(`<td></td>`);
        var button = $(`<button class="button install-consent" disabled data-form-id="${meta.forms[key].formId}">${meta.labels.installConsentButton}</button>`);
        button.click(function (event) {
          event.preventDefault();
          installCF7Consent(parseInt($(this).data('form-id')));
        });
        cell.append(button);

        row.append(cell);

        tableBody.append(row);

        ++formCount;
      }

      if (formCount == 0) {
        $('.mwg-cf7-no-forms').slideDown();
      } else {
        // container.slideDown();
        container.fadeIn();
      }

      refreshCF7FormsTable(meta.forms);

      if (spinnerOffTimeout) {
        clearTimeout(spinnerOffTimeout);
      }

      spinnerOffTimeout = setTimeout(function () {
        $(container).find('.pp-spinner').fadeOut();
      }, 250);
    });

    // function cancelSpinner() {
    // 	spinner.fadeOut();
    // 		$('[data-mwg-cf7-forms]').each(function(index, el) {

    // }

    function refreshCF7FormsTable(formsData) {
      if (formsData) {
        $('[data-mwg-cf7-forms]').each(function (index, el) {
          var table = $(this).find('table');

          for (const key in formsData) {
            var row = $(table).find(`tr[data-form-name="${key}"]`);
            console.log(`Update: ${key}`);
            if (formsData[key].isConsentInstalled) {
              row.find('.dashicons-no').hide();
              row.find('.dashicons-yes').fadeIn();
              row.find(`.install-consent`).prop('disabled', true);
              // row.find(`.install-consent`).fadeOut();
            } else {
              // row.find(`.install-consent`).fadeIn();
              row.find(`.install-consent`).prop('disabled', false);
              row.find('.dashicons-yes').hide();
              row.find('.dashicons-no').fadeIn();
            }
          }
        });
      }
    }

    function installCF7Consent(formId) {
      const installButton = $(`button[data-form-id="${formId}"`);
      const container = installButton.closest('[data-mwg-cf7-forms]');
      const meta = container.data('mwg-cf7-forms');
      const spinner = $(container).find('.pp-spinner');

      console.log(`Install for form ${formId}`);

      spinner.fadeIn();

      var request = {
        action: meta.action,
        nonce: meta.nonce,
        formId: formId,
      };

      console.log('Install');
      console.log(request);

      installButton.prop('disabled', true);

      $.post(ajaxurl, request)
        .done(function (response) {
          console.log(response);

          refreshCF7FormsTable(response.forms);
        })
        .always(function (response) {
          console.log('finished');
          // $(`button[data-form-id="${response.formId}"`).prop('disabled', false);
          spinner.fadeOut();
        });
    }
  });
})(jQuery);
