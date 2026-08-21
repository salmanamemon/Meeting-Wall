<?php
/** One leaderboard card (used 4x in the v1 dashboard): title + badge, top-3 distinct-score tiers,
	carousel through pages of 3. Reuses the wall's .card/.lb-list/.lb-page/.lb-row classes and CSS.
	Expects: $cardTitle, $cardBadge, $cardLeaders (ranked list, desc). */
$tiers = leaderboardTiers($cardLeaders);
$pages = array_chunk($tiers['leaders'], 3);
?>
<section class="card leaderboard lb-card">
	<div class="card-head">
		<h3><?= escapeHtml($cardTitle) ?></h3><span
			class="badge<?= $cardBadge === 'THIS WEEK' ? ' badge-week' : '' ?>"><?= escapeHtml($cardBadge) ?></span>
	</div>
	<?php if (!$pages): ?>
		<div class="lb-empty"><?= escapeHtml($cardEmpty ?? 'No meetings yet.') ?></div>
	<?php else: ?>
		<div class="lb-list" <?= count($pages) > 1 ? ' data-rotate="1"' : '' ?>>
			<?php foreach ($pages as $pageIndex => $page): ?>
				<div class="lb-page<?= $pageIndex === 0 ? ' active' : '' ?>">
					<?php foreach ($page as $leader): ?>
						<div class="lb-row<?= $leader['rank'] === 1 ? ' lead' : '' ?>">
							<span class="rank"><?= ordinal($leader['rank']) ?></span>
							<?= avatarHtml($leader['name'], $leader['photo'] ?? null) ?>
							<span
								class="lb-name"><?= escapeHtml($leader['name']) ?><?= $leader['rank'] === 1 ? ' &#128081;' : '' ?></span>
							<span class="lb-count"><?= $leader['count'] ?></span>
						</div>
					<?php endforeach; ?>
				</div>
			<?php endforeach; ?>
		</div>
	<?php endif; ?>
</section>
