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

        .user-management {
            display: grid;
            gap: 2rem;
        }

        @media (min-width: 1120px) {
            .user-management {
                grid-template-columns: minmax(280px, 1fr) minmax(360px, 1.2fr);
                align-items: flex-start;
            }
        }

        .user-management-primary {
            display: flex;
            flex-direction: column;
            gap: 1.5rem;
        }

        .user-list-card {
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }

        .user-table {
            margin-top: 0.5rem;
        }

        .user-table li {
            border: none;
            border-bottom: none;
            padding: 0.85rem 0.6rem;
            border-radius: var(--radius-md);
            transition: background 0.2s ease, transform 0.2s ease;
            gap: 0.75rem;
        }

        .user-table li + li {
            margin-top: 0.4rem;
        }

        .user-table li:hover {
            background: rgba(79, 70, 229, 0.1);
            transform: translateY(-1px);
        }

        .user-table li.active {
            background: rgba(79, 70, 229, 0.16);
            box-shadow: inset 0 0 0 1px rgba(79, 70, 229, 0.2);
        }

        .user-table li .user-meta {
            display: flex;
            flex-direction: column;
            gap: 0.25rem;
        }

        .user-table li .user-meta span {
            font-size: 0.8rem;
            color: var(--text-secondary);
        }

        .user-role-tag {
            padding: 0.35rem 0.75rem;
            border-radius: 999px;
            background: rgba(148, 163, 184, 0.16);
            color: var(--text-secondary);
            font-size: 0.75rem;
            font-weight: 600;
            white-space: nowrap;
        }

        .user-role-tag.is-admin {
            background: rgba(79, 70, 229, 0.18);
            color: var(--brand-color-strong);
        }

        .user-detail-card {
            display: flex;
            flex-direction: column;
            gap: 1.35rem;
        }

        .user-detail-header {
            display: flex;
            justify-content: space-between;
            gap: 1rem;
            align-items: flex-start;
        }

        .user-detail-header h3 {
            margin: 0;
            font-size: 1.3rem;
            letter-spacing: -0.01em;
        }

        .user-detail-header p {
            margin: 0.35rem 0 0;
            color: var(--text-secondary);
            line-height: 1.6;
        }

        .user-detail-chip {
            background: rgba(148, 163, 184, 0.16);
            color: var(--text-secondary);
        }

        .user-detail-chip.is-admin {
            background: rgba(79, 70, 229, 0.18);
            color: var(--brand-color-strong);
        }

        .user-detail-empty {
            padding: 1.2rem 1.4rem;
            border-radius: var(--radius-md);
            background: rgba(148, 163, 184, 0.1);
            color: var(--text-secondary);
            line-height: 1.6;
        }

        .password-inline {
            display: flex;
            gap: 0.75rem;
            align-items: center;
        }

        .password-inline input {
            flex: 1;
        }

        .password-inline button {
            flex-shrink: 0;
        }

        .danger-zone {
            border-top: 1px solid rgba(148, 163, 184, 0.16);
            padding-top: 1.2rem;
            display: flex;
            flex-direction: column;
            gap: 0.75rem;
        }

        .danger-zone strong {
            font-size: 1rem;
        }

        .danger-zone p {
            margin: 0.4rem 0 0;
            color: var(--text-secondary);
            font-size: 0.9rem;
            line-height: 1.5;
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
            <div class="user-management" style="margin-top:2rem;">
                <div class="user-management-primary">
                    <div class="card list-card user-list-card">
                        <div class="panel-header">
                            <h3>现有用户</h3>
                            <p class="hint">点击用户即可查看详情、修改信息或重置密码。</p>
                        </div>
                        <ul class="table-list user-table" id="userList"></ul>
                    </div>
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
                </div>
                <section class="card surface-section user-detail-card" id="userDetailCard">
                    <div class="user-detail-header">
                        <div>
                            <h3 id="userDetailTitle">用户详情</h3>
                            <p id="userDetailSubtitle">请选择左侧的用户进行管理。</p>
                        </div>
                        <span class="chip subtle user-detail-chip" id="userDetailRoleChip" hidden></span>
                    </div>
                    <div class="user-detail-empty" id="userDetailEmpty">没有选中的用户，点击左侧列表中的用户即可开始编辑。</div>
                    <form id="updateUserForm" class="form-grid" hidden>
                        <div>
                            <label for="editUsername">用户名</label>
                            <input id="editUsername" required>
                        </div>
                        <div>
                            <label for="editDisplayName">显示名称</label>
                            <input id="editDisplayName" placeholder="学生姓名或昵称">
                        </div>
                        <div>
                            <label for="editRole">角色</label>
                            <select id="editRole">
                                <option value="student">学员</option>
                                <option value="admin">管理员</option>
                            </select>
                        </div>
                        <div>
                            <label for="editPassword">重置密码</label>
                            <div class="password-inline">
                                <input id="editPassword" type="password" placeholder="填写新密码，留空则不修改">
                                <button type="button" class="ghost-button" id="resetPasswordButton">生成临时密码</button>
                            </div>
                            <p class="hint" style="margin-top:0.5rem;">生成临时密码会立即生效，并在下方显示结果。</p>
                        </div>
                        <button type="submit" class="primary-button">保存修改</button>
                        <div class="message inline" id="updateUserMessage" hidden></div>
                    </form>
                    <div class="danger-zone" id="userDangerZone" hidden>
                        <div>
                            <strong>危险操作</strong>
                            <p>删除用户将一并移除其课程分配，且无法撤销。</p>
                        </div>
                        <button type="button" class="inline-button danger" id="deleteUserButton">删除用户</button>
                        <div class="message inline" id="deleteUserMessage" hidden></div>
                    </div>
                </section>
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
                <div style="display:flex; flex-direction:column; gap:1.5rem;">
                    <div class="card list-card">
                        <h3>课程列表</h3>
                        <p class="hint">点击课程可编辑信息，删除将同时移除课节与分配记录。</p>
                        <ul class="table-list" id="courseList"></ul>
                        <div class="message inline" id="courseListMessage" hidden></div>
                    </div>
                    <form id="updateCourseForm" class="card surface-section form-grid" style="padding:2rem;" hidden>
                        <h3 style="margin-top:0;">编辑课程</h3>
                        <div>
                            <label for="editCourseTitle">课程名称</label>
                            <input id="editCourseTitle" placeholder="请输入课程标题" required>
                        </div>
                        <div>
                            <label for="editCourseDescription">课程简介</label>
                            <textarea id="editCourseDescription" rows="4" placeholder="填写课程简介"></textarea>
                        </div>
                        <div style="display:flex; gap:0.75rem; flex-wrap:wrap;">
                            <button type="submit" class="primary-button">保存修改</button>
                            <button type="button" class="ghost-button" id="cancelCourseEdit">取消</button>
                        </div>
                        <div class="message inline" id="updateCourseMessage" hidden></div>
                    </form>
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
                        <label for="lessonType">课节类型</label>
                        <select id="lessonType">
                            <option value="recorded">录播课</option>
                            <option value="live">直播课</option>
                        </select>
                    </div>
                    <div data-lesson-field="recorded">
                        <label for="lessonVideo">视频链接</label>
                        <input id="lessonVideo" placeholder="支持本地文件链接或哔哩哔哩地址">
                        <p class="hint">示例：<code>https://example.com/video.mp4</code> 或 <code>https://www.bilibili.com/video/BVxxxx</code></p>
                    </div>
                    <div data-lesson-field="live" hidden>
                        <label for="lessonLiveUrl">直播地址</label>
                        <input id="lessonLiveUrl" placeholder="输入直播间链接或会议加入地址">
                        <p class="hint">可填写腾讯会议、飞书会议或直播平台链接，学员可一键进入。</p>
                    </div>
                    <div data-lesson-field="live" hidden>
                        <label for="lessonLiveStart">直播开始时间</label>
                        <input id="lessonLiveStart" type="datetime-local">
                        <p class="hint">用于显示提醒，选填。示例：<code>2024-05-01T19:30</code></p>
                    </div>
                    <div data-lesson-field="live" hidden>
                        <label for="lessonLiveEnd">直播结束时间</label>
                        <input id="lessonLiveEnd" type="datetime-local">
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
                    <form id="updateLessonForm" class="card surface-section form-grid" style="padding:2rem;" hidden>
                        <h3 style="margin-top:0;">编辑课节</h3>
                        <div>
                            <label for="editLessonCourseSelect">所属课程</label>
                            <select id="editLessonCourseSelect" required></select>
                        </div>
                        <div>
                            <label for="editLessonTitle">课节标题</label>
                            <input id="editLessonTitle" placeholder="请输入课节名称" required>
                        </div>
                        <div>
                            <label for="editLessonType">课节类型</label>
                            <select id="editLessonType">
                                <option value="recorded">录播课</option>
                                <option value="live">直播课</option>
                            </select>
                        </div>
                        <div data-lesson-field="recorded">
                            <label for="editLessonVideo">视频链接</label>
                            <input id="editLessonVideo" placeholder="支持本地文件链接或哔哩哔哩地址">
                        </div>
                        <div data-lesson-field="live" hidden>
                            <label for="editLessonLiveUrl">直播地址</label>
                            <input id="editLessonLiveUrl" placeholder="输入直播间链接或会议加入地址">
                        </div>
                        <div data-lesson-field="live" hidden>
                            <label for="editLessonLiveStart">直播开始时间</label>
                            <input id="editLessonLiveStart" type="datetime-local">
                        </div>
                        <div data-lesson-field="live" hidden>
                            <label for="editLessonLiveEnd">直播结束时间</label>
                            <input id="editLessonLiveEnd" type="datetime-local">
                        </div>
                        <div style="display:flex; gap:0.75rem; flex-wrap:wrap;">
                            <button type="submit" class="primary-button">保存课节</button>
                            <button type="button" class="ghost-button" id="cancelLessonEdit">取消</button>
                        </div>
                        <div class="message inline" id="updateLessonMessage" hidden></div>
                    </form>
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
    const updateUserForm = document.getElementById('updateUserForm');
    const updateUserMessage = document.getElementById('updateUserMessage');
    const editUsernameInput = document.getElementById('editUsername');
    const editDisplayNameInput = document.getElementById('editDisplayName');
    const editRoleSelect = document.getElementById('editRole');
    const editPasswordInput = document.getElementById('editPassword');
    const resetPasswordButton = document.getElementById('resetPasswordButton');
    const deleteUserButton = document.getElementById('deleteUserButton');
    const deleteUserMessage = document.getElementById('deleteUserMessage');
    const userDetailTitle = document.getElementById('userDetailTitle');
    const userDetailSubtitle = document.getElementById('userDetailSubtitle');
    const userDetailRoleChip = document.getElementById('userDetailRoleChip');
    const userDetailEmpty = document.getElementById('userDetailEmpty');
    const userDangerZone = document.getElementById('userDangerZone');

    if (resetPasswordButton) {
        resetPasswordButton.disabled = true;
    }
    if (deleteUserButton) {
        deleteUserButton.disabled = true;
    }
    const createCourseForm = document.getElementById('createCourseForm');
    const createCourseMessage = document.getElementById('createCourseMessage');
    const courseListEl = document.getElementById('courseList');
    const courseListMessage = document.getElementById('courseListMessage');
    const updateCourseForm = document.getElementById('updateCourseForm');
    const updateCourseMessage = document.getElementById('updateCourseMessage');
    const editCourseTitleInput = document.getElementById('editCourseTitle');
    const editCourseDescriptionInput = document.getElementById('editCourseDescription');
    const cancelCourseEditButton = document.getElementById('cancelCourseEdit');

    const createLessonForm = document.getElementById('createLessonForm');
    const createLessonMessage = document.getElementById('createLessonMessage');
    const lessonCourseSelect = document.getElementById('lessonCourseSelect');
    const lessonListEl = document.getElementById('lessonList');
    const lessonListMessage = document.getElementById('lessonListMessage');
    const updateLessonForm = document.getElementById('updateLessonForm');
    const updateLessonMessage = document.getElementById('updateLessonMessage');
    const editLessonCourseSelect = document.getElementById('editLessonCourseSelect');
    const editLessonTitleInput = document.getElementById('editLessonTitle');
    const editLessonVideoInput = document.getElementById('editLessonVideo');
    const lessonTypeSelect = document.getElementById('lessonType');
    const lessonVideoInput = document.getElementById('lessonVideo');
    const lessonLiveUrlInput = document.getElementById('lessonLiveUrl');
    const lessonLiveStartInput = document.getElementById('lessonLiveStart');
    const lessonLiveEndInput = document.getElementById('lessonLiveEnd');
    const editLessonTypeSelect = document.getElementById('editLessonType');
    const editLessonLiveUrlInput = document.getElementById('editLessonLiveUrl');
    const editLessonLiveStartInput = document.getElementById('editLessonLiveStart');
    const editLessonLiveEndInput = document.getElementById('editLessonLiveEnd');
    const createLessonFieldGroups = document.querySelectorAll('#createLessonForm [data-lesson-field]');
    const editLessonFieldGroups = document.querySelectorAll('#updateLessonForm [data-lesson-field]');
    const cancelLessonEditButton = document.getElementById('cancelLessonEdit');

    if (cancelCourseEditButton) {
        cancelCourseEditButton.addEventListener('click', () => {
            clearCourseEditor();
        });
    }
    if (cancelLessonEditButton) {
        cancelLessonEditButton.addEventListener('click', () => {
            const currentCourseId = parseInt(lessonCourseSelect.value, 10);
            clearLessonEditor();
            if (currentCourseId && Array.isArray(state.lessons[currentCourseId])) {
                renderLessons(currentCourseId, state.lessons[currentCourseId]);
            }
        });
    }

    if (lessonTypeSelect) {
        const initialType = lessonTypeSelect.value || 'recorded';
        syncLessonFieldGroups(createLessonFieldGroups, initialType);
        lessonTypeSelect.addEventListener('change', () => {
            const type = lessonTypeSelect.value || 'recorded';
            syncLessonFieldGroups(createLessonFieldGroups, type);
        });
    }

    if (editLessonTypeSelect) {
        const initialEditType = editLessonTypeSelect.value || 'recorded';
        syncLessonFieldGroups(editLessonFieldGroups, initialEditType);
        editLessonTypeSelect.addEventListener('change', () => {
            const type = editLessonTypeSelect.value || 'recorded';
            syncLessonFieldGroups(editLessonFieldGroups, type);
        });
    }

    const assignCourseForm = document.getElementById('assignCourseForm');
    const assignCourseMessage = document.getElementById('assignCourseMessage');
    const assignUserSelect = document.getElementById('assignUserSelect');
    const assignCourseSelect = document.getElementById('assignCourseSelect');
    const assignmentListEl = document.getElementById('assignmentList');

    let state = {
        users: [],
        courses: [],
        lessons: {},
        currentUser: null,
        selectedUserId: null,
        selectedCourseId: null,
        selectedLessonId: null,
        editingLessonOriginalCourseId: null
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
            empty.className = 'text-muted';
            userListEl.appendChild(empty);
            return;
        }
        state.users.forEach((user) => {
            const item = document.createElement('li');
            item.dataset.userId = user.id;
            item.setAttribute('role', 'button');
            item.tabIndex = 0;
            item.classList.add('selectable');
            if (state.selectedUserId === user.id) {
                item.classList.add('active');
            }
            const info = document.createElement('div');
            info.className = 'user-meta';
            const nameEl = document.createElement('strong');
            nameEl.textContent = user.display_name || user.username;
            info.appendChild(nameEl);
            const metaEl = document.createElement('span');
            metaEl.textContent = `用户名：${user.username}`;
            info.appendChild(metaEl);
            item.appendChild(info);
            const roleTag = document.createElement('span');
            roleTag.className = 'user-role-tag' + (user.role === 'admin' ? ' is-admin' : '');
            roleTag.textContent = user.role === 'admin' ? '管理员' : '学员';
            item.appendChild(roleTag);
            userListEl.appendChild(item);
        });
    }

    function selectUser(userId) {
        const numericId = typeof userId === 'number' ? userId : parseInt(userId, 10);
        const target = state.users.find((user) => user.id === numericId) || null;
        state.selectedUserId = target ? target.id : null;

        document.querySelectorAll('#userList li[data-user-id]').forEach((el) => {
            const matchId = parseInt(el.dataset.userId, 10);
            el.classList.toggle('active', !Number.isNaN(matchId) && matchId === state.selectedUserId);
        });

        if (!target) {
            if (assignUserSelect && assignUserSelect.value !== '') {
                assignUserSelect.value = '';
            }
            userDetailTitle.textContent = '用户详情';
            userDetailSubtitle.textContent = state.users.length ? '请选择左侧的用户进行管理。' : '暂无用户，请先创建。';
            userDetailRoleChip.hidden = true;
            userDetailEmpty.hidden = false;
            updateUserForm.hidden = true;
            userDangerZone.hidden = true;
            if (resetPasswordButton) {
                resetPasswordButton.disabled = true;
            }
            if (deleteUserButton) {
                deleteUserButton.disabled = true;
            }
            editUsernameInput.value = '';
            editDisplayNameInput.value = '';
            editRoleSelect.value = 'student';
            editPasswordInput.value = '';
            setMessage(updateUserMessage);
            setMessage(deleteUserMessage);
            renderAssignmentPlaceholder(state.users.length ? '请选择用户查看已分配课程' : '暂无用户，请先创建。');
            return;
        }

        userDetailTitle.textContent = target.display_name || target.username;
        userDetailSubtitle.textContent = `用户名：${target.username}`;
        userDetailRoleChip.hidden = false;
        userDetailRoleChip.textContent = target.role === 'admin' ? '管理员' : '学员';
        userDetailRoleChip.classList.toggle('is-admin', target.role === 'admin');
        userDetailEmpty.hidden = true;
        updateUserForm.hidden = false;
        userDangerZone.hidden = false;
        editUsernameInput.value = target.username;
        editDisplayNameInput.value = target.display_name || '';
        editRoleSelect.value = target.role;
        editPasswordInput.value = '';
        if (resetPasswordButton) {
            resetPasswordButton.disabled = false;
        }
        const isCurrentAdmin = state.currentUser && Number(state.currentUser.id) === target.id;
        if (deleteUserButton) {
            deleteUserButton.disabled = !!isCurrentAdmin;
            deleteUserButton.title = isCurrentAdmin ? '无法删除当前登录的管理员' : '';
        }
        setMessage(updateUserMessage);
        setMessage(deleteUserMessage);
        setMessage(assignCourseMessage);

        if (assignUserSelect) {
            const valueString = String(target.id);
            if (assignUserSelect.value !== valueString) {
                assignUserSelect.value = valueString;
            }
        }

        loadAssignmentsForUser(target.id);
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
            item.dataset.courseId = course.id;
            item.classList.add('selectable');
            item.setAttribute('role', 'button');
            item.tabIndex = 0;
            if (state.selectedCourseId === course.id) {
                item.classList.add('active');
            }

            const info = document.createElement('div');
            info.style.flex = '1';
            const title = document.createElement('strong');
            title.textContent = course.title;
            info.appendChild(title);
            const meta = document.createElement('div');
            meta.className = 'text-muted';
            meta.style.fontSize = '0.85rem';
            const description = summarize(course.description || '', 64);
            meta.textContent = description ? `课程ID：${course.id} · ${description}` : `课程ID：${course.id} · 暂无描述`;
            info.appendChild(meta);
            item.appendChild(info);

            const actions = document.createElement('div');
            actions.className = 'list-actions';
            actions.style.display = 'flex';
            actions.style.gap = '0.5rem';
            actions.style.alignItems = 'center';

            const editButton = document.createElement('button');
            editButton.type = 'button';
            editButton.className = 'inline-button';
            editButton.dataset.courseId = course.id;
            editButton.dataset.courseAction = 'edit';
            editButton.textContent = '编辑';
            actions.appendChild(editButton);

            const deleteButton = document.createElement('button');
            deleteButton.type = 'button';
            deleteButton.className = 'inline-button danger';
            deleteButton.dataset.courseId = course.id;
            deleteButton.dataset.courseAction = 'delete';
            deleteButton.textContent = '删除';
            actions.appendChild(deleteButton);

            item.appendChild(actions);

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

    function clearCourseEditor() {
        state.selectedCourseId = null;
        if (updateCourseForm) {
            updateCourseForm.hidden = true;
            updateCourseForm.reset();
        }
        setMessage(updateCourseMessage);
        refreshCourseList();
    }

    function showCourseEditor(courseId) {
        const target = state.courses.find((course) => course.id === courseId);
        if (!target) {
            setMessage(updateCourseMessage, '未找到课程信息', 'error');
            return;
        }
        state.selectedCourseId = target.id;
        if (updateCourseForm) {
            updateCourseForm.hidden = false;
        }
        if (editCourseTitleInput) {
            editCourseTitleInput.value = target.title || '';
        }
        if (editCourseDescriptionInput) {
            editCourseDescriptionInput.value = target.description || '';
        }
        setMessage(updateCourseMessage);
        refreshCourseList();
    }

    function clearLessonEditor() {
        state.selectedLessonId = null;
        state.editingLessonOriginalCourseId = null;
        if (updateLessonForm) {
            updateLessonForm.hidden = true;
            updateLessonForm.reset();
        }
        if (editLessonTypeSelect) {
            editLessonTypeSelect.value = 'recorded';
            syncLessonFieldGroups(editLessonFieldGroups, 'recorded');
        }
        setMessage(updateLessonMessage);
    }

    function showLessonEditor(courseId, lessonId) {
        const lessons = state.lessons[courseId] || [];
        const target = lessons.find((lesson) => lesson.id === lessonId);
        if (!target) {
            setMessage(updateLessonMessage, '未找到课节信息', 'error');
            return;
        }
        state.selectedLessonId = target.id;
        state.editingLessonOriginalCourseId = courseId;
        if (updateLessonForm) {
            updateLessonForm.hidden = false;
        }
        if (editLessonCourseSelect) {
            populateSelect(editLessonCourseSelect, state.courses, 'id', 'title', courseId);
        }
        if (lessonCourseSelect && lessonCourseSelect.value !== String(courseId)) {
            lessonCourseSelect.value = String(courseId);
        }
        if (editLessonTitleInput) {
            editLessonTitleInput.value = target.title || '';
        }
        if (editLessonVideoInput) {
            editLessonVideoInput.value = target.video_url || '';
        }
        if (editLessonTypeSelect) {
            editLessonTypeSelect.value = target.type === 'live' ? 'live' : 'recorded';
            syncLessonFieldGroups(editLessonFieldGroups, editLessonTypeSelect.value);
        }
        if (editLessonLiveUrlInput) {
            editLessonLiveUrlInput.value = target.live_url || '';
        }
        if (editLessonLiveStartInput) {
            editLessonLiveStartInput.value = toDateTimeLocalValue(target.live_start_at || '');
        }
        if (editLessonLiveEndInput) {
            editLessonLiveEndInput.value = toDateTimeLocalValue(target.live_end_at || '');
        }
        setMessage(updateLessonMessage);
        renderLessons(courseId, lessons);
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

    function formatDateTimeDisplay(value) {
        if (!value) {
            return '';
        }
        const normalized = String(value).replace(' ', 'T');
        const date = new Date(normalized);
        if (Number.isNaN(date.getTime())) {
            return '';
        }
        return date.toLocaleString('zh-CN', { hour12: false });
    }

    function toDateTimeLocalValue(value) {
        if (!value) {
            return '';
        }
        const normalized = String(value).replace(' ', 'T');
        const date = new Date(normalized);
        if (Number.isNaN(date.getTime())) {
            return '';
        }
        const year = date.getFullYear();
        const month = String(date.getMonth() + 1).padStart(2, '0');
        const day = String(date.getDate()).padStart(2, '0');
        const hours = String(date.getHours()).padStart(2, '0');
        const minutes = String(date.getMinutes()).padStart(2, '0');
        return `${year}-${month}-${day}T${hours}:${minutes}`;
    }

    function syncLessonFieldGroups(nodes, activeType) {
        if (!nodes) {
            return;
        }
        nodes.forEach((node) => {
            const role = node.getAttribute('data-lesson-field');
            if (!role) {
                return;
            }
            const shouldShow = role === activeType;
            node.hidden = !shouldShow;
        });
    }

    function generateTemporaryPassword(length = 10) {
        const pool = 'ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnopqrstuvwxyz23456789';
        let result = '';
        for (let index = 0; index < length; index += 1) {
            const random = Math.floor(Math.random() * pool.length);
            result += pool.charAt(random);
        }
        return result;
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
        if (state.editingLessonOriginalCourseId === courseId) {
            const exists = lessons.some((lesson) => lesson.id === state.selectedLessonId);
            if (!exists) {
                clearLessonEditor();
            }
        }
        lessons.forEach((lesson, index) => {
            const item = document.createElement('li');
            item.dataset.lessonId = lesson.id;
            item.dataset.courseId = courseId;
            item.classList.add('selectable');
            item.setAttribute('role', 'button');
            item.tabIndex = 0;
            if (state.selectedLessonId === lesson.id) {
                item.classList.add('active');
            }
            const lessonType = lesson.type === 'live' ? 'live' : 'recorded';
            if (lessonType === 'live') {
                item.classList.add('lesson-live');
            }

            const info = document.createElement('div');
            info.style.flex = '1';
            const title = document.createElement('strong');
            title.textContent = `${index + 1}. ${lesson.title}`;
            info.appendChild(title);
            const meta = document.createElement('div');
            meta.className = 'text-muted';
            meta.style.fontSize = '0.85rem';
            const metaParts = [`课节ID：${lesson.id}`, lessonType === 'live' ? '直播课' : '录播课'];
            if (lessonType === 'live') {
                const startText = formatDateTimeDisplay(lesson.live_start_at);
                if (startText) {
                    metaParts.push(`开始 ${startText}`);
                }
            } else {
                const videoText = summarize(lesson.video_url || '', 70);
                if (videoText) {
                    metaParts.push(videoText);
                }
            }
            meta.textContent = metaParts.join(' · ');
            info.appendChild(meta);
            item.appendChild(info);

            const actions = document.createElement('div');
            actions.className = 'list-actions';
            actions.style.display = 'flex';
            actions.style.gap = '0.5rem';
            actions.style.alignItems = 'center';

            const editButton = document.createElement('button');
            editButton.type = 'button';
            editButton.className = 'inline-button';
            editButton.dataset.lessonId = lesson.id;
            editButton.dataset.courseId = courseId;
            editButton.dataset.lessonAction = 'edit';
            editButton.textContent = '编辑';
            actions.appendChild(editButton);

            const deleteButton = document.createElement('button');
            deleteButton.type = 'button';
            deleteButton.className = 'inline-button danger';
            deleteButton.dataset.lessonId = lesson.id;
            deleteButton.dataset.courseId = courseId;
            deleteButton.dataset.lessonAction = 'delete';
            deleteButton.textContent = '删除';
            actions.appendChild(deleteButton);

            item.appendChild(actions);

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
                id: Number(lesson.id),
                course_id: Number(courseId),
                type: lesson.type === 'live' ? 'live' : 'recorded',
                live_url: lesson.live_url || '',
                live_start_at: lesson.live_start_at || null,
                live_end_at: lesson.live_end_at || null,
                video_url: lesson.video_url || ''
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
            state.users = (usersData.users || []).map((user) => ({
                ...user,
                id: Number(user.id)
            }));
            state.users.sort((a, b) => a.id - b.id);
            state.courses = coursesData.courses || [];
            state.selectedUserId = state.users.length ? state.users[0].id : null;
            refreshUserList();
            refreshCourseList();
            const selectedUserOption = populateSelect(
                assignUserSelect,
                state.users,
                'id',
                (user) => (user.display_name ? `${user.display_name}（${user.username}）` : user.username),
                state.selectedUserId
            );
            populateSelect(assignCourseSelect, state.courses, 'id', 'title');
            const selectedLessonCourse = populateSelect(lessonCourseSelect, state.courses, 'id', 'title');
            populateSelect(editLessonCourseSelect, state.courses, 'id', 'title');

            const normalizedUserId = parseInt(selectedUserOption, 10);
            if (!Number.isNaN(normalizedUserId) && normalizedUserId > 0) {
                selectUser(normalizedUserId);
            } else {
                selectUser(state.selectedUserId);
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
        if (Number.isNaN(userId) || userId <= 0) {
            setMessage(assignCourseMessage);
            selectUser(null);
            return;
        }
        setMessage(assignCourseMessage);
        selectUser(userId);
    });

    lessonCourseSelect.addEventListener('change', () => {
        const courseId = parseInt(lessonCourseSelect.value, 10);
        setMessage(createLessonMessage);
        setMessage(lessonListMessage);
        if (state.editingLessonOriginalCourseId && state.editingLessonOriginalCourseId !== courseId) {
            clearLessonEditor();
        }
        if (courseId) {
            loadLessonsForCourse(courseId);
        } else if (!state.courses.length) {
            renderLessonPlaceholder('暂无课程，请先创建。');
        } else {
            renderLessonPlaceholder('请选择课程查看课节');
        }
    });

    userListEl.addEventListener('click', (event) => {
        const item = event.target.closest('li[data-user-id]');
        if (!item) {
            return;
        }
        const userId = parseInt(item.dataset.userId, 10);
        if (Number.isNaN(userId) || userId <= 0) {
            return;
        }
        selectUser(userId);
    });

    userListEl.addEventListener('keydown', (event) => {
        if (event.key !== 'Enter' && event.key !== ' ') {
            return;
        }
        const item = event.target.closest('li[data-user-id]');
        if (!item) {
            return;
        }
        event.preventDefault();
        const userId = parseInt(item.dataset.userId, 10);
        if (Number.isNaN(userId) || userId <= 0) {
            return;
        }
        selectUser(userId);
    });

    courseListEl.addEventListener('click', async (event) => {
        const button = event.target.closest('button[data-course-id]');
        if (button) {
            const courseId = parseInt(button.dataset.courseId, 10);
            if (!courseId) {
                return;
            }
            const action = button.dataset.courseAction || 'delete';
            if (action === 'edit') {
                showCourseEditor(courseId);
                return;
            }
            if (action !== 'delete') {
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
                const previousLessonSelection = lessonCourseSelect.value;
                const previousAssignSelection = assignCourseSelect.value;
                const removedActiveCourse = state.selectedCourseId === courseId;
                const removedLessonEditingCourse = state.editingLessonOriginalCourseId === courseId;
                state.courses = state.courses.filter((item) => item.id !== courseId);
                delete state.lessons[courseId];
                if (removedActiveCourse) {
                    clearCourseEditor();
                } else {
                    refreshCourseList();
                }
                if (removedLessonEditingCourse) {
                    clearLessonEditor();
                }
                setMessage(courseListMessage, '课程已删除', 'success');

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

                populateSelect(
                    editLessonCourseSelect,
                    state.courses,
                    'id',
                    'title',
                    state.editingLessonOriginalCourseId || ''
                );

                const selectedLessonCourseId = parseInt(nextLessonValue, 10);
                if (selectedLessonCourseId) {
                    await loadLessonsForCourse(selectedLessonCourseId);
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
            return;
        }

        const item = event.target.closest('li[data-course-id]');
        if (!item) {
            return;
        }
        const courseId = parseInt(item.dataset.courseId, 10);
        if (!courseId) {
            return;
        }
        showCourseEditor(courseId);
    });

    courseListEl.addEventListener('keydown', (event) => {
        if (event.key !== 'Enter' && event.key !== ' ') {
            return;
        }
        if (event.target instanceof HTMLElement && event.target.tagName === 'BUTTON') {
            return;
        }
        const item = event.target.closest('li[data-course-id]');
        if (!item) {
            return;
        }
        event.preventDefault();
        const courseId = parseInt(item.dataset.courseId, 10);
        if (!courseId) {
            return;
        }
        showCourseEditor(courseId);
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
        if (button) {
            const lessonId = parseInt(button.dataset.lessonId, 10);
            const courseId = parseInt(button.dataset.courseId || lessonCourseSelect.value, 10);
            if (!lessonId) {
                return;
            }
            const action = button.dataset.lessonAction || 'delete';
            if (action === 'edit') {
                if (courseId) {
                    showLessonEditor(courseId, lessonId);
                }
                return;
            }
            if (action !== 'delete') {
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
                if (state.selectedLessonId === lessonId) {
                    clearLessonEditor();
                }
                setMessage(lessonListMessage, '课节已删除', 'success');
                await loadLessonsForCourse(courseId);
            } catch (error) {
                setMessage(lessonListMessage, error.message || '删除课节失败', 'error');
                button.disabled = false;
                button.textContent = originalLabel;
            }
            return;
        }

        const item = event.target.closest('li[data-lesson-id]');
        if (!item) {
            return;
        }
        const lessonId = parseInt(item.dataset.lessonId, 10);
        const courseId = parseInt(item.dataset.courseId || lessonCourseSelect.value, 10);
        if (!lessonId || !courseId) {
            return;
        }
        showLessonEditor(courseId, lessonId);
    });

    lessonListEl.addEventListener('keydown', (event) => {
        if (event.key !== 'Enter' && event.key !== ' ') {
            return;
        }
        if (event.target instanceof HTMLElement && event.target.tagName === 'BUTTON') {
            return;
        }
        const item = event.target.closest('li[data-lesson-id]');
        if (!item) {
            return;
        }
        event.preventDefault();
        const lessonId = parseInt(item.dataset.lessonId, 10);
        const courseId = parseInt(item.dataset.courseId || lessonCourseSelect.value, 10);
        if (!lessonId || !courseId) {
            return;
        }
        showLessonEditor(courseId, lessonId);
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
            const newUser = {
                ...result.user,
                id: Number(result.user.id)
            };
            state.users.push(newUser);
            state.users.sort((a, b) => a.id - b.id);
            state.selectedUserId = newUser.id;
            refreshUserList();
            populateSelect(
                assignUserSelect,
                state.users,
                'id',
                (user) => (user.display_name ? `${user.display_name}（${user.username}）` : user.username),
                newUser.id
            );
            createUserForm.reset();
            setMessage(createUserMessage, '创建成功', 'success');
            setMessage(assignCourseMessage);
            selectUser(newUser.id);
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
            const newCourse = {
                ...result.course,
                id: Number(result.course.id)
            };
            state.courses.push(newCourse);
            state.courses.sort((a, b) => a.id - b.id);
            refreshCourseList();
            const newCourseId = newCourse.id;
            populateSelect(assignCourseSelect, state.courses, 'id', 'title', newCourseId);
            const lessonSelectValue = populateSelect(lessonCourseSelect, state.courses, 'id', 'title', newCourseId);
            populateSelect(editLessonCourseSelect, state.courses, 'id', 'title', state.editingLessonOriginalCourseId || newCourseId);
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

    if (updateCourseForm) {
        updateCourseForm.addEventListener('submit', async (event) => {
            event.preventDefault();
            if (!state.selectedCourseId) {
                setMessage(updateCourseMessage, '请选择需要编辑的课程', 'error');
                return;
            }
            const payload = {
                id: state.selectedCourseId,
                title: editCourseTitleInput.value.trim(),
                description: editCourseDescriptionInput.value.trim()
            };
            if (!payload.title) {
                setMessage(updateCourseMessage, '课程名称不能为空', 'error');
                return;
            }
            setMessage(updateCourseMessage, '正在保存课程，请稍候...');
            try {
                const result = await fetchJSON(`${API_BASE}/courses.php`, {
                    method: 'PATCH',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(payload)
                });
                const updatedCourse = {
                    ...result.course,
                    id: Number(result.course.id)
                };
                const index = state.courses.findIndex((course) => course.id === updatedCourse.id);
                if (index !== -1) {
                    state.courses[index] = updatedCourse;
                } else {
                    state.courses.push(updatedCourse);
                }
                state.courses.sort((a, b) => a.id - b.id);
                state.selectedCourseId = updatedCourse.id;
                refreshCourseList();

                const existingCourseIds = new Set(state.courses.map((course) => course.id));
                const previousLessonSelection = parseInt(lessonCourseSelect.value, 10);
                const previousAssignSelection = parseInt(assignCourseSelect.value, 10);
                const previousEditLessonSelection = parseInt(editLessonCourseSelect.value, 10);

                const resolvedLessonSelection = populateSelect(
                    lessonCourseSelect,
                    state.courses,
                    'id',
                    'title',
                    existingCourseIds.has(previousLessonSelection) ? previousLessonSelection : updatedCourse.id
                );

                populateSelect(
                    assignCourseSelect,
                    state.courses,
                    'id',
                    'title',
                    existingCourseIds.has(previousAssignSelection) ? previousAssignSelection : updatedCourse.id
                );

                const nextEditSelection = state.editingLessonOriginalCourseId && existingCourseIds.has(state.editingLessonOriginalCourseId)
                    ? state.editingLessonOriginalCourseId
                    : (existingCourseIds.has(previousEditLessonSelection) ? previousEditLessonSelection : updatedCourse.id);

                populateSelect(
                    editLessonCourseSelect,
                    state.courses,
                    'id',
                    'title',
                    nextEditSelection
                );

                const selectedAssignmentsUserId = parseInt(assignUserSelect.value, 10);
                if (selectedAssignmentsUserId) {
                    await loadAssignmentsForUser(selectedAssignmentsUserId);
                }
                setMessage(assignCourseMessage);

                setMessage(updateCourseMessage, '课程信息已更新', 'success');
                setMessage(courseListMessage, '课程信息已更新', 'success');

                const activeLessonCourseId = parseInt(resolvedLessonSelection, 10);
                if (activeLessonCourseId && Array.isArray(state.lessons[activeLessonCourseId])) {
                    renderLessons(activeLessonCourseId, state.lessons[activeLessonCourseId]);
                }
            } catch (error) {
                setMessage(updateCourseMessage, error.message || '保存失败', 'error');
            }
        });
    }

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
            type: (lessonTypeSelect?.value || 'recorded')
        };
        if (!payload.course_id || !payload.title) {
            setMessage(createLessonMessage, '请选择课程并填写课节标题', 'error');
            return;
        }
        if (payload.type === 'live') {
            payload.live_url = (lessonLiveUrlInput?.value || '').trim();
            payload.live_start_at = (lessonLiveStartInput?.value || '').trim();
            payload.live_end_at = (lessonLiveEndInput?.value || '').trim();
            if (!payload.live_url) {
                setMessage(createLessonMessage, '请填写直播地址', 'error');
                return;
            }
        } else {
            payload.video_url = (lessonVideoInput?.value || '').trim();
        }
        setMessage(createLessonMessage, '正在添加课节...');
        try {
            await fetchJSON(`${API_BASE}/lessons.php`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(payload)
            });
            const selectedCourseId = payload.course_id;
            createLessonForm.reset();
            if (lessonTypeSelect) {
                lessonTypeSelect.value = 'recorded';
                syncLessonFieldGroups(createLessonFieldGroups, 'recorded');
            }
            const selectedCourseAfterCreate = populateSelect(
                lessonCourseSelect,
                state.courses,
                'id',
                'title',
                selectedCourseId
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

    if (updateLessonForm) {
        updateLessonForm.addEventListener('submit', async (event) => {
            event.preventDefault();
            if (!state.selectedLessonId) {
                setMessage(updateLessonMessage, '请选择需要编辑的课节', 'error');
                return;
            }
            const payload = {
                lesson_id: state.selectedLessonId,
                course_id: parseInt(editLessonCourseSelect.value, 10),
                title: editLessonTitleInput.value.trim(),
                type: (editLessonTypeSelect?.value || 'recorded')
            };
            if (!payload.course_id || !payload.title) {
                setMessage(updateLessonMessage, '请选择课程并填写课节标题', 'error');
                return;
            }
            if (payload.type === 'live') {
                payload.live_url = (editLessonLiveUrlInput?.value || '').trim();
                payload.live_start_at = (editLessonLiveStartInput?.value || '').trim();
                payload.live_end_at = (editLessonLiveEndInput?.value || '').trim();
                if (!payload.live_url) {
                    setMessage(updateLessonMessage, '请填写直播地址', 'error');
                    return;
                }
            } else {
                payload.video_url = editLessonVideoInput.value.trim();
            }
            setMessage(updateLessonMessage, '正在保存课节，请稍候...');
            try {
                const result = await fetchJSON(`${API_BASE}/lessons.php`, {
                    method: 'PATCH',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(payload)
                });
                const updatedLesson = {
                    ...result.lesson,
                    id: Number(result.lesson.id),
                    course_id: Number(result.lesson.course_id),
                    type: result.lesson.type === 'live' ? 'live' : 'recorded',
                    live_url: result.lesson.live_url || '',
                    live_start_at: result.lesson.live_start_at || null,
                    live_end_at: result.lesson.live_end_at || null,
                    video_url: result.lesson.video_url || ''
                };
                const previousCourseId = state.editingLessonOriginalCourseId;
                state.selectedLessonId = updatedLesson.id;
                state.editingLessonOriginalCourseId = updatedLesson.course_id;

                populateSelect(editLessonCourseSelect, state.courses, 'id', 'title', updatedLesson.course_id);
                if (lessonCourseSelect.value !== String(updatedLesson.course_id)) {
                    lessonCourseSelect.value = String(updatedLesson.course_id);
                }

                await loadLessonsForCourse(updatedLesson.course_id);
                if (previousCourseId && previousCourseId !== updatedLesson.course_id) {
                    await loadLessonsForCourse(previousCourseId);
                }

                setMessage(updateLessonMessage, '课节已更新', 'success');
                setMessage(lessonListMessage, '课节已更新', 'success');
            } catch (error) {
                setMessage(updateLessonMessage, error.message || '保存失败', 'error');
            }
        });
    }

    updateUserForm.addEventListener('submit', async (event) => {
        event.preventDefault();
        if (!state.selectedUserId) {
            setMessage(updateUserMessage, '请选择需要修改的用户', 'error');
            return;
        }
        const payload = {
            id: state.selectedUserId,
            username: editUsernameInput.value.trim(),
            display_name: editDisplayNameInput.value.trim(),
            role: editRoleSelect.value
        };
        if (!payload.username) {
            setMessage(updateUserMessage, '用户名不能为空', 'error');
            return;
        }
        const password = editPasswordInput.value.trim();
        if (password) {
            payload.password = password;
        }
        setMessage(updateUserMessage, '正在保存修改，请稍候...');
        try {
            const result = await fetchJSON(`${API_BASE}/users.php`, {
                method: 'PATCH',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(payload)
            });
            const updatedUser = {
                ...result.user,
                id: Number(result.user.id)
            };
            const index = state.users.findIndex((user) => user.id === updatedUser.id);
            if (index !== -1) {
                state.users[index] = updatedUser;
            }
            state.users.sort((a, b) => a.id - b.id);
            state.selectedUserId = updatedUser.id;
            refreshUserList();
            populateSelect(
                assignUserSelect,
                state.users,
                'id',
                (user) => (user.display_name ? `${user.display_name}（${user.username}）` : user.username),
                updatedUser.id
            );
            selectUser(updatedUser.id);
            if (state.currentUser && Number(state.currentUser.id) === updatedUser.id) {
                state.currentUser = { ...state.currentUser, ...updatedUser };
                adminChip.textContent = `${state.currentUser.display_name || state.currentUser.username} · 管理员`;
            }
            editPasswordInput.value = '';
            setMessage(updateUserMessage, '保存成功', 'success');
        } catch (error) {
            setMessage(updateUserMessage, error.message || '保存失败', 'error');
        }
    });

    resetPasswordButton.addEventListener('click', async () => {
        if (!state.selectedUserId) {
            setMessage(updateUserMessage, '请选择需要重置密码的用户', 'error');
            return;
        }
        const target = state.users.find((user) => user.id === state.selectedUserId);
        if (!target) {
            setMessage(updateUserMessage, '用户不存在', 'error');
            return;
        }
        const tempPassword = generateTemporaryPassword(10);
        setMessage(updateUserMessage, '正在重置密码，请稍候...');
        try {
            await fetchJSON(`${API_BASE}/users.php`, {
                method: 'PATCH',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ id: target.id, password: tempPassword })
            });
            editPasswordInput.value = '';
            let copied = false;
            if (navigator.clipboard && window.isSecureContext) {
                try {
                    await navigator.clipboard.writeText(tempPassword);
                    copied = true;
                } catch (clipboardError) {
                    copied = false;
                }
            }
            const suffix = copied ? '（已复制）' : '';
            setMessage(updateUserMessage, `新密码：${tempPassword}${suffix}`, 'success');
        } catch (error) {
            setMessage(updateUserMessage, error.message || '重置密码失败', 'error');
        }
    });

    deleteUserButton.addEventListener('click', async () => {
        if (!state.selectedUserId) {
            setMessage(deleteUserMessage, '请选择需要删除的用户', 'error');
            return;
        }
        const targetIndex = state.users.findIndex((user) => user.id === state.selectedUserId);
        if (targetIndex === -1) {
            setMessage(deleteUserMessage, '用户不存在', 'error');
            return;
        }
        const targetUser = state.users[targetIndex];
        const label = targetUser.display_name || targetUser.username;
        if (!window.confirm(`确定删除用户「${label}」吗？该操作无法恢复。`)) {
            return;
        }
        setMessage(deleteUserMessage, '正在删除用户...');
        try {
            await fetchJSON(`${API_BASE}/users.php`, {
                method: 'DELETE',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ id: targetUser.id })
            });
            state.users.splice(targetIndex, 1);
            const fallback = state.users[targetIndex] || state.users[targetIndex - 1] || null;
            state.users.sort((a, b) => a.id - b.id);
            state.selectedUserId = fallback ? fallback.id : null;
            refreshUserList();
            populateSelect(
                assignUserSelect,
                state.users,
                'id',
                (user) => (user.display_name ? `${user.display_name}（${user.username}）` : user.username),
                state.selectedUserId
            );
            setMessage(deleteUserMessage, '用户已删除', 'success');
            setMessage(updateUserMessage);
            setMessage(assignCourseMessage);
            selectUser(state.selectedUserId);
        } catch (error) {
            setMessage(deleteUserMessage, error.message || '删除失败', 'error');
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
