<?php
// UserModel.php

class UserModel
{
    protected $connection; // 数据库连接

    public function __construct()
    {
        // 获取数据库配置
        $databaseConfig = require 'Config/database.php';
        $this->connection = new mysqli(
            $databaseConfig['hostname'],
            $databaseConfig['username'],
            $databaseConfig['password'],
            $databaseConfig['database']
        );

        if ($this->connection->connect_error) {
            die('数据库连接失败: ' . $this->connection->connect_error);
        }
    }

    public function login($account, $password)
    {
        // 空验证
        if (empty($account) || empty($password)) {
            return null;
        }
        // 查询数据库，验证账号密码
        $sql = "SELECT * FROM user WHERE email = '$account' AND pwd = '$password'";
        $result = $this->connection->query($sql);

        if ($result && $result->num_rows > 0) {
            // 登录成功，返回用户信息
            $userInfo = $result->fetch_assoc();
            //对$userInfo进行处理
            $id=$userInfo['id'];
            //去掉敏感信息
            unset($userInfo['pwd']);
            unset($userInfo['id']);
            unset($userInfo['create_time']);
            //当头像为空时，使用默认头像
            if (!$userInfo['avatar']){
                $config = require 'Config/public.php';
                $userInfo['avatar'] = $config['avatar'];
            }
            //查询用户信息getUserInfo
            $Info=$this->getUserInfo($userInfo['uid']);
            //将info的score role status加入到userInfo中
            $userInfo['score']=$Info['score'];
            $userInfo['role']=$Info['role'];
            $userInfo['status']=$Info['status'];
            //制作随机token
            $token = md5(uniqid(md5(microtime(true)),true));
            //将token存入 user 数据库
            $sql = "UPDATE user SET token = '$token' WHERE id = '$id'";
            $this->connection->query($sql);
            //将token存入userinfo
            $userInfo['token'] = $token;
            return $userInfo;
        }
        return null; // 登录失败
    }
    public function register($nickname, $email, $pwd)
    {
        // 空验证
        if (empty($nickname) || empty($email) || empty($pwd)) {
            return "参数不能为空";
        }
        // 查询数据库，验证账号是否重复
        $sql = "SELECT * FROM user WHERE email = '$email'";
        $result = $this->connection->query($sql);

        if ($result && $result->num_rows > 0) {
            return "账号重复"; // 账号重复
        }
        //查询当前数据库中最大的uid
        $sql = "SELECT MAX(uid) FROM user";
        $result = $this->connection->query($sql);
        $uid = $result->fetch_assoc()['MAX(uid)'];
        $uid = $uid + 1;
        //create_time
        $create_time = time();
        // 注册成功，插入数据库
        $sql = "INSERT INTO user (nickname, email, pwd, uid,create_time) VALUES ('$nickname', '$email', '$pwd','$uid','$create_time')";
        $result = $this->connection->query($sql);
        if (!$result) {
            //返回SQL错误信息
            return $this->connection->error;
        }
        // 初始化用户信息
        $sql = "INSERT INTO info (uid,score,status,role) VALUES ('$uid',10000,1,1)";
        $result = $this->connection->query($sql);

        if (!$result) {
            //返回SQL错误信息
            return $this->connection->error;
        }
        return 200; // 返回用户id


    }
    public function verifyToken($token)
    {
        //空验证
        if (empty($token)) {
            return null;
        }
        //查询数据库，验证token
        $sql = "SELECT * FROM user WHERE token = '$token'";
        $result = $this->connection->query($sql);
        //判断是否存在
        if ($result->num_rows > 0) {
            //存在，返回用户信息
            $userInfo = $result->fetch_assoc();
            //当头像为空时，使用默认头像
            if (!$userInfo['avatar']){
                $config = require 'Config/public.php';
                $userInfo['avatar'] = $config['avatar'];
            }
            return $userInfo;
        } else {
            return null;
        }

    }
    public function getUserInfo($id,$isall=false){
        //空验证
        if (empty($id)) {
            return null;
        }
        //查询数据库，验证token
        $sql = "SELECT * FROM info WHERE uid = '$id'";
        $result = $this->connection->query($sql);
        //判断是否存在
        if ($result->num_rows > 0) {
            //存在，返回用户信息
            $userInfo = $result->fetch_assoc();
            if($isall){
                //额外要获取昵称 头像
                $sql = "SELECT * FROM user WHERE uid = '$id'";
                $result = $this->connection->query($sql);
                $user = $result->fetch_assoc();
                $userInfo['nickname']=$user['nickname'];
                $userInfo['avatar']=$user['avatar'];
                if (!$userInfo['avatar']){
                    $config = require 'Config/public.php';
                    $userInfo['avatar'] = $config['avatar'];
                }
            }
            return $userInfo;
        } else {
            return null;
        }
    }


}
