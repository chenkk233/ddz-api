<?php
// login.php

require_once 'Model/UserModel.php';
require_once 'Utils/ResponseUtils.php';
// POST和GET请求的开关
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo ResponseUtils::err_js('请求方式错误', 405);
    exit;
}
// 获取请求中的账号和密码
$account = $_POST['account'];
$password = $_POST['password'];
// 空验证
if (empty($account) || empty($password)) {
    echo ResponseUtils::err_js('账号或密码不能为空', 400);
    exit;
}
// 实例化 UserModel
$userModel = new UserModel();


// 调用 UserModel 的 login 方法进行登录验证
$userInfo = $userModel->login($account, $password);
header('Content-Type: application/json');
if ($userInfo) {
    // 登录成功，返回用户信息
    echo ResponseUtils::ok_js($userInfo, '登录成功', 200);
} else {
    // 登录失败，返回错误信息
    echo ResponseUtils::err_js('账号或密码错误', 400);
}


