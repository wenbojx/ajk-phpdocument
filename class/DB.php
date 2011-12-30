<?php
class DB{
	private $mysql_obj = null;
	private $file_table = 'acms_files';
	private $class_table = 'acms_classes';
	private $method_table = 'acms_methods';
	private $count_table = 'acms_count';
	/**
	 * 删除文件表中的某行
	 * @author liwenbo
	 */
	public function update_file( $id, $datas ){
		if(!$id){
			return false;
		}
		//更新行信息del值为1
		$where = ' id='.$id;
		
		if($id==1024){
			print_r($datas);
		}
		$this->get_mysql_obj()->update($this->file_table, $datas, $where);
	}

	/**
     * 获取某级目录信息
     * @author liwenbo
     * @param int $pid 父目录ID
     */
    public function get_lever_info($pid){
    	$sql = "SELECT * FROM {$this->file_table} WHERE pid='{$pid}' AND type='2' AND del='1'";
		$datas = $this->get_mysql_obj()->fetchAll($sql);
		return $datas;
    }
	/**
	 * 添加文件信息
	 * @author liwenbo
	 */
	public function add_file($datas){
		if(!$datas){
			return false;
		}
		return $this->get_mysql_obj()->insert($this->file_table,$datas);
	}

    /**
     * 查询数据库中是否存在该文件或文件夹
     * @author wenboli
     * @return array $datas 返回文件信息，如无数据返回false;
     */
    public function check_file_exit($filename, $filepath, $type) {
        if($filename == '' || $type == '') {
            return false;
        }
        $sql = "SELECT * FROM {$this->file_table} WHERE name='{$filename}' AND path='{$filepath}' AND type='{$type}' LIMIT 1";
		$datas = $this->get_mysql_obj()->fetchOne($sql);
        return $datas;
    }
    /**
     * 根据pid获取文件信息
     * @author liwenbo
     */
    public function get_file_by_pid($pid){
		if(!$pid){
			return false;
		}
		$sql = "SELECT * FROM {$this->file_table} WHERE pid='{$pid}'";
		$datas = $this->get_mysql_obj()->fetchAll($sql);
        return $datas;
    }
	/**
     * 根据关键字获取文件信息
     * @author liwenbo
     */
    public function get_file_by_keyworld($key){
		if(!$key){
			return false;
		}
		$sql = "SELECT id FROM {$this->file_table} WHERE docs LIKE '%{$key}%'";
		$datas = $this->get_mysql_obj()->fetchAll($sql);
		$files = array();
		if($datas){
			foreach ($datas as $v){
				$files[] = $v['id'];
			}
		}
        return $files;
    }
	/**
     * 根据id获取文件信息
     * @author liwenbo
     */
    public function get_file_by_id($id){
		if(!$id){
			return false;
		}
		$sql = "SELECT * FROM {$this->file_table} WHERE id='{$id}' LIMIT 1";
		$datas = $this->get_mysql_obj()->fetchOne($sql);
        return $datas;
    }

    /**
     * 批量删除文件
     * @author wenboli
     * @param array $ids 需删除的列
     */
    public function del_files($ids) {
        if(count($ids) < 1) {
            return false;
        }
        foreach ($ids as $v){
        	$flag = $this->update_file($v, array('del'=>0));
        }
        return $flag;
    }
	/**
     * 获取可用文件列表
     * @author wenboli
     */
    public function get_activit_file() {
        $sql = "SELECT id,pid,path,name,filemd5,type FROM {$this->file_table} WHERE type='1' and modify=1 and del =1";
		$datas = $this->get_mysql_obj()->fetchAll($sql);
        return $datas;
    }
	/**
     * 获取单个类信息 by id
     * @author wenboli
     */
    public function get_class_by_id ($id){
        $sql = "SELECT * FROM {$this->class_table} WHERE id='{$id}' LIMIT 1 ";
		$datas = $this->get_mysql_obj()->fetchOne($sql);

        return $datas;
    }
	/**
     * 获取单个类信息 by name
     * @author wenboli
     */
    public function get_class_by_name ($name){
        $sql = "SELECT * FROM {$this->class_table} WHERE cname='{$name}' ";
		$datas = $this->get_mysql_obj()->fetchAll($sql);
        return $datas;
    }
	/**
	 * 获取某类的子类信息
	 * @author liwenbo
	 * @param int $id 父ID
	 */
    public function get_child_class($id){
    	$sql = "SELECT id,fid,pid,ffid,path,cname,extends FROM {$this->class_table} WHERE pid='{$id}' ";
		$datas = $this->get_mysql_obj()->fetchAll($sql);
		//print_r($datas);
        return $datas;
    }
	/**
     * 获取单个类信息 by name, fid
     * @author wenboli
     */
    public function get_class ($name, $fid){
        $sql = "SELECT * FROM {$this->class_table} WHERE cname='{$name}' AND fid='{$fid}' LIMIT 1";
		$datas = $this->get_mysql_obj()->fetchOne($sql);
        return $datas;
    }
	/**
	 * 获取多个类信息
	 */
    public function get_class_by_ids ($ids){
    	if(!$ids){
    		return array();
    	}
    	$ids_str = implode(',', $ids);
    	$sql = "SELECT id,fid,pid,intro,ffid,path,cname,extends FROM {$this->class_table} WHERE fid in ({$ids_str}) ";
		$datas = $this->get_mysql_obj()->fetchAll($sql);
		return $datas;
    }

	/**
     * 根据ID更新类数据
     * @author wenboli
     */
    public function update_class_by_id ($id, $data){
    	$where = ' id='.$id;
		return $this->get_mysql_obj()->update($this->class_table, $data, $where);
    }
    /**
     * 添加类
     * @author wenboli
     */
	public function add_class($datas){
		return $this->get_mysql_obj()->insert($this->class_table,$datas);
	}
	/**
     * 获取所有extends不为空的类
     * @author liwenbo
     */
    public function get_extends_class(){

        $sql = "SELECT * FROM {$this->class_table} WHERE extends!='' AND `pid` IS NULL ";
        //echo $sql;
		$datas = $this->get_mysql_obj()->fetchAll($sql);
        return $datas;
    }
	/**
     * 获取单个方法信息 by name, pid
     * @author wenboli
     */
    public function get_method ($name, $pid){

    	$sql = "SELECT * FROM {$this->method_table} WHERE mname='{$name}' AND pid='{$pid}' LIMIT 1";
        $row = $this->get_mysql_obj()->fetchOne($sql);
        return $row;
    }
    /**
     * 获取所有方法信息
     * @author liwenbo
     */
    public function get_functions(){
    	$sql = "SELECT A.id,A.pid,A.mname, B.fid,B.cname FROM {$this->method_table} as A, {$this->class_table} as B WHERE A.del='1' AND A.pid=B.id ";
        //echo $sql;
		$datas = $this->get_mysql_obj()->fetchAll($sql);
		return $datas;
    }
    /**
     * 获取引用过某方法的类信息
     * @author liwenbo
     * @param string $mname 方法名
     * @param string $cname 类名
     */
    public function get_methods_quote($mname,$cname,$pid){
    	if(!$mname || !$cname){
    		return array();
    	}
    	$sql = "SELECT id,cname FROM {$this->class_table} WHERE id!={$pid} and  ( docblock like '%->{$mname}%' or docblock like '%::{$mname}%') and docblock like '%{$cname}%'";
    	//echo $sql."<br>";
    	$datas = $this->get_mysql_obj()->fetchAll($sql);
		return $datas;
    }
	/**
     * 根据ID更新方法数据
     * @author wenboli
     */
    public function update_method_by_id ($id, $data){
    	$where = ' id='.$id;
		return $this->get_mysql_obj()->update($this->method_table, $data, $where);
    }
	/**
     * 添加方法
     * @author wenboli
     */
	public function add_method($datas){
		return $this->get_mysql_obj()->insert($this->method_table,$datas);
	}


	///////////////////////   统计   /////////////////////////

	/**
     * 获取含class的文件数
     * @author liwenbo
     * @param str $path 文件目录
     */
    public function get_files_by_path($path){
    	if(!$path){
        	return 0;
        }
        $sql = "SELECT id FROM {$this->file_table} WHERE type='1' AND path LIKE '%{$path}%' AND del=1";
        return $this->get_mysql_obj()->count($sql);
    }
	/**
     * 获取含class数
     * @author liwenbo
     * @param str $path 文件目录
     */
    public function get_class_by_path($path){
        if(!$path){
        	return 0;
        }
        $sql = "SELECT id FROM {$this->class_table} WHERE path LIKE '%{$path}%' AND del=1";
        return $this->get_mysql_obj()->count($sql);
    }
	/**
     * 获取含class注释数
     * @author liwenbo
     * @param str $path 文件目录
     * @type string $type 字段名
     */
    public function get_class_type_by_path($path, $type){
        if(!$path || !$type){
        	return 0;
        }
        $sql = "SELECT id FROM {$this->class_table} WHERE path LIKE '%{$path}%' AND {$type}!='' AND del=1";
        //echo $sql;
        return $this->get_mysql_obj()->count($sql);
    }
	/**
     * 获取含method数
     * @author liwenbo
     * @param str $path 文件目录
     */
    public function get_method_by_path($path){
        if(!$path){
        	return 0;
        }
        $sql = "SELECT id FROM {$this->method_table} WHERE path LIKE '%{$path}%' AND del=1";
        return $this->get_mysql_obj()->count($sql);
    }
    /**
     * 获取含method注释数
     * @author liwenbo
     * @param str $path 文件目录
     */
    public function get_method_type_by_path($path, $type){
    	if(!$path || !$type){
        	return 0;
        }
        $sql = "SELECT id FROM {$this->method_table} WHERE path LIKE '%{$path}%' AND {$type}!='' AND del=1";
        return $this->get_mysql_obj()->count($sql);
    }
	/**
	 * 根据release获取统计信息
	 */
    public function get_count_by_release($release, $type){
    	if(!$release){
    		return false;
    	}
    	$sql = "SELECT id FROM {$this->count_table} WHERE `type`='{$type}' AND `release`='{$release}'";
    	//echo $sql;
        return $this->get_mysql_obj()->count($sql);
    }
	/**
	 * 根据release更新统计信息
	 */
    public function update_count_by_release($datas){
    	if(!$datas['release']){
    		return false;
    	}
    	$where = ' `release`='.$datas['release'];
    	unset($datas['release']);
    	$datas['datas'] = addslashes($datas['datas']);
		return $this->get_mysql_obj()->update($this->count_table, $datas, $where);
    }
    /**
     * 保存统计记录
     */
    public function save_count($datas,$type=1){
    	if(!$datas){
    		return false;
    	}
    	if(!$this->get_count_by_release( $datas['release'], $type)){
    		//print_r($datas);
			return $this->get_mysql_obj()->insert($this->count_table,$datas);
    	}
		return $this->update_count_by_release($datas);
    }
    //获取所有分类信息
	public function get_sorts( )
	{
		$sql = "SELECT id,pid,name FROM {$this->file_table} WHERE type=2 AND del = 1 ORDER BY name ASC";
		$sortData = $this->get_mysql_obj()->fetchAll($sql);
		if(!$sortData){
			return false;
		}
		return $sortData;
	}
	/**
	 * 获取某目录下类信息
	 * @author liwenbo
	 */
	public function get_classes ($fid = ''){
		if(!$fid){
			return false;
		}
		$sql = "SELECT id,pid,fid,ffid,cname,path,extends,author,intro FROM {$this->class_table} WHERE ffid={$fid} AND del = 1 ORDER BY cname ASC";
		//echo $sql;
		$listData = $this->get_mysql_obj()->fetchAll($sql);
		if(!$listData){
			return false;
		}
		return $listData;
	}
	/**
	 * 获取某类的信息及方法信息
	 * @author liwenbo
	 */
	public function get_class_info($id){
		if(!$id){
			return false;
		}
		$sql = "SELECT A.*, B.name FROM {$this->class_table} AS A, {$this->file_table} as B WHERE A.id={$id} AND A.del = 1 AND A.fid=B.id ORDER BY A.cname ASC";
		$datas = $this->get_mysql_obj()->fetchOne($sql);
		//print_r($datas);
		if($datas){
			$datas['method'] = $this->get_methods_list($datas['id']);

		}
		//print_r($datas);
		return $datas;
	}
	/**
	 * 获取某个类的方法信息
	 * @author liwenbo
	 */
	public function get_methods_list($id){
		if(!$id){
			return false;
		}
		$sql = "SELECT * FROM {$this->method_table} WHERE pid={$id} AND del = 1 ORDER BY mname ASC";
		$datas = $this->get_mysql_obj()->fetchAll($sql);
		$return = array();
		if($datas){
			foreach ($datas as $k=>$v){
				$return[$k] = $v;
				if($v['quote']){
					$v['quote'] = substr($v['quote'], 0, -1);
					$list = explode(',', $v['quote']);
					$quotes = array();
					$i = 0;
					foreach ($list as $v1){
						$v_ex = explode('|', $v1);
						$quotes[$i]['id'] = $v_ex[0];
						$quotes[$i]['name'] = $v_ex[1];
						$i++;
					}
					$return[$k]['quotes'] = $quotes;
				}
			}
		}
		return $return;
	}
	public function get_author_methods($name){
		$sql = "SELECT count(id) FROM {$this->method_table} WHERE author LIKE '%{$name}%'";
		return $this->get_mysql_obj()->fetchOne($sql);
	}
	/**
	 * 获取统计数据
	 */
	public function get_counts( )
	{
		$sql = "SELECT * FROM {$this->count_table} ORDER BY id DESC";
		//echo $sql;
		$countsData = $this->get_mysql_obj()->fetchAll($sql);
		if(!$countsData){
			return false;
		}
		return $countsData;
	}
	/**
     * 初始化 bll对象
     * @author wenboli
     *
     */
    protected function get_mysql_obj() {
    	if(!$this->mysql_obj){
    		include_once('./class/Mysql.php');

    		$this->mysql_obj = Mysql::getInstance('default', true);
    		//print_r($this->mysql_obj);
    	}
        return $this->mysql_obj;
    }

}