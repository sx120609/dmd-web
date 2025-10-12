# 简易录播课系统

这是一个使用 PHP + MySQL 搭建的简易录播课程系统示例，前端采用原生 HTML/CSS/JS 并与后端接口分离：

- **登录页（index.php）**：提供现代化的登录体验，完成登录后自动跳转课堂。
- **课堂页（dashboard.php）**：按用户身份展示专属课程，录播播放与直播安排分栏呈现，可识别哔哩哔哩链接并内置第三方播放器。
- **管理后台（admin.php）**：管理员可在独立页面中维护用户、录播课程/课节以及独立的直播课安排，并管理课程分配。
- 后端接口均使用原生 PHP（`mysqli`）实现并返回 JSON 数据供前端调用。

## 快速开始

1. 将项目放置在支持 PHP 的服务器上，使 `index.php`、`dashboard.php`、`admin.php`、`install.php` 与 `api/` 目录可访问。
2. 首次部署后访问 `install.php`，按照页面提示填写数据库与管理员信息，脚本会自动：
   - 创建（如不存在）并初始化数据库表结构；
   - 生成 `config.php` 配置文件；
   - 创建首位管理员账号。
3. 安装完成后删除 `install.php` 并使用管理员账号登录系统。
4. 普通学员登录后进入课堂页即可观看被分配的课程；管理员在课堂页可跳转至后台继续维护资源。

如需手动部署或二次配置，可参考 `config.example.php` 与 `database.sql` 获取所需配置和建表语句。

### 已有环境的数据库修复

如果此前运行过旧版本的代码，可能存在字段缺失或外键约束不一致导致课程/直播加载失败的情况。部署完最新代码后，可执行

```bash
php repair_database.php
```

该脚本会读取 `config.php` 并：

- 重新执行 `database.sql`，确保核心数据表存在；
- 检查并补齐 `users`、`courses`、`lessons`、`live_sessions`、`user_courses`、`user_live_sessions` 表所需字段；
- 自动恢复直播课外键约束。

脚本具备幂等性，可在需要时重复运行。

## 目录结构

```
index.php             # 登录页面
dashboard.php         # 学员课堂页面
admin.php             # 管理后台
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
├── lessons.php       # 管理课节（新增/编辑/删除）
├── live_sessions.php # 管理直播课安排
└── course_assignments.php # 课程分配
install.php           # 首次部署安装脚本
```

## 直播课功能

- 直播课拥有独立的数据表与接口，可在后台的「直播课程」页集中创建、更新和删除。
- 新增直播课时需选择已存在的课程进行归属，系统会根据课程分配自动控制可见范围。
- 学员端「直播课程」分栏会展示即将到来的直播信息及进入按钮，录播列表不再混杂直播条目。

## 注意事项

- 所有需要登录的接口均基于 PHP 会话，需保证浏览器允许携带 Cookie（同源部署时默认即可）。
- 新增的 `live_sessions`、`user_live_sessions` 表用于存储直播课信息和可选的用户关联，更新代码后请执行 `database.sql` 或运行 `php repair_database.php` 初始化/补全结构。
- 示例未引入现代前端构建工具，方便直接修改并部署；实际环境中建议结合 HTTPS、输入校验、权限校验等最佳实践进行增强。
