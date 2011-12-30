-- phpMyAdmin SQL Dump
-- version 3.3.10
-- http://www.phpmyadmin.net
--
-- 主机: 127.0.0.1
-- 生成日期: 2011 年 12 月 01 日 12:07
-- 服务器版本: 5.1.41
-- PHP 版本: 5.3.2-1ubuntu4.10

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- 数据库: `codelist`
--

-- --------------------------------------------------------

--
-- 表的结构 `acms_classes`
--

CREATE TABLE IF NOT EXISTS `acms_classes` (
  `id` int(7) NOT NULL AUTO_INCREMENT,
  `fid` int(7) NOT NULL COMMENT '文件ID',
  `pid` int(7) DEFAULT NULL COMMENT '父ID',
  `ffid` int(7) NOT NULL DEFAULT '0' COMMENT '所属文件目录ID',
  `path` varchar(150) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT '路径',
  `cname` char(50) COLLATE utf8_unicode_ci NOT NULL COMMENT '类名',
  `modify` int(11) NOT NULL DEFAULT '1' COMMENT ' 1为已修改，2为未修改',
  `extends` char(100) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT '父类名称',
  `intro` text COLLATE utf8_unicode_ci COMMENT '简介',
  `abstract` varchar(500) COLLATE utf8_unicode_ci DEFAULT NULL,
  `access` varchar(500) COLLATE utf8_unicode_ci DEFAULT NULL,
  `author` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `copyright` varchar(500) COLLATE utf8_unicode_ci DEFAULT NULL,
  `deprecated` varchar(500) COLLATE utf8_unicode_ci DEFAULT NULL,
  `deprec` varchar(500) COLLATE utf8_unicode_ci DEFAULT NULL,
  `example` varchar(500) COLLATE utf8_unicode_ci DEFAULT NULL,
  `exception` varchar(500) COLLATE utf8_unicode_ci DEFAULT NULL,
  `global` varchar(500) COLLATE utf8_unicode_ci DEFAULT NULL,
  `ignore` varchar(500) COLLATE utf8_unicode_ci DEFAULT NULL,
  `internal` varchar(500) COLLATE utf8_unicode_ci DEFAULT NULL,
  `link` varchar(500) COLLATE utf8_unicode_ci DEFAULT NULL,
  `name` varchar(500) COLLATE utf8_unicode_ci DEFAULT NULL,
  `package` varchar(500) COLLATE utf8_unicode_ci DEFAULT NULL,
  `param` varchar(500) COLLATE utf8_unicode_ci DEFAULT NULL,
  `return` varchar(500) COLLATE utf8_unicode_ci DEFAULT NULL,
  `see` varchar(500) COLLATE utf8_unicode_ci DEFAULT NULL,
  `since` varchar(500) COLLATE utf8_unicode_ci DEFAULT NULL,
  `static` varchar(500) COLLATE utf8_unicode_ci DEFAULT NULL,
  `staticvar` varchar(500) COLLATE utf8_unicode_ci DEFAULT NULL,
  `subpackage` varchar(500) COLLATE utf8_unicode_ci DEFAULT NULL,
  `throws` varchar(500) COLLATE utf8_unicode_ci DEFAULT NULL,
  `todo` varchar(500) COLLATE utf8_unicode_ci DEFAULT NULL,
  `var` varchar(500) COLLATE utf8_unicode_ci DEFAULT NULL,
  `version` varchar(500) COLLATE utf8_unicode_ci DEFAULT NULL,
  `release` int(7) DEFAULT NULL COMMENT '软件release版本号格式年+release号',
  `docblock` text COLLATE utf8_unicode_ci,
  `del` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  FULLTEXT KEY `docblock` (`docblock`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=3960 ;

-- --------------------------------------------------------

--
-- 表的结构 `acms_count`
--

CREATE TABLE IF NOT EXISTS `acms_count` (
  `id` int(5) NOT NULL AUTO_INCREMENT,
  `datas` text COLLATE utf8_unicode_ci NOT NULL COMMENT '统计信息',
  `release` varchar(10) COLLATE utf8_unicode_ci NOT NULL COMMENT '版本信息',
  `type` tinyint(1) NOT NULL COMMENT '1注释率，2排名',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='统计信息' AUTO_INCREMENT=25 ;

-- --------------------------------------------------------

--
-- 表的结构 `acms_files`
--

CREATE TABLE IF NOT EXISTS `acms_files` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `pid` int(7) NOT NULL COMMENT '父ID',
  `name` char(50) COLLATE utf8_unicode_ci NOT NULL,
  `path` varchar(150) COLLATE utf8_unicode_ci NOT NULL COMMENT '文件目录',
  `filemd5` char(32) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT '文件MD5值',
  `type` tinyint(1) NOT NULL DEFAULT '1' COMMENT '1为file 2为folder',
  `modify` tinyint(1) NOT NULL DEFAULT '1' COMMENT '1为已修改，2为未修改',
  `release` int(7) NOT NULL DEFAULT '0' COMMENT '软件release版本号格式年+release号',
  `docs` text COLLATE utf8_unicode_ci COMMENT '文档内容',
  `doc_class` text COLLATE utf8_unicode_ci COMMENT '类内容',
  `doc_function` text COLLATE utf8_unicode_ci COMMENT 'function内容',
  `del` tinyint(1) NOT NULL DEFAULT '1' COMMENT '0为已删除1为正常',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='文件目录表' AUTO_INCREMENT=5054 ;

-- --------------------------------------------------------

--
-- 表的结构 `acms_methods`
--

CREATE TABLE IF NOT EXISTS `acms_methods` (
  `id` int(7) NOT NULL AUTO_INCREMENT,
  `fid` int(7) NOT NULL COMMENT '目录ID',
  `pid` int(7) DEFAULT NULL COMMENT '类ID',
  `path` varchar(150) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT '路径',
  `mname` char(50) COLLATE utf8_unicode_ci NOT NULL COMMENT '方法名',
  `modify` int(11) NOT NULL DEFAULT '1' COMMENT '1为已修改，2为未修改',
  `intro` text COLLATE utf8_unicode_ci COMMENT '简介',
  `abstract` varchar(500) COLLATE utf8_unicode_ci DEFAULT NULL,
  `access` varchar(500) COLLATE utf8_unicode_ci DEFAULT NULL,
  `author` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `copyright` varchar(500) COLLATE utf8_unicode_ci DEFAULT NULL,
  `deprecated` varchar(500) COLLATE utf8_unicode_ci DEFAULT NULL,
  `deprec` varchar(500) COLLATE utf8_unicode_ci DEFAULT NULL,
  `example` varchar(500) COLLATE utf8_unicode_ci DEFAULT NULL,
  `exception` varchar(500) COLLATE utf8_unicode_ci DEFAULT NULL,
  `global` varchar(500) COLLATE utf8_unicode_ci DEFAULT NULL,
  `ignore` varchar(500) COLLATE utf8_unicode_ci DEFAULT NULL,
  `internal` varchar(500) COLLATE utf8_unicode_ci DEFAULT NULL,
  `link` varchar(500) COLLATE utf8_unicode_ci DEFAULT NULL,
  `name` varchar(500) COLLATE utf8_unicode_ci DEFAULT NULL,
  `package` varchar(500) COLLATE utf8_unicode_ci DEFAULT NULL,
  `param` varchar(500) COLLATE utf8_unicode_ci DEFAULT NULL,
  `return` varchar(500) COLLATE utf8_unicode_ci DEFAULT NULL,
  `see` varchar(500) COLLATE utf8_unicode_ci DEFAULT NULL,
  `since` varchar(500) COLLATE utf8_unicode_ci DEFAULT NULL,
  `static` varchar(500) COLLATE utf8_unicode_ci DEFAULT NULL,
  `staticvar` varchar(500) COLLATE utf8_unicode_ci DEFAULT NULL,
  `subpackage` varchar(500) COLLATE utf8_unicode_ci DEFAULT NULL,
  `throws` varchar(500) COLLATE utf8_unicode_ci DEFAULT NULL,
  `todo` varchar(500) COLLATE utf8_unicode_ci DEFAULT NULL,
  `var` varchar(500) COLLATE utf8_unicode_ci DEFAULT NULL,
  `version` varchar(500) COLLATE utf8_unicode_ci DEFAULT NULL,
  `release` int(7) DEFAULT NULL COMMENT '软件release版本号格式年+release号',
  `docblock` text COLLATE utf8_unicode_ci,
  `quote` text COLLATE utf8_unicode_ci COMMENT '引用信息多个引用用,号隔开',
  `del` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=23227 ;
