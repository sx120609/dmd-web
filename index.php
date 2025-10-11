<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <title>网课系统 - 录播课程</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/plyr@3.7.8/dist/plyr.css">
    <style>
        :root {
            color-scheme: light;
        }
        * {
            box-sizing: border-box;
        }
        body {
            font-family: "Microsoft YaHei", Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f5f6fa;
            color: #333;
        }
        header {
            background-color: #34495e;
            color: #fff;
            padding: 16px 24px;
        }
        header h1 {
            margin: 0;
            font-size: 24px;
        }
        header p {
            margin: 4px 0 0;
            font-size: 13px;
            color: rgba(255, 255, 255, 0.85);
        }
        main {
            display: grid;
            gap: 24px;
            padding: 24px;
            grid-template-columns: minmax(260px, 320px) 1fr;
            align-items: start;
        }
        .panel {
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            padding: 16px 24px;
        }
        .panel h2 {
            margin-top: 0;
            font-size: 18px;
        }
        #authPanel {
            grid-column: 1 / -1;
        }
        .course-list {
            display: flex;
            flex-direction: column;
            gap: 12px;
        }
        .course-list ul {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        .course-list li {
            margin-bottom: 12px;
            padding-bottom: 12px;
            border-bottom: 1px solid #ececec;
        }
        .course-list button {
            border: none;
            background: none;
            padding: 0;
            font: inherit;
            color: #2980b9;
            font-weight: bold;
            cursor: pointer;
        }
        .course-list p {
            margin: 4px 0 0;
            font-size: 13px;
            color: #666;
        }
        #courseSection {
            display: none;
            grid-column: span 2;
            gap: 24px;
        }
        #courseSection.active {
            display: grid;
            grid-template-columns: minmax(240px, 320px) 1fr;
            gap: 24px;
        }
        .course-content {
            min-height: 200px;
        }
        .lesson {
            margin-bottom: 24px;
        }
        .lesson h3 {
            margin: 0 0 8px;
        }
        .video {
            position: relative;
            width: 100%;
            padding-bottom: 56.25%;
            background-color: #000;
            border-radius: 6px;
            overflow: hidden;
        }
        .video iframe,
        .video video {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            border: 0;
        }
        .placeholder {
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100%;
            color: #fff;
            text-align: center;
            padding: 16px;
        }
        .empty-state {
            color: #999;
            font-size: 14px;
        }
        .message {
            background-color: #fef6d8;
            border: 1px solid #f5d97b;
            color: #7c5c00;
            padding: 12px 16px;
            border-radius: 6px;
            margin-bottom: 16px;
            font-size: 14px;
            display: none;
        }
        .message.error {
            background-color: #fdecea;
            border-color: #f5b3ad;
            color: #a94442;
        }
        .loading {
            color: #2980b9;
            font-size: 14px;
        }
        #adminPanel {
            display: none;
            grid-column: 1 / -1;
        }
        #adminPanel.active {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
            gap: 16px;
        }
        .form-group {
            margin-bottom: 12px;
        }
        label {
            display: block;
            font-weight: bold;
            margin-bottom: 4px;
        }
        input, select, textarea {
            width: 100%;
            padding: 8px;
            border: 1px solid #ccc;
            border-radius: 4px;
            font-family: inherit;
        }
        textarea {
            min-height: 80px;
            resize: vertical;
        }
        button.primary {
            background-color: #2980b9;
            color: #fff;
            border: none;
            padding: 8px 16px;
            border-radius: 4px;
            cursor: pointer;
        }
        button.primary:disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }
        .panel + .panel {
            margin-top: 0;
        }
        .list-small {
            font-size: 13px;
            color: #666;
        }
        @media (max-width: 900px) {
            main {
                display: flex;
                flex-direction: column;
            }
            #courseSection.active {
                display: flex;
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
<header>
    <h1>录播课程中心</h1>
    <p id="welcomeText">请先登录以查看您的专属课程。</p>
</header>
<main>
    <section id="authPanel" class="panel" aria-live="polite">
        <h2>账号登录</h2>
        <form id="loginForm">
            <div class="form-group">
                <label for="username">用户名</label>
                <input type="text" id="username" name="username" required>
            </div>
            <div class="form-group">
                <label for="password">密码</label>
                <input type="password" id="password" name="password" required>
            </div>
            <button type="submit" class="primary">登录</button>
            <button type="button" id="logoutBtn" class="primary" style="background-color:#7f8c8d; display:none;">退出登录</button>
            <p id="authMessage" class="message" style="display:none;"></p>
        </form>
        <p class="list-small">管理员可通过后台管理用户、课程和课节。</p>
    </section>
    <section id="courseSection">
        <section class="panel course-list" aria-label="课程列表">
            <h2>我的课程</h2>
            <div id="courseMessage" class="message" role="alert"></div>
            <div id="courseLoading" class="loading" hidden>正在加载课程...</div>
            <ul id="courseList"></ul>
        </section>
        <section class="panel course-content" aria-live="polite">
            <h2 id="courseTitle">欢迎进入录播课程中心</h2>
            <p id="courseDescription" class="empty-state">请选择左侧的课程查看详细内容。</p>
            <div id="lessonContainer"></div>
        </section>
    </section>
    <section id="adminPanel" aria-label="后台管理">
        <article class="panel">
            <h2>创建用户</h2>
            <form id="createUserForm">
                <div class="form-group">
                    <label for="newUsername">用户名</label>
                    <input type="text" id="newUsername" name="username" required>
                </div>
                <div class="form-group">
                    <label for="newDisplayName">显示名称</label>
                    <input type="text" id="newDisplayName" name="display_name">
                </div>
                <div class="form-group">
                    <label for="newPassword">密码</label>
                    <input type="password" id="newPassword" name="password" required>
                </div>
                <div class="form-group">
                    <label for="newRole">角色</label>
                    <select id="newRole" name="role">
                        <option value="student">学生</option>
                        <option value="admin">管理员</option>
                    </select>
                </div>
                <button type="submit" class="primary">创建用户</button>
                <p class="list-small" id="createUserMessage"></p>
            </form>
        </article>
        <article class="panel">
            <h2>创建课程</h2>
            <form id="createCourseForm">
                <div class="form-group">
                    <label for="courseTitleInput">课程名称</label>
                    <input type="text" id="courseTitleInput" name="title" required>
                </div>
                <div class="form-group">
                    <label for="courseDescriptionInput">课程描述</label>
                    <textarea id="courseDescriptionInput" name="description"></textarea>
                </div>
                <button type="submit" class="primary">创建课程</button>
                <p class="list-small" id="createCourseMessage"></p>
            </form>
        </article>
        <article class="panel">
            <h2>分配课程</h2>
            <form id="assignCourseForm">
                <div class="form-group">
                    <label for="assignUser">选择用户</label>
                    <select id="assignUser" name="user_id" required></select>
                </div>
                <div class="form-group">
                    <label for="assignCourse">选择课程</label>
                    <select id="assignCourse" name="course_id" required></select>
                </div>
                <button type="submit" class="primary">分配课程</button>
                <p class="list-small" id="assignCourseMessage"></p>
            </form>
        </article>
        <article class="panel">
            <h2>添加课节</h2>
            <form id="createLessonForm">
                <div class="form-group">
                    <label for="lessonCourse">所属课程</label>
                    <select id="lessonCourse" name="course_id" required></select>
                </div>
                <div class="form-group">
                    <label for="lessonTitle">课节标题</label>
                    <input type="text" id="lessonTitle" name="title" required>
                </div>
                <div class="form-group">
                    <label for="lessonVideo">视频地址</label>
                    <input type="text" id="lessonVideo" name="video_url" placeholder="支持外链、本地文件或哔哩哔哩视频链接">
                </div>
                <button type="submit" class="primary">添加课节</button>
                <p class="list-small" id="createLessonMessage"></p>
            </form>
        </article>
    </section>
</main>
<script src="https://cdn.jsdelivr.net/npm/plyr@3.7.8/dist/plyr.polyfilled.min.js"></script>
<script>
(function () {
    const API_BASE = 'api';
    const welcomeTextEl = document.getElementById('welcomeText');
    const authPanel = document.getElementById('authPanel');
    const loginForm = document.getElementById('loginForm');
    const logoutBtn = document.getElementById('logoutBtn');
    const authMessageEl = document.getElementById('authMessage');
    const courseSection = document.getElementById('courseSection');
    const courseListEl = document.getElementById('courseList');
    const courseMessageEl = document.getElementById('courseMessage');
    const courseLoadingEl = document.getElementById('courseLoading');
    const courseTitleEl = document.getElementById('courseTitle');
    const courseDescriptionEl = document.getElementById('courseDescription');
    const lessonContainerEl = document.getElementById('lessonContainer');
    const adminPanel = document.getElementById('adminPanel');
    const createUserForm = document.getElementById('createUserForm');
    const createUserMessage = document.getElementById('createUserMessage');
    const createCourseForm = document.getElementById('createCourseForm');
    const createCourseMessage = document.getElementById('createCourseMessage');
    const assignCourseForm = document.getElementById('assignCourseForm');
    const assignCourseMessage = document.getElementById('assignCourseMessage');
    const assignUserSelect = document.getElementById('assignUser');
    const assignCourseSelect = document.getElementById('assignCourse');
    const lessonCourseSelect = document.getElementById('lessonCourse');
    const createLessonForm = document.getElementById('createLessonForm');
    const createLessonMessage = document.getElementById('createLessonMessage');

    let currentCourseId = null;
    let currentUser = null;
    const plyrPlayers = [];

    function setMessage(el, message, type = 'info') {
        if (!el) return;
        if (!message) {
            el.style.display = 'none';
            el.textContent = '';
            el.classList.remove('error');
            return;
        }
        el.textContent = message;
        el.classList.toggle('error', type === 'error');
        el.style.display = 'block';
    }

    function resetPlyrPlayers() {
        while (plyrPlayers.length) {
            const player = plyrPlayers.pop();
            try {
                if (player && typeof player.destroy === 'function') {
                    player.destroy();
                }
            } catch (error) {
                console.warn('销毁播放器实例失败', error);
            }
        }
    }

    function initPlyrPlayers() {
        if (!window.Plyr) {
            return;
        }
        const videos = document.querySelectorAll('.js-plyr');
        videos.forEach((videoEl) => {
            try {
                const player = new Plyr(videoEl, {
                    controls: ['play', 'progress', 'current-time', 'mute', 'volume', 'settings', 'fullscreen'],
                    ratio: '16:9'
                });
                plyrPlayers.push(player);
            } catch (error) {
                console.error('初始化播放器失败', error);
            }
        });
    }

    function guessVideoMimeType(url) {
        if (!url) return '';
        const cleanUrl = url.split('?')[0].split('#')[0];
        const ext = cleanUrl.substring(cleanUrl.lastIndexOf('.') + 1).toLowerCase();
        switch (ext) {
            case 'mp4':
                return 'video/mp4';
            case 'webm':
                return 'video/webm';
            case 'ogg':
            case 'ogv':
                return 'video/ogg';
            case 'm3u8':
                return 'application/x-mpegURL';
            default:
                return '';
        }
    }

    function buildVideoPlayer(videoUrl) {
        const videoWrapper = document.createElement('div');
        videoWrapper.className = 'video';

        if (!videoUrl) {
            const placeholder = document.createElement('div');
            placeholder.className = 'placeholder';
            placeholder.textContent = '尚未上传视频链接';
            videoWrapper.appendChild(placeholder);
            return videoWrapper;
        }

        const trimmedUrl = videoUrl.trim();
        if (!trimmedUrl) {
            const placeholder = document.createElement('div');
            placeholder.className = 'placeholder';
            placeholder.textContent = '尚未上传视频链接';
            videoWrapper.appendChild(placeholder);
            return videoWrapper;
        }

        const bilibiliEmbedRegex = /player\.bilibili\.com/i;
        const bilibiliBvMatch = trimmedUrl.match(/bilibili\.com\/video\/(BV[\w]+)/i);
        const bilibiliAvMatch = trimmedUrl.match(/bilibili\.com\/video\/av(\d+)/i);

        if (bilibiliEmbedRegex.test(trimmedUrl) || bilibiliBvMatch || bilibiliAvMatch) {
            let embedUrl = trimmedUrl;
            let page = 1;
            try {
                const urlObj = new URL(trimmedUrl, window.location.href);
                const pageParam = parseInt(urlObj.searchParams.get('p'), 10);
                if (!Number.isNaN(pageParam) && pageParam > 0) {
                    page = pageParam;
                }
            } catch (error) {
                // ignore parse errors
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
            videoWrapper.appendChild(iframe);
            return videoWrapper;
        }

        const video = document.createElement('video');
        video.className = 'js-plyr';
        video.setAttribute('controls', '');
        video.setAttribute('playsinline', '');
        video.setAttribute('preload', 'metadata');
        video.setAttribute('controlsList', 'nodownload');

        const source = document.createElement('source');
        source.src = trimmedUrl;
        const mimeType = guessVideoMimeType(trimmedUrl);
        if (mimeType) {
            source.type = mimeType;
        }
        video.appendChild(source);
        videoWrapper.appendChild(video);
        return videoWrapper;
    }

    function setCourseContent(course) {
        if (!course) {
            resetPlyrPlayers();
            courseTitleEl.textContent = '欢迎进入录播课程中心';
            courseDescriptionEl.textContent = '请选择左侧的课程查看详细内容。';
            courseDescriptionEl.classList.add('empty-state');
            lessonContainerEl.innerHTML = '';
            return;
        }

        courseTitleEl.textContent = course.title || '未命名课程';
        if (course.description) {
            courseDescriptionEl.textContent = course.description;
            courseDescriptionEl.classList.remove('empty-state');
        } else {
            courseDescriptionEl.textContent = '该课程暂无描述。';
            courseDescriptionEl.classList.add('empty-state');
        }
    }

    function renderLessons(lessons) {
        resetPlyrPlayers();
        lessonContainerEl.innerHTML = '';
        if (!lessons || lessons.length === 0) {
            const empty = document.createElement('p');
            empty.textContent = '该课程暂无课节，请稍后再来。';
            empty.className = 'empty-state';
            lessonContainerEl.appendChild(empty);
            return;
        }

        lessons.forEach((lesson) => {
            const wrapper = document.createElement('article');
            wrapper.className = 'lesson';

            const title = document.createElement('h3');
            title.textContent = lesson.title || '未命名课节';
            wrapper.appendChild(title);

            const videoWrapper = buildVideoPlayer(lesson.video_url);
            wrapper.appendChild(videoWrapper);
            lessonContainerEl.appendChild(wrapper);
        });

        initPlyrPlayers();
    }

    async function fetchJSON(url, options = {}) {
        const fetchOptions = Object.assign({ credentials: 'include' }, options || {});
        const extraHeaders = (options && options.headers) ? options.headers : {};
        fetchOptions.headers = Object.assign({ 'Accept': 'application/json' }, extraHeaders);
        const response = await fetch(url, fetchOptions);
        if (!response.ok) {
            let message = '接口请求失败，状态码：' + response.status;
            try {
                const err = await response.json();
                if (err && err.error) {
                    message = err.error;
                }
            } catch (e) {
                // ignore
            }
            throw new Error(message);
        }
        return response.json();
    }

    async function fetchSession() {
        try {
            const data = await fetchJSON(`${API_BASE}/session.php`);
            currentUser = data?.user || null;
            updateAuthUI();
            if (currentUser) {
                await Promise.all([loadCourses(), refreshAdminData()]);
            }
        } catch (error) {
            console.error(error);
        }
    }

    function updateAuthUI() {
        const loggedIn = Boolean(currentUser);
        loginForm.querySelector('button[type="submit"]').style.display = loggedIn ? 'none' : 'inline-block';
        logoutBtn.style.display = loggedIn ? 'inline-block' : 'none';
        loginForm.username.disabled = loggedIn;
        loginForm.password.disabled = loggedIn;
        setMessage(authMessageEl, '');
        if (loggedIn) {
            welcomeTextEl.textContent = `欢迎，${currentUser.display_name || currentUser.username}！`;
            courseSection.classList.add('active');
            adminPanel.classList.toggle('active', currentUser.role === 'admin');
            authPanel.querySelector('h2').textContent = '账户信息';
        } else {
            welcomeTextEl.textContent = '请先登录以查看您的专属课程。';
            courseSection.classList.remove('active');
            adminPanel.classList.remove('active');
            setCourseContent(null);
            courseListEl.innerHTML = '';
            lessonContainerEl.innerHTML = '';
            currentCourseId = null;
            authPanel.querySelector('h2').textContent = '账号登录';
        }
    }

    async function loadCourses() {
        if (!currentUser) {
            return;
        }
        courseLoadingEl.hidden = false;
        setMessage(courseMessageEl, '');

        try {
            const data = await fetchJSON(`${API_BASE}/courses.php`);
            const courses = Array.isArray(data?.courses) ? data.courses : [];

            if (courses.length === 0) {
                courseListEl.innerHTML = '<li class="empty-state">暂无课程，请联系管理员分配课程。</li>';
                setCourseContent(null);
                lessonContainerEl.innerHTML = '';
                return;
            }

            courseListEl.innerHTML = '';
            courses.forEach((course) => {
                const li = document.createElement('li');
                const button = document.createElement('button');
                button.type = 'button';
                button.textContent = course.title || '未命名课程';
                button.addEventListener('click', () => selectCourse(course.id));
                li.appendChild(button);

                if (course.description) {
                    const desc = document.createElement('p');
                    desc.textContent = course.description;
                    li.appendChild(desc);
                }

                courseListEl.appendChild(li);
            });
        } catch (error) {
            setMessage(courseMessageEl, error.message || '加载课程失败，请稍后重试。', 'error');
            courseListEl.innerHTML = '';
        } finally {
            courseLoadingEl.hidden = true;
        }
    }

    async function selectCourse(courseId) {
        if (!courseId || courseId === currentCourseId) {
            return;
        }
        currentCourseId = courseId;
        setCourseContent(null);
        lessonContainerEl.innerHTML = '<p class="loading">正在加载课节...</p>';

        try {
            const data = await fetchJSON(`${API_BASE}/courses.php?id=${encodeURIComponent(courseId)}`);
            const course = data?.course || null;
            const lessons = Array.isArray(data?.lessons) ? data.lessons : [];
            setCourseContent(course);
            renderLessons(lessons);
        } catch (error) {
            setCourseContent(null);
            lessonContainerEl.innerHTML = '';
            setMessage(courseMessageEl, error.message || '加载课节失败，请稍后重试。', 'error');
        }
    }

    async function refreshAdminData() {
        if (!currentUser || currentUser.role !== 'admin') {
            return;
        }
        try {
            const [userRes, courseRes] = await Promise.all([
                fetchJSON(`${API_BASE}/users.php`),
                fetchJSON(`${API_BASE}/courses.php?all=1`)
            ]);
            const users = Array.isArray(userRes?.users) ? userRes.users : [];
            const courses = Array.isArray(courseRes?.courses) ? courseRes.courses : [];
            assignUserSelect.innerHTML = '<option value="">请选择</option>';
            users.forEach((user) => {
                const option = document.createElement('option');
                option.value = user.id;
                option.textContent = `${user.display_name || user.username} (${user.role === 'admin' ? '管理员' : '学生'})`;
                assignUserSelect.appendChild(option);
            });
            const courseOptions = courses.map((course) => {
                const option = document.createElement('option');
                option.value = course.id;
                option.textContent = course.title || `课程${course.id}`;
                return option;
            });
            assignCourseSelect.innerHTML = '<option value="">请选择</option>';
            lessonCourseSelect.innerHTML = '<option value="">请选择</option>';
            courseOptions.forEach((option) => {
                assignCourseSelect.appendChild(option.cloneNode(true));
                lessonCourseSelect.appendChild(option.cloneNode(true));
            });
        } catch (error) {
            console.error(error);
        }
    }

    loginForm.addEventListener('submit', async (event) => {
        event.preventDefault();
        if (currentUser) {
            return;
        }
        const formData = new FormData(loginForm);
        const payload = {
            username: formData.get('username')?.trim(),
            password: formData.get('password'),
        };
        if (!payload.username || !payload.password) {
            setMessage(authMessageEl, '请输入用户名和密码', 'error');
            return;
        }
        setMessage(authMessageEl, '正在登录，请稍候...');
        try {
            const data = await fetchJSON(`${API_BASE}/login.php`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                body: JSON.stringify(payload)
            });
            currentUser = data?.user || null;
            loginForm.reset();
            updateAuthUI();
            await Promise.all([loadCourses(), refreshAdminData()]);
            setMessage(authMessageEl, '登录成功', 'info');
        } catch (error) {
            setMessage(authMessageEl, error.message || '登录失败，请重试', 'error');
        }
    });

    logoutBtn.addEventListener('click', async () => {
        try {
            await fetchJSON(`${API_BASE}/logout.php`, { method: 'POST' });
        } catch (error) {
            console.error(error);
        }
        currentUser = null;
        updateAuthUI();
    });

    createUserForm.addEventListener('submit', async (event) => {
        event.preventDefault();
        if (!currentUser || currentUser.role !== 'admin') return;
        createUserMessage.textContent = '正在创建用户...';
        try {
            const payload = {
                username: document.getElementById('newUsername').value.trim(),
                display_name: document.getElementById('newDisplayName').value.trim(),
                password: document.getElementById('newPassword').value,
                role: document.getElementById('newRole').value,
            };
            if (!payload.username || !payload.password) {
                createUserMessage.textContent = '用户名和密码不能为空';
                return;
            }
            await fetchJSON(`${API_BASE}/users.php`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(payload)
            });
            createUserForm.reset();
            createUserMessage.textContent = '创建成功';
            await refreshAdminData();
        } catch (error) {
            createUserMessage.textContent = error.message || '创建失败';
        }
    });

    createCourseForm.addEventListener('submit', async (event) => {
        event.preventDefault();
        if (!currentUser || currentUser.role !== 'admin') return;
        createCourseMessage.textContent = '正在创建课程...';
        try {
            const payload = {
                title: document.getElementById('courseTitleInput').value.trim(),
                description: document.getElementById('courseDescriptionInput').value.trim(),
            };
            if (!payload.title) {
                createCourseMessage.textContent = '课程名称不能为空';
                return;
            }
            await fetchJSON(`${API_BASE}/courses.php`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(payload)
            });
            createCourseForm.reset();
            createCourseMessage.textContent = '创建成功';
            await Promise.all([refreshAdminData(), loadCourses()]);
        } catch (error) {
            createCourseMessage.textContent = error.message || '创建失败';
        }
    });

    assignCourseForm.addEventListener('submit', async (event) => {
        event.preventDefault();
        if (!currentUser || currentUser.role !== 'admin') return;
        assignCourseMessage.textContent = '正在分配课程...';
        try {
            const payload = {
                user_id: parseInt(assignUserSelect.value, 10),
                course_id: parseInt(assignCourseSelect.value, 10),
            };
            if (!payload.user_id || !payload.course_id) {
                assignCourseMessage.textContent = '请选择用户和课程';
                return;
            }
            await fetchJSON(`${API_BASE}/course_assignments.php`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(payload)
            });
            assignCourseMessage.textContent = '分配成功';
            await loadCourses();
        } catch (error) {
            assignCourseMessage.textContent = error.message || '分配失败';
        }
    });

    createLessonForm.addEventListener('submit', async (event) => {
        event.preventDefault();
        if (!currentUser || currentUser.role !== 'admin') return;
        createLessonMessage.textContent = '正在添加课节...';
        try {
            const payload = {
                course_id: parseInt(lessonCourseSelect.value, 10),
                title: document.getElementById('lessonTitle').value.trim(),
                video_url: document.getElementById('lessonVideo').value.trim(),
            };
            if (!payload.course_id || !payload.title) {
                createLessonMessage.textContent = '请选择课程并填写课节标题';
                return;
            }
            await fetchJSON(`${API_BASE}/lessons.php`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(payload)
            });
            createLessonForm.reset();
            createLessonMessage.textContent = '添加成功';
            if (currentCourseId === payload.course_id) {
                await selectCourse(payload.course_id);
            }
        } catch (error) {
            createLessonMessage.textContent = error.message || '添加失败';
        }
    });

    fetchSession();
})();
</script>
</body>
</html>
