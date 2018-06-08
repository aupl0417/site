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
		var ACTION	=	'<?php echo enCryptRestUri("/Setting/index");?>';
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
<link rel="stylesheet" type="text/css" href="/Public/new_design/css/lzx.css">
<header>
	<div class="bg_f1">
	<!-- 首页头部banner -->
	<?php if(MODULE_NAME == 'Home' && ACTION_NAME == 'index' && CONTROLLER_NAME == 'Index'): ?><!--<div id="index-top-banner" style="height: 100px; width: 100%; background: url('/Public/images/index-top-banner/2017newyear.jpg') center center">-->
			<!--<div class="container" style="text-align: right">-->
				<!--<a onclick="return $(this).closest('#index-top-banner').slideUp('slow');" title="关闭" href="javascript:;" class="fs16"><i class="fa fa-times-circle"></i> </a>-->
			<!--</div>-->
		<!--</div>--><?php endif; ?>
	<div class="container text_77 line30">您好!
		<span class="ajax_topbar">
		<?php if(in_array((MODULE_NAME), explode(',',"Home,Faq,Search,Brand,Item"))): ?><a class="text_black" href="<?php echo DM('user', '/login');?>">请登录</a>
			<a class="text_black" href="<?php echo DM('user', '/register');?>">免费注册</a>
		<?php else: ?>
			<?php if(empty($_SESSION['user'])): ?><a class="text_black" href="<?php echo DM('user', '/login');?>">请登录</a>
			<a class="text_black" href="<?php echo DM('user', '/register');?>">免费注册</a>
			<?php else: ?>
			<a target="_blank" class="text_black" href="<?php echo DM('seller');?>"><span class="text_yellow"><?php echo ($_SESSION['user']['nick']); ?></span></a>
			<a class="text_black" href="<?php echo DM('user', '/logout');?>">退出登录</a><?php endif; endif; ?>
		</span>
		<!--<div class="pull-right clearfix ad">-->
			<!--<div class="re fl plr10 hover_title">-->
				<!--<a target="_blank" class="text_77" href="<?php echo DM('my');?>">买家中心<i class="fa fa-caret-down ml5"></i></a>-->
				<!--<div class="ab dn line20 hover_body">-->
					<!--<a target="_blank" class="text_77" href="<?php echo DM('my', '/orders');?>"><p class="mb7">已买到的宝贝</p></a>-->
					<!--<a target="_blank" class="text_77" href="<?php echo DM('my', '/history');?>"><p class="mb7">我的足迹</p></a>-->
				<!--</div>					-->
			<!--</div>-->
			<!--<div class="re fl plr10 hover_title">-->
				<!--<a target="_blank" class="text_77" href="<?php echo DM('my','/fav');?>"><i class="fa fa-star mr5 text_red"></i>收藏夹<i class="fa fa-caret-down ml5"></i></a>-->
				<!--<div class="ab dn line20 hover_body">-->
					<!--<a target="_blank" class="text_77" href="<?php echo DM('my', '/fav');?>"><p class="mb7">收藏的宝贝</p></a>-->
					<!--<a target="_blank" class="text_77" href="<?php echo DM('my', '/favshop');?>"><p class="mb7">收藏的店铺</p></a>-->
				<!--</div>					-->
			<!--</div>-->
			<!--<div class="re fl plr10">-->
				<!--<a class="text_77" href="<?php echo DM('s', '/category');?>">商品分类</a>				-->
			<!--</div>-->
			<!--<div class="re fl plr10 hover_title">-->
				<!--<a target="_blank" class="text_77" href="<?php echo DM('seller');?>">卖家中心<i class="fa fa-caret-down ml5"></i></a>-->
				<!--<div class="ab dn line20 hover_body">-->
					<!--<a target="_blank" class="text_77" href="<?php echo DM('zhaoshang');?>"><p class="mb7">免费开店</p></a>-->
					<!--<a target="_blank" class="text_77" href="<?php echo DM('seller', '/orders');?>"><p class="mb7">已卖出的宝贝</p></a>-->
					<!--<a target="_blank" class="text_77" href="<?php echo DM('sell');?>"><p class="mb7">出售中的宝贝</p></a>-->
					<!--<a target="_blank" class="text_77" href="<?php echo DM('ad');?>"><p class="mb7">广告位购买</p></a>-->
					<!--<a target="_blank" class="text_77" href="<?php echo DM('seller', '/refund');?>"><p class="mb7">退款列表</p></a>-->
					<!--&lt;!&ndash; <a target="_blank" class="text_77" href="https://imweb.dtfangyuan.com:9443/download/index.html"><p class="mb7">大唐方圆</p></a> &ndash;&gt;-->
				<!--</div>					-->
			<!--</div>-->
			<!--<div class="re fl plr10">-->
				<!--<a target="_blank" class="text_red" href="https://www.dttx.com/apps">app下载</a>-->
			<!--</div>-->
			<!--<div class="re fl plr10 hover_title">-->
				<!--<a class="text_77" href="javascript:;">联系客服<i class="fa fa-caret-down ml5"></i></a>-->
				<!--<div class="ab dn line20 hover_body">-->
					<!--<a class="text_77" href="<?php echo DM('www', '/service');?>"><p class="mb7">客服中心</p></a>-->
					<!--<a class="text_77" href="<?php echo DM('faq');?>"><p class="mb0">帮助中心</p></a>-->
				<!--</div>					-->
			<!--</div>	-->
		<!--</div>-->
	</div>
</div>
	<?php echo W('Common/Ad/index_new', array(130,'margin:auto;max-height:100px;'));?>
	<div class="container">
		<div class="row mg0">
			<div class="col-xs-2 pl0"><a href="<?php echo DM('www');?>"><img src="/Public/new_design/images/dt_logo.png"></a></div>
			<div class="col-xs-8 pr0" style="height: 48px; line-height: 48px; margin-top: 40px;">
				<div class="re fl plr10">
					<a class="text_77 fs14" href="<?php echo DM('seller');?>">卖家首页</a>
				</div>
				<?php $_result=getShopMenu();if(is_array($_result)): $i = 0; $__LIST__ = $_result;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i; if(($vo["position"]) == "1"): ?><div class="re fl plr10 hover_title ml10">
							<a class="text_77 fs14 <?php echo ($vo["class"]); ?>" <?php if(($vo["target"]) == "1"): ?>target="_blank"<?php endif; ?> href="<?php echo eval($vo['url']);?>"><?php echo ($vo["name"]); if(!empty($vo["child"])): ?><i class="fa fa-caret-down ml5"></i><?php endif; ?></a>
							<?php if(!empty($vo["child"])): ?><div class="ab dn line20 hover_body" style="left: 0;top: 39px;border: 0;">
								<?php if(is_array($vo["child"])): $i = 0; $__LIST__ = $vo["child"];if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$c): $mod = ($i % 2 );++$i; if(!empty($_SESSION['user']['funIds'])): if(in_array(($c["auth"]), is_array($_SESSION['user']['funIds'])?$_SESSION['user']['funIds']:explode(',',$_SESSION['user']['funIds']))): ?><a class="text_77 fs14 <?php echo ($c["class"]); ?>" <?php if(($c["target"]) == "1"): ?>target="_blank"<?php endif; ?> href="<?php echo eval($c['url']);?>"><p class="mb7"><?php echo ($c["name"]); ?></p></a><?php endif; ?>
										<?php else: ?>
										<a class="text_77 fs14 <?php echo ($c["class"]); ?>" <?php if(($c["target"]) == "1"): ?>target="_blank"<?php endif; ?> href="<?php echo eval($c['url']);?>"><p class="mb7"><?php echo ($c["name"]); ?></p></a><?php endif; endforeach; endif; else: echo "" ;endif; ?>
							</div><?php endif; ?>
						</div><?php endif; endforeach; endif; else: echo "" ;endif; ?>
				<!--<form action="<?php echo DM('s');?>" method="get" class="search_goods_shop fr" style="width: 400px; margin-top: 5px;">-->
					<!--<div class="search_tle row mg0">-->
						<!--<input name="keywords" class="col-xs-8 bor_no plr10 fs14" style="height:32px" type="text" placeholder="请输入店铺/商品的搜索关键字" value="" autocomplete="off">-->
						<!--<button class="col-xs-2 btn btn_m bg_red bor_no bor_r0 fs16 fr" style="height:34px;line-height:18px;color:white">搜索</button>-->
					<!--</div>-->
				<!--</form>-->
			</div>
			<!--<?php echo W('Common/Side/cart');?>-->
		</div>
	</div>
</header>

    <div class="centent_body">
        <!-- to do your html codeing -->
        <div class="revision_bg">
            <div class="container ptb_size_lg clearfix">
                <!-- 左侧导航 -->
                <div class="revisionLeft controller-active" data-controller="/Setting" data-url="/setting.html" style="width: 150px;">
    <div class="pb10">
        <?php $_result=getShopMenu();if(is_array($_result)): $i = 0; $__LIST__ = $_result;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i; if(($vo["position"]) == "4"): ?><div class="user_left_tle">
                <a class="revisionRoad" href="javascript:;"><?php echo ($vo["name"]); ?></a>
                <div class="ad">
                    <?php if(is_array($vo["child"])): $i = 0; $__LIST__ = $vo["child"];if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$c): $mod = ($i % 2 );++$i; if(!empty($_SESSION['user']['funIds'])): if(in_array(($c["auth"]), is_array($_SESSION['user']['funIds'])?$_SESSION['user']['funIds']:explode(',',$_SESSION['user']['funIds']))): ?><a class="revisionRoadSon <?php echo ($c["class"]); ?>" <?php if(($c["target"]) == "1"): ?>target="_blank"<?php endif; ?> data-controller="<?php echo ($c["controller"]); ?>" href="<?php echo eval($c['url']);?>"><?php echo ($c["name"]); ?></a><?php endif; ?>
                            <?php else: ?>
                            <a class="revisionRoadSon <?php echo ($c["class"]); ?>" <?php if(($c["target"]) == "1"): ?>target="_blank"<?php endif; ?> data-controller="<?php echo ($c["controller"]); ?>" href="<?php echo eval($c['url']);?>">
                                <?php echo ($c["name"]); ?>
                                <?php if(($vo["is_new"]) == "1"): ?><img class="ml5" src="/Public/images/icon-new.gif" /><?php endif; ?>
                            </a><?php endif; endforeach; endif; else: echo "" ;endif; ?>
                </div>
            </div><?php endif; endforeach; endif; else: echo "" ;endif; ?>
    </div>
</div>
                <!-- 左侧导航结束 -->

                <div class="revisionRight pd15" style="width: 1020px"><!-- 右侧内容 -->
                    <div class="clearfix revisionOrderTab" data-url="">
                        <a class="orderOption active" href="javascript:;">店铺设置</a>
                    </div>

                    <!-- 表单 -->
                    <div class="content">
    <div class="row">
        <div class="col-sm-2">

        </div>
        <div class="col-sm-8">
            <?php echo ($form["html"]); ?>
        </div>
        <div class="col-sm-2">

        </div>
    </div>
</div>

                </div>

            </div>
        </div>
        <!-- my codeing over -_-   -->

    </div>

	<div style="position: fixed;bottom: 10px;right: 10px;width: 150px;height: 400px;background: url(/Public/images/fangyuan.png)" class="hide">
		<a onclick="return $(this).closest('div').addClass('hide');" style="position: absolute;right: 0; top: 0; display: inline-block;width: 20px;height: 20px;" href="javascript:;"></a>
		<a onclick="return $(this).closest('div').addClass('hide');" target="_blank" style="position: absolute;right: 7px; top: 276px; display: inline-block;width: 135px;height: 33px;" href="https://imweb.dtfangyuan.com:9443/download/index.html"></a>
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
				<div class="col-xs-2 pl40">
					<h5><strong>新手指南</strong></h5>
					<a href="<?php echo DM('faq');?>/view.html?id=866" target="_blank"><p class="mb0 line22">注册流程</p></a>
					<a href="<?php echo DM('faq');?>/view.html?id=868" target="_blank"><p class="mb0 line22">登录流程</p></a>
					<a href="<?php echo DM('faq');?>/view.html?id=882" target="_blank"><p class="mb0 line22">商品查找</p></a>
					<a href="<?php echo DM('faq');?>/view.html?id=953" target="_blank"><p class="mb0 line22">购物流程</p></a>
				</div>
				<div class="col-xs-2 pl40">
					<h5><strong>会员相关</strong></h5>
					<a href="<?php echo DM('faq');?>/view.html?id=884" target="_blank"><p class="mb0 line22">积分获取</p></a>
					<a href="<?php echo DM('faq');?>/view.html?id=826" target="_blank"><p class="mb0 line22">忘记密码</p></a>
					<a href="<?php echo DM('faq');?>/view.html?id=887" target="_blank"><p class="mb0 line22">后台管理</p></a>
					<a href="<?php echo DM('faq');?>/view.html?id=890" target="_blank"><p class="mb0 line22">商家入驻</p></a>
					<a href="<?php echo DM('faq');?>/view.html?id=893" target="_blank"><p class="mb0 line22">入驻流程</p></a>
				</div>
				<div class="col-xs-2 pl40">
					<h5><strong>支付相关</strong></h5>
					<a href="<?php echo DM('faq');?>/view.html?id=961" target="_blank"><p class="mb0 line22">余额充值 </p></a>
					<a href="<?php echo DM('faq');?>/view.html?id=958" target="_blank"><p class="mb0 line22">唐宝支付 </p></a>
					<a href="<?php echo DM('faq');?>/view.html?id=958" target="_blank"><p class="mb0 line22">支付宝支付 </p></a>
					<a href="<?php echo DM('faq');?>/view.html?id=958" target="_blank"><p class="mb0 line22">余额支付 </p></a>
				</div>
				<div class="col-xs-2 pl40">
					<h5><strong>物流配送</strong></h5>
					<a href="<?php echo DM('faq');?>/view.html?id=896" target="_blank"><p class="mb0 line22">配送方式 </p></a>
					<a href="<?php echo DM('faq');?>/view.html?id=898" target="_blank"><p class="mb0 line22">配送范围 </p></a>
					<a href="<?php echo DM('faq');?>/view.html?id=901" target="_blank"><p class="mb0 line22">收货验货 </p></a>
				</div>
				<div class="col-xs-4">
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

    //个人店铺不可使用的链接
    <?php if(($_SESSION['user']['shop_type']) == "6"): ?>$(".noHref").click(function () {
            vmodal({
                url:'<?php echo U("/tool/notAccess");?>',
                footer:false,
                width:860,
            });
            return false;
        });<?php endif; ?>
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
	
    <!-- to do your javascript codeing -->
    <?php echo ($form["js"]); ?>

	<div id="ajax-modal" class="modal fade" tabindex="-1">
	<div class="modal-dialog modal-lg">
		<div class="modal-content">
			<div class="modal-header">
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
		
<div id="ajax-modal2" class="modal fade" tabindex="-1">
	<div class="modal-dialog modal-lg">
		<div class="modal-content">
			<div class="modal-header">
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
<!-- <div class="loading hide">
	<div class="loading-body text-center"><img src="__IMG__/loading.gif"></div>
</div> -->
	</body>
</html>