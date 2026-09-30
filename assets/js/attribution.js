/*!
 * Olive attribution + event tracking
 * - Captures UTM / click IDs into first-touch and last-touch cookies (90 days)
 * - Fills hidden fields on forms marked data-olive-form
 * - Pushes clean dataLayer events for WhatsApp, call, email, map clicks,
 *   form_start and menu_view (GTM maps them to GA4 / Meta)
 * No personal data is stored in cookies; only campaign parameters.
 */
(function () {
  'use strict';

  var DAYS = 90;
  var PARAMS = ['utm_source', 'utm_medium', 'utm_campaign', 'utm_term', 'utm_content', 'utm_id', 'gclid', 'gbraid', 'wbraid', 'fbclid'];
  var dl = (window.dataLayer = window.dataLayer || []);

  // ---------- cookie helpers ----------
  function setCookie(name, value, days) {
    var d = new Date();
    d.setTime(d.getTime() + days * 864e5);
    document.cookie = name + '=' + encodeURIComponent(value) + '; expires=' + d.toUTCString() + '; path=/; SameSite=Lax; Secure';
  }
  function getCookie(name) {
    var m = document.cookie.match('(?:^|; )' + name.replace(/[.$?*|{}()[\]\\/+^]/g, '\\$&') + '=([^;]*)');
    return m ? decodeURIComponent(m[1]) : '';
  }
  function readJSON(name) {
    try { return JSON.parse(getCookie(name) || 'null'); } catch (e) { return null; }
  }
  function clean(v) {
    return String(v || '').replace(/[<>"'`]/g, '').slice(0, 150);
  }

  // ---------- read this visit ----------
  var qs = new URLSearchParams(window.location.search);
  var visit = {};
  var hasCampaign = false;
  PARAMS.forEach(function (p) {
    var v = qs.get(p);
    if (v) { visit[p] = clean(v); hasCampaign = true; }
  });

  // Derive a source when there are no UTMs
  if (!hasCampaign) {
    var ref = document.referrer;
    var refHost = '';
    try { refHost = ref ? new URL(ref).hostname.replace(/^www\./, '') : ''; } catch (e) { refHost = ''; }
    var ownHost = window.location.hostname.replace(/^www\./, '');
    if (!refHost || refHost === ownHost) {
      visit.utm_source = '(direct)';
      visit.utm_medium = '(none)';
    } else if (/(^|\.)google\./.test(refHost) || /(^|\.)bing\.com$/.test(refHost) || /duckduckgo\.com$/.test(refHost) || /search\.yahoo\.com$/.test(refHost)) {
      visit.utm_source = refHost.split('.')[0] === 'search' ? 'yahoo' : refHost.replace(/\..*$/, '');
      visit.utm_medium = 'organic';
    } else if (/(facebook|instagram|l\.facebook|lm\.facebook|l\.instagram)\./.test(refHost)) {
      visit.utm_source = /instagram/.test(refHost) ? 'instagram' : 'facebook';
      visit.utm_medium = 'social';
    } else {
      visit.utm_source = refHost;
      visit.utm_medium = 'referral';
    }
  }
  visit.referrer = clean(document.referrer);
  visit.landing_page = clean(window.location.pathname + window.location.search).slice(0, 150);
  visit.seen_at = new Date().toISOString();

  // ---------- first touch (never overwritten) ----------
  var ft = readJSON('olv_ft');
  if (!ft) {
    ft = visit;
    setCookie('olv_ft', JSON.stringify(ft), DAYS);
  }

  // ---------- last touch (overwritten by campaign visits or new referrers) ----------
  var lt = readJSON('olv_lt');
  var isInternalNav = document.referrer && document.referrer.indexOf(window.location.hostname) !== -1;
  if (!lt || hasCampaign || (!isInternalNav && visit.utm_medium !== '(none)')) {
    lt = visit;
    setCookie('olv_lt', JSON.stringify(lt), DAYS);
  }

  // ---------- internal traffic flag (visit any page with ?omm_internal=1 once) ----------
  if (qs.get('omm_internal') === '1') setCookie('olv_internal', '1', 365);
  if (qs.get('omm_internal') === '0') setCookie('olv_internal', '', -1);
  if (getCookie('olv_internal') === '1') {
    dl.push({ traffic_type: 'internal' });
  }

  // ---------- helpers exposed for the form ----------
  function gaClientId() {
    var ga = getCookie('_ga'); // GA1.1.123456789.1700000000
    var parts = ga.split('.');
    return parts.length >= 4 ? parts.slice(-2).join('.') : '';
  }

  function attributionFields() {
    return {
      ft_source: ft.utm_source || '', ft_medium: ft.utm_medium || '', ft_campaign: ft.utm_campaign || '',
      lt_source: lt.utm_source || '', lt_medium: lt.utm_medium || '', lt_campaign: lt.utm_campaign || '',
      lt_term: lt.utm_term || '', lt_content: lt.utm_content || '', lt_utm_id: lt.utm_id || '',
      gclid: lt.gclid || ft.gclid || '', gbraid: lt.gbraid || '', wbraid: lt.wbraid || '',
      fbclid: lt.fbclid || ft.fbclid || '',
      referrer: ft.referrer || '', landing_page: ft.landing_page || '', first_seen_at: ft.seen_at || '',
      fbp: getCookie('_fbp'), fbc: getCookie('_fbc'), ga_client_id: gaClientId(),
      page_url: window.location.href.slice(0, 250)
    };
  }
  window.OliveAttribution = { fields: attributionFields };

  function fillForm(form) {
    var data = attributionFields();
    Object.keys(data).forEach(function (k) {
      var input = form.querySelector('input[name="' + k + '"]');
      if (!input) {
        input = document.createElement('input');
        input.type = 'hidden';
        input.name = k;
        form.appendChild(input);
      }
      input.value = data[k];
    });
  }

  // ---------- events ----------
  function where(el) {
    var box = el.closest('[data-track-location]');
    return box ? box.getAttribute('data-track-location') : 'unknown';
  }

  document.addEventListener('click', function (ev) {
    var a = ev.target.closest && ev.target.closest('a[href]');
    if (!a) return;
    var href = a.getAttribute('href') || '';
    var evt = null;
    if (/^https:\/\/(wa\.me|api\.whatsapp\.com)\//.test(href)) evt = 'whatsapp_click';
    else if (/^tel:/.test(href)) evt = 'call_click';
    else if (/^mailto:/.test(href)) evt = 'email_click';
    else if (a.hasAttribute('data-track-map') || /google\.[a-z.]+\/maps|maps\.app\.goo\.gl|goo\.gl\/maps/.test(href)) evt = 'map_click';
    if (evt) dl.push({ event: evt, click_location: where(a) });
  }, true);

  function wireForms() {
    var forms = document.querySelectorAll('form[data-olive-form]');
    Array.prototype.forEach.call(forms, function (form) {
      fillForm(form);
      var started = false;
      form.addEventListener('focusin', function () {
        if (started) return;
        started = true;
        dl.push({ event: 'form_start', form_id: form.id || 'enquiry' });
      });
      // refresh values right before submit (GA / Meta cookies may arrive late)
      form.addEventListener('submit', function () { fillForm(form); });
    });
  }

  function watchMenu() {
    var menu = document.getElementById('menu');
    if (!menu || !('IntersectionObserver' in window)) return;
    var io = new IntersectionObserver(function (entries) {
      entries.forEach(function (en) {
        if (en.isIntersecting) {
          dl.push({ event: 'menu_view' });
          io.disconnect();
        }
      });
    }, { threshold: 0.5 });
    io.observe(menu);
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', function () { wireForms(); watchMenu(); });
  } else {
    wireForms(); watchMenu();
  }
})();
