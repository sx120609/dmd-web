<?php
require __DIR__ . '/bootstrap.php';
ensure_teacher_role_enum($mysqli);

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
$jsonInput = get_json_input();
$action = trim((string) ($_GET['action'] ?? $_POST['action'] ?? $jsonInput['action'] ?? 'config'));

$clouds = [
    'global' => [
        'label' => '国际版',
        'authority' => 'https://login.microsoftonline.com',
        'graph' => 'https://graph.microsoft.com/v1.0',
        'scope' => 'offline_access https://graph.microsoft.com/User.Read https://graph.microsoft.com/Files.ReadWrite.All https://graph.microsoft.com/Sites.ReadWrite.All',
    ],
    'china' => [
        'label' => '世纪互联',
        'authority' => 'https://login.chinacloudapi.cn',
        'graph' => 'https://microsoftgraph.chinacloudapi.cn/v1.0',
        'scope' => 'offline_access https://microsoftgraph.chinacloudapi.cn/User.Read https://microsoftgraph.chinacloudapi.cn/Files.ReadWrite.All https://microsoftgraph.chinacloudapi.cn/Sites.ReadWrite.All',
    ],
];

function od_demo_absolute_url(string $path): string
{
    $scheme = $_SERVER['HTTP_X_FORWARDED_PROTO'] ?? null;
    if (!$scheme) {
        $scheme = ((!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || (isset($_SERVER['SERVER_PORT']) && (int) $_SERVER['SERVER_PORT'] === 443)) ? 'https' : 'http';
    }
    $host = $_SERVER['HTTP_X_FORWARDED_HOST'] ?? ($_SERVER['HTTP_HOST'] ?? 'localhost');
    $prefix = trim((string) ($_SERVER['HTTP_X_FORWARDED_PREFIX'] ?? ''), '/');
    $normalizedPath = '/' . ltrim($path, '/');
    if ($prefix !== '' && !str_starts_with($normalizedPath, '/' . $prefix . '/')) {
        $normalizedPath = '/' . $prefix . $normalizedPath;
    }
    return $scheme . '://' . $host . preg_replace('~/{2,}~', '/', $normalizedPath);
}

function od_demo_callback_url(): string
{
    $candidates = [
        $_SERVER['HTTP_X_ORIGINAL_URI'] ?? '',
        $_SERVER['REQUEST_URI'] ?? '',
        $_SERVER['SCRIPT_NAME'] ?? '',
    ];

    foreach ($candidates as $candidate) {
        $path = parse_url((string) $candidate, PHP_URL_PATH);
        if (!is_string($path) || $path === '') {
            continue;
        }
        if (preg_match('~^(.*?/api/onedrive_demo)(?:\.php)?/?$~', $path, $m)) {
            return od_demo_absolute_url($m[1] . '.php?action=callback');
        }
    }

    $referer = $_SERVER['HTTP_REFERER'] ?? '';
    $refererPath = is_string($referer) ? parse_url($referer, PHP_URL_PATH) : '';
    if (is_string($refererPath) && $refererPath !== '') {
        $basePath = rtrim(dirname($refererPath), '/');
        if ($basePath === '' || $basePath === '.') {
            $basePath = '';
        }
        return od_demo_absolute_url($basePath . '/api/onedrive_demo.php?action=callback');
    }

    return od_demo_absolute_url('/api/onedrive_demo.php?action=callback');
}

function od_demo_config_for_cloud(array $config, array $clouds, string $cloud): array
{
    if (!isset($clouds[$cloud])) {
        error_response('未知的 OneDrive 云环境', 400);
    }
    $app = $config['onedrive_demo']['apps'][$cloud] ?? [];
    return array_merge($clouds[$cloud], [
        'cloud' => $cloud,
        'client_id' => trim((string) ($app['client_id'] ?? '')),
        'client_secret' => trim((string) ($app['client_secret'] ?? '')),
        'tenant' => trim((string) ($app['tenant'] ?? 'common')),
        'scope' => trim((string) ($app['scope'] ?? $clouds[$cloud]['scope'])),
    ]);
}

function od_demo_token_key(string $cloud): string
{
    return 'onedrive_demo_token_' . $cloud;
}

function od_demo_http(string $method, string $url, array $headers = [], $body = null): array
{
    if (!function_exists('curl_init')) {
        error_response('当前 PHP 未启用 cURL，无法调用 Microsoft Graph', 500);
    }
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HEADER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 45);
    if ($body !== null) {
        curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
    }
    if ($headers) {
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    }
    $raw = curl_exec($ch);
    if ($raw === false) {
        $err = curl_error($ch);
        curl_close($ch);
        error_response('HTTP 请求失败：' . $err, 502);
    }
    $status = (int) curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
    $headerSize = (int) curl_getinfo($ch, CURLINFO_HEADER_SIZE);
    $responseBody = substr($raw, $headerSize);
    curl_close($ch);
    $decoded = json_decode((string) $responseBody, true);
    return [
        'status' => $status,
        'body' => is_array($decoded) ? $decoded : $responseBody,
    ];
}

function od_demo_token_endpoint(array $cloudConfig): string
{
    $tenant = $cloudConfig['tenant'] !== '' ? $cloudConfig['tenant'] : 'common';
    return rtrim($cloudConfig['authority'], '/') . '/' . rawurlencode($tenant) . '/oauth2/v2.0/token';
}

function od_demo_authorize_endpoint(array $cloudConfig): string
{
    $tenant = $cloudConfig['tenant'] !== '' ? $cloudConfig['tenant'] : 'common';
    return rtrim($cloudConfig['authority'], '/') . '/' . rawurlencode($tenant) . '/oauth2/v2.0/authorize';
}

function od_demo_exchange_code(array $cloudConfig, string $code): array
{
    $body = http_build_query([
        'client_id' => $cloudConfig['client_id'],
        'client_secret' => $cloudConfig['client_secret'],
        'grant_type' => 'authorization_code',
        'code' => $code,
        'redirect_uri' => od_demo_callback_url(),
        'scope' => $cloudConfig['scope'],
    ]);
    $res = od_demo_http('POST', od_demo_token_endpoint($cloudConfig), ['Content-Type: application/x-www-form-urlencoded'], $body);
    if ($res['status'] < 200 || $res['status'] >= 300 || !is_array($res['body'])) {
        error_response('换取 token 失败：' . json_encode($res['body'], JSON_UNESCAPED_UNICODE), 502);
    }
    return $res['body'];
}

function od_demo_refresh_token(array $cloudConfig, array $token): array
{
    if (empty($token['refresh_token'])) {
        error_response('没有 refresh_token，请重新授权', 401);
    }
    $body = http_build_query([
        'client_id' => $cloudConfig['client_id'],
        'client_secret' => $cloudConfig['client_secret'],
        'grant_type' => 'refresh_token',
        'refresh_token' => $token['refresh_token'],
        'redirect_uri' => od_demo_callback_url(),
        'scope' => $cloudConfig['scope'],
    ]);
    $res = od_demo_http('POST', od_demo_token_endpoint($cloudConfig), ['Content-Type: application/x-www-form-urlencoded'], $body);
    if ($res['status'] < 200 || $res['status'] >= 300 || !is_array($res['body'])) {
        error_response('刷新 token 失败：' . json_encode($res['body'], JSON_UNESCAPED_UNICODE), 502);
    }
    return $res['body'];
}

function od_demo_store_token(string $cloud, array $token): void
{
    $expiresIn = (int) ($token['expires_in'] ?? 3600);
    $_SESSION[od_demo_token_key($cloud)] = [
        'access_token' => $token['access_token'] ?? '',
        'refresh_token' => $token['refresh_token'] ?? ($_SESSION[od_demo_token_key($cloud)]['refresh_token'] ?? ''),
        'expires_at' => time() + $expiresIn,
        'scope' => $token['scope'] ?? '',
        'token_type' => $token['token_type'] ?? 'Bearer',
    ];
}

function od_demo_access_token(array $config, array $clouds, string $cloud): array
{
    $cloudConfig = od_demo_config_for_cloud($config, $clouds, $cloud);
    $token = $_SESSION[od_demo_token_key($cloud)] ?? null;
    if (!is_array($token) || empty($token['access_token'])) {
        error_response('尚未完成 OneDrive 授权', 401);
    }
    if ((int) ($token['expires_at'] ?? 0) <= time() + 120) {
        $newToken = od_demo_refresh_token($cloudConfig, $token);
        od_demo_store_token($cloud, $newToken);
        $token = $_SESSION[od_demo_token_key($cloud)];
    }
    return [$cloudConfig, $token];
}

function od_demo_graph(array $cloudConfig, array $token, string $method, string $path, $body = null): array
{
    $url = str_starts_with($path, 'https://') ? $path : rtrim($cloudConfig['graph'], '/') . '/' . ltrim($path, '/');
    $headers = ['Authorization: Bearer ' . $token['access_token']];
    if ($body !== null) {
        $headers[] = 'Content-Type: application/json';
        $body = json_encode($body, JSON_UNESCAPED_UNICODE);
    }
    $res = od_demo_http($method, $url, $headers, $body);
    if ($res['status'] < 200 || $res['status'] >= 300) {
        error_response('Graph 请求失败：' . json_encode($res['body'], JSON_UNESCAPED_UNICODE), $res['status'] ?: 502);
    }
    return $res['body'];
}

function od_demo_graph_path(string $path): string
{
    $trimmed = trim($path, '/');
    if ($trimmed === '') {
        return '';
    }
    return implode('/', array_map('rawurlencode', explode('/', $trimmed)));
}

function od_demo_site_id_from_url(string $siteUrl): string
{
    $parts = parse_url($siteUrl);
    if (empty($parts['host'])) {
        error_response('SharePoint 站点地址无效', 400);
    }
    $path = trim((string) ($parts['path'] ?? ''), '/');
    return 'sites/' . $parts['host'] . ($path !== '' ? ':/' . od_demo_graph_path($path) : '');
}

if ($action === 'callback') {
    $state = (string) ($_GET['state'] ?? '');
    $code = (string) ($_GET['code'] ?? '');
    $stored = $_SESSION['onedrive_demo_oauth_state'] ?? null;
    if (!is_array($stored) || empty($stored['state']) || !hash_equals($stored['state'], $state)) {
        error_response('OAuth state 校验失败，请回到测试页重新授权', 400);
    }
    if ($code === '') {
        error_response('授权回调缺少 code', 400);
    }
    $cloud = $stored['cloud'];
    $cloudConfig = od_demo_config_for_cloud($config, $clouds, $cloud);
    $token = od_demo_exchange_code($cloudConfig, $code);
    od_demo_store_token($cloud, $token);
    unset($_SESSION['onedrive_demo_oauth_state']);
    header('Content-Type: text/html; charset=utf-8');
    echo '<!doctype html><meta charset="utf-8"><title>OneDrive 授权完成</title><body style="font-family:system-ui,sans-serif;padding:32px"><h2>授权完成</h2><p>可以关闭此页，回到 OneDrive/SharePoint Demo 测试。</p><script>if(window.opener){window.opener.postMessage({type:"onedrive-demo-auth-complete"},"*");}</script></body>';
    exit;
}

$cloud = trim((string) ($_GET['cloud'] ?? $_POST['cloud'] ?? $jsonInput['cloud'] ?? 'global'));

if ($action === 'config') {
    $user = current_user($mysqli);
    $cloudPayload = [];
    foreach (array_keys($clouds) as $cloudName) {
        $cloudConfig = od_demo_config_for_cloud($config, $clouds, $cloudName);
        $token = $_SESSION[od_demo_token_key($cloudName)] ?? null;
        $cloudPayload[$cloudName] = [
            'label' => $cloudConfig['label'],
            'graph' => $cloudConfig['graph'],
            'authority' => $cloudConfig['authority'],
            'configured' => $cloudConfig['client_id'] !== '' && $cloudConfig['client_secret'] !== '',
            'authorized' => is_array($token) && !empty($token['access_token']),
            'expires_at' => is_array($token) ? (int) ($token['expires_at'] ?? 0) : null,
            'scope' => $cloudConfig['scope'],
        ];
    }
    json_response([
        'callback_url' => od_demo_callback_url(),
        'clouds' => $cloudPayload,
        'logged_in' => (bool) $user,
        'user' => $user,
    ]);
}

$currentUser = require_admin_or_teacher($mysqli);
$cloudConfig = od_demo_config_for_cloud($config, $clouds, $cloud);

if ($action === 'auth_url') {
    if ($cloudConfig['client_id'] === '' || $cloudConfig['client_secret'] === '') {
        error_response('请先在 config.php 配置 onedrive_demo.apps.' . $cloud . ' 的 client_id/client_secret', 400);
    }
    $state = bin2hex(random_bytes(24));
    $_SESSION['onedrive_demo_oauth_state'] = [
        'state' => $state,
        'cloud' => $cloud,
        'user_id' => (int) $currentUser['id'],
    ];
    $authUrl = od_demo_authorize_endpoint($cloudConfig) . '?' . http_build_query([
        'client_id' => $cloudConfig['client_id'],
        'response_type' => 'code',
        'redirect_uri' => od_demo_callback_url(),
        'response_mode' => 'query',
        'scope' => $cloudConfig['scope'],
        'state' => $state,
        'prompt' => 'select_account',
    ]);
    json_response(['auth_url' => $authUrl, 'callback_url' => od_demo_callback_url()]);
}

if ($action === 'disconnect') {
    unset($_SESSION[od_demo_token_key($cloud)]);
    json_response(['ok' => true]);
}

[$cloudConfig, $token] = od_demo_access_token($config, $clouds, $cloud);

if ($action === 'token_status') {
    json_response([
        'authorized' => true,
        'cloud' => $cloud,
        'expires_at' => (int) ($token['expires_at'] ?? 0),
        'scope' => $token['scope'] ?? '',
    ]);
}

if ($action === 'me_drive') {
    json_response(od_demo_graph($cloudConfig, $token, 'GET', '/me/drive'));
}

if ($action === 'list') {
    $path = trim((string) ($_GET['path'] ?? $jsonInput['path'] ?? ''), '/');
    $siteId = trim((string) ($_GET['site_id'] ?? $jsonInput['site_id'] ?? ''));
    $apiPath = $siteId !== ''
        ? '/sites/' . rawurlencode($siteId) . '/drive/root' . ($path === '' ? '/children' : ':/' . od_demo_graph_path($path) . ':/children')
        : '/me/drive/root' . ($path === '' ? '/children' : ':/' . od_demo_graph_path($path) . ':/children');
    json_response(od_demo_graph($cloudConfig, $token, 'GET', $apiPath));
}

if ($action === 'site') {
    $siteUrl = trim((string) ($_GET['site_url'] ?? $jsonInput['site_url'] ?? ''));
    if ($siteUrl === '') {
        error_response('缺少 site_url', 400);
    }
    json_response(od_demo_graph($cloudConfig, $token, 'GET', '/' . od_demo_site_id_from_url($siteUrl)));
}

if ($action === 'upload_session') {
    if ($method !== 'POST') {
        error_response('创建上传会话仅支持 POST', 405);
    }
    $filename = basename(trim((string) ($jsonInput['filename'] ?? $_POST['filename'] ?? '')));
    if ($filename === '') {
        error_response('缺少 filename', 400);
    }
    $folder = trim((string) ($jsonInput['folder'] ?? $_POST['folder'] ?? ''), '/');
    $siteId = trim((string) ($jsonInput['site_id'] ?? $_POST['site_id'] ?? ''));
    $targetPath = od_demo_graph_path(trim($folder . '/' . $filename, '/'));
    $apiPath = $siteId !== ''
        ? '/sites/' . rawurlencode($siteId) . '/drive/root:/' . $targetPath . ':/createUploadSession'
        : '/me/drive/root:/' . $targetPath . ':/createUploadSession';
    json_response(od_demo_graph($cloudConfig, $token, 'POST', $apiPath, [
        'item' => [
            '@microsoft.graph.conflictBehavior' => 'rename',
            'name' => $filename,
        ],
    ]));
}

if ($action === 'simple_upload') {
    if ($method !== 'POST') {
        error_response('简单上传仅支持 POST', 405);
    }
    if (empty($_FILES['file']) || !is_uploaded_file($_FILES['file']['tmp_name'])) {
        error_response('缺少上传文件字段 file', 400);
    }
    if ((int) ($_FILES['file']['size'] ?? 0) > 4 * 1024 * 1024) {
        error_response('Graph 简单上传 demo 限制 4MB 以内，大文件请先测试创建上传会话', 400);
    }
    $filename = basename((string) ($_POST['filename'] ?? $_FILES['file']['name'] ?? 'demo.bin'));
    if ($filename === '') {
        $filename = 'demo.bin';
    }
    $folder = trim((string) ($_POST['folder'] ?? ''), '/');
    $siteId = trim((string) ($_POST['site_id'] ?? ''));
    $targetPath = od_demo_graph_path(trim($folder . '/' . $filename, '/'));
    $apiPath = $siteId !== ''
        ? '/sites/' . rawurlencode($siteId) . '/drive/root:/' . $targetPath . ':/content'
        : '/me/drive/root:/' . $targetPath . ':/content';
    $body = file_get_contents($_FILES['file']['tmp_name']);
    if ($body === false) {
        error_response('无法读取上传临时文件', 500);
    }
    $mime = $_FILES['file']['type'] ?: 'application/octet-stream';
    $res = od_demo_http('PUT', rtrim($cloudConfig['graph'], '/') . '/' . ltrim($apiPath, '/'), [
        'Authorization: Bearer ' . $token['access_token'],
        'Content-Type: ' . $mime,
    ], $body);
    if ($res['status'] < 200 || $res['status'] >= 300) {
        error_response('Graph 简单上传失败：' . json_encode($res['body'], JSON_UNESCAPED_UNICODE), $res['status'] ?: 502);
    }
    json_response($res['body']);
}

error_response('未知的 OneDrive demo 动作', 400);
