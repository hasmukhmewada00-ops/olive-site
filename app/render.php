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

/** LocalBusiness + FAQPage structured data from content (confirmed items only). */
function schema_json(): string
{
    $b = content('business', []);
    $a = $b['address'] ?? [];
    $biz = [
        '@type' => 'FoodEstablishment',
        '@id' => canonical_url('/#business'),
        'name' => $b['name'] ?? 'Olive Catering Company',
        'description' => content('footer.text', ''),
        'url' => canonical_url('/'),
        'image' => canonical_url('/assets/img/og-image.jpg'),
        'servesCuisine' => ['Vegetarian', 'Jain', 'Indian'],
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
        'areaServed' => array_map(fn ($c) => ['@type' => 'City', 'name' => $c], $b['area_served'] ?? []),
        'sameAs' => array_values(array_filter([
            !empty($b['instagram']) ? 'https://www.instagram.com/' . $b['instagram'] . '/' : null,
            $b['gbp_url'] ?? null,
        ])),
    ];
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

    $faqs = array_filter(content('faq', []), fn ($f) => ($f['confirmed'] ?? true) !== false);
    $graph = [$biz];
    if ($faqs) {
        $graph[] = [
            '@type' => 'FAQPage',
            'mainEntity' => array_values(array_map(fn ($f) => [
                '@type' => 'Question',
                'name' => $f['q'],
                'acceptedAnswer' => ['@type' => 'Answer', 'text' => $f['a']],
            ], $faqs)),
        ];
    }
    return json_encode(['@context' => 'https://schema.org', '@graph' => $graph], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG);
}
