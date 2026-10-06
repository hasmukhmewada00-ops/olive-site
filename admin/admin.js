/* Olive admin: file preview info, character counts, confirm dialogs, busy buttons */
(function () {
  'use strict';

  // Show the chosen photo's size; warn early when it is too big.
  document.querySelectorAll('[data-upload] input[type=file]').forEach(function (input) {
    input.addEventListener('change', function () {
      var info = input.closest('form').querySelector('.file__info');
      var f = input.files && input.files[0];
      if (!info) return;
      if (!f) { info.hidden = true; return; }
      var mb = (f.size / 1048576).toFixed(1);
      info.hidden = false;
      var max = parseInt(input.closest('form').getAttribute('data-max'), 10) || 12 * 1048576;
      info.textContent = f.size > max
        ? 'This photo is ' + mb + ' MB. Please use one under ' + Math.floor(max / 1048576) + ' MB.'
        : f.name + ' (' + mb + ' MB). It will be optimised automatically.';
    });
  });

  // Live character count under limited fields.
  document.querySelectorAll('[data-count]').forEach(function (el) {
    var max = parseInt(el.getAttribute('maxlength'), 10);
    if (!max) return;
    var out = document.createElement('span');
    out.className = 'charcount';
    el.insertAdjacentElement('afterend', out);
    var upd = function () { out.textContent = el.value.length + ' / ' + max; };
    el.addEventListener('input', upd);
    upd();
  });

  document.querySelectorAll('form').forEach(function (form) {
    form.addEventListener('submit', function (e) {
      var msg = form.getAttribute('data-confirm');
      if (msg && !window.confirm(msg)) { e.preventDefault(); return; }
      var btn = form.querySelector('button[type=submit]');
      if (btn) {
        btn.disabled = true;
        btn.textContent = form.hasAttribute('data-upload') ? 'Uploading, please wait...' : 'Saving...';
      }
    });
  });
})();
