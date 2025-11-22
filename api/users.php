<?php
require __DIR__ . '/bootstrap.php';

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        require_admin($mysqli);
        $result = $mysqli->query('SELECT id, username, display_name, role FROM users ORDER BY id ASC');
        if (!$result) {
            error_response('无法获取用户列表');
        }
        $users = [];
        while ($row = $result->fetch_assoc()) {
            $row['id'] = (int) $row['id'];
            $users[] = $row;
        }
        json_response(['users' => $users]);
        break;
    case 'POST':
        require_admin($mysqli);
        $input = get_json_input();
        if (empty($input)) {
            $input = $_POST;
        }
        if (($input['action'] ?? '') === 'delete') {
            $userId = isset($input['id']) ? (int) $input['id'] : 0;
            if ($userId <= 0) {
                error_response('缺少用户ID');
            }
            if ($userId === (int) ($_SESSION['user']['id'] ?? 0)) {
                error_response('无法删除当前登录的管理员');
            }

            $stmt = $mysqli->prepare('SELECT role FROM users WHERE id = ? LIMIT 1');
            if (!$stmt) {
                error_response('无法查询用户信息');
            }
            $stmt->bind_param('i', $userId);
            $stmt->execute();
            $result = $stmt->get_result();
            $row = $result->fetch_assoc();
            $stmt->close();

            if (!$row) {
                error_response('用户不存在', 404);
            }

            if ($row['role'] === 'admin') {
                $countResult = $mysqli->query("SELECT COUNT(*) AS total FROM users WHERE role = 'admin'");
                if ($countResult) {
                    $countRow = $countResult->fetch_assoc();
                    if ((int) ($countRow['total'] ?? 0) <= 1) {
                        error_response('系统至少需要一名管理员');
                    }
                }
            }

            $stmt = $mysqli->prepare('DELETE FROM users WHERE id = ? LIMIT 1');
            if (!$stmt) {
                error_response('无法删除用户');
            }
            $stmt->bind_param('i', $userId);
            if (!$stmt->execute()) {
                $stmt->close();
                error_response('删除用户失败');
            }
            $stmt->close();
            json_response(['success' => true]);
        }
        $username = trim($input['username'] ?? '');
        $displayName = trim($input['display_name'] ?? '');
        $password = $input['password'] ?? '';
        $role = $input['role'] ?? 'student';

        if ($username === '' || $password === '') {
            error_response('用户名和密码不能为空');
        }
        if (!in_array($role, ['student', 'admin'], true)) {
            $role = 'student';
        }
        $passwordHash = password_hash($password, PASSWORD_DEFAULT);

        $stmt = $mysqli->prepare('INSERT INTO users (username, display_name, role, password_hash) VALUES (?, ?, ?, ?)');
        if (!$stmt) {
            error_response('无法创建用户');
        }
        $stmt->bind_param('ssss', $username, $displayName, $role, $passwordHash);
        if (!$stmt->execute()) {
            $stmt->close();
            error_response('创建用户失败，可能用户名已存在');
        }
        $userId = $stmt->insert_id;
        $stmt->close();

        json_response(['user' => ['id' => (int) $userId, 'username' => $username, 'display_name' => $displayName, 'role' => $role]]);
        break;
    case 'PUT':
    case 'PATCH':
        $currentAdmin = require_admin($mysqli);
        $input = get_json_input();
        $userId = isset($input['id']) ? (int) $input['id'] : 0;
        if ($userId <= 0) {
            error_response('缺少用户ID');
        }

        $stmt = $mysqli->prepare('SELECT id, username, role FROM users WHERE id = ? LIMIT 1');
        if (!$stmt) {
            error_response('无法查询用户信息');
        }
        $stmt->bind_param('i', $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        $existing = $result->fetch_assoc();
        $stmt->close();

        if (!$existing) {
            error_response('用户不存在', 404);
        }

        $fields = [];
        $types = '';
        $values = [];

        if (array_key_exists('username', $input)) {
            $username = trim((string) ($input['username'] ?? ''));
            if ($username === '') {
                error_response('用户名不能为空');
            }
            $fields[] = 'username = ?';
            $types .= 's';
            $values[] = $username;
        }

        if (array_key_exists('display_name', $input)) {
            $displayName = trim((string) ($input['display_name'] ?? ''));
            if ($displayName === '') {
                $fields[] = 'display_name = NULL';
            } else {
                $fields[] = 'display_name = ?';
                $types .= 's';
                $values[] = $displayName;
            }
        }

        if (array_key_exists('role', $input)) {
            $role = $input['role'] === 'admin' ? 'admin' : 'student';
            if ($existing['role'] === 'admin' && $role !== 'admin') {
                $countResult = $mysqli->query("SELECT COUNT(*) AS total FROM users WHERE role = 'admin'");
                if ($countResult) {
                    $countRow = $countResult->fetch_assoc();
                    if ((int) ($countRow['total'] ?? 0) <= 1) {
                        error_response('系统至少需要一名管理员');
                    }
                }
            }
            if ($userId === (int) $currentAdmin['id'] && $role !== 'admin') {
                error_response('无法修改当前登录管理员的角色');
            }
            $fields[] = 'role = ?';
            $types .= 's';
            $values[] = $role;
        }

        if (!empty($input['password'])) {
            $password = (string) $input['password'];
            if (strlen($password) < 4) {
                error_response('新密码长度至少为4位');
            }
            $passwordHash = password_hash($password, PASSWORD_DEFAULT);
            $fields[] = 'password_hash = ?';
            $types .= 's';
            $values[] = $passwordHash;
        }

        if (!$fields) {
            error_response('没有可更新的字段');
        }

        $query = 'UPDATE users SET ' . implode(', ', $fields) . ' WHERE id = ? LIMIT 1';
        $stmt = $mysqli->prepare($query);
        if (!$stmt) {
            error_response('无法更新用户');
        }
        if ($types !== '') {
            $types .= 'i';
            $values[] = $userId;
            $stmt->bind_param($types, ...$values);
        } else {
            $stmt->bind_param('i', $userId);
        }

        if (!$stmt->execute()) {
            $stmt->close();
            error_response('更新用户信息失败，可能用户名已存在');
        }
        $stmt->close();

        $stmt = $mysqli->prepare('SELECT id, username, display_name, role FROM users WHERE id = ? LIMIT 1');
        if (!$stmt) {
            error_response('无法获取更新后的用户信息');
        }
        $stmt->bind_param('i', $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();
        $stmt->close();

        if (!$user) {
            error_response('无法获取更新后的用户信息');
        }

        $user['id'] = (int) $user['id'];
        json_response(['user' => $user]);
        break;
    case 'DELETE':
        $currentAdmin = require_admin($mysqli);
        $input = get_json_input();
        $userId = 0;
        if (!empty($input['id'])) {
            $userId = (int) $input['id'];
        } elseif (!empty($_GET['id'])) {
            $userId = (int) $_GET['id'];
        }
        if ($userId <= 0) {
            error_response('缺少用户ID');
        }
        if ($userId === (int) $currentAdmin['id']) {
            error_response('无法删除当前登录的管理员');
        }

        $stmt = $mysqli->prepare('SELECT role FROM users WHERE id = ? LIMIT 1');
        if (!$stmt) {
            error_response('无法查询用户信息');
        }
        $stmt->bind_param('i', $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $stmt->close();

        if (!$row) {
            error_response('用户不存在', 404);
        }

        if ($row['role'] === 'admin') {
            $countResult = $mysqli->query("SELECT COUNT(*) AS total FROM users WHERE role = 'admin'");
            if ($countResult) {
                $countRow = $countResult->fetch_assoc();
                if ((int) ($countRow['total'] ?? 0) <= 1) {
                    error_response('系统至少需要一名管理员');
                }
            }
        }

        $stmt = $mysqli->prepare('DELETE FROM users WHERE id = ? LIMIT 1');
        if (!$stmt) {
            error_response('无法删除用户');
        }
        $stmt->bind_param('i', $userId);
        if (!$stmt->execute()) {
            $stmt->close();
            error_response('删除用户失败');
        }
        $stmt->close();

        json_response(['deleted' => true]);
        break;
    default:
        error_response('不支持的请求方法', 405);
}
