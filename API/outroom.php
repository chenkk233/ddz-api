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
$rtoken = $_POST['rtoken'];
$room = (new RoomModel)->verifyRtoken($rtoken);
if(!$room){
    echo ResponseUtils::err_js('rtoken验证失败', 401);
    exit;
}

// 离开房间
$roomInfo = (new RoomModel)->outRoom($user['uid'],$rtoken,$room['playerlist']);
if(!$roomInfo){
    echo ResponseUtils::err_js('退出房间失败', 500);
    exit;
}
// 返回房间号
echo ResponseUtils::ok_js($room['token'], '退出房间成功',200);

