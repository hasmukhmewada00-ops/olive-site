<?php
/** Shared header. $base = '' on the home page, '/' elsewhere, so anchors work site-wide. */
$base = $base ?? '';
$b = content('business', []);
$name = (string) ($b['name'] ?? 'Olive Catering Company');
$phone = (string) ($b['phone'] ?? '');
$waMsg = (string) content('holding.whatsapp_message', 'Hi Olive, I would like to enquire about catering for my event. (via website)');
$announce = content('announcement', []);
?>
<body>
<?php require OLIVE_ROOT . '/partials/gtm-body.php'; ?>
<?php require OLIVE_ROOT . '/partials/loader.php'; ?>
<a class="skip" href="#main">Skip to content</a>

<?php if (is_staging()): ?>
<div class="stagebar">Staging preview &middot; not public &middot; items tagged <span class="tbc">to confirm</span> are hidden on the live site</div>
<?php endif; ?>

<?php if (!empty($announce['on']) && !empty($announce['text'])): ?>
<div class="announce"><?= e($announce['text']) ?></div>
<?php endif; ?>

<header class="top" id="top">
  <div class="wrap top__row">
    <a class="brand" href="<?= e($base) ?>#top" aria-label="<?= e($name) ?> home">
      <?= logo_svg('olive-wordmark', 'brand__logo') ?>
    </a>
    <nav class="nav" id="nav" aria-label="Main">
      <a href="<?= e($base) ?>#about">About</a>
      <a href="<?= e($base) ?>#services">Services</a>
      <a href="<?= e($base) ?>#menu">Menu</a>
      <a href="<?= e($base) ?>#gallery">Gallery</a>
      <a href="/blog/">Blog</a>
      <a href="<?= e($base) ?>#contact">Contact</a>
    </nav>
    <div class="top__cta" data-track-location="header">
      <a class="link-call" href="<?= e(tel_href()) ?>"><?= $phone !== '' ? e($phone) : 'Call us' ?></a>
      <a class="btn btn--cta btn--sm" href="<?= e(wa_href($waMsg)) ?>"<?= str_starts_with(wa_href($waMsg), 'http') ? ' target="_blank" rel="noopener"' : '' ?>>WhatsApp</a>
      <button class="burger" type="button" aria-expanded="false" aria-controls="nav" aria-label="Open menu"><span></span><span></span></button>
    </div>
  </div>
</header>
