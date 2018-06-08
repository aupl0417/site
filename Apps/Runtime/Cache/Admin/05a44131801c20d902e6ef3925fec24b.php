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

<div id="cl-wrapper" class="login-container">

	<div class="middle-login">
		<div class="block-flat">
			<div class="header">							
				<h3 class="text-center">乐兑</h3>
			</div>
				<div class="block content ">

                    <ul class="nav nav-tabs border-d">
                        <li class="active">
                                <a href="#tab3" data-toggle="tab">密码登录</a>
                            </li>
							<li class="">
                                <a href="#tab100827592" data-toggle="tab" onclick="saoma()">扫码登录</a>
                            </li>
					</ul>

                    <div class="tab-content tab-content-noborder" style="padding:0px">

                        <div class="tab-pane active " id="tab3">
							<form style="margin-bottom: 0px !important;" class="form-horizontal vform" id="form-login" name="form-login">
								<div class="content">
									
									<?php echo W('Widget/buildform',array(array( 'field' =>array( array( 'formtype' =>'text', 'name' =>'username', 'is_need' =>true, 'left' =>'<span class="input-group-addon"><i class="fa fa-user"></i></span>', 'col' =>array('','col-xs-12'), 'placeholder' =>'请输入账号', ), array( 'formtype' =>'password', 'name' =>'password', 'is_need' =>true, 'left' =>'<span class="input-group-addon"><i class="fa fa-lock"></i></span>', 'col' =>array('','col-xs-12'), 'placeholder' =>'请输入密码', ), array( 'formtype' =>'vcode', 'name' =>'vcode', 'is_need' =>true, 'left' =>'<span class="input-group-addon"><i class="fa fa-lock"></i></span>', 'col' =>array('','col-xs-12'), 'placeholder' =>'请输入验证码', ), ), )));?>				
								
									

										
								</div>
								<div class="foot">
									<button type="submit" class="btn btn-lg btn-block btn-primary btn-rad btn-trans m0"><i class="fa fa-key"></i> 登录

								</div>
							</form>
                        </div>
							
						<div class="tab-pane  pt20" id="tab100827592">
							<div class="text-center" >
								<img src="/Login/qrcode" class="img-responsive" style="margin: 0 auto;">
							<div>
							<h5 class="text-muted">请使用乐兑work端扫码登录</h5>
						</div>

                     </div>
                </div>

         </div>
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

	
<script type="text/javascript" src="/Public/Jquery/backstretch/jquery.backstretch.min.js"></script>
<script type="text/javascript" src="/Public/Apps/Admin/Login/rand-bg.js"></script>
<script>

checkform({
	formid:'#form-login',
	url:'/Login/check_login',
	rules:{
		username: {
			required: !0,
		},	
		password: {
			required: !0,
			maxlength:18,
			minlength:6
		},
        vcode: {
            required: true,
			rangelength:[4,5],
        }			

	},
	messages:{
		username:{
			required:'请输入管理员账号'
		},
		password: {
			required: "请输入新密码",
			maxlength:"密码长度不能超过18个字符",
			minlength:"密码长度至少6个字符以上"
		},
        vcode: {
            required: '请输入验证码！',
			rangelength:'验证码长度为4~5位！',
        }			

	},
	script:'callback(ret)'
});

function callback(ret){
	valert({status:ret.status,msg:ret.msg});
	if(ret.status=='success'){
		location.href='/';
	}
}

	function saoma(){
		$().ready(function(){
			setInterval("mylogin()",3000);//1000为1秒钟,检查是否登录
			setInterval("mytime()",10000);//1000为1秒钟,替换二维码
		});
	}
	
	function mytime(){
		$(".img-responsive").attr('src','/Login/qrcode');
	}
		
	function mylogin(){
		$.ajax({
			type: "POST",
			url: "/Login/checked_login",
			success: function(data){
				if(data.status=='success'){
					location.href='/';
				}
			}
		});
	}
</script>
</body>
</html>