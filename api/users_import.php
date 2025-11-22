<?php
require __DIR__ . '/bootstrap.php';
require_admin($mysqli);

ensure_cloud_files_table($mysqli); // no-op if exists, keep consistency

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    error_response('只支持 POST 上传', 405);
}

if (empty($_FILES['file'])) {
    error_response('请上传包含用户数据的 CSV 文件');
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
    // Remove UTF-8 BOM if present
    $v = preg_replace('/^\xEF\xBB\xBF/', '', $v);
    return trim($v);
};

$expectedHeaders = ['username', 'display_name', 'password', 'role'];
$header = array_map($normalize, $header);
if (array_map('strtolower', $header) !== $expectedHeaders) {
    fclose($handle);
    error_response('表头需为：username,display_name,password,role（CSV 格式）');
}

$insertStmt = $mysqli->prepare('INSERT INTO users (username, display_name, password_hash, role) VALUES (?, ?, ?, ?)');
if (!$insertStmt) {
    fclose($handle);
    error_response('无法准备写入用户');
}

$inserted = 0;
$skipped = 0;
$errors = [];
$rowNumber = 1; // header counted

while (($row = fgetcsv($handle)) !== false) {
    $rowNumber++;
    // Skip empty lines
    if (count(array_filter($row, fn($v) => trim((string) $v) !== '')) === 0) {
        continue;
    }
    $row = array_map($normalize, $row);
    [$username, $displayName, $password, $role] = array_pad($row, 4, '');

    if ($username === '' || $password === '') {
        $errors[] = "第 {$rowNumber} 行缺少用户名或密码，已跳过";
        $skipped++;
        continue;
    }
    if (!in_array($role, ['student', 'admin'], true)) {
        $errors[] = "第 {$rowNumber} 行角色无效（仅支持 student 或 admin），已按 student 处理";
        $role = 'student';
    }

    $passwordHash = password_hash($password, PASSWORD_DEFAULT);
    $insertStmt->bind_param('ssss', $username, $displayName, $passwordHash, $role);
    if (!$insertStmt->execute()) {
        if ($mysqli->errno === 1062) {
            $errors[] = "第 {$rowNumber} 行用户名已存在，已跳过";
        } else {
            $errors[] = "第 {$rowNumber} 行写入失败：" . $insertStmt->error;
        }
        $skipped++;
        continue;
    }
    if ($insertStmt->affected_rows > 0) {
        $inserted++;
    } else {
        $errors[] = "第 {$rowNumber} 行用户名重复，已跳过";
        $skipped++;
    }
}

fclose($handle);
$insertStmt->close();

json_response([
    'success' => true,
    'inserted' => $inserted,
    'skipped' => $skipped,
    'errors' => $errors
]);
