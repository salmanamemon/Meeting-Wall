<?php
/** Leaderboard: top 5 owners by this-week meetings, plus a dynamic 6th row driven by the active swiper slide.
    Expects $leaders (top 5) and $wall['leaders'] (full weekly ranking). */
// name => {rank, count} for the whole week, so the client can place whoever is on the active slide.
$rankMap = [];
foreach ($wall['leaders'] as $i => $l) $rankMap[$l['name']] = ['rank' => $i + 1, 'count' => $l['count']];
?>
<section class="card leaderboard">
  <div class="card-head"><h3>Leaderboard</h3><span class="badge">THIS WEEK</span></div>
  <?php foreach ($leaders as $i => $l): ?>
    <div class="lb-row<?= $i === 0 ? ' lead' : '' ?>">
      <span class="rank"><?= $i + 1 ?></span>
      <span class="avatar"><?= h(initials($l['name'])) ?></span>
      <span class="lb-name"><?= h($l['name']) ?><?= $i === 0 ? ' &#128081;' : '' ?></span>
      <span class="lb-count"><?= $l['count'] ?></span>
    </div>
  <?php endforeach; ?>
  <div class="lb-row lb-active" id="lb-active" hidden>
    <span class="rank"></span><span class="avatar"></span><span class="lb-name"></span><span class="lb-count"></span>
  </div>
  <script type="application/json" id="lb-data"><?= json_encode($rankMap) ?></script>
</section>
