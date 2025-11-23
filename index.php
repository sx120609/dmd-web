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
            justify-content: center;
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

        .primary-button,
        .ghost-button {
            text-align: center;
            justify-content: center;
        }

        .nav-actions {
            display: flex;
            gap: 0.5rem;
            align-items: center;
            flex-wrap: wrap;
        }

        .lang-toggle,
        .font-toggle {
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            padding: 0.4rem 0.75rem;
            border-radius: 999px;
            border: 1px solid rgba(15, 23, 42, 0.12);
            background: rgba(255, 255, 255, 0.85);
            font-weight: 600;
            color: #0f172a;
        }

        .font-toggle button {
            border: none;
            background: transparent;
            padding: 0.2rem 0.45rem;
            border-radius: 8px;
            font-weight: 700;
            color: #1e293b;
        }

        .font-toggle button:hover,
        .lang-toggle:hover {
            background: rgba(79, 70, 229, 0.08);
        }

        .font-toggle button:disabled {
            opacity: 0.5;
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

        .qr-section {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 1.2rem;
            align-items: center;
        }

        .qr-card {
            border-radius: 16px;
            background: rgba(255, 255, 255, 0.92);
            border: var(--card-border);
            box-shadow: var(--card-shadow);
            padding: 1.5rem;
            display: flex;
            gap: 1rem;
            align-items: center;
        }

        .qr-card .qr-image {
            width: 180px;
            height: 180px;
            border-radius: 12px;
            border: 1px solid rgba(148, 163, 184, 0.25);
            object-fit: cover;
            background: #f8fafc;
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
            text-align: center;
            justify-content: center;
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
            scroll-margin-top: 120px;
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
                padding-inline: 1rem;
            }

            .hero {
                padding-top: 2rem;
            }

            .hero-actions {
                width: 100%;
                justify-content: flex-start;
            }

            .primary-button,
            .ghost-button {
                width: 100%;
                text-align: center;
                justify-content: center;
            }

            .feature-grid {
                grid-template-columns: 1fr;
            }

            .pathway {
                grid-template-columns: 1fr;
            }

            .qr-card {
                flex-direction: column;
                align-items: flex-start;
            }

            .nav-actions {
                display: none;
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
                    <div class="small text-uppercase text-secondary" data-i18n="brandTagline">Rare Light</div>
                    <div class="fw-bold" data-i18n="brandTitle">Rare Light · 罕见病儿童公益</div>
                </div>
            </div>
            <div class="nav-actions">
                <div class="font-toggle" aria-label="Font size controls">
                    <span data-i18n="fontLabel">A</span>
                    <button type="button" id="fontSmaller" aria-label="Smaller font">A-</button>
                    <button type="button" id="fontReset" aria-label="Default font">A</button>
                    <button type="button" id="fontLarger" aria-label="Larger font">A+</button>
                </div>
                <button class="lang-toggle" type="button" id="langToggle" aria-label="Language toggle">EN / 中文</button>
            </div>
        </div>
    </nav>

    <main>
        <section class="hero">
            <div class="container-xxl">
                <div class="hero-grid">
                    <div class="hero-copy">
                        <span class="brand-eyebrow" data-i18n="heroEyebrow">Rare Light · 点亮希望</span>
                        <h1 data-i18n="heroTitle">“线上趣味课 + 线下科普行” 双轨陪伴罕见病儿童</h1>
                        <p class="lead" data-i18n="heroLead">RARE LIGHT 罕见病关爱项目，面向患儿与家庭同步提供线上趣味课堂与线下科普关怀，兼顾成长需求与社会认知，打造专业且温暖的公益服务体系。</p>
                        <div class="hero-actions">
                            <a class="primary-button" data-classroom-link href="#classroom" data-i18n="ctaEnter">进入网课</a>
                            <a class="ghost-button" href="#contact" data-i18n="ctaPartner">与我们合作</a>
                        </div>
                        <div class="session-note" id="sessionNote" data-i18n="sessionNote">课堂由专业志愿者维护，登录后即可继续学习。</div>
                    </div>
                    <div class="hero-card">
                        <p class="eyebrow mb-2" data-i18n="cardEyebrow">项目全景</p>
                        <h3 class="fw-bold mb-3" data-i18n="cardTitle">线上课堂迭代五期 · 线下科普深入社区与校园</h3>
                        <p class="text-secondary mb-3" data-i18n="cardDesc">教师梯队与服务规模同步壮大，课堂覆盖语文、数学、英语、科学、地理、历史、AI 等学科，累计近千课时，陪伴 30+ 罕见病患儿家庭。线下科普行走进医院、校园与社区，媒体报道与实践成果持续放大影响。</p>
                        <div class="stat-grid">
                            <div class="stat">
                                <strong>5 期课堂</strong>
                                <span class="text-secondary">近千课时，覆盖多学科</span>
                            </div>
                            <div class="stat">
                                <strong>70 位志愿者</strong>
                                <span class="text-secondary">教师梯队持续壮大</span>
                            </div>
                            <div class="stat">
                                <strong>30+ 家庭</strong>
                                <span class="text-secondary">5 年持续陪伴</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section class="section" id="programs">
            <div class="container-xxl">
                <header>
                    <p class="eyebrow mb-1" data-i18n="sectionActionEyebrow">我们的行动</p>
                    <h2 data-i18n="sectionActionTitle">线上趣味课 + 线下科普行 · 双轨服务模式</h2>
                </header>
                <div class="feature-grid">
                    <div class="feature-card">
                        <span class="feature-icon">A</span>
                        <h4 class="fw-bold mb-1" data-i18n="featureA">线上趣味课堂</h4>
                        <p class="text-secondary mb-0" data-i18n="featureAText">中国药科大学志愿者面向患儿开展系列教学，从英语拓展到语数外、科学、地理、历史、AI，多学科陪伴成长。</p>
                    </div>
                    <div class="feature-card">
                        <span class="feature-icon">B</span>
                        <h4 class="fw-bold mb-1" data-i18n="featureB">线下科普关怀</h4>
                        <p class="text-secondary mb-0" data-i18n="featureBText">“罕见病患儿进校园”“罕爱童行・药语同航”等活动走进校园、医院与社区，让公众看见、理解并支持罕见病家庭。</p>
                    </div>
                    <div class="feature-card">
                        <span class="feature-icon">C</span>
                        <h4 class="fw-bold mb-1" data-i18n="featureC">科普传播与倡导</h4>
                        <p class="text-secondary mb-0" data-i18n="featureCText">媒体报道、公众号“因你罕见，我药看见”持续输出诊疗指南、药物政策与真实故事，为家庭提供权威信息服务。</p>
                    </div>
                    <div class="feature-card">
                        <span class="feature-icon">D</span>
                        <h4 class="fw-bold mb-1" data-i18n="featureD">志愿者与科研支撑</h4>
                        <p class="text-secondary mb-0" data-i18n="featureDText">70 人志愿者队伍与调研报告双线推进，沉淀实证数据，为服务迭代和政策倡导提供依据。</p>
                    </div>
                </div>
                <div class="cta-banner mt-4">
                    <div>
                        <div class="fw-bold fs-5" data-i18n="ctaJoinTitle">想加入我们？</div>
                        <div data-i18n="ctaJoinDesc">欢迎医疗机构、教育机构与企业伙伴共建罕见病儿童支持网络。</div>
                    </div>
                    <a class="ghost-button cta-button" href="#contact" data-i18n="ctaJoinBtn">成为合作伙伴 ↗</a>
                </div>
            </div>
        </section>

        <section class="section">
            <div class="container-xxl">
                <div class="qr-section">
                    <div class="qr-card">
                        <img src="assets/img/wechat-qr.png" alt="微信扫码关注 Rare Light 公众号" class="qr-image">
                        <div>
                            <p class="eyebrow mb-2" data-i18n="qrTitle">关注 Rare Light 公众号</p>
                            <h3 class="fw-bold mb-2" data-i18n="qrSubtitle">微信扫码，获取最新课程与科普动态</h3>
                            <p class="text-secondary mb-0" data-i18n="qrDesc">用微信扫描二维码即可关注，第一时间了解课程安排、线下活动、科普信息与政策更新。</p>
                        </div>
                    </div>
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
                            <p class="eyebrow mb-1" data-i18n="classroomEyebrow">Rare Light School</p>
                            <h3 class="fw-bold" data-i18n="classroomTitle">网课入口</h3>
                            <p class="text-secondary" data-i18n="classroomDesc">患儿与家长可在此登录进入课堂；已分配课程的用户将直接跳转至学习页面。</p>
                            <div class="d-flex flex-wrap gap-2">
                                <a class="primary-button" data-classroom-link href="#classroom">立即进入网课</a>
                            </div>
                        </div>
                        <div class="login-panel">
                            <h4 class="fw-bold mb-2" data-i18n="loginTitle">课堂登录</h4>
                            <p class="text-secondary mb-3" data-i18n="loginDesc">请输入管理员分配的账号登录。如果您尚未拥有账户，请联系我们的协调员。</p>
                            <form id="loginForm" autocomplete="on">
                                <div>
                                    <label for="username" data-i18n="loginUsername">用户名</label>
                                    <input id="username" name="username" type="text" placeholder="student01" required>
                                </div>
                                <div>
                                    <label for="password" data-i18n="loginPassword">密码</label>
                                    <input id="password" name="password" type="password" placeholder="请输入密码" required>
                                </div>
                                <button type="submit" class="primary-button w-100" id="loginButton" data-i18n="loginButton">登录并进入课堂</button>
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
                    <p class="eyebrow mb-1" data-i18n="contactEyebrow">联系与合作</p>
                    <h2 data-i18n="contactTitle">一起为罕见病儿童点亮更多光</h2>
                </header>
                <div class="feature-grid">
                    <div class="feature-card">
                        <h5 class="fw-bold mb-1" data-i18n="contactVolunteer">成为志愿者</h5>
                        <p class="text-secondary mb-0" data-i18n="contactVolunteerDesc">加入陪伴小组、教学与运营支持团队。</p>
                    </div>
                    <div class="feature-card">
                        <h5 class="fw-bold mb-1" data-i18n="contactPartner">机构合作</h5>
                        <p class="text-secondary mb-0" data-i18n="contactPartnerDesc">医疗、教育、公益、企业伙伴欢迎联系共建项目。</p>
                    </div>
                    <div class="feature-card">
                        <h5 class="fw-bold mb-1" data-i18n="contactFamily">家庭申请</h5>
                        <p class="text-secondary mb-0" data-i18n="contactFamilyDesc">罕见病患儿家庭可申请课堂访问与陪伴支持。</p>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <footer class="footer">
        <div class="container-xxl d-flex flex-wrap justify-content-between gap-2">
            <div>Rare Light — 为罕见病儿童提供陪伴、教育与资源链接。</div>
            <div class="text-secondary">联系邮箱：hello@rarelight.org</div>
        </div>
    </footer>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
    const API_BASE = 'api';
    const FONT_KEY = 'rl_font_scale';
    const LANG_KEY = 'rl_lang';
    const i18n = {
        zh: {
            brandTagline: 'Rare Light',
            brandTitle: 'Rare Light · 罕见病儿童公益',
            navAbout: '关于我们',
            navNews: '新闻',
            navResources: '资源与工具',
            navCourses: '在线课程',
            navLogin: '进入网课',
            fontLabel: '字号',
            heroEyebrow: 'Rare Light · 点亮希望',
            heroTitle: '“线上趣味课 + 线下科普行” 双轨陪伴罕见病儿童',
            heroLead: 'RARE LIGHT 罕见病关爱项目，面向患儿与家庭同步提供线上趣味课堂与线下科普关怀，兼顾成长需求与社会认知，打造专业且温暖的公益服务体系。',
            ctaEnter: '进入网课',
            ctaPartner: '与我们合作',
            sessionNote: '课堂由专业志愿者维护，登录后即可继续学习。',
            cardEyebrow: '项目全景',
            cardTitle: '线上课堂迭代五期 · 线下科普深入社区与校园',
            cardDesc: '教师梯队与服务规模同步壮大，课堂覆盖语文、数学、英语、科学、地理、历史、AI 等学科，累计近千课时，陪伴 30+ 罕见病患儿家庭。线下科普行走进医院、校园与社区，媒体报道与实践成果持续放大影响。',
            sectionActionEyebrow: '我们的行动',
            sectionActionTitle: '线上趣味课 + 线下科普行 · 双轨服务模式',
            featureA: '线上趣味课堂',
            featureAText: '中国药科大学志愿者面向患儿开展系列教学，从英语拓展到语数外、科学、地理、历史、AI，多学科陪伴成长。',
            featureB: '线下科普关怀',
            featureBText: '“罕见病患儿进校园”“罕爱童行・药语同航”等活动走进校园、医院与社区，让公众看见、理解并支持罕见病家庭。',
            featureC: '科普传播与倡导',
            featureCText: '媒体报道、公众号“因你罕见，我药看见”持续输出诊疗指南、药物政策与真实故事，为家庭提供权威信息服务。',
            featureD: '志愿者与科研支撑',
            featureDText: '70 人志愿者队伍与调研报告双线推进，沉淀实证数据，为服务迭代和政策倡导提供依据。',
            ctaJoinTitle: '想加入我们？',
            ctaJoinDesc: '欢迎医疗机构、教育机构与企业伙伴共建罕见病儿童支持网络。',
            ctaJoinBtn: '成为合作伙伴 ↗',
            qrTitle: '关注 Rare Light 公众号',
            qrSubtitle: '微信扫码，获取最新课程与科普动态',
            qrDesc: '用微信扫描二维码即可关注，第一时间了解课程安排、线下活动、科普信息与政策更新。',
            classroomEyebrow: 'Rare Light School',
            classroomTitle: '网课入口',
            classroomDesc: '患儿与家长可在此登录进入课堂；已分配课程的用户将直接跳转至学习页面。',
            loginTitle: '课堂登录',
            loginDesc: '请输入管理员分配的账号登录。如果您尚未拥有账户，请联系我们的协调员。',
            loginUsername: '用户名',
            loginPassword: '密码',
            loginButton: '登录并进入课堂',
            contactEyebrow: '联系与合作',
            contactTitle: '一起为罕见病儿童点亮更多光',
            contactVolunteer: '成为志愿者',
            contactVolunteerDesc: '加入陪伴小组、教学与运营支持团队。',
            contactPartner: '机构合作',
            contactPartnerDesc: '医疗、教育、公益、企业伙伴欢迎联系共建项目。',
            contactFamily: '家庭申请',
            contactFamilyDesc: '罕见病患儿家庭可申请课堂访问与陪伴支持。'
        },
        en: {
            brandTagline: 'Rare Light',
            brandTitle: 'Rare Light · Rare Disease Care',
            navAbout: 'About Us',
            navNews: 'News',
            navResources: 'Resources & Tools',
            navCourses: 'Online Courses',
            navLogin: 'Enter Classroom',
            fontLabel: 'Font',
            heroEyebrow: 'Rare Light · Hope',
            heroTitle: 'Hybrid “Online Fun Courses + Offline Sci-pop” for Rare Disease Children',
            heroLead: 'Rare Light provides online fun classes and offline outreach for children and families, blending growth support with social awareness in a warm, professional way.',
            ctaEnter: 'Enter Classroom',
            ctaPartner: 'Partner with Us',
            sessionNote: 'Log in to continue learning; classes are maintained by volunteers.',
            cardEyebrow: 'Project Snapshot',
            cardTitle: 'Five phases of online classes · Offline outreach to campuses and communities',
            cardDesc: 'Expanding subjects across languages, STEM, humanities, and AI with nearly 1,000 class hours, serving 30+ families. Offline events bring visibility and support through hospitals, campuses, and media.',
            sectionActionEyebrow: 'What We Do',
            sectionActionTitle: 'Online Fun Courses + Offline Sci-pop',
            featureA: 'Online Fun Classes',
            featureAText: 'Student volunteers teach across English, Math, Science, Geography, History, and AI, accompanying rare disease children in learning.',
            featureB: 'Offline Outreach',
            featureBText: 'Campus visits and hospital/community events raise awareness and support for rare disease families.',
            featureC: 'Sci-pop & Advocacy',
            featureCText: 'We publish guides, policy updates, and real stories via media and the “Rare Light” WeChat account.',
            featureD: 'Volunteers & Research',
            featureDText: 'A 70-person volunteer team plus research to inform service iteration and advocacy.',
            ctaJoinTitle: 'Join Us',
            ctaJoinDesc: 'Hospitals, schools, and corporate partners are welcome to build the support network together.',
            ctaJoinBtn: 'Become a Partner ↗',
            qrTitle: 'Follow Rare Light on WeChat',
            qrSubtitle: 'Scan to get course updates & news',
            qrDesc: 'Scan the QR code to follow and receive course schedules, events, and policy updates.',
            classroomEyebrow: 'Rare Light School',
            classroomTitle: 'Classroom Access',
            classroomDesc: 'Children and parents log in here; assigned users jump directly to learning.',
            loginTitle: 'Classroom Login',
            loginDesc: 'Use the account assigned by administrators. If you need access, contact our coordinator.',
            loginUsername: 'Username',
            loginPassword: 'Password',
            loginButton: 'Log in & Enter Classroom',
            contactEyebrow: 'Contact & Partnership',
            contactTitle: 'Light up more hope for rare disease children together',
            contactVolunteer: 'Become a Volunteer',
            contactVolunteerDesc: 'Join companion groups, teaching, or operations support teams.',
            contactPartner: 'Institutional Partnership',
            contactPartnerDesc: 'Hospitals, schools, NGOs, and companies are welcome to co-build the program.',
            contactFamily: 'Family Application',
            contactFamilyDesc: 'Rare disease families can apply for classroom access and companion support.'
        }
    };

    const htmlEl = document.documentElement;
    const fontSmallerBtn = document.getElementById('fontSmaller');
    const fontResetBtn = document.getElementById('fontReset');
    const fontLargerBtn = document.getElementById('fontLarger');
    const langToggle = document.getElementById('langToggle');
    let currentFontScale = 1;
    let currentLang = localStorage.getItem(LANG_KEY) || 'zh';
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

    function applyFontScale(scale) {
        currentFontScale = Math.min(1.3, Math.max(0.85, scale));
        htmlEl.style.setProperty('--font-base', '16px');
        htmlEl.style.fontSize = `calc(var(--font-base) * ${currentFontScale})`;
        localStorage.setItem(FONT_KEY, String(currentFontScale));
    }

    function initFontControls() {
        const saved = parseFloat(localStorage.getItem(FONT_KEY) || '1');
        if (!Number.isNaN(saved)) {
            applyFontScale(saved);
        }
        if (fontSmallerBtn) {
            fontSmallerBtn.addEventListener('click', () => applyFontScale(currentFontScale - 0.05));
        }
        if (fontResetBtn) {
            fontResetBtn.addEventListener('click', () => applyFontScale(1));
        }
        if (fontLargerBtn) {
            fontLargerBtn.addEventListener('click', () => applyFontScale(currentFontScale + 0.05));
        }
    }

    function applyTranslations(lang) {
        const dict = i18n[lang] || i18n.zh;
        document.querySelectorAll('[data-i18n]').forEach((el) => {
            const key = el.dataset.i18n;
            if (!key || !(key in dict)) return;
            if (el.tagName === 'INPUT' || el.tagName === 'TEXTAREA') {
                el.setAttribute('placeholder', dict[key]);
            } else {
                el.textContent = dict[key];
            }
        });
        currentLang = lang;
        localStorage.setItem(LANG_KEY, currentLang);
        if (langToggle) {
            langToggle.textContent = lang === 'zh' ? '中文 / EN' : 'EN / 中文';
        }
    }

    function initLangToggle() {
        applyTranslations(currentLang);
        if (langToggle) {
            langToggle.addEventListener('click', () => {
                const next = currentLang === 'zh' ? 'en' : 'zh';
                applyTranslations(next);
            });
        }
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
                updateClassroomLinks('dashboard', i18n[currentLang].navLogin || '进入我的课堂');
                sessionNote.textContent = `${i18n[currentLang].sessionNote || '课堂由专业志愿者维护，登录后即可继续学习。'} 欢迎回来，${name}。`;
                loginForm.querySelectorAll('input, button').forEach((element) => {
                    element.disabled = true;
                });
                showMessage(currentLang === 'zh' ? '您已登录，可直接进入课堂。' : 'You are logged in. Enter the classroom directly.', 'success');
            } else {
                updateClassroomLinks('#classroom', i18n[currentLang].navLogin || '进入网课');
                sessionNote.textContent = i18n[currentLang].sessionNote || '课堂由专业志愿者维护，登录后即可继续学习。';
            }
        } catch (error) {
            sessionNote.textContent = i18n[currentLang].sessionNote || '课堂由专业志愿者维护，登录后即可继续学习。';
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

    initFontControls();
    initLangToggle();
    checkSession();
</script>
</body>
</html>
