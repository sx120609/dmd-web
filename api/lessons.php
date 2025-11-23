<?php
require __DIR__ . '/bootstrap.php';

$method = $_SERVER['REQUEST_METHOD'];
ensure_teacher_role_enum($mysqli);
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
ensure_lesson_attachments_column($mysqli);

function assert_course_access(mysqli $mysqli, int $courseId, array $user): void
{
    if (($user['role'] ?? '') !== 'teacher') {
        return;
    }
    $stmt = $mysqli->prepare('SELECT owner_id FROM courses WHERE id = ? LIMIT 1');
    if (!$stmt) {
        error_response('无法验证课程权限');
    }
    $stmt->bind_param('i', $courseId);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $stmt->close();
    if ($row && (int) ($row['owner_id'] ?? 0) === (int) $user['id']) {
        return;
    }

    $assignStmt = $mysqli->prepare('SELECT 1 FROM user_courses WHERE course_id = ? AND user_id = ? LIMIT 1');
    if ($assignStmt) {
        $assignStmt->bind_param('ii', $courseId, $user['id']);
        $assignStmt->execute();
        $found = (bool) $assignStmt->get_result()->fetch_row();
        $assignStmt->close();
        if ($found) {
            return;
        }
    }

    error_response('仅所属或分配的课程可编辑课节', 403);
}

if ($method === 'POST') {
    $user = require_admin_or_teacher($mysqli);
    $input = $jsonInput ?: get_json_input();
    if (empty($input)) {
        $input = $_POST;
    }
    $courseId = (int) ($input['course_id'] ?? 0);
    $title = trim($input['title'] ?? '');
    $videoUrl = trim($input['video_url'] ?? '');
    $description = trim($input['description'] ?? '');
    $attachmentsRaw = $input['attachments'] ?? null;
    $attachments = [];
    if (is_array($attachmentsRaw)) {
        $attachments = $attachmentsRaw;
    } elseif (is_string($attachmentsRaw) && trim($attachmentsRaw) !== '') {
        $lines = explode("\n", $attachmentsRaw);
        foreach ($lines as $line) {
            $parts = array_map('trim', explode('|', $line, 2));
            if (count($parts) === 2 && $parts[1] !== '') {
                $attachments[] = ['title' => $parts[0] ?: $parts[1], 'url' => $parts[1]];
            } elseif ($parts[0] !== '') {
                $attachments[] = ['title' => $parts[0], 'url' => $parts[0]];
            }
        }
    }

    if ($courseId <= 0 || $title === '') {
        error_response('课程和课节标题不能为空');
    }
    assert_course_access($mysqli, $courseId, $user);

    $attachmentsJson = $attachments ? json_encode($attachments, JSON_UNESCAPED_UNICODE) : null;

    $stmt = $mysqli->prepare('INSERT INTO lessons (course_id, title, video_url, description, attachments) VALUES (?, ?, ?, ?, ?)');
    if (!$stmt) {
        error_response('无法创建课节');
    }
    $stmt->bind_param('issss', $courseId, $title, $videoUrl, $description, $attachmentsJson);
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
            'attachments' => $attachments,
        ],
    ]);
} elseif ($method === 'PATCH' || $method === 'PUT') {
    $user = require_admin_or_teacher($mysqli);
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

    $stmt = $mysqli->prepare('SELECT id, course_id, title, video_url, description, attachments FROM lessons WHERE id = ? LIMIT 1');
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
    $attachmentsRaw = $input['attachments'] ?? null;
    $attachments = [];
    if (array_key_exists('attachments', $input)) {
        if (is_array($attachmentsRaw)) {
            $attachments = $attachmentsRaw;
        } elseif (is_string($attachmentsRaw) && trim($attachmentsRaw) !== '') {
            $lines = explode("\n", $attachmentsRaw);
            foreach ($lines as $line) {
                $parts = array_map('trim', explode('|', $line, 2));
                if (count($parts) === 2 && $parts[1] !== '') {
                    $attachments[] = ['title' => $parts[0] ?: $parts[1], 'url' => $parts[1]];
                } elseif ($parts[0] !== '') {
                    $attachments[] = ['title' => $parts[0], 'url' => $parts[0]];
                }
            }
        } else {
            $attachments = [];
        }
    } else {
        if (!empty($current['attachments'])) {
            $decoded = json_decode($current['attachments'], true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                $attachments = $decoded;
            }
        }
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
    assert_course_access($mysqli, $courseId, $user);

    $attachmentsJson = $attachments ? json_encode($attachments, JSON_UNESCAPED_UNICODE) : null;

    $stmt = $mysqli->prepare('UPDATE lessons SET course_id = ?, title = ?, video_url = ?, description = ?, attachments = ? WHERE id = ? LIMIT 1');
    if (!$stmt) {
        error_response('无法更新课节');
    }
    $stmt->bind_param('issssi', $courseId, $title, $videoUrl, $description, $attachmentsJson, $lessonId);
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
            'attachments' => $attachments,
        ],
    ]);
} elseif ($method === 'DELETE') {
    $user = require_admin_or_teacher($mysqli);
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

    $courseCheck = $mysqli->prepare('SELECT course_id FROM lessons WHERE id = ? LIMIT 1');
    if ($courseCheck) {
        $courseCheck->bind_param('i', $lessonId);
        $courseCheck->execute();
        $res = $courseCheck->get_result()->fetch_assoc();
        $courseCheck->close();
        if ($res && isset($res['course_id'])) {
            assert_course_access($mysqli, (int) $res['course_id'], $user);
        }
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
