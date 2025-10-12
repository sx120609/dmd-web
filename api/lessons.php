<?php
require __DIR__ . '/bootstrap.php';

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'POST') {
    require_admin($mysqli);
    $input = get_json_input();
    if (empty($input)) {
        $input = $_POST;
    }
    $courseId = (int) ($input['course_id'] ?? 0);
    $title = trim($input['title'] ?? '');
    $videoUrl = trim($input['video_url'] ?? '');

    if ($courseId <= 0 || $title === '') {
        error_response('课程和课节标题不能为空');
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

    json_response(['lesson' => ['id' => (int) $lessonId, 'course_id' => $courseId, 'title' => $title, 'video_url' => $videoUrl]]);
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
