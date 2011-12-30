<?php
/**
* 将数据库表中数据转换成多维数组最终转换成xml或json
*
* @author 李文博 <faashi@gmail.com>
* @version 1.0
* @package columns
* @CopyRight faashi.com
* @date 2009-06-29
*/
class FieldToTree
{
	var $dataFormat = '';
	/**
	* 返回数据.
	* @author 李文博 <faashi@gmail.com>
	* @version 1.0
	* @CopyRight : faashi.com
	* @date 2009-06-29
	* @function createTree
	* @param array $datas
	* @param string $pid 父ID字段
	* @param string $id ID字段
	* @param string $type 转换后的数据类型array,xml,json,其它
	* @param string $rootValue 根的值
	* @return $type决定
	*/
	public function createTree($treeDatas, $id='id', $pid='pid', $rootValue=0, $type='array', $order='id')
	{
		$datas = self::creatArray($treeDatas, $id='id', $pid='pid', $rootValue=0, $order);
		if(empty($datas)){
			return '';
		}
		include_once('./class/DataFormat.php');
		$this->dataFormat = new DataFormat();
		if($type=='array'){
			return $datas;
		}
		return $this->dataFormat->dataTypeFormat($datas, $type);
	}
	//构造成多维数组 $rootValue
	public function creatArray($treeDatas, $id='id', $pid='pid', $rootValue=0 ,$order='id')
	{
		$data = '';
		$i = 0;
		foreach($treeDatas as $k=>$v){
			if($v[$pid] == $rootValue){
				$data[$i] = $v;
				unset($treeDatas[$k]);
				$childDatas = $this->creatArray($treeDatas, $id, $pid, $v[$id]);
				if( !empty($childDatas)){
					$data[$i]['child'] = $childDatas;
				}
				$i++;
			}
		}
		return $this->array2sort($data, $order);
	}
	/**
	 * @name array2sort
	 * @desc 对二维数组排序
	 * @param array $datas
	 * @param string $item
	 * @return array
	 * @access public
	 **/
	public function array2sort($datas,$item,$sort=SORT_ASC) {
	   if(!is_array($datas) || empty($item)){
			return false;
	   }
	   $order = array();
	   foreach ($datas as $key => $row) {
				$order[$key] = $row[$item];
		}
		array_multisort($order, $sort ,$datas);
		return $datas;
	}
}


?>