# 简易录播课系统

这是一个使用 PHP + MySQL 搭建的简易录播课程系统示例，前端采用原生 HTML/CSS/JS 并与后端接口分离：

- **登录页（index.php）**：提供现代化的登录体验，完成登录后自动跳转课堂。
- **课堂页（dashboard.php）**：按用户身份展示专属课程，并基于 Plyr 播放器提供流畅的录播观看体验，可识别哔哩哔哩链接。
- **管理后台（admin.php）**：管理员可在独立页面中维护用户、课程、课节以及课程分配。
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
├── lessons.php       # 添加课节
└── course_assignments.php # 课程分配
install.php           # 首次部署安装脚本
```

## 注意事项

- 所有需要登录的接口均基于 PHP 会话，需保证浏览器允许携带 Cookie（同源部署时默认即可）。
- 管理操作暂无删除接口，如需扩展请在 `api/` 目录内增加对应 PHP 文件。
- 示例未引入现代前端构建工具，方便直接修改并部署；实际环境中建议结合 HTTPS、输入校验、权限校验等最佳实践进行增强。
