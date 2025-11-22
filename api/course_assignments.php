<?php
require __DIR__ . '/bootstrap.php';

$method = $_SERVER['REQUEST_METHOD'];
$jsonInput = get_json_input();
if ($method === 'POST') {
    $override = strtoupper(
        $_POST['_method'] ?? $_GET['_method'] ?? $jsonInput['_method'] ?? ($_SERVER['HTTP_X_HTTP_METHOD_OVERRIDE'] ?? '')
    );
    if (in_array($override, ['DELETE'], true)) {
        $method = $override;
    }
}

switch ($method) {
    case 'GET':
        require_admin($mysqli);
        $userId = isset($_GET['user_id']) ? (int) $_GET['user_id'] : 0;

        if ($userId > 0) {
            $userStmt = $mysqli->prepare('SELECT id, username, display_name FROM users WHERE id = ? LIMIT 1');
            if (!$userStmt) {
                error_response('无法获取用户信息');
            }
            $userStmt->bind_param('i', $userId);
            $userStmt->execute();
            $userResult = $userStmt->get_result();
            $user = $userResult->fetch_assoc();
            $userStmt->close();

            if (!$user) {
                error_response('用户不存在', 404);
            }

            $stmt = $mysqli->prepare('SELECT uc.course_id, c.title, c.description FROM user_courses uc INNER JOIN courses c ON c.id = uc.course_id WHERE uc.user_id = ? ORDER BY c.id ASC');
            if (!$stmt) {
                error_response('无法获取分配课程');
            }
            $stmt->bind_param('i', $userId);
            $stmt->execute();
            $result = $stmt->get_result();
            $assignments = [];
            while ($row = $result->fetch_assoc()) {
                $assignments[] = [
                    'course_id' => (int) $row['course_id'],
                    'course_title' => $row['title'],
                    'course_description' => $row['description']
                ];
            }
            $stmt->close();

            json_response([
                'assignments' => $assignments,
                'user' => [
                    'id' => (int) $user['id'],
                    'username' => $user['username'],
                    'display_name' => $user['display_name']
                ]
            ]);
        } else {
            $sql = 'SELECT uc.user_id, u.username, u.display_name, uc.course_id, c.title, c.description FROM user_courses uc INNER JOIN users u ON u.id = uc.user_id INNER JOIN courses c ON c.id = uc.course_id ORDER BY u.id ASC, c.id ASC';
            $result = $mysqli->query($sql);
            if (!$result) {
                error_response('无法获取课程分配列表');
            }
            $assignments = [];
            while ($row = $result->fetch_assoc()) {
                $assignments[] = [
                    'user_id' => (int) $row['user_id'],
                    'username' => $row['username'],
                    'display_name' => $row['display_name'],
                    'course_id' => (int) $row['course_id'],
                    'course_title' => $row['title'],
                    'course_description' => $row['description']
                ];
            }
            json_response(['assignments' => $assignments]);
        }
        break;
    case 'POST':
        require_admin($mysqli);
        $input = $jsonInput ?: get_json_input();
        if (empty($input)) {
            $input = $_POST;
        }
        $action = $input['action'] ?? 'assign';
        $userId = (int) ($input['user_id'] ?? 0);
        $courseId = (int) ($input['course_id'] ?? 0);

        if ($userId <= 0 || $courseId <= 0) {
            error_response('请选择有效的用户和课程');
        }

        if ($action === 'delete' || $action === 'remove') {
            $stmt = $mysqli->prepare('DELETE FROM user_courses WHERE user_id = ? AND course_id = ?');
            if (!$stmt) {
                error_response('无法移除课程分配');
            }
            $stmt->bind_param('ii', $userId, $courseId);
            if (!$stmt->execute()) {
                $stmt->close();
                error_response('移除课程分配失败');
            }
            $removed = $stmt->affected_rows > 0;
            $stmt->close();
            json_response(['success' => true, 'removed' => $removed]);
        }

        $stmt = $mysqli->prepare('INSERT IGNORE INTO user_courses (user_id, course_id) VALUES (?, ?)');
        if (!$stmt) {
            error_response('无法分配课程');
        }
        $stmt->bind_param('ii', $userId, $courseId);
        if (!$stmt->execute()) {
            $stmt->close();
            error_response('分配课程失败');
        }
        $stmt->close();

        json_response(['success' => true]);
        break;
    case 'DELETE':
        require_admin($mysqli);
        $input = $jsonInput ?: get_json_input();
        if (empty($input)) {
            $input = $_POST;
            if (empty($input)) {
                $input = $_GET;
            }
        }
        $userId = (int) ($input['user_id'] ?? 0);
        $courseId = (int) ($input['course_id'] ?? 0);

        if ($userId <= 0 || $courseId <= 0) {
            error_response('请选择有效的用户和课程');
        }

        $stmt = $mysqli->prepare('DELETE FROM user_courses WHERE user_id = ? AND course_id = ?');
        if (!$stmt) {
            error_response('无法移除课程分配');
        }
        $stmt->bind_param('ii', $userId, $courseId);
        if (!$stmt->execute()) {
            $stmt->close();
            error_response('移除课程分配失败');
        }
        $removed = $stmt->affected_rows > 0;
        $stmt->close();

        json_response(['success' => true, 'removed' => $removed]);
        break;
    default:
        error_response('不支持的请求方法', 405);
}
