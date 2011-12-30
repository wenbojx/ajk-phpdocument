<?php
/**
 * 解析类信息
 * @author liwenbo
 *
 */
class ParserClass{
    //需解析的字段
    protected $fit_fileds = array("intro", "abstract", "access", "author", "copyright", "deprecated", "deprec", "example", "exception", "global", "ignore", "internal", "link", "name", "package", "param", "return", "see", "since", "static", "staticvar", "subpackage", "throws", "todo", "var", "version", "docblok");
    public $extends = '';
    /**
     * 判断是否含有类
     * @author wenboli
     * @param string $string 待处理的字符串
     * @param boolen
     */
    public function check_class( $string ){
        //处理继承关系
        $pattern = '/class(.*)\{/';
        preg_match( $pattern, $string, $matches);
        return $matches ? true : false;
    }
    /**
     * 取class的内容
     * @author wenboli
     * @param string $string 待处理的字符串
     * @param string 已处理的字符串
     */
    public function get_class_content( $string ){
        $pattern = '/class(.*)\{(.*)\}/';
        preg_match_all( $pattern, $string, $matches);
        //print_r($matches);
        return $string;
    }
    /**
     * 对内容进行替换
     * @author wenboli
     * @param string $string 待处理的字符串
     * @param string 已处理的字符串
     */
    public function format_excluded ($string, $excluded){
        $exclude = explode('+++', $excluded);
        foreach ($exclude as $v){
            $string = str_replace($v, '' ,$string);
        }
        //print_r($exclude);
        //echo $string;
        return $string;
    }
    /**
     * 获取类开始行
     */
    public function get_class_start(){

    }
    /**
     * 获取类结束行
     */
    public function get_class_end(){

    }
    /**
     * 获取方法开始行
     */
    public function get_method_start(){

    }
    /**
     * 获取方法结束行
     */
    public function get_method_end(){

    }
    /**
     * 获取类名
     * @author wenboli
     * @param object $reflectionClass
     */
    public function get_class_name ($reflection_class){
        $name = $reflection_class->getName();
        return $name;
    }
    /**
     * 获取类注释文档
     * @author wenboli
     * @param object $reflectionClass
     *
     */
    public function get_class_doc ($reflection_class){
        $doc = $reflection_class->getDocComment();
        return $doc;
    }
    /**
     * 获取类下所有方法
     * @author wenboli
     * @param object $reflectionClass
     */
    public function get_methods ($reflection_class){
        $methods = $reflection_class->getMethods();
        return $methods;
    }
    /**
     * 获取方法注释文档
     * @author wenboli
     * @param string $class 类名
     * @param string $method 方法名称
     */
    public function get_method_doc($class, $method){
        $reflectionMethod = new ReflectionMethod( $class, $method );
        $reflectionMethodDoc = $reflectionMethod->getDocComment();
        return $reflectionMethodDoc;
    }
    /**
     * 获取方法内容
     */
    public function get_method_content($class, $method, $content){
    	$reflectionMethod = new ReflectionMethod( $class, $method );
        $start_line = $reflectionMethod->getStartLine();
        $end_line = $reflectionMethod->getEndLine();
        $lines = explode("\n", $content);
		$str = '';
		if($lines){
			for($i=($start_line-1); $i<$end_line; $i++ ){
				$str .= $lines[$i]."\n";
			}
		}
		return $str;
    }
    /**
     * 获取方法参数
     * @author wenboli
     * @param string $class 类名
     * @param string $method 方法名称
     */
    public function get_method_param($class, $method){
        $reflectionMethod = new ReflectionMethod( $class, $method );
        $method_params = $reflectionMethod->getParameters();
        return $method_params;
    }
    /**
     * 解析参数
     * @author wenboli
     * @param string $string 需解析的参数
     */
    public function resolve_param ($string){
        //去掉头和尾的/** */
        $string = substr (trim ($string), 3, strlen (trim ($string))-4 );
        //保留字符串中的\*
        $string = str_replace('\*', '(|+|)', $string);
        $string = str_replace('*', '', $string);
        $string = str_replace('(|+|)', '*', $string);
        //暂时替换@这种字符
        $string = str_replace('\@', '(|-|)', $string);
        $line_array = explode ('@', $string);
        $resolve['intro'] = '';
        foreach ($line_array as $v){
            $world_break = explode(' ', $v);
            if( !is_array($world_break) || count($world_break)<2){
                $resolve['intro'] .= $world_break[0];
                continue;
            }
            $filed = $world_break[0];
            if ($filed != '' && in_array ($filed, $this->fit_fileds)){
                //$prefix = isset ($resolve[$filed]) && $resolve[$filed] != '' ? '|&+&|' : '';
                if (isset ($resolve[$filed]) && $resolve[$filed] != ''){
                    $resolve[$filed] .= '|&+&|' . implode('',$world_break);
                    $resolve[$filed] = str_ireplace($filed, '', $resolve[$filed]);
                }
                else{
                    $resolve[$filed] = implode('',$world_break);
					$resolve[$filed] = str_ireplace($filed, '', $resolve[$filed]);
                }
            }
            else{
                $resolve['intro'] .= implode ('' , $world_break);

            }
        }
        if($resolve['intro'] == ''){
            unset ($resolve['intro']);
        }
        return $resolve;
    }

    /**
     * 处理继承关系
     * @author wenboli
     * @param string $string 待处理的字符串
     * @param string 已处理的字符串
     */
    public function format_extends( $string, $extends ){
        //处理继承关系
        $pattern = '/extends(.*)\{/';
        preg_match( $pattern, $string, $matches);
        if( $matches){
            $extends = $matches[1];
            $string = str_replace($matches[0], '{', $string);
        }
        $this->extends = $extends;
        return $string;
    }
    /**
     * 去掉类中的abstract 方法信息
     * @author liwenbo
     * @param string $string 待处理的字符串
     * @param string 已处理的字符串
     */
    public function format_abstract( $string ){
        /*if( $start = strpos($str, $val) );
        return 'abstract '. $string;*/
    	return $string;
    }
    /**
     * 处理包含文件
     * @author wenboli
     * @param string $string 待处理的字符串
     * @param string 已处理的字符串
     */
    public function format_include( $string ){
        $pattern = '/apf_require_class\((.*)\)/';
        preg_match_all( $pattern, $string, $matches_1);
        $string = str_replace('apf_require_class(', 'apf_require_class_test(', $string);
        $string = str_replace('apf_require_class (', 'apf_require_class_test(', $string);
        $string = str_replace('apf_require_controller(', 'apf_require_controller_test(', $string);
        $string = str_replace('apf_require_controller (', 'apf_require_controller_test(', $string);
        $string = str_replace('apf_require_file(', 'apf_require_file_test(', $string);
        $string = str_replace('apf_require_file (', 'apf_require_file_test(', $string);
        $string = str_replace('apf_require_page(', 'apf_require_page_test(', $string);
        $string = str_replace('apf_require_page (', 'apf_require_page_test(', $string);
        $string = str_replace('apf_require_component(', 'apf_require_component_test(', $string);
        $string = str_replace('apf_require_component (', 'apf_require_component_test(', $string);
        $string = str_replace('$dao = new area();', '', $string);
        $string = str_replace('$dao->update_area();', '', $string);
        //$string = str_replace('$propDao = new property();', '', $string);

        return $string;
    }
}