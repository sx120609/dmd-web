# 简易录播课系统

这是一个使用 PHP + MySQL 搭建的简易录播课程系统示例，前端采用原生 HTML/CSS/JS 并与后端接口分离：

- **登录页（/login）**：提供现代化的登录体验，完成登录后自动跳转课堂。
- **课堂页（/dashboard）**：按用户身份展示专属课程，并基于 Plyr 播放器提供流畅的录播观看体验，可识别哔哩哔哩链接。
- **管理后台（/admin）**：管理员可在独立页面中维护用户、课程、课节以及课程分配。
- **移动端自适应**：核心页面针对平板和手机做了响应式布局优化，侧边栏会自动折叠为单列，按钮与表单也会匹配窄屏宽度。
- 后端接口均使用原生 PHP（`mysqli`）实现并返回 JSON 数据供前端调用。

## 快速开始

1. 将项目放置在支持 PHP 与 URL 重写的服务器上，保持根目录中的 `.htaccess` 生效以启用伪静态路由（Apache 环境）。如使用 Nginx，可参考下方示例配置重写规则。
2. 首次部署后访问 `/install`（或直接访问 `install.php`），按照页面提示填写数据库与管理员信息，脚本会自动：
   - 创建（如不存在）并初始化数据库表结构；
   - 生成 `config.php` 配置文件；
   - 创建首位管理员账号。
3. 安装完成后删除 `install.php` 并使用管理员账号登录系统。
4. 普通学员登录后进入课堂页即可观看被分配的课程；管理员在课堂页可跳转至后台继续维护资源。

伪静态规则将以下友好地址映射至对应的 PHP 文件：

- `/login` → `index.php`
- `/dashboard` → `dashboard.php`
- `/admin` → `admin.php`
- `/install` → `install.php`
- `/api/<name>` → `api/<name>.php`

如需手动部署或二次配置，可参考 `config.example.php` 与 `database.sql` 获取所需配置和建表语句。

### Apache 伪静态示例

在 Apache 环境下，可将以下内容保存为项目根目录的 `.htaccess`，以便自动重写常用页面与 API：

```apacheconf
<IfModule mod_rewrite.c>
    RewriteEngine On

    # 允许直接访问已有文件或目录
    RewriteCond %{REQUEST_FILENAME} -f [OR]
    RewriteCond %{REQUEST_FILENAME} -d
    RewriteRule ^ - [L]

    # 顶级页面的友好路由
    RewriteRule ^$ index.php [L,QSA]
    RewriteRule ^login/?$ index.php [L,QSA]
    RewriteRule ^dashboard/?$ dashboard.php [L,QSA]
    RewriteRule ^admin/?$ admin.php [L,QSA]
    RewriteRule ^install/?$ install.php [L,QSA]

    # API 伪静态（如 /api/session -> /api/session.php）
    RewriteRule ^api/([a-z0-9_]+)/?$ api/$1.php [L,QSA,NC]
</IfModule>
```

### Nginx 伪静态示例

若使用 Nginx 部署，请在对应的 `server` 区块中添加如下规则来模拟 `.htaccess` 的伪静态效果：

```nginx
location / {
    try_files $uri $uri/ @rewrite;
}

location @rewrite {
    rewrite ^/login/?$ /index.php last;
    rewrite ^/dashboard/?$ /dashboard.php last;
    rewrite ^/admin/?$ /admin.php last;
    rewrite ^/install/?$ /install.php last;
    rewrite ^/api/([a-z0-9_]+)/?$ /api/$1.php last;
    rewrite ^/$ /index.php last;
}

# 静态资源保持默认处理方式
location ~* \.(css|js|png|jpg|jpeg|gif|ico|svg)$ {
    try_files $uri =404;
}
```

请根据自身 PHP 运行方式（如 PHP-FPM）补充 `fastcgi` 等相关配置。

## 目录结构

```
.htaccess             # 伪静态路由规则
index.php             # 登录页面（/login）
dashboard.php         # 学员课堂页面（/dashboard）
admin.php             # 管理后台（/admin）
assets/
├── css/main.css      # 共享样式与主题
config.php            # 数据库和会话配置（示例已提供）
api/                  # 登录、课程、用户等接口
├── bootstrap.php     # 会话和数据库初始化
├── login.php         # 登录接口
├── logout.php        # 退出接口
├── session.php       # 获取当前登录用户
├── users.php         # 管理用户
├── courses.php       # 管理/获取课程及课节
├── lessons.php       # 添加课节
└── course_assignments.php # 课程分配
install.php           # 首次部署安装脚本（/install）
```

## 注意事项

- 所有需要登录的接口均基于 PHP 会话，需保证浏览器允许携带 Cookie（同源部署时默认即可）。
- 管理操作暂无删除接口，如需扩展请在 `api/` 目录内增加对应 PHP 文件。
- 示例未引入现代前端构建工具，方便直接修改并部署；实际环境中建议结合 HTTPS、输入校验、权限校验等最佳实践进行增强。
