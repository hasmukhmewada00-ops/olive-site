<?php
declare(strict_types=1);
/** Served at /sitemap.xml. Lists only pages that are public on this host. */
define('OLIVE_SKIP_GATE', true);
require __DIR__ . '/app/bootstrap.php';
require_once OLIVE_ROOT . '/app/blog.php';

header('Content-Type: application/xml; charset=utf-8');

$homeMod = date('Y-m-d', max(
    (int) filemtime(OLIVE_ROOT . '/content.sample.json'),
    is_file(OLIVE_CMS_FILE) ? (int) filemtime(OLIVE_CMS_FILE) : 0,
    (int) filemtime(OLIVE_ROOT . '/app/templates/home.php')
));

$urls = [['loc' => canonical_url('/'), 'lastmod' => $homeMod]];
if (blog_enabled()) {
    $posts = blog_posts();
    $urls[] = ['loc' => canonical_url('/blog/'), 'lastmod' => $posts ? ($posts[0]['updated'] ?? $posts[0]['date']) : $homeMod];
    foreach ($posts as $p) {
        $urls[] = ['loc' => blog_url($p, true), 'lastmod' => $p['updated'] ?? $p['date']];
    }
}

echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
foreach ($urls as $u) {
    echo '  <url><loc>' . htmlspecialchars($u['loc'], ENT_XML1) . '</loc><lastmod>' . htmlspecialchars((string) $u['lastmod'], ENT_XML1) . '</lastmod></url>' . "\n";
}
echo '</urlset>' . "\n";
