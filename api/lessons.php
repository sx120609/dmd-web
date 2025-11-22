<?php
require __DIR__ . '/bootstrap.php';

$method = $_SERVER['REQUEST_METHOD'];
$jsonInput = get_json_input();

// 部分防火墙/代理会拦截 PUT/PATCH/DELETE，这里支持 _method/头部覆盖
if ($method === 'POST') {
    $override = strtoupper(
        $_POST['_method'] ?? $_GET['_method'] ?? $jsonInput['_method'] ?? ($_SERVER['HTTP_X_HTTP_METHOD_OVERRIDE'] ?? '')
    );
    if (in_array($override, ['PUT', 'PATCH', 'DELETE'], true)) {
        $method = $override;
    }
}

ensure_lessons_description_column($mysqli);

if ($method === 'POST') {
    require_admin($mysqli);
    $input = $jsonInput ?: get_json_input();
    if (empty($input)) {
        $input = $_POST;
    }
    $courseId = (int) ($input['course_id'] ?? 0);
    $title = trim($input['title'] ?? '');
    $videoUrl = trim($input['video_url'] ?? '');
    $description = trim($input['description'] ?? '');

    if ($courseId <= 0 || $title === '') {
        error_response('课程和课节标题不能为空');
    }

    $stmt = $mysqli->prepare('INSERT INTO lessons (course_id, title, video_url, description) VALUES (?, ?, ?, ?)');
    if (!$stmt) {
        error_response('无法创建课节');
    }
    $stmt->bind_param('isss', $courseId, $title, $videoUrl, $description);
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
            'description' => $description,
        ],
    ]);
} elseif ($method === 'PATCH' || $method === 'PUT') {
    require_admin($mysqli);
    $input = $jsonInput ?: get_json_input();
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

    $stmt = $mysqli->prepare('SELECT id, course_id, title, video_url, description FROM lessons WHERE id = ? LIMIT 1');
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
    $description = array_key_exists('description', $input)
        ? trim((string) $input['description'])
        : ($current['description'] ?? '');

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

    $stmt = $mysqli->prepare('UPDATE lessons SET course_id = ?, title = ?, video_url = ?, description = ? WHERE id = ? LIMIT 1');
    if (!$stmt) {
        error_response('无法更新课节');
    }
    $stmt->bind_param('isssi', $courseId, $title, $videoUrl, $description, $lessonId);
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
            'description' => $description,
        ],
    ]);
} elseif ($method === 'DELETE') {
    require_admin($mysqli);
    $input = $jsonInput ?: get_json_input();
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
