<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>智能课堂 · 学习中心</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/main.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/plyr@3.7.8/dist/plyr.css">
</head>
<body class="app-shell">
<header class="topbar">
    <div class="topbar__brand">
        <span class="dot"></span>
        <div class="brand-text">
            <strong>智能课堂</strong>
            <small>学习中心</small>
        </div>
    </div>
    <div class="topbar__actions">
        <div class="user-pill" id="userInfo">加载中…</div>
        <a id="adminLink" href="admin.php" class="btn btn-ghost" hidden>管理后台</a>
        <button class="btn btn-light" id="logoutBtn">退出登录</button>
    </div>
</header>
<main class="main-view">
    <div class="tab-switcher" role="tablist">
        <button class="tab-switcher__btn is-active" data-target="recordedPanel">录播课程</button>
        <button class="tab-switcher__btn" data-target="livePanel">直播课程</button>
    </div>

    <section id="recordedPanel" class="panel is-active" role="tabpanel" aria-labelledby="recordedTab">
        <div class="panel__body recorded-layout">
            <aside class="panel-card course-list">
                <header>
                    <h2>我的课程</h2>
                    <p>选择课程以查看对应课节。</p>
                </header>
                <div id="coursesEmpty" class="empty-state" hidden>
                    <p>暂未分配课程，请联系管理员。</p>
                </div>
                <ul id="coursesList" class="list"></ul>
            </aside>
            <div class="panel-card lesson-column">
                <div class="lesson-column__header">
                    <div>
                        <span class="label">当前课程</span>
                        <h3 id="selectedCourseTitle">尚未选择课程</h3>
                    </div>
                    <p id="selectedCourseDescription" class="text-muted"></p>
                </div>
                <div class="lesson-column__body" id="lessonsContainer">
                    <div class="empty-state" id="lessonsEmpty">
                        <p>请选择左侧课程查看课节。</p>
                    </div>
                    <div class="lessons-grid" id="lessonsList"></div>
                </div>
            </div>
            <div class="panel-card player-column">
                <div class="player-header">
                    <div>
                        <span class="label">当前课节</span>
                        <h3 id="selectedLessonTitle">尚未选择课节</h3>
                    </div>
                    <p id="selectedLessonMeta" class="text-muted"></p>
                </div>
                <div class="player-wrapper" id="playerWrapper">
                    <div class="empty-state" id="playerEmpty">
                        <p>请选择右侧课节开始学习。</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section id="livePanel" class="panel" role="tabpanel" aria-labelledby="liveTab">
        <div class="panel__body">
            <header class="panel-card live-header">
                <div>
                    <h2>直播课堂</h2>
                    <p>查看已安排的直播课，准时进入教室。</p>
                </div>
                <button class="btn btn-light" id="refreshLiveBtn">刷新</button>
            </header>
            <div id="liveSessionsContainer" class="live-grid"></div>
            <div id="liveEmpty" class="empty-state" hidden>
                <p>暂无直播安排，稍后再来看看。</p>
            </div>
        </div>
    </section>
</main>

<script src="https://cdn.jsdelivr.net/npm/plyr@3.7.8/dist/plyr.polyfilled.min.js"></script>
<script>
(() => {
    const state = {
        user: null,
        courses: [],
        lessonsByCourse: new Map(),
        liveSessions: [],
        selectedCourseId: null,
        selectedLessonId: null,
        player: null
    };

    const userInfoEl = document.getElementById('userInfo');
    const adminLink = document.getElementById('adminLink');
    const logoutBtn = document.getElementById('logoutBtn');
    const coursesListEl = document.getElementById('coursesList');
    const coursesEmptyEl = document.getElementById('coursesEmpty');
    const selectedCourseTitleEl = document.getElementById('selectedCourseTitle');
    const selectedCourseDescriptionEl = document.getElementById('selectedCourseDescription');
    const lessonsContainerEl = document.getElementById('lessonsContainer');
    const lessonsEmptyEl = document.getElementById('lessonsEmpty');
    const lessonsListEl = document.getElementById('lessonsList');
    const selectedLessonTitleEl = document.getElementById('selectedLessonTitle');
    const selectedLessonMetaEl = document.getElementById('selectedLessonMeta');
    const playerWrapperEl = document.getElementById('playerWrapper');
    const playerEmptyEl = document.getElementById('playerEmpty');
    const liveContainerEl = document.getElementById('liveSessionsContainer');
    const liveEmptyEl = document.getElementById('liveEmpty');
    const refreshLiveBtn = document.getElementById('refreshLiveBtn');
    const tabButtons = document.querySelectorAll('.tab-switcher__btn');

    async function apiRequest(url, options) {
        const response = await fetch(url, options);
        if (!response.ok) {
            const data = await response.json().catch(() => ({ error: '请求失败' }));
            throw new Error(data.error || '请求失败');
        }
        return response.json();
    }

    function updateUserInfo() {
        if (!state.user) {
            userInfoEl.textContent = '未登录';
            return;
        }
        userInfoEl.textContent = state.user.display_name ? `${state.user.display_name} (${state.user.username})` : state.user.username;
        if (state.user.role === 'admin') {
            adminLink.hidden = false;
        }
    }

    function teardownPlayer() {
        if (state.player) {
            state.player.destroy();
            state.player = null;
        }
    }

    function toBilibiliEmbed(url) {
        try {
            const parsed = new URL(url);
            if (!parsed.hostname.includes('bilibili.com')) {
                return null;
            }
            const segments = parsed.pathname.split('/').filter(Boolean);
            const bvCode = segments.find(part => part.toUpperCase().startsWith('BV'));
            if (!bvCode) {
                return null;
            }
            const params = new URLSearchParams({
                bvid: bvCode,
                page: parsed.searchParams.get('p') || '1'
            });
            return `https://player.bilibili.com/player.html?${params.toString()}`;
        } catch (err) {
            return null;
        }
    }

    function renderPlayer(lesson) {
        teardownPlayer();
        playerWrapperEl.innerHTML = '';
        if (!lesson) {
            playerWrapperEl.appendChild(playerEmptyEl);
            playerEmptyEl.hidden = false;
            return;
        }

        playerEmptyEl.hidden = true;
        const videoUrl = lesson.video_url ? lesson.video_url.trim() : '';
        const biliEmbed = videoUrl ? toBilibiliEmbed(videoUrl) : null;

        if (biliEmbed) {
            const iframe = document.createElement('iframe');
            iframe.src = biliEmbed;
            iframe.allow = 'fullscreen; picture-in-picture';
            iframe.allowFullscreen = true;
            iframe.title = lesson.title;
            iframe.className = 'player-frame';
            playerWrapperEl.appendChild(iframe);
            return;
        }

        if (!videoUrl) {
            const placeholder = document.createElement('div');
            placeholder.className = 'empty-state';
            placeholder.innerHTML = '<p>该课节暂无视频资源。</p>';
            playerWrapperEl.appendChild(placeholder);
            return;
        }

        const video = document.createElement('video');
        video.className = 'player-media';
        video.setAttribute('controls', 'controls');
        video.setAttribute('playsinline', 'playsinline');
        const source = document.createElement('source');
        source.src = videoUrl;
        video.appendChild(source);
        playerWrapperEl.appendChild(video);
        state.player = new Plyr(video, {
            ratio: '16:9',
            i18n: {
                restart: '重新播放',
                play: '播放',
                pause: '暂停',
                mute: '静音',
                unmute: '取消静音',
                enterFullscreen: '全屏',
                exitFullscreen: '退出全屏'
            }
        });
    }

    function renderCourses() {
        coursesListEl.innerHTML = '';
        if (!state.courses.length) {
            coursesEmptyEl.hidden = false;
            return;
        }
        coursesEmptyEl.hidden = true;
        state.courses.forEach(course => {
            const item = document.createElement('li');
            item.className = 'list-item';
            if (state.selectedCourseId === course.id) {
                item.classList.add('is-active');
            }
            item.innerHTML = `
                <button type="button">
                    <strong>${course.title}</strong>
                    ${course.description ? `<span>${course.description}</span>` : ''}
                </button>
            `;
            item.addEventListener('click', () => {
                if (state.selectedCourseId !== course.id) {
                    selectCourse(course.id);
                }
            });
            coursesListEl.appendChild(item);
        });
    }

    function renderLessons(courseId) {
        lessonsListEl.innerHTML = '';
        const lessons = state.lessonsByCourse.get(courseId) || [];
        if (!lessons.length) {
            lessonsEmptyEl.hidden = false;
            return;
        }
        lessonsEmptyEl.hidden = true;
        lessons.forEach(lesson => {
            const card = document.createElement('button');
            card.type = 'button';
            card.className = 'lesson-card';
            if (lesson.id === state.selectedLessonId) {
                card.classList.add('is-active');
            }
            card.innerHTML = `
                <span class="lesson-card__index">#${lesson.id}</span>
                <div class="lesson-card__content">
                    <strong>${lesson.title}</strong>
                    ${lesson.created_at ? `<small>${formatDate(lesson.created_at)}</small>` : ''}
                </div>
            `;
            card.addEventListener('click', () => {
                if (state.selectedLessonId !== lesson.id) {
                    selectLesson(lesson.id);
                }
            });
            lessonsListEl.appendChild(card);
        });
    }

    function formatDate(value) {
        if (!value) return '';
        const date = new Date(value.replace(' ', 'T'));
        if (Number.isNaN(date.getTime())) {
            return value;
        }
        return date.toLocaleString('zh-CN', {
            month: 'short',
            day: 'numeric'
        });
    }

    function renderSelectedLesson() {
        const course = state.courses.find(c => c.id === state.selectedCourseId);
        const lessons = state.lessonsByCourse.get(state.selectedCourseId) || [];
        const lesson = lessons.find(l => l.id === state.selectedLessonId);
        selectedLessonTitleEl.textContent = lesson ? lesson.title : '尚未选择课节';
        selectedLessonMetaEl.textContent = lesson ? `课程：${course ? course.title : ''}` : '';
        renderPlayer(lesson);
    }

    function selectLesson(lessonId) {
        state.selectedLessonId = lessonId;
        renderLessons(state.selectedCourseId);
        renderSelectedLesson();
    }

    async function loadLessons(courseId) {
        lessonsContainerEl.classList.add('is-loading');
        try {
            const data = await apiRequest(`api/lessons.php?course_id=${courseId}`);
            const lessons = Array.isArray(data.lessons) ? data.lessons.map(lesson => ({
                id: Number(lesson.id),
                course_id: Number(lesson.course_id),
                title: lesson.title,
                video_url: lesson.video_url || '',
                created_at: lesson.created_at || ''
            })) : [];
            state.lessonsByCourse.set(courseId, lessons);
            renderLessons(courseId);
            if (lessons.length) {
                state.selectedLessonId = lessons[0].id;
            } else {
                state.selectedLessonId = null;
            }
            renderSelectedLesson();
        } catch (err) {
            lessonsListEl.innerHTML = `<div class="empty-state"><p>课节加载失败：${err.message}</p></div>`;
            state.selectedLessonId = null;
            renderPlayer(null);
        } finally {
            lessonsContainerEl.classList.remove('is-loading');
        }
    }

    function selectCourse(courseId) {
        state.selectedCourseId = courseId;
        const course = state.courses.find(item => item.id === courseId);
        selectedCourseTitleEl.textContent = course ? course.title : '尚未选择课程';
        selectedCourseDescriptionEl.textContent = course && course.description ? course.description : '';
        state.selectedLessonId = null;
        renderCourses();
        renderLessons(courseId);
        renderSelectedLesson();
        if (!state.lessonsByCourse.has(courseId)) {
            loadLessons(courseId);
        } else if ((state.lessonsByCourse.get(courseId) || []).length) {
            state.selectedLessonId = state.lessonsByCourse.get(courseId)[0].id;
            renderLessons(courseId);
            renderSelectedLesson();
        } else {
            renderPlayer(null);
        }
    }

    function renderLiveSessions() {
        liveContainerEl.innerHTML = '';
        if (!state.liveSessions.length) {
            liveEmptyEl.hidden = false;
            return;
        }
        liveEmptyEl.hidden = true;
        state.liveSessions.forEach(session => {
            const card = document.createElement('article');
            card.className = 'live-card';
            const startText = formatDateTime(session.starts_at);
            const endText = formatDateTime(session.ends_at);
            card.innerHTML = `
                <header>
                    <span class="label">${session.course_title || '直播课'}</span>
                    <h3>${session.title}</h3>
                </header>
                <p>${session.description || '暂无简介'}</p>
                <dl>
                    <div>
                        <dt>开始时间</dt>
                        <dd>${startText || '待定'}</dd>
                    </div>
                    <div>
                        <dt>结束时间</dt>
                        <dd>${endText || '待定'}</dd>
                    </div>
                </dl>
                <a class="btn btn-primary" target="_blank" rel="noopener" href="${session.stream_url}">进入直播</a>
            `;
            liveContainerEl.appendChild(card);
        });
    }

    function formatDateTime(value) {
        if (!value) return '';
        const date = new Date(value.replace(' ', 'T'));
        if (Number.isNaN(date.getTime())) {
            return value;
        }
        return date.toLocaleString('zh-CN', {
            hour12: false,
            month: '2-digit',
            day: '2-digit',
            hour: '2-digit',
            minute: '2-digit'
        });
    }

    async function loadLiveSessions() {
        try {
            const data = await apiRequest('api/live_sessions.php');
            const sessions = Array.isArray(data.sessions) ? data.sessions.map(item => ({
                id: Number(item.id),
                course_id: Number(item.course_id),
                course_title: item.course_title || '',
                title: item.title,
                description: item.description || '',
                stream_url: item.stream_url,
                starts_at: item.starts_at || '',
                ends_at: item.ends_at || ''
            })) : [];
            state.liveSessions = sessions;
            renderLiveSessions();
        } catch (err) {
            liveContainerEl.innerHTML = `<div class="empty-state"><p>直播课加载失败：${err.message}</p></div>`;
            state.liveSessions = [];
            liveEmptyEl.hidden = true;
        }
    }

    function bindTabs() {
        tabButtons.forEach(button => {
            button.addEventListener('click', () => {
                if (button.classList.contains('is-active')) return;
                tabButtons.forEach(btn => btn.classList.remove('is-active'));
                button.classList.add('is-active');
                document.querySelectorAll('.panel').forEach(panel => {
                    panel.classList.remove('is-active');
                });
                const targetId = button.dataset.target;
                const panel = document.getElementById(targetId);
                if (panel) {
                    panel.classList.add('is-active');
                    if (targetId === 'livePanel') {
                        loadLiveSessions();
                    }
                }
            });
        });
    }

    async function loadCourses() {
        try {
            const data = await apiRequest('api/courses.php');
            const courses = Array.isArray(data.courses) ? data.courses.map(item => ({
                id: Number(item.id),
                title: item.title,
                description: item.description || ''
            })) : [];
            state.courses = courses;
            renderCourses();
            if (courses.length) {
                selectCourse(courses[0].id);
            }
        } catch (err) {
            coursesListEl.innerHTML = `<li class="list-item error">课程加载失败：${err.message}</li>`;
            coursesEmptyEl.hidden = true;
        }
    }

    async function init() {
        try {
            const session = await apiRequest('api/session.php');
            state.user = session.user;
        } catch (err) {
            window.location.href = 'index.php';
            return;
        }
        updateUserInfo();
        bindTabs();
        await loadCourses();
        await loadLiveSessions();
    }

    logoutBtn.addEventListener('click', async () => {
        try {
            await apiRequest('api/logout.php', { method: 'POST' });
        } catch (err) {
            console.error(err);
        }
        window.location.href = 'index.php';
    });

    refreshLiveBtn.addEventListener('click', () => {
        loadLiveSessions();
    });

    init();
})();
</script>
</body>
</html>
