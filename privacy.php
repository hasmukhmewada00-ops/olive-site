<?php
declare(strict_types=1);
require __DIR__ . '/app/bootstrap.php';
$page_type = 'privacy';
$b = content('business', []);
?><!doctype html>
<html lang="en-IN">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<?php require __DIR__ . '/partials/gtm-head.php'; ?>
<title>Privacy | Olive Catering Company</title>
<meta name="robots" content="noindex, follow">
<link rel="canonical" href="<?= e(canonical_url('/privacy.php')) ?>">
<link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@500&family=Manrope:wght@400;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="<?= e(asset('assets/css/main.css')) ?>">
</head>
<body>
<?php require __DIR__ . '/partials/gtm-body.php'; ?>
<main class="section">
  <div class="wrap" style="max-width:760px">
    <p class="eyebrow"><a href="/">Olive Catering Company</a></p>
    <h1 class="h2">Privacy</h1>
    <p class="lead">When you send an enquiry on this website, we collect your name, mobile number, event date, event type, approximate guest count and any message you write. We use these only to reply to your enquiry and plan your event.</p>
    <p class="lead">We also note how you found our website (for example Google or Instagram) so we know which of our listings help people find us. This site uses Google Analytics and similar tools that set cookies to measure visits.</p>
    <p class="lead">Your details are seen only by the Olive team and the people who manage this website for us. We do not sell or share them for anyone else's marketing.</p>
    <p class="lead">To see, correct or delete the details you sent us, email <a href="mailto:<?= e($b['email'] ?? '') ?>"><?= e($b['email'] ?? '') ?></a>.</p>
  </div>
</main>
</body>
</html>
