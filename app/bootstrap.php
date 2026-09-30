<?php
declare(strict_types=1);

/**
 * Loaded first by every public PHP page.
 * Settings, safe defaults, security headers, staging gate, helpers.
 */

define('OLIVE_ROOT', dirname(__DIR__));
define('OLIVE_DATA', OLIVE_ROOT . '/data');

// ---------------------------------------------------------------
// Settings: config.php on the server, sample values as fallback
// ---------------------------------------------------------------
$__defaults = require OLIVE_ROOT . '/config.sample.php';
$__local = is_file(OLIVE_ROOT . '/config.php') ? require OLIVE_ROOT . '/config.php' : [];
$GLOBALS['OLIVE_CONFIG'] = array_replace_recursive($__defaults, is_array($__local) ? $__local : []);

// If config.php is missing, never assume we are live.
if (!$__local) {
    $GLOBALS['OLIVE_CONFIG']['site_live'] = false;
    $GLOBALS['OLIVE_CONFIG']['env'] = str_starts_with(strtolower($_SERVER['HTTP_HOST'] ?? ''), 'staging.') ? 'staging' : 'production';
}
unset($__defaults, $__local);

function cfg(string $key, mixed $default = null): mixed
{
    $value = $GLOBALS['OLIVE_CONFIG'];
    foreach (explode('.', $key) as $part) {
        if (!is_array($value) || !array_key_exists($part, $value)) {
            return $default;
        }
        $value = $value[$part];
    }
    return $value;
}

function is_staging(): bool
{
    return cfg('env') === 'staging' || str_starts_with(strtolower($_SERVER['HTTP_HOST'] ?? ''), 'staging.');
}

// ---------------------------------------------------------------
// Errors: log, never show on live
// ---------------------------------------------------------------
error_reporting(E_ALL);
ini_set('display_errors', is_staging() ? '1' : '0');
ini_set('log_errors', '1');
if (is_dir(OLIVE_DATA) && is_writable(OLIVE_DATA)) {
    ini_set('error_log', OLIVE_DATA . '/php-errors.log');
}
date_default_timezone_set('Asia/Kolkata');

// ---------------------------------------------------------------
// Security headers (PHP pages)
// ---------------------------------------------------------------
header('X-Content-Type-Options: nosniff');
header('Referrer-Policy: strict-origin-when-cross-origin');
if (is_staging()) {
    header('X-Robots-Tag: noindex, nofollow');
}

// ---------------------------------------------------------------
// Staging gate: username + password before anything renders.
// Done in PHP (not hPanel directory protection) so git deploys
// can never overwrite or drop it.
// ---------------------------------------------------------------
if (is_staging() && PHP_SAPI !== 'cli' && !defined('OLIVE_SKIP_GATE')) {
    $hash = (string) cfg('staging_pass_hash', '');
    $user = $_SERVER['PHP_AUTH_USER'] ?? '';
    $pass = $_SERVER['PHP_AUTH_PW'] ?? '';

    // Some LiteSpeed setups pass credentials only via HTTP_AUTHORIZATION
    if ($user === '' && !empty($_SERVER['HTTP_AUTHORIZATION']) && str_starts_with($_SERVER['HTTP_AUTHORIZATION'], 'Basic ')) {
        [$user, $pass] = array_pad(explode(':', (string) base64_decode(substr($_SERVER['HTTP_AUTHORIZATION'], 6)), 2), 2, '');
    }

    $ok = $hash !== ''
        && hash_equals((string) cfg('staging_user', ''), $user)
        && password_verify($pass, $hash);

    if (!$ok) {
        header('WWW-Authenticate: Basic realm="Olive staging", charset="UTF-8"');
        http_response_code(401);
        echo $hash === '' ? 'Staging is locked: set staging_pass_hash in config.php.' : 'Login required.';
        exit;
    }
}

// ---------------------------------------------------------------
// Content (editable via the CMS later)
// ---------------------------------------------------------------
function content(?string $key = null, mixed $default = null): mixed
{
    static $data = null;
    if ($data === null) {
        $file = is_file(OLIVE_DATA . '/content.json') ? OLIVE_DATA . '/content.json' : OLIVE_ROOT . '/content.sample.json';
        $data = json_decode((string) file_get_contents($file), true) ?: [];
    }
    if ($key === null) {
        return $data;
    }
    $value = $data;
    foreach (explode('.', $key) as $part) {
        if (!is_array($value) || !array_key_exists($part, $value)) {
            return $default;
        }
        $value = $value[$part];
    }
    return $value;
}

// ---------------------------------------------------------------
// Helpers
// ---------------------------------------------------------------
function e(mixed $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

/** Asset URL with a cache-busting version from the file's modified time. */
function asset(string $path): string
{
    $path = ltrim($path, '/');
    $file = OLIVE_ROOT . '/' . $path;
    $v = is_file($file) ? (string) filemtime($file) : '1';
    return '/' . $path . '?v=' . $v;
}

/** Digits only, with India code: "+91 98xx" -> "9198xx". */
function phone_digits(string $phone): string
{
    $d = preg_replace('/\D+/', '', $phone) ?? '';
    if (strlen($d) === 10) {
        $d = '91' . $d;
    }
    return $d;
}

function whatsapp_link(string $phone, string $message): string
{
    return 'https://wa.me/' . phone_digits($phone) . '?text=' . rawurlencode($message);
}

function canonical_url(string $path = '/'): string
{
    return rtrim((string) cfg('base_url'), '/') . $path;
}

/** Signed token proving the form was loaded here, and when. */
function form_token(): string
{
    $t = (string) time();
    return $t . '.' . hash_hmac('sha256', $t, form_secret());
}

function form_token_age(string $token): ?int
{
    [$t, $sig] = array_pad(explode('.', $token, 2), 2, '');
    if (!ctype_digit($t) || !hash_equals(hash_hmac('sha256', $t, form_secret()), $sig)) {
        return null;
    }
    return time() - (int) $t;
}

function form_secret(): string
{
    $s = (string) cfg('app_secret', '');
    return $s !== '' ? $s : hash('sha256', OLIVE_ROOT . php_uname());
}

function start_session(): void
{
    if (session_status() === PHP_SESSION_ACTIVE) {
        return;
    }
    session_name('olive_s');
    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'secure' => true,
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();
}
