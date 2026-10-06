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
$__defaults = is_file(OLIVE_ROOT . '/config.sample.php') ? require OLIVE_ROOT . '/config.sample.php' : [];
$__defaults = is_array($__defaults) ? $__defaults : [];
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
/**
 * Read Basic-auth username/password from wherever this server puts them.
 * LiteSpeed/Apache/CGI setups differ, so check every known location.
 */
function olive_basic_auth(): array
{
    if (isset($_SERVER['PHP_AUTH_USER']) && $_SERVER['PHP_AUTH_USER'] !== '') {
        return [trim((string) $_SERVER['PHP_AUTH_USER']), (string) ($_SERVER['PHP_AUTH_PW'] ?? '')];
    }
    $header = $_SERVER['HTTP_AUTHORIZATION']
        ?? $_SERVER['REDIRECT_HTTP_AUTHORIZATION']
        ?? $_SERVER['REDIRECT_REDIRECT_HTTP_AUTHORIZATION']
        ?? '';
    if ($header === '' && function_exists('getallheaders')) {
        foreach ((array) getallheaders() as $k => $v) {
            if (strcasecmp((string) $k, 'Authorization') === 0) {
                $header = (string) $v;
                break;
            }
        }
    }
    if (stripos($header, 'Basic ') !== 0) {
        return ['', ''];
    }
    $decoded = (string) base64_decode(trim(substr($header, 6)), true);
    [$u, $p] = array_pad(explode(':', $decoded, 2), 2, '');
    return [trim($u), $p];
}

if (is_staging() && PHP_SAPI !== 'cli' && !defined('OLIVE_SKIP_GATE')) {
    $hash = trim((string) cfg('staging_pass_hash', ''));
    [$user, $pass] = olive_basic_auth();

    $ok = $hash !== ''
        && hash_equals(trim((string) cfg('staging_user', '')), $user)
        && password_verify($pass, $hash);

    if (!$ok) {
        header('WWW-Authenticate: Basic realm="Olive staging", charset="UTF-8"');
        header('Cache-Control: no-store');
        http_response_code(401);
        header('Content-Type: text/plain; charset=utf-8');
        if ($hash === '') {
            echo "Staging is locked (code A): config.php is missing, or staging_pass_hash is empty.";
        } elseif ($user === '' && $pass === '') {
            echo "Login required (code B). If you already typed the password, the server did not pass it to PHP.";
        } elseif (!hash_equals(trim((string) cfg('staging_user', '')), $user)) {
            echo "Login failed (code C): wrong username.";
        } else {
            echo "Login failed (code D): wrong password, or the hash in config.php was changed while pasting.";
        }
        exit;
    }
}

// ---------------------------------------------------------------
// Content: base copy from content.sample.json (in git), with the
// client's admin-panel edits from data/cms.json applied on top.
// ---------------------------------------------------------------
define('OLIVE_CMS_FILE', OLIVE_DATA . '/cms.json');

function content(?string $key = null, mixed $default = null): mixed
{
    static $data = null;
    if ($data === null) {
        $data = json_decode((string) file_get_contents(OLIVE_ROOT . '/content.sample.json'), true) ?: [];
        $data = cms_apply($data, cms_overrides());
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

/** The client's saved edits (data/cms.json), or [] if none yet. */
function cms_overrides(): array
{
    if (!is_file(OLIVE_CMS_FILE)) {
        return [];
    }
    $ov = json_decode((string) file_get_contents(OLIVE_CMS_FILE), true);
    return is_array($ov) ? $ov : [];
}

/**
 * Apply admin edits to the base content. Only the editable slots are
 * touched; headings, SEO copy, FAQ and layout always come from code.
 */
function cms_apply(array $d, array $ov): array
{
    if (isset($ov['business']) && is_array($ov['business'])) {
        $d['business'] = array_replace_recursive($d['business'] ?? [], $ov['business']);
    }
    foreach (['announcement', 'popup'] as $k) {
        if (isset($ov[$k]) && is_array($ov[$k])) {
            $d[$k] = array_replace($d[$k] ?? [], $ov[$k]);
        }
    }
    foreach (['trust', 'associations', 'testimonials', 'gallery'] as $k) {
        if (isset($ov[$k]) && is_array($ov[$k])) {
            $d[$k] = array_values($ov[$k]);
        }
    }
    if (isset($ov['areas']['list']) && is_array($ov['areas']['list'])) {
        $d['areas']['list'] = array_map(fn ($n) => ['name' => (string) $n], $ov['areas']['list']);
    }
    if (isset($ov['areas']['region'])) {
        $d['areas']['region'] = (string) $ov['areas']['region'];
    }
    if (isset($ov['dishes']) && is_array($ov['dishes'])) {
        $d['signature_dishes'] = array_map(fn ($n) => ['name' => (string) $n], $ov['dishes']);
    }
    if (isset($ov['footer_text'])) {
        $d['footer']['text'] = (string) $ov['footer_text'];
    }
    foreach (($ov['images'] ?? []) as $slot => $img) {
        if (!is_array($img) || empty($img['src'])) {
            continue;
        }
        [$type, $ref] = array_pad(explode(':', (string) $slot, 2), 2, '');
        if ($type === 'hero') {
            $d['hero']['image'] = $img;
        } elseif ($type === 'about') {
            $d['about']['image'] = $img;
        } elseif ($type === 'feature') {
            foreach ($d['features'] ?? [] as $i => $f) {
                if (($f['id'] ?? '') === $ref) {
                    $d['features'][$i]['image'] = $img;
                }
            }
        } elseif ($type === 'hygiene' && isset($d['hygiene']['steps'][(int) $ref])) {
            $d['hygiene']['steps'][(int) $ref]['image'] = $img;
        }
    }
    return $d;
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

/** Inline an SVG logo from assets/img/logo (trusted files in the repo). */
function logo_svg(string $name, string $class = ''): string
{
    $file = OLIVE_ROOT . '/assets/img/logo/' . basename($name) . '.svg';
    if (!is_file($file)) {
        return '';
    }
    $svg = trim((string) file_get_contents($file));
    return $class !== '' ? preg_replace('/^<svg /', '<svg class="' . e($class) . '" ', $svg, 1) : $svg;
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
