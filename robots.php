<?php
declare(strict_types=1);
// Served at /robots.txt (see .htaccess). Staging blocks all crawlers.
define('OLIVE_SKIP_GATE', true);
require __DIR__ . '/app/bootstrap.php';

header('Content-Type: text/plain; charset=utf-8');

if (is_staging()) {
    echo "User-agent: *\nDisallow: /\n";
    exit;
}

echo "User-agent: *\n";
echo "Disallow: /admin/\n";
echo "Disallow: /enquiry.php\n";
echo "Disallow: /thank-you.php\n";
echo "\n";
if (cfg('site_live') === true) {
    echo 'Sitemap: ' . canonical_url('/sitemap.xml') . "\n";
}
// AI and answer-engine crawlers are welcome (GEO). They follow the rules above.
