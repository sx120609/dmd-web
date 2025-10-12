<?php
require __DIR__ . '/bootstrap.php';

$method = $_SERVER['REQUEST_METHOD'];

function normalize_datetime(?string $value): ?string
{
    if ($value === null) {
        return null;
    }
    $trimmed = trim($value);
    if ($trimmed === '') {
        return null;
    }
    $timestamp = strtotime($trimmed);
    if ($timestamp === false) {
        return null;
    }
    return date('Y-m-d H:i:s', $timestamp);
}

function format_session_row(array $row): array
{
    $row['id'] = (int) $row['id'];
    $row['course_id'] = (int) $row['course_id'];
    return $row;
}

if ($method === 'GET') {
    $user = require_login($mysqli);

    if (isset($_GET['id'])) {
        $sessionId = (int) $_GET['id'];
        if ($sessionId <= 0) {
            error_response('直播课ID无效');
        }

        if ($user['role'] === 'admin') {
            $stmt = $mysqli->prepare('SELECT ls.id, ls.course_id, c.title AS course_title, ls.title, ls.description, ls.stream_url, ls.starts_at, ls.ends_at, ls.created_at FROM live_sessions ls INNER JOIN courses c ON c.id = ls.course_id WHERE ls.id = ? LIMIT 1');
            if (!$stmt) {
                error_response('无法获取直播课');
            }
            $stmt->bind_param('i', $sessionId);
        } else {
            $stmt = $mysqli->prepare('SELECT ls.id, ls.course_id, c.title AS course_title, ls.title, ls.description, ls.stream_url, ls.starts_at, ls.ends_at, ls.created_at FROM live_sessions ls INNER JOIN courses c ON c.id = ls.course_id INNER JOIN user_courses uc ON uc.course_id = ls.course_id WHERE ls.id = ? AND uc.user_id = ? LIMIT 1');
            if (!$stmt) {
                error_response('无法获取直播课');
            }
            $stmt->bind_param('ii', $sessionId, $user['id']);
        }
        $stmt->execute();
        $result = $stmt->get_result();
        $session = $result->fetch_assoc();
        $stmt->close();

        if (!$session) {
            error_response('直播课不存在或无访问权限', 404);
        }

        json_response(['session' => format_session_row($session)]);
    }

    if ($user['role'] === 'admin') {
        $sql = 'SELECT ls.id, ls.course_id, c.title AS course_title, ls.title, ls.description, ls.stream_url, ls.starts_at, ls.ends_at, ls.created_at FROM live_sessions ls INNER JOIN courses c ON c.id = ls.course_id ORDER BY COALESCE(ls.starts_at, ls.created_at) ASC, ls.id ASC';
        $result = $mysqli->query($sql);
        if (!$result) {
            error_response('无法获取直播课列表');
        }
    } else {
        $stmt = $mysqli->prepare('SELECT DISTINCT ls.id, ls.course_id, c.title AS course_title, ls.title, ls.description, ls.stream_url, ls.starts_at, ls.ends_at, ls.created_at FROM live_sessions ls INNER JOIN courses c ON c.id = ls.course_id INNER JOIN user_courses uc ON uc.course_id = ls.course_id WHERE uc.user_id = ? ORDER BY COALESCE(ls.starts_at, ls.created_at) ASC, ls.id ASC');
        if (!$stmt) {
            error_response('无法获取直播课列表');
        }
        $stmt->bind_param('i', $user['id']);
        $stmt->execute();
        $result = $stmt->get_result();
    }

    $sessions = [];
    while ($row = $result->fetch_assoc()) {
        $sessions[] = format_session_row($row);
    }
    if (isset($stmt) && $stmt instanceof mysqli_stmt) {
        $stmt->close();
    }
    json_response(['sessions' => $sessions]);
} elseif ($method === 'POST') {
    require_admin($mysqli);
    $input = get_json_input();
    if (!$input) {
        $input = $_POST;
    }

    $courseId = (int) ($input['course_id'] ?? 0);
    $title = trim($input['title'] ?? '');
    $description = trim($input['description'] ?? '');
    $streamUrl = trim($input['stream_url'] ?? '');
    $startsAt = normalize_datetime($input['starts_at'] ?? null);
    $endsAt = normalize_datetime($input['ends_at'] ?? null);

    if ($courseId <= 0) {
        error_response('请选择关联课程');
    }
    if ($title === '' || $streamUrl === '') {
        error_response('直播课标题和地址不能为空');
    }

    $stmt = $mysqli->prepare('SELECT id, title FROM courses WHERE id = ? LIMIT 1');
    if (!$stmt) {
        error_response('无法验证课程');
    }
    $stmt->bind_param('i', $courseId);
    $stmt->execute();
    $course = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    if (!$course) {
        error_response('课程不存在', 404);
    }

    $stmt = $mysqli->prepare('INSERT INTO live_sessions (course_id, title, description, stream_url, starts_at, ends_at) VALUES (?, ?, ?, ?, ?, ?)');
    if (!$stmt) {
        error_response('无法创建直播课');
    }
    $stmt->bind_param('isssss', $courseId, $title, $description, $streamUrl, $startsAt, $endsAt);
    if (!$stmt->execute()) {
        $stmt->close();
        error_response('创建直播课失败');
    }
    $sessionId = $stmt->insert_id;
    $stmt->close();

    json_response([
        'session' => [
            'id' => (int) $sessionId,
            'course_id' => $courseId,
            'course_title' => $course['title'],
            'title' => $title,
            'description' => $description,
            'stream_url' => $streamUrl,
            'starts_at' => $startsAt,
            'ends_at' => $endsAt,
        ],
    ]);
} elseif ($method === 'PATCH' || $method === 'PUT') {
    require_admin($mysqli);
    $input = get_json_input();
    if (!$input) {
        $raw = file_get_contents('php://input');
        if ($raw) {
            parse_str($raw, $input);
        }
    }
    if (!$input) {
        $input = $_POST;
    }

    $sessionId = (int) ($input['id'] ?? $input['session_id'] ?? 0);
    if ($sessionId <= 0) {
        error_response('直播课ID无效');
    }

    $stmt = $mysqli->prepare('SELECT ls.id, ls.course_id, ls.title, ls.description, ls.stream_url, ls.starts_at, ls.ends_at, c.title AS course_title FROM live_sessions ls INNER JOIN courses c ON c.id = ls.course_id WHERE ls.id = ? LIMIT 1');
    if (!$stmt) {
        error_response('无法获取直播课信息');
    }
    $stmt->bind_param('i', $sessionId);
    $stmt->execute();
    $result = $stmt->get_result();
    $current = $result->fetch_assoc();
    $stmt->close();
    if (!$current) {
        error_response('直播课不存在', 404);
    }

    $courseId = array_key_exists('course_id', $input) ? (int) $input['course_id'] : (int) $current['course_id'];
    $title = array_key_exists('title', $input) ? trim((string) $input['title']) : ($current['title'] ?? '');
    $description = array_key_exists('description', $input) ? trim((string) $input['description']) : ($current['description'] ?? '');
    $streamUrl = array_key_exists('stream_url', $input) ? trim((string) $input['stream_url']) : ($current['stream_url'] ?? '');
    $startsAt = array_key_exists('starts_at', $input) ? normalize_datetime($input['starts_at']) : ($current['starts_at'] ?? null);
    if (array_key_exists('starts_at', $input) && $input['starts_at'] !== '' && $startsAt === null) {
        error_response('开始时间格式无效');
    }
    $endsAt = array_key_exists('ends_at', $input) ? normalize_datetime($input['ends_at']) : ($current['ends_at'] ?? null);
    if (array_key_exists('ends_at', $input) && $input['ends_at'] !== '' && $endsAt === null) {
        error_response('结束时间格式无效');
    }

    if ($courseId <= 0) {
        error_response('请选择关联课程');
    }
    if ($title === '' || $streamUrl === '') {
        error_response('直播课标题和地址不能为空');
    }

    $courseTitle = $current['course_title'] ?? '';

    if ($courseId !== (int) $current['course_id']) {
        $stmt = $mysqli->prepare('SELECT id, title FROM courses WHERE id = ? LIMIT 1');
        if (!$stmt) {
            error_response('无法验证课程');
        }
        $stmt->bind_param('i', $courseId);
        $stmt->execute();
        $course = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        if (!$course) {
            error_response('课程不存在', 404);
        }
        $courseTitle = $course['title'];
    }

    $stmt = $mysqli->prepare('UPDATE live_sessions SET course_id = ?, title = ?, description = ?, stream_url = ?, starts_at = ?, ends_at = ? WHERE id = ? LIMIT 1');
    if (!$stmt) {
        error_response('无法更新直播课');
    }
    $stmt->bind_param('isssssi', $courseId, $title, $description, $streamUrl, $startsAt, $endsAt, $sessionId);
    if (!$stmt->execute()) {
        $stmt->close();
        error_response('更新直播课失败');
    }
    $stmt->close();

    json_response([
        'session' => [
            'id' => $sessionId,
            'course_id' => $courseId,
            'course_title' => $courseTitle,
            'title' => $title,
            'description' => $description,
            'stream_url' => $streamUrl,
            'starts_at' => $startsAt,
            'ends_at' => $endsAt,
        ],
    ]);
} elseif ($method === 'DELETE') {
    require_admin($mysqli);
    $input = get_json_input();
    if (!$input) {
        $raw = file_get_contents('php://input');
        if ($raw) {
            parse_str($raw, $input);
        }
    }
    if (!$input) {
        $input = $_GET;
    }

    $sessionId = (int) ($input['session_id'] ?? 0);
    if ($sessionId <= 0) {
        error_response('直播课ID无效');
    }

    $stmt = $mysqli->prepare('DELETE FROM live_sessions WHERE id = ? LIMIT 1');
    if (!$stmt) {
        error_response('无法删除直播课');
    }
    $stmt->bind_param('i', $sessionId);
    if (!$stmt->execute()) {
        $stmt->close();
        error_response('删除直播课失败');
    }
    if ($stmt->affected_rows <= 0) {
        $stmt->close();
        error_response('直播课不存在或已被删除', 404);
    }
    $stmt->close();

    json_response(['success' => true]);
} else {
    error_response('不支持的请求方法', 405);
}
