<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>网课系统 · 我的课堂</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/main.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/plyr@3.7.8/dist/plyr.css">
    <style>
        .course-header {
            display: flex;
            flex-direction: column;
            gap: 0.85rem;
            margin-bottom: 2.25rem;
        }

        .course-header h1 {
            margin: 0;
            font-size: 2rem;
            letter-spacing: -0.02em;
        }

        .course-header p {
            margin: 0;
            color: var(--text-secondary);
            line-height: 1.7;
        }

        .course-meta {
            display: flex;
            flex-wrap: wrap;
            gap: 0.75rem;
            align-items: center;
        }

        .course-meta .badge {
            background: rgba(79, 70, 229, 0.12);
            color: var(--brand-color-strong);
        }

        .lesson-card footer {
            margin-top: 1rem;
            display: flex;
            justify-content: flex-end;
            font-size: 0.85rem;
            color: var(--text-secondary);
        }

        .course-list-container {
            display: grid;
            gap: 1rem;
        }

        .course-empty {
            border-radius: var(--radius-md);
            padding: 1.75rem;
            text-align: center;
            color: var(--text-secondary);
            background: rgba(148, 163, 184, 0.08);
            line-height: 1.6;
        }

        .skeleton {
            border-radius: var(--radius-sm);
            background: linear-gradient(90deg, rgba(226, 232, 240, 0.5), rgba(226, 232, 240, 0.2), rgba(226, 232, 240, 0.5));
            background-size: 400% 400%;
            animation: shimmer 1.5s ease infinite;
            height: 64px;
        }

        @keyframes shimmer {
            0% { background-position: 100% 0; }
            100% { background-position: 0 0; }
        }
    </style>
</head>
<body class="app-shell">
<header class="app-header">
    <div class="inner">
        <div>
            <div class="brand">智能录播课堂</div>
            <p class="text-muted" id="welcomeText">正在加载...</p>
        </div>
        <div style="display: flex; align-items: center; gap: 0.75rem; flex-wrap: wrap;">
            <div class="user-chip" id="userChip"></div>
            <button class="ghost-button" id="adminButton" style="display:none;">进入管理后台</button>
            <button class="ghost-button" id="logoutButton">退出登录</button>
        </div>
    </div>
</header>
<main class="app-main split-2">
    <aside class="sidebar card">
        <h2>我的课程</h2>
        <p class="text-muted" style="margin-top: -0.35rem;">选择课程查看详细课节与录播内容。</p>
        <div class="course-list-container" id="courseList">
            <div class="skeleton"></div>
            <div class="skeleton" style="width: 80%;"></div>
            <div class="skeleton" style="width: 65%;"></div>
        </div>
    </aside>
    <section class="card surface-section">
        <div class="course-header">
            <h1 id="courseTitle">欢迎来到课堂</h1>
            <p id="courseDescription">从左侧选择课程开始观看您的录播内容。</p>
            <div class="course-meta" id="courseMeta" hidden>
                <span class="badge" id="lessonCount"></span>
            </div>
        </div>
        <div class="lesson-list" id="lessonList">
            <div class="empty-state">暂未选择课程。</div>
        </div>
    </section>
</main>
<script src="https://cdn.jsdelivr.net/npm/plyr@3.7.8/dist/plyr.polyfilled.min.js"></script>
<script>
    const API_BASE = 'api';
    const courseListEl = document.getElementById('courseList');
    const courseTitleEl = document.getElementById('courseTitle');
    const courseDescriptionEl = document.getElementById('courseDescription');
    const lessonListEl = document.getElementById('lessonList');
    const lessonCountEl = document.getElementById('lessonCount');
    const courseMetaEl = document.getElementById('courseMeta');
    const welcomeTextEl = document.getElementById('welcomeText');
    const userChipEl = document.getElementById('userChip');
    const logoutButton = document.getElementById('logoutButton');
    const adminButton = document.getElementById('adminButton');

    let currentUser = null;
    let currentCourseId = null;
    let players = [];

    function showWelcome(user) {
        welcomeTextEl.textContent = user ? `欢迎回来，${user.display_name || user.username}` : '欢迎来到课堂';
        if (user) {
            userChipEl.textContent = `${user.display_name || user.username} · ${user.role === 'admin' ? '管理员' : '学员'}`;
        }
    }

    function clearPlayers() {
        players.forEach(player => {
            if (player && typeof player.destroy === 'function') {
                player.destroy();
            }
        });
        players = [];
    }

    function guessMime(url) {
        if (!url) return '';
        const clean = url.split('?')[0].split('#')[0];
        const ext = clean.substring(clean.lastIndexOf('.') + 1).toLowerCase();
        switch (ext) {
            case 'mp4': return 'video/mp4';
            case 'webm': return 'video/webm';
            case 'ogg':
            case 'ogv': return 'video/ogg';
            case 'm3u8': return 'application/x-mpegURL';
            default: return '';
        }
    }

    function buildPlayer(url) {
        const wrapper = document.createElement('div');
        wrapper.className = 'player';
        if (!url) {
            const placeholder = document.createElement('div');
            placeholder.className = 'empty-state';
            placeholder.style.margin = 0;
            placeholder.textContent = '该课节尚未提供视频链接';
            wrapper.appendChild(placeholder);
            return { wrapper };
        }
        const trimmed = url.trim();
        if (!trimmed) {
            const placeholder = document.createElement('div');
            placeholder.className = 'empty-state';
            placeholder.style.margin = 0;
            placeholder.textContent = '该课节尚未提供视频链接';
            wrapper.appendChild(placeholder);
            return { wrapper };
        }
        const bilibiliEmbedRegex = /player\.bilibili\.com/i;
        const bilibiliBvMatch = trimmed.match(/bilibili\.com\/video\/(BV[\w]+)/i);
        const bilibiliAvMatch = trimmed.match(/bilibili\.com\/video\/av(\d+)/i);
        if (bilibiliEmbedRegex.test(trimmed) || bilibiliBvMatch || bilibiliAvMatch) {
            let embedUrl = trimmed;
            let page = 1;
            try {
                const urlObj = new URL(trimmed, window.location.href);
                const pageParam = parseInt(urlObj.searchParams.get('p'), 10);
                if (!Number.isNaN(pageParam) && pageParam > 0) {
                    page = pageParam;
                }
            } catch (error) {
                // ignore
            }
            if (bilibiliBvMatch) {
                const bvid = bilibiliBvMatch[1];
                embedUrl = `https://player.bilibili.com/player.html?bvid=${encodeURIComponent(bvid)}&page=${page}&high_quality=1&autoplay=0`;
            } else if (bilibiliAvMatch) {
                const aid = bilibiliAvMatch[1];
                embedUrl = `https://player.bilibili.com/player.html?aid=${encodeURIComponent(aid)}&page=${page}&high_quality=1&autoplay=0`;
            }
            const iframe = document.createElement('iframe');
            iframe.src = embedUrl;
            iframe.allowFullscreen = true;
            iframe.referrerPolicy = 'no-referrer';
            iframe.setAttribute('allow', 'fullscreen; picture-in-picture');
            wrapper.appendChild(iframe);
            return { wrapper };
        }
        const video = document.createElement('video');
        video.setAttribute('playsinline', '');
        video.setAttribute('controls', '');
        video.setAttribute('preload', 'metadata');
        video.setAttribute('controlsList', 'nodownload');
        const source = document.createElement('source');
        source.src = trimmed;
        const mime = guessMime(trimmed);
        if (mime) {
            source.type = mime;
        }
        video.appendChild(source);
        wrapper.appendChild(video);
        return { wrapper, video };
    }

    function renderLessons(lessons) {
        clearPlayers();
        lessonListEl.innerHTML = '';
        if (!lessons || lessons.length === 0) {
            const empty = document.createElement('div');
            empty.className = 'empty-state';
            empty.textContent = '该课程暂时还没有课节内容。';
            lessonListEl.appendChild(empty);
            courseMetaEl.hidden = true;
            return;
        }
        lessonCountEl.textContent = `${lessons.length} 个课节`;
        courseMetaEl.hidden = false;
        lessons.forEach((lesson, index) => {
            const card = document.createElement('article');
            card.className = 'lesson-card fade-in';
            const heading = document.createElement('h3');
            heading.textContent = lesson.title || `课节 ${index + 1}`;
            const media = buildPlayer(lesson.video_url || '');
            media.wrapper.style.minHeight = '260px';
            const footer = document.createElement('footer');
            footer.textContent = `课节 ${index + 1}`;
            card.appendChild(heading);
            card.appendChild(media.wrapper);
            card.appendChild(footer);
            lessonListEl.appendChild(card);
            if (media.video) {
                const player = new Plyr(media.video, {
                    controls: ['play-large', 'play', 'progress', 'current-time', 'mute', 'volume', 'settings', 'fullscreen'],
                    settings: ['speed', 'quality'],
                    ratio: '16:9'
                });
                players.push(player);
            }
        });
    }

    function updateCourseHeader(course) {
        if (!course) {
            courseTitleEl.textContent = '欢迎来到课堂';
            courseDescriptionEl.textContent = '从左侧选择课程开始观看您的录播内容。';
            courseMetaEl.hidden = true;
            return;
        }
        courseTitleEl.textContent = course.title || '未命名课程';
        courseDescriptionEl.textContent = course.description ? course.description : '该课程暂无详细介绍。';
    }

    function renderCourseList(courses) {
        courseListEl.innerHTML = '';
        if (!courses || courses.length === 0) {
            const empty = document.createElement('div');
            empty.className = 'course-empty';
            empty.innerHTML = '暂未为您分配课程，请联系管理员。';
            courseListEl.appendChild(empty);
            updateCourseHeader();
            lessonListEl.innerHTML = '<div class="empty-state">暂无课程内容</div>';
            return;
        }
        courses.forEach((course, index) => {
            const item = document.createElement('button');
            item.type = 'button';
            item.className = 'course-item';
            item.dataset.courseId = course.id;
            item.innerHTML = `<strong>${course.title}</strong><div class="text-muted" style="margin-top:0.35rem;">${course.description || '暂无描述'}</div>`;
            item.addEventListener('click', () => selectCourse(course.id));
            courseListEl.appendChild(item);
            if (index === 0) {
                selectCourse(course.id);
            }
        });
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
            const message = data?.message || '请求失败';
            throw new Error(message);
        }
        return data;
    }

    async function selectCourse(courseId) {
        if (courseId === currentCourseId) {
            return;
        }
        currentCourseId = courseId;
        document.querySelectorAll('.course-item').forEach((el) => {
            el.classList.toggle('active', Number(el.dataset.courseId) === courseId);
        });
        try {
            const data = await fetchJSON(`${API_BASE}/courses.php?id=${courseId}`);
            updateCourseHeader(data.course);
            renderLessons(data.lessons || []);
        } catch (error) {
            updateCourseHeader();
            lessonListEl.innerHTML = `<div class="empty-state">加载课程内容失败：${error.message}</div>`;
        }
    }

    async function loadCourses() {
        try {
            const data = await fetchJSON(`${API_BASE}/courses.php`);
            renderCourseList(data.courses || []);
        } catch (error) {
            courseListEl.innerHTML = `<div class="course-empty">无法加载课程列表：${error.message}</div>`;
            updateCourseHeader();
            lessonListEl.innerHTML = '<div class="empty-state">请稍后再试</div>';
        }
    }

    async function loadSession() {
        try {
            const data = await fetchJSON(`${API_BASE}/session.php`);
            if (!data.user) {
                window.location.href = 'index.php';
                return;
            }
            currentUser = data.user;
            showWelcome(currentUser);
            if (currentUser.role === 'admin') {
                adminButton.style.display = 'inline-flex';
            }
            await loadCourses();
        } catch (error) {
            window.location.href = 'index.php';
        }
    }

    logoutButton.addEventListener('click', async () => {
        try {
            await fetchJSON(`${API_BASE}/logout.php`, { method: 'POST' });
        } catch (error) {
            console.error(error);
        }
        window.location.href = 'index.php';
    });

    adminButton.addEventListener('click', () => {
        window.location.href = 'admin.php';
    });

    loadSession();
</script>
</body>
</html>
