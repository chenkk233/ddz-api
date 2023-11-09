<?php
//origin 开放跨域
header('Access-Control-Allow-Origin:*');
// index.php
require_once 'Utils/ResponseUtils.php';
$requestUri = $_SERVER['REQUEST_URI'];
$baseUrl = '/'; // 你的项目的基本 URL
// POST和GET请求的开关
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo ResponseUtils::err_js('请求方式错误', 405);
    exit;
}
// 解析请求的路径部分
$path = parse_url($requestUri, PHP_URL_PATH);
$path = trim($path, '/');
$path = preg_replace('/[^a-zA-Z0-9\/]/', '', $path);

// 根据路径部分来确定要调用的 API 接口文件
switch ($path) {
    case 'login': //登录
        require_once 'API/login.php';
        break;
    case 'register': //注册
        require_once 'API/register.php';
        break;
    case 'userinfo': //用户信息
        require_once 'API/userinfo.php';
        break;
    case 'createroom': //创建房间
        require_once 'API/createroom.php';
        break;
    case 'inroom': //进入房间
        require_once 'API/inroom.php';
        break;
    case 'outroom': //退出房间
        require_once 'API/outroom.php';
        break;
    case 'roominfo': //房间信息
        require_once 'API/roominfo.php';
        break;
    case 'roomready': //房间准备
        require_once 'API/roomready.php';
        break;
    case 'roomstart': //房间开始
        require_once 'API/roomstart.php';
        break;
    case 'creategame': //创建游戏
        require_once 'API/creategame.php';
        break;
    case 'gameinfo': //游戏信息
        require_once 'API/gameinfo.php';
        break;
    case 'jiaodizhu': //叫地主
        require_once 'API/jiaodizhu.php';
        break;
    case 'qiangdizhu'://抢地主
        require_once 'API/qiangdizhu.php';
        break;
    case 'buqiang'://不抢
    case 'bujiao'://不叫
        require_once 'API/bujiao.php';
        break;
    case 'outcards': //出牌
        require_once 'API/outcards.php';
        break;
    case 'noput': //不出
        require_once 'API/noput.php';
        break;
    case 'gameover'://游戏结束
        require_once 'API/gameover.php';
        break;



    // 添加其他路由规则
    default:
        // 返回 404 错误或其他处理逻辑
        echo ResponseUtils::err_js('暂无此接口', 404);
        break;
}
