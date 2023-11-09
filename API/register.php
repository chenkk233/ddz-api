<?php
// register.php

require_once 'Model/UserModel.php';
require_once 'Utils/ResponseUtils.php';
// POST和GET请求的开关
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo ResponseUtils::err_js('请求方式错误', 405);
    exit;
}
// 获取请求中的账号和密码
$email = $_POST['email'];
$pwd = $_POST['pwd'];
$repwd = $_POST['repwd'];
$nickname=$_POST['nickname'];
// 空验证
if (empty($email) || empty($pwd) || empty($repwd)) {
    echo ResponseUtils::err_js('账号或密码不能为空', 400);
    exit;
}
// 当nickname为空时，将nickname设置为email
if(empty($nickname)){
    $nickname=$email;
}
// 密码和确认密码是否一致
if ($pwd !== $repwd) {
    echo ResponseUtils::err_js('两次密码不一致', 400);
    exit;
}
//正则判断邮箱格式是否正确
if(!preg_match('/^[\w-]+(\.[\w-]+)*@[\w-]+(\.[\w-]+)+$/', $email)){
    echo ResponseUtils::err_js('邮箱格式不正确', 400);
    exit;
}
// 实例化 UserModel
$userModel = new UserModel();
// 调用 UserModel 的 login 方法进行登录验证
$userInfo = $userModel->register($nickname,$email, $pwd);
header('Content-Type: application/json');
if ($userInfo == 200) {
    // 登录成功，返回用户信息
    echo ResponseUtils::ok_js(null, '注册成功', 200);
} else {
    // 登录失败，返回错误信息
    echo ResponseUtils::err_js($userInfo, 400);
}


