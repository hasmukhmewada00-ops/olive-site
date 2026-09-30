<?php
declare(strict_types=1);

/**
 * Lead handling: save locally first (never lose a lead), then email,
 * Google Sheet and (optionally) Meta Conversions API.
 */

require_once __DIR__ . '/lib/PHPMailer/Exception.php';
require_once __DIR__ . '/lib/PHPMailer/PHPMailer.php';
require_once __DIR__ . '/lib/PHPMailer/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;

const OLIVE_EVENT_TYPES = [
    'Wedding',
    'NRI / Destination wedding',
    'Corporate event',
    'Birthday / Social gathering',
    'Festival / Religious function',
    'Canteen / Institutional',
    'Other',
];

/** Attribution fields accepted from the hidden form inputs (filled by attribution.js). */
const OLIVE_ATTRIBUTION_FIELDS = [
    'ft_source', 'ft_medium', 'ft_campaign',
    'lt_source', 'lt_medium', 'lt_campaign', 'lt_term', 'lt_content', 'lt_utm_id',
    'gclid', 'gbraid', 'wbraid', 'fbclid',
    'referrer', 'landing_page', 'first_seen_at',
    'fbp', 'fbc', 'ga_client_id', 'page_url',
];

/** Column order for the CSV backup and the Google Sheet. */
const OLIVE_LEAD_COLUMNS = [
    'lead_id', 'submitted_at_ist', 'name', 'phone', 'event_date', 'event_type', 'guests', 'guests_band', 'message',
    'ft_source', 'ft_medium', 'ft_campaign',
    'lt_source', 'lt_medium', 'lt_campaign', 'lt_term', 'lt_content',
    'gclid', 'fbclid', 'referrer', 'landing_page', 'page_url', 'form_source', 'device', 'ga_client_id', 'status',
];

function new_lead_id(): string
{
    return 'OLV-' . date('Ymd') . '-' . strtoupper(bin2hex(random_bytes(2)));
}

function guests_band(int $n): string
{
    return match (true) {
        $n <= 0 => '',
        $n < 50 => 'under_50',
        $n <= 100 => '50_100',
        $n <= 250 => '101_250',
        $n <= 500 => '251_500',
        $n <= 1000 => '501_1000',
        default => '1000_plus',
    };
}

function device_type(string $ua): string
{
    if (preg_match('/iPad|Tablet/i', $ua)) {
        return 'tablet';
    }
    return preg_match('/Mobi|Android|iPhone/i', $ua) ? 'mobile' : 'desktop';
}

function client_ip(): string
{
    return (string) ($_SERVER['REMOTE_ADDR'] ?? '');
}

/** Very small file-based limit: max $max submissions per $window seconds per IP. */
function rate_limited(string $bucket, int $max = 5, int $window = 600): bool
{
    $dir = OLIVE_DATA . '/ratelimit';
    if (!is_dir($dir) && !@mkdir($dir, 0750, true)) {
        return false;
    }
    $file = $dir . '/' . hash('sha256', $bucket . client_ip()) . '.json';
    $now = time();
    $hits = is_file($file) ? (json_decode((string) file_get_contents($file), true) ?: []) : [];
    $hits = array_values(array_filter($hits, fn ($t) => is_int($t) && $t > $now - $window));
    if (count($hits) >= $max) {
        return true;
    }
    $hits[] = $now;
    file_put_contents($file, json_encode($hits), LOCK_EX);
    return false;
}

/** Append the lead to data/leads/YYYY-MM.csv. Returns false only if the disk write failed. */
function save_lead_csv(array $lead): bool
{
    $dir = OLIVE_DATA . '/leads';
    if (!is_dir($dir) && !@mkdir($dir, 0750, true)) {
        error_log('Olive: cannot create leads folder');
        return false;
    }
    $file = $dir . '/' . date('Y-m') . '.csv';
    $new = !is_file($file);
    $fh = fopen($file, 'ab');
    if (!$fh) {
        return false;
    }
    flock($fh, LOCK_EX);
    if ($new) {
        fputcsv($fh, OLIVE_LEAD_COLUMNS, ',', '"', '');
    }
    $row = [];
    foreach (OLIVE_LEAD_COLUMNS as $col) {
        // Prefix cells that spreadsheet apps would treat as formulas
        $v = (string) ($lead[$col] ?? '');
        $row[] = preg_match('/^[=+\-@]/', $v) ? "'" . $v : $v;
    }
    $ok = fputcsv($fh, $row, ',', '"', '') !== false;
    flock($fh, LOCK_UN);
    fclose($fh);
    return $ok;
}

function send_lead_email(array $lead): bool
{
    $smtp = cfg('smtp', []);
    $to = array_filter((array) cfg('lead_recipients', []));
    if (empty($smtp['pass']) || !$to) {
        error_log('Olive: SMTP not configured, email skipped for ' . $lead['lead_id']);
        return false;
    }

    $rows = [
        'Name' => $lead['name'],
        'Phone' => $lead['phone'],
        'Event date' => $lead['event_date'] ?: '-',
        'Event type' => $lead['event_type'],
        'Approx. guests' => $lead['guests'] ?: '-',
        'Message' => $lead['message'] ?: '-',
        'Came from' => trim($lead['lt_source'] . ' / ' . $lead['lt_medium'] . ($lead['lt_campaign'] ? ' / ' . $lead['lt_campaign'] : ''), ' /'),
        'First visit from' => trim($lead['ft_source'] . ' / ' . $lead['ft_medium'], ' /'),
        'Received' => $lead['submitted_at_ist'],
        'Lead ID' => $lead['lead_id'],
    ];

    $html = '<table cellpadding="6" style="font-family:Arial,sans-serif;font-size:14px;border-collapse:collapse">';
    $text = '';
    foreach ($rows as $k => $v) {
        $html .= '<tr><td style="color:#555;border-bottom:1px solid #eee"><b>' . e($k) . '</b></td><td style="border-bottom:1px solid #eee">' . nl2br(e($v)) . '</td></tr>';
        $text .= $k . ': ' . $v . "\n";
    }
    $html .= '</table>';

    $wa = 'https://wa.me/' . phone_digits($lead['phone']);
    $html .= '<p style="font-family:Arial,sans-serif"><a href="' . e($wa) . '">Reply on WhatsApp</a> &middot; <a href="tel:+' . e(phone_digits($lead['phone'])) . '">Call</a></p>';

    try {
        $m = new PHPMailer(true);
        $m->isSMTP();
        $m->Host = (string) $smtp['host'];
        $m->Port = (int) $smtp['port'];
        $m->SMTPAuth = true;
        $m->Username = (string) $smtp['user'];
        $m->Password = (string) $smtp['pass'];
        $m->SMTPSecure = ($smtp['secure'] ?? 'ssl') === 'tls' ? PHPMailer::ENCRYPTION_STARTTLS : PHPMailer::ENCRYPTION_SMTPS;
        $m->CharSet = 'UTF-8';
        $m->Timeout = 10;
        $m->setFrom((string) $smtp['from_email'], (string) ($smtp['from_name'] ?? 'Olive Website'));
        foreach ($to as $addr) {
            $m->addAddress((string) $addr);
        }
        $m->Subject = 'New enquiry: ' . $lead['event_type'] . ($lead['event_date'] ? ' on ' . $lead['event_date'] : '') . ' - ' . $lead['name'];
        $m->isHTML(true);
        $m->Body = $html;
        $m->AltBody = $text . "\nWhatsApp: " . $wa;
        $m->send();
        return true;
    } catch (\Throwable $ex) {
        error_log('Olive: email failed for ' . $lead['lead_id'] . ': ' . $ex->getMessage());
        return false;
    }
}

function send_lead_sheet(array $lead): bool
{
    $url = (string) cfg('sheet_webhook_url', '');
    if ($url === '' || !function_exists('curl_init')) {
        return false;
    }
    $payload = ['secret' => (string) cfg('sheet_webhook_secret', ''), 'columns' => OLIVE_LEAD_COLUMNS, 'lead' => array_intersect_key($lead, array_flip(OLIVE_LEAD_COLUMNS))];
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => json_encode($payload, JSON_UNESCAPED_UNICODE),
        CURLOPT_HTTPHEADER => ['Content-Type: text/plain;charset=utf-8'],
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_TIMEOUT => 8,
    ]);
    $body = curl_exec($ch);
    $code = (int) curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
    curl_close($ch);
    if ($code !== 200 || !str_contains((string) $body, '"ok":true')) {
        error_log('Olive: sheet failed for ' . $lead['lead_id'] . ' (HTTP ' . $code . ')');
        return false;
    }
    return true;
}

/** Server-side Meta Lead event. Same event_id as the browser Pixel, so Meta de-duplicates. */
function send_meta_capi(array $lead): bool
{
    if (cfg('capi_enabled') !== true || !cfg('meta_dataset_id') || !cfg('meta_access_token') || !function_exists('curl_init')) {
        return false;
    }
    $userData = array_filter([
        'ph' => [hash('sha256', phone_digits($lead['phone']))],
        'fn' => [hash('sha256', mb_strtolower(trim(explode(' ', $lead['name'])[0])))],
        'country' => [hash('sha256', 'in')],
        'client_ip_address' => client_ip(),
        'client_user_agent' => (string) ($_SERVER['HTTP_USER_AGENT'] ?? ''),
        'fbp' => $lead['fbp'] ?: null,
        'fbc' => $lead['fbc'] ?: ($lead['fbclid'] ? 'fb.1.' . (time() * 1000) . '.' . $lead['fbclid'] : null),
    ]);
    $body = [
        'data' => [[
            'event_name' => 'Lead',
            'event_time' => time(),
            'event_id' => $lead['lead_id'],
            'action_source' => 'website',
            'event_source_url' => $lead['page_url'] ?: canonical_url('/'),
            'user_data' => $userData,
            'custom_data' => ['content_category' => $lead['event_type'], 'guests_band' => $lead['guests_band']],
        ]],
    ];
    if (cfg('meta_test_event_code')) {
        $body['test_event_code'] = (string) cfg('meta_test_event_code');
    }
    $url = 'https://graph.facebook.com/' . cfg('meta_api_version', 'v23.0') . '/' . rawurlencode((string) cfg('meta_dataset_id')) . '/events?access_token=' . rawurlencode((string) cfg('meta_access_token'));
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => json_encode($body),
        CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 8,
    ]);
    curl_exec($ch);
    $code = (int) curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
    curl_close($ch);
    if ($code !== 200) {
        error_log('Olive: CAPI failed for ' . $lead['lead_id'] . ' (HTTP ' . $code . ')');
        return false;
    }
    return true;
}
