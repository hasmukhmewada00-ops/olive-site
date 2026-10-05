<?php
declare(strict_types=1);

/**
 * Flat-file blog. Posts live in blog-content/:
 *   posts.json    list of post metadata (newest first after sorting)
 *   {slug}.html   post body (trusted HTML written by OMM, not client-editable)
 */

const OLIVE_BLOG_DIR = OLIVE_ROOT . '/blog-content';

/** Blog pages are visible on staging, and on live only once the site is launched. */
function blog_enabled(): bool
{
    return is_staging() || cfg('site_live') === true;
}

function blog_posts(): array
{
    static $posts = null;
    if ($posts !== null) {
        return $posts;
    }
    $file = OLIVE_BLOG_DIR . '/posts.json';
    $list = is_file($file) ? (json_decode((string) file_get_contents($file), true) ?: []) : [];
    $list = array_values(array_filter($list, fn ($p) => !empty($p['slug']) && is_file(OLIVE_BLOG_DIR . '/' . basename($p['slug']) . '.html')
        && (($p['draft'] ?? false) !== true || is_staging())));
    usort($list, fn ($a, $b) => strcmp((string) ($b['date'] ?? ''), (string) ($a['date'] ?? '')));
    return $posts = $list;
}

function blog_post(string $slug): ?array
{
    foreach (blog_posts() as $p) {
        if ($p['slug'] === $slug) {
            return $p;
        }
    }
    return null;
}

function blog_body(array $post): string
{
    return (string) file_get_contents(OLIVE_BLOG_DIR . '/' . basename($post['slug']) . '.html');
}

function blog_url(array $post, bool $absolute = false): string
{
    $path = '/blog/' . $post['slug'] . '/';
    return $absolute ? canonical_url($path) : $path;
}

/** Image src for a post at a given width (480/960/1600). */
function blog_img(array $post, int $w = 960): string
{
    return '/assets/img/photos/' . $post['image'] . '-' . $w . '.webp';
}

function blog_read_minutes(array $post): int
{
    $words = str_word_count(strip_tags(blog_body($post)));
    return max(2, (int) round($words / 200));
}

function blog_date(string $ymd): string
{
    $d = DateTimeImmutable::createFromFormat('!Y-m-d', $ymd);
    return $d ? $d->format('j M Y') : $ymd;
}

/** BlogPosting + BreadcrumbList (+ FAQPage) structured data for one post. */
function blog_schema(array $post): string
{
    $org = [
        '@type' => 'Organization',
        '@id' => canonical_url('/#business'),
        'name' => (string) content('business.name', 'Olive Catering Company'),
        'url' => canonical_url('/'),
        'logo' => ['@type' => 'ImageObject', 'url' => canonical_url('/assets/img/og-image.jpg')],
    ];
    $graph = [
        [
            '@type' => 'BlogPosting',
            '@id' => blog_url($post, true) . '#article',
            'headline' => $post['title'],
            'description' => $post['description'],
            'image' => canonical_url(blog_img($post, 1600)),
            'datePublished' => $post['date'],
            'dateModified' => $post['updated'] ?? $post['date'],
            'author' => $org,
            'publisher' => $org,
            'mainEntityOfPage' => blog_url($post, true),
            'about' => $post['keyword'] ?? '',
            'inLanguage' => 'en-IN',
        ],
        [
            '@type' => 'BreadcrumbList',
            'itemListElement' => [
                ['@type' => 'ListItem', 'position' => 1, 'name' => 'Home', 'item' => canonical_url('/')],
                ['@type' => 'ListItem', 'position' => 2, 'name' => 'Blog', 'item' => canonical_url('/blog/')],
                ['@type' => 'ListItem', 'position' => 3, 'name' => $post['title'], 'item' => blog_url($post, true)],
            ],
        ],
    ];
    if (!empty($post['faq'])) {
        $graph[] = [
            '@type' => 'FAQPage',
            'mainEntity' => array_map(fn ($f) => [
                '@type' => 'Question',
                'name' => $f['q'],
                'acceptedAnswer' => ['@type' => 'Answer', 'text' => $f['a']],
            ], $post['faq']),
        ];
    }
    return json_encode(['@context' => 'https://schema.org', '@graph' => $graph], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG);
}
