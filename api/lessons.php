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
$courseId = (int) ($input['course_id'] ?? 0);
$title = trim($input['title'] ?? '');
$videoUrl = trim($input['video_url'] ?? '');

if ($courseId <= 0 || $title === '') {
    error_response('课程和课节标题不能为空');
}

$stmt = $mysqli->prepare('INSERT INTO lessons (course_id, title, video_url) VALUES (?, ?, ?)');
if (!$stmt) {
    error_response('无法创建课节');
}
$stmt->bind_param('iss', $courseId, $title, $videoUrl);
if (!$stmt->execute()) {
    $stmt->close();
    error_response('创建课节失败');
}
$lessonId = $stmt->insert_id;
$stmt->close();

json_response(['lesson' => ['id' => (int) $lessonId, 'course_id' => $courseId, 'title' => $title, 'video_url' => $videoUrl]]);
