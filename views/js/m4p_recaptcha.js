/**
 * m4p_recaptcha
 *
 * @author    Modules4Presta <contact@modules4presta.io>
 * @copyright 2026 Nice Code sp. z o.o. (Modules4Presta)
 * @license   https://opensource.org/licenses/MIT MIT License
 */

(function () {
  'use strict';

  var config = window.m4pRecaptcha;
  if (!config || !config.siteKey) {
    return;
  }

  var loading = null;

  // api.js is ~150 kB of third-party script, so it waits until someone actually starts on a form.
  function load() {
    if (!loading) {
      loading = new Promise(function (resolve, reject) {
        var script = document.createElement('script');
        script.src = 'https://www.google.com/recaptcha/api.js?render=' + encodeURIComponent(config.siteKey);
        script.async = true;
        script.onload = function () {
          window.grecaptcha.ready(resolve);
        };
        script.onerror = reject;
        document.head.appendChild(script);
      });
    }

    return loading;
  }

  function tokenField(form) {
    return form.querySelector('input[name="' + config.field + '"]');
  }

  document.addEventListener('focusin', function (event) {
    var form = event.target.closest && event.target.closest('form');
    if (form && tokenField(form)) {
      load();
    }
  });

  // Tokens live two minutes and verify once, so each submit fetches a fresh one first. The capture
  // listener runs before the form's own handlers (including AJAX ones) and replays the submit.
  document.addEventListener('submit', function (event) {
    var form = event.target;
    var input = tokenField(form);
    if (!input) {
      return;
    }

    if (form.dataset.m4pRecaptchaReady) {
      delete form.dataset.m4pRecaptchaReady;
      return;
    }

    event.preventDefault();
    event.stopImmediatePropagation();
    // A second click while the token is on its way would send the form twice.
    if (form.dataset.m4pRecaptchaPending) {
      return;
    }
    form.dataset.m4pRecaptchaPending = '1';
    var submitter = event.submitter || null;

    load()
      .then(function () {
        return window.grecaptcha.execute(config.siteKey, { action: input.dataset.action });
      })
      .then(function (token) {
        input.value = token;
      }, function () {
        input.value = '';
      })
      .then(function () {
        delete form.dataset.m4pRecaptchaPending;
        form.dataset.m4pRecaptchaReady = '1';
        if (form.requestSubmit) {
          form.requestSubmit(submitter);
        } else {
          form.submit();
        }
      });
  }, true);
})();
