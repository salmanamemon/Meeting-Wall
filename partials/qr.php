<?php /** QR card: scan to download the DanubeOne app. SVG is inlined so it renders regardless of the server's MIME config. */ ?>
<section class="card qr">
  <span class="qr-code" aria-label="QR code to download the DanubeOne app"><?php readfile(__DIR__ . '/../assets/images/QR.svg'); ?></span>
  <div><p class="mini-label">GET THE APP</p><p class="qr-title">Download DanubeOne</p><p class="qr-url">Scan to install</p></div>
</section>
