<?php
$score_info = array(
	'note'=>array(
		'total'=>50, //注释总分50
		'child'=>array(
			'0'=>-5, //类注释-5分
			'1'=>-3, //方法注释缺一个-3分
			'2'=>-5, //总注释量少于15%
			'3'=>-10, //总注释量少于10%
			'4'=>-15, //总注释量少于5%
			'5'=>-20, //无注释
		)
	),
	'format'=>array(
		'total'=>50, //格式划总分50
		'child'=>array(
			'0'=>-2, //一个未对齐格式扣2分
			'1'=>-3, //一个方法超过70行-3分
			'2'=>-5, //一个方法超过100行-5分
			'3'=>-10, //总行数超过1000行
			'4'=>-5, //总行数/方法数>40 平均每个方法超过40行
			'5'=>-8, //总行数/方法数>50 平均每个方法超过50行
		)
	)
);
/**
 * 代码打分
 */
class Score{
	protected $db_obj = '';
	protected $total = 100;
	protected $parser = null;
	protected $content = '';
	public $start_line = 0; //类开始行
	public $end_line = 0; //类结束行
	public $score_info = '';
	protected $score = array(
		'note'=>array(
			'0'=>0,
			'1'=>0,
			'2'=>0,
			'3'=>0,
			'4'=>0,
			'5'=>0
		),
		'format'=>array(
			'0'=>0,
			'1'=>0,
			'2'=>0,
			'3'=>0,
			'4'=>0
		)
	);

	public function get_score($id){
		if(!$id){
			array();
		}
		global $score_info;
		$this->score_info = $score_info;
		include_once('./class/ParserClass.php');
        $this->parser = new ParserClass;
		//获取类信息
		$this->content = $this->get_class_info($id);
		//echo $this->content;
		//运行class
		try {
                eval('?> <?php ' . $this->content);
            } catch (Exception $e) {
                echo 'Caught exception: ', $e->getMessage(), "\n";
            }
        $system_class = get_declared_classes();
        //print_r($system_class);
        $class_count = count($system_class);
        $class = $system_class[$class_count - 1];

        //类注释
        $score_info = $this->count_note_score($class);
        //print_r($this->score);
        //计算分数
		$score = $this->count_score($score_info);
		return $score;
	}
	/**
	 * 计算分数
	 */
	protected function count_score($score_info){
		$note_total = $this->score_info['note']['total'];
		$score_1 = 0;
		foreach($score_info['note'] as $k=>$v){
			$score_1 += $v * $this->score_info['note']['child'][$k];
		}
		$count = $note_total+$score_1;
		$note_total = $count > 0 ?$count : 0;
		unset($k);
		unset($v);
		$format_total = $this->score_info['format']['total'];
		$score_2 = 0;
		foreach($score_info['format'] as $k=>$v){
			//echo $k;
			$score_2 += $v * $this->score_info['format']['child'][$k];
		}
		$count = $format_total+$score_2;
		$fomat_total = $count > 0 ?$count : 0;
		return $note_total+$fomat_total;
	}
	/**
	 * 计算注释得分
	 */
	protected function count_note_score($class){
		$reflectionClass = new ReflectionClass($class);
		$this->start_line = $reflectionClass->getStartLine();
		$this->end_line = $reflectionClass->getEndLine();


		$class_doc = $this->parser->get_class_doc($reflectionClass);
        //无类注释扣分
        if(!$class_doc){
        	$this->score['note']['0'] = 1;
        }

        //获取方法列表
        $methods = $this->parser->get_methods($reflectionClass);
        if($methods){
        	foreach ($methods as $v){
        		$method_doc = $this->parser->get_method_doc($class, $v->name);
        		if(!$method_doc){
        			$this->score['note']['1'] += 1;
        		}
        		//获取方法内容
                $function_content[] = $this->parser->get_method_content($class, $v->name, $this->content);
        	}
        }
		//获取'//' 注释行数
		$type_1_count = $this->get_count_type_1();
		//echo $type_1_count;
		//获取 /* -- */类型的注释行数
		$type_2_count = $this->get_count_type_2();
		//echo $type_2_count;
		$total_type = $type_1_count+$type_2_count;
		$pesent = ($total_type*100)/($this->end_line-$this->start_line);
		//echo $pesent;
		if( $pesent>10 && $pesent <=15){
			$this->score['note']['2'] += 1;
		}
		elseif( $pesent>5 && $pesent <=10 ){
			$this->score['note']['3'] += 1;
		}
		elseif( $pesent>1 && $pesent <=5){
			$this->score['note']['4'] += 1;
		}
		elseif( $pesent <=1){
			$this->score['note']['5'] += 1;
		}

		//计算方法行数
		//print_r($function_content);
		$class_total_lines = $this->get_total_lines();
		if($class_total_lines>1000){
			$this->score['format']['3'] = 1;
		}
		//echo $class_total_lines."---\n";

		foreach ($function_content as $vs){
			//echo $vs;
			$fun_lines = $this->get_fun_lines($vs);
			if($fun_lines<=100 && $fun_lines>70){
				$this->score['format']['1'] += 1;
			}
			elseif($fun_lines>100){
				$this->score['format']['2'] += 1;
			}
			//echo $fun_lines."\n";
		}
		$count_function = count($function_content);
		$per_function_line = $class_total_lines/$count_function;
		if( $per_function_line<50 && $per_function_line>40){
			$this->score['format']['4'] += 1;
		}
		elseif($per_function_line>50){
			$this->score['format']['5'] += 1;
		}
		return $this->score;
	}
	/**
	 * 去除方法和类中的注释信息
	 */
	protected function trim_note_content($content){
		//去除注释
		$content = preg_replace("/\/\*.*\*\//Us","",$content);
	  	$content = preg_replace("/\/\/(.*)\\n/","\n",$content);
	  	//去除空行
		$content = preg_replace( "/^[\s]*\n/", "", $content );
	  	return $content;
	}
	/*
	 * 获取行数
	 */
	protected function get_lines($content){
		$pattern = '/(.*)\\n(.*)/Us';
		preg_match_all($pattern,$content, $matche);
		//print_r($matche);
		$i = 0;
		foreach ($matche[0] as $v){
			if(trim($v) != ''){
				$i++;
			}
		}
		return $i;
	}
	/**
	 * 获取类总行数
	 */
	protected function get_total_lines(){
		$content = $this->trim_note_content($this->content);
		$total = $this->get_lines($content);
		return $total;
	}
	/**
	 * 获取每个方法行数
	 */
	protected function get_fun_lines($content){
		$content = $this->trim_note_content($content);
		$total = $this->get_lines($content);
		return $total;
	}

	/**
	 * 获取'//' 注释数
	 */
	protected function get_count_type_1(){
		$pattern = '/\/\/(.*)\\n/';
		preg_match_all( $pattern, $this->content, $matches);
		if($matches[0]){
			return count($matches[0]);
		}
		return 0;
	}
	/**
	 * 获取'\/* *\/' 注释行数
	 */
	protected function get_count_type_2(){
		$pattern = '/\/\*.*\*\//Us';
		preg_match_all( $pattern, $this->content, $matches);
		$line_num = 0;
		if($matches[0]){
			foreach ($matches[0] as $v){
				$v = $v."\n";

				if( substr($v, 0, 3) == '/**'){
					continue;
				}
				//获取行数
				$pattern = '/(.*)\\n(.*)/Us';
				preg_match_all($pattern, $v, $matche);
				if($matche){
					$line_num += count($matche[0]);
				}
			}
		}
		return $line_num;
	}
	/**
	 * 获取类信息
	 */
	public function get_class_info($id){
		//$class_info =get_class_by_id
		$class_info = $this->get_db_obj()->get_class_by_id($id);
		//print_r($class_info);
		if( $class_info && $class_info['docblock'] ){
			return $class_info['docblock'];
		}
		return '';
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