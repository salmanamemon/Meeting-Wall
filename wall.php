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
// Top-bar totals stay all-meetings (unchanged). THIS WEEK / MEETINGS TODAY = summed cnt from the leaderboard API.
$weekAll = leaderboard('THIS_WEEK') ?: $wall['leaders'];
$wall['week'] = array_sum(array_column($weekAll, 'count'));
$todayAll = leaderboard('TODAY');
$wall['today'] = $todayAll ? array_sum(array_column($todayAll, 'count')) : $wall['today'];
// Leaderboard rows: agency/broker meetings only, both periods — the card rotates WEEK <-> TODAY.
$weekAgency = leaderboard('THIS_WEEK', 'agency');
$todayAgency = leaderboard('TODAY', 'agency');
// Match hero cards to profile photos by OwnerId (use the widest set).
$photoByOwnerId = [];
foreach ($weekAll as $leader) {
    if (!empty($leader['ownerId']) && !empty($leader['photo'])) $photoByOwnerId[$leader['ownerId']] = $leader['photo'];
}
$streak = $weekAll[0] ?? null;
$phrases = tickerPhrases();
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
