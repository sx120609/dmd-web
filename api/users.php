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
    default:
        error_response('不支持的请求方法', 405);
}
