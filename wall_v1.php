<?php
/** Meeting Wall v1 (#wall contents): a 2x2 leaderboard grid + rail, no hero slider. global $config. */
global $config;
// Top-bar totals (all meetings), same as the main wall.
$weekAll = leaderboard('THIS_WEEK');
$todayAll = leaderboard('TODAY');
$wall = ['week' => array_sum(array_column($weekAll, 'count')), 'today' => array_sum(array_column($todayAll, 'count'))];
// The four leaderboards.
$brokerWeek = leaderboard('THIS_WEEK', 'agency');
$brokerToday = leaderboard('TODAY', 'agency');
$customerWeek = leaderboard('THIS_WEEK', 'customer');
$customerToday = leaderboard('TODAY', 'customer');
$goal = max(1, (int) ($config['daily_goal'] ?? 2));
$streak = $brokerWeek[0] ?? ($weekAll[0] ?? null);
$phrases = tickerPhrases();
$partialsDir = __DIR__ . '/partials';
// Consider the API down only if every leaderboard came back empty.
$apiDown = !$weekAll && !$todayAll && !$brokerWeek && !$brokerToday && !$customerWeek && !$customerToday;
?>
<?php include "$partialsDir/topbar.php"; ?>
<?php if ($apiDown): ?>
	<section class="state error" role="alert">Leaderboard is unavailable right now. Please try again later.</section>
<?php else: ?>
	<div class="lb-quad">
		<?php $cardTitle = 'BROKER MEETINGS';
		$cardBadge = 'TODAY';
		$cardLeaders = $brokerToday;
		include "$partialsDir/leaderboard_card.php"; ?>
		<?php $cardTitle = 'BROKER MEETINGS';
		$cardBadge = 'THIS WEEK';
		$cardLeaders = $brokerWeek;
		include "$partialsDir/leaderboard_card.php"; ?>
		<?php $cardTitle = 'CUSTOMER MEETINGS';
		$cardBadge = 'TODAY';
		$cardLeaders = $customerToday;
		include "$partialsDir/leaderboard_card.php"; ?>
		<?php $cardTitle = 'CUSTOMER MEETINGS';
		$cardBadge = 'THIS WEEK';
		$cardLeaders = $customerWeek;
		include "$partialsDir/leaderboard_card.php"; ?>
	</div>
	<div class="goal-row">
		<?php include "$partialsDir/goal_v1.php"; ?>
		<?php if ($streak)
			include "$partialsDir/streak.php"; ?>
		<?php include "$partialsDir/qr.php"; ?>
	</div>
<?php endif; ?>
<?php include "$partialsDir/ticker.php"; ?>
