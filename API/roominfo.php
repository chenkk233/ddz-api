<?php
//roominfo.php
//房间信息
require_once 'Model/UserModel.php';
require_once 'Model/RoomModel.php';
require_once 'Utils/ResponseUtils.php';

// 获取请求中的token
$token = $_POST['token'];
// 验证token
$user = (new UserModel)->verifyToken($token);
if(!$user){
    echo ResponseUtils::err_js('token验证失败', 401);
    exit;
}
// 验证 rtoken
$rtoken = $_POST['rtoken'];
$room = (new RoomModel)->verifyRtoken($rtoken);
if(!$room){
    echo ResponseUtils::err_js('rtoken验证失败', 401);
    exit;
}
// 获取房间信息
$roomInfo = (new RoomModel)->roomInfo($user['uid'],$rtoken);
// 返回房间信息
if($roomInfo) {
    echo ResponseUtils::ok_js($roomInfo,"获取房间信息成功",200);
} else {
    echo ResponseUtils::err_js('获取房间信息失败', 500);
}

