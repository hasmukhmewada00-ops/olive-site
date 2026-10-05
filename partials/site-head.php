<?php
/**
 * Shared <head>. Expects $meta = [title, desc, canonical, og_image, og_type, schema(json string), robots?]
 */
$meta += ['og_type' => 'website', 'og_image' => canonical_url('/assets/img/og-image.jpg'), 'robots' => '', 'schema' => ''];
$siteName = (string) content('business.name', 'Olive Catering Company');
?><!doctype html>
<html lang="en-IN">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<?php require OLIVE_ROOT . '/partials/loader-head.php'; ?>
<?php require OLIVE_ROOT . '/partials/gtm-head.php'; ?>
<title><?= e($meta['title']) ?></title>
<meta name="description" content="<?= e($meta['desc']) ?>">
<?php if (is_staging()): ?><meta name="robots" content="noindex, nofollow"><?php elseif ($meta['robots'] !== ''): ?><meta name="robots" content="<?= e($meta['robots']) ?>"><?php endif; ?>
<link rel="canonical" href="<?= e($meta['canonical']) ?>">
<meta property="og:type" content="<?= e($meta['og_type']) ?>">
<meta property="og:site_name" content="<?= e($siteName) ?>">
<meta property="og:title" content="<?= e($meta['title']) ?>">
<meta property="og:description" content="<?= e($meta['desc']) ?>">
<meta property="og:url" content="<?= e($meta['canonical']) ?>">
<meta property="og:image" content="<?= e($meta['og_image']) ?>">
<meta property="og:locale" content="en_IN">
<meta name="twitter:card" content="summary_large_image">
<meta name="theme-color" content="#3A4733">
<link rel="icon" href="/assets/img/favicon.svg" type="image/svg+xml">
<link rel="alternate" type="text/plain" href="/llms.txt" title="LLM summary">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,500;0,600;1,500&family=Manrope:wght@400;500;600;700&family=Noto+Serif+Gujarati:wght@500&display=swap" rel="stylesheet">
<link rel="stylesheet" href="<?= e(asset('assets/css/main.css')) ?>">
<?php if ($meta['schema'] !== ''): ?><script type="application/ld+json"><?= $meta['schema'] ?></script><?php endif; ?>
</head>
