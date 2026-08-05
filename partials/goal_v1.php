<?php
/** Goal v1: carousels through today's brokers, showing photo + name + their broker-meet progress (X/goal).
    Keeps the existing goal bar/number styling. Expects $brokerToday (agency TODAY leaderboard) and $goal. */
$goalPeople = [];
foreach ($brokerToday as $broker) {
    $goalPeople[] = [
        'name' => $broker['name'],
        'initials' => initials($broker['name']),
        'photo' => $broker['photo'] ?? '',
        'done' => min((int) $broker['count'], $goal),
    ];
}
$first = $goalPeople[0] ?? ['name' => '', 'initials' => '', 'photo' => '', 'done' => 0];
$firstPercent = (int) round($first['done'] / $goal * 100);
?>
<div class="goal goal-v1" data-goal="<?= $goal ?>">
  <span class="avatar goal-avatar" id="goal-avatar"><?= escapeHtml($first['initials']) ?><?php if ($first['photo']): ?><img src="<?= escapeHtml($first['photo']) ?>" alt="" onerror="this.remove()"><?php endif; ?></span>
  <div class="goal-person">
    <p class="mini-label">DAILY BROKER MEET</p>
    <p class="goal-person-name" id="goal-name"><?= escapeHtml($first['name']) ?></p>
  </div>
  <div class="goal-bar"><span id="goal-fill" style="width:<?= $firstPercent ?>%"></span></div>
  <p class="goal-num"><span id="goal-done"><?= $first['done'] ?></span> / <?= $goal ?> <span class="pct" id="goal-percent"><?= $firstPercent ?>%</span></p>
  <div class="just-now" id="just-now" hidden><span class="bolt">&#9889;</span> GOAL COMPLETE</div>
  <script type="application/json" id="goal-people"><?= json_encode($goalPeople) ?></script>
</div>
