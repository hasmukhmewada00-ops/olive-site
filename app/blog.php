<?php
declare(strict_types=1);

/**
 * Flat-file blog. Two sources are merged:
 *   blog-content/posts.json + {slug}.html   guides written by OMM (in git)
 *   data/articles.json                      articles written in the admin (not in git)
 * The client can change the cover photo of any guide (data/cms.json > blog_covers)
 * and write, tag, publish and unpublish their own articles.
 */

const OLIVE_BLOG_DIR = OLIVE_ROOT . '/blog-content';
const OLIVE_ARTICLES_FILE = OLIVE_DATA . '/articles.json';

/** URL words that can never be an article slug. */
const OLIVE_RESERVED_SLUGS = ['tag', 'page', 'feed', 'admin', 'index', 'new'];

/** Categories shown above the headline. */
const OLIVE_BLOG_CATEGORIES = [
    'Wedding planning',
    'Pure Veg & Jain',
    'Corporate',
    'Family & social',
    'Menu ideas',
    'Behind the kitchen',
    'Planning guide',
];

/** Blog pages are visible on staging, and on live only once the site is launched. */
function blog_enabled(): bool
{
    return is_staging() || cfg('site_live') === true;
}

/** Articles written in the admin, as saved. */
function blog_client_articles(): array
{
    if (!is_file(OLIVE_ARTICLES_FILE)) {
        return [];
    }
    $list = json_decode((string) file_get_contents(OLIVE_ARTICLES_FILE), true);
    return is_array($list) ? array_values(array_filter($list, fn ($a) => is_array($a) && !empty($a['slug']))) : [];
}

/** Guides written by OMM, with any cover photo the client has changed. */
function blog_code_posts(): array
{
    $file = OLIVE_BLOG_DIR . '/posts.json';
    $list = is_file($file) ? (json_decode((string) file_get_contents($file), true) ?: []) : [];
    $covers = (array) (cms_overrides()['blog_covers'] ?? []);
    $out = [];
    foreach ($list as $p) {
        if (empty($p['slug']) || !is_file(OLIVE_BLOG_DIR . '/' . basename($p['slug']) . '.html')) {
            continue;
        }
        if (!empty($covers[$p['slug']]['src'])) {
            $c = $covers[$p['slug']];
            $p['image_src'] = $c['src'];
            $p['image_alt'] = $c['alt'] ?? ($p['image_alt'] ?? '');
            $p['og_src'] = $c['og'] ?? null;
        }
        $p['source'] = 'code';
        $out[] = $p;
    }
    return $out;
}

/** All posts, newest first. Drafts only on staging unless $withDrafts. */
function blog_posts(bool $withDrafts = false): array
{
    static $cache = [];
    $key = $withDrafts ? 'all' : 'pub';
    if (isset($cache[$key])) {
        return $cache[$key];
    }
    $list = array_merge(blog_code_posts(), array_map(fn ($a) => $a + ['source' => 'client'], blog_client_articles()));
    $show = $withDrafts || is_staging();
    $list = array_values(array_filter($list, fn ($p) => ($p['draft'] ?? false) !== true || $show));
    usort($list, fn ($a, $b) => strcmp((string) ($b['date'] ?? ''), (string) ($a['date'] ?? '')));
    return $cache[$key] = $list;
}

/** Secret preview token so a draft can be shared with the client (never indexed). */
function blog_preview_token(string $slug): string
{
    return substr(hash_hmac('sha256', 'preview:' . $slug, form_secret()), 0, 24);
}

function blog_post(string $slug, string $previewToken = ''): ?array
{
    foreach (blog_posts() as $p) {
        if ($p['slug'] === $slug) {
            return $p;
        }
    }
    if ($previewToken !== '' && hash_equals(blog_preview_token($slug), $previewToken)) {
        foreach (blog_posts(true) as $p) {
            if ($p['slug'] === $slug) {
                return $p;
            }
        }
    }
    return null;
}

function blog_body(array $post): string
{
    if (($post['source'] ?? '') === 'client') {
        return blog_markdown((string) ($post['body'] ?? ''));
    }
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
    if (!empty($post['image_src'])) {
        return preg_replace('/-\d+\.webp$/', '-' . $w . '.webp', (string) $post['image_src']) ?? (string) $post['image_src'];
    }
    return '/assets/img/photos/' . $post['image'] . '-' . $w . '.webp';
}

/** Absolute share-image URL: uploaded 1200x630 JPG, the supplied og file, or the cover. */
function blog_og_image(array $post): string
{
    if (!empty($post['og_src'])) {
        return canonical_url((string) $post['og_src']);
    }
    if (is_file(OLIVE_ROOT . '/assets/img/og/' . $post['slug'] . '.jpg')) {
        return canonical_url('/assets/img/og/' . $post['slug'] . '.jpg');
    }
    return canonical_url(blog_img($post, 1600));
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

// ---------------------------------------------------------------
// Tags
// ---------------------------------------------------------------
function blog_tag_slug(string $label): string
{
    $s = strtolower(trim((string) preg_replace('/[^A-Za-z0-9]+/', '-', $label), '-'));
    return substr($s, 0, 40);
}

/** [slug => label] for one post. */
function blog_post_tags(array $post): array
{
    $out = [];
    foreach ((array) ($post['tags'] ?? []) as $label) {
        $slug = blog_tag_slug((string) $label);
        if ($slug !== '') {
            $out[$slug] = (string) $label;
        }
    }
    return $out;
}

/** [slug => ['label' => ..., 'count' => n]] across published posts, most used first. */
function blog_all_tags(): array
{
    $all = [];
    foreach (blog_posts() as $p) {
        foreach (blog_post_tags($p) as $slug => $label) {
            $all[$slug] ??= ['label' => $label, 'count' => 0];
            $all[$slug]['count']++;
        }
    }
    uasort($all, fn ($a, $b) => $b['count'] <=> $a['count']);
    return $all;
}

function blog_tag_url(string $slug): string
{
    return '/blog/tag/' . $slug . '/';
}

/** Posts that share the most tags with $post, then newest. */
function blog_related(array $post, int $limit = 3): array
{
    $mine = array_keys(blog_post_tags($post));
    $others = array_values(array_filter(blog_posts(), fn ($p) => $p['slug'] !== $post['slug']));
    usort($others, function ($a, $b) use ($mine) {
        $sa = count(array_intersect($mine, array_keys(blog_post_tags($a))));
        $sb = count(array_intersect($mine, array_keys(blog_post_tags($b))));
        return $sb <=> $sa ?: strcmp((string) $b['date'], (string) $a['date']);
    });
    return array_slice($others, 0, $limit);
}

// ---------------------------------------------------------------
// Article body: a small, safe writing format
// ---------------------------------------------------------------
/** Inline formatting on text that has already been escaped: **bold** and [text](link). */
function blog_inline(string $text): string
{
    $t = htmlspecialchars($text, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    $t = preg_replace_callback(
        '/\[([^\]]{1,120})\]\((https?:\/\/[^\s)]{1,300}|\/(?!\/)[^\s)]{0,300})\)/',
        function ($m) {
            $url = $m[2];
            $external = $url[0] !== '/';
            return '<a href="' . $url . '"' . ($external ? ' rel="noopener" target="_blank"' : '') . '>' . $m[1] . '</a>';
        },
        $t
    ) ?? $t;
    return preg_replace('/\*\*(.+?)\*\*/s', '<strong>$1</strong>', $t) ?? $t;
}

/**
 * Turn the writing format into HTML. Supported:
 *   blank line = new paragraph (the first paragraph is the intro)
 *   ## Heading      ### Smaller heading
 *   - bullet        1. numbered
 *   **bold**        [link text](https://... or /#enquiry)
 * Everything else is escaped, so nothing the client types can break the page.
 */
function blog_markdown(string $md): string
{
    $lines = preg_split('/\R/u', trim($md)) ?: [];
    $html = '';
    $para = [];
    $list = null;       // 'ul' | 'ol' | null
    $first = true;      // first paragraph gets the intro style

    $flushPara = function () use (&$para, &$html, &$first) {
        if ($para) {
            $cls = $first ? ' class="post__lead"' : '';
            $html .= '<p' . $cls . '>' . blog_inline(implode(' ', $para)) . "</p>\n";
            $first = false;
            $para = [];
        }
    };
    $closeList = function () use (&$list, &$html) {
        if ($list) {
            $html .= '</' . $list . ">\n";
            $list = null;
        }
    };

    foreach ($lines as $line) {
        $line = rtrim($line);
        if ($line === '') {
            $flushPara();
            $closeList();
            continue;
        }
        if (preg_match('/^(#{2,3})\s+(.{1,140})$/u', $line, $m)) {
            $flushPara();
            $closeList();
            $tag = strlen($m[1]) === 2 ? 'h2' : 'h3';
            $html .= '<' . $tag . '>' . blog_inline(trim($m[2])) . '</' . $tag . ">\n";
            $first = false;
            continue;
        }
        if (preg_match('/^[-*]\s+(.+)$/u', $line, $m)) {
            $flushPara();
            if ($list !== 'ul') {
                $closeList();
                $html .= "<ul>\n";
                $list = 'ul';
            }
            $html .= '<li>' . blog_inline($m[1]) . "</li>\n";
            continue;
        }
        if (preg_match('/^\d+[.)]\s+(.+)$/u', $line, $m)) {
            $flushPara();
            if ($list !== 'ol') {
                $closeList();
                $html .= "<ol>\n";
                $list = 'ol';
            }
            $html .= '<li>' . blog_inline($m[1]) . "</li>\n";
            continue;
        }
        $closeList();
        $para[] = trim($line);
    }
    $flushPara();
    $closeList();
    return $html;
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
    $tags = array_values(blog_post_tags($post));
    $article = [
        '@type' => 'BlogPosting',
        '@id' => blog_url($post, true) . '#article',
        'headline' => $post['title'],
        'description' => $post['description'],
        'image' => blog_og_image($post),
        'datePublished' => $post['date'],
        'dateModified' => $post['updated'] ?? $post['date'],
        'author' => $org,
        'publisher' => $org,
        'mainEntityOfPage' => blog_url($post, true),
        'about' => $post['keyword'] ?? '',
        'inLanguage' => 'en-IN',
        'wordCount' => str_word_count(strip_tags(blog_body($post))),
    ];
    if (!empty($post['eyebrow'])) {
        $article['articleSection'] = $post['eyebrow'];
    }
    if ($tags) {
        $article['keywords'] = implode(', ', $tags);
    }
    $graph = [
        $article,
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
