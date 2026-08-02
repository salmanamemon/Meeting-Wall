<?php
declare(strict_types=1);

$configFile = __DIR__ . '/config.php';
if (!is_file($configFile)) {
    http_response_code(500);
    exit('Missing config.php. Copy config.example.php and configure it.');
}
$config = require $configFile;

// Evaluate all dates in the business timezone. Meetings arrive as UTC; without this the 9AM–9PM
// window and "today" are judged in the server's UTC, dropping meetings that are daytime in Dubai.
date_default_timezone_set($config['timezone'] ?? 'Asia/Dubai');

session_name('darstories_session');
session_set_cookie_params(['httponly' => true, 'secure' => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off', 'samesite' => 'Lax']);
session_start();

function db(): PDO
{
    global $config;
    static $pdo;
    return $pdo ??= new PDO(
        "mysql:host={$config['db']['host']};dbname={$config['db']['name']};charset=utf8mb4",
        $config['db']['user'], $config['db']['pass'],
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC]
    );
}

function csrf(): string
{
    return $_SESSION['csrf'] ??= bin2hex(random_bytes(32));
}

function verifyCsrf(): void
{
    if (!hash_equals($_SESSION['csrf'] ?? '', $_POST['csrf'] ?? '')) {
        http_response_code(403);
        exit('Invalid request.');
    }
}

function cookieOptions(int $expires): array
{
    return ['expires' => $expires, 'path' => '/', 'httponly' => true, 'secure' => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off', 'samesite' => 'Lax'];
}

function login(array $user): void
{
    global $config;
    session_regenerate_id(true);
    $_SESSION['user'] = ['id' => (int) $user['id'], 'name' => $user['name'], 'email' => $user['email']];
    $selector = bin2hex(random_bytes(12));
    $token = bin2hex(random_bytes(32));
    $expires = time() + ((int) $config['login_days'] * 86400);
    db()->prepare('INSERT INTO auth_tokens (user_id, selector, token_hash, expires_at) VALUES (?, ?, ?, FROM_UNIXTIME(?))')
        ->execute([$user['id'], $selector, hash('sha256', $token), $expires]);
    setcookie('darstories_login', $selector . ':' . $token, cookieOptions($expires));
}

function currentUser(): ?array
{
    if (isset($_SESSION['user'])) return $_SESSION['user'];
    [$selector, $token] = array_pad(explode(':', $_COOKIE['darstories_login'] ?? '', 2), 2, '');
    if (!preg_match('/^[a-f0-9]{24}$/', $selector) || !preg_match('/^[a-f0-9]{64}$/', $token)) return null;
    $stmt = db()->prepare('SELECT u.id, u.name, u.email, t.token_hash FROM auth_tokens t JOIN users u ON u.id = t.user_id WHERE t.selector = ? AND t.expires_at > NOW()');
    $stmt->execute([$selector]);
    $row = $stmt->fetch();
    if (!$row || !hash_equals($row['token_hash'], hash('sha256', $token))) return null;
    $_SESSION['user'] = ['id' => (int) $row['id'], 'name' => $row['name'], 'email' => $row['email']];
    return $_SESSION['user'];
}

function requireLogin(): array
{
    $user = currentUser();
    if (!$user) { header('Location: login.php'); exit; }
    return $user;
}

function logout(): void
{
    [$selector] = explode(':', $_COOKIE['darstories_login'] ?? ':', 2);
    if ($selector) db()->prepare('DELETE FROM auth_tokens WHERE selector = ?')->execute([$selector]);
    setcookie('darstories_login', '', cookieOptions(time() - 3600));
    $_SESSION = [];
    session_destroy();
}

function clientIp(): string { return substr($_SERVER['REMOTE_ADDR'] ?? 'unknown', 0, 45); }

function loginLockMinutes(): int
{
    $stmt = db()->prepare('SELECT locked_until FROM login_attempts WHERE ip_address = ?');
    $stmt->execute([clientIp()]);
    $until = $stmt->fetchColumn();
    if (!$until || strtotime($until) <= time()) {
        if ($until) db()->prepare('DELETE FROM login_attempts WHERE ip_address = ?')->execute([clientIp()]);
        return 0;
    }
    return (int) ceil((strtotime($until) - time()) / 60);
}

function recordFailedLogin(): int
{
    $ip = clientIp();
    $pdo = db();
    $stmt = $pdo->prepare('SELECT failures FROM login_attempts WHERE ip_address = ?');
    $stmt->execute([$ip]);
    $failures = (int) $stmt->fetchColumn() + 1;
    $lockedUntil = $failures >= 10 ? date('Y-m-d H:i:s', time() + 900) : null;
    $pdo->prepare('INSERT INTO login_attempts (ip_address, failures, locked_until) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE failures = VALUES(failures), locked_until = VALUES(locked_until)')
        ->execute([$ip, $failures, $lockedUntil]);
    return $lockedUntil ? 15 : 0;
}

function clearFailedLogins(): void
{
    db()->prepare('DELETE FROM login_attempts WHERE ip_address = ?')->execute([clientIp()]);
}

function stories(): array
{
    global $config;
    // ponytail: 20s file cache caps middleware calls to ~1 per 20s no matter how many tabs poll. Raise TTL to soften load further.
    $cache = sys_get_temp_dir() . '/darstories_stories.json';
    if (is_file($cache) && time() - filemtime($cache) < 20) {
        $cached = json_decode((string) file_get_contents($cache), true);
        if (is_array($cached)) return $cached;
    }
    $curl = curl_init($config['api']['url']);
    curl_setopt_array($curl, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 12,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => json_encode($config['api']['body'] ?? [], JSON_THROW_ON_ERROR),
        CURLOPT_HTTPHEADER => array_merge(['Accept: application/json'], $config['api']['headers'] ?? []),
    ]);
    $body = curl_exec($curl);
    $error = curl_error($curl);
    $status = (int) curl_getinfo($curl, CURLINFO_RESPONSE_CODE);
    curl_close($curl);
    if ($body === false || $status < 200 || $status >= 300) throw new RuntimeException($error ?: "The stories service returned HTTP $status.");
    $data = json_decode($body, true, 512, JSON_THROW_ON_ERROR);
    $rows = $data['data']['daliysaleList'] ?? $data['data']['dailySaleList'] ?? [];
    if (!is_array($rows)) throw new RuntimeException('The stories service returned an invalid list.');
    $items = array_values(array_filter($rows, 'is_array'));
    file_put_contents($cache, json_encode($items), LOCK_EX);
    return $items;
}

// Weekly leaderboard from the middleware (aggregated per user). Returns [['name' => ..., 'count' => int], ...]
// sorted by count desc. Returns [] on any failure so the caller can fall back. Cached 60s.
function leaderboard(): array
{
    global $config;
    $cacheFile = sys_get_temp_dir() . '/darstories_leaderboard.json';
    if (is_file($cacheFile) && time() - filemtime($cacheFile) < 60) {
        $cached = json_decode((string) file_get_contents($cacheFile), true);
        if (is_array($cached)) return $cached;
    }
    $url = $config['leaderboard']['url'] ?? '';
    if (!$url) return [];
    $curl = curl_init($url);
    curl_setopt_array($curl, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 12,
        CURLOPT_HTTPHEADER => array_merge(['Accept: application/json'], $config['leaderboard']['headers'] ?? []),
    ]);
    $body = curl_exec($curl);
    $status = (int) curl_getinfo($curl, CURLINFO_RESPONSE_CODE);
    curl_close($curl);
    if ($body === false || $status < 200 || $status >= 300) return [];
    $rows = json_decode($body, true)['data'] ?? [];
    if (!is_array($rows)) return [];
    $leaders = [];
    foreach ($rows as $row) {
        if (!is_array($row)) continue;
        $hasPhoto = !empty($row['IsProfilePhotoActive']) && !empty($row['FullPhotoUrl']);
        $leaders[] = [
            'name' => $row['Name'] ?? 'Unassigned',
            'count' => (int) ($row['cnt'] ?? 0),
            'ownerId' => $row['OwnerId'] ?? '',
            'photo' => $hasPhoto ? $row['FullPhotoUrl'] : null,
        ];
    }
    usort($leaders, fn(array $first, array $second) => $second['count'] <=> $first['count']);
    file_put_contents($cacheFile, json_encode($leaders), LOCK_EX);
    return $leaders;
}

// Renders the story cards to an HTML string, reusing the same partial the hero uses.
function renderSlides(array $items): string { ob_start(); include __DIR__ . '/partials/slides.php'; return ob_get_clean(); }

// Renders the whole data-driven dashboard (#wall contents). Shared by index.php and the ?fragment=1 poll.
function renderWall(array $items, string $error = ''): string { ob_start(); include __DIR__ . '/wall.php'; return ob_get_clean(); }

// Meeting timestamp from whichever date field is present.
function meetingTs(array $item): ?int
{
    $raw = $item['Check_Out_Date_Time__c'] ?? $item['Meeting_Date__c'] ?? $item['createdOn'] ?? '';
    $timestamp = $raw ? strtotime((string) $raw) : false;
    return $timestamp !== false ? $timestamp : null;
}

// Initials for the generated avatar, e.g. "Maya Ortiz" -> "MO".
function initials(string $name): string
{
    $parts = array_values(array_filter(preg_split('/\s+/', trim($name)) ?: []));
    $first = $parts[0][0] ?? '?';
    $last = count($parts) > 1 ? $parts[count($parts) - 1][0] : '';
    return strtoupper($first . $last);
}

// Avatar markup: profile photo when available, initials underneath as the fallback (the image removes
// itself on error, revealing the initials). $extraClass e.g. 'big' for the hero avatar.
function avatarHtml(string $name, ?string $photo = null, string $extraClass = ''): string
{
    $classAttribute = 'avatar' . ($extraClass ? ' ' . $extraClass : '');
    $imageTag = $photo ? '<img src="' . escapeHtml($photo) . '" alt="" loading="lazy" onerror="this.remove()">' : '';
    return '<span class="' . $classAttribute . '">' . escapeHtml(initials($name)) . $imageTag . '</span>';
}

// "57 MIN AGO" style relative label.
function agoText(int $timestamp): string
{
    $seconds = max(0, time() - $timestamp);
    if ($seconds < 60) return 'JUST NOW';
    if ($seconds < 3600) return intdiv($seconds, 60) . ' MIN AGO';
    if ($seconds < 86400) return intdiv($seconds, 3600) . ' HR AGO';
    return intdiv($seconds, 86400) . ' DAY AGO';
}

// A meeting "counts" only if it happened today between 9AM and 9PM. Previous days never carry into today.
function inTodayWindow(array $item): bool
{
    $timestamp = meetingTs($item);
    if ($timestamp === null || date('Y-m-d', $timestamp) !== date('Y-m-d')) return false;
    $hour = (int) date('G', $timestamp);
    return $hour >= 9 && $hour < 21; // 9AM–9PM
}

// Today's meetings (9AM–9PM only) — what the wall actually displays and scores.
function todayMeetings(array $items): array
{
    return array_values(array_filter($items, 'inTodayWindow'));
}

// Derived dashboard numbers. today = 9AM–9PM count (goal/stat); week = this-week count;
// leaders = this-week ranking (from the API's weekly data), full list — the wall slices the top 5.
function wallData(array $items): array
{
    $currentWeek = date('oW');
    $todayCount = $weekCount = 0;
    $countByOwner = [];
    foreach ($items as $meeting) {
        $timestamp = meetingTs($meeting);
        if ($timestamp === null) continue;
        if (inTodayWindow($meeting)) $todayCount++;
        if (date('oW', $timestamp) === $currentWeek) {
            $weekCount++;
            $ownerName = $meeting['OwnerName'] ?? 'Unassigned';
            $countByOwner[$ownerName] = ($countByOwner[$ownerName] ?? 0) + 1;
        }
    }
    arsort($countByOwner);
    $leaders = [];
    foreach ($countByOwner as $ownerName => $count) $leaders[] = ['name' => $ownerName, 'count' => $count];
    return ['today' => $todayCount, 'week' => $weekCount, 'leaders' => $leaders];
}

// Fingerprint of the current story set, so the client can detect changes and reload.
function storiesSignature(array $items): string { return md5(json_encode($items)); }

function escapeHtml(mixed $value): string { return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8'); }

// Local asset URL with a mtime cache-buster so edits are never served stale.
function asset(string $path): string { $fullPath = __DIR__ . '/' . $path; return escapeHtml($path . (is_file($fullPath) ? '?v=' . filemtime($fullPath) : '')); }

function appUrl(string $path = ''): string
{
    $scheme = !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' ? 'https' : 'http';
    $host = preg_replace('/[^a-zA-Z0-9.:-]/', '', $_SERVER['HTTP_HOST'] ?? 'localhost');
    $base = rtrim(dirname($_SERVER['SCRIPT_NAME'] ?? '/'), '/');
    return "$scheme://$host$base/" . ltrim($path, '/');
}
