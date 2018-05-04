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
// 指定允许其他域名访问
header('Access-Control-Allow-Origin:*');
// 响应类型
header('Access-Control-Allow-Methods:POST');
// 响应头设置
//header('Access-Control-Allow-Headers:x-requested-with,content-type');
header('Access-Control-Allow-Headers:Origin, X-Requested-With, Content-Type,Accept,Authorization');
// 定义应用目录
define('APP_PATH', __DIR__ . '/../application/');
//上传图片那边的
define('BASE_ROOT_PATH',str_replace('\\','/',dirname(__FILE__)));
define('BASE_DATA_PATH',BASE_ROOT_PATH.'/../upload/');

define('TIMESTAMP', time());

//接口路径
define('API_URL', 'http://jztule.com/api/public/index.php/');
//图片路径
define('IMG_DOMAIN','http://jztule.com/api/upload/');

// 加载框架引导文件
require __DIR__ . '/../thinkphp/start.php';
