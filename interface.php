<?php
header("Content-type: text/html; charset=utf-8"); 
if(!isset($_GET['do']) || !$_GET['do']){
	die('none done type');
}

include_once('./config.php');
$version = isset($_GET['v'])?trim($_GET['v']):$version;
define('RELEASE_VERSION', $version);


include_once('./class/DataFormat.php');
$dataFormat = new DataFormat();

$type = 'json';

switch ($_GET['do']){
	case 'file':
		include_once('./class/File.php');
		$file_obj = new File();
		$file_obj->run($root_dir);
		break;
	case 'explain':
		include_once('./class/Explain.php');
		$explain_obj = new Explain();
		$explain_obj->run();
		break;
	case 'reflection':
		include_once('./class/Reflection.php');
		$reflection_obj = new Reflections();
		$reflection_obj->run();
		break;
	case 'count':
		include_once('./class/Groupcount.php');
		$count_obj = new Groupcount();
		$count_obj->run();
		break;
	case 'getcount':
		include_once('./class/Code.php');
		$code_obj = new Code();
		$datas =  $code_obj->get_count();

		echo $dataFormat->dataTypeFormat($datas, $type);
		exit();
		break;
	case 'getmethodorder':
		include_once('./class/Code.php');
		$code_obj = new Code();
		$datas =  $code_obj->getmethodorder();

		echo $dataFormat->dataTypeFormat($datas, $type);
		exit();
		break;
	case 'tree':
		//$datas =  '{"content":[{"id":"1","fid":"0","rid":"1","name":"PHP","intro":null,"counts":"0","order":"0","child":[{"id":"6","fid":"1","rid":"1","name":"\u7cfb\u7edf\u51fd\u6570","intro":"\u7cfb\u7edf\u51fd\u6570","counts":"1","order":"0","child":[{"id":"7","fid":"6","rid":"1","name":"Apache \u7279\u6709\u51fd\u6570","intro":"\u672c\u7c7b\u51fd\u6570\u4ec5\u5728 PHP \u4f5c\u4e3a Apache \u7684\u6a21\u5757\u8fd0\u884c\u65f6\u53ef\u7528\u3002 ","counts":"1","order":"0"},{"id":"8","fid":"6","rid":"1","name":"Array \u6570\u7ec4\u51fd\u6570","intro":"\u672c\u7c7b\u51fd\u6570\u5141\u8bb8\u7528\u591a\u79cd\u65b9\u6cd5\u6765\u64cd\u4f5c\u6570\u7ec4\u548c\u4e0e\u4e4b\u4ea4\u4e92\u3002\u6570\u7ec4\u7684\u672c\u8d28\u662f\u50a8\u5b58\uff0c\u7ba1\u7406\u548c\u64cd\u4f5c\u4e00\u7ec4\u53d8\u91cf\u3002   PHP \u652f\u6301\u4e00\u7ef4\u548c\u591a\u7ef4\u6570\u7ec4\uff0c\u53ef\u4ee5\u662f\u7528\u6237\u521b\u5efa\u6216\u7531\u53e6\u4e00\u4e2a\u51fd\u6570\u521b\u5efa\u3002\u6709\u4e00\u4e9b\u7279\u5b9a\u7684\u6570\u636e\u5e93\u5904\u7406\u51fd\u6570\u53ef\u4ee5\u4ece\u6570\u636e\u5e93\u67e5\u8be2\u4e2d\u751f\u6210\u6570\u7ec4\uff0c\u8fd8\u6709\u4e00\u4e9b\u51fd\u6570\u8fd4\u56de\u6570\u7ec4\u3002   \u53c2\u89c1\u624b\u518c\u4e2d\u7684\u6570\u7ec4\u4e00\u8282\u5173\u4e8e PHP \u662f\u600e\u6837\u5b9e\u73b0\u548c\u4f7f\u7528\u6570\u7ec4\u7684\u8be6\u7ec6\u89e3\u91ca\u3002\u53c2\u89c1\u6570\u7ec4\u8fd0\u7b97\u7b26\u4e00\u8282\u5173\u4e8e\u600e\u6837\u64cd\u4f5c\u6570\u7ec4\u7684\u5176\u5b83\u65b9\u6cd5\u3002 ","counts":"1","order":"0"},{"id":"11","fid":"6","rid":"1","name":"Directory \u76ee\u5f55\u51fd\u6570","intro":"\u8981\u7f16\u8bd1\u672c\u6269\u5c55\u6a21\u5757\u4e0d\u9700\u8981\u5916\u90e8\u5e93\u6587\u4ef6\u3002","counts":"0","order":"0"},{"id":"12","fid":"6","rid":"1","name":"Date\/Time \u65e5\u671f\uff0f\u65f6\u95f4\u51fd\u6570","intro":"\u53ef\u4ee5\u7528\u8fd9\u4e9b\u51fd\u6570\u5f97\u5230 PHP \u6240\u8fd0\u884c\u7684\u670d\u52a1\u5668\u7684\u65e5\u671f\u548c\u65f6\u95f4\u3002\u53ef\u4ee5\u7528\u8fd9\u4e9b\u51fd\u6570\u5c06\u65e5\u671f\u548c\u65f6\u95f4\u4ee5\u5f88\u591a\u4e0d\u540c\u65b9\u5f0f\u683c\u5f0f\u5316\u8f93\u51fa\u3002 ","counts":"0","order":"0"}]},{"id":"13","fid":"1","rid":"1","name":"\u6269\u5c55\u51fd\u6570","intro":"\u7528\u6237\u4e0a\u4f20\u7684\u51fd\u6570","counts":"1","order":"0","child":[{"id":"15","fid":"13","rid":"1","name":"\u5206\u9875\u7c7b","intro":"\u5206\u9875\u7c7b","counts":"0","order":"0"},{"id":"14","fid":"13","rid":"1","name":"\u6587\u4ef6\u64cd\u4f5c","intro":"\u6587\u4ef6\u64cd\u4f5c","counts":"1","order":"0","child":[{"id":"16","fid":"14","rid":"1","name":"WINDOWS\u5e73\u53f0","intro":"WINDOWS\u5e73\u53f0","counts":"0","order":"0"},{"id":"17","fid":"14","rid":"1","name":"linux\u5e73\u53f0","intro":"linux\u5e73\u53f0","counts":"0","order":"0"}]}]}]}],"state":"1","start_time":"2011-06-16 17:14:15","end_time":"2011-06-16 17:14:15"}';
		include_once('./class/Code.php');
		$code_obj = new Code();
		echo $code_obj->get_file_tree();
		//$content = $dataFormat->dataTypeFormat( $datas,$rtype,$run->state,$run->errorCode );
		exit();
		break;
	case 'codelist':
		include_once('./class/Code.php');
		$code_obj = new Code();
		$fid = intval($_GET['pid']?$_GET['pid']:'0');
		$datas = $code_obj->get_classes($fid);
		echo $dataFormat->dataTypeFormat($datas, $type);

		exit();
		break;
	case 'search':
		include_once('./class/Code.php');
		$code_obj = new Code();
		$key = $_GET['key'];
		$datas = $code_obj->search_files($key);
		echo $dataFormat->dataTypeFormat($datas, $type);

		exit();
		break;
	case 'code':
		include_once('./class/Code.php');
		$code_obj = new Code();
		$id = intval($_GET['id']?$_GET['id']:'0');
		$datas = $code_obj->get_class_info($id);
		//print_r($datas);
		echo $dataFormat->dataTypeFormat($datas, $type);

		exit();
		break;
	case 'score':
		include_once('./class/Score.php');
		$score_obj = new Score();
		$id = intval($_GET['id']?$_GET['id']:'0');
		$datas = $score_obj->get_score($id);
		echo $dataFormat->dataTypeFormat($datas, $type);

		exit();
		break;
}











