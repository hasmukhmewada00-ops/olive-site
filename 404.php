<?php
declare(strict_types=1);
require __DIR__ . '/app/bootstrap.php';
http_response_code(404);
$page_type = '404';
?><!doctype html>
<html lang="en-IN">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<?php require __DIR__ . '/partials/gtm-head.php'; ?>
<title>Page not found | Olive Catering Company</title>
<meta name="robots" content="noindex">
<link rel="stylesheet" href="<?= e(asset('assets/css/holding.css')) ?>">
</head>
<body>
<?php require __DIR__ . '/partials/gtm-body.php'; ?>
<main class="hold">
  <div class="hold__inner">
    <p class="hold__mark"><span class="hold__word">Olive</span><span class="hold__tag">The Catering Company</span></p>
    <span class="hold__rule" aria-hidden="true"></span>
    <h1 class="hold__headline">This page isn't on the menu.</h1>
    <p class="hold__sub">The page you're looking for doesn't exist or has moved.</p>
    <div class="hold__actions"><a class="btn btn--primary" href="/">Back to home</a></div>
  </div>
</main>
</body>
</html>
