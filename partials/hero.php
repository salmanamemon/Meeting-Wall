<?php /** Hero: Swiper of meeting cards. Expects $items. */ ?>
<div class="hero-slider swiper" aria-label="Meetings">
  <div class="swiper-wrapper"><?php include __DIR__ . '/slides.php'; ?></div>
  <div class="swiper-pagination"></div>
  <div class="just-now" id="just-now" hidden><span class="bolt">&#9889;</span> JUST NOW</div>
</div>
