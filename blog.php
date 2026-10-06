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

$page_type = 'blog_index';
$base = '/';
$posts = blog_posts();

$meta = [
    'title' => 'Catering Guides for Weddings & Events in Gandhidham | Olive Catering Company',
    'desc' => 'Practical guides from Olive Catering Company on planning pure veg and Jain catering for weddings, corporate events and celebrations in Gandhidham, Kutch and across Gujarat.',
    'canonical' => canonical_url('/blog/'),
    'schema' => json_encode([
        '@context' => 'https://schema.org',
        '@graph' => [
            [
                '@type' => 'Blog',
                '@id' => canonical_url('/blog/#blog'),
                'name' => 'Olive Catering Company Blog',
                'url' => canonical_url('/blog/'),
                'publisher' => ['@id' => canonical_url('/#business')],
                'blogPost' => array_map(fn ($p) => ['@type' => 'BlogPosting', 'headline' => $p['title'], 'url' => blog_url($p, true), 'datePublished' => $p['date']], $posts),
            ],
            [
                '@type' => 'BreadcrumbList',
                'itemListElement' => [
                    ['@type' => 'ListItem', 'position' => 1, 'name' => 'Home', 'item' => canonical_url('/')],
                    ['@type' => 'ListItem', 'position' => 2, 'name' => 'Blog', 'item' => canonical_url('/blog/')],
                ],
            ],
        ],
    ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG),
];
require OLIVE_ROOT . '/partials/site-head.php';
require OLIVE_ROOT . '/partials/site-header.php';
?>
<main id="main">
  <section class="section blogindex">
    <div class="wrap">
      <nav class="crumbs" aria-label="Breadcrumb"><a href="/">Home</a><span aria-hidden="true">/</span><span>Blog</span></nav>
      <header class="section__head">
        <p class="eyebrow">Olive journal</p>
        <h1 class="h2 blogindex__title">Catering guides for weddings and events in Gandhidham</h1>
        <p class="lead">Practical advice on planning pure veg and Jain catering for weddings, corporate events and family celebrations in Gandhidham, Kutch and across Gujarat.</p>
      </header>
      <?php $cards = $posts; require OLIVE_ROOT . '/partials/blog-cards.php'; ?>
    </div>
  </section>
</main>
<?php require OLIVE_ROOT . '/partials/site-footer.php'; ?>
