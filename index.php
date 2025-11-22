<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Rare Light · 点亮罕见病儿童的希望</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="assets/css/main.css">
    <style>
        :root {
            --home-bg: radial-gradient(circle at 20% 20%, rgba(59, 130, 246, 0.12), transparent 25%),
                radial-gradient(circle at 80% 10%, rgba(45, 212, 191, 0.14), transparent 26%),
                radial-gradient(circle at 80% 70%, rgba(76, 29, 149, 0.08), transparent 40%),
                #f8fafc;
            --card-shadow: 0 24px 80px rgba(15, 23, 42, 0.12);
            --card-border: 1px solid rgba(148, 163, 184, 0.14);
            --deep-gradient: linear-gradient(135deg, #2563eb, #60a5fa, #22d3ee);
            --pill-bg: rgba(255, 255, 255, 0.65);
        }

        body.home {
            font-family: 'Inter', system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            background: var(--home-bg);
            color: #0f172a;
            min-height: 100vh;
        }

        .site-shell {
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }

        .site-nav {
            position: sticky;
            top: 0;
            z-index: 10;
            backdrop-filter: blur(16px);
            background: rgba(248, 250, 252, 0.9);
            border-bottom: 1px solid rgba(148, 163, 184, 0.12);
        }

        .nav-brand {
            display: inline-flex;
            align-items: center;
            gap: 0.75rem;
            font-weight: 700;
            letter-spacing: -0.01em;
        }

        .brand-mark {
            width: 38px;
            height: 38px;
            border-radius: 12px;
            background: var(--deep-gradient);
            color: #fff;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            box-shadow: 0 12px 30px rgba(37, 99, 235, 0.35);
        }

        .brand-eyebrow {
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
            padding: 0.45rem 0.9rem;
            border-radius: 999px;
            background: var(--pill-bg);
            color: #1e293b;
            font-weight: 600;
            font-size: 0.78rem;
            letter-spacing: 0.06em;
            text-transform: uppercase;
        }

        .hero {
            position: relative;
            padding: clamp(2.5rem, 6vw, 4.5rem) 0 clamp(2.5rem, 6vw, 4.5rem);
        }

        .hero-grid {
            display: grid;
            grid-template-columns: minmax(0, 1.05fr) minmax(0, 0.95fr);
            gap: clamp(1.75rem, 3vw, 2.5rem);
            align-items: center;
        }

        .hero h1 {
            font-size: clamp(2.4rem, 4vw, 3.6rem);
            font-weight: 800;
            letter-spacing: -0.02em;
            line-height: 1.1;
            margin: 1rem 0 1.25rem;
        }

        .hero p.lead {
            font-size: 1.06rem;
            color: #475569;
            line-height: 1.8;
            margin-bottom: 1.75rem;
        }

        .hero-actions {
            display: flex;
            flex-wrap: wrap;
            gap: 0.9rem;
            align-items: center;
        }

        .ghost-button {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.85rem 1.1rem;
            border-radius: 12px;
            border: 1px solid rgba(15, 23, 42, 0.12);
            font-weight: 600;
            color: #0f172a;
            background: rgba(255, 255, 255, 0.7);
            text-decoration: none;
            transition: transform 160ms ease, box-shadow 160ms ease;
        }

        .ghost-button:hover {
            transform: translateY(-1px);
            box-shadow: 0 10px 30px rgba(15, 23, 42, 0.08);
        }

        .session-note {
            margin-top: 1rem;
            color: #0f172a;
            font-weight: 600;
        }

        .eyebrow {
            font-weight: 700;
            color: #2563eb;
            letter-spacing: 0.02em;
        }

        .hero-card {
            position: relative;
            background: rgba(255, 255, 255, 0.9);
            border-radius: 22px;
            padding: clamp(1.6rem, 2vw, 2rem);
            box-shadow: var(--card-shadow);
            border: var(--card-border);
            overflow: hidden;
        }

        .hero-card::after {
            content: '';
            position: absolute;
            inset: -35% -10% auto auto;
            height: 260px;
            width: 260px;
            background: radial-gradient(circle at center, rgba(96, 165, 250, 0.2), transparent 70%);
            z-index: 0;
            transform: rotate(-8deg);
        }

        .hero-card > * {
            position: relative;
            z-index: 1;
        }

        .stat-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 1rem;
            margin-top: 1.25rem;
        }

        .stat {
            padding: 1rem;
            border-radius: 14px;
            background: rgba(248, 250, 252, 0.75);
            border: 1px solid rgba(148, 163, 184, 0.18);
        }

        .stat strong {
            display: block;
            font-size: 1.4rem;
            color: #0f172a;
        }

        .section {
            padding: clamp(2.5rem, 6vw, 4rem) 0;
        }

        .section header {
            margin-bottom: 1.5rem;
        }

        .section header h2 {
            font-weight: 800;
            letter-spacing: -0.01em;
        }

        .feature-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 1rem;
        }

        .feature-card {
            padding: 1.4rem;
            border-radius: 14px;
            background: rgba(255, 255, 255, 0.92);
            border: var(--card-border);
            box-shadow: 0 18px 40px rgba(15, 23, 42, 0.08);
            display: grid;
            gap: 0.6rem;
        }

        .feature-icon {
            width: 44px;
            height: 44px;
            border-radius: 12px;
            background: rgba(37, 99, 235, 0.1);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            color: #2563eb;
            font-weight: 700;
        }

        .pathway {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: 1rem;
        }

        .pathway-step {
            padding: 1.4rem;
            border-radius: 14px;
            border: 1px dashed rgba(148, 163, 184, 0.45);
            background: rgba(248, 250, 252, 0.8);
        }

        .cta-banner {
            margin-top: 1.5rem;
            padding: 1.4rem;
            border-radius: 16px;
            background: var(--deep-gradient);
            color: #fff;
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: 1rem;
            justify-content: space-between;
        }

        .cta-banner .cta-button {
            background: #ffffff;
            color: #1d4ed8;
            border: 1px solid rgba(255, 255, 255, 0.75);
            box-shadow: 0 14px 32px rgba(15, 23, 42, 0.16);
            transition: transform 0.18s ease, box-shadow 0.18s ease, filter 0.18s ease;
        }

        .cta-banner .cta-button:hover {
            transform: translateY(-1px);
            box-shadow: 0 18px 40px rgba(15, 23, 42, 0.2);
            filter: brightness(1.02);
        }

        .classroom {
            background: rgba(255, 255, 255, 0.9);
            border-radius: 22px;
            border: var(--card-border);
            box-shadow: var(--card-shadow);
            padding: clamp(1.75rem, 4vw, 2.5rem);
        }

        .classroom-grid {
            display: grid;
            grid-template-columns: minmax(0, 1.05fr) minmax(0, 0.95fr);
            gap: clamp(1.5rem, 3vw, 2rem);
            align-items: start;
        }

        .login-panel {
            padding: 1.25rem;
            border-radius: 16px;
            border: 1px solid rgba(148, 163, 184, 0.18);
            background: rgba(248, 250, 252, 0.85);
            backdrop-filter: blur(16px);
        }

        .login-panel form {
            display: grid;
            gap: 0.9rem;
        }

        .login-panel .message {
            min-height: 1.4rem;
        }

        .footer {
            padding: 2.25rem 0;
            color: #475569;
            font-size: 0.95rem;
        }

        @media (max-width: 960px) {
            .hero-grid,
            .classroom-grid {
                grid-template-columns: 1fr;
            }

            .site-nav {
                position: static;
            }
        }
    </style>
</head>
<body class="home">
<div class="site-shell">
    <nav class="site-nav">
        <div class="container-xxl py-3 d-flex align-items-center justify-content-between gap-3">
            <div class="nav-brand">
                <span class="brand-mark">RL</span>
                <div>
                    <div class="small text-uppercase text-secondary">Rare Light</div>
                    <div class="fw-bold">稀光行动 · 罕见病儿童公益</div>
                </div>
            </div>
            <div class="d-flex align-items-center gap-2 flex-wrap">
                <a class="ghost-button" href="#programs">了解项目</a>
                <a class="primary-button" id="classroomCta" data-classroom-link href="#classroom">进入网课</a>
            </div>
        </div>
    </nav>

    <main>
        <section class="hero">
            <div class="container-xxl">
                <div class="hero-grid">
                    <div class="hero-copy">
                        <span class="brand-eyebrow">Rare Light · 点亮希望</span>
                        <h1>为罕见病儿童照亮疗愈与学习的道路</h1>
                        <p class="lead">我们陪伴罕见病患儿及其家庭，连结医疗资源、学习支持与心理关怀，让每一份努力都化作抵达未来的微光。</p>
                        <div class="hero-actions">
                            <a class="primary-button" data-classroom-link href="#classroom">进入网课</a>
                            <a class="ghost-button" href="#contact">与我们合作</a>
                        </div>
                        <div class="session-note" id="sessionNote">课堂由专业志愿者维护，登录后即可继续学习。</div>
                    </div>
                    <div class="hero-card">
                        <p class="eyebrow mb-2">我们在做的事</p>
                        <h3 class="fw-bold mb-3">陪伴治疗、守护学习、连接社会资源</h3>
                        <p class="text-secondary mb-3">Rare Light 通过家庭陪伴、线上课堂与公益伙伴网络，为罕见病儿童提供持续支持。</p>
                        <div class="stat-grid">
                            <div class="stat">
                                <strong>40+ 城市</strong>
                                <span class="text-secondary">志愿者和合作社群覆盖</span>
                            </div>
                            <div class="stat">
                                <strong>每周课堂</strong>
                                <span class="text-secondary">定期录播与直播结合</span>
                            </div>
                            <div class="stat">
                                <strong>家庭关怀</strong>
                                <span class="text-secondary">陪伴、心理与转介支持</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section class="section" id="programs">
            <div class="container-xxl">
                <header>
                    <p class="eyebrow mb-1">我们的行动</p>
                    <h2>Rare Light 如何提供帮助</h2>
                </header>
                <div class="feature-grid">
                    <div class="feature-card">
                        <span class="feature-icon">A</span>
                        <h4 class="fw-bold mb-1">陪伴与同侪支持</h4>
                        <p class="text-secondary mb-0">志愿者与家长伙伴组成陪伴小组，回应情绪、学习与资源需求。</p>
                    </div>
                    <div class="feature-card">
                        <span class="feature-icon">B</span>
                        <h4 class="fw-bold mb-1">线上课堂 · Rare Light School</h4>
                        <p class="text-secondary mb-0">面向患儿的专属课堂，提供长期、可重复观看的录播课程与直播互动。</p>
                    </div>
                    <div class="feature-card">
                        <span class="feature-icon">C</span>
                        <h4 class="fw-bold mb-1">医疗与社会资源链接</h4>
                        <p class="text-secondary mb-0">携手医院、公益组织与企业，为家庭争取医疗、康复及生活支持。</p>
                    </div>
                    <div class="feature-card">
                        <span class="feature-icon">D</span>
                        <h4 class="fw-bold mb-1">家庭韧性工作坊</h4>
                        <p class="text-secondary mb-0">通过小组训练与工具包，帮助家长获得自助与互助的方法。</p>
                    </div>
                </div>
                <div class="cta-banner mt-4">
                    <div>
                        <div class="fw-bold fs-5">想加入我们？</div>
                        <div>欢迎医疗机构、教育机构与企业伙伴共建罕见病儿童支持网络。</div>
                    </div>
                    <a class="ghost-button cta-button" href="#contact">成为合作伙伴 ↗</a>
                </div>
            </div>
        </section>

        <section class="section">
            <div class="container-xxl">
                <header>
                    <p class="eyebrow mb-1">陪伴路径</p>
                    <h2>从需求到陪伴，我们这样行动</h2>
                </header>
                <div class="pathway">
                    <div class="pathway-step">
                        <h5 class="fw-bold">1. 了解家庭需求</h5>
                        <p class="text-secondary mb-0">与家长沟通病情、教育与心理需求，建立一对一档案。</p>
                    </div>
                    <div class="pathway-step">
                        <h5 class="fw-bold">2. 匹配资源与课堂</h5>
                        <p class="text-secondary mb-0">为孩子安排合适的线上课程与志愿者陪伴，并链接医疗与社会资源。</p>
                    </div>
                    <div class="pathway-step">
                        <h5 class="fw-bold">3. 持续跟进与评估</h5>
                        <p class="text-secondary mb-0">定期回访，评估课堂效果与家庭需求，调整支持方案。</p>
                    </div>
                </div>
            </div>
        </section>

        <section class="section">
            <div class="container-xxl">
                <div class="classroom" id="classroom">
                    <div class="classroom-grid">
                        <div>
                            <p class="eyebrow mb-1">Rare Light School</p>
                            <h3 class="fw-bold">网课入口</h3>
                            <p class="text-secondary">患儿与家长可在此登录进入课堂；已分配课程的用户将直接跳转至学习页面。</p>
                            <ul class="list-unstyled text-secondary mb-3">
                                <li class="mb-1">· 录播与外部视频均可播放</li>
                                <li class="mb-1">· 支持按课程分配和进度查看</li>
                                <li class="mb-1">· 志愿者与管理员可在后台维护内容</li>
                            </ul>
                            <div class="d-flex flex-wrap gap-2">
                                <a class="primary-button" data-classroom-link href="#classroom">立即进入网课</a>
                                <a class="ghost-button" href="admin">管理员入口</a>
                            </div>
                        </div>
                        <div class="login-panel">
                            <h4 class="fw-bold mb-2">课堂登录</h4>
                            <p class="text-secondary mb-3">请输入管理员分配的账号登录。如果您尚未拥有账户，请联系我们的协调员。</p>
                            <form id="loginForm" autocomplete="on">
                                <div>
                                    <label for="username">用户名</label>
                                    <input id="username" name="username" type="text" placeholder="student01" required>
                                </div>
                                <div>
                                    <label for="password">密码</label>
                                    <input id="password" name="password" type="password" placeholder="请输入密码" required>
                                </div>
                                <button type="submit" class="primary-button w-100" id="loginButton">登录并进入课堂</button>
                                <div class="message login-message" id="loginMessage" aria-live="polite" hidden></div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section class="section" id="contact">
            <div class="container-xxl">
                <header>
                    <p class="eyebrow mb-1">联系与合作</p>
                    <h2>一起为罕见病儿童点亮更多光</h2>
                </header>
                <div class="feature-grid">
                    <div class="feature-card">
                        <h5 class="fw-bold mb-1">成为志愿者</h5>
                        <p class="text-secondary mb-0">加入陪伴小组、教学与运营支持团队。</p>
                    </div>
                    <div class="feature-card">
                        <h5 class="fw-bold mb-1">机构合作</h5>
                        <p class="text-secondary mb-0">医疗、教育、公益、企业伙伴欢迎联系共建项目。</p>
                    </div>
                    <div class="feature-card">
                        <h5 class="fw-bold mb-1">家庭申请</h5>
                        <p class="text-secondary mb-0">罕见病患儿家庭可申请课堂访问与陪伴支持。</p>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <footer class="footer">
        <div class="container-xxl d-flex flex-wrap justify-content-between gap-2">
            <div>Rare Light · 稀光行动 — 为罕见病儿童提供陪伴、教育与资源链接。</div>
            <div class="text-secondary">联系邮箱：hello@rarelight.org</div>
        </div>
    </footer>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
    const API_BASE = 'api';
    const classroomLinks = document.querySelectorAll('[data-classroom-link]');
    const sessionNote = document.getElementById('sessionNote');
    const loginForm = document.getElementById('loginForm');
    const loginButton = document.getElementById('loginButton');
    const loginMessage = document.getElementById('loginMessage');

    function normalizeApiUrl(url) {
        if (url.startsWith(`${API_BASE}/`)) {
            const [path, query] = url.split('?');
            const sanitizedPath = path.replace(/\/{2,}/g, '/');
            return query ? `${sanitizedPath}?${query}` : sanitizedPath;
        }
        return url;
    }

    function updateClassroomLinks(target, text) {
        classroomLinks.forEach((link) => {
            link.href = target;
            if (text) {
                link.textContent = text;
            }
        });
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
                const name = data.user.display_name || data.user.username;
                updateClassroomLinks('dashboard', '进入我的课堂');
                sessionNote.textContent = `欢迎回来，${name}。点击即可直接进入课堂继续学习。`;
                loginForm.querySelectorAll('input, button').forEach((element) => {
                    element.disabled = true;
                });
                showMessage('您已登录，可直接进入课堂。', 'success');
            } else {
                updateClassroomLinks('#classroom', '进入网课');
                sessionNote.textContent = '课堂由专业志愿者维护，登录后即可继续学习。';
            }
        } catch (error) {
            sessionNote.textContent = '课堂由专业志愿者维护，登录后即可继续学习。';
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
            }, 350);
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
