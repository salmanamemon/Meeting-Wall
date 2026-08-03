<?php
/** Meeting Wall (#wall contents). Computes derived data, then composes the section partials. Expects $items, $error, global $config. */
global $config;
$wall = wallData($items);
$items = todayMeetings($items); // only today's cards (9AM–9PM) — previous days don't show
$goal = max(1, (int) ($config['daily_goal'] ?? 2));
// The goal is per user: each owner's "Broker Meet" activities today (not "Customer Meeting"), capped at the goal.
// The bar follows the active slide.
$todayCountByOwner = [];
foreach ($items as $meeting) {
    if (($meeting['Activity_Type__c'] ?? '') !== 'Broker Meet') continue;
    $ownerName = $meeting['OwnerName'] ?? 'Unassigned';
    $todayCountByOwner[$ownerName] = ($todayCountByOwner[$ownerName] ?? 0) + 1;
}
$firstOwner = $items[0]['OwnerName'] ?? '';
$done = min($todayCountByOwner[$firstOwner] ?? 0, $goal); // initial value for the first slide's user
$percent = (int) round($done / $goal * 100);
// Weekly leaderboard comes from the dedicated API; fall back to the stories-derived ranking if it's down.
$rankedLeaders = leaderboard() ?: $wall['leaders'];
$wall['leaders'] = $rankedLeaders; // full ranking used for the pinned 6th-row rank lookup
$wall['week'] = array_sum(array_column($rankedLeaders, 'count')); // whole-week total from the leaderboard API
$leaders = $rankedLeaders;
// Match hero cards to leaderboard profile photos by OwnerId (activity_list carries OwnerId too).
$photoByOwnerId = [];
foreach ($rankedLeaders as $leader) {
    if (!empty($leader['ownerId']) && !empty($leader['photo'])) $photoByOwnerId[$leader['ownerId']] = $leader['photo'];
}
$streak = $leaders[0] ?? null;
$phrases = $config['ticker'] ?? ['BOOKED BEATS PERFECT.', 'MOMENTUM IS A TEAM SPORT.'];
$partialsDir = __DIR__ . '/partials';
?>
<?php include "$partialsDir/topbar.php"; ?>
<?php if ($error): ?>
  <section class="state error" role="alert"><?= escapeHtml($error) ?></section>
<?php elseif (!$items): ?>
  <section class="state">No meetings logged yet today.</section>
<?php else: ?>
<div class="grid">
  <?php include "$partialsDir/hero.php"; ?>
  <aside class="rail">
    <?php include "$partialsDir/leaderboard.php"; ?>
    <?php if ($streak) include "$partialsDir/streak.php"; ?>
    <?php include "$partialsDir/qr.php"; ?>
  </aside>
</div>
<?php include "$partialsDir/goal.php"; ?>
<?php endif; ?>
<?php include "$partialsDir/ticker.php"; ?>
