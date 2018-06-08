$(document).ready(function(){
	hover();
	
	
	
});
var window_h=$(window).height();
var window_w=$(window).width();
var vScroll;	//iScroll;

//loading文本
var loading_text='<div class="view-loading"><div class="loading-body"><img src="/Apps/Wap/View/default/Public/Images/wap_loading.gif" class="not-block"></div></div>';

var no_data_text='<div class="load-body">该页面暂无数据显示</div>';

//加载URL页面
function loadurl(param){
	$(param.tag).html(loading_text).load(param.url);
}

//点击样式切换
function hover(param){
	//return false;
	var tag='.pages .page.active ';
	if(param!=undefined && param!='') tag=param+' ';
	$(tag+'.hover').click(function(){
		var obj=$(this);
		obj.addClass('hover-active');
		setTimeout(function(){
			obj.removeClass('hover-active');
		},200);
	});	
}

//新开页面
/**
* @param
* param.close  true时关闭当前窗口
* param.logined true时表示需要登录才能打开
* param.name 窗口名
* param.url 要打开的页面URL
*/
function openWin(param){
	if(param.close==true) closeWin(); //关闭当前窗口
	
	if(param.logined==true){	//验证页面是否需要登录
		if(check_login()==false){
			openWin({name:'login',url:'/Login/index'});
			return false;
		}
	}
	
	if(param.authed==true){
		if(check_auth()==false){
			openWin({name:'no_auth',url:'/Login/no_auth'});
			return false;
		}		
	}
	
	
	//记录页面
	var page=$('body').data('page');
	if(page!='' && page!=undefined){
		//page+=','+param.name;
		var arr=page.split(',');
		if($.inArray(param.name,arr)==-1){
			page=param.name+','+page;
		}else if(arr.length>1){	//将当前激活的窗口放到最前
			arr.splice($.inArray(param.name,arr),1);
			page=param.name+','+arr.join(',');
		}
		$('body').data({page:page});
		
		//var narr=page.split(',');
		//for(var i=0;i<narr.length;i++){
			//$('.page #'+narr[i]).css({zIndex:(narr.length-i)});
		//}
		//alert(page);
	}else{
		$('body').data({page:param.name});
	}

	var size=$('.pages .page').size();
	if(size>0) $('.pages .page.active').removeClass('active');	
	if($('.pages .page#'+param.name).size()>0){
		$('.pages .page#'+param.name).addClass('active');
		if(param.ref==true || param.url!=$('.pages .page#'+param.name).data('url')){
			loadurl({
				tag:'.pages .page#'+param.name,
				url:param.url,
			});
			//hover();	
			$('.pages .page#'+param.name).data({url:param.url});
		}
		//$('.pages .page#'+param.name).animate({left:'0px'});
	}else{
		if(param.is_animate==false){
			var html='<div class="page active animated" id="'+param.name+'" style="left:0px;top:0px;height:'+window_h+'px;" data-url="'+param.url+'"></div>';
			$('.pages').append(html);		
		}else{			
			var html='<div class="page active animated" id="'+param.name+'" style="left:0px;top:0px;height:'+window_h+'px;" data-url="'+param.url+'"></div>';
			$('.pages').append(html);
			//$('.pages .page#'+param.name).animate({left:'0px'});
		}
		loadurl({
			tag:'.pages .page#'+param.name,
			url:param.url,
		});
		//hover();
	}	
};

//刷新激活的窗口
function winRef(){
	loadurl({
		tag:'.page.active',
		url:$('.page.active').data('url'),
	});
}


//关闭页面
function closeWin(param){
	var size=$('.pages .page').size();
	
	if(param==undefined){	//关闭当前窗口
		var name=$('.pages .page.active').attr('id');
	}else if(param.name!='' && param.name!=undefined){	//关闭指定窗口
		var name=param.name;
	}else var name=$('.pages .page.active').attr('id');	

	if(size>1){
		//记录页面
		var page=$('body').data('page');
		var arr=page.split(',');
		arr.splice($.inArray(name,arr),1);
		$('body').data({page:arr.join(',')});

		var active_name=arr[0];
		$('.pages .page#'+active_name).addClass('active');
		
		//刷新激活的窗口
		$('.pages .page#'+name).addClass('fadeOutRight');
		setTimeout(function(){
			$('.pages .page#'+name).remove();
			if(param!=undefined && param.ref==true){
				loadurl({
					tag:'.page.active',
					url:$('.page.active').data('url'),					
				});
			}
		},500);
		
	}else if($('.page#'+name).data('url')!='/Index/index'){
		openWin({name:'home',url:'/Index/index'});
	}

};

//从当前窗口关闭到指定窗口
function closeToWin(param){
	var page=$('body').data('page');
	var arr=page.split(',');
	if(arr.length>1){
		for(var i=0;i<arr.length;i++){
			if(arr[i]!=param.name){
				//alert(arr[i]);
				closeWin({name:arr[i]});
			}else{
				return true;
			}
		}
	}
};

//新开框架页面
function openFrame(param){
	var height=window_h-50;
	if(param.footer==true) height=height-51;
	var overflow='hidden';

	if(get_browse()=='Safari') overflow='auto';
	//alert(get_browse());
	
	$('.page.active section').css({height:height,overflow:overflow}).html(loading);
	
	var html='<iframe frameborder="0" width="100%" height="'+height+'" marginheight="0" marginwidth="0" name="'+param.name+'" src="'+param.url+'"></iframe>';
	$('.page.active section').html(html);
};

//获取浏览类型
function get_browse(){
    var userAgent = navigator.userAgent; //取得浏览器的userAgent字符串
    var isOpera = userAgent.indexOf("Opera") > -1;
    if (isOpera) {
        return "Opera"
    }; //判断是否Opera浏览器
    if (userAgent.indexOf("Firefox") > -1) {
        return "FF";
    } //判断是否Firefox浏览器
    if (userAgent.indexOf("Chrome") > -1){
  return "Chrome";
 }
    if (userAgent.indexOf("Safari") > -1) {
        return "Safari";
    } //判断是否Safari浏览器
    if (userAgent.indexOf("compatible") > -1 && userAgent.indexOf("MSIE") > -1 && !isOpera) {
        return "IE";
    }; //判断是否IE浏览器
}

//修复容器
//默认减掉头部50px和页脚51px，1时为即只有头部，没有尾部
function fixBar(t,px){
	if(px==undefined) px=0;
	
	var css={};
	css.height=window_h-102;
	css.marginTop='51px';
	
	switch(t){
		case 1:
			css.height=css.height+51;
		break;
		case 2:
			css.height=window_h;
			css.marginTop='0px';
		break;
		case 3:
			css.height=window_h-51;
			css.marginTop='0px';
		break;		
	}
	
	var iScroll_height=(css.height-px)+'px';
	css.height=css.height+'px';	
	$('.page.active section').css(css);
	
	
	//iscroll
	/*
	if($('.page.active .iscroll-box').size()>0){
		$('.page.active .iscroll-box').css({height:iScroll_height});

		//延迟使用iscrllo,否则会失败
		setTimeout(function(){
			vScroll = new IScroll('.page.active .iscroll-box', { 
				scrollbars: true,
				mouseWheel: true,
				click:true,
				interactiveScrollbars: true,
				shrinkScrollbars: 'scale',
				fadeScrollbars: true,
				probeType: 3,
			});
			
			
			vScroll.on("scroll",function(){
				//scroll事件，可以用来控制上拉和下拉之后显示的模块中，
				//样式和内容展示的部分的改变。
				var y = this.y,
					maxY = this.maxScrollY - y,
					is_pull_up = $('.page.active .iscroll-box .pull-up').hasClass('active');
				

				if(y > 50 && is_pull_up==false){
					$('.page.active .iscroll-box .pull-up').addClass('active');
				}
				

			});		
			
			vScroll.on("scrollEnd",function(){
				var y = this.y,
					maxY = this.maxScrollY - y,
					is_pull_up = $('.page.active .iscroll-box .pull-up').hasClass('active');
					
				//当下拉，使得边界超出时，如果手指从屏幕移开，则会触发该事件
				if(this.y >= 0 && is_pull_up==true){
					//获取内容于屏幕拉开的距离
					//可以在该部分中，修改样式，并且仅限ajax或者其他的一些操作
					//此时只是为了能演示该功能，只添加了一个alert功能。
					//并且，由于alert会阻塞后续的动画效果，所以，
					//添加了后面的一行代码，移除之前添加上的一个样式
					winRef();					
					$('.page.active .iscroll-box .pull-up').removeClass('active');
				}
			});	
						
		},1000);		
		
	
		
	}
	*/

	
	hover();
}

//打开左侧或右侧面板
function openLayer(param,callback){
	var css={width:window_w+'px',height:window_h+'px',display:'block'};
	$('.page.active .layer').css(css);
	$('.page.active .layer .layer-box').fadeIn("slow","linear");
	
	$('.page.active .layer .layer-mask').click(function(){
		$('.page.active .layer .layer-box').fadeOut("slow","linear");
		$('.page.active .layer').css({display:'none'});
	});
	

	if(param.title){
		$('.page.active .layer .layer-title').html(param.title);
	}

	//是否显示头部
	if(param.is_header==false){
		$('.page.active .layer .layer-title').addClass('hide');
	}else{
		$('.page.active .layer .layer-title.hide').removeClass('hide');		
	}	
	if(param.url){
		$('.page.active .layer .layer-content').html(loading_text).load(param.url);
	}else if(param.msg){
		$('.page.active .layer .layer-content').html(param.msg);
	}
	
	if(callback) callback();	
}

function ajax_post(param,success_callback){
	loading(true);
	$.ajax({  
		type: 'post', 
		url: param.url,
		data:param.data,
		dataType:'json',
		async:true,
		success: function (ret) {
			if(param.script) eval(param.script);
			if(success_callback) success_callback(ret);
			loading(false);
		},
		error:function(e){
			valert({msg:'读取失败！'});
			loading(false);
		}
	}); 	

}

function ajax_get(param,success_callback){
	loading(true);

	dataType=param.dataType?param.dataType:'json';
	$.ajax({  
		type: 'get', 
		url: param.url,
		dataType:dataType,
		async:true,
		success: function (ret) {
			if(param.script) eval(param.script);
			if(success_callback) success_callback(ret);
			loading(false);
		},
		error:function(e){
			valert({msg:'读取失败！'});
			loading(false);
		}
	}); 	
}

//ajax post表单
function ajax_post_form(param,success_callback){
	
	loading(true);
	var form=$(param.formid);
	var d=form.data();
	if(d.url && param.url==undefined) param.url=d.url;
	//alert(param.url);

	var options = {
		beforeSubmit:param.beforeSubmit,
		target: '#ajax_post_tips',
		url: param.url,
		type: 'POST',
		success:function (ret) {
			if(param.script) eval(param.script);
			if(success_callback) success_callback(ret);
			loading(false);
		},
		error:function(e){
			valert({msg:'读取失败！'});
			loading(false);
		}
	};
	form.ajaxSubmit(options);	
}

//loading
function loading(t){
	if(t==true){
		$('#ajax_tips').html(loading_text);
	}else{
		$('#ajax_tips').html('');
	}
}

//全选
function select_all(param){
	var tag=param.tag?param.tag+' ':'body ';
	$('.select-all').click(function(){
		var d=$(this).data();
		if(d.tag) tag=d.tag+' ';
		$(tag+'input[type="checkbox"]').each(function(){
			$(this).iCheck('toggle');
            if(d.callback){
                eval(d.callback);
            }
		});		
		
	});
}

function valert(param){
	//alert(param.msg);
	webToast(param.msg,"middle",2000);	
};

function checkbox_select_all(){
	$('[data-type="select-all"]').on('ifChecked', function(event){
		var d=$(this).data();
		$(d.tag+' input[type="checkbox"]').iCheck('check');
	});
	$('[data-type="select-all"]').on('ifUnchecked', function(event){
		var d=$(this).data();
		$(d.tag+' input[type="checkbox"]').iCheck('uncheck');
	});

}

//验证登录
function check_login(){
	$.ajaxSetup({async: false});
	var res=false;
	
	$.get('/Login/is_logined',function(ret){
		if(ret.code==1) res=true;
	});
	
	return res;
}

//验证是否认证
function check_auth(){
	$.ajaxSetup({async: false});
	var res=false;
	
	$.get('/Login/is_authed',function(ret){
		if(ret.code==1) res=true;
	});
	
	return res;
}

//检测某区域是否有代理
function check_city_agent(city_id){
	$.ajaxSetup({async: false});
	var res=true;	

	$.get('/Upgrade/check_city_agent/city_id/'+city_id,function(ret){
		if(ret.code==99) res=false;
	});
	
	return res;	
}

/* tabs */
function tabs(param){
	var tag='.page.active ';
	if(param!=undefined && param!='') tag=param+' ';
	$(tag+'.tabs .tabs-item').each(function(index){
		var obj=$(this);
		obj.click(function(){
			$(this).closest('.tabs').find('.tabs-item').removeClass('active');
			$(this).addClass('active');		
			
			$(tag+'.tabs-content .tabs-pane').removeClass('active');
			$(tag+'.tabs-content .tabs-pane').eq(index).addClass('active');			
			
		});
	});
}

/* 页面跳转 */
function gourl(url){
	window.location.href=url;
}
//将form中的值转换为键值对
function getFormJson(form) {
	var o = {};
	var a = $(form).serializeArray();
	$.each(a, function () {
		if (o[this.name] !== undefined) {
			if (!o[this.name].push) {
				o[this.name] = [o[this.name]];
			}
			o[this.name].push(this.value || '');
		} else {
			o[this.name] = this.value || '';
		}
	});
	return o;
}

//模态框
function vmodal(param,callback){
	if(param.title){
		$('.page.active .vmodal .vmodal-header').html(param.title);
	}
	if(param.footer){
		$('.page.active .vmodal .vmodal-footer').html(param.footer);
	}
	//是否显示头部
	if(param.is_header==false){
		$('.page.active .vmodal .vmodal-header').addClass('hide');
	}else{
		$('.page.active .vmodal .vmodal-header.hide').removeClass('hide');		
	}
	//是否显示底部
	if(param.is_footer==false){
		$('.page.active .vmodal .vmodal-footer').addClass('hide');
	}else{
		$('.page.active .vmodal .vmodal-footer.hide').removeClass('hide');
	}

	if (param.cancel_text) {
		$('.page.active .vmodal .vmodal-footer .vmodal-cancel').html(param.cancel_text);
	}

	if (param.success_text) {
		$('.page.active .vmodal .vmodal-footer .vmodal-success').html(param.success_text);
	}

	if(param.url){
		$('.page.active .vmodal .vmodal-content').html(loading_text).load(param.url);
	}else if(param.msg){
		$('.page.active .vmodal .vmodal-content').html(param.msg);
	}
	$('.page.active .vmodal').addClass('active');
	$('.page.active .vmodal-mask').addClass('active');
	$('.page.active .vmodal-footer,.page.active .vmodal-mask').click(function(){
		$('.page.active .vmodal').removeClass('active');
		$('.page.active .vmodal-mask').removeClass('active');
	});
	
	if(callback) callback();
}

//通用数据分页
function pagelist(param){
	var obj=$('.page.active section');
	if(param.obj!=undefined) obj=param.obj;
	
	var d=obj.data();
	var p=1;
	if(d.p!=undefined && d.p!=''){
		p=d.p;
	}

	if(d.allpage!=undefined && d.p>d.allpage){
		obj.find('.load-more').html('已无记录可加载！');
		return false;
	}	
	param.data.p=p;		
	ajax_post({
		url:'/Api/api',
		data:param.data,
	},function(ret){
		if(ret.code==1){	
			if(ret.data.allpage>1){
				obj.find('.load-more').addClass('active');
			}
			p++;
			obj.data({p:p,allpage:ret.data.allpage});
			
			if(param.success) param.success(ret);
		}else if(ret.code==3){
			if(param.error) param.error(ret);
		}
	});		
}


function gotop(){
	if($('.page.active .iscroll-box').size()>0){
		vScroll.scrollTo(0,0);
	}else{
		$('section').animate({scrollTop :'0px'}, 200);
	}
}

function chooseImg(url, imagesBox, sort, i) {
	var self;
	if(i !== '' && i !== undefined) {
		self = $("#images_"+sort+"_"+i);
	} else {
		self = $("#images_"+sort);
	}
	self.change(function() {
		 var data = new FormData();
         //为FormData对象添加数据
         $.each(self[0].files, function(i, file) {
             data.append('imageData', file);
         });
         loading(true);
         $.ajax({
             url:url,
             type:'POST',
             data:data,
             cache: false, 
             contentType: false,    //不可缺
             processData: false,    //不可缺
             success:function(ret){
            	if(i !== '' && i !== undefined) {
            		$('.page.active ' + imagesBox + i).find('.upload_images').eq(sort).attr('src',ret.url);
    				$('.page.active ' + imagesBox + i).find('.upload_images').eq(sort).data('url',ret.url);
    				var imagesInput = $(imagesBox + i).find('input[name="images"]');
            	} else {
            		$('.page.active ' + imagesBox).find('.upload_images').eq(sort).attr('src',ret.url);
    				$('.page.active ' + imagesBox).find('.upload_images').eq(sort).data('url',ret.url);
    				var imagesInput = $(imagesBox).find('input[name="images"]');
            	}
            	
				var val = imagesInput.val();
				if(val != '') {
					imagesInput.val(val + ',' + ret.url);
				} else {
					imagesInput.val(ret.url);
				}
				if(ret.code != 1) {
					valert(ret);
				}
				loading(false);
             }
         });
		self.unbind();
	});
}

// 增加一个图片位置
function rateImagesAdd(action, imagesBox, obj, i){
	var len = obj.prevAll().length;
	var html = '<div class="chooseImages" style="position: relative;display: inline-block;">';
		if (i !== '' && i !== undefined) {
			html += '<input type="file" onclick="chooseImg(\''+action+'\', \''+imagesBox+'\',' + len + ', ' + i + ')" id="images_'+ len +'_'+ i +'" name="images_'+ len +'_'+ i +'" style="width: 100px;height: 100px;opacity: 0.0;border: solid 1px #000;position: absolute;z-index: 9;"/>';
			
		} else {
			html += '<input type="file" onclick="chooseImg(\''+action+'\', \''+imagesBox+'\',' + len + ')" id="images_'+ len +'" name="images_'+ len +'" style="width: 100px;height: 100px;opacity: 0.0;border: solid 1px #000;position: absolute;z-index: 9;"/>';
			
		}
		html += '<img src="/Apps/Wap/View/default/Public/Images/up_load.jpg" data-url="" class="upload_images mr20" alt="" width="100" height="100"></div>';
	obj.before(html);
}