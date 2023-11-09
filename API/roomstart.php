<?php
//roomstart.php
//房间开始
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
//判断是否是房主
if($room['master'] != $user['uid']){
    echo ResponseUtils::err_js('只有房主才能开始游戏', 403);
    exit;
}
//将$playerlist按照,分割
$playerlist = explode(',',$room['playerlist']);
//判断是否满员 在数组里查找是否含有0
if(in_array(0,$playerlist)){
    echo ResponseUtils::err_js('房间未满员', 404);
    exit;
}
//将$readylist按照,分割
$readylist = explode(',',$room['readylist']);
//判断是否全部准备了
if(in_array(0,$readylist)){
    echo ResponseUtils::err_js('有玩家未准备', 404);
    exit;

}
// 获取房间信息
$roomInfo = (new RoomModel)->roomStart($user['uid'],$rtoken,$room['readylist'],$room['playerlist']);
// 返回房间信息
if($roomInfo) {
    echo ResponseUtils::ok_js(null,"开始游戏成功",200);
} else {
    echo ResponseUtils::err_js('开始游戏失败', 500);
}

