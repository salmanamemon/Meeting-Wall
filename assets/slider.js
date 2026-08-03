function initClock() {
  clearInterval(window.meetingWallClockTimer);
  const timeElement = document.getElementById('clock');
  const dateElement = document.getElementById('clock-date');
  if (!timeElement) return;
  const updateClock = () => {
    const now = new Date();
    timeElement.textContent = now.toLocaleTimeString('en-US', {hour: 'numeric', minute: '2-digit', hour12: true});
    if (dateElement) dateElement.textContent = now.toLocaleDateString('en-US', {weekday: 'long', month: 'long', day: 'numeric'}).toUpperCase();
  };
  updateClock();
  window.meetingWallClockTimer = setInterval(updateClock, 1000);
}

function bindFullscreen() {
  const fullscreenButton = document.getElementById('fs');
  if (fullscreenButton) fullscreenButton.onclick = () =>
    document.fullscreenElement ? document.exitFullscreen() : document.documentElement.requestFullscreen();
}

// Pinned 6th leaderboard row: always mirrors the user on the active swiper slide (with their weekly rank + count).
function updateActiveLeaderRow() {
  const swiper = window.meetingWallSwiper;
  const activeRow = document.getElementById('lb-active');
  if (!swiper || !activeRow) return;
  const activeSlide = swiper.slides[swiper.activeIndex]; // loop duplicates carry the same data
  const ownerName = activeSlide && activeSlide.dataset.owner;
  if (!ownerName) { activeRow.hidden = true; return; }
  let rankByOwner = {};
  try { rankByOwner = JSON.parse(document.getElementById('lb-data').textContent); } catch (error) {}
  const ownerRank = rankByOwner[ownerName];
  activeRow.querySelector('.rank').textContent = ownerRank ? ownerRank.rank : '';
  const avatarCell = activeRow.querySelector('.avatar');
  avatarCell.textContent = activeSlide.dataset.initials || '';
  const photoUrl = activeSlide.dataset.photo;
  if (photoUrl) {
    const photoImage = document.createElement('img');
    photoImage.src = photoUrl;
    photoImage.alt = '';
    photoImage.onerror = () => photoImage.remove();
    avatarCell.appendChild(photoImage);
  }
  activeRow.querySelector('.lb-name').textContent = ownerName;
  activeRow.querySelector('.lb-count').textContent = ownerRank ? ownerRank.count : 0;
  activeRow.hidden = false;
}

// Per-user daily goal: the bar follows the active slide, showing that user's meetings today (capped at the goal).
function updateGoalForSlide() {
  const swiper = window.meetingWallSwiper;
  const goalElement = document.querySelector('.goal');
  if (!swiper || !goalElement) return;
  const activeSlide = swiper.slides[swiper.activeIndex];
  const ownerName = activeSlide && activeSlide.dataset.owner;
  if (!ownerName) return;
  const goal = parseInt(goalElement.dataset.goal, 10) || 2;
  let countByOwner = {};
  try { countByOwner = JSON.parse(document.getElementById('goal-data').textContent); } catch (error) {}
  const done = Math.min(countByOwner[ownerName] || 0, goal);
  const percent = Math.round(done / goal * 100);
  document.getElementById('goal-fill').style.width = percent + '%';
  document.getElementById('goal-done').textContent = done;
  document.getElementById('goal-percent').textContent = percent + '%';
  maybeCelebrate(ownerName, done, goal);
}

// When the active slide's user has hit their goal (2/2), play the celebration once per user per day.
function maybeCelebrate(ownerName, done, goal) {
  if (!ownerName || done < goal) return;
  const today = new Date().toISOString().slice(0, 10);
  const celebratedKey = 'mw-celebrated-' + today + '-' + ownerName;
  if (localStorage.getItem(celebratedKey)) return; // already celebrated this user today
  localStorage.setItem(celebratedKey, '1');
  playCelebration();
}

// Stop any running celebration immediately (called on every slide change so it never bleeds to the next person).
function stopCelebration() {
  clearInterval(window.meetingWallConfettiTimer);
  clearTimeout(window.meetingWallCelebrationTimer);
  if (window.confetti && window.confetti.reset) window.confetti.reset(); // clear particles already in flight
  const justNowBadge = document.getElementById('just-now');
  if (justNowBadge) justNowBadge.hidden = true;
}

// Show the badge and rain themed confetti for ~4s (or until the slide changes), then hide the badge.
function playCelebration() {
  stopCelebration(); // never stack celebrations
  const justNowBadge = document.getElementById('just-now');
  if (justNowBadge) justNowBadge.hidden = false;
  if (window.confetti) {
    const colors = ['#ff6a00', '#ff2e9a', '#f5b544', '#fff7ed'];
    const endTime = Date.now() + 4000;
    // Light bursts every 300ms (not every frame) so the confetti stays sparse.
    window.meetingWallConfettiTimer = setInterval(() => {
      if (Date.now() > endTime) { clearInterval(window.meetingWallConfettiTimer); return; }
      window.confetti({particleCount: 3, angle: 60, spread: 55, startVelocity: 50, origin: {x: 0, y: 0.9}, colors});
      window.confetti({particleCount: 3, angle: 120, spread: 55, startVelocity: 50, origin: {x: 1, y: 0.9}, colors});
    }, 300);
  }
  window.meetingWallCelebrationTimer = setTimeout(() => { if (justNowBadge) justNowBadge.hidden = true; }, 4500);
}

// Cross-fade the leaderboard through its pages of 3 (present only when there are more than 3 users).
function rotateLeaderboard() {
  clearInterval(window.meetingWallLeaderboardTimer);
  const leaderboardList = document.querySelector('.lb-list[data-rotate]');
  if (!leaderboardList) return;
  const pages = [...leaderboardList.querySelectorAll('.lb-page')];
  if (pages.length < 2) return;
  let currentPage = 0;
  window.meetingWallLeaderboardTimer = setInterval(() => {
    pages[currentPage].classList.remove('active');
    currentPage = (currentPage + 1) % pages.length;
    pages[currentPage].classList.add('active');
  }, 6000);
}

// One meeting card per view; touch-swipeable, auto-advances. Destroys the prior instance so poll swaps don't leak.
function initSwiper() {
  if (window.meetingWallSwiper) { window.meetingWallSwiper.destroy(true, true); window.meetingWallSwiper = null; }
  const heroElement = document.querySelector('.hero-slider');
  if (!heroElement || !window.Swiper) return;
  const slideCount = heroElement.querySelectorAll('.swiper-slide').length;
  window.meetingWallSwiper = new Swiper(heroElement, {
    loop: slideCount > 1,
    grabCursor: true,
    autoplay: slideCount > 1 ? {delay: 6000, disableOnInteraction: false, pauseOnMouseEnter: true} : false,
    pagination: {el: heroElement.querySelector('.swiper-pagination'), clickable: true, dynamicBullets: true, dynamicMainBullets: 1},
  });
  window.meetingWallSwiper.on('slideChange', () => { stopCelebration(); updateActiveLeaderRow(); updateGoalForSlide(); });
  updateActiveLeaderRow();
  updateGoalForSlide();
}

// Keep the "X AGO" labels live without re-rendering (so idle periods don't freeze the times).
function agoLabel(unixSeconds) {
  const seconds = Math.max(0, Math.floor(Date.now() / 1000) - unixSeconds);
  if (seconds < 60) return 'JUST NOW';
  if (seconds < 3600) return Math.floor(seconds / 60) + ' MIN AGO';
  if (seconds < 86400) return Math.floor(seconds / 3600) + ' HR AGO';
  return Math.floor(seconds / 86400) + ' DAY AGO';
}
function refreshAgos() {
  clearInterval(window.meetingWallAgoTimer);
  const update = () => document.querySelectorAll('.story').forEach((slide) => {
    const unixSeconds = parseInt(slide.dataset.ts, 10);
    const agoElement = slide.querySelector('.hero-ago');
    if (unixSeconds && agoElement) agoElement.textContent = agoLabel(unixSeconds);
  });
  update();
  window.meetingWallAgoTimer = setInterval(update, 30000);
}

function initWall() {
  initClock();
  bindFullscreen();
  initSwiper();
  rotateLeaderboard();
  refreshAgos();
}

initWall();

// Live updates: poll every 30s; when the data changes, swap the whole dashboard in place — no reload.
// Runs forever (signature is always set, even on API error) so the wall self-recovers 24/7.
// ponytail: re-render everything instead of diffing — same visible result, far less code. Server caches, so this is cheap.
setInterval(async () => {
  try {
    const { signature, html } = await (await fetch('index.php?fragment=1', {cache: 'no-store'})).json();
    if (!signature || signature === document.body.dataset.signature) return;
    const previousSlide = window.meetingWallSwiper ? window.meetingWallSwiper.realIndex : 0;
    document.body.dataset.signature = signature;
    document.getElementById('wall').innerHTML = html;
    initWall();
    // Keep the viewer on the same slide across a refresh instead of jumping back to the first.
    if (window.meetingWallSwiper && previousSlide > 0) window.meetingWallSwiper.slideToLoop(previousSlide, 0);
  } catch (error) {} // network blip: ignore, retry next tick
}, 30000);
