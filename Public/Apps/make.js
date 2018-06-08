$(document).ready(function(){
	$(window).resize(function() {
		var window_h=$(window).height();
		//var view_h=window_h-30;	
		
		$('iframe').attr('height',window_h);
	}).resize();	
	
	/*if($('.switch').size()>0){
		$('.switch').bootstrapSwitch();
	}*/
	
	vmodal_tag();
	confirm_tag();
	iCheckClass();
	select_all({});
	loadurl_tag();
	image_zoom();
	minicolors();
	//img_lazyload({});

	between_datepick();
	
	$('.loading').click(function(){
		$(this).addClass('hide');		
	});
	

	$('[data-type="delete"]').click(function(){
		var d=$(this).data();
		deleters(d);		
	});	
	
	/*Tooltips*/
	 $('.ttip, [data-toggle="tooltip"]').tooltip();

	/*Popover*/
	$('[data-popover="popover"]').popover();
  
  
    /*Return to top*/
    var offset = 220;
    var duration = 500;

      
    jQuery(window).scroll(function() {
        if (jQuery(this).scrollTop() > offset) {
            jQuery('.to-top').fadeIn(duration);
        } else {
            jQuery('.to-top').fadeOut(duration);
        }
    });
    
    jQuery('.to-top').click(function(event) {
        event.preventDefault();
        jQuery('html, body').animate({scrollTop: 0}, duration);
        return false;
    });	
	
	if($('.datepicker').size()>0){
		$('.datepicker').datetimepicker({
			format:'Y-m-d',
			timepicker:false,
		});
	}
	/*$(".change-active").parent().click(function(){
		var action = $(".change-active").data('action');
		if(action == 'dianpu'){
			$('[name="cation"]').val('baobei');
			$(".change-active").html('宝贝');
			$(".change-active").data('action','baobei');
		}
		if(action == 'baobei'){
			$('[name="cation"]').val('dianpu');
			$(".change-active").html('店铺');
			$(".change-active").data('action','dianpu');
		}
	});*/

	
	if($('.wangedit').size()>0){
		$('.wangedit').wangEditor({
			//uploadUrl:'http://www.ylh.com/Uploads/upload',
		});		
	}
	
    //刷新验证码
	if($('.verify').size()>0){
		$('.verify').click(function(){
			var d=$(this).data();
            $('.verify img').attr('src', d.url+'?' + Math.random());
        });	
	}
	
	if($('.qrcode').size()>0){
		$('.qrcode').each(function(){
			var d=$(this).data();
			$(this).qrcode({
				text:d.url,
				width:d.width,
				height:d.width,
			});			
		});
	}
	
	if($('[data-type="ueditor"]').size()>0){
		$('[data-type="ueditor"]').each(function(){
			var id=$(this).attr('id');
			var str='var ue_'+id+'=UE.getEditor("'+id+'");';
			eval(str);
		});
	}
	
	if($('.seo-set').size()>0){
		$('.seo-set').click(function(){
			var d=$('.seo-set').data();
			vmodal({title:'页面关键词设置',iframe:d.url,width:'95%'});
		});
	}
	
	//tab定位
	if($('.orders-menu').size() > 0) {
		var thisUrl=$('.orders-menu').attr('data-url');
		$('.orders-menu [href="'+thisUrl+'"]').closest('li').addClass('active');
	}
	
	//tab定位
	if($(".menu-left").size() > 0) {
		var menuUrl	=	$(".menu-left").data();
		$(".menu-left a[href='"+menuUrl.url+"']").addClass("active");
	}
	
	if($(".seller-side").size() > 0) {
		var menuUrl	=	$(".seller-side").data();
		$(".seller-side a[href='"+menuUrl.url+"']").addClass("active");
	}
	if($(".orders-tab").size() > 0) {
		var menuUrl	=	$(".orders-tab").data();
		$(".orders-tab a[href='"+menuUrl.url+"']").addClass("active");
	}
	if($(".store-nav").size() > 0) {
		var menuUrl	=	$(".store-nav").data();
		$(".store-nav a[href='"+menuUrl.url+"']").addClass("active");
	}
});



var restUrl='';
var loading='<div class="view-loading"><img src="/Public/images/loading.gif"></div>';
//var window_h=$(window).height();
//var view_h=window_h;

//图片延迟加截
function img_lazyload(param){
	var tag='body ';
	if(param!=undefined && param.tag) tag=param.tag;
	var loadimg='/Public/images/wait.png';
	if(param!=undefined && param.loadimg) loadimg=param.loadimg;
	
	if($(tag+"img.lazy").size()>0){
		$(tag+"img.lazy").lazyload({
			placeholder : loadimg,
			threshold : 200,
			effect: "fadeIn",
			skip_invisible : false,
		});
	}
}

function loadurl(param){
	$(param.tag).html(loading).load(param.url);
}
function ajax_load(tag,url,loadicon){
	if(loadicon!=undefined) loading=loadicon;
	$(tag).html(loading).load(url);
}
function loadurl_tag(loadicon){
	if(loadicon!=undefined) loading=loadicon;
	$('[data-type="loadurl"]').each(function(){
		var d=$(this).data();
		if(d.tag){
			$(d.tag).html(loading).load(d.url);
		}else{
			$(this).html(loading).load(d.url);
		}
	});
}


function gourl(param){
	location.href=param.url;
}

function ref(){
	location.reload();
}

function openToWin(param){
	
}

function back(){
	window.history.back();
}


function reframe(param){
	var script='top.'+param.name+'.window.ref();';
	eval(script);
}

function openWin(param){
	
	if(param.login==1) {
		check_login();
	}

	if(top.$('.views .view[data-page="'+param.name+'"]').size()>0){
		top.$('.views .view.active').removeClass('active');
		top.$('.views .view[data-page="'+param.name+'"]').addClass('active');
		
		top.$('.nav-label li').removeClass('active');
		top.$('.nav-label li[data-page="'+param.name+'"]').addClass('active');
		
		if(param.ref==1) {
			reframe({name:param.name});
		}
	}else{
		var window_h=$(window).height();
		var view_h=window_h-70-40;
		
		var height=param.height?param.height:view_h;
		var html='<div class="view active" data-page="'+param.name+'" height="'+view_h+'"><iframe src="'+param.url+'" height="'+height+'" width="100%" name="'+param.name+'" id="'+param.name+'" frameborder="0"></iframe></div>';
		top.$('.views .view.active').removeClass('active');
		top.$('.views').append(html);
		
		var nav='<li data-page="'+param.name+'" data-rootid="'+param.rootid+'"><span onclick="openWin({name:\''+param.name+'\',title:\''+param.title+'\',url:\''+param.url+'\',rootid:\''+param.rootid+'\'})">'+param.title+'</span> <span onclick="closeWin({name:\''+param.name+'\'})"><i class="fa fa-times"></i></span></li>';	
		top.$('.nav-label').append(nav);
		
		top.$('.nav-label li').removeClass('active');
		top.$('.nav-label li[data-page="'+param.name+'"]').addClass('active');
	}
	
	var d=$('.nav-label li.active').data();
	$('.mleft li[data-id="'+d.rootid+'"]').addClass('active').siblings().removeClass('active');
	var i=$('.mleft li[data-id="'+d.rootid+'"]').attr('data-index');
	smenu2(i);
	
	$('.left-menu li[id="'+param.name+'"]').addClass('active').siblings().removeClass('active');
	
	if(param.script) eval(param.script);
}

function closeWin(param){
	var index=top.$('.views .view[data-page="'+param.name+'"]').index();
	top.$('.views .view').eq(index-1).addClass('active');
	top.$('.views .view[data-page="'+param.name+'"]').remove();
	top.$('.nav-label li[data-page="'+param.name+'"]').remove();
}



function view(){
	var h=window_h;
	$('.view').css('height',h+'px');	
}


function valert(param){
	if(param.code!=undefined && param.code==1) param.status='success';
    $.gritter.removeAll({
        after_close: function(){
			$.gritter.add({
				position: param.position?param.position:'top-right',
				title: param.title?param.title:'提示',
				text: param.msg,
				class_name: param.status
			});
        }
    });
	
}

function talert(param){
	if(param.title==undefined) param.title='提示';
	param.status = param.status == 1 ? 'success' : 'error';
	if(param.code!==undefined && param.code==1) param.status = 'success';
	if(param.code!==undefined && param.code!=1) param.status = 'error';
	toastr.options = {
	  "closeButton": true,
	  "debug": false,
	  "positionClass": "toast-bottom-center",
	  "onclick": null,
	  "showDuration": "300",
	  "hideDuration": "1000",
	  "timeOut": "5000",
	  "extendedTimeOut": "1000",
	  "showEasing": "swing",
	  "hideEasing": "linear",
	  "showMethod": "fadeIn",
	  "hideMethod": "fadeOut",
	  "onShown":param.show_callback,
	  "onHidden":param.hide_callback,
	}
	switch(param.status){
		case 'success':
			toastr.success(param.msg,param.title);
		break;
		case 'warning':
			toastr.warning(param.msg,param.title);
		break;
		case 'error':
			toastr.error(param.msg,param.title);
		break;
		case 'info':
			toastr.info(param.msg,param.title);
		break;
	}
	
}




function loadicon(t){
	if(t==true){
		$('.loading').removeClass('hide');
	}else{
		$('.loading').addClass('hide');		
	}
}

function loadicon_tag(t,obj){
    var loading = $('<div class="loading"><i class="fa fa-refresh fa-spin"></i></div>');
      
    loading.appendTo(obj);
    loading.fadeIn();
    setTimeout(function() {
       loading.fadeOut();
    }, 1000);
      

}



function province(param){
	var province=$(param.tag.p).attr('data-value');
	if(province) {
		var url=restUrl+'/Rest/city/sid/'+province;
		ajax_get({
			url:url,
			script:'city_select(ret,param.option)',
			option:param,
		});		
		
	}
	
	$(param.tag.p).change(function(){
		if(param.tag.a){
			$(param.tag.a).html('<option value="">请选择区域</option>');
		}
		//alert($(this).val());
		var url=restUrl+'/Rest/city/sid/'+$(this).val();
		ajax_get({
			url:url,
			script:'city_select(ret,param.option)',
			option:param,
		});
	});

}

function city_select(ret,param){
	var city=$(param.tag.c).attr('data-value');
	
	var html='<option value="">请选择城市</option>';	
	for(var i=0;i<ret.length;i++){
		html+='<option value="'+ret[i].id+'" '+(ret[i].id==city?'selected':'')+'>'+ret[i].name+'</option>';
	}
	$(param.tag.c).html(html);

}

function city(param){
	var city=$(param.tag.c).attr('data-value');
	if(city) {
		var url=restUrl+'/Rest/city/sid/'+city;
		ajax_get({
			url:url,
			script:'area_select(ret,param.option)',
			option:param,
		});	
		
	}	
	
	$(param.tag.c).change(function(){
		//alert($(this).val());
		var url=restUrl+'/Rest/city/sid/'+$(this).val();
		ajax_get({
			url:url,
			script:'area_select(ret,param.option)',
			option:param,
		});
	});
}
function area_select(ret,param){
	var area=$(param.tag.a).attr('data-value');
	
	var html='<option value="">请选择区域</option>';
	
	for(var i=0;i<ret.length;i++){
		html+='<option value="'+ret[i].id+'" '+(ret[i].id==area?'selected':'')+'>'+ret[i].name+'</option>';
	}
	$(param.tag.a).html(html);

}

function ajax_post_form(param,success_callback){
	
	loadicon(true);
	var form=$(param.formid);
	var d=form.data();
	if(d.url && param.url==undefined) param.url=d.url;
	
	var options = {
		beforeSubmit:param.beforeSubmit,
		target: '#ajax_tips',
		url: param.url,
		type: 'POST',
		success:function (ret) {
			if(param.script) eval(param.script);
			if(success_callback) success_callback(ret);
			loadicon(false);
		},
		error:function(e){
			valert({msg:'请求失败！'});
			loadicon(false);
		}
	};
	form.ajaxSubmit(options);	
}

function ajax_post(param,success_callback){
	if(param.loadicon!=false) loadicon(true);
	if(param.async != false) param.async = true;
	$.ajax({  
		type: 'post', 
		url: param.url,
		data:param.data,
		dataType:'json',
		async: param.async,
		success: function (ret) {
			if(param.script) eval(param.script);
			if(success_callback) success_callback(ret);
			loadicon(false);
		},
		error:function(e){
			valert({msg:'请求失败！'});
			loadicon(false);
		}
	}); 	

}

function ajax_get(param,success_callback){
	if(param.loadicon==true) loadicon(true);

	dataType=param.dataType?param.dataType:'json';
	$.ajax({  
		type: 'get', 
		url: param.url,
		dataType:dataType,
		success: function (ret) {
			if(param.script) eval(param.script);
			if(success_callback) success_callback(ret);
			loadicon(false);
		},
		error:function(e){
			valert({msg:'请求失败！'});
			loadicon(false);
		}
	}); 	
}


function iCheckClass(param){
	/** BEGIN ICHECK **/
	/** Minimal Skins **/
	var tag='';
	if(param) tag=param+' ';

	if ($(tag+'.i-black').length > 0){
		$(tag+'input.i-black').iCheck({
			checkboxClass: 'icheckbox_minimal',
			radioClass: 'iradio_minimal',
			increaseArea: '20%'
		});
	}
	if ($(tag+'.i-red').length > 0){
		$(tag+'input.i-red').iCheck({
			checkboxClass: 'icheckbox_minimal-red',
			radioClass: 'iradio_minimal-red',
			increaseArea: '20%'
		});
	}
	if ($(tag+'.i-green').length > 0){
		$(tag+'input.i-green').iCheck({
			checkboxClass: 'icheckbox_minimal-green',
			radioClass: 'iradio_minimal-green',
			increaseArea: '20%'
		});
	}
	if ($(tag+'.i-blue').length > 0){
		$(tag+'input.i-blue').iCheck({
			checkboxClass: 'icheckbox_minimal-blue',
			radioClass: 'iradio_minimal-blue',
			increaseArea: '20%'
		});
	}
	if ($(tag+'.i-aero').length > 0){
		$(tag+'input.i-aero').iCheck({
			checkboxClass: 'icheckbox_minimal-aero',
			radioClass: 'iradio_minimal-aero',
			increaseArea: '20%'
		});
	}
	if ($(tag+'.i-grey').length > 0){
		$(tag+'input.i-grey').iCheck({
			checkboxClass: 'icheckbox_minimal-grey',
			radioClass: 'iradio_minimal-grey',
			increaseArea: '20%'
		});
	}
	if ($(tag+'.i-orange').length > 0){
		$(tag+'input.i-orange').iCheck({
			checkboxClass: 'icheckbox_minimal-orange',
			radioClass: 'iradio_minimal-orange',
			increaseArea: '20%'
		});
	}
	if ($(tag+'.i-yellow').length > 0){
		$(tag+'input.i-yellow').iCheck({
			checkboxClass: 'icheckbox_minimal-yellow',
			radioClass: 'iradio_minimal-yellow',
			increaseArea: '20%'
		});
	}
	if ($(tag+'.i-pink').length > 0){
		$(tag+'input.i-pink').iCheck({
			checkboxClass: 'icheckbox_minimal-pink',
			radioClass: 'iradio_minimal-pink',
			increaseArea: '20%'
		});
	}
	if ($(tag+'.i-purple').length > 0){
		$(tag+'input.i-purple').iCheck({
			checkboxClass: 'icheckbox_minimal-purple',
			radioClass: 'iradio_minimal-purple',
			increaseArea: '20%'
		});
	}
		
	/** Square Skins **/
	if ($(tag+'.i-black-square').length > 0){
		$(tag+'input.i-black-square').iCheck({
			checkboxClass: 'icheckbox_square',
			radioClass: 'iradio_square',
			increaseArea: '20%'
		});
	}
	if ($(tag+'.i-red-square').length > 0){
		$(tag+'input.i-red-square').iCheck({
			checkboxClass: 'icheckbox_square-red',
			radioClass: 'iradio_square-red',
			increaseArea: '20%'
		});
	}
	if ($(tag+'.i-green-square').length > 0){
		$(tag+'input.i-green-square').iCheck({
			checkboxClass: 'icheckbox_square-green',
			radioClass: 'iradio_square-green',
			increaseArea: '20%'
		});
	}
	if ($(tag+'.i-blue-square').length > 0){
		$(tag+'input.i-blue-square').iCheck({
			checkboxClass: 'icheckbox_square-blue',
			radioClass: 'iradio_square-blue',
			increaseArea: '20%'
		});
	}
	if ($(tag+'.i-aero-square').length > 0){
		$(tag+'input.i-aero-square').iCheck({
			checkboxClass: 'icheckbox_square-aero',
			radioClass: 'iradio_square-aero',
			increaseArea: '20%'
		});
	}
	if ($(tag+'.i-grey-square').length > 0){
		$(tag+'input.i-grey-square').iCheck({
			checkboxClass: 'icheckbox_square-grey',
			radioClass: 'iradio_square-grey',
			increaseArea: '20%'
		});
	}
	if ($(tag+'.i-orange-square').length > 0){
		$(tag+'input.i-orange-square').iCheck({
			checkboxClass: 'icheckbox_square-orange',
			radioClass: 'iradio_square-orange',
			increaseArea: '20%'
		});
	}
	if ($(tag+'.i-yellow-square').length > 0){
		$(tag+'input.i-yellow-square').iCheck({
			checkboxClass: 'icheckbox_square-yellow',
			radioClass: 'iradio_square-yellow',
			increaseArea: '20%'
		});
	}
	if ($(tag+'.i-pink-square').length > 0){
		$(tag+'input.i-pink-square').iCheck({
			checkboxClass: 'icheckbox_square-pink',
			radioClass: 'iradio_square-pink',
			increaseArea: '20%'
		});
	}
	if ($(tag+'.i-purple-square').length > 0){
		$(tag+'input.i-purple-square').iCheck({
			checkboxClass: 'icheckbox_square-purple',
			radioClass: 'iradio_square-purple',
			increaseArea: '20%'
		});
	}
		
	/** Flat Skins **/
	if ($(tag+'.i-black-flat').length > 0){
		$(tag+'input.i-black-flat').iCheck({
			checkboxClass: 'icheckbox_flat',
			radioClass: 'iradio_flat',
			increaseArea: '20%'
		});
	}
	if ($(tag+'.i-red-flat').length > 0){
		$(tag+'input.i-red-flat').iCheck({
			checkboxClass: 'icheckbox_flat-red',
			radioClass: 'iradio_flat-red',
			increaseArea: '20%'
		});
	}
	if ($(tag+'.i-green-flat').length > 0){
		$(tag+'input.i-green-flat').iCheck({
			checkboxClass: 'icheckbox_flat-green',
			radioClass: 'iradio_flat-green',
			increaseArea: '20%'
		});
	}
	if ($(tag+'.i-blue-flat').length > 0){
		$(tag+'input.i-blue-flat').iCheck({
			checkboxClass: 'icheckbox_flat-blue',
			radioClass: 'iradio_flat-blue',
			increaseArea: '20%'
		});
	}
	if ($(tag+'.i-aero-flat').length > 0){
		$(tag+'input.i-aero-flat').iCheck({
			checkboxClass: 'icheckbox_flat-aero',
			radioClass: 'iradio_flat-aero',
			increaseArea: '20%'
		});
	}
	if ($(tag+'.i-grey-flat').length > 0){
		$(tag+'input.i-grey-flat').iCheck({
			checkboxClass: 'icheckbox_flat-grey',
			radioClass: 'iradio_flat-grey',
			increaseArea: '20%'
		});
	}
	if ($(tag+'.i-orange-flat').length > 0){
		$(tag+'input.i-orange-flat').iCheck({
			checkboxClass: 'icheckbox_flat-orange',
			radioClass: 'iradio_flat-orange',
			increaseArea: '20%'
		});
	}
	if ($(tag+'.i-yellow-flat').length > 0){
		$(tag+'input.i-yellow-flat').iCheck({
			checkboxClass: 'icheckbox_flat-yellow',
			radioClass: 'iradio_flat-yellow',
			increaseArea: '20%'
		});
	}
	if ($(tag+'.i-pink-flat').length > 0){
		$(tag+'input.i-pink-flat').iCheck({
			checkboxClass: 'icheckbox_flat-pink',
			radioClass: 'iradio_flat-pink',
			increaseArea: '20%'
		});
	}
	if ($(tag+'.i-purple-flat').length > 0){
		$(tag+'input.i-purple-flat').iCheck({
			checkboxClass: 'icheckbox_flat-purple',
			radioClass: 'iradio_flat-purple',
			increaseArea: '20%'
		});
	}
	/** END ICHECK **/
}


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


//模态框
function vmodal(param,callback){
        var height=$(window).height()-250;
	var confirm='<div class="p10 text-center"><button class="btn btn-success btn-rad btn-trans modal-ok mr10"><i class="fa fa-check"></i> 确定</button><button class="btn btn-rad btn-warning btn-trans modal-cancel" data-dismiss="modal"><i class="fa fa-times"></i> 取消</button></div>';
	var modaltag=param.tag?param.tag:'#ajax-modal';
	var title=param.title?param.title:'提示窗口';   

		//alert(modaltag);
        
	$(modaltag).find('.modal-title').html(title);
	if(param.width) $(modaltag).find('.modal-dialog').css({width:param.width});
	

	var options=new Array();
	options['backdrop']=true;
	if(param.backdrop==false) options['backdrop']=false;
	
	if(param.footer==false){
		$(modaltag).find('.modal-footer').addClass('hide');
	}else{
		$(modaltag).find('.modal-footer').removeClass('hide');
	}
		

        if(param.url) $(modaltag).find('.modal-body').html(loading).load(param.url);
        else if(param.iframe){
            $(modaltag).find('.modal-body').html('<iframe class="embed-responsive-item" width="100%" height="'+height+'" src="'+param.iframe+'" frameborder="no" border="0" marginwidth="0" marginheight="0"></iframe>');
        }else if(param.msg) {
            if(param.confirm){
				$(modaltag).find('.modal-body').html('<div class="'+param.class+'">'+param.msg+'</div>'+confirm);
            }else{
				$(modaltag).find('.modal-body').html('<div class="'+param.class+'">'+param.msg+'</div>');
            }
		}

	$(modaltag).modal(options);
	if(param.script) eval(param.script);
	if(callback) callback();
}

function vmodal_tag(param){
	var tag='';
	if(param && param.tag) tag=param.tag+' ';
	$(tag+'[data-type="vmodal"]').click(function(){
		var d=$(this).data();
		vmodal(d);
	});	
}

//删除确认
function confirm_form(param,callback,success_callback){
	vmodal({title:param.title,msg:param.msg,class:param.class,width:param.width,confirm:1},function(){
		$('.modal-ok').click(function(){
			ajax_post_form({
				formid:param.formid,
				url:param.furl,
				script:param.script
			},success_callback);
		});
	});
	if(callback) callback();
}

function deleters(param,callback){
	vmodal({title:param.title,msg:param.msg,class:param.class,width:param.width,confirm:1},function(){
		$('.modal-ok').click(function(){
			ajax_get({
				url:param.url,
				script:param.script
			});
		});
	});	
}
function confirm_tag(){
	$('[data-type="confirm"]').click(function(){
		var d=$(this).data();
		deleters(d);
	});	
}
//表单验提交
function checkform(param,success_callback){
	var rules=param.rules?param.rules:{};
	var messages=param.messages?param.messages:{};
	
	var form1 = $(param.formid);
	var d=form1.data();
	

	
	if(param.url==undefined && d.url!='') param.url=d.url;
	//alert(param.url);
	
	form1.validate({
		errorClass: "help-block animation-slideDown",
		errorElement: "div",
		errorPlacement: function(e, a) {
			a.parents(".form-group > div").append(e)
		},
		highlight: function(e) {
			$(e).closest(".form-group").removeClass("has-success has-error").addClass("has-error"),
			$(e).closest(".help-block").remove()
		},
		success: function(e) {
			e.closest(".form-group").removeClass("has-success has-error"),
			e.closest(".help-block").remove()
		},
		rules: rules,

		messages: messages,

		invalidHandler: function(event, validator) { //验证前错误提示
			// success1.hide();
			//error1.show();
		},

		submitHandler: function(form) { //验证完成后操作
			if(param.beforeScript) {
				var result=eval(param.beforeScript);
				//alert(result);
				if(result==false) return false;
			}
			
			
			if(param.is_submit==true){
				if(param.beforeSubmit) eval(param.beforeSubmit);
				form1[0].submit();
				if(param.afterSubmit) eval(param.afterSubmit);
			}else{
				ajax_post_form({
					formid: param.formid,
					url: param.url,
					script: param.script,
					beforeSubmit:param.beforeSubmit,
					headers:param.headers,
				},success_callback);				
			}
			if(param.is_shield==true){
				$('#shield').show();
			}
			//提交按钮不可点击
			if(param.btn_disable){
				$('#'+param.btn_disable).attr("disabled", "true");
			}
			return false;

		}
	});	
	
}


//editor
//function editor(){
//	$('[data-type="editor"]').redactor();
//}



function image_zoom(param){
	var tag=$('body');	
	if(param && param.obj) var tag=param.obj;
	else if(param && param.tag) var tag=$(param.tag);
	
	if(tag.find('.image-zoom').size()>0){
		tag.find('.image-zoom').magnificPopup({ 
			type: 'image',
			mainClass: 'mfp-with-zoom', // this class is for CSS animation below
			zoom: {
				enabled: true, // By default it's false, so don't forget to enable it
				duration: 300, // duration of the effect, in milliseconds
				easing: 'ease-in-out', // CSS transition easing function 
				opener: function(openerElement) {
					var parent = $(openerElement);
					return parent;
				}
			}
		});	
	}
}


function itabs(param,script){
	param.tag=param.tag?param.tag+' ':'body ';

	if(param.event=='mouseover'){
		$(param.tag+' .itabs li').mouseover(function(){
			var d=$(this).parent().data();
			var obj=$(this);
			
			var index=$(this).index();
			$(this).addClass('active').siblings().removeClass('active');
			$('#'+d.id+'.itabs-content .itabs-pane').removeClass('active').eq(index).addClass('active');
			
			if(script) script(obj);
		});		
	}else{

		$(param.tag+' .itabs li').click(function(){
			var d=$(this).parent().data();
			var obj=$(this);

			var index=$(this).index();
			$(this).addClass('active').siblings().removeClass('active');

			var prevTag=d.id?'#'+d.id+' ':'';
			$(prevTag+'.itabs-content .itabs-pane').removeClass('active').eq(index).addClass('active');
			if(script) script(obj);			
		});
	}
}

//颜色选择器
function minicolors(){
        $('.minicolors').each(function() {
            $(this).minicolors({
                control: $(this).attr('data-control') || 'hue',
                defaultValue: $(this).attr('data-defaultValue') || '',
                inline: $(this).attr('data-inline') === 'true',
                letterCase: $(this).attr('data-letterCase') || 'lowercase',
                opacity: $(this).attr('data-opacity'),
                position: $(this).attr('data-position') || 'bottom left',
                change: function(hex, opacity) {
                    if (!hex) return;
                    if (opacity) hex += ', ' + opacity;
                    if (typeof console === 'object') {
                        console.log(hex);
                    }
                },
                theme: 'bootstrap'
            });

        });	
}


//随机数
function random(min,max){
    return Math.floor(min+Math.random()*(max-min));
}
//数组随机抽取单元
function randomSort(arr)
{
    if(!arr || !arr.length)
    {
		return [];
    }
    var outputArr = [];
    var cloneInputArr = arr.slice(0,arr.length);
    while( cloneInputArr.length)
	{
		outputArr.push(cloneInputArr.splice(Math.random() * cloneInputArr.length,1)[0]);
	}
	return outputArr;
}

//从Y-m-d H:i:s 中取Y-m-d
function dateCmp(d){
	var ret=d.split(' ');
	return ret[0];
}

//自定义表单错误提示
function hidden_check_show(param){
	var tpl=new Array();
	tpl[0]=param.msg;
	tpl[1]='<div class="alert alert-warning alert-white rounded">'+
				'<button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>'+
				'<div class="icon"><i class="fa fa-warning"></i></div>'+
				'<strong>错误！</strong> '+param.msg+
			'</div>';
	var tpl_index=param.tpl?param.tpl:0;
	
	var error_tag=param.error_tag?param.error_tag:'.form-group';

	var tips='<div class="help-block animation-slideDown">'+tpl[tpl_index]+'</div>';
	if(param.tag){
		$(param.tag).closest(error_tag+' >div').append(tips);
		$(param.tag).closest(error_tag).addClass('has-error');		
	}else{
		$(error_tag+' >div').append(tips);
		$(error_tag).addClass('has-error');				
	}
	
	$('.loading').addClass('hide');

}
function hidden_check_remove(param){
	$(param.tag).closest('.form-group .help-block').remove();
	$(param.tag).closest('.form-group').removeClass('has-error');
	$('.loading').addClass('hide');
}

function between_datepick(){
	if($('[data-type="between-datepick"]').size()>0){
		$('[data-type="between-datepick"]').each(function(){
			var d=$(this).data();
			//var options={d.var:$(d.tag).val()!=''?$(d.tag).val():false};
			
			//var options={maxDate:$('#eday').val()!=''?$('#eday').val():false};
			if(d.var=='minDate'){
				$(this).datetimepicker({
					timepicker:false,
					format:'Y-m-d',
					onShow:function( ct ){
						this.setOptions({minDate:$('#sday').val()!=''?$('#sday').val():false});
					},
				});
			}else{
				$(this).datetimepicker({
					timepicker:false,
					format:'Y-m-d',
					onShow:function( ct ){
						this.setOptions({maxDate:$('#eday').val()!=''?$('#eday').val():false});
					},
				});				
			}
			
		});
	}
}


//不能为纯数据
jQuery.validator.addMethod("notNumber",
	function(value, element) {

		return this.optional(element) || /(?!\d+$)\S+$/.test(value);
	},
	"字母或字母与数字的组合，不能为纯数字！");

//不允许特殊字符
jQuery.validator.addMethod("notSpecial",
	function(value, element) {
		//alert(/[^(\ )(\~)(\!)(\@)(\#)  (\$)(\%)(\^)(\&)(\*)(\()(\))(\-)(\_)(\+)(\=)  (\[)(\])(\{)(\})(\|)(\\)(\;)(\:)(\')(\")(\,)(\.)(\/)  (\<)(\>)(\?)(\)]+/.test(value));
		//var containSpecial = new RegExp(/[(\ )(\~)(\!)(\@)(\#)  (\$)(\%)(\^)(\&)(\*)(\()(\))(\-)(\_)(\+)(\=)  (\[)(\])(\{)(\})(\|)(\\)(\;)(\:)(\')(\")(\,)(\.)(\/)  (\<)(\>)(\?)(\)]+/);
		return this.optional(element) || /^[\u4e00-\u9fa5a-zA-Z0-9]+$/ .test(value);
	},
	"不允许特殊字符");

//大写、小写、数字两两结合
jQuery.validator.addMethod("str_strong",
	function(value, element) {
		return this.optional(element) || /(?![A-Z]+$)(?![a-z]+$)(?!\d+$)\S+$/.test(value);
	},
	"大写、小写、数字两两结合");


/*表单验证函数*/
// 字符验证 
jQuery.validator.addMethod("stringCheck",
function(value, element) {
	return this.optional(element) || /^[\u0391-\uFFE5\w]+$/.test(value);
},
"只能包括中文字、英文字母、数字和下划线");

//验证用户名，支持中文
jQuery.validator.addMethod("username",
function(value, element) {
	return this.optional(element) || /^(([\u4E00-\uFA29]|[\uE7C7-\uE7F3]|[a-zA-Z0-9]){3,25})*$/.test(value);
},
"只能包括中文字、英文字母、数字和下划线");

//验证店铺名，支持中文
jQuery.validator.addMethod("storename",
function(value, element) {
	return this.optional(element) || /^(([\u4E00-\uFA29]|[\uE7C7-\uE7F3]|[a-zA-Z0-9]){5,25})*$/.test(value);
},
"只能包括中文字、英文字母、数字和下划线");

//验证店铺域名
jQuery.validator.addMethod("domain",
function(value, element) {
	return this.optional(element) || /^([a-zA-Z]([a-zA-Z0-9]){5,16})*$/.test(value);
},
"只能字母开头，英文字母、数字和下划线");

// 中文字两个字节 
jQuery.validator.addMethod("byteRangeLength",
function(value, element, param) {
	var length = value.length;
	for (var i = 0; i < value.length; i++) {
		if (value.charCodeAt(i) > 127) {
			length++;
		}
	}
	return this.optional(element) || (length >= param[0] && length <= param[1]);
},
"请确保输入的值在3-15个字节之间(一个中文字算2个字节)");

// 身份证号码验证 
jQuery.validator.addMethod("isIdCardNo",
function(value, element) {
	return this.optional(element) || idCardNoUtil.checkIdCardNo(value);
},
"请正确输入您的身份证号码");

//护照编号验证
jQuery.validator.addMethod("passport",
function(value, element) {
	return this.optional(element) || checknumber(value);
},
"请正确输入您的护照编号");

// 手机号码验证 
jQuery.validator.addMethod("isMobile",
function(value, element) {
	var length = value.length;
	//var mobile = /^(((13[0-9]{1})|(15[0-9]{1}))+\d{8})$/;
	var mobile = /^((1[0-9]{2})+\d{8})$/;
	return this.optional(element) || (length == 11 && mobile.test(value));
},
"请正确填写您的手机号码");

// 电话号码验证 
jQuery.validator.addMethod("isTel",
function(value, element) {
	var tel = /^\d{3,4}-?\d{7,9}$/; //电话号码格式010-12345678 
	return this.optional(element) || (tel.test(value));
},
"请正确填写您的电话号码");

// 联系电话(手机/电话皆可)验证 
jQuery.validator.addMethod("isPhone",
function(value, element) {
	var length = value.length;
	var mobile = /^(((13[0-9]{1})|(15[0-9]{1}))+\d{8})$/;
	var tel = /^\d{3,4}-?\d{7,9}$/;
	return this.optional(element) || (tel.test(value) || mobile.test(value));

},
"请正确填写您的联系电话");

// 邮政编码验证 
jQuery.validator.addMethod("isZipCode",
function(value, element) {
	var tel = /^[0-9]{6}$/;
	return this.optional(element) || (tel.test(value));
},
"请正确填写您的邮政编码");

// 推荐ID码 
jQuery.validator.addMethod("refCode",
function(value, element) {
	var code = /^[0-9]+$/;
	return this.optional(element) || (code.test(value));
},
"请正确填写用户推荐ID");

//身份证相关
var idCardNoUtil = {
	provinceAndCitys: {
		11 : "北京",
		12 : "天津",
		13 : "河北",
		14 : "山西",
		15 : "内蒙古",
		21 : "辽宁",
		22 : "吉林",
		23 : "黑龙江",
		31 : "上海",
		32 : "江苏",
		33 : "浙江",
		34 : "安徽",
		35 : "福建",
		36 : "江西",
		37 : "山东",
		41 : "河南",
		42 : "湖北",
		43 : "湖南",
		44 : "广东",
		45 : "广西",
		46 : "海南",
		50 : "重庆",
		51 : "四川",
		52 : "贵州",
		53 : "云南",
		54 : "西藏",
		61 : "陕西",
		62 : "甘肃",
		63 : "青海",
		64 : "宁夏",
		65 : "新疆",
		71 : "台湾",
		81 : "香港",
		82 : "澳门",
		91 : "国外"
	},

	powers: ["7", "9", "10", "5", "8", "4", "2", "1", "6", "3", "7", "9", "10", "5", "8", "4", "2"],

	parityBit: ["1", "0", "X", "9", "8", "7", "6", "5", "4", "3", "2"],

	genders: {
		male: "男",
		female: "女"
	},

	checkAddressCode: function(addressCode) {
		var check = /^[1-9]\d{5}$/.test(addressCode);
		if (!check) return false;
		if (idCardNoUtil.provinceAndCitys[parseInt(addressCode.substring(0, 2))]) {
			return true;
		} else {
			return false;
		}
	},

	checkBirthDayCode: function(birDayCode) {
		var check = /^[1-9]\d{3}((0[1-9])|(1[0-2]))((0[1-9])|([1-2][0-9])|(3[0-1]))$/.test(birDayCode);
		if (!check) return false;
		var yyyy = parseInt(birDayCode.substring(0, 4), 10);
		var mm = parseInt(birDayCode.substring(4, 6), 10);
		var dd = parseInt(birDayCode.substring(6), 10);
		var xdata = new Date(yyyy, mm - 1, dd);
		if (xdata > new Date()) {
			return false; //生日不能大于当前日期
		} else if ((xdata.getFullYear() == yyyy) && (xdata.getMonth() == mm - 1) && (xdata.getDate() == dd)) {
			return true;
		} else {
			return false;
		}
	},

	getParityBit: function(idCardNo) {
		var id17 = idCardNo.substring(0, 17);
		var power = 0;
		for (var i = 0; i < 17; i++) {
			power += parseInt(id17.charAt(i), 10) * parseInt(idCardNoUtil.powers[i]);
		}
		var mod = power % 11;
		return idCardNoUtil.parityBit[mod];
	},

	checkParityBit: function(idCardNo) {
		var parityBit = idCardNo.charAt(17).toUpperCase();
		if (idCardNoUtil.getParityBit(idCardNo) == parityBit) {
			return true;
		} else {
			return false;
		}
	},

	checkIdCardNo: function(idCardNo) {
		//15位和18位身份证号码的基本校验
		var check = /^\d{15}|(\d{17}(\d|x|X))$/.test(idCardNo);
		if (!check) return false;
		//判断长度为15位或18位
		if (idCardNo.length == 15) {
			return idCardNoUtil.check15IdCardNo(idCardNo);
		} else if (idCardNo.length == 18) {
			return idCardNoUtil.check18IdCardNo(idCardNo);
		} else {
			return false;
		}
	},
	//校验15位的身份证号码
	check15IdCardNo: function(idCardNo) {
		//15位身份证号码的基本校验
		var check = /^[1-9]\d{7}((0[1-9])|(1[0-2]))((0[1-9])|([1-2][0-9])|(3[0-1]))\d{3}$/.test(idCardNo);
		if (!check) return false;
		//校验地址码
		var addressCode = idCardNo.substring(0, 6);
		check = idCardNoUtil.checkAddressCode(addressCode);
		if (!check) return false;
		var birDayCode = '19' + idCardNo.substring(6, 12);
		//校验日期码
		return idCardNoUtil.checkBirthDayCode(birDayCode);
	},
	//校验18位的身份证号码
	check18IdCardNo: function(idCardNo) {
		//18位身份证号码的基本格式校验
		var check = /^[1-9]\d{5}[1-9]\d{3}((0[1-9])|(1[0-2]))((0[1-9])|([1-2][0-9])|(3[0-1]))\d{3}(\d|x|X)$/.test(idCardNo);
		if (!check) return false;
		//校验地址码
		var addressCode = idCardNo.substring(0, 6);
		check = idCardNoUtil.checkAddressCode(addressCode);
		if (!check) return false;
		//校验日期码
		var birDayCode = idCardNo.substring(6, 14);
		check = idCardNoUtil.checkBirthDayCode(birDayCode);
		if (!check) return false;
		//验证校检码
		return idCardNoUtil.checkParityBit(idCardNo);
	},
	formateDateCN: function(day) {
		var yyyy = day.substring(0, 4);
		var mm = day.substring(4, 6);
		var dd = day.substring(6);
		return yyyy + '-' + mm + '-' + dd;
	},
	//获取信息
	getIdCardInfo: function(idCardNo) {
		var idCardInfo = {
			gender: "",
			//性别
			birthday: "" // 出生日期(yyyy-mm-dd)
		};
		if (idCardNo.length == 15) {
			var aday = '19' + idCardNo.substring(6, 12);
			idCardInfo.birthday = idCardNoUtil.formateDateCN(aday);
			if (parseInt(idCardNo.charAt(14)) % 2 == 0) {
				idCardInfo.gender = idCardNoUtil.genders.female;
			} else {
				idCardInfo.gender = idCardNoUtil.genders.male;
			}
		} else if (idCardNo.length == 18) {
			var aday = idCardNo.substring(6, 14);
			idCardInfo.birthday = idCardNoUtil.formateDateCN(aday);
			if (parseInt(idCardNo.charAt(16)) % 2 == 0) {
				idCardInfo.gender = idCardNoUtil.genders.female;
			} else {
				idCardInfo.gender = idCardNoUtil.genders.male;
			}
		}
		return idCardInfo;
	},

	getId15: function(idCardNo) {
		if (idCardNo.length == 15) {
			return idCardNo;
		} else if (idCardNo.length == 18) {
			return idCardNo.substring(0, 6) + idCardNo.substring(8, 17);
		} else {
			return null;
		}
	},

	getId18: function(idCardNo) {
		if (idCardNo.length == 15) {
			var id17 = idCardNo.substring(0, 6) + '19' + idCardNo.substring(6);
			var parityBit = idCardNoUtil.getParityBit(id17);
			return id17 + parityBit;
		} else if (idCardNo.length == 18) {
			return idCardNo;
		} else {
			return null;
		}
	}
};
//验证护照是否正确
function checknumber(number) {
	var str = number;
	//在JavaScript中，正则表达式只能使用"/"开头和结束，不能使用双引号
	var Expression = /(P\d{7})|(G\d{8})/;
	var objExp = new RegExp(Expression);
	if (objExp.test(str) == true) {
		return true;
	} else {
		return false;
	}
};



/**
 * 密码必须为字母及数字组合类型
 */
jQuery.validator.addMethod("alnum", function(value, element){
	return this.optional(element) || /^(([0-9]+[a-zA-Z]+[0-9]*)|([a-zA-Z]+[0-9]+[a-zA-Z]*))+$/.test(value);
});
//如不选中国，则不作身份证号码验证
jQuery.validator.addMethod("isIdCardNo1",
	function(value, element) {
		if($('#country').val()!=37)return true;
		return this.optional(element) || idCardNoUtil.checkIdCardNo(value) ;
});
// 如不选中国，则不作手机号码验证
jQuery.validator.addMethod("isMobile1",
	function(value, element) {
		if($('#country').val()!=37)return true;
		var length = value.length;
		//var mobile = /^(((13[0-9]{1})|(15[0-9]{1}))+\d{8})$/;
		var mobile = /^((1[0-9]{2})+\d{8})$/;
		return this.optional(element) || (length == 11 && mobile.test(value));
}, "请正确填写您的手机号码");
/**
 * 反之重复提交
 */
function clickDisabled() {
	//点击后三秒内不能给再点击
	$(".btn-submit").click(function() {
	 	$(this).addClass("disabled");
	 	setTimeout("$('.btn-submit').removeClass('disabled');", 3000);
	})
}

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


function minicolors(tag){
	if(tag==undefined) var tag='';
	else tag=tag+' ';
        $(tag+'.minicolors').each(function() {
            $(this).minicolors({
                control: $(this).attr('data-control') || 'hue',
                defaultValue: $(this).attr('data-defaultValue') || '',
                inline: $(this).attr('data-inline') === 'true',
                letterCase: $(this).attr('data-letterCase') || 'lowercase',
                opacity: $(this).attr('data-opacity'),
                position: $(this).attr('data-position') || 'bottom left',
                change: function(hex, opacity) {
                    if (!hex) return;
                    if (opacity) hex += ', ' + opacity;
                    if (typeof console === 'object') {
                        console.log(hex);
                    }
                },
                theme: 'bootstrap'
            });

        });
}

function api(param,callback){
	if(param.url==undefined) param.url='/Api/api';
	ajax_post({
		url:param.url,
		data:param.data
	},function(ret){
		if(callback) callback(ret);		
	});
}


//选择模板
function select_tpl(obj){
	obj.addClass('selected').siblings().removeClass('selected');
	var d=obj.data();
	$('#tpl_id').val(d.id);
	$('#tpl_url').val(d.url);	
}

//删除
function item_delete(obj){
	var obj;
	vmodal({
		title:'删除',
		msg:'<h4>真的要删除吗？</h4>',
		class:'text-center',
		confirm:1,
		tag:'#ajax-modal2',
		width:'650px',
		backdrop:false,
	},function(){
		$('.modal-ok').click(function(){
			obj.closest('.box').remove();
			$('#ajax-modal2').modal('hide');
		});
	});
}

//发布店铺
function publish(){
	vmodal({
		title:'发布',
		msg:'<h4>确定要发布该店铺装修吗？</h4>',
		class:'text-center',
		confirm:1,
		width:'650px',
		backdrop:false,
	},function(){
		$('.modal-ok').click(function(){
			api({
				data:{apiurl:'/Make/api/method/publish',is_openid:1}
			},function(ret){
				if(ret.code==1){
					var html='<div class="text-center"><h4>发布成功！</h4></div><div class="p10 text-center"><a href="'+ret.data.shop_url+'" class="btn btn-success mr10" target="_blank"><i class="fa fa-check"></i> 浏览店铺</a><button class="btn btn-warning" data-dismiss="modal"><i class="fa fa-times"></i> 继续装修</button></div>';
					$('.modal-body').html(html);
				}else talert(ret);
			});
		});
	});
}


//导入模板库
function import_tpl(){
	vmodal({
		title:'导入',
		msg:'<h4>确定要将该装修成果导入模板库吗？</h4>',
		class:'text-center',
		confirm:1,
		width:'650px',
		backdrop:false,
	},function(){
		$('.modal-ok').click(function(){
			api({
				data:{apiurl:'/Make/api/method/import_tpl',is_openid:1}
			},function(ret){
				if(ret.code==1){
					var html='<div class="text-center"><h4>导入成功！</h4></div>';
					$('.modal-body').html(html);
				}else talert(ret);
			});
		});
	});
}

/**图片下拉选择*/
function form_select_images(obj){
	var d=obj.data();
	$(d.tag).html(obj.html());
	$(d.field).val(d.value);
}

/** in_array */
function in_array(val,arr){
	var k=-1;
	for(i=0;i<arr.length;i++){
		if(val == arr[i]){
			return i;
		}
	}		
	return k;
};

//file表单域名上传图片
function upload_file(obj){
	var tag=obj.closest('.input-group');
	var data = obj.data();

	$('#form-upload')[0].reset();
	if(data.width != undefined) $('#form-upload #width').val(data.width);
	if(data.height != undefined) $('#form-upload #height').val(data.height);
	$('#form-upload #field').val(data.name);
	$('#form-upload #imageData').click();
	
	$('#form-upload #imageData').unbind().change(function(){
			if($(this).val()!=''){
				/*
				var html='<li class="text-center">';
					html+='	<div class="li-img-box">';
					html+='	<img src="/Public/images/wap_loading.gif">';												
					html+='	</div>';												
					html+='</li>';
				
				
				tag.find('.images-select-box').html(html);
				*/				
				
				ajax_post_form({
					formid:'#form-upload',
				},function(ret){
					valert(ret);
					if(ret.code==1){
						tag.find('input[name="'+data.name+'"]').val(ret.url);
						// 替换内容
						var html='<li class="text-center" data-path="'+ret.url+'">';
							html+='	<div class="li-img-box">';
							html+='	<a class="image-zoom" href="'+ret.url+'" title="图片"><img src="'+ret.url+'?imageMogr2/thumbnail/!150x150r"></a>';												
							html+='	</div>';
							html+='<div class="delete-images" onclick="delete_file_item($(this),\''+data.name+'\')"><div class="selected-icon"><i class="fa fa-times"></i></div></div>';
							html+='</li>';
						tag.find('.images-select-box').html(html);
						
						image_zoom({obj:tag});
					}
				});	
			}
	});	

}

function delete_file_item(obj,name){	
	obj.closest('.input-group').find('input[name="'+name+'"]').val('');
	obj.parent('li').remove();
}