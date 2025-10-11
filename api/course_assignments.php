<?php
require __DIR__ . '/bootstrap.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    error_response('仅支持 POST 请求', 405);
}

require_admin($mysqli);
$input = get_json_input();
if (empty($input)) {
    $input = $_POST;
}
$userId = (int) ($input['user_id'] ?? 0);
$courseId = (int) ($input['course_id'] ?? 0);

if ($userId <= 0 || $courseId <= 0) {
    error_response('请选择有效的用户和课程');
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
