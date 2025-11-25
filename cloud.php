<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>云盘 · Rare Light</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="assets/css/main.css">
    <style>
        body.cloud {
            background: radial-gradient(circle at 12% 18%, rgba(59, 130, 246, 0.08), transparent 25%),
                radial-gradient(circle at 80% 12%, rgba(45, 212, 191, 0.12), transparent 25%),
                #f8fafc;
        }
        .cloud-hero {
            padding: clamp(2.4rem, 6vw, 3.6rem) 0;
        }
        .cloud-hero .hero-panel {
            border-radius: 20px;
            background: linear-gradient(135deg, #1d4ed8, #60a5fa);
            color: #fff;
            box-shadow: 0 24px 80px rgba(37, 99, 235, 0.35);
        }
        .cloud-hero .hero-pill {
            background: rgba(255, 255, 255, 0.14);
            color: #e0f2fe;
        }
        .cloud-section {
            padding: 1.6rem 0 3rem;
        }
        .cloud-card {
            border: 1px solid rgba(148, 163, 184, 0.18);
            border-radius: 18px;
            background: rgba(255, 255, 255, 0.9);
            box-shadow: 0 18px 40px rgba(15, 23, 42, 0.08);
        }
        .file-list thead {
            color: #475569;
        }
        .file-actions button {
            min-width: 90px;
        }
        .pill-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            padding: 0.35rem 0.75rem;
            border-radius: 999px;
            font-weight: 600;
            background: rgba(226, 232, 240, 0.8);
            color: #0f172a;
        }
        .pill-badge.success {
            background: rgba(16, 185, 129, 0.16);
            color: #0f172a;
        }
        .pill-badge.muted {
            background: rgba(148, 163, 184, 0.2);
        }
        .upload-progress {
            display: flex;
            flex-direction: column;
            gap: 0.2rem;
        }
        @media (max-width: 960px) {
            .file-actions {
                display: grid;
                gap: 0.5rem;
            }
        }
    </style>
</head>
<body class="app-shell cloud">
<nav class="navbar navbar-expand-lg app-navbar">
    <div class="container-xxl py-3 px-3 px-lg-4 w-100 d-flex align-items-center gap-3 flex-wrap">
        <div class="d-flex align-items-center gap-3">
            <div class="brand-glow">RL</div>
            <div class="d-flex flex-column">
                <span class="brand-eyebrow text-uppercase">RARE LIGHT</span>
                <span class="navbar-brand p-0 m-0 fw-semibold">Rare Light 云盘</span>
            </div>
        </div>
        <div class="d-flex flex-wrap align-items-center gap-2 ms-auto">
            <div class="user-chip" id="userChip"></div>
            <a class="btn btn-outline-secondary btn-sm rounded-pill" href="/rarelight/">返回首页</a>
            <button class="btn btn-outline-secondary btn-sm rounded-pill" id="dashboardButton">返回课堂</button>
            <a class="btn btn-outline-primary btn-sm rounded-pill" href="/rarelight/admin">返回管理后台</a>
            <button class="btn btn-outline-danger btn-sm rounded-pill" id="logoutButton">退出登录</button>
        </div>
    </div>
</nav>

<section class="cloud-hero">
    <div class="container-xxl hero-container">
        <div class="hero-panel student-hero p-4 p-lg-5">
            <div class="hero-eyebrow">Rare Light · 云盘</div>
            <div class="hero-main">
                <div class="hero-copy">
                    <h1 class="hero-title mb-3">Rare Light·自主安全网盘</h1>
                    <p class="hero-subtitle mb-0">上传教学素材、生成外链、管理文件。外链以随机令牌保护，随时可关闭。</p>
                </div>
                <div class="hero-meta">
                    <span class="hero-pill">组织专属</span>
                    <span class="hero-pill soft">文件外链可控</span>
                </div>
            </div>
        </div>
    </div>
</section>

<main class="cloud-section">
    <div class="container-xxl d-flex flex-column gap-4">
        <div class="row g-4">
            <div class="col-12 col-lg-5">
                <div class="cloud-card p-4 h-100">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <div>
                            <p class="text-uppercase small text-secondary mb-1">上传文件</p>
                            <h4 class="mb-0">添加新文件</h4>
                        </div>
                        <span class="pill-badge muted" id="quotaInfo">单文件上限 1GB</span>
                    </div>
                    <form id="uploadForm" class="d-flex flex-column gap-3">
                        <div>
                            <label for="fileInput" class="form-label">选择文件</label>
                            <input class="form-control" type="file" id="fileInput" name="file" multiple required>
                            <div class="form-text">可批量选择文件，单个文件上限 1GB。</div>
                        </div>
                        <div class="upload-progress" hidden id="uploadProgressWrap">
                            <div class="progress" style="height: 10px;">
                                <div class="progress-bar" role="progressbar" id="uploadProgressBar" style="width: 0%;" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div>
                            </div>
                            <div class="small text-secondary" id="uploadProgressText"></div>
                        </div>
                        <button type="submit" class="primary-button" id="uploadButton">上传文件</button>
                        <div class="message" id="uploadMessage" hidden></div>
                    </form>
                </div>
            </div>
            <div class="col-12 col-lg-7">
                <div class="cloud-card p-4 h-100">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <div>
                            <p class="text-uppercase small text-secondary mb-1">文件概览</p>
                            <h4 class="mb-0">我的文件</h4>
                        </div>
                        <span class="pill-badge success" id="fileCount">0 个文件</span>
                    </div>
                    <div class="table-responsive">
                        <table class="table align-middle file-list">
                            <thead>
                            <tr>
                                <th>名称</th>
                                <th>大小</th>
                                <th>外链</th>
                                <th class="text-end">操作</th>
                            </tr>
                            </thead>
                            <tbody id="fileTableBody">
                            <tr><td colspan="4" class="text-secondary text-center py-4">正在加载...</td></tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mt-3" id="paginationControls" hidden>
                        <div class="d-flex align-items-center gap-2">
                            <button class="btn btn-sm btn-outline-secondary" id="prevPage">上一页</button>
                            <button class="btn btn-sm btn-outline-secondary" id="nextPage">下一页</button>
                        </div>
                        <div class="small text-secondary" id="pageSummary"></div>
                    </div>
                    <div class="message" id="listMessage" hidden></div>
                </div>
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
    const CHUNK_SIZE = 20 * 1024 * 1024; // 20MB 分片，提升进度感知与续传体验
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

    const PAGE_SIZE = 10;
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
        const initResp = await fetchJSON(`${filesMultipartEndpoint}`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                action: 'init',
                upload_id: uploadId,
                filename: file.name,
                size_bytes: file.size,
                mime_type: file.type || 'application/octet-stream',
                chunk_size: CHUNK_SIZE
            })
        });
        const uploadedChunks = Array.isArray(initResp.uploaded_chunks) ? initResp.uploaded_chunks : [];
        const totalChunks = Math.max(1, Math.ceil(file.size / CHUNK_SIZE));
        let uploadedBytes = uploadedChunks.reduce((acc, cur) => {
            const start = cur * CHUNK_SIZE;
            const end = Math.min(file.size, (cur + 1) * CHUNK_SIZE);
            return acc + Math.max(0, end - start);
        }, 0);
        const startTime = performance.now();
        const missing = [];
        for (let i = 0; i < totalChunks; i += 1) {
            if (!uploadedChunks.includes(i)) {
                missing.push(i);
            }
        }

        const concurrency = 3;
        let cursor = 0;
        let lastProgressBytes = uploadedBytes;
        let lastProgressTime = startTime;

        const uploadChunk = async (chunkIndex) => {
            const start = chunkIndex * CHUNK_SIZE;
            const end = Math.min(file.size, start + CHUNK_SIZE);
            const blob = file.slice(start, end);
            const resp = await fetch(`${filesMultipartEndpoint}?action=chunk&upload_id=${encodeURIComponent(uploadId)}&index=${chunkIndex}`, {
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
            const speed = formatSpeed(uploadedBytes - lastProgressBytes, now - lastProgressTime);
            lastProgressBytes = uploadedBytes;
            lastProgressTime = now;
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
        const statusResp = await fetchJSON(`${filesMultipartEndpoint}`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                action: 'status',
                upload_id: uploadId
            })
        });
        const finalUploaded = Array.isArray(statusResp.uploaded_chunks) ? statusResp.uploaded_chunks.length : 0;
        if (finalUploaded < totalChunks) {
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
                        <button class="btn btn-sm btn-outline-primary" data-action="toggle">${file.is_public ? '关闭外链' : '开启外链'}</button>
                        <button class="btn btn-sm btn-outline-secondary" data-action="copy"${file.is_public ? '' : ' disabled'}>复制外链</button>
                        <a class="btn btn-sm btn-outline-success" href="${downloadUrl}" target="_blank" rel="noopener noreferrer">下载</a>
                        <button class="btn btn-sm btn-outline-danger" data-action="delete">删除</button>
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
