<?php
require __DIR__ . '/bootstrap.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    error_response('仅支持 POST 请求', 405);
}

$input = get_json_input();
if (empty($input)) {
    $input = $_POST;
}

$username = trim($input['username'] ?? '');
$password = $input['password'] ?? '';

if ($username === '' || $password === '') {
    error_response('请输入用户名和密码');
}

$stmt = $mysqli->prepare('SELECT id, username, display_name, role, password_hash FROM users WHERE username = ? LIMIT 1');
if (!$stmt) {
    error_response('无法查询用户信息');
}
$stmt->bind_param('s', $username);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();

if (!$user || !password_verify($password, $user['password_hash'])) {
    error_response('用户名或密码错误', 401);
}

$_SESSION['user_id'] = $user['id'];

json_response([
    'user' => [
        'id' => (int) $user['id'],
        'username' => $user['username'],
        'display_name' => $user['display_name'],
        'role' => $user['role'],
    ],
]);
