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
    <style>
        body.login {
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 3rem 1.5rem;
        }

        .login-grid {
            width: min(1040px, 100%);
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
            gap: 2.25rem;
            align-items: stretch;
        }

        .login-hero {
            padding: 2.75rem;
            background: linear-gradient(135deg, rgba(79, 70, 229, 0.85), rgba(147, 51, 234, 0.85));
            color: #fff;
            border-radius: var(--radius-lg);
            position: relative;
            overflow: hidden;
        }

        .login-hero::after {
            content: '';
            position: absolute;
            inset: 0;
            background: radial-gradient(circle at top left, rgba(255, 255, 255, 0.25), transparent 50%);
            pointer-events: none;
        }

        .login-hero h1 {
            margin-top: 0;
            font-size: clamp(2rem, 3vw, 2.8rem);
            font-weight: 700;
            letter-spacing: -0.02em;
        }

        .login-hero p {
            font-size: 1rem;
            line-height: 1.75;
            color: rgba(255, 255, 255, 0.82);
        }

        .login-hero .feature-list {
            margin: 2rem 0 0;
            padding: 0;
            list-style: none;
            display: grid;
            gap: 1rem;
        }

        .login-hero .feature-list li {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            font-weight: 600;
        }

        .login-hero .feature-list span {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 1.85rem;
            height: 1.85rem;
            border-radius: 999px;
            background: rgba(255, 255, 255, 0.18);
            font-size: 0.95rem;
        }

        .login-card {
            padding: 2.5rem 2.75rem;
            display: flex;
            flex-direction: column;
            gap: 2rem;
        }

        .login-card header h2 {
            margin: 0 0 0.4rem;
            font-size: 1.75rem;
        }

        .login-card header p {
            margin: 0;
            color: var(--text-secondary);
            line-height: 1.6;
        }

        .login-card form {
            display: grid;
            gap: 1.1rem;
        }

        .login-card footer {
            font-size: 0.85rem;
            color: var(--text-secondary);
        }

        .login-card footer a {
            color: var(--brand-color);
            font-weight: 600;
        }

        .floating-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            background: rgba(79, 70, 229, 0.1);
            color: #fff;
            padding: 0.5rem 1rem;
            border-radius: 999px;
            font-size: 0.9rem;
            backdrop-filter: blur(8px);
        }

        .floating-badge svg {
            width: 1.2rem;
            height: 1.2rem;
        }

        .login-message {
            min-height: 1.5rem;
        }

        @media (max-width: 720px) {
            body.login {
                padding: 2rem 1rem;
            }

            .login-card {
                padding: 2rem;
            }
        }
    </style>
</head>
<body class="login">
<main class="login-grid">
    <section class="login-hero card fade-in">
        <div class="floating-badge">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6l4 2" />
                <path stroke-linecap="round" stroke-linejoin="round" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            随时随地学习
        </div>
        <h1>欢迎来到智能录播课堂</h1>
        <p>为每位学生分发专属课程，沉浸式观看体验与轻盈的管理后台一应俱全。登录后即可开始学习或管理内容。</p>
        <ul class="feature-list">
            <li><span>·</span> 一键播放录播课，支持哔哩哔哩地址</li>
            <li><span>·</span> 灵活分配课程给不同学员</li>
            <li><span>·</span> 管理端支持课程、课节、用户维护</li>
        </ul>
    </section>
    <section class="login-card card fade-in">
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
            <div class="message login-message" id="loginMessage" aria-live="polite"></div>
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

    function showMessage(text, type = '') {
        loginMessage.textContent = text || '';
        loginMessage.classList.remove('error', 'success');
        if (type) {
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
            const message = data?.message || '请求失败，请稍后重试';
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
