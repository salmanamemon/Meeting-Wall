<?php
/** Video competition card: rotating "moments" from THIS WEEK's video-upload board ($videoWeek, set by
    video_data.php). Occupies the goal-row slot where the QR "GET THE APP" card sits. Reuses the .streak
    card + .lb-list[data-rotate]/.lb-page cross-fade (slider_v1.js) — same as streak.php, no JS added.
    Moments self-gate on data. Renders nothing when there are no video uploads this week. */
$videoBoard = array_values(array_filter($videoWeek ?? [], fn($leader) => ($leader['count'] ?? 0) > 0));
$videoLeader = $videoBoard[0] ?? null;
if (!$videoLeader) return;
$videoRunnerUp = $videoBoard[1] ?? null;
$videoGap = $videoRunnerUp ? $videoLeader['count'] - $videoRunnerUp['count'] : null;

$moments = [];
$moments[] = ['ico' => '&#127916;', 'label' => 'TOP UPLOADER', 'text' => escapeHtml($videoLeader['name']) . ' &mdash; ' . (int) $videoLeader['count'] . ' videos this week'];
if ($videoRunnerUp && $videoGap <= 2) { // close race — make the rivalry visible
    $moments[] = ['ico' => '&#9876;&#65039;', 'label' => 'NECK AND NECK (VIDEOS)', 'text' => escapeHtml($videoLeader['name']) . ' vs ' . escapeHtml($videoRunnerUp['name'])];
}
// Everyone tied at second place chases #1 (same gap for all).
$videoSecondCount = null;
foreach ($videoBoard as $person) {
    if ($person['count'] < $videoLeader['count']) { $videoSecondCount = $person['count']; break; }
}
if ($videoSecondCount !== null) {
    $videoBehind = $videoLeader['count'] - $videoSecondCount;
    foreach ($videoBoard as $person) {
        if ($person['count'] !== $videoSecondCount) continue;
        $moments[] = ['ico' => '&#127937;', 'label' => 'CHASING THE LEAD (VIDEOS)', 'text' => escapeHtml($person['name']) . ' &mdash; ' . $videoBehind . ' to catch ' . escapeHtml($videoLeader['name'])];
    }
}
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
