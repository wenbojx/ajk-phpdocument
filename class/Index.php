<?php
apf_require_controller('Codelist_Abstract');
apf_require_class('Aifang_Core_Bll_Codelist_Search');
/**
 *
 * 扫描目录文件夹并保存数据
 * @author wenboli
 * @todo 暂时没考虑数据安全，默认认为线上项目文件名不存在涉及安全的数据
 *
 */
class Codelist_IndexController extends Codelist_AbstractController {
	/**
	 * 执行页面
	 * @author wenboli
	 */
    public function handle_request_internel() {
        return 'Codelist_Index';
    }

}