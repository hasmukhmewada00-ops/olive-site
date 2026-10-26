<?php
declare(strict_types=1);
require __DIR__ . '/app/bootstrap.php';
require_once OLIVE_ROOT . '/app/render.php';
require_once OLIVE_ROOT . '/app/leads.php';
require_once OLIVE_ROOT . '/app/blog.php';
require_once OLIVE_ROOT . '/app/services.php';

$slug = preg_replace('/[^a-z0-9-]/', '', strtolower((string) ($_GET['slug'] ?? '')));
$page = $slug !== '' ? service_page($slug) : null;
if (!$page) {
    http_response_code(404);
    require __DIR__ . '/404.php';
    exit;
}

$page_type = 'service';
$base = '/';
$wa = $page['wa'];
$waH = wa_href($wa);
$waExt = str_starts_with($waH, 'http') ? ' target="_blank" rel="noopener"' : '';
$posts = [];
foreach ($page['posts'] as $ps) {
    $p = blog_post($ps);
    if ($p) {
        $posts[] = $p;
    }
}

$meta = [
    'title' => $page['seo_title'],
    'desc' => $page['description'],
    'canonical' => service_url($slug, true),
    'og_image' => canonical_url(service_img($page, 1600)),
    'schema' => service_schema($page),
];
require OLIVE_ROOT . '/partials/site-head.php';
require OLIVE_ROOT . '/partials/site-header.php';
?>
<main id="main">
  <article class="post">
    <div class="wrap post__wrap">
      <nav class="crumbs" aria-label="Breadcrumb"><a href="/">Home</a><span aria-hidden="true">/</span><a href="/#services">Services</a><span aria-hidden="true">/</span><span><?= e($page['nav']) ?></span></nav>
      <header class="post__head">
        <p class="eyebrow"><?= e($page['eyebrow']) ?></p>
        <h1 class="post__title"><?= e($page['title']) ?></h1>
      </header>
      <figure class="post__hero">
        <img src="<?= e(service_img($page, 1600)) ?>" srcset="<?= e(service_img($page, 960)) ?> 960w, <?= e(service_img($page, 1600)) ?> 1600w" sizes="(min-width: 900px) 860px, 94vw" width="1600" height="1200" alt="<?= e($page['image_alt']) ?>" fetchpriority="high">
      </figure>
      <div class="prose">
        <p class="post__lead"><?= e($page['lead']) ?></p>
        <?= $page['body'] /* trusted HTML from app/services.php */ ?>
      </div>

      <aside class="post__cta" data-track-location="service_cta">
        <p class="eyebrow">Olive Catering Company</p>
        <h2 class="post__cta-title">Get a quote for your event</h2>
        <p>Tell us your date, type of event and guest count. We will call you back with menu ideas and a quote.</p>
        <div class="post__cta-actions">
          <a class="btn btn--cta" href="/#enquiry">Get a quote</a>
          <a class="btn btn--line" href="<?= e($waH) ?>"<?= $waExt ?>>WhatsApp us</a>
        </div>
      </aside>

      <section class="post__faq faq">
        <h2 class="h2">Frequently asked questions</h2>
        <div class="faq__list">
          <?php foreach ($page['faq'] as $f): ?>
            <details><summary><?= e($f['q']) ?></summary><p><?= e($f['a']) ?></p></details>
          <?php endforeach; ?>
        </div>
      </section>

      <nav class="prose" style="margin-top:44px" aria-label="Related services">
        <h2>Related services</h2>
        <ul>
          <?php foreach ($page['related'] as $rs): $rp = service_page($rs); if ($rp): ?>
            <li><a href="<?= e(service_url($rs)) ?>"><?= e($rp['title']) ?></a></li>
          <?php endif; endforeach; ?>
        </ul>
      </nav>
    </div>
  </article>

  <?php if ($posts): ?>
  <section class="section bloglist bloglist--related">
    <div class="wrap">
      <header class="section__head"><h2 class="h2">Planning guides</h2></header>
      <?php $related = $posts; $cards = $posts; require OLIVE_ROOT . '/partials/blog-cards.php'; ?>
    </div>
  </section>
  <?php endif; ?>
</main>
<?php require OLIVE_ROOT . '/partials/site-footer.php'; ?>
