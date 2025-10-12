<?php
require __DIR__ . '/bootstrap.php';

$method = $_SERVER['REQUEST_METHOD'];

function normalize_lesson_type($value): string
{
    $normalized = strtolower(trim((string) $value));
    return in_array($normalized, ['recorded', 'live'], true) ? $normalized : 'recorded';
}

function normalize_datetime_value($value): ?string
{
    if ($value === null) {
        return null;
    }
    $trimmed = trim((string) $value);
    if ($trimmed === '') {
        return null;
    }
    $timestamp = strtotime($trimmed);
    if ($timestamp === false) {
        return null;
    }
    return date('Y-m-d H:i:s', $timestamp);
}

if ($method === 'POST') {
    require_admin($mysqli);
    $input = get_json_input();
    if (empty($input)) {
        $input = $_POST;
    }
    $courseId = (int) ($input['course_id'] ?? 0);
    $title = trim($input['title'] ?? '');
    $videoUrl = trim($input['video_url'] ?? '');
    $type = normalize_lesson_type($input['type'] ?? 'recorded');
    $liveUrl = trim($input['live_url'] ?? '');
    $liveStartSource = $input['live_start_at'] ?? null;
    $liveEndSource = $input['live_end_at'] ?? null;
    $liveStartAt = normalize_datetime_value($liveStartSource);
    $liveEndAt = normalize_datetime_value($liveEndSource);

    if ($courseId <= 0 || $title === '') {
        error_response('课程和课节标题不能为空');
    }

    if ($type === 'live') {
        if ($liveUrl === '') {
            error_response('直播课需要提供直播地址');
        }
        if ($liveStartSource !== null && $liveStartSource !== '' && $liveStartAt === null) {
            error_response('直播开始时间格式无效');
        }
        if ($liveEndSource !== null && $liveEndSource !== '' && $liveEndAt === null) {
            error_response('直播结束时间格式无效');
        }
        $videoUrl = '';
    } else {
        $liveUrl = '';
        $liveStartAt = null;
        $liveEndAt = null;
    }

    $stmt = $mysqli->prepare('INSERT INTO lessons (course_id, title, video_url, type, live_url, live_start_at, live_end_at) VALUES (?, ?, ?, ?, ?, ?, ?)');
    if (!$stmt) {
        error_response('无法创建课节');
    }
    $stmt->bind_param('issssss', $courseId, $title, $videoUrl, $type, $liveUrl, $liveStartAt, $liveEndAt);
    if (!$stmt->execute()) {
        $stmt->close();
        error_response('创建课节失败');
    }
    $lessonId = $stmt->insert_id;
    $stmt->close();

    json_response([
        'lesson' => [
            'id' => (int) $lessonId,
            'course_id' => $courseId,
            'title' => $title,
            'video_url' => $videoUrl,
            'type' => $type,
            'live_url' => $liveUrl,
            'live_start_at' => $liveStartAt,
            'live_end_at' => $liveEndAt,
        ],
    ]);
} elseif ($method === 'PATCH' || $method === 'PUT') {
    require_admin($mysqli);
    $input = get_json_input();
    if (empty($input)) {
        $raw = file_get_contents('php://input');
        if ($raw) {
            parse_str($raw, $input);
        }
    }
    if (empty($input)) {
        $input = $_POST;
    }

    $lessonId = (int) ($input['lesson_id'] ?? $input['id'] ?? 0);
    if ($lessonId <= 0) {
        error_response('课节ID无效');
    }

    $stmt = $mysqli->prepare('SELECT id, course_id, title, video_url, type, live_url, live_start_at, live_end_at FROM lessons WHERE id = ? LIMIT 1');
    if (!$stmt) {
        error_response('无法获取课节信息');
    }
    $stmt->bind_param('i', $lessonId);
    $stmt->execute();
    $result = $stmt->get_result();
    $current = $result->fetch_assoc();
    $stmt->close();

    if (!$current) {
        error_response('课节不存在', 404);
    }

    $courseId = array_key_exists('course_id', $input) ? (int) $input['course_id'] : (int) $current['course_id'];
    $title = array_key_exists('title', $input) ? trim((string) $input['title']) : ($current['title'] ?? '');
    $videoUrl = array_key_exists('video_url', $input) ? trim((string) $input['video_url']) : ($current['video_url'] ?? '');
    $type = array_key_exists('type', $input) ? normalize_lesson_type($input['type']) : ($current['type'] ?? 'recorded');

    $liveUrl = array_key_exists('live_url', $input)
        ? trim((string) $input['live_url'])
        : ($current['live_url'] ?? '');

    $liveStartAtSource = array_key_exists('live_start_at', $input) ? $input['live_start_at'] : null;
    if ($liveStartAtSource !== null) {
        $liveStartAt = normalize_datetime_value($liveStartAtSource);
        if ($liveStartAtSource !== '' && $liveStartAt === null) {
            error_response('直播开始时间格式无效');
        }
    } else {
        $liveStartAt = $current['live_start_at'] ?? null;
    }

    $liveEndAtSource = array_key_exists('live_end_at', $input) ? $input['live_end_at'] : null;
    if ($liveEndAtSource !== null) {
        $liveEndAt = normalize_datetime_value($liveEndAtSource);
        if ($liveEndAtSource !== '' && $liveEndAt === null) {
            error_response('直播结束时间格式无效');
        }
    } else {
        $liveEndAt = $current['live_end_at'] ?? null;
    }

    if ($courseId <= 0) {
        error_response('课程ID无效');
    }

    if ($title === '') {
        error_response('课节标题不能为空');
    }

    if ($courseId !== (int) $current['course_id']) {
        $courseStmt = $mysqli->prepare('SELECT id FROM courses WHERE id = ? LIMIT 1');
        if (!$courseStmt) {
            error_response('无法验证课程信息');
        }
        $courseStmt->bind_param('i', $courseId);
        $courseStmt->execute();
        $courseResult = $courseStmt->get_result();
        $targetCourse = $courseResult->fetch_assoc();
        $courseStmt->close();
        if (!$targetCourse) {
            error_response('目标课程不存在', 404);
        }
    }

    if ($type === 'live') {
        if ($liveUrl === '') {
            error_response('直播课需要提供直播地址');
        }
        if (!array_key_exists('video_url', $input)) {
            $videoUrl = '';
        }
    } else {
        $type = 'recorded';
        if (!array_key_exists('video_url', $input)) {
            $videoUrl = $current['video_url'] ?? '';
        }
        $liveUrl = '';
        $liveStartAt = null;
        $liveEndAt = null;
    }

    $stmt = $mysqli->prepare('UPDATE lessons SET course_id = ?, title = ?, video_url = ?, type = ?, live_url = ?, live_start_at = ?, live_end_at = ? WHERE id = ? LIMIT 1');
    if (!$stmt) {
        error_response('无法更新课节');
    }
    $stmt->bind_param('issssssi', $courseId, $title, $videoUrl, $type, $liveUrl, $liveStartAt, $liveEndAt, $lessonId);
    if (!$stmt->execute()) {
        $stmt->close();
        error_response('更新课节失败');
    }
    $stmt->close();

    json_response([
        'lesson' => [
            'id' => (int) $lessonId,
            'course_id' => (int) $courseId,
            'title' => $title,
            'video_url' => $videoUrl,
            'type' => $type,
            'live_url' => $liveUrl,
            'live_start_at' => $liveStartAt,
            'live_end_at' => $liveEndAt,
        ],
    ]);
} elseif ($method === 'DELETE') {
    require_admin($mysqli);
    $input = get_json_input();
    if (empty($input)) {
        $raw = file_get_contents('php://input');
        if ($raw) {
            parse_str($raw, $input);
        }
    }
    if (empty($input)) {
        $input = $_GET;
    }

    $lessonId = (int) ($input['lesson_id'] ?? 0);
    if ($lessonId <= 0) {
        error_response('课节ID无效');
    }

    $stmt = $mysqli->prepare('DELETE FROM lessons WHERE id = ? LIMIT 1');
    if (!$stmt) {
        error_response('无法删除课节');
    }
    $stmt->bind_param('i', $lessonId);
    if (!$stmt->execute()) {
        $stmt->close();
        error_response('删除课节失败');
    }
    $affected = $stmt->affected_rows;
    $stmt->close();

    if ($affected <= 0) {
        error_response('课节不存在或已被删除', 404);
    }

    json_response(['success' => true]);
} else {
    error_response('不支持的请求方法', 405);
}
