<?php
require __DIR__ . '/bootstrap.php';
ensure_cloud_files_table($mysqli);
ensure_teacher_role_enum($mysqli);

$method = $_SERVER['REQUEST_METHOD'] ?? 'POST';
$jsonInput = get_json_input();
$currentUser = require_admin_or_teacher($mysqli);

$configuredStorage = $config['storage']['cloud_dir'] ?? null;
if (is_string($configuredStorage) && trim($configuredStorage) !== '') {
    $storageDir = $configuredStorage;
    $isAbsolute = ($storageDir[0] ?? '') === '/' || preg_match('~^[A-Za-z]:[\\\\/]~', $storageDir);
    if (!$isAbsolute) {
        $storageDir = dirname(__DIR__) . '/' . ltrim($storageDir, '/');
    }
} else {
    $storageDir = dirname(__DIR__) . '/uploads/files';
}

$chunkBaseDir = rtrim($storageDir, '/') . '/chunks';
$maxChunkSize = 150 * 1024 * 1024; // 150MB，需小于 Nginx/PHP 限制

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

function ensure_dir(string $dir): void
{
    $parent = dirname($dir);
    if (!is_dir($dir)) {
        if (!is_dir($parent)) {
            @mkdir($parent, 0775, true);
        }
        if (!@mkdir($dir, 0775, true) && !is_dir($dir)) {
            error_response('无法创建目录：' . $dir, 500);
        }
    }
    if (!is_writable($dir)) {
        error_response('目录不可写：' . $dir, 500);
    }
}

function sanitize_upload_id(string $id): string
{
    return preg_replace('~[^A-Za-z0-9._-]~', '', $id);
}

function list_uploaded_chunks(string $dir): array
{
    if (!is_dir($dir)) {
        return [];
    }
    $files = scandir($dir) ?: [];
    $chunks = [];
    foreach ($files as $file) {
        if (preg_match('/^(\\d+)\\.part$/', $file, $m)) {
            $chunks[] = (int) $m[1];
        }
    }
    sort($chunks);
    return $chunks;
}

function merge_chunks(string $chunkDir, int $totalChunks, string $targetPath, int $expectedSize): void
{
    $out = fopen($targetPath, 'wb');
    if ($out === false) {
        error_response('无法创建合并文件', 500);
    }
    for ($i = 0; $i < $totalChunks; $i++) {
        $chunkPath = $chunkDir . '/' . $i . '.part';
        if (!is_file($chunkPath)) {
            fclose($out);
            error_response("缺少分片 {$i}", 400);
        }
        $in = fopen($chunkPath, 'rb');
        if ($in === false) {
            fclose($out);
            error_response("无法读取分片 {$i}", 500);
        }
        stream_copy_to_stream($in, $out);
        fclose($in);
    }
    fflush($out);
    fclose($out);
    clearstatcache(true, $targetPath);
    $finalSize = @filesize($targetPath);
    if ($expectedSize > 0 && $finalSize !== $expectedSize) {
        @unlink($targetPath);
        error_response('合并后文件大小不一致', 500);
    }
}

function file_payload(array $row, string $endpoint): array
{
    return [
        'id' => (int) $row['id'],
        'original_name' => $row['original_name'],
        'mime_type' => $row['mime_type'],
        'size_bytes' => (int) $row['size_bytes'],
        'is_public' => (bool) $row['is_public'],
        'share_token' => $row['share_token'],
        'created_at' => $row['created_at'],
        'share_url' => $endpoint . '?token=' . $row['share_token'],
        'download_url' => $endpoint . '?id=' . $row['id'] . '&download=1'
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

function meta_path(string $chunkDir): string
{
    return rtrim($chunkDir, '/').'/._meta.json';
}

function read_meta(string $chunkDir): array
{
    $metaFile = meta_path($chunkDir);
    if (!is_file($metaFile)) {
        return [];
    }
    $json = @file_get_contents($metaFile);
    $data = json_decode((string) $json, true);
    return is_array($data) ? $data : [];
}

function write_meta(string $chunkDir, array $meta): void
{
    $metaFile = meta_path($chunkDir);
    @file_put_contents($metaFile, json_encode($meta, JSON_UNESCAPED_UNICODE));
}

$cleanup_dir = function (string $dir): void {
    if (!is_dir($dir)) {
        return;
    }
    $files = glob($dir . '/*');
    if (is_array($files)) {
        foreach ($files as $file) {
            @unlink($file);
        }
    }
    @rmdir($dir);
};

$action = $_REQUEST['action'] ?? ($jsonInput['action'] ?? '');
if ($method === 'POST' && (isset($_REQUEST['_method']) || isset($jsonInput['_method']))) {
    $override = strtoupper((string) ($_REQUEST['_method'] ?? $jsonInput['_method'] ?? ''));
    if (in_array($override, ['PATCH', 'DELETE'], true)) {
        $method = $override;
    }
}

if ($method !== 'POST') {
    error_response('仅支持 POST', 405);
}

if (!in_array($action, ['init', 'chunk', 'complete', 'status'], true)) {
    error_response('未知的分片动作', 400);
}

$uploadId = isset($_REQUEST['upload_id']) ? sanitize_upload_id((string) $_REQUEST['upload_id']) : sanitize_upload_id((string) ($jsonInput['upload_id'] ?? ''));
$originalName = isset($_REQUEST['filename']) ? basename((string) $_REQUEST['filename']) : basename((string) ($jsonInput['filename'] ?? ''));
$sizeBytes = isset($_REQUEST['size_bytes']) ? (int) $_REQUEST['size_bytes'] : (int) ($jsonInput['size_bytes'] ?? 0);
$mimeType = isset($_REQUEST['mime_type']) ? (string) $_REQUEST['mime_type'] : (string) ($jsonInput['mime_type'] ?? '');
$chunkSizeClient = isset($_REQUEST['chunk_size']) ? (int) $_REQUEST['chunk_size'] : (int) ($jsonInput['chunk_size'] ?? 0);

ensure_dir($chunkBaseDir);

if ($action === 'init') {
    if ($uploadId === '') {
        $uploadId = bin2hex(random_bytes(12));
    }
    $chunkDir = $chunkBaseDir . '/' . $uploadId;
    ensure_dir($chunkDir);
    $meta = read_meta($chunkDir);
    $incomingSize = $sizeBytes;
    $incomingChunkSize = $chunkSizeClient > 0 ? $chunkSizeClient : $maxChunkSize;
    $shouldReset = false;
    if (!empty($meta)) {
        $metaSize = (int) ($meta['size_bytes'] ?? 0);
        $metaChunk = (int) ($meta['chunk_size'] ?? 0);
        if (($incomingSize > 0 && $metaSize > 0 && $metaSize !== $incomingSize) ||
            ($incomingChunkSize > 0 && $metaChunk > 0 && $metaChunk !== $incomingChunkSize)) {
            $shouldReset = true;
        }
    }
    if ($shouldReset) {
        ($cleanup_dir)($chunkDir);
        ensure_dir($chunkDir);
        $meta = [];
    }
    $meta = [
        'size_bytes' => $incomingSize,
        'chunk_size' => $incomingChunkSize,
        'filename' => $originalName,
        'mime_type' => $mimeType
    ];
    write_meta($chunkDir, $meta);
    $uploaded = list_uploaded_chunks($chunkDir);
    json_response([
        'upload_id' => $uploadId,
        'uploaded_chunks' => $uploaded,
        'max_chunk_size' => $maxChunkSize
    ]);
}

if ($action === 'status') {
    if ($uploadId === '') {
        error_response('缺少 upload_id');
    }
    $chunkDir = $chunkBaseDir . '/' . $uploadId;
    $uploaded = list_uploaded_chunks($chunkDir);
    $meta = read_meta($chunkDir);
    json_response([
        'upload_id' => $uploadId,
        'uploaded_chunks' => $uploaded,
        'meta' => $meta
    ]);
}

if ($action === 'chunk') {
    if ($uploadId === '') {
        error_response('缺少 upload_id');
    }
    $index = isset($_GET['index']) ? (int) $_GET['index'] : (isset($_POST['index']) ? (int) $_POST['index'] : -1);
    if ($index < 0) {
        error_response('缺少分片序号');
    }
    $chunkDir = $chunkBaseDir . '/' . $uploadId;
    ensure_dir($chunkDir);
    $meta = read_meta($chunkDir);
    $declaredChunkSize = (int) ($meta['chunk_size'] ?? $chunkSizeClient);
    $contentLength = isset($_SERVER['CONTENT_LENGTH']) ? (int) $_SERVER['CONTENT_LENGTH'] : 0;
    $expectedChunkSize = isset($_GET['size']) ? (int) $_GET['size'] : (isset($_REQUEST['size']) ? (int) $_REQUEST['size'] : 0);
    if ($expectedChunkSize <= 0) {
        $expectedChunkSize = $declaredChunkSize > 0 ? $declaredChunkSize : $contentLength;
    }
    $contentLength = $contentLength > 0 ? $contentLength : $expectedChunkSize;
    if ($contentLength > 0 && $contentLength > $maxChunkSize) {
        error_response('单个分片过大', 413);
    }
    $chunkPath = $chunkDir . '/' . $index . '.part';
    $fp = fopen($chunkPath, 'wb');
    if ($fp === false) {
        error_response('无法写入分片', 500);
    }
    $input = fopen('php://input', 'rb');
    if ($input === false) {
        fclose($fp);
        error_response('无法读取上传内容', 500);
    }
    stream_copy_to_stream($input, $fp);
    fclose($fp);
    fclose($input);
    clearstatcache(true, $chunkPath);
    $written = @filesize($chunkPath);
    if ($expectedChunkSize > 0 && $written !== $expectedChunkSize) {
        @unlink($chunkPath);
        error_response('分片大小不一致', 400);
    }
    json_response(['success' => true, 'index' => $index]);
}

if ($action === 'complete') {
    if ($uploadId === '') {
        error_response('缺少 upload_id');
    }
    if ($originalName === '' || $sizeBytes <= 0) {
        error_response('缺少文件名或大小');
    }
    $totalChunks = isset($_REQUEST['total_chunks']) ? (int) $_REQUEST['total_chunks'] : (int) ($jsonInput['total_chunks'] ?? 0);
    if ($totalChunks <= 0) {
        error_response('缺少分片数量');
    }
    $chunkDir = $chunkBaseDir . '/' . $uploadId;
    if (!is_dir($chunkDir)) {
        error_response('分片不存在', 404);
    }
    $available = list_uploaded_chunks($chunkDir);
    if (count($available) < $totalChunks) {
        error_response('分片未传完', 400);
    }
    $meta = read_meta($chunkDir);
    if (!empty($meta)) {
        $metaSize = (int) ($meta['size_bytes'] ?? 0);
        if ($metaSize > 0) {
            $sizeBytes = $metaSize;
        }
    }
    // 合并前再校验总大小
    $sumSize = 0;
    foreach ($available as $idx) {
        $p = $chunkDir . '/' . $idx . '.part';
        $sz = @filesize($p);
        if (!is_int($sz) || $sz <= 0) {
            error_response("分片 {$idx} 大小异常", 400);
        }
        $sumSize += $sz;
    }
    if ($sizeBytes > 0 && $sumSize !== $sizeBytes) {
        error_response('分片总大小不一致', 400);
    }

    ensure_storage_dir($storageDir);
    $ext = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
    $storedName = bin2hex(random_bytes(16)) . ($ext ? '.' . $ext : '');
    $targetPath = $storageDir . '/' . $storedName;
    merge_chunks($chunkDir, $totalChunks, $targetPath, $sizeBytes);

    $shareToken = bin2hex(random_bytes(16));
    $stmt = $mysqli->prepare('INSERT INTO cloud_files (user_id, original_name, stored_name, mime_type, size_bytes, is_public, share_token) VALUES (?, ?, ?, ?, ?, 0, ?)');
    if (!$stmt) {
        @unlink($targetPath);
        error_response('无法写入文件记录');
    }
    $stmt->bind_param('isssis', $currentUser['id'], $originalName, $storedName, $mimeType, $sizeBytes, $shareToken);
    $stmt->execute();
    $newId = $stmt->insert_id;
    $stmt->close();

    // 清理分片
    $files = glob($chunkDir . '/*');
    if (is_array($files)) {
        foreach ($files as $file) {
            @unlink($file);
        }
    }
    @rmdir($chunkDir);

    $file = fetch_file_by_id($mysqli, (int) $newId);
    $endpoint = dirname($_SERVER['SCRIPT_NAME']);
    $endpoint = rtrim($endpoint, '/\\') . '/files.php';
    $payload = file_payload($file, $endpoint);
    json_response(['file' => $payload], 201);
}
