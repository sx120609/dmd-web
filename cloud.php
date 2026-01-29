<!DOCTYPE html>
<html lang="zh-CN">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>云盘 · Rare Light</title>
    <link rel="icon" type="image/svg+xml" href="/rarelight/favicon.svg">
    <link rel="shortcut icon" href="/rarelight/favicon.svg">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

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
            --header-height: 70px;
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

        .nav-btn-primary {
            background: var(--rl-primary);
            color: white;
            box-shadow: 0 4px 12px rgba(59, 130, 246, 0.25);
            border: 1px solid transparent;
        }

        .nav-btn-primary:hover {
            background: #2563eb;
            /* Darker blue */
            color: white;
            transform: translateY(-1px);
            box-shadow: 0 6px 16px rgba(59, 130, 246, 0.35);
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

        /* --- 页面内容 --- */
        .page-container {
            padding: 2rem 0 4rem;
        }

        .panel-glass {
            background: var(--glass-bg);
            backdrop-filter: blur(12px);
            border: var(--glass-border);
            border-radius: 16px;
            box-shadow: var(--glass-shadow);
            display: flex;
            flex-direction: column;
            overflow: hidden;
        }

        .panel-header {
            padding: 1.25rem 1.5rem;
            border-bottom: 1px solid rgba(0, 0, 0, 0.04);
            background: rgba(255, 255, 255, 0.4);
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 1rem;
        }

        .panel-title {
            font-size: 1.1rem;
            font-weight: 700;
            margin: 0;
            display: flex;
            align-items: center;
            gap: 8px;
            color: var(--rl-text-main);
        }

        .panel-body {
            padding: 1.5rem;
        }

        .rl-badge {
            font-size: 0.75rem;
            padding: 4px 10px;
            border-radius: 6px;
            font-weight: 600;
            background: rgba(0, 0, 0, 0.04);
            color: var(--rl-text-muted);
        }

        .rl-badge.success {
            background: rgba(16, 185, 129, 0.1);
            color: #10b981;
        }

        /* 表格样式优化 */
        .table {
            --bs-table-bg: transparent;
        }

        .file-list th {
            font-size: 0.8rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: var(--rl-text-muted);
            border-bottom: 1px solid rgba(0, 0, 0, 0.05);
            padding-bottom: 1rem;
        }

        .file-list td {
            padding: 1rem 0.5rem;
            border-bottom: 1px solid rgba(0, 0, 0, 0.03);
            color: var(--rl-text-main);
            font-size: 0.95rem;
        }

        .file-name {
            font-weight: 600;
        }

        .file-meta {
            font-size: 0.8rem;
            color: var(--rl-text-muted);
        }

        /* 进度条 */
        .progress {
            background: rgba(0, 0, 0, 0.05);
            border-radius: 99px;
            overflow: hidden;
        }

        @media (max-width: 992px) {
            .nav-actions {
                display: none;
            }
        }
    </style>
</head>

<body class="cloud-shell">
    <nav class="site-nav">
        <div class="container-xxl d-flex align-items-center justify-content-between w-100">
            <a href="/rarelight/" class="nav-brand">
                <span class="brand-mark">RL</span>
                <div class="brand-text">
                    <div class="small text-uppercase">RARE LIGHT</div>
                    <div class="fw-bold">云盘 · Cloud</div>
                </div>
            </a>

            <div class="nav-actions d-none d-md-flex align-items-center gap-2">
                <div class="user-chip me-2" id="userChip" style="display:none"></div>

                <a class="nav-btn nav-btn-ghost" href="/rarelight/">
                    <i class="bi bi-house"></i> 首页
                </a>
                <button class="nav-btn nav-btn-primary" id="dashboardButton">
                    <i class="bi bi-collection-play-fill me-1"></i> 返回课堂
                </button>
                <button class="nav-btn nav-btn-ghost text-danger" id="logoutButton" title="退出登录">
                    <i class="bi bi-box-arrow-right"></i>
                </button>
            </div>
        </div>
    </nav>

    <!-- Hero Removed in favor of clean layout -->

    <main class="page-container">
        <div class="container-xxl d-flex flex-column gap-4">

            <div class="d-flex align-items-center justify-content-between mb-2">
                <h2 class="fw-bold m-0"><i class="bi bi-cloud me-2 text-primary"></i>云盘管理</h2>
            </div>

            <div class="row g-4">
                <div class="col-12 col-lg-5">
                    <div class="panel-glass h-100">
                        <div class="panel-header">
                            <h3 class="panel-title"><i class="bi bi-cloud-upload text-primary"></i> 上传文件</h3>
                            <span class="rl-badge">分片上传</span>
                        </div>
                        <div class="panel-body">
                            <form id="uploadForm" class="d-flex flex-column gap-3">
                                <div>
                                    <label for="fileInput" class="form-label small fw-bold text-muted">选择文件</label>
                                    <input class="form-control" type="file" id="fileInput" name="file" multiple
                                        required>
                                    <div class="form-text">可批量选择文件，分片上传，断点可重连。</div>
                                </div>
                                <div class="upload-progress" hidden id="uploadProgressWrap">
                                    <div class="progress" style="height: 6px;">
                                        <div class="progress-bar" role="progressbar" id="uploadProgressBar"
                                            style="width: 0%;" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100">
                                        </div>
                                    </div>
                                    <div class="small text-secondary mt-1" id="uploadProgressText"></div>
                                </div>
                                <button type="submit" class="nav-btn nav-btn-primary w-100 justify-content-center py-2"
                                    id="uploadButton">
                                    <i class="bi bi-cloud-arrow-up"></i> 开始上传
                                </button>
                                <div class="message small mt-2" id="uploadMessage" hidden></div>
                            </form>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-lg-7">
                    <div class="panel-glass h-100">
                        <div class="panel-header">
                            <h3 class="panel-title"><i class="bi bi-folder2-open text-primary"></i> 我的文件</h3>
                            <span class="rl-badge success" id="fileCount">0 个文件</span>
                        </div>
                        <div class="panel-body p-0">
                            <div class="table-responsive">
                                <table class="table align-middle file-list mb-0">
                                    <thead class="bg-light bg-opacity-50">
                                        <tr>
                                            <th class="ps-4">名称</th>
                                            <th>大小</th>
                                            <th>外链</th>
                                            <th class="text-end pe-4">操作</th>
                                        </tr>
                                    </thead>
                                    <tbody id="fileTableBody">
                                        <tr>
                                            <td colspan="4" class="text-secondary text-center py-5">正在加载...</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div class="panel-footer p-3 border-top border-light d-flex align-items-center justify-content-between"
                            id="paginationControls" hidden>
                            <div class="d-flex align-items-center gap-2">
                                <button class="nav-btn nav-btn-outline py-1 px-2 small" id="prevPage">上一页</button>
                                <button class="nav-btn nav-btn-outline py-1 px-2 small" id="nextPage">下一页</button>
                            </div>
                            <div class="small text-muted" id="pageSummary"></div>
                        </div>
                    </div>
                    <div class="message mt-2" id="listMessage" hidden></div>
                </div>
            </div>
        </div>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const BASE_PATH = '/rarelight';
        const API_BASE = `${BASE_PATH}/api`;
        const sessionEndpoint = `${API_BASE}/session.php`;
        const filesEndpoint = `${API_BASE}/files.php`;
        const filesMultipartEndpoint = `${API_BASE}/files_multipart.php`;
        const ROUTE_LOGIN = `${BASE_PATH}/login`;
        const ROUTE_DASHBOARD = `${BASE_PATH}/dashboard`;

        function withBasePath(path = '') {
            if (!path) return '';
            if (/^https?:\/\//i.test(path)) return path;
            const normalized = path.startsWith('/') ? path : `/${path}`;
            if (normalized.startsWith(`${BASE_PATH}/`)) return normalized;
            return `${BASE_PATH}${normalized}`.replace(/\/{2,}/g, '/');
        }

        function buildAbsoluteUrl(path = '') {
            if (!path) return '';
            if (/^https?:\/\//i.test(path)) return path;
            return `${window.location.origin}${withBasePath(path)}`;
        }

        const fileTableBody = document.getElementById('fileTableBody');
        const fileCountEl = document.getElementById('fileCount');
        const listMessage = document.getElementById('listMessage');
        const uploadForm = document.getElementById('uploadForm');
        const fileInput = document.getElementById('fileInput');
        const uploadButton = document.getElementById('uploadButton');
        const uploadMessage = document.getElementById('uploadMessage');
        const userChip = document.getElementById('userChip');
        const logoutButton = document.getElementById('logoutButton');
        const dashboardButton = document.getElementById('dashboardButton');
        const uploadProgressWrap = document.getElementById('uploadProgressWrap');
        const uploadProgressBar = document.getElementById('uploadProgressBar');
        const uploadProgressText = document.getElementById('uploadProgressText');
        const paginationControls = document.getElementById('paginationControls');
        const prevPageButton = document.getElementById('prevPage');
        const nextPageButton = document.getElementById('nextPage');
        const pageSummary = document.getElementById('pageSummary');

        const PAGE_SIZE = 6;
        let paginationState = {
            page: 1,
            per_page: PAGE_SIZE,
            total: 0,
            total_pages: 1
        };

        function normalizeApiUrl(url) {
            if (url.startsWith(`${API_BASE}/`)) {
                const [path, query] = url.split('?');
                const sanitizedPath = path.replace(/\/{2,}/g, '/');
                return query ? `${sanitizedPath}?${query}` : sanitizedPath;
            }
            return url;
        }

        function setMessage(el, text = '', type = '') {
            if (!el) return;
            el.textContent = text || '';
            el.classList.remove('error', 'success');
            const hasText = Boolean(text);
            el.hidden = !hasText;
            if (hasText && type) {
                el.classList.add(type);
            }
        }

        function formatSize(bytes) {
            if (!bytes) return '0 B';
            const units = ['B', 'KB', 'MB', 'GB', 'TB'];
            const i = Math.min(units.length - 1, Math.floor(Math.log(bytes) / Math.log(1024)));
            const size = bytes / Math.pow(1024, i);
            return `${size.toFixed(size >= 10 || i === 0 ? 0 : 1)} ${units[i]}`;
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

        function setUploadProgress(value, text = '') {
            if (!uploadProgressWrap || !uploadProgressBar) return;
            const pct = Math.min(100, Math.max(0, Math.round(value)));
            uploadProgressBar.style.width = `${pct}%`;
            uploadProgressBar.setAttribute('aria-valuenow', String(pct));
            if (uploadProgressText) {
                uploadProgressText.textContent = text || `${pct}%`;
            }
            uploadProgressWrap.hidden = false;
        }

        function resetUploadProgress() {
            if (uploadProgressWrap) {
                uploadProgressWrap.hidden = true;
            }
            if (uploadProgressBar) {
                uploadProgressBar.style.width = '0%';
                uploadProgressBar.setAttribute('aria-valuenow', '0');
            }
            if (uploadProgressText) {
                uploadProgressText.textContent = '';
            }
        }

        async function hashString(input) {
            if (!window.crypto || !crypto.subtle) {
                return btoa(input).replace(/=+$/, '');
            }
            const encoder = new TextEncoder();
            const data = encoder.encode(input);
            const digest = await crypto.subtle.digest('SHA-1', data);
            return Array.from(new Uint8Array(digest)).map((b) => b.toString(16).padStart(2, '0')).join('');
        }

        async function computeUploadId(file) {
            const base = `${file.name}|${file.size}|${file.lastModified || 0}`;
            const hash = await hashString(base);
            return `${hash}-${file.size}`;
        }

        function pickChunkSize(sizeBytes) {
            if (sizeBytes < 200 * 1024 * 1024) {
                return 2 * 1024 * 1024; // 2MB
            }
            if (sizeBytes <= 2 * 1024 * 1024 * 1024) {
                return 5 * 1024 * 1024; // 5MB
            }
            return 10 * 1024 * 1024; // 10MB
        }

        function formatSpeed(bytes, elapsedMs) {
            if (!elapsedMs) return '';
            const bytesPerSec = bytes / (elapsedMs / 1000);
            const units = ['B/s', 'KB/s', 'MB/s', 'GB/s'];
            const idx = Math.min(units.length - 1, Math.floor(Math.log(bytesPerSec) / Math.log(1024)));
            const val = bytesPerSec / (1024 ** idx);
            return `${val.toFixed(val >= 10 || idx === 0 ? 0 : 1)} ${units[idx]}`;
        }

        async function uploadFileChunked(file, index, totalFiles) {
            const uploadId = await computeUploadId(file);
            const chunkSize = pickChunkSize(file.size);
            const initResp = await fetchJSON(`${filesMultipartEndpoint}`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    action: 'init',
                    upload_id: uploadId,
                    filename: file.name,
                    size_bytes: file.size,
                    mime_type: file.type || 'application/octet-stream',
                    chunk_size: chunkSize
                })
            });
            const uploadedChunks = Array.isArray(initResp.uploaded_chunks) ? initResp.uploaded_chunks : [];
            const totalChunks = Math.max(1, Math.ceil(file.size / chunkSize));
            let uploadedBytes = uploadedChunks.reduce((acc, cur) => {
                const start = cur * chunkSize;
                const end = Math.min(file.size, (cur + 1) * chunkSize);
                return acc + Math.max(0, end - start);
            }, 0);
            const startTime = performance.now();
            const missing = [];
            for (let i = 0; i < totalChunks; i += 1) {
                if (!uploadedChunks.includes(i)) {
                    missing.push(i);
                }
            }

            const concurrency = 6;
            let cursor = 0;

            const uploadChunk = async (chunkIndex) => {
                const start = chunkIndex * chunkSize;
                const end = Math.min(file.size, start + chunkSize);
                const blob = file.slice(start, end);
                const resp = await fetch(`${filesMultipartEndpoint}?action=chunk&upload_id=${encodeURIComponent(uploadId)}&index=${chunkIndex}&size=${blob.size}&total_chunks=${totalChunks}`, {
                    method: 'POST',
                    credentials: 'include',
                    body: blob,
                    headers: {
                        'Content-Type': 'application/octet-stream'
                    }
                });
                if (!resp.ok) {
                    let msg = `分片 ${chunkIndex} 上传失败（${resp.status}）`;
                    try {
                        const data = await resp.json();
                        if (data && (data.error || data.message)) {
                            msg = data.error || data.message;
                        }
                    } catch (e) { /* ignore */ }
                    throw new Error(msg);
                }
                uploadedBytes += blob.size;
                const now = performance.now();
                const pct = Math.round((uploadedBytes / file.size) * 100);
                const speed = formatSpeed(uploadedBytes, now - startTime); // 平均速度
                setUploadProgress(pct, `文件 ${index + 1}/${totalFiles} · ${pct}%${speed ? ` · ${speed}` : ''}`);
            };

            const workers = Array.from({ length: concurrency }).map(async () => {
                while (cursor < missing.length) {
                    const current = cursor;
                    cursor += 1;
                    await uploadChunk(missing[current]);
                }
            });
            await Promise.all(workers);

            // 二次校验已传分片再合并
            const ensureAllChunks = async () => {
                const statusResp = await fetchJSON(`${filesMultipartEndpoint}`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        action: 'status',
                        upload_id: uploadId
                    })
                });
                const uploadedList = Array.isArray(statusResp.uploaded_chunks) ? statusResp.uploaded_chunks : [];
                if (uploadedList.length >= totalChunks) {
                    return [];
                }
                const missingAgain = [];
                for (let i = 0; i < totalChunks; i += 1) {
                    if (!uploadedList.includes(i)) {
                        missingAgain.push(i);
                    }
                }
                return missingAgain;
            };

            const missingAgain = await ensureAllChunks();
            if (missingAgain.length) {
                cursor = 0;
                const retry = Array.from({ length: concurrency }).map(async () => {
                    while (cursor < missingAgain.length) {
                        const current = cursor;
                        cursor += 1;
                        await uploadChunk(missingAgain[current]);
                    }
                });
                await Promise.all(retry);
            }

            const finalMissing = await ensureAllChunks();
            if (finalMissing.length) {
                throw new Error('分片未传完，请重试');
            }

            const completeResp = await fetchJSON(`${filesMultipartEndpoint}`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                credentials: 'include',
                body: JSON.stringify({
                    action: 'complete',
                    upload_id: uploadId,
                    filename: file.name,
                    size_bytes: file.size,
                    mime_type: file.type || 'application/octet-stream',
                    total_chunks: totalChunks
                })
            });
            return completeResp;
        }

        function renderFiles(files = []) {
            fileTableBody.innerHTML = '';
            if (!files.length) {
                fileTableBody.innerHTML = '<tr><td colspan="4" class="text-secondary text-center py-4">暂无文件，上传后即可在此管理。</td></tr>';
            }
            const totalText = paginationState.total || files.length;
            fileCountEl.textContent = `${totalText} 个文件`;
            files.forEach((file) => {
                const tr = document.createElement('tr');
                const status = file.is_public ? '<span class="badge text-bg-success-subtle text-success-emphasis">已开启</span>' : '<span class="badge text-bg-secondary">关闭</span>';
                const downloadUrl = buildAbsoluteUrl(file.download_url);
                tr.innerHTML = `
                <td>
                    <div class="fw-semibold">${file.original_name}</div>
                    <div class="text-secondary small">${file.mime_type || '未知类型'} · 上传于 ${file.created_at}</div>
                </td>
                <td>${formatSize(file.size_bytes)}</td>
                <td>${status}</td>
                <td class="text-end">
                    <div class="file-actions d-inline-flex flex-wrap justify-content-end gap-2">
                        <button class="nav-btn nav-btn-outline py-1 px-2 small" data-action="toggle">
                            <i class="bi bi-link-45deg"></i> ${file.is_public ? '关闭' : '开启'}
                        </button>
                        <button class="nav-btn nav-btn-outline py-1 px-2 small" data-action="copy"${file.is_public ? '' : ' disabled'}>
                            <i class="bi bi-clipboard"></i>
                        </button>
                        <a class="nav-btn nav-btn-outline py-1 px-2 small text-success border-success-subtle" href="${downloadUrl}" target="_blank" rel="noopener noreferrer">
                            <i class="bi bi-download"></i>
                        </a>
                        <button class="nav-btn nav-btn-outline py-1 px-2 small text-danger border-danger-subtle" data-action="delete">
                            <i class="bi bi-trash"></i>
                        </button>
                    </div>
                </td>
            `;
                tr.querySelector('[data-action="toggle"]').addEventListener('click', () => toggleShare(file));
                tr.querySelector('[data-action="copy"]').addEventListener('click', () => copyShare(file));
                tr.querySelector('[data-action="delete"]').addEventListener('click', () => deleteFile(file));
                fileTableBody.appendChild(tr);
            });
        }

        function updatePaginationControls() {
            const { page, total_pages: totalPages, total } = paginationState;
            if (!paginationControls) return;
            const shouldShow = totalPages > 1;
            paginationControls.hidden = !shouldShow;
            if (pageSummary) {
                pageSummary.textContent = `第 ${page} / ${Math.max(totalPages, 1)} 页 · 共 ${total} 个文件`;
            }
            if (prevPageButton) {
                prevPageButton.disabled = page <= 1;
            }
            if (nextPageButton) {
                nextPageButton.disabled = page >= totalPages;
            }
        }

        async function loadFiles(targetPage = paginationState.page || 1) {
            setMessage(listMessage);
            try {
                const safePage = Math.max(1, Number(targetPage) || 1);
                const data = await fetchJSON(`${filesEndpoint}?page=${safePage}&per_page=${PAGE_SIZE}`);
                if (data.pagination && data.pagination.total_pages && safePage > data.pagination.total_pages && data.pagination.total_pages > 0) {
                    return loadFiles(data.pagination.total_pages);
                }
                paginationState = {
                    page: data.pagination?.page || safePage,
                    per_page: data.pagination?.per_page || PAGE_SIZE,
                    total: data.pagination?.total ?? (data.files ? data.files.length : 0),
                    total_pages: data.pagination?.total_pages || 1
                };
                renderFiles(data.files || []);
                updatePaginationControls();
            } catch (error) {
                setMessage(listMessage, error.message || '文件列表获取失败', 'error');
                fileTableBody.innerHTML = '<tr><td colspan="4" class="text-secondary text-center py-4">文件列表加载失败</td></tr>';
                if (paginationControls) {
                    paginationControls.hidden = true;
                }
            }
        }

        async function toggleShare(file) {
            try {
                setMessage(listMessage, '正在更新外链状态...');
                const data = await fetchJSON(filesEndpoint, {
                    method: 'POST', // 兼容防火墙拦截 PATCH
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ id: file.id, is_public: !file.is_public, _method: 'PATCH' })
                });
                setMessage(listMessage, '已更新外链状态', 'success');
                const updated = data.file;
                await loadFiles(paginationState.page);
                return updated;
            } catch (error) {
                setMessage(listMessage, error.message || '更新失败', 'error');
            }
        }

        async function deleteFile(file) {
            if (!window.confirm(`确定删除「${file.original_name}」吗？`)) {
                return;
            }
            try {
                setMessage(listMessage, '正在删除文件...');
                await fetchJSON(filesEndpoint, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ action: 'delete', id: file.id })
                });
                setMessage(listMessage, '文件已删除', 'success');
                await loadFiles(Math.max(1, paginationState.page));
            } catch (error) {
                setMessage(listMessage, error.message || '删除失败', 'error');
            }
        }

        async function copyShare(file) {
            if (!file.is_public) {
                setMessage(listMessage, '请先开启外链再复制链接', 'error');
                return;
            }
            const link = buildAbsoluteUrl(file.share_url);
            try {
                await navigator.clipboard.writeText(link);
                setMessage(listMessage, '外链已复制', 'success');
            } catch (error) {
                setMessage(listMessage, '无法复制外链，请手动复制', 'error');
            }
        }

        uploadForm.addEventListener('submit', async (event) => {
            event.preventDefault();
            const files = fileInput.files ? Array.from(fileInput.files) : [];
            if (!files.length) {
                setMessage(uploadMessage, '请选择文件', 'error');
                return;
            }
            uploadButton.disabled = true;
            resetUploadProgress();
            setMessage(uploadMessage, `正在上传 ${files.length} 个文件，请稍候...`);
            const total = files.length;

            try {
                for (let i = 0; i < files.length; i += 1) {
                    await uploadFileChunked(files[i], i, total);
                }
                setMessage(uploadMessage, '全部上传成功', 'success');
                resetUploadProgress();
                uploadForm.reset();
                await loadFiles(paginationState.page || 1);
            } catch (error) {
                setMessage(uploadMessage, error.message || '上传失败', 'error');
                resetUploadProgress();
            } finally {
                uploadButton.disabled = false;
            }
        });

        logoutButton.addEventListener('click', async () => {
            try {
                await fetchJSON(`${API_BASE}/logout.php`, { method: 'POST' });
            } catch (error) {
                console.error(error);
            }
            window.location.href = ROUTE_LOGIN;
        });

        dashboardButton.addEventListener('click', () => {
            window.location.href = ROUTE_DASHBOARD;
        });

        if (prevPageButton) {
            prevPageButton.addEventListener('click', () => {
                const targetPage = Math.max(1, (paginationState.page || 1) - 1);
                loadFiles(targetPage);
            });
        }

        if (nextPageButton) {
            nextPageButton.addEventListener('click', () => {
                const targetPage = Math.min((paginationState.total_pages || 1), (paginationState.page || 1) + 1);
                loadFiles(targetPage);
            });
        }

        async function loadSession() {
            try {
                const data = await fetchJSON(sessionEndpoint);
                if (!data.user || (data.user.role !== 'admin' && data.user.role !== 'teacher')) {
                    window.location.href = ROUTE_LOGIN;
                    return;
                }
                const name = data.user.display_name || data.user.username || '';
                const roleLabel = data.user.role === 'teacher' ? '老师' : '管理员';
                userChip.textContent = `${name} · ${roleLabel}`;
                userChip.style.display = 'inline-flex';
                await loadFiles(1);
            } catch (error) {
                window.location.href = ROUTE_LOGIN;
            }
        }

        loadSession();
    </script>
</body>

</html>