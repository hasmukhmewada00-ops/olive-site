<?php
declare(strict_types=1);
require __DIR__ . '/app/bootstrap.php';
start_session();

// Read the one-time lead payload, then clear it so a refresh never re-fires the event.
$lead = $_SESSION['olive_lead'] ?? null;
unset($_SESSION['olive_lead']);

$page_type = 'thank_you';
$b = content('business', []);
$wa = (string) (($b['whatsapp'] ?? '') ?: ($b['phone'] ?? ''));
$ref = $lead['lead_id'] ?? '';
$waText = 'Hi Olive, I just sent an enquiry on your website.' . ($ref ? ' Ref ' . $ref . '.' : '') . ' (via website)';
?><!doctype html>
<html lang="en-IN">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<?php require __DIR__ . '/partials/gtm-head.php'; ?>
<?php if ($lead): ?>
<script>
window.dataLayer.push(<?= json_encode([
    'event' => 'generate_lead',
    'lead_id' => $lead['lead_id'],
    'event_type' => $lead['event_type'],
    'guests_band' => $lead['guests_band'],
    'lead_source' => $lead['lt_source'],
    'lead_medium' => $lead['lt_medium'],
    'lead_campaign' => $lead['lt_campaign'],
], JSON_UNESCAPED_SLASHES | JSON_HEX_TAG) ?>);
</script>
<?php endif; ?>
<title>Thank you | Olive Catering Company</title>
<meta name="robots" content="noindex, nofollow">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@500;600&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="<?= e(asset('assets/css/holding.css')) ?>">
</head>
<body>
<?php require __DIR__ . '/partials/gtm-body.php'; ?>
<main class="hold">
  <div class="hold__inner">
    <p class="hold__mark"><?= logo_svg('olive-logo') ?></p>
    <span class="hold__rule" aria-hidden="true"></span>
    <h1 class="hold__headline">Thank you, we've received your enquiry.</h1>
    <p class="hold__sub">Our team will call you shortly. For a faster reply, send us a message on WhatsApp.</p>
    <div class="hold__actions" data-track-location="thank_you">
      <?php if ($wa !== ''): ?>
        <a class="btn btn--primary" href="<?= e(whatsapp_link($wa, $waText)) ?>" target="_blank" rel="noopener">Message us on WhatsApp</a>
      <?php endif; ?>
      <a class="btn btn--ghost" href="/">Back to home</a>
    </div>
    <?php if ($ref): ?><p class="hold__place">Reference: <?= e($ref) ?></p><?php endif; ?>
  </div>
</main>
<script src="<?= e(asset('assets/js/attribution.js')) ?>" defer></script>
</body>
</html>
