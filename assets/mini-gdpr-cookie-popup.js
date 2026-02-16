/**
 * Mini GDPR Cookie Popup
 */
document.addEventListener('DOMContentLoaded', function () {
  'use strict';

  if (typeof mgwcsData != 'undefined') {
    var hasConsented = false;

    // console.log(typeof window.localStorage);
    mgwcsData.blkon = mgwcsData.blkon == '1';
    mgwcsData.always = mgwcsData.always == '1';

    console.log(mgwcsData);

    if (typeof localStorage !== 'undefined') {
      var consentDate = Date.parse(localStorage.getItem(mgwcsData.cn));
      if (consentDate) {
        var now = new Date();
        var consentAge = Math.round((now - consentDate) / 1000.0);
        var maxAge = parseInt(mgwcsData.cd) * 86400; // 1 day === 86400 secs

        // console.log( `Consent age: ${consentAge}`);

        hasConsented = consentAge < maxAge;
      }
    } else {
      // console.log( 'ccc' );
      document.cookie.split(';').forEach(function (keyValuePair) {
        if (keyValuePair.split('=')[0].trim().startsWith(mgwcsData.cn)) {
          // console.log(`Found: ${keyValuePair}`);
          hasConsented = true;
        }
      });
    }

    if (hasConsented) {
      // console.log('User has consented');
      insertBlockedScripts();
    } else {
      // console.log('User has NOT consented');
      mgwShowPopup();
    }
  }
});

function mgwShowPopup() {
  var popupContainer = document.createElement('div');
  // popupContainer.innerHTML = `<p>${mgwcsData.msg}</p><div class="btn-box"><button class="accept" onclick="mgwConsentToScripts()">${mgwcsData.ok}</button><button class="more-info" onclick="mgwShowBlockedScripts()">${mgwcsData.mre}</button></div>`;
  popupContainer.innerHTML = `<p>${mgwcsData.msg}</p><div class="btn-box"><button class="accept">${mgwcsData.ok}</button><button class="more-info">${mgwcsData.mre}</button></div>`;
  popupContainer.id = 'mgwcsCntr';

  popupContainer.querySelector('button.accept').addEventListener('click', mgwConsentToScripts);
  popupContainer.querySelector('button.more-info').addEventListener('click', mgwShowBlockedScripts);

  // $(popupContainer).find('button.accept').on('click', mgwConsentToScripts);
  // $(popupContainer).find('button.more-info').on('click', mgwShowBlockedScripts);

  if (Array.isArray(mgwcsData.cls)) {
    popupContainer.className = mgwcsData.cls.join(' ');
  }

  document.body.appendChild(popupContainer);
}

function mgwConsentToScripts() {
  var container = document.getElementById('mgwcsCntr');
  if (container) {
    if (typeof localStorage !== 'undefined') {
      localStorage.setItem(mgwcsData.cn, new Date());
    } else {
      var expiresWhen = new Date();
      // console.log( `Duration: ${mgwcsData.cd}` );
      expiresWhen.setDate(expiresWhen.getDate() + parseInt(mgwcsData.cd));
      // console.log( `Expire: ${result.toUTCString()}` );

      document.cookie = `${mgwcsData.cn}=true; expires=${expiresWhen.toUTCString()}; Secure`;
    }

    insertBlockedScripts();

    container.className += ' mgw-fin';

    setTimeout(function () {
      document.getElementById('mgwcsCntr').remove();
    }, 500);
  }
}

function insertBlockedScripts() {
  // console.log( `mgwcsData.blkon: ${mgwcsData.blkon}` );

  if (mgwcsData.blkon) {
    console.log('Unblocking scripts');
    for (const scriptHandle in mgwcsData.meta) {
      // console.log(mgwcsData.meta[scriptHandle].src);
      console.log(`Defer ${scriptHandle} : ${mgwcsData.meta[scriptHandle]['can-defer']}`);

      if (mgwcsData.meta[scriptHandle]['can-defer']) {
        var scriptElement = document.createElement('script');
        scriptElement.setAttribute('src', mgwcsData.meta[scriptHandle].src);
        document.head.appendChild(scriptElement);

        if (mgwcsData.meta[scriptHandle].extra) {
          scriptElement = document.createElement('extra');
          scriptElement.appendChild(document.createTextNode(mgwcsData.meta[scriptHandle].after));
          document.head.appendChild(scriptElement);
        }

        if (mgwcsData.meta[scriptHandle].after) {
          scriptElement = document.createElement('script');
          scriptElement.appendChild(document.createTextNode(mgwcsData.meta[scriptHandle].after));
          document.head.appendChild(scriptElement);
        }
      }
    }
  }
}

function mgwShowBlockedScripts() {
  var overlayContainer = document.createElement('div');
  overlayContainer.id = 'mgwcsOvly';
  overlayContainer.className = 'mgw-ovl';
  overlayContainer.addEventListener('click', mgwCloseBlockedScripts);

  var overlayPanel = document.createElement('div');
  overlayPanel.className = 'mgw-nfo';
  overlayContainer.appendChild(overlayPanel);

  // console.log( mgwcsData.meta );
  // console.log(Object.keys(mgwcsData.meta).length);

  // if( mgwcsData.meta.length ) {
  if (Object.keys(mgwcsData.meta).length) {
    var blurb = document.createElement('p');
    blurb.innerHTML = mgwcsData.nfo1;

    if (mgwcsData.nfo3) {
      blurb.innerHTML += `<br />${mgwcsData.nfo3}`;
    }

    overlayPanel.appendChild(blurb);

    var scriptList = document.createElement('ul');

    // for( var foo = 0; foo < 20; ++foo ) {
    for (const scriptHandle in mgwcsData.meta) {
      var listItem = document.createElement('li');
      listItem.innerHTML = mgwcsData.meta[scriptHandle].description;
      scriptList.appendChild(listItem);
    }
    // }

    var wrapper = document.createElement('div');
    wrapper.className = 'plglst';
    wrapper.appendChild(scriptList);
    overlayPanel.appendChild(wrapper);
  } else {
    var blurb = document.createElement('p');
    blurb.innerHTML = mgwcsData.nfo2;
    overlayPanel.appendChild(blurb);
  }

  var closeButton = document.createElement('button');
  closeButton.innerText = 'Close';
  closeButton.addEventListener('DOMContentLoaded', mgwCloseBlockedScripts);

  overlayPanel.appendChild(closeButton);

  document.body.appendChild(overlayContainer);
}

function mgwCloseBlockedScripts() {
  var overlayContainer = document.getElementById('mgwcsOvly');
  if (overlayContainer) {
    overlayContainer.remove();
  }
}
