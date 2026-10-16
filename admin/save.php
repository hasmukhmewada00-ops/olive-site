<?php
declare(strict_types=1);
/** Saves one text group from the admin page. Photos are handled by image.php. */
require __DIR__ . '/_init.php';
admin_require();
if ($_SERVER['REQUEST_METHOD'] === 'POST' && empty($_POST) && (int) ($_SERVER['CONTENT_LENGTH'] ?? 0) > 0) {
    flash('The files are too large for the server. Please use logos under ' . cms_limit_mb() . '.', 'bad');
    back_to('associations');
}
csrf_check();

$group = (string) ($_POST['group'] ?? '');
$ov = cms_overrides();
$p = $_POST;
$errors = [];

/** Rows posted as name[i][field]; rows where every field is blank are dropped. */
$rows = function (string $key, array $fields, int $max) use ($p): array {
    $out = [];
    foreach ((array) ($p[$key] ?? []) as $r) {
        if (!is_array($r)) {
            continue;
        }
        $row = [];
        foreach ($fields as $f => $len) {
            $row[$f] = clean($r[$f] ?? '', $len);
        }
        if (implode('', $row) !== '') {
            $out[] = $row;
        }
    }
    return array_slice($out, 0, $max);
};

$phoneOk = fn (string $v) => $v === '' || (bool) preg_match('/^\+?[0-9 ()-]{10,20}$/', $v);

switch ($group) {
    case 'contact':
        $b = [
            'phone' => clean($p['phone'] ?? '', 20),
            'whatsapp' => clean($p['whatsapp'] ?? '', 20),
            'email' => clean($p['email'] ?? '', 120),
            'instagram' => clean($p['instagram'] ?? '', 60),
            'hours' => clean($p['hours'] ?? '', 120),
            'fssai' => clean($p['fssai'] ?? '', 20),
            'map_query' => clean($p['map_query'] ?? '', 200),
            'gbp_url' => clean($p['gbp_url'] ?? '', 300),
            'address' => [
                'street' => clean($p['street'] ?? '', 200),
                'locality' => clean($p['locality'] ?? '', 60),
                'district' => clean($p['district'] ?? '', 60),
                'region' => clean($p['region'] ?? '', 60),
                'postal_code' => clean($p['postal_code'] ?? '', 10),
            ],
        ];
        // Accept a full Instagram link or @handle; keep just the handle.
        $b['instagram'] = preg_replace('~^(https?://)?(www\.)?instagram\.com/~i', '', $b['instagram']) ?? '';
        $b['instagram'] = trim(ltrim($b['instagram'], '@'), '/');
        if (!$phoneOk($b['phone'])) {
            $errors[] = 'Calling number: use digits only, for example +91 98765 43210.';
        }
        if (!$phoneOk($b['whatsapp'])) {
            $errors[] = 'WhatsApp number: use digits only, for example +91 98765 43210.';
        }
        if ($b['email'] !== '' && !filter_var($b['email'], FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Email address does not look right.';
        }
        if ($b['instagram'] !== '' && !preg_match('/^[A-Za-z0-9._]{1,30}$/', $b['instagram'])) {
            $errors[] = 'Instagram: enter only the handle, for example olive288711.';
        }
        if ($b['fssai'] !== '' && !preg_match('/^\d{14}$/', $b['fssai'])) {
            $errors[] = 'FSSAI licence number should be 14 digits.';
        }
        if ($b['gbp_url'] !== '' && !preg_match('~^https://~', $b['gbp_url'])) {
            $errors[] = 'Google Business Profile link must start with https://';
        }
        if ($b['address']['street'] === '' || $b['address']['locality'] === '') {
            $errors[] = 'Address and city cannot be empty.';
        }
        $ov['business'] = $b;
        break;

    case 'trust':
        $ov['trust'] = $rows('trust', ['value' => 12, 'label' => 40], 5);
        if (count($ov['trust']) < 2) {
            $errors[] = 'Keep at least 2 highlights in the strip.';
        }
        break;

    case 'associations':
        $list = [];
        $files = $_FILES['assoc_logo'] ?? null;
        foreach ((array) ($p['assoc'] ?? []) as $i => $r) {
            if (!is_array($r)) {
                continue;
            }
            $name = clean($r['name'] ?? '', 80);
            if ($name === '') {
                continue;
            }
            $row = [
                'name' => $name,
                'status' => ($r['status'] ?? '') === 'previous' ? 'previous' : 'current',
                'note' => clean($r['note'] ?? '', 60),
            ];
            $logo = (string) ($r['logo'] ?? '');
            if (!preg_match('~^/(uploads|assets/img/logos)/[a-z0-9-]+\.webp$~', $logo) || !empty($r['logo_remove'])) {
                $logo = '';
            }
            $lw = (int) ($r['logo_w'] ?? 0);
            $lh = (int) ($r['logo_h'] ?? 0);
            $up = is_array($files) && isset($files['error'][$i]) && (int) $files['error'][$i] !== UPLOAD_ERR_NO_FILE;
            if ($up) {
                try {
                    $one = ['name' => $files['name'][$i], 'tmp_name' => $files['tmp_name'][$i], 'error' => $files['error'][$i], 'size' => $files['size'][$i]];
                    $img = cms_process_logo($one, $name);
                    $logo = $img['src'];
                    $lw = $img['w'];
                    $lh = $img['h'];
                } catch (RuntimeException $ex) {
                    $errors[] = $name . ': ' . $ex->getMessage();
                }
            }
            if ($logo !== '' && $lw > 0 && $lh > 0) {
                $row['logo'] = $logo;
                $row['logo_w'] = $lw;
                $row['logo_h'] = $lh;
            }
            $list[] = $row;
        }
        $ov['associations'] = array_slice($list, 0, 10);
        break;

    case 'areas':
        $ov['areas'] = [
            'list' => clean_lines($p['areas'] ?? '', 15, 40),
            'region' => clean($p['region'] ?? '', 40),
        ];
        if (!$ov['areas']['list']) {
            $errors[] = 'Add at least one city.';
        }
        break;

    case 'dishes':
        $ov['dishes'] = clean_lines($p['dishes'] ?? '', 12, 40);
        break;

    case 'testimonials':
        $list = $rows('t', ['quote' => 400, 'name' => 60, 'event' => 60], 6);
        foreach ($list as $t) {
            if ($t['quote'] === '' || $t['name'] === '') {
                $errors[] = 'Each review needs the words and the client name.';
                break;
            }
        }
        $ov['testimonials'] = $list;
        break;

    case 'announcement':
        $ov['announcement'] = ['on' => !empty($p['on']), 'text' => clean($p['text'] ?? '', 140)];
        if ($ov['announcement']['on'] && $ov['announcement']['text'] === '') {
            $errors[] = 'Write the announcement text, or switch the bar off.';
        }
        break;

    case 'popup':
        $ov['popup'] = [
            'on' => !empty($p['on']),
            'delay_seconds' => max(5, min(120, (int) ($p['delay_seconds'] ?? 20))),
            'heading' => clean($p['heading'] ?? '', 60),
            'text' => clean($p['text'] ?? '', 160),
        ];
        break;

    case 'footer':
        $ov['footer_text'] = clean($p['footer_text'] ?? '', 200);
        break;

    case 'restore':
        $file = basename((string) ($p['file'] ?? ''));
        $path = cms_backup_dir() . '/' . $file;
        if (!preg_match('/^cms-\d{8}-\d{6}-[0-9a-f]{4}\.json$/', $file) || !is_file($path)) {
            flash('That backup was not found.', 'bad');
            back_to('backups');
        }
        $restored = json_decode((string) file_get_contents($path), true);
        if (!is_array($restored)) {
            flash('That backup could not be read.', 'bad');
            back_to('backups');
        }
        $ov = $restored;
        break;

    default:
        http_response_code(400);
        exit('Unknown section.');
}

if ($errors) {
    flash(implode(' ', $errors), 'bad');
    back_to($group);
}

try {
    cms_save($ov);
    flash($group === 'restore' ? 'Backup restored. The site now shows that version.' : 'Saved. The change is live on this site now.');
} catch (Throwable $t) {
    flash($t->getMessage(), 'bad');
}
back_to($group === 'restore' ? 'backups' : $group);
