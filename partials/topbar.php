<?php /** Top bar: brand, today/week stats, live clock, controls. Expects $wall. */ ?>
<header class="topbar">
	<div class="brand">
		<p class="eyebrow">SALES FLOOR</p>
		<h1>MEETING WALL</h1>
	</div>
	<div class="topbar-right">
		<div class="stat">
			<p class="stat-label">MEETINGS TODAY</p>
			<p class="stat-num"><?= $wall['today'] ?></p>
			<?php if ($wall['todayRms']): ?>
				<p class="stat-sub">BY <?= $wall['todayRms'] ?> RM<?= $wall['todayRms'] == 1 ? '' : 's' ?></p>
			<?php endif; ?>
		</div>
		<div class="stat">
			<p class="stat-label">MEETINGS THIS WEEK</p>
			<p class="stat-num pink"><?= $wall['week'] ?></p>
			<?php if ($wall['weekRms']): ?>
				<p class="stat-sub">BY <?= $wall['weekRms'] ?> RM<?= $wall['weekRms'] == 1 ? '' : 's' ?></p><?php endif; ?>
		</div>
		<div class="clock">
			<p id="clock" class="clock-time">--:--</p>
			<p id="clock-date" class="clock-date"></p>
		</div>
		<div class="icons">
			<button id="fs" class="icon" aria-label="Fullscreen">&#9974;</button>
			<form method="post" action="logout.php" class="icon-form"><input type="hidden" name="csrf"
					value="<?= escapeHtml(csrf()) ?>"><button class="icon" aria-label="Sign out">&#9211;</button></form>
		</div>
	</div>
</header>
