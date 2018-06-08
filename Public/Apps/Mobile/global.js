$(document).ready(function(){
	
});

var loading='<div class="view-loading"><div class="loading-body"><img src="/Public/images/wap_loading.gif"></div></div>';
//var window_h=$(window).height();
//var view_h=window_h;

function tourl(param){
	var section=param.section?param.section:'#view';
	var tag=section+' article .scroller';
	
	$(tag).html('');
	$(section+' header .title').html(param.title);
	$('section.active').removeClass('active');
	$(section).addClass('active');		
	loadurl({
		tag:tag,
		url:param.url
	});
}

function toback(){
	$('section.active').removeClass('active');
	$('#main').addClass('active');
}

function qrcode(){
	if($('.qrcode').size()>0){
		var url=$('.qrcode').data();
		$('.qrcode').qrcode({
			text:url.url,
			width:200,
			height:200,
		});
	}
}


function loadurl(param){
	$(param.tag).html(loading).load(param.url);
}

function fixPage(){
	var header_h=$('section.active header').height();
	var footer_h=0;
	if($('section.active footer').size()>0) footer_h=parseInt($('section.active footer').height())+1;
	var window_h=$(window).height();
	$('section.active article .scroller').height(window_h-header_h-footer_h);
}



function openWin(param,callback){
	if($('#pages section').size()>0) $('#pages section').removeClass('active');
	var tag='#'+param.name;
	if($('#pages section[id="'+param.name+'"]').size()>0){ //已存则激活
		$('#pages section[id="'+param.name+'"]').addClass('active');
		var d=$('#pages section[id="'+param.name+'"]').data();
		if(d.url!=param.url){
			$('#pages section[id="'+param.name+'"]').data('url',param.url);
			loadurl({
				tag:tag,
				url:param.url
			});	
		}
	}else{
		var html='<section id="'+param.name+'" class="active" data-url="'+param.url+'"></section>';
		$('#pages').append(html);
		loadurl({
			tag:tag,
			url:param.url
		});
	}
	if(param.script) eval(param.script);
	if(callback) callback();
}

function refWin(param){
	var d=$('section.active').data();
	loadurl({
		tag:'section.active',
		url:d.url
	});	
}

function closeWin(param){
	if(param.name){
		if($('#'+param.name).prev().size()>0){
			$('#'+param.name).prev().addClass('active');
			$('#'+param.name).remove();
			if(param.script) eval(param.script);
		}else{
			gourl({url:'/'});
		}		
	}else{
		if(param.closest('section').prev().size()>0){
			param.closest('section').prev().addClass('active');
			param.closest('section').remove();
			if(param.script) eval(param.script);
		}else{
			gourl({url:'/'});
		}
	}
	loadicon(false);
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


function loadicon(t){
	if(t==true){
		$('.loading').removeClass('hide');
	}else{
		$('.loading').addClass('hide');		
	}
}



function iCheckClass(param){
	/** BEGIN ICHECK **/
	/** Minimal Skins **/
	var tag='';
	if(param) tag=param+' ';
	
	//alert(tag);

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


function province(param){
	var province=param.province?param.province:$(param.tag.p).attr('data-value');

	if(province) {
		var url=sub_domain.rest+'/City/city/sid/'+province;

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
		var url=sub_domain.rest+'/City/city/sid/'+$(this).val();
		ajax_get({
			url:url,
			script:'city_select(ret,param.option)',
			option:param,
		});
	});

}

function city_select(ret,param){
	var city=param.city?param.city:$(param.tag.c).attr('data-value');
	
	var html='<option value="">请选择城市</option>';	
	for(var i=0;i<ret.length;i++){
		html+='<option value="'+ret[i].id+'" '+(ret[i].id==city?'selected':'')+'>'+ret[i].name+'</option>';
	}
	$(param.tag.c).html(html);

}

function city(param){
	var city=param.city?param.city:$(param.tag.c).attr('data-value');
	if(city) {
		var url=sub_domain.rest+'/City/city/sid/'+city;
		ajax_get({
			url:url,
			script:'area_select(ret,param.option)',
			option:param,
		});	
		
	}	
	
	$(param.tag.c).change(function(){
		//alert($(this).val());
		var url=sub_domain.rest+'/City/city/sid/'+$(this).val();
		ajax_get({
			url:url,
			script:'area_select(ret,param.option)',
			option:param,
		});
	});
}
function area_select(ret,param){
	var area=param.area?param.area:$(param.tag.a).attr('data-value');
	
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
	//alert(param.url);

	var options = {
		beforeSubmit:param.beforeSubmit,
		target: '#ajax_tips',
		url: param.url,
		type: 'POST',
		success:function (ret) {
			//alert(ret);
			if(param.script) eval(param.script);
			if(success_callback) success_callback(ret);
			loadicon(false);
		},
		error:function(e){
			valert({msg:'读取失败，请检查网络是否正常！'});
			loadicon(false);
		}
	};
	form.ajaxSubmit(options);	
}

function ajax_post(param,success_callback){
	loadicon(true);
	$.ajax({  
		type: 'post', 
		url: param.url,
		data:param.data,
		dataType:'json',
		success: function (ret) {
			if(param.script) eval(param.script);
			if(success_callback) success_callback(ret);
			loadicon(false);
		},
		error:function(e){
			valert({msg:'读取失败，请检查网络是否正常！'});
			loadicon(false);
		}
	}); 	

}

function ajax_get(param,success_callback){
	if(param.loadicon==false) loadicon(false);
	else loadicon(true);

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
			valert({msg:'读取失败，请检查网络是否正常！'});
			loadicon(false);
		}
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
				},success_callback);				
			}

			return false;

		}
	});	
	
}


function valert(param){
	if(param.status==1 || param.code==1) param.status=='success';
	else if(param.status==0 || param.code==0) param.status=='warning';
	
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


//模态框
function vmodal(param,callback){
        var height=$(window).height()-250;
	var confirm='<div class="p10 text-center"><button class="btn btn-success btn-warning btn-trans modal-ok mr10"><i class="fa fa-check"></i> 确定</button><button class="btn btn-rad btn-warning btn-trans modal-cancel" data-dismiss="modal"><i class="fa fa-times"></i> 取消</button></div>';
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


//显示广告
function ad_display(data){
	$.each(data,function(i,val){
	
		var obj=$('[data-positionid="'+val.id+'"]');
		if(obj.size()>0){
			var d=obj.data();
			var tid=val.type==1?'ad_slide':'ad_images';
			if(d.tid) var tid=d.tid;


			var html = template(tid, {data:val.dlist});
			//alert(html);
			obj.html(html);		

			if(val.type==1){
				$(".ad_slide_"+val.id).slide({ 
					titCell:".num ul" , 
					mainCell:".ad_slide_pic" , 
					effect:"left",
					prevCell:".prev_pic",
					nextCell:".next_pic",
					autoPlay:true, 
					interTime:3000, 
					delayTime:500,
					autoPage:true 
				});				
			}	

		}
	});
		
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
			$(param.tag+prevTag+'.itabs-content .itabs-pane').removeClass('active').eq(index).addClass('active');
			if(script) script(obj);			
		});
	}
}

//分页
function pagelist(param,callback){
	var d=$(param.tag).data();	//存放内容的标签
	if(d.p==undefined || d.p=='') var p=1;
	else p=parseInt(d.p)+1;
	
	if(d.allpage!='' && p>parseInt(d.allpage)){
		valert({status:'warning',msg:'已是最后一页！'});
		return;
	}	
	$(param.tag).data('p',p);
	

	var data=param.data;
	data.p=p;	
	
	ajax_post({
		url:param.url,
		data:data,
	},function(ret){
		if(ret.data!=undefined) $(param.tag).data('allpage',ret.data.allpage);
		if(ret.allpage!=undefined) $(param.tag).data('allpage',ret.allpage);
		
		if(callback) callback(ret);  //自定义分页内容输出显示
	});
}



function delSpace(txt) { //清除字符串中所有的空白字符
	if (txt == null) {
		return "";
	} else {
		txt = txt.toString();
		txt = txt.replace(/\s{1,}/, "");
		return txt;
	}
}
function isQQ(txt) { //是否为QQ号码，即5-9位数字
	if (txt == null || txt == "") {
		return false;
	} else {
		var regex = /[1-9]{1}[0-9]{4,8}/;
		return regex.test(txt);
	}
}
function isPhoneNum(txt) { //检测是否为电话号码(固定电话或手机)
	return isPhone(txt) || isMobile(txt);
}
function isPhone(txt) { //检测是否为固定电话号码
	if (txt == null || txt == "") {
		return false;
	} else {
		var regex = /[0-9]{1}[0-9]{2,3}-[1-9]{1}[0-9]{5,8}/;
		return regex.test(txt);
	}
}
function isMobile(txt) { //检测是否为手机号码
	if (txt == null || txt == "") {
		return false;
	} else {
		var regex = /1[0-9]{10}/;
		return regex.test(txt);
	}
}

function isEmail(txt) { //是否为电子邮件地址:xxx@xxx.xxx
	if (txt == null || txt == "") {
		return false;
	} else {
		var regex = /(.){1,}@(.){1,}\.(.){1,}/;
		return regex.test(txt);
	}
}
function isNumeric(txt) { //是否为数字
	if (txt == null || txt == "") {
		return false;
	} else {
		txt = delSpace(txt);
		return ! isNaN(parseFloat(txt));
	}
}
function isInteger(txt) { // 是否为整数
	if (txt == null || txt == "") {
		return false;
	} else {
		txt = delSpace(txt);
		var regex=/^\d+$/;
		//return ! isNaN(parseInt(txt));
		return regex.test(txt);
	}
}
function isPositiveNumber(txt) { //是否为正数
	if (txt == null || txt == "") {
		return false;
	} else {
		txt = delSpace(txt);
		if (isNaN(parseInt(txt))) {
			return false;
		} else {
			return (parseInt(txt) > 0);
		}
	}
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

