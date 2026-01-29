<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>项目日志 · Rare Light</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="assets/css/main.css">
    <style>
        body.blog {
            background: radial-gradient(circle at 15% 18%, rgba(59, 130, 246, 0.12), transparent 25%),
                radial-gradient(circle at 80% 12%, rgba(45, 212, 191, 0.12), transparent 25%),
                #f8fafc;
        }

        .blog-hero {
            padding: clamp(2.5rem, 6vw, 4rem) 0 2rem;
        }

        .blog-hero .hero-panel {
            border-radius: 22px;
            background: linear-gradient(135deg, #0f172a, #1d4ed8);
            color: #fff;
            box-shadow: 0 24px 80px rgba(15, 23, 42, 0.35);
        }

        .blog-hero .hero-pill {
            background: rgba(255, 255, 255, 0.18);
            color: #e0f2fe;
        }

        .blog-layout {
            display: grid;
            grid-template-columns: minmax(0, 1fr) minmax(0, 320px);
            gap: 1.5rem;
        }

        .post-card {
            border-radius: 18px;
            border: 1px solid rgba(148, 163, 184, 0.2);
            background: rgba(255, 255, 255, 0.94);
            box-shadow: 0 16px 36px rgba(15, 23, 42, 0.08);
            padding: 1.4rem 1.5rem;
        }

        .post-meta {
            display: flex;
            flex-wrap: wrap;
            gap: 0.5rem 0.75rem;
            color: #64748b;
            font-size: 0.9rem;
        }

        .post-title {
            font-size: 1.35rem;
            font-weight: 700;
            margin: 0.6rem 0 0.6rem;
        }

        .post-tags {
            display: flex;
            flex-wrap: wrap;
            gap: 0.4rem;
        }

        .post-tags span {
            padding: 0.15rem 0.6rem;
            border-radius: 999px;
            background: rgba(99, 102, 241, 0.12);
            color: #3730a3;
            font-size: 0.78rem;
            font-weight: 600;
        }

        .post-body {
            color: #475569;
            line-height: 1.7;
        }

        .sticky-side {
            position: sticky;
            top: 110px;
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }

        .side-card {
            border-radius: 18px;
            border: 1px solid rgba(148, 163, 184, 0.16);
            background: rgba(255, 255, 255, 0.92);
            padding: 1.25rem 1.3rem;
            box-shadow: 0 14px 30px rgba(15, 23, 42, 0.08);
        }

        .side-card h3 {
            font-size: 1.05rem;
            margin-bottom: 0.75rem;
        }

        @media (max-width: 992px) {
            .blog-layout {
                grid-template-columns: minmax(0, 1fr);
            }
            .sticky-side {
                position: static;
            }
        }
    </style>
</head>
<body class="app-shell blog">
<nav class="navbar navbar-expand-lg app-navbar">
    <div class="container-xxl py-3 px-3 px-lg-4 w-100 d-flex align-items-center gap-3 flex-wrap">
        <div class="d-flex align-items-center gap-3">
            <div class="brand-glow">RL</div>
            <div class="d-flex flex-column">
                <span class="brand-eyebrow text-uppercase">RARE LIGHT</span>
                <span class="navbar-brand p-0 m-0 fw-semibold">项目日志</span>
            </div>
        </div>
        <div class="d-flex flex-wrap align-items-center gap-2 ms-auto">
            <a class="btn btn-outline-secondary btn-sm rounded-pill" href="/rarelight/">返回首页</a>
            <a class="btn btn-outline-primary btn-sm rounded-pill" href="/rarelight/dashboard">进入课堂</a>
        </div>
    </div>
</nav>

<section class="blog-hero">
    <div class="container-xxl hero-container">
        <div class="hero-panel student-hero p-4 p-lg-5">
            <div class="hero-eyebrow">Rare Light · 项目展示</div>
            <div class="hero-main">
                <div class="hero-copy">
                    <h1 class="hero-title mb-3">项目日志 / Blog</h1>
                    <p class="hero-subtitle mb-0">这里记录项目进展、需求调研与阶段成果。后续内容可持续补充。</p>
                </div>
                <div class="hero-meta">
                    <span class="hero-pill">可持续更新</span>
                    <span class="hero-pill soft">面向展示与汇报</span>
                </div>
            </div>
        </div>
    </div>
</section>

<main class="container-xxl pb-5">
    <div class="blog-layout">
        <section class="d-flex flex-column gap-4" id="postList">
            <div class="post-card text-secondary">正在加载内容...</div>
        </section>

        <aside class="sticky-side">
            <div class="side-card">
                <h3>如何新增内容</h3>
                <ol class="mb-0 text-secondary">
                    <li>进入管理后台 → 项目日志</li>
                    <li>新增文章并保存</li>
                    <li>刷新本页面即可展示</li>
                </ol>
                <div class="mt-3">
                    <a class="btn btn-outline-primary btn-sm" href="/rarelight/admin">进入后台编辑</a>
                </div>
            </div>
            <div class="side-card">
                <h3>展示建议</h3>
                <ul class="mb-0 text-secondary">
                    <li>每周 1 条进展</li>
                    <li>阶段性放截图或链接</li>
                    <li>突出“产出与进度”</li>
                </ul>
            </div>
        </aside>
    </div>
</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
    const BASE_PATH = '/rarelight';
    const API_BASE = `${BASE_PATH}/api`;
    const postListEl = document.getElementById('postList');

    function escapeHtml(str = '') {
        return String(str)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/\"/g, '&quot;')
            .replace(/'/g, '&#39;');
    }

    function summarize(text = '', maxLength = 140) {
        const clean = String(text).replace(/\s+/g, ' ').trim();
        if (clean.length <= maxLength) return clean;
        return `${clean.slice(0, maxLength)}…`;
    }

    function formatDate(value) {
        if (!value) return '';
        const date = new Date(value);
        if (Number.isNaN(date.getTime())) return value;
        return `${date.getFullYear()}-${String(date.getMonth() + 1).padStart(2, '0')}-${String(date.getDate()).padStart(2, '0')}`;
    }

    function renderPosts(posts) {
        postListEl.innerHTML = '';
        if (!Array.isArray(posts) || posts.length === 0) {
            postListEl.innerHTML = '<div class="post-card text-secondary">暂无日志内容，请先在后台新增文章。</div>';
            return;
        }
        posts.forEach((post) => {
            const tags = (post.tags || '').split(',').map((tag) => tag.trim()).filter(Boolean);
            const summary = post.summary && post.summary.trim() ? post.summary : summarize(post.content || '', 160);
            const metaParts = [
                formatDate(post.created_at),
                post.author ? `负责人：${escapeHtml(post.author)}` : ''
            ].filter(Boolean);
            const tagHtml = tags.length
                ? `<div class="post-tags">${tags.map((tag) => `<span>${escapeHtml(tag)}</span>`).join('')}</div>`
                : '';
            const card = document.createElement('article');
            card.className = 'post-card';
            card.innerHTML = `
                <div class="post-meta">${metaParts.map((item) => `<span>${item}</span>`).join('')}</div>
                <h2 class="post-title">${escapeHtml(post.title || '未命名文章')}</h2>
                ${tagHtml}
                <p class="post-body mb-0">${escapeHtml(summary)}</p>
            `;
            postListEl.appendChild(card);
        });
    }

    async function loadPosts() {
        try {
            const response = await fetch(`${API_BASE}/blog_posts.php`);
            const data = await response.json();
            if (!response.ok) {
                throw new Error(data && (data.message || data.error) || '加载失败');
            }
            renderPosts(data.posts || []);
        } catch (error) {
            postListEl.innerHTML = `<div class="post-card text-danger">加载失败：${escapeHtml(error.message)}</div>`;
        }
    }

    loadPosts();
</script>
</body>
</html>
