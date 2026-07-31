<?php
/** Meeting Wall (#wall contents). Computes derived data, then composes the section partials. Expects $items, $error, global $config. */
global $config;
$wall = wallData($items);
$items = todayMeetings($items); // only today's cards (9AM–9PM) — previous days don't show
$goal = max(1, (int) ($config['daily_goal'] ?? 2));
$done = min($wall['today'], $goal); // bar caps at the goal: 1/2 or 2/2
$pct = (int) round($done / $goal * 100);
$leaders = array_slice($wall['leaders'], 0, 5);
$streak = $leaders[0] ?? null;
$phrases = $config['ticker'] ?? ['BOOKED BEATS PERFECT.', 'MOMENTUM IS A TEAM SPORT.'];
$P = __DIR__ . '/partials';
?>
<?php include "$P/topbar.php"; ?>
<?php if ($error): ?>
  <section class="state error" role="alert"><?= h($error) ?></section>
<?php elseif (!$items): ?>
  <section class="state">No meetings logged yet.</section>
<?php else: ?>
<div class="grid">
  <?php include "$P/hero.php"; ?>
  <aside class="rail">
    <?php include "$P/leaderboard.php"; ?>
    <?php if ($streak) include "$P/streak.php"; ?>
  </aside>
</div>
<?php include "$P/goal.php"; ?>
<?php endif; ?>
<?php include "$P/ticker.php"; ?>
