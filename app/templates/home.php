<?php
declare(strict_types=1);
/** Full one-page site. Included by index.php. */
require_once OLIVE_ROOT . '/app/render.php';
require_once OLIVE_ROOT . '/app/leads.php';

$page_type = 'home';
$b = content('business', []);
$hero = content('hero', []);
$name = (string) ($b['name'] ?? 'Olive Catering Company');
$waMsg = (string) content('holding.whatsapp_message', 'Hi Olive, I would like to enquire about catering for my event. (via website)');
$phone = (string) ($b['phone'] ?? '');

// Form state after a failed submit (only touch the session if one exists)
$form = ['errors' => [], 'old' => []];
if (isset($_COOKIE['olive_s'])) {
    start_session();
    $form = $_SESSION['olive_form'] ?? $form;
    unset($_SESSION['olive_form']);
}
$err = $form['errors'];
$old = $form['old'];
$val = fn (string $k) => e($old[$k] ?? '');

$title = 'Olive Catering Company | Pure Veg & Jain Caterers in Gandhidham, Kutch';
$desc = 'Pure veg and Jain caterers in Gandhidham and Adipur. Wedding, corporate and event catering across Kutch with live counters and trained staff. Since 2023.';
$gallery = array_values(array_filter(content('gallery', []), 'visible'));
$testimonials = array_values(array_filter(content('testimonials', []), 'visible'));
$associations = array_values(array_filter(content('associations', []), 'visible'));
$faqs = array_values(array_filter(content('faq', []), 'visible'));
$dishes = array_values(array_filter(content('signature_dishes', []), 'visible'));
$announce = content('announcement', []);
?><!doctype html>
<html lang="en-IN">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<?php require OLIVE_ROOT . '/partials/gtm-head.php'; ?>
<title><?= e($title) ?></title>
<meta name="description" content="<?= e($desc) ?>">
<?php if (is_staging()): ?><meta name="robots" content="noindex, nofollow"><?php endif; ?>
<link rel="canonical" href="<?= e(canonical_url('/')) ?>">
<meta property="og:type" content="website">
<meta property="og:site_name" content="<?= e($name) ?>">
<meta property="og:title" content="<?= e($title) ?>">
<meta property="og:description" content="<?= e($desc) ?>">
<meta property="og:url" content="<?= e(canonical_url('/')) ?>">
<meta property="og:image" content="<?= e(canonical_url('/assets/img/og-image.jpg')) ?>">
<meta property="og:locale" content="en_IN">
<meta name="twitter:card" content="summary_large_image">
<meta name="theme-color" content="#3A4733">
<link rel="icon" href="/assets/img/favicon.svg" type="image/svg+xml">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,500;0,600;1,500&family=Manrope:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="<?= e(asset('assets/css/main.css')) ?>">
<script type="application/ld+json"><?= schema_json() ?></script>
</head>
<body>
<?php require OLIVE_ROOT . '/partials/gtm-body.php'; ?>
<a class="skip" href="#main">Skip to content</a>

<?php if (is_staging()): ?>
<div class="stagebar">Staging preview &middot; not public &middot; items tagged <span class="tbc">to confirm</span> are hidden on the live site</div>
<?php endif; ?>

<?php if (!empty($announce['on']) && !empty($announce['text'])): ?>
<div class="announce"><?= e($announce['text']) ?></div>
<?php endif; ?>

<header class="top" id="top">
  <div class="wrap top__row">
    <a class="brand" href="#top" aria-label="<?= e($name) ?> home">
      <?= logo_svg('olive-wordmark', 'brand__logo') ?>
    </a>
    <nav class="nav" id="nav" aria-label="Main">
      <a href="#about">About</a>
      <a href="#services">Services</a>
      <a href="#menu">Menu</a>
      <a href="#gallery">Gallery</a>
      <a href="#contact">Contact</a>
    </nav>
    <div class="top__cta" data-track-location="header">
      <a class="link-call" href="<?= e(tel_href()) ?>"><?= $phone !== '' ? e($phone) : 'Call us' ?></a>
      <a class="btn btn--cta btn--sm" href="<?= e(wa_href($waMsg)) ?>"<?= str_starts_with(wa_href($waMsg), 'http') ? ' target="_blank" rel="noopener"' : '' ?>>WhatsApp</a>
      <button class="burger" type="button" aria-expanded="false" aria-controls="nav" aria-label="Open menu"><span></span><span></span></button>
    </div>
  </div>
</header>

<main id="main">

  <!-- Hero -->
  <section class="hero">
    <div class="wrap hero__grid">
      <div class="hero__text">
        <p class="eyebrow"><?= e($hero['eyebrow'] ?? '') ?></p>
        <h1 class="hero__title"><?= e($hero['headline'] ?? '') ?></h1>
        <p class="hero__sub"><?= e($hero['subline'] ?? '') ?></p>
        <div class="hero__actions" data-track-location="hero">
          <a class="btn btn--cta" href="#enquiry">Get a quote</a>
          <a class="btn btn--line" href="<?= e(wa_href($waMsg)) ?>"<?= str_starts_with(wa_href($waMsg), 'http') ? ' target="_blank" rel="noopener"' : '' ?>>WhatsApp us</a>
        </div>
      </div>
      <figure class="hero__media">
        <?= picture($hero['image'] ?? [], 'cloche cloche--hero', '(min-width: 900px) 42vw, 90vw', true, 1200, 1500) ?>
        <figcaption class="hero__seal" aria-hidden="true"><span>100%</span>Pure veg</figcaption>
      </figure>
    </div>
  </section>

  <!-- Trust -->
  <section class="trust" aria-label="Olive in numbers">
    <ul class="wrap trust__list">
      <?php foreach (content('trust', []) as $t): ?>
        <li><strong><?= e($t['value']) ?></strong><span><?= e($t['label']) ?></span></li>
      <?php endforeach; ?>
    </ul>
  </section>

  <!-- About -->
  <section class="section about" id="about">
    <div class="wrap about__grid">
      <?= picture(content('about.image', []), 'cloche cloche--about', '(min-width: 900px) 30vw, 80vw', false, 900, 1100) ?>
      <div>
        <p class="eyebrow">About Olive</p>
        <h2 class="h2"><?= e(content('about.heading', '')) ?></h2>
        <?php foreach (content('about.body', []) as $para): ?>
          <p class="lead"><?= e($para) ?></p>
        <?php endforeach; ?>
      </div>
    </div>
  </section>

  <!-- Services -->
  <section class="section services" id="services">
    <div class="wrap">
      <header class="section__head">
        <p class="eyebrow">What we cater</p>
        <h2 class="h2">Catering for every occasion in Kutch</h2>
      </header>
      <ul class="services__grid">
        <?php foreach (content('services', []) as $s): ?>
          <li class="svc">
            <h3 class="svc__title"><?= e($s['title']) ?></h3>
            <p><?= e($s['text']) ?></p>
          </li>
        <?php endforeach; ?>
      </ul>
    </div>
  </section>

  <!-- Menu card (signature section) -->
  <section class="menu" id="menu">
    <div class="wrap">
      <div class="menucard">
        <p class="menucard__top">Olive Catering Company</p>
        <h2 class="menucard__title">Our menus &amp; specialties</h2>
        <span class="menucard__rule" aria-hidden="true"></span>
        <ul class="menucard__list">
          <?php foreach (content('specialties', []) as $sp): ?>
            <li>
              <h3><?= e($sp['title']) ?></h3>
              <p><?= e($sp['text']) ?></p>
            </li>
          <?php endforeach; ?>
        </ul>
        <?php if ($dishes): ?>
          <div class="menucard__dishes">
            <p class="menucard__label">Signature dishes</p>
            <p><?php foreach ($dishes as $i => $d): ?><?= $i ? ' &middot; ' : '' ?><?= e($d['name']) ?><?= tbc($d) ?><?php endforeach; ?></p>
          </div>
        <?php endif; ?>
        <a class="btn btn--cta" href="#enquiry" data-track-location="menu">Plan my menu</a>
      </div>
    </div>
  </section>

  <!-- Hygiene -->
  <section class="section hygiene">
    <div class="wrap hygiene__grid">
      <div>
        <p class="eyebrow">Hygiene &amp; quality</p>
        <h2 class="h2"><?= e(content('hygiene.heading', '')) ?></h2>
        <?php if (!empty($b['fssai'])): ?>
          <p class="fssai">FSSAI Lic. No. <strong><?= e($b['fssai']) ?></strong></p>
        <?php endif; ?>
      </div>
      <ul class="checks">
        <?php foreach (content('hygiene.points', []) as $pt): ?>
          <li><?= e($pt) ?></li>
        <?php endforeach; ?>
      </ul>
    </div>
  </section>

  <!-- Gallery -->
  <section class="section gallery" id="gallery">
    <div class="wrap">
      <header class="section__head">
        <p class="eyebrow">Gallery</p>
        <h2 class="h2">From our kitchen to your celebration</h2>
      </header>
      <ul class="gallery__grid">
        <?php foreach ($gallery as $i => $g): ?>
          <li>
            <?php if (!empty($g['src'])): ?>
              <a href="<?= e(preg_replace('/-\d+\.webp$/', '-1600.webp', (string) $g['src'])) ?>" class="gallery__link" data-lightbox aria-label="Open photo: <?= e($g['alt']) ?>">
                <?= picture($g, 'gallery__img', '(min-width: 900px) 25vw, 50vw', false, 960, 720) ?>
              </a>
            <?php else: ?>
              <?= picture($g, 'gallery__img', '50vw') ?>
            <?php endif; ?>
          </li>
        <?php endforeach; ?>
      </ul>
    </div>
  </section>

  <?php if ($testimonials || $associations): ?>
  <!-- Words from clients -->
  <section class="section words">
    <div class="wrap">
      <header class="section__head">
        <p class="eyebrow">Kind words</p>
        <h2 class="h2">What our clients say</h2>
      </header>
      <?php if ($testimonials): ?>
        <ul class="quotes">
          <?php foreach ($testimonials as $t): ?>
            <li class="quote">
              <blockquote><p><?= e($t['quote']) ?></p></blockquote>
              <p class="quote__by"><?= e($t['name']) ?><span><?= e($t['event']) ?></span><?= tbc($t) ?></p>
            </li>
          <?php endforeach; ?>
        </ul>
      <?php endif; ?>
      <?php if ($associations): ?>
        <div class="assoc">
          <p class="menucard__label">Trusted by</p>
          <ul>
            <?php foreach ($associations as $as): ?>
              <li><?= e($as['name']) ?><?php if (!empty($as['note'])): ?><span><?= e($as['note']) ?></span><?php endif; ?><?= tbc($as) ?></li>
            <?php endforeach; ?>
          </ul>
        </div>
      <?php endif; ?>
    </div>
  </section>
  <?php endif; ?>

  <!-- FAQ -->
  <?php if ($faqs): ?>
  <section class="section faq">
    <div class="wrap faq__grid">
      <header>
        <p class="eyebrow">Questions</p>
        <h2 class="h2">Good to know before you book</h2>
      </header>
      <div class="faq__list">
        <?php foreach ($faqs as $f): ?>
          <details>
            <summary><?= e($f['q']) ?><?= tbc($f) ?></summary>
            <p><?= e($f['a']) ?></p>
          </details>
        <?php endforeach; ?>
      </div>
    </div>
  </section>
  <?php endif; ?>

  <!-- Enquiry + contact -->
  <section class="section contact" id="contact">
    <div class="wrap contact__grid">
      <div class="enquiry" id="enquiry">
        <p class="eyebrow">Get a quote</p>
        <h2 class="h2">Tell us about your event</h2>
        <p class="muted">Share a few details and we will call you back with menu ideas and a quote.</p>

        <?php if (!empty($err['form'])): ?>
          <p class="form-alert" role="alert"><?= e($err['form']) ?></p>
        <?php elseif ($err): ?>
          <p class="form-alert" role="alert">Please check the highlighted fields.</p>
        <?php endif; ?>

        <form class="form" id="enquiry-form" action="/enquiry.php" method="post" novalidate data-olive-form>
          <input type="hidden" name="form_token" value="<?= e(form_token()) ?>">
          <div class="hp" aria-hidden="true">
            <label>Leave this empty <input type="text" name="company_website" tabindex="-1" autocomplete="off"></label>
          </div>

          <div class="field<?= isset($err['name']) ? ' is-bad' : '' ?>">
            <label for="f-name">Your name</label>
            <input id="f-name" name="name" type="text" autocomplete="name" required maxlength="80" value="<?= $val('name') ?>">
            <?php if (isset($err['name'])): ?><p class="field__err"><?= e($err['name']) ?></p><?php endif; ?>
          </div>

          <div class="field<?= isset($err['phone']) ? ' is-bad' : '' ?>">
            <label for="f-phone">Mobile number</label>
            <input id="f-phone" name="phone" type="tel" inputmode="tel" autocomplete="tel" required maxlength="20" placeholder="10-digit mobile" value="<?= $val('phone') ?>">
            <?php if (isset($err['phone'])): ?><p class="field__err"><?= e($err['phone']) ?></p><?php endif; ?>
          </div>

          <div class="field<?= isset($err['event_type']) ? ' is-bad' : '' ?>">
            <label for="f-type">Type of event</label>
            <select id="f-type" name="event_type" required>
              <option value="">Choose one</option>
              <?php foreach (OLIVE_EVENT_TYPES as $t): ?>
                <option<?= ($old['eventType'] ?? '') === $t ? ' selected' : '' ?>><?= e($t) ?></option>
              <?php endforeach; ?>
            </select>
            <?php if (isset($err['event_type'])): ?><p class="field__err"><?= e($err['event_type']) ?></p><?php endif; ?>
          </div>

          <div class="field-row">
            <div class="field<?= isset($err['event_date']) ? ' is-bad' : '' ?>">
              <label for="f-date">Event date <span class="opt">optional</span></label>
              <input id="f-date" name="event_date" type="date" min="<?= e(date('Y-m-d')) ?>" value="<?= e($old['eventDate'] ?? '') ?>">
              <?php if (isset($err['event_date'])): ?><p class="field__err"><?= e($err['event_date']) ?></p><?php endif; ?>
            </div>
            <div class="field<?= isset($err['guests']) ? ' is-bad' : '' ?>">
              <label for="f-guests">Approx. guests <span class="opt">optional</span></label>
              <input id="f-guests" name="guests" type="number" inputmode="numeric" min="1" max="20000" value="<?= e($old['guestsRaw'] ?? '') ?>">
              <?php if (isset($err['guests'])): ?><p class="field__err"><?= e($err['guests']) ?></p><?php endif; ?>
            </div>
          </div>

          <div class="field">
            <label for="f-msg">Anything else? <span class="opt">optional</span></label>
            <textarea id="f-msg" name="message" rows="3" maxlength="1000" placeholder="Jain menu, live counters, venue, timings..."><?= $val('message') ?></textarea>
          </div>

          <button class="btn btn--cta btn--block" type="submit">Send enquiry</button>
          <p class="form-note">By sending this you agree that Olive may contact you about your enquiry. <a href="/privacy.php">Privacy</a></p>
        </form>
      </div>

      <aside class="reach" data-track-location="contact">
        <h2 class="reach__title">Reach us directly</h2>
        <ul class="reach__list">
          <li><span>Call</span><a href="<?= e(tel_href()) ?>"><?= $phone !== '' ? e($phone) : 'Number coming soon' ?></a></li>
          <li><span>WhatsApp</span><a href="<?= e(wa_href($waMsg)) ?>"<?= str_starts_with(wa_href($waMsg), 'http') ? ' target="_blank" rel="noopener"' : '' ?>>Chat with us</a></li>
          <?php if (!empty($b['email'])): ?><li><span>Email</span><a href="mailto:<?= e($b['email']) ?>"><?= e($b['email']) ?></a></li><?php endif; ?>
          <li><span>Office</span><address><?= e(full_address()) ?></address></li>
          <?php if (!empty($b['hours'])): ?><li><span>Hours</span><?= e($b['hours']) ?></li><?php endif; ?>
        </ul>
        <?php $mq = rawurlencode((string) ($b['map_query'] ?? full_address())); ?>
        <div class="map">
          <iframe title="Map to Olive Catering Company, Adipur" src="https://www.google.com/maps?q=<?= e($mq) ?>&amp;output=embed" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
        </div>
        <a class="link-map" data-track-map href="https://www.google.com/maps/search/?api=1&amp;query=<?= e($mq) ?>" target="_blank" rel="noopener">Get directions</a>
      </aside>
    </div>
  </section>
</main>

<footer class="foot">
  <div class="wrap foot__grid">
    <div>
      <p class="foot__logo"><?= logo_svg('olive-logo') ?></p>
      <p class="foot__text"><?= e(content('footer.text', '')) ?></p>
    </div>
    <nav aria-label="Footer">
      <a href="#about">About</a><a href="#services">Services</a><a href="#menu">Menu</a><a href="#gallery">Gallery</a><a href="#contact">Contact</a>
    </nav>
    <div class="foot__meta" data-track-location="footer">
      <?php if (!empty($b['instagram'])): ?><a href="https://www.instagram.com/<?= e($b['instagram']) ?>/" target="_blank" rel="noopener">Instagram</a><?php endif; ?>
      <?php if (!empty($b['fssai'])): ?><p>FSSAI Lic. No. <?= e($b['fssai']) ?></p><?php endif; ?>
      <p>Serving <?= e(implode(', ', $b['area_served'] ?? [])) ?></p>
    </div>
  </div>
  <div class="wrap foot__base">
    <p>&copy; <?= date('Y') ?> <?= e($name) ?>. All rights reserved.</p>
    <?php if (content('footer.credit') === true): ?><p>Website by One Man Marketing</p><?php endif; ?>
  </div>
</footer>

<a class="wa-float" href="<?= e(wa_href($waMsg)) ?>"<?= str_starts_with(wa_href($waMsg), 'http') ? ' target="_blank" rel="noopener"' : '' ?> data-track-location="float" aria-label="Chat on WhatsApp">
  <svg viewBox="0 0 24 24" width="26" height="26" aria-hidden="true"><path fill="currentColor" d="M12 2a10 10 0 0 0-8.6 15.1L2 22l5-1.3A10 10 0 1 0 12 2Zm0 18.2a8.2 8.2 0 0 1-4.2-1.2l-.3-.2-3 .8.8-2.9-.2-.3A8.2 8.2 0 1 1 12 20.2Zm4.5-6.1c-.2-.1-1.5-.7-1.7-.8-.2-.1-.4-.1-.6.1l-.8 1c-.1.2-.3.2-.5.1a6.7 6.7 0 0 1-3.3-2.9c-.3-.4.2-.4.7-1.3.1-.2 0-.3 0-.4l-.8-1.8c-.2-.5-.4-.4-.6-.4h-.5a1 1 0 0 0-.7.3 3 3 0 0 0-.9 2.2 5.2 5.2 0 0 0 1.1 2.8 11.9 11.9 0 0 0 4.6 4c1.7.7 2.4.8 3.2.7.5-.1 1.5-.6 1.8-1.2.2-.6.2-1.1.1-1.2l-.4-.2Z"/></svg>
</a>

<dialog class="lightbox" id="lightbox" aria-label="Photo">
  <button class="lightbox__close" type="button" aria-label="Close photo">&times;</button>
  <img alt="">
</dialog>

<script src="<?= e(asset('assets/js/attribution.js')) ?>" defer></script>
<script src="<?= e(asset('assets/js/main.js')) ?>" defer></script>
</body>
</html>
