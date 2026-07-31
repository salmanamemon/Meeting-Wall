<?php /** Scrolling motivational ticker. Expects $phrases. */ ?>
<div class="ticker" aria-hidden="true"><div class="ticker-track"><?php for ($r = 0; $r < 2; $r++): foreach ($phrases as $p): ?><span class="ticker-dot">&#9670;</span><span class="ticker-phrase"><?= h($p) ?></span><?php endforeach; endfor; ?></div></div>
