<?php /** Top bar: brand, today/week stats, live clock, controls. Expects $wall. */ ?>
<header class="topbar">
  <div class="brand"><p class="eyebrow">SALES FLOOR</p><h1>MEETING WALL</h1></div>
  <div class="topbar-right">
    <div class="stat"><p class="stat-label">MEETINGS TODAY</p><p class="stat-num"><?= $wall['today'] ?></p></div>
    <div class="stat"><p class="stat-label">THIS WEEK</p><p class="stat-num pink"><?= $wall['week'] ?></p></div>
    <div class="clock"><p id="clock" class="clock-time">--:--</p><p id="clock-date" class="clock-date"></p></div>
    <div class="icons">
      <button id="fs" class="icon" aria-label="Fullscreen">&#9974;</button>
      <form method="post" action="logout.php" class="icon-form"><input type="hidden" name="csrf" value="<?= h(csrf()) ?>"><button class="icon" aria-label="Sign out">&#9211;</button></form>
    </div>
  </div>
</header>
