# Rare Light 罕见病课堂系统

基于 PHP + MySQL 的课堂与后台管理系统，覆盖首页宣传、课堂播放、云盘与后台运营管理。

## 功能特性

- **门户与登录**：首页提供中/英双语与字体缩放切换，已登录用户自动提示进入课堂；登录表单直达课堂。
- **角色与权限**：学生、老师、管理员；老师仅能操作归属或分配给自己的课程/课节与文件，管理员全权。
- **课程/课节管理**：课程包含讲师、标签、owner；课节支持“名称|链接”或纯链接的附件清单（可直接放云盘外链），描述字段自动补齐。
- **课堂体验**：课程列表可搜索、按标签/老师/进度过滤，并支持最新/进度/未看排序；本地存储进度、手动标记完成；附件一键打开；自动识别哔哩哔哩链接并提供跳转入口。
- **后台运营**：后台切换用户/课程/课节/课程分配四个视图；课节与课程更新实时保存，删除级联清理课节与分配记录；教师被分配或拥有的课程才能编辑。
- **批量导入与分配**：CSV 模板字段 `username,display_name,password,role`，支持 student/admin/teacher，逐行错误提示；管理员可为任意用户分配/移除课程。
- **云盘与外链**：管理员/老师可批量上传，支持分片断点续传（150MB 分片、按文件 hash 断点续传）；文件分页列表、删除、外链开关；随机令牌外链、强制附件下载、上传类型白名单。
- **安全与体验**：Session Cookie HttpOnly + SameSite=Lax（HTTPS 自动 Secure），课堂/后台/云盘均适配移动端；支持自定义存储目录、反代前缀、Nginx 内部转发以保护真实路径。

## 安装与启动

> 假设挂载路径为 `/rarelight`，请确保 Web Server 支持 URL 重写。

1. 环境：PHP 7.4+、MySQL 5.7+/MariaDB、pdo_mysql/mysqli 扩展；`uploads/` 及配置的云盘目录需可写。
2. 部署代码到站点目录，启用 `.htaccess` 或 Nginx 重写（见下方示例）。
3. 首次访问 `/rarelight/install`（或 `/rarelight/install.php`），填写数据库与管理员信息，脚本会创建表结构并生成 `config.php`。
   - 如需手动配置，可复制 `config.example.php` 为 `config.php`，填好数据库信息后执行 `database.sql`，并手动创建可写的 `uploads/files`。
   - 云盘可在 `config.php` 的 `storage` 下设置 `cloud_dir`（存储目录）、`public_prefix`/`public_base_path`（静态前缀）、`nginx_internal_prefix`（内部跳转）。
4. 安装完成后删除 `install.php`，使用管理员账号登录后台；学生访问 `/rarelight/dashboard` 开始学习。

常用路由：
- `/rarelight/login` → 登录页（index.php）
- `/rarelight/dashboard` → 课堂
- `/rarelight/admin` → 管理后台
- `/rarelight/cloud` → 云盘
- `/rarelight/api/<name>` → 对应接口（login、logout、session、users、users_import、courses、lessons、course_assignments、files、files_multipart）

## 目录结构

```
index.php             # 首页 + 登录（含多语言）
dashboard.php         # 学员课堂
admin.php             # 管理后台
cloud.php             # 云盘（含分片上传）
assets/css/main.css   # 样式
api/
├─ bootstrap.php           # 会话、DB、权限工具
├─ session.php             # 当前登录用户
├─ login.php / logout.php  # 登录/登出
├─ users.php               # 用户 CRUD
├─ users_import.php        # CSV 批量导入
├─ courses.php             # 课程 CRUD（含 owner、标签、讲师）
├─ lessons.php             # 课节 CRUD（含附件）
├─ course_assignments.php  # 课程分配
├─ files.php               # 云盘上传/列表/删除/外链
└─ files_multipart.php     # 分片断点续传
database.sql          # 建表 SQL
config.example.php    # 配置示例
uploads/files         # 云盘目录（需可写）
```

## Nginx 重写示例

```nginx
# 将应用挂载在 /rarelight
location ^~ /rarelight/ {
    try_files $uri $uri/ @rarelight_rewrite;
}

location @rarelight_rewrite {
    rewrite ^/rarelight/login/?$ /rarelight/index.php last;
    rewrite ^/rarelight/dashboard/?$ /rarelight/dashboard.php last;
    rewrite ^/rarelight/admin/?$ /rarelight/admin.php last;
    rewrite ^/rarelight/cloud/?$ /rarelight/cloud.php last;
    rewrite ^/rarelight/install/?$ /rarelight/install.php last;
    rewrite ^/rarelight/api/([a-z0-9_]+)/?$ /rarelight/api/$1.php last;
    rewrite ^/rarelight/?$ /rarelight/index.php last;
}

location ~* ^/rarelight/.*\.(css|js|png|jpg|jpeg|gif|ico|svg)$ {
    try_files $uri =404;
}
```

## 已做的基础安全

- Session Cookie HttpOnly + SameSite=Lax，HTTPS 自动 Secure。
- 云盘下载强制附件，上传扩展白名单，令牌外链可随时关闭。
- 删除类操作使用自定义确认弹窗。

> 建议继续加强：CSRF token、防 XSS 转义、接口限流/验证码、云盘执行隔离等。
