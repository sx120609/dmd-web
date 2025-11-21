<?php
require __DIR__ . '/bootstrap.php';
ensure_cloud_files_table($mysqli);

$configuredStorage = $config['storage']['cloud_dir'] ?? null;
if (is_string($configuredStorage) && trim($configuredStorage) !== '') {
    $storageDir = $configuredStorage;
    $isAbsolute = ($storageDir[0] ?? '') === '/' || preg_match('~^[A-Za-z]:[\\\\/]~', $storageDir);
    if (!$isAbsolute) {
        $storageDir = $rootDir . '/' . ltrim($storageDir, '/');
    }
} else {
    $storageDir = $rootDir . '/uploads/files';
}
$baseUrl = '/api/files.php';
$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

function ensure_storage_dir(string $dir): void
{
    $parent = dirname($dir);
    if (!is_dir($dir)) {
        if ((!is_dir($parent) || !is_writable($parent)) && !is_writable($parent)) {
            error_response('文件目录不可写：' . $dir . '。请手动创建该目录并赋予 PHP 进程写权限。', 500);
        }
        if (!@mkdir($dir, 0775, true) && !is_dir($dir)) {
            error_response('无法创建文件目录：' . $dir . '。请手动创建并确认权限。', 500);
        }
    }
    if (!is_writable($dir)) {
        error_response('文件目录不可写：' . $dir . '。请调整权限后重试。', 500);
    }
}

ensure_storage_dir($storageDir);

function short_size(int $bytes): string
{
    if ($bytes <= 0) {
        return '0 B';
    }
    $units = ['B', 'KB', 'MB', 'GB', 'TB'];
    $i = (int) floor(log($bytes, 1024));
    $i = max(0, min($i, count($units) - 1));
    $val = $bytes / (1024 ** $i);
    return sprintf('%s %s', $val >= 10 || $i === 0 ? round($val) : round($val, 1), $units[$i]);
}

function file_payload(array $row): array
{
    return [
        'id' => (int) $row['id'],
        'original_name' => $row['original_name'],
        'mime_type' => $row['mime_type'],
        'size_bytes' => (int) $row['size_bytes'],
        'is_public' => (bool) $row['is_public'],
        'share_token' => $row['share_token'],
        'created_at' => $row['created_at'],
        'share_url' => '/api/files.php?token=' . $row['share_token'],
        'download_url' => '/api/files.php?id=' . $row['id'] . '&download=1'
    ];
}

function fetch_file_by_id(mysqli $mysqli, int $id): ?array
{
    $stmt = $mysqli->prepare('SELECT * FROM cloud_files WHERE id = ? LIMIT 1');
    if (!$stmt) {
        error_response('无法读取文件记录');
    }
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc() ?: null;
    $stmt->close();
    return $row;
}

function stream_file_download(array $file, string $storageDir): void
{
    $path = $storageDir . '/' . $file['stored_name'];
    if (!is_file($path)) {
        error_response('文件已不存在', 404);
    }
    header_remove('Content-Type');
    header('Content-Type: ' . ($file['mime_type'] ?: 'application/octet-stream'));
    header('Content-Disposition: attachment; filename="' . rawurlencode($file['original_name']) . '"');
    header('Content-Length: ' . $file['size_bytes']);
    readfile($path);
    exit;
}

function upload_error_text(int $errorCode): string
{
    $uploadMax = ini_get('upload_max_filesize') ?: '—';
    $postMax = ini_get('post_max_size') ?: '—';
    return match ($errorCode) {
        UPLOAD_ERR_INI_SIZE => "文件大小超过服务器 upload_max_filesize 限制（当前约 {$uploadMax}），请压缩后再试或调大限制。",
        UPLOAD_ERR_FORM_SIZE => '文件大小超过表单允许的最大值。',
        UPLOAD_ERR_PARTIAL => '文件仅上传了一部分，请重试。',
        UPLOAD_ERR_NO_FILE => "未收到文件，可能请求体超过 post_max_size（当前约 {$postMax}）或网络中断。",
        UPLOAD_ERR_NO_TMP_DIR => '服务器临时目录不存在，请联系管理员检查 upload_tmp_dir 权限。',
        UPLOAD_ERR_CANT_WRITE => '服务器无法写入文件，请联系管理员检查磁盘权限/配额。',
        UPLOAD_ERR_EXTENSION => '上传被扩展中断，请联系管理员确认配置。',
        default => '上传失败，请重试。',
    };
}

// Public download by token
if (isset($_GET['token'])) {
    $token = trim($_GET['token']);
    if ($token === '') {
        error_response('无效的外链', 404);
    }
    $stmt = $mysqli->prepare('SELECT * FROM cloud_files WHERE share_token = ? AND is_public = 1 LIMIT 1');
    if (!$stmt) {
        error_response('无法读取文件记录');
    }
    $stmt->bind_param('s', $token);
    $stmt->execute();
    $result = $stmt->get_result();
    $file = $result->fetch_assoc() ?: null;
    $stmt->close();
    if (!$file) {
        error_response('外链已失效或文件不存在', 404);
    }
    stream_file_download($file, $storageDir);
}

// Admin-only actions below
$currentUser = require_admin($mysqli);

if ($method === 'GET' && isset($_GET['id'], $_GET['download'])) {
    $id = (int) $_GET['id'];
    $file = fetch_file_by_id($mysqli, $id);
    if (!$file || (int) $file['user_id'] !== (int) $currentUser['id']) {
        error_response('文件不存在或无权限', 404);
    }
    stream_file_download($file, $storageDir);
}

switch ($method) {
    case 'GET':
        $stmt = $mysqli->prepare('SELECT * FROM cloud_files WHERE user_id = ? ORDER BY created_at DESC');
        if (!$stmt) {
            error_response('无法读取文件列表');
        }
        $stmt->bind_param('i', $currentUser['id']);
        $stmt->execute();
        $result = $stmt->get_result();
        $files = [];
        while ($row = $result->fetch_assoc()) {
            $files[] = file_payload($row);
        }
        $stmt->close();
        json_response(['files' => $files]);
        break;

    case 'POST':
        if (empty($_FILES) || empty($_FILES['file'])) {
            $postMax = ini_get('post_max_size') ?: '服务器限制未知';
            error_response("未收到文件，可能超过 post_max_size（当前约 {$postMax}）或请求格式异常");
        }
        $file = $_FILES['file'];
        if (!is_dir($storageDir) && !@mkdir($storageDir, 0775, true) && !is_dir($storageDir)) {
            error_response('无法创建文件目录');
        }
        if (!is_writable($storageDir)) {
            error_response('文件目录不可写，请检查权限');
        }
        if ($file['error'] !== UPLOAD_ERR_OK) {
            error_response(upload_error_text((int) $file['error']));
        }
        $maxSize = 200 * 1024 * 1024; // 200MB
        if ((int) $file['size'] > $maxSize) {
            error_response('文件过大，单个文件不超过 200MB');
        }
        $originalName = basename($file['name']);
        $ext = pathinfo($originalName, PATHINFO_EXTENSION);
        $storedName = bin2hex(random_bytes(16)) . ($ext ? '.' . $ext : '');
        $mimeType = $file['type'] ?: (function_exists('mime_content_type') ? mime_content_type($file['tmp_name']) : 'application/octet-stream');
        $sizeBytes = (int) $file['size'];
        $shareToken = bin2hex(random_bytes(16));
        $targetPath = $storageDir . '/' . $storedName;

        if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
            error_response('保存文件失败');
        }

        $stmt = $mysqli->prepare('INSERT INTO cloud_files (user_id, original_name, stored_name, mime_type, size_bytes, is_public, share_token) VALUES (?, ?, ?, ?, ?, 0, ?)');
        if (!$stmt) {
            @unlink($targetPath);
            error_response('无法写入文件记录');
        }
        $stmt->bind_param('isssis', $currentUser['id'], $originalName, $storedName, $mimeType, $sizeBytes, $shareToken);
        $stmt->execute();
        $newId = $stmt->insert_id;
        $stmt->close();

        $created = fetch_file_by_id($mysqli, $newId);
        json_response(['file' => file_payload($created)], 201);
        break;

    case 'PATCH':
        $input = get_json_input();
        $id = isset($input['id']) ? (int) $input['id'] : 0;
        $isPublic = isset($input['is_public']) && $input['is_public'] ? 1 : 0;
        if ($id <= 0) {
            error_response('缺少文件ID');
        }
        $file = fetch_file_by_id($mysqli, $id);
        if (!$file || (int) $file['user_id'] !== (int) $currentUser['id']) {
            error_response('文件不存在或无权限', 404);
        }
        if (empty($file['share_token'])) {
            $file['share_token'] = bin2hex(random_bytes(16));
        }
        $stmt = $mysqli->prepare('UPDATE cloud_files SET is_public = ?, share_token = ? WHERE id = ?');
        if (!$stmt) {
            error_response('无法更新分享状态');
        }
        $stmt->bind_param('isi', $isPublic, $file['share_token'], $id);
        $stmt->execute();
        $stmt->close();
        $updated = fetch_file_by_id($mysqli, $id);
        json_response(['file' => file_payload($updated)]);
        break;

    case 'DELETE':
        $input = get_json_input();
        $id = isset($input['id']) ? (int) $input['id'] : 0;
        if ($id <= 0) {
            error_response('缺少文件ID');
        }
        $file = fetch_file_by_id($mysqli, $id);
        if (!$file || (int) $file['user_id'] !== (int) $currentUser['id']) {
            error_response('文件不存在或无权限', 404);
        }
        $path = $storageDir . '/' . $file['stored_name'];
        $stmt = $mysqli->prepare('DELETE FROM cloud_files WHERE id = ?');
        if (!$stmt) {
            error_response('无法删除文件记录');
        }
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $stmt->close();
        if (is_file($path)) {
            @unlink($path);
        }
        json_response(['success' => true]);
        break;

    default:
        error_response('不支持的请求方法', 405);
}
