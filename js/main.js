var main = {};

/**
 * 获取统计数据
 */
main.get_count_main = function(){
	//alert(pid);
	//获取该目录下子目录
    var url = resource+"?do=getcount&rtype=json";
	//alert(url);
    $.sajax('GET', url, get_list, '', 'json', true, loading);
	var node = 'code_list';
	function get_list(datas){
		//alert(datas);
		if (datas.state && datas.content) {
			
			$.each(datas.content,function(ks, vs){
				var str ='';
				var str1 = '';
				var str2 = '';
				str += '<dl><dt>release: '+ks+'</dt>';
				str1 += '<dd class="code_count"><table width="100%"><tr height="22" class="bglist1"><th>目录</th><th>文件数</th><th>含类数</th><th>有类注释</th><th>有类作者</th><th>方法数</th>';
				str1 += '<th>有方法注释</th><th>有方法作者</th><th>有参数注释</th><th>类注释率</th><th>方法注释率</th></tr>';
				str2 += '<dd class="dev_count"><table width="100%"><tr><th>排名</th><th>姓名</th><th>方法数</th></tr>';
				var i=1;
				var files = 0;
				var classes = 0;
				var intro = 0;
				var author = 0;
				var method = 0;
				var m_intro = 0;
				var m_author = 0;
				var m_param = 0;
				$.each(vs.code, function(k,v){
					var class_style = i%2==0?'bglist1':'bglist2';
					str1 += '<tr height="18" class="'+class_style+'"><td style="text-align:left">&nbsp;'+k+'</td>';
					str1 += '<td>'+v.files+'</td>';
					str1 += '<td>'+v.classes+'</td>';
					str1 += '<td>'+v.class_intro+'</td>';
					str1 += '<td>'+v.class_author+'</td>';
					str1 += '<td>'+v.method+'</td>';
					str1 += '<td>'+v.method_intro+'</td>';
					str1 += '<td>'+v.method_author+'</td>';
					str1 += '<td>'+v.method_param+'</td>';
					str1 += '<td>'+((v.class_intro/v.classes)*100).toFixed(2)+'%</td>';
					str1 += '<td>'+((v.method_intro/v.method)*100).toFixed(2)+'%</td></tr>';
					files += parseInt(v.files);
					classes += parseInt(v.classes);
					intro += parseInt(v.class_intro);
					author += parseInt(v.class_author);
					method += parseInt(v.method);
					m_intro += parseInt(v.method_intro);
					m_author += parseInt(v.method_author);
					m_param += parseInt(v.method_param);
					i++;
				})
				str1 += '<tr height="22" class="bglist3"><td style="text-align:left">&nbsp;总计</td>';
					str1 += '<th>'+files+'</th>';
					str1 += '<th>'+classes+'</th>';
					str1 += '<th>'+intro+'</th>';
					str1 += '<th>'+author+'</th>';
					str1 += '<th>'+method+'</th>';
					str1 += '<th>'+m_intro+'</th>';
					str1 += '<th>'+m_author+'</th>';
					str1 += '<th>'+m_param+'</th>';
					str1 += '<th>'+((intro/classes)*100).toFixed(2)+'%</th>';
					str1 += '<th>'+((m_intro/method)*100).toFixed(2)+'%</th></tr>';
				
				var i = 1;
				$.each(vs.dev, function(k,v){
					str2 += '<tr height="18">';
					str2 += '<td>'+i+'</td>';
					str2 += '<td>'+k+'</td>';
					str2 += '<td>'+v+'</td>';
					str2 += '</tr>';
					i++;
				})
				str1 += '</table></dd>';
				str2 += '</table>';
				str2 += '如果你的名字未出现在列表中<br>可能意味着你没有做注释的习惯哦！<br>注释很简单只要在你写的方法前加：<br>';
				str2 += '@author xxx <br>赶快去认领你的代码吧!';
				str2 += '</dd>';
				str = str+str1+str2+'</dl><div style="clear:both"></div>';

				$("#list_main").append(str);
			})

			
		}
	}
	function loading(){
		$.loading(node);
	}
}





