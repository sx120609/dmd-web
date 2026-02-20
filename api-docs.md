# API 文档

本文档对应当前项目代码（`/api/*.php`），用于前后端联调与第三方调用。

## 1. 基础信息

- API 基础路径：`/rarelight/api`
- 伪静态调用：`/rarelight/api/<name>`（如 `/rarelight/api/login`）
- 直连 PHP：`/rarelight/api/<name>.php`（如 `/rarelight/api/login.php`）
- 返回格式：`application/json; charset=utf-8`
- 错误格式：`{"error":"错误信息"}`

### 鉴权方式

- 大多数接口使用 Session（登录后自动携带 Cookie）。
- 图床接口 `image_host` 额外支持 Token（见下文）。

### 方法覆盖（兼容受限网络环境）

部分接口支持通过以下方式把 `POST` 覆盖为 `PATCH/PUT/DELETE`：

- Body 字段：`_method`
- Query：`?_method=PATCH`
- Header：`X-HTTP-Method-Override`

## 2. 通用状态码

- `200`：成功
- `201`：创建成功
- `400`：参数错误
- `401`：未登录/认证失败
- `403`：权限不足
- `404`：资源不存在
- `405`：方法不支持
- `413`：请求体过大（如分片过大）
- `500`：服务器内部错误

## 3. 会话与登录

### 3.1 登录

- `POST /api/login`
- 权限：匿名可调用
- 请求（JSON 或表单）：
  - `username`：用户名或 8 位学号
  - `password`：密码
- 返回：

```json
{
  "user": {
    "id": 1,
    "username": "admin",
    "display_name": "管理员",
    "role": "admin"
  }
}
```

### 3.2 当前会话

- `GET /api/session`
- 权限：匿名可调用
- 返回（未登录）：

```json
{ "user": null }
```

- 返回（已登录）：

```json
{
  "user": {
    "id": 1,
    "username": "admin",
    "display_name": "管理员",
    "role": "admin"
  }
}
```

### 3.3 退出登录

- `POST /api/logout`
- 权限：已登录用户
- 返回：

```json
{ "success": true }
```

## 4. 图床 API（独立功能）

### 4.1 上传图片并返回 URL

- `POST /api/image_host`
- 权限（满足其一）：
  - 管理员/老师登录态（Session）
  - 或配置了 `config.php` 的 `image_bed.api_token`，并在请求中带 token
- Token 传递方式（任选其一）：
  - Header：`X-Image-Token: <token>`（推荐）
  - Header：`X-Api-Token: <token>`
  - Form：`token=<token>`
  - Query：`?token=<token>`
- 请求类型：`multipart/form-data`
- 文件字段：
  - `image`（推荐）
  - 或 `file`
- 限制：
  - 仅支持单文件
  - 仅支持图片 MIME（jpg/png/gif/webp/bmp/tif/ico）
  - 默认大小上限：`20MB`（可通过 `image_bed.max_size_bytes` 修改）
- 成功返回（`201`）：

```json
{
  "url": "https://example.com/rarelight/uploads/images/xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx.jpg",
  "relative_url": "/rarelight/uploads/images/xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx.jpg",
  "mime_type": "image/jpeg",
  "size_bytes": 123456,
  "filename": "xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx.jpg"
}
```

### 4.2 Curl 示例

使用 Token：

```bash
curl -X POST "https://your-domain/rarelight/api/image_host" \
  -H "X-Image-Token: your-token" \
  -F "image=@/path/to/demo.png"
```

使用登录态（浏览器内调用）：

```js
const fd = new FormData();
fd.append('image', fileInput.files[0]);
const res = await fetch('/rarelight/api/image_host', {
  method: 'POST',
  credentials: 'include',
  body: fd
});
const data = await res.json();
```

## 5. 云盘文件 API

### 5.1 普通上传/列表/外链（`files`）

- 基础路径：`/api/files`
- 权限：管理员/老师（`token` 外链下载除外）

#### `GET /api/files`

- 查询参数（可选）：
  - `page`、`per_page`：分页
- 返回：
  - `files`: 文件数组
  - `pagination`: 分页信息（分页请求时）

#### `POST /api/files`

- 上传文件（`multipart/form-data`）：
  - 字段：`file`（支持单个或多个）
- 或删除文件（JSON）：
  - `{"action":"delete","id":123}`

#### `PATCH /api/files`

- 更新外链开关：
  - `id`: 文件 ID
  - `is_public`: `true/false`

#### `DELETE /api/files`

- 删除文件：
  - `id`: 文件 ID

#### `GET /api/files?token=<share_token>`

- 外链公开下载（无需登录，前提是该文件已公开）

### 5.2 分片上传（`files_multipart`）

- `POST /api/files_multipart`
- 权限：管理员/老师
- `action` 支持：`init`、`status`、`chunk`、`complete`

#### `action=init`

- 请求（JSON）：
  - `upload_id`（可选，不传则服务端生成）
  - `filename`
  - `size_bytes`
  - `mime_type`
  - `chunk_size`
- 返回：
  - `upload_id`
  - `uploaded_chunks`
  - `max_chunk_size`

#### `action=status`

- 请求：`upload_id`
- 返回：
  - `upload_id`
  - `uploaded_chunks`
  - `meta`

#### `action=chunk`

- 建议 Query：
  - `upload_id`
  - `index`（分片序号，从 0 开始）
  - `size`
  - `total_chunks`
- Body：该分片的二进制内容（`application/octet-stream`）
- 返回：`{"success":true,"index":0}`

#### `action=complete`

- 请求（JSON）：
  - `upload_id`
  - `filename`
  - `size_bytes`
  - `mime_type`
  - `total_chunks`
- 返回（`201`）：
  - `file`: 与 `files` 接口相同的文件对象

## 6. 管理与业务 API

### 6.1 用户管理 `users`（管理员）

- `GET /api/users`：用户列表
- `POST /api/users`：创建用户
- `PATCH/PUT /api/users`：更新用户
- `DELETE /api/users`：删除用户

### 6.2 用户导入 `users_import`（管理员）

- `POST /api/users_import`
- 上传字段：`file`（CSV）
- 表头支持：
  - `username,display_name,password,role`
  - `username,display_name,password,role,entry_year,entry_term`
  - `username,display_name,password,role,student_no`

### 6.3 课程管理 `courses`

- `GET /api/courses`：按角色返回可见课程
- `GET /api/courses?id=<course_id>`：课程详情 + 课节
- `POST /api/courses`：创建课程（管理员/老师）
- `PATCH/PUT /api/courses`：更新课程（管理员/老师）
- `DELETE /api/courses`：删除课程（管理员/老师）

### 6.4 课节管理 `lessons`（管理员/老师）

- `POST /api/lessons`：创建课节
- `PATCH/PUT /api/lessons`：更新课节
- `DELETE /api/lessons`：删除课节

### 6.5 课程分配 `course_assignments`（管理员）

- `GET /api/course_assignments`：分配列表（可带 `user_id`）
- `POST /api/course_assignments`：分配/移除
- `DELETE /api/course_assignments`：移除分配

### 6.6 批量课程分配 `course_assignments_import`（管理员）

- `POST /api/course_assignments_import`
- 上传字段：`file`（CSV）
- 表头：`username,course_id`

### 6.7 学习进度 `progress`（已登录用户）

- `GET /api/progress`：获取进度（可带 `course_id`）
- `POST /api/progress`：上报进度
  - `action`：`visit` / `complete` / `uncomplete`
  - `course_id`
  - `lesson_id`

### 6.8 项目日志 `blog_posts`

- `GET /api/blog_posts`：文章列表（公开）
- `GET /api/blog_posts?id=<id>`：文章详情（公开）
- `POST /api/blog_posts`：创建文章（管理员）
- `PATCH/PUT /api/blog_posts`：更新文章（管理员）
- `DELETE /api/blog_posts`：删除文章（管理员）

## 7. 配置项（与 API 相关）

`config.php` 中可选项：

```php
'image_bed' => [
  'api_token' => 'replace-with-strong-token',
  'max_size_bytes' => 20 * 1024 * 1024,
],
'storage' => [
  'cloud_dir' => '/path/to/uploads/files',
  'public_prefix' => '/uploads/files',
  'public_base_path' => '/rarelight',
  'public_base_url' => 'https://organic.cpu.edu.cn',
  'nginx_internal_prefix' => '/protected/files',
  'image_dir' => '/path/to/uploads/images',
  'image_public_prefix' => '/uploads/images',
]
```

其中 `storage.public_base_url` 用于图床接口返回绝对 URL 的域名（反向代理场景强烈建议配置）。
