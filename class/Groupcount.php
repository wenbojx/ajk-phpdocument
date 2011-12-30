<?php
/**
 *
 * 统计数据
 * @author wenboli
 * @todo 暂时没考虑数据安全，默认认为线上项目文件名不存在涉及安全的数据
 *
 */
class Groupcount{
    protected $db_obj = '';
    protected $count_datas = array();
    /**
     * 执行页面
     * @author wenboli
     */
    public function run() {
		//获取一级目录信息
        $folder_datas = $this->get_lever_one(1);
        //统计文件信息
        $this->get_files($folder_datas);
        //统计类信息
        $this->get_class_count($folder_datas);
        //统计类注释数
        $this->get_class_type_count($folder_datas, 'docblock');
		//$this->count_datas;
		//exit();
        //统计类中有intro信息数
        $this->get_class_type_count($folder_datas, 'intro');
        //统计类中有author信息数
        $this->get_class_type_count($folder_datas, 'author');
        //统计类中有param信息数
        $this->get_class_type_count($folder_datas, 'param');
        //统计方法信息
        $this->get_method_count($folder_datas);
        //统计类注释数
        $this->get_method_type_count($folder_datas, 'docblock');
        //统计方法中有intro信息数
        $this->get_method_type_count($folder_datas, 'intro');
        //统计方法中有author信息数
        $this->get_method_type_count($folder_datas, 'author');
        //统计方法中有param信息数
        $this->get_method_type_count($folder_datas, 'param');
		//print_r($this->count_datas);
        //保存统计信息
        echo $this->save_count_datas();
        //print_r($this->count_datas);
        
        //统计排名
        echo $this->count_order();
        //echo 11;
        //print_r($params);
        exit();
    }
    /**
     * 统计排名
     */
    public function count_order(){
    	global $CONFIG;
    	$count_datas = array();
    	foreach ($CONFIG['dev'] as $v) {
    		if(!$v){
    			continue;
    		}
    		$count = array();
    		$count = $this->get_db_obj()->get_author_methods($v);
    		//print_r($count);
    		$count_datas[$v] = $count['count(id)'];
    	}

    	arsort($count_datas);
    	$datas['datas'] = json_encode($count_datas);
        $datas['type'] = 2;
        $datas['release'] = RELEASE_VERSION;
        //print_r($datas);
        return $this->get_db_obj()->save_count($datas,2);
    	//return $count_datas;
    }
    /**
     * 保存统计数据
     * @author liwenbo
     */
    public function save_count_datas(){
        $datas['datas'] = json_encode($this->count_datas);
        $datas['type'] = 1;
        $datas['release'] = RELEASE_VERSION;
        return $this->get_db_obj()->save_count($datas);
    }
    /**
     * 获取所有一级目录下的method注释数
     * @author liwenbo
     * @type string $type 字段名
     */
    public function get_method_type_count($datas, $type){
        if(count($datas)<1){
            return false;
        }
        $file_datas = array();
        foreach($datas as $val){
        	$val['path'] = $val['path'].$val['name'].DIRECTORY_SEPARATOR;
            $this->count_datas[$val['name']]['method_'.$type] = $this->get_db_obj()->get_method_type_by_path($val['path'], $type);
        }
        return $this->count_datas;
    }
    /**
     * 获取所有一级目录下的method信息
     * @author liwenbo
     */
    public function get_method_count($datas){
        if(count($datas)<1){
            return false;
        }
        $file_datas = array();
        foreach($datas as $val){
        	$val['path'] = $val['path'].$val['name'].DIRECTORY_SEPARATOR;
            $this->count_datas[$val['name']]['method'] = $this->get_db_obj()->get_method_by_path($val['path']);
        }
        return $this->count_datas;
    }

    /**
     * 获取所有一级目录下的class注释数
     * @author liwenbo
     * @type string $type 字段名
     */
    public function get_class_type_count($datas, $type){
        if(count($datas)<1){
            return false;
        }
        $file_datas = array();
        foreach($datas as $val){
        	$val['path'] = $val['path'].$val['name'].DIRECTORY_SEPARATOR;
            $this->count_datas[$val['name']]['class_'.$type] = $this->get_db_obj()->get_class_type_by_path($val['path'], $type);
        }
        //print_r($this->count_datas);
        return $this->count_datas;
    }

    /**
     * 获取所有一级目录下的class信息
     * @author liwenbo
     */
    public function get_class_count($datas){
        if(count($datas)<1){
            return false;
        }
        $file_datas = array();
        foreach($datas as $val){
        	$val['path'] = $val['path'].$val['name'].DIRECTORY_SEPARATOR;
            $this->count_datas[$val['name']]['classes'] = $this->get_db_obj()->get_class_by_path($val['path']);
        }
        //print_r($this->count_datas);
        return $this->count_datas;
    }

    /**
     * 获取所有一级目录下的class文件数信息
     * @author liwenbo
     */
    public function get_files($datas){
        if(count($datas)<1){
            return false;
        }
        $file_datas = array();
        foreach($datas as $val){
        	$val['path'] = $val['path'].$val['name'].DIRECTORY_SEPARATOR;
            $this->count_datas[$val['name']]['files'] = $this->get_db_obj()->get_files_by_path($val['path']);
        }
        return $this->count_datas;
    }

    /**
     * 获取某级目录信息
     * @author liwenbo
     * @param int $pid 父目录ID
     */
    public function get_lever_one($pid=0){
        return $this->get_db_obj()->get_lever_info($pid);
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