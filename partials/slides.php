<?php /** Renders the hero meeting cards. Expects $items in scope. Shared by wall.php and the ?fragment=1 endpoint. */ ?>
<?php foreach ($items as $item):
  $owner = $item['OwnerName'] ?? 'Unassigned';
  $document = $item['checkOutDocumentLink'] ?? '';
  $photo = ($photoByOwnerId ?? [])[$item['OwnerId'] ?? ''] ?? null;
  $timestamp = meetingTs($item);
  $time = $timestamp ? date('g:i A', $timestamp) : '';
  $ago = $timestamp ? agoText($timestamp) : '';
?><article class="story swiper-slide" data-owner="<?= escapeHtml($owner) ?>" data-initials="<?= escapeHtml(initials($owner)) ?>" data-photo="<?= escapeHtml($photo ?? '') ?>" data-ts="<?= (int) $timestamp ?>"><?php if ($document): ?><img class="hero-bg" src="<?= escapeHtml($document) ?>" alt="Meeting photo for <?= escapeHtml($owner) ?>" loading="lazy" onerror="this.style.display='none'"><?php endif; ?><div class="hero-grad"></div><div class="hero-meta"><?= avatarHtml($owner, $photo, 'big') ?><div><h2><?= escapeHtml($owner) ?></h2><p class="hero-sub"><?php /* hero-time hidden per request: if ($time): ?><span class="hero-time"><?= escapeHtml($time) ?></span><?php endif; if ($time && $ago): ?> · <?php endif; */ ?><?php if ($ago): ?><span class="hero-ago"><?= escapeHtml($ago) ?></span><?php endif; ?></p></div></div></article><?php endforeach; ?>
