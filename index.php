<?php
require __DIR__ . '/functions.php';
$user = requireLogin();
$error = '';
try { $items = stories(); } catch (Throwable $e) { $items = []; $error = 'Stories are unavailable right now. Please try again later.'; }
$signature = $error ? '' : storiesSignature($items);
if (isset($_GET['fragment'])) { header('Content-Type: application/json'); echo json_encode(['signature' => $signature, 'html' => renderWall($items, $error)]); exit; }
?>
<!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Meeting Wall</title><meta name="description" content="Live meeting scoreboard for the sales floor."><meta property="og:type" content="website"><meta property="og:title" content="Meeting Wall"><meta property="og:description" content="Live meeting scoreboard for the sales floor."><meta property="og:url" content="<?= h(appUrl('index.php')) ?>"><meta property="og:image" content="<?= h(appUrl('assets/images/dar-stories-share.png')) ?>"><meta name="twitter:card" content="summary_large_image"><link rel="icon" type="image/png" href="favicon.png"><link rel="preconnect" href="https://fonts.googleapis.com"><link rel="preconnect" href="https://fonts.gstatic.com" crossorigin><link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Archivo:wght@700;800;900&family=IBM+Plex+Mono:wght@500;600&family=IBM+Plex+Sans:wght@400;600&display=swap"><link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/Swiper/11.1.14/swiper-bundle.min.css"><link rel="stylesheet" href="<?= asset('css/wall.css') ?>"><script src="https://cdnjs.cloudflare.com/ajax/libs/Swiper/11.1.14/swiper-bundle.min.js"></script></head>
<body data-signature="<?= h($signature) ?>"><div id="wall"><?php include __DIR__ . '/wall.php'; ?></div><script src="<?= asset('assets/slider.js') ?>"></script></body></html>
