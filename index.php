<?php
declare(strict_types=1);
require __DIR__ . '/app/bootstrap.php';

// Full site template arrives in the build phase. Until site_live is true,
// every visitor sees the holding page, which is kept out of Google.
if (cfg('site_live') === true && is_file(__DIR__ . '/app/templates/home.php')) {
    require __DIR__ . '/app/templates/home.php';
    exit;
}

$page_type = 'holding';
$b = content('business', []);
$h = content('holding', []);
$phone = (string) ($b['phone'] ?? '');
$wa = (string) ($b['whatsapp'] ?? '') ?: $phone;
$email = (string) ($b['email'] ?? '');
$insta = (string) ($b['instagram'] ?? '');
$name = (string) ($b['name'] ?? 'Olive Catering Company');
?><!doctype html>
<html lang="en-IN">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<?php require __DIR__ . '/partials/gtm-head.php'; ?>
<title><?= e($name) ?> | Pure Veg &amp; Jain Caterers, Kutch</title>
<meta name="robots" content="noindex, follow">
<meta name="description" content="<?= e($name) ?>: pure veg and Jain catering for weddings, corporate events and celebrations across Gandhidham, Adipur and Kutch. New website coming soon.">
<link rel="canonical" href="<?= e(canonical_url('/')) ?>">
<meta name="theme-color" content="#3A4733">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@500;600&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="<?= e(asset('assets/css/holding.css')) ?>">
</head>
<body>
<?php require __DIR__ . '/partials/gtm-body.php'; ?>
<main class="hold">
  <div class="hold__inner">
    <p class="hold__mark" aria-label="<?= e($name) ?>">
      <span class="hold__word">Olive</span>
      <span class="hold__tag">The Catering Company</span>
    </p>

    <span class="hold__rule" aria-hidden="true"></span>

    <h1 class="hold__headline"><?= e($h['headline'] ?? '') ?></h1>
    <p class="hold__sub"><?= e($h['subline'] ?? '') ?></p>

    <div class="hold__actions" data-track-location="holding">
      <?php if ($wa !== ''): ?>
        <a class="btn btn--primary" href="<?= e(whatsapp_link($wa, (string) ($h['whatsapp_message'] ?? 'Hi Olive'))) ?>" target="_blank" rel="noopener">WhatsApp us</a>
      <?php endif; ?>
      <?php if ($phone !== ''): ?>
        <a class="btn btn--ghost" href="tel:+<?= e(phone_digits($phone)) ?>">Call <?= e($phone) ?></a>
      <?php endif; ?>
      <?php if ($email !== ''): ?>
        <a class="btn btn--ghost" href="mailto:<?= e($email) ?>"><?= e($email) ?></a>
      <?php endif; ?>
    </div>

    <?php if ($insta !== ''): ?>
      <p class="hold__social" data-track-location="holding">
        <a href="https://www.instagram.com/<?= e($insta) ?>/" target="_blank" rel="noopener">Instagram @<?= e($insta) ?></a>
      </p>
    <?php endif; ?>

    <p class="hold__place">Adipur &middot; Gandhidham &middot; Kutch, Gujarat</p>
  </div>
</main>
<script src="<?= e(asset('assets/js/attribution.js')) ?>" defer></script>
</body>
</html>
