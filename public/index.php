<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------

// [ 应用入口文件 ]

// 定义应用目录
define('APP_PATH', __DIR__ . '/../application/');

define('BASE_ROOT_PATH',str_replace('\\','/',dirname(__FILE__)));
define('BASE_DATA_PATH',BASE_ROOT_PATH.'/../upload/');


define('API_URL', 'http://jztule.com/api/public/index.php/');
define('TIMESTAMP', time());

// 加载框架引导文件
require __DIR__ . '/../thinkphp/start.php';
