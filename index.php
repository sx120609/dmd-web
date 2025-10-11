<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <title>网课系统 - 录播课程</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
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
            display: flex;
            gap: 24px;
            padding: 24px;
            flex-wrap: wrap;
        }
        .panel {
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            padding: 16px 24px;
        }
        .course-list {
            flex: 1 1 280px;
            max-width: 340px;
        }
        .course-list h2,
        .course-content h2 {
            margin-top: 0;
            font-size: 18px;
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
        .course-content {
            flex: 2 1 480px;
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
        @media (max-width: 900px) {
            main {
                flex-direction: column;
            }
            .course-list,
            .course-content {
                max-width: none;
                width: 100%;
            }
        }
    </style>
</head>
<body>
<header>
    <h1>录播课程中心</h1>
    <p>该页面为前端界面，通过接口获取课程与课节信息。请确保后端提供相应 API。</p>
</header>
<main>
    <section class="panel course-list" aria-label="课程列表">
        <h2>全部课程</h2>
        <div id="courseMessage" class="message" role="alert"></div>
        <div id="courseLoading" class="loading" hidden>正在加载课程...</div>
        <ul id="courseList"></ul>
    </section>
    <section class="panel course-content" aria-live="polite">
        <h2 id="courseTitle">欢迎进入录播课程中心</h2>
        <p id="courseDescription" class="empty-state">请选择左侧的课程查看详细内容。</p>
        <div id="lessonContainer"></div>
    </section>
</main>
<script>
(function () {
    const API_BASE = 'api';
    const courseListEl = document.getElementById('courseList');
    const courseMessageEl = document.getElementById('courseMessage');
    const courseLoadingEl = document.getElementById('courseLoading');
    const courseTitleEl = document.getElementById('courseTitle');
    const courseDescriptionEl = document.getElementById('courseDescription');
    const lessonContainerEl = document.getElementById('lessonContainer');

    let currentCourseId = null;

    function setMessage(message, type = 'info') {
        if (!message) {
            courseMessageEl.style.display = 'none';
            courseMessageEl.textContent = '';
            courseMessageEl.classList.remove('error');
            return;
        }
        courseMessageEl.textContent = message;
        courseMessageEl.classList.toggle('error', type === 'error');
        courseMessageEl.style.display = 'block';
    }

    function setCourseContent(course) {
        if (!course) {
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

            const videoWrapper = document.createElement('div');
            videoWrapper.className = 'video';

            if (lesson.video_url) {
                if (/^https?:\/\//i.test(lesson.video_url)) {
                    const iframe = document.createElement('iframe');
                    iframe.src = lesson.video_url;
                    iframe.allowFullscreen = true;
                    iframe.referrerPolicy = 'no-referrer';
                    videoWrapper.appendChild(iframe);
                } else {
                    const video = document.createElement('video');
                    video.controls = true;
                    video.src = lesson.video_url;
                    videoWrapper.appendChild(video);
                }
            } else {
                const placeholder = document.createElement('div');
                placeholder.className = 'placeholder';
                placeholder.textContent = '尚未上传视频链接';
                videoWrapper.appendChild(placeholder);
            }

            wrapper.appendChild(videoWrapper);
            lessonContainerEl.appendChild(wrapper);
        });
    }

    async function fetchJSON(url) {
        const response = await fetch(url, {
            headers: {
                'Accept': 'application/json'
            }
        });

        if (!response.ok) {
            throw new Error('接口请求失败，状态码：' + response.status);
        }
        return response.json();
    }

    async function loadCourses() {
        courseLoadingEl.hidden = false;
        setMessage('');

        try {
            const data = await fetchJSON(`${API_BASE}/courses.php`);
            const courses = Array.isArray(data?.courses) ? data.courses : [];

            if (courses.length === 0) {
                courseListEl.innerHTML = '<li class="empty-state">暂无课程，请在后台添加课程。</li>';
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
            setMessage(error.message || '加载课程失败，请稍后重试。', 'error');
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
            setMessage(error.message || '加载课节失败，请稍后重试。', 'error');
        }
    }

    loadCourses();
})();
</script>
</body>
</html>
