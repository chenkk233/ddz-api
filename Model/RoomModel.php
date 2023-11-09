<?php
// UserModel.php
require_once 'Model/UserModel.php';
class RoomModel
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

    public function createRoom($id){
        //空验证
        if(empty($id)){
            return false;
        }
        //查询是否存在他为房主的房间
        $sql="select * from rooms where master='$id'";
        $result = $this->connection->query($sql);
        //判断是否存在
        if($result->num_rows>0){
            //存在
            $row = $result->fetch_assoc();
            return $row['token'];
        }
        // 初始化房间
        //随机生成房间号 1000-1000-1000
        $rid = rand(1000,9999).'-'.rand(1000,9999).'-'.rand(1000,9999);
        //初始化房间信息
        $mode=1;
        $name='房间'.$rid;
        $capacity=3;
        $status=0;
        $create_time=time();
        $over_time=0;
        $playerlist=$id.','.'0'.','.'0';
        $readylist='1'.','.'0'.','.'0';
        $master=$id;
        $token=md5(uniqid(md5(microtime(true)),true));
        //插入数据库
        $sql="insert into rooms (rid,mode,name,capacity,status,create_time,over_time,playerlist,readylist,master,token) values ('$rid','$mode','$name','$capacity','$status','$create_time','$over_time','$playerlist','$readylist','$master','$token')";
        //执行
        $result = $this->connection->query($sql);
        //判断是否成功
        if($result){
            return $token;
        }else{
            return false;
        }

    }
    public function inRoom($id,$rtoken,$playerlist){
        //空验证
        if(empty($id)){
            return false;
        }
        //将$playerlist按照,分割
        $playerlist = explode(',',$playerlist);
        //判断是否已经在房间里
        if(in_array($id,$playerlist)){
            return true;
        }
        //判断房间是否已满 没有为0的空位
        if(!in_array(0,$playerlist)){
            return false;
        }

        //$playerlist里的元素第一个为 0的元素 替换为$id
        foreach ($playerlist as $key => $value) {
            if($value == 0){
                $playerlist[$key] = $id;
                break;
            }
        }
        //将$playerlist按照,拼接成字符串
        $playerlist = implode(',',$playerlist);


        //加入房间 将用户id加入到房间的playerlist中
        $sql = "UPDATE rooms SET playerlist = '$playerlist' WHERE token = '$rtoken'";
        //执行
        $result = $this->connection->query($sql);
        //判断是否成功
        if($result){
            return true;
        }else{
            return false;
        }
    }
    public function roomInfo($id,$rtoken){
        //空验证
        if(empty($id)){
            return false;
        }
        if(empty($rtoken)){
            return false;
        }
        //获取房间信息
        $sql = "SELECT * FROM rooms WHERE token = '$rtoken'";
        //执行
        $result = $this->connection->query($sql);
        //判断是否成功
        if($result){



            $roominfo=$result->fetch_assoc();
            $mode=$roominfo['mode'];
            $modeinfo=$this->getmode($mode);
            $roominfo['modeinfo']=$modeinfo;
            $playerlist=$roominfo['playerlist'];
            $playerlist=explode(',',$playerlist);
            $readylist=$roominfo['readylist'];
            $readylist=explode(',',$readylist);
            //判断我是否在房间里
            if(!in_array($id,$playerlist)){
                return false;
            }
            //依次获取玩家信息
            $playerinfo=array();
            foreach ($playerlist as $key => $value) {

                    $playerinfo[$key] = (new UserModel)->getuserinfo($value, true);

            }
            foreach ($playerinfo as $key => $value) {
            //判断房主的位置
            if($value['uid']==$roominfo['master']){
                $roominfo['masterkey']=$key;
                $playerinfo[$key]['master']=true;
            }else{
                $playerinfo[$key]['master']=false;
            }

                //判断自己的位置
                if($value['uid']==$id){
                    $roominfo['mykey']=$key;
                    $playerinfo[$key]['myself']=0;
                    if($key==0){
                        $last=1;
                        $upkey=2;
                    }else if($key==1) {
                        $last = 2;
                        $upkey = 0;
                    }else if($key==2){
                        $last=0;
                        $upkey=1;
                    }

                    $playerinfo[$last]['myself']=1;
                    $playerinfo[$upkey]['myself']=2;

                }
                //判断这位玩家是否准备
                if($readylist[$key]==1){
                    $playerinfo[$key]['ready']=true;
                }else{
                    $playerinfo[$key]['ready']=false;
                }
                unset($playerinfo[$key]['id']);
            }
            if($roominfo['mykey']==$roominfo['masterkey']){
                //我是房主
                //判断房间是否满员
                if(in_array(0,$playerlist)) {
                    //房间没满
                    $roominfo['btn_status'] = 0;
                }else{
                    //判断是否全部准备了
                    if(in_array(0,$readylist)) {
                        //有人没准备
                        $roominfo['btn_status'] = 0;
                    }else{
                        //全部准备了
                        $roominfo['btn_status'] = 1;
                    }
                }
            }else{
                //我准备了嘛
                if($readylist[$roominfo['mykey']]==1) {
                    //我准备了
                    $roominfo['btn_status'] = 3;
                }else{
                    //我没准备
                    $roominfo['btn_status'] = 2;
                }
            }


            //隐藏$roominfo的id
            unset($roominfo['id']);
            unset($roominfo['mode']);
            $roominfo['playerinfo']=$playerinfo;
            return $roominfo;

        }else{
            return false;
        }
    }
    public function roomReady($id,$rtoken,$readylist,$playerlist){
        //空验证
        if(empty($id)){
            return false;
        }
        if(empty($rtoken)){
            return false;
        }
        //将$playerlist按照,分割
        $playerlist = explode(',',$playerlist);
        //查找自己的位置
        $key=array_search($id,$playerlist);
        //将$readylist按照,分割
        $readylist = explode(',',$readylist);
        //判断是否已经准备了
        if($readylist[$key]==1){
            //就取消准备
            $readylist[$key]=0;
        }else{
            //就准备
            $readylist[$key]=1;
        }

        //将$readylist按照,拼接成字符串
        $readylist = implode(',',$readylist);
        //加入房间 将用户id加入到房间的readylist中
        $sql = "UPDATE rooms SET readylist = '$readylist' WHERE token = '$rtoken'";
        //执行
        $result = $this->connection->query($sql);
        //判断是否成功
        if($result){
            return true;
        }else{
            return false;
        }
    }
    public function roomStart($id,$rtoken,$readylist,$playerlist){
        //空验证
        if(empty($id)){
            return false;
        }
        if(empty($rtoken)){
            return false;
        }
        //将$playerlist按照,分割
        $playerlist = explode(',',$playerlist);
        //判断是否满员 在数组里查找是否含有0
        if(in_array(0,$playerlist)){
            return false;
        }
        //将$readylist按照,分割
        $readylist = explode(',',$readylist);
        //判断是否全部准备了
        if(in_array(0,$readylist)){
            return false;
        }
        //将房间状态改为1
        $sql = "UPDATE rooms SET status = 1 WHERE token = '$rtoken'";
        //执行
        $result = $this->connection->query($sql);
        //判断是否成功
        if($result){
            return true;
        }else{
            return false;
        }




    }

    public function getmode($id){
        //空验证
        if(empty($id)){
            return false;
        }
        //获取房间信息
        $sql = "SELECT * FROM mode WHERE id = '$id'";
        //执行
        $result = $this->connection->query($sql);
        //判断是否成功
        if($result){
            return $result->fetch_assoc();
        }else{
            return false;
        }
    }

    public function outRoom($id,$rtoken,$playerlist){
        //空验证
        if(empty($id)){
            return false;
        }
        //将$playerlist按照,分割
        $playerlist = explode(',',$playerlist);
        //判断是否已经在房间里
        if(!in_array($id,$playerlist)){
            return true;
        }
        //$playerlist里的元素第一个为 0的元素 替换为$id
        foreach ($playerlist as $key => $value) {
            if($value == $id){
                $playerlist[$key] = 0;
                break;
            }
        }
        //将$playerlist按照,拼接成字符串
        $playerlist = implode(',',$playerlist);
        $sql = "UPDATE rooms SET playerlist = '$playerlist' WHERE token = '$rtoken'";
        //执行
        $result = $this->connection->query($sql);
        //判断是否成功
        if($result){
            return true;
        }else{
            return false;
        }

    }
    public function verifyRid($id){
        //空验证
        if(empty($id)){
            return false;
        }
        //查询是否存在房间
        $sql="select * from rooms where rid='$id'";
        $result = $this->connection->query($sql);
        //判断是否存在
        if($result->num_rows>0){
            //存在
            $row = $result->fetch_assoc();
            return $row;
        }else{
            return false;
        }

    }
    public function verifyRtoken($token)
    {
        //空验证
        if (empty($token)) {
            return null;
        }
        //查询数据库，验证token
        $sql = "SELECT * FROM rooms WHERE token = '$token'";
        $result = $this->connection->query($sql);
        //判断是否存在
        if ($result->num_rows > 0) {
            //存在，返回房间信息
            $RoomInfo = $result->fetch_assoc();
            return $RoomInfo;
        } else {
            return null;
        }

    }



}
