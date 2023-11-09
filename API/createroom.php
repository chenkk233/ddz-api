<?php
// createroom.php
//玩家信息
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
// 创建房间
$rtoken = (new RoomModel)->createRoom($user['uid']);
if(!$rtoken){
    echo ResponseUtils::err_js('创建房间失败', 500);
    exit;
}
// 返回房间号
echo ResponseUtils::ok_js($rtoken, '创建房间成功',200);
