<!DOCTYPE html>
<html lang="zh-CN">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>网课系统 · 我的课堂</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Plus+Jakarta+Sans:wght@400;500;600;700&family=Noto+Sans+SC:wght@400;500;700&display=swap"
        rel="stylesheet">

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/plyr@3.7.8/dist/plyr.css">

    <style>
        :root {
            /* === 核心色盘 (与主页一致) === */
            --rl-bg: #f8fafc;
            --rl-text-main: #0f172a;
            --rl-text-muted: #64748b;
            --rl-primary: #3b82f6;
            --rl-accent: #8b5cf6;
            --deep-gradient: linear-gradient(135deg, #2563eb, #60a5fa, #22d3ee);
            --gradient-glow: radial-gradient(circle at 50% 0%, rgba(59, 130, 246, 0.15), rgba(139, 92, 246, 0.05), transparent 70%);

            /* 面板/卡片样式 */
            --glass-bg: rgba(255, 255, 255, 0.75);
            --glass-border: 1px solid rgba(255, 255, 255, 0.6);
            --glass-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.02), 0 2px 4px -1px rgba(0, 0, 0, 0.02);
            --active-item-bg: rgba(59, 130, 246, 0.08);
            --active-item-border: 1px solid rgba(59, 130, 246, 0.2);

            /* 布局变量 */
            --header-height: 70px;
            --sidebar-width: 360px;
        }

        body {
            font-family: 'Plus Jakarta Sans', 'Noto Sans SC', system-ui, sans-serif;
            background-color: var(--rl-bg);
            background-image: var(--gradient-glow);
            background-attachment: fixed;
            background-size: 100% 100vh;
            color: var(--rl-text-main);
            min-height: 100vh;
            overflow-x: hidden;
            overflow-y: auto;
        }

        /* --- 导航栏 --- */
        .site-nav {
            position: sticky;
            top: 0;
            z-index: 1000;
            height: var(--header-height);
            backdrop-filter: blur(20px);
            background: rgba(255, 255, 255, 0.85);
            border-bottom: 1px solid rgba(255, 255, 255, 0.5);
            display: flex;
            align-items: center;
        }

        .nav-brand {
            display: inline-flex;
            align-items: center;
            gap: 0.75rem;
            text-decoration: none;
            font-family: 'Inter', sans-serif;
        }

        .brand-mark {
            width: 36px;
            height: 36px;
            border-radius: 10px;
            background: var(--deep-gradient);
            color: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            box-shadow: 0 4px 12px rgba(37, 99, 235, 0.2);
        }

        .brand-text {
            line-height: 1.2;
        }

        .brand-text .small {
            font-size: 0.7rem;
            color: var(--rl-text-muted);
            font-weight: 600;
            letter-spacing: 0.05em;
        }

        .brand-text .fw-bold {
            font-size: 1rem;
            color: var(--rl-text-main);
        }

        .nav-btn {
            padding: 0.4rem 0.9rem;
            border-radius: 8px;
            font-size: 0.85rem;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.2s;
            border: 1px solid transparent;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
        }

        .nav-btn-ghost {
            color: var(--rl-text-muted);
            background: transparent;
        }

        .nav-btn-ghost:hover {
            color: var(--rl-text-main);
            background: rgba(0, 0, 0, 0.04);
        }

        .nav-btn-outline {
            border-color: rgba(0, 0, 0, 0.1);
            color: var(--rl-text-main);
            background: white;
        }

        .nav-btn-outline:hover {
            border-color: var(--rl-primary);
            color: var(--rl-primary);
            transform: translateY(-1px);
        }

        .user-chip {
            padding: 0.35rem 0.8rem;
            background: rgba(59, 130, 246, 0.1);
            color: var(--rl-primary);
            border-radius: 99px;
            font-size: 0.8rem;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
        }

        /* --- 仪表盘主体布局 --- */
        .dashboard-container {
            display: grid;
            grid-template-columns: var(--sidebar-width) 1fr;
            gap: 1.5rem;
            padding: 1.5rem 0 3rem;
            max-width: 1600px;
            margin: 0 auto;
            align-items: start;
        }

        /* 侧边栏 - 自然高度，完整显示所有内容 */
        .sidebar {
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }

        /* 主舞台 - 自然高度，完整显示 */
        .stage {
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }

        .panel-glass {
            background: var(--glass-bg);
            backdrop-filter: blur(12px);
            border: var(--glass-border);
            border-radius: 16px;
            box-shadow: var(--glass-shadow);
            display: flex;
            flex-direction: column;
        }

        .panel-header {
            padding: 1rem 1.25rem;
            border-bottom: 1px solid rgba(0, 0, 0, 0.04);
            background: rgba(255, 255, 255, 0.4);
        }

        .panel-title {
            font-size: 0.95rem;
            font-weight: 700;
            margin: 0;
            display: flex;
            align-items: center;
            gap: 8px;
            line-height: 1.2;
        }

        .panel-subtitle {
            font-size: 0.8rem;
            color: var(--rl-text-muted);
            margin-top: 4px;
        }

        .panel-body {
            padding: 1rem;
        }

        /* 课程列表项样式 */
        .sidebar-item {
            display: block;
            width: 100%;
            text-align: left;
            padding: 1rem;
            border: 1px solid transparent;
            background: rgba(255, 255, 255, 0.6);
            border-radius: 12px;
            margin-bottom: 0.75rem;
            transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
            cursor: pointer;
            position: relative;
        }

        .sidebar-item:last-child {
            margin-bottom: 0;
        }

        .sidebar-item:hover {
            transform: translateY(-2px);
            background: #fff;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.03);
        }

        .sidebar-item.active {
            background: white;
            border: var(--active-item-border);
            box-shadow: 0 4px 12px rgba(59, 130, 246, 0.15);
        }

        .sidebar-item.active::before {
            content: '';
            position: absolute;
            left: 0;
            top: 15%;
            bottom: 15%;
            width: 4px;
            background: var(--rl-primary);
            border-radius: 0 4px 4px 0;
        }

        .item-title {
            font-weight: 600;
            color: var(--rl-text-main);
            font-size: 0.95rem;
            margin-bottom: 0.25rem;
            line-height: 1.4;
        }

        .item-desc {
            font-size: 0.8rem;
            color: var(--rl-text-muted);
            line-height: 1.5;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .item-meta {
            display: flex;
            flex-wrap: wrap;
            gap: 6px;
            margin-top: 0.6rem;
        }

        .rl-badge {
            font-size: 0.7rem;
            padding: 2px 8px;
            border-radius: 6px;
            font-weight: 600;
            background: rgba(0, 0, 0, 0.04);
            color: var(--rl-text-muted);
        }

        .rl-badge.primary {
            background: rgba(59, 130, 246, 0.1);
            color: var(--rl-primary);
        }

        .rl-badge.success {
            background: rgba(16, 185, 129, 0.1);
            color: #10b981;
        }

        /* 筛选器样式 */
        .filter-group .form-control,
        .filter-group .form-select {
            font-size: 0.85rem;
            border-radius: 8px;
            border: 1px solid rgba(0, 0, 0, 0.08);
            background: rgba(255, 255, 255, 0.8);
            padding: 0.4rem 0.6rem;
        }

        .filter-group .form-control:focus,
        .filter-group .form-select:focus {
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
            border-color: var(--rl-primary);
        }

        /* --- 主舞台 (Stage) --- */
        /* 样式已在上方定义 */

        .player-wrapper {
            background: #000;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 20px 40px -5px rgba(0, 0, 0, 0.2);
            aspect-ratio: 16/9;
            position: relative;
        }

        /* Plyr 覆盖样式以匹配主题 */
        .plyr {
            --plyr-color-main: var(--rl-primary);
            border-radius: 16px;
            height: 100%;
        }

        .stage-header {
            margin-bottom: 1rem;
        }

        .breadcrumbs {
            font-size: 0.85rem;
            color: var(--rl-text-muted);
            margin-bottom: 0.5rem;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .breadcrumbs span.current {
            color: var(--rl-primary);
            font-weight: 600;
        }

        .stage-title {
            font-size: 1.75rem;
            font-weight: 800;
            letter-spacing: -0.02em;
            margin-bottom: 0.5rem;
        }

        .stage-desc {
            font-size: 1rem;
            color: var(--rl-text-muted);
            line-height: 1.6;
            max-width: 800px;
        }

        .attachment-list {
            display: flex;
            flex-wrap: wrap;
            gap: 0.75rem;
            margin-top: 1rem;
        }

        .attachment-item {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 0.5rem 1rem;
            background: white;
            border: 1px solid rgba(0, 0, 0, 0.08);
            border-radius: 10px;
            font-size: 0.85rem;
            color: var(--rl-text-main);
            text-decoration: none;
            transition: all 0.2s;
        }

        .attachment-item:hover {
            border-color: var(--rl-primary);
            color: var(--rl-primary);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
        }

        /* 空状态 */
        .empty-state {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            height: 100%;
            min-height: 200px;
            color: var(--rl-text-muted);
            text-align: center;
            padding: 2rem;
        }

        .empty-icon {
            font-size: 3rem;
            margin-bottom: 1rem;
            opacity: 0.3;
        }

        /* B站跳转卡片 */
        .portal-card {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            height: 100%;
            background: #f1f5f9;
            color: #475569;
            text-decoration: none;
            transition: all 0.3s;
        }

        .portal-card:hover {
            background: #e2e8f0;
            color: #334155;
        }

        .portal-logo {
            font-size: 1.2rem;
            font-weight: 700;
            color: #fb7299;
            margin-bottom: 0.5rem;
        }

        /* 响应式调整 */
        .mobile-drawer-overlay {
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, 0.4);
            z-index: 1040;
            opacity: 0;
            pointer-events: none;
            transition: opacity 0.3s;
        }

        @media (max-width: 992px) {
            .dashboard-container {
                grid-template-columns: 1fr;
                height: auto;
                display: block;
                padding-bottom: 80px;
                /* Space for bottom nav */
            }

            .sidebar {
                position: fixed;
                top: 0;
                left: 0;
                bottom: 0;
                width: 85vw;
                /* Better fit for small screens */
                max-width: 320px;
                z-index: 1050;
                background: var(--rl-bg);
                padding: 1rem;
                transform: translateX(-100%);
                transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
                box-shadow: 4px 0 24px rgba(0, 0, 0, 0.1);
            }

            .course-drawer-open .sidebar.course-sidebar {
                transform: translateX(0);
            }

            .lesson-drawer-open .sidebar.lesson-sidebar {
                transform: translateX(0);
            }

            .course-drawer-open .mobile-drawer-overlay,
            .lesson-drawer-open .mobile-drawer-overlay {
                opacity: 1;
                pointer-events: auto;
            }

            .sidebar-section {
                display: none;
            }

            .sidebar-section.active {
                display: flex;
                height: 100%;
            }

            /* 隐藏顶部导航的部分元素以简化视图 */
            .site-nav .d-flex.align-items-center:not(.w-100) {
                /* Keep logo visible, hide right side actions if needed, or adjust */
            }
        }

        /* 移动端底部导航 */
        .mobile-bottom-nav {
            display: none;
            position: fixed;
            bottom: 0;
            left: 0;
            width: 100%;
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            padding: 8px 16px;
            box-shadow: 0 -4px 20px rgba(0, 0, 0, 0.05);
            z-index: 1000;
            justify-content: space-around;
            align-items: center;
            border-top: 1px solid rgba(0, 0, 0, 0.05);
            padding-bottom: max(8px, env(safe-area-inset-bottom));
        }

        .mobile-nav-item {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 4px;
            color: var(--rl-text-muted);
            text-decoration: none;
            font-size: 0.75rem;
            font-weight: 500;
            padding: 4px 12px;
            border-radius: 8px;
            transition: all 0.2s;
            border: none;
            background: transparent;
        }

        .mobile-nav-item i {
            font-size: 1.4rem;
            margin-bottom: -2px;
        }

        .mobile-nav-item.active {
            color: var(--rl-primary);
            background: rgba(59, 130, 246, 0.08);
        }

        @media (max-width: 992px) {
            .mobile-bottom-nav {
                display: flex;
            }

            .mobile-only-buttons {
                display: none !important;
            }

            /* Hide old buttons */
        }

        @media (min-width: 993px) {
            .mobile-only {
                display: none !important;
            }

            .mobile-bottom-nav {
                display: none;
            }
        }
    </style>
</head>

<body class="dashboard-shell">

    <nav class="site-nav">
        <div class="container-xxl px-4 w-100 d-flex align-items-center justify-content-between">
            <div class="d-flex align-items-center gap-3">
                <a href="/rarelight/" class="nav-brand">
                    <span class="brand-mark">RL</span>
                    <div class="brand-text d-none d-sm-block">
                        <div class="small text-uppercase">RARE LIGHT</div>
                        <div class="fw-bold">我的课堂</div>
                    </div>
                </a>
            </div>

            <div class="d-flex align-items-center gap-2">
                <div class="user-chip d-none d-md-inline-flex" id="userChip">学员</div>
                <div class="text-end me-2 d-none d-lg-block">
                    <div class="small fw-bold" id="welcomeText">正在加载...</div>
                </div>

                <a class="nav-btn nav-btn-outline d-none d-sm-inline-flex" href="/rarelight/">
                    <i class="bi bi-house"></i> 首页
                </a>

                <button class="nav-btn nav-btn-outline" id="cloudButton" style="display:none;">
                    <i class="bi bi-cloud"></i> <span class="d-none d-md-inline">云盘</span>
                </button>
                <button class="nav-btn nav-btn-outline" id="adminButton" style="display:none;">
                    <i class="bi bi-speedometer2"></i> <span class="d-none d-md-inline">后台</span>
                </button>

                <button class="nav-btn nav-btn-ghost text-danger" id="logoutButton" title="退出登录">
                    <i class="bi bi-box-arrow-right"></i>
                </button>
            </div>
        </div>
    </nav>

    <div class="mobile-drawer-overlay" id="drawerBackdrop"></div>

    <main class="dashboard-container container-xxl">

        <aside class="sidebar" id="mainSidebar">
            <div class="panel-glass sidebar-section active">
                <div class="panel-header">
                    <h3 class="panel-title"><i class="bi bi-collection-play text-primary"></i> 我的课程</h3>
                    <div class="filter-group mt-2 d-flex flex-column gap-2">
                        <input type="search" class="form-control" id="courseSearchInput" placeholder="搜索课程...">
                        <div class="d-flex gap-2">
                            <select class="form-select" id="courseSortSelect">
                                <option value="default">默认排序</option>
                                <option value="latest">最新</option>
                                <option value="progress">进度</option>
                            </select>
                            <select class="form-select" id="courseProgressFilter" style="max-width: 100px;">
                                <option value="all">全部</option>
                                <option value="unwatched">未看</option>
                                <option value="in_progress">学习中</option>
                                <option value="completed">已看</option>
                            </select>
                        </div>
                        <select id="courseTagFilter" hidden>
                            <option value="">全部标签</option>
                        </select>
                        <select id="courseInstructorFilter" hidden>
                            <option value="">全部老师</option>
                        </select>
                    </div>
                </div>
                <div class="panel-body custom-scrollbar" id="courseList">
                    <div class="sidebar-item" style="cursor: default;">
                        <div class="placeholder-glow">
                            <span class="placeholder col-8 mb-2"></span>
                            <span class="placeholder col-12" style="height: 40px; opacity: 0.1"></span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="panel-glass sidebar-section active">
                <div class="panel-header sticky-top">
                    <h3 class="panel-title" id="lessonPaneTitle"><i class="bi bi-list-task text-primary"></i> 课节列表</h3>
                    <div class="panel-subtitle" id="lessonPaneHint">请先选择课程</div>
                </div>
                <div class="panel-body custom-scrollbar" id="lessonList">
                    <div class="empty-state py-4">
                        <i class="bi bi-arrow-up-circle empty-icon"></i>
                        <p class="small">暂未选择课程</p>
                    </div>
                </div>
            </div>
        </aside>

        <section class="stage">
            <!-- Mobile buttons removed, replaced by bottom nav -->

            <div class="panel-glass p-4" style="flex-shrink: 0;">
                <div class="stage-header">
                    <div class="breadcrumbs" id="breadcrumbs"><span>网课</span></div>
                    <h2 class="stage-title" id="lessonTitle">欢迎回来</h2>
                    <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
                        <p class="stage-desc mb-0" id="lessonDescription">选择课程开始学习。</p>
                        <div id="lessonMeta" hidden>
                            <button class="nav-btn nav-btn-outline" id="markCompleteButton">
                                <i class="bi bi-check2-circle"></i> <span>标记已完成</span>
                            </button>
                        </div>
                    </div>
                </div>

                <div class="player-wrapper" id="playerHost">
                    <div class="empty-state" style="background: #1e293b;">
                        <i class="bi bi-play-circle empty-icon text-white"></i>
                        <p class="text-white-50">选择课节以开始播放</p>
                    </div>
                </div>

                <div id="lessonAttachments" class="mt-3"></div>

                <div class="mt-4 pt-4 border-top" id="courseSummaryArea">
                    <h4 class="h5 fw-bold mb-3" id="courseSummaryTitle">课程概览</h4>
                    <div class="d-flex gap-3 align-items-center mb-3">
                        <span class="rl-badge primary" id="courseLessonCount">0 课节</span>
                        <span class="rl-badge" id="courseStatusChip" hidden></span>
                        <div class="progress flex-grow-1" style="height: 6px; max-width: 200px;">
                            <div class="progress-bar" id="courseProgressBar" style="width: 0%"></div>
                        </div>
                    </div>
                    <p class="text-muted small" id="courseSummaryDescription">选择课程查看详情。</p>
                </div>
            </div>

            <div class="alert alert-light border shadow-sm small text-center" id="stageHint" hidden></div>
        </section>
    </main>

    <!-- 底部导航栏 -->
    <div class="mobile-bottom-nav">
        <button class="mobile-nav-item" onclick="window.location.href='/rarelight/'">
            <i class="bi bi-house"></i>
            <span>首页</span>
        </button>
        <button class="mobile-nav-item" id="mobileCourseToggle">
            <i class="bi bi-collection-play"></i>
            <span>课程</span>
        </button>
        <button class="mobile-nav-item" id="mobileLessonToggle">
            <i class="bi bi-list-task"></i>
            <span>课节</span>
        </button>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/plyr@3.7.8/dist/plyr.polyfilled.min.js"></script>

    <script>
        const BASE_PATH = '/rarelight';
        const API_BASE = `${BASE_PATH}/api`;
        const ROUTE_LOGIN = `${BASE_PATH}/login`;
        const ROUTE_ADMIN = `${BASE_PATH}/admin`;
        const ROUTE_CLOUD = `${BASE_PATH}/cloud`;

        function normalizeApiUrl(url) { return url; }

        const courseListEl = document.getElementById('courseList');
        const lessonListEl = document.getElementById('lessonList');
        const lessonPaneTitleEl = document.getElementById('lessonPaneTitle');
        const lessonPaneHintEl = document.getElementById('lessonPaneHint');
        const breadcrumbsEl = document.getElementById('breadcrumbs');
        const lessonTitleEl = document.getElementById('lessonTitle');
        const lessonDescriptionEl = document.getElementById('lessonDescription');
        const lessonMetaEl = document.getElementById('lessonMeta');
        const playerHostEl = document.getElementById('playerHost');
        const attachmentsHostId = 'lessonAttachments';
        const welcomeTextEl = document.getElementById('welcomeText');
        const userChipEl = document.getElementById('userChip');
        const logoutButton = document.getElementById('logoutButton');
        const cloudButton = document.getElementById('cloudButton');
        const adminButton = document.getElementById('adminButton');
        const courseSummaryTitleEl = document.getElementById('courseSummaryTitle');
        const courseSummaryDescriptionEl = document.getElementById('courseSummaryDescription');
        const courseLessonCountEl = document.getElementById('courseLessonCount');
        const courseStatusChipEl = document.getElementById('courseStatusChip');
        const courseProgressBarEl = document.getElementById('courseProgressBar');
        const stageHintEl = document.getElementById('stageHint');

        // Mobile Drawers
        const drawerBackdrop = document.getElementById('drawerBackdrop');
        // New Bottom Nav Buttons
        const mobileCourseToggle = document.getElementById('mobileCourseToggle');
        const mobileLessonToggle = document.getElementById('mobileLessonToggle');

        const mainSidebar = document.getElementById('mainSidebar');

        // Filters
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
            search: '', tag: '', instructor: '', progress: 'all', sort: 'default'
        };
        let progressStore = {};

        // --- Drawer Logic ---
        function closeAllDrawers() {
            document.body.classList.remove('course-drawer-open', 'lesson-drawer-open');
            // Reset sidebar transforms for mobile
            if (window.innerWidth <= 992) {
                mainSidebar.style.transform = '';
                mainSidebar.classList.remove('course-sidebar', 'lesson-sidebar');
            }
        }

        function openDrawer(type) {
            closeAllDrawers();
            document.body.classList.add(`${type}-drawer-open`);
            if (window.innerWidth <= 992) {
                mainSidebar.classList.add(`${type}-sidebar`);
                // Show/Hide specific sections within the sidebar based on type
                const sections = mainSidebar.querySelectorAll('.sidebar-section');
                sections.forEach(sec => sec.style.display = 'none');

                if (type === 'course') mainSidebar.firstElementChild.style.display = 'block';
                if (type === 'lesson') mainSidebar.lastElementChild.style.display = 'block';
            }
        }

        // Restore sidebar visibility on resize
        window.addEventListener('resize', () => {
            if (window.innerWidth > 992) {
                closeAllDrawers();
                const sections = mainSidebar.querySelectorAll('.sidebar-section');
                sections.forEach(sec => sec.style.display = '');
            }
        });

        if (mobileCourseToggle) mobileCourseToggle.addEventListener('click', () => {
            openDrawer('course');
            updateMobileNavActive('course');
        });
        if (mobileLessonToggle) mobileLessonToggle.addEventListener('click', () => {
            openDrawer('lesson');
            updateMobileNavActive('lesson');
        });
        if (drawerBackdrop) drawerBackdrop.addEventListener('click', () => {
            closeAllDrawers();
            updateMobileNavActive(null);
        });

        function updateMobileNavActive(type) {
            document.querySelectorAll('.mobile-nav-item').forEach(btn => btn.classList.remove('active'));
            if (type === 'course') mobileCourseToggle.classList.add('active');
            if (type === 'lesson') mobileLessonToggle.classList.add('active');
        }

        // --- UI Update Functions (Re-styled) ---

        function renderCourseList(courses) {
            const source = courses && Array.isArray(courses) ? courses : allCourses;
            const filtered = applyCourseFilters(source || []);
            courseListEl.innerHTML = '';

            if (!filtered.length) {
                courseListEl.innerHTML = '<div class="empty-state py-3"><p class="small mb-0">没有找到课程</p></div>';
                return;
            }

            filtered.forEach((course) => {
                const progress = getCourseProgress(course.id, course.lesson_count || 0);
                const isActive = course.id === currentCourseId;
                const progressColor = progress.percentage >= 100 ? 'success' : 'primary';

                const item = document.createElement('div');
                item.className = `sidebar-item ${isActive ? 'active' : ''}`;
                item.dataset.courseId = course.id;

                item.innerHTML = `
                <div class="item-title">${course.title}</div>
                <div class="item-desc">${course.description || '暂无描述'}</div>
                <div class="item-meta">
                    ${course.instructor ? `<span class="rl-badge primary">${course.instructor}</span>` : ''}
                    <span class="rl-badge ${progressColor}">${progress.percentage}% 完成</span>
                </div>
            `;
                item.addEventListener('click', () => selectCourse(course.id));
                courseListEl.appendChild(item);
            });
        }

        function renderLessonList(lessons, course) {
            // Reset Mobile Sidebar View
            if (window.innerWidth > 992) {
                const sections = mainSidebar.querySelectorAll('.sidebar-section');
                sections.forEach(sec => sec.style.display = '');
            }

            currentCourse = course || null;
            currentLessons = lessons || [];
            if (currentCourse && currentCourse.id) {
                updateCourseTotalLessons(currentCourse.id, currentLessons.length || 0);
            }
            currentLessonId = null;
            clearPlayers();

            playerHostEl.innerHTML = `
            <div class="empty-state" style="background: #1e293b;">
                <i class="bi bi-play-circle empty-icon text-white"></i>
                <p class="text-white-50">请选择课节</p>
            </div>`;

            lessonListEl.innerHTML = '';
            updateCourseSummary(currentCourse, currentLessons.length);
            setCourseProgress(0, currentLessons.length, currentCourse ? currentCourse.id : null);
            renderCourseList(allCourses); // Re-render to update active state

            // Breadcrumbs & Titles
            lessonTitleEl.textContent = currentCourse ? currentCourse.title : '欢迎回来';
            lessonDescriptionEl.textContent = currentCourse ? (currentCourse.description || '') : '选择课程开始学习';

            if (!currentLessons.length) {
                lessonListEl.innerHTML = '<div class="empty-state py-3"><p class="small">该课程暂无课节</p></div>';
                lessonMetaEl.hidden = true;
                updateBreadcrumbs(currentCourse);
                return;
            }

            lessonPaneHintEl.textContent = `共 ${currentLessons.length} 节`;
            lessonMetaEl.hidden = false;

            currentLessons.forEach((lesson, index) => {
                const isVisited = isLessonVisited(currentCourseId, lesson.id);
                const isCompleted = isLessonCompleted(currentCourseId, lesson.id);
                const order = String(index + 1).padStart(2, '0');

                const btn = document.createElement('div');
                btn.className = `sidebar-item d-flex gap-3 align-items-start ${isCompleted ? 'border-success-subtle' : ''}`;
                btn.dataset.lessonId = lesson.id;

                let statusIcon = isCompleted ? '<i class="bi bi-check-circle-fill text-success"></i>' :
                    (isVisited ? '<i class="bi bi-circle-half text-primary"></i>' : '<i class="bi bi-circle text-muted"></i>');

                btn.innerHTML = `
                <div class="pt-1">${statusIcon}</div>
                <div class="flex-grow-1" style="min-width: 0;">
                    <div class="item-title mb-1 text-break">${lesson.title}</div>
                    <div class="small text-muted">课节 ${order}</div>
                </div>
            `;

                btn.addEventListener('click', () => selectLesson(lesson.id));
                lessonListEl.appendChild(btn);
            });

            // Auto select first if available? Optional.
            // selectLesson(currentLessons[0].id);
        }

        function selectLesson(lessonId) {
            const normalizedLessonId = Number(lessonId);
            if (!currentLessons.length || normalizedLessonId === currentLessonId) return;

            const lesson = currentLessons.find(i => Number(i.id) === normalizedLessonId);
            if (!lesson) return;

            // UI Updates
            closeAllDrawers();
            currentLessonId = normalizedLessonId;

            // Highlight active lesson
            document.querySelectorAll('#lessonList .sidebar-item').forEach(el => {
                el.classList.toggle('active', Number(el.dataset.lessonId) === currentLessonId);
            });

            clearPlayers();
            const { wrapper, video } = buildPlayer(lesson.video_url || '');
            playerHostEl.innerHTML = '';
            playerHostEl.appendChild(wrapper);

            renderLessonAttachments(lesson.attachments || lesson.attachment);
            lessonTitleEl.textContent = lesson.title || '课节';
            lessonDescriptionEl.textContent = lesson.description || '暂无详细介绍';

            updateBreadcrumbs(currentCourse, lesson);

            const lessonIndex = currentLessons.findIndex(i => Number(i.id) === currentLessonId);
            markLessonVisited(currentCourseId, currentLessonId, currentLessons.length);
            renderCourseList(allCourses); // Update progress bars in sidebar
            syncMarkCompleteButton();

            if (video) {
                const player = new Plyr(video, {
                    controls: ['play-large', 'play', 'progress', 'current-time', 'mute', 'volume', 'settings', 'fullscreen'],
                    settings: ['speed']
                });
                player.on('ended', () => {
                    setLessonCompleted(currentCourseId, currentLessonId, true);
                    refreshProgressUI();
                });
                players.push(player);
            }
            setCourseProgress(lessonIndex + 1, currentLessons.length, currentCourseId);
        }

        // --- Utility Functions (Adapted styling) ---

        function updateBreadcrumbs(course, lesson) {
            const fragments = ['<span>网课</span>'];
            if (course) {
                fragments.push('<i class="bi bi-chevron-right" style="font-size:0.7em"></i>');
                fragments.push(`<span class="${!lesson ? 'current' : ''}">${course.title}</span>`);
            }
            if (lesson) {
                fragments.push('<i class="bi bi-chevron-right" style="font-size:0.7em"></i>');
                fragments.push(`<span class="current">${lesson.title}</span>`);
            }
            if (breadcrumbsEl) breadcrumbsEl.innerHTML = fragments.join(' ');
        }

        function syncMarkCompleteButton() {
            if (!markCompleteButton) return;
            const labelEl = markCompleteButton.querySelector('span');
            const iconEl = markCompleteButton.querySelector('i');

            if (!currentCourseId || !currentLessonId) {
                markCompleteButton.hidden = true;
                return;
            }
            markCompleteButton.hidden = false;

            const record = progressStore[currentCourseId] || { completed: [] };
            const isDone = Array.isArray(record.completed) && record.completed.includes(currentLessonId);

            if (isDone) {
                markCompleteButton.className = 'nav-btn nav-btn-outline bg-success-subtle text-success border-success';
                labelEl.textContent = '已完成';
                iconEl.className = 'bi bi-check-circle-fill';
            } else {
                markCompleteButton.className = 'nav-btn nav-btn-outline';
                labelEl.textContent = '标记完成';
                iconEl.className = 'bi bi-circle';
            }
        }

        function buildPlayer(url) {
            const wrapper = document.createElement('div');
            wrapper.style.width = '100%';
            wrapper.style.height = '100%';

            if (!url || !url.trim()) {
                wrapper.className = 'empty-state';
                wrapper.style.background = '#000';
                wrapper.innerHTML = '<p class="text-white">暂无视频源</p>';
                return { wrapper };
            }

            // 简化的 Bilibili 检测
            if (url.includes('bilibili')) {
                wrapper.innerHTML = `
                <a href="${url}" target="_blank" class="portal-card">
                    <div class="portal-logo">Bilibili</div>
                    <div class="fw-bold">点击跳转至 B站观看</div>
                    <div class="small mt-2">观看结束后请返回标记完成</div>
                </a>
             `;
                return { wrapper };
            }

            const video = document.createElement('video');
            video.className = 'player-media';
            video.playsInline = true;
            video.controls = true;
            const source = document.createElement('source');
            source.src = url;
            video.appendChild(source);
            wrapper.appendChild(video);
            return { wrapper, video };
        }

        function renderLessonAttachments(attachments) {
            const host = document.getElementById(attachmentsHostId);
            host.innerHTML = '';
            const items = Array.isArray(attachments) ? attachments : [];
            if (!items.length) return;

            const label = document.createElement('div');
            label.className = 'small fw-bold text-muted mb-2';
            label.textContent = '课节附件';
            host.appendChild(label);

            const list = document.createElement('div');
            list.className = 'attachment-list';
            items.forEach((att, idx) => {
                const link = document.createElement('a');
                link.href = att.url || att.link || '#';
                link.target = '_blank';
                link.className = 'attachment-item';
                link.innerHTML = `<i class="bi bi-paperclip"></i> ${att.title || `附件 ${idx + 1}`}`;
                list.appendChild(link);
            });
            host.appendChild(list);
        }

        // --- Data Logic (Original Logic Preserved) ---
        // 下面的逻辑与您原始提供的 JS 几乎完全一致，只是去掉了一些不再需要的 DOM 操作

        const handleCourseFilterChange = () => {
            courseFilters.search = courseSearchInput.value.trim().toLowerCase();
            courseFilters.sort = courseSortSelect.value;
            courseFilters.progress = courseProgressFilter ? courseProgressFilter.value : 'all';
            renderCourseList(allCourses);
        };

        if (courseSearchInput) courseSearchInput.addEventListener('input', handleCourseFilterChange);
        if (courseSortSelect) courseSortSelect.addEventListener('change', handleCourseFilterChange);
        if (courseProgressFilter) courseProgressFilter.addEventListener('change', handleCourseFilterChange);

        // ... (保留原始的 fetchJSON, loadProgressStore, getCourseProgress, postProgressUpdate 等逻辑函数) ...
        // 为了节省篇幅，这里假设您保留了原有 script标签底部的核心数据处理逻辑 
        // 必须保留的辅助函数：

        function normalizeTags(tags) {
            if (!tags) return [];
            return String(tags).split(',').map(t => t.trim()).filter(Boolean);
        }

        function applyCourseFilters(courses) {
            const list = courses || [];
            return list.filter(c => {
                // Search Filter
                if (courseFilters.search && !c.title.toLowerCase().includes(courseFilters.search)) {
                    return false;
                }

                // Progress Filter
                const progress = getCourseProgress(c.id, c.lesson_count);
                if (courseFilters.progress !== 'all') {
                    if (courseFilters.progress === 'unwatched' && progress.percentage > 0) return false;
                    if (courseFilters.progress === 'in_progress' && (progress.percentage === 0 || progress.percentage === 100)) return false;
                    if (courseFilters.progress === 'completed' && progress.percentage < 100) return false;
                }

                // Tag Filter (placeholder if needed in future)
                if (courseFilters.tag && c.tags && !normalizeTags(c.tags).includes(courseFilters.tag)) {
                    return false;
                }

                // Instructor Filter (placeholder)
                if (courseFilters.instructor && c.instructor !== courseFilters.instructor) {
                    return false;
                }

                return true;
            }).sort((a, b) => {
                // Sort Logic
                if (courseFilters.sort === 'latest') {
                    return (b.id - a.id); // Assuming higher ID is newer
                }
                if (courseFilters.sort === 'progress') {
                    const pA = getCourseProgress(a.id, a.lesson_count).percentage;
                    const pB = getCourseProgress(b.id, b.lesson_count).percentage;
                    return pB - pA;
                }
                return 0; // Default
            });
        }

        // 必须重新绑定的 Mark Complete 事件
        if (markCompleteButton) {
            markCompleteButton.addEventListener('click', () => {
                if (!currentCourseId || !currentLessonId) return;
                const record = progressStore[currentCourseId] || { completed: [] };
                const isDone = Array.isArray(record.completed) && record.completed.includes(currentLessonId);
                setLessonCompleted(currentCourseId, currentLessonId, !isDone);
                syncMarkCompleteButton();
                refreshProgressUI();
            });
        }

        // 复用原有的进度相关函数 (getCourseProgress, markLessonVisited, etc.)
        // ... 将原代码的这部分逻辑直接复制过来即可 ...

        // 为了代码完整性，这里补充关键的进度状态检查辅助函数
        function isLessonVisited(cid, lid) {
            const r = progressStore[cid];
            return r && r.visited && r.visited.includes(lid);
        }
        function isLessonCompleted(cid, lid) {
            const r = progressStore[cid];
            return r && r.completed && r.completed.includes(lid);
        }

        // 复制原有的 Session Load 逻辑
        async function loadSession() {
            try {
                const data = await fetchJSON(`${API_BASE}/session.php`);
                if (!data.user) { window.location.href = ROUTE_LOGIN; return; }
                currentUser = data.user;
                await loadProgressStore();

                welcomeTextEl.textContent = `你好，${currentUser.display_name || currentUser.username}`;

                // Update User Role Chip
                let roleText = '学员';
                if (currentUser.role === 'teacher') roleText = '老师';
                if (currentUser.role === 'admin') roleText = '管理员';
                if (userChipEl) userChipEl.textContent = roleText;

                if (currentUser.role === 'admin' || currentUser.role === 'teacher') {
                    adminButton.style.display = 'inline-flex';
                    cloudButton.style.display = 'inline-flex';
                }
                await loadCourses();
            } catch (error) {
                window.location.href = ROUTE_LOGIN;
            }
        }

        // 复制原有的 Course Load 逻辑
        async function loadCourses() {
            try {
                const data = await fetchJSON(`${API_BASE}/courses.php`);
                allCourses = (data.courses || []).map(c => ({
                    ...c, id: Number(c.id), lesson_count: Number(c.lesson_count || 0)
                }));
                renderCourseList(allCourses);
            } catch (e) { console.error(e); }
        }

        // 复制原有的 Select Course 逻辑
        async function selectCourse(courseId) {
            if (Number(courseId) === currentCourseId) return;
            currentCourseId = Number(courseId);

            // Highlight logic
            document.querySelectorAll('#courseList .sidebar-item').forEach(el => {
                el.classList.toggle('active', Number(el.dataset.courseId) === currentCourseId);
            });

            try {
                const data = await fetchJSON(`${API_BASE}/courses.php?id=${currentCourseId}`);
                const lessons = (data.lessons || []).map(l => ({
                    ...l, id: Number(l.id), attachments: l.attachments || []
                }));
                renderLessonList(lessons, data.course);

                // Mobile: switch to lesson drawer
                if (window.innerWidth <= 992) {
                    openDrawer('lesson');
                    updateMobileNavActive('lesson');
                }

            } catch (e) { console.error(e); }
        }

        // 辅助: Fetch
        async function fetchJSON(url, opts = {}) {
            const res = await fetch(url, { credentials: 'include', headers: { 'Accept': 'application/json', ...opts.headers }, ...opts });
            const data = await res.json().catch(() => null);
            if (!res.ok) throw new Error((data && data.message) || 'Error');
            return data;
        }

        // 辅助: Progress Logic placeholders (Please keep your original logic here)
        function updateCourseTotalLessons(cid, count) {
            if (!progressStore[cid]) progressStore[cid] = { visited: [], completed: [], total: 0 };
            progressStore[cid].total = count;
        }
        function getCourseProgress(cid, total) {
            const r = progressStore[cid] || { visited: [], completed: [], total: total || 0 };
            const t = total || r.total || 0;
            const c = r.completed ? r.completed.length : 0;
            return { percentage: t > 0 ? Math.round((c / t) * 100) : 0, completed: c, total: t };
        }
        function setCourseProgress(a, b, c) { /* UI update helper, already integrated above */ }
        function setCourseSummary(t, d) {
            courseSummaryTitleEl.textContent = t;
            courseSummaryDescriptionEl.textContent = d;
        }
        function updateCourseSummary(c, lCount) {
            if (!c) return;
            setCourseSummary(c.title, c.description);
            courseLessonCountEl.textContent = `${lCount} 课节`;
            const p = getCourseProgress(c.id, lCount);
            courseProgressBarEl.style.width = `${p.percentage}%`;
        }
        function markLessonVisited(cid, lid, total) {
            if (!progressStore[cid]) progressStore[cid] = { visited: [], completed: [], total: total };
            if (!progressStore[cid].visited.includes(lid)) {
                progressStore[cid].visited.push(lid);
                postProgressUpdate('visit', cid, lid);
            }
        }
        function setLessonCompleted(cid, lid, status) {
            if (!progressStore[cid]) progressStore[cid] = { visited: [], completed: [], total: 0 };
            if (status) {
                if (!progressStore[cid].completed.includes(lid)) progressStore[cid].completed.push(lid);
            } else {
                progressStore[cid].completed = progressStore[cid].completed.filter(id => id !== lid);
            }
            postProgressUpdate(status ? 'complete' : 'uncomplete', cid, lid);
        }
        function postProgressUpdate(action, cid, lid) {
            fetchJSON(`${API_BASE}/progress.php`, {
                method: 'POST', headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ action, course_id: cid, lesson_id: lid })
            }).catch(() => { });
        }
        function refreshProgressUI() {
            renderCourseList(allCourses);
            // Also update summary if this is current course
            if (currentCourse) {
                updateCourseSummary(currentCourse, currentLessons.length);

                // Update lesson list sidebar icons locally without full re-render
                if (currentLessons.length) {
                    const items = lessonListEl.querySelectorAll('.sidebar-item');
                    items.forEach(item => {
                        const lid = Number(item.dataset.lessonId);
                        if (!lid) return;

                        const isVisited = isLessonVisited(currentCourse.id, lid);
                        const isCompleted = isLessonCompleted(currentCourse.id, lid);

                        // Re-generate icon
                        let statusIcon = isCompleted ? '<i class="bi bi-check-circle-fill text-success"></i>' :
                            (isVisited ? '<i class="bi bi-circle-half text-primary"></i>' : '<i class="bi bi-circle text-muted"></i>');

                        // Update icon container (first child)
                        const iconContainer = item.querySelector('.pt-1');
                        if (iconContainer) iconContainer.innerHTML = statusIcon;

                        // Update border class (toggle)
                        if (isCompleted) item.classList.add('border-success-subtle');
                        else item.classList.remove('border-success-subtle');
                    });
                }
            }
        }
        async function loadProgressStore() {
            try {
                const d = await fetchJSON(`${API_BASE}/progress.php`);
                (d.progress || []).forEach(r => {
                    const cid = Number(r.course_id);
                    if (!progressStore[cid]) progressStore[cid] = { visited: [], completed: [] };
                    if (r.visited) progressStore[cid].visited.push(Number(r.lesson_id));
                    if (r.completed) progressStore[cid].completed.push(Number(r.lesson_id));
                });
            } catch (e) { }
        }
        function clearPlayers() {
            players.forEach(p => p.destroy());
            players = [];
        }

        // Init
        loadSession();

        // Button Listeners
        if (cloudButton) cloudButton.addEventListener('click', () => window.location.href = ROUTE_CLOUD);
        if (adminButton) adminButton.addEventListener('click', () => window.location.href = ROUTE_ADMIN);

        // Logout
        logoutButton.addEventListener('click', async () => {
            await fetchJSON(`${API_BASE}/logout.php`, { method: 'POST' }).catch(() => { });
            window.location.href = ROUTE_LOGIN;
        });

    </script>
</body>

</html>