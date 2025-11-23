<?php
require __DIR__ . '/bootstrap.php';

$method = $_SERVER['REQUEST_METHOD'];
ensure_teacher_role_enum($mysqli);
$jsonInput = get_json_input();

ensure_lessons_description_column($mysqli);
ensure_lesson_attachments_column($mysqli);
ensure_course_metadata_columns($mysqli);
ensure_course_owner_column($mysqli);

$rawOverride = '';
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

$lessonsHasAttachments = (function (mysqli $mysqli): bool {
    static $cached = null;
    if ($cached !== null) {
        return $cached;
    }
    $check = $mysqli->query("SHOW COLUMNS FROM `lessons` LIKE 'attachments'");
    if ($check instanceof mysqli_result) {
        $cached = $check->num_rows > 0;
        $check->free();
    } else {
        $cached = false;
    }
    return $cached;
})($mysqli);

function ensure_teacher_owns_course(mysqli $mysqli, int $courseId, array $user): void
{
    if (($user['role'] ?? '') !== 'teacher') {
        return;
    }
    $teacherId = (int) $user['id'];
    $stmt = $mysqli->prepare('SELECT owner_id FROM courses WHERE id = ? LIMIT 1');
    if (!$stmt) {
        error_response('无法验证课程归属');
    }
    $stmt->bind_param('i', $courseId);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $stmt->close();
    $ownerId = isset($row['owner_id']) ? (int) $row['owner_id'] : 0;

    if ($ownerId === $teacherId) {
        return;
    }

    // 允许分配给老师的课程
    $assignStmt = $mysqli->prepare('SELECT 1 FROM user_courses WHERE course_id = ? AND user_id = ? LIMIT 1');
    if ($assignStmt) {
        $assignStmt->bind_param('ii', $courseId, $teacherId);
        $assignStmt->execute();
        $hasRow = (bool) $assignStmt->get_result()->fetch_row();
        $assignStmt->close();
        if ($hasRow) {
            return;
        }
    }

    error_response('仅被分配或归属的课程可操作', 403);
}

if ($method === 'POST') {
    $rawOverride = strtoupper(
        $_POST['_method'] ?? $_GET['_method'] ?? $jsonInput['_method'] ?? ($_SERVER['HTTP_X_HTTP_METHOD_OVERRIDE'] ?? '')
    );
    if (in_array($rawOverride, ['DELETE', 'PATCH', 'PUT'], true)) {
        $method = $rawOverride;
    }
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
            $stmt = $mysqli->prepare('SELECT id, title, description, instructor, tags, created_at, owner_id FROM courses WHERE id = ? LIMIT 1');
        } elseif ($user['role'] === 'teacher') {
            $stmt = $mysqli->prepare('SELECT id, title, description, instructor, tags, created_at, owner_id FROM courses WHERE (owner_id = ? OR id IN (SELECT course_id FROM user_courses WHERE user_id = ?)) AND id = ? LIMIT 1');
        } else {
            $stmt = $mysqli->prepare('SELECT c.id, c.title, c.description, c.instructor, c.tags, c.created_at, c.owner_id FROM courses c INNER JOIN user_courses uc ON uc.course_id = c.id WHERE uc.user_id = ? AND c.id = ? LIMIT 1');
        }
        if (!$stmt) {
            error_response('无法获取课程信息');
        }
        if ($user['role'] === 'admin') {
            $stmt->bind_param('i', $courseId);
        } elseif ($user['role'] === 'teacher') {
            $stmt->bind_param('iii', $user['id'], $user['id'], $courseId);
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

        $lessonFields = $lessonsHasAttachments ? 'id, title, video_url, description, attachments' : 'id, title, video_url, description';
        $stmt = $mysqli->prepare("SELECT {$lessonFields} FROM lessons WHERE course_id = ? ORDER BY id ASC");
        if (!$stmt) {
            // fallback无附件列
            $lessonsHasAttachments = false;
            $stmt = $mysqli->prepare('SELECT id, title, video_url, description FROM lessons WHERE course_id = ? ORDER BY id ASC');
            if (!$stmt) {
                error_response('无法获取课节列表');
            }
        }
        $stmt->bind_param('i', $courseId);
        $stmt->execute();
        $lessonsResult = $stmt->get_result();
        $lessons = [];
        while ($row = $lessonsResult->fetch_assoc()) {
            $row['id'] = (int) $row['id'];
            $row['attachments'] = $lessonsHasAttachments ? $normalizeAttachments($row['attachments'] ?? []) : [];
            $lessons[] = $row;
        }
        $stmt->close();

        json_response([
            'course' => $course,
            'lessons' => $lessons,
            'lesson_count' => count($lessons)
        ]);
    } else {
        $all = isset($_GET['all']) && in_array($user['role'], ['admin', 'teacher'], true);
        if ($all) {
            if ($user['role'] === 'teacher') {
                $stmt = $mysqli->prepare('SELECT DISTINCT c.id, c.title, c.description, c.instructor, c.tags, c.created_at, c.owner_id, (SELECT COUNT(*) FROM lessons l WHERE l.course_id = c.id) AS lesson_count FROM courses c LEFT JOIN user_courses uc ON uc.course_id = c.id WHERE c.owner_id = ? OR uc.user_id = ? ORDER BY c.id ASC');
                if (!$stmt) {
                    error_response('无法获取课程列表');
                }
                $stmt->bind_param('ii', $user['id'], $user['id']);
                $stmt->execute();
                $result = $stmt->get_result();
            } else {
                $sql = 'SELECT id, title, description, instructor, tags, created_at, owner_id, (SELECT COUNT(*) FROM lessons l WHERE l.course_id = courses.id) AS lesson_count FROM courses ORDER BY id ASC';
                $result = $mysqli->query($sql);
            }
        } else {
            $stmt = $mysqli->prepare('SELECT c.id, c.title, c.description, c.instructor, c.tags, c.created_at, c.owner_id, (SELECT COUNT(*) FROM lessons l WHERE l.course_id = c.id) AS lesson_count FROM courses c INNER JOIN user_courses uc ON uc.course_id = c.id WHERE uc.user_id = ? ORDER BY c.id ASC');
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
    $user = require_admin_or_teacher($mysqli);
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

    $ownerId = isset($input['owner_id']) ? (int) $input['owner_id'] : null;
    if ($user['role'] === 'teacher') {
        $ownerId = (int) $user['id'];
    }

    $stmt = $mysqli->prepare('INSERT INTO courses (title, description, instructor, tags, owner_id) VALUES (?, ?, ?, ?, ?)');
    if (!$stmt) {
        error_response('无法创建课程');
    }
    $stmt->bind_param('ssssi', $title, $description, $instructor, $tags, $ownerId);
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
            'owner_id' => $ownerId,
            'lesson_count' => isset($input['lessons']) && is_array($input['lessons']) ? count($input['lessons']) : 0
        ]
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

    $courseId = (int) ($input['course_id'] ?? $input['id'] ?? 0);
    if ($courseId <= 0) {
        error_response('课程ID无效');
    }

    $stmt = $mysqli->prepare('SELECT id, title, description, instructor, tags, owner_id FROM courses WHERE id = ? LIMIT 1');
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

    ensure_teacher_owns_course($mysqli, $courseId, $user);

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

    $courseId = (int) ($input['course_id'] ?? $input['id'] ?? 0);
    if ($courseId <= 0) {
        error_response('课程ID无效');
    }
    ensure_teacher_owns_course($mysqli, $courseId, $user);

    delete_course($mysqli, $courseId);
    json_response(['success' => true]);
} else {
    error_response('不支持的请求方法', 405);
}
