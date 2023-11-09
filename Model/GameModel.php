<?php
// GameModel.php
require_once 'Model/UserModel.php';
require_once 'Model/RoomModel.php';
class GameModel
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

    public function createGame($id,$room)
    {
        //斗地主洗牌
        $config = require 'Config/public.php';
        $cards = $config['cards']; //原始牌组
        //洗牌
        shuffle($cards);
        //分牌
        $p1Cards = array_slice($cards, 0, 17);
        $p2Cards = array_slice($cards, 17, 17);
        $p3Cards = array_slice($cards, 34, 17);
        $landlordCards = array_slice($cards, 51, 3);
        //将牌二维数组排序
        sort($p1Cards);
        sort($p2Cards);
        sort($p3Cards);
        sort($landlordCards);
        //将牌二维数组的[0]按照,拼接成字符串
        $p1Cards = implode(',', array_column($p1Cards, 0));
        $p2Cards = implode(',', array_column($p2Cards, 0));
        $p3Cards = implode(',', array_column($p3Cards, 0));
        $landlordCards = implode(',', array_column($landlordCards, 0));



        // 创建游戏
        $round=1; //轮次 1等于叫地主模式
        $current=0; //当前出牌人
        $landlordKey=null;
        $lastoutKey=null;
        $status=1; //游戏状态 1等于游戏中
        $create_time=time();
        $update_time=time();
        $rid=$room['rid'];
        //判断是否已经存在游戏
        $sql = "SELECT * FROM gamedata WHERE rid='{$rid}' AND status=1";
        $result = $this->connection->query($sql);
        if ($result->num_rows > 0) {
            return false;
        }
        $sql = "INSERT INTO gamedata (rid,round,current,landlordKey,p1Cards,p2Cards,p3Cards,landlordCards,status,create_time,multiple,update_time) VALUES ('{$rid}','{$round}','{$current}','{$landlordKey}','{$p1Cards}','{$p2Cards}','{$p3Cards}','{$landlordCards}','{$status}','{$create_time}','1','{$update_time}')";

        $result = $this->connection->query($sql);
        if ($result === false) {
            //die( $this->connection->error);
            return false;
        }
        return $this->connection->insert_id;


    }
    public function gameInfo($id,$room,$rtoken){
        $sql = "SELECT * FROM gamedata WHERE rid='{$room['rid']}' AND status > 0 order by id desc limit 1";
        $result = $this->connection->query($sql);
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $roomData=(new RoomModel)->roomInfo($id,$rtoken);
            // 是否出牌区有人出牌
            $gamelog=$this->getGameLog($room['rid'],1);

            foreach ($roomData['playerinfo'] as $key => $value) {
                $roomData['playerinfo'][$key]['cards']=$row['p'.($key+1).'Cards'];
                //判断是谁的回合
                if($row['current']==$key){
                    $roomData['playerinfo'][$key]['current']=1;
                }else{
                    $roomData['playerinfo'][$key]['current']=0;
                }
                //判断是否是地主
                if($row['landlordKey']==$key) {
                    $roomData['playerinfo'][$key]['landlord'] = 1;
                }else{
                    $roomData['playerinfo'][$key]['landlord'] = 0;
                }
                if($gamelog) {
                    //判断$gamelog['uid']是谁
                    if($roomData['playerinfo'][$key]['uid']==$gamelog[0]['uid']){
                        $roomData['playerinfo'][$key]['outcards']=$gamelog[0]['cards'];
                    }else{
                        $roomData['playerinfo'][$key]['outcards']='';
                    }
                    
                }else{
                    $roomData['playerinfo'][$key]['outcards']='';
                }

            }
            //是否是我的回合
            if($row['current']==$roomData['mykey']){
                $roomData['mycurrent']=1;
                //是
                if($row['round']==1){
                    //叫地主模式
                    $roomData['mybtn']='1,0'; //叫地主按钮 和 不叫按钮
                }else if($row['round']==2){
                    // 抢地主模式
                    $roomData['mybtn']='2,0'; //抢地主按钮 和 不抢按钮
                }else if($row['round']==3){
                    // 首次出牌模式
                    $roomData['mybtn']='3'; //出牌按钮
                }else if($row['round']==4){
                    // 出牌模式
                    $roomData['mybtn']='3,0'; //出牌按钮 和 不出按钮
                }else if($row['round']==5){
                    // 出不起模式
                    $roomData['mybtn']='0'; //不出按钮
                }
            }else{
                $roomData['mybtn']='-1'; //不是我的回合
                $roomData['mycurrent']=0;
            }

            $roomData['round']=$row['round'];
            $roomData['gamestatus']=$row['status'];
            $roomData['landlordCards']=$row['landlordCards'];
            $roomData['current']=$row['current'];
            $roomData['multiple']=$row['multiple'];
            $config=require 'Config/public.php';
            $roomData['gameVersion']=$config['gameVersion'];
            return $roomData;
        }


    }
    public function getGameLog($rid,$length=10){
        $sql = "SELECT * FROM outlog WHERE rid='{$rid}' AND cards!='' AND status='1' ORDER BY id DESC LIMIT ".$length;
        $result = $this->connection->query($sql);
        if ($result->num_rows > 0) {
            $data=[];
            while($row = $result->fetch_assoc()) {
                $data[]=$row;
            }
            return $data;
        }
    }
    public function jiaodizhu($id,$room,$rtoken){
       //获取游戏信息
        $gameData=$this->gameInfo($id,$room,$rtoken);
        //判断是否是我的回合
        if($gameData['mycurrent']==0){
            return false;
        }
        //判断是否是叫地主模式
        if($gameData['mybtn']!='1,0'){
            return false;
        }
        //判断是否已经叫过地主
        //在outlog表中查询是否有叫地主的记录
        $sql = "SELECT * FROM outlog WHERE rid='{$room['rid']}' AND uid='{$id}' AND type='1' AND status='1' ORDER BY id DESC LIMIT 1";
        $result = $this->connection->query($sql);
        if ($result->num_rows > 0) {
            //已经叫过地主
            return false;
        }

        // 执行叫地主
        $sql = "INSERT INTO outlog (rid,uid,type,status,create_time,log,userkey) VALUES ('{$room['rid']}','{$id}','1','1','".time()."','执行了叫地主','{$gameData['mykey']}')";
        $result = $this->connection->query($sql);
        if ($result === false) {
            //die( $this->connection->error);
            return false;
        }
        // 将游戏信息中的轮次改为2 抢地主模式 并且将当前出牌人改为当前玩家的下家
        $downkey=$gameData['mykey']+1;
        //当downkey==3时 说明当前玩家是最后一个玩家 需要将downkey改为0
        if($downkey==3){
            $downkey=0;
        }
        $sql = "UPDATE gamedata SET round='2',current='{$downkey}',update_time='".time()."' WHERE rid='{$room['rid']}'";
        $result = $this->connection->query($sql);
        if ($result === false) {
            //die( $this->connection->error);
            return false;
        }

        return true;

    }
    public function qiangdizhu($id,$room,$rtoken){
        //获取游戏信息
        $gameData=$this->gameInfo($id,$room,$rtoken);
        //判断是否是我的回合
        if($gameData['mycurrent']==0){
            return false;
        }
        //判断是否是抢地主模式
        if($gameData['mybtn']!='2,0'){
            return false;
        }
        //判断是否已经抢过地主
        //在outlog表中查询是否有叫地主/抢地主的记录
        $sql = "SELECT * FROM outlog WHERE rid='{$room['rid']}' AND uid='{$id}' AND (type='1' OR type ='2') AND status='1' ORDER BY id DESC LIMIT 1";
        $result = $this->connection->query($sql);
        if ($result->num_rows > 0) {
            //之前已经叫过一次地主了
            //那么这次抢地主直接获得地主身份
            $result = $this->setLandlord($id,$gameData,$room['rid']);
            if($result==false){
                return false;
            }
            return true;
        }
        // 说明没有叫过地主/抢过地主
        // 执行抢地主
        $sql = "INSERT INTO outlog (rid,uid,type,status,create_time,log,userkey) VALUES ('{$room['rid']}','{$id}','2','1','".time()."','执行了抢地主','{$gameData['mykey']}')";
        $result = $this->connection->query($sql);
        if ($result === false) {
            //die( $this->connection->error);
            return false;
        }
        //将gamedata的轮到的玩家+1
        $downkey=$gameData['mykey']+1;
        //当downkey==3时 说明当前玩家是最后一个玩家 需要将downkey改为0
        if($downkey==3){
            $downkey=0;
        }
        //赋分
        $gameData['multiple']=$gameData['multiple']*2;
        $sql = "UPDATE gamedata SET multiple='{$gameData['multiple']}',current='{$downkey}',update_time='".time()."' WHERE rid='{$room['rid']}' AND status='1'";
        $result = $this->connection->query($sql);
        if ($result === false) {
            //die( $this->connection->error);
            return false;
        }
        return true;
    }

    //赋予玩家地主身份
    public function setLandlord($id,$gameData,$rid){
        //赋分
        $gameData['multiple']=$gameData['multiple']*2;
        //将地主的牌加入到当前玩家的牌中
        $new_cards=$gameData['landlordCards'].','.$gameData['playerinfo'][$gameData['mykey']]['cards'];
        //将牌数组排序
        $new_cards=explode(',',$new_cards);
        sort($new_cards);
        //将牌按照,拼接成字符串
        $new_cards=implode(',',$new_cards);
        //将游戏信息中的轮次改为3 首次出牌模式 并且将当前出牌人改为当前玩家 并且将地主身份改为当前玩家 并且将玩家牌改为新的牌
        $sql = "UPDATE gamedata SET multiple='{$gameData['multiple']}',round='3',current='{$gameData['mykey']}',landlordKey='{$gameData['mykey']}',p".($gameData['mykey']+1)."Cards='{$new_cards}',update_time='".time()."' WHERE rid='{$rid}' AND status='1'";
        $result = $this->connection->query($sql);
        if ($result === false) {
            //die( $this->connection->error);
            return false;
        }
        // 执行抢地主
        $sql = "INSERT INTO outlog (rid,uid,type,status,create_time,log,userkey) VALUES ('{$rid}','{$id}','2','1','".time()."','执行了抢地主','{$gameData['mykey']}')";
        $result = $this->connection->query($sql);
        if ($result === false) {
            //die( $this->connection->error);
            return false;
        }
        return true;

        }
        public function bujiao($id,$room,$rtoken){
            //获取游戏信息
            $gameData=$this->gameInfo($id,$room,$rtoken);
            //判断是否是我的回合
            if($gameData['mycurrent']==0){
                return false;
            }
            //判断$gameData['mybtn']字符串中是否含有0
            if(strpos($gameData['mybtn'],'0')===false){
                return false;
            }

            //判断自己的上家是否是不叫了
            $sql = "SELECT * FROM outlog WHERE rid='{$room['rid']}' AND status='1' AND type='0' order by id desc limit 1";
            $result = $this->connection->query($sql);
            //查询记录的数量
            //判断记录的数量是否大于0 如果大于0 说明自己的上家不叫了
            if($result->num_rows >0 && $gameData['round']!=1){
                // 查询上一个叫地主的人
                $sql = "SELECT * FROM outlog WHERE rid='{$room['rid']}' AND status='1' AND (type='1' OR type='2') order by id desc limit 1";
                $result = $this->connection->query($sql);
                $row = $result->fetch_assoc();
                $current=$row['userkey'];
                $sql = "UPDATE gamedata SET round='3',current='{$current}',update_time='".time()."' WHERE rid='{$room['rid']}'  AND status='1'";
                $result = $this->connection->query($sql);
                if ($result === false) {
                    //die( $this->connection->error);
                    return false;
                }


            }else {
                //执行不叫
                //将gamedata的轮到的玩家+1
                $downkey = $gameData['mykey'] + 1;
                //当downkey==3时 说明当前玩家是最后一个玩家 需要将downkey改为0
                if ($downkey == 3) {
                    $downkey = 0;
                }
                $sql = "UPDATE gamedata SET current='{$downkey}',update_time='" . time() . "' WHERE rid='{$room['rid']}'  AND status='1'";
                $result = $this->connection->query($sql);
                if ($result === false) {
                    //die( $this->connection->error);
                    return false;
                }
            }
            // 执行不叫
            $sql = "INSERT INTO outlog (rid,uid,type,status,create_time,log,userkey) VALUES ('{$room['rid']}','{$id}','0','1','".time()."','执行了不叫','{$gameData['mykey']}')";
            //执行
            $result = $this->connection->query($sql);
            if ($result === false) {
                //die( $this->connection->error);
                return false;
            }
            return true;
        }
    public function buchu($id,$room,$rtoken){
        //获取游戏信息
        $gameData=$this->gameInfo($id,$room,$rtoken);
        //判断是否是我的回合
        if($gameData['mycurrent']==0){
            return false;
        }
        //判断$gameData['mybtn']字符串中是否含有0
        if(strpos($gameData['mybtn'],'0')===false){
            return false;
        }
        //执行不叫
        //将gamedata的轮到的玩家+1
        $downkey=$gameData['mykey']+1;
        //当downkey==3时 说明当前玩家是最后一个玩家 需要将downkey改为0
        if($downkey==3){
            $downkey=0;
        }
        $sql = "UPDATE gamedata SET current='{$downkey}',update_time='".time()."' WHERE rid='{$room['rid']}'";
        $result = $this->connection->query($sql);
        if ($result === false) {
            //die( $this->connection->error);
            return false;
        }
        //判断现在有没有人不出
        $sql = "SELECT * FROM outlog WHERE rid='{$room['rid']}' AND status='1' order by id desc limit 1";
        $result = $this->connection->query($sql);
        //查询所有的出牌记录
        $row = $result->fetch_assoc();
        //判断type是否为0
        if($row['type']==0){
            //说明现在不出了2次，要将出牌人改为上一个出牌人
            //查询上一个出牌人
            $sql = "SELECT * FROM outlog WHERE rid='{$room['rid']}' AND status='1' AND type='3' order by id desc limit 1";
            $result = $this->connection->query($sql);
            //查询第一个记录
            $row = $result->fetch_assoc();
            //将出牌人改为上一个出牌人
            $current=$row['userkey'];
            //轮次变回3 代表首次出牌模式
            $sql = "UPDATE gamedata SET current='{$current}',round='3',update_time='".time()."' WHERE rid='{$room['rid']}'";
            $result = $this->connection->query($sql);
            if ($result === false) {
                //die( $this->connection->error);
                return false;
            }
        }

        // 执行不叫
        $sql = "INSERT INTO outlog (rid,uid,type,status,create_time,log,userkey) VALUES ('{$room['rid']}','{$id}','0','1','".time()."','执行了不出','{$gameData['mykey']}')";
        //执行
        $result = $this->connection->query($sql);
        if ($result === false) {
            //die( $this->connection->error);
            return false;
        }
        return true;
    }
    public function gameoverInfo($id,$room,$rtoken){
        //获取游戏信息
        $gameData=$this->gameInfo($id,$room,$rtoken);
        // 判断status是否为2
        if($gameData['gamestatus']!=2){
            return false;
        }
        // 说明游戏结束了
        //算分
        $multiple=$gameData['multiple'];
        $down_score=$gameData['modeinfo']['downscore'];
        // 查询谁的牌为空
        $winkey=[];
        $losekey=[];
        foreach ($gameData['playerinfo'] as $key => $value) {
            if($value['cards']=='' && $value['landlord']==1){
                $gameData['playerinfo'][$key]['win']=true;
                //地主赢了
                $landlordwin=true;
            }else if($value['cards']=='' && $value['landlord']==0){
                $gameData['playerinfo'][$key]['win']=true;
                //农民赢了
                $landlordwin=false;

            }
        }
        foreach ($gameData['playerinfo'] as $key => $value) {
           if($value['landlord']==1 && $landlordwin==false) {
               $gameData['playerinfo'][$key]['win']=false;
               $losekey[] = $key;
               $mark=$multiple*-1;
           }else if($value['landlord']==1 && $landlordwin==true){
               $winkey[] = $key;
               $gameData['playerinfo'][$key]['win']=true;
                $mark=$multiple;
           }else if($value['landlord']==0 && $landlordwin==false) {
               $winkey[] = $key;
               $gameData['playerinfo'][$key]['win'] = true;
                $mark=$multiple/2;
           }else if($value['landlord']==0 && $landlordwin==true){
               $gameData['playerinfo'][$key]['win']=false;
               $losekey[] = $key;
                $mark=($multiple/2)*-1;
           }
            $gameData['playerinfo'][$key]['mark']=$mark*$down_score;
        }
        //将分数写入数据库
        foreach ($gameData['playerinfo'] as $key => $value) {
            $score=$value['score']+$value['mark'];
            $sql = "UPDATE info SET score='{$score}' WHERE uid='{$value['uid']}'";
            $result = $this->connection->query($sql);
            if ($result === false) {
                //die( $this->connection->error);
                return false;
            }
        }
        return $gameData['playerinfo'];



    }



}
