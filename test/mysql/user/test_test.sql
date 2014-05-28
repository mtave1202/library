-- phpMyAdmin SQL Dump
-- version 4.0.10deb1
-- http://www.phpmyadmin.net
--
-- ホスト: localhost
-- 生成日時: 2014 年 5 月 28 日 05:06
-- サーバのバージョン: 5.5.37-0ubuntu0.14.04.1
-- PHP のバージョン: 5.5.9-1ubuntu4

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- データベース: `user`
--

-- --------------------------------------------------------

--
-- テーブルの構造 `test_test`
--

CREATE TABLE IF NOT EXISTS `test_test` (
  `test_id` int(11) NOT NULL,
  `test2_id` int(11) NOT NULL,
  `name` varchar(20) NOT NULL,
  PRIMARY KEY (`test_id`,`test2_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- テーブルのデータのダンプ `test_test`
--

INSERT INTO `test_test` (`test_id`, `test2_id`, `name`) VALUES
(1, 1, 'name1_1'),
(1, 2, 'name1_2'),
(1, 3, 'name1_3'),
(1, 4, 'name1_4'),
(2, 1, 'name2_1'),
(2, 2, 'name2_2'),
(2, 3, 'name2_3'),
(3, 1, 'name3_1'),
(3, 2, 'name3_2'),
(3, 3, 'name3_3');

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
