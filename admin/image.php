<?php
declare(strict_types=1);
/** Photo changes: page slots and the gallery. Every photo needs alt text. */
require __DIR__ . '/_init.php';
admin_require();

// A post larger than the server limit arrives with an empty $_POST (no CSRF token).
if ($_SERVER['REQUEST_METHOD'] === 'POST' && empty($_POST) && (int) ($_SERVER['CONTENT_LENGTH'] ?? 0) > 0) {
    flash('That photo is too large for the server. Please use one under ' . cms_limit_mb() . '.', 'bad');
    back_to('photos');
}
csrf_check();

$action = (string) ($_POST['action'] ?? '');
$alt = clean($_POST['alt'] ?? '', 125);
$file = $_FILES['photo'] ?? null;
$hasFile = is_array($file) && (int) ($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE;
$ov = cms_overrides();
$anchor = 'photos';

try {
    switch ($action) {
        case 'slot':
            $slot = (string) ($_POST['slot'] ?? '');
            $slots = cms_image_slots();
            if (!isset($slots[$slot])) {
                throw new RuntimeException('Unknown photo position.');
            }
            $anchor = 'slot-' . $slot;
            if (($e = alt_error($alt)) !== '') {
                throw new RuntimeException($e);
            }
            if ($hasFile) {
                $img = cms_process_image($file, $alt, $slot === 'hero');
            } else {
                $img = $slots[$slot]['current'];
                if (empty($img['src'])) {
                    throw new RuntimeException('Choose a photo to upload.');
                }
                $img['alt'] = $alt;
            }
            unset($img['ai_placeholder'], $img['placeholder']);
            $ov['images'][$slot] = $img;
            cms_save($ov);
            flash($hasFile ? 'Photo uploaded and optimised. It is live on this site now.' : 'Photo description saved.');
            break;

        case 'gallery_add':
            $anchor = 'gallery';
            $gallery = content('gallery', []);
            if (count($gallery) >= CMS_GALLERY_MAX) {
                throw new RuntimeException('The gallery is full (' . CMS_GALLERY_MAX . ' photos). Remove one first.');
            }
            if (($e = alt_error($alt)) !== '') {
                throw new RuntimeException($e);
            }
            if (!$hasFile) {
                throw new RuntimeException('Choose a photo to upload.');
            }
            $img = cms_process_image($file, $alt);
            if (($_POST['where'] ?? 'end') === 'start') {
                array_unshift($gallery, $img);
            } else {
                $gallery[] = $img;
            }
            $ov['gallery'] = array_values($gallery);
            cms_save($ov);
            flash('Photo added to the gallery.');
            break;

        case 'gallery_alt':
        case 'gallery_move':
        case 'gallery_remove':
            $anchor = 'gallery';
            $gallery = array_values(content('gallery', []));
            $i = (int) ($_POST['i'] ?? -1);
            if (!isset($gallery[$i])) {
                throw new RuntimeException('That photo was not found. Reload the page.');
            }
            if ($action === 'gallery_alt') {
                if (($e = alt_error($alt)) !== '') {
                    throw new RuntimeException($e);
                }
                $gallery[$i]['alt'] = $alt;
                $msg = 'Photo description saved.';
            } elseif ($action === 'gallery_move') {
                $j = ($_POST['dir'] ?? '') === 'up' ? $i - 1 : $i + 1;
                if (isset($gallery[$j])) {
                    [$gallery[$i], $gallery[$j]] = [$gallery[$j], $gallery[$i]];
                }
                $msg = 'Gallery order saved.';
            } else {
                array_splice($gallery, $i, 1);
                $msg = 'Photo removed from the gallery.';
            }
            $ov['gallery'] = array_values($gallery);
            cms_save($ov);
            flash($msg);
            break;

        default:
            throw new RuntimeException('Unknown action.');
    }
} catch (Throwable $t) {
    flash($t instanceof RuntimeException ? $t->getMessage() : 'Something went wrong. Please try again.', 'bad');
}
back_to($anchor);
