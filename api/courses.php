<?php
require __DIR__ . '/bootstrap.php';

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    $user = require_login($mysqli);

    if (isset($_GET['id'])) {
        $courseId = (int) $_GET['id'];
        if ($courseId <= 0) {
            error_response('参数错误');
        }

        if ($user['role'] === 'admin') {
            $stmt = $mysqli->prepare('SELECT id, title, description FROM courses WHERE id = ? LIMIT 1');
        } else {
            $stmt = $mysqli->prepare('SELECT c.id, c.title, c.description FROM courses c INNER JOIN user_courses uc ON uc.course_id = c.id WHERE uc.user_id = ? AND c.id = ? LIMIT 1');
        }
        if (!$stmt) {
            error_response('无法获取课程信息');
        }
        if ($user['role'] === 'admin') {
            $stmt->bind_param('i', $courseId);
        } else {
            $stmt->bind_param('ii', $user['id'], $courseId);
        }
        $stmt->execute();
        $result = $stmt->get_result();
        $course = $result->fetch_assoc();
        $stmt->close();

        if (!$course) {
            error_response('课程不存在或无访问权限', 404);
        }

        $stmt = $mysqli->prepare('SELECT id, title, video_url FROM lessons WHERE course_id = ? ORDER BY id ASC');
        if (!$stmt) {
            error_response('无法获取课节列表');
        }
        $stmt->bind_param('i', $courseId);
        $stmt->execute();
        $lessonsResult = $stmt->get_result();
        $lessons = [];
        while ($row = $lessonsResult->fetch_assoc()) {
            $row['id'] = (int) $row['id'];
            $lessons[] = $row;
        }
        $stmt->close();

        json_response(['course' => $course, 'lessons' => $lessons]);
    } else {
        $all = isset($_GET['all']) && $user['role'] === 'admin';
        if ($all) {
            $sql = 'SELECT id, title, description FROM courses ORDER BY id ASC';
            $result = $mysqli->query($sql);
        } else {
            $stmt = $mysqli->prepare('SELECT c.id, c.title, c.description FROM courses c INNER JOIN user_courses uc ON uc.course_id = c.id WHERE uc.user_id = ? ORDER BY c.id ASC');
            if (!$stmt) {
                error_response('无法获取课程列表');
            }
            $stmt->bind_param('i', $user['id']);
            $stmt->execute();
            $result = $stmt->get_result();
        }

        if (!$result) {
            if (isset($stmt) && $stmt) {
                $stmt->close();
            }
            error_response('无法获取课程列表');
        }
        $courses = [];
        while ($row = $result->fetch_assoc()) {
            $row['id'] = (int) $row['id'];
            $courses[] = $row;
        }
        if (!$all && isset($stmt) && $stmt) {
            $stmt->close();
        }
        json_response(['courses' => $courses]);
    }
} elseif ($method === 'POST') {
    require_admin($mysqli);
    $input = get_json_input();
    if (empty($input)) {
        $input = $_POST;
    }
    $title = trim($input['title'] ?? '');
    $description = trim($input['description'] ?? '');

    if ($title === '') {
        error_response('课程标题不能为空');
    }

    $stmt = $mysqli->prepare('INSERT INTO courses (title, description) VALUES (?, ?)');
    if (!$stmt) {
        error_response('无法创建课程');
    }
    $stmt->bind_param('ss', $title, $description);
    if (!$stmt->execute()) {
        $stmt->close();
        error_response('创建课程失败');
    }
    $courseId = $stmt->insert_id;
    $stmt->close();

    if (!empty($input['lessons']) && is_array($input['lessons'])) {
        $lessonStmt = $mysqli->prepare('INSERT INTO lessons (course_id, title, video_url) VALUES (?, ?, ?)');
        if ($lessonStmt) {
            foreach ($input['lessons'] as $lesson) {
                $lessonTitle = trim($lesson['title'] ?? '');
                $videoUrl = trim($lesson['video_url'] ?? '');
                if ($lessonTitle === '') {
                    continue;
                }
                $lessonStmt->bind_param('iss', $courseId, $lessonTitle, $videoUrl);
                $lessonStmt->execute();
            }
            $lessonStmt->close();
        }
    }

    json_response(['course' => ['id' => (int) $courseId, 'title' => $title, 'description' => $description]]);
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

    $courseId = (int) ($input['course_id'] ?? $input['id'] ?? 0);
    if ($courseId <= 0) {
        error_response('课程ID无效');
    }

    $stmt = $mysqli->prepare('SELECT id, title, description FROM courses WHERE id = ? LIMIT 1');
    if (!$stmt) {
        error_response('无法获取课程信息');
    }
    $stmt->bind_param('i', $courseId);
    $stmt->execute();
    $result = $stmt->get_result();
    $current = $result->fetch_assoc();
    $stmt->close();

    if (!$current) {
        error_response('课程不存在', 404);
    }

    $title = array_key_exists('title', $input) ? trim((string) $input['title']) : ($current['title'] ?? '');
    $description = array_key_exists('description', $input) ? trim((string) $input['description']) : ($current['description'] ?? '');

    if ($title === '') {
        error_response('课程标题不能为空');
    }

    $stmt = $mysqli->prepare('UPDATE courses SET title = ?, description = ? WHERE id = ? LIMIT 1');
    if (!$stmt) {
        error_response('无法更新课程');
    }
    $stmt->bind_param('ssi', $title, $description, $courseId);
    if (!$stmt->execute()) {
        $stmt->close();
        error_response('更新课程失败');
    }
    $stmt->close();

    json_response(['course' => ['id' => (int) $courseId, 'title' => $title, 'description' => $description]]);
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

    $courseId = (int) ($input['course_id'] ?? $input['id'] ?? 0);
    if ($courseId <= 0) {
        error_response('课程ID无效');
    }

    $stmt = $mysqli->prepare('DELETE FROM courses WHERE id = ? LIMIT 1');
    if (!$stmt) {
        error_response('无法删除课程');
    }
    $stmt->bind_param('i', $courseId);
    if (!$stmt->execute()) {
        $stmt->close();
        error_response('删除课程失败');
    }
    $affected = $stmt->affected_rows;
    $stmt->close();

    if ($affected <= 0) {
        error_response('课程不存在或已删除', 404);
    }

    json_response(['success' => true]);
} else {
    error_response('不支持的请求方法', 405);
}
