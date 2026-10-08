<?php
declare(strict_types=1);
require __DIR__ . '/_init.php';
admin_require();

$isNew = isset($_GET['new']);
$slugQ = preg_replace('/[^a-z0-9-]/', '', (string) ($_GET['slug'] ?? ''));
$existing = null;
if (!$isNew) {
    foreach (blog_client_articles() as $a) {
        if ($a['slug'] === $slugQ) {
            $existing = $a;
        }
    }
    if (!$existing) {
        flash('That article was not found, or it is an Olive guide. For guides you can change the cover photo from the list.', 'bad');
        header('Location: /admin/articles.php', true, 303);
        exit;
    }
}

// Re-show what was typed after a failed save.
admin_session();
$kept = $_SESSION['article_form'] ?? null;
unset($_SESSION['article_form']);
$d = $existing ?? [];
if ($kept && (($kept['new'] ?? false) === $isNew) && (($kept['slug'] ?? '') === ($existing['slug'] ?? ''))) {
    $d = array_merge($d, $kept['data']);
    $d['tags'] = $kept['data']['tags'];
}
$tagsText = is_array($d['tags'] ?? null) ? implode(', ', $d['tags']) : (string) ($d['tags'] ?? '');
$published = $existing && ($existing['draft'] ?? true) === false;
$flash = flash();
$host = (string) ($_SERVER['HTTP_HOST'] ?? '');
$faq = array_values($d['faq'] ?? []);
$hasCover = !empty($existing['image_src']);
$title = $isNew ? 'New article' : 'Edit article';
?><!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="robots" content="noindex, nofollow">
<title><?= e($title) ?> | Olive admin</title>
<link rel="icon" href="/assets/img/favicon.svg" type="image/svg+xml">
<link rel="stylesheet" href="<?= e(asset('admin/admin.css')) ?>">
</head>
<body>
<header class="bar">
  <div class="bar__in">
    <span class="bar__logo"><?= logo_svg('olive-logo') ?></span>
    <span class="bar__site"><?= e($host) ?><?= is_staging() ? ' <em>staging</em>' : '' ?></span>
    <nav class="bar__links"><a href="/admin/">Site content</a><a href="/admin/articles.php">Articles</a><a href="/" target="_blank" rel="noopener">View site</a><a href="/admin/logout.php">Log out</a></nav>
  </div>
</header>

<div class="layout layout--editor">
<main class="main">
  <?php if ($flash): ?><p class="alert alert--<?= e($flash['type']) ?>" role="status"><?= e($flash['msg']) ?></p><?php endif; ?>
  <p class="crumb"><a href="/admin/articles.php">&larr; All articles</a></p>

  <form method="post" action="/admin/article-save.php" enctype="multipart/form-data" data-article-form data-upload data-max="<?= cms_upload_limit() ?>" data-host="<?= e(canonical_url('/')) ?>">
    <?= csrf_field() ?>
    <input type="hidden" name="is_new" value="<?= $isNew ? '1' : '0' ?>">
    <input type="hidden" name="orig_slug" value="<?= e($existing['slug'] ?? '') ?>">

    <section class="card">
      <h2><?= e($title) ?>
        <?php if ($existing): ?><span class="pill <?= $published ? 'pill--live' : 'pill--draft' ?>"><?= $published ? 'Published' : 'Draft' ?></span><?php endif; ?>
      </h2>

      <label>Title <span class="opt">what readers see. 40 to 65 characters is best</span>
        <input name="title" value="<?= e($d['title'] ?? '') ?>" maxlength="90" required data-count data-field="title" placeholder="e.g. How to Plan a Jain Wedding Menu in Gandhidham"></label>

      <div class="grid2">
        <label>Category
          <select name="eyebrow">
            <?php foreach (OLIVE_BLOG_CATEGORIES as $c): ?><option<?= ($d['eyebrow'] ?? 'Planning guide') === $c ? ' selected' : '' ?>><?= e($c) ?></option><?php endforeach; ?>
          </select>
        </label>
        <label>Tags <span class="opt">comma separated, 1 to 6</span>
          <input name="tags" value="<?= e($tagsText) ?>" data-field="tags" placeholder="Wedding catering, Jain catering, Gandhidham"></label>
      </div>
      <?php if (($all = blog_all_tags())): ?>
        <p class="muted small">Tags already in use (click to add):
          <?php foreach ($all as $t): ?><button type="button" class="chip chip--btn" data-add-tag="<?= e($t['label']) ?>"><?= e($t['label']) ?></button><?php endforeach; ?>
        </p>
      <?php endif; ?>

      <label>Cover photo <span class="opt"><?= $hasCover ? 'leave empty to keep the current one' : 'required to publish' ?>. JPG, PNG or WebP, at least 800 px wide, up to <?= e(cms_limit_mb()) ?></span>
        <input type="file" name="photo" accept="image/jpeg,image/png,image/webp" data-field="photo"></label>
      <p class="file__info" hidden></p>
      <?php if ($hasCover): ?>
        <div class="cover"><img src="<?= e(thumb(['src' => $existing['image_src']])) ?>" alt="<?= e($existing['image_alt'] ?? '') ?>" width="240" height="180"></div>
      <?php endif; ?>
      <label>Describe the cover photo (alt text) <span class="opt">required, 10 to 125 characters. Say what is in the photo</span>
        <textarea name="image_alt" rows="2" maxlength="125" data-count data-field="alt"><?= e($d['image_alt'] ?? '') ?></textarea></label>
    </section>

    <section class="card">
      <h2>Write the article</h2>
      <details class="help">
        <summary>How to format (tap to open)</summary>
        <ul>
          <li>Leave a <strong>blank line</strong> between paragraphs. The first paragraph is the introduction, shown larger.</li>
          <li><code>## Heading</code> for a main heading, <code>### Heading</code> for a smaller one.</li>
          <li><code>- item</code> for bullet points, <code>1. item</code> for numbered steps.</li>
          <li><code>**bold**</code> for bold words. <code>[text](https://example.com)</code> for a link.</li>
          <li>Link to our own pages so Google finds them: <code>[wedding catering](/#weddings)</code>, <code>[corporate catering](/#corporate)</code>, <code>[get a quote](/#enquiry)</code>, <code>[Jain guide](/blog/jain-catering-guide-gandhidham/)</code></li>
          <li>Aim for 600 words or more, with at least two <code>##</code> headings. Write about real questions clients ask.</li>
        </ul>
      </details>
      <label class="sr-only" for="f-body">Article text</label>
      <textarea id="f-body" name="body" rows="24" data-field="body" placeholder="Write your introduction here.&#10;&#10;## First heading&#10;&#10;Your paragraph..."><?= e($d['body'] ?? '') ?></textarea>
      <p class="muted small" data-words>0 words</p>
    </section>

    <section class="card">
      <h2>Questions and answers <span class="opt">optional. Can appear in Google results</span></h2>
      <?php for ($i = 0; $i < max(count($faq) + 1, 3); $i++): $r = $faq[$i] ?? []; if ($i >= 6) break; ?>
        <fieldset class="review">
          <legend>Question <?= $i + 1 ?></legend>
          <label>Question <input name="faq[<?= $i ?>][q]" value="<?= e($r['q'] ?? '') ?>" maxlength="140" data-faq></label>
          <label>Answer <textarea name="faq[<?= $i ?>][a]" rows="2" maxlength="600"><?= e($r['a'] ?? '') ?></textarea></label>
        </fieldset>
      <?php endfor; ?>
    </section>

    <section class="card">
      <h2>Google search settings</h2>
      <label>Search description <span class="opt">70 to 160 characters. This is the text under the title on Google</span>
        <textarea name="description" rows="3" maxlength="200" data-count data-field="description"><?= e($d['description'] ?? '') ?></textarea></label>
      <div class="grid2">
        <label>Main keyword <span class="opt">what people would search</span>
          <input name="keyword" value="<?= e($d['keyword'] ?? '') ?>" maxlength="60" data-field="keyword" placeholder="e.g. Jain caterers Gandhidham"></label>
        <label>Search title <span class="opt">optional, up to 60 characters. Blank = the title above</span>
          <input name="seo_title" value="<?= e($d['seo_title'] ?? '') ?>" maxlength="70" data-count data-field="seo_title"></label>
      </div>
      <label>Web address (URL) <span class="opt"><?= $published ? 'locked after publishing so links keep working' : 'made from the title. Lowercase words and dashes' ?></span>
        <input name="slug" value="<?= e($d['slug'] ?? ($existing['slug'] ?? '')) ?>" maxlength="70" data-field="slug"<?= $published ? ' readonly' : '' ?>></label>

      <div class="serp" aria-label="How it may look on Google">
        <p class="serp__url" data-serp-url></p>
        <p class="serp__title" data-serp-title></p>
        <p class="serp__desc" data-serp-desc></p>
      </div>

      <h3>SEO checklist</h3>
      <ul class="checks" data-checks></ul>
    </section>

    <div class="actions">
      <?php if ($published): ?>
        <button class="btn" type="submit" name="action" value="save">Save changes</button>
        <a class="btn btn--ghost" href="<?= e(blog_url($existing)) ?>" target="_blank" rel="noopener">View article</a>
      <?php else: ?>
        <button class="btn" type="submit" name="action" value="publish">Publish</button>
        <button class="btn btn--ghost" type="submit" name="action" value="save">Save as draft</button>
        <?php if ($existing): ?><a class="btn btn--ghost" href="<?= e(blog_url($existing) . '?preview=' . blog_preview_token($existing['slug'])) ?>" target="_blank" rel="noopener">Preview draft</a><?php endif; ?>
      <?php endif; ?>
    </div>
  </form>

  <?php if ($existing): ?>
    <div class="actions actions--sub">
      <?php if ($published): ?>
        <form method="post" action="/admin/article-save.php" data-confirm="Make this article private again? It will disappear from the blog and from Google.">
          <?= csrf_field() ?><input type="hidden" name="action" value="unpublish"><input type="hidden" name="slug" value="<?= e($existing['slug']) ?>">
          <button class="btn btn--ghost btn--small" type="submit">Unpublish (back to draft)</button>
        </form>
      <?php endif; ?>
      <form method="post" action="/admin/article-save.php" data-confirm="Delete this article? A backup copy is kept for recovery.">
        <?= csrf_field() ?><input type="hidden" name="action" value="delete"><input type="hidden" name="slug" value="<?= e($existing['slug']) ?>">
        <button class="btn btn--danger btn--small" type="submit">Delete article</button>
      </form>
    </div>
  <?php endif; ?>
</main>
</div>
<script src="<?= e(asset('admin/admin.js')) ?>" defer></script>
<script src="<?= e(asset('admin/article.js')) ?>" defer></script>
</body>
</html>
