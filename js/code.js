var code = {};



code.do_search = function(){
	var key = $("#keyword").val();
	if( key == ''){
		alert("请输入搜索关键字");
	}
	code.get_class_by_search(key);
}

code.get_class_by_search = function(key){
	//alert(pid);
	//获取该目录下子目录
    var url = resource+"?do=search&key="+key;
	//alert(url);
    $.sajax('GET', url, get_list, '', 'json', true, loading);
	var node = 'search_list';

	function get_list(datas){
		if (datas.state && datas.content) {
			$("#search_num").html(datas.content.length);
			code.creat_list_html(node, datas.content);
		}
	}
	function loading(){
		$.loading(node);
	}
}

/**
 * 创建列表
 * @param {Object} pid
 */
code.creat_list = function(pid){
	//alert(pid);
	if(!pid){
		return false;
	}
	code.get_class_by_pid(pid)
}
/**
 * 根据目录ID获取目录下的类列表
 * @param {Object} pid
 */
code.get_class_by_pid = function(pid){
	//alert(pid);
	//获取该目录下子目录
    var url = resource+"?do=codelist&pid="+pid+"&rtype=json";
	//alert(url);
    $.sajax('GET', url, get_list, '', 'json', true, loading);
	var node = 'code_list';
	function get_list(datas){
		//alert(datas);
		if (datas.state && datas.content) {
			code.creat_list_html(node, datas.content);
		}
	}
	function loading(){
		$.loading(node);
	}
}

code.creat_list_html = function( node, datas )
{
	var i = 0;
	$("#"+node).empty();
	$.each( datas, function(k,v){
		var nodeObj = $('<div class="box"></div>');
		$("#"+node).append(nodeObj);
		var headObj= $('<div class="title"><h2><a onclick="return $.a(this)" rel="tp=class|st_2&pid='+v.ffid+'&id='+v.id+'" href="tp=class|st_2&pid='+v.ffid+'&id='+v.id+'"><span class="emlink">【类名】</span>'+v.cname+'&nbsp;</a></h2></div>');
		$(nodeObj).append(headObj);
		v.intro = v.intro?v.intro:'	';
		var descripObj = $('<div class="intro"><p>类注释: '+v.intro+'...<a onclick="return $.a(this)" rel="tp=class|st_2&pid='+v.ffid+'&id='+v.id+'" href="tp=class|st_2&pid='+v.ffid+'&id='+v.id+'">详细</a></p></div>');
		$(nodeObj).append(descripObj);

		//var localDate = new Date(parseInt(v.time) * 1000).toLocaleString();
		//v.time = localDate.substr(0, localDate.length-8).replace(/年|月/g, "/").replace(/日/g, " ");

		i++;

	})
}
code.creat_detail = function(id){
	if(!id){
		return false;
	}
	var node = 'code_detail';
	var url = resource+"?do=code&id="+id+"&rtype=json";
	//alert(url);
    $.sajax('GET', url, get_detail, '', 'json', true, loading);
	function get_detail(datas){
		//alert(datas);
		if (datas.state && datas.content) {
			code.creat_detail_html(node, datas.content);
			//获取类得分
			code.get_class_score(id);
		}
	}
	function loading(){
		$.loading(node);
	}

}
code.creat_detail_html = function( node, datas )
{
	$("#"+node).empty();
	var nodeObj = $('<div class="ct_box" id="funBox_hd"></div>');
	$("#"+node).append(nodeObj);
	var cname = datas.cname?datas.cname:'';
	var release = datas.release?datas.release:'';
	var author = datas.author?datas.author:'';
	var intro = datas.intro?datas.intro:'';
	var docblock = datas.docblock?datas.docblock:'';

	var title_str = '<h1><span class="emlink">【类名】</span>'+cname;
	if(datas.pid){
		title_str += ' <font style="font-size:13px">extends</font> <a href="tp=class|st_2&id='+datas.pid+'" rel="tp=class|st_2&id='+datas.pid+'" onclick="return $.a(this)">'+datas.extends_str+'</a>';
	}
	title_str += '</h1>';
	$(nodeObj).append(title_str);

	var class_path = '<a href="tp=class_list|st_1&pid='+datas.ffid+'" rel="tp=class_list|st_1&pid='+datas.ffid+'" onclick="return $.a(this)">'+datas.path+'</a>'+datas.name;
	$(nodeObj).append('<p class="info">版本：'+release+'<span class="y_split"> | </span>开发：'+author+'<br>路径: '+class_path+'</p>');

	var jianjie = $('<div class="box"><div class="title"><h3>简介</h3><div class="t_more"><i class="icon_close_1"></i></div></div><div class="text"><p>'+intro+'</p></div></div>');
	$(nodeObj).append(jianjie);
	//alert(datas.childs_node.length);
	if(datas.childs_node.length){
		var jianjie = $('<div class="box"><div class="title"><h3>子类</h3><div class="t_more"><i class="icon_close_1"></i></div></div><div class="text" id="class_child"></div></div>');
		$(nodeObj).append(jianjie);
		var child_str = '';
		$.each(datas.childs_node,function(k,v){
			child_str += '<div class="child_float"><a href="tp=class|st_2&id='+v.id+'" rel="tp=class|st_2&id='+v.id+'" onclick="return $.a(this)">'+v.cname+'</a></div>';
		});
		child_str +='<div class="clear"></div>';
		$("#class_child").html(child_str);
	}

	var daimai = $('<div class="box"><div class="title"><h3>代码 (<i style="color:red" id="class_score"></i>)</h3><div class="t_more"><i class="icon_close_1"></i></div></div><div class="text"><p><pre class="" id="myCode'+datas.id+'"></pre></p></div></div>');
	$(nodeObj).append(daimai);
	$("#myCode"+datas.id).text(docblock);

	var fun_list = $('<div class="fun_list"></div>');
	$(nodeObj).append(fun_list);

	$.each(datas.method, function(k,v){
		var mname = v.mname?v.mname:'';
		var docblock = v.docblock?v.docblock:'';
		var dl = $('<dl></dl>');
		$(fun_list).append(dl);
		var dt = $('<dt class="title">方法名：'+mname+'</dt>');
		$(dl).append(dt);
		var dd = $('<dd class="text"><pre>'+docblock+'</pre></dd>');
		$(dl).append(dd);
		//alert(v.quotes);
		if (v.quotes && v.quotes.length) {
			//alert(11);
			var ddq = $('<dd class="text method_body"></dd>');
			$(dl).append(ddq);
			var child_str = '<div style="height:20px">以下类可能引用过该方法：</div><div class="clear"></div>';
			$.each(v.quotes,function(k1,v1){
				child_str += '<div class="method_float"><a href="tp=class|st_2&id='+v1.id+'" rel="tp=class|st_2&id='+v1.id+'" onclick="return $.a(this)">'+v1.name+'</a></div>';
			});
			child_str +='<div class="clear"></div>';
			$(ddq).html(child_str);
		}
	})

	//下拉按钮
	//codeFun.display_block(node);

	//$('#fun_id').val(datas.id);
}
//获取类得分
code.get_class_score = function(id){

	var url = resource+"?do=score&id="+id+"&rtype=json";
    $.sajax('GET', url, get_score, '', 'json', true);
	function get_score(datas){
		if (datas.state && datas.content) {
			$('#class_score').html("得分:"+datas.content);
		}
	}

}



