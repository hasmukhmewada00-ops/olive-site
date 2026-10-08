/* Olive admin: article editor helpers (SEO checklist, Google preview, tag buttons, auto web address) */
(function () {
  'use strict';
  var form = document.querySelector('[data-article-form]');
  if (!form) return;

  var f = function (n) { return form.querySelector('[data-field="' + n + '"]'); };
  var val = function (n) { var el = f(n); return el ? el.value.trim() : ''; };
  var hasCover = !!form.querySelector('.cover');
  var slugEl = f('slug');
  var slugTouched = !!slugEl.value;

  function slugify(s) {
    s = s.toLowerCase().replace(/[^a-z0-9]+/g, '-').replace(/^-+|-+$/g, '');
    if (s.length > 60) { s = s.slice(0, 60); var i = s.lastIndexOf('-'); if (i > 20) s = s.slice(0, i); }
    return s.replace(/-+$/, '');
  }
  if (!slugEl.readOnly) {
    slugEl.addEventListener('input', function () { slugTouched = true; });
    f('title').addEventListener('input', function () { if (!slugTouched) { slugEl.value = slugify(val('title')); render(); } });
  }

  // Click an existing tag to add it
  form.querySelectorAll('[data-add-tag]').forEach(function (b) {
    b.addEventListener('click', function () {
      var t = f('tags'), cur = t.value.split(',').map(function (x) { return x.trim(); }).filter(Boolean);
      var tag = b.getAttribute('data-add-tag');
      if (cur.map(function (x) { return x.toLowerCase(); }).indexOf(tag.toLowerCase()) < 0 && cur.length < 6) cur.push(tag);
      t.value = cur.join(', ');
      render();
    });
  });

  function words(s) { var m = s.trim().match(/\S+/g); return m ? m.length : 0; }
  function firstPara(s) { return (s.split(/\n\s*\n/)[0] || '').toLowerCase(); }

  function render() {
    var title = val('title'), desc = val('description'), kw = val('keyword').toLowerCase();
    var body = val('body'), seo = val('seo_title'), slug = val('slug');
    var tags = val('tags').split(',').map(function (x) { return x.trim(); }).filter(Boolean);
    var wc = words(body);
    var photo = f('photo');
    var coverOk = (photo && photo.files && photo.files.length) || hasCover;
    var altLen = val('alt').length;
    var h2 = (body.match(/^##\s+\S/gm) || []).length;
    var links = (body.match(/\]\(\/[^/)]/g) || []).length;
    var faqs = 0;
    form.querySelectorAll('[data-faq]').forEach(function (q) { if (q.value.trim()) faqs++; });

    form.querySelector('[data-words]').textContent = wc + ' words' + (wc < 300 ? ' (300 needed to publish)' : wc < 600 ? ' (600 or more is best)' : '');

    // Google preview
    var shownTitle = seo || (title ? title + ' | Olive Catering Company' : 'Your article title');
    form.querySelector('[data-serp-url]').textContent = form.getAttribute('data-host').replace(/^https?:\/\//, '').replace(/\/$/, '') + ' › blog › ' + (slug || 'web-address');
    form.querySelector('[data-serp-title]').textContent = shownTitle.length > 60 ? shownTitle.slice(0, 58) + '…' : shownTitle;
    form.querySelector('[data-serp-desc]').textContent = desc ? (desc.length > 160 ? desc.slice(0, 158) + '…' : desc) : 'Your search description will appear here.';

    var checks = [
      [title.length >= 30 && title.length <= 65, 'Title is 30 to 65 characters (now ' + title.length + ')', true],
      [desc.length >= 120 && desc.length <= 160, 'Search description is 120 to 160 characters (now ' + desc.length + ')', desc.length >= 70 && desc.length <= 160],
      [kw !== '', 'Main keyword is filled in', false],
      [kw !== '' && (title.toLowerCase().indexOf(kw) >= 0 || seo.toLowerCase().indexOf(kw) >= 0), 'Main keyword is in the title', false],
      [kw !== '' && desc.toLowerCase().indexOf(kw) >= 0, 'Main keyword is in the search description', false],
      [kw !== '' && firstPara(body).indexOf(kw) >= 0, 'Main keyword is in the first paragraph', false],
      [wc >= 600, 'Article has 600 words or more (now ' + wc + ')', wc >= 300],
      [h2 >= 2, 'At least two ## headings (now ' + h2 + ')', false],
      [links >= 1, 'At least one link to our own pages, like (/#weddings)', false],
      [!!coverOk && altLen >= 10, 'Cover photo with a description', !!coverOk && altLen >= 10],
      [tags.length >= 1 && tags.length <= 6, 'Between 1 and 6 tags (now ' + tags.length + ')', tags.length >= 1],
      [faqs >= 2, 'Two or more questions and answers (now ' + faqs + ')', false]
    ];
    var ul = form.querySelector('[data-checks]');
    ul.innerHTML = '';
    checks.forEach(function (c) {
      var li = document.createElement('li');
      li.className = c[0] ? 'ok' : (c[2] ? 'warn' : 'todo');
      li.textContent = (c[0] ? '✓ ' : '○ ') + c[1];
      ul.appendChild(li);
    });
  }

  form.addEventListener('input', render);
  form.addEventListener('change', render);
  render();
})();
