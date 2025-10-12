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
        .course-empty,
        .lesson-empty {
            border-radius: 18px;
            padding: 1.25rem 1.4rem;
            color: var(--text-secondary);
            background: rgba(255, 255, 255, 0.82);
            border: 1px dashed rgba(148, 163, 184, 0.35);
            line-height: 1.6;
        }

        .course-empty {
            text-align: left;
        }

        .lesson-empty {
            text-align: center;
        }

        .skeleton {
            border-radius: 16px;
            background: linear-gradient(90deg, rgba(226, 232, 240, 0.55), rgba(226, 232, 240, 0.2), rgba(226, 232, 240, 0.55));
            background-size: 400% 400%;
            animation: shimmer 1.6s ease infinite;
            height: 52px;
        }

        @keyframes shimmer {
            0% { background-position: 100% 0; }
            100% { background-position: 0 0; }
        }
    </style>
</head>
<body class="dashboard-shell">
<header class="topbar">
    <div class="topbar-inner">
        <div class="topbar-branding">
            <div class="brand">智能录播课堂</div>
            <p class="text-muted" id="welcomeText">正在加载...</p>
        </div>
        <div class="topbar-actions">
            <div class="user-chip" id="userChip"></div>
            <button class="ghost-button" id="adminButton" style="display:none;">进入管理后台</button>
            <button class="ghost-button" id="logoutButton">退出登录</button>
        </div>
    </div>
</header>
<main class="dashboard-main">
    <div class="dashboard-canvas">
        <aside class="primary-panel glass-panel" aria-label="课程导航">
            <header class="panel-header">
                <div>
                    <h2>我的课程</h2>
                    <p>挑选一个课程继续学习。</p>
                </div>
            </header>
            <div class="nav-list" id="courseList">
                <div class="skeleton"></div>
                <div class="skeleton" style="width: 80%;"></div>
                <div class="skeleton" style="width: 65%;"></div>
            </div>
        </aside>
        <section class="content-columns">
            <aside class="secondary-panel glass-panel" aria-label="课节导航">
                <header class="panel-header">
                    <div>
                        <h3 id="lessonPaneTitle">课节</h3>
                        <p id="lessonPaneHint">先选择课程以加载课节。</p>
                    </div>
                </header>
                <div class="nav-list" id="lessonList">
                    <div class="lesson-empty">暂未选择课程。</div>
                </div>
            </aside>
            <article class="lesson-stage glass-panel" aria-live="polite">
                <div class="breadcrumbs" id="breadcrumbs">
                    <span>网课</span>
                </div>
                <header class="lesson-header">
                    <h1 id="lessonTitle">欢迎来到课堂</h1>
                    <p id="lessonDescription">从左侧依次选择课程与课节即可开始学习。</p>
                </header>
                <div class="lesson-meta" id="lessonMeta" hidden>
                    <span class="badge" id="courseBadge"></span>
                    <span class="badge" id="lessonBadge"></span>
                </div>
                <div class="player-stage" id="playerHost">
                    <div class="empty-state">尚未选择课节。</div>
                </div>
            </article>
        </section>
    </div>
</main>
<script src="https://cdn.jsdelivr.net/npm/plyr@3.7.8/dist/plyr.polyfilled.min.js"></script>
<script>
    const API_BASE = 'api';
    const courseListEl = document.getElementById('courseList');
    const lessonListEl = document.getElementById('lessonList');
    const lessonPaneTitleEl = document.getElementById('lessonPaneTitle');
    const lessonPaneHintEl = document.getElementById('lessonPaneHint');
    const breadcrumbsEl = document.getElementById('breadcrumbs');
    const lessonTitleEl = document.getElementById('lessonTitle');
    const lessonDescriptionEl = document.getElementById('lessonDescription');
    const lessonMetaEl = document.getElementById('lessonMeta');
    const courseBadgeEl = document.getElementById('courseBadge');
    const lessonBadgeEl = document.getElementById('lessonBadge');
    const playerHostEl = document.getElementById('playerHost');
    const welcomeTextEl = document.getElementById('welcomeText');
    const userChipEl = document.getElementById('userChip');
    const logoutButton = document.getElementById('logoutButton');
    const adminButton = document.getElementById('adminButton');

    let currentUser = null;
    let currentCourseId = null;
    let currentLessonId = null;
    let currentLessons = [];
    let currentCourse = null;
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

    function updateBreadcrumbs(course, lesson) {
        const fragments = ['<span>网课</span>'];
        if (course) {
            fragments.push('<svg viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M7 4l6 6-6 6"/></svg>');
            fragments.push(`<span class="current">${course.title || '未命名课程'}</span>`);
        }
        if (lesson) {
            fragments.push('<svg viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M7 4l6 6-6 6"/></svg>');
            fragments.push(`<span class="current">${lesson.title || '课节'}</span>`);
        }
        breadcrumbsEl.innerHTML = fragments.join('');
    }

    function renderLessonList(lessons, course) {
        currentCourse = course || null;
        currentLessons = lessons || [];
        currentLessonId = null;
        clearPlayers();
        playerHostEl.innerHTML = '<div class="empty-state">尚未选择课节。</div>';
        lessonListEl.innerHTML = '';
        if (!currentLessons.length) {
            lessonBadgeEl.textContent = '0 个课节';
            courseBadgeEl.textContent = currentCourse ? `课程 · ${currentCourse.title || '未命名课程'}` : '课程';
            const empty = document.createElement('div');
            empty.className = 'lesson-empty';
            empty.textContent = '该课程暂时还没有课节内容。';
            lessonListEl.appendChild(empty);
            lessonPaneHintEl.textContent = '等待添加课节后即可在此选择。';
            lessonMetaEl.hidden = true;
            lessonTitleEl.textContent = currentCourse ? currentCourse.title || '未命名课程' : '欢迎来到课堂';
            lessonDescriptionEl.textContent = currentCourse && currentCourse.description ? currentCourse.description : '请等待老师发布课节内容。';
            updateBreadcrumbs(currentCourse);
            return;
        }
        lessonPaneHintEl.textContent = '选择一个课节即可开始观看。';
        lessonMetaEl.hidden = false;
        courseBadgeEl.textContent = currentCourse ? `课程 · ${currentCourse.title || '未命名课程'}` : '课程';
        lessonBadgeEl.textContent = `${currentLessons.length} 个课节`;
        currentLessons.forEach((lesson, index) => {
            const button = document.createElement('button');
            button.type = 'button';
            button.className = 'nav-button fade-in';
            button.dataset.lessonId = lesson.id;
            button.innerHTML = `<strong>${lesson.title || `课节 ${index + 1}`}</strong>` +
                `<span>${lesson.description || '点击查看详情'}</span>`;
            button.addEventListener('click', () => selectLesson(lesson.id));
            lessonListEl.appendChild(button);
        });
        selectLesson(currentLessons[0].id);
    }

    function renderCourseList(courses) {
        courseListEl.innerHTML = '';
        if (!courses || courses.length === 0) {
            const empty = document.createElement('div');
            empty.className = 'course-empty';
            empty.textContent = '暂未为您分配课程，请联系管理员。';
            courseListEl.appendChild(empty);
            lessonPaneTitleEl.textContent = '课节';
            lessonPaneHintEl.textContent = '先选择课程以加载课节。';
            lessonListEl.innerHTML = '<div class="lesson-empty">暂未选择课程。</div>';
            lessonMetaEl.hidden = true;
            courseBadgeEl.textContent = '课程';
            lessonBadgeEl.textContent = '0 个课节';
            lessonTitleEl.textContent = '欢迎来到课堂';
            lessonDescriptionEl.textContent = '从左侧依次选择课程与课节即可开始学习。';
            updateBreadcrumbs();
            return;
        }
        courses.forEach((course) => {
            const item = document.createElement('button');
            item.type = 'button';
            item.className = 'nav-button course-button';
            item.dataset.courseId = course.id;
            item.innerHTML = `<strong>${course.title}</strong><small>${course.description || '暂无描述'}</small>`;
            item.addEventListener('click', () => selectCourse(course.id));
            courseListEl.appendChild(item);
        });
        selectCourse(courses[0].id);
    }

    function highlightCourse(courseId) {
        document.querySelectorAll('#courseList .nav-button').forEach((el) => {
            el.classList.toggle('active', Number(el.dataset.courseId) === courseId);
        });
    }

    function highlightLesson(lessonId) {
        document.querySelectorAll('#lessonList .nav-button').forEach((el) => {
            el.classList.toggle('active', Number(el.dataset.lessonId) === lessonId);
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
        const normalizedCourseId = Number(courseId);
        if (normalizedCourseId === currentCourseId) {
            return;
        }
        currentCourseId = normalizedCourseId;
        highlightCourse(normalizedCourseId);
        try {
            const data = await fetchJSON(`${API_BASE}/courses.php?id=${normalizedCourseId}`);
            currentCourse = data.course || null;
            lessonPaneTitleEl.textContent = currentCourse?.title ? `${currentCourse.title} 的课节` : '课节';
            updateBreadcrumbs(currentCourse);
            renderLessonList(data.lessons || [], currentCourse);
        } catch (error) {
            currentCourse = null;
            lessonListEl.innerHTML = `<div class="lesson-empty">加载课程内容失败：${error.message}</div>`;
            lessonMetaEl.hidden = true;
            lessonTitleEl.textContent = '课程加载失败';
            lessonDescriptionEl.textContent = '请稍后再试，或联系管理员排查问题。';
        }
    }

    function selectLesson(lessonId) {
        const normalizedLessonId = Number(lessonId);
        if (!currentLessons.length) {
            return;
        }
        if (normalizedLessonId === currentLessonId) {
            return;
        }
        const lesson = currentLessons.find((item) => Number(item.id) === normalizedLessonId);
        if (!lesson) {
            return;
        }
        currentLessonId = normalizedLessonId;
        highlightLesson(currentLessonId);
        clearPlayers();
        const { wrapper, video } = buildPlayer(lesson.video_url || '');
        playerHostEl.innerHTML = '';
        playerHostEl.appendChild(wrapper);
        lessonTitleEl.textContent = lesson.title || '课节';
        lessonDescriptionEl.textContent = lesson.description || '该课节暂无详细介绍。';
        updateBreadcrumbs(currentCourse, lesson);
        if (video) {
            const player = new Plyr(video, {
                controls: ['play-large', 'play', 'progress', 'current-time', 'mute', 'volume', 'settings', 'fullscreen'],
                settings: ['speed', 'quality'],
                ratio: '16:9'
            });
            players.push(player);
        }
    }

    async function loadCourses() {
        try {
            const data = await fetchJSON(`${API_BASE}/courses.php`);
            renderCourseList(data.courses || []);
        } catch (error) {
            courseListEl.innerHTML = `<div class="course-empty">无法加载课程列表：${error.message}</div>`;
            lessonPaneTitleEl.textContent = '课节';
            lessonPaneHintEl.textContent = '请稍后再试加载课程。';
            lessonListEl.innerHTML = '<div class="lesson-empty">暂无课程内容</div>';
            lessonMetaEl.hidden = true;
            lessonTitleEl.textContent = '课程加载失败';
            lessonDescriptionEl.textContent = '无法获取课程列表，请稍后重试。';
            updateBreadcrumbs();
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
