<!DOCTYPE html>
<html lang="zh-CN">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title data-i18n="pageTitle">Rare Light · 点亮罕见病儿童的希望</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Plus+Jakarta+Sans:wght@400;500;600;700&family=Noto+Sans+SC:wght@400;500;700&display=swap"
        rel="stylesheet">

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <style>
        :root {
            /* === 统一色盘 (与日志页一致) === */
            --rl-bg: #f8fafc;
            --rl-text-main: #0f172a;
            --rl-text-muted: #64748b;
            --rl-primary: #3b82f6;
            --rl-accent: #8b5cf6;

            /* 原始Logo所需的渐变 (保留) */
            --deep-gradient: linear-gradient(135deg, #2563eb, #60a5fa, #22d3ee);

            /* 新版背景渐变 */
            --gradient-glow: radial-gradient(circle at 50% 0%, rgba(59, 130, 246, 0.15), rgba(139, 92, 246, 0.05), transparent 70%);

            /* 卡片样式 */
            --card-bg: rgba(255, 255, 255, 0.85);
            --card-border: 1px solid rgba(255, 255, 255, 0.6);
            --card-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.02), 0 2px 4px -1px rgba(0, 0, 0, 0.02);
            --card-shadow-hover: 0 20px 25px -5px rgba(0, 0, 0, 0.05), 0 8px 10px -6px rgba(0, 0, 0, 0.01);

            /* 字体基准变量 */
            --font-base: 16px;
        }

        body.home {
            font-family: 'Plus Jakarta Sans', 'Noto Sans SC', system-ui, sans-serif;
            background-color: var(--rl-bg);
            background-image: var(--gradient-glow);
            background-attachment: fixed;
            background-size: 100% 100vh;
            background-repeat: no-repeat;
            color: var(--rl-text-main);
            min-height: 100vh;
            font-size: var(--font-base);
            /* 支持字号缩放 */
            -webkit-font-smoothing: antialiased;
        }

        .site-shell {
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }

        /* --- 统一导航栏样式 --- */
        .site-nav {
            position: sticky;
            top: 0;
            z-index: 100;
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            background: rgba(255, 255, 255, 0.75);
            border-bottom: 1px solid rgba(255, 255, 255, 0.5);
            padding: 0.75rem 0;
            transition: all 0.3s ease;
        }

        /* Logo 样式 (严格保留原样) */
        .nav-brand {
            display: inline-flex;
            align-items: center;
            gap: 0.75rem;
            font-family: 'Inter', sans-serif;
            text-decoration: none;
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
            font-size: 1.1rem;
        }

        .brand-text .small {
            font-size: 0.75rem;
            letter-spacing: 0.05em;
            color: #64748b;
            font-weight: 600;
        }

        .brand-text .fw-bold {
            font-weight: 700 !important;
            color: #0f172a;
            font-size: 1.05rem;
            letter-spacing: -0.01em;
        }

        /* 导航右侧按钮组 */
        .nav-actions {
            display: flex;
            gap: 0.5rem;
            align-items: center;
        }

        /* 统一按钮基础样式 */
        .nav-btn {
            padding: 0.45rem 1rem;
            border-radius: 10px;
            font-size: 0.9rem;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.2s;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            border: 1px solid transparent;
        }

        /* 幽灵按钮 (用于次要链接) */
        .nav-btn-ghost {
            color: var(--rl-text-muted);
            background: transparent;
        }

        .nav-btn-ghost:hover {
            color: var(--rl-text-main);
            background: rgba(0, 0, 0, 0.04);
        }

        /* 主要按钮 (CTA) */
        .nav-btn-primary {
            background: var(--rl-text-main);
            color: white;
            box-shadow: 0 4px 12px rgba(15, 23, 42, 0.15);
        }

        .nav-btn-primary:hover {
            transform: translateY(-1px);
            background: #1e293b;
            color: white;
        }

        /* 工具条样式 (字号/语言) */
        .tool-bar {
            display: flex;
            align-items: center;
            gap: 0.25rem;
            background: rgba(255, 255, 255, 0.5);
            border: 1px solid rgba(0, 0, 0, 0.05);
            padding: 4px;
            border-radius: 10px;
            margin-right: 0.5rem;
        }

        .tool-btn {
            border: none;
            background: transparent;
            color: var(--rl-text-muted);
            font-size: 0.85rem;
            font-weight: 600;
            padding: 4px 8px;
            border-radius: 6px;
            transition: all 0.2s;
        }

        .tool-btn:hover {
            background: rgba(0, 0, 0, 0.05);
            color: var(--rl-text-main);
        }

        .tool-btn:active {
            transform: scale(0.95);
        }

        /* --- Hero 区域微调 --- */
        .hero {
            padding: clamp(3rem, 6vw, 5rem) 0;
            position: relative;
        }

        .hero h1 {
            font-size: clamp(2.4rem, 4vw, 3.6rem);
            font-weight: 800;
            letter-spacing: -0.02em;
            line-height: 1.15;
            margin: 1rem 0 1.25rem;
            background: linear-gradient(135deg, #1e293b 0%, #475569 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .hero p.lead {
            font-size: 1.1rem;
            color: var(--rl-text-muted);
            line-height: 1.8;
            margin-bottom: 2rem;
        }

        .brand-eyebrow {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 6px 14px;
            background: rgba(59, 130, 246, 0.1);
            color: var(--rl-primary);
            border-radius: 99px;
            font-size: 0.85rem;
            font-weight: 600;
            letter-spacing: 0.03em;
            text-transform: uppercase;
        }

        /* Hero Card (玻璃拟态增强) */
        .hero-card {
            background: rgba(255, 255, 255, 0.65);
            backdrop-filter: blur(12px);
            border-radius: 24px;
            padding: clamp(1.6rem, 2vw, 2.5rem);
            border: var(--card-border);
            box-shadow: var(--card-shadow);
            position: relative;
            overflow: hidden;
        }

        /* 统计数据块 */
        .stat {
            background: white;
            border-radius: 16px;
            padding: 1.2rem;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.02);
            border: 1px solid rgba(0, 0, 0, 0.03);
            transition: transform 0.2s;
        }

        .stat:hover {
            transform: translateY(-2px);
        }

        .stat strong {
            font-size: 1.5rem;
            color: var(--rl-text-main);
            font-weight: 800;
        }

        /* --- 通用 Section 样式 --- */
        .section {
            padding: clamp(3rem, 6vw, 5rem) 0;
        }

        .eyebrow {
            font-weight: 700;
            color: var(--rl-primary);
            letter-spacing: 0.05em;
            text-transform: uppercase;
            font-size: 0.9rem;
        }

        /* Feature Cards */
        .feature-card {
            background: var(--card-bg);
            border: var(--card-border);
            border-radius: 16px;
            padding: 1.5rem;
            box-shadow: var(--card-shadow);
            transition: all 0.3s ease;
        }

        .feature-card:hover {
            transform: translateY(-4px);
            background: white;
            box-shadow: var(--card-shadow-hover);
            border-color: rgba(59, 130, 246, 0.2);
        }

        .feature-icon {
            width: 48px;
            height: 48px;
            border-radius: 12px;
            background: var(--deep-gradient);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 700;
            font-size: 1.2rem;
            margin-bottom: 1rem;
            box-shadow: 0 8px 16px rgba(59, 130, 246, 0.2);
        }

        /* Login Panel */
        .login-panel {
            padding: 2rem;
            border-radius: 20px;
            border: var(--card-border);
            background: white;
            box-shadow: var(--card-shadow);
        }

        .login-panel input {
            width: 100%;
            padding: 0.75rem 1rem;
            border-radius: 10px;
            border: 1px solid #e2e8f0;
            background: #f8fafc;
            margin-bottom: 1rem;
            transition: all 0.2s;
        }

        .login-panel input:focus {
            outline: none;
            border-color: var(--rl-primary);
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
            background: white;
        }

        .login-panel label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 600;
            font-size: 0.9rem;
            color: var(--rl-text-main);
        }

        /* CTA Banner */
        .cta-banner {
            background: var(--deep-gradient);
            border-radius: 20px;
            padding: 2.5rem;
            color: white;
            position: relative;
            overflow: hidden;
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        /* Reveal Animation */
        .reveal {
            opacity: 0;
            transform: translateY(20px);
            transition: opacity 0.6s ease, transform 0.6s ease;
        }

        .reveal.visible {
            opacity: 1;
            transform: translateY(0);
        }

        .footer {
            padding: 3rem 0;
            color: var(--rl-text-muted);
            border-top: 1px solid rgba(0, 0, 0, 0.05);
            background: white;
        }

        @media (max-width: 992px) {

            .hero-grid,
            .classroom-grid {
                grid-template-columns: 1fr;
            }

            .nav-actions {
                position: fixed;
                bottom: 0;
                left: 0;
                width: 100%;
                background: rgba(255, 255, 255, 0.9);
                backdrop-filter: blur(20px);
                -webkit-backdrop-filter: blur(20px);
                padding: 10px 16px;
                box-shadow: 0 -4px 20px rgba(0, 0, 0, 0.05);
                z-index: 1000;
                justify-content: space-around;
                align-items: center;
                border-top: 1px solid rgba(0, 0, 0, 0.05);
                padding-bottom: max(10px, env(safe-area-inset-bottom));
                display: flex;
            }

            .site-nav .nav-actions {
                display: none;
            }

            /* Hide top actions on mobile */
            body {
                padding-bottom: 80px;
            }
        }

        /* 针对大屏保留顶部导航 */
        @media (min-width: 993px) {
            .mobile-bottom-nav {
                display: none;
            }
        }
    </style>
</head>

<body class="home">
    <div class="site-shell">
        <nav class="site-nav">
            <div class="container-xxl d-flex align-items-center justify-content-between">
                <div class="nav-brand">
                    <span class="brand-mark">RL</span>
                    <div class="brand-text">
                        <div class="small text-uppercase" data-i18n="brandTagline">Rare Light</div>
                        <div class="fw-bold" data-i18n="brandTitle">Rare Light · 罕见病儿童公益</div>
                    </div>
                </div>

                <div class="nav-actions d-none d-lg-flex">
                    <div class="tool-bar" aria-label="Font size controls">
                        <button type="button" class="tool-btn" id="fontSmaller" aria-label="Smaller font"><i
                                class="bi bi-type"></i>-</button>
                        <button type="button" class="tool-btn" id="fontReset" aria-label="Default font"><i
                                class="bi bi-arrow-counterclockwise"></i></button>
                        <button type="button" class="tool-btn" id="fontLarger" aria-label="Larger font"><i
                                class="bi bi-type"></i>+</button>
                    </div>

                    <button class="nav-btn nav-btn-ghost me-2" type="button" id="langToggle"
                        aria-label="Language toggle">
                        <i class="bi bi-translate me-1"></i> EN / 中文
                    </button>

                    <a class="nav-btn nav-btn-ghost" href="/rarelight/blog" data-i18n="navProjectLog">项目日志</a>
                    <a class="nav-btn nav-btn-primary ms-2" id="classroomCta" data-classroom-link href="#classroom">
                        <i class="bi bi-grid-fill me-2"></i><span data-i18n="navLogin">进入网课</span>
                    </a>
                </div>
            </div>
        </nav>

        <main>
            <section class="hero">
                <div class="container-xxl">
                    <div class="hero-grid"
                        style="display: grid; grid-template-columns: minmax(0, 1.1fr) minmax(0, 0.9fr); gap: 3rem; align-items: center;">
                        <div class="hero-copy reveal" data-reveal>
                            <div class="brand-eyebrow mb-3" data-i18n="heroEyebrow">
                                <i class="bi bi-stars me-1"></i> Rare Light · 点亮希望
                            </div>
                            <h1 data-i18n="heroTitle">“线上趣味课 + 线下科普行” 双轨陪伴罕见病儿童</h1>
                            <p class="lead" data-i18n="heroLead">RARE LIGHT
                                罕见病关爱项目，面向患儿与家庭同步提供线上趣味课堂与线下科普关怀，兼顾成长需求与社会认知，打造专业且温暖的公益服务体系。</p>

                            <div class="d-flex flex-wrap gap-3 mt-4">
                                <a class="nav-btn nav-btn-primary" data-classroom-link href="#classroom"
                                    style="padding: 0.6rem 1.4rem; font-size: 1rem;">
                                    <span data-i18n="ctaEnter">进入网课</span>
                                </a>
                                <a class="nav-btn nav-btn-ghost" href="/rarelight/blog"
                                    style="border: 1px solid rgba(0,0,0,0.1); background: white;">
                                    <span data-i18n="navProjectLog">项目日志</span>
                                </a>
                            </div>
                            <div class="mt-3 text-secondary small" id="sessionNote" data-i18n="sessionNote"
                                style="opacity: 0.8;">
                                课堂由专业志愿者维护，登录后即可继续学习。
                            </div>
                        </div>

                        <div class="hero-card reveal" data-reveal>
                            <p class="eyebrow mb-2" data-i18n="cardEyebrow">项目全景</p>
                            <h3 class="fw-bold mb-3" data-i18n="cardTitle">线上课堂迭代五期 · 线下科普深入社区与校园</h3>
                            <p class="text-muted mb-4" data-i18n="cardDesc">教师梯队与服务规模同步壮大，课堂覆盖多学科，累计近千课时，陪伴 30+ 罕见病患儿家庭。
                            </p>

                            <div class="d-grid gap-3"
                                style="grid-template-columns: repeat(auto-fit, minmax(140px, 1fr));">
                                <div class="stat">
                                    <strong data-i18n="stat1" class="text-primary">5 期</strong>
                                    <div class="small text-muted mt-1" data-i18n="stat1Desc">近千课时覆盖</div>
                                </div>
                                <div class="stat">
                                    <strong data-i18n="stat2" class="text-primary">70+</strong>
                                    <div class="small text-muted mt-1" data-i18n="stat2Desc">专业志愿者</div>
                                </div>
                                <div class="stat">
                                    <strong data-i18n="stat3" class="text-primary">30+</strong>
                                    <div class="small text-muted mt-1" data-i18n="stat3Desc">家庭长期陪伴</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <section class="section reveal" id="about" data-reveal>
                <div class="container-xxl">
                    <header class="mb-5">
                        <p class="eyebrow mb-2" data-i18n="sectionActionEyebrow">我们的行动</p>
                        <h2 class="fw-bold h2" data-i18n="sectionActionTitle">线上趣味课 + 线下科普行 · 双轨服务模式</h2>
                    </header>
                    <div class="d-grid gap-4" style="grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));">
                        <div class="feature-card reveal" data-reveal>
                            <div class="feature-icon"><i class="bi bi-laptop"></i></div>
                            <h4 class="fw-bold mb-2" data-i18n="featureA">线上趣味课堂</h4>
                            <p class="text-secondary mb-0" data-i18n="featureAText">
                                中国药科大学志愿者面向患儿开展系列教学，从英语拓展到语数外、科学、地理、历史、AI，多学科陪伴成长。</p>
                        </div>
                        <div class="feature-card reveal" data-reveal>
                            <div class="feature-icon"><i class="bi bi-people"></i></div>
                            <h4 class="fw-bold mb-2" data-i18n="featureB">线下科普关怀</h4>
                            <p class="text-secondary mb-0" data-i18n="featureBText">
                                “罕见病患儿进校园”“罕爱童行・药语同航”等活动走进校园、医院与社区，让公众看见、理解并支持罕见病家庭。</p>
                        </div>
                        <div class="feature-card reveal" data-reveal>
                            <div class="feature-icon"><i class="bi bi-megaphone"></i></div>
                            <h4 class="fw-bold mb-2" data-i18n="featureC">科普传播与倡导</h4>
                            <p class="text-secondary mb-0" data-i18n="featureCText">
                                媒体报道、公众号“因你罕见，我药看见”持续输出诊疗指南、药物政策与真实故事，为家庭提供权威信息服务。</p>
                        </div>
                        <div class="feature-card reveal" data-reveal>
                            <div class="feature-icon"><i class="bi bi-graph-up-arrow"></i></div>
                            <h4 class="fw-bold mb-2" data-i18n="featureD">志愿者与科研支撑</h4>
                            <p class="text-secondary mb-0" data-i18n="featureDText">70
                                人志愿者队伍与调研报告双线推进，沉淀实证数据，为服务迭代和政策倡导提供依据。</p>
                        </div>
                    </div>

                    <div class="cta-banner mt-5 d-flex align-items-center justify-content-between flex-wrap gap-4">
                        <div>
                            <div class="fw-bold fs-4 mb-1" data-i18n="ctaJoinTitle">想加入我们？</div>
                            <div data-i18n="ctaJoinDesc" style="opacity: 0.9;">欢迎医疗机构、教育机构与企业伙伴共建罕见病儿童支持网络。</div>
                        </div>
                        <a class="nav-btn" href="#contact" data-i18n="ctaJoinBtn"
                            style="background: white; color: var(--rl-primary);">成为合作伙伴 ↗</a>
                    </div>
                </div>
            </section>

            <section class="section reveal" id="resources" data-reveal>
                <div class="container-xxl">
                    <div class="feature-card d-flex flex-wrap flex-md-nowrap align-items-center gap-4 p-4">
                        <img src="assets/img/wechat-qr.png" alt="QR Code"
                            style="width: 160px; height: 160px; border-radius: 12px; object-fit: cover; background: #eee;"
                            class="flex-shrink-0">
                        <div>
                            <p class="eyebrow mb-2" data-i18n="qrTitle">关注 Rare Light 公众号</p>
                            <h3 class="fw-bold mb-2" data-i18n="qrSubtitle">微信扫码，获取最新课程与科普动态</h3>
                            <p class="text-secondary mb-0" data-i18n="qrDesc">用微信扫描二维码即可关注，第一时间了解课程安排、线下活动、科普信息与政策更新。
                            </p>
                        </div>
                    </div>
                </div>
            </section>

            <section class="section reveal" id="news" data-reveal>
                <div class="container-xxl">
                    <header class="mb-5">
                        <p class="eyebrow mb-2" data-i18n="pathwayEyebrow">陪伴路径</p>
                        <h2 class="fw-bold" data-i18n="pathwayTitle">从需求到陪伴，我们这样行动</h2>
                    </header>
                    <div class="d-grid gap-4" style="grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));">
                        <div class="feature-card reveal" data-reveal style="border-left: 4px solid var(--rl-primary);">
                            <h5 class="fw-bold text-primary" data-i18n="pathway1">1. 了解家庭需求</h5>
                            <p class="text-secondary mb-0 mt-2" data-i18n="pathway1Desc">与家长沟通病情、教育与心理需求，建立一对一档案。</p>
                        </div>
                        <div class="feature-card reveal" data-reveal style="border-left: 4px solid var(--rl-accent);">
                            <h5 class="fw-bold" style="color: var(--rl-accent);" data-i18n="pathway2">2. 匹配资源与课堂</h5>
                            <p class="text-secondary mb-0 mt-2" data-i18n="pathway2Desc">为孩子安排合适的线上课程与志愿者陪伴，并链接医疗与社会资源。
                            </p>
                        </div>
                        <div class="feature-card reveal" data-reveal style="border-left: 4px solid #10b981;">
                            <h5 class="fw-bold" style="color: #10b981;" data-i18n="pathway3">3. 持续跟进与评估</h5>
                            <p class="text-secondary mb-0 mt-2" data-i18n="pathway3Desc">定期回访，评估课堂效果与家庭需求，调整支持方案。</p>
                        </div>
                    </div>
                </div>
            </section>

            <section class="section reveal" data-reveal>
                <div class="container-xxl">
                    <div class="classroom" id="classroom" style="scroll-margin-top: 100px;">
                        <div class="row g-5 align-items-center">
                            <div class="col-lg-6 reveal" data-reveal>
                                <p class="eyebrow mb-2" data-i18n="classroomEyebrow">Rare Light School</p>
                                <h3 class="fw-bold mb-3" data-i18n="classroomTitle">网课入口</h3>
                                <p class="text-muted mb-4" data-i18n="classroomDesc">患儿与家长可在此登录进入课堂；已分配课程的用户将直接跳转至学习页面。
                                </p>
                                <div class="d-flex gap-2">
                                    <a class="nav-btn nav-btn-primary px-4 py-2" data-classroom-link href="#classroom"
                                        data-i18n="ctaImmediate">立即进入网课</a>
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="login-panel reveal" data-reveal>
                                    <h4 class="fw-bold mb-3" data-i18n="loginTitle">课堂登录</h4>
                                    <p class="small text-secondary mb-4" data-i18n="loginDesc">请输入管理员分配的账号登录。</p>
                                    <form id="loginForm" autocomplete="on">
                                        <div>
                                            <label for="username" data-i18n="loginUsername">用户名</label>
                                            <input id="username" name="username" type="text" placeholder="student01"
                                                required>
                                        </div>
                                        <div>
                                            <label for="password" data-i18n="loginPasswordLabel">密码</label>
                                            <input id="password" name="password" type="password" placeholder="请输入密码"
                                                required data-i18n-placeholder="loginPasswordPlaceholder">
                                        </div>
                                        <button type="submit" class="nav-btn nav-btn-primary w-100 py-2 mt-2"
                                            id="loginButton" data-i18n="loginButton">登录并进入课堂</button>
                                        <div class="mt-3 text-center small fw-bold" id="loginMessage" aria-live="polite"
                                            hidden></div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <section class="section reveal" id="contact" data-reveal>
                <div class="container-xxl">
                    <header class="text-center mb-5">
                        <p class="eyebrow mb-2" data-i18n="contactEyebrow">联系与合作</p>
                        <h2 class="fw-bold" data-i18n="contactTitle">一起为罕见病儿童点亮更多光</h2>
                    </header>
                    <div class="row g-4 justify-content-center">
                        <div class="col-md-4">
                            <div class="feature-card text-center h-100 reveal" data-reveal>
                                <div class="text-primary fs-2 mb-3"><i class="bi bi-heart-fill"></i></div>
                                <h5 class="fw-bold mb-2" data-i18n="contactVolunteer">成为志愿者</h5>
                                <p class="text-secondary small mb-0" data-i18n="contactVolunteerDesc">加入陪伴小组、教学与运营支持团队。
                                </p>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="feature-card text-center h-100 reveal" data-reveal>
                                <div class="text-primary fs-2 mb-3"><i class="bi bi-building"></i></div>
                                <h5 class="fw-bold mb-2" data-i18n="contactPartner">机构合作</h5>
                                <p class="text-secondary small mb-0" data-i18n="contactPartnerDesc">
                                    医疗、教育、公益、企业伙伴欢迎联系共建项目。</p>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="feature-card text-center h-100 reveal" data-reveal>
                                <div class="text-primary fs-2 mb-3"><i class="bi bi-house-door-fill"></i></div>
                                <h5 class="fw-bold mb-2" data-i18n="contactFamily">家庭申请</h5>
                                <p class="text-secondary small mb-0" data-i18n="contactFamilyDesc">罕见病患儿家庭可申请课堂访问与陪伴支持。
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </main>

        <footer class="footer">
            <div class="container-xxl d-flex flex-wrap justify-content-between align-items-center gap-3">
                <div class="d-flex align-items-center gap-2">
                    <span class="fw-bold text-dark">Rare Light</span>
                    <span class="text-muted">|</span>
                    <span class="small" data-i18n="footerText">为罕见病儿童提供陪伴、教育与资源链接。</span>
                </div>
                <div class="small text-secondary" data-i18n="footerEmail">
                    <i class="bi bi-envelope me-1"></i> hello@rarelight.org
                </div>
            </div>
        </footer>

        <div class="nav-actions d-lg-none"
            style="display: flex; gap: 10px; align-items: center; justify-content: space-around;">
            <button class="nav-btn nav-btn-ghost" id="mobileLangToggle"><i class="bi bi-translate"></i></button>
            <a class="nav-btn nav-btn-ghost" href="/rarelight/blog" data-i18n="navProjectLog">日志</a>
            <a class="nav-btn nav-btn-primary flex-grow-1" href="#classroom" data-i18n="navLogin">进入网课</a>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // 保持原有逻辑不变
        const BASE_PATH = '/rarelight';
        const API_BASE = `${BASE_PATH}/api`;
        const ROUTE_DASHBOARD = `${BASE_PATH}/dashboard`;
        const FONT_KEY = 'rl_font_scale';
        const LANG_KEY = 'rl_lang';
        const i18n = {
            zh: {
                brandTagline: 'Rare Light',
                brandTitle: 'Rare Light · 罕见病儿童公益',
                pageTitle: 'Rare Light · 点亮罕见病儿童的希望',
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
                stat1: '5 期课堂',
                stat1Desc: '近千课时，覆盖多学科',
                stat2: '70 位志愿者',
                stat2Desc: '教师梯队持续壮大',
                stat3: '30+ 家庭',
                stat3Desc: '5 年持续陪伴',
                pathwayEyebrow: '陪伴路径',
                pathwayTitle: '从需求到陪伴，我们这样行动',
                pathway1: '1. 了解家庭需求',
                pathway1Desc: '与家长沟通病情、教育与心理需求，建立一对一档案。',
                pathway2: '2. 匹配资源与课堂',
                pathway2Desc: '为孩子安排合适的线上课程与志愿者陪伴，并链接医疗与社会资源。',
                pathway3: '3. 持续跟进与评估',
                pathway3Desc: '定期回访，评估课堂效果与家庭需求，调整支持方案。',
                ctaImmediate: '立即进入网课',
                qrTitle: '关注 Rare Light 公众号',
                qrSubtitle: '微信扫码，获取最新课程与科普动态',
                qrDesc: '用微信扫描二维码即可关注，第一时间了解课程安排、线下活动、科普信息与政策更新。',
                classroomEyebrow: 'Rare Light School',
                classroomTitle: '网课入口',
                classroomDesc: '患儿与家长可在此登录进入课堂；已分配课程的用户将直接跳转至学习页面。',
                loginTitle: '课堂登录',
                loginDesc: '请输入管理员分配的账号登录。如果您尚未拥有账户，请联系我们的协调员。',
                loginUsername: '用户名',
                loginPasswordLabel: '密码',
                loginPasswordPlaceholder: '请输入密码',
                loginButton: '登录并进入课堂',
                navProjectLog: '项目日志',
                navLoginLoggedIn: '进入我的课堂',
                contactEyebrow: '联系与合作',
                contactTitle: '一起为罕见病儿童点亮更多光',
                contactVolunteer: '成为志愿者',
                contactVolunteerDesc: '加入陪伴小组、教学与运营支持团队。',
                contactPartner: '机构合作',
                contactPartnerDesc: '医疗、教育、公益、企业伙伴欢迎联系共建项目。',
                contactFamily: '家庭申请',
                contactFamilyDesc: '罕见病患儿家庭可申请课堂访问与陪伴支持。',
                footerText: 'Rare Light — 为罕见病儿童提供陪伴、教育与资源链接。',
                footerEmail: '联系邮箱：hello@rarelight.org'
            },
            en: {
                brandTagline: 'Rare Light',
                brandTitle: 'Rare Light · Rare Disease Care',
                pageTitle: 'Rare Light · Rare Disease Care',
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
                stat1: '5 phases of classes',
                stat1Desc: 'Nearly 1,000 class hours across subjects',
                stat2: '70 volunteers',
                stat2Desc: 'Growing teaching team',
                stat3: '30+ families',
                stat3Desc: '5 years of ongoing support',
                pathwayEyebrow: 'Our Path',
                pathwayTitle: 'From needs to sustained companionship',
                pathway1: '1. Understand needs',
                pathway1Desc: 'Talk with parents about medical, education, and psychological needs; build a 1:1 profile.',
                pathway2: '2. Match resources & classes',
                pathway2Desc: 'Arrange suitable online courses, volunteer companions, and medical/social resources.',
                pathway3: '3. Follow-up & evaluate',
                pathway3Desc: 'Regular check-ins to adjust plans based on family feedback.',
                ctaImmediate: 'Enter Classroom Now',
                qrTitle: 'Follow Rare Light on WeChat',
                qrSubtitle: 'Scan to get course updates & news',
                qrDesc: 'Scan the QR code to follow and receive course schedules, events, and policy updates.',
                classroomEyebrow: 'Rare Light School',
                classroomTitle: 'Classroom Access',
                classroomDesc: 'Children and parents log in here; assigned users jump directly to learning.',
                loginTitle: 'Classroom Login',
                loginDesc: 'Use the account assigned by administrators. If you need access, contact our coordinator.',
                loginUsername: 'Username',
                loginPasswordLabel: 'Password',
                loginPasswordPlaceholder: 'Enter password',
                loginButton: 'Log in & Enter Classroom',
                navProjectLog: 'Project Log',
                navLoginLoggedIn: 'Enter My Classroom',
                contactEyebrow: 'Contact & Partnership',
                contactTitle: 'Light up more hope for rare disease children together',
                contactVolunteer: 'Become a Volunteer',
                contactVolunteerDesc: 'Join companion groups, teaching, or operations support teams.',
                contactPartner: 'Institutional Partnership',
                contactPartnerDesc: 'Hospitals, schools, NGOs, and companies are welcome to co-build the program.',
                contactFamily: 'Family Application',
                contactFamilyDesc: 'Rare disease families can apply for classroom access and companion support.',
                footerText: 'Rare Light — Providing companionship, education, and resources for children with rare diseases.',
                footerEmail: 'Email: hello@rarelight.org'
            }
        };

        const htmlEl = document.documentElement;
        const fontSmallerBtn = document.getElementById('fontSmaller');
        const fontResetBtn = document.getElementById('fontReset');
        const fontLargerBtn = document.getElementById('fontLarger');
        const langToggle = document.getElementById('langToggle');
        const mobileLangToggle = document.getElementById('mobileLangToggle');
        let currentFontScale = 1;
        let currentLang = localStorage.getItem(LANG_KEY) || 'zh';
        const classroomLinks = document.querySelectorAll('[data-classroom-link]');
        const sessionNote = document.getElementById('sessionNote');
        const loginForm = document.getElementById('loginForm');
        const loginButton = document.getElementById('loginButton');
        const loginMessage = document.getElementById('loginMessage');

        function withBasePath(path = '') {
            if (!path) return '';
            if (/^https?:\/\//i.test(path) || path.startsWith('#')) return path;
            const normalized = path.startsWith('/') ? path : `/${path}`;
            if (normalized.startsWith(`${BASE_PATH}/`)) return normalized;
            return `${BASE_PATH}${normalized}`.replace(/\/{2,}/g, '/');
        }

        function normalizeApiUrl(url) {
            if (url.startsWith(`${API_BASE}/`)) {
                const [path, query] = url.split('?');
                const sanitizedPath = path.replace(/\/{2,}/g, '/');
                return query ? `${sanitizedPath}?${query}` : sanitizedPath;
            }
            if (/^\/?api\//i.test(url)) {
                return withBasePath(url);
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
                el.textContent = dict[key];
            });
            document.querySelectorAll('[data-i18n-placeholder]').forEach((el) => {
                const key = el.dataset.i18nPlaceholder;
                if (!key || !(key in dict)) return;
                el.setAttribute('placeholder', dict[key]);
            });
            document.title = dict.pageTitle || 'Rare Light';
            currentLang = lang;
            localStorage.setItem(LANG_KEY, currentLang);

            const langText = lang === 'zh' ? '<i class="bi bi-translate me-1"></i> 中文 / EN' : '<i class="bi bi-translate me-1"></i> EN / 中文';
            if (langToggle) langToggle.innerHTML = langText;

        }

        function initLangToggle() {
            applyTranslations(currentLang);
            const toggleHandler = () => {
                const next = currentLang === 'zh' ? 'en' : 'zh';
                applyTranslations(next);
            };
            if (langToggle) langToggle.addEventListener('click', toggleHandler);
            if (mobileLangToggle) mobileLangToggle.addEventListener('click', toggleHandler);
        }

        function initRevealOnScroll() {
            const items = document.querySelectorAll('[data-reveal]');
            if (!('IntersectionObserver' in window) || !items.length) {
                items.forEach((el) => el.classList.add('visible'));
                return;
            }
            const observer = new IntersectionObserver((entries) => {
                entries.forEach((entry) => {
                    if (entry.isIntersecting) {
                        entry.target.classList.add('visible');
                        observer.unobserve(entry.target);
                    }
                });
            }, { threshold: 0.1 });
            items.forEach((el) => observer.observe(el));
        }

        function updateClassroomLinks(target, i18nKey) {
            const href = withBasePath(target);
            classroomLinks.forEach((link) => {
                link.href = href;
                if (i18nKey) {
                    // 查找内部的 span[data-i18n] 并更新 key
                    let span = link.querySelector('span[data-i18n]');
                    if (!span && link.hasAttribute('data-i18n')) {
                        span = link; // link 自身就是 i18n 容器
                    }

                    if (span) {
                        span.setAttribute('data-i18n', i18nKey);
                        // 立即应用当前语言的翻译
                        const text = i18n[currentLang][i18nKey];
                        if (text) span.textContent = text;
                    }
                }
            });
        }

        function showMessage(text = '', type = '') {
            const hasText = Boolean(text);
            loginMessage.textContent = hasText ? text : '';
            loginMessage.classList.remove('text-danger', 'text-success');
            loginMessage.hidden = !hasText;
            if (hasText && type) {
                loginMessage.classList.add(type === 'error' ? 'text-danger' : 'text-success');
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
                    updateClassroomLinks(ROUTE_DASHBOARD, 'navLoginLoggedIn');
                    sessionNote.textContent = `${i18n[currentLang].sessionNote || '课堂由专业志愿者维护，登录后即可继续学习。'} ${currentLang === 'zh' ? `欢迎回来，${name}。` : `Welcome back, ${name}.`}`;
                    loginForm.querySelectorAll('input, button').forEach((element) => {
                        element.disabled = true;
                    });
                    showMessage(currentLang === 'zh' ? '您已登录，可直接进入课堂。' : 'You are logged in. Enter the classroom directly.', 'success');
                } else {
                    updateClassroomLinks('#classroom', 'navLogin');
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
                    window.location.href = ROUTE_DASHBOARD;
                }, 350);
            } catch (error) {
                showMessage(error.message || '登录失败，请重试', 'error');
            } finally {
                loginButton.disabled = false;
            }
        });

        initFontControls();
        initLangToggle();
        initRevealOnScroll();
        checkSession();
    </script>
</body>

</html>