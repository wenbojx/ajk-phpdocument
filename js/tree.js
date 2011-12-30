var codeList = {};
var codeDatas = {};
var pagecount = 0; //总页数
var s_tp = {};

s_tp.st_1 = 'class_list^main';
s_tp.st_2 = 'class^main';
s_tp.st_3 = 'search_list^main';
s_tp.st_4 = 'main^main';

/********start index*************/

codeList.indexRun = function(node)
{
	var node = node?node:0;
    codeList.loadTree(node);
}

/**
 * 载入树
 * @author wenboli
 */
codeList.loadTree = function( fid )
{
     if(fid === ''){
        return false;
    }
    //$("#ylh_public_code").show();
    //获取该目录下子目录
    var url = resource+"?do=tree&fid="+fid+"&rtype=json";
    $.sajax('GET', url, getSorts, '', 'json', true, loading);
	function loading()
    {
		//alert(11);
	    var obj = $('<div style="text-align:center; padding:20px 0">loading...</div>');
	    $("#leftnav_content").append(obj);
    }
    function getSorts( datas )
    {
        $("#leftnav_content").empty();
        //$("#sortName").text(datas.content[0].name);
        if(datas.state && datas.content){
			//alert(111);
            var current = $("<ul/>").attr("id", 'browser').addClass('filetree').appendTo("#leftnav_content");
            if(datas.content[0].child){
                $.each(datas.content[0].child, function(k,v){
                    createTree( v ,current );
                })
            }
            $("#browser").treeview({  //初始化树目录
                persist: "location",
                control: "#treecontrol",
                collapsed: true
            });

        }
    }
    function createTree( datas , node )
    {
        var tp_name = 'class_list';
        /*if( !datas.child ){
            tp_name = 'class';
        }*/
        var current = $("<li/>").html("<span><a onClick='return $.a(this);' id='"+datas.id+"_node' rel='#tp="+tp_name+"|st_1&pid="+datas.id+"' href='#tp="+tp_name+"|st_1&pid="+datas.id+"'>" + datas.name + "</a></span>").appendTo(node);
        $(current).children("span").addClass('folder');
        $(current).attr('id',datas.id+'_span');
        if( datas.child && datas.child.length ){
            var branch = $("<ul/>").appendTo(current);
            $.each(datas.child, function( k,v ){
                createTree( v , branch );
            })
        }
    }
    //取消树下所有节点A标签样式
    function cancelNodeAClass (node){
        $('#'+node).find("a").each(function (){
            $(this).attr("class","");
        })
    }
    //改变节点样式
    function chNodeClass ( id ){
        cancelNodeAClass("browser");
        $("#"+id+"_node").attr("class","selectednode");
    }
    // 展开树节点ID:需要展开的节点
    function openTreeNode ( id ){
        var parents_node = $("#"+id+'_span').parent().parent();
        if(parents_node.attr("id") == tree ){
            return false;
        }
        //$(parents_node).children('div').attr('class','hitarea collapsable-hitarea lastCollapsable-hitarea');
        //$(parents_node).attr('class','collapsable lastCollapsable');
        //$(parents_node).show();
        //$(parents_node).children('ul').show();
        id = parents_node.attr("id");
        id = id.replace(/\_span/, '');
        $(parents_node).attr("class",'open');
        openTreeNode( id);
    }
}