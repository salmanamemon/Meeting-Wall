# PLAN.md — Phase 2: `index_v1.php` leaderboard dashboard

A second dashboard variant. **`index.php` and everything it uses stay untouched** — this is
additive: new files + new (unused-by-v1... i.e. unused-by-index) functions only.

## Decisions (confirmed with user)
- 2×2 grid of 4 leaderboard cards + a side rail (streak + QR). Drop the old single leaderboard. No hero.
- The 4 cards: Brokers (agency) This Week / Today, Customers (customer) This Week / Today.
- Each card = top-3 distinct-score tiers (competition rank, crowns), carousel through pages of 3.
- `.goal` carousels through today's brokers: photo + name + their broker-meet progress (X/2). Keep the 2/2 confetti celebration (once per person/day).
- Top-bar totals unchanged (all-meeting week/today sums). Ticker reused.

## Chunks (each ~one commit)
1. **Data + orchestrator** — `renderWallV1()` in functions.php; `wall_v1.php` fetches the 4 leaderboards + totals + goal people.
2. **Reusable card** — `partials/leaderboard_card.php` (title, badge, tiers, carousel). Reuses `.card/.lb-list/.lb-page/.lb-row/.avatar` classes + `leaderboardTiers()`.
3. **Goal v1** — `partials/goal_v1.php` (photo + name + bar, carousel data as JSON).
4. **Shell** — `index_v1.php` (duplicate of index.php, no swiper; loads `wall_v1.css` + `slider_v1.js`; `?fragment=1` → `renderWallV1()`).
5. **Styles** — `css/wall_v1.css` (2×2 grid + rail, goal person, responsive). Reuse wall.css.
6. **JS** — `assets/slider_v1.js` (clock, fullscreen, rotate each `.lb-list` independently, goal broker carousel + celebration, 30s poll to `index_v1.php?fragment=1`).

## Reuse
`leaderboard($period,$type)`, `leaderboardTiers()`, `avatarHtml()`, `tickerPhrases()`, `asset()`,
partials `topbar.php` / `streak.php` / `qr.php` / `ticker.php`, and `css/wall.css` classes.
