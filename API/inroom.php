<?php
// inroom.php
//玩家信息
require_once 'Model/UserModel.php';
require_once 'Model/RoomModel.php';
require_once 'Utils/ResponseUtils.php';
// 获取请求中的token
$token = $_POST['token'];
// 验证 token
$user = (new UserModel)->verifyToken($token);
if(!$user){
    echo ResponseUtils::err_js('token验证失败', 401);
    exit;
}
// 验证 rtoken
$rid = $_POST['rid'];
$room = (new RoomModel)->verifyRid($rid);
if(!$room){
    echo ResponseUtils::err_js('找不到该房间', 401);
    exit;
}

// 加入房间
$roomInfo = (new RoomModel)->inRoom($user['uid'], $room['token'],$room['playerlist']);
if(!$roomInfo){
    echo ResponseUtils::err_js('进入房间失败', 500);
    exit;
}
// 返回房间号
echo ResponseUtils::ok_js($room['token'], '进入房间成功',200);

