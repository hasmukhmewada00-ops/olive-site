<?php
declare(strict_types=1);
require __DIR__ . '/_init.php';
admin_require();

$flash = flash();
$posts = blog_posts(true);
$host = (string) ($_SERVER['HTTP_HOST'] ?? '');
?><!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="robots" content="noindex, nofollow">
<title>Articles | Olive admin</title>
<link rel="icon" href="/assets/img/favicon.svg" type="image/svg+xml">
<link rel="stylesheet" href="<?= e(asset('admin/admin.css')) ?>">
</head>
<body>
<header class="bar">
  <div class="bar__in">
    <span class="bar__logo"><?= logo_svg('olive-logo') ?></span>
    <span class="bar__site"><?= e($host) ?><?= is_staging() ? ' <em>staging</em>' : '' ?></span>
    <nav class="bar__links"><a href="/admin/">Site content</a><a href="/admin/articles.php" aria-current="page">Articles</a><a href="/" target="_blank" rel="noopener">View site</a><a href="/admin/logout.php">Log out</a></nav>
  </div>
</header>

<div class="layout layout--single">
<main class="main">
  <?php if ($flash): ?><p class="alert alert--<?= e($flash['type']) ?>" role="status"><?= e($flash['msg']) ?></p><?php endif; ?>

  <section class="card">
    <div class="card__head">
      <div>
        <h2>Articles <span class="count"><?= count($posts) ?></span></h2>
        <p class="muted">Articles help Olive show up on Google when families search for caterers. Write about real questions your clients ask.</p>
      </div>
      <a class="btn" href="/admin/article.php?new=1">Write a new article</a>
    </div>

    <ul class="alist">
      <?php foreach ($posts as $p): $mine = ($p['source'] ?? '') === 'client'; $draft = ($p['draft'] ?? false) === true; ?>
        <li class="aitem" id="a-<?= e($p['slug']) ?>">
          <img class="aitem__img" src="<?= e(blog_img($p, 480)) ?>" alt="<?= e($p['image_alt'] ?? '') ?>" width="160" height="120" loading="lazy">
          <div class="aitem__main">
            <p class="aitem__title"><?= e($p['title']) ?></p>
            <p class="aitem__meta">
              <span class="pill <?= $draft ? 'pill--draft' : 'pill--live' ?>"><?= $draft ? 'Draft' : 'Published' ?></span>
              <?= e(blog_date((string) $p['date'])) ?> &middot; <?= $mine ? 'Written by you' : 'Olive guide' ?>
              <?php foreach (blog_post_tags($p) as $tl): ?><span class="chip"><?= e($tl) ?></span><?php endforeach; ?>
            </p>
            <div class="aitem__tools">
              <?php if ($mine): ?>
                <a class="btn btn--small" href="/admin/article.php?slug=<?= e(rawurlencode($p['slug'])) ?>">Edit</a>
              <?php endif; ?>
              <?php if ($draft): ?>
                <a class="btn btn--ghost btn--small" href="<?= e(blog_url($p) . '?preview=' . blog_preview_token($p['slug'])) ?>" target="_blank" rel="noopener">Preview draft</a>
              <?php else: ?>
                <a class="btn btn--ghost btn--small" href="<?= e(blog_url($p)) ?>" target="_blank" rel="noopener">View</a>
              <?php endif; ?>
            </div>
            <?php if (!$mine): ?>
              <details class="aitem__cover">
                <summary>Change cover photo</summary>
                <form class="photo__form" method="post" action="/admin/article-save.php" enctype="multipart/form-data" data-upload data-max="<?= cms_upload_limit() ?>">
                  <?= csrf_field() ?><input type="hidden" name="action" value="cover_code"><input type="hidden" name="slug" value="<?= e($p['slug']) ?>">
                  <label class="file">Choose photo <input type="file" name="photo" accept="image/jpeg,image/png,image/webp" required></label>
                  <p class="file__info" hidden></p>
                  <label>Describe the photo (alt text) <span class="opt">required, 10 to 125 characters</span>
                    <textarea name="alt" rows="2" required minlength="10" maxlength="125" data-count><?= e($p['image_alt'] ?? '') ?></textarea></label>
                  <button class="btn btn--small" type="submit">Save cover photo</button>
                </form>
              </details>
            <?php endif; ?>
          </div>
        </li>
      <?php endforeach; ?>
    </ul>
  </section>
</main>
</div>
<script src="<?= e(asset('admin/admin.js')) ?>" defer></script>
</body>
</html>
