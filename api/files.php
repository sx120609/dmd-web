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
ensure_teacher_role_enum($mysqli);
$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
$jsonInput = get_json_input();
if ($method === 'POST') {
    $override = strtoupper(
        $_POST['_method'] ?? $_GET['_method'] ?? $jsonInput['_method'] ?? ($_SERVER['HTTP_X_HTTP_METHOD_OVERRIDE'] ?? '')
    );
    if (in_array($override, ['PATCH', 'DELETE'], true)) {
        $method = $override;
    }
}

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

function resolve_mime_type(array $file, string $path): string
{
    if (!empty($file['mime_type'])) {
        return $file['mime_type'];
    }
    if (is_file($path)) {
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        if ($finfo) {
            $detected = finfo_file($finfo, $path);
            finfo_close($finfo);
            if ($detected) {
                return $detected;
            }
        }
    }
    $ext = strtolower(pathinfo($file['original_name'] ?? $path, PATHINFO_EXTENSION));
    return match ($ext) {
        'mp4' => 'video/mp4',
        'webm' => 'video/webm',
        'mov' => 'video/quicktime',
        'm3u8' => 'application/vnd.apple.mpegurl',
        'mp3' => 'audio/mpeg',
        'wav' => 'audio/wav',
        'ogg', 'ogv' => 'video/ogg',
        'mkv' => 'video/x-matroska',
        'avi' => 'video/x-msvideo',
        default => 'application/octet-stream',
    };
}

function stream_file_download(array $file, string $storageDir): void
{
    $storedName = $file['stored_name'] ?? '';
    if ($storedName === '') {
        error_response('文件记录损坏', 500);
    }

    $realPath = rtrim($storageDir, '/').'/'.$storedName;
    if (!is_file($realPath)) {
        error_response('文件已不存在', 404);
    }

    // 解析应用根路径，支持反代前缀（可在 config.php 的 storage.public_base_path 覆盖）
    $basePathOverride = $config['storage']['public_base_path'] ?? '';
    $forwardedPrefix = $_SERVER['HTTP_X_FORWARDED_PREFIX'] ?? '';
    if (is_string($basePathOverride) && trim($basePathOverride) !== '') {
        $appBasePath = '/' . trim($basePathOverride, '/');
    } else {
        if (is_string($forwardedPrefix) && trim($forwardedPrefix) !== '') {
            $appBasePath = '/' . trim($forwardedPrefix, '/');
        } else {
            $scriptPath = $_SERVER['SCRIPT_NAME'] ?? '';
            $apiDir = rtrim(str_replace('\\', '/', dirname($scriptPath)), '/');
            $appBasePath = rtrim(dirname($apiDir), '/'); // 去掉 /api 层
            if ($appBasePath === '.' || $appBasePath === '') {
                // 保底以 /rarelight 作为前缀，避免被反代剥掉导致 /uploads 开头
                $appBasePath = '/rarelight';
            }
        }
    }
    // 直接跳转到静态文件，Range/缓存交给 nginx 或前置静态服务
    $publicPrefix = $config['storage']['public_prefix'] ?? '/uploads/files';
    $internalPrefix = $config['storage']['nginx_internal_prefix'] ?? null; // 可选：/protected/files

    $publicUrl = ($appBasePath ? $appBasePath : '') . '/' . ltrim($publicPrefix, '/');
    $publicUrl = preg_replace('~/{2,}~', '/', rtrim($publicUrl, '/'));
    $publicUrl .= '/' . rawurlencode($storedName);

    if (is_string($internalPrefix) && trim($internalPrefix) !== '') {
        // 优先使用内部转发避免多次请求
        $internalUrl = '/' . ltrim($internalPrefix, '/');
        $internalUrl = preg_replace('~/{2,}~', '/', rtrim($internalUrl, '/'));
        $internalUrl .= '/' . rawurlencode($storedName);
        header('X-Accel-Redirect: ' . $internalUrl);
        header('X-Accel-Buffering: no');
        exit;
    }

    // 使用相对路径跳转，适配 organic 的反代域名
    header('Location: ' . $publicUrl, true, 302);
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
$currentUser = require_admin_or_teacher($mysqli);

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
        $page = isset($_GET['page']) ? (int) $_GET['page'] : 0;
        $perPage = isset($_GET['per_page']) ? (int) $_GET['per_page'] : 0;
        $perPage = $perPage > 0 ? min($perPage, 100) : 0;

        $baseQuery = 'FROM cloud_files';
        $where = '';
        $params = [];
        $types = '';
        if (!in_array($currentUser['role'], ['admin', 'teacher'], true)) {
            $where = ' WHERE user_id = ?';
            $types = 'i';
            $params[] = $currentUser['id'];
        }

        if ($page > 0 && $perPage > 0) {
            $offset = ($page - 1) * $perPage;
            $stmt = $mysqli->prepare("SELECT * {$baseQuery}{$where} ORDER BY created_at DESC LIMIT ? OFFSET ?");
            if (!$stmt) {
                error_response('无法读取文件列表');
            }
            $bindTypes = $types . 'ii';
            $stmt->bind_param($bindTypes, ...array_merge($params, [$perPage, $offset]));
            $stmt->execute();
            $result = $stmt->get_result();
            $files = [];
            while ($row = $result->fetch_assoc()) {
                $files[] = file_payload($row);
            }
            $stmt->close();

            $countStmt = $mysqli->prepare("SELECT COUNT(*) AS total {$baseQuery}{$where}");
            if (!$countStmt) {
                error_response('无法统计文件数量');
            }
            if ($types !== '') {
                $countStmt->bind_param($types, ...$params);
            }
            $countStmt->execute();
            $countResult = $countStmt->get_result()->fetch_assoc();
            $countStmt->close();
            $total = (int) ($countResult['total'] ?? 0);
            $totalPages = $perPage > 0 ? (int) ceil($total / $perPage) : 1;
            if ($totalPages < 1) {
                $totalPages = 1;
            }
            json_response([
                'files' => $files,
                'pagination' => [
                    'page' => $page,
                    'per_page' => $perPage,
                    'total' => $total,
                    'total_pages' => $totalPages
                ]
            ]);
            break;
        }

        $stmt = $mysqli->prepare("SELECT * {$baseQuery}{$where} ORDER BY created_at DESC");
        if (!$stmt) {
            error_response('无法读取文件列表');
        }
        if ($types !== '') {
            $stmt->bind_param($types, ...$params);
        }
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
        // DELETE fallback via POST {action: delete, id: ...}
        $input = $jsonInput ?: get_json_input();
        if (($input['action'] ?? '') === 'delete') {
            $deleteId = isset($input['id']) ? (int) $input['id'] : 0;
            if ($deleteId <= 0) {
                error_response('缺少文件ID');
            }
            $file = fetch_file_by_id($mysqli, $deleteId);
            if (!$file) {
                error_response('文件不存在', 404);
            }
            if (!in_array($currentUser['role'], ['admin', 'teacher'], true) && (int) $file['user_id'] !== (int) $currentUser['id']) {
                error_response('无权限删除该文件', 403);
            }
            $path = $storageDir . '/' . $file['stored_name'];
            $stmtDel = $mysqli->prepare('DELETE FROM cloud_files WHERE id = ?');
            if (!$stmtDel) {
                error_response('无法删除文件记录');
            }
            $stmtDel->bind_param('i', $deleteId);
            $stmtDel->execute();
            $stmtDel->close();
            if (is_file($path)) {
                @unlink($path);
            }
            json_response(['success' => true]);
        }
        if (empty($_FILES) || empty($_FILES['file'])) {
            $postMax = ini_get('post_max_size') ?: '服务器限制未知';
            error_response("未收到文件，可能超过 post_max_size（当前约 {$postMax}）或请求格式异常");
        }
        if (!is_dir($storageDir) && !@mkdir($storageDir, 0775, true) && !is_dir($storageDir)) {
            error_response('无法创建文件目录');
        }
        if (!is_writable($storageDir)) {
            error_response('文件目录不可写，请检查权限');
        }
        $maxSize = 2 * 1024 * 1024 * 1024; // 2GB
        $allowedExt = ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'txt', 'csv', 'mp4', 'mov', 'avi', 'mkv', 'mp3', 'wav', 'png', 'jpg', 'jpeg', 'gif', 'webp', 'zip'];

        $files = $_FILES['file'];
        $multi = is_array($files['name']);
        $count = $multi ? count($files['name']) : 1;
        $createdEntries = [];

        for ($i = 0; $i < $count; $i++) {
            $error = $multi ? (int) $files['error'][$i] : (int) $files['error'];
            if ($error !== UPLOAD_ERR_OK) {
                error_response(upload_error_text($error));
            }
            $name = $multi ? $files['name'][$i] : $files['name'];
            $tmpName = $multi ? $files['tmp_name'][$i] : $files['tmp_name'];
            $size = $multi ? (int) $files['size'][$i] : (int) $files['size'];
            if ($size > $maxSize) {
                error_response('文件过大，单个文件不超过 2GB');
            }
            $originalName = basename($name);
            $ext = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
            $storedName = bin2hex(random_bytes(16)) . ($ext ? '.' . $ext : '');
            $mimeType = ($multi ? $files['type'][$i] : $files['type']) ?: (function_exists('mime_content_type') ? mime_content_type($tmpName) : 'application/octet-stream');
            $sizeBytes = $size;
            $shareToken = bin2hex(random_bytes(16));
            $targetPath = $storageDir . '/' . $storedName;
            if ($ext && !in_array($ext, $allowedExt, true)) {
                error_response('不支持的文件类型');
            }
            if (!move_uploaded_file($tmpName, $targetPath)) {
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
            $createdEntries[] = file_payload(fetch_file_by_id($mysqli, $newId));
        }
        json_response(['files' => $createdEntries], 201);
        break;

    case 'PATCH':
        $input = $jsonInput ?: get_json_input();
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
        $input = $jsonInput ?: ($_REQUEST ?: get_json_input());
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
