<?php
/** Streak card: rotates through competition "moments" derived from the THIS_WEEK agency board
    ($brokerWeek). Reuses the .lb-list[data-rotate]/.lb-page cross-fade (slider_v1.js) — no JS needed.
    Moments self-gate on data, so early week shows only ON A STREAK; rivalry cards appear as counts grow.
    Expects $streak (guard) and $brokerWeek. */
$board = array_values(array_filter($brokerWeek ?? [$streak], fn($leader) => ($leader['count'] ?? 0) > 0));
$leader = $board[0] ?? null;
$runnerUp = $board[1] ?? null;
$gap = ($leader && $runnerUp) ? $leader['count'] - $runnerUp['count'] : null;

$moments = [];
if ($leader) {
    $moments[] = ['ico' => '&#128293;', 'label' => 'ON A STREAK', 'text' => escapeHtml($leader['name']) . ' &mdash; ' . (int) $leader['count'] . ' this week'];
}
if ($runnerUp && $gap <= 2) { // close race — make the rivalry visible
    $moments[] = ['ico' => '&#9876;&#65039;', 'label' => 'NECK AND NECK', 'text' => escapeHtml($leader['name']) . ' vs ' . escapeHtml($runnerUp['name'])];
}
// Everyone tied at second place (same count, behind #1) gets a slide chasing #1 — same gap for all.
$secondCount = null;
foreach ($board as $person) {
    if ($person['count'] < $leader['count']) { $secondCount = $person['count']; break; }
}
if ($secondCount !== null) {
    $behind = $leader['count'] - $secondCount;
    foreach ($board as $person) {
        if ($person['count'] !== $secondCount) continue;
        $moments[] = ['ico' => '&#127937;', 'label' => 'CHASING THE LEAD', 'text' => escapeHtml($person['name']) . ' &mdash; ' . $behind . ' to catch ' . escapeHtml($leader['name'])];
    }
}
$activeToday = count(array_filter($brokerToday ?? [], fn($broker) => ($broker['count'] ?? 0) > 0));
if ($activeToday >= 2) { // the whole floor is moving today
    $moments[] = ['ico' => '&#129309;', 'label' => 'WHOLE FLOOR MOVING', 'text' => $activeToday . ' brokers active today'];
}
// Customer-meeting race this week: everyone tied at second place chases #1 (same gap for all).
$customerBoard = array_values(array_filter($customerWeek ?? [], fn($leader) => ($leader['count'] ?? 0) > 0));
$customerLeader = $customerBoard[0] ?? null;
if ($customerLeader) {
    $customerSecondCount = null;
    foreach ($customerBoard as $person) {
        if ($person['count'] < $customerLeader['count']) { $customerSecondCount = $person['count']; break; }
    }
    if ($customerSecondCount !== null) {
        $customerBehind = $customerLeader['count'] - $customerSecondCount;
        foreach ($customerBoard as $person) {
            if ($person['count'] !== $customerSecondCount) continue;
            $moments[] = ['ico' => '&#127942;', 'label' => 'CUSTOMER RACE', 'text' => escapeHtml($person['name']) . ' &mdash; ' . $customerBehind . ' to catch ' . escapeHtml($customerLeader['name'])];
        }
    }
}
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
