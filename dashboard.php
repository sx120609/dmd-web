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
<body class="app-shell dashboard-shell">
<nav class="navbar navbar-expand-lg app-navbar">
    <div class="container-xxl py-3 px-3 px-lg-4 w-100 d-flex align-items-center gap-3 flex-wrap">
        <div class="d-flex align-items-center gap-3">
            <div class="brand-glow">CL</div>
            <div class="d-flex flex-column">
                <span class="brand-eyebrow text-uppercase">智能录播课堂</span>
                <span class="navbar-brand p-0 m-0 fw-semibold">学习工作台</span>
            </div>
        </div>
        <div class="d-flex flex-wrap align-items-center gap-3 ms-auto">
            <div class="text-end">
                <div class="welcome-text" id="welcomeText">正在加载...</div>
            </div>
            <div class="user-chip" id="userChip"></div>
            <a class="btn btn-outline-secondary btn-sm" href="/">返回首页</a>
            <button class="btn btn-outline-primary btn-sm" id="cloudButton" style="display:none;">云盘</button>
            <button class="btn btn-outline-primary btn-sm" id="adminButton" style="display:none;">进入管理后台</button>
            <button class="btn btn-outline-secondary btn-sm" id="logoutButton">退出登录</button>
        </div>
    </div>
</nav>

<section class="page-hero py-3 py-lg-4">
    <div class="container-xxl hero-container">
        <div class="hero-panel student-hero">
            <div class="hero-eyebrow">继续学习</div>
            <div class="hero-main">
                <div class="hero-copy">
                    <h1 class="hero-title" id="workspaceHeading">我的课堂</h1>
                    <p class="hero-subtitle" id="workspaceIntro">从左侧选择课程，即可在右侧查看课节详情。</p>
                </div>
                <div class="hero-meta">
                    <span class="hero-pill" id="courseLessonCount">0 个课节</span>
                    <span class="hero-pill soft" id="courseStatusChip" hidden>待选课</span>
                </div>
            </div>
            <div class="hero-summary">
                <h2 class="hero-summary-title" id="courseSummaryTitle">尚未选择课程</h2>
                <p class="hero-summary-desc" id="courseSummaryDescription">从左侧课程列表中选择一个课程开始学习。</p>
            </div>
        </div>
    </div>
</section>

<main class="dashboard-main container-xxl pb-5">
    <div class="dashboard-split">
        <section class="split-sidebar">
            <div class="sidebar-section">
                <div class="sidebar-heading">
                    <h2>我的课程</h2>
                    <p>挑选一个课程继续学习。</p>
                </div>
                <div class="d-flex flex-column gap-2 mb-3">
                    <input type="search" class="form-control form-control-sm" id="courseSearchInput" placeholder="搜索课程名称/标签/老师">
                    <div class="d-flex flex-wrap gap-2">
                        <select class="form-select form-select-sm" id="courseTagFilter" style="min-width: 120px;">
                            <option value="">全部标签</option>
                        </select>
                        <select class="form-select form-select-sm" id="courseInstructorFilter" style="min-width: 120px;">
                            <option value="">全部老师</option>
                        </select>
                        <select class="form-select form-select-sm" id="courseProgressFilter" style="min-width: 140px;">
                            <option value="all">全部进度</option>
                            <option value="unwatched">未看</option>
                            <option value="in_progress">学习中</option>
                            <option value="completed">已完成</option>
                        </select>
                        <select class="form-select form-select-sm" id="courseSortSelect" style="min-width: 140px;">
                            <option value="default">默认排序</option>
                            <option value="latest">最新优先</option>
                            <option value="progress">进度优先</option>
                            <option value="unwatched_first">未看优先</option>
                        </select>
                    </div>
                </div>
                <div class="sidebar-body">
                    <div class="panel-list" id="courseList">
                        <div class="panel-list-item">
                            <div class="placeholder-glow">
                                <span class="placeholder col-10"></span>
                            </div>
                        </div>
                        <div class="panel-list-item">
                            <div class="placeholder-glow">
                                <span class="placeholder col-7"></span>
                            </div>
                        </div>
                        <div class="panel-list-item">
                            <div class="placeholder-glow">
                                <span class="placeholder col-5"></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="sidebar-section">
                <div class="sidebar-heading">
                    <h3 id="lessonPaneTitle">课节</h3>
                    <p id="lessonPaneHint">先选择课程以加载课节。</p>
                </div>
                <div class="sidebar-body">
                    <div class="panel-list" id="lessonList">
                        <div class="panel-empty">暂未选择课程。</div>
                    </div>
                </div>
            </div>
        </section>
        <section class="split-stage">
            <header class="stage-header">
                <div class="breadcrumbs" id="breadcrumbs"><span>网课</span></div>
                <h2 class="stage-title" id="lessonTitle">欢迎来到课堂</h2>
                <p class="stage-subtitle" id="lessonDescription">从左侧依次选择课程与课节即可开始学习。</p>
                <div class="stage-meta" id="lessonMeta" hidden>
                    <span class="chip" id="courseBadge"></span>
                    <span class="chip subtle" id="lessonBadge"></span>
                </div>
                <div class="d-flex flex-wrap gap-2 align-items-center mt-2">
                    <button class="btn btn-outline-secondary btn-sm" id="markCompleteButton" disabled>标记已完成</button>
                </div>
            </header>
            <div class="stage-content">
                <div class="stage-hint" id="stageHint">尚未选择课节。</div>
                <div class="player-stage" id="playerHost">
                    <div class="empty-state">尚未选择课节。</div>
                </div>
            </div>
        </section>
    </div>
</main>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/plyr@3.7.8/dist/plyr.polyfilled.min.js"></script>
<script>
    const API_BASE = 'api';

    function normalizeApiUrl(url) {
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
    const cloudButton = document.getElementById('cloudButton');
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
    const courseSearchInput = document.getElementById('courseSearchInput');
    const courseTagFilter = document.getElementById('courseTagFilter');
    const courseInstructorFilter = document.getElementById('courseInstructorFilter');
    const courseProgressFilter = document.getElementById('courseProgressFilter');
    const courseSortSelect = document.getElementById('courseSortSelect');
    const markCompleteButton = document.getElementById('markCompleteButton');

    let currentUser = null;
    let currentCourseId = null;
    let currentLessonId = null;
    let currentLessons = [];
    let currentCourse = null;
    let players = [];
    let allCourses = [];
    let courseFilters = {
        search: '',
        tag: '',
        instructor: '',
        progress: 'all',
        sort: 'default'
    };
    let progressStore = {};
    let progressKey = '';

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

    if (mobileQuery && typeof mobileQuery.addEventListener === 'function') {
        mobileQuery.addEventListener('change', handleDesktopSwitch);
    } else if (mobileQuery && typeof mobileQuery.addListener === 'function') {
        mobileQuery.addListener(handleDesktopSwitch);
    }

    if (courseDrawerToggle) {
        courseDrawerToggle.addEventListener('click', () => openDrawer('course'));
    }
    if (lessonDrawerToggle) {
        lessonDrawerToggle.addEventListener('click', () => openDrawer('lesson'));
    }
    if (courseDrawerClose) {
        courseDrawerClose.addEventListener('click', closeAllDrawers);
    }
    if (lessonDrawerClose) {
        lessonDrawerClose.addEventListener('click', closeAllDrawers);
    }
    if (drawerBackdrop) {
        drawerBackdrop.addEventListener('click', closeAllDrawers);
    }

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape' && (document.body.classList.contains('course-drawer-open') || document.body.classList.contains('lesson-drawer-open'))) {
            event.preventDefault();
            closeAllDrawers();
        }
    });

    const handleCourseFilterChange = () => {
        courseFilters.search = courseSearchInput ? courseSearchInput.value.trim().toLowerCase() : '';
        courseFilters.tag = courseTagFilter ? courseTagFilter.value : '';
        courseFilters.instructor = courseInstructorFilter ? courseInstructorFilter.value : '';
        courseFilters.progress = courseProgressFilter ? courseProgressFilter.value : 'all';
        courseFilters.sort = courseSortSelect ? courseSortSelect.value : 'default';
        renderCourseList(allCourses);
    };

    if (courseSearchInput) {
        courseSearchInput.addEventListener('input', handleCourseFilterChange);
    }
    if (courseTagFilter) {
        courseTagFilter.addEventListener('change', handleCourseFilterChange);
    }
    if (courseInstructorFilter) {
        courseInstructorFilter.addEventListener('change', handleCourseFilterChange);
    }
    if (courseProgressFilter) {
        courseProgressFilter.addEventListener('change', handleCourseFilterChange);
    }
    if (courseSortSelect) {
        courseSortSelect.addEventListener('change', handleCourseFilterChange);
    }

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

    function buildPlayer(url) {
        const wrapper = document.createElement('div');
        wrapper.className = 'player';
        const wrapInFrame = (element, extraFrameClass = '') => {
            const frame = document.createElement('div');
            frame.className = 'player-frame';
            if (extraFrameClass) {
                if (Array.isArray(extraFrameClass)) {
                    extraFrameClass.filter(Boolean).forEach(cls => frame.classList.add(cls));
                } else {
                    String(extraFrameClass)
                        .split(' ')
                        .map(cls => cls.trim())
                        .filter(Boolean)
                        .forEach(cls => frame.classList.add(cls));
                }
            }
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
            let bvid = bilibiliBvMatch ? bilibiliBvMatch[1] : '';
            let aid = bilibiliAvMatch ? bilibiliAvMatch[1] : '';
            if (bilibiliEmbedRegex.test(trimmed)) {
                try {
                    const embedUrl = new URL(trimmed, window.location.href);
                    if (!bvid) {
                        const queryBvid = embedUrl.searchParams.get('bvid');
                        if (queryBvid) {
                            bvid = queryBvid;
                        }
                    }
                    if (!aid) {
                        const queryAid = embedUrl.searchParams.get('aid');
                        if (queryAid) {
                            aid = queryAid;
                        }
                    }
                    const pageParam = parseInt(embedUrl.searchParams.get('page') || embedUrl.searchParams.get('p'), 10);
                    if (!Number.isNaN(pageParam) && pageParam > 0) {
                        page = pageParam;
                    }
                } catch (error) {
                    // ignore malformed url
                }
            }
            let portalUrl = trimmed;
            if (bvid) {
                portalUrl = `https://www.bilibili.com/video/${encodeURIComponent(bvid)}`;
            } else if (aid) {
                portalUrl = `https://www.bilibili.com/video/av${encodeURIComponent(aid)}`;
            }
            let hostLabel = 'bilibili.com';
            try {
                const portalUrlObj = new URL(portalUrl, window.location.href);
                if (page > 1) {
                    portalUrlObj.searchParams.set('p', String(page));
                }
                portalUrl = portalUrlObj.toString();
                hostLabel = portalUrlObj.hostname.replace(/^www\./, '') || hostLabel;
                hostLabel = hostLabel.replace(/^player\./, '') || hostLabel;
            } catch (error) {
                // keep defaults on malformed url
            }
            const portal = document.createElement('a');
            portal.className = 'player-portal player-portal--bilibili';
            portal.href = portalUrl;
            portal.target = '_blank';
            portal.rel = 'noopener noreferrer';

            const chip = document.createElement('span');
            chip.className = 'portal-chip';
            chip.textContent = '哔哩哔哩外部视频';
            portal.appendChild(chip);

            const heading = document.createElement('div');
            heading.className = 'portal-heading';
            portal.appendChild(heading);

            const title = document.createElement('span');
            title.className = 'portal-title';
            title.textContent = '前往哔哩哔哩观看';
            heading.appendChild(title);

            const meta = document.createElement('span');
            meta.className = 'portal-meta';
            meta.textContent = page > 1 ? `${hostLabel} · P${page}` : hostLabel;
            heading.appendChild(meta);

            const description = document.createElement('p');
            description.className = 'portal-description';
            description.textContent = '点击在新标签打开完整视频，返回此页即可继续学习。';
            portal.appendChild(description);

            const action = document.createElement('span');
            action.className = 'portal-action';
            action.textContent = '打开视频';
            const actionIcon = document.createElement('span');
            actionIcon.setAttribute('aria-hidden', 'true');
            actionIcon.textContent = '↗';
            action.appendChild(actionIcon);
            portal.appendChild(action);

            const scene = document.createElement('div');
            scene.className = 'player-portal-scene';
            scene.appendChild(portal);

            wrapper.classList.add('player--bilibili', 'player--external', 'player--portal');
            wrapper.appendChild(scene);
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

    function setCourseSummary(title, description, lessonCountText = '0 个课节', statusText = '') {
        if (courseSummaryTitleEl) {
            courseSummaryTitleEl.textContent = title;
        }
        if (courseSummaryDescriptionEl) {
            courseSummaryDescriptionEl.textContent = description;
        }
        if (courseLessonCountEl) {
            courseLessonCountEl.textContent = lessonCountText;
        }
        if (courseStatusChipEl) {
            courseStatusChipEl.textContent = statusText;
            courseStatusChipEl.hidden = !statusText;
        }
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

    function setCourseProgress(completed = 0, total = 0) {
        const safeTotal = Math.max(Number(total) || 0, 0);
        const safeCompleted = Math.min(Math.max(Number(completed) || 0, 0), safeTotal);
        const percentage = safeTotal > 0 ? Math.round((safeCompleted / safeTotal) * 100) : 0;

        if (courseProgressBarEl) {
            courseProgressBarEl.style.setProperty('--progress', `${percentage}%`);
            courseProgressBarEl.setAttribute('aria-valuemin', '0');
            courseProgressBarEl.setAttribute('aria-valuemax', '100');
            courseProgressBarEl.setAttribute('aria-valuenow', String(percentage));
            if ('value' in courseProgressBarEl) {
                courseProgressBarEl.value = percentage;
            }
        }

        if (courseLessonCountEl) {
            if (safeTotal <= 0) {
                courseLessonCountEl.textContent = '0 个课节';
            } else if (safeCompleted <= 0) {
                courseLessonCountEl.textContent = `${safeTotal} 个课节`;
            } else {
                courseLessonCountEl.textContent = `${safeTotal} 个课节 · 已学 ${safeCompleted}/${safeTotal}`;
            }
        }
    }

    function loadProgressStore(userId = 'guest') {
        progressKey = `rl_progress_${userId}`;
        try {
            const raw = localStorage.getItem(progressKey);
            progressStore = raw ? JSON.parse(raw) : {};
        } catch (error) {
            progressStore = {};
        }
    }

    function saveProgressStore() {
        if (!progressKey) return;
        try {
            localStorage.setItem(progressKey, JSON.stringify(progressStore));
        } catch (error) {
            // ignore storage errors
        }
    }

    function markLessonVisited(courseId, lessonId, totalLessons = 0) {
        if (!courseId || !lessonId) return;
        if (!progressStore[courseId]) {
            progressStore[courseId] = { visited: [], completed: [], total: totalLessons || 0 };
        }
        const record = progressStore[courseId];
        if (!Array.isArray(record.visited)) {
            record.visited = [];
        }
        if (!Array.isArray(record.completed)) {
            record.completed = [];
        }
        if (!record.visited.includes(lessonId)) {
            record.visited.push(lessonId);
        }
        if (totalLessons > 0) {
            record.total = totalLessons;
        } else if (!record.total && currentLessons && currentLessons.length) {
            record.total = currentLessons.length;
        }
        progressStore[courseId] = record;
        saveProgressStore();
    }

    function updateCourseTotalLessons(courseId, lessonCount) {
        if (!courseId) return;
        if (!progressStore[courseId]) {
            progressStore[courseId] = { visited: [], completed: [], total: lessonCount || 0 };
        } else if (lessonCount > 0) {
            progressStore[courseId].total = lessonCount;
        }
        saveProgressStore();
    }

    function getCourseProgress(courseId, lessonCount = 0) {
        const record = progressStore[courseId] || { visited: [], completed: [], total: lessonCount || 0 };
        const total = lessonCount || record.total || 0;
        const visitedCount = Array.isArray(record.visited) ? record.visited.length : 0;
        const completedCount = Array.isArray(record.completed) ? record.completed.length : 0;
        const effectiveVisited = Math.max(visitedCount, completedCount);
        const safeVisited = Math.min(visitedCount, total || visitedCount);
        const pct = total > 0 ? Math.round((Math.min(effectiveVisited, total) / total) * 100) : 0;
        let status = 'unstarted';
        if (pct >= 100 && total > 0) {
            status = 'completed';
        } else if (pct > 0) {
            status = 'in_progress';
        }
        return { total, visited: safeVisited, completed: completedCount, percentage: pct, status };
    }

    function setLessonCompleted(courseId, lessonId, isCompleted = true) {
        if (!courseId || !lessonId) return;
        if (!progressStore[courseId]) {
            progressStore[courseId] = { visited: [], completed: [], total: currentLessons.length || 0 };
        }
        const record = progressStore[courseId];
        if (!Array.isArray(record.completed)) {
            record.completed = [];
        }
        if (!Array.isArray(record.visited)) {
            record.visited = [];
        }
        if (isCompleted) {
            if (!record.completed.includes(lessonId)) {
                record.completed.push(lessonId);
            }
            if (!record.visited.includes(lessonId)) {
                record.visited.push(lessonId);
            }
        } else {
            record.completed = record.completed.filter((id) => id !== lessonId);
        }
        progressStore[courseId] = record;
        saveProgressStore();
    }

    function renderLessonList(lessons, course) {
        closeAllDrawers();
        currentCourse = course || null;
        currentLessons = lessons || [];
        if (currentCourse && currentCourse.id) {
            updateCourseTotalLessons(currentCourse.id, currentLessons.length || 0);
        }
        currentLessonId = null;
        clearPlayers();
        playerHostEl.innerHTML = '<div class="empty-state">尚未选择课节。</div>';
        lessonListEl.innerHTML = '';
        updateCourseSummary(currentCourse, currentLessons.length);
        setCourseProgress(0, currentLessons.length);
        renderCourseList(allCourses);
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
        const source = courses && Array.isArray(courses) ? courses : allCourses;
        const filtered = applyCourseFilters(source || []);
        courseListEl.innerHTML = '';
        if (!filtered.length) {
            const empty = document.createElement('div');
            empty.className = 'list-group-item text-center text-secondary small';
            empty.textContent = source && source.length ? '没有符合筛选条件的课程。' : '暂未为您分配课程，请联系管理员。';
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
        workspaceIntroEl.textContent = `已为您分配 ${source.length} 门课程。`;
        filtered.forEach((course) => {
            const progress = getCourseProgress(course.id, course.lesson_count || 0);
            const progressText = course.lesson_count > 0 ? `${progress.visited}/${course.lesson_count}` : '暂无课节';
            const tags = normalizeTags(course.tags);
            const item = document.createElement('button');
            item.type = 'button';
            item.className = 'list-group-item list-group-item-action course-button';
            item.dataset.courseId = course.id;
            item.innerHTML = `
                <div class="fw-semibold">${course.title}</div>
                <div class="d-flex flex-wrap align-items-center gap-2 mt-1">
                    ${course.instructor ? `<span class="badge rounded-pill bg-primary-subtle text-primary-emphasis">讲师 · ${course.instructor}</span>` : ''}
                    ${tags.length ? tags.map((tag) => `<span class="badge bg-light text-secondary border">${tag}</span>`).join('') : ''}
                    <span class="badge rounded-pill ${progress.status === 'completed' ? 'bg-success-subtle text-success' : 'bg-secondary-subtle text-secondary'}">${progressText}</span>
                </div>
                <div class="text-secondary small mt-1">${course.description || '暂无描述'}</div>
            `;
            item.addEventListener('click', () => selectCourse(course.id));
            courseListEl.appendChild(item);
        });
        const preferredCourse = filtered.find((c) => c.id === currentCourseId) || filtered[0];
        if (preferredCourse) {
            selectCourse(preferredCourse.id);
        }
    }

    function highlightCourse(courseId) {
        document.querySelectorAll('#courseList .list-group-item-action').forEach((el) => {
            el.classList.toggle('active', Number(el.dataset.courseId) === courseId);
        });
    }

    function normalizeTags(tags) {
        if (!tags) return [];
        return String(tags)
            .split(',')
            .map((tag) => tag.trim())
            .filter(Boolean);
    }

    function updateFilterOptions() {
        if (!allCourses.length) return;
        const tagSet = new Set();
        const instructorSet = new Set();
        allCourses.forEach((course) => {
            normalizeTags(course.tags).forEach((tag) => tagSet.add(tag));
            if (course.instructor && course.instructor.trim()) {
                instructorSet.add(course.instructor.trim());
            }
        });
        if (courseTagFilter) {
            const current = courseTagFilter.value;
            courseTagFilter.innerHTML = '<option value="">全部标签</option>';
            Array.from(tagSet).sort().forEach((tag) => {
                const option = document.createElement('option');
                option.value = tag;
                option.textContent = tag;
                if (current === tag) option.selected = true;
                courseTagFilter.appendChild(option);
            });
        }
        if (courseInstructorFilter) {
            const current = courseInstructorFilter.value;
            courseInstructorFilter.innerHTML = '<option value="">全部老师</option>';
            Array.from(instructorSet).sort().forEach((name) => {
                const option = document.createElement('option');
                option.value = name;
                option.textContent = name;
                if (current === name) option.selected = true;
                courseInstructorFilter.appendChild(option);
            });
        }
    }

    function applyCourseFilters(courses) {
        const filtered = (courses || []).filter((course) => {
            const progress = getCourseProgress(course.id, course.lesson_count || 0);
            const search = courseFilters.search.toLowerCase();
            if (search) {
                const haystack = [
                    course.title,
                    course.description,
                    course.instructor,
                    course.tags
                ].join(' ').toLowerCase();
                if (!haystack.includes(search)) {
                    return false;
                }
            }
            if (courseFilters.tag) {
                const tags = normalizeTags(course.tags);
                if (!tags.includes(courseFilters.tag)) {
                    return false;
                }
            }
            if (courseFilters.instructor) {
                if (!course.instructor || course.instructor !== courseFilters.instructor) {
                    return false;
                }
            }
            if (courseFilters.progress === 'unwatched' && progress.percentage > 0) return false;
            if (courseFilters.progress === 'completed' && progress.percentage < 100) return false;
            if (courseFilters.progress === 'in_progress' && (progress.percentage <= 0 || progress.percentage >= 100)) return false;
            return true;
        });

        const sorter = {
            latest: (a, b) => (new Date(b.created_at || 0) - new Date(a.created_at || 0)) || b.id - a.id,
            progress: (a, b) => getCourseProgress(b.id, b.lesson_count || 0).percentage - getCourseProgress(a.id, a.lesson_count || 0).percentage,
            unwatched_first: (a, b) => {
                const pa = getCourseProgress(a.id, a.lesson_count || 0).percentage;
                const pb = getCourseProgress(b.id, b.lesson_count || 0).percentage;
                if (pa === 0 && pb !== 0) return -1;
                if (pb === 0 && pa !== 0) return 1;
                return pa - pb;
            }
        };

        if (courseFilters.sort && courseFilters.sort !== 'default' && sorter[courseFilters.sort]) {
            filtered.sort(sorter[courseFilters.sort]);
        } else {
            filtered.sort((a, b) => a.id - b.id);
        }

        return filtered;
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
            const message = (data && (data.message || data.error)) || '请求失败';
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
            const courseTitle = currentCourse && currentCourse.title ? currentCourse.title : '';
            lessonPaneTitleEl.textContent = courseTitle ? `${courseTitle} 的课节` : '课节';
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
        markLessonVisited(currentCourseId, currentLessonId, currentLessons.length);
        renderCourseList(allCourses);
        syncMarkCompleteButton();
        if (video) {
            const player = new Plyr(video, {
                controls: ['play-large', 'play', 'progress', 'current-time', 'mute', 'volume', 'settings', 'fullscreen'],
                settings: ['speed', 'quality']
            });
            players.push(player);
        }
    }

    function syncMarkCompleteButton() {
        if (!markCompleteButton) return;
        if (!currentCourseId || !currentLessonId) {
            markCompleteButton.disabled = true;
            markCompleteButton.textContent = '标记已完成';
            return;
        }
        const record = progressStore[currentCourseId] || { completed: [] };
        const isDone = Array.isArray(record.completed) && record.completed.includes(currentLessonId);
        markCompleteButton.disabled = false;
        markCompleteButton.textContent = isDone ? '取消已完成' : '标记已完成';
    }

    async function loadCourses() {
        try {
            const data = await fetchJSON(`${API_BASE}/courses.php`);
            allCourses = (data.courses || []).map((course) => ({
                ...course,
                id: Number(course.id),
                lesson_count: Number(course.lesson_count || course.lessons_count || 0)
            }));
            updateFilterOptions();
            renderCourseList(allCourses);
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
            loadProgressStore(currentUser.id || 'guest');
            showWelcome(currentUser);
            if (currentUser.role === 'admin') {
                adminButton.style.display = 'inline-flex';
                if (cloudButton) {
                    cloudButton.style.display = 'inline-flex';
                }
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

    if (cloudButton) {
        cloudButton.addEventListener('click', () => {
            window.location.href = 'cloud';
        });
    }

    if (markCompleteButton) {
        markCompleteButton.addEventListener('click', () => {
            if (!currentCourseId || !currentLessonId) {
                return;
            }
            const record = progressStore[currentCourseId] || { completed: [] };
            const isDone = Array.isArray(record.completed) && record.completed.includes(currentLessonId);
            setLessonCompleted(currentCourseId, currentLessonId, !isDone);
            syncMarkCompleteButton();
            renderCourseList(allCourses);
            const lessonIndex = currentLessons.findIndex((item) => Number(item.id) === currentLessonId);
            setCourseProgress(lessonIndex + 1, currentLessons.length);
        });
    }

    loadSession();
</script>
</body>
</html>
