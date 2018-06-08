<?php if (!defined('THINK_PATH')) exit();?><!doctype html>
<html>
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">	
	<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
	<title><?php echo (C("seo.title")); ?></title>
	<meta name="keywords" content="<?php echo (C("seo.keywords")); ?>">
	<meta name="description" content="<?php echo (C("seo.description")); ?>">	
	<meta name="author" content="Mercury">
	<!-- Bootstrap core CSS -->
	<link rel="stylesheet" type="text/css" href="/Public/new_design/flatdream/js/bootstrap/dist/css/bootstrap.css">
	<link rel="stylesheet" type="text/css" href="/Public/new_design/flatdream/js/jquery.gritter/css/jquery.gritter.css" />
	<link rel="stylesheet" type="text/css" href="/Public/new_design/flatdream/fonts/font-awesome-4/css/font-awesome.min.css">
	<link href="/Public/CSS/flatdream/js/jquery.magnific-popup/dist/magnific-popup.css" rel="stylesheet" />
	<link rel="stylesheet" type="text/css" href="/Public/Jquery/toastr/toastr.css" />
	
	<!-- HTML5 shim and Respond.js IE8 support of HTML5 elements and media queries -->
	<!--[if lt IE 9]>
	  <script src="../assets/js/html5shiv.js"></script>
	  <script src="../assets/js/respond.min.js"></script>
	<![endif]-->
	<link rel="stylesheet" type="text/css" href="/Public/new_design/flatdream/js/jquery.nanoscroller/nanoscroller.css" />
    <link rel="stylesheet" type="text/css" href="/Public/new_design/flatdream/js/jquery.codemirror/lib/codemirror.css">
	<link rel="stylesheet" type="text/css" href="/Public/new_design/flatdream/js/jquery.codemirror/theme/ambiance.css">
	<link rel="stylesheet" type="text/css" href="/Public/new_design/flatdream/js/jquery.vectormaps/jquery-jvectormap-1.2.2.css"  media="screen"/>

	<link rel="stylesheet" type="text/css" href="/Public/new_design/flatdream/css/style.css"/>
	<!-- 定义样式 -->
	<link rel="stylesheet" type="text/css" href="/Public/new_design/css/common.css" >
	<link rel="stylesheet" type="text/css" href="/Public/new_design/css/css.css">
	<link rel="stylesheet" type="text/css" href="/Public/new_design/css/other.css">
	<link rel="stylesheet" type="text/css" href="/Public/new_design/css/style.css">
	<script src="/Public/new_design/flatdream/js/jquery.js"></script>
	<script type="text/javascript" src="/Public/Jquery/jquery.lazyload.min.js"></script>
	
	
	<script>
		var ACTION	=	'<?php echo enCryptRestUri("/Login/index");?>';
	</script>
</head>
<body style="overflow-x:hidden">
	<div id="ajax_tips"></div>
	<!--[if lt IE 10]>
	<div class="alert alert-warning md0 dn browser-low-tip">
		<button type="button" class="close" data-dismiss="alert" aria-hidden="true" onclick="close_browser_low_tip(this)">&times;</button>
		<i class="fa fa-warning sign"></i><strong>浏览器版本过低！</strong> 请使用谷歌浏览器(Chrome)、火狐浏览器(FireFox)、360浏览器、搜狗浏览器、腾讯浏览器(TT)、IE10以上、苹果浏览器(Safari)等浏览器浏览本站才可体验最佳效果。
	</div>
	<script>		
		if(!$.cookie('close_browser_low_tip')){
			$(".browser-low-tip").show();
		}
		function close_browser_low_tip(obj){
			if(confirm("不再弹出？")){
				var date = new Date();
				date.setTime(date.getTime() + (86400 * 1000));
				$.cookie('close_browser_low_tip','1', { path: '/', expires: date });  
				$(obj).parent().remove();
			}
		}
	</script>
	<![endif]-->
<header>		
	<div class="container">
		<div class="row mg0">
			<div class="col-xs-2 pd0 text-right"><a href="<?php echo DM('www');?>"><img src="/Public/new_design/images/dt_logo.png"></a></div>
			<div class="col-xs-2 pr0 pt20"><div class="pt5 pl15 mt10 fs20 strong solid_l">欢迎登录</div></div>
		</div>
	</div>		
</header>
<div class="centent_body">
		<div style="border-bottom:2px solid #fbc819">
			<div class="container text-right pt2"></div>
		</div>
		<div class="login_img">
			<div class="container pt40 pb40" >
				<div class="row">
					<div class="col-xs-8 text-center">
						<div class="pt40 pb40">
							<a href="javascript:;"><img src="/Public/new_design/images/banner-img1.png" alt=""></a>
						</div>
					</div>
					<div class="col-xs-4 pl0">
						<div class="login-wbox-opactiy">
						<div class="pd10 bg_white" id="code-icon-box">
							<div class="pd10">
								<form id='formadd' class="form-horizontal" data-url="<?php echo U('/Login/login');?>">
									<div class="text-center fs18 mb30 mt10 re"><strong>用户登录</strong></div>
									<!-- <div class="code-icon "><a href="javascript:void(0)" id="code-icon-input"><img src="/Public/new_design/images/code-img-icon.png" alt="" class=""></a></div> -->
									<div class="form-group mb30">
										<div class="col-xs-12">
											<div class="re">
												<span class="user-input-icon login-input-icon"></span>
												<input name="username" type="text" class="form-control pl45 h40 fs14" placeholder="请输入用户名">
											</div>
										</div>
									</div>
									<div class="form-group mb30">
										<div class="col-xs-12">
											<div class="re">
												<span class="pass-input-icon login-input-icon"></span>
												<input type="password" name="password" class="form-control pl45 h40 fs14" placeholder="请输入密码">
											</div>
										</div>
									</div>
									
									<div class="form-group mb30 dn">
										<div class="col-xs-12">
											<div class="re">
												<span class="pass-input-icon login-input-icon"></span>
												<div class="row mgt0">
													<div class="col-xs-6 pr10">
														<div class="">
															<input type="text" id="vcode" class="form-control pl45 h40 fs14" name="vcode" placeholder="图形验证码" value="" maxlength="5">
														</div>
													</div>
													<div class="col-xs-6 pl0">
														<a href="javascript:void(0)" data-url="<?php echo U('/verify/index', ['h' => 40]);?>" class="verify" title="点击图片更换验证码">
															<img src="<?php echo U('/verify/index', ['h' => 40]);?>" alt="验证码" class="verifyimg" style="height:40px;">
														</a>
													</div>
												</div>
											</div>
										</div>
									</div>
									
									<div class="ptb10 clearfix">
										<label class="square mr10 fl"><input name="remember" value="1" type="checkbox"><span></span></label>两周内自动登录
										<a href="<?php echo U('/forget');?>" class="pull-right hide">忘记密码？</a>
									</div>
									<div class="">
										<button class="btn btn-primary btn-block h40 line24 mg0">登录</button>
									</div>
									<div class="mtb20">
										<a href="<?php echo U('/register/supplier');?>" class="btn btn-default  btn-block btn-trans h40 line24 mg0">供货商注册</a>
									</div>
								</form>
							</div>
						</div>
						<!--正常登录-->
						<div class="pd10 bg_white" style="display: none" id="com-icon-box">
							<div class="pd10">
								<form class="form-horizontal">
									<div class="text-center fs18 mb50 mt10 re"><strong>手机扫码，安全登录</strong></div>
									<div class="code-icon"><a href="javascript:void(0)" id="com-icon-input"><img src="/Public/new_design/images/com-img-icon.png" alt=""></a></div>
									<div class="mt20 mb50 text-center ">
										<div class=" text-center">
											<img src="/Public/new_design/images/code-img2.jpg" alt="" width="200" height="200" class="solid-all">
										</div>
									</div>
									<div class="ptb10 text-center">
										请使用乐兑客户端扫描二维码登录
									</div>
								</form>
							</div>
						</div>
						<!--二维码登录-->
					</div>				
					</div>
				</div>
			</div>	
		</div>
	</div>
	<footer class="solid_t bg_f5">	
	<div class="container mt30 mb30">
		<div class="row mg0">
			<div class="col-xs-3 pl40 ">
				<img class="fl mr15" src="/Public/new_design/images/footer_1.png">
				<p class="mb0 mt10 fs20 line30"><strong class="">正品保证</strong></p>
				<p class="mb0 fs16 text_77">正品行货 放心选购</p>
			</div>
			<div class="col-xs-3 pl40 ">
				<img class="fl mr15" src="/Public/new_design/images/footer_2.png">
				<p class="mb0 mt10 fs20 line30"><strong class="">消费奖励</strong></p>
				<p class="mb0 fs16 text_77">购物奖励积分</p>
			</div>
			<div class="col-xs-3 pl40 ">
				<img class="fl mr15" src="/Public/new_design/images/footer_3.png">
				<p class="mb0 mt10 fs20 line30"><strong class="">售后无忧</strong></p>
				<p class="mb0 fs16 text_77">7天无理由退款</p>
			</div>
			<div class="col-xs-3 pl40 ">
				<img class="fl mr15" src="/Public/new_design/images/footer_4.png">
				<p class="mb0 mt10 fs20 line30"><strong class="">帮助中心</strong></p>
				<p class="mb0 fs16 text_77">您的购物指南</p>
			</div>
		</div>
	</div>

	<div class="bg_f0">
		<div class="container pt20 pb20">
			<div class="row mg0">
				<div class="col-xs-3 pl40">
					<h5><strong>新手指南</strong></h5>
					<a href="<?php echo DM('faq');?>/view.html?id=866" target="_blank"><p class="mb0 line22">注册流程</p></a>
					<a href="<?php echo DM('faq');?>/view.html?id=868" target="_blank"><p class="mb0 line22">登录流程</p></a>
					<a href="<?php echo DM('faq');?>/view.html?id=882" target="_blank"><p class="mb0 line22">商品查找</p></a>
					<a href="<?php echo DM('faq');?>/view.html?id=953" target="_blank"><p class="mb0 line22">购物流程</p></a>
				</div>
				<div class="col-xs-3 pl40">
					<h5><strong>会员相关</strong></h5>
					<a href="<?php echo DM('faq');?>/view.html?id=884" target="_blank"><p class="mb0 line22">积分获取</p></a>
					<a href="<?php echo DM('faq');?>/view.html?id=826" target="_blank"><p class="mb0 line22">忘记密码</p></a>
					<a href="<?php echo DM('faq');?>/view.html?id=887" target="_blank"><p class="mb0 line22">后台管理</p></a>
					<a href="<?php echo DM('faq');?>/view.html?id=890" target="_blank"><p class="mb0 line22">商家入驻</p></a>
					<a href="<?php echo DM('faq');?>/view.html?id=893" target="_blank"><p class="mb0 line22">入驻流程</p></a>
				</div>
				<div class="col-xs-3 pl40">
					<h5><strong>支付相关</strong></h5>
					<a href="<?php echo DM('faq');?>/view.html?id=961" target="_blank"><p class="mb0 line22">余额充值 </p></a>
					<a href="<?php echo DM('faq');?>/view.html?id=958" target="_blank"><p class="mb0 line22">金积分支付 </p></a>
					<a href="<?php echo DM('faq');?>/view.html?id=958" target="_blank"><p class="mb0 line22">银积分支付 </p></a>
					<a href="<?php echo DM('faq');?>/view.html?id=958" target="_blank"><p class="mb0 line22">支付宝支付 </p></a>
					<a href="<?php echo DM('faq');?>/view.html?id=958" target="_blank"><p class="mb0 line22">余额支付 </p></a>
				</div>
				<div class="col-xs-3 pl40">
					<h5><strong>物流配送</strong></h5>
					<a href="<?php echo DM('faq');?>/view.html?id=896" target="_blank"><p class="mb0 line22">配送方式 </p></a>
					<a href="<?php echo DM('faq');?>/view.html?id=898" target="_blank"><p class="mb0 line22">配送范围 </p></a>
					<a href="<?php echo DM('faq');?>/view.html?id=901" target="_blank"><p class="mb0 line22">收货验货 </p></a>
				</div>
				<div class="col-xs-4 hide">
					<div class="row">
						<div class="col-xs-6">
							<h5><strong>售后服务</strong></h5>
							<a href="<?php echo DM('faq');?>/view.html?id=908" target="_blank"><p class="mb0 line22">退换货政策</p></a>
							<a href="<?php echo DM('faq');?>/view.html?id=910" target="_blank"><p class="mb0 line22">退换货费用</p></a>
							<a href="<?php echo DM('faq');?>/view.html?id=911" target="_blank"><p class="mb0 line22">退换货申请</p></a>
							<a href="<?php echo DM('faq');?>/view.html?id=914" target="_blank"><p class="mb0 line22">退换货流程</p></a>
						</div>
						<div class="col-xs-6 pt10 text-center">
							<img width="102" src="/Public/images/download_app.png">
							<p class="mb0 text-center line24">APP购物更优惠</p>								
						</div>

					</div>
				</div>
			</div>
		</div>
	</div>
	<p class="text-center bg_black text_white mb0 line50">
	<?php echo C('cfg.site')['copyright'];?> ICP备案：<?php echo C('cfg.site')['icp'];?> &nbsp;&nbsp;ICP证号：<?php echo C('cfg.site')['icp_code'];?> &nbsp;&nbsp;
	<?php if(session('admin.id') > 0): ?><a class="text-center bg_black text_red mb0 line50" href="javascript:;" onclick="seoSet('<?php echo MODULE_NAME.','.CONTROLLER_NAME.','.ACTION_NAME; ?>')">SEO设置</a><?php endif; ?>
	</p>
</footer>


<!--<script src="<?php echo DM('tongji');?>/ShopCollect/index/r/<?php echo think_encrypt($_SERVER['HTTP_REFERER'],C('CRYPT_PREFIX'));?>/s/<?php echo enCryptRestUri($_SERVER['SERVER_NAME']);?>/u/<?php echo enCryptRestUri($_SERVER['REQUEST_URI']);?>'"></script>-->
<script>


function seoSet(url){
	vmodal({
		title:'设置此页面SEO',
		iframe:'<?php echo DM("www");?>/index/seoSet/url/' + url,
		width:'900px',
	});
}








</script>
	
<!--[if IE 8]>
<script src="/Public/CSS/proui/js/helpers/excanvas.min.js"></script>
<![endif]-->

<?php echo W('Common/Script/js');?>
<script type="text/javascript">
$(document).ready(function() {
    $("img").lazyload({
         placeholder : "/Public/images/nopic.png", //加载图片前的占位图片
         effect      : "fadeIn", //加载图片使用的效果(淡入)
         skip_invisible : false,
    });
});

function ajax_topbar(){
	if($('.ajax_topbar').size()>0){
		ajax_get({
			url:'/Topbar/topbar',
		},function(ret){
			if($('.ajax_topbar').size()>0) $('.ajax_topbar').html(ret.topbar);
			if($('.ajax_user').size()>0) $('.ajax_user').html(ret.user);
			if($('.ajax_cart_num').size()>0) $('.ajax_cart_num').html(ret.cart);
			
			if($('.ajax_search_near').size()>0){
				if(ret.keywords && ret.keywords!=''){
					var html='';
					$.each(ret.keywords,function(i,item){
						html +='<a class="btn btn-sm btn-trans plr20" href="<?php echo DM('s');?>/Index/index/keywords/'+item+'">'+item+'</a>';
					});
					/*
					for(i=0;i<ret.keywords.length;i++){
						if(i<10){
							html +='<a class="btn btn-sm btn-trans plr20" href="<?php echo DM('s');?>/Index/index/keywords/'+ret.keywords[i]+'">'+ret.keywords[i]+'</a>';
						}
					}
					*/
					$('.ajax_search_near').html(html);
				}else{
					$('.ajax_search_near').html('<div class="plr20">暂无搜索记录！</div>');
				}
			}
		});
	}
}



</script>
	<script>
		$(".verify").click(function() {
			var d = $(this).data();
			$('.verify img').attr('src',d.url + '?id=' +  + Math.random());
		})
		
		$(function() {
			checkform({
				formid:"#formadd",
				rules:{
					username:{
						required:true,
						rangelength:[6,20]
					},
					password:{
						required:true,
						rangelength:[6,20]
					},
					vcode:{
						required:true,
						rangelength:[4,5],
					},
				},
				messages:{
					username:{
						required:'用户名不能为空',
						rangelength:'用户名不能小于6位且不能大于20位',
					},
					password:{
						required:'密码不能为空',
						rangelength:'密码长度介于8~20个字符，区分大小写！',
					},
					vcode:{
						required:'验证码不能为空',
						rangelength:'验证码长度为4~5位！'
					},
				}
			}, function(ret) {
				talert({status:ret.code,msg:ret.msg});
				if(ret.code == 1) {
					gourl({url:'<?php echo DM("seller");?>'});
				} else {
					$("input[name='vcode']").closest('.form-group').removeClass('dn');
					var d = $('.verify').data();
					$('.verify img').attr('src',d.url + '?id=' +  + Math.random());
				}
			});
		})
		
		$(function(){
			$("#code-icon-input").click(function(){
				$("#com-icon-box").show();
				$("#code-icon-box").hide();
			});
		
			$("#com-icon-input").click(function(){
				$("#code-icon-box").show();
				$("#com-icon-box").hide();
			});
		
		});
		</script>
	</body>
</html>