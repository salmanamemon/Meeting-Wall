// v1 leaderboard dashboard: live clock, four independent leaderboard carousels, a goal broker carousel
// with the 2/2 celebration, and the same 30s self-recovering poll as the main wall. No hero swiper.

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

// Each leaderboard card carousels through its pages of 3 independently.
function rotateLeaderboards() {
  (window.meetingWallLeaderboardTimers || []).forEach(clearInterval);
  window.meetingWallLeaderboardTimers = [];
  document.querySelectorAll('.lb-list[data-rotate]').forEach((leaderboardList) => {
    const pages = [...leaderboardList.querySelectorAll('.lb-page')];
    if (pages.length < 2) return;
    let currentPage = 0;
    const timer = setInterval(() => {
      pages[currentPage].classList.remove('active');
      currentPage = (currentPage + 1) % pages.length;
      pages[currentPage].classList.add('active');
    }, 6000);
    window.meetingWallLeaderboardTimers.push(timer);
  });
}

// Clear any running celebration (also stops confetti particles already in flight).
function stopCelebration() {
  clearInterval(window.meetingWallConfettiTimer);
  clearTimeout(window.meetingWallCelebrationTimer);
  if (window.confetti && window.confetti.reset) window.confetti.reset();
  const badge = document.getElementById('just-now');
  if (badge) badge.hidden = true;
}

function playCelebration() {
  stopCelebration();
  const badge = document.getElementById('just-now');
  if (badge) badge.hidden = false;
  if (window.confetti) {
    const colors = ['#ff6a00', '#ff2e9a', '#f5b544', '#fff7ed'];
    const endTime = Date.now() + 4000;
    window.meetingWallConfettiTimer = setInterval(() => {
      if (Date.now() > endTime) { clearInterval(window.meetingWallConfettiTimer); return; }
      window.confetti({particleCount: 3, angle: 60, spread: 55, startVelocity: 50, origin: {x: 0, y: 0.9}, colors});
      window.confetti({particleCount: 3, angle: 120, spread: 55, startVelocity: 50, origin: {x: 1, y: 0.9}, colors});
    }, 300);
  }
  window.meetingWallCelebrationTimer = setTimeout(() => { if (badge) badge.hidden = true; }, 4500);
}

// Celebrate a broker at their goal (2/2), once per person per day.
function maybeCelebrate(ownerName, done, goal) {
  if (!ownerName || done < goal) return;
  const today = new Date().toISOString().slice(0, 10);
  const celebratedKey = 'mw-celebrated-' + today + '-' + ownerName;
  if (localStorage.getItem(celebratedKey)) return;
  localStorage.setItem(celebratedKey, '1');
  playCelebration();
}

// The goal component carousels through today's brokers: photo + name + their broker-meet progress.
function initGoalCarousel() {
  clearInterval(window.meetingWallGoalTimer);
  const goalElement = document.querySelector('.goal-v1');
  if (!goalElement) return;
  let people = [];
  try { people = JSON.parse(document.getElementById('goal-people').textContent); } catch (error) {}
  if (!people.length) return;
  const goal = parseInt(goalElement.dataset.goal, 10) || 2;
  const showPerson = (personIndex) => {
    stopCelebration(); // clear any celebration from the previous person before switching
    const person = people[personIndex];
    const percent = Math.round(person.done / goal * 100);
    const avatarCell = document.getElementById('goal-avatar');
    avatarCell.textContent = person.initials || '';
    if (person.photo) {
      const photoImage = document.createElement('img');
      photoImage.src = person.photo;
      photoImage.alt = '';
      photoImage.onerror = () => photoImage.remove();
      avatarCell.appendChild(photoImage);
    }
    document.getElementById('goal-name').textContent = person.name;
    document.getElementById('goal-fill').style.width = percent + '%';
    document.getElementById('goal-done').textContent = person.done;
    document.getElementById('goal-percent').textContent = percent + '%';
    maybeCelebrate(person.name, person.done, goal);
  };
  // Resume where the previous render left off so the 30s poll updates counts in place instead of
  // snapping the card back to the first broker.
  let currentIndex = (window.meetingWallGoalIndex || 0) % people.length;
  window.meetingWallGoalIndex = currentIndex;
  showPerson(currentIndex);
  if (people.length < 2) return;
  window.meetingWallGoalTimer = setInterval(() => {
    currentIndex = (currentIndex + 1) % people.length;
    window.meetingWallGoalIndex = currentIndex;
    showPerson(currentIndex);
  }, 5000);
}

function initWall() {
  initClock();
  bindFullscreen();
  rotateLeaderboards();
  initGoalCarousel();
}

initWall();

// Live updates: poll every 30s. Runs forever (self-recovers). URL is relative so it hits whichever
// page served the wall (index.php / index_v1.php) — rename-proof.
setInterval(async () => {
  try {
    const { signature, version, html } = await (await fetch('?fragment=1', {cache: 'no-store'})).json();
    // A deploy bumped version.txt: full reload to pick up new CSS/JS/PHP, not just the #wall HTML.
    if (version && version !== document.body.dataset.version) { location.reload(); return; }
    if (!signature || signature === document.body.dataset.signature) return;
    document.body.dataset.signature = signature;
    stopCelebration();
    document.getElementById('wall').innerHTML = html;
    initWall();
  } catch (error) {} // network blip: ignore, retry next tick
}, 30000);
