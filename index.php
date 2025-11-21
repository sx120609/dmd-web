<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Rare Light · Be the Rare Light</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Sora:wght@400;500;600;700&family=Noto+Sans+SC:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/main.css">
    <style>
        :root {
            --brand-gradient: radial-gradient(circle at 20% 20%, rgba(82, 141, 255, 0.35), transparent 38%),
                radial-gradient(circle at 80% 10%, rgba(13, 215, 193, 0.32), transparent 35%),
                linear-gradient(135deg, #0f172a, #1e293b, #0ea5e9);
            --brand-color: #0ea5e9;
            --brand-color-strong: #0284c7;
            --surface: rgba(255, 255, 255, 0.9);
            --surface-muted: rgba(255, 255, 255, 0.7);
            --text-primary: #0b1221;
            --text-secondary: #41516b;
            --border-color: rgba(15, 23, 42, 0.08);
            --shadow: 0 34px 70px rgba(0, 24, 86, 0.25);
            --body-background: radial-gradient(circle at 12% 18%, rgba(14, 165, 233, 0.16), transparent 48%),
                radial-gradient(circle at 82% 24%, rgba(13, 215, 193, 0.16), transparent 48%),
                linear-gradient(180deg, #f6f9ff 0%, #edf1f7 45%, #e6ebf5 100%);
            --canvas-width: 1240px;
            font-family: 'Sora', 'Noto Sans SC', 'Segoe UI', system-ui, -apple-system, BlinkMacSystemFont, 'Microsoft YaHei', sans-serif;
        }

        body.landing {
            background: var(--body-background);
            margin: 0;
            color: var(--text-primary);
        }

        .page {
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        .container {
            width: min(var(--canvas-width), 92vw);
            margin: 0 auto;
        }

        .topbar {
            position: sticky;
            top: 0;
            z-index: 10;
            backdrop-filter: blur(16px);
            background: rgba(246, 249, 255, 0.72);
            border-bottom: 1px solid rgba(148, 163, 184, 0.12);
        }

        .nav {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 1.1rem 0;
            gap: 1rem;
        }

        .brand {
            display: inline-flex;
            align-items: center;
            gap: 0.7rem;
            font-weight: 700;
            letter-spacing: -0.02em;
            font-size: 1.1rem;
        }

        .brand-mark {
            width: 38px;
            height: 38px;
            border-radius: 12px;
            background: linear-gradient(135deg, #0ea5e9, #26c6da);
            position: relative;
            overflow: hidden;
            box-shadow: 0 12px 30px rgba(14, 165, 233, 0.35);
        }

        .brand-mark::after {
            content: '';
            position: absolute;
            inset: 7px;
            border-radius: 8px;
            background: rgba(255, 255, 255, 0.5);
            box-shadow: inset 0 0 0 1px rgba(255, 255, 255, 0.6);
        }

        .nav-links {
            display: flex;
            align-items: center;
            gap: 1.5rem;
        }

        .nav-links a {
            text-decoration: none;
            color: var(--text-secondary);
            font-weight: 600;
            letter-spacing: -0.01em;
        }

        .nav-links a:hover {
            color: var(--text-primary);
        }

        .hero {
            display: grid;
            grid-template-columns: minmax(0, 1.05fr) minmax(0, 0.95fr);
            gap: 2.5rem;
            align-items: end;
            padding: 3.2rem 0 2.2rem;
        }

        .eyebrow {
            display: inline-flex;
            gap: 0.65rem;
            align-items: center;
            padding: 0.5rem 0.95rem;
            border-radius: 999px;
            background: rgba(14, 165, 233, 0.12);
            color: #0284c7;
            font-weight: 700;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            font-size: 0.8rem;
        }

        .hero h1 {
            margin: 1.3rem 0 0.8rem;
            font-size: clamp(2.4rem, 4vw, 3.8rem);
            letter-spacing: -0.04em;
            line-height: 1.05;
        }

        .hero p.lead {
            margin: 0 0 1.5rem;
            font-size: 1.1rem;
            color: var(--text-secondary);
            line-height: 1.7;
        }

        .actions {
            display: flex;
            flex-wrap: wrap;
            gap: 0.8rem;
            align-items: center;
        }

        .stats {
            margin-top: 1.8rem;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 1rem;
        }

        .stat {
            padding: 1.1rem 1.2rem;
            border-radius: 18px;
            background: rgba(255, 255, 255, 0.82);
            border: 1px solid rgba(148, 163, 184, 0.16);
            box-shadow: 0 20px 42px rgba(15, 23, 42, 0.08);
        }

        .stat strong {
            display: block;
            font-size: 1.8rem;
            letter-spacing: -0.04em;
        }

        .stat span {
            color: var(--text-secondary);
        }

        .hero-visual {
            position: relative;
            height: 100%;
            display: grid;
            align-items: stretch;
        }

        .panel {
            position: relative;
            border-radius: 28px;
            padding: 2.4rem;
            background: linear-gradient(160deg, rgba(255, 255, 255, 0.9), rgba(244, 248, 252, 0.75));
            border: 1px solid rgba(148, 163, 184, 0.18);
            box-shadow: 0 40px 100px rgba(0, 24, 86, 0.22);
            overflow: hidden;
            isolation: isolate;
        }

        .panel::before {
            content: '';
            position: absolute;
            width: 280px;
            height: 280px;
            right: -80px;
            top: -80px;
            background: radial-gradient(circle, rgba(14, 165, 233, 0.28), transparent 60%);
            filter: blur(4px);
            z-index: 0;
        }

        .panel::after {
            content: '';
            position: absolute;
            width: 320px;
            height: 320px;
            left: -100px;
            bottom: -140px;
            background: radial-gradient(circle, rgba(13, 215, 193, 0.22), transparent 65%);
            filter: blur(4px);
            z-index: 0;
        }

        .panel-content {
            position: relative;
            z-index: 1;
            display: grid;
            gap: 1.25rem;
        }

        .panel-headline {
            font-size: 1.2rem;
            letter-spacing: -0.02em;
            margin: 0;
        }

        .chips {
            display: flex;
            flex-wrap: wrap;
            gap: 0.6rem;
        }

        .chip {
            padding: 0.55rem 0.9rem;
            border-radius: 12px;
            background: rgba(14, 165, 233, 0.12);
            color: #0369a1;
            font-weight: 700;
            letter-spacing: 0.01em;
        }

        .badge-ghost {
            padding: 0.55rem 0.9rem;
            border-radius: 12px;
            background: rgba(255, 255, 255, 0.7);
            border: 1px solid rgba(148, 163, 184, 0.18);
            color: var(--text-secondary);
            font-weight: 600;
        }

        section {
            padding: 3.5rem 0;
        }

        .section-head {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
            margin-bottom: 1.6rem;
        }

        .section-head h2 {
            margin: 0;
            font-size: 2rem;
            letter-spacing: -0.03em;
        }

        .section-head p {
            margin: 0;
            color: var(--text-secondary);
            max-width: 540px;
            line-height: 1.7;
        }

        .cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: 1rem;
        }

        .card-neo {
            border-radius: 18px;
            border: 1px solid rgba(148, 163, 184, 0.16);
            background: rgba(255, 255, 255, 0.82);
            padding: 1.4rem;
            box-shadow: 0 18px 40px rgba(15, 23, 42, 0.08);
            display: grid;
            gap: 0.6rem;
        }

        .card-neo h3 {
            margin: 0;
            font-size: 1.2rem;
            letter-spacing: -0.02em;
        }

        .card-neo p {
            margin: 0;
            color: var(--text-secondary);
            line-height: 1.65;
        }

        .pill {
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            padding: 0.45rem 0.9rem;
            border-radius: 999px;
            background: rgba(14, 165, 233, 0.1);
            color: #0369a1;
            font-weight: 700;
            font-size: 0.85rem;
        }

        .layout-split {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 1.4rem;
            align-items: start;
        }

        .list {
            list-style: none;
            padding: 0;
            margin: 0;
            display: grid;
            gap: 0.9rem;
        }

        .list li {
            padding: 1rem 1.1rem;
            border-radius: 14px;
            background: rgba(255, 255, 255, 0.82);
            border: 1px solid rgba(148, 163, 184, 0.16);
            display: flex;
            gap: 0.8rem;
            align-items: flex-start;
        }

        .list strong {
            display: block;
            margin-bottom: 0.2rem;
        }

        .callout {
            padding: 2.4rem;
            border-radius: 22px;
            background: linear-gradient(135deg, rgba(15, 23, 42, 0.94), rgba(15, 23, 42, 0.88)), var(--brand-gradient);
            color: #f8fafc;
            border: 1px solid rgba(148, 163, 184, 0.16);
            box-shadow: 0 32px 80px rgba(15, 23, 42, 0.28);
        }

        .callout h3 {
            margin: 0 0 0.6rem;
            font-size: 1.8rem;
            letter-spacing: -0.02em;
        }

        .callout p {
            margin: 0 0 1.2rem;
            color: rgba(248, 250, 252, 0.8);
        }

        footer {
            padding: 2.6rem 0 2.2rem;
            color: var(--text-secondary);
            border-top: 1px solid rgba(148, 163, 184, 0.12);
            margin-top: 1rem;
        }

        footer .brand {
            margin-bottom: 0.4rem;
        }

        .login-shell {
            padding: 1.7rem;
            border-radius: 18px;
            background: rgba(255, 255, 255, 0.9);
            border: 1px solid rgba(148, 163, 184, 0.2);
            box-shadow: 0 20px 50px rgba(15, 23, 42, 0.12);
            display: grid;
            gap: 1.1rem;
        }

        .login-shell h3 {
            margin: 0;
            font-size: 1.35rem;
        }

        .login-shell small {
            color: var(--text-secondary);
        }

        .login-shell form {
            display: grid;
            gap: 1rem;
        }

        .login-message {
            min-height: 1.4rem;
        }

        @media (max-width: 1024px) {
            .hero {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 720px) {
            .nav {
                flex-direction: column;
                align-items: flex-start;
            }

            .nav-links {
                flex-wrap: wrap;
            }

            section {
                padding: 3rem 0;
            }
        }
    </style>
</head>
<body class="landing">
<div class="page">
    <header class="topbar">
        <div class="container nav">
            <div class="brand">
                <span class="brand-mark" aria-hidden="true"></span>
                Rare Light · 罕见光
            </div>
            <nav class="nav-links" aria-label="主导航">
                <a href="#mission">使命 Mission</a>
                <a href="#programs">项目 Programs</a>
                <a href="#platform">教育平台 Platform</a>
                <a href="portal/">登录 Login</a>
            </nav>
            <div class="actions">
                <a class="ghost-button" href="#platform">了解平台</a>
                <a class="primary-button" href="portal/">进入 Rare Light</a>
            </div>
        </div>
    </header>

    <main class="container">
        <section class="hero">
            <div>
                <span class="eyebrow">Rare Light · Global Compassion</span>
                <h1>为罕见病儿童带去一束可见的光<br>Be the rare light for every rare child.</h1>
                <p class="lead">Rare Light 是面向罕见病儿童与家庭的国际化公益组织。我们联动医疗、教育与社区资源，用可持续的科技平台与温度同行——课程、陪伴、转介，一体化守护每一次「平凡生活」。</p>
                <div class="actions">
                    <a class="primary-button" href="#mission">探索 Rare Light</a>
                    <a class="ghost-button" href="portal/">登录教学平台</a>
                </div>
                <div class="stats">
                    <div class="stat">
                        <strong>12+</strong>
                        <span>国家与地区覆盖 Territories served</span>
                    </div>
                    <div class="stat">
                        <strong>3,800+</strong>
                        <span>家庭获得陪伴 Families supported</span>
                    </div>
                    <div class="stat">
                        <strong>7,500h</strong>
                        <span>学习与赋能时长 Learning hours delivered</span>
                    </div>
                    <div class="stat">
                        <strong>280+</strong>
                        <span>志愿导师 Volunteers worldwide</span>
                    </div>
                </div>
            </div>
            <div class="hero-visual">
                <div class="panel">
                    <div class="panel-content">
                        <p class="panel-headline">「我们把专业与温度织进每一个普通日子。」<br><small class="text-muted">A calm, Apple-inspired space for hope.</small></p>
                        <div class="chips">
                            <span class="chip">Care · 医疗联动</span>
                            <span class="chip">Learn · 课程支持</span>
                            <span class="chip">Connect · 社区陪伴</span>
                        </div>
                        <div class="badge-ghost">ISO/IEC 27001-ready 数据治理 · 全球多语志愿团队</div>
                        <div class="card-neo" style="margin-top: 0.4rem;">
                            <h3>「网课」只是其中的一环</h3>
                            <p>Rare Light 平台不仅提供直播与录播课程，还聚合医疗咨询、心理陪伴、家长训练营与慈善资源转介。每位孩子与看护者都有自己的专属旅程。</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section id="mission">
            <div class="section-head">
                <div>
                    <div class="pill">Mission 使命</div>
                    <h2>把「稀有」变成「看见」</h2>
                </div>
                <p>我们与全球罕见病联盟、儿科专家、教育团队共建放心的陪伴路径——医疗照护与心灵陪伴并重，教学与生活同步，尊重每一个节奏。</p>
            </div>
            <div class="cards">
                <div class="card-neo">
                    <h3>Whole-person Care 全人关怀</h3>
                    <p>医疗转介、心理支持、家访陪伴，一体化设计，确保孩子与家庭都被看见。</p>
                </div>
                <div class="card-neo">
                    <h3>Learning that Breathes 呼吸式学习</h3>
                    <p>短时段、可暂停、可复看的课程体验，尊重病程起伏，也能保持成长。</p>
                </div>
                <div class="card-neo">
                    <h3>Global & Local 联合力量</h3>
                    <p>国际志愿导师 + 本地社工团队，跨语言、跨时区协作，保持服务的连续性。</p>
                </div>
                <div class="card-neo">
                    <h3>Data with Dignity 数据有尊严</h3>
                    <p>隐私优先的技术底座，合规审计留痕，全程征得家长同意。</p>
                </div>
            </div>
        </section>

        <section id="programs">
            <div class="section-head">
                <div>
                    <div class="pill">Programs 项目</div>
                    <h2>四大支柱，连接世界级资源</h2>
                </div>
                <p>Rare Light 的服务矩阵覆盖教育、医疗、陪伴与资源筹措，形成闭环支持。</p>
            </div>
            <div class="layout-split">
                <div class="card-neo">
                    <h3>Education Hub 教育中心</h3>
                    <p>直播课堂、录播微课、AI 字幕与讲义自动整理，适配不同病情与年龄层的节奏。</p>
                    <ul class="list">
                        <li>
                            <div>
                                <strong>Multilingual 同步译制</strong>
                                <span class="text-muted">中英双语字幕，按需切换，助力跨国医疗迁移。</span>
                            </div>
                        </li>
                        <li>
                            <div>
                                <strong>Care-aware 日程</strong>
                                <span class="text-muted">与治疗计划对齐，支持暂停、延时与个性化提醒。</span>
                            </div>
                        </li>
                    </ul>
                </div>
                <div class="card-neo">
                    <h3>Care & Bridge 医疗与转介</h3>
                    <p>与合作医院、罕见病中心、药企慈善通道对接，快速建立病程档案与跟进提醒。</p>
                    <ul class="list">
                        <li>
                            <div>
                                <strong>Specialist Roster 专家库</strong>
                                <span class="text-muted">儿科、心理、物理康复、罕见病顾问组随时连线。</span>
                            </div>
                        </li>
                        <li>
                            <div>
                                <strong>Family-first Consent 家庭为先</strong>
                                <span class="text-muted">每次记录与转介都需要授权，透明可追溯。</span>
                            </div>
                        </li>
                    </ul>
                </div>
                <div class="card-neo">
                    <h3>Community & Relief 社区与纾困</h3>
                    <p>家长互助圈、线下同城陪伴、爱心基金发放，保持情绪与生活的韧性。</p>
                    <ul class="list">
                        <li>
                            <div>
                                <strong>Care Circles 陪伴小组</strong>
                                <span class="text-muted">按病种/年龄分组，社工拓展、志愿导师共同陪伴。</span>
                            </div>
                        </li>
                        <li>
                            <div>
                                <strong>Relief Grants 微光计划</strong>
                                <span class="text-muted">透明的救助与采购流程，面向紧急医疗与教育设备。</span>
                            </div>
                        </li>
                    </ul>
                </div>
            </div>
        </section>

        <section id="platform">
            <div class="section-head">
                <div>
                    <div class="pill">Platform 平台</div>
                    <h2>Apple 风格的平静体验，兼具安全与温度</h2>
                </div>
                <p>Rare Light Cloud 将课堂、档案、沟通整合在一处，家长与志愿导师随时随地进入，体验简洁而柔和。</p>
            </div>
            <div class="layout-split">
                <div class="panel">
                    <div class="panel-content">
                        <p class="panel-headline">一键进入的课堂 · 透明的病程 · 可信的隐私</p>
                        <div class="chips">
                            <span class="chip">Auto Notes 自动课纪要</span>
                            <span class="chip">Family Console 家庭中枢</span>
                            <span class="chip">Zero-distraction 米色界面</span>
                        </div>
                        <div class="badge-ghost">Progressive Web · Mobile ready · 深色/浅色自动</div>
                        <div class="list" style="margin-top: 0.8rem;">
                            <li>
                                <div>
                                    <strong>专属账号，差异化课程授权</strong>
                                    <span class="text-muted">每个孩子与看护者的课程、文档与提醒分级呈现。</span>
                                </div>
                            </li>
                            <li>
                                <div>
                                    <strong>录播 + B 站 + 本地文件</strong>
                                    <span class="text-muted">无需跳转，安全播放，便捷追踪学习进度。</span>
                                </div>
                            </li>
                            <li>
                                <div>
                                    <strong>全程留痕，可导出的陪伴档案</strong>
                                    <span class="text-muted">医疗团队、学校与社福机构都能拿到清晰摘要。</span>
                                </div>
                            </li>
                        </div>
                    </div>
                </div>
                <div class="card-neo" id="login">
                    <h3>进入 Rare Light 平台</h3>
                    <p class="text-muted">登录入口已移至独立模块，支持家长、志愿导师与管理员安全访问。</p>
                    <div class="actions">
                        <a class="primary-button" href="portal/">前往登录</a>
                        <a class="ghost-button" href="mailto:hello@rarelight.org">开通或重置账号</a>
                    </div>
                    <small class="text-muted">If you need a new account or password reset, please contact your Rare Light coordinator.</small>
                </div>
            </div>
        </section>

        <section>
            <div class="callout">
                <h3>与我们一起，照亮罕见的生命旅程</h3>
                <p>如果你是医疗伙伴、教育机构、公益基金会或个人志愿者，欢迎加入 Rare Light。用稳健的隐私守护与柔和的体验，陪伴更多家庭向前。</p>
                <div class="actions">
                    <a class="primary-button" href="mailto:hello@rarelight.org">联系合作 · Partnership</a>
                    <a class="ghost-button" href="#mission">了解使命</a>
                </div>
            </div>
        </section>
    </main>

    <footer>
        <div class="container">
            <div class="brand">
                <span class="brand-mark" aria-hidden="true"></span>
                Rare Light · Rare Disease Care for Children
            </div>
            <p>让世界看到稀有的光。With dignity, privacy, and calm design.</p>
        </div>
    </footer>
</div>
</body>
</html>
