<?php
declare(strict_types=1);
/** Full one-page site. Included by index.php. */
require_once OLIVE_ROOT . '/app/render.php';
require_once OLIVE_ROOT . '/app/leads.php';
require_once OLIVE_ROOT . '/app/blog.php';

$page_type = 'home';
$base = '';
$b = content('business', []);
$hero = content('hero', []);
$phone = (string) ($b['phone'] ?? '');
$waMsg = (string) content('holding.whatsapp_message', 'Hi Olive, I would like to enquire about catering for my event. (via website)');

// Form state after a failed submit (only touch the session if one exists)
$form = ['errors' => [], 'old' => []];
if (isset($_COOKIE['olive_s'])) {
    start_session();
    $form = $_SESSION['olive_form'] ?? $form;
    unset($_SESSION['olive_form']);
}
$err = $form['errors'];
$old = $form['old'];
$val = fn (string $k) => e($old[$k] ?? '');

$gallery = array_values(array_filter(content('gallery', []), fn ($g) => visible($g) && (!empty($g['src']) || is_staging())));
$testimonials = array_values(array_filter(content('testimonials', []), 'visible'));
$associations = array_values(array_filter(content('associations', []), 'visible'));
$faqs = array_values(array_filter(content('faq', []), 'visible'));
$dishes = array_values(array_filter(content('signature_dishes', []), 'visible'));

$meta = [
    'title' => (string) content('seo.title', 'Olive Catering Company'),
    'desc' => (string) content('seo.description', ''),
    'canonical' => canonical_url('/'),
    'schema' => schema_json(),
];
require OLIVE_ROOT . '/partials/site-head.php';
?>
<?php require OLIVE_ROOT . '/partials/site-header.php'; ?>

<main id="main">

  <!-- Hero -->
  <section class="hero">
    <div class="wrap hero__grid">
      <div class="hero__text">
        <p class="eyebrow"><?= e($hero['eyebrow'] ?? '') ?></p>
        <h1 class="hero__title"><?= e($hero['headline'] ?? '') ?></h1>
        <p class="hero__sub"><?= e($hero['subline'] ?? '') ?></p>
        <div class="hero__actions" data-track-location="hero">
          <a class="btn btn--cta" href="#enquiry">Get a quote</a>
          <a class="btn btn--line" href="<?= e(wa_href($waMsg)) ?>"<?= str_starts_with(wa_href($waMsg), 'http') ? ' target="_blank" rel="noopener"' : '' ?>>WhatsApp us</a>
        </div>
      </div>
      <figure class="hero__media">
        <?= picture($hero['image'] ?? [], 'cloche cloche--hero', '(min-width: 900px) 42vw, 90vw', true, 1200, 1500) ?>
        <figcaption class="hero__seal" aria-hidden="true"><span>100%</span>Pure veg</figcaption>
      </figure>
    </div>
  </section>

  <!-- Trust -->
  <section class="trust" aria-label="Olive in numbers">
    <ul class="wrap trust__list">
      <?php foreach (array_filter(content('trust', []), 'visible') as $t): ?>
        <li><strong><?= e($t['value']) ?></strong><span><?= e($t['label']) ?><?= tbc($t) ?></span></li>
      <?php endforeach; ?>
    </ul>
  </section>

  <!-- About -->
  <section class="section about" id="about">
    <div class="wrap about__grid">
      <?= picture(content('about.image', []), 'cloche cloche--about', '(min-width: 900px) 30vw, 80vw', false, 900, 1100) ?>
      <div>
        <p class="eyebrow">About Olive</p>
        <h2 class="h2"><?= e(content('about.heading', '')) ?></h2>
        <?php foreach (content('about.body', []) as $para): ?>
          <p class="lead"><?= e($para) ?></p>
        <?php endforeach; ?>
      </div>
    </div>
  </section>

  <!-- Services -->
  <section class="section services" id="services">
    <div class="wrap">
      <header class="section__head">
        <p class="eyebrow">What we cater</p>
        <h2 class="h2"><?= e(content('services_heading', '')) ?></h2>
      </header>
      <ul class="services__grid">
        <?php foreach (array_filter(content('services', []), 'visible') as $s): ?>
          <li class="svc">
            <?= local_name($s) ?>
            <h3 class="svc__title"><?php if (!empty($s['link'])): ?><a href="<?= e($s['link']) ?>"><?= e($s['title']) ?></a><?php else: ?><?= e($s['title']) ?><?php endif; ?><?= tbc($s) ?></h3>
            <p><?= e($s['text']) ?></p>
          </li>
        <?php endforeach; ?>
      </ul>
    </div>
  </section>


  <!-- Service features: Pure veg & Jain, Weddings, Corporate, Family & social -->
  <?php foreach (content('features', []) as $fi => $f): $post = !empty($f['post']) ? blog_post((string) $f['post']) : null; ?>
  <section class="section feature<?= $fi % 2 ? ' feature--flip' : '' ?>" id="<?= e($f['id']) ?>">
    <div class="wrap feature__grid">
      <figure class="feature__media">
        <?= picture($f['image'] ?? [], 'feature__img', '(min-width: 900px) 46vw, 92vw', false, 1600, 1200) ?>
      </figure>
      <div class="feature__text">
        <p class="eyebrow"><?= e($f['eyebrow'] ?? '') ?></p>
        <?= local_name($f) ?>
        <h2 class="h2"><?= e($f['heading']) ?></h2>
        <?php foreach ($f['body'] ?? [] as $para): ?><p class="lead"><?= e($para) ?></p><?php endforeach; ?>
        <?php if (!empty($f['points'])): ?>
          <ul class="ticks"><?php foreach ($f['points'] as $pt): ?><li><?= e($pt) ?></li><?php endforeach; ?></ul>
        <?php endif; ?>
        <?php if (!empty($f['sub']) && visible($f['sub'])): ?>
          <div class="feature__sub">
            <h3><?= e($f['sub']['heading']) ?><?= tbc($f['sub']) ?></h3>
            <p><?= e($f['sub']['text']) ?></p>
          </div>
        <?php endif; ?>
        <div class="feature__actions" data-track-location="feature_<?= e($f['id']) ?>">
          <a class="btn btn--cta" href="#enquiry">Get a quote</a>
          <?php if ($post): ?><a class="link-more" href="<?= e(blog_url($post)) ?>">Read: <?= e($post['short'] ?? $post['title']) ?></a><?php endif; ?>
        </div>
      </div>
    </div>
  </section>
  <?php endforeach; ?>

  <!-- Menu card (signature section) -->
  <section class="menu" id="menu">
    <div class="wrap">
      <div class="menucard">
        <p class="menucard__top">Olive Catering Company</p>
        <h2 class="menucard__title"><?= e(content('menu_heading', 'Our menus')) ?></h2>
        <span class="menucard__rule" aria-hidden="true"></span>
        <ul class="menucard__list">
          <?php foreach (content('specialties', []) as $sp): ?>
            <li>
              <h3><?= e($sp['title']) ?></h3>
              <p><?= e($sp['text']) ?></p>
            </li>
          <?php endforeach; ?>
        </ul>
        <?php if ($dishes): ?>
          <div class="menucard__dishes">
            <p class="menucard__label"><?= e(content('menu_dishes_label', 'Signature dishes')) ?></p>
            <p><?php foreach ($dishes as $i => $d): ?><?= $i ? ' &middot; ' : '' ?><?= e($d['name']) ?><?= tbc($d) ?><?php endforeach; ?></p>
          </div>
        <?php endif; ?>
        <a class="btn btn--cta" href="#enquiry" data-track-location="menu">Plan my menu</a>
      </div>
    </div>
  </section>

  <!-- Hygiene story -->
  <section class="section story" id="hygiene">
    <div class="wrap">
      <header class="section__head story__head">
        <p class="eyebrow">Hygiene &amp; quality</p>
        <h2 class="h2"><?= e(content('hygiene.heading', '')) ?></h2>
        <p class="lead"><?= e(content('hygiene.intro', '')) ?></p>
        <?php if (!empty($b['fssai'])): ?>
          <p class="fssai">FSSAI Lic. No. <strong><?= e($b['fssai']) ?></strong></p>
        <?php endif; ?>
      </header>
      <?php $steps = content('hygiene.steps', []); ?>
      <div class="story__grid">
        <div class="story__stage" aria-hidden="true">
          <?php foreach ($steps as $i => $st): ?>
            <div class="story__frame<?= $i === 0 ? ' is-active' : '' ?>" data-frame="<?= $i ?>">
              <?= picture($st['image'] ?? [], 'cloche story__img', '(min-width: 900px) 38vw, 0px', false, 900, 1100) ?>
            </div>
          <?php endforeach; ?>
        </div>
        <ol class="story__steps">
          <?php foreach ($steps as $i => $st): ?>
            <li class="story__step<?= $i === 0 ? ' is-active' : '' ?>" data-step="<?= $i ?>">
              <div class="story__inline"><?= picture($st['image'] ?? [], 'story__img story__img--inline', '90vw', false, 900, 700) ?></div>
              <span class="story__count"><?= $i + 1 ?> / <?= count($steps) ?></span>
              <h3 class="story__title"><?= e($st['title'] ?? '') ?></h3>
              <p><?= e($st['text'] ?? '') ?></p>
            </li>
          <?php endforeach; ?>
        </ol>
      </div>
    </div>
  </section>

  <!-- Gallery -->
  <section class="section gallery" id="gallery">
    <div class="wrap">
      <header class="section__head">
        <p class="eyebrow">Gallery</p>
        <h2 class="h2">From our kitchen to your celebration</h2>
      </header>
      <ul class="gallery__grid">
        <?php foreach ($gallery as $i => $g): ?>
          <li>
            <?php if (!empty($g['src'])): ?>
              <a href="<?= e(preg_replace('/-\d+\.webp$/', '-1600.webp', (string) $g['src'])) ?>" class="gallery__link" data-lightbox aria-label="Open photo: <?= e($g['alt']) ?>">
                <?= picture($g, 'gallery__img', '(min-width: 900px) 25vw, 50vw', false, 960, 720) ?>
              </a>
            <?php else: ?>
              <?= picture($g, 'gallery__img', '50vw') ?>
            <?php endif; ?>
          </li>
        <?php endforeach; ?>
      </ul>
    </div>
  </section>

  <?php if ($testimonials || $associations): ?>
  <!-- Words from clients -->
  <section class="section words">
    <div class="wrap">
      <header class="section__head">
        <p class="eyebrow"><?= $testimonials ? 'Kind words' : 'Associations' ?></p>
        <h2 class="h2"><?= $testimonials ? 'What our clients say' : 'Hotels and partners we work with' ?></h2>
      </header>
      <?php if ($testimonials): ?>
        <ul class="quotes">
          <?php foreach ($testimonials as $t): ?>
            <li class="quote">
              <blockquote><p><?= e($t['quote']) ?></p></blockquote>
              <p class="quote__by"><?= e($t['name']) ?><span><?= e($t['event']) ?></span><?= tbc($t) ?></p>
            </li>
          <?php endforeach; ?>
        </ul>
      <?php endif; ?>
      <?php if ($associations): $assocGroups = ['current' => 'Current Association', 'previous' => 'Previous Associations']; ?>
        <div class="assoc">
          <?php foreach ($assocGroups as $gKey => $gLabel): $gItems = array_values(array_filter($associations, fn ($a) => ($a['status'] ?? 'current') === $gKey)); if (!$gItems): continue; endif; ?>
            <div class="assoc__group">
              <p class="menucard__label"><?= e(count($gItems) === 1 ? rtrim($gLabel, 's') : $gLabel) ?></p>
              <ul>
                <?php foreach ($gItems as $as): ?>
                  <li><?= e($as['name']) ?><?php if (!empty($as['note'])): ?><span><?= e($as['note']) ?></span><?php endif; ?><?= tbc($as) ?></li>
                <?php endforeach; ?>
              </ul>
            </div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </div>
  </section>
  <?php endif; ?>


  <?php $areas = array_values(array_filter(content('areas.list', []), 'visible')); if ($areas): ?>
  <!-- Service areas -->
  <section class="section areas" id="areas">
    <div class="wrap areas__grid">
      <header>
        <p class="eyebrow">Where we cater</p>
        <h2 class="h2"><?= e(content('areas.heading', '')) ?></h2>
        <p class="lead"><?= e(content('areas.intro', '')) ?></p>
      </header>
      <ul class="areas__list">
        <?php foreach ($areas as $ar): ?><li><?= e($ar['name']) ?><?= tbc($ar) ?></li><?php endforeach; ?>
        <?php if (content('areas.region', '') !== ''): ?><li class="areas__region"><?= e(content('areas.region')) ?></li><?php endif; ?>
      </ul>
    </div>
  </section>
  <?php endif; ?>

  <?php $posts = array_slice(blog_posts(), 0, 3); if ($posts && blog_enabled()): ?>
  <!-- From the blog -->
  <section class="section bloglist">
    <div class="wrap">
      <header class="section__head">
        <p class="eyebrow">Planning guides</p>
        <h2 class="h2">Helpful reads before you book</h2>
      </header>
      <?php $cards = $posts; require OLIVE_ROOT . '/partials/blog-cards.php'; ?>
      <p class="bloglist__all"><a class="link-more" href="/blog/">All articles</a></p>
    </div>
  </section>
  <?php endif; ?>

  <!-- FAQ -->
  <?php if ($faqs): ?>
  <section class="section faq">
    <div class="wrap faq__grid">
      <header>
        <p class="eyebrow">Questions</p>
        <h2 class="h2">Questions about Olive Catering Company</h2>
      </header>
      <div class="faq__list">
        <?php foreach ($faqs as $f): ?>
          <details>
            <summary><?= e($f['q']) ?><?= tbc($f) ?></summary>
            <p><?= e($f['a']) ?></p>
          </details>
        <?php endforeach; ?>
      </div>
    </div>
  </section>
  <?php endif; ?>

  <!-- Enquiry + contact -->
  <section class="section contact" id="contact">
    <div class="wrap contact__grid">
      <div class="enquiry" id="enquiry">
        <p class="eyebrow">Get a quote</p>
        <h2 class="h2">Tell us about your event</h2>
        <p class="muted">Share a few details and we will call you back with menu ideas and a quote.</p>

        <?php if (!empty($err['form'])): ?>
          <p class="form-alert" role="alert"><?= e($err['form']) ?></p>
        <?php elseif ($err): ?>
          <p class="form-alert" role="alert">Please check the highlighted fields.</p>
        <?php endif; ?>

        <form class="form" id="enquiry-form" action="/enquiry.php" method="post" novalidate data-olive-form>
          <input type="hidden" name="form_token" value="<?= e(form_token()) ?>">
          <input type="hidden" name="form_source" value="main">
          <div class="hp" aria-hidden="true">
            <label>Leave this empty <input type="text" name="company_website" tabindex="-1" autocomplete="off"></label>
          </div>

          <div class="field<?= isset($err['name']) ? ' is-bad' : '' ?>">
            <label for="f-name">Your name</label>
            <input id="f-name" name="name" type="text" autocomplete="name" required maxlength="80" value="<?= $val('name') ?>">
            <?php if (isset($err['name'])): ?><p class="field__err"><?= e($err['name']) ?></p><?php endif; ?>
          </div>

          <div class="field<?= isset($err['phone']) ? ' is-bad' : '' ?>">
            <label for="f-phone">Mobile number</label>
            <input id="f-phone" name="phone" type="tel" inputmode="tel" autocomplete="tel" required maxlength="20" placeholder="10-digit mobile" value="<?= $val('phone') ?>">
            <?php if (isset($err['phone'])): ?><p class="field__err"><?= e($err['phone']) ?></p><?php endif; ?>
          </div>

          <div class="field<?= isset($err['event_type']) ? ' is-bad' : '' ?>">
            <label for="f-type">Type of event</label>
            <select id="f-type" name="event_type" required>
              <option value="">Choose one</option>
              <?php foreach (OLIVE_EVENT_TYPES as $t): ?>
                <option<?= ($old['eventType'] ?? '') === $t ? ' selected' : '' ?>><?= e($t) ?></option>
              <?php endforeach; ?>
            </select>
            <?php if (isset($err['event_type'])): ?><p class="field__err"><?= e($err['event_type']) ?></p><?php endif; ?>
          </div>

          <div class="field-row">
            <div class="field<?= isset($err['event_date']) ? ' is-bad' : '' ?>">
              <label for="f-date">Event date <span class="opt">optional</span></label>
              <input id="f-date" name="event_date" type="date" min="<?= e(date('Y-m-d')) ?>" value="<?= e($old['eventDate'] ?? '') ?>">
              <?php if (isset($err['event_date'])): ?><p class="field__err"><?= e($err['event_date']) ?></p><?php endif; ?>
            </div>
            <div class="field<?= isset($err['guests']) ? ' is-bad' : '' ?>">
              <label for="f-guests">Approx. guests <span class="opt">optional</span></label>
              <input id="f-guests" name="guests" type="number" inputmode="numeric" min="1" max="20000" value="<?= e($old['guestsRaw'] ?? '') ?>">
              <?php if (isset($err['guests'])): ?><p class="field__err"><?= e($err['guests']) ?></p><?php endif; ?>
            </div>
          </div>

          <div class="field">
            <label for="f-msg">Anything else? <span class="opt">optional</span></label>
            <textarea id="f-msg" name="message" rows="3" maxlength="1000" placeholder="Jain menu, live counters, venue, timings..."><?= $val('message') ?></textarea>
          </div>

          <button class="btn btn--cta btn--block" type="submit">Send enquiry</button>
          <p class="form-note">By sending this you agree that Olive may contact you about your enquiry. <a href="/privacy.php">Privacy</a></p>
        </form>
      </div>

      <aside class="reach" data-track-location="contact">
        <h2 class="reach__title">Reach us directly</h2>
        <ul class="reach__list">
          <li><span>Call</span><a href="<?= e(tel_href()) ?>"><?= $phone !== '' ? e($phone) : 'Number coming soon' ?></a></li>
          <li><span>WhatsApp</span><a href="<?= e(wa_href($waMsg)) ?>"<?= str_starts_with(wa_href($waMsg), 'http') ? ' target="_blank" rel="noopener"' : '' ?>>Chat with us</a></li>
          <?php if (!empty($b['email'])): ?><li><span>Email</span><a href="mailto:<?= e($b['email']) ?>"><?= e($b['email']) ?></a></li><?php endif; ?>
          <li><span>Office</span><address><?= e(full_address()) ?></address></li>
          <?php if (!empty($b['hours'])): ?><li><span>Hours</span><?= e($b['hours']) ?></li><?php endif; ?>
        </ul>
        <?php $mq = rawurlencode((string) ($b['map_query'] ?? full_address())); ?>
        <div class="map">
          <iframe title="Map to Olive Catering Company, Gandhidham" src="https://www.google.com/maps?q=<?= e($mq) ?>&amp;output=embed" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
        </div>
        <a class="link-map" data-track-map href="https://www.google.com/maps/search/?api=1&amp;query=<?= e($mq) ?>" target="_blank" rel="noopener">Get directions</a>
      </aside>
    </div>
  </section>
</main>

<?php require OLIVE_ROOT . '/partials/site-footer.php'; ?>
