<?php
// outcards.php
//玩家信息
require_once 'Model/UserModel.php';
require_once 'Model/RoomModel.php';
require_once 'Model/GameModel.php';
require_once 'Model/CardsModel.php';
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
// 验证出牌的合法性
$cards = $_POST['cards'];
//将前端传过来的牌转换成数组
$cards = explode(',',$cards);
//判断房间是否已经开始游戏
if($room['status'] == 0){
    echo ResponseUtils::err_js('房间还没有开始游戏', 403);
    exit;
}
// 判断出牌是否合法
$cardsRes = (new CardsModel)->verifyOutcards($cards,$room,$user['uid']);

if(!$cardsRes){
    echo ResponseUtils::ok_js($cardsRes,'出牌不合法', 403);
    exit;
}
echo ResponseUtils::ok_js(null, '出牌成功',200);
