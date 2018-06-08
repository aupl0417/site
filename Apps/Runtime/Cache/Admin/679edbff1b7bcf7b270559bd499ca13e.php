<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE html>
<html lang="zh_cn"> 
<head>
	<meta charset="utf-8">
	<meta name="description" content="乐兑后台管理系统">
	<meta name="author" content="懒猫">
	<title>乐兑后台管理系统</title>
	
	<link rel="stylesheet" type="text/css" href="/Public/CSS/flatdream/js/bootstrap/dist/css/bootstrap.css" />
	<link rel="stylesheet" type="text/css" href="/Public/CSS/flatdream/js/jasny.bootstrap/extend/css/jasny-bootstrap.min.css" />
	<link rel="stylesheet" type="text/css" href="/Public/CSS/flatdream/fonts/font-awesome-4/css/font-awesome.min.css" />
	<link rel="stylesheet" type="text/css" href="/Public/CSS/flatdream/js/jquery.gritter/css/jquery.gritter.css" />
	<link rel="stylesheet" type="text/css" href="/Public/CSS/flatdream/js/jquery.nanoscroller/nanoscroller.css" />
	<link rel="stylesheet" type="text/css" href="/Public/CSS/flatdream/css/skin-red.css" />	
	<link rel="stylesheet" type="text/css" href="/Public/CSS/flatdream/js/jquery.icheck/skins/all.css" />
	<link rel="stylesheet" type="text/css" href="/Public/CSS/flatdream/js/jquery.magnific-popup/dist/magnific-popup.css" />
	<link rel="stylesheet" type="text/css" href="/Public/CSS/flatdream/js/bootstrap.switch/bootstrap-switch.min.css" />

	<link rel="stylesheet" type="text/css" href="/Public/Jquery/DateTimePicker/jquery.datetimepicker.css" />
	<link rel="stylesheet" type="text/css" href="/Public/Jquery/jquery-minicolors/jquery.minicolors.css" />

	<link rel="stylesheet" type="text/css" href="/Apps/Admin/View/default/Public/css/css.css" />


	<script type="text/javascript" src="/Public/Jquery/qiniu_ueditor/ueditor_mintoolbar.config.js"></script>
	<script type="text/javascript" src="/Public/Jquery/qiniu_ueditor/ueditor.all.min.js"></script>
	<script type="text/javascript" src="/Public/Jquery/qiniu_ueditor/lang/zh-cn/zh-cn.js"></script>

</head>
<body class="<?php echo (CONTROLLER_NAME); ?>_<?php echo (ACTION_NAME); echo ($tpl); ?>">
<div id="ajax_tips"></div>
	<div class="left-box active">
		<div class="logo"><a href="/" target="_top"><img src="/Apps/Admin/View/default/Public/images/logo.png" ></a></div>
		<div class="left-menu"></div>

	</div><!--/左侧菜单-->
	
	<div class="right-box">
		<!--主菜单-->
		<div class="top-box">
			<div class="l mleft">
			</div>
			<div class="r">
				<table class="no-border">
					<tbody class="no-border-x no-border-y">
					<tr>
						<td class=" pt20"></td>
						<td width="50"><img src="<?php echo myurl(C('admin.face'));?>" class="img-circle" width="40" height="40"></td>
						<td width="200" class="ft16"><?php echo C('admin.username');?>，<a href="/Login/logout" target="_top">退出管理</a></td>
					</tr>
					</tbody>
				</table>				
			</div>
		</div>
		
		<!--标签页-->
		<div class="nav-box">
			<ul class="nav-label">
				<li class="ttip toggle-menu" title="关闭左侧菜单"><i class="fa fa-chevron-left ft18"></i></li>
				<li class="ttip close-label" title="关闭所有标签"><i class="fa fa-times-circle ft18"></i></li>
			</ul>
		</div>
		
		<div class="views">

		</div>
	</div>

<div id="ajax-modal" class="modal fade" tabindex="-1">
	<div class="modal-dialog modal-lg">
		<div class="modal-content">
			<div class="modal-header no-border">
				<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
				<h4 class="modal-title">提示窗口</h4>
			</div>
			<div class="modal-body"></div>
			<div class="modal-footer">
				<div class="pull-left text-danger"><button type="button" class="btn btn-danger hide btn-submit">提交</button></div>
				<button type="button" class="btn btn-default" data-dismiss="modal">关闭</button>
			</div><!-- /.modal-footer -->
		</div><!-- /.modal-content .modal-no-shadow .modal-no-border .the-box .danger .full -->
	</div><!-- /.modal-dialog -->

</div>
		
<div id="ajax-modal2" class="modal fade" tabindex="-1">
	<div class="modal-dialog modal-lg">
		<div class="modal-content">
			<div class="modal-header no-border">
				<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
				<h4 class="modal-title">提示窗口</h4>
			</div>
			<div class="modal-body"></div>
			<div class="modal-footer">
				<div class="pull-left text-danger"></div>
				<button type="button" class="btn btn-default" data-dismiss="modal">关闭</button>
			</div><!-- /.modal-footer -->
		</div><!-- /.modal-content .modal-no-shadow .modal-no-border .the-box .danger .full -->
	</div><!-- /.modal-dialog -->

</div>
<div class="loading hide">
	<div class="loading-body text-center"><img src="/Apps/Admin/View/default/Public/images/loading.gif"></div>
</div>

<form enctype="multipart/form-data" id="form-upload" data-url="/Upload/upload_save" class="hide">
	<input id="imageData" name="imageData" type="file" value="" />
	<input type="hidden" id="width" name="width" value="">
	<input type="hidden" id="height" name="height" value="">
	<input type="hidden" id="type" name="type" value="">	
	<input type="hidden" id="field" name="field" value="">
	<input type="hidden" id="field1" name="field1" value="">
	<input type="hidden" id="field2" name="field2" value="">
</form>

	<script type="text/javascript" src="/Public/Jquery/jquery-2.1.1.min.js"></script>	
	<script type="text/javascript" src="/Public/CSS/flatdream/js/jquery.nanoscroller/jquery.nanoscroller.js"></script>
	<script type="text/javascript" src="/Public/CSS/flatdream/js/jquery.gritter/js/jquery.gritter.js"></script>
	<script type="text/javascript" src="/Public/CSS/flatdream/js/jquery.icheck/icheck.min.js"></script>	
	<script type="text/javascript" src="/Public/CSS/flatdream/js/bootstrap/dist/js/bootstrap.min.js"></script>		
	<script type="text/javascript" src="/Public/CSS/flatdream/js/jquery.niftymodals/js/jquery.modalEffects.js"></script>	
	<script type="text/javascript" src="/Public/CSS/flatdream/js/jquery.magnific-popup/dist/jquery.magnific-popup.min.js"></script>
	<script type="text/javascript" src="/Public/CSS/flatdream/js/bootstrap.switch/bootstrap-switch.js"></script>
	<script type="text/javascript" src="/Public/CSS/flatdream/js/jasny.bootstrap/extend/js/jasny-bootstrap.min.js"></script>
	
	
	<script type="text/javascript" src="/Public/Jquery/jquery-ui-1.10.3.custom/development-bundle/ui/jquery.ui.core.js"></script>
	<script type="text/javascript" src="/Public/Jquery/jquery-ui-1.10.3.custom/development-bundle/ui/jquery.ui.widget.js"></script>
	<script type="text/javascript" src="/Public/Jquery/jquery-ui-1.10.3.custom/development-bundle/ui/jquery.ui.mouse.js"></script>
	<script type="text/javascript" src="/Public/Jquery/jquery-ui-1.10.3.custom/development-bundle/ui/jquery.ui.sortable.js"></script>
	
	<script type="text/javascript" src="/Public/Jquery/jquery-validation/js/jquery.validate.min.js"></script>	
	<script type="text/javascript" src="/Public/Jquery/artTemplate/dist/template.js"></script>
	<script type="text/javascript" src="/Public/Jquery/jquery.form.js"></script>
	<script type="text/javascript" src="/Public/Apps/admin.js"></script>	
	<script type="text/javascript" src="/Public/Apps/Admin/global.js"></script>
	<script src="/Public/Jquery/DateTimePicker/jquery.datetimepicker.js"></script>

	<script type="text/javascript" src="/Public/Jquery/edit_area/edit_area_full.js"></script>
	<script type="text/javascript" src="/Public/Jquery/jquery-minicolors/jquery.minicolors.min.js"></script> 

	

<script id="topmenu" type="text/html">
	<ul>
		{{each menu as val i}}
			<li onclick="smenu({{i}})" class="{{i==0?'active':''}}" data-id="{{val.id}}" data-index="{{i}}">{{val.name}}</li>
		{{/each}}
	</ul>
</script>

<script id="smenu" type="text/html">
	{{each menu.dlist as val i}}
	<div class="smenu-us">
		<div class="smenu-first">
			{{if val.url}}
				{{if val.target!=''}}
					<a href="{{val.url}}" target="{{val.target}}">{{val.name}}</a>
				{{else}}
				<a href="javascript:void(0)" onclick="">{{val.name}}</a>
				{{/if}}
			{{else}}
				{{val.name}}
			{{/if}}
		</div>
		<ul>
			{{each val.dlist as ds j}}
				<li id="{{ds.id}}" data-rootid="{{val.sid}}">
					{{if ds.url}}
						{{if ds.target!=''}}
							<a href="{{ds.url}}" target="{{ds.target}}">{{ds.name}}</a>
						{{else}}
						<a href="javascript:void(0)" onclick="openWin({name:'{{ds.id}}',title:'{{ds.name}}',url:'{{ds.url}}',rootid:'{{val.sid}}'})">{{ds.name}}</a>
						{{/if}}
					{{else}}
						{{ds.name}}
					{{/if}}				
				
				</li>
			{{/each}}
		</ul>
	</div>
	<div class="clearfix"></div>
	{{/each}}
</script>

<script>
var menu=<?php echo (json_encode($list)); ?>;

$(document).ready(function(){

	var data={menu:menu};
	var html = template('topmenu', data);
	$('.top-box .mleft').html(html);	
	//openWin({name:'main',title:'首页',url:'/Index/main'});
	smenu(0);

	$('.close-label').click(function(){
		$('.nav-label li').each(function(index){
			if(index>2){
				var d=$(this).data();
				closeWin({name:d.page});
			}
		});
	});
	
	$('.toggle-menu').click(function(){
		var obj=$(this);
		$('.left-box').toggleClass('active');
		if($('.left-box').hasClass('active')){
			$('.right-box').css({'margin-left':'200px'});
			obj.html('<i class="fa fa-chevron-left ft18"></i>');
		}else{
			$('.right-box').css({'margin-left':'0'});
			obj.html('<i class="fa fa-chevron-right ft18"></i>');
		}
	});
});	

	function smenu(i){
		var lmenu={menu:menu[i]};
		
		var html = template('smenu', lmenu);
		$('.left-menu').html(html);	
		if(menu[i].dlist[0].dlist[0].id) openWin({name:menu[i].dlist[0].dlist[0].id,title:menu[i].dlist[0].dlist[0].name,url:menu[i].dlist[0].dlist[0].url,rootid:menu[i].id})
		//alert(html);
		
		//$('.mleft li').eq(i).addClass('active').siblings().removeClass('active');
	}
	
	function smenu2(i){
		var lmenu={menu:menu[i]};		
		var html = template('smenu', lmenu);
		$('.left-menu').html(html);		
	}

//定时刷新在线雇员，60秒刷一次

window.setInterval(function(){
	$.ajax({  
		type: 'get',
		url:'/Index/online_updatetime',
		dataType: 'json',
		success:function(ret){
			if(ret.code == 0){
				alert(ret.msg);
				top.location.href='/Login';
			}
		}
	});
},60*1000);

</script>
</body>
</html>