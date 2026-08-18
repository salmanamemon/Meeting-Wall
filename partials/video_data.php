<?php
/** Video-upload leaderboard data (no output). Sets $videoToday and $videoWeek so wall_v1.php can render
    them with the shared leaderboard_card.php, exactly like the meeting cards. Hits the SAME dar_leaderboard
    endpoint as leaderboard() but with leader_board_type=video; does NOT touch functions.php.
    Zero-count entries are dropped. Include this once near the top of wall_v1.php. */

global $config;

// meeting_type for the video board. One line to change if the video endpoint expects a different value.
$videoMeetingType = 'agency';

// Same shape/parse as leaderboard(), plus leader_board_type=video. Video count comes back as expr0.
$fetchVideoLeaders = function (string $period) use ($config, $videoMeetingType): array {
    $cacheKey = 'video-' . $period . '-' . $videoMeetingType;
    $cacheFile = sys_get_temp_dir() . '/darstories_leaderboard_' . preg_replace('/[^A-Za-z0-9_-]/', '', $cacheKey) . '.json';
    if (is_file($cacheFile) && time() - filemtime($cacheFile) < 60) {
        $cached = json_decode((string) file_get_contents($cacheFile), true);
        if (is_array($cached)) return $cached;
    }
    $base = $config['leaderboard']['url'] ?? '';
    if (!$base) return [];
    $url = $base . '?meeting_date=' . urlencode($period) . '&meeting_type=' . urlencode($videoMeetingType) . '&leader_board_type=video';
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
        $count = (int) ($row['expr0'] ?? $row['cnt'] ?? 0); // video endpoint returns the count as expr0
        if ($count <= 0) continue; // don't show zero-count people
        $hasPhoto = !empty($row['IsProfilePhotoActive']) && !empty($row['FullPhotoUrl']);
        $leaders[] = [
            'name' => $row['Name'] ?? 'Unassigned',
            'count' => $count,
            'ownerId' => $row['OwnerId'] ?? '',
            'photo' => $hasPhoto ? $row['FullPhotoUrl'] : null,
        ];
    }
    usort($leaders, fn(array $first, array $second) => $second['count'] <=> $first['count']);
    file_put_contents($cacheFile, json_encode($leaders), LOCK_EX);
    return $leaders;
};

$videoToday = $fetchVideoLeaders('TODAY');
$videoWeek = $fetchVideoLeaders('THIS_WEEK');
$hasVideo = (bool) ($videoToday || $videoWeek);
