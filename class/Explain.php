<?php
/**
 *
 * 解析文件中的class function信息
 * @author wenboli
 * @todo 暂时没考虑数据安全，默认认为线上项目文件名不存在涉及安全的数据
 *
 */
class Explain {

    protected $db_obj = null;

    protected $parser = null;

    protected $reflection_name = 'interface.php?do=reflection';

    /**
     * 执行页面
     * @author wenboli
     */
    public function run() {
    	global $relation;
    	
    	include_once('./class/ParserClass.php');
        $this->parser = new ParserClass();
		//exit();
		//获取正常的文件信息
        $file_list = $this->get_db_obj()->get_activit_file();
        if(!$file_list){
            die('没有可更新的文件');
        }
        $numss = 0;
        //echo count($file_list);

        //exit();
        //print_r($file_list);
        //解析错误的类
        $resolve_errors = array();
        foreach($file_list as $val) {
/*            if($numss>10){
                continue;
            }
            
            $numss++;*/
            if(! $val) {
                continue;
            }
            //print_r($val);
            $class_doc_datas = null;
            $class_datas = array();
            //解析类
            $class_datas = $this->resolve_file($val['id']);
            //print_r($class_datas);
            //exit();
            if(! $class_datas) {
            	$resolve_errors[] = $val['path'].$val['name'];
                continue;
            }

            $class_doc_datas = $class_datas;
            $class_doc_datas['fid'] = $val['id'];
            $class_doc_datas['path'] = $val['path'];
            $class_doc_datas['ffid'] = $val['pid'];
            
            if($class_datas = $this->check_class_exit($class_doc_datas['cname'], $class_doc_datas['fid'])) {

                //如果存在该类更新数据
                $this->update_class($class_datas['id'], $class_doc_datas);
                $pid = $class_datas['id'];
                
                echo 'update-----classname=' . $class_doc_datas['cname'] . '--' . $class_doc_datas['path'] . '-- ' . $val['id'] . '--extends : '.$class_doc_datas['extends'].'<br>';
                
            } else {
                //添加类信息
                $class_id = $this->add_class($class_doc_datas);
                $pid = $class_id;
                echo 'insert-----classname=' . $class_doc_datas['cname'] . '--' . $class_doc_datas['path'] . '-- ' . $val['id'] . '--extends : '.$class_doc_datas['extends'].'<br>';
            }

            if(!isset($class_doc_datas['methods']) || !$class_doc_datas['methods']) {
                continue;
            }

            foreach($class_doc_datas['methods'] as $v1) {
                $v1['path'] = $val['path'];
                
                //添加方法信息
                if($method_datas = $this->check_method_exit($v1['mname'], $pid)) {
                    //如果存在该方法更新数据
                    $this->update_method($pid, $v1);
                    echo 'update------functionname=' . $v1['mname'] . '<br>';
                    
                } else {
                    $method_id = $this->add_method($v1, $pid);
                    echo 'insert------functionname=' . $v1['mname'] . '<br>';
                }
            }
            echo '<br>';
            $this->update_modify($val['id'],2);
            $this->flush();
        }
        echo '<br><br><br>----------解析错误-------------';
        //print_r($resolve_errors);
        if($resolve_errors){
        	foreach ($resolve_errors as $k=>$v){
        		echo ($k+1).': '.$v.'<br>';
        	}
        }
        echo '----------end解析错误-------------<br><br><br>';
        //更新类pid信息
        //$this->update_class_pid();
        
        if($relation){
	        echo '----------正在处理方法依赖关系.....-------------<br><br><br>';
	        //更新方法信息，获取引用该方法的类
			$this->update_method_cite();
        }
        exit();
    }
    /**
     * 更新方法信息，获取引用该方法的类
     * @author liwenbo
     */
    public function update_method_cite(){
		//获取所有方法信息
		$func = $this->get_db_obj()->get_functions();
		//print_r($func);
		$i = 0;
		if($func){
			foreach ($func as $v){
				//获取引用过该方法的类
				$quote_datas = $this->get_db_obj()->get_methods_quote($v['mname'], $v['cname'], $v['pid']);
				if($quote_datas){
					$quote_str = '';
					$up_datas = '';
					foreach ($quote_datas as $vs){
						$quote_str .= $vs['id'].'|'.$vs['cname'].',';
					}
					$up_datas['quote'] = $quote_str;
					if($v['id'] && $up_datas['quote']){
						$this->get_db_obj()->update_method_by_id($v['id'], $up_datas);
						echo "------{$v['id']}-{$v['mname']}-------<br>";
						$this->flush();
					}
				}
			}
			
		}
    }
    /**
     * 更新pid信息
     * @author liwenbo
     * @param array $extends 需更新的类
     */
    public function update_class_pid(){
    	$extends = $this->get_extends();

        if(!is_array($extends)){
            return false;
        }
        //print_r($extends);
        echo "<br><strong>update pid</strong><br>";
        foreach($extends as $v){
            $where['cname'] = $v['extends'];
            //获取该类信息
            $class_info = $this->get_db_obj()->get_class_by_name($v['extends']);
            $pid = 0;
            //处理class名相同的情况，按path的相似度为准
            if(count($class_info)>1){
                $pid = $this->similar_text($class_info, $v['path']);
            }
            elseif(count($class_info)==1){
                $pid = $class_info[0]['id'];
            }
            $datas['pid'] = $pid;
            echo $pid.'<br>';
            $id = $this->get_db_obj()->update_class_by_id($v['id'], $datas);

        }
    }
    /**
     * 从一组字符串中获取与给定字符串中最相似的
     * @author liwenbo
     * @param array $path_array
     * @param string $path
     */
    public function similar_text($path_array, $path){
        if(!is_array($path_array) || $path == ''){
            return false;
        }
        $id = 0;
        $similar_persent = 0;
        foreach($path_array as $k=>$v){
            similar_text($v['path'],$path, $similar_persent_this);
            if($similar_persent_this > $similar_persent){
                $similar_persent = $similar_persent_this;
                $id = $v['id'];
            }
        }
        return $id;
    }
    /**
     * 获取所有extends不为空的类
     * @author liwenbo
     */
    public function get_extends(){
        return $this->get_db_obj()->get_extends_class();
    }
    /**
     * 调用远程接口解析类
     * @author wenboli
     */
    public function resolve_file($id) {
    	global $domain;
        $url = $domain.$this->reflection_name;

        $resolve_string = file_get_contents($url . '&id=' . $id);
        //echo $url . '&id=' . $id;
        /*if($id==302){
        	echo $url . '&id=' . $id.'<br>';
        	echo $resolve_string;
        	exit();
        }*/

        //echo $resolve_string;
        //获取{}中的内容
        $pattern = '/\{(.*)\}/';
        preg_match( $pattern, $resolve_string, $matches);
        if( !isset($matches[0]) || $matches[0]==''){
            return false;
        }
        if($data = json_decode($matches[0], true)) {
            return $data;
        }
        return false;
    }

    /**
     * 修改文件信息中de modify值
     * @author wenbli
     *
     */
    public function update_modify($id, $v = 1) {
        if(! $id) {
            return false;
        }
        return $this->get_db_obj()->update_file($id, array(
                'modify' => $v
        ));
    }

    /**
     * 添加类信息
     * @author wenboli
     */
    public function add_class($datas) {
    	
        unset($datas['methods']);
        $datas['release'] = RELEASE_VERSION;
        $id = $this->get_db_obj()->add_class($datas);
        //更新文件状态 modify 为2未修改
        //self::update_modify ($datas['fid'], 2);
        return $id;
    }

    /**
     * 更新类信息
     * @author wenboli
     */
    public function update_class($id, $datas) {
        if(! $id) {
            return false;
        }
        //echo $id.'<br>';
        if(isset($datas['methods'])) {
            unset($datas['methods']);
        }
        $fid = $datas['fid'];
        unset($datas['path']);
        unset($datas['fid']);
        unset($datas['cname']);
        //print_r($datas);
        if($datas['docblock']){
        	$datas['docblock'] = addslashes($datas['docblock']);
        }
        $id = $this->get_db_obj()->update_class_by_id($id, $datas);
        //更新文件状态 modify 为2未修改
        //self::update_modify ($fid, 2);
        return $id;
    }

    /**
     * 添加方法信息
     * @author wenboli
     */
    public function add_method($datas, $pid) {
        $datas['pid'] = $pid;
        $datas['release'] = RELEASE_VERSION;
        $id = $this->get_db_obj()->add_method($datas);
        return $id;
    }

    /**
     * 更新方法信息
     * @author wenboli
     */
    public function update_method($id, $datas) {
        if(! $id) {
            return false;
        }
        unset($datas['pid']);
        unset($datas['mname']);
        if(isset($datas['docblock'])){
        	$datas['docblock'] = addslashes($datas['docblock']);
        	//print_r($datas['docblock']);
        }
        if(isset($datas['intro'])){
        	//echo 111;
        	$datas['intro'] = addslashes($datas['intro']);
        }
        //$datas['path'] = addslashes($datas['path']);
        //print_r($datas['docblock']);
        $id = $this->get_db_obj()->update_method_by_id($id, $datas);
        return $id;
    }

    /**
     * 查询数据库中是否存在该类
     * @author wenboli
     *
     */
    public function check_class_exit($class_name, $fid) {
        if($class_name == '' || $fid == '') {
            return false;
        }
        $datas = $this->get_db_obj()->get_class($class_name, $fid);
        return $datas ? $datas : false;
    }
    /**
     * 根据类名查找类信息
     * @author wenboli
     *
     */
    public function get_class_extends($class_name) {
        if($class_name == '') {
            return false;
        }
        $datas = $this->get_db_obj()->get_class_by_name($class_name);
        return $datas ? $datas : false;
    }

    /**
     * 查询数据库中是否存在该方法
     * @author wenboli
     *
     */
    public function check_method_exit($method_name, $pid) {
        if($method_name == '' || $pid == '') {
            return false;
        }
        $datas = $this->get_db_obj()->get_method($method_name, $pid);
        return $datas ? $datas : false;
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