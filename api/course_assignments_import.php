<?php
require __DIR__ . '/bootstrap.php';
require_admin($mysqli);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    error_response('只支持 POST 上传', 405);
}

if (empty($_FILES['file'])) {
    error_response('请上传包含分配数据的 CSV 文件');
}

$file = $_FILES['file'];
if ($file['error'] !== UPLOAD_ERR_OK) {
    error_response('上传失败，错误码: ' . $file['error']);
}

$maxSize = 5 * 1024 * 1024; // 5MB CSV
if ((int) $file['size'] > $maxSize) {
    error_response('文件过大，请控制在 5MB 以内');
}

$tmpPath = $file['tmp_name'];
$handle = fopen($tmpPath, 'rb');
if ($handle === false) {
    error_response('无法读取上传文件');
}

$header = fgetcsv($handle);
if ($header === false) {
    fclose($handle);
    error_response('文件为空或格式不正确');
}

$normalize = static function ($value) {
    $v = (string) $value;
    $v = preg_replace('/^\xEF\xBB\xBF/', '', $v);
    return trim($v);
};

$expectedHeaders = ['username', 'course_id'];
$header = array_map($normalize, $header);
if (array_map('strtolower', $header) !== $expectedHeaders) {
    fclose($handle);
    error_response('表头需为：username,course_id（CSV 格式）');
}

$userStmt = $mysqli->prepare('SELECT id FROM users WHERE username = ? LIMIT 1');
if (!$userStmt) {
    fclose($handle);
    error_response('无法准备用户查询');
}

$courseIdStmt = $mysqli->prepare('SELECT id, title FROM courses WHERE id = ? LIMIT 1');
if (!$courseIdStmt) {
    fclose($handle);
    $userStmt->close();
    error_response('无法准备课程查询');
}

$insertStmt = $mysqli->prepare('INSERT IGNORE INTO user_courses (user_id, course_id) VALUES (?, ?)');
if (!$insertStmt) {
    fclose($handle);
    $userStmt->close();
    $courseIdStmt->close();
    error_response('无法准备写入分配');
}

$inserted = 0;
$skipped = 0;
$errors = [];
$rowNumber = 1; // header counted

while (($row = fgetcsv($handle)) !== false) {
    $rowNumber++;
    if (count(array_filter($row, fn($v) => trim((string) $v) !== '')) === 0) {
        continue;
    }
    $row = array_map($normalize, $row);
    [$username, $courseIdRaw] = array_pad($row, 2, '');

    if ($username === '' || $courseIdRaw === '') {
        $errors[] = "第 {$rowNumber} 行缺少用户名或课程ID，已跳过";
        $skipped++;
        continue;
    }

    $courseId = (int) $courseIdRaw;
    if ($courseId <= 0) {
        $errors[] = "第 {$rowNumber} 行课程ID无效（{$courseIdRaw}），已跳过";
        $skipped++;
        continue;
    }

    $userStmt->bind_param('s', $username);
    $userStmt->execute();
    $userResult = $userStmt->get_result();
    $userRow = $userResult->fetch_assoc();
    $userResult->free();
    if (!$userRow) {
        $errors[] = "第 {$rowNumber} 行用户不存在（{$username}），已跳过";
        $skipped++;
        continue;
    }
    $userId = (int) $userRow['id'];

    $courseIdStmt->bind_param('i', $courseId);
    $courseIdStmt->execute();
    $courseResult = $courseIdStmt->get_result();
    $courseRow = $courseResult->fetch_assoc();
    $courseResult->free();
    if (!$courseRow) {
        $errors[] = "第 {$rowNumber} 行课程ID不存在（{$courseIdRaw}），已跳过";
        $skipped++;
        continue;
    }
    $resolvedCourseTitle = $courseRow['title'] ?? '';

    $insertStmt->bind_param('ii', $userId, $courseId);
    if (!$insertStmt->execute()) {
        $errors[] = "第 {$rowNumber} 行写入失败（{$username} - {$resolvedCourseTitle}）";
        $skipped++;
        continue;
    }

    if ($insertStmt->affected_rows > 0) {
        $inserted++;
    } else {
        $skipped++;
    }
}

fclose($handle);
$userStmt->close();
$courseIdStmt->close();
$insertStmt->close();

json_response([
    'success' => true,
    'inserted' => $inserted,
    'skipped' => $skipped,
    'errors' => $errors
]);
