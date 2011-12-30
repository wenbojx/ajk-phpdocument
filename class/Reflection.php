<?php
/**
 *
 * 扫描目录文件夹并保存数据
 * @author wenboli
 * @todo 暂时没考虑数据安全，默认认为线上项目文件名不存在涉及安全的数据
 *
 */
class Reflections {

    protected $db_obj = '';
    protected $parser = null;

    /**
     * 执行页面
     * @author wenboli
     */
    public function run() {
    	include_once('./class/ParserClass.php');
        $this->parser = new ParserClass;

        $id = intval($_GET['id']);
        $class_datas = array();
        if(!$id) {
            echo json_encode($datas);
            exit();
        }
        $file_datas = $this->get_activit_file($id);

        if(! $file_datas) {
            echo json_encode($datas);
            exit();
        }
        //print_r($file_datas);
        //exit();

        $file_datas['doc_class'] = stripcslashes($file_datas['doc_class']);

        $class_list = json_decode($file_datas['doc_class'], true);
        print_r($class_list);

        foreach($class_list as $v) {
            if(! $v) {
                continue;
            }
            $v = stripcslashes($v);
            //echo $v;
            $class_doc_datas = null;
            $extends = '';
            $str = $this->parser->format_extends($v, $extends);
            $extends = $this->parser->extends;
            //echo $str.'<br><br><br><br><br>';
            $str = $this->parser->format_abstract($str);

            $str = stripslashes($str);
            //echo $str;
            //exit();
            try {
                eval('?> <?php ' . $str);
            } catch (Exception $e) {
                echo 'Caught exception: ', $e->getMessage(), "\n";
            }
            $system_class = get_declared_classes();
            //print_r($system_class);
            $class_count = count($system_class);
            //echo $class_count .'-'.$this->system_load_class.'<br>';
            $class = $system_class[$class_count - 1];
            $reflectionClass = null;
            $reflectionClass = new ReflectionClass($class);
            //获取类注释
            $class_doc = $this->parser->get_class_doc($reflectionClass);
            //解析类注释
            $class_datas = $this->parser->resolve_param($class_doc);

            $class_datas['cname'] = trim($class);
            //$class_datas['docblock'] = trim($class_doc);
            //类内容
            $class_datas['docblock'] = $str;
            $str = '';

            $class_datas['extends'] = trim($extends);
            $class_datas['path'] = trim($v['path']);
            $class_datas['fid'] = trim($v['id']);

            //获取方法列表
            $methods = $this->parser->get_methods($reflectionClass);
            //print_r($methods);
            $i = 0;
            foreach($methods as $v1) {
                $func_doc_datas = null;
                //获取方法参数
                $method_params = $this->parser->get_method_param($class, $v1->name);
                //获取方法注释
                $method_doc = $this->parser->get_method_doc($class, $v1->name);
                //解析方法注释
                $class_datas['methods'][$i] = $this->parser->resolve_param($method_doc);
                $class_datas['methods'][$i]['mname'] = trim($v1->name);

                //获取方法内容
                $function_content = $this->parser->get_method_content($class, $v1->name, $class_datas['docblock']);

                $class_datas['methods'][$i]['docblock'] = trim($function_content);

                $class_datas['methods'][$i]['fid'] = trim($id);
                $i++;
            }
        }
        //print_r($class_datas);
        echo json_encode($class_datas);
        exit();
    }

    /**
     * 获取文件信息
     * @author wenboli
     * @param int $id 文件id
     */
    public function get_activit_file($id) {
        if(! $id) {
            return false;
        }
        $file = $this->get_db_obj()->get_file_by_id($id);
        //print_r($file);
        return $file ? $file : false;
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