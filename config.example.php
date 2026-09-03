<?php
return [
    'db' => [
        'host' => '127.0.0.1',
        'user' => 'root',
        'password' => '',
        'database' => 'course_platform',
        'port' => 3306,
        'charset' => 'utf8mb4',
    ],
    'session_name' => 'COURSESESSID',
    // 可选：图床 API token。设置后可通过请求头 X-Image-Token 免登录上传
    // 'image_bed' => [
    //     'api_token' => 'replace-with-a-strong-random-token',
    //     'max_size_bytes' => 20 * 1024 * 1024,
    // ],
    // 可选：云盘文件存储目录，默认使用项目根目录下的 uploads/files
    // 'storage' => [
    //     'cloud_dir' => '/path/to/writable/uploads/files',
    //     'image_dir' => '/path/to/writable/uploads/images',
    //     'public_base_url' => 'https://rarelight.cn',
    //     'image_public_prefix' => '/uploads/images',
    // ],
    // 可选：OneDrive / SharePoint Graph API 接入 demo 配置。
    // Azure Portal / Azure 中国门户中创建“Web”应用后，把 /api/onedrive_demo.php?action=callback
    // 接口返回的 callback_url 填入 Redirect URI。
    // 'onedrive_demo' => [
    //     'apps' => [
    //         'global' => [
    //             'client_id' => '00000000-0000-0000-0000-000000000000',
    //             'client_secret' => 'replace-with-client-secret',
    //             'tenant' => 'common',
    //             'scope' => 'offline_access https://graph.microsoft.com/User.Read https://graph.microsoft.com/Files.ReadWrite.All https://graph.microsoft.com/Sites.ReadWrite.All',
    //         ],
    //         'china' => [
    //             'client_id' => '00000000-0000-0000-0000-000000000000',
    //             'client_secret' => 'replace-with-client-secret',
    //             'tenant' => 'common',
    //             'scope' => 'offline_access https://microsoftgraph.chinacloudapi.cn/User.Read https://microsoftgraph.chinacloudapi.cn/Files.ReadWrite.All https://microsoftgraph.chinacloudapi.cn/Sites.ReadWrite.All',
    //         ],
    //     ],
    // ],
];
