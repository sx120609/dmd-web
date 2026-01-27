<?php
require __DIR__ . '/bootstrap.php';

$method = $_SERVER['REQUEST_METHOD'];
$jsonInput = get_json_input();
ensure_user_progress_table($mysqli);

if ($method === 'POST') {
    $rawOverride = strtoupper(
        $_POST['_method'] ?? $_GET['_method'] ?? $jsonInput['_method'] ?? ($_SERVER['HTTP_X_HTTP_METHOD_OVERRIDE'] ?? '')
    );
    if (in_array($rawOverride, ['DELETE', 'PATCH', 'PUT'], true)) {
        $method = $rawOverride;
    }
}

if ($method === 'GET') {
    $user = require_login($mysqli);
    $courseId = isset($_GET['course_id']) ? (int) $_GET['course_id'] : 0;

    if ($courseId > 0) {
        $stmt = $mysqli->prepare('SELECT course_id, lesson_id, visited_at IS NOT NULL AS visited, completed_at IS NOT NULL AS completed FROM user_lesson_progress WHERE user_id = ? AND course_id = ?');
        if (!$stmt) {
            error_response('无法获取学习进度');
        }
        $stmt->bind_param('ii', $user['id'], $courseId);
    } else {
        $stmt = $mysqli->prepare('SELECT course_id, lesson_id, visited_at IS NOT NULL AS visited, completed_at IS NOT NULL AS completed FROM user_lesson_progress WHERE user_id = ?');
        if (!$stmt) {
            error_response('无法获取学习进度');
        }
        $stmt->bind_param('i', $user['id']);
    }

    $stmt->execute();
    $result = $stmt->get_result();
    $progress = [];
    while ($row = $result->fetch_assoc()) {
        $progress[] = [
            'course_id' => (int) $row['course_id'],
            'lesson_id' => (int) $row['lesson_id'],
            'visited' => (int) $row['visited'] === 1,
            'completed' => (int) $row['completed'] === 1
        ];
    }
    $stmt->close();

    json_response(['progress' => $progress]);
}

if ($method === 'POST') {
    $user = require_login($mysqli);
    $courseId = (int) ($jsonInput['course_id'] ?? $_POST['course_id'] ?? 0);
    $lessonId = (int) ($jsonInput['lesson_id'] ?? $_POST['lesson_id'] ?? 0);
    $action = strtolower(trim((string) ($jsonInput['action'] ?? $_POST['action'] ?? '')));

    if ($courseId <= 0 || $lessonId <= 0 || $action === '') {
        error_response('参数错误');
    }

    if ($action === 'visit') {
        $stmt = $mysqli->prepare(
            'INSERT INTO user_lesson_progress (user_id, course_id, lesson_id, visited_at) VALUES (?, ?, ?, NOW()) '
            . 'ON DUPLICATE KEY UPDATE course_id = VALUES(course_id), visited_at = IFNULL(visited_at, VALUES(visited_at))'
        );
        if (!$stmt) {
            error_response('无法记录进度');
        }
        $stmt->bind_param('iii', $user['id'], $courseId, $lessonId);
        $stmt->execute();
        $stmt->close();
        json_response(['ok' => true]);
    }

    if ($action === 'complete') {
        $stmt = $mysqli->prepare(
            'INSERT INTO user_lesson_progress (user_id, course_id, lesson_id, visited_at, completed_at) VALUES (?, ?, ?, NOW(), NOW()) '
            . 'ON DUPLICATE KEY UPDATE course_id = VALUES(course_id), visited_at = IFNULL(visited_at, VALUES(visited_at)), completed_at = VALUES(completed_at)'
        );
        if (!$stmt) {
            error_response('无法记录进度');
        }
        $stmt->bind_param('iii', $user['id'], $courseId, $lessonId);
        $stmt->execute();
        $stmt->close();
        json_response(['ok' => true]);
    }

    if ($action === 'uncomplete') {
        $stmt = $mysqli->prepare('UPDATE user_lesson_progress SET completed_at = NULL WHERE user_id = ? AND lesson_id = ? LIMIT 1');
        if (!$stmt) {
            error_response('无法更新进度');
        }
        $stmt->bind_param('ii', $user['id'], $lessonId);
        $stmt->execute();
        $stmt->close();
        json_response(['ok' => true]);
    }

    error_response('不支持的操作');
}

error_response('不支持的请求方式', 405);
