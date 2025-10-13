<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>网课系统 · 登录</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="assets/css/main.css">
    <style>
        body.login {
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 3rem 1.5rem;
        }

        .login-wrapper {
            width: min(1040px, 100%);
            display: grid;
            grid-template-columns: minmax(0, 1.05fr) minmax(0, 0.95fr);
            border-radius: var(--radius-lg);
            overflow: hidden;
            background: rgba(255, 255, 255, 0.85);
            border: 1px solid rgba(148, 163, 184, 0.16);
            box-shadow: 0 34px 90px rgba(15, 23, 42, 0.18);
            backdrop-filter: blur(18px);
            min-height: 520px;
        }

        .login-aside {
            position: relative;
            padding: 3.2rem 3rem;
            background: var(--brand-gradient);
            color: #fff;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            gap: 2.75rem;
            isolation: isolate;
        }

        .login-aside::after {
            content: '';
            position: absolute;
            inset: 0;
            background: radial-gradient(circle at top left, rgba(255, 255, 255, 0.35), transparent 55%),
                radial-gradient(circle at bottom right, rgba(15, 23, 42, 0.22), transparent 55%);
            opacity: 0.9;
            z-index: 0;
        }

        .login-aside > * {
            position: relative;
            z-index: 1;
        }

        .login-brand {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            font-weight: 600;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            font-size: 0.85rem;
            padding: 0.35rem 1rem;
            border-radius: 999px;
            background: rgba(255, 255, 255, 0.18);
        }

        .login-aside h1 {
            margin: 0.75rem 0 1rem;
            font-size: clamp(2.2rem, 3vw, 3rem);
            font-weight: 700;
            letter-spacing: -0.02em;
            line-height: 1.1;
        }

        .login-aside p {
            margin: 0;
            line-height: 1.75;
            color: rgba(255, 255, 255, 0.85);
        }

        .login-points {
            margin: 0;
            padding: 0;
            list-style: none;
            display: grid;
            gap: 1rem;
        }

        .login-points li {
            display: flex;
            align-items: center;
            gap: 0.85rem;
            font-weight: 500;
        }

        .login-points li::before {
            content: '✓';
            width: 1.85rem;
            height: 1.85rem;
            border-radius: 999px;
            background: rgba(255, 255, 255, 0.22);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            box-shadow: inset 0 0 0 1px rgba(255, 255, 255, 0.35);
        }

        .login-panel {
            padding: 3.2rem 3rem;
            display: flex;
            flex-direction: column;
            gap: 2.5rem;
            background: rgba(255, 255, 255, 0.94);
        }

        .login-panel header h2 {
            margin: 0 0 0.6rem;
            font-size: 2rem;
        }

        .login-panel header p {
            margin: 0;
            color: var(--text-secondary);
            line-height: 1.7;
        }

        .login-panel form {
            display: grid;
            gap: 1.2rem;
        }

        .login-panel footer {
            font-size: 0.88rem;
            color: var(--text-secondary);
            line-height: 1.6;
        }

        .login-panel footer a {
            color: var(--brand-color);
            font-weight: 600;
        }

        .login-message {
            min-height: 1.6rem;
        }

        @media (max-width: 960px) {
            .login-wrapper {
                grid-template-columns: 1fr;
            }

            .login-aside,
            .login-panel {
                padding: 2.75rem 2.5rem;
            }

            .login-aside {
                min-height: auto;
            }
        }

        @media (max-width: 640px) {
            body.login {
                padding: 2rem 1rem;
            }

            .login-panel {
                padding: 2.25rem 2rem;
            }
        }
    </style>
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
            <button type="submit" class="primary-button" id="loginButton">立即登录</button>
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

    function normalizeApiUrl(url) {
        if (url.startsWith(`${API_BASE}/`)) {
            const [path, query] = url.split('?');
            const sanitizedPath = path.replace(/\/{2,}/g, '/');
            return query ? `${sanitizedPath}?${query}` : sanitizedPath;
        }
        return url;
    }

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
        const response = await fetch(normalizeApiUrl(url), {
            credentials: 'include',
            headers: {
                'Accept': 'application/json',
                ...options.headers
            },
            ...options
        });
        const data = await response.json().catch(() => null);
        if (!response.ok) {
            const message = (data && (data.message || data.error)) || '请求失败，请稍后重试';
            throw new Error(message);
        }
        return data;
    }

    async function checkSession() {
        try {
            const data = await fetchJSON(`${API_BASE}/session.php`, { method: 'GET' });
            if (data && data.user) {
                window.location.href = 'dashboard';
            }
        } catch (error) {
            // ignore
        }
    }

    loginForm.addEventListener('submit', async (event) => {
        event.preventDefault();
        const formData = new FormData(loginForm);
        const rawUsername = formData.get('username');
        const payload = {
            username: rawUsername ? rawUsername.trim() : '',
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
                window.location.href = 'dashboard';
            }, 400);
        } catch (error) {
            showMessage(error.message || '登录失败，请重试', 'error');
        } finally {
            loginButton.disabled = false;
        }
    });

    checkSession();
</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
