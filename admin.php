<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>网课系统 · 管理后台</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/main.css">
    <style>
        .admin-header {
            display: flex;
            flex-direction: column;
            gap: 0.85rem;
        }

        .admin-header h1 {
            margin: 0;
            font-size: 2rem;
            letter-spacing: -0.02em;
        }

        .admin-header p {
            margin: 0;
            color: var(--text-secondary);
            line-height: 1.65;
        }

        .section-grid {
            display: grid;
            gap: 1.75rem;
        }

        @media (min-width: 1024px) {
            .section-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }

        .list-card {
            padding: 1.75rem;
        }

        .list-card h3 {
            margin-top: 0;
            margin-bottom: 1.1rem;
        }

        .hint {
            font-size: 0.85rem;
            color: var(--text-secondary);
            margin-top: 0.35rem;
            line-height: 1.5;
        }

        .empty-hint {
            padding: 1.25rem;
            border-radius: var(--radius-sm);
            background: rgba(148, 163, 184, 0.1);
            color: var(--text-secondary);
            text-align: center;
            font-size: 0.95rem;
        }
    </style>
</head>
<body class="app-shell">
<header class="app-header">
    <div class="inner">
        <div class="brand">管理后台</div>
        <div style="display:flex; align-items:center; gap:0.75rem; flex-wrap: wrap;">
            <div class="user-chip" id="adminChip"></div>
            <button class="ghost-button" id="backButton">返回课堂</button>
            <button class="ghost-button" id="logoutButton">退出登录</button>
        </div>
    </div>
</header>
<main class="app-main">
    <section class="card surface-section">
        <div class="admin-header">
            <h1>快速配置教学内容</h1>
            <p>管理用户、课程与课节，分配资源给不同的学员。所有操作实时生效。</p>
            <div class="pill-tabs" role="tablist">
                <button type="button" class="active" data-target="users">用户管理</button>
                <button type="button" data-target="courses">课程管理</button>
                <button type="button" data-target="lessons">课节管理</button>
                <button type="button" data-target="assignments">课程分配</button>
            </div>
        </div>
        <div class="tab-content active" id="tab-users" role="tabpanel">
            <div class="split" style="margin-top:2rem; gap:2rem;">
                <form id="createUserForm" class="card surface-section form-grid" style="padding:2rem;">
                    <div>
                        <label for="newUsername">用户名</label>
                        <input id="newUsername" name="username" placeholder="例如：student01" required>
                    </div>
                    <div>
                        <label for="newDisplayName">显示名称</label>
                        <input id="newDisplayName" name="display_name" placeholder="学生姓名或昵称">
                    </div>
                    <div>
                        <label for="newPassword">初始密码</label>
                        <input id="newPassword" name="password" type="password" placeholder="设置登录密码" required>
                    </div>
                    <div>
                        <label for="newRole">角色</label>
                        <select id="newRole" name="role">
                            <option value="student">学员</option>
                            <option value="admin">管理员</option>
                        </select>
                    </div>
                    <button type="submit" class="primary-button">创建用户</button>
                    <div class="message inline" id="createUserMessage" hidden></div>
                </form>
                <div class="card list-card">
                    <h3>现有用户</h3>
                    <ul class="table-list" id="userList"></ul>
                </div>
            </div>
        </div>
        <div class="tab-content" id="tab-courses" role="tabpanel">
            <div class="split" style="margin-top:2rem; gap:2rem;">
                <form id="createCourseForm" class="card surface-section form-grid" style="padding:2rem;">
                    <div>
                        <label for="courseTitleInput">课程名称</label>
                        <input id="courseTitleInput" name="title" placeholder="例如：高等数学" required>
                    </div>
                    <div>
                        <label for="courseDescriptionInput">课程简介</label>
                        <textarea id="courseDescriptionInput" name="description" rows="4" placeholder="补充课程概述与亮点"></textarea>
                    </div>
                    <button type="submit" class="primary-button">创建课程</button>
                    <div class="message inline" id="createCourseMessage" hidden></div>
                </form>
                <div class="card list-card">
                    <h3>课程列表</h3>
                    <ul class="table-list" id="courseList"></ul>
                    <div class="message inline" id="courseListMessage" hidden></div>
                </div>
            </div>
        </div>
        <div class="tab-content" id="tab-lessons" role="tabpanel">
            <div class="split" style="margin-top:2rem; gap:2rem;">
                <form id="createLessonForm" class="card surface-section form-grid" style="padding:2rem;">
                    <div>
                        <label for="lessonCourseSelect">选择课程</label>
                        <select id="lessonCourseSelect" required></select>
                    </div>
                    <div>
                        <label for="lessonTitle">课节标题</label>
                        <input id="lessonTitle" placeholder="例如：第一讲 极限的概念" required>
                    </div>
                    <div>
                        <label for="lessonVideo">视频链接</label>
                        <input id="lessonVideo" placeholder="支持本地文件链接或哔哩哔哩地址">
                        <p class="hint">示例：<code>https://example.com/video.mp4</code> 或 <code>https://www.bilibili.com/video/BVxxxx</code></p>
                    </div>
                    <button type="submit" class="primary-button">添加课节</button>
                    <div class="message inline" id="createLessonMessage" hidden></div>
                </form>
                <div style="display:flex; flex-direction:column; gap:1.5rem;">
                    <div class="card list-card">
                        <h3>课节列表</h3>
                        <p class="hint">选择课程后可查看现有课节，并删除不再需要的内容。</p>
                        <ul class="table-list" id="lessonList">
                            <li class="text-muted">请选择课程查看课节</li>
                        </ul>
                        <div class="message inline" id="lessonListMessage" hidden></div>
                    </div>
                    <div class="card list-card">
                        <h3>课节小贴士</h3>
                        <p class="hint">添加课节后，学员刷新课程即可观看最新内容。建议为不同来源的视频提供清晰命名，便于识别。</p>
                        <div class="empty-hint" style="margin-top:1.5rem;">删除课节后，已分配的学员将无法再看到该内容。</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="tab-content" id="tab-assignments" role="tabpanel">
            <div class="split" style="margin-top:2rem; gap:2rem;">
                <form id="assignCourseForm" class="card surface-section form-grid" style="padding:2rem;">
                    <div>
                        <label for="assignUserSelect">选择用户</label>
                        <select id="assignUserSelect" required></select>
                    </div>
                    <div>
                        <label for="assignCourseSelect">选择课程</label>
                        <select id="assignCourseSelect" required></select>
                    </div>
                    <button type="submit" class="primary-button">分配课程</button>
                    <div class="message inline" id="assignCourseMessage" hidden></div>
                </form>
                <div style="display:flex; flex-direction:column; gap:1.5rem;">
                    <div class="card list-card">
                        <h3>已分配课程</h3>
                        <p class="hint">切换下方用户可查看课程列表，并可一键移除不再需要的课程。</p>
                        <ul class="table-list" id="assignmentList">
                            <li class="text-muted">请选择用户查看已分配课程</li>
                        </ul>
                    </div>
                    <div class="card list-card">
                        <h3>使用说明</h3>
                        <p class="hint">分配操作会立即生效；学员再次打开课程列表即可看到新的课程。</p>
                        <div class="empty-hint" style="margin-top:1.5rem;">重复分配同一课程不会产生错误，系统会自动忽略。</div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</main>
<script>
    const API_BASE = 'api';
    const logoutButton = document.getElementById('logoutButton');
    const backButton = document.getElementById('backButton');
    const adminChip = document.getElementById('adminChip');

    const tabButtons = document.querySelectorAll('.pill-tabs button');
    const tabContents = document.querySelectorAll('.tab-content');

    const createUserForm = document.getElementById('createUserForm');
    const createUserMessage = document.getElementById('createUserMessage');
    const userListEl = document.getElementById('userList');

    const createCourseForm = document.getElementById('createCourseForm');
    const createCourseMessage = document.getElementById('createCourseMessage');
    const courseListEl = document.getElementById('courseList');
    const courseListMessage = document.getElementById('courseListMessage');

    const createLessonForm = document.getElementById('createLessonForm');
    const createLessonMessage = document.getElementById('createLessonMessage');
    const lessonCourseSelect = document.getElementById('lessonCourseSelect');
    const lessonListEl = document.getElementById('lessonList');
    const lessonListMessage = document.getElementById('lessonListMessage');

    const assignCourseForm = document.getElementById('assignCourseForm');
    const assignCourseMessage = document.getElementById('assignCourseMessage');
    const assignUserSelect = document.getElementById('assignUserSelect');
    const assignCourseSelect = document.getElementById('assignCourseSelect');
    const assignmentListEl = document.getElementById('assignmentList');

    let state = {
        users: [],
        courses: [],
        lessons: {},
        currentUser: null
    };

    function setMessage(element, text = '', type = '') {
        if (!element) {
            return;
        }
        const message = text || '';
        element.textContent = message;
        element.classList.remove('error', 'success');
        const hasText = Boolean(message);
        element.hidden = !hasText;
        if (hasText && type) {
            element.classList.add(type);
        }
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
            const message = data?.message || data?.error || '请求失败';
            throw new Error(message);
        }
        return data;
    }

    function refreshUserList() {
        userListEl.innerHTML = '';
        if (!state.users.length) {
            const empty = document.createElement('li');
            empty.textContent = '暂无用户';
            empty.style.color = 'var(--text-secondary)';
            userListEl.appendChild(empty);
            return;
        }
        state.users.forEach((user) => {
            const item = document.createElement('li');
            const label = document.createElement('div');
            label.innerHTML = `<strong>${user.display_name || user.username}</strong><div class="text-muted" style="font-size:0.85rem;">${user.username} · ${user.role === 'admin' ? '管理员' : '学员'}</div>`;
            item.appendChild(label);
            userListEl.appendChild(item);
        });
    }

    function refreshCourseList() {
        courseListEl.innerHTML = '';
        setMessage(courseListMessage);
        if (!state.courses.length) {
            const empty = document.createElement('li');
            empty.textContent = '暂无课程';
            empty.style.color = 'var(--text-secondary)';
            courseListEl.appendChild(empty);
            return;
        }
        state.courses.forEach((course) => {
            const item = document.createElement('li');
            const label = document.createElement('div');
            label.innerHTML = `<strong>${course.title}</strong><div class="text-muted" style="font-size:0.85rem;">${course.description || '暂无描述'}</div>`;
            item.appendChild(label);

            const action = document.createElement('button');
            action.type = 'button';
            action.className = 'inline-button danger';
            action.dataset.courseId = course.id;
            action.textContent = '删除';
            item.appendChild(action);

            courseListEl.appendChild(item);
        });
    }

    function populateSelect(selectEl, items, valueKey, labelResolver, preferredValue = null) {
        const fallbackValue = preferredValue ?? selectEl.value ?? '';
        selectEl.innerHTML = '';
        if (!items.length) {
            const option = document.createElement('option');
            option.value = '';
            option.textContent = '暂无数据';
            selectEl.appendChild(option);
            selectEl.disabled = true;
            return '';
        }
        selectEl.disabled = false;
        const fallbackString = fallbackValue !== null && fallbackValue !== undefined ? String(fallbackValue) : '';
        let matched = false;
        items.forEach((item) => {
            const option = document.createElement('option');
            const value = item[valueKey];
            option.value = value;
            const label = typeof labelResolver === 'function' ? labelResolver(item) : (item[labelResolver] || `ID ${value}`);
            option.textContent = label;
            if (!matched && String(value) === fallbackString) {
                option.selected = true;
                matched = true;
            }
            selectEl.appendChild(option);
        });
        if (!matched) {
            selectEl.selectedIndex = 0;
        }
        return selectEl.value;
    }

    function summarize(text, maxLength = 60) {
        if (!text) {
            return '';
        }
        const clean = String(text).replace(/\s+/g, ' ').trim();
        if (clean.length <= maxLength) {
            return clean;
        }
        return `${clean.slice(0, maxLength)}…`;
    }

    function renderLessonPlaceholder(text, tone = 'muted') {
        lessonListEl.innerHTML = '';
        const item = document.createElement('li');
        item.textContent = text;
        if (tone === 'error') {
            item.style.color = '#b91c1c';
        } else {
            item.className = 'text-muted';
        }
        lessonListEl.appendChild(item);
    }

    function renderLessons(courseId, lessons) {
        lessonListEl.innerHTML = '';
        if (!Array.isArray(lessons) || !lessons.length) {
            renderLessonPlaceholder('该课程还没有课节');
            return;
        }
        lessons.forEach((lesson, index) => {
            const item = document.createElement('li');
            const info = document.createElement('div');
            const title = document.createElement('strong');
            title.textContent = `${index + 1}. ${lesson.title}`;
            info.appendChild(title);
            const meta = document.createElement('div');
            meta.className = 'text-muted';
            meta.style.fontSize = '0.85rem';
            const videoText = summarize(lesson.video_url || '', 70);
            meta.textContent = videoText ? `课节ID：${lesson.id} · ${videoText}` : `课节ID：${lesson.id}`;
            info.appendChild(meta);
            item.appendChild(info);

            const action = document.createElement('button');
            action.type = 'button';
            action.className = 'inline-button danger';
            action.dataset.lessonId = lesson.id;
            action.dataset.courseId = courseId;
            action.textContent = '删除';
            item.appendChild(action);

            lessonListEl.appendChild(item);
        });
    }

    async function loadLessonsForCourse(courseId) {
        if (!courseId) {
            renderLessonPlaceholder('请选择课程查看课节');
            return;
        }
        renderLessonPlaceholder('正在加载课节...');
        try {
            const data = await fetchJSON(`${API_BASE}/courses.php?id=${courseId}`);
            const lessons = (data.lessons || []).map((lesson) => ({
                ...lesson,
                id: Number(lesson.id)
            }));
            state.lessons[courseId] = lessons;
            renderLessons(courseId, lessons);
            setMessage(lessonListMessage);
        } catch (error) {
            renderLessonPlaceholder(error.message || '加载课节失败', 'error');
            setMessage(lessonListMessage, error.message || '加载课节失败', 'error');
        }
    }

    function renderAssignmentPlaceholder(text, tone = 'muted') {
        assignmentListEl.innerHTML = '';
        const item = document.createElement('li');
        item.textContent = text;
        if (tone === 'error') {
            item.style.color = '#b91c1c';
        } else {
            item.className = 'text-muted';
        }
        assignmentListEl.appendChild(item);
    }

    function renderAssignments(assignments) {
        assignmentListEl.innerHTML = '';
        if (!assignments.length) {
            renderAssignmentPlaceholder('尚未分配课程');
            return;
        }
        assignments.forEach((assignment) => {
            const item = document.createElement('li');
            const info = document.createElement('div');
            const title = document.createElement('strong');
            title.textContent = assignment.course_title || `课程 ${assignment.course_id}`;
            info.appendChild(title);
            const meta = document.createElement('div');
            meta.className = 'text-muted';
            meta.style.fontSize = '0.85rem';
            const description = summarize(assignment.course_description || '', 64);
            meta.textContent = description ? `课程ID：${assignment.course_id} · ${description}` : `课程ID：${assignment.course_id}`;
            info.appendChild(meta);
            item.appendChild(info);

            const action = document.createElement('button');
            action.type = 'button';
            action.className = 'inline-button danger';
            action.dataset.courseId = assignment.course_id;
            action.textContent = '移除';
            item.appendChild(action);

            assignmentListEl.appendChild(item);
        });
    }

    async function loadAssignmentsForUser(userId) {
        if (!userId) {
            renderAssignmentPlaceholder('请选择用户查看已分配课程');
            return;
        }
        renderAssignmentPlaceholder('正在加载已分配课程...');
        try {
            const data = await fetchJSON(`${API_BASE}/course_assignments.php?user_id=${userId}`);
            renderAssignments(data.assignments || []);
        } catch (error) {
            renderAssignmentPlaceholder(error.message || '加载已分配课程失败', 'error');
        }
    }

    async function loadInitialData() {
        try {
            const session = await fetchJSON(`${API_BASE}/session.php`);
            if (!session.user || session.user.role !== 'admin') {
                window.location.href = 'dashboard.php';
                return;
            }
            state.currentUser = session.user;
            adminChip.textContent = `${session.user.display_name || session.user.username} · 管理员`;
            const [usersData, coursesData] = await Promise.all([
                fetchJSON(`${API_BASE}/users.php`),
                fetchJSON(`${API_BASE}/courses.php?all=1`)
            ]);
            state.users = usersData.users || [];
            state.courses = coursesData.courses || [];
            refreshUserList();
            refreshCourseList();
            const selectedUser = populateSelect(assignUserSelect, state.users, 'id', (user) => user.display_name || user.username);
            populateSelect(assignCourseSelect, state.courses, 'id', 'title');
            const selectedLessonCourse = populateSelect(lessonCourseSelect, state.courses, 'id', 'title');

            const initialUserId = parseInt(selectedUser, 10);
            if (initialUserId) {
                loadAssignmentsForUser(initialUserId);
            } else {
                renderAssignmentPlaceholder('暂无用户，请先创建。');
            }

            const initialCourseId = parseInt(selectedLessonCourse, 10);
            if (initialCourseId) {
                loadLessonsForCourse(initialCourseId);
            } else if (!state.courses.length) {
                renderLessonPlaceholder('暂无课程，请先创建。');
            } else {
                renderLessonPlaceholder('请选择课程查看课节');
            }
        } catch (error) {
            alert(error.message || '加载管理信息失败');
            window.location.href = 'index.php';
        }
    }

    tabButtons.forEach((button) => {
        button.addEventListener('click', () => {
            tabButtons.forEach((btn) => btn.classList.toggle('active', btn === button));
            const target = button.dataset.target;
            tabContents.forEach((content) => {
                content.classList.toggle('active', content.id === `tab-${target}`);
            });
        });
    });

    assignUserSelect.addEventListener('change', () => {
        const userId = parseInt(assignUserSelect.value, 10);
        setMessage(assignCourseMessage);
        if (userId) {
            loadAssignmentsForUser(userId);
        } else {
            renderAssignmentPlaceholder('请选择用户查看已分配课程');
        }
    });

    lessonCourseSelect.addEventListener('change', () => {
        const courseId = parseInt(lessonCourseSelect.value, 10);
        setMessage(createLessonMessage);
        setMessage(lessonListMessage);
        if (courseId) {
            loadLessonsForCourse(courseId);
        } else if (!state.courses.length) {
            renderLessonPlaceholder('暂无课程，请先创建。');
        } else {
            renderLessonPlaceholder('请选择课程查看课节');
        }
    });

    courseListEl.addEventListener('click', async (event) => {
        const button = event.target.closest('button[data-course-id]');
        if (!button) {
            return;
        }
        const courseId = parseInt(button.dataset.courseId, 10);
        if (!courseId) {
            return;
        }
        const course = state.courses.find((item) => item.id === courseId);
        const courseLabel = course?.title ? `课程「${course.title}」` : '该课程';
        if (!window.confirm(`确定删除${courseLabel}？删除后将同步移除课节与课程分配。`)) {
            return;
        }
        const originalLabel = button.textContent;
        button.disabled = true;
        button.textContent = '删除中...';
        setMessage(courseListMessage, '正在删除课程，请稍候...');
        try {
            await fetchJSON(`${API_BASE}/courses.php`, {
                method: 'DELETE',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ course_id: courseId })
            });
            state.courses = state.courses.filter((item) => item.id !== courseId);
            delete state.lessons[courseId];
            refreshCourseList();
            setMessage(courseListMessage, '课程已删除', 'success');

            const previousLessonSelection = lessonCourseSelect.value;
            const previousAssignSelection = assignCourseSelect.value;

            const nextLessonValue = populateSelect(
                lessonCourseSelect,
                state.courses,
                'id',
                'title',
                previousLessonSelection && parseInt(previousLessonSelection, 10) !== courseId ? previousLessonSelection : ''
            );

            populateSelect(
                assignCourseSelect,
                state.courses,
                'id',
                'title',
                previousAssignSelection && parseInt(previousAssignSelection, 10) !== courseId ? previousAssignSelection : ''
            );

            const selectedLessonId = parseInt(nextLessonValue, 10);
            if (selectedLessonId) {
                await loadLessonsForCourse(selectedLessonId);
            } else if (!state.courses.length) {
                renderLessonPlaceholder('暂无课程，请先创建。');
            } else {
                renderLessonPlaceholder('请选择课程查看课节');
            }
            setMessage(lessonListMessage);

            const selectedUserId = parseInt(assignUserSelect.value, 10);
            if (selectedUserId) {
                await loadAssignmentsForUser(selectedUserId);
            } else {
                renderAssignmentPlaceholder('请选择用户查看已分配课程');
            }
            setMessage(assignCourseMessage);
            setMessage(createLessonMessage);
        } catch (error) {
            setMessage(courseListMessage, error.message || '删除课程失败', 'error');
            button.disabled = false;
            button.textContent = originalLabel;
        }
    });

    assignmentListEl.addEventListener('click', async (event) => {
        const button = event.target.closest('button[data-course-id]');
        if (!button) {
            return;
        }
        const userId = parseInt(assignUserSelect.value, 10);
        const courseId = parseInt(button.dataset.courseId, 10);
        if (!userId || !courseId) {
            return;
        }
        const originalLabel = button.textContent;
        button.disabled = true;
        button.textContent = '移除中...';
        try {
            await fetchJSON(`${API_BASE}/course_assignments.php`, {
                method: 'DELETE',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ user_id: userId, course_id: courseId })
            });
            setMessage(assignCourseMessage, '已移除课程', 'success');
            await loadAssignmentsForUser(userId);
        } catch (error) {
            setMessage(assignCourseMessage, error.message || '移除课程失败', 'error');
            button.disabled = false;
            button.textContent = originalLabel;
        }
    });

    lessonListEl.addEventListener('click', async (event) => {
        const button = event.target.closest('button[data-lesson-id]');
        if (!button) {
            return;
        }
        const lessonId = parseInt(button.dataset.lessonId, 10);
        const courseId = parseInt(button.dataset.courseId || lessonCourseSelect.value, 10);
        if (!lessonId) {
            return;
        }
        if (!window.confirm('确定删除该课节？删除后无法恢复。')) {
            return;
        }
        const originalLabel = button.textContent;
        button.disabled = true;
        button.textContent = '删除中...';
        try {
            await fetchJSON(`${API_BASE}/lessons.php`, {
                method: 'DELETE',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ lesson_id: lessonId })
            });
            setMessage(lessonListMessage, '课节已删除', 'success');
            await loadLessonsForCourse(courseId);
        } catch (error) {
            setMessage(lessonListMessage, error.message || '删除课节失败', 'error');
            button.disabled = false;
            button.textContent = originalLabel;
        }
    });

    createUserForm.addEventListener('submit', async (event) => {
        event.preventDefault();
        const payload = {
            username: document.getElementById('newUsername').value.trim(),
            display_name: document.getElementById('newDisplayName').value.trim(),
            password: document.getElementById('newPassword').value,
            role: document.getElementById('newRole').value
        };
        if (!payload.username || !payload.password) {
            setMessage(createUserMessage, '用户名和密码不能为空', 'error');
            return;
        }
        setMessage(createUserMessage, '正在创建用户，请稍候...');
        try {
            const result = await fetchJSON(`${API_BASE}/users.php`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(payload)
            });
            state.users.push(result.user);
            refreshUserList();
            const selectedAfterCreate = populateSelect(
                assignUserSelect,
                state.users,
                'id',
                (user) => user.display_name || user.username,
                result.user.id
            );
            createUserForm.reset();
            setMessage(createUserMessage, '创建成功', 'success');
            setMessage(assignCourseMessage);
            const newUserId = parseInt(selectedAfterCreate, 10);
            if (newUserId) {
                loadAssignmentsForUser(newUserId);
            }
        } catch (error) {
            setMessage(createUserMessage, error.message || '创建失败', 'error');
        }
    });

    createCourseForm.addEventListener('submit', async (event) => {
        event.preventDefault();
        const payload = {
            title: document.getElementById('courseTitleInput').value.trim(),
            description: document.getElementById('courseDescriptionInput').value.trim()
        };
        if (!payload.title) {
            setMessage(createCourseMessage, '课程名称不能为空', 'error');
            return;
        }
        setMessage(createCourseMessage, '正在创建课程，请稍候...');
        try {
            const result = await fetchJSON(`${API_BASE}/courses.php`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(payload)
            });
            state.courses.push(result.course);
            refreshCourseList();
            const newCourseId = result.course.id;
            populateSelect(assignCourseSelect, state.courses, 'id', 'title', newCourseId);
            const lessonSelectValue = populateSelect(lessonCourseSelect, state.courses, 'id', 'title', newCourseId);
            createCourseForm.reset();
            setMessage(createCourseMessage, '课程创建成功', 'success');
            setMessage(lessonListMessage);
            const createdCourseId = parseInt(lessonSelectValue, 10);
            if (createdCourseId) {
                loadLessonsForCourse(createdCourseId);
            }
        } catch (error) {
            setMessage(createCourseMessage, error.message || '创建失败', 'error');
        }
    });

    assignCourseForm.addEventListener('submit', async (event) => {
        event.preventDefault();
        const payload = {
            user_id: parseInt(assignUserSelect.value, 10),
            course_id: parseInt(assignCourseSelect.value, 10)
        };
        if (!payload.user_id || !payload.course_id) {
            setMessage(assignCourseMessage, '请选择用户和课程', 'error');
            return;
        }
        setMessage(assignCourseMessage, '正在分配，请稍候...');
        try {
            await fetchJSON(`${API_BASE}/course_assignments.php`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(payload)
            });
            await loadAssignmentsForUser(payload.user_id);
            setMessage(assignCourseMessage, '分配成功', 'success');
        } catch (error) {
            setMessage(assignCourseMessage, error.message || '分配失败', 'error');
        }
    });

    createLessonForm.addEventListener('submit', async (event) => {
        event.preventDefault();
        const payload = {
            course_id: parseInt(lessonCourseSelect.value, 10),
            title: document.getElementById('lessonTitle').value.trim(),
            video_url: document.getElementById('lessonVideo').value.trim()
        };
        if (!payload.course_id || !payload.title) {
            setMessage(createLessonMessage, '请选择课程并填写课节标题', 'error');
            return;
        }
        setMessage(createLessonMessage, '正在添加课节...');
        try {
            await fetchJSON(`${API_BASE}/lessons.php`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(payload)
            });
            createLessonForm.reset();
            const selectedCourseAfterCreate = populateSelect(
                lessonCourseSelect,
                state.courses,
                'id',
                'title',
                payload.course_id
            );
            setMessage(createLessonMessage, '课节添加成功', 'success');
            setMessage(lessonListMessage);
            const refreshedCourseId = parseInt(selectedCourseAfterCreate, 10);
            if (refreshedCourseId) {
                loadLessonsForCourse(refreshedCourseId);
            }
        } catch (error) {
            setMessage(createLessonMessage, error.message || '添加失败', 'error');
        }
    });

    logoutButton.addEventListener('click', async () => {
        try {
            await fetchJSON(`${API_BASE}/logout.php`, { method: 'POST' });
        } catch (error) {
            console.error(error);
        }
        window.location.href = 'index.php';
    });

    backButton.addEventListener('click', () => {
        window.location.href = 'dashboard.php';
    });

    loadInitialData();
</script>
</body>
</html>
