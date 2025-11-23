# Rare Light 罕见病课堂系统

基于 PHP + MySQL 的课堂与后台管理系统，支持学生/老师/管理员角色，涵盖课程/课节管理、课堂播放、云盘与批量导入。

## 功能概览

- **角色与权限**：学生、老师、管理员。老师仅能操作归属或分配给自己的课程/课节；管理员全权。
- **课程/课节管理**：课程含讲师、标签、owner；课节支持附件（多行“名称|链接”或纯链接），可直接插入云盘外链。
- **课堂体验**：课程筛选（搜索/标签/老师/进度）、排序，本地进度记录，手动标记完成，附件展示，哔哩哔哩外链识别。
- **批量导入**：CSV 支持 student/admin/teacher 角色，模板自带示例，错误行提示。
- **云盘**：管理员/老师可上传、分页查看、删除、开启外链；上传类型白名单，下载强制为附件。
- **品牌与移动端**：前端品牌统一 Rare Light，主页/课堂/后台/云盘均适配移动端。

## 快速开始

1. 将项目部署到支持 PHP 的服务器，确保 URL 重写生效（Apache 使用根目录 `.htaccess`，Nginx 参考下方示例）。
2. 首次访问 `/install`（或直接 `install.php`），按提示填写数据库与管理员信息，自动创建表结构与 `config.php`。
3. 安装后删除 `install.php`，使用管理员账号登录。
4. 学员进入 `/dashboard` 观看分配课程；老师/管理员在课堂页可跳转后台维护资源。

常用路由：
- `/login` → 登录页（index.php）
- `/dashboard` → 课堂
- `/admin` → 管理后台
- `/cloud` → 云盘
- `/api/<name>` → 对应接口

## 目录结构

```
index.php             # 登录页
dashboard.php         # 学员课堂
admin.php             # 管理后台
cloud.php             # 云盘
assets/css/main.css   # 样式
api/
├─ bootstrap.php      # 会话、DB 初始化
├─ session.php        # 当前用户
├─ login.php          # 登录
├─ logout.php         # 退出
├─ users.php          # 用户管理
├─ users_import.php   # CSV 批量导入（含 teacher）
├─ courses.php        # 课程/课节读取与管理
├─ lessons.php        # 课节 CRUD（含附件）
├─ files.php          # 云盘上传/列表/外链/删除
└─ course_assignments.php # 课程分配
database.sql          # 建表 SQL
config.example.php    # 配置示例
uploads/files         # 云盘目录（需可写）
```

## Nginx 重写示例

```nginx
location / {
    try_files $uri $uri/ @rewrite;
}
location @rewrite {
    rewrite ^/login/?$ /index.php last;
    rewrite ^/dashboard/?$ /dashboard.php last;
    rewrite ^/admin/?$ /admin.php last;
    rewrite ^/cloud/?$ /cloud.php last;
    rewrite ^/install/?$ /install.php last;
    rewrite ^/api/([a-z0-9_]+)/?$ /api/$1.php last;
    rewrite ^/$ /index.php last;
}
location ~* \.(css|js|png|jpg|jpeg|gif|ico|svg)$ {
    try_files $uri =404;
}
```

## 安全加固（已做基础项）

- Session Cookie 启用 HttpOnly + SameSite=Lax，HTTPS 下自动 Secure。
- 云盘下载强制附件头，上传限制白名单扩展名。
- 删除操作使用自定义确认弹窗。

> 建议继续加强：CSRF token、防 XSS 转义、接口限流/验证码、云盘执行隔离等。
