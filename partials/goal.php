<?php /** Daily team goal progress. Expects $done, $goal, $pct. */ ?>
<div class="goal">
  <p class="mini-label">DAILY TEAM GOAL</p>
  <div class="goal-bar"><span style="width:<?= $pct ?>%"></span></div>
  <p class="goal-num"><?= $done ?> / <?= $goal ?> <span class="pct"><?= $pct ?>%</span></p>
</div>
