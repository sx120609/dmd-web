<?php
require __DIR__ . '/bootstrap.php';

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    $user = require_login($mysqli);
    $courseId = isset($_GET['course_id']) ? (int) $_GET['course_id'] : 0;

    if ($courseId <= 0) {
        error_response('缺少课程ID');
    }

    if ($user['role'] !== 'admin') {
        $stmt = $mysqli->prepare('SELECT 1 FROM user_courses WHERE user_id = ? AND course_id = ? LIMIT 1');
        if (!$stmt) {
            error_response('无法验证课程权限');
        }
        $stmt->bind_param('ii', $user['id'], $courseId);
        $stmt->execute();
        $hasAccess = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        if (!$hasAccess) {
            error_response('无权访问该课程', 403);
        }
    }

    $stmt = $mysqli->prepare('SELECT id, course_id, title, video_url, created_at FROM lessons WHERE course_id = ? ORDER BY id ASC');
    if (!$stmt) {
        error_response('无法获取课节列表');
    }
    $stmt->bind_param('i', $courseId);
    $stmt->execute();
    $result = $stmt->get_result();
    $lessons = [];
    while ($row = $result->fetch_assoc()) {
        $row['id'] = (int) $row['id'];
        $row['course_id'] = (int) $row['course_id'];
        $lessons[] = $row;
    }
    $stmt->close();

    json_response(['lessons' => $lessons]);
} elseif ($method === 'POST') {
    require_admin($mysqli);
    $input = get_json_input();
    if (!$input) {
        $input = $_POST;
    }

    $courseId = (int) ($input['course_id'] ?? 0);
    $title = trim($input['title'] ?? '');
    $videoUrl = trim($input['video_url'] ?? '');

    if ($courseId <= 0 || $title === '') {
        error_response('课程和课节标题不能为空');
    }

    $stmt = $mysqli->prepare('SELECT id FROM courses WHERE id = ? LIMIT 1');
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

    $stmt = $mysqli->prepare('INSERT INTO lessons (course_id, title, video_url) VALUES (?, ?, ?)');
    if (!$stmt) {
        error_response('无法创建课节');
    }
    $stmt->bind_param('iss', $courseId, $title, $videoUrl);
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

    $lessonId = (int) ($input['lesson_id'] ?? $input['id'] ?? 0);
    if ($lessonId <= 0) {
        error_response('课节ID无效');
    }

    $stmt = $mysqli->prepare('SELECT id, course_id, title, video_url FROM lessons WHERE id = ? LIMIT 1');
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

    if ($courseId <= 0) {
        error_response('课程ID无效');
    }
    if ($title === '') {
        error_response('课节标题不能为空');
    }

    if ($courseId !== (int) $current['course_id']) {
        $stmt = $mysqli->prepare('SELECT id FROM courses WHERE id = ? LIMIT 1');
        if (!$stmt) {
            error_response('无法验证课程');
        }
        $stmt->bind_param('i', $courseId);
        $stmt->execute();
        $course = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        if (!$course) {
            error_response('目标课程不存在', 404);
        }
    }

    $stmt = $mysqli->prepare('UPDATE lessons SET course_id = ?, title = ?, video_url = ? WHERE id = ? LIMIT 1');
    if (!$stmt) {
        error_response('无法更新课节');
    }
    $stmt->bind_param('issi', $courseId, $title, $videoUrl, $lessonId);
    if (!$stmt->execute()) {
        $stmt->close();
        error_response('更新课节失败');
    }
    $stmt->close();

    json_response([
        'lesson' => [
            'id' => $lessonId,
            'course_id' => $courseId,
            'title' => $title,
            'video_url' => $videoUrl,
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
    if ($stmt->affected_rows <= 0) {
        $stmt->close();
        error_response('课节不存在或已被删除', 404);
    }
    $stmt->close();

    json_response(['success' => true]);
} else {
    error_response('不支持的请求方法', 405);
}
