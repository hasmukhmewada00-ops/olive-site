<?php /** Blog cards grid. Expects $cards (array of posts). */ ?>
<ul class="bcards">
  <?php foreach ($cards as $c): ?>
    <li class="bcard">
      <a class="bcard__link" href="<?= e(blog_url($c)) ?>">
        <img class="bcard__img" src="<?= e(blog_img($c, 960)) ?>" srcset="<?= e(blog_img($c, 480)) ?> 480w, <?= e(blog_img($c, 960)) ?> 960w" sizes="(min-width: 900px) 33vw, 92vw" width="960" height="720" alt="<?= e($c['image_alt'] ?? '') ?>" loading="lazy" decoding="async">
        <span class="bcard__meta"><?= e(blog_date((string) $c['date'])) ?> &middot; <?= blog_read_minutes($c) ?> min read</span>
        <span class="bcard__title"><?= e($c['title']) ?></span>
        <span class="bcard__desc"><?= e($c['description']) ?></span>
      </a>
    </li>
  <?php endforeach; ?>
</ul>
