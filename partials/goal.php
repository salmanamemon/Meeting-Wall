<?php /** Per-user daily goal — the active slide user's meetings today, capped at $goal.
    Expects $done, $goal, $percent (initial, first slide) and $todayCountByOwner (for the client to follow slides). */ ?>
<div class="goal" data-goal="<?= $goal ?>">
  <p class="mini-label">DAILY GOAL</p>
  <div class="goal-bar"><span id="goal-fill" style="width:<?= $percent ?>%"></span></div>
  <p class="goal-num"><span id="goal-done"><?= $done ?></span> / <?= $goal ?> <span class="pct" id="goal-percent"><?= $percent ?>%</span></p>
  <script type="application/json" id="goal-data"><?= json_encode($todayCountByOwner) ?></script>
</div>
