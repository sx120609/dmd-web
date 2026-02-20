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
    //     'image_public_prefix' => '/uploads/images',
    // ],
];
