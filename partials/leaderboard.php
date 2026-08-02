<?php
/** Leaderboard: everyone sorted high→low, shown 3 rows per page. Pages cross-fade through the full list,
    so ties (repeated counts) and lower scores all get their turn. Plus a pinned row for the active swiper
    slide. Expects $leaders (full ranked list) and $wall['leaders'] (full ranking for lookups). */
$leaderPages = array_chunk($leaders, 3);
$rankByOwner = [];
foreach ($wall['leaders'] as $rankIndex => $leader) {
    $rankByOwner[$leader['name']] = ['rank' => $rankIndex + 1, 'count' => $leader['count']];
}
?>
<section class="card leaderboard">
  <div class="card-head"><h3>Leaderboard</h3><span class="badge">THIS WEEK</span></div>
  <div class="lb-list"<?= count($leaderPages) > 1 ? ' data-rotate="1"' : '' ?>>
    <?php foreach ($leaderPages as $pageIndex => $pageLeaders): ?>
    <div class="lb-page<?= $pageIndex === 0 ? ' active' : '' ?>">
      <?php foreach ($pageLeaders as $positionInPage => $leader): $rank = $pageIndex * 3 + $positionInPage + 1; ?>
        <div class="lb-row<?= $rank === 1 ? ' lead' : '' ?>">
          <span class="rank"><?= $rank ?></span>
          <?= avatarHtml($leader['name'], $leader['photo'] ?? null) ?>
          <span class="lb-name"><?= escapeHtml($leader['name']) ?><?= $rank === 1 ? ' &#128081;' : '' ?></span>
          <span class="lb-count"><?= $leader['count'] ?></span>
        </div>
      <?php endforeach; ?>
    </div>
    <?php endforeach; ?>
  </div>
  <div class="lb-row lb-active" id="lb-active" hidden>
    <span class="rank"></span><span class="avatar"></span><span class="lb-name"></span><span class="lb-count"></span>
  </div>
  <script type="application/json" id="lb-data"><?= json_encode($rankByOwner) ?></script>
</section>
