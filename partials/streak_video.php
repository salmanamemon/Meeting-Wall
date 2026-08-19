<?php
/** Video competition card: rotating "moments" from the video-upload boards — THIS WEEK ($videoWeek) and
    TODAY ($videoToday), set by video_data.php. Occupies the goal-row slot where the QR "GET THE APP" card
    sits. Reuses the .streak card + .lb-list[data-rotate]/.lb-page cross-fade (slider_v1.js), like streak.php.
    Moments self-gate on data. Renders nothing when there are no video uploads. */

// Build the moments for one board (a period). Returns [] when the board is empty.
$buildVideoMoments = function (array $rawBoard, string $periodText, string $periodTag): array {
    $board = array_values(array_filter($rawBoard, fn($leader) => ($leader['count'] ?? 0) > 0));
    $leader = $board[0] ?? null;
    if (!$leader) return [];
    $runnerUp = $board[1] ?? null;
    $gap = $runnerUp ? $leader['count'] - $runnerUp['count'] : null;

    $moments = [];
    $moments[] = ['ico' => '&#127916;', 'label' => 'TOP UPLOADER &middot; ' . $periodTag, 'text' => escapeHtml($leader['name']) . ' &mdash; ' . (int) $leader['count'] . ' videos ' . $periodText];
    if ($runnerUp && $gap <= 2) { // close race — make the rivalry visible
        $moments[] = ['ico' => '&#9876;&#65039;', 'label' => 'NECK AND NECK &middot; ' . $periodTag, 'text' => escapeHtml($leader['name']) . ' vs ' . escapeHtml($runnerUp['name'])];
    }
    // Everyone tied at second place chases #1 (same gap for all).
    $secondCount = null;
    foreach ($board as $person) {
        if ($person['count'] < $leader['count']) { $secondCount = $person['count']; break; }
    }
    if ($secondCount !== null) {
        $behind = $leader['count'] - $secondCount;
        foreach ($board as $person) {
            if ($person['count'] !== $secondCount) continue;
            $moments[] = ['ico' => '&#127937;', 'label' => 'CHASING THE LEAD &middot; ' . $periodTag, 'text' => escapeHtml($person['name']) . ' &mdash; ' . $behind . ' to catch ' . escapeHtml($leader['name'])];
        }
    }
    return $moments;
};

$moments = array_merge(
    $buildVideoMoments($videoWeek ?? [], 'this week', 'THIS WEEK'),
    $buildVideoMoments($videoToday ?? [], 'today', 'TODAY')
);
if (!$moments) return;
?>
<section class="card streak">
  <div class="lb-list"<?= count($moments) > 1 ? ' data-rotate="1"' : '' ?>>
    <?php foreach ($moments as $index => $moment): ?>
    <div class="lb-page streak-moment<?= $index === 0 ? ' active' : '' ?>">
      <span class="streak-ico"><?= $moment['ico'] ?></span>
      <div><p class="mini-label"><?= $moment['label'] ?></p><p class="streak-name"><?= $moment['text'] ?></p></div>
    </div>
    <?php endforeach; ?>
  </div>
</section>
