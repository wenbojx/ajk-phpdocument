<?php
class DataFormat
{
	private $start_time = 0;			// 处理的开始时间，UNIX_TIMESTAMP
	private $end_time = 0;				// 处理的结束时间，UNIX_TIMESTAMP
	private $content = null;			// 主体内容，String
	private $state = 1;                 //输出内容状态0为出错1为正常
	private $error_code = '';           //错误代码
	private $charset = 'utf-8';         //输出编码
	private $attribute = false;          //是否输出带属性的xml
	public function __construct()
	{
		$this->start_time = time();
	}
	/**
	 * 帮助数据提供者输出标准格式的内容。
	 * 方法返回的内容就是输出给数据使用者的全部数据（封装后的新数据或者在header中写入了标准格式）。
	 * 默认是把标准格式写入到 header 中，这样就不会改变输出的主体内容。（注：在 header 方法前不能有数据输出）
	 * @param String $content 输出的主体内容
	 * @param String $result_id 操作代码
	 * @param String $result_msg 操作描述信息
	 * @return String 把数据进行格式化封装后的新数据
	 */
	public function outPut ( $content,$type="html", $result_id = null, $result_msg = null )
	{
		switch ($type){
			case 'html':
				header("Content-type: text/html");
				break;
			case 'xml':
				header("Content-type: text/xml");
				break;
		}

		$this->end_time = time();
		if($result_id){
			header("result_id: {$result_id}");
		}
		if($result_msg){
			header("result_msg: {$result_msg}");
		}
		header('start_time: '.date('Y-m-d H:i:s', $this->start_time));
		header('end_time: '.date('Y-m-d H:i:s', $this->end_time));
		echo $content;
	}
	/**
	* 处理通过接口返回的数据类型.
	* @author 李文博 <faashi@gmail.com>
	* @version 1.0
	* @CopyRight : faashi.com
	* @date 2009-06-29
	* @function dataFormat
	* @param array $data 需转换的数组或字符
	* @param array $type 需返回的数据类型
	* @return string or json or jsonp or xml or serialize
	*/
	public function dataTypeFormat( $data , $type, $state=1,$error_code='',$attribute = false, $charset='utf-8')
	{
		$this->attribute = $attribute;
		$this->charset = $charset;
		$this->state = $state;
		$this->error_code = $error_code;
		$type = $type==''?'json':$type;
		switch ($type)
		{
			case 'json':
				return self::createJson($data);
			break;
			case 'xml':
				return self::createXml ($data);
			break;
			case 'string':
				return $data;
			break;
			case 'serialize':
				return self::createSerialize($data);
			break;
		}
	}
	/**
	* 处理通过接口返回json数据.
	* @author 李文博 <faashi@gmail.com>
	* @version 1.0
	* @CopyRight : faashi.com
	* @date 2009-06-29
	* @function createJson
	* @param array $data 需转换的数组或字符
	* @return string json
	*/
	public function createSerialize($data)
	{
		$content = array();
		$content['content'] = $data;
		$content['state'] = $this->state;
		if(!$this->state){
			$content['errorCode'] = $this->error_code;
		}
		$this->end_time = time();
		$content['start_time'] = date('Y-m-d H:i:s', $this->start_time);
		$content['end_time'] = date('Y-m-d H:i:s', $this->end_time);
		$output = serialize($content);
		return $output;
	}
	/**
	 * 将参数data数组转换为JSON格式，返回字符串，如果指定了输出编码，则内容已经执行了编码转换。
	 * 如果指定了result_id,result_msg，则会把这两个数据也写入到JSON数据中。
	 * @param Array $data 数据体
	 * @param String $result_id 操作代码
	 * @param String $result_msg 操作描述信息
	 * @return String JSON格式的数据
	 */
	public function createJson( $data )
	{
		$content = array();
		$content['content'] = $data;
		$content['state'] = $this->state;
		if(!$this->state){
			$content['errorCode'] = $this->error_code;
		}
		$this->end_time = time();
		$content['start_time'] = date('Y-m-d H:i:s', $this->start_time);
		$content['end_time'] = date('Y-m-d H:i:s', $this->end_time);
		$output = json_encode($content);
		return $output;
	}
	/**
	* 处理通过接口返回xml数据.
	* @author 李文博 <faashi@gmail.com>
	* @version 1.0
	* @CopyRight : faashi.com
	* @date 2009-06-29
	* @function interface_back_xml
	* @param array $data 需转换的数组或字符
	* @return string xml
	*/
	/**
	 * 将参数data数组转换为XML格式，XML字符串，如果指定了输出编码，则内容已经执行了编码转换。
	 * 如果指定了result_id,result_msg，则会把这两个数据也写入到Xml数据中。
	 * @param Array $data 数据体
	 * @param String $result_id 操作代码
	 * @param String $result_msg 操作描述信息
	 * @return String
	 */
	public function createXml( $data )
	{
		if ($this->charset != 'utf-8')
		{
			$doc = new DOMDocument('1.0', strtoupper($this->params['out_charset']));
		}
		else
		{
			$doc = new DOMDocument('1.0', 'UTF-8');
		}
		$parent = $doc->createElement('root');
		$doc->appendChild($parent);
		if (is_array($data))
		{
			$node = $doc->createElement('content');
			$node = $this->createChildNode($doc, $node, $data);
		}
		else
		{
			$node = $doc->createElement('content', $data);
		}
		$parent->appendChild($node);
		$node = $doc->createElement('state', $this->state);
		$parent->appendChild($node);
		if(!$this->state){
			$node = $doc->createElement('errorCode', $this->error_code);
			$parent->appendChild($node);
		}
		$this->end_time = time();
		$node = $doc->createElement('start_time', date('Y-m-d H:i:s', $this->start_time));
		$parent->appendChild($node);
		$node = $doc->createElement('end_time', date('Y-m-d H:i:s', $this->end_time));
		$parent->appendChild($node);
		$output = $doc->saveXML();
		if (ord($output[strlen($output) - 1]) == 10)
		{
			$output = substr($output, 0, strlen($output) - 1);
		}
		return $output;
	}
	/**
	 * 在节点node下面增加data中的数据
	 * @param XmlDocument $doc XML文档对象
	 * @param XmlElement $node XML节点对象
	 * @param Array $data 数据
	 * @return XmlElement
	 */
	/*private function createChildNode($doc, $node, $data)
	{
		foreach ($data as $key => $value)
		{

			if (is_array($value))
			{
				if (is_numeric($key))
				{
					$key = 'item_'.$key;
				}
				$child = $doc->createElement($key);
				$child = $this->createChildNode($doc, $child, $value);
			}
			else
			{
				$child = $doc->createElement($key, $value);
			}
			if ($child)
			{
				$node->appendChild($child);
			}
		}
		return $node;
	}*/
/**
	 * 在节点node下面增加data中的数据并创建属性
	 * @param XmlDocument $doc XML文档对象
	 * @param XmlElement $node XML节点对象
	 * @param Array $data 数据
	 * @return XmlElement
	 */
	private function createChildNode($doc, $node, $data)
	{
		foreach ($data as $key => $value)
		{
			$flag = true;  //是否建立下级子节点
			if (is_array($value))
			{
				$key = 'item';
				/*if (is_numeric($key))
				{
					$key = 'item_'.$key;
				}*/
				$child = $doc->createElement($key);
				if($this->attribute && isset($value['item_attr']) && is_array($value['item_attr'])){
					foreach($value['item_attr'] as $key_a => $value_a){
						$child->setAttribute($key_a,$value_a);
					}
					if(isset($value['item_lists']) && is_array($value['item_lists'])){
						$value = $value['item_lists'];
					}
					else{
						$flag = false;
					}
				}
				if($flag)
				$child = $this->createChildNode($doc, $child, $value);

			}
			else
			{
				$child = $doc->createElement($key, $value);
			}
			if ($child)
			{
				$node->appendChild($child);
			}
		}
		return $node;
	}
}


?>