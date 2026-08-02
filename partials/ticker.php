<?php /** Scrolling motivational ticker. Expects $phrases. */ ?>
<div class="ticker" aria-hidden="true"><div class="ticker-track"><?php for ($repeat = 0; $repeat < 2; $repeat++): foreach ($phrases as $phrase): ?><span class="ticker-dot">&#9670;</span><span class="ticker-phrase"><?= escapeHtml($phrase) ?></span><?php endforeach; endfor; ?></div></div>
