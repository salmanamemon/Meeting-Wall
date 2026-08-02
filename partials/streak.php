<?php /** Streak card: top owner today. Expects $streak. */ ?>
<section class="card streak">
  <span class="streak-ico">&#128293;</span>
  <div><p class="mini-label">ON A STREAK</p><p class="streak-name"><?= escapeHtml($streak['name']) ?> &mdash; <?= $streak['count'] ?> this week</p></div>
</section>
