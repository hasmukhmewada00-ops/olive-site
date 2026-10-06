<?php
declare(strict_types=1);

/**
 * Rendering helpers for the public page.
 */

/** Should this content item be shown here? Unconfirmed items show on staging only. */
function visible(array $item): bool
{
    return ($item['confirmed'] ?? true) !== false || is_staging();
}

/** Small "to confirm" tag, staging only. */
function tbc(array $item): string
{
    return (($item['confirmed'] ?? true) === false && is_staging())
        ? '<span class="tbc" title="Shown on staging only until the client confirms">to confirm</span>'
        : '';
}

/**
 * Picture element for a content image. With no src yet, renders a labelled
 * placeholder block of the same shape so layouts can be reviewed.
 * $sizes follows the HTML sizes attribute.
 */
function picture(array $img, string $class, string $sizes = '100vw', bool $eager = false, int $w = 1600, int $h = 1200): string
{
    $alt = (string) ($img['alt'] ?? '');
    $src = (string) ($img['src'] ?? '');

    if ($src === '') {
        $label = (string) ($img['placeholder'] ?? $alt);
        return '<div class="' . e($class) . ' ph" role="img" aria-label="' . e($alt) . '"><span class="ph__label">Photo &middot; ' . e($label) . '</span></div>';
    }

    // Uploads are stored as name-480.webp / name-960.webp / name-1600.webp by the CMS.
    $srcset = '';
    if (preg_match('/^(.*)-(\d+)\.webp$/', $src, $m)) {
        $parts = [];
        foreach ([480, 960, 1600] as $size) {
            $parts[] = $m[1] . '-' . $size . '.webp ' . $size . 'w';
        }
        $srcset = ' srcset="' . e(implode(', ', $parts)) . '" sizes="' . e($sizes) . '"';
    }
    $w = (int) ($img['width'] ?? $w);
    $h = (int) ($img['height'] ?? $h);
    $load = $eager ? ' fetchpriority="high"' : ' loading="lazy" decoding="async"';

    return '<img class="' . e($class) . '" src="' . e($src) . '"' . $srcset . ' width="' . $w . '" height="' . $h . '" alt="' . e($alt) . '"' . $load . '>';
}

/** Gujarati name line (script + romanised) shown above a service or feature title. */
function local_name(array $item): string
{
    $l = $item['local'] ?? [];
    if (empty($l['gu']) && empty($l['tr'])) {
        return '';
    }
    $out = '<p class="local">';
    if (!empty($l['gu'])) {
        $out .= '<span class="local__gu" lang="gu">' . e((string) $l['gu']) . '</span>';
    }
    if (!empty($l['tr'])) {
        $out .= '<span class="local__tr" lang="gu-Latn">' . e((string) $l['tr']) . '</span>';
    }
    return $out . '</p>';
}

/** WhatsApp link, or the enquiry form while the number is not confirmed. */
function wa_href(string $message): string
{
    $b = content('business', []);
    $n = (string) (($b['whatsapp'] ?? '') ?: ($b['phone'] ?? ''));
    return $n !== '' ? whatsapp_link($n, $message) : '#enquiry';
}

function tel_href(): string
{
    $n = (string) content('business.phone', '');
    return $n !== '' ? 'tel:+' . phone_digits($n) : '#enquiry';
}

function full_address(): string
{
    $a = content('business.address', []);
    return implode(', ', array_filter([$a['street'] ?? '', $a['locality'] ?? '', ($a['district'] ?? '') ?: null, trim(($a['region'] ?? '') . ' ' . ($a['postal_code'] ?? ''))]));
}

/** Confirmed service areas (city names). Unconfirmed ones only on staging. */
function service_areas(bool $confirmedOnly = true): array
{
    $list = content('areas.list', []);
    return array_values(array_map(fn ($a) => $a['name'], array_filter($list, fn ($a) => !$confirmedOnly || ($a['confirmed'] ?? true) !== false)));
}

/**
 * Entity graph for search and AI engines: LocalBusiness (caterer) + WebSite + FAQPage.
 * Only confirmed facts go into the schema; nothing marked confirmed:false is published.
 */
function schema_json(): string
{
    $b = content('business', []);
    $a = $b['address'] ?? [];
    $name = (string) ($b['name'] ?? 'Olive Catering Company');
    $services = [];
    foreach (content('features', []) as $f) {
        $services[] = ['@type' => 'Offer', 'itemOffered' => ['@type' => 'Service', 'name' => $f['heading'], 'serviceType' => 'Catering', 'description' => implode(' ', $f['body'] ?? [])]];
    }
    $biz = [
        '@type' => ['LocalBusiness', 'FoodEstablishment'],
        '@id' => canonical_url('/#business'),
        'name' => $name,
        'description' => (string) content('seo.description', ''),
        'slogan' => 'Pure Veg & Jain Catering in Gandhidham, Kutch and across Gujarat',
        'url' => canonical_url('/'),
        'image' => array_values(array_filter([
            canonical_url('/assets/img/og-image.jpg'),
            !empty(content('hero.image.src')) ? canonical_url((string) content('hero.image.src')) : null,
        ])),
        'logo' => canonical_url('/assets/img/logo/olive-logo.svg'),
        'servesCuisine' => ['Pure Vegetarian', 'Jain', 'Gujarati', 'Indian'],
        'knowsAbout' => ['Wedding catering', 'Jain catering', 'Pure vegetarian catering', 'Corporate catering', 'Event catering', 'Live food counters'],
        'priceRange' => '₹₹',
        'foundingDate' => (string) ($b['established'] ?? ''),
        'address' => [
            '@type' => 'PostalAddress',
            'streetAddress' => $a['street'] ?? '',
            'addressLocality' => $a['locality'] ?? '',
            'addressRegion' => $a['region'] ?? '',
            'postalCode' => $a['postal_code'] ?? '',
            'addressCountry' => $a['country'] ?? 'IN',
        ],
        'areaServed' => array_merge(
            array_map(fn ($c) => ['@type' => 'City', 'name' => $c . ', Gujarat'], service_areas()),
            [['@type' => 'AdministrativeArea', 'name' => 'Kutch district, Gujarat'], ['@type' => 'State', 'name' => 'Gujarat']]
        ),
        'hasOfferCatalog' => ['@type' => 'OfferCatalog', 'name' => 'Catering services', 'itemListElement' => $services],
        'sameAs' => array_values(array_filter([
            !empty($b['instagram']) ? 'https://www.instagram.com/' . $b['instagram'] . '/' : null,
            $b['gbp_url'] ?? null,
        ])),
    ];
    if (!empty($b['fssai'])) {
        $biz['identifier'] = ['@type' => 'PropertyValue', 'propertyID' => 'FSSAI licence', 'value' => (string) $b['fssai']];
    }
    if (!empty($b['phone'])) {
        $biz['telephone'] = '+' . phone_digits((string) $b['phone']);
    }
    if (!empty($b['email'])) {
        $biz['email'] = $b['email'];
    }
    if (!empty($b['geo']['lat']) && !empty($b['geo']['lng'])) {
        $biz['geo'] = ['@type' => 'GeoCoordinates', 'latitude' => $b['geo']['lat'], 'longitude' => $b['geo']['lng']];
    }
    if (!empty($b['opening_hours'])) {
        $biz['openingHours'] = $b['opening_hours'];
    }

    $graph = [
        $biz,
        ['@type' => 'WebSite', '@id' => canonical_url('/#website'), 'url' => canonical_url('/'), 'name' => $name, 'publisher' => ['@id' => canonical_url('/#business')], 'inLanguage' => 'en-IN'],
    ];
    $faqs = array_filter(content('faq', []), fn ($f) => ($f['confirmed'] ?? true) !== false);
    if ($faqs) {
        $graph[] = [
            '@type' => 'FAQPage',
            '@id' => canonical_url('/#faq'),
            'mainEntity' => array_values(array_map(fn ($f) => [
                '@type' => 'Question',
                'name' => $f['q'],
                'acceptedAnswer' => ['@type' => 'Answer', 'text' => $f['a']],
            ], $faqs)),
        ];
    }
    return json_encode(['@context' => 'https://schema.org', '@graph' => $graph], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG);
}
