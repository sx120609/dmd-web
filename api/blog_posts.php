<?php
require __DIR__ . '/bootstrap.php';

$method = $_SERVER['REQUEST_METHOD'];
$jsonInput = get_json_input();
ensure_blog_posts_table($mysqli);

if ($method === 'POST') {
    $override = strtoupper(
        $_POST['_method'] ?? $_GET['_method'] ?? $jsonInput['_method'] ?? ($_SERVER['HTTP_X_HTTP_METHOD_OVERRIDE'] ?? '')
    );
    if (in_array($override, ['DELETE', 'PATCH', 'PUT'], true)) {
        $method = $override;
    }
}

if ($method === 'GET') {
    $postId = isset($_GET['id']) ? (int) $_GET['id'] : 0;

    if ($postId > 0) {
        $stmt = $mysqli->prepare('SELECT id, title, summary, content, tags, author, created_at, updated_at FROM blog_posts WHERE id = ? LIMIT 1');
        if (!$stmt) {
            error_response('无法获取文章');
        }
        $stmt->bind_param('i', $postId);
        $stmt->execute();
        $result = $stmt->get_result();
        $post = $result->fetch_assoc();
        $stmt->close();
        if (!$post) {
            error_response('文章不存在', 404);
        }
        $post['id'] = (int) $post['id'];
        json_response(['post' => $post]);
    }

    $result = $mysqli->query('SELECT id, title, summary, content, tags, author, created_at, updated_at FROM blog_posts ORDER BY created_at DESC, id DESC');
    if (!$result) {
        error_response('无法获取文章列表');
    }
    $posts = [];
    while ($row = $result->fetch_assoc()) {
        $row['id'] = (int) $row['id'];
        $posts[] = $row;
    }
    json_response(['posts' => $posts]);
}

if ($method === 'POST') {
    require_admin($mysqli);
    $input = $jsonInput ?: get_json_input();
    if (empty($input)) {
        $input = $_POST;
    }

    $title = trim((string) ($input['title'] ?? ''));
    $summary = trim((string) ($input['summary'] ?? ''));
    $content = trim((string) ($input['content'] ?? ''));
    $tags = trim((string) ($input['tags'] ?? ''));
    $author = trim((string) ($input['author'] ?? ''));

    if ($method === 'POST' && !isset($input['id'])) {
        if ($title === '' || $content === '') {
            error_response('标题与正文不能为空');
        }
        $stmt = $mysqli->prepare('INSERT INTO blog_posts (title, summary, content, tags, author) VALUES (?, ?, ?, ?, ?)');
        if (!$stmt) {
            error_response('无法创建文章');
        }
        $stmt->bind_param('sssss', $title, $summary, $content, $tags, $author);
        if (!$stmt->execute()) {
            $stmt->close();
            error_response('创建文章失败');
        }
        $postId = $stmt->insert_id;
        $stmt->close();
        $post = $mysqli->query('SELECT id, title, summary, content, tags, author, created_at, updated_at FROM blog_posts WHERE id = ' . (int) $postId)->fetch_assoc();
        json_response(['post' => $post], 201);
    }

    if (in_array($method, ['PATCH', 'PUT'], true)) {
        $postId = (int) ($input['id'] ?? 0);
        if ($postId <= 0) {
            error_response('文章ID无效');
        }
        if ($title === '' || $content === '') {
            error_response('标题与正文不能为空');
        }
        $stmt = $mysqli->prepare('UPDATE blog_posts SET title = ?, summary = ?, content = ?, tags = ?, author = ? WHERE id = ?');
        if (!$stmt) {
            error_response('无法更新文章');
        }
        $stmt->bind_param('sssssi', $title, $summary, $content, $tags, $author, $postId);
        if (!$stmt->execute()) {
            $stmt->close();
            error_response('更新文章失败');
        }
        $stmt->close();
        $post = $mysqli->query('SELECT id, title, summary, content, tags, author, created_at, updated_at FROM blog_posts WHERE id = ' . (int) $postId)->fetch_assoc();
        json_response(['post' => $post]);
    }

    error_response('不支持的操作');
}

if ($method === 'DELETE') {
    require_admin($mysqli);
    $input = $jsonInput ?: get_json_input();
    if (empty($input)) {
        $input = $_POST;
        if (empty($input)) {
            $input = $_GET;
        }
    }
    $postId = (int) ($input['id'] ?? 0);
    if ($postId <= 0) {
        error_response('文章ID无效');
    }
    $stmt = $mysqli->prepare('DELETE FROM blog_posts WHERE id = ? LIMIT 1');
    if (!$stmt) {
        error_response('无法删除文章');
    }
    $stmt->bind_param('i', $postId);
    if (!$stmt->execute()) {
        $stmt->close();
        error_response('删除文章失败');
    }
    $removed = $stmt->affected_rows > 0;
    $stmt->close();
    json_response(['success' => true, 'removed' => $removed]);
}

error_response('不支持的请求方法', 405);
