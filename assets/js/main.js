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
