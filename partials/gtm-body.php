<?php
/** Immediately after the opening <body> tag. */
$gtm = (string) cfg('gtm_id', '');
if (preg_match('/^GTM-[A-Z0-9]+$/', $gtm)): ?>
<!-- Google Tag Manager (noscript) -->
<noscript><iframe src="https://www.googletagmanager.com/ns.html?id=<?= e($gtm) ?>"
height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
<!-- End Google Tag Manager (noscript) -->
<?php endif; ?>
