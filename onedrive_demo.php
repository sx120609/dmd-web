<!DOCTYPE html>
<html lang="zh-CN">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>OneDrive / SharePoint Demo · Rare Light</title>
    <link rel="icon" type="image/svg+xml" href="/rarelight/favicon.svg">
    <link rel="shortcut icon" href="/rarelight/favicon.svg">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        body {
            min-height: 100vh;
            background: #f8fafc;
            color: #0f172a;
            font-family: system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
        }

        .site-nav {
            position: sticky;
            top: 0;
            z-index: 10;
            background: rgba(255, 255, 255, .92);
            border-bottom: 1px solid #e2e8f0;
            backdrop-filter: blur(16px);
        }

        .panel {
            background: #fff;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            box-shadow: 0 10px 30px rgba(15, 23, 42, .05);
        }

        .mono-box {
            min-height: 180px;
            max-height: 420px;
            overflow: auto;
            background: #0f172a;
            color: #e2e8f0;
            border-radius: 8px;
            padding: 16px;
            font: 13px/1.55 ui-monospace, SFMono-Regular, Consolas, "Liberation Mono", monospace;
            white-space: pre-wrap;
        }

        .small-label {
            font-size: .78rem;
            font-weight: 700;
            color: #64748b;
            text-transform: uppercase;
        }
    </style>
</head>

<body>
    <nav class="site-nav py-3">
        <div class="container-xxl d-flex align-items-center justify-content-between gap-3">
            <a class="text-decoration-none fw-bold text-dark d-inline-flex align-items-center gap-2" href="/rarelight/cloud">
                <i class="bi bi-cloud"></i>
                云盘
            </a>
            <div class="d-flex gap-2">
                <a class="btn btn-sm btn-outline-secondary" href="/rarelight/dashboard">控制台</a>
                <a class="btn btn-sm btn-outline-secondary" href="/rarelight/cloud">返回云盘</a>
            </div>
        </div>
    </nav>

    <main class="container-xxl py-4">
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-3 mb-4">
            <div>
                <h1 class="h3 fw-bold mb-1">OneDrive / SharePoint 接入 Demo</h1>
                <div class="text-secondary">独立测试页，只验证 Microsoft Graph 授权和文件接口，不替换当前云盘存储。</div>
            </div>
            <span class="badge text-bg-light border" id="loginState">正在检测登录状态</span>
        </div>

        <div class="row g-4">
            <section class="col-12 col-xl-5">
                <div class="panel p-4 h-100">
                    <div class="d-flex align-items-center justify-content-between gap-3 mb-3">
                        <h2 class="h5 fw-bold mb-0">授权配置</h2>
                        <button class="btn btn-sm btn-outline-secondary" id="refreshConfigButton">
                            <i class="bi bi-arrow-clockwise"></i>
                        </button>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small-label" for="cloudSelect">云环境</label>
                        <select class="form-select" id="cloudSelect">
                            <option value="global">国际版</option>
                            <option value="china">世纪互联</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small-label">Azure Portal 回调地址</label>
                        <div class="input-group">
                            <input class="form-control" id="callbackUrl" readonly>
                            <button class="btn btn-outline-secondary" id="copyCallbackButton" type="button">
                                <i class="bi bi-copy"></i>
                            </button>
                        </div>
                    </div>

                    <div class="d-flex gap-2 flex-wrap mb-4">
                        <button class="btn btn-primary" id="authButton">
                            <i class="bi bi-microsoft"></i>
                            获取授权链接
                        </button>
                        <button class="btn btn-outline-danger" id="disconnectButton">
                            <i class="bi bi-x-circle"></i>
                            清除 demo token
                        </button>
                    </div>

                    <div class="border rounded p-3 bg-light">
                        <div class="small-label mb-2">配置状态</div>
                        <div id="configSummary" class="small text-secondary">尚未加载</div>
                    </div>
                </div>
            </section>

            <section class="col-12 col-xl-7">
                <div class="panel p-4 h-100">
                    <h2 class="h5 fw-bold mb-3">Graph 测试</h2>
                    <div class="row g-3">
                        <div class="col-12 col-md-6">
                            <label class="form-label small-label" for="pathInput">OneDrive 目录</label>
                            <input class="form-control" id="pathInput" placeholder="留空表示根目录">
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label small-label" for="siteUrlInput">SharePoint Site URL</label>
                            <input class="form-control" id="siteUrlInput" placeholder="https://tenant.sharepoint.com/sites/demo">
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label small-label" for="siteIdInput">SharePoint Site ID</label>
                            <input class="form-control" id="siteIdInput" placeholder="查询 Site 后自动填入">
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label small-label" for="uploadNameInput">上传会话文件名</label>
                            <input class="form-control" id="uploadNameInput" placeholder="demo.txt">
                        </div>
                        <div class="col-12">
                            <label class="form-label small-label" for="demoFileInput">简单上传测试文件</label>
                            <input class="form-control" id="demoFileInput" type="file">
                            <div class="form-text">用于 Graph 简单上传，限制 4MB 以内；填入 Site ID 时上传到 SharePoint，否则上传到当前账号 OneDrive。</div>
                        </div>
                    </div>

                    <div class="d-flex gap-2 flex-wrap mt-4">
                        <button class="btn btn-outline-primary" data-test="me_drive">读取默认 Drive</button>
                        <button class="btn btn-outline-primary" data-test="list">列出 OneDrive 目录</button>
                        <button class="btn btn-outline-primary" data-test="site">查询 SharePoint Site</button>
                        <button class="btn btn-outline-primary" data-test="list_site">列出 SharePoint 根目录</button>
                        <button class="btn btn-outline-primary" data-test="upload_session">创建上传会话</button>
                        <button class="btn btn-outline-primary" data-test="simple_upload">简单上传</button>
                    </div>
                </div>
            </section>

            <section class="col-12">
                <div class="panel p-4">
                    <div class="d-flex align-items-center justify-content-between gap-3 mb-3">
                        <h2 class="h5 fw-bold mb-0">响应</h2>
                        <button class="btn btn-sm btn-outline-secondary" id="clearOutputButton">清空</button>
                    </div>
                    <pre class="mono-box mb-0" id="output">等待操作...</pre>
                </div>
            </section>
        </div>
    </main>

    <script>
        const BASE_PATH = '/rarelight';
        const API = `${BASE_PATH}/api/onedrive_demo.php`;

        const output = document.getElementById('output');
        const loginState = document.getElementById('loginState');
        const cloudSelect = document.getElementById('cloudSelect');
        const callbackUrl = document.getElementById('callbackUrl');
        const configSummary = document.getElementById('configSummary');
        const siteIdInput = document.getElementById('siteIdInput');

        let config = null;

        function browserCallbackUrl() {
            const currentBase = window.location.pathname.replace(/\/onedrive-demo\/?$/, '');
            return `${window.location.origin}${currentBase}/api/onedrive_demo.php?action=callback`;
        }

        function print(data) {
            output.textContent = typeof data === 'string' ? data : JSON.stringify(data, null, 2);
        }

        async function api(action, options = {}) {
            const cloud = cloudSelect.value;
            const method = options.method || 'GET';
            const url = new URL(API, window.location.origin);
            url.searchParams.set('action', action);
            url.searchParams.set('cloud', cloud);
            if (['config', 'auth_url'].includes(action)) {
                url.searchParams.set('redirect_uri', browserCallbackUrl());
            }
            Object.entries(options.query || {}).forEach(([key, value]) => {
                if (value !== undefined && value !== null && value !== '') url.searchParams.set(key, value);
            });
            const response = await fetch(url.toString(), {
                method,
                headers: options.body ? { 'Content-Type': 'application/json' } : undefined,
                body: options.body ? JSON.stringify(options.body) : undefined,
                credentials: 'same-origin',
            });
            const text = await response.text();
            let data;
            try {
                data = JSON.parse(text);
            } catch {
                data = text;
            }
            if (!response.ok) {
                throw data;
            }
            return data;
        }

        async function uploadDemoFile() {
            const cloud = cloudSelect.value;
            const file = document.getElementById('demoFileInput').files[0];
            if (!file) throw { error: '请选择简单上传测试文件' };
            const url = new URL(API, window.location.origin);
            url.searchParams.set('action', 'simple_upload');
            url.searchParams.set('cloud', cloud);
            const form = new FormData();
            form.append('file', file);
            form.append('filename', document.getElementById('uploadNameInput').value.trim() || file.name);
            form.append('folder', document.getElementById('pathInput').value.trim());
            form.append('site_id', siteIdInput.value.trim());
            const response = await fetch(url.toString(), {
                method: 'POST',
                body: form,
                credentials: 'same-origin',
            });
            const text = await response.text();
            let data;
            try {
                data = JSON.parse(text);
            } catch {
                data = text;
            }
            if (!response.ok) throw data;
            return data;
        }

        function renderConfig(data) {
            config = data;
            callbackUrl.value = browserCallbackUrl();
            loginState.textContent = data.logged_in ? `已登录：${data.user.display_name || data.user.username}` : '未登录';
            loginState.className = data.logged_in ? 'badge text-bg-success' : 'badge text-bg-warning';

            const current = data.clouds?.[cloudSelect.value];
            if (!current) {
                configSummary.textContent = '未识别的云环境';
                return;
            }
            configSummary.innerHTML = [
                `Graph：<code>${current.graph}</code>`,
                `登录端点：<code>${current.authority}</code>`,
                `应用配置：${current.configured ? '已配置' : '未配置 client_id/client_secret'}`,
                `授权状态：${current.authorized ? '已授权' : '未授权'}`,
                `Scope：<code>${current.scope}</code>`,
            ].join('<br>');
        }

        async function loadConfig() {
            const data = await api('config');
            renderConfig(data);
            print(data);
        }

        document.getElementById('refreshConfigButton').addEventListener('click', loadConfig);
        cloudSelect.addEventListener('change', () => config && renderConfig(config));
        document.getElementById('copyCallbackButton').addEventListener('click', async () => {
            await navigator.clipboard.writeText(callbackUrl.value);
        });
        document.getElementById('clearOutputButton').addEventListener('click', () => print(''));

        document.getElementById('authButton').addEventListener('click', async () => {
            const data = await api('auth_url');
            print(data);
            window.open(data.auth_url, '_blank', 'noopener,noreferrer');
        });

        document.getElementById('disconnectButton').addEventListener('click', async () => {
            print(await api('disconnect', { method: 'POST', body: { cloud: cloudSelect.value } }));
            await loadConfig();
        });

        document.querySelectorAll('[data-test]').forEach((button) => {
            button.addEventListener('click', async () => {
                const test = button.dataset.test;
                const path = document.getElementById('pathInput').value.trim();
                const siteUrl = document.getElementById('siteUrlInput').value.trim();
                const siteId = siteIdInput.value.trim();
                const filename = document.getElementById('uploadNameInput').value.trim();
                try {
                    if (test === 'me_drive') {
                        print(await api('me_drive'));
                    } else if (test === 'list') {
                        print(await api('list', { query: { path } }));
                    } else if (test === 'site') {
                        const data = await api('site', { query: { site_url: siteUrl } });
                        if (data.id) siteIdInput.value = data.id;
                        print(data);
                    } else if (test === 'list_site') {
                        print(await api('list', { query: { site_id: siteId } }));
                    } else if (test === 'upload_session') {
                        print(await api('upload_session', {
                            method: 'POST',
                            body: { filename, folder: path, site_id: siteId }
                        }));
                    } else if (test === 'simple_upload') {
                        print(await uploadDemoFile());
                    }
                    await loadConfig();
                } catch (error) {
                    print(error);
                }
            });
        });

        window.addEventListener('message', (event) => {
            if (event.data?.type === 'onedrive-demo-auth-complete') {
                loadConfig();
            }
        });

        loadConfig().catch(print);
    </script>
</body>

</html>
