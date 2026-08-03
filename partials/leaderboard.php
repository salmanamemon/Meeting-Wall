<?php
/** Leaderboard: the top 3 distinct scores as rank tiers (1/2/3). Everyone sharing a score shares its
    rank number (e.g. two people on 4 are both "1"); all top-score (rank 1) people get the crown + highlight.
    Rows page 3 at a time, cross-fading. Plus a pinned row mirroring the active swiper slide.
    Expects $leaders (full ranked list, desc) and $wall['leaders'] (full ranking for lookups). */

// Competition rank by score across ALL scores (used by the pinned active-slide row).
$allCounts = array_values(array_unique(array_column($wall['leaders'], 'count')));
rsort($allCounts);
$rankByCount = [];
foreach ($allCounts as $position => $count) $rankByCount[$count] = $position + 1;
$rankByOwner = [];
foreach ($wall['leaders'] as $leader) {
    $rankByOwner[$leader['name']] = ['rank' => $rankByCount[$leader['count']] ?? '', 'count' => $leader['count']];
}

// Keep only the top 3 distinct scores; tag each leader with its tier rank (1/2/3).
$topThreeCounts = array_slice($allCounts, 0, 3);
$topLeaders = [];
foreach ($leaders as $leader) {
    $tierIndex = array_search($leader['count'], $topThreeCounts, true);
    if ($tierIndex === false) continue;
    $leader['rank'] = $tierIndex + 1;
    $topLeaders[] = $leader;
}
$leaderPages = array_chunk($topLeaders, 3);
?>
<section class="card leaderboard">
  <div class="card-head"><h3>Leaderboard</h3><span class="badge">THIS WEEK</span></div>
  <div class="lb-list"<?= count($leaderPages) > 1 ? ' data-rotate="1"' : '' ?>>
    <?php foreach ($leaderPages as $pageIndex => $pageLeaders): ?>
    <div class="lb-page<?= $pageIndex === 0 ? ' active' : '' ?>">
      <?php foreach ($pageLeaders as $leader): ?>
        <div class="lb-row<?= $leader['rank'] === 1 ? ' lead' : '' ?>">
          <span class="rank"><?= $leader['rank'] ?></span>
          <?= avatarHtml($leader['name'], $leader['photo'] ?? null) ?>
          <span class="lb-name"><?= escapeHtml($leader['name']) ?><?= $leader['rank'] === 1 ? ' &#128081;' : '' ?></span>
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
