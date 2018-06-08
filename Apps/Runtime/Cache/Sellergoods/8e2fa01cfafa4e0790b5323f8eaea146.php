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
    var ACTION = '<?php echo enCryptRestUri("/Goods/index");?>';
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


<div class="content-wrap">
    <div class="create-content">
        <div class="main-card">
			<form method="get" action="" class="p10 bg-gray md20">
				<div class="pl10 row mt10">
					<div class="col-xs-2">
						<div class="input-group">
							<span class="input-group-addon">关键词</span>
							<input type="text" class="form-control" name="q" placeholder="请输入商品名称" value="<?php echo ($_GET['q']); ?>">
						</div>
					</div>
					<div class="col-xs-3">
						<div class="input-group">
							<span class="input-group-addon">价格从</span>
							<input type="text" class="form-control" name="s_price" placeholder="宝贝最低价" value="<?php echo ($_GET['s_price']); ?>">
							<span class="input-group-addon">至</span>
							<input type="text" class="form-control" name="e_price" placeholder="宝贝最高价" value="<?php echo ($_GET['e_price']); ?>">
							<span class="input-group-addon">元</span>
						</div>
					</div>
					<div class="col-xs-2">
						<div class="input-group">
							<span class="input-group-addon">状态</span>
							<select type="text" class="form-control" name="status">
								<option value="1" <?php if($_GET['status'] == 1): ?>selected="selected"<?php endif; ?>>出售中</option>
								<option value="2" <?php if($_GET['status'] == 2): ?>selected="selected"<?php endif; ?>>待上架</option>
								<option value="3" <?php if($_GET['status'] == 3): ?>selected="selected"<?php endif; ?>>主图缺失</option>
								<option value="4" <?php if($_GET['status'] == 4): ?>selected="selected"<?php endif; ?>>违规</option>
							</select>
						</div>
					</div>
					<div class="col-xs-2">
						<div class="input-group">
							<span class="input-group-addon">交易类型</span>
							<select type="text" class="form-control" name="scoreType">
								<option value="1" <?php if($_GET['scoreType'] == 1): ?>selected="selected"<?php endif; ?>>金积分</option>
								<option value="4" <?php if($_GET['scoreType'] == 4): ?>selected="selected"<?php endif; ?>>银积分</option>
								<option value="2" <?php if($_GET['scoreType'] == 2): ?>selected="selected"<?php endif; ?>>现金</option>
							</select>
						</div>
					</div>
					<div class="col-xs-2">
						<button class="btn btn-primary btn-120px" type="submit"><i class="fa fa-search"></i> 搜索</button>
					</div>
				</div>
			</form>	
			<div class="clearfix">	
				<ul class="nav nav-tabs border-d">
					<li class="<?php echo ($_GET['status']==1)?'active':'';?> ml20">
						<a href="<?php echo U('/Goods/index',array('status'=>1,'shop_category_id'=>$_GET['shop_category_id']));?>">出售中</a>
					</li>
					<li class="<?php echo $_GET['is_best']==1?'active':'';?>">
						<a href="<?php echo U('/Goods/index',array('is_best'=>1,'shop_category_id'=>$_GET['shop_category_id']));?>">橱窗中</a>
					</li>
					<li class="<?php echo $_GET['status']==2?'active':'';?>">
						<a href="<?php echo U('/Goods/index',array('status'=>2,'shop_category_id'=>$_GET['shop_category_id']));?>">待上架</a>
					</li>
					<li class="<?php echo $_GET['status']==3?'active':'';?>">
						<a href="<?php echo U('/Goods/index',array('status'=>3,'shop_category_id'=>$_GET['shop_category_id']));?>">主图缺失</a>
					</li>
					<li class="<?php echo $_GET['status']==4?'active':'';?>">
						<a href="<?php echo U('/Goods/index',array('status'=>4,'shop_category_id'=>$_GET['shop_category_id']));?>">违规</a>
					</li>
				</ul>	
			</div>
			<div class="content pd10">
				<div class="bg-warning pd10 letter-spacing1">
					<i class="fa fa-exclamation-circle fs16 fl text_yellow"></i>
					<p class="ml20 mb0">小提示，参与官方活动中的商品不允许编辑、下架、删除等操作。</p>
				</div>
			</div>
			
            <div class="">
            	<?php if(!empty($data)): ?><form id="form-edit">
				<div class="p20">
					<?php switch($_GET['status']): case "1": ?><div class="btn btn-primary btn-trans btn-rad" onclick="goods_offline()">批量下架</div>
							<div class="btn btn-primary btn-trans btn-rad" onclick="category_control()">批量修改分类</div>
							<div class="btn btn-primary btn-trans btn-rad" onclick="addMoreBest()">批量设置橱窗</div>
							<div class="btn btn-primary btn-trans btn-rad" onclick="express_tpl()">批量修改运费模板</div>
							<div class="btn btn-danger btn-trans btn-rad" onclick="delMoreBest()">批量移除橱窗</div>
							<div class="btn btn-danger btn-trans btn-rad" onclick="deleteGoods()">批量删除</div><?php break;?>
						<?php default: ?>
							<?php if(empty($_GET['is_best'])): if(($_GET['status']) == "2"): ?><div class="btn btn-primary btn-trans btn-rad" onclick="goods_online()">批量上架</div><?php endif; ?>
								<div class="btn btn-primary btn-trans btn-rad" onclick="category_control()">批量修改分类</div>
								<div class="btn btn-primary btn-trans btn-rad" onclick="express_tpl()">批量修改运费模板</div>
								<div class="btn btn-danger btn-trans btn-rad" onclick="deleteGoods()">批量删除</div><?php endif; endswitch;?>

					<?php if(($_GET['is_best']) == "1"): ?><div class="btn btn-primary btn-trans btn-rad" onclick="goods_offline()">批量下架</div>
						<div class="btn btn-primary btn-trans btn-rad" onclick="category_control()">批量修改分类</div>
						<div class="btn btn-primary btn-trans btn-rad" onclick="express_tpl()">批量修改运费模板</div>
						<div class="btn btn-danger btn-trans btn-rad" onclick="delMoreBest()">批量移除橱窗</div>
						<div class="btn btn-danger btn-trans btn-rad" onclick="deleteGoods()">批量删除</div><?php endif; ?>
				</div>	            		
                <table class="tm no-border">
                	<thead class="no-border">
                        <tr class="subject">
                            <th class="text-center" width="5%" title="全选"><input name="all_choice" id="checkedAll" class="i-red-square" type="checkbox"> </th>
							<th class="" width="5%">宝贝图片</th>
                            <th class="" width="20%">宝贝名称</th>
                            <th class="" width="6%">价格</th>
							<th class="text-center" width="6%">商品库存</th>
							<th class="text-center" width="6%">销售数量</th>
							<th class="text-center" width="8%">交易类型</th>
							<th class="text-center" width="8%">运费模板</th>
							<th class="">所属分类</th>
							<th class="text-center" width="5%">
								<?php if(($_GET['status']) == "4"): ?>违规原因
									<?php else: ?>
									商品来源<?php endif; ?>
							</th>
                            <th class="text-center" width="10%">创建时间</th>
                            <th class="text-center" width="4%">状态</th>
                            <th class="text-center" width="5%">操作</th>
                        </tr>
                    </thead>
                        <tbody class="no-border-y goods-list">
                            <?php if(is_array($data["list"])): foreach($data["list"] as $key=>$vo): ?><tr>
                                    <td class="text-center"><input type="checkbox" class="ids i-red-square" name="ids[]" value="<?php echo ($vo["id"]); ?>" /></td>
									<td class="">
										<a href="javascript:;" url="<?php echo DM('item'); echo U('/index/index', ['id' => $vo['attr_list'][0]['id']]);?>">
											<img src="<?php echo ($vo["images"]); ?>" width="50px" />
										</a>
									</td>
                                    <td class="flex-start flex-fdr flex-aifs">
										<textarea required="true" rangelength="[20,80]" maxlength="80" data-title="<?php echo ($vo["goods_name"]); ?>" data-id="<?php echo ($vo["id"]); ?>" class=" bor_no goods_name flex-f1" rows="5"><?php echo ($vo["goods_name"]); ?></textarea>
										<i class="fa fa-pencil fa-fw  text_77"></i><?php if($vo["is_best"] == 1): ?><img src="/Public/images/icon-hot.png"><?php endif; ?>
									</td>
									<td class="strong"><input type="text" class="strong text_44 input_80 plr5 bor_no changeSku" value="<?php echo ($vo["price"]); ?>" data-url="<?php echo U('/goods/sku', ['id' => $vo['id']]);?>"/></td>
									<td class="text-center"><span class="text_red"><?php echo ($vo["num"]); ?></span><?php if(($vo["num"]) < "10"): ?><strong class="text_red ml10">库存不足</strong><?php endif; ?></td>
									<td class="text-center"><?php echo ($vo["sale_num"]); ?></td>
									<td class="text-center"><?php echo ($scoreType[$vo[score_type]]); ?></td>
									<td class="text-center"><?php echo ($tpl[$vo[express_tpl_id]]); ?></td>
									<td class="">
										<?php if(empty($vo["my_category_name"])): else: ?>
											<?php if(is_array($vo["my_category_name"])): foreach($vo["my_category_name"] as $key=>$v): ?><div><span onclick="delete_category(<?php echo ($vo["id"]); ?>,<?php echo ($key); ?>)"><i class="fa fa-times text-danger"></i></span><?php echo ($v); ?></div><?php endforeach; endif; endif; ?>
									</td>
                                     <td class="text-center text_red">
										 <?php if(($_GET['status']) == "4"): echo ($vo["illegl_reason"]); ?>
											 <?php else: ?>
											 <?php if($vo['is_collection'] == 1): ?>淘宝
											 <?php elseif($vo['is_collection'] == 2): ?>
												天猫<?php endif; endif; ?>
									 </td> 
                                    <td class="text-center"><?php echo ($vo["atime"]); ?></td>
                                    <td class="text-center">
									<?php switch($vo["status"]): case "1": ?><div class="text-success">销售中</div><?php break;?>    
										<?php case "2": ?><div class="text-info">待上架</div><?php break;?>   
										<?php case "3": ?><div class="text-default">缺少主图</div><?php break;?>   
										<?php case "4": ?><div class="text-danger">违规</div><?php break;?>
										<?php case "5": ?><div class="text-danger">异常</div><?php break;?>
										<?php default: ?>禁止上架<?php endswitch;?>
									</td>
                                    <td class="text-center link-blue">
										<a href="<?php echo U('/goods/edit', ['id' => $vo['id']]);?>">编辑</a>
                                        <?php if($vo['status'] == 1): ?><a href="javascript:down_goods('<?php echo ($vo["id"]); ?>');">下架</a>
                                        <?php else: ?>
											<?php if(!in_array(($_GET['status']), explode(',',"3,4"))): ?><a href="javascript:up_goods('<?php echo ($vo["id"]); ?>');">上架</a><?php endif; endif; ?>
                                    </td>
                                </tr><?php endforeach; endif; ?>
                        </tbody>
                </table>
				<div class='p20'>
					<div class="page-box">
						<?php echo page_html($data['pageinfo']);?>
					</div>
				</div>
					</form>
				<?php else: ?>
					<div class="text-center nors">
						<img src="/Apps/Sellergoods/View/default/Public/images/no-find.png" alt="找不到记录！" />
					</div><?php endif; ?>
            </div>

        </div>
    </div>
</div>
<script type="text/javascript" src="/Public/Jquery/jquery-ui-1.10.3.custom/development-bundle/ui/jquery.ui.core.js"></script>
<script type="text/javascript" src="/Public/Jquery/jquery-ui-1.10.3.custom/development-bundle/ui/jquery.ui.widget.js"></script>
<script type="text/javascript" src="/Public/Jquery/jquery-ui-1.10.3.custom/development-bundle/ui/jquery.ui.mouse.js"></script>
<script type="text/javascript" src="/Public/Jquery/jquery-ui-1.10.3.custom/development-bundle/ui/jquery.ui.sortable.js"></script>
<script>
	//编辑商品名称
	$("textarea.goods_name").bind({
		focusout:function() {
			var obj = $(this);
			var title	=	obj.val();
			var d	=	obj.data();
			if(title.length < 2 || title.length > 80) {
                talert({status:0,msg:'商品名称应在2-80位'});
                return false;
			}
			if(title == d.title) {
				return false;
			}

			api({
				data:{apiurl:'/SellerGoodsManage/goods_name_edit',goods_id:d.id,goods_name:title,is_openid:1}
			},function(ret){
				ret.status = ret.code;
				talert(ret);
				if(ret.code == 1){
					obj.data('title',title);
				}
			});
		}
	});
	//编辑商品属性
	$(".changeSku").focus(function() {
		var d	=	$(this).data();
		vmodal({
			title:d.title,
			url:d.url,
			width:"80%"
		});
	})
	//全选
	function choice(){
		$('input[name="all_choice"]').on('ifChanged', function(){
			$('input[name="ids[]"]').iCheck('toggle');
		});
	}

	//批量修改运费模板
	function express_tpl(){
		var ids	=	'';
		$(".ids").each(function() {
			if($(this).is(":checked") == true) {
				ids	+=	$(this).val() + ',';
			}
		})
		if(ids == "" ){
			talert({status:0,msg:'请至少选择一项宝贝'});
			return false;
		}
		vmodal({title:'选择运费模板',url:'/Goods/change_tpl',width:'900px'})
	}

	//打开类目操作页面
	function category_control(){
		var num = $(".ids:checked").length;
		if(num >0){
			vmodal({title:'类目操作',url:'/Goods/category_control',width:'900px'})
		}else{
			talert({status:0,msg:'请先勾选需要操作的宝贝'});
		}
	}

	//批量追加分类
	function express_category(){
		var ids	=	'';
		$(".ids").each(function() {
			if($(this).is(":checked") == true) {
				ids	+=	$(this).val() + ',';
			}
		})
		if(ids == "" ){
			talert({status:0,msg:'请至少选择一项宝贝'});
			return false;
		}
		vmodal({title:'请选择要追加的分类',url:'/Goods/change_category',width:'900px'})
	}
	
	//批量上架
	function goods_online(){
		var ids	=	'';
		$(".ids").each(function() {
			if($(this).is(":checked") == true) {
				ids	+=	$(this).val() + ',';
			}
		})
		if(ids == "" ){
			talert({status:0,msg:'请至少选择一项宝贝上架'});
			return false;
		}
		ids = ids.substr(0,ids.length-1);
		
		api({
			data:{apiurl:'/SellerGoodsManage/set_goods_online',id:ids,is_openid:1}
		},function(ret){
			ret.status = ret.code;
			talert(ret);
			if(ret.code == 1){
				setTimeout(function(){
					ref();
				},1000);
			}
		});
	}
	
	//批量下架
	function goods_offline(){
		var ids	=	'';
		$(".ids").each(function() {
			if($(this).is(":checked") == true) {
				ids	+=	$(this).val() + ',';
			}
		})
		if(ids == "" ){
			talert({status:0,msg:'请至少选择一项宝贝下架'});
			return false;
		}
		ids = ids.substr(0,ids.length-1);
		
		api({
			data:{apiurl:'/SellerGoodsManage/set_goods_offline',id:ids,is_openid:1}
		},function(ret){
			ret.status = ret.code;
			talert(ret);
			if(ret.code == 1){
				setTimeout(function(){
					ref();
				},1000);
			}
		});
	}

    $(function() {
		choice();
    });
 
    function up_goods(w_no){
        vmodal({
            title:"确认上架",
            msg:"<h4>您真的要上架此商品吗？</h4>",
            class:'text-center',
            confirm:1,
            width:'650px',
        },function(ret){
            $(".modal-ok").click(function(){
				api({
					data:{apiurl:'/SellerGoodsManage/set_goods_online',id:w_no,is_openid:1}
				},function(ret){
 
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
	
	function down_goods(w_no){
        vmodal({
            title:"确认下架",
            msg:"<h4>您真的要下架此商品吗？</h4>",
            class:'text-center',
            confirm:1,
            width:'650px',
        },function(ret){
            $(".modal-ok").click(function(){
				api({
					data:{apiurl:'/SellerGoodsManage/set_goods_offline',id:w_no,is_openid:1}
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

	//删除分类
	function delete_category(s_id,goods_category_id){
        vmodal({
            title:"确认删除",
            msg:"<h4>您真的要删除此分类吗？</h4>",
            class:'text-center',
            confirm:1,
            width:'650px',
        },function(ret){
            $(".modal-ok").click(function(){
				api({
					data:{apiurl:'/SellerGoodsManage/delete_goods_category',id:s_id,shop_category_id:goods_category_id,is_openid:1}
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
	
	//批量修改分类
	function update_category(){
		var ids	=	'';
		$(".ids").each(function() {
			if($(this).is(":checked") == true) {
				ids	+=	$(this).val() + ',';
			}
		})
		if(ids == "" ){
			talert({status:0,msg:'请至少选择一项宝贝'});
			return false;
		}
		vmodal({title:'请选择分类',url:'/Goods/update_category',width:'900px'})
	}
	
	//批量删除分类
	function batch_delete_category(s_id,goods_category_id){
		var ids	=	'';
		$(".ids").each(function() {
			if($(this).is(":checked") == true) {
				ids	+=	$(this).val() + ',';
			}
		})
		if(ids == "" ){
			talert({status:0,msg:'请至少选择一项宝贝'});
			return false;
		}
		vmodal({title:'选择要删除的分类',url:'/Goods/batch_delete_category',width:'900px'})
    }

    //批量添加宝贝橱窗
	function addMoreBest(){
		var num = $(".ids:checked").length;
		if(num >0){
			var ids = $(".ids:checked").map(function(){
				return $(this).val();
			}).get().join(',');
			api({
				data:{apiurl:'/SellerGoodsManage/set_best',id:ids,is_openid:1}
			},function(ret){
				talert({status:ret.code,msg:ret.msg});
				if(ret.code==1){
					setTimeout(function(){
						parent.ref();
					},1000);
				}
			});
		}else{
			talert({status:0,msg:'请先勾选需要添加橱窗的宝贝'});
		}
	}
	//批量删除宝贝橱窗
	function delMoreBest(){
		var num = $(".ids:checked").length;
		if(num >0){
			var ids = $(".ids:checked").map(function(){
				return $(this).val();
			}).get().join(',');
			api({
				data:{apiurl:'/SellerGoodsManage/cancel_best',id:ids,is_openid:1}
			},function(ret){
				talert({status:ret.code,msg:ret.msg});
				if(ret.code==1){
					setTimeout(function(){
						parent.ref();
					},1000);
				}
			});
		}else{
			talert({status:0,msg:'请先勾选需要移除橱窗的宝贝'});
		}
	}
	
	/**
	 * 删除商品
	 */
	function deleteGoods() {
		var ids = getIds();
		if (ids == false) return false;
		vmodal({
			title:'删除提示',
			msg:'<p class="text-center">您真的要删除选中的商品吗？</p>',
			confirm:true,
			width:600,
			footer:false,
		}, function () {
			$(".modal-ok").click(function () {
                api({
                    data:{apiurl:'/SellerGoods/goods_delete',id:ids,is_openid:1}
                },function(ret){
                    talert({status:ret.code,msg:ret.msg});
                    if(ret.code==1){
                        setTimeout(function(){
                            parent.ref();
                        },1000);
                    }
                });
            });
        });
    }

    /**
	 * 获取IDS
	 *
     * @returns {*}
     */
    function getIds() {
        var num = $(".ids:checked").length;
        if (num > 0) {
            var ids = $(".ids:checked").map(function(){
                return $(this).val();
            }).get().join(',');
            return ids;
		}
        talert({status:0,msg:'请先勾选需要移除橱窗的宝贝'});
        return false;
    }
	
</script>
</body>
</html>