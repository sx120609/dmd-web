<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>网课系统 · 登录</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/main.css">
</head>
<body class="login">
<main class="login-wrapper fade-in">
    <section class="login-aside">
        <div>
            <span class="login-brand">智能录播课堂</span>
            <h1>为每位学生定制的线上课堂</h1>
            <p>轻松分发录播课、管理学员与进度，支持哔哩哔哩地址及本地资源，带来更连贯的学习体验。</p>
        </div>
        <ul class="login-points">
            <li>专属账号登录，按用户分配课程</li>
            <li>课节结构清晰，支持多种视频源</li>
            <li>管理员后台实时维护课程内容</li>
        </ul>
    </section>
    <section class="login-panel">
        <header>
            <h2>账号登录</h2>
            <p>请输入管理员为您创建的用户名和密码。</p>
        </header>
        <form id="loginForm" autocomplete="on">
            <div>
                <label for="username">用户名</label>
                <input id="username" name="username" type="text" placeholder="student01" required>
            </div>
            <div>
                <label for="password">密码</label>
                <input id="password" name="password" type="password" placeholder="请输入密码" required>
            </div>
            <button type="submit" class="btn btn-primary" id="loginButton">立即登录</button>
            <div class="message login-message" id="loginMessage" aria-live="polite" hidden></div>
        </form>
        <footer>
            如需开通账号或重置密码，请联系课堂管理员。
        </footer>
    </section>
</main>
<script>
    const loginForm = document.getElementById('loginForm');
    const loginButton = document.getElementById('loginButton');
    const loginMessage = document.getElementById('loginMessage');

    const API_BASE = 'api';

    function showMessage(text = '', type = '') {
        const hasText = Boolean(text);
        loginMessage.textContent = hasText ? text : '';
        loginMessage.classList.remove('error', 'success');
        loginMessage.hidden = !hasText;
        if (hasText && type) {
            loginMessage.classList.add(type);
        }
    }

    async function fetchJSON(url, options = {}) {
        const response = await fetch(url, {
            credentials: 'include',
            headers: {
                'Accept': 'application/json',
                ...options.headers
            },
            ...options
        });
        const data = await response.json().catch(() => null);
        if (!response.ok) {
            const message = data?.message || data?.error || '请求失败，请稍后重试';
            throw new Error(message);
        }
        return data;
    }

    async function checkSession() {
        try {
            const data = await fetchJSON(`${API_BASE}/session.php`, { method: 'GET' });
            if (data?.user) {
                window.location.href = 'dashboard.php';
            }
        } catch (error) {
            // ignore
        }
    }

    loginForm.addEventListener('submit', async (event) => {
        event.preventDefault();
        const formData = new FormData(loginForm);
        const payload = {
            username: formData.get('username')?.trim(),
            password: formData.get('password')
        };
        if (!payload.username || !payload.password) {
            showMessage('请输入用户名和密码', 'error');
            return;
        }
        loginButton.disabled = true;
        showMessage('正在登录，请稍候...');
        try {
            await fetchJSON(`${API_BASE}/login.php`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(payload)
            });
            showMessage('登录成功，正在跳转...', 'success');
            setTimeout(() => {
                window.location.href = 'dashboard.php';
            }, 400);
        } catch (error) {
            showMessage(error.message || '登录失败，请重试', 'error');
        } finally {
            loginButton.disabled = false;
        }
    });

    checkSession();
</script>
</body>
</html>
