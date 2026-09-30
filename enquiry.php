<?php
declare(strict_types=1);
require __DIR__ . '/app/bootstrap.php';
require __DIR__ . '/app/leads.php';

/**
 * Enquiry form handler.
 * Order: spam checks -> validate -> save CSV -> email -> Sheet -> CAPI -> thank-you.
 * generate_lead fires ONLY on the thank-you page, after this has succeeded.
 */

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
    header('Location: /', true, 303);
    exit;
}

start_session();

$back = function (array $errors, array $old): never {
    $_SESSION['olive_form'] = ['errors' => $errors, 'old' => $old];
    header('Location: /#enquiry', true, 303);
    exit;
};
$silentDrop = function (): never {
    // Bots get a normal-looking thank-you page but nothing is saved or tracked.
    header('Location: /thank-you.php', true, 303);
    exit;
};

// ---------------- spam checks ----------------
if (trim((string) ($_POST['company_website'] ?? '')) !== '') {
    $silentDrop();                                   // honeypot filled
}
$age = form_token_age((string) ($_POST['form_token'] ?? ''));
if ($age === null || $age < 3 || $age > 86400) {
    $silentDrop();                                   // no token, too fast, or stale
}
if (rate_limited('enquiry', 5, 600)) {
    $back(['form' => 'Too many enquiries from this connection. Please WhatsApp or call us instead.'], []);
}

// ---------------- validate ----------------
$str = fn (string $k, int $max) => mb_substr(trim(preg_replace('/\s+/u', ' ', (string) ($_POST[$k] ?? '')) ?? ''), 0, $max);

$name = $str('name', 80);
$phoneRaw = $str('phone', 20);
$eventDate = $str('event_date', 10);
$eventType = $str('event_type', 40);
$guestsRaw = $str('guests', 6);
$message = mb_substr(trim((string) ($_POST['message'] ?? '')), 0, 1000);

$old = compact('name', 'eventDate', 'eventType', 'guestsRaw', 'message') + ['phone' => $phoneRaw];
$errors = [];

if (mb_strlen($name) < 2) {
    $errors['name'] = 'Please enter your name.';
}

$digits = preg_replace('/\D+/', '', $phoneRaw) ?? '';
if (strlen($digits) === 12 && str_starts_with($digits, '91')) {
    $digits = substr($digits, 2);
} elseif (strlen($digits) === 11 && str_starts_with($digits, '0')) {
    $digits = substr($digits, 1);
}
if (!preg_match('/^[6-9]\d{9}$/', $digits)) {
    $errors['phone'] = 'Please enter a valid 10-digit mobile number.';
}

if ($eventDate !== '') {
    $d = DateTimeImmutable::createFromFormat('!Y-m-d', $eventDate);
    if (!$d || $d->format('Y-m-d') !== $eventDate) {
        $errors['event_date'] = 'Please pick a valid date.';
    } elseif ($d < new DateTimeImmutable('today')) {
        $errors['event_date'] = 'The event date is in the past.';
    }
}

if (!in_array($eventType, OLIVE_EVENT_TYPES, true)) {
    $errors['event_type'] = 'Please choose the type of event.';
}

$guests = 0;
if ($guestsRaw !== '') {
    $guests = (int) preg_replace('/\D+/', '', $guestsRaw);
    if ($guests < 1 || $guests > 20000) {
        $errors['guests'] = 'Please enter an approximate number of guests.';
    }
}

if ($errors) {
    $back($errors, $old);
}

// ---------------- build the lead ----------------
$lead = [
    'lead_id' => new_lead_id(),
    'submitted_at_ist' => date('Y-m-d H:i:s'),
    'name' => $name,
    'phone' => substr($digits, 0, 5) . ' ' . substr($digits, 5),   // "98765 43210": reads as text in Sheets/Excel
    'event_date' => $eventDate,
    'event_type' => $eventType,
    'guests' => $guests ?: '',
    'guests_band' => guests_band($guests),
    'message' => $message,
    'device' => device_type((string) ($_SERVER['HTTP_USER_AGENT'] ?? '')),
    'form_source' => in_array($_POST['form_source'] ?? '', ['main', 'popup'], true) ? $_POST['form_source'] : 'main',
    'status' => 'New',
];
foreach (OLIVE_ATTRIBUTION_FIELDS as $f) {
    $lead[$f] = mb_substr(preg_replace('/[\x00-\x1F<>"\'`]/u', '', (string) ($_POST[$f] ?? '')) ?? '', 0, 250);
}

// ---------------- deliver ----------------
$saved = save_lead_csv($lead);
$mailed = send_lead_email($lead);
send_lead_sheet($lead);
send_meta_capi($lead);

if (!$saved && !$mailed) {
    // Nothing reached the business: ask the visitor to use WhatsApp instead.
    error_log('Olive: lead NOT stored anywhere: ' . json_encode($lead));
    $back(['form' => 'Sorry, something went wrong on our side. Please WhatsApp or call us and we will help right away.'], $old);
}

// One-time payload for the thank-you page (fires generate_lead once)
$_SESSION['olive_lead'] = [
    'lead_id' => $lead['lead_id'],
    'event_type' => $lead['event_type'],
    'guests_band' => $lead['guests_band'],
    'form_source' => $lead['form_source'],
    'lt_source' => $lead['lt_source'],
    'lt_medium' => $lead['lt_medium'],
    'lt_campaign' => $lead['lt_campaign'],
];
session_regenerate_id(true);

header('Location: /thank-you.php', true, 303);
exit;
