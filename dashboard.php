<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>网课系统 · 我的课堂</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="assets/css/main.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/plyr@3.7.8/dist/plyr.css">
</head>
<body class="bg-body-tertiary">
<nav class="navbar navbar-expand-lg glass-navbar sticky-top">
    <div class="container-xxl py-2 px-3 px-lg-4">
        <div class="d-flex flex-column">
            <span class="navbar-brand p-0 m-0 fw-semibold">智能录播课堂</span>
            <small class="text-secondary" id="welcomeText">正在加载...</small>
        </div>
        <div class="d-flex flex-wrap gap-2 align-items-center ms-auto">
            <div class="chip" id="userChip" style="display:none;"></div>
            <button class="btn btn-outline-primary btn-sm" id="adminButton" style="display:none;">进入管理后台</button>
            <button class="btn btn-outline-secondary btn-sm" id="logoutButton">退出登录</button>
        </div>
    </div>
</nav>
<main class="dashboard-main py-5">
    <div class="container-xxl d-flex flex-column gap-4">
        <section class="dashboard-hero glass-panel gradient-surface">
            <div class="hero-text">
                <div class="small text-secondary mb-3 breadcrumbs" id="breadcrumbs"><span>网课</span></div>
                <h1 class="display-6 mb-2" id="workspaceHeading">我的课堂</h1>
                <p class="text-secondary mb-0" id="workspaceIntro">从左侧选择课程，即可在右侧查看课节详情。</p>
            </div>
            <div class="hero-summary">
                <div>
                    <h2 class="h5 mb-1" id="courseSummaryTitle">尚未选择课程</h2>
                    <p class="text-secondary small mb-3" id="courseSummaryDescription">从左侧课程列表中选择一个课程开始学习。</p>
                </div>
                <div class="d-flex flex-wrap gap-2">
                    <span class="badge rounded-pill" id="courseLessonCount">0 个课节</span>
                    <span class="badge rounded-pill badge-muted" id="courseStatusChip" hidden>待选课</span>
                </div>
            </div>
        </section>

        <section class="summary-grid">
            <article class="summary-card glass-panel">
                <div class="summary-icon bg-primary-subtle text-primary">
                    <i class="bi bi-journal-text"></i>
                </div>
                <div>
                    <h3 class="h6 mb-1">课程学习进度</h3>
                    <p class="text-secondary small mb-2">选择课程后即可查看课节进度，快速定位未完成的内容。</p>
                    <div class="progress" role="progressbar" aria-valuemin="0" aria-valuemax="100" aria-valuenow="0">
                        <div class="progress-bar" id="courseProgressBar" style="width: 0%;"></div>
                    </div>
                </div>
            </article>
            <article class="summary-card glass-panel">
                <div class="summary-icon bg-info-subtle text-info">
                    <i class="bi bi-lightning-charge"></i>
                </div>
                <div>
                    <h3 class="h6 mb-1">学习小贴士</h3>
                    <p class="text-secondary small mb-0">将课程加入收藏、设定学习计划，保持稳定的学习节奏。</p>
                </div>
            </article>
            <article class="summary-card glass-panel">
                <div class="summary-icon bg-success-subtle text-success">
                    <i class="bi bi-people"></i>
                </div>
                <div>
                    <h3 class="h6 mb-1">课堂公告</h3>
                    <p class="text-secondary small mb-0">随时关注教学公告与最新课节安排，不错过任何更新。</p>
                </div>
            </article>
        </section>

        <div class="dashboard-grid">
            <aside class="dashboard-sidebar">
                <div class="dashboard-card glass-panel">
                    <div class="card-header border-0 bg-transparent px-0 pt-0">
                        <h2 class="h5 mb-1">我的课程</h2>
                        <p class="text-secondary small mb-0">挑选一个课程继续学习。</p>
                    </div>
                    <div class="list-group list-group-flush course-list" id="courseList">
                        <div class="list-group-item bg-transparent">
                            <div class="placeholder-glow">
                                <span class="placeholder col-10"></span>
                            </div>
                        </div>
                        <div class="list-group-item bg-transparent">
                            <div class="placeholder-glow">
                                <span class="placeholder col-7"></span>
                            </div>
                        </div>
                        <div class="list-group-item bg-transparent">
                            <div class="placeholder-glow">
                                <span class="placeholder col-5"></span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="dashboard-card glass-panel">
                    <div class="card-header border-0 bg-transparent px-0 pt-0">
                        <div class="d-flex align-items-start justify-content-between">
                            <div>
                                <h3 class="h6 mb-1" id="lessonPaneTitle">课节</h3>
                                <p class="text-secondary small mb-0" id="lessonPaneHint">先选择课程以加载课节。</p>
                            </div>
                        </div>
                    </div>
                    <div class="list-group list-group-flush lesson-list" id="lessonList">
                        <div class="list-group-item text-center text-secondary small">暂未选择课程。</div>
                    </div>
                </div>
            </aside>
            <section class="dashboard-content">
                <div class="dashboard-card glass-panel stage-card">
                    <header class="mb-4">
                        <div class="d-flex flex-column flex-lg-row gap-3 align-items-start align-items-lg-center justify-content-between">
                            <div>
                                <h2 class="h4 mb-2" id="lessonTitle">欢迎来到课堂</h2>
                                <p class="text-secondary mb-0" id="lessonDescription">从左侧依次选择课程与课节即可开始学习。</p>
                            </div>
                            <div class="d-flex flex-wrap gap-2" id="lessonMeta" hidden>
                                <span class="badge rounded-pill" id="courseBadge"></span>
                                <span class="badge rounded-pill badge-info" id="lessonBadge"></span>
                            </div>
                        </div>
                    </header>
                    <div class="stage-hint" id="stageHint">尚未选择课节。</div>
                    <div class="player-stage" id="playerHost">
                        <div class="empty-state">尚未选择课节。</div>
                    </div>
                </div>
            </section>
        </div>
    </div>
</main>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
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
    const courseProgressBarEl = document.getElementById('courseProgressBar');
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
        if (userChipEl) {
            if (user) {
                userChipEl.textContent = `${user.display_name || user.username} · ${user.role === 'admin' ? '管理员' : '学员'}`;
                userChipEl.style.display = 'inline-flex';
            } else {
                userChipEl.textContent = '';
                userChipEl.style.display = 'none';
            }
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

    function tuneBilibiliUrl(url, page) {
        try {
            const urlObj = new URL(url, window.location.origin);
            if (page) {
                urlObj.searchParams.set('page', String(page));
            }
            urlObj.searchParams.set('as_wide', '1');
            urlObj.searchParams.set('high_quality', '1');
            urlObj.searchParams.set('autoplay', '0');
            urlObj.searchParams.set('danmaku', '0');
            urlObj.searchParams.set('muted', '0');
            return urlObj.toString();
        } catch (error) {
            return url;
        }
    }

    function buildPlayer(url) {
        const wrapper = document.createElement('div');
        wrapper.className = 'player';
        const wrapInFrame = (element) => {
            const frame = document.createElement('div');
            frame.className = 'ratio ratio-16x9 player-frame';
            frame.appendChild(element);
            wrapper.appendChild(frame);
        };
        if (!url || !url.trim()) {
            const placeholder = document.createElement('div');
            placeholder.className = 'empty-state';
            placeholder.style.margin = 0;
            placeholder.textContent = '该课节尚未提供视频链接';
            wrapper.appendChild(placeholder);
            return { wrapper };
        }
        const trimmed = url.trim();
        const bilibiliEmbedRegex = /player\.bilibili\.com/i;
        const bilibiliBvMatch = trimmed.match(/bilibili\.com\/video\/(BV[\w]+)/i);
        const bilibiliAvMatch = trimmed.match(/bilibili\.com\/video\/av(\d+)/i);
        let page = 1;
        try {
            const urlObj = new URL(trimmed, window.location.href);
            const pageParam = parseInt(urlObj.searchParams.get('p'), 10);
            if (!Number.isNaN(pageParam) && pageParam > 0) {
                page = pageParam;
            }
        } catch (error) {
            // ignore malformed url
        }
        if (bilibiliEmbedRegex.test(trimmed) || bilibiliBvMatch || bilibiliAvMatch) {
            let embedUrl = trimmed;
            if (bilibiliBvMatch) {
                const bvid = bilibiliBvMatch[1];
                embedUrl = `https://player.bilibili.com/player.html?bvid=${encodeURIComponent(bvid)}`;
            } else if (bilibiliAvMatch) {
                const aid = bilibiliAvMatch[1];
                embedUrl = `https://player.bilibili.com/player.html?aid=${encodeURIComponent(aid)}`;
            }
            embedUrl = tuneBilibiliUrl(embedUrl, page);
            const iframe = document.createElement('iframe');
            iframe.src = embedUrl;
            iframe.className = 'player-embed';
            iframe.allowFullscreen = true;
            iframe.referrerPolicy = 'no-referrer';
            iframe.setAttribute('allow', 'fullscreen; picture-in-picture');
            iframe.setAttribute('loading', 'lazy');
            iframe.title = 'Bilibili 播放器';
            wrapInFrame(iframe);
            return { wrapper };
        }
        const video = document.createElement('video');
        video.className = 'player-media';
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
        wrapInFrame(video);
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
        if (breadcrumbsEl) {
            breadcrumbsEl.innerHTML = fragments.join('');
        }
    }

    function setCourseSummary(title, description, lessonCountText = '0 个课节', statusText = '', lessonCountNumber = 0) {
        courseSummaryTitleEl.textContent = title;
        courseSummaryDescriptionEl.textContent = description;
        courseLessonCountEl.textContent = lessonCountText;
        courseStatusChipEl.textContent = statusText;
        courseStatusChipEl.hidden = !statusText;
        setCourseProgress(0, lessonCountNumber);
    }

    function setCourseProgress(completed, total) {
        if (!courseProgressBarEl) return;
        const safeTotal = Number.isFinite(total) && total > 0 ? total : 0;
        const safeCompleted = Math.max(0, Math.min(Number(completed) || 0, safeTotal));
        const progress = safeTotal === 0 ? 0 : Math.round((safeCompleted / safeTotal) * 100);
        courseProgressBarEl.style.width = `${progress}%`;
        courseProgressBarEl.setAttribute('aria-valuenow', String(progress));
    }

    function updateCourseSummary(course, lessonCount = 0) {
        if (!course) {
            setCourseSummary('暂无课程', '暂未为您分配课程，请联系管理员。', '0 个课节', '待分配', 0);
            return;
        }
        const description = course.description && course.description.trim() ? course.description : '该课程暂无简介。';
        const statusText = lessonCount > 0 ? '学习中' : '准备中';
        setCourseSummary(course.title || '未命名课程', description, `${lessonCount} 个课节`, statusText, lessonCount);
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
        setCourseProgress(0, currentLessons.length);
        workspaceHeadingEl.textContent = currentCourse ? (currentCourse.title || '未命名课程') : '我的课堂';
        if (!currentLessons.length) {
            lessonBadgeEl.textContent = '0 个课节';
            courseBadgeEl.textContent = currentCourse ? `课程 · ${currentCourse.title || '未命名课程'}` : '课程';
            const empty = document.createElement('div');
            empty.className = 'list-group-item text-center text-secondary small';
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
            button.className = 'list-group-item list-group-item-action d-flex flex-column gap-1';
            button.dataset.lessonId = lesson.id;
            const order = String(index + 1).padStart(2, '0');
            button.innerHTML = `
                <div class="d-flex align-items-center justify-content-between">
                    <span class="fw-semibold">${lesson.title || `课节 ${index + 1}`}</span>
                    <span class="badge rounded-pill bg-primary-subtle text-primary-emphasis">${order}</span>
                </div>
                <div class="text-secondary small">${lesson.description || '点击查看详情'}</div>
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
            empty.className = 'list-group-item text-center text-secondary small';
            empty.textContent = '暂未为您分配课程，请联系管理员。';
            courseListEl.appendChild(empty);
            lessonPaneTitleEl.textContent = '课节';
            lessonPaneHintEl.textContent = '等待分配课程后即可在此查看课节。';
            lessonListEl.innerHTML = '<div class="list-group-item text-center text-secondary small">暂无课程。</div>';
            lessonMetaEl.hidden = true;
            courseBadgeEl.textContent = '课程';
            lessonBadgeEl.textContent = '0 个课节';
            lessonTitleEl.textContent = '欢迎来到课堂';
            lessonDescriptionEl.textContent = '待分配课程后将在此显示课节内容。';
            workspaceHeadingEl.textContent = '我的课堂';
            workspaceIntroEl.textContent = '暂无课程，联系管理员开通访问。';
            setCourseSummary('暂无课程', '暂未为您分配课程，请联系管理员。', '0 个课节', '待分配', 0);
            setStageHint('等待分配课程。', false);
            updateBreadcrumbs();
            return;
        }
        workspaceHeadingEl.textContent = '我的课堂';
        workspaceIntroEl.textContent = `已为您分配 ${courses.length} 门课程。`;
        courses.forEach((course) => {
            const item = document.createElement('button');
            item.type = 'button';
            item.className = 'list-group-item list-group-item-action course-button';
            item.dataset.courseId = course.id;
            item.innerHTML = `
                <div class="fw-semibold">${course.title}</div>
                <div class="text-secondary small">${course.description || '暂无描述'}</div>
            `;
            item.addEventListener('click', () => selectCourse(course.id));
            courseListEl.appendChild(item);
        });
        selectCourse(courses[0].id);
    }

    function highlightCourse(courseId) {
        document.querySelectorAll('#courseList .list-group-item-action').forEach((el) => {
            el.classList.toggle('active', Number(el.dataset.courseId) === courseId);
        });
    }

    function highlightLesson(lessonId) {
        document.querySelectorAll('#lessonList .list-group-item-action').forEach((el) => {
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
            lessonListEl.innerHTML = `<div class="list-group-item text-center text-secondary small">加载课程内容失败：${error.message}</div>`;
            lessonMetaEl.hidden = true;
            lessonTitleEl.textContent = '课程加载失败';
            lessonDescriptionEl.textContent = '请稍后重试或联系管理员排查问题。';
            workspaceHeadingEl.textContent = '课程加载失败';
            workspaceIntroEl.textContent = '课程内容暂时不可用，请稍后刷新。';
            setCourseSummary('课程加载失败', '无法加载课程详情，请稍后重试。', '0 个课节', '加载失败', 0);
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
        const lessonIndex = currentLessons.findIndex((item) => Number(item.id) === currentLessonId);
        setCourseProgress(lessonIndex + 1, currentLessons.length);
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
            courseListEl.innerHTML = `<div class="list-group-item text-center text-secondary small">无法加载课程列表：${error.message}</div>`;
            lessonPaneTitleEl.textContent = '课节';
            lessonPaneHintEl.textContent = '请稍后刷新重试。';
            lessonListEl.innerHTML = '<div class="list-group-item text-center text-secondary small">暂无课程内容</div>';
            lessonMetaEl.hidden = true;
            lessonTitleEl.textContent = '课程加载失败';
            lessonDescriptionEl.textContent = '无法获取课程列表，请稍后重试。';
            workspaceHeadingEl.textContent = '课程加载失败';
            workspaceIntroEl.textContent = '课程列表暂时不可用，请稍后刷新。';
            setCourseSummary('课程加载失败', '无法获取课程列表，请稍后重试。', '0 个课节', '加载失败', 0);
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
