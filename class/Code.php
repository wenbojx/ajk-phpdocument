<?php
/**
 *
 * 扫描目录文件夹并保存数据
 * @author wenboli
 * @todo 暂时没考虑数据安全，默认认为线上项目文件名不存在涉及安全的数据
 *
 */
class Code{
	protected $db_obj = null;

	/**
	 * 获取某目录树信息
	 * @author liwenbo
	 */
	public function get_file_tree($pid = 0){
		$datas = $this->get_db_obj()->get_sorts();
		//echo count($datas);
		include_once('./class/FieldToTree.php');
		//将数据处理成树结构
		$fieldToTree = new FieldToTree();
		$sortDatas = $fieldToTree->createTree( $datas, '', '', '', 'json');
		return $sortDatas;
	}
	/**
	 * 获取方法数排名
	 */
	public function getmethodorder(){
		
	}
	/**
	 * 获取全局统计数据
	 */
	public function get_count(){
		$datas = $this->get_db_obj()->get_counts();
		//print_r($datas);
		$count_datas = array();
		if($datas){
			foreach($datas as $v){
				//$v['info'] = json_decode($v['datas'], true);
				$type = $v['type']==1?'code':'dev';
				$count_datas[$v['release']][$type] = json_decode($v['datas'], true);
			}
		}
		//print_r($count_datas);
		return $count_datas;
	}
	/**
	 * 搜索类或方法
	 */
	public function search_files($key){
		$datas = $this->get_db_obj()->get_file_by_keyworld($key);
		if($datas){
			$classes = $this->get_db_obj()->get_class_by_ids($datas);
			return $classes;
		}
		return array();
	}
	/**
	 * 获取某目录下类信息
	 * @author liwenbo
	 */
	public function get_classes ($fid = ''){
		if(!$fid){
			return false;
		}
		$datas = $this->get_db_obj()->get_classes($fid);
		//print_r($datas);
		return $datas;
	}
	/**
	 * 获取某类的信息及方法信息
	 * @author liwenbo
	 */
	public function get_class_info($id){
		if(!$id){
			return false;
		}
		$datas = $this->get_db_obj()->get_class_info($id);
		if($datas['pid']){
			$datas['extends_str'] = $datas['extends'];
			$replace_str = $datas['cname'].' extends ';
			$replace_str .= "{$datas['extends']}";
			$datas['docblock'] = str_ireplace($datas['cname'], $replace_str, $datas['docblock']);
			//获取子类信息
		}
		$datas['childs_node'] = array();
		$childs = $this->get_db_obj()->get_child_class($datas['id']);
		if($childs){
			$datas['childs_node'] = $childs;
		}
		//print_r($datas);
		return $datas;
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