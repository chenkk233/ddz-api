<?php
//userinfo.php
//玩家信息
require_once 'Model/UserModel.php';
require_once 'Utils/ResponseUtils.php';

// 获取请求中的token
$token = $_POST['token'];
// 验证token
$user = (new UserModel)->verifyToken($token);
if(!$user){
    echo ResponseUtils::err_js('token验证失败', 401);
    exit;
}
// 获取用户信息
$userInfo = (new UserModel)->getUserInfo($user['uid']);
// 将user的nickname和avatar添加到userInfo中
$userInfo['nickname'] = $user['nickname'];
$userInfo['avatar'] = $user['avatar'];
// 返回用户信息
if($userInfo) {
    echo ResponseUtils::ok_js($userInfo,"获取用户信息成功",200);
} else {
    echo ResponseUtils::err_js('获取用户信息失败', 500);
}

