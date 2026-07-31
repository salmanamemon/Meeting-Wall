function initClock() {
  clearInterval(window._clock);
  const t = document.getElementById('clock'), d = document.getElementById('clock-date');
  if (!t) return;
  const upd = () => {
    const n = new Date();
    t.textContent = n.toLocaleTimeString('en-GB', {hour: '2-digit', minute: '2-digit'});
    if (d) d.textContent = n.toLocaleDateString('en-US', {weekday: 'long', month: 'long', day: 'numeric'}).toUpperCase();
  };
  upd();
  window._clock = setInterval(upd, 1000);
}

function bindFullscreen() {
  const b = document.getElementById('fs');
  if (b) b.onclick = () => document.fullscreenElement ? document.exitFullscreen() : document.documentElement.requestFullscreen();
}

// Reflect the active slide's user as a 6th leaderboard row — only when they're outside the weekly top 5.
function updateActiveLeaderRow() {
  const sw = window._swiper, row = document.getElementById('lb-active');
  if (!sw || !row) return;
  const slide = sw.slides[sw.activeIndex]; // duplicates carry the same data, so this is fine in loop mode
  const owner = slide && slide.dataset.owner;
  if (!owner) { row.hidden = true; return; }
  let data = {};
  try { data = JSON.parse(document.getElementById('lb-data').textContent); } catch {}
  const info = data[owner];
  if (info && info.rank <= 5) { row.hidden = true; return; } // already in the top 5
  row.querySelector('.rank').textContent = info ? info.rank : '–';
  row.querySelector('.avatar').textContent = slide.dataset.initials || '';
  row.querySelector('.lb-name').textContent = owner;
  row.querySelector('.lb-count').textContent = info ? info.count : 0;
  row.hidden = false;
}

// One meeting card per view; touch-swipeable, auto-advances. Destroys the prior instance so poll swaps don't leak.
function initSwiper() {
  if (window._swiper) { window._swiper.destroy(true, true); window._swiper = null; }
  const el = document.querySelector('.hero-slider');
  if (!el || !window.Swiper) return;
  const count = el.querySelectorAll('.swiper-slide').length;
  window._swiper = new Swiper(el, {
    loop: count > 1,
    grabCursor: true,
    autoplay: count > 1 ? {delay: 6000, disableOnInteraction: false, pauseOnMouseEnter: true} : false,
    pagination: {el: el.querySelector('.swiper-pagination'), clickable: true},
  });
  window._swiper.on('slideChange', updateActiveLeaderRow);
  updateActiveLeaderRow();
}

function initWall() {
  initClock();
  bindFullscreen();
  initSwiper();
}

initWall();

// Live updates: poll for changes; when the data changes, swap the whole dashboard in place — no reload.
// ponytail: re-render everything instead of diffing — same visible result, far less code. Server caches, so this is cheap.
if (document.body.dataset.signature) setInterval(async () => {
  try {
    const { signature, html } = await (await fetch('index.php?fragment=1', {cache: 'no-store'})).json();
    if (!signature || signature === document.body.dataset.signature) return;
    document.body.dataset.signature = signature;
    document.getElementById('wall').innerHTML = html;
    initWall();
  } catch {} // network blip: ignore, retry next tick
}, 30000);
