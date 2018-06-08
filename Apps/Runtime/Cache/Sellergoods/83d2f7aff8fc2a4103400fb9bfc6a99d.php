<?php if (!defined('THINK_PATH')) exit();?><!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <title><?php echo (C("seo.title")); ?></title>
    <meta name="keywords" content="<?php echo (C("seo.keywords")); ?>">
    <meta name="description" content="<?php echo (C("seo.description")); ?>"> 
    <meta name="author" content="It is true">
    <meta name="viewport" content="width=device-width,initial-scale=1.0"/>

    <!-- Bootstrap core CSS -->
    <link rel="stylesheet" href="/Public/new_design/flatdream/js/bootstrap/dist/css/bootstrap.css">
    <link rel="stylesheet" type="text/css" href="/Public/new_design/flatdream/js/jquery.gritter/css/jquery.gritter.css"/>
    <link rel="stylesheet" href="/Public/new_design/flatdream/fonts/font-awesome-4/css/font-awesome.min.css">
    <!-- HTML5 shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!--[if lt IE 9]>
    <script src="../assets/js/html5shiv.js"></script>
    <script src="../assets/js/respond.min.js"></script>
    <![endif]-->
    <link rel="stylesheet" type="text/css" href="/Public/new_design/flatdream/js/jquery.nanoscroller/nanoscroller.css"/>
    <link rel="stylesheet" type="text/css" href="/Public/new_design/flatdream/js/jquery.codemirror/lib/codemirror.css">
    <link rel="stylesheet" type="text/css" href="/Public/new_design/flatdream/js/jquery.codemirror/theme/ambiance.css">
    <link rel="stylesheet" type="text/css" href="/Public/new_design/flatdream/js/jquery.vectormaps/jquery-jvectormap-1.2.2.css" media="screen"/>
    <link href="/Public/new_design/flatdream/css/style.css" rel="stylesheet"/>
    <!-- 定义样式 -->
    <link href="/Apps/Sellergoods/View/default/Public/css/common.css" rel="stylesheet">
    <link href="/Apps/Sellergoods/View/default/Public/css/css.css" rel="stylesheet">
    <link href="/Apps/Sellergoods/View/default/Public/css/open_yun.css" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="/Public/CSS/flatdream/js/jquery.icheck/skins/square/_all.css">

    <!-- 图片放大 -->
    <link href="/Public/CSS/flatdream/js/jquery.magnific-popup/dist/magnific-popup.css" rel="stylesheet" />
    <!-- talert 弹框css -->
    <link rel="stylesheet" type="text/css" href="/Public/Jquery/toastr/toastr.css" />

    <script src="/Public/new_design/flatdream/js/jquery.js"></script>
    
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
    <!-- 函数列表 -->
    <script src="/Public/Apps/global.js"></script>
    

    <script type="text/javascript" src="/Public/Jquery/jquery.lazyload.min.js"></script>

    <script src="/Public/new_design/flatdream/js/jquery.pushmenu/js/jPushMenu.js"></script>
    <script type="text/javascript" src="/Public/new_design/flatdream/js/jquery.nanoscroller/jquery.nanoscroller.js"></script>
    <script type="text/javascript" src="/Public/new_design/flatdream/js/jquery.sparkline/jquery.sparkline.min.js"></script>
    <script type="text/javascript" src="/Public/new_design/flatdream/js/jquery.ui/jquery-ui.js"></script>
    <script type="text/javascript" src="/Public/new_design/flatdream/js/jquery.gritter/js/jquery.gritter.js"></script>
    <!--<script type="text/javascript" src="/Public/new_design/flatdream/js/behaviour/core.js"></script>-->
    <script src="/Public/new_design/flatdream/js/bootstrap/dist/js/bootstrap.min.js"></script>

    <script src="/Public/new_design/flatdream/js/jquery.codemirror/lib/codemirror.js"></script>
    <script src="/Public/new_design/flatdream/js/jquery.codemirror/mode/xml/xml.js"></script>
    <script src="/Public/new_design/flatdream/js/jquery.codemirror/mode/css/css.js"></script>
    <script src="/Public/new_design/flatdream/js/jquery.codemirror/mode/htmlmixed/htmlmixed.js"></script>
    <script src="/Public/new_design/flatdream/js/jquery.codemirror/addon/edit/matchbrackets.js"></script>
    <script src="/Public/new_design/flatdream/js/jquery.vectormaps/jquery-jvectormap-1.2.2.min.js"></script>
    <script src="/Public/new_design/flatdream/js/jquery.vectormaps/maps/jquery-jvectormap-world-mill-en.js"></script>
    <script src="/Public/new_design/flatdream/js/behaviour/dashboard.js"></script>
    <!--<script type="text/javascript" src="/Public/Apps/make.js"></script>-->
    <!-- 上传images图片插件 -->
    <script type="text/javascript" src="/Public/Webuploader/js/webuploader.js"></script>
    <link rel="stylesheet" type="text/css" href="/Public/Webuploader/css/webuploader.css">


    <script>
    var ACTION = '<?php echo enCryptRestUri("/Index/index");?>';
    </script>
</head>

<body>
<!-- ajax_post_form的 表单提交div -->
<div id="ajax_tips"></div>
<!-- 模态框 -->
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


<header style="border-bottom: solid 2px #fbc819">
    <div class="">
        <div class="clearfix content_tle fs16 strong">
            <!--<img class="fl plr20" height="27" src="/Apps/Sellergoods/View/default/Public/images/dt_logo.png" alt="" style="margin: 9px 16px 0 16px;">-->
            <a href="" style="display: inline-block; width: 180px; padding: 0;">
                <img src="/Apps/Sellergoods/View/default/Public/images/dt_logo.png">
            </a>
            <a <?php if(empty($_GET['url'])): ?>class="active alert"<?php endif; ?> href="<?php echo DM('sell');?>" onclick="">商品管理首页</a>
            <a <?php if(($_GET['url']) == "cate"): ?>class="active alert"<?php endif; ?> href="<?php echo DM('sell'); echo U('Index/index',array('url'=>'cate'));?>">分类管理</a>
            <a <?php if(($_GET['url']) == "/goods/create"): ?>class="active alert"<?php endif; ?> href="<?php echo U('/index');?>?url=/goods/create">发布商品</a>
            <a <?php if(($_GET['url']) == "/goods/import"): ?>class="active alert"<?php endif; ?> href="<?php echo U('/index');?>?url=/goods/import">导入商品</a>
            <a href="<?php echo DM('seller');?>">卖家中心<span class="ab fs30 fl">◆</span></a>
            <div class="pull-right open_yun fs12">
                <!-- <input class="fl form-control" type="text"> -->
                <!-- <div class="fl btn btn_m btn-prusia btntrans">搜索</div> -->
                <a href="javascript:;"><?php echo ($_SESSION['user']['nick']); ?></a>
                <!-- <a href="">工单</a> -->
                <!-- <a href="">站内信</a> -->
                <a href="<?php echo DM('user', '/logout');?>">退出</a>
            </div>
        </div>
    </div>
</header>
    <div class="centent_body">
        <div class="ticket-main-wrap service-ticket-create">
            <ul id="left_sidebar" class="sidebar" style="overflow-y:auto;">
                <li class="menu-item <?php if($first_url == '/Cate/index'): ?>sidebar-current<?php endif; ?>">
	<i class="fa fa-list-ul fl"></i><a onclick="url_frame(this,'<?php echo U('/Cate');?>')"><span >分类管理</span></a>
</li>
<li class="menu-item <?php if($first_url == '/Goods/index'): ?>sidebar-current<?php endif; ?>">
	<i class="fa fa-plus-circle fl"></i><a onclick="url_frame(this,'<?php echo U('/Goods');?>')"><span >宝贝管理</span></a>
</li>
<li class="menu-item">
	<a onclick="url_frame(this,'<?php echo U('/Goods/index',array('shop_category_id'=>0));?>')">未分类</a>
</li>
<?php if(is_array($cate_list)): foreach($cate_list as $key=>$vo): ?><li class="menu-item">
		<a title="<?php echo ($vo["category_name"]); ?>" onclick="url_frame(this,'<?php echo U('/Goods/index',array('shop_category_id'=>$vo[id]));?>')">
			<!--<img src="/Apps/Sellergoods/View/default/Public/images/1.jpg" src2="/Apps/Sellergoods/View/default/Public/images/2.jpg" is_show="1" class="ico10" onclick="showSecondCate(this,<?php echo ($vo["id"]); ?>)"/>-->
			<img class="ico15" src="/Apps/Sellergoods/View/default/Public/images/5.png" src2="/Apps/Sellergoods/View/default/Public/images/6.png" is_show="1" onclick="showSecondCate(this,<?php echo ($vo["id"]); ?>)"/>
			<span ><?php echo ($vo["category_name"]); ?> </span>
		</a>
	</li>
	<?php if(is_array($vo["dlist"])): foreach($vo["dlist"] as $key=>$vol): ?><li class="menu-item s_<?php echo ($vo["id"]); ?>">
			<a title="<?php echo ($vol["category_name"]); ?>" onclick="url_frame(this,'<?php echo U('/Goods/index',array('shop_category_id'=>$vol[id]));?>')">
				<span >　<?php echo ($vol["category_name"]); ?></span>
			</a>
		</li><?php endforeach; endif; endforeach; endif; ?>
            </ul><!-- left over -->
            <div class="content-wrap" style="padding:0;">
                <iframe src="<?php echo ($first_url); ?>" height="100%" width="100%"  frameborder="0" class="main-iframe" id="main-iframe"></iframe>
            </div><!-- right over -->
        </div>
    </div>
<script>

function deleteWorkorder(w_no){
    vmodal({
        title:"删除工单",
        // url:'<?php echo U("/freightTemplate/create_area",array("express_id"=>$data["id"]));?>',
        msg:"您真的要删除吗？",
        class:'text-center',
        confirm:1,
    },function(ret){
        $(".modal-ok").click(function(){
            var ac = '<?php echo enCryptRestUri("/Workorder/delete");?>';
            ajax_post({
                url: '/run/authRun',
                headers:{Action:ac},
                data:{
                    w_no:w_no
                }
            },function(ret){
                ret.status = ret.code;
                talert(ret);
                if(ret.code == 1){
                    setTimeout(function(){
                        ref();
                    },1000);
                }
            });
        });
    });
}
    $(function(){
        $("#main-iframe").height( $(window).height()-$('header').height()-5);
        $("#left_sidebar").height( $(window).height()-$('header').height()-5);


    });
    function url_frame(obj,url){

        $(obj).closest('li').addClass('sidebar-current').siblings().removeClass('sidebar-current');
        $("#main-iframe").attr('src',url);

    }

    function showSecondCate(obj,sid){

        var new_src =  $(obj).attr('src2');
        var old_src =  $(obj).attr('src');
        if($(obj).attr('is_show') == 1){
            $(obj).attr('is_show',0);
            $('.s_'+sid+'').hide();
        }else{
            $(obj).attr('is_show',1);
            $('.s_'+sid+'').show();
        }
        $(obj).attr('src',new_src);
        $(obj).attr('src2',old_src);
        event.stopPropagation();
    }
    //分类页刷新
    function cate_reload(){
        location.href="<?php echo DM('sell'); echo U('Index/index',array('url'=>'cate'));?>";
    }

</script>


</body>
</html>