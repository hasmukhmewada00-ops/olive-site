<?php
declare(strict_types=1);
require __DIR__ . '/_init.php';
admin_require();

$b = content('business', []);
$a = $b['address'] ?? [];
$flash = flash();
$slots = cms_image_slots();
$gallery = array_values(content('gallery', []));
$assoc = array_values(content('associations', []));
$trust = array_values(content('trust', []));
// Placeholder reviews from the template are not shown; only real ones are edited here.
$reviews = array_values(array_filter(content('testimonials', []), fn ($t) => ($t['confirmed'] ?? true) !== false));
$ann = content('announcement', []);
$pop = content('popup', []);
$areas = array_map(fn ($x) => $x['name'], content('areas.list', []));
$dishes = array_map(fn ($x) => $x['name'], array_filter(content('signature_dishes', []), fn ($d) => ($d['confirmed'] ?? true) !== false));
$host = (string) ($_SERVER['HTTP_HOST'] ?? '');

/** Upload form for one photo position. */
function photo_form(string $action, array $img, array $extra, string $button, bool $required): string
{
    $out = '<form class="photo__form" method="post" action="/admin/image.php" enctype="multipart/form-data" data-upload data-max="' . cms_upload_limit() . '">' . csrf_field();
    $out .= '<input type="hidden" name="action" value="' . e($action) . '">';
    foreach ($extra as $k => $v) {
        $out .= '<input type="hidden" name="' . e($k) . '" value="' . e($v) . '">';
    }
    $out .= '<label class="file">' . ($required ? 'Choose photo' : 'Replace photo <span class="opt">optional</span>')
        . '<input type="file" name="photo" accept="image/jpeg,image/png,image/webp"' . ($required ? ' required' : '') . '></label>';
    $out .= '<p class="file__info" hidden></p>';
    $out .= '<label>Describe the photo (alt text) <span class="opt">required, 10 to 125 characters</span>'
        . '<textarea name="alt" rows="2" required minlength="10" maxlength="125" placeholder="e.g. Live pani puri counter at a wedding in Gandhidham" data-count>' . e($img['alt'] ?? '') . '</textarea></label>';
    $out .= '<button class="btn" type="submit">' . e($button) . '</button></form>';
    return $out;
}

function thumb_img(array $img, string $label = ''): string
{
    if (empty($img['src'])) {
        return '<div class="photo__empty">No photo yet</div>';
    }
    $tag = !empty($img['ai_placeholder']) ? '<span class="tag">Sample photo, replace with a real one</span>' : '';
    return '<div class="photo__thumb"><img src="' . e(thumb($img)) . '" alt="' . e($img['alt'] ?? $label) . '" loading="lazy" width="240" height="180">' . $tag . '</div>';
}
?><!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="robots" content="noindex, nofollow">
<title>Olive admin</title>
<link rel="icon" href="/assets/img/favicon.svg" type="image/svg+xml">
<link rel="stylesheet" href="<?= e(asset('admin/admin.css')) ?>">
</head>
<body>
<header class="bar">
  <div class="bar__in">
    <span class="bar__logo"><?= logo_svg('olive-logo') ?></span>
    <span class="bar__site"><?= e($host) ?><?= is_staging() ? ' <em>staging</em>' : '' ?></span>
    <nav class="bar__links"><a href="/" target="_blank" rel="noopener">View site</a><a href="/admin/logout.php">Log out</a></nav>
  </div>
</header>

<div class="layout">
<nav class="side" aria-label="Sections">
  <a href="#contact">Contact details</a>
  <a href="#photos">Section photos</a>
  <a href="#gallery">Gallery</a>
  <a href="#associations">Associations</a>
  <a href="#trust">Highlights strip</a>
  <a href="#areas">Where we cater</a>
  <a href="#dishes">Live counter favourites</a>
  <a href="#testimonials">Client reviews</a>
  <a href="#announcement">Announcement bar</a>
  <a href="#popup">Enquiry pop-up</a>
  <a href="#footer">Footer</a>
  <a href="#backups">Backups</a>
</nav>

<main class="main">
  <?php if ($flash): ?><p class="alert alert--<?= e($flash['type']) ?>" role="status"><?= e($flash['msg']) ?></p><?php endif; ?>
  <?php if (is_staging()): ?><p class="note">This is the <strong>staging</strong> admin. Changes here show only on the staging site. The live site has its own admin at the same /admin/ address.</p><?php endif; ?>

  <!-- Contact -->
  <section class="card" id="contact">
    <h2>Contact details</h2>
    <p class="muted">Used everywhere on the site at once: header, call and WhatsApp buttons, footer, map and Google's business data.</p>
    <form method="post" action="/admin/save.php">
      <?= csrf_field() ?><input type="hidden" name="group" value="contact">
      <div class="grid2">
        <label>Calling number <input name="phone" type="tel" value="<?= e($b['phone'] ?? '') ?>" placeholder="+91 98765 43210"></label>
        <label>WhatsApp number <span class="opt">blank = same as calling</span><input name="whatsapp" type="tel" value="<?= e($b['whatsapp'] ?? '') ?>" placeholder="+91 98765 43210"></label>
        <label>Email <input name="email" type="email" value="<?= e($b['email'] ?? '') ?>"></label>
        <label>Instagram handle <input name="instagram" value="<?= e($b['instagram'] ?? '') ?>" placeholder="olive288711"></label>
        <label>Business hours <span class="opt">optional</span><input name="hours" value="<?= e($b['hours'] ?? '') ?>" placeholder="Mon to Sun, 9 am to 9 pm"></label>
        <label>FSSAI licence number <input name="fssai" inputmode="numeric" value="<?= e($b['fssai'] ?? '') ?>"></label>
      </div>
      <h3>Address</h3>
      <label>Street / building <input name="street" value="<?= e($a['street'] ?? '') ?>" required></label>
      <div class="grid2">
        <label>City <input name="locality" value="<?= e($a['locality'] ?? '') ?>" required></label>
        <label>District <input name="district" value="<?= e($a['district'] ?? '') ?>"></label>
        <label>State <input name="region" value="<?= e($a['region'] ?? '') ?>"></label>
        <label>PIN code <input name="postal_code" inputmode="numeric" value="<?= e($a['postal_code'] ?? '') ?>"></label>
      </div>
      <label>Map search text <span class="opt">what Google Maps should find</span><input name="map_query" value="<?= e($b['map_query'] ?? '') ?>"></label>
      <label>Google Business Profile link <span class="opt">optional</span><input name="gbp_url" type="url" value="<?= e($b['gbp_url'] ?? '') ?>" placeholder="https://g.page/..."></label>
      <button class="btn" type="submit">Save contact details</button>
    </form>
  </section>

  <!-- Section photos -->
  <section class="card" id="photos">
    <h2>Section photos</h2>
    <p class="muted">Upload a JPG, PNG or WebP (phone photos are fine, up to <?= e(cms_limit_mb()) ?>, at least 800 px wide). The site makes small, fast WebP copies automatically and crops to fit each frame. Every photo needs a short description for Google and screen readers.</p>
    <div class="photos">
      <?php foreach ($slots as $key => $s): ?>
        <article class="photo" id="slot-<?= e($key) ?>">
          <h3><?= e($s['label']) ?></h3>
          <p class="muted small">Frame: <?= e($s['shape']) ?></p>
          <?= thumb_img($s['current'], $s['label']) ?>
          <?= photo_form('slot', $s['current'], ['slot' => $key], 'Save photo', empty($s['current']['src'])) ?>
        </article>
      <?php endforeach; ?>
    </div>
  </section>

  <!-- Gallery -->
  <section class="card" id="gallery">
    <h2>Gallery <span class="count"><?= count($gallery) ?> / <?= CMS_GALLERY_MAX ?></span></h2>
    <p class="muted">Photos show in this order. Use real event photos: food on modern crockery, buffet setups, the kitchen and the team.</p>
    <article class="photo photo--add">
      <h3>Add a photo</h3>
      <?= photo_form('gallery_add', [], [], 'Add to gallery', true) ?>
    </article>
    <ol class="gal">
      <?php foreach ($gallery as $i => $g): ?>
        <li class="photo">
          <?= thumb_img($g) ?>
          <form method="post" action="/admin/image.php" class="photo__form">
            <?= csrf_field() ?><input type="hidden" name="action" value="gallery_alt"><input type="hidden" name="i" value="<?= $i ?>">
            <label>Description <textarea name="alt" rows="2" required minlength="10" maxlength="125" data-count><?= e($g['alt'] ?? '') ?></textarea></label>
            <button class="btn btn--small" type="submit">Save</button>
          </form>
          <div class="gal__tools">
            <?php foreach (['up' => 'Move up', 'down' => 'Move down'] as $dir => $lbl): if (($dir === 'up' && $i === 0) || ($dir === 'down' && $i === count($gallery) - 1)) continue; ?>
              <form method="post" action="/admin/image.php"><?= csrf_field() ?><input type="hidden" name="action" value="gallery_move"><input type="hidden" name="i" value="<?= $i ?>"><input type="hidden" name="dir" value="<?= $dir ?>"><button class="btn btn--ghost btn--small" type="submit"><?= $lbl ?></button></form>
            <?php endforeach; ?>
            <form method="post" action="/admin/image.php" data-confirm="Remove this photo from the gallery?"><?= csrf_field() ?><input type="hidden" name="action" value="gallery_remove"><input type="hidden" name="i" value="<?= $i ?>"><button class="btn btn--danger btn--small" type="submit">Remove</button></form>
          </div>
        </li>
      <?php endforeach; ?>
    </ol>
  </section>

  <!-- Associations -->
  <section class="card" id="associations">
    <h2>Associations</h2>
    <p class="muted">Hotels and partners Olive works with, or has worked with. Leave a row blank to remove it.</p>
    <form method="post" action="/admin/save.php">
      <?= csrf_field() ?><input type="hidden" name="group" value="associations">
      <?php for ($i = 0; $i < max(count($assoc) + 2, 4); $i++): $r = $assoc[$i] ?? []; ?>
        <div class="row3">
          <label>Name <input name="assoc[<?= $i ?>][name]" value="<?= e($r['name'] ?? '') ?>" maxlength="80" placeholder="Hotel or company name"></label>
          <label>Type
            <select name="assoc[<?= $i ?>][status]">
              <option value="current"<?= ($r['status'] ?? 'current') === 'current' ? ' selected' : '' ?>>Current</option>
              <option value="previous"<?= ($r['status'] ?? '') === 'previous' ? ' selected' : '' ?>>Previous</option>
            </select>
          </label>
          <label>Small note <span class="opt">optional</span><input name="assoc[<?= $i ?>][note]" value="<?= e($r['note'] ?? '') ?>" maxlength="60" placeholder="Food & beverage partner"></label>
        </div>
      <?php endfor; ?>
      <button class="btn" type="submit">Save associations</button>
    </form>
  </section>

  <!-- Trust strip -->
  <section class="card" id="trust">
    <h2>Highlights strip</h2>
    <p class="muted">The green band under the banner. Short value on top, label below. 2 to 5 items.</p>
    <form method="post" action="/admin/save.php">
      <?= csrf_field() ?><input type="hidden" name="group" value="trust">
      <?php for ($i = 0; $i < 5; $i++): $r = $trust[$i] ?? []; ?>
        <div class="row2">
          <label>Value <input name="trust[<?= $i ?>][value]" value="<?= e($r['value'] ?? '') ?>" maxlength="12" placeholder="5-Star"></label>
          <label>Label <input name="trust[<?= $i ?>][label]" value="<?= e($r['label'] ?? '') ?>" maxlength="40" placeholder="Hotel-style service"></label>
        </div>
      <?php endfor; ?>
      <button class="btn" type="submit">Save highlights</button>
    </form>
  </section>

  <!-- Areas -->
  <section class="card" id="areas">
    <h2>Where we cater</h2>
    <form method="post" action="/admin/save.php">
      <?= csrf_field() ?><input type="hidden" name="group" value="areas">
      <label>Cities <span class="opt">one per line</span><textarea name="areas" rows="6"><?= e(implode("\n", $areas)) ?></textarea></label>
      <label>Wider area label <span class="opt">shown as the last, highlighted item</span><input name="region" value="<?= e(content('areas.region', '')) ?>" maxlength="40" placeholder="Across Gujarat"></label>
      <button class="btn" type="submit">Save areas</button>
    </form>
  </section>

  <!-- Dishes -->
  <section class="card" id="dishes">
    <h2>Live counter favourites</h2>
    <p class="muted">Shown on the menu card. One per line, up to 12.</p>
    <form method="post" action="/admin/save.php">
      <?= csrf_field() ?><input type="hidden" name="group" value="dishes">
      <textarea name="dishes" rows="6" aria-label="Dishes"><?= e(implode("\n", $dishes)) ?></textarea>
      <button class="btn" type="submit">Save dishes</button>
    </form>
  </section>

  <!-- Testimonials -->
  <section class="card" id="testimonials">
    <h2>Client reviews</h2>
    <p class="muted">Real reviews only, with the client's permission. Up to 6. Leave a row blank to remove it.</p>
    <form method="post" action="/admin/save.php">
      <?= csrf_field() ?><input type="hidden" name="group" value="testimonials">
      <?php for ($i = 0; $i < min(6, max(count($reviews) + 1, 3)); $i++): $r = $reviews[$i] ?? []; ?>
        <fieldset class="review">
          <legend>Review <?= $i + 1 ?></legend>
          <label>Review <textarea name="t[<?= $i ?>][quote]" rows="3" maxlength="400"><?= e($r['quote'] ?? '') ?></textarea></label>
          <div class="row2">
            <label>Client name <input name="t[<?= $i ?>][name]" value="<?= e($r['name'] ?? '') ?>" maxlength="60"></label>
            <label>Event <input name="t[<?= $i ?>][event]" value="<?= e($r['event'] ?? '') ?>" maxlength="60" placeholder="Wedding, Gandhidham"></label>
          </div>
        </fieldset>
      <?php endfor; ?>
      <button class="btn" type="submit">Save reviews</button>
    </form>
  </section>

  <!-- Announcement -->
  <section class="card" id="announcement">
    <h2>Announcement bar</h2>
    <p class="muted">A thin strip at the very top of the site, for offers or season bookings.</p>
    <form method="post" action="/admin/save.php">
      <?= csrf_field() ?><input type="hidden" name="group" value="announcement">
      <label class="check"><input type="checkbox" name="on" value="1"<?= !empty($ann['on']) ? ' checked' : '' ?>> Show the bar</label>
      <label>Text <input name="text" value="<?= e($ann['text'] ?? '') ?>" maxlength="140" placeholder="Now booking weddings for the 2026 to 2027 season" data-count></label>
      <button class="btn" type="submit">Save announcement</button>
    </form>
  </section>

  <!-- Popup -->
  <section class="card" id="popup">
    <h2>Enquiry pop-up</h2>
    <form method="post" action="/admin/save.php">
      <?= csrf_field() ?><input type="hidden" name="group" value="popup">
      <label class="check"><input type="checkbox" name="on" value="1"<?= !empty($pop['on']) ? ' checked' : '' ?>> Show the pop-up</label>
      <div class="grid2">
        <label>Heading <input name="heading" value="<?= e($pop['heading'] ?? '') ?>" maxlength="60"></label>
        <label>Show after (seconds) <input name="delay_seconds" type="number" min="5" max="120" value="<?= (int) ($pop['delay_seconds'] ?? 20) ?>"></label>
      </div>
      <label>Text <input name="text" value="<?= e($pop['text'] ?? '') ?>" maxlength="160" data-count></label>
      <button class="btn" type="submit">Save pop-up</button>
    </form>
  </section>

  <!-- Footer -->
  <section class="card" id="footer">
    <h2>Footer</h2>
    <form method="post" action="/admin/save.php">
      <?= csrf_field() ?><input type="hidden" name="group" value="footer">
      <label>Footer text <textarea name="footer_text" rows="2" maxlength="200" data-count><?= e(content('footer.text', '')) ?></textarea></label>
      <button class="btn" type="submit">Save footer</button>
    </form>
  </section>

  <!-- Backups -->
  <section class="card" id="backups">
    <h2>Backups</h2>
    <p class="muted">A copy is kept before every save (last <?= CMS_BACKUP_KEEP ?>). Restoring brings back all text and photo choices from that moment.</p>
    <?php $bk = cms_backups(); if (!$bk): ?>
      <p class="muted">No backups yet. One is made the next time you save.</p>
    <?php else: ?>
      <ul class="backups">
        <?php foreach ($bk as $x): ?>
          <li>
            <span><?= e(date('d M Y, g:i a', $x['time'])) ?></span>
            <form method="post" action="/admin/save.php" data-confirm="Restore the site to this backup?"><?= csrf_field() ?><input type="hidden" name="group" value="restore"><input type="hidden" name="file" value="<?= e($x['file']) ?>"><button class="btn btn--ghost btn--small" type="submit">Restore</button></form>
          </li>
        <?php endforeach; ?>
      </ul>
    <?php endif; ?>
  </section>

  <p class="muted small foot">Page headings, service text, FAQ and search settings are managed by One Man Marketing to protect Google rankings.</p>
</main>
</div>
<script src="<?= e(asset('admin/admin.js')) ?>" defer></script>
</body>
</html>
