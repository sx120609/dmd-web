<?php
require __DIR__ . '/bootstrap.php';
ensure_teacher_role_enum($mysqli);

$storageConfig = (isset($config['storage']) && is_array($config['storage'])) ? $config['storage'] : [];
$imageBedConfig = (isset($config['image_bed']) && is_array($config['image_bed'])) ? $config['image_bed'] : [];

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
if ($method !== 'POST') {
    error_response('仅支持 POST 上传', 405);
}

function ensure_storage_dir(string $dir): void
{
    $parent = dirname($dir);
    if (!is_dir($dir)) {
        if ((!is_dir($parent) || !is_writable($parent)) && !is_writable($parent)) {
            error_response('图片目录不可写：' . $dir, 500);
        }
        if (!@mkdir($dir, 0775, true) && !is_dir($dir)) {
            error_response('无法创建图片目录：' . $dir, 500);
        }
    }
    if (!is_writable($dir)) {
        error_response('图片目录不可写：' . $dir, 500);
    }
}

function upload_error_text(int $errorCode): string
{
    $uploadMax = ini_get('upload_max_filesize') ?: '—';
    $postMax = ini_get('post_max_size') ?: '—';
    switch ($errorCode) {
        case UPLOAD_ERR_INI_SIZE:
            return "图片大小超过服务器 upload_max_filesize 限制（当前约 {$uploadMax}）。";
        case UPLOAD_ERR_FORM_SIZE:
            return '图片大小超过表单允许的最大值。';
        case UPLOAD_ERR_PARTIAL:
            return '图片仅上传了一部分，请重试。';
        case UPLOAD_ERR_NO_FILE:
            return "未收到图片，可能请求体超过 post_max_size（当前约 {$postMax}）。";
        case UPLOAD_ERR_NO_TMP_DIR:
            return '服务器临时目录不存在，请联系管理员。';
        case UPLOAD_ERR_CANT_WRITE:
            return '服务器无法写入图片，请联系管理员检查权限。';
        case UPLOAD_ERR_EXTENSION:
            return '图片上传被扩展中断，请联系管理员。';
        default:
            return '上传失败，请重试。';
    }
}

function app_base_path(array $storageConfig): string
{
    $basePathOverride = $storageConfig['public_base_path'] ?? '';
    $forwardedPrefix = $_SERVER['HTTP_X_FORWARDED_PREFIX'] ?? '';
    if (is_string($basePathOverride) && trim($basePathOverride) !== '') {
        return '/' . trim($basePathOverride, '/');
    }
    if (is_string($forwardedPrefix) && trim($forwardedPrefix) !== '') {
        return '/' . trim($forwardedPrefix, '/');
    }
    $scriptPath = $_SERVER['SCRIPT_NAME'] ?? '';
    $apiDir = rtrim(str_replace('\\', '/', dirname($scriptPath)), '/');
    $appBasePath = rtrim(dirname($apiDir), '/');
    if ($appBasePath === '.' || $appBasePath === '') {
        return '/rarelight';
    }
    return $appBasePath;
}

function build_public_urls(array $storageConfig, string $storedName): array
{
    $appBasePath = app_base_path($storageConfig);
    $publicPrefix = $storageConfig['image_public_prefix'] ?? '/uploads/images';
    $relativeUrl = ($appBasePath ? $appBasePath : '') . '/' . ltrim($publicPrefix, '/');
    $relativeUrl = preg_replace('~/{2,}~', '/', rtrim($relativeUrl, '/'));
    $relativeUrl .= '/' . rawurlencode($storedName);

    $forwardedProto = trim((string) ($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? ''));
    $scheme = $forwardedProto !== '' ? strtolower(trim(explode(',', $forwardedProto)[0])) : '';
    if ($scheme !== 'http' && $scheme !== 'https') {
        $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    }

    $forwardedHost = trim((string) ($_SERVER['HTTP_X_FORWARDED_HOST'] ?? ''));
    $host = $forwardedHost !== '' ? trim(explode(',', $forwardedHost)[0]) : trim((string) ($_SERVER['HTTP_HOST'] ?? ''));
    $absoluteUrl = $host !== '' ? ($scheme . '://' . $host . $relativeUrl) : $relativeUrl;

    return [
        'relative' => $relativeUrl,
        'absolute' => $absoluteUrl
    ];
}

function has_valid_image_token(string $expectedToken): bool
{
    if ($expectedToken === '') {
        return false;
    }
    $provided = '';
    if (!empty($_SERVER['HTTP_X_IMAGE_TOKEN'])) {
        $provided = trim((string) $_SERVER['HTTP_X_IMAGE_TOKEN']);
    } elseif (!empty($_SERVER['HTTP_X_API_TOKEN'])) {
        $provided = trim((string) $_SERVER['HTTP_X_API_TOKEN']);
    } elseif (isset($_POST['token'])) {
        $provided = trim((string) $_POST['token']);
    } elseif (isset($_GET['token'])) {
        $provided = trim((string) $_GET['token']);
    }
    if ($provided === '') {
        return false;
    }
    return hash_equals($expectedToken, $provided);
}

$imageToken = trim((string) ($imageBedConfig['api_token'] ?? ''));
if (!has_valid_image_token($imageToken)) {
    require_admin_or_teacher($mysqli);
}

$configuredStorage = $storageConfig['image_dir'] ?? null;
if (is_string($configuredStorage) && trim($configuredStorage) !== '') {
    $storageDir = $configuredStorage;
    $isAbsolute = ($storageDir[0] ?? '') === '/' || preg_match('~^[A-Za-z]:[\\\\/]~', $storageDir);
    if (!$isAbsolute) {
        $storageDir = $rootDir . '/' . ltrim($storageDir, '/');
    }
} else {
    $storageDir = $rootDir . '/uploads/images';
}
ensure_storage_dir($storageDir);

if (empty($_FILES)) {
    error_response('未收到图片文件');
}

$file = null;
if (!empty($_FILES['image'])) {
    $file = $_FILES['image'];
} elseif (!empty($_FILES['file'])) {
    $file = $_FILES['file'];
} else {
    foreach ($_FILES as $candidate) {
        if (is_array($candidate) && isset($candidate['tmp_name'])) {
            $file = $candidate;
            break;
        }
    }
}

if (!$file || !is_array($file)) {
    error_response('未找到可用图片文件');
}

if (is_array($file['name'] ?? null)) {
    error_response('图床接口仅支持单文件上传');
}

$error = (int) ($file['error'] ?? UPLOAD_ERR_NO_FILE);
if ($error !== UPLOAD_ERR_OK) {
    error_response(upload_error_text($error));
}

$maxSize = (int) ($imageBedConfig['max_size_bytes'] ?? (20 * 1024 * 1024));
$size = (int) ($file['size'] ?? 0);
if ($size <= 0) {
    error_response('无效图片大小');
}
if ($size > $maxSize) {
    error_response('图片过大，超过限制');
}

$tmpName = (string) ($file['tmp_name'] ?? '');
if ($tmpName === '' || !is_uploaded_file($tmpName)) {
    error_response('上传文件无效');
}

$imageInfo = @getimagesize($tmpName);
if ($imageInfo === false) {
    error_response('仅支持图片文件');
}

$finfo = finfo_open(FILEINFO_MIME_TYPE);
$detectedMime = $finfo ? (string) finfo_file($finfo, $tmpName) : '';
if ($finfo) {
    finfo_close($finfo);
}

if (strpos($detectedMime, 'image/') !== 0) {
    error_response('仅支持图片文件');
}

$mimeToExt = [
    'image/jpeg' => 'jpg',
    'image/png' => 'png',
    'image/gif' => 'gif',
    'image/webp' => 'webp',
    'image/bmp' => 'bmp',
    'image/x-ms-bmp' => 'bmp',
    'image/tiff' => 'tif',
    'image/x-icon' => 'ico'
];
$ext = $mimeToExt[$detectedMime] ?? '';
if ($ext === '') {
    error_response('暂不支持该图片格式');
}

$storedName = bin2hex(random_bytes(16)) . '.' . $ext;
$targetPath = rtrim($storageDir, '/') . '/' . $storedName;
if (!move_uploaded_file($tmpName, $targetPath)) {
    error_response('保存图片失败');
}

$urls = build_public_urls($storageConfig, $storedName);
json_response([
    'url' => $urls['absolute'],
    'relative_url' => $urls['relative'],
    'mime_type' => $detectedMime,
    'size_bytes' => $size,
    'filename' => $storedName
], 201);
