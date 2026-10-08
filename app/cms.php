<?php
declare(strict_types=1);

/**
 * Admin panel library: login, CSRF, saving with backups, and image processing.
 * Loaded only by files in /admin/.
 */

const CMS_BACKUP_KEEP = 20;
const CMS_MAX_UPLOAD_BYTES = 12 * 1024 * 1024;   // phone photos are often 4 to 10 MB
const CMS_MIN_WIDTH = 800;                       // smaller photos look blurry on the site
const CMS_MAX_PIXELS = 50_000_000;               // protects server memory
const CMS_GALLERY_MAX = 30;
const CMS_SIZES = [480, 960, 1600];

/** Byte budgets per output width; quality steps down until the file fits. */
const CMS_BUDGET = [480 => 60_000, 960 => 160_000, 1600 => 320_000];
const CMS_BUDGET_HERO_1600 = 250_000;
const CMS_QUALITIES = [80, 74, 68, 62, 56, 50];

/** Every photo position on the page that the admin can change. */
function cms_image_slots(): array
{
    $slots = [
        'hero' => ['label' => 'Main banner (top of page)', 'shape' => 'Tall, 4:5', 'current' => content('hero.image', [])],
        'about' => ['label' => 'About Olive', 'shape' => 'Tall, 4:5', 'current' => content('about.image', [])],
    ];
    foreach (content('features', []) as $f) {
        $slots['feature:' . $f['id']] = ['label' => 'Section: ' . ($f['eyebrow'] ?? $f['id']), 'shape' => 'Wide, 4:3', 'current' => $f['image'] ?? []];
    }
    foreach (content('hygiene.steps', []) as $i => $st) {
        $slots['hygiene:' . $i] = ['label' => 'Hygiene step ' . ($i + 1) . ': ' . ($st['title'] ?? ''), 'shape' => 'Tall, 4:5', 'current' => $st['image'] ?? []];
    }
    return $slots;
}

// ---------------------------------------------------------------
// Session, login, CSRF
// ---------------------------------------------------------------
function admin_https(): bool
{
    return (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        || strtolower((string) ($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '')) === 'https';
}

function admin_session(): void
{
    if (session_status() === PHP_SESSION_ACTIVE) {
        return;
    }
    session_name('olive_admin');
    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/admin/',
        'secure' => admin_https(),
        'httponly' => true,
        'samesite' => 'Strict',
    ]);
    session_start();
}

function admin_headers(): void
{
    header('Cache-Control: no-store, max-age=0');
    header('X-Frame-Options: DENY');
    header('X-Robots-Tag: noindex, nofollow');
    header("Content-Security-Policy: default-src 'self'; img-src 'self' data: blob:; style-src 'self'; script-src 'self'; frame-ancestors 'none'; form-action 'self'");
}

function admin_logged_in(): bool
{
    admin_session();
    $last = (int) ($_SESSION['admin_last'] ?? 0);
    if (empty($_SESSION['admin_user']) || time() - $last > 1800) {
        return false;
    }
    $_SESSION['admin_last'] = time();
    return true;
}

function admin_require(): void
{
    admin_headers();
    if (!admin_logged_in()) {
        $_SESSION = [];
        header('Location: /admin/login.php');
        exit;
    }
}

function csrf_token(): string
{
    admin_session();
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf'];
}

function csrf_field(): string
{
    return '<input type="hidden" name="csrf" value="' . e(csrf_token()) . '">';
}

function csrf_check(): void
{
    admin_session();
    $sent = (string) ($_POST['csrf'] ?? '');
    if ($_SERVER['REQUEST_METHOD'] !== 'POST' || empty($_SESSION['csrf']) || !hash_equals($_SESSION['csrf'], $sent)) {
        http_response_code(400);
        exit('This form expired. Go back, reload the page and try again.');
    }
}

/** Login rate limit: 5 failed attempts per IP in 15 minutes. */
function admin_throttle_file(): string
{
    return OLIVE_DATA . '/admin-throttle.json';
}

function admin_ip(): string
{
    return (string) ($_SERVER['REMOTE_ADDR'] ?? '0');
}

function admin_locked_out(): bool
{
    $all = json_decode((string) @file_get_contents(admin_throttle_file()), true) ?: [];
    $mine = array_filter($all[admin_ip()] ?? [], fn ($t) => $t > time() - 900);
    return count($mine) >= 5;
}

function admin_record_fail(): void
{
    $all = json_decode((string) @file_get_contents(admin_throttle_file()), true) ?: [];
    foreach ($all as $ip => $times) {
        $all[$ip] = array_values(array_filter($times, fn ($t) => $t > time() - 900));
        if (!$all[$ip]) {
            unset($all[$ip]);
        }
    }
    $all[admin_ip()][] = time();
    @file_put_contents(admin_throttle_file(), json_encode($all), LOCK_EX);
}

function admin_clear_fails(): void
{
    $all = json_decode((string) @file_get_contents(admin_throttle_file()), true) ?: [];
    unset($all[admin_ip()]);
    @file_put_contents(admin_throttle_file(), json_encode($all), LOCK_EX);
}

function flash(?string $msg = null, string $type = 'ok'): ?array
{
    admin_session();
    if ($msg !== null) {
        $_SESSION['flash'] = ['msg' => $msg, 'type' => $type];
        return null;
    }
    $f = $_SESSION['flash'] ?? null;
    unset($_SESSION['flash']);
    return $f;
}

function back_to(string $anchor = ''): never
{
    header('Location: /admin/' . ($anchor !== '' ? '#' . rawurlencode($anchor) : ''), true, 303);
    exit;
}

// ---------------------------------------------------------------
// Saving, backups, restore
// ---------------------------------------------------------------
function cms_backup_dir(): string
{
    return OLIVE_DATA . '/backups';
}

/** Save the overrides: back up the current file first, write atomically, purge page cache. */
function cms_save(array $ov): void
{
    if (!is_dir(OLIVE_DATA)) {
        @mkdir(OLIVE_DATA, 0755, true);
    }
    $dir = cms_backup_dir();
    if (!is_dir($dir)) {
        @mkdir($dir, 0755, true);
    }
    if (is_file(OLIVE_CMS_FILE)) {
        @copy(OLIVE_CMS_FILE, $dir . '/cms-' . date('Ymd-His') . '-' . bin2hex(random_bytes(2)) . '.json');
        $old = glob($dir . '/cms-*.json') ?: [];
        rsort($old);
        foreach (array_slice($old, CMS_BACKUP_KEEP) as $f) {
            @unlink($f);
        }
    }
    $ov['_saved_at'] = date('c');
    $tmp = OLIVE_CMS_FILE . '.tmp';
    $json = json_encode($ov, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    if ($json === false || file_put_contents($tmp, $json, LOCK_EX) === false || !rename($tmp, OLIVE_CMS_FILE)) {
        throw new RuntimeException('Could not save. Check that the data folder is writable.');
    }
    cms_purge_cache();
}

/** Tell LiteSpeed (Hostinger) to drop cached pages so changes show at once. */
function cms_purge_cache(): void
{
    if (!headers_sent()) {
        header('X-LiteSpeed-Purge: *');
    }
}

function cms_backups(): array
{
    $files = glob(cms_backup_dir() . '/cms-*.json') ?: [];
    rsort($files);
    return array_map(fn ($f) => ['file' => basename($f), 'time' => filemtime($f)], $files);
}

// ---------------------------------------------------------------
// Text helpers
// ---------------------------------------------------------------
/** Trimmed single-line text, control characters removed, cut to $max characters. */
function clean(mixed $v, int $max): string
{
    $s = preg_replace('/[\x00-\x1F\x7F]+/u', ' ', (string) $v) ?? '';
    $s = trim(preg_replace('/\s+/u', ' ', $s) ?? '');
    return mb_substr($s, 0, $max);
}

/** One item per line, blank lines dropped. */
function clean_lines(mixed $v, int $maxItems, int $maxLen): array
{
    $lines = preg_split('/\R/u', (string) $v) ?: [];
    $out = [];
    foreach ($lines as $l) {
        $l = clean($l, $maxLen);
        if ($l !== '') {
            $out[] = $l;
        }
    }
    return array_slice(array_values(array_unique($out)), 0, $maxItems);
}

function slugify(string $s, int $max = 50): string
{
    $s = strtolower(trim(preg_replace('/[^A-Za-z0-9]+/', '-', $s) ?? '', '-'));
    if (strlen($s) > $max) {
        $cut = substr($s, 0, $max);
        $s = str_contains($cut, '-') ? substr($cut, 0, (int) strrpos($cut, '-')) : $cut;
    }
    $s = trim($s, '-');
    return $s !== '' ? $s : 'olive-photo';
}

/** Alt text rules: describe the photo, 10 to 125 characters. Returns an error or ''. */
function alt_error(string $alt): string
{
    $len = mb_strlen($alt);
    if ($len < 10) {
        return 'Please describe the photo in a few words (at least 10 characters). Example: "Live pani puri counter at a wedding in Gandhidham".';
    }
    if ($len > 125) {
        return 'Please keep the photo description under 125 characters.';
    }
    if (preg_match('/^(image|photo|picture|img)\b/i', $alt)) {
        return 'Do not start with "image of" or "photo of". Just describe what is in the photo.';
    }
    return '';
}

// ---------------------------------------------------------------
// Image processing
// ---------------------------------------------------------------
/**
 * Check, clean and resize an uploaded photo. Saves WebP copies at 480, 960
 * and 1600 px wide in /uploads, named from the alt text.
 * Returns the image record for content, or throws with a plain message.
 */
/** Resize with imagecopyresampled (more reliable across servers than imagescale). */
function cms_resize(GdImage $src, int $tw, int $th): ?GdImage
{
    $dst = @imagecreatetruecolor($tw, $th);
    if (!$dst) {
        return null;
    }
    imagealphablending($dst, true);
    if (!@imagecopyresampled($dst, $src, 0, 0, 0, 0, $tw, $th, imagesx($src), imagesy($src))) {
        imagedestroy($dst);
        return null;
    }
    return $dst;
}

/** Effective upload limit in bytes: our cap, or the server's if lower. */
function cms_upload_limit(): int
{
    $toBytes = function (string $v): int {
        $v = trim($v);
        $n = (int) $v;
        return match (strtolower(substr($v, -1))) { 'g' => $n << 30, 'm' => $n << 20, 'k' => $n << 10, default => $n };
    };
    $limits = [CMS_MAX_UPLOAD_BYTES];
    foreach (['upload_max_filesize', 'post_max_size'] as $k) {
        $b = $toBytes((string) ini_get($k));
        if ($b > 0) {
            $limits[] = $b;
        }
    }
    return min($limits);
}

function cms_limit_mb(): string
{
    return (string) floor(cms_upload_limit() / 1048576) . ' MB';
}

function cms_process_image(array $file, string $alt, bool $hero = false, bool $og = false): array
{
    if (!extension_loaded('gd') || !function_exists('imagewebp')) {
        throw new RuntimeException('This server cannot process images (GD with WebP is missing). Contact One Man Marketing.');
    }
    $err = (int) ($file['error'] ?? UPLOAD_ERR_NO_FILE);
    if ($err === UPLOAD_ERR_INI_SIZE || $err === UPLOAD_ERR_FORM_SIZE) {
        throw new RuntimeException('That photo is too large. Please use one under ' . cms_limit_mb() . '.');
    }
    if ($err !== UPLOAD_ERR_OK || !is_uploaded_file((string) $file['tmp_name'])) {
        throw new RuntimeException('The photo did not upload. Please try again.');
    }
    $tmp = (string) $file['tmp_name'];
    if (filesize($tmp) > cms_upload_limit()) {
        throw new RuntimeException('That photo is too large. Please use one under ' . cms_limit_mb() . '.');
    }

    // Trust the file's real contents, never its name or the browser's claim.
    $mime = (new finfo(FILEINFO_MIME_TYPE))->file($tmp);
    if (!in_array($mime, ['image/jpeg', 'image/png', 'image/webp'], true)) {
        throw new RuntimeException('Please upload a JPG, PNG or WebP photo.');
    }
    $info = @getimagesize($tmp);
    if (!$info || $info[0] < 1 || $info[1] < 1) {
        throw new RuntimeException('That file is not a readable photo.');
    }
    [$w, $h] = $info;
    if ($w * $h > CMS_MAX_PIXELS) {
        throw new RuntimeException('That photo has too many pixels. Please resize it below 50 megapixels.');
    }
    if (max($w, $h) < CMS_MIN_WIDTH) {
        throw new RuntimeException('That photo is too small (' . $w . ' x ' . $h . ' px). Please use one at least 800 px wide so it looks sharp.');
    }

    @ini_set('memory_limit', '768M');
    @set_time_limit(120);

    $src = match ($mime) {
        'image/jpeg' => @imagecreatefromjpeg($tmp),
        'image/png' => @imagecreatefrompng($tmp),
        'image/webp' => @imagecreatefromwebp($tmp),
    };
    if (!$src) {
        throw new RuntimeException('That photo could not be read. Try saving it again as JPG.');
    }

    // Shrink huge phone photos straight away: later steps then need far less memory.
    $longSide = max(imagesx($src), imagesy($src));
    if ($longSide > 2400) {
        $ratio = 2400 / $longSide;
        $small = cms_resize($src, max(1, (int) round(imagesx($src) * $ratio)), max(1, (int) round(imagesy($src) * $ratio)));
        if (!$small) {
            imagedestroy($src);
            throw new RuntimeException('This server ran out of memory processing the photo. Try a smaller photo (under 4 MB), or resize it to 2000 px wide first.');
        }
        imagedestroy($src);
        $src = $small;
    }

    // Phone photos store rotation in EXIF; apply it so the photo is upright.
    if ($mime === 'image/jpeg' && function_exists('exif_read_data')) {
        $exif = @exif_read_data($tmp);
        $o = (int) ($exif['Orientation'] ?? 1);
        $rot = match ($o) { 3 => 180, 6 => -90, 8 => 90, default => 0 };
        if ($rot !== 0) {
            $r = imagerotate($src, $rot, 0);
            if ($r) {
                imagedestroy($src);
                $src = $r;
            }
        }
    }
    imagepalettetotruecolor($src);
    $w = imagesx($src);
    $h = imagesy($src);

    $dir = OLIVE_ROOT . '/uploads';
    if (!is_dir($dir) && !@mkdir($dir, 0755, true)) {
        throw new RuntimeException('The uploads folder is missing and could not be created.');
    }
    $base = slugify($alt) . '-' . bin2hex(random_bytes(2));

    $written = [];
    $dims = [];
    try {
        foreach (CMS_SIZES as $target) {
            $tw = min($target, $w);
            $th = (int) round($h * $tw / $w);
            $img = $tw === $w ? $src : cms_resize($src, $tw, $th);
            if (!$img) {
                throw new RuntimeException('Resizing failed: the server ran out of memory. Try a smaller photo (under 4 MB) or resize it to 2000 px wide first.');
            }
            imagealphablending($img, false);
            imagesavealpha($img, true);

            $budget = ($hero && $target === 1600) ? CMS_BUDGET_HERO_1600 : CMS_BUDGET[$target];
            $data = '';
            foreach (CMS_QUALITIES as $q) {
                ob_start();
                imagewebp($img, null, $q);
                $data = (string) ob_get_clean();
                if (strlen($data) <= $budget) {
                    break;
                }
            }
            if ($img !== $src) {
                imagedestroy($img);
            }
            $path = $dir . '/' . $base . '-' . $target . '.webp';
            if ($data === '' || file_put_contents($path, $data, LOCK_EX) === false) {
                throw new RuntimeException('Could not save the photo. Check that the uploads folder is writable.');
            }
            $written[] = $path;
            $dims[$target] = [$tw, $th];
        }

        // Share image for WhatsApp / Facebook / Google: 1200 x 630 JPG, centre-cropped.
        $ogUrl = null;
        if ($og) {
            $ratio = 1200 / 630;
            $sw = $w;
            $sh = (int) round($w / $ratio);
            if ($sh > $h) {
                $sh = $h;
                $sw = (int) round($h * $ratio);
            }
            $dst = imagecreatetruecolor(1200, 630);
            if ($dst && imagecopyresampled($dst, $src, 0, 0, (int) (($w - $sw) / 2), (int) (($h - $sh) / 2), 1200, 630, $sw, $sh)) {
                ob_start();
                imagejpeg($dst, null, 82);
                $jpg = (string) ob_get_clean();
                $ogPath = $dir . '/' . $base . '-og.jpg';
                if ($jpg !== '' && file_put_contents($ogPath, $jpg, LOCK_EX) !== false) {
                    $written[] = $ogPath;
                    $ogUrl = '/uploads/' . $base . '-og.jpg';
                }
            }
            if ($dst) {
                imagedestroy($dst);
            }
        }
    } catch (Throwable $t) {
        foreach ($written as $p) {
            @unlink($p);
        }
        throw $t instanceof RuntimeException ? $t : new RuntimeException('Could not process the photo.');
    } finally {
        imagedestroy($src);
    }

    $rec = [
        'src' => '/uploads/' . $base . '-960.webp',
        'alt' => $alt,
        'width' => $dims[1600][0],
        'height' => $dims[1600][1],
    ];
    if (!empty($ogUrl)) {
        $rec['og'] = $ogUrl;
    }
    return $rec;
}

/** Thumbnail URL for the admin list (the 480 copy when there is one). */
function thumb(array $img): string
{
    $src = (string) ($img['src'] ?? '');
    return preg_replace('/-\d+\.webp$/', '-480.webp', $src) ?? $src;
}

// ---------------------------------------------------------------
// Articles (data/articles.json)
// ---------------------------------------------------------------
/** Save the articles list with a backup of the previous version (last 20 kept). */
function cms_articles_save(array $list): void
{
    $dir = cms_backup_dir();
    if (!is_dir(OLIVE_DATA)) {
        @mkdir(OLIVE_DATA, 0755, true);
    }
    if (!is_dir($dir)) {
        @mkdir($dir, 0755, true);
    }
    if (is_file(OLIVE_ARTICLES_FILE)) {
        @copy(OLIVE_ARTICLES_FILE, $dir . '/articles-' . date('Ymd-His') . '-' . bin2hex(random_bytes(2)) . '.json');
        $old = glob($dir . '/articles-*.json') ?: [];
        rsort($old);
        foreach (array_slice($old, CMS_BACKUP_KEEP) as $f) {
            @unlink($f);
        }
    }
    $tmp = OLIVE_ARTICLES_FILE . '.tmp';
    $json = json_encode(array_values($list), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    if ($json === false || file_put_contents($tmp, $json, LOCK_EX) === false || !rename($tmp, OLIVE_ARTICLES_FILE)) {
        throw new RuntimeException('Could not save the article. Check that the data folder is writable.');
    }
    cms_purge_cache();
}

/** Slug rules for a new article: lowercase words and dashes, not reserved, not taken. */
function article_slug_error(string $slug, ?string $ownSlug = null): string
{
    if (!preg_match('/^[a-z0-9]+(-[a-z0-9]+)*$/', $slug) || strlen($slug) > 70) {
        return 'The web address (URL) can only have lowercase letters, numbers and dashes, up to 70 characters.';
    }
    if (in_array($slug, OLIVE_RESERVED_SLUGS, true)) {
        return 'That web address is reserved. Please choose another.';
    }
    foreach (blog_posts(true) as $p) {
        if ($p['slug'] === $slug && $slug !== $ownSlug) {
            return 'Another article already uses that web address. Please change it a little.';
        }
    }
    return '';
}
