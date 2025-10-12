<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>智能课堂 · 管理后台</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/main.css">
</head>
<body class="app-shell">
<header class="topbar">
    <div class="topbar__brand">
        <span class="dot"></span>
        <div class="brand-text">
            <strong>智能课堂</strong>
            <small>管理后台</small>
        </div>
    </div>
    <div class="topbar__actions">
        <div class="user-pill" id="currentAdmin">加载中…</div>
        <a class="btn btn-ghost" href="dashboard.php">返回课堂</a>
        <button class="btn btn-light" id="logoutBtn">退出登录</button>
    </div>
</header>
<div class="admin-layout">
    <nav class="admin-sidebar" role="tablist">
        <button class="sidebar-btn is-active" data-target="usersPanel">用户管理</button>
        <button class="sidebar-btn" data-target="coursesPanel">录播课程</button>
        <button class="sidebar-btn" data-target="livePanel">直播课程</button>
    </nav>
    <main class="admin-main">
        <section id="usersPanel" class="admin-panel is-active" role="tabpanel">
            <header class="panel-header">
                <div>
                    <h1>用户管理</h1>
                    <p>创建账号、更新信息并维护课程分配。</p>
                </div>
            </header>
            <div class="panel-body split">
                <aside class="panel-card list-panel">
                    <div class="panel-card__header">
                        <h2>全部用户</h2>
                    </div>
                    <ul id="usersList" class="list"></ul>
                    <div id="usersEmpty" class="empty-state" hidden>
                        <p>暂无用户，请先创建。</p>
                    </div>
                </aside>
                <div class="panel-card detail-panel">
                    <section class="stack">
                        <h2>创建新用户</h2>
                        <form id="createUserForm" class="form-grid">
                            <label>
                                <span>用户名</span>
                                <input type="text" name="username" required>
                            </label>
                            <label>
                                <span>显示名称</span>
                                <input type="text" name="display_name" placeholder="可选">
                            </label>
                            <label>
                                <span>初始密码</span>
                                <input type="password" name="password" required>
                            </label>
                            <label>
                                <span>角色</span>
                                <select name="role">
                                    <option value="student">学员</option>
                                    <option value="admin">管理员</option>
                                </select>
                            </label>
                            <button type="submit" class="btn btn-primary">创建用户</button>
                            <p class="form-tip" id="createUserMessage"></p>
                        </form>
                    </section>
                    <section class="stack">
                        <h2>用户详情</h2>
                        <div id="userDetailEmpty" class="empty-state">
                            <p>请选择左侧用户查看并编辑信息。</p>
                        </div>
                        <form id="userDetailForm" class="form-grid" hidden>
                            <input type="hidden" name="id" id="userDetailId">
                            <label>
                                <span>用户名</span>
                                <input type="text" name="username" id="userDetailUsername" required>
                            </label>
                            <label>
                                <span>显示名称</span>
                                <input type="text" name="display_name" id="userDetailDisplayName">
                            </label>
                            <label>
                                <span>角色</span>
                                <select name="role" id="userDetailRole">
                                    <option value="student">学员</option>
                                    <option value="admin">管理员</option>
                                </select>
                            </label>
                            <label>
                                <span>重置密码</span>
                                <input type="password" name="password" id="userDetailPassword" placeholder="留空则不修改">
                            </label>
                            <div class="form-actions">
                                <button type="submit" class="btn btn-primary">保存修改</button>
                                <button type="button" class="btn btn-danger" id="deleteUserBtn">删除用户</button>
                            </div>
                            <p class="form-tip" id="userDetailMessage"></p>
                        </form>
                        <div id="userAssignments" class="assignments" hidden>
                            <div class="assignments__header">
                                <h3>已分配课程</h3>
                                <div class="assignments__action">
                                    <select id="assignmentCourseSelect"></select>
                                    <button class="btn btn-light" id="assignCourseBtn">分配</button>
                                </div>
                            </div>
                            <ul id="assignmentList" class="chip-list"></ul>
                        </div>
                    </section>
                </div>
            </div>
        </section>

        <section id="coursesPanel" class="admin-panel" role="tabpanel">
            <header class="panel-header">
                <div>
                    <h1>录播课程</h1>
                    <p>维护课程信息与课节内容。</p>
                </div>
            </header>
            <div class="panel-body split">
                <aside class="panel-card list-panel">
                    <div class="panel-card__header">
                        <h2>课程列表</h2>
                    </div>
                    <ul id="coursesList" class="list"></ul>
                    <div id="coursesEmpty" class="empty-state" hidden>
                        <p>暂无课程，请先创建。</p>
                    </div>
                </aside>
                <div class="panel-card detail-panel">
                    <section class="stack">
                        <h2>新增课程</h2>
                        <form id="createCourseForm" class="form-grid">
                            <label>
                                <span>课程标题</span>
                                <input type="text" name="title" required>
                            </label>
                            <label>
                                <span>课程简介</span>
                                <textarea name="description" rows="3" placeholder="可选"></textarea>
                            </label>
                            <button type="submit" class="btn btn-primary">创建课程</button>
                            <p class="form-tip" id="createCourseMessage"></p>
                        </form>
                    </section>
                    <section class="stack">
                        <h2>课程详情</h2>
                        <div id="courseDetailEmpty" class="empty-state">
                            <p>请选择左侧课程查看详情。</p>
                        </div>
                        <form id="courseDetailForm" class="form-grid" hidden>
                            <input type="hidden" id="courseDetailId">
                            <label>
                                <span>课程标题</span>
                                <input type="text" id="courseDetailTitle" required>
                            </label>
                            <label>
                                <span>课程简介</span>
                                <textarea id="courseDetailDescription" rows="3"></textarea>
                            </label>
                            <div class="form-actions">
                                <button type="submit" class="btn btn-primary">保存修改</button>
                                <button type="button" class="btn btn-danger" id="deleteCourseBtn">删除课程</button>
                            </div>
                            <p class="form-tip" id="courseDetailMessage"></p>
                        </form>
                        <div id="lessonManager" class="lesson-manager" hidden>
                            <h3>课节管理</h3>
                            <form id="createLessonForm" class="form-grid">
                                <input type="hidden" name="course_id" id="newLessonCourseId">
                                <label>
                                    <span>课节标题</span>
                                    <input type="text" name="title" required>
                                </label>
                                <label>
                                    <span>视频地址</span>
                                    <input type="text" name="video_url" placeholder="支持外链或本地文件">
                                </label>
                                <button type="submit" class="btn btn-light">添加课节</button>
                                <p class="form-tip" id="createLessonMessage"></p>
                            </form>
                            <div class="lessons-list-wrapper">
                                <ul id="lessonsList" class="list"></ul>
                                <div id="lessonsEmpty" class="empty-state" hidden>
                                    <p>该课程暂未添加课节。</p>
                                </div>
                            </div>
                            <form id="editLessonForm" class="form-grid" hidden>
                                <input type="hidden" id="editLessonId">
                                <label>
                                    <span>课节标题</span>
                                    <input type="text" id="editLessonTitle" required>
                                </label>
                                <label>
                                    <span>视频地址</span>
                                    <input type="text" id="editLessonVideo" placeholder="支持外链或本地文件">
                                </label>
                                <div class="form-actions">
                                    <button type="submit" class="btn btn-primary">更新课节</button>
                                    <button type="button" class="btn btn-danger" id="deleteLessonBtn">删除课节</button>
                                </div>
                                <p class="form-tip" id="editLessonMessage"></p>
                            </form>
                        </div>
                    </section>
                </div>
            </div>
        </section>

        <section id="livePanel" class="admin-panel" role="tabpanel">
            <header class="panel-header">
                <div>
                    <h1>直播课程</h1>
                    <p>单独维护直播课安排，与录播课程分离管理。</p>
                </div>
            </header>
            <div class="panel-body split">
                <aside class="panel-card list-panel">
                    <div class="panel-card__header">
                        <h2>直播安排</h2>
                    </div>
                    <ul id="liveList" class="list"></ul>
                    <div id="liveEmpty" class="empty-state" hidden>
                        <p>暂无直播安排。</p>
                    </div>
                </aside>
                <div class="panel-card detail-panel">
                    <section class="stack">
                        <h2>新增直播课</h2>
                        <form id="createLiveForm" class="form-grid">
                            <label>
                                <span>关联课程</span>
                                <select name="course_id" id="createLiveCourse" required></select>
                            </label>
                            <label>
                                <span>直播标题</span>
                                <input type="text" name="title" required>
                            </label>
                            <label>
                                <span>直播地址</span>
                                <input type="text" name="stream_url" placeholder="直播链接" required>
                            </label>
                            <label>
                                <span>开始时间</span>
                                <input type="datetime-local" name="starts_at">
                            </label>
                            <label>
                                <span>结束时间</span>
                                <input type="datetime-local" name="ends_at">
                            </label>
                            <label>
                                <span>直播说明</span>
                                <textarea name="description" rows="3" placeholder="可选"></textarea>
                            </label>
                            <button type="submit" class="btn btn-primary">创建直播课</button>
                            <p class="form-tip" id="createLiveMessage"></p>
                        </form>
                    </section>
                    <section class="stack">
                        <h2>直播课详情</h2>
                        <div id="liveDetailEmpty" class="empty-state">
                            <p>请选择左侧直播课进行编辑。</p>
                        </div>
                        <form id="liveDetailForm" class="form-grid" hidden>
                            <input type="hidden" id="liveDetailId">
                            <label>
                                <span>关联课程</span>
                                <select id="liveDetailCourse" required></select>
                            </label>
                            <label>
                                <span>直播标题</span>
                                <input type="text" id="liveDetailTitle" required>
                            </label>
                            <label>
                                <span>直播地址</span>
                                <input type="text" id="liveDetailUrl" required>
                            </label>
                            <label>
                                <span>开始时间</span>
                                <input type="datetime-local" id="liveDetailStarts">
                            </label>
                            <label>
                                <span>结束时间</span>
                                <input type="datetime-local" id="liveDetailEnds">
                            </label>
                            <label>
                                <span>直播说明</span>
                                <textarea id="liveDetailDescription" rows="3"></textarea>
                            </label>
                            <div class="form-actions">
                                <button type="submit" class="btn btn-primary">保存修改</button>
                                <button type="button" class="btn btn-danger" id="deleteLiveBtn">删除直播课</button>
                            </div>
                            <p class="form-tip" id="liveDetailMessage"></p>
                        </form>
                    </section>
                </div>
            </div>
        </section>
    </main>
</div>

<script>
(() => {
    const state = {
        user: null,
        users: [],
        courses: [],
        lessonsByCourse: new Map(),
        assignmentsByUser: new Map(),
        liveSessions: [],
        selectedUserId: null,
        selectedCourseId: null,
        selectedLessonId: null,
        selectedLiveId: null
    };

    const currentAdminEl = document.getElementById('currentAdmin');
    const logoutBtn = document.getElementById('logoutBtn');
    const sidebarButtons = document.querySelectorAll('.sidebar-btn');

    const usersListEl = document.getElementById('usersList');
    const usersEmptyEl = document.getElementById('usersEmpty');
    const createUserForm = document.getElementById('createUserForm');
    const createUserMessage = document.getElementById('createUserMessage');
    const userDetailForm = document.getElementById('userDetailForm');
    const userDetailEmpty = document.getElementById('userDetailEmpty');
    const userDetailMessage = document.getElementById('userDetailMessage');
    const deleteUserBtn = document.getElementById('deleteUserBtn');
    const userDetailIdInput = document.getElementById('userDetailId');
    const userDetailUsernameInput = document.getElementById('userDetailUsername');
    const userDetailDisplayNameInput = document.getElementById('userDetailDisplayName');
    const userDetailRoleSelect = document.getElementById('userDetailRole');
    const userDetailPasswordInput = document.getElementById('userDetailPassword');
    const assignmentsWrap = document.getElementById('userAssignments');
    const assignmentSelect = document.getElementById('assignmentCourseSelect');
    const assignCourseBtn = document.getElementById('assignCourseBtn');
    const assignmentListEl = document.getElementById('assignmentList');

    const coursesListEl = document.getElementById('coursesList');
    const coursesEmptyEl = document.getElementById('coursesEmpty');
    const createCourseForm = document.getElementById('createCourseForm');
    const createCourseMessage = document.getElementById('createCourseMessage');
    const courseDetailForm = document.getElementById('courseDetailForm');
    const courseDetailEmpty = document.getElementById('courseDetailEmpty');
    const courseDetailMessage = document.getElementById('courseDetailMessage');
    const courseDetailId = document.getElementById('courseDetailId');
    const courseDetailTitle = document.getElementById('courseDetailTitle');
    const courseDetailDescription = document.getElementById('courseDetailDescription');
    const deleteCourseBtn = document.getElementById('deleteCourseBtn');
    const lessonManager = document.getElementById('lessonManager');
    const createLessonForm = document.getElementById('createLessonForm');
    const createLessonMessage = document.getElementById('createLessonMessage');
    const newLessonCourseId = document.getElementById('newLessonCourseId');
    const lessonsListEl = document.getElementById('lessonsList');
    const lessonsEmptyEl = document.getElementById('lessonsEmpty');
    const editLessonForm = document.getElementById('editLessonForm');
    const editLessonIdInput = document.getElementById('editLessonId');
    const editLessonTitleInput = document.getElementById('editLessonTitle');
    const editLessonVideoInput = document.getElementById('editLessonVideo');
    const editLessonMessage = document.getElementById('editLessonMessage');
    const deleteLessonBtn = document.getElementById('deleteLessonBtn');

    const liveListEl = document.getElementById('liveList');
    const liveEmptyEl = document.getElementById('liveEmpty');
    const createLiveForm = document.getElementById('createLiveForm');
    const createLiveMessage = document.getElementById('createLiveMessage');
    const createLiveCourseSelect = document.getElementById('createLiveCourse');
    const liveDetailEmpty = document.getElementById('liveDetailEmpty');
    const liveDetailForm = document.getElementById('liveDetailForm');
    const liveDetailMessage = document.getElementById('liveDetailMessage');
    const liveDetailId = document.getElementById('liveDetailId');
    const liveDetailCourse = document.getElementById('liveDetailCourse');
    const liveDetailTitle = document.getElementById('liveDetailTitle');
    const liveDetailUrl = document.getElementById('liveDetailUrl');
    const liveDetailStarts = document.getElementById('liveDetailStarts');
    const liveDetailEnds = document.getElementById('liveDetailEnds');
    const liveDetailDescription = document.getElementById('liveDetailDescription');
    const deleteLiveBtn = document.getElementById('deleteLiveBtn');

    async function apiRequest(url, options) {
        const response = await fetch(url, options);
        if (!response.ok) {
            const data = await response.json().catch(() => ({ error: '请求失败' }));
            throw new Error(data.error || '请求失败');
        }
        return response.json();
    }

    function switchPanel(targetId) {
        document.querySelectorAll('.admin-panel').forEach(panel => {
            panel.classList.toggle('is-active', panel.id === targetId);
        });
    }

    sidebarButtons.forEach(button => {
        button.addEventListener('click', () => {
            if (button.classList.contains('is-active')) return;
            sidebarButtons.forEach(btn => btn.classList.remove('is-active'));
            button.classList.add('is-active');
            switchPanel(button.dataset.target);
            if (button.dataset.target === 'livePanel') {
                loadLiveSessions();
            }
        });
    });

    function updateAdminInfo() {
        if (!state.user) {
            currentAdminEl.textContent = '未登录';
            return;
        }
        currentAdminEl.textContent = state.user.display_name ? `${state.user.display_name} (${state.user.username})` : state.user.username;
    }

    function renderUsers() {
        usersListEl.innerHTML = '';
        if (!state.users.length) {
            usersEmptyEl.hidden = false;
            return;
        }
        usersEmptyEl.hidden = true;
        state.users.forEach(user => {
            const item = document.createElement('li');
            item.className = 'list-item';
            if (user.id === state.selectedUserId) {
                item.classList.add('is-active');
            }
            item.innerHTML = `
                <button type="button">
                    <strong>${user.display_name || user.username}</strong>
                    <span>${user.role === 'admin' ? '管理员' : '学员'} · ${user.username}</span>
                </button>
            `;
            item.addEventListener('click', () => selectUser(user.id));
            usersListEl.appendChild(item);
        });
    }

    function renderAssignments(userId) {
        const assignments = state.assignmentsByUser.get(userId) || [];
        assignmentListEl.innerHTML = '';
        if (!assignments.length) {
            assignmentListEl.innerHTML = '<li class="chip">尚未分配课程</li>';
            return;
        }
        assignments.forEach(item => {
            const li = document.createElement('li');
            li.className = 'chip';
            li.innerHTML = `
                <span>${item.course_title}</span>
                <button type="button" aria-label="移除课程">×</button>
            `;
            li.querySelector('button').addEventListener('click', () => removeAssignment(userId, item.course_id));
            assignmentListEl.appendChild(li);
        });
    }

    async function loadAssignments(userId) {
        try {
            const data = await apiRequest(`api/course_assignments.php?user_id=${userId}`);
            const assignments = Array.isArray(data.assignments) ? data.assignments.map(item => ({
                course_id: Number(item.course_id),
                course_title: item.course_title || '',
                course_description: item.course_description || ''
            })) : [];
            state.assignmentsByUser.set(userId, assignments);
            renderAssignments(userId);
        } catch (err) {
            assignmentListEl.innerHTML = `<li class="chip chip-error">加载失败：${err.message}</li>`;
        }
    }

    function populateCourseOptions(selectEl) {
        if (!selectEl) return;
        selectEl.innerHTML = '';
        state.courses.forEach(course => {
            const option = document.createElement('option');
            option.value = course.id;
            option.textContent = course.title;
            selectEl.appendChild(option);
        });
    }

    async function selectUser(userId) {
        state.selectedUserId = userId;
        renderUsers();
        const user = state.users.find(item => item.id === userId);
        if (!user) {
            userDetailForm.hidden = true;
            userDetailEmpty.hidden = false;
            assignmentsWrap.hidden = true;
            return;
        }
        userDetailForm.hidden = false;
        userDetailEmpty.hidden = true;
        assignmentsWrap.hidden = false;
        userDetailIdInput.value = user.id;
        userDetailUsernameInput.value = user.username;
        userDetailDisplayNameInput.value = user.display_name || '';
        userDetailRoleSelect.value = user.role === 'admin' ? 'admin' : 'student';
        userDetailPasswordInput.value = '';
        userDetailMessage.textContent = '';
        populateCourseOptions(assignmentSelect);
        loadAssignments(user.id);
    }

    async function loadUsers() {
        try {
            const data = await apiRequest('api/users.php');
            const users = Array.isArray(data.users) ? data.users.map(item => ({
                id: Number(item.id),
                username: item.username,
                display_name: item.display_name || '',
                role: item.role || 'student'
            })) : [];
            state.users = users;
            renderUsers();
            if (users.length) {
                selectUser(users[0].id);
            }
        } catch (err) {
            usersListEl.innerHTML = `<li class="list-item error">用户加载失败：${err.message}</li>`;
            usersEmptyEl.hidden = true;
        }
    }

    async function createUser(payload) {
        createUserMessage.textContent = '保存中…';
        try {
            await apiRequest('api/users.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(payload)
            });
            createUserMessage.textContent = '创建成功';
            createUserForm.reset();
            await loadUsers();
        } catch (err) {
            createUserMessage.textContent = `创建失败：${err.message}`;
        }
    }

    async function updateUser(payload) {
        userDetailMessage.textContent = '保存中…';
        try {
            await apiRequest('api/users.php', {
                method: 'PATCH',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(payload)
            });
            userDetailMessage.textContent = '已保存';
            await loadUsers();
            selectUser(payload.id);
        } catch (err) {
            userDetailMessage.textContent = `保存失败：${err.message}`;
        }
    }

    async function deleteUser(userId) {
        if (!window.confirm('确定要删除该用户吗？此操作不可恢复。')) {
            return;
        }
        try {
            await apiRequest('api/users.php', {
                method: 'DELETE',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ id: userId })
            });
            state.selectedUserId = null;
            userDetailForm.hidden = true;
            assignmentsWrap.hidden = true;
            userDetailEmpty.hidden = false;
            await loadUsers();
        } catch (err) {
            alert(`删除失败：${err.message}`);
        }
    }

    async function addAssignment(userId, courseId) {
        try {
            await apiRequest('api/course_assignments.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ user_id: userId, course_id: courseId })
            });
            await loadAssignments(userId);
        } catch (err) {
            alert(`分配课程失败：${err.message}`);
        }
    }

    async function removeAssignment(userId, courseId) {
        try {
            await apiRequest('api/course_assignments.php', {
                method: 'DELETE',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ user_id: userId, course_id: courseId })
            });
            await loadAssignments(userId);
        } catch (err) {
            alert(`移除课程失败：${err.message}`);
        }
    }

    function renderCourses() {
        coursesListEl.innerHTML = '';
        if (!state.courses.length) {
            coursesEmptyEl.hidden = false;
            populateCourseOptions(createLiveCourseSelect);
            populateCourseOptions(liveDetailCourse);
            return;
        }
        coursesEmptyEl.hidden = true;
        state.courses.forEach(course => {
            const item = document.createElement('li');
            item.className = 'list-item';
            if (course.id === state.selectedCourseId) {
                item.classList.add('is-active');
            }
            item.innerHTML = `
                <button type="button">
                    <strong>${course.title}</strong>
                    <span>${course.description || '暂无简介'}</span>
                </button>
            `;
            item.addEventListener('click', () => selectCourse(course.id));
            coursesListEl.appendChild(item);
        });
        populateCourseOptions(createLiveCourseSelect);
        populateCourseOptions(liveDetailCourse);
    }

    async function loadCourses() {
        try {
            const data = await apiRequest('api/courses.php?all=1');
            const courses = Array.isArray(data.courses) ? data.courses.map(item => ({
                id: Number(item.id),
                title: item.title,
                description: item.description || ''
            })) : [];
            state.courses = courses;
            state.lessonsByCourse.clear();
            renderCourses();
            if (courses.length) {
                selectCourse(courses[0].id);
            }
            if (state.selectedUserId) {
                await loadAssignments(state.selectedUserId);
            }
        } catch (err) {
            coursesListEl.innerHTML = `<li class="list-item error">课程加载失败：${err.message}</li>`;
            coursesEmptyEl.hidden = true;
        }
    }

    async function createCourse(payload) {
        createCourseMessage.textContent = '保存中…';
        try {
            await apiRequest('api/courses.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(payload)
            });
            createCourseMessage.textContent = '创建成功';
            createCourseForm.reset();
            await loadCourses();
        } catch (err) {
            createCourseMessage.textContent = `创建失败：${err.message}`;
        }
    }

    async function updateCourse(courseId, payload) {
        courseDetailMessage.textContent = '保存中…';
        try {
            await apiRequest('api/courses.php', {
                method: 'PATCH',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ id: courseId, ...payload })
            });
            courseDetailMessage.textContent = '已保存';
            await loadCourses();
            selectCourse(courseId);
        } catch (err) {
            courseDetailMessage.textContent = `保存失败：${err.message}`;
        }
    }

    async function deleteCourse(courseId) {
        if (!window.confirm('确定要删除该课程及其课节吗？')) {
            return;
        }
        try {
            await apiRequest('api/courses.php', {
                method: 'DELETE',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ id: courseId })
            });
            state.selectedCourseId = null;
            courseDetailForm.hidden = true;
            lessonManager.hidden = true;
            courseDetailEmpty.hidden = false;
            await loadCourses();
        } catch (err) {
            alert(`删除失败：${err.message}`);
        }
    }

    function renderLessons(courseId) {
        lessonsListEl.innerHTML = '';
        const lessons = state.lessonsByCourse.get(courseId) || [];
        if (!lessons.length) {
            lessonsEmptyEl.hidden = false;
            editLessonForm.hidden = true;
            return;
        }
        lessonsEmptyEl.hidden = true;
        lessons.forEach(lesson => {
            const item = document.createElement('li');
            item.className = 'list-item';
            if (lesson.id === state.selectedLessonId) {
                item.classList.add('is-active');
            }
            item.innerHTML = `
                <button type="button">
                    <strong>${lesson.title}</strong>
                    <span>${lesson.video_url ? lesson.video_url : '未设置视频'}</span>
                </button>
            `;
            item.addEventListener('click', () => selectLesson(courseId, lesson.id));
            lessonsListEl.appendChild(item);
        });
    }

    async function loadLessons(courseId) {
        try {
            const data = await apiRequest(`api/lessons.php?course_id=${courseId}`);
            const lessons = Array.isArray(data.lessons) ? data.lessons.map(item => ({
                id: Number(item.id),
                course_id: Number(item.course_id),
                title: item.title,
                video_url: item.video_url || ''
            })) : [];
            state.lessonsByCourse.set(courseId, lessons);
            renderLessons(courseId);
        } catch (err) {
            lessonsListEl.innerHTML = `<li class="list-item error">课节加载失败：${err.message}</li>`;
        }
    }

    async function selectCourse(courseId) {
        state.selectedCourseId = courseId;
        renderCourses();
        const course = state.courses.find(item => item.id === courseId);
        if (!course) {
            courseDetailForm.hidden = true;
            lessonManager.hidden = true;
            courseDetailEmpty.hidden = false;
            return;
        }
        courseDetailForm.hidden = false;
        lessonManager.hidden = false;
        courseDetailEmpty.hidden = true;
        courseDetailId.value = course.id;
        courseDetailTitle.value = course.title;
        courseDetailDescription.value = course.description || '';
        courseDetailMessage.textContent = '';
        createLessonMessage.textContent = '';
        editLessonMessage.textContent = '';
        newLessonCourseId.value = course.id;
        await loadLessons(courseId);
        editLessonForm.hidden = true;
        state.selectedLessonId = null;
    }

    async function createLesson(payload) {
        createLessonMessage.textContent = '保存中…';
        try {
            await apiRequest('api/lessons.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(payload)
            });
            createLessonMessage.textContent = '已添加';
            createLessonForm.reset();
            newLessonCourseId.value = state.selectedCourseId;
            await loadLessons(state.selectedCourseId);
        } catch (err) {
            createLessonMessage.textContent = `添加失败：${err.message}`;
        }
    }

    function selectLesson(courseId, lessonId) {
        state.selectedLessonId = lessonId;
        renderLessons(courseId);
        const lessons = state.lessonsByCourse.get(courseId) || [];
        const lesson = lessons.find(item => item.id === lessonId);
        if (!lesson) {
            editLessonForm.hidden = true;
            return;
        }
        editLessonForm.hidden = false;
        editLessonIdInput.value = lesson.id;
        editLessonTitleInput.value = lesson.title;
        editLessonVideoInput.value = lesson.video_url || '';
        editLessonMessage.textContent = '';
    }

    async function updateLesson(lessonId, payload) {
        editLessonMessage.textContent = '保存中…';
        try {
            await apiRequest('api/lessons.php', {
                method: 'PATCH',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ id: lessonId, ...payload })
            });
            editLessonMessage.textContent = '已保存';
            await loadLessons(state.selectedCourseId);
            selectLesson(state.selectedCourseId, lessonId);
        } catch (err) {
            editLessonMessage.textContent = `保存失败：${err.message}`;
        }
    }

    async function deleteLesson(lessonId) {
        if (!window.confirm('确定要删除该课节吗？')) {
            return;
        }
        try {
            await apiRequest('api/lessons.php', {
                method: 'DELETE',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ lesson_id: lessonId })
            });
            editLessonForm.hidden = true;
            state.selectedLessonId = null;
            await loadLessons(state.selectedCourseId);
        } catch (err) {
            alert(`删除失败：${err.message}`);
        }
    }

    function renderLiveSessions() {
        liveListEl.innerHTML = '';
        if (!state.liveSessions.length) {
            liveEmptyEl.hidden = false;
            return;
        }
        liveEmptyEl.hidden = true;
        state.liveSessions.forEach(session => {
            const item = document.createElement('li');
            item.className = 'list-item';
            if (session.id === state.selectedLiveId) {
                item.classList.add('is-active');
            }
            item.innerHTML = `
                <button type="button">
                    <strong>${session.title}</strong>
                    <span>${session.course_title || '未关联课程'}</span>
                </button>
            `;
            item.addEventListener('click', () => selectLive(session.id));
            liveListEl.appendChild(item);
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
            populateCourseOptions(createLiveCourseSelect);
            populateCourseOptions(liveDetailCourse);
            if (sessions.length && !state.selectedLiveId) {
                selectLive(sessions[0].id);
            }
        } catch (err) {
            liveListEl.innerHTML = `<li class="list-item error">直播课加载失败：${err.message}</li>`;
            liveEmptyEl.hidden = true;
        }
    }

    function toDateTimeLocalValue(value) {
        if (!value) return '';
        const date = new Date(value.replace(' ', 'T'));
        if (Number.isNaN(date.getTime())) {
            return '';
        }
        const year = date.getFullYear();
        const month = String(date.getMonth() + 1).padStart(2, '0');
        const day = String(date.getDate()).padStart(2, '0');
        const hour = String(date.getHours()).padStart(2, '0');
        const minute = String(date.getMinutes()).padStart(2, '0');
        return `${year}-${month}-${day}T${hour}:${minute}`;
    }

    function selectLive(sessionId) {
        state.selectedLiveId = sessionId;
        renderLiveSessions();
        const session = state.liveSessions.find(item => item.id === sessionId);
        if (!session) {
            liveDetailForm.hidden = true;
            liveDetailEmpty.hidden = false;
            return;
        }
        liveDetailForm.hidden = false;
        liveDetailEmpty.hidden = true;
        liveDetailId.value = session.id;
        liveDetailCourse.value = session.course_id;
        liveDetailTitle.value = session.title;
        liveDetailUrl.value = session.stream_url;
        liveDetailStarts.value = toDateTimeLocalValue(session.starts_at);
        liveDetailEnds.value = toDateTimeLocalValue(session.ends_at);
        liveDetailDescription.value = session.description || '';
        liveDetailMessage.textContent = '';
    }

    async function createLive(payload) {
        createLiveMessage.textContent = '保存中…';
        try {
            await apiRequest('api/live_sessions.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(payload)
            });
            createLiveMessage.textContent = '创建成功';
            createLiveForm.reset();
            await loadLiveSessions();
        } catch (err) {
            createLiveMessage.textContent = `创建失败：${err.message}`;
        }
    }

    async function updateLive(sessionId, payload) {
        liveDetailMessage.textContent = '保存中…';
        try {
            await apiRequest('api/live_sessions.php', {
                method: 'PATCH',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ id: sessionId, ...payload })
            });
            liveDetailMessage.textContent = '已保存';
            await loadLiveSessions();
            selectLive(sessionId);
        } catch (err) {
            liveDetailMessage.textContent = `保存失败：${err.message}`;
        }
    }

    async function deleteLive(sessionId) {
        if (!window.confirm('确定要删除该直播课吗？')) {
            return;
        }
        try {
            await apiRequest('api/live_sessions.php', {
                method: 'DELETE',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ session_id: sessionId })
            });
            state.selectedLiveId = null;
            liveDetailForm.hidden = true;
            liveDetailEmpty.hidden = false;
            await loadLiveSessions();
        } catch (err) {
            alert(`删除失败：${err.message}`);
        }
    }

    createUserForm.addEventListener('submit', event => {
        event.preventDefault();
        const formData = new FormData(createUserForm);
        createUser({
            username: formData.get('username').trim(),
            display_name: (formData.get('display_name') || '').trim(),
            password: formData.get('password'),
            role: formData.get('role')
        });
    });

    userDetailForm.addEventListener('submit', event => {
        event.preventDefault();
        const payload = {
            id: Number(userDetailIdInput.value),
            username: userDetailUsernameInput.value.trim(),
            display_name: userDetailDisplayNameInput.value.trim(),
            role: userDetailRoleSelect.value
        };
        const password = userDetailPasswordInput.value;
        if (password) {
            payload.password = password;
        }
        updateUser(payload);
    });

    deleteUserBtn.addEventListener('click', () => {
        if (state.selectedUserId) {
            deleteUser(state.selectedUserId);
        }
    });

    assignCourseBtn.addEventListener('click', () => {
        const courseId = Number(assignmentSelect.value);
        if (!state.selectedUserId || !courseId) {
            return;
        }
        addAssignment(state.selectedUserId, courseId);
    });

    createCourseForm.addEventListener('submit', event => {
        event.preventDefault();
        const formData = new FormData(createCourseForm);
        createCourse({
            title: formData.get('title').trim(),
            description: (formData.get('description') || '').trim()
        });
    });

    courseDetailForm.addEventListener('submit', event => {
        event.preventDefault();
        const courseId = Number(courseDetailId.value);
        updateCourse(courseId, {
            title: courseDetailTitle.value.trim(),
            description: courseDetailDescription.value.trim()
        });
    });

    deleteCourseBtn.addEventListener('click', () => {
        if (state.selectedCourseId) {
            deleteCourse(state.selectedCourseId);
        }
    });

    createLessonForm.addEventListener('submit', event => {
        event.preventDefault();
        if (!state.selectedCourseId) return;
        const formData = new FormData(createLessonForm);
        createLesson({
            course_id: state.selectedCourseId,
            title: formData.get('title').trim(),
            video_url: (formData.get('video_url') || '').trim()
        });
    });

    editLessonForm.addEventListener('submit', event => {
        event.preventDefault();
        const lessonId = Number(editLessonIdInput.value);
        if (!lessonId) return;
        updateLesson(lessonId, {
            course_id: state.selectedCourseId,
            title: editLessonTitleInput.value.trim(),
            video_url: editLessonVideoInput.value.trim()
        });
    });

    deleteLessonBtn.addEventListener('click', () => {
        const lessonId = Number(editLessonIdInput.value);
        if (lessonId) {
            deleteLesson(lessonId);
        }
    });

    createLiveForm.addEventListener('submit', event => {
        event.preventDefault();
        const formData = new FormData(createLiveForm);
        createLive({
            course_id: Number(formData.get('course_id')),
            title: formData.get('title').trim(),
            stream_url: formData.get('stream_url').trim(),
            starts_at: formData.get('starts_at'),
            ends_at: formData.get('ends_at'),
            description: (formData.get('description') || '').trim()
        });
    });

    liveDetailForm.addEventListener('submit', event => {
        event.preventDefault();
        const sessionId = Number(liveDetailId.value);
        if (!sessionId) return;
        updateLive(sessionId, {
            course_id: Number(liveDetailCourse.value),
            title: liveDetailTitle.value.trim(),
            stream_url: liveDetailUrl.value.trim(),
            starts_at: liveDetailStarts.value,
            ends_at: liveDetailEnds.value,
            description: liveDetailDescription.value.trim()
        });
    });

    deleteLiveBtn.addEventListener('click', () => {
        if (state.selectedLiveId) {
            deleteLive(state.selectedLiveId);
        }
    });

    logoutBtn.addEventListener('click', async () => {
        try {
            await apiRequest('api/logout.php', { method: 'POST' });
        } catch (err) {
            console.error(err);
        }
        window.location.href = 'index.php';
    });

    async function init() {
        try {
            const session = await apiRequest('api/session.php');
            if (!session.user || session.user.role !== 'admin') {
                window.location.href = 'index.php';
                return;
            }
            state.user = session.user;
        } catch (err) {
            window.location.href = 'index.php';
            return;
        }
        updateAdminInfo();
        await loadCourses();
        await loadUsers();
        await loadLiveSessions();
    }

    init();
})();
</script>
</body>
</html>
