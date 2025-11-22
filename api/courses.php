<?php
require __DIR__ . '/bootstrap.php';

$method = $_SERVER['REQUEST_METHOD'];
$jsonInput = get_json_input();

ensure_lessons_description_column($mysqli);
ensure_lesson_attachments_column($mysqli);
ensure_course_metadata_columns($mysqli);

$rawOverride = '';
if ($method === 'POST') {
    $rawOverride = strtoupper(
        $_POST['_method'] ?? $_GET['_method'] ?? $jsonInput['_method'] ?? ($_SERVER['HTTP_X_HTTP_METHOD_OVERRIDE'] ?? '')
    );
    if (in_array($rawOverride, ['DELETE', 'PATCH', 'PUT'], true)) {
        $method = $rawOverride;
    }

    $normalizeAttachments = static function ($raw) {
        if (is_array($raw)) {
            return $raw;
        }
        if (is_string($raw) && trim($raw) !== '') {
            $decoded = json_decode($raw, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                return $decoded;
            }
        }
        return [];
    };
}

function delete_course(mysqli $mysqli, int $courseId): void
{
    // 显式清理关联记录，兼容未开启级联的旧表结构
    $mysqli->begin_transaction();
    try {
        $stmt = $mysqli->prepare('DELETE FROM lessons WHERE course_id = ?');
        if (!$stmt) {
            throw new Exception('无法删除课节');
        }
        $stmt->bind_param('i', $courseId);
        if (!$stmt->execute()) {
            throw new Exception('删除课节失败');
        }
        $stmt->close();

        $stmt = $mysqli->prepare('DELETE FROM user_courses WHERE course_id = ?');
        if ($stmt) {
            $stmt->bind_param('i', $courseId);
            $stmt->execute();
            $stmt->close();
        }

        $stmt = $mysqli->prepare('DELETE FROM courses WHERE id = ? LIMIT 1');
        if (!$stmt) {
            throw new Exception('无法删除课程');
        }
        $stmt->bind_param('i', $courseId);
        if (!$stmt->execute()) {
            throw new Exception('删除课程失败');
        }
        $affected = $stmt->affected_rows;
        $stmt->close();

        if ($affected <= 0) {
            $mysqli->rollback();
            error_response('课程不存在或已删除', 404);
        }

        $mysqli->commit();
    } catch (Exception $e) {
        $mysqli->rollback();
        error_response($e->getMessage() ?: '删除课程失败');
    }
}

if ($method === 'GET') {
    $user = require_login($mysqli);

    if (isset($_GET['id'])) {
        $courseId = (int) $_GET['id'];
        if ($courseId <= 0) {
            error_response('参数错误');
        }

        if ($user['role'] === 'admin') {
            $stmt = $mysqli->prepare('SELECT id, title, description, instructor, tags, created_at FROM courses WHERE id = ? LIMIT 1');
        } else {
            $stmt = $mysqli->prepare('SELECT c.id, c.title, c.description, c.instructor, c.tags, c.created_at FROM courses c INNER JOIN user_courses uc ON uc.course_id = c.id WHERE uc.user_id = ? AND c.id = ? LIMIT 1');
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

        $stmt = $mysqli->prepare('SELECT id, title, video_url, description, attachments FROM lessons WHERE course_id = ? ORDER BY id ASC');
        if (!$stmt) {
            error_response('无法获取课节列表');
        }
        $stmt->bind_param('i', $courseId);
        $stmt->execute();
        $lessonsResult = $stmt->get_result();
        $lessons = [];
        while ($row = $lessonsResult->fetch_assoc()) {
            $row['id'] = (int) $row['id'];
            $row['attachments'] = $normalizeAttachments($row['attachments'] ?? []);
            $lessons[] = $row;
        }
        $stmt->close();

        json_response([
            'course' => $course,
            'lessons' => $lessons,
            'lesson_count' => count($lessons)
        ]);
    } else {
        $all = isset($_GET['all']) && $user['role'] === 'admin';
        if ($all) {
            $sql = 'SELECT id, title, description, instructor, tags, created_at, (SELECT COUNT(*) FROM lessons l WHERE l.course_id = courses.id) AS lesson_count FROM courses ORDER BY id ASC';
            $result = $mysqli->query($sql);
        } else {
            $stmt = $mysqli->prepare('SELECT c.id, c.title, c.description, c.instructor, c.tags, c.created_at, (SELECT COUNT(*) FROM lessons l WHERE l.course_id = c.id) AS lesson_count FROM courses c INNER JOIN user_courses uc ON uc.course_id = c.id WHERE uc.user_id = ? ORDER BY c.id ASC');
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
            $row['attachments'] = isset($row['attachments']) ? $normalizeAttachments($row['attachments']) : [];
            $courses[] = $row;
        }
        if (!$all && isset($stmt) && $stmt) {
            $stmt->close();
        }
        json_response(['courses' => $courses]);
    }
} elseif ($method === 'POST') {
    require_admin($mysqli);
    $input = $jsonInput ?: get_json_input();
    if (empty($input)) {
        $input = $_POST;
    }
    $methodOverride = strtoupper($input['_method'] ?? $input['method'] ?? $input['action'] ?? '');
    if ($methodOverride === 'DELETE') {
        $courseId = (int) ($input['course_id'] ?? $input['id'] ?? 0);
        if ($courseId <= 0) {
            error_response('课程ID无效');
        }
        delete_course($mysqli, $courseId);
        json_response(['success' => true]);
    }

    $title = trim($input['title'] ?? '');
    $description = trim($input['description'] ?? '');
    $instructor = trim($input['instructor'] ?? '');
    $tags = trim($input['tags'] ?? '');

    if ($title === '') {
        error_response('课程标题不能为空');
    }

    $stmt = $mysqli->prepare('INSERT INTO courses (title, description, instructor, tags) VALUES (?, ?, ?, ?)');
    if (!$stmt) {
        error_response('无法创建课程');
    }
    $stmt->bind_param('ssss', $title, $description, $instructor, $tags);
    if (!$stmt->execute()) {
        $stmt->close();
        error_response('创建课程失败');
    }
    $courseId = $stmt->insert_id;
    $stmt->close();

    if (!empty($input['lessons']) && is_array($input['lessons'])) {
        $lessonStmt = $mysqli->prepare('INSERT INTO lessons (course_id, title, video_url, description) VALUES (?, ?, ?, ?)');
        if ($lessonStmt) {
            foreach ($input['lessons'] as $lesson) {
                $lessonTitle = trim($lesson['title'] ?? '');
                $videoUrl = trim($lesson['video_url'] ?? '');
                $lessonDescription = trim($lesson['description'] ?? '');
                if ($lessonTitle === '') {
                    continue;
                }
                $lessonStmt->bind_param('isss', $courseId, $lessonTitle, $videoUrl, $lessonDescription);
                $lessonStmt->execute();
            }
            $lessonStmt->close();
        }
    }

    json_response([
        'course' => [
            'id' => (int) $courseId,
            'title' => $title,
            'description' => $description,
            'instructor' => $instructor,
            'tags' => $tags,
            'lesson_count' => isset($input['lessons']) && is_array($input['lessons']) ? count($input['lessons']) : 0
        ]
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

    $courseId = (int) ($input['course_id'] ?? $input['id'] ?? 0);
    if ($courseId <= 0) {
        error_response('课程ID无效');
    }

    $stmt = $mysqli->prepare('SELECT id, title, description, instructor, tags FROM courses WHERE id = ? LIMIT 1');
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
    $instructor = array_key_exists('instructor', $input) ? trim((string) $input['instructor']) : ($current['instructor'] ?? '');
    $tags = array_key_exists('tags', $input) ? trim((string) $input['tags']) : ($current['tags'] ?? '');

    if ($title === '') {
        error_response('课程标题不能为空');
    }

    $stmt = $mysqli->prepare('UPDATE courses SET title = ?, description = ?, instructor = ?, tags = ? WHERE id = ? LIMIT 1');
    if (!$stmt) {
        error_response('无法更新课程');
    }
    $stmt->bind_param('ssssi', $title, $description, $instructor, $tags, $courseId);
    if (!$stmt->execute()) {
        $stmt->close();
        error_response('更新课程失败');
    }
    $stmt->close();

    json_response([
        'course' => [
            'id' => (int) $courseId,
            'title' => $title,
            'description' => $description,
            'instructor' => $instructor,
            'tags' => $tags
        ]
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

    $courseId = (int) ($input['course_id'] ?? $input['id'] ?? 0);
    if ($courseId <= 0) {
        error_response('课程ID无效');
    }

    delete_course($mysqli, $courseId);
    json_response(['success' => true]);
} else {
    error_response('不支持的请求方法', 405);
}
