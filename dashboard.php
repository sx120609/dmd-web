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
            padding: 1.1rem 1.35rem;
            color: var(--text-secondary);
            background: rgba(255, 255, 255, 0.78);
            border: 1px dashed rgba(148, 163, 184, 0.32);
            line-height: 1.6;
        }

        .lesson-empty {
            text-align: center;
        }

        .skeleton {
            border-radius: 16px;
            background: linear-gradient(90deg, rgba(226, 232, 240, 0.55), rgba(226, 232, 240, 0.18), rgba(226, 232, 240, 0.55));
            background-size: 400% 400%;
            animation: shimmer 1.6s ease infinite;
            height: 52px;
        }

        @keyframes shimmer {
            0% {
                background-position: 100% 0;
            }
            100% {
                background-position: 0 0;
            }
        }

        .workspace-lead.glass-panel {
            padding: 1.5rem 1.6rem;
        }

        .workspace-lead p {
            margin: 0;
            color: var(--text-secondary);
            line-height: 1.6;
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
<div class="drawer-backdrop" id="drawerBackdrop"></div>
<main class="dashboard-main">
    <div class="dashboard-layout">
        <aside class="dashboard-sidebar glass-panel drawer-panel" aria-label="课程导航">
            <header class="panel-header">
                <div>
                    <h2>我的课程</h2>
                    <p>挑选一个课程继续学习。</p>
                </div>
                <button type="button" class="drawer-close" id="courseDrawerClose">关闭</button>
            </header>
            <div class="nav-list" id="courseList">
                <div class="skeleton"></div>
                <div class="skeleton" style="width: 82%;"></div>
                <div class="skeleton" style="width: 65%;"></div>
            </div>
        </aside>
        <section class="workspace">
            <div class="mobile-controls">
                <button type="button" class="mobile-drawer-trigger" id="courseDrawerToggle" data-drawer="course">选择课程</button>
                <button type="button" class="mobile-drawer-trigger" id="lessonDrawerToggle" data-drawer="lesson">选择课节</button>
            </div>
            <section class="lesson-overview glass-panel">
                <div class="overview-top">
                    <div class="breadcrumbs" id="breadcrumbs">
                        <span>网课</span>
                    </div>
                    <div class="overview-heading">
                        <h1 id="workspaceHeading">我的课堂</h1>
                        <p id="workspaceIntro">从左侧选择课程，即可在右侧查看课节详情。</p>
                    </div>
                </div>
                <div class="overview-bottom">
                    <div class="overview-info">
                        <h3 id="courseSummaryTitle">尚未选择课程</h3>
                        <p id="courseSummaryDescription">从左侧课程列表中选择一个课程开始学习。</p>
                    </div>
                    <div class="course-summary-meta">
                        <span class="chip" id="courseLessonCount">0 个课节</span>
                        <span class="chip subtle" id="courseStatusChip" hidden>待选课</span>
                    </div>
                </div>
            </section>
            <div class="lesson-columns lesson-deck">
                <section class="lesson-panel glass-panel drawer-panel" aria-label="课节导航">
                    <header class="panel-header">
                        <div>
                            <h3 id="lessonPaneTitle">课节</h3>
                            <p id="lessonPaneHint">先选择课程以加载课节。</p>
                        </div>
                        <button type="button" class="drawer-close" id="lessonDrawerClose">关闭</button>
                    </header>
                    <div class="nav-list" id="lessonList">
                        <div class="lesson-empty">暂未选择课程。</div>
                    </div>
                </section>
                <article class="lesson-stage glass-panel" aria-live="polite">
                    <header class="stage-header">
                        <h1 id="lessonTitle">欢迎来到课堂</h1>
                        <p id="lessonDescription">从左侧依次选择课程与课节即可开始学习。</p>
                    </header>
                    <div class="stage-meta" id="lessonMeta" hidden>
                        <span class="badge" id="courseBadge"></span>
                        <span class="badge" id="lessonBadge"></span>
                    </div>
                    <p class="stage-hint" id="stageHint">尚未选择课节。</p>
                    <div class="player-stage" id="playerHost">
                        <div class="empty-state">尚未选择课节。</div>
                    </div>
                </article>
            </div>
        </section>
    </div>
</main>
<script src="https://cdn.jsdelivr.net/npm/plyr@3.7.8/dist/plyr.polyfilled.min.js"></script>
<script>
    const API_BASE = 'api';

    function normalizeApiUrl(url) {
        if (url.startsWith(`${API_BASE}/`)) {
            const [path, query] = url.split('?');
            const sanitizedPath = path.replace(/\.php$/, '');
            return query ? `${sanitizedPath}?${query}` : sanitizedPath;
        }
        return url;
    }
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
    const workspaceHeadingEl = document.getElementById('workspaceHeading');
    const workspaceIntroEl = document.getElementById('workspaceIntro');
    const courseSummaryTitleEl = document.getElementById('courseSummaryTitle');
    const courseSummaryDescriptionEl = document.getElementById('courseSummaryDescription');
    const courseLessonCountEl = document.getElementById('courseLessonCount');
    const courseStatusChipEl = document.getElementById('courseStatusChip');
    const stageHintEl = document.getElementById('stageHint');
    const drawerBackdrop = document.getElementById('drawerBackdrop');
    const courseDrawerToggle = document.getElementById('courseDrawerToggle');
    const lessonDrawerToggle = document.getElementById('lessonDrawerToggle');
    const courseDrawerClose = document.getElementById('courseDrawerClose');
    const lessonDrawerClose = document.getElementById('lessonDrawerClose');
    const mobileQuery = window.matchMedia('(max-width: 768px)');

    let currentUser = null;
    let currentCourseId = null;
    let currentLessonId = null;
    let currentLessons = [];
    let currentCourse = null;
    let players = [];

    function closeAllDrawers() {
        document.body.classList.remove('course-drawer-open', 'lesson-drawer-open');
    }

    function openDrawer(type) {
        closeAllDrawers();
        document.body.classList.add(`${type}-drawer-open`);
    }

    const handleDesktopSwitch = (event) => {
        if (!event.matches) {
            closeAllDrawers();
        }
    };

    if (mobileQuery?.addEventListener) {
        mobileQuery.addEventListener('change', handleDesktopSwitch);
    } else if (mobileQuery?.addListener) {
        mobileQuery.addListener(handleDesktopSwitch);
    }

    courseDrawerToggle?.addEventListener('click', () => openDrawer('course'));
    lessonDrawerToggle?.addEventListener('click', () => openDrawer('lesson'));
    courseDrawerClose?.addEventListener('click', closeAllDrawers);
    lessonDrawerClose?.addEventListener('click', closeAllDrawers);
    drawerBackdrop?.addEventListener('click', closeAllDrawers);

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape' && (document.body.classList.contains('course-drawer-open') || document.body.classList.contains('lesson-drawer-open'))) {
            event.preventDefault();
            closeAllDrawers();
        }
    });

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

    function setCourseSummary(title, description, lessonCountText = '0 个课节', statusText = '') {
        courseSummaryTitleEl.textContent = title;
        courseSummaryDescriptionEl.textContent = description;
        courseLessonCountEl.textContent = lessonCountText;
        courseStatusChipEl.textContent = statusText;
        courseStatusChipEl.hidden = !statusText;
    }

    function updateCourseSummary(course, lessonCount = 0) {
        if (!course) {
            setCourseSummary('暂无课程', '暂未为您分配课程，请联系管理员。', '0 个课节', '待分配');
            return;
        }
        const description = course.description && course.description.trim() ? course.description : '该课程暂无简介。';
        const statusText = lessonCount > 0 ? '学习中' : '准备中';
        setCourseSummary(course.title || '未命名课程', description, `${lessonCount} 个课节`, statusText);
    }

    function setStageHint(message, hidden = false) {
        if (!stageHintEl) return;
        stageHintEl.textContent = message;
        stageHintEl.hidden = hidden;
    }

    function renderLessonList(lessons, course) {
        closeAllDrawers();
        currentCourse = course || null;
        currentLessons = lessons || [];
        currentLessonId = null;
        clearPlayers();
        playerHostEl.innerHTML = '<div class="empty-state">尚未选择课节。</div>';
        lessonListEl.innerHTML = '';
        updateCourseSummary(currentCourse, currentLessons.length);
        workspaceHeadingEl.textContent = currentCourse ? (currentCourse.title || '未命名课程') : '我的课堂';
        if (!currentLessons.length) {
            lessonBadgeEl.textContent = '0 个课节';
            courseBadgeEl.textContent = currentCourse ? `课程 · ${currentCourse.title || '未命名课程'}` : '课程';
            const empty = document.createElement('div');
            empty.className = 'lesson-empty';
            empty.textContent = '课程内容准备中。';
            lessonListEl.appendChild(empty);
            lessonPaneHintEl.textContent = '老师正在准备课节内容。';
            lessonMetaEl.hidden = true;
            lessonTitleEl.textContent = currentCourse ? currentCourse.title || '未命名课程' : '欢迎来到课堂';
            lessonDescriptionEl.textContent = currentCourse && currentCourse.description ? currentCourse.description : '敬请期待课程内容。';
            workspaceIntroEl.textContent = '课程暂无课节内容，稍后再来看看。';
            setStageHint('课程暂无课节内容。', false);
            updateBreadcrumbs(currentCourse);
            return;
        }
        lessonPaneHintEl.textContent = '选择课节开始学习。';
        lessonMetaEl.hidden = false;
        courseBadgeEl.textContent = currentCourse ? `课程 · ${currentCourse.title || '未命名课程'}` : '课程';
        lessonBadgeEl.textContent = `${currentLessons.length} 个课节`;
        workspaceIntroEl.textContent = `共有 ${currentLessons.length} 个课节，选择一个开始学习。`;
        currentLessons.forEach((lesson, index) => {
            const button = document.createElement('button');
            button.type = 'button';
            button.className = 'nav-button fade-in';
            button.dataset.lessonId = lesson.id;
            const order = String(index + 1).padStart(2, '0');
            button.innerHTML = `
                <div class="nav-button-head">
                    <span class="nav-index">${order}</span>
                    <strong>${lesson.title || `课节 ${index + 1}`}</strong>
                </div>
                <div class="nav-desc">${lesson.description || '点击查看详情'}</div>
            `;
            button.addEventListener('click', () => selectLesson(lesson.id));
            lessonListEl.appendChild(button);
        });
        setStageHint('选择左侧课节播放视频。');
        selectLesson(currentLessons[0].id);
    }

    function renderCourseList(courses) {
        closeAllDrawers();
        courseListEl.innerHTML = '';
        if (!courses || courses.length === 0) {
            const empty = document.createElement('div');
            empty.className = 'course-empty';
            empty.textContent = '暂未为您分配课程，请联系管理员。';
            courseListEl.appendChild(empty);
            lessonPaneTitleEl.textContent = '课节';
            lessonPaneHintEl.textContent = '等待分配课程后即可在此查看课节。';
            lessonListEl.innerHTML = '<div class="lesson-empty">暂无课程。</div>';
            lessonMetaEl.hidden = true;
            courseBadgeEl.textContent = '课程';
            lessonBadgeEl.textContent = '0 个课节';
            lessonTitleEl.textContent = '欢迎来到课堂';
            lessonDescriptionEl.textContent = '待分配课程后将在此显示课节内容。';
            workspaceHeadingEl.textContent = '我的课堂';
            workspaceIntroEl.textContent = '暂无课程，联系管理员开通访问。';
            setCourseSummary('暂无课程', '暂未为您分配课程，请联系管理员。', '0 个课节', '待分配');
            setStageHint('等待分配课程。', false);
            updateBreadcrumbs();
            return;
        }
        workspaceHeadingEl.textContent = '我的课堂';
        workspaceIntroEl.textContent = `已为您分配 ${courses.length} 门课程。`;
        courses.forEach((course) => {
            const item = document.createElement('button');
            item.type = 'button';
            item.className = 'nav-button course-button';
            item.dataset.courseId = course.id;
            item.innerHTML = `
                <div class="nav-button-head">
                    <strong>${course.title}</strong>
                </div>
                <div class="nav-desc">${course.description || '暂无描述'}</div>
            `;
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
            const message = data?.message || data?.error || '请求失败';
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
            lessonDescriptionEl.textContent = '请稍后重试或联系管理员排查问题。';
            workspaceHeadingEl.textContent = '课程加载失败';
            workspaceIntroEl.textContent = '课程内容暂时不可用，请稍后刷新。';
            setCourseSummary('课程加载失败', '无法加载课程详情，请稍后重试。', '0 个课节', '加载失败');
            setStageHint('课程内容暂时不可用，请稍后重试。', false);
            closeAllDrawers();
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
        closeAllDrawers();
        currentLessonId = normalizedLessonId;
        highlightLesson(currentLessonId);
        clearPlayers();
        const { wrapper, video } = buildPlayer(lesson.video_url || '');
        playerHostEl.innerHTML = '';
        playerHostEl.appendChild(wrapper);
        lessonTitleEl.textContent = lesson.title || '课节';
        lessonDescriptionEl.textContent = lesson.description || '该课节暂无详细介绍。';
        workspaceIntroEl.textContent = `正在观看「${lesson.title || '课节'}」`;
        updateBreadcrumbs(currentCourse, lesson);
        setStageHint('', true);
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
            lessonPaneHintEl.textContent = '请稍后刷新重试。';
            lessonListEl.innerHTML = '<div class="lesson-empty">暂无课程内容</div>';
            lessonMetaEl.hidden = true;
            lessonTitleEl.textContent = '课程加载失败';
            lessonDescriptionEl.textContent = '无法获取课程列表，请稍后重试。';
            workspaceHeadingEl.textContent = '课程加载失败';
            workspaceIntroEl.textContent = '课程列表暂时不可用，请稍后刷新。';
            setCourseSummary('课程加载失败', '无法获取课程列表，请稍后重试。', '0 个课节', '加载失败');
            setStageHint('课程列表暂时不可用，请稍后重试。', false);
            updateBreadcrumbs();
            closeAllDrawers();
        }
    }

    async function loadSession() {
        try {
            const data = await fetchJSON(`${API_BASE}/session.php`);
            if (!data.user) {
                window.location.href = 'login';
                return;
            }
            currentUser = data.user;
            showWelcome(currentUser);
            if (currentUser.role === 'admin') {
                adminButton.style.display = 'inline-flex';
            }
            await loadCourses();
        } catch (error) {
            window.location.href = 'login';
        }
    }

    logoutButton.addEventListener('click', async () => {
        try {
            await fetchJSON(`${API_BASE}/logout.php`, { method: 'POST' });
        } catch (error) {
            console.error(error);
        }
        window.location.href = 'login';
    });

    adminButton.addEventListener('click', () => {
        window.location.href = 'admin';
    });

    loadSession();
</script>
</body>
</html>
