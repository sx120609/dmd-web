# 简易录播课系统

这是一个使用 PHP + MySQL 搭建的简易录播课程系统示例，支持前后端分离：

- 学生登录后只能看到被分配的课程和课节。
- 管理员可以在后台管理界面创建用户、创建课程、添加课节，并将课程分配给指定用户。
- 所有接口均采用原生 PHP（`mysqli`）实现，返回 JSON 数据供前端调用。

## 快速开始

1. 将 `config.example.php` 复制为 `config.php` 并填写数据库连接信息。
2. 在数据库中执行 `database.sql` 中的建表语句，初始化所需数据表。
3. 手动向 `users` 表插入至少一位管理员账号（`role` 为 `admin`，`password_hash` 可通过 PHP 的 `password_hash` 函数生成）。
4. 将项目放置在支持 PHP 的服务器上，使 `index.php` 和 `api/` 目录可访问。

前端页面默认从 `api/` 目录下的接口获取数据，确保服务器允许跨请求携带 Cookie（同源部署时默认即可）。

## 目录结构

```
index.php            # 前端单页应用
config.php           # 数据库和会话配置（示例已提供）
api/                 # 登录、课程、用户等接口
└── bootstrap.php    # 会话和数据库初始化
└── login.php        # 登录接口
└── logout.php       # 退出接口
└── session.php      # 获取当前登录用户
└── users.php        # 管理用户
└── courses.php      # 管理/获取课程及课节
└── lessons.php      # 添加课节
└── course_assignments.php # 课程分配
```

## 注意事项

- 所有需要登录的接口均基于 PHP 会话，需保证浏览器允许携带 Cookie。
- 该示例仅提供最基础的管理能力，实际部署时请结合 HTTPS、输入校验、分页等最佳实践进行增强。
- 若要扩展更多功能，可在 `api/` 目录新增 PHP 文件并在前端调用对应接口。
