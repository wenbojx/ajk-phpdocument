<?php
/**
 *
 * 扫描目录文件夹并保存数据
 * @author wenboli
 * @todo 暂时没考虑数据安全，默认认为线上项目文件名不存在涉及安全的数据
 *
 */
class File{
	protected $db_obj = null;
	public $root_dir = '';
	//需解析的文件类型
    public $filter_file_type = array(
            'php'
    );
	//排除的目录名
    public $exclude_folder = array(
            '.svn',
            '.',
            '..',
            '.settings',
            'config',
            'cache',
            'codelist'
    );
    public $filter_file = array(
	    'functions.php'
    );
    /**
     * 执行页面
     * @author wenboli
     */
    public function run($root_dir) {
    	if(!$root_dir){
        	die('no path');
        }
    	$this->root_dir = $root_dir;
        //保存根目录
        $root_dir = array(
                array(
                        'type' => 2,
                        'path' => '',
                        'name' => 'ajk_root',
                		'docs' => '',
                )
        );
        //保存根目录信息
        $id = $this->save_files(0, $root_dir);
        if(! $id) {
            $id = 1;
        }
        echo "开始扫描文件...\r\n<br>";
        $tree_file = $this->search_file('', array(), $id);
        echo "<br>扫描文件结束\r\n";
        exit();
    }

    /**
     * 搜索目录下所有.php文件
     * @author wenboli
     * @param string $dir 搜索的目录
     * @param array $tree_file 文件目录树
     * @param array $filter_file_type 筛选的文件类型
     * @param int $parent_id 上级目录id
     */
    public function search_file($dir, $tree_file = array(), $parent_id = 1) {
        $mydir = dir( $this->root_dir.$dir);
        //echo $this->root_dir.$dir;
        $i = 0;
        $files = array();
        $files_contain = array(); // 单目录下的文件及文件夹
        while($file = $mydir->read()) {

            if((is_dir($this->root_dir.$dir . DIRECTORY_SEPARATOR . $file)) && ! in_array($file, $this->exclude_folder)) {
                $folder[0]['type'] = 2;
                $folder[0]['name'] = $file;
                $folder[0]['path'] = $dir . DIRECTORY_SEPARATOR ;
                $parent_dir_id = $this->save_files($parent_id, $folder);
                //echo $parent_dir_id;
                $tree_file[$file] = $this->search_file( $dir . DIRECTORY_SEPARATOR . $file, $tree_file, $parent_dir_id);
                $files_contain[] = $file;
            } elseif(! in_array($file, $this->exclude_folder)) {
                $contents = file_get_contents($this->root_dir.$dir . DIRECTORY_SEPARATOR . $file);
                //获取文件后缀名
                $suffix = array_pop(explode('.', $file));
                if(! in_array($suffix, $this->filter_file_type)) {
                    continue;
                }
                //判断文件内容中是否含class 或function
                if(!$this->filter_class_function($contents)) {

                	echo($dir . DIRECTORY_SEPARATOR.$file).'不含类的文件------++-------------<br>';
                	//echo $contents;
                    continue;
                }
                if(in_array($file, $this->filter_file)){
                	continue;
                }
                $tree_file['ACMS_FILS_LISTS'][] = $file;
                $files[$i]['docs'] = $contents;
                unset($contents);
                //记录文件信息
                $files[$i]['type'] = 1;
                $files[$i]['name'] = $file;
                $files[$i]['path'] = $dir . DIRECTORY_SEPARATOR;
                $files_contain[] = $file;
                $i++;
            }
        }
        //比对数据库中的目录信息
        $del_list = $this->scan_folder_db($parent_id, $files_contain);
        if($del_list) {
            //更新列数据信息
            $this->get_db_obj()->del_files($del_list);
        }
        //保存用户数据
        if($files){
        	$this->save_files($parent_id, $files);
        }
        $mydir->close();
        return $tree_file;
    }

    /**
     * 保存文件或文件夹信息
     * @author wenboli
     * @param int $parent_id 父目录id
     * @param array $files 文件
     */
    public function save_files($parent_id = 1, $files = array()) {
        //构造数据
        if(! is_array($files) || ! $files) {
            return false;
        }
        foreach($files as $v) {
            //判断文件是否存在
            if($file_data = $this->get_db_obj()->check_file_exit($v['name'], $v['path'], $v['type'])) {
                //如果数据库中包含该文件，根据del值来执行
                if(isset($file_data['del']) && $file_data['del'] == 1) {
                    $id = $file_data['id'];
                    //如果是目录直接跳过
                    if($v['type'] == 2) {
                        continue;
                    }
                    $last_file_md5 = $file_data['filemd5'];
                    $now_file_md5 = md5_file($this->root_dir.$v['path'] . $v['name']);
                    if($last_file_md5 != $now_file_md5) {
                        //修改文件表中modify 为1已修改状态
                        $this->do_update($v, $parent_id, 'update', $id);
                    }
                }
                if(isset($file_data['del']) && $file_data['del'] == 0) {
                    //更新行信息del值为1
                    if($this->get_db_obj()->update_file(array(0=>$file_data['id']))) {
                        return $file_data['id'];
                    }
                }
                continue;
            }
            $id = $this->do_update($v, $parent_id);
        }
        unset($files);
        return $id;
    }
    /**
     * 保存或更新操作
     * @param array $v 文件信息
     * @param str $do insert or update
     */
    protected function do_update($v,$parent_id,$do='insert',$id=0){
    	$file['pid'] = $parent_id;
        $file['name'] = $v['name'];
        $file['path'] = $v['path'];
        $file['type'] = $v['type'];
        $file['docs'] = isset($v['docs'])&&$v['docs']?addslashes($v['docs']):'';
        $file['release'] = RELEASE_VERSION;
        $file['filemd5'] = $v['type'] == 1 ? md5_file($this->root_dir.$file['path'] . $file['name']) : '';

        if($file['type'] ==1){
            //解析文件中的class信息
	        $classes = $this->parser_class($file['docs']);
	
	        $str = json_encode($classes);
	        $file['doc_class'] = addslashes($str);
        }
        
        if($do=='insert'){
	        $id = $this->get_db_obj()->add_file($file);
        }
        else{
        	unset($file['release']);
        	$file['modify'] = 1;
        	$file['doc_class'] = addslashes($file['doc_class']);
        	$this->get_db_obj()->update_file($id, $file);
        }
        echo $file['path'] . $file['name'] . '<br>';

        $this->flush();
        unset($file);
        return $id;
    }
    /**
     * 解析文件中的class内容
     * @author liwenbo
     * @todo 此处 后期可优化
     */
    public function parser_class($str){
    	//echo "\n\n";
    	//echo $str;
        $str = $this->replace_enter_newline($str);
        //echo $str;

        $pattern = '/@abstract class (.*?)\{/';
        preg_match_all( $pattern, $str, $matches);
    	if(!$matches[0]){
	        $pattern = '/@final class (.*?)\{/';
	        preg_match_all( $pattern, $str, $matches);
        }

        if(!$matches[0]){
	        $pattern = '/@class (.*?)\{/';
	        preg_match_all( $pattern, $str, $matches);
        }
        //print_r($matches);
        //exit();
        if(!$matches && !$matches[0]){
        	//echo '-----11------'.$str;
        	//print_r($matches);
            //continue;
            return false;
        }
        $i = 1;
        $k = 0;
        $jieshao_start = 0;
        $jieshao_length = 0;
        $pattern = '/\/\*\*(.*?)\*\//';
        foreach($matches[0] as $val){
        	$val = substr($val, 1, strlen($val));
        	//echo $val;
            //查找第N个类出现的位置
            $start = strpos($str, $val);
            if($i==1){
                $jieshao_length = $start;
            }
            else{
                $jieshao_length = $start - $jieshao_start;
            }
            //如果存在下一个类
            if( isset($matches[0][$i]) ){
                $end = strpos($str, $matches[0][1]);
                $i++;
            }
            else {
                $end = strlen($str);
            }
            //获取第N个类的注释
            $str_jieshi = substr($str, $jieshao_start, $jieshao_length);
            $matches_jieshi = '';
            preg_match_all( $pattern, $str_jieshi, $matches_jieshi);
            //print_r($matches_jieshi);
            if(!$matches_jieshi[0]){
            	//$str_array[$k] = '';
            	$jieshao[$k] = '';
            }
            else{
	            $num = count($matches_jieshi[0])-1;
	            //echo $num;
	            $jieshao[$k] = $matches_jieshi[0][$num];
	            $jieshao_start = $start;
            }
            $str_array[$k] = substr($str, $start, ($end-$start));
            $i++;
            $k++;
        }
        $i = 0;
        $val = '';
        //print_r($jieshao);
        $classes = array();
        foreach ($str_array as $val){
            $str = '';
            //遍历
            $end = $this->find_class_pos($val);
            $str = substr($val, 0, $end+1);
            $str = $this->restore_enter_newline($str);
            $zhushi = $this->restore_enter_newline($jieshao[$i]);
            $str = $zhushi . "\n" . $str;
            $classes[] = $str;
            $i++;
        }
        //print_r($classes);
        return $classes;
    }
    /**
     * 逐个字符解析类的结束位置
     * @param string $str 文件内容
     * @todo 后期寻找更快捷的取class内容的方法
     */
    public function find_class_pos( $str, $start=0, $end=0 ){
        $k = 0;
        for($i=0; $i<strlen($str); $i++){
            $char = substr( $str, $i, 1 );
            if( $char== '{' || $char== '}'){
                if($char== '{'){
                    $k++;
                }
                else{
                    $k--;
                }
                if($k==0){
                    return $i;
                }
            }

        }
    }
    /**
     * 输出页面内容
     * Enter description here ...
     */
    public function flush() {
        ob_flush();
        flush();
    }

    /**
     * 比对数据库中文件夹信息和扫描到的文件夹信息
     * 删除文件及文件夹
     * @author wenboli
     * @param int $pid 父目录ID
     * @param array $files 扫描到的目录信息
     * @return array 需删除的文件ID
     */
    public function scan_folder_db($p_id, $files = array()) {
        if(count($files) < 1) {
            return false;
        }
        $db_files = $this->get_db_obj()->get_file_by_pid($p_id);
        if(! is_array($db_files) || count($db_files) < 1) {
            return false;
        }
        $del_list = array();
        foreach($db_files as $v) {
            if(! in_array($v['name'], $files) && $v['del'] != 0) {
                $del_list[] = $v['id'];
            }
        }
        return $del_list;
    }

    /**
     * 过滤字符串中含class或function 的文件
     */
    public function filter_class_function($str) {
        $str = $this->replace_enter_newline($str);
        $pattern = '/function (.*)\{/';
        preg_match($pattern, $str, $matches_fun);

    	$pattern = '/@abstract class (.*?)\{/';
        preg_match_all( $pattern, $str, $matches_class);
    	if(!$matches_class[0]){
	        $pattern = '/@final class (.*?)\{/';
	        preg_match_all( $pattern, $str, $matches_class);
        }
        if(!$matches_class[0]){
	        $pattern = '/@class (.*?)\{/';
	        preg_match_all( $pattern, $str, $matches_class);
        }
		//print_r($matches_class);
        if($matches_fun && $matches_class[0]) {
            return true;
        }
        //print_r($matches_fun);
        //print_r($matches_class);
        return false;
    }
    /**
     * 替换字符串中的换行和回车键
     * @author liwenbo
     */
    public function replace_enter_newline($str){
        if($str==''){
            return false;
        }
        $str = str_replace("\n", '@\n@', $str);
        $str = str_replace("\r", '@\r@', $str);
        return $str;
    }
    /**
     * 还原字符串中的换行回车替换符
     e* @author liwenbo
     */
    public function restore_enter_newline($str){
        if($str==''){
            return false;
        }
        $str = str_replace('@\n@', "\n", $str);
        $str = str_replace('@\r@', "\r", $str);
        return $str;
    }
    /**
     * 初始化 bll对象
     * @author wenboli
     *
     */
    protected function get_db_obj() {
    	if(!$this->db_obj){
    		include_once('./class/DB.php');
    		$this->db_obj = new DB();
    	}

        return $this->db_obj;
    }
}