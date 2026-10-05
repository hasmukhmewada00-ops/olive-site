<?php
/** Shared footer, contact buttons, quick-enquiry pop-up and scripts. */
$base = $base ?? '';
?>
<footer class="foot">
  <div class="wrap foot__grid">
    <div>
      <p class="foot__logo"><?= logo_svg('olive-logo') ?></p>
      <p class="foot__text"><?= e(content('footer.text', '')) ?></p>
    </div>
    <nav aria-label="Footer">
      <a href="<?= e($base) ?>#about">About</a><a href="<?= e($base) ?>#services">Services</a><a href="<?= e($base) ?>#menu">Menu</a><a href="<?= e($base) ?>#gallery">Gallery</a><a href="/blog/">Blog</a><a href="<?= e($base) ?>#contact">Contact</a>
    </nav>
    <div class="foot__meta" data-track-location="footer">
      <?php if (!empty($b['instagram'])): ?><a href="https://www.instagram.com/<?= e($b['instagram']) ?>/" target="_blank" rel="noopener">Instagram</a><?php endif; ?>
      <?php if (!empty($b['fssai'])): ?><p>FSSAI Lic. No. <?= e($b['fssai']) ?></p><?php endif; ?>
      <?php $servedNames = array_map(fn ($x) => $x['name'], array_filter(content('areas.list', []), 'visible')); ?>
      <?php if ($servedNames): ?><p>Serving <?= e(implode(', ', $servedNames)) ?></p><?php endif; ?>
    </div>
  </div>
  <div class="wrap foot__base">
    <p>&copy; <?= date('Y') ?> <?= e($name) ?>. All rights reserved.</p>
    <?php if (content('footer.credit') === true): ?><p>Website by One Man Marketing</p><?php endif; ?>
  </div>
</footer>

<?php $waH = wa_href($waMsg); $waExt = str_starts_with($waH, 'http') ? ' target="_blank" rel="noopener"' : ''; ?>
<div class="float" data-track-location="float">
  <a class="float__btn float__btn--call" href="<?= e(tel_href()) ?>" aria-label="Call Olive Catering Company">
    <svg viewBox="0 0 24 24" width="24" height="24" aria-hidden="true"><path fill="currentColor" d="M6.6 10.8a15.2 15.2 0 0 0 6.6 6.6l2.2-2.2a1 1 0 0 1 1-.25 11.4 11.4 0 0 0 3.6.57 1 1 0 0 1 1 1V20a1 1 0 0 1-1 1A17 17 0 0 1 3 4a1 1 0 0 1 1-1h3.5a1 1 0 0 1 1 1c0 1.25.2 2.45.57 3.57a1 1 0 0 1-.25 1Z"/></svg>
  </a>
  <a class="float__btn float__btn--wa" href="<?= e($waH) ?>"<?= $waExt ?> aria-label="Chat on WhatsApp">
    <svg viewBox="0 0 24 24" width="26" height="26" aria-hidden="true"><path fill="currentColor" d="M12 2a10 10 0 0 0-8.6 15.1L2 22l5-1.3A10 10 0 1 0 12 2Zm0 18.2a8.2 8.2 0 0 1-4.2-1.2l-.3-.2-3 .8.8-2.9-.2-.3A8.2 8.2 0 1 1 12 20.2Zm4.5-6.1c-.2-.1-1.5-.7-1.7-.8-.2-.1-.4-.1-.6.1l-.8 1c-.1.2-.3.2-.5.1a6.7 6.7 0 0 1-3.3-2.9c-.3-.4.2-.4.7-1.3.1-.2 0-.3 0-.4l-.8-1.8c-.2-.5-.4-.4-.6-.4h-.5a1 1 0 0 0-.7.3 3 3 0 0 0-.9 2.2 5.2 5.2 0 0 0 1.1 2.8 11.9 11.9 0 0 0 4.6 4c1.7.7 2.4.8 3.2.7.5-.1 1.5-.6 1.8-1.2.2-.6.2-1.1.1-1.2l-.4-.2Z"/></svg>
  </a>
</div>

<nav class="actionbar" aria-label="Contact Olive" data-track-location="sticky_bar">
  <a class="actionbar__btn actionbar__btn--call" href="<?= e(tel_href()) ?>">
    <svg viewBox="0 0 24 24" width="20" height="20" aria-hidden="true"><path fill="currentColor" d="M6.6 10.8a15.2 15.2 0 0 0 6.6 6.6l2.2-2.2a1 1 0 0 1 1-.25 11.4 11.4 0 0 0 3.6.57 1 1 0 0 1 1 1V20a1 1 0 0 1-1 1A17 17 0 0 1 3 4a1 1 0 0 1 1-1h3.5a1 1 0 0 1 1 1c0 1.25.2 2.45.57 3.57a1 1 0 0 1-.25 1Z"/></svg>
    Call now
  </a>
  <a class="actionbar__btn actionbar__btn--wa" href="<?= e($waH) ?>"<?= $waExt ?>>
    <svg viewBox="0 0 24 24" width="20" height="20" aria-hidden="true"><path fill="currentColor" d="M12 2a10 10 0 0 0-8.6 15.1L2 22l5-1.3A10 10 0 1 0 12 2Zm0 18.2a8.2 8.2 0 0 1-4.2-1.2l-.3-.2-3 .8.8-2.9-.2-.3A8.2 8.2 0 1 1 12 20.2Z"/></svg>
    WhatsApp
  </a>
</nav>

<?php $pop = content('popup', []); if (!empty($pop['on'])): ?>
<dialog class="qe" id="quick-enquiry" aria-labelledby="qe-title" data-delay="<?= (int) ($pop['delay_seconds'] ?? 20) ?>">
  <button class="qe__close" type="button" aria-label="Close">&times;</button>
  <p class="eyebrow">Get a quote</p>
  <h2 class="qe__title" id="qe-title"><?= e($pop['heading'] ?? '') ?></h2>
  <p class="muted"><?= e($pop['text'] ?? '') ?></p>
  <form class="form qe__form" action="/enquiry.php" method="post" id="qe-form" data-olive-form>
    <input type="hidden" name="form_token" value="<?= e(form_token()) ?>">
    <input type="hidden" name="form_source" value="popup">
    <div class="hp" aria-hidden="true"><label>Leave this empty <input type="text" name="company_website" tabindex="-1" autocomplete="off"></label></div>
    <div class="field"><label for="qe-name">Your name</label><input id="qe-name" name="name" type="text" autocomplete="name" required maxlength="80"></div>
    <div class="field"><label for="qe-phone">Mobile number</label><input id="qe-phone" name="phone" type="tel" inputmode="tel" autocomplete="tel" required maxlength="20" placeholder="10-digit mobile"></div>
    <div class="field"><label for="qe-type">Type of event</label>
      <select id="qe-type" name="event_type" required><option value="">Choose one</option><?php foreach (OLIVE_EVENT_TYPES as $t): ?><option><?= e($t) ?></option><?php endforeach; ?></select>
    </div>
    <button class="btn btn--cta btn--block" type="submit">Call me back</button>
    <p class="form-note">We only use your number to reply to this enquiry. <a href="/privacy.php">Privacy</a></p>
  </form>
</dialog>
<?php endif; ?>

<dialog class="lightbox" id="lightbox" aria-label="Photo">
  <button class="lightbox__close" type="button" aria-label="Close photo">&times;</button>
  <img alt="">
</dialog>

<script src="<?= e(asset('assets/js/attribution.js')) ?>" defer></script>
<script src="<?= e(asset('assets/js/main.js')) ?>" defer></script>
</body>
</html>
