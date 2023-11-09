-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- 主机： localhost
-- 生成日期： 2023-11-09 16:41:47
-- 服务器版本： 5.7.40-log
-- PHP 版本： 7.4.33

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- 数据库： `database_name_placeholder`
--

-- --------------------------------------------------------

--
-- 表的结构 `gamedata`
--

CREATE TABLE `gamedata` (
  `id` int(255) NOT NULL,
  `rid` varchar(999) NOT NULL,
  `round` varchar(999) NOT NULL COMMENT '轮次',
  `current` varchar(999) NOT NULL COMMENT '轮到的玩家',
  `landlordKey` varchar(999) DEFAULT NULL,
  `landlordCards` varchar(999) NOT NULL,
  `p1Cards` varchar(999) NOT NULL,
  `p2Cards` varchar(999) NOT NULL,
  `p3Cards` varchar(999) NOT NULL,
  `status` varchar(999) NOT NULL,
  `multiple` varchar(999) DEFAULT NULL,
  `create_time` varchar(999) NOT NULL,
  `update_time` varchar(999) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- 表的结构 `info`
--

CREATE TABLE `info` (
  `id` int(255) NOT NULL COMMENT '唯一id',
  `uid` varchar(999) NOT NULL COMMENT '用户id',
  `score` varchar(999) NOT NULL COMMENT '金币数量',
  `status` varchar(999) NOT NULL COMMENT '状态',
  `role` varchar(999) NOT NULL COMMENT '选择的角色'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- 转存表中的数据 `info`
--

INSERT INTO `info` (`id`, `uid`, `score`, `status`, `role`) VALUES
(1, '10001', '10000', '1', '1'),
(2, '10002', '10000', '1', '1'),
(3, '10003', '10000', '1', '1');

-- --------------------------------------------------------

--
-- 表的结构 `mode`
--

CREATE TABLE `mode` (
  `id` int(255) NOT NULL,
  `name` varchar(999) NOT NULL,
  `multiple` varchar(999) NOT NULL COMMENT '倍数',
  `downscore` varchar(999) NOT NULL COMMENT '底分',
  `capping` varchar(999) NOT NULL COMMENT '封顶',
  `number` varchar(999) NOT NULL COMMENT '默认局数',
  `thinktime` varchar(999) NOT NULL COMMENT '思考时间'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- 转存表中的数据 `mode`
--

INSERT INTO `mode` (`id`, `name`, `multiple`, `downscore`, `capping`, `number`, `thinktime`) VALUES
(1, '经典玩法', '1', '100', '0', '3', '30');

-- --------------------------------------------------------

--
-- 表的结构 `outlog`
--

CREATE TABLE `outlog` (
  `id` int(255) NOT NULL,
  `rid` varchar(999) NOT NULL,
  `uid` varchar(999) NOT NULL,
  `create_time` varchar(999) NOT NULL,
  `cards` varchar(999) DEFAULT NULL,
  `type` varchar(999) NOT NULL,
  `log` varchar(999) DEFAULT NULL,
  `status` varchar(255) NOT NULL,
  `userkey` varchar(999) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- 表的结构 `rooms`
--

CREATE TABLE `rooms` (
  `id` int(255) NOT NULL,
  `rid` varchar(999) NOT NULL,
  `mode` varchar(99) NOT NULL,
  `name` varchar(999) NOT NULL,
  `capacity` varchar(999) NOT NULL COMMENT '房间容量',
  `status` varchar(999) NOT NULL COMMENT '状态',
  `create_time` varchar(999) NOT NULL,
  `over_time` varchar(999) DEFAULT NULL,
  `playerlist` varchar(999) NOT NULL,
  `readylist` varchar(999) NOT NULL,
  `master` varchar(999) NOT NULL,
  `token` varchar(999) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- 表的结构 `user`
--

CREATE TABLE `user` (
  `id` int(255) NOT NULL COMMENT '唯一id',
  `uid` varchar(999) NOT NULL COMMENT '用户id',
  `email` varchar(999) NOT NULL COMMENT '电子邮箱',
  `pwd` varchar(999) NOT NULL COMMENT '密码',
  `avatar` varchar(999) DEFAULT NULL COMMENT '头像',
  `nickname` varchar(999) NOT NULL,
  `token` varchar(999) DEFAULT NULL,
  `create_time` varchar(999) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- 转存表中的数据 `user`
--

INSERT INTO `user` (`id`, `uid`, `email`, `pwd`, `avatar`, `nickname`, `token`, `create_time`) VALUES
(1, '10001', '123@qq.com', '123', NULL, '测试玩家1', '1db389466adf78422d49c0b29440ac7b', '1699182122'),
(2, '10002', '456@qq.com', '123', NULL, '测试玩家2', '61a3492c77ea450819160bfa5f68eda9', '1699188928'),
(3, '10003', '789@qq.com', '123', NULL, '测试玩家3', '23e9c321e0c22b4243ddb4a2a1449656', '1699242005');

--
-- 转储表的索引
--

--
-- 表的索引 `gamedata`
--
ALTER TABLE `gamedata`
  ADD PRIMARY KEY (`id`);

--
-- 表的索引 `info`
--
ALTER TABLE `info`
  ADD PRIMARY KEY (`id`);

--
-- 表的索引 `mode`
--
ALTER TABLE `mode`
  ADD PRIMARY KEY (`id`);

--
-- 表的索引 `outlog`
--
ALTER TABLE `outlog`
  ADD PRIMARY KEY (`id`);

--
-- 表的索引 `rooms`
--
ALTER TABLE `rooms`
  ADD PRIMARY KEY (`id`);

--
-- 表的索引 `user`
--
ALTER TABLE `user`
  ADD PRIMARY KEY (`id`);

--
-- 在导出的表使用AUTO_INCREMENT
--

--
-- 使用表AUTO_INCREMENT `gamedata`
--
ALTER TABLE `gamedata`
  MODIFY `id` int(255) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- 使用表AUTO_INCREMENT `info`
--
ALTER TABLE `info`
  MODIFY `id` int(255) NOT NULL AUTO_INCREMENT COMMENT '唯一id', AUTO_INCREMENT=4;

--
-- 使用表AUTO_INCREMENT `mode`
--
ALTER TABLE `mode`
  MODIFY `id` int(255) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- 使用表AUTO_INCREMENT `outlog`
--
ALTER TABLE `outlog`
  MODIFY `id` int(255) NOT NULL AUTO_INCREMENT;

--
-- 使用表AUTO_INCREMENT `rooms`
--
ALTER TABLE `rooms`
  MODIFY `id` int(255) NOT NULL AUTO_INCREMENT;

--
-- 使用表AUTO_INCREMENT `user`
--
ALTER TABLE `user`
  MODIFY `id` int(255) NOT NULL AUTO_INCREMENT COMMENT '唯一id', AUTO_INCREMENT=4;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
