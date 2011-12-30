/*
 * jQuery history plugin
 *
 * sample page: http://www.mikage.to/jquery/jquery_history.html
 *
 * Copyright (c) 2006-2009 Taku Sano (Mikage Sawatari)
 * Licensed under the MIT License:
 *   http://www.opensource.org/licenses/mit-license.php
 *
 * Modified by Lincoln Cooper to add Safari support and only call the callback once during initialization
 * for msie when no initial hash supplied.
 */

jQuery.extend({
	loading: function(targets,msg)
    {
		var msg = msg?msg:'loading...'
    	var obj = $('<div style="text-align:center; padding:20px 0">'+msg+'</div>');
			//alert(target);
        $("#"+targets).append(obj);
    },
    //系统提示信息
    msg_show: function(msg,time)
    {
        time = (time=="")?3:time;
        $("#"+msg_tip).css("left",(($(document).width())/2-(parseInt(100)/2))+"px");
        $("#"+msg_tip).html(msg);
        $("#"+msg_tip).show();
        setTimeout($.msg_hiden,1000*time,[msg_tip]);
    },
    msg_hiden: function( id )
    {
        id = id==''?msg_tip:id;
        $("#"+id).hide("slow");
    },
    //显示提示信息
    msgTip: function(id,msg,css,time){
        $("#"+id+"_tip").html(msg);
        $("#"+id+"_tip").show();
        if(css){
            $("#"+id+"_tip").attr("class",css);
        }
        if( time ){
            setTimeout( $.msg_hiden, 1000*time, [id+"_tip"] );
        }
    },
    //去掉前后空格
    trim: function (val)
    {
        val =  val.replace(/(^\s*)|(\s*$)/g, '');
        return val;
    },
    //new_p新打开页面
    pageLoad: function( hash,new_p ){
        //alert(hash);
        var url = $.getArg( hash );
        //参数
        var data = '';//初始化附加URL参数
        if(typeof(url)=="object"){
            $.each(url, function(k,v){
                if( $.trim(k) != "tp" ) // && $.trim(k) != "app")
                data +="&"+k+"="+v;
            })
        }
        /*var app = default_app;
        //alert(hash+url.app);
        if(url.app){
            app = url.app;
        }*/
        hash = url.tp;
		//alert(hash);
        if(!new_p){//如果不是新打开页面 计算层数
        	//alert(111);
            new_page = false;
            //alert(hash);
            var hash_split = hash.split("|");
            p_num = hash_split.length-1;
            var st = hash_split[p_num];
            hash = $.getLongUrl( st );
			//alert(hash);
            hash_split = hash.split("|");
            p_num = hash_split.length-1;
        }
        else{
            p_num = 0;
            //uid = $.getUid()?$.getUid():uid;
            //var one_app = dfApp=="1000"?1000:1001;
            //$.loadPage('header', dfBH, one_app, '', false, false);
            //载入尾
            //$.loadPage('footer', dfBF, one_app, '', false, false);
        }
        //alert(hash+p_num);
        var arg_tp = $.splitVarP(hash,p_num);
        //alert(arg_tp.hash+arg_tp.target);
        $.loadPage(arg_tp.hash, arg_tp.target, data, true, false);
    },
    /***********载入页面****************/
    /*
    var add 是否以增加内容的形式
    */
    loadPage:function(hash, target, data, async, add){
        //alert(hash);
        if(!hash ){
            hash = dfTp;
        }
        //alert(target);
        if(!target){
            target = dfTar;
        }
        //alert(app);
        var url = rootDomain +hash+'.html';
        $.sajax('GET', url, innerHtml, data, 'text', async, loading);
        function innerHtml( html ){
            $("#"+target).html( html );
        }
        function loading()
        {
            //return false;
			//alert(target);
            if(!add){
                $("#"+target).empty();
            }
            var obj = $('<div style="text-align:center; padding:20px 0">loading...</div>');
			//alert(target);
            $("#"+target).append(obj);
        }
    },
    sajax:function(type, url, success, data, dataType, async, before, error, cache, time){
        var type = type?type:'GET';
        if(!url) return false;
        var success = success?success:donothing;
        var dataType = dataType?dataType:'html';
        var async = typeof(async)=='undefined'?true:async;
        var before = before?before:donothing;
        var error = error?error:donothing;
        var cache = cache?cache:true;
        var timeout = time?time:timeout;
        var data = data?data:'';
        $.ajax({
            type:type,
            url: url,
            cache:cache,
            timeout: timeout,
            async: async,
            dataType: dataType,
            data: data,
            beforeSend: function(){
                before();
            },
            success: function( datas ){
                success(datas);
            },
            error: function(){
                error();
            }
        });
        function donothing(){
            return false;
        }
    },
    a: function ( a ){
        if( $(a).attr("rel") ){
            var hash = $(a).attr("rel");
        }
        hash = hash.replace(/^.*#/, '');
        $.historyLoad(hash);
        return false;
    },
    /****************取得页面的参数*********************/
    getArg: function( href_str ){
        var arg = {};
        if(!href_str){
            href_str = location.hash;
        }
        href_str = href_str.replace(/#/ , '');
        if( !href_str ){
            return false;
        }

        var args = href_str.split("&");
        if(args.length>0){
            for(var i=0; i<args.length; i++)
            {
                var arg_split = args[i].split("=");
                if(!arg_split[1]){
                    arg_split[1] = '';
                }
                //alert(arg_split[0]+arg_split[1]);
				if(arg_split[0] && arg_split[1])
                eval('arg.'+arg_split[0]+'="'+arg_split[1]+'";');
            }
        }
        //alert(arg.tp);
        return arg

    },
    /********************对参数p的值进行分解****num:返回第几层的数据***********************/
    splitVarP:function( tp,num ){
        var arg_tp = {};
        if(!tp) return false;
        var split_tp_1 = tp.split('|');
        if( split_tp_1[num] && split_tp_1[num].substr(0,3)=="st_" ){
            var tp = $.getLongUrl( split_tp_1[num] );
            if( !split_tp_1[num] ){
                return false;
            }
        }
        var split_tp = tp.split('|');
        if( split_tp.length >0 && split_tp[num]){
            var page_msgs = split_tp[num];
            var page_msg = page_msgs.split('^');
            if(page_msg.length<2 && split_tp.length>2){
                return false;
            }
            arg_tp.hash = page_msg[0]?page_msg[0]:dfTp;
            arg_tp.target = page_msg[1]?page_msg[1]:dfTar;
            return arg_tp;
        }
        else{
            return false;
        }
    },
    //根据段地址获取长地址
    getLongUrl:function( st )
    {
        var tp = "";
        switch( st ){
            case "st_1":
            tp = s_tp.st_1?s_tp.st_1:"";
            break;
            case "st_2":
            tp = s_tp.st_2?s_tp.st_2:"";
            break;
            case "st_3":
            tp = s_tp.st_3?s_tp.st_3:"";
            break;
            case "st_4":
            tp = s_tp.st_4?s_tp.st_4:"";
            break;
            case "st_5":
            tp = s_tp.st_5?s_tp.st_5:"";
            break;
            case "st_6":
            tp = s_tp.st_6?s_tp.st_6:"";
            break;
            case "st_7":
            tp = s_tp.st_7?s_tp.st_7:"";
            break;
            case "st_8":
            tp = s_tp.st_8?s_tp.st_8:"";
            break;
        }

        return tp;
    },

    /********************/
    onLoad: function()
    {
        var loadpage = "tp="+dfTp;
        var hash = location.hash.replace(/#/ , '');
        var host = window.location.host.toLowerCase();
        var domain = host.substr(0,(host.length-10) );
        if(hash){
            loadpage = hash;
        }
        return loadpage;
    },
    reflash:function(url){
        if(url){
            location.replace(url);
            return true;
        }
        location.reload();
    },
    //弹出窗口 [ID,上下位置,偏移值]
    openwin:function(id,path,dist){
        $('<div id="winmask" style=" display:none;"><iframe frameborder="0" border="0" style="width:100%;height:100%;position:absolute;z-index:1;left:0;top:0;filter:Alpha(opacity=0);"></iframe></div>').appendTo( $("body"));
        var ggbug=(window.MessageEvent && !document.getBoxObjectFor)?document.body:document.documentElement; //判断goolge
        var trim_Version=navigator.appVersion.split(";")[1].replace(/[ ]/g,"");
        var objid=document.getElementById(id);
        var sh = document.documentElement.scrollHeight;
        var ch = document.documentElement.clientHeight;
        document.getElementById("winmask").style.height = sh>ch?sh+"px":ch+"px"; //设遮罩层高度

        objid.style.display=document.getElementById("winmask").style.display=(objid.style.display=="none")?"block":"none"; //判断窗口显示隐藏  判断遮罩层显示隐藏
        function getleft(){objid.style.left=(document.documentElement.clientWidth-objid.offsetWidth)/2+"px"} //打开时取得窗口left居中值
        function gettop(){objid.style.top=(document.documentElement.clientHeight-objid.offsetHeight)/2+"px";} //打开时取得窗口top居中值
        function ie6gettop(){objid.style.top=document.documentElement.scrollTop+dist+"px";} //ie6取得top值
        function ie6getbottom(){objid.style.top=document.documentElement.clientHeight+document.documentElement.scrollTop-objid.offsetHeight-dist+"px";} //ie6取得top值，得到bottom值
        function ie6getcen(){objid.style.top=(document.documentElement.clientHeight-objid.offsetHeight)/2+document.documentElement.scrollTop+"px";} //ie6取得top居中值
        getleft();gettop();
        if(path){ //当为固定样式时
            if(trim_Version=="MSIE6.0"){ //判断IE6
                if(path=="top"){ie6gettop();window.onresize=window.onscroll=function(){getleft();ie6gettop()}}else{ie6getbottom();window.onresize=window.onscroll=function(){getleft();ie6getbottom()}}
            }else{
                window.onresize=function(){getleft();gettop();}
                if(path=="top"){objid.style.top=dist+"px";}else{objid.style.top="auto";objid.style.bottom=dist+"px";}
            }
        }else{ //当为非固定样式时
            if(trim_Version=="MSIE6.0"){ //判断IE6
                window.onresize=window.onscroll=function(){getleft();if(!dist){ie6getcen()};}
                if(dist){ie6gettop()}else{ie6getcen();}
            }else{
                window.onresize=function(){getleft();gettop();}
                if(dist){objid.style.top=ggbug.scrollTop+dist+"px";}
            }
        }
    },
    //拖拽功能
    Mdrag:function(id,event){
        var dragid=document.getElementById(id) //窗口ID
        var pX=(document.all)?event.x - dragid.offsetLeft:event.pageX - dragid.offsetLeft; //计算窗口当前位置
        var pY=(document.all)?event.y - dragid.offsetTop:event.pageY - dragid.offsetTop;
        var winobj=(document.all)?document:window;
        if(document.all){dragid.setCapture()}
        winobj.onmousemove=function(event){ //注册移动事件
            dragid.style.left=(document.all)?(window.event.x - pX)+"px":(event.pageX - pX)+"px"; //设置窗口位置
            dragid.style.top =(document.all)?(window.event.y - pY)+"px":(event.pageY - pY)+"px"
        }
        winobj.onmouseup=function(event){winobj.onmousemove=null;if(document.all){dragid.releaseCapture()}} //弹起释放移动事件
    },
    /***********************给页面的表单赋值*****************************/
    pushValue: function ( datas, prefix, exception)
    {
        $.each(datas,function(key ,value ){
            if( typeof(exception)=='object' ){
                for(var i=0; i<exception.length; i++){
                    if(key==exception[i]){
                        return true;
                    }
                }
            }
            var obj = document.getElementById(prefix+key);//$("#"+prefix+key);
            var type = $(obj).attr("type");
            if(!obj){
                var obj_1 = document.getElementById(prefix+key+"_"+value);//$("#"+prefix+key);
                if(!obj_1){
                    return true;
                }
                var type = $(obj_1).attr("type");
            }
            switch(type){
                case "text":
                    $(obj).attr("value",value);
                    break;
                case "textarea":
                    $(obj).attr("value",value);
                    break;
                case "hidden":
                    $(obj).attr("value",value);
                    break;
                case "radio":
                    obj_1.checked = 'checked';
                    break;
                case "checkbox":
                    return true;
                    obj.checked = obj.value == value?"checked":"";
                    if(obj.value == value ){
                        obj.checked = "checked";
                        //alert(obj.parentNode.getElementsByTagName('li'));
                        gid(id+"_li").className ="checked";//改变样式
                    }
                    break;
                case "select-one":
                    for(var i=0;i<obj.options.length;i++){
                        if( obj.options[i].value == value ){
                            obj.options[i].selected = "selected";
                        }
                    }
                    break;
            }
        })
    }
})


