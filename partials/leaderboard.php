<?php
/** Leaderboard: rotates between THIS WEEK and TODAY, each showing the top 3 distinct scores as rank tiers
    (agency/broker meetings). Ties share a rank; all rank-1 people get the crown + highlight. Pages of 3
    cross-fade in sequence (week pages, then today pages, on loop) and the badge follows the active period.
    Plus a pinned row mirroring the active swiper slide. Expects $weekAgency, $todayAgency. */
$weekTiers = leaderboardTiers($weekAgency);
$todayTiers = leaderboardTiers($todayAgency);
$views = [
    ['label' => 'THIS WEEK', 'pages' => array_chunk($weekTiers['leaders'], 3)],
    ['label' => 'TODAY', 'pages' => array_chunk($todayTiers['leaders'], 3)],
];
$pageCount = count($views[0]['pages']) + count($views[1]['pages']);
?>
<section class="card leaderboard">
  <div class="card-head"><h3>Leaderboard</h3><span class="badge" id="lb-badge">THIS WEEK</span></div>
  <div class="lb-list"<?= $pageCount > 1 ? ' data-rotate="1"' : '' ?>>
    <?php foreach ($views as $view): foreach ($view['pages'] as $page): ?>
    <div class="lb-page" data-period="<?= escapeHtml($view['label']) ?>">
      <?php foreach ($page as $leader): ?>
        <div class="lb-row<?= $leader['rank'] === 1 ? ' lead' : '' ?>">
          <span class="rank"><?= $leader['rank'] ?></span>
          <?= avatarHtml($leader['name'], $leader['photo'] ?? null) ?>
          <span class="lb-name"><?= escapeHtml($leader['name']) ?><?= $leader['rank'] === 1 ? ' &#128081;' : '' ?></span>
          <span class="lb-count"><?= $leader['count'] ?></span>
        </div>
      <?php endforeach; ?>
    </div>
    <?php endforeach; endforeach; ?>
  </div>
  <div class="lb-row lb-active" id="lb-active" hidden>
    <span class="rank"></span><span class="avatar"></span><span class="lb-name"></span><span class="lb-count"></span>
  </div>
  <script type="application/json" id="lb-ranks-week"><?= json_encode($weekTiers['ranks']) ?></script>
  <script type="application/json" id="lb-ranks-today"><?= json_encode($todayTiers['ranks']) ?></script>
</section>
