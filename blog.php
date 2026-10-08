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
$tagSlug = preg_replace('/[^a-z0-9-]/', '', strtolower((string) ($_GET['tag'] ?? '')));
$allTags = blog_all_tags();
$tagLabel = '';
if ($tagSlug !== '') {
    if (!isset($allTags[$tagSlug])) {
        http_response_code(404);
        require __DIR__ . '/404.php';
        exit;
    }
    $tagLabel = $allTags[$tagSlug]['label'];
    $posts = array_values(array_filter($posts, fn ($p) => isset(blog_post_tags($p)[$tagSlug])));
}

$meta = [
    'title' => $tagLabel !== '' ? $tagLabel . ' | Olive Catering Company Blog' : 'Catering Guides for Weddings & Events in Gandhidham | Olive Catering Company',
    'robots' => $tagLabel !== '' ? 'noindex, follow' : '',
    'desc' => 'Practical guides from Olive Catering Company on planning pure veg and Jain catering for weddings, corporate events and celebrations in Gandhidham, Kutch and across Gujarat.',
    'canonical' => $tagLabel !== '' ? canonical_url(blog_tag_url($tagSlug)) : canonical_url('/blog/'),
    'schema' => $tagLabel !== '' ? '' : json_encode([
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
      <nav class="crumbs" aria-label="Breadcrumb"><a href="/">Home</a><span aria-hidden="true">/</span><?php if ($tagLabel !== ''): ?><a href="/blog/">Blog</a><span aria-hidden="true">/</span><span><?= e($tagLabel) ?></span><?php else: ?><span>Blog</span><?php endif; ?></nav>
      <header class="section__head">
        <p class="eyebrow">Olive journal</p>
        <?php if ($tagLabel !== ''): ?>
          <h1 class="h2 blogindex__title"><?= e($tagLabel) ?></h1>
          <p class="lead"><?= count($posts) ?> article<?= count($posts) === 1 ? '' : 's' ?> tagged <?= e($tagLabel) ?>. <a href="/blog/">See all articles</a></p>
        <?php else: ?>
          <h1 class="h2 blogindex__title">Catering guides for weddings and events in Gandhidham</h1>
          <p class="lead">Practical advice on planning pure veg and Jain catering for weddings, corporate events and family celebrations in Gandhidham, Kutch and across Gujarat.</p>
        <?php endif; ?>
      </header>
      <?php if (count($allTags) > 1): ?>
        <ul class="tags tags--filter" aria-label="Browse by topic">
          <li><a href="/blog/"<?= $tagLabel === '' ? ' aria-current="page"' : '' ?>>All</a></li>
          <?php foreach ($allTags as $ts => $t): ?><li><a href="<?= e(blog_tag_url($ts)) ?>"<?= $ts === $tagSlug ? ' aria-current="page"' : '' ?>><?= e($t['label']) ?></a></li><?php endforeach; ?>
        </ul>
      <?php endif; ?>
      <?php $cards = $posts; require OLIVE_ROOT . '/partials/blog-cards.php'; ?>
    </div>
  </section>
</main>
<?php require OLIVE_ROOT . '/partials/site-footer.php'; ?>
