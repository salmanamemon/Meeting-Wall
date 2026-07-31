<?php /** Renders the hero meeting cards. Expects $items in scope. Shared by wall.php and the ?fragment=1 endpoint. */ ?>
<?php foreach ($items as $item):
  $owner = $item['OwnerName'] ?? 'Unassigned';
  $document = $item['checkOutDocumentLink'] ?? '';
  $ts = meetingTs($item);
  $time = $ts ? date('g:i A', $ts) : '';
  $ago = $ts ? agoText($ts) : '';
?><article class="story swiper-slide" data-owner="<?= h($owner) ?>" data-initials="<?= h(initials($owner)) ?>"><?php if ($document): ?><img class="hero-bg" src="<?= h($document) ?>" alt="Meeting photo for <?= h($owner) ?>" loading="lazy" onerror="this.style.display='none'"><?php endif; ?><div class="hero-grad"></div><div class="hero-meta"><span class="avatar big"><?= h(initials($owner)) ?></span><div><h2><?= h($owner) ?></h2><p class="hero-sub"><?php if ($time): ?><span class="hero-time"><?= h($time) ?></span><?php endif; ?><?php if ($time && $ago): ?> · <?php endif; ?><?php if ($ago): ?><span class="hero-ago"><?= h($ago) ?></span><?php endif; ?></p></div></div></article><?php endforeach; ?>
