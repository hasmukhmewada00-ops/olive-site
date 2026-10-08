<?php
declare(strict_types=1);
/** Create, update, publish, unpublish and delete articles; change the cover of an Olive guide. */
require __DIR__ . '/_init.php';
admin_require();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && empty($_POST) && (int) ($_SERVER['CONTENT_LENGTH'] ?? 0) > 0) {
    flash('That photo is too large for the server. Please use one under ' . cms_limit_mb() . '.', 'bad');
    header('Location: /admin/articles.php', true, 303);
    exit;
}
csrf_check();

$p = $_POST;
$action = (string) ($p['action'] ?? '');
$file = $_FILES['photo'] ?? null;
$hasFile = is_array($file) && (int) ($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE;

function go(string $url): never
{
    header('Location: ' . $url, true, 303);
    exit;
}

try {
    // ---------- change the cover photo of an Olive guide ----------
    if ($action === 'cover_code') {
        $slug = preg_replace('/[^a-z0-9-]/', '', (string) ($p['slug'] ?? ''));
        $post = null;
        foreach (blog_code_posts() as $c) {
            if ($c['slug'] === $slug) {
                $post = $c;
            }
        }
        if (!$post) {
            throw new RuntimeException('That article was not found.');
        }
        $alt = clean($p['alt'] ?? '', 125);
        if (($e = alt_error($alt)) !== '') {
            throw new RuntimeException($e);
        }
        if (!$hasFile) {
            throw new RuntimeException('Choose a photo to upload.');
        }
        $img = cms_process_image($file, $alt, false, true);
        $ov = cms_overrides();
        $ov['blog_covers'][$slug] = ['src' => $img['src'], 'alt' => $alt, 'og' => $img['og'] ?? null];
        cms_save($ov);
        flash('Cover photo changed. It is live now, including the share picture for WhatsApp.');
        go('/admin/articles.php#a-' . $slug);
    }

    $list = blog_client_articles();
    $indexOf = function (string $slug) use ($list): ?int {
        foreach ($list as $i => $a) {
            if ($a['slug'] === $slug) {
                return $i;
            }
        }
        return null;
    };

    // ---------- delete ----------
    if ($action === 'delete') {
        $i = $indexOf((string) ($p['slug'] ?? ''));
        if ($i === null) {
            throw new RuntimeException('That article was not found.');
        }
        array_splice($list, $i, 1);
        cms_articles_save($list);
        flash('Article deleted. A backup copy is kept in Backups.');
        go('/admin/articles.php');
    }

    // ---------- unpublish ----------
    if ($action === 'unpublish') {
        $i = $indexOf((string) ($p['slug'] ?? ''));
        if ($i === null) {
            throw new RuntimeException('That article was not found.');
        }
        $list[$i]['draft'] = true;
        cms_articles_save($list);
        flash('Article moved back to draft. It is no longer public.');
        go('/admin/article.php?slug=' . rawurlencode($list[$i]['slug']));
    }

    if ($action !== 'save' && $action !== 'publish') {
        throw new RuntimeException('Unknown action.');
    }

    // ---------- save / publish ----------
    $isNew = ($p['is_new'] ?? '') === '1';
    $origSlug = $isNew ? '' : (string) ($p['orig_slug'] ?? '');
    $i = $isNew ? null : $indexOf($origSlug);
    if (!$isNew && $i === null) {
        throw new RuntimeException('That article was not found. It may have been deleted.');
    }
    $old = $i !== null ? $list[$i] : [];
    $wasPublished = $i !== null && ($old['draft'] ?? true) === false;
    $publish = $action === 'publish' || ($wasPublished && $action === 'save');

    $title = clean($p['title'] ?? '', 90);
    $slug = $wasPublished ? $origSlug : strtolower(clean($p['slug'] ?? '', 70));
    if ($slug === '' && $title !== '') {
        $slug = slugify($title, 60);
    }
    $seoTitle = clean($p['seo_title'] ?? '', 70);
    $desc = clean($p['description'] ?? '', 200);
    $keyword = clean($p['keyword'] ?? '', 60);
    $category = (string) ($p['eyebrow'] ?? '');
    $category = in_array($category, OLIVE_BLOG_CATEGORIES, true) ? $category : 'Planning guide';
    $body = str_replace(["\r\n", "\r"], "\n", (string) ($p['body'] ?? ''));
    $body = trim(preg_replace('/[^\P{C}\n\t]+/u', '', $body) ?? '');
    if (mb_strlen($body) > 40000) {
        throw new RuntimeException('The article is too long (limit 40,000 characters).');
    }

    $tags = [];
    foreach (preg_split('/[,\n]+/', (string) ($p['tags'] ?? '')) ?: [] as $t) {
        $t = clean($t, 30);
        if ($t !== '' && blog_tag_slug($t) !== '' && !isset($tags[blog_tag_slug($t)])) {
            $tags[blog_tag_slug($t)] = $t;
        }
    }
    $tags = array_slice(array_values($tags), 0, 6);

    $faq = [];
    foreach ((array) ($p['faq'] ?? []) as $r) {
        $q = clean($r['q'] ?? '', 140);
        $a = clean($r['a'] ?? '', 600);
        if ($q !== '' && $a !== '') {
            $faq[] = ['q' => $q, 'a' => $a];
        }
    }
    $faq = array_slice($faq, 0, 6);

    // Cover photo: keep the old one unless a new file is uploaded.
    $coverAlt = clean($p['image_alt'] ?? '', 125);
    $cover = ['src' => $old['image_src'] ?? '', 'og' => $old['og_src'] ?? null];
    if ($hasFile) {
        if (($e = alt_error($coverAlt)) !== '') {
            throw new RuntimeException($e);
        }
        $img = cms_process_image($file, $coverAlt, false, true);
        $cover = ['src' => $img['src'], 'og' => $img['og'] ?? null];
    }

    $errors = [];
    if ($title === '') {
        $errors[] = 'Please add a title.';
    } else {
        $slugErr = $slug === '' ? 'Please add a web address (URL).' : article_slug_error($slug, $origSlug !== '' ? $origSlug : null);
        if ($slugErr !== '') {
            $errors[] = $slugErr;
        }
    }

    if ($publish) {
        if (mb_strlen($title) < 15) {
            $errors[] = 'The title is too short. Use at least 15 characters.';
        }
        if (mb_strlen($desc) < 70 || mb_strlen($desc) > 160) {
            $errors[] = 'The search description must be 70 to 160 characters (now ' . mb_strlen($desc) . ').';
        }
        if (str_word_count($body) < 300) {
            $errors[] = 'The article needs at least 300 words to publish (now ' . str_word_count($body) . ').';
        }
        if ($cover['src'] === '') {
            $errors[] = 'Add a cover photo before publishing.';
        } elseif (($e = alt_error($coverAlt)) !== '') {
            $errors[] = 'Cover photo: ' . $e;
        }
        if (!$tags) {
            $errors[] = 'Add at least one tag, for example "Wedding catering".';
        }
    }

    if ($errors) {
        // Keep what was typed so nothing is lost.
        admin_session();
        $_SESSION['article_form'] = [
            'slug' => $origSlug, 'new' => $isNew,
            'data' => ['title' => $title, 'slug' => $slug, 'seo_title' => $seoTitle, 'description' => $desc, 'keyword' => $keyword,
                'eyebrow' => $category, 'body' => $body, 'tags' => implode(', ', $tags), 'faq' => $faq, 'image_alt' => $coverAlt],
        ];
        flash(implode(' ', $errors), 'bad');
        go($isNew ? '/admin/article.php?new=1' : '/admin/article.php?slug=' . rawurlencode($origSlug));
    }

    $today = date('Y-m-d');
    $rec = [
        'slug' => $slug,
        'title' => $title,
        'short' => $title,
        'description' => $desc,
        'keyword' => $keyword,
        'eyebrow' => $category,
        'tags' => $tags,
        'date' => ($publish && !$wasPublished) ? $today : ($old['date'] ?? $today),
        'draft' => !$publish,
        'image_src' => $cover['src'],
        'image_alt' => $coverAlt !== '' ? $coverAlt : ($old['image_alt'] ?? ''),
        'og_src' => $cover['og'],
        'faq' => $faq,
        'body' => $body,
    ];
    if ($seoTitle !== '') {
        $rec['seo_title'] = $seoTitle;
    }
    if ($wasPublished) {
        $rec['updated'] = $today;
    } elseif (!empty($old['updated'])) {
        $rec['updated'] = $old['updated'];
    }

    if ($i !== null) {
        $list[$i] = $rec;
    } else {
        array_unshift($list, $rec);
    }
    cms_articles_save($list);
    admin_session();
    unset($_SESSION['article_form']);
    flash($publish
        ? ($wasPublished ? 'Changes saved. The article is updated on the site.' : 'Published. The article is live on the blog now.')
        : 'Draft saved. Use "Preview draft" to see how it will look.');
    go('/admin/article.php?slug=' . rawurlencode($slug));
} catch (Throwable $t) {
    flash($t instanceof RuntimeException ? $t->getMessage() : 'Something went wrong. Please try again.', 'bad');
    go('/admin/articles.php');
}
