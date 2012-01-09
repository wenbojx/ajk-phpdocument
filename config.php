<?php
$CONFIG['db']['master'] = array(
	'host'=>'127.0.0.1',
	'user'=>'root',
	'password'=>'1026',
	'database'=>'codelist'
);
//开发人员名单
$CONFIG['dev'] = array(
	'liwenbo',
	':Alan.P',
	'herryli',
	'zhxia',
	'hlan',
	'Jock',
	'jamesjiang'
);

$domain = 'http://codelist.wbli.dev.aifang.com/';
$root_dir = '/var/www/v2_anjuke_backs/'; //统计目录
$version = '201202'; //版本号
$relation = false; //是否统计方法关联性