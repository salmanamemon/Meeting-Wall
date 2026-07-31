<?php /** QR card: scan to post a photo. Expects $qr, $uploadUrl. */ ?>
<section class="card qr">
  <img src="<?= h($qr) ?>" alt="QR code" width="110" height="110" loading="lazy">
  <div><p class="mini-label">JUST HAD A MEETING?</p><p class="qr-title">Scan and post your photo</p><p class="qr-url"><?= h(preg_replace('#^https?://#', '', $uploadUrl)) ?></p></div>
</section>
