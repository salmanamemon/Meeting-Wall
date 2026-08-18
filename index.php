<?php
require __DIR__ . '/functions.php';
$user = requireLogin();
// v1 has no activity slider — all data comes from the leaderboard API. Signature drives the 30s live poll
// (refreshes on leaderboard changes + at midnight); stays non-empty so polling always runs and self-recovers.
include __DIR__ . '/partials/video_data.php'; // sets $videoToday, $videoWeek (separate video endpoint)
$signature = md5(json_encode([
	leaderboard('THIS_WEEK'),
	leaderboard('TODAY'),
	leaderboard('THIS_WEEK', 'agency'),
	leaderboard('TODAY', 'agency'),
	leaderboard('THIS_WEEK', 'customer'),
	leaderboard('TODAY', 'customer'),
	$videoToday,
	$videoWeek,
	date('Y-m-d'),
]));
if (isset($_GET['fragment'])) {
	header('Content-Type: application/json');
	echo json_encode(['signature' => $signature, 'html' => renderWallV1()]);
	exit;
}
?>
<!doctype html>
<html lang="en">

<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width,initial-scale=1">
	<title>Meeting Wall — Leaderboards</title>
	<meta name="description" content="Live leaderboard dashboard for the sales floor.">
	<link rel="icon" type="image/png" href="favicon.png">
	<link rel="preconnect" href="https://fonts.googleapis.com">
	<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
	<link rel="stylesheet"
		href="https://fonts.googleapis.com/css2?family=Archivo:wght@700;800;900&family=IBM+Plex+Mono:wght@500;600&family=IBM+Plex+Sans:wght@400;600&display=swap">
	<link rel="stylesheet" href="<?= asset('css/wall.css') ?>">
	<link rel="stylesheet" href="<?= asset('css/wall_v1.css') ?>">
	<script src="https://cdn.jsdelivr.net/npm/canvas-confetti@1.9.3/dist/confetti.browser.min.js"></script>
</head>

<body data-signature="<?= escapeHtml($signature) ?>">
	<div id="wall"><?php include __DIR__ . '/wall_v1.php'; ?></div>
	<script src="<?= asset('assets/slider_v1.js') ?>"></script>
</body>

</html>
