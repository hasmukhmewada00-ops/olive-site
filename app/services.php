<?php
declare(strict_types=1);
/**
 * Service and area landing pages, served at /services/{slug}/ by service.php.
 * Facts here must match content.sample.json: pure veg kitchen, Jain menus without onion, garlic or
 * root vegetables, FSSAI + HACCP kitchen, live counters (rumali roti, pani puri, pizza), modern
 * crockery and covered serving, trained staff, base at Radisson Resort and Spa, Gandhidham, since 2023.
 * No prices, minimum guests or capacities until the client confirms them.
 */

function service_pages(): array
{
    static $pages = null;
    if ($pages !== null) {
        return $pages;
    }
    $pages = [

        'wedding-caterers-gandhidham' => [
            'nav' => 'Wedding catering',
            'title' => 'Wedding Caterers in Gandhidham & Kutch',
            'seo_title' => 'Wedding Caterers in Gandhidham & Kutch | Pure Veg & Jain | Olive',
            'description' => 'Olive Catering Company is a pure veg and Jain wedding caterer in Gandhidham, serving weddings across Kutch and Gujarat with buffets, live counters and trained staff.',
            'eyebrow' => 'Weddings',
            'keyword' => 'wedding caterers Gandhidham',
            'image' => 'olive-wedding-catering-spread-gandhidham',
            'image_alt' => 'Wedding table with curries, jeera rice, rotis, salad and papad in copper bowls',
            'lead' => 'Olive Catering Company is a pure vegetarian and Jain wedding caterer based in Gandhidham, Kutch. We cater wedding functions in Gandhidham, Anjar, Bhuj, Mundra and Kandla and across Gujarat, with buffet setups, live counters, modern crockery and trained hospitality staff and supervisors.',
            'body' => '
<h2>What we do for weddings</h2>
<p>A wedding is several meals across several days, each with its own mood. We plan the food for each function with your family, so you can spend the days with your guests instead of the kitchen.</p>
<ul>
  <li><strong>Buffet setups</strong> planned around your venue and guest count</li>
  <li><strong>Live counters</strong> such as rumali roti, pani puri and pizza, made fresh in front of guests</li>
  <li><strong>Modern crockery and covered serving dishes</strong>, presented to five-star standards</li>
  <li><strong>Trained hospitality staff and supervisors</strong> at every counter</li>
  <li><strong>Themed menus and custom buffets</strong> designed around your event</li>
</ul>

<h2>Pure veg and Jain, on the same buffet</h2>
<p>Everything we cook is pure vegetarian. For Jain families and guests we prepare Jain dishes without onion, garlic or root vegetables, and Jain and regular dishes can be served on the same buffet so that no guest is left with a small choice. Read more on our <a href="/services/jain-catering-gandhidham/">Jain catering</a> page.</p>

<h2>Destination and NRI weddings in Kutch</h2>
<p>Coming home to Kutch for a wedding? We work with NRI and destination wedding families so that menus and arrangements are settled well before the celebrations begin.</p>

<h2>Where we cater</h2>
<p>We are based at Radisson Resort and Spa on Airport Road, Gandhidham, and cater weddings across Kutch and Gujarat. See all the places we serve on <a href="/services/catering-services-gujarat/">Catering Services in Gujarat</a>.</p>

<h2>Food safety you can ask about</h2>
<p>Food is cooked in our HACCP-compliant central kitchen, checked by experienced chefs, and served by trained staff. We are FSSAI licensed (Lic. No. 10724013000075).</p>

<h2>Planning a wedding? Start here</h2>
<ul>
  <li><a href="/blog/wedding-catering-gandhidham-planning-guide/">Wedding catering in Gandhidham: a planning guide</a></li>
  <li><a href="/blog/wedding-catering-checklist-gandhidham/">Wedding catering checklist</a></li>
  <li><a href="/blog/gujarati-wedding-menu-list/">Gujarati wedding menu list</a></li>
  <li><a href="/blog/how-catering-pricing-works-gujarat/">How catering quotes work</a></li>
</ul>',
            'faq' => [
                ['q' => 'Who are the wedding caterers in Gandhidham?', 'a' => 'Olive Catering Company is a pure veg and Jain wedding caterer based in Gandhidham, at Radisson Resort and Spa on Airport Road. Since 2023 it has catered weddings and family celebrations with buffet setups, live counters and trained hospitality staff.'],
                ['q' => 'Do you cater weddings outside Gandhidham?', 'a' => 'Yes. We cater weddings in Anjar, Bhuj, Mundra and Kandla and across Kutch and Gujarat. Share your venue and date and we will confirm.'],
                ['q' => 'Can you serve Jain and regular vegetarian food at the same wedding?', 'a' => 'Yes. Everything we cook is vegetarian, and we prepare Jain dishes without onion, garlic or root vegetables. Both can be served on the same buffet with clear labels.'],
                ['q' => 'Do you have live counters for weddings?', 'a' => 'Yes. Our live counters include rumali roti, pani puri and pizza, made fresh in front of your guests.'],
                ['q' => 'How do I get a wedding catering quote?', 'a' => 'Send your wedding dates, functions, venue and approximate guest count through the enquiry form or on WhatsApp, and we will call you back with menu ideas and a quote.'],
            ],
            'posts' => ['wedding-catering-gandhidham-planning-guide', 'wedding-catering-checklist-gandhidham', 'gujarati-wedding-menu-list'],
            'related' => ['jain-catering-gandhidham', 'gujarati-wedding-menu', 'catering-services-gujarat'],
            'wa' => 'Hi Olive, I would like a quote for wedding catering. Date: ___ Venue: ___ Guests: ___ (via website)',
        ],

        'jain-catering-gandhidham' => [
            'nav' => 'Jain catering',
            'title' => 'Jain Catering in Gandhidham & Kutch',
            'seo_title' => 'Jain Catering in Gandhidham & Kutch | No Onion, Garlic or Root Veg | Olive',
            'description' => 'Jain catering in Gandhidham and across Kutch and Gujarat from Olive Catering Company. Pure veg kitchen; Jain menus without onion, garlic or root vegetables for weddings and functions.',
            'eyebrow' => 'Pure veg & Jain',
            'keyword' => 'Jain catering Gandhidham',
            'image' => 'olive-jain-thali-gandhidham',
            'image_alt' => 'Brass thali with dal, kadhi, sabzi, rotli, rice, papad and a sweet',
            'lead' => 'Olive Catering Company prepares Jain menus for weddings, family functions and events in Gandhidham, Kutch and across Gujarat. Jain dishes are cooked without onion, garlic or root vegetables, in a kitchen that is pure vegetarian throughout.',
            'body' => '
<h2>What Jain catering means</h2>
<p>Jain food is vegetarian food prepared without onion, garlic or root vegetables. Many families also follow rules about timing and about particular days. We plan Jain menus with the host family, so you decide which practices matter for your guests.</p>

<h2>A pure veg kitchen</h2>
<p>Olive is a pure vegetarian caterer, so there is no meat, fish or egg anywhere in our kitchen. Pure veg is not one option on our menu; it is the whole kitchen.</p>

<h2>Jain and regular dishes at the same event</h2>
<p>You do not have to choose between a Jain function and a regular one. We can serve Jain and regular vegetarian dishes on the same buffet, clearly labelled, so every guest eats with confidence.</p>

<h2>Where we cater Jain menus</h2>
<ul>
  <li>Weddings and wedding functions (see <a href="/services/wedding-caterers-gandhidham/">wedding catering</a>)</li>
  <li>Poojas, festivals and community jamanvar (see <a href="/services/pooja-festival-catering-gandhidham/">pooja and festival catering</a>)</li>
  <li>Family functions, birthdays and anniversaries</li>
  <li>Corporate events with Jain options for those who need them</li>
</ul>

<h2>Read before you book</h2>
<p>Our <a href="/blog/jain-catering-guide-gandhidham/">Jain catering guide</a> explains what Jain food excludes and what to ask any caterer before you confirm.</p>',
            'faq' => [
                ['q' => 'Does Olive Catering Company provide Jain catering?', 'a' => 'Yes. Olive prepares Jain menus for weddings, family functions and other events, cooked without onion, garlic or root vegetables.'],
                ['q' => 'Is the whole kitchen vegetarian?', 'a' => 'Yes. Everything Olive cooks is pure vegetarian.'],
                ['q' => 'Can Jain and non-Jain vegetarian dishes be served together?', 'a' => 'Yes. Jain and regular vegetarian dishes can be served on the same buffet with clear labels.'],
                ['q' => 'Which areas do you cater Jain events in?', 'a' => 'We cater in Gandhidham, Anjar, Bhuj, Mundra and Kandla and across Kutch and Gujarat.'],
                ['q' => 'How do I plan a Jain menu with Olive?', 'a' => 'Tell us the event, the number of Jain guests and which practices the family follows. We will suggest a menu and call you back with a quote.'],
            ],
            'posts' => ['jain-catering-guide-gandhidham', 'wedding-catering-checklist-gandhidham', 'pooja-festival-catering-guide-gandhidham'],
            'related' => ['wedding-caterers-gandhidham', 'pooja-festival-catering-gandhidham', 'catering-services-gujarat'],
            'wa' => 'Hi Olive, I would like a quote for Jain catering. Event: ___ Date: ___ Guests: ___ (via website)',
        ],

        'gujarati-wedding-menu' => [
            'nav' => 'Gujarati wedding menu',
            'title' => 'Gujarati Wedding Catering Menu in Gandhidham',
            'seo_title' => 'Gujarati Wedding Menu & Caterers in Gandhidham, Kutch | Olive',
            'description' => 'Planning a Gujarati wedding menu in Gandhidham or Kutch? How Olive Catering Company builds pure veg and Jain wedding menus with live counters and buffet service.',
            'eyebrow' => 'Wedding menus',
            'keyword' => 'Gujarati wedding menu Gandhidham',
            'image' => 'olive-wedding-dessert-counter-kutch',
            'image_alt' => 'Wedding dessert counter with jalebi, sweets and marigold garlands',
            'lead' => 'A Gujarati wedding menu is built around thali-style comfort: farsan, dal, kadhi, sabzi, rotli, rice and sweets, with live counters for variety. Olive Catering Company plans pure vegetarian and Jain wedding menus in Gandhidham, Kutch and across Gujarat around your family, functions and guest count.',
            'body' => '
<h2>How we plan a Gujarati wedding menu</h2>
<p>There is no single menu that fits every family. We start with your functions, your guests and any Jain requirements, then build the menu function by function, from the welcome drinks and starters to the main meal and sweets.</p>

<h2>Parts of a Gujarati wedding meal</h2>
<ul>
  <li><strong>Welcome and starters:</strong> chilled drinks and farsan</li>
  <li><strong>Main meal:</strong> dal, kadhi, seasonal sabzi, paneer dishes, rotli, puri or parathas, rice or khichdi</li>
  <li><strong>Live counters:</strong> rumali roti, pani puri and pizza, made fresh in front of guests</li>
  <li><strong>Sweets and dessert counter:</strong> a traditional sweet or two alongside a dessert spread</li>
</ul>
<p>The exact dishes depend on the season, the function and your family tradition. We confirm them with you before the event.</p>

<h2>Jain dishes on the same buffet</h2>
<p>For Jain guests we prepare dishes without onion, garlic or root vegetables and label them clearly on the buffet, so the menu does not shrink for anyone.</p>

<h2>Presentation and service</h2>
<p>Food is served in modern crockery and covered serving dishes by trained hospitality staff and supervisors, with eco-friendly serveware available on request.</p>

<h2>More on wedding menus</h2>
<ul>
  <li><a href="/blog/gujarati-wedding-menu-list/">Gujarati wedding menu list: ideas by course</a></li>
  <li><a href="/services/wedding-caterers-gandhidham/">Wedding caterers in Gandhidham</a></li>
  <li><a href="/blog/how-catering-pricing-works-gujarat/">How catering quotes work</a></li>
</ul>',
            'faq' => [
                ['q' => 'What is included in a Gujarati wedding menu?', 'a' => 'A typical Gujarati wedding menu has welcome drinks, farsan, dal, kadhi, sabzi, rotli or puri, rice or khichdi and sweets, often with live counters. Olive plans the exact menu with you for each function.'],
                ['q' => 'Can the menu include Jain dishes?', 'a' => 'Yes. Olive prepares Jain dishes without onion, garlic or root vegetables and can serve them alongside regular vegetarian dishes.'],
                ['q' => 'Do you offer live counters at weddings?', 'a' => 'Yes. Live counters include rumali roti, pani puri and pizza, made fresh in front of your guests.'],
                ['q' => 'Can we customise the menu?', 'a' => 'Yes. Menus and buffet layouts are designed around your event, theme and guest count.'],
                ['q' => 'Is non-vegetarian food available?', 'a' => 'No. Olive Catering Company is 100% pure vegetarian.'],
            ],
            'posts' => ['gujarati-wedding-menu-list', 'wedding-catering-gandhidham-planning-guide', 'how-catering-pricing-works-gujarat'],
            'related' => ['wedding-caterers-gandhidham', 'jain-catering-gandhidham', 'catering-services-gujarat'],
            'wa' => 'Hi Olive, I would like to plan a Gujarati wedding menu. Date: ___ Guests: ___ (via website)',
        ],

        'pooja-festival-catering-gandhidham' => [
            'nav' => 'Pooja & festival catering',
            'title' => 'Pooja, Festival & Jamanvar Catering in Gandhidham',
            'seo_title' => 'Pooja, Festival & Jamanvar Catering in Gandhidham, Kutch | Olive',
            'description' => 'Pure veg and Jain catering for pujas, festivals, Navratri and community jamanvar in Gandhidham, Kutch and across Gujarat from Olive Catering Company.',
            'eyebrow' => 'Pooja & festivals',
            'keyword' => 'pooja catering Gandhidham',
            'image' => 'wedding-reception-food-counter-marigold-decor',
            'image_alt' => 'Food counter with brass serving vessels, a brass lamp and marigold decor',
            'lead' => 'Olive Catering Company caters pujas, festivals, Navratri and community jamanvar in Gandhidham, Kutch and across Gujarat. Menus are pure vegetarian, with Jain options without onion, garlic or root vegetables, sized to your guest list.',
            'body' => '
<h2>Functions we cater</h2>
<ul>
  <li>Satyanarayan and other poojas at home or in a hall</li>
  <li>Festival gatherings, including Navratri</li>
  <li>Community jamanvar and religious functions</li>
  <li>Janoi, Simant, birthdays, anniversaries and family get-togethers</li>
</ul>

<h2>Pure veg by default</h2>
<p>Our whole kitchen is pure vegetarian, so a religious function never needs special arrangements about meat or egg. For families who follow Jain rules, we prepare dishes without onion, garlic or root vegetables.</p>

<h2>The same care as a wedding</h2>
<p>A family function gets the same care as a large wedding: menus sized to your guest list, modern crockery and covered serving, trained staff, and live counters such as rumali roti, pani puri and pizza when you want something special.</p>

<h2>Tell us what the day needs</h2>
<p>Share the date, the timing of the function, the number of guests and any rules the family follows. If you are not sure, our <a href="/blog/pooja-festival-catering-guide-gandhidham/">pooja and festival catering guide</a> lists what to decide first.</p>',
            'faq' => [
                ['q' => 'Do you cater pujas and festival functions?', 'a' => 'Yes. Olive caters pujas, festivals, Navratri, community jamanvar and family functions in Gandhidham, Kutch and across Gujarat.'],
                ['q' => 'Is the food pure vegetarian?', 'a' => 'Yes. Everything Olive cooks is pure vegetarian, and Jain menus without onion, garlic or root vegetables are available.'],
                ['q' => 'Can you cater a small family function?', 'a' => 'Yes. Olive caters family functions as well as large weddings, with menus sized to your guest list.'],
                ['q' => 'What should I tell you when I enquire?', 'a' => 'The date, timing, venue, approximate guest count and any food rules your family follows.'],
            ],
            'posts' => ['pooja-festival-catering-guide-gandhidham', 'jain-catering-guide-gandhidham', 'wedding-catering-checklist-gandhidham'],
            'related' => ['jain-catering-gandhidham', 'wedding-caterers-gandhidham', 'catering-services-gujarat'],
            'wa' => 'Hi Olive, I would like a quote for pooja/festival catering. Date: ___ Guests: ___ (via website)',
        ],

        'corporate-catering-gandhidham' => [
            'nav' => 'Corporate catering',
            'title' => 'Corporate Catering in Gandhidham, Kandla & Mundra',
            'seo_title' => 'Corporate Catering in Gandhidham, Kandla & Mundra | Olive',
            'description' => 'Corporate lunches, meetings, conferences, launches and staff events in Gandhidham, Kandla, Mundra and across Gujarat. Pure veg menus with Jain options from Olive Catering Company.',
            'eyebrow' => 'Corporate',
            'keyword' => 'corporate catering Gandhidham',
            'image' => 'olive-corporate-lunch-catering-gandhidham',
            'image_alt' => 'Corporate lunch buffet in steel chafing dishes with guests in an office hall',
            'lead' => 'Olive Catering Company caters corporate lunches, meetings, conferences, launches and staff events in Gandhidham, Kandla, Mundra and across Gujarat, with pure veg menus that work for mixed teams and Jain options for those who need them.',
            'body' => '
<h2>Corporate events we cater</h2>
<ul>
  <li>Working lunches and team meals</li>
  <li>Meetings and conferences</li>
  <li>Product launches and client events</li>
  <li>Staff celebrations and festival lunches</li>
</ul>

<h2>Why pure veg suits mixed teams</h2>
<p>A fully vegetarian menu removes a common source of friction at office events: there is one kitchen, one menu and food everyone on the team can eat. Jain options are available for colleagues and guests who need them.</p>

<h2>On time, clean and covered</h2>
<p>Food is cooked in our HACCP-compliant central kitchen, which is sanitised daily, and served in covered dishes by trained hospitality staff. We are FSSAI licensed (Lic. No. 10724013000075).</p>

<h2>Port, industrial and office areas</h2>
<p>We cater in Gandhidham, Kandla and Mundra and across Gujarat. Our team is based at Radisson Resort and Spa on Airport Road, Gandhidham, and we also have an association with Radisson Resort and Spa, Kandla.</p>

<h2>Planning a corporate event?</h2>
<p>Read our <a href="/blog/corporate-catering-gandhidham-kandla-mundra/">corporate catering guide for Gandhidham, Kandla and Mundra</a>, then send us the date, guest count and timing.</p>',
            'faq' => [
                ['q' => 'Does Olive provide corporate catering in Gandhidham?', 'a' => 'Yes. Olive caters corporate lunches, meetings, conferences, launches and staff events in Gandhidham, Kandla, Mundra and across Gujarat.'],
                ['q' => 'Are Jain options available for corporate events?', 'a' => 'Yes. Menus are pure vegetarian, with Jain options without onion, garlic or root vegetables for those who need them.'],
                ['q' => 'Is your kitchen FSSAI licensed?', 'a' => 'Yes. Olive Catering Company holds FSSAI licence number 10724013000075 and cooks in a HACCP-compliant central kitchen.'],
                ['q' => 'How do I request a corporate catering quote?', 'a' => 'Send the date, number of people, timing and venue through the enquiry form or WhatsApp, and we will call you back.'],
            ],
            'posts' => ['corporate-catering-gandhidham-kandla-mundra', 'how-catering-pricing-works-gujarat'],
            'related' => ['catering-services-gujarat', 'jain-catering-gandhidham', 'wedding-caterers-gandhidham'],
            'wa' => 'Hi Olive, I would like a quote for corporate catering. Date: ___ People: ___ Venue: ___ (via website)',
        ],

        'catering-services-gujarat' => [
            'nav' => 'Areas we serve',
            'title' => 'Catering Services in Gandhidham, Kutch & Across Gujarat',
            'seo_title' => 'Caterers in Gandhidham, Anjar, Bhuj, Mundra, Kandla & Gujarat | Olive',
            'description' => 'Olive Catering Company is based in Gandhidham and caters weddings, corporate events and celebrations in Anjar, Bhuj, Mundra, Kandla, across Kutch and across Gujarat.',
            'eyebrow' => 'Areas we serve',
            'keyword' => 'caterers in Kutch Gujarat',
            'image' => 'olive-service-staff-wedding-buffet-gandhidham',
            'image_alt' => 'Five Olive staff in green waistcoats behind a wedding buffet of brass serving dishes',
            'lead' => 'Olive Catering Company is based at Radisson Resort and Spa on Airport Road, Gandhidham, Kutch. We cater weddings, corporate events and celebrations in Gandhidham, Anjar, Bhuj, Mundra and Kandla, across Kutch, and across Gujarat. Everything we cook is pure vegetarian, with Jain menus on request.',
            'body' => '
<h2>Where we cater</h2>
<h3>Gandhidham</h3>
<p>Our home base. Our central kitchen and team are in Gandhidham, and we cater weddings, corporate events and family functions across the city. See <a href="/services/wedding-caterers-gandhidham/">wedding catering</a> and <a href="/services/corporate-catering-gandhidham/">corporate catering</a>.</p>
<h3>Anjar</h3>
<p>We cater weddings and functions in Anjar, and we have an association with The Fern Royal Farm Resort, Anjar.</p>
<h3>Bhuj</h3>
<p>We cater weddings, events and celebrations in Bhuj, including for families coming home to Kutch for a wedding.</p>
<h3>Mundra and Kandla</h3>
<p>We cater corporate lunches, meetings and staff events in Mundra and Kandla, and we have an association with Radisson Resort and Spa, Kandla.</p>
<h3>Across Gujarat</h3>
<p>For weddings and events elsewhere in Gujarat, send us the venue and date and we will confirm whether we can take it on.</p>

<h2>The same kitchen standards everywhere</h2>
<p>Food is cooked in our HACCP-compliant central kitchen, checked by experienced chefs, and served by trained hospitality staff and supervisors. We are FSSAI licensed (Lic. No. 10724013000075).</p>

<h2>Our services</h2>
<ul>
  <li><a href="/services/wedding-caterers-gandhidham/">Wedding catering</a></li>
  <li><a href="/services/jain-catering-gandhidham/">Jain catering</a></li>
  <li><a href="/services/gujarati-wedding-menu/">Gujarati wedding menu</a></li>
  <li><a href="/services/pooja-festival-catering-gandhidham/">Pooja and festival catering</a></li>
  <li><a href="/services/corporate-catering-gandhidham/">Corporate catering</a></li>
</ul>',
            'faq' => [
                ['q' => 'Where does Olive Catering Company provide catering services?', 'a' => 'Olive is based in Gandhidham, Kutch, and caters events across Kutch and Gujarat, including Gandhidham, Anjar, Bhuj, Mundra and Kandla.'],
                ['q' => 'Do you cater outside Kutch?', 'a' => 'Yes, across Gujarat. Share your venue and date and we will confirm availability.'],
                ['q' => 'Where is your kitchen?', 'a' => 'Our team is based at Radisson Resort and Spa, Airport Road, Gandhidham, Kutch.'],
                ['q' => 'Is the food pure vegetarian everywhere you cater?', 'a' => 'Yes. Everything Olive cooks is pure vegetarian, with Jain menus without onion, garlic or root vegetables on request.'],
            ],
            'posts' => ['wedding-catering-gandhidham-planning-guide', 'corporate-catering-gandhidham-kandla-mundra', 'jain-catering-guide-gandhidham'],
            'related' => ['wedding-caterers-gandhidham', 'corporate-catering-gandhidham', 'jain-catering-gandhidham'],
            'wa' => 'Hi Olive, I would like to enquire about catering in ___ (city). Date: ___ Guests: ___ (via website)',
        ],
    ];
    return $pages;
}

function service_page(string $slug): ?array
{
    $all = service_pages();
    if (!isset($all[$slug])) {
        return null;
    }
    return ['slug' => $slug] + $all[$slug];
}

function service_url(string $slug, bool $absolute = false): string
{
    $path = '/services/' . $slug . '/';
    return $absolute ? canonical_url($path) : $path;
}

function service_img(array $page, int $w = 960): string
{
    return '/assets/img/photos/' . $page['image'] . '-' . $w . '.webp';
}

function service_schema(array $page): string
{
    $url = service_url($page['slug'], true);
    $areas = array_map(fn ($n) => ['@type' => 'AdministrativeArea', 'name' => $n], ['Gandhidham', 'Anjar', 'Bhuj', 'Mundra', 'Kandla', 'Kutch', 'Gujarat']);
    $graph = [
        [
            '@type' => 'Service',
            '@id' => $url . '#service',
            'name' => $page['title'],
            'description' => $page['description'],
            'serviceType' => $page['keyword'],
            'url' => $url,
            'image' => canonical_url(service_img($page, 1600)),
            'areaServed' => $areas,
            'provider' => ['@id' => canonical_url('/#business'), '@type' => 'FoodEstablishment', 'name' => (string) content('business.name', 'Olive Catering Company'), 'url' => canonical_url('/')],
        ],
        [
            '@type' => 'BreadcrumbList',
            'itemListElement' => [
                ['@type' => 'ListItem', 'position' => 1, 'name' => 'Home', 'item' => canonical_url('/')],
                ['@type' => 'ListItem', 'position' => 2, 'name' => 'Services', 'item' => canonical_url('/#services')],
                ['@type' => 'ListItem', 'position' => 3, 'name' => $page['title'], 'item' => $url],
            ],
        ],
    ];
    if (!empty($page['faq'])) {
        $graph[] = [
            '@type' => 'FAQPage',
            'mainEntity' => array_map(fn ($f) => [
                '@type' => 'Question',
                'name' => $f['q'],
                'acceptedAnswer' => ['@type' => 'Answer', 'text' => $f['a']],
            ], $page['faq']),
        ];
    }
    return json_encode(['@context' => 'https://schema.org', '@graph' => $graph], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG);
}
