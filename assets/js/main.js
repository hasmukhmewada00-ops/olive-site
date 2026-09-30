/* Olive: small page behaviours (menu, header state, gallery lightbox) */
(function () {
  'use strict';

  // Mobile menu
  var burger = document.querySelector('.burger');
  var nav = document.getElementById('nav');
  if (burger && nav) {
    burger.addEventListener('click', function () {
      var open = burger.getAttribute('aria-expanded') === 'true';
      burger.setAttribute('aria-expanded', String(!open));
      burger.setAttribute('aria-label', open ? 'Open menu' : 'Close menu');
      nav.classList.toggle('is-open', !open);
    });
    nav.addEventListener('click', function (e) {
      if (e.target.closest('a')) {
        burger.setAttribute('aria-expanded', 'false');
        burger.setAttribute('aria-label', 'Open menu');
        nav.classList.remove('is-open');
      }
    });
  }

  // Header border once the page scrolls
  var top = document.querySelector('.top');
  if (top) {
    var onScroll = function () { top.classList.toggle('is-scrolled', window.scrollY > 8); };
    onScroll();
    window.addEventListener('scroll', onScroll, { passive: true });
  }

  // Gallery lightbox
  var box = document.getElementById('lightbox');
  if (box && typeof box.showModal === 'function') {
    var img = box.querySelector('img');
    document.addEventListener('click', function (e) {
      var link = e.target.closest('[data-lightbox]');
      if (!link) return;
      e.preventDefault();
      var thumb = link.querySelector('img');
      img.src = link.getAttribute('href');
      img.alt = thumb ? thumb.alt : '';
      box.showModal();
    });
    box.addEventListener('click', function (e) {
      if (e.target === box || e.target.closest('.lightbox__close')) box.close();
    });
  }

  // Scroll to the form if the server sent us back with errors
  if (document.querySelector('.form-alert')) {
    var f = document.getElementById('enquiry');
    if (f) f.scrollIntoView();
  }
})();

/* Remove the loader from the page once it has faded out */
(function () {
  var l = document.getElementById('loader');
  if (!l) return;
  l.addEventListener('animationend', function (e) {
    if (e.animationName === 'olvOut') { l.remove(); document.documentElement.classList.remove('show-loader'); }
  });
})();

/* Mirror-work glint: each abhla catches the light once as it scrolls into view */
(function () {
  var mirrors = document.querySelectorAll('.abhla');
  if (!mirrors.length || !('IntersectionObserver' in window)) return;
  var io = new IntersectionObserver(function (entries) {
    entries.forEach(function (en, i) {
      if (!en.isIntersecting) return;
      var el = en.target;
      setTimeout(function () { el.classList.add('is-lit'); }, (i % 12) * 70);
      io.unobserve(el);
    });
  }, { threshold: 0.6 });
  Array.prototype.forEach.call(mirrors, function (m) { io.observe(m); });
})();

/* Hygiene story: the photo on the left follows the step being read */
(function () {
  var steps = document.querySelectorAll('.story__step');
  var frames = document.querySelectorAll('.story__frame');
  if (!steps.length || !('IntersectionObserver' in window)) return;
  function activate(i) {
    Array.prototype.forEach.call(steps, function (s) { s.classList.toggle('is-active', s.getAttribute('data-step') === String(i)); });
    Array.prototype.forEach.call(frames, function (f) { f.classList.toggle('is-active', f.getAttribute('data-frame') === String(i)); });
  }
  var io = new IntersectionObserver(function (entries) {
    entries.forEach(function (en) { if (en.isIntersecting) activate(en.target.getAttribute('data-step')); });
  }, { rootMargin: '-45% 0px -45% 0px' });
  Array.prototype.forEach.call(steps, function (s) { io.observe(s); });
})();

/* Quick-enquiry pop-up: once per session, after the delay, never after an enquiry */
(function () {
  var dlg = document.getElementById('quick-enquiry');
  if (!dlg || typeof dlg.showModal !== 'function') return;
  var dl = (window.dataLayer = window.dataLayer || []);
  var delay = (parseInt(dlg.getAttribute('data-delay'), 10) || 20) * 1000;
  var engaged = false;

  function seen() { try { return sessionStorage.getItem('olv_qe') === '1'; } catch (e) { return false; } }
  function markSeen() { try { sessionStorage.setItem('olv_qe', '1'); } catch (e) {} }
  if (/(?:^|; )olv_lead=1/.test(document.cookie) || seen()) return;

  var mainForm = document.getElementById('enquiry-form');
  if (mainForm) mainForm.addEventListener('focusin', function () { engaged = true; });

  function formInView() {
    var box = document.getElementById('enquiry');
    if (!box) return false;
    var r = box.getBoundingClientRect();
    return r.top < window.innerHeight && r.bottom > 0;
  }

  function tryOpen() {
    if (engaged || seen() || document.querySelector('dialog[open]') || document.documentElement.classList.contains('show-loader')) return;
    if (document.hidden || formInView()) { setTimeout(tryOpen, 5000); return; }
    markSeen();
    dlg.showModal();
    dl.push({ event: 'popup_view', form_id: 'popup' });
  }
  setTimeout(tryOpen, delay);

  function close() { if (dlg.open) { dlg.close(); dl.push({ event: 'popup_close', form_id: 'popup' }); } }
  dlg.querySelector('.qe__close').addEventListener('click', close);
  dlg.addEventListener('click', function (e) { if (e.target === dlg) close(); });
  dlg.addEventListener('cancel', function () { dl.push({ event: 'popup_close', form_id: 'popup' }); });
})();
