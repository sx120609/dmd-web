<?php
require __DIR__ . '/bootstrap.php';
ensure_user_student_no_column($mysqli);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    error_response('仅支持 POST 请求', 405);
}

$input = get_json_input();
if (empty($input)) {
    $input = $_POST;
}

$identifier = trim($input['username'] ?? '');
$password = $input['password'] ?? '';

if ($identifier === '' || $password === '') {
    error_response('请输入用户名和密码');
}

$user = null;
$isStudentNo = preg_match('/^\d{8}$/', $identifier) === 1;

if ($isStudentNo) {
    $stmt = $mysqli->prepare('SELECT id, username, display_name, role, password_hash FROM users WHERE student_no = ? LIMIT 1');
    if (!$stmt) {
        error_response('无法查询用户信息');
    }
    $stmt->bind_param('s', $identifier);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    $stmt->close();
}

if (!$user) {
    $stmt = $mysqli->prepare('SELECT id, username, display_name, role, password_hash FROM users WHERE username = ? LIMIT 1');
    if (!$stmt) {
        error_response('无法查询用户信息');
    }
    $stmt->bind_param('s', $identifier);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    $stmt->close();
}

if (!$user || !password_verify($password, $user['password_hash'])) {
    error_response('账号或学号、密码错误', 401);
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
