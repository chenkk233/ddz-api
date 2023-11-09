<?php
// GameModel.php
require_once 'Model/UserModel.php';
require_once 'Model/RoomModel.php';
require_once 'Model/GameModel.php';
class CardsModel
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
    public function verifyOutcards($cards,$room,$uid){

        //获取游戏信息
        $gameData=(new GameModel)->gameInfo($uid,$room,$room['token']);
        //当前玩家自己的手牌
        $my_cards=$gameData['playerinfo'][$gameData['mykey']]['cards'];

        $my_cards = explode(',',$my_cards);
        //判断$cards是否是$my_cards的子集
        $result = array_diff($cards,$my_cards);
        if(!empty($result)){
            return false;
        }

        //判断是否是第一次出牌 $gameData['mybtn']字符串中是否含有0

        if(strpos($gameData['mybtn'],'0')===false) {
            //说明是单独出牌
            //单独出牌只需要判断是否符合规则
            $rule= $this->verifyCards($cards);
            if($rule==false){
                return false;
            }
            //判断$rule是否是炸弹
            if($rule[0]==0){
                //如果是炸弹 则倍数翻倍
                $gameData['multiple']=$gameData['multiple']*2;
            }
            $log="执行了单独出牌";

        }else{

            //说明是跟牌
            //查询上一个玩家出的牌
            $sql = "select * from outlog where rid='{$room['rid']}' AND (type ='3' OR type ='4') AND status='1' order by id desc limit 1";
            $result = $this->connection->query($sql);
            if ($result === false) {
                //die( $this->connection->error);
                return false;
            }
            $out_cards = $result->fetch_assoc();
            if(empty($out_cards)){
                return false;
            }
            //将上一个玩家出的牌转换成数组

            $out_cards = explode(',',$out_cards['cards']);


            $myrule= $this->verifyCards($cards);
            if($myrule==false){
                return false;
            }
            if($myrule[0]==0){
                //如果是炸弹 则倍数翻倍
                $gameData['multiple']=$gameData['multiple']*2;
            }
            $outrule= $this->verifyCards($out_cards);
            if($outrule==false){
                return false;
            }

            // 比较两个rule是否同类型
            if($myrule[0]!=$outrule[0]){
                //如果我是炸弹，上一个玩家不是炸弹，我就可以出牌
                if($myrule[0]==0 && $outrule[0]!=0){
                }else if($myrule[0]==0 && $myrule[1]==0){
                    //我是王炸 我可以出牌
                }
                else{
                    return false;
                }
            }
            // 比较两个rule的权值
            if($myrule[1]>=$outrule[1]){
                return false;
            }
            $log="执行了跟牌";
        }

        //判断是否是自己的回合
        if($gameData['mycurrent']!=1){
            return false;
        }

        // 出牌 从$my_cards中剔除$cards的元素
        $out_cards = $cards;
        $my_cards = array_diff($my_cards,$cards);
        //将剩余的牌转换成字符串
        $my_cards = implode(',',$my_cards);
        $out_cards=implode(',',$out_cards);
        //将gamedata的轮到的玩家+1
        $downkey=$gameData['mykey']+1;
        //当downkey==3时 说明当前玩家是最后一个玩家 需要将downkey改为0
        if($downkey==3){
            $downkey=0;
        }
        //判断我的手牌是否为空
        if(empty($my_cards)){
            //将status改为2 代表游戏结束
            $sql = "update gamedata set status='2',p".($gameData['mykey']+1)."Cards='{$my_cards}',multiple='{$gameData['multiple']}' where rid='{$room['rid']}' AND status='1'";
            $result= $this->connection->query($sql);
            if ($result === false) {
                //die( $this->connection->error);
                return false;
            }
            //更新outlog
            //将outlog 里面的rid==room['rid'] 的status改为0
            $sql = "update outlog set status='0' where rid='{$room['rid']}'";
            $result= $this->connection->query($sql);
            if ($result === false) {
                //die( $this->connection->error);
                return false;
            }
            $sql = "INSERT INTO outlog (rid,uid,type,status,create_time,log,cards,userkey) VALUES ('{$room['rid']}','{$uid}','3','0','".time()."','$log','$out_cards','{$gameData['mykey']}')";
            $result = $this->connection->query($sql);
            if ($result === false) {
                //die( $this->connection->error);
                return false;
            }
            return true;
        }
        //更新数据库 round=4 跟牌行为
        $sql = "update gamedata set round='4',current='$downkey',p".($gameData['mykey']+1)."Cards='{$my_cards}',multiple='{$gameData['multiple']}' where rid='{$room['rid']}' AND status='1'";
        $result= $this->connection->query($sql);
        if ($result === false) {
            //die( $this->connection->error);
            return false;
        }
        //更新outlog
        //将outlog 里面的rid==room['rid'] 的status改为0
        $sql = "update outlog set status='0' where rid='{$room['rid']}'";
        $result= $this->connection->query($sql);
        if ($result === false) {
            //die( $this->connection->error);
            return false;
        }
        $sql = "INSERT INTO outlog (rid,uid,type,status,create_time,log,cards,userkey) VALUES ('{$room['rid']}','{$uid}','3','1','".time()."','$log','$out_cards','{$gameData['mykey']}')";
        $result = $this->connection->query($sql);
        if ($result === false) {
            //die( $this->connection->error);
            return false;
        }
        return true;



        return true;
    }
    //判断出牌是否符合规则
    public function verifyCards($cards){
        $res=[]; //返回结果 第一个元素是牌的类型 第二个元素是牌的权值
        //获取牌权值
        $config = require 'Config/public.php';
        $cards_value = $config['cards'];

        //将牌的值转换成$cards_value二维数组的[1]权值
        $cards = array_map(function($v) use($cards_value){
            return $cards_value[$v][1];
        },$cards);
        sort($cards);

        $count = count($cards);
        // 将牌的权值 排序
        // 统计每个权值的数量
        $countedCards = array_count_values($cards);
        // 排序规则：权值从小到大，相同权值的牌数量从多到少
        uksort($countedCards, function($a, $b) use ($countedCards) {
            if ($countedCards[$a] != $countedCards[$b]) {
                return $countedCards[$b] - $countedCards[$a];
            }
            return $a - $b;
        });
// 检查是否满足出牌规则
        // 单独判断 0 1 代表 大王和小王
        if ($count == 2 && $cards[0] == 0 && $cards[1] == 1) {
            // 王炸
            return [0,0];
        }
        //判断有大小王的情况下 除了王炸其他都不满足
        if ($count == 2 && ($cards[0] == 0 || $cards[1] == 1)) {
            //不满足任何出牌规则
            return false;
        }
        // 单张
        if ($count == 1) {
            // 判断是否是大小王
            if ($cards[0] == 0) {
                return [1,0]; // 大王
            }
            if ($cards[0] == 1) {
                return [1,1];
            }
            //return "单张";
            return [1,$cards[0]];
        }

        // 对子
        if ($count == 2 && count($countedCards) == 1) {
            //return "对子";
            return [2,$cards[0]];
        }
        // 三张
        if ($count == 3 && count($countedCards) == 1) {
            //return "三张";
            return [3,$cards[0]];
        }

        // 顺子
        if ($count >= 5 && count($countedCards) == $count && max($cards) - min($cards) == $count - 1) {
            //return "顺子";
            //当前最大值的牌型
            return [4,$cards[$count-1]];
        }

        // 连对
        if ($count >= 6 && $count % 2 == 0 && count($countedCards) == $count / 2 && max($cards) - min($cards) == $count / 2 - 1) {
            //return "连对";
            return [5,$cards[$count-1]];

        }

        // 三带一
        if ($count == 4 && count($countedCards) == 2 && in_array(3, $countedCards)) {
            //return "三带一";
            return [6,$cards[0]];
        }

        // 三带一对
        if ($count == 5 && count($countedCards) == 2 && in_array(3, $countedCards) && in_array(2, $countedCards)) {
            //return "三带一对";
            return [7,$cards[0]];
        }

        // 炸弹
        if ($count == 4 && count($countedCards) == 1) {
            //return "炸弹";
            return [0,$cards[0]];
        }

        // 飞机
        if ($count >= 6 && $count % 3 == 0 && count($countedCards) == $count / 3 && max($cards) - min($cards) == $count / 3 - 1) {
            //return "飞机";
            //从$cards剔除权值数量小于3的牌
            foreach ($countedCards as $k=>$v){
                if($v<3){
                    unset($countedCards[$k]);
                }
            }
            //获取飞机的最大值
            $max = max(array_keys($countedCards));
            return [8,$max];
        }

        return false;




    }
    public function compareCards($a, $b) {
        // 比较权值
        if ($a != $b['value']) {
            return $a['value'] - $b['value'];
        }

        // 比较相同权值的牌数量
        return count($b['cards']) - count($a['cards']);
    }



}