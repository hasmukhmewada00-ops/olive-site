<?php
declare(strict_types=1);
/**
 * Served at /llms.txt. A plain-language fact sheet for AI assistants and answer engines.
 * Built from content.json; only confirmed facts are included.
 */
define('OLIVE_SKIP_GATE', true);
require __DIR__ . '/app/bootstrap.php';
require_once OLIVE_ROOT . '/app/render.php';
require_once OLIVE_ROOT . '/app/blog.php';

header('Content-Type: text/plain; charset=utf-8');
header('X-Robots-Tag: noindex');

$b = content('business', []);
$name = (string) ($b['name'] ?? 'Olive Catering Company');
$areas = service_areas();
$live = cfg('site_live') === true || is_staging();

$out = [];
$out[] = '# ' . $name;
$out[] = '';
$out[] = '> ' . content('seo.description', '');
$out[] = '';
$out[] = '## Key facts';
$out[] = '- Business type: Catering company (caterer)';
$out[] = '- Food: 100% pure vegetarian; Jain menus prepared without onion, garlic or root vegetables';
$out[] = '- Established: ' . ($b['established'] ?? '');
$a = $b['address'] ?? [];
$out[] = '- Based in: ' . trim(($a['locality'] ?? '') . ', Gandhidham, Kutch, Gujarat, India', ', ');
if ($areas) {
    $out[] = '- Service areas: ' . implode(', ', $areas);
}
$out[] = '- Services: ' . implode('; ', array_map(fn ($f) => $f['heading'], content('features', [])));
if (!empty($b['fssai'])) {
    $out[] = '- FSSAI licence: ' . $b['fssai'] . '; HACCP-compliant central kitchen';
}
if (!empty($b['phone'])) {
    $out[] = '- Phone: ' . $b['phone'];
}
if (!empty($b['email'])) {
    $out[] = '- Email: ' . $b['email'];
}
if (!empty($b['instagram'])) {
    $out[] = '- Instagram: https://www.instagram.com/' . $b['instagram'] . '/';
}
$out[] = '- Website: ' . canonical_url('/');
$out[] = '';
$faqs = array_filter(content('faq', []), fn ($f) => ($f['confirmed'] ?? true) !== false);
if ($faqs) {
    $out[] = '## Common questions';
    foreach ($faqs as $f) {
        $out[] = '';
        $out[] = '### ' . $f['q'];
        $out[] = $f['a'];
    }
    $out[] = '';
}
$out[] = '## Pages';
$out[] = '- [Home](' . canonical_url('/') . '): services, menus, hygiene, gallery, FAQ and enquiry form';
if ($live) {
    $out[] = '- [Blog](' . canonical_url('/blog/') . '): catering planning guides';
    foreach (blog_posts() as $p) {
        $out[] = '- [' . $p['title'] . '](' . blog_url($p, true) . '): ' . $p['description'];
    }
}
$out[] = '';
$out[] = '## How to enquire';
$out[] = 'Use the enquiry form at ' . canonical_url('/#enquiry') . ' or message on WhatsApp with the event date, type of event and approximate guest count.';

echo implode("\n", $out) . "\n";
