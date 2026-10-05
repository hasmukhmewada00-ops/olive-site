<?php
declare(strict_types=1);
require __DIR__ . '/app/bootstrap.php';
require_once OLIVE_ROOT . '/app/render.php';
require_once OLIVE_ROOT . '/app/leads.php';
require_once OLIVE_ROOT . '/app/blog.php';

if (!blog_enabled()) {
    header('Location: /', true, 302);
    exit;
}

$slug = preg_replace('/[^a-z0-9-]/', '', strtolower((string) ($_GET['slug'] ?? '')));
$post = $slug !== '' ? blog_post($slug) : null;
if (!$post) {
    http_response_code(404);
    require __DIR__ . '/404.php';
    exit;
}

$page_type = 'blog_post';
$base = '/';
$related = array_values(array_filter(blog_posts(), fn ($p) => $p['slug'] !== $post['slug']));
$waBlog = 'Hi Olive, I read your article "' . $post['title'] . '" and would like a catering quote. (via website)';

$meta = [
    'title' => $post['seo_title'] ?? ($post['title'] . ' | Olive Catering Company'),
    'desc' => $post['description'],
    'canonical' => blog_url($post, true),
    'og_type' => 'article',
    'og_image' => canonical_url('/assets/img/og/' . $post['slug'] . '.jpg'),
    'schema' => blog_schema($post),
];
require OLIVE_ROOT . '/partials/site-head.php';
require OLIVE_ROOT . '/partials/site-header.php';
?>
<main id="main">
  <article class="post">
    <div class="wrap post__wrap">
      <nav class="crumbs" aria-label="Breadcrumb"><a href="/">Home</a><span aria-hidden="true">/</span><a href="/blog/">Blog</a><span aria-hidden="true">/</span><span><?= e($post['short'] ?? $post['title']) ?></span></nav>
      <header class="post__head">
        <p class="eyebrow"><?= e($post['eyebrow'] ?? 'Planning guide') ?></p>
        <h1 class="post__title"><?= e($post['title']) ?></h1>
        <p class="post__meta">By Olive Catering Company &middot; <time datetime="<?= e($post['date']) ?>"><?= e(blog_date((string) $post['date'])) ?></time> &middot; <?= blog_read_minutes($post) ?> min read</p>
      </header>
      <figure class="post__hero">
        <img src="<?= e(blog_img($post, 1600)) ?>" srcset="<?= e(blog_img($post, 960)) ?> 960w, <?= e(blog_img($post, 1600)) ?> 1600w" sizes="(min-width: 900px) 860px, 94vw" width="1600" height="1200" alt="<?= e($post['image_alt'] ?? '') ?>" fetchpriority="high">
      </figure>
      <div class="prose">
        <?= blog_body($post) /* trusted HTML from blog-content/ */ ?>
      </div>

      <?php if (!empty($post['faq'])): ?>
      <section class="post__faq faq">
        <h2 class="h2">Frequently asked questions</h2>
        <div class="faq__list">
          <?php foreach ($post['faq'] as $f): ?>
            <details><summary><?= e($f['q']) ?></summary><p><?= e($f['a']) ?></p></details>
          <?php endforeach; ?>
        </div>
      </section>
      <?php endif; ?>

      <aside class="post__cta" data-track-location="blog_cta">
        <p class="eyebrow">Olive Catering Company</p>
        <h2 class="post__cta-title">Planning an event in Gandhidham?</h2>
        <p>Tell us your date, type of event and guest count. We will call you back with menu ideas and a quote.</p>
        <div class="post__cta-actions">
          <a class="btn btn--cta" href="/#enquiry">Get a quote</a>
          <a class="btn btn--line" href="<?= e(wa_href($waBlog)) ?>"<?= str_starts_with(wa_href($waBlog), 'http') ? ' target="_blank" rel="noopener"' : '' ?>>WhatsApp us</a>
        </div>
      </aside>
    </div>
  </article>

  <?php if ($related): ?>
  <section class="section bloglist bloglist--related">
    <div class="wrap">
      <header class="section__head"><h2 class="h2">More planning guides</h2></header>
      <?php $cards = array_slice($related, 0, 3); require OLIVE_ROOT . '/partials/blog-cards.php'; ?>
    </div>
  </section>
  <?php endif; ?>
</main>
<?php require OLIVE_ROOT . '/partials/site-footer.php'; ?>
