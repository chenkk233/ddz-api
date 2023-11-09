<?php
// jiaodizhu.php
//玩家信息
require_once 'Model/UserModel.php';
require_once 'Model/RoomModel.php';
require_once 'Model/GameModel.php';
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
//判断房间是否已经开始游戏
if($room['status'] == 0){
    echo ResponseUtils::err_js('房间还没有开始游戏', 403);
    exit;
}
// 向后台请求叫地主
$gameData = (new GameModel)->jiaodizhu($user['uid'],$room,$rtoken);
if(!$gameData){
    echo ResponseUtils::err_js('叫地主失败', 500);
    exit;
}
// 返回房间号
echo ResponseUtils::ok_js($gameData, '叫地主成功',200);