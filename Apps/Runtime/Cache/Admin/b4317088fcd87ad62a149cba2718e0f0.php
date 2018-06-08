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
	<div class="navbar-fixed-top">
		<div class="pull-right">
			<div class="btn btn-primary btn-rad btn-trans m10" onclick="ref()"><i class="fa fa-refresh"></i> 刷新</div>
		</div>
		
		<ul class="nav-box2" data-page="/Index/main">
			<li><a href="#">管理首页</a></li>
		</ul>
	</div>
<script type="text/javascript" src="/Public/Jquery/ECharts/echarts.common.min.js"></script>
<link rel="stylesheet" type="text/css" href="/Apps/Admin/View/default/Public/css/skin_index_main.css" />
	<div style="margin-bottom:70px"></div>

	<div class="box m20">
		<div class="p10 border-d ft16">在线雇员(<?php echo (count($online_user)); ?>)</div>
		<div class="p20 online-user">

			<?php if(is_array($online_user)): $i = 0; $__LIST__ = $online_user;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?><div class="btn" data-username="<?php echo ($vo["name"]); ?>" data-admin_id="<?php echo ($vo["id"]); ?>" style="margin: 5px" title="踢TA下线，登录时间：<?php echo ($vo["atime"]); ?>，更新时间：<?php echo (date("Y-m-d H:i:s",$vo["time"])); ?>" onclick="vmodal({title:'踢TA下线',url:'/Index/offline/admin_id/<?php echo ($vo["id"]); ?>',width:'800px'})" <?php echo $vo['username']==session("admin.username")?"disabled":"";?>>
					<div><i class="fa fa-user"></i> <?php echo ($vo["name"]); echo $vo['username']==session("admin.username")?"(我自己)":"";?></div>
					<div><?php echo ($vo["username"]); ?></div>
					<div><?php echo ($vo["ip"]); ?></div>
				</div><?php endforeach; endif; else: echo "" ;endif; ?>

		</div>
	</div>

	<div class="info-panel">
		<dl class="member">
			<dt>
			<div class="ico"><i></i><sub title="会员总数"><span><em id="statistics_member"><?php echo ($totals_basic["total_member"]); ?></em></span></sub></div>
			<h3>会员</h3>
			<h5></h5>
			</dt>
			<dd>
				<ul>
					<li class="w50pre normal"><a href="javascript:void(0)" onclick="openWin({name:'100693910',title:'会员管理',url:'/User',rootid:'2'})">本周新增<sub><em id="statistics_week_add_member"></em><?php echo ($totals_basic_week["week_member"]); ?></sub></a></li>
					<li class="w50pre normal"><a href="javascript:void(0)" onclick="openWin({name:'100693910',title:'会员管理',url:'/User',rootid:'2'})">昨天新增<sub><em id="statistics_cashlist"><?php echo ($totals_basic["member"]); ?></em></sub></a></li>
				</ul>
			</dd>
		</dl>
		<dl class="shop hide">
			<dt>
			<div class="ico"><i></i><sub title="新增店铺数"><span><em id="statistics_store"><?php echo ($totals_basic["normal_store"]); ?></em></span></sub></div>
			<h3>店铺</h3>
			<h5></h5>
			</dt>
			<dd>
				<ul>
					<li class="w33pre normal"><a href="javascript:void(0)" onclick="openWin({name:'100693972',title:'店铺管理',url:'/Shop',rootid:'100693970'})">本周新增<sub><em id="statistics_store_joinin"><?php echo ($totals_basic_week["week_open_store"]); ?></em></sub></a></li>
					<li class="w33pre normal"><a href="javascript:void(0)" onclick="openWin({name:'100693972',title:'店铺管理',url:'/Shop',rootid:'100693970'})">昨天新增<sub><em id="statistics_store_bind_class_applay"><?php echo ($totals_basic["open_store_success"]); ?></em></sub></a></li>
					<li class="w33pre normal"><a href="javascript:void(0)" onclick="openWin({name:'100693992',title:'开店申请',url:'/Shopjoininfo',rootid:'100693970'})">待审核<sub><em id="statistics_store_joinin"><?php echo ($totals_basic["store_examine"]); ?></em></sub></a></li>
				</ul>
			</dd>
		</dl>
		<dl class="goods">
			<dt>
			<div class="ico"><i></i><sub title="商品总数"><span><em id="statistics_goods"><?php echo ($totals_basic["online_goods_num"]); ?></em></span></sub></div>
			<h3>商品</h3>
			<h5></h5>
			</dt>
			<dd>
				<ul>
					<li class="w50pre normal"><a href="javascript:void(0)" onclick="openWin({name:'100693934',title:'商品管理',url:'/Goods',rootid:'3'})">本周新增<sub title=""><em id="statistics_week_add_product"><?php echo ($totals_basic_week["week_goods_num"]); ?></em></sub></a></li>
					<li class="w50pre normal"><a href="javascript:void(0)" onclick="openWin({name:'100693934',title:'商品管理',url:'/Goods',rootid:'3'})">昨天新增<sub><em id="statistics_product_verify"><?php echo ($totals_basic["goods_num"]); ?></em></sub></a></li>
					<li class="w25pre normal hide"><a href="javascript:void(0)" onclick="openWin({name:'100693993',title:'违规商品',url:'/Goodsillegl',rootid:'3'})">违规<sub><em id="statistics_inform_list"><?php echo ($totals_basic["goods_illegl"]); ?></em></sub></a></li>
					<li class="w25pre normal hide"><a href="javascript:void(0)" onclick="openWin({name:'100693934',title:'商品管理',url:'/Goods',rootid:'3'})">主图不正常<sub><em id="statistics_brand_apply"><?php echo ($totals_basic["goods_badimg"]); ?></em></sub></a></li>
				</ul>
			</dd>
		</dl>
		<dl class="trade">
			<dt>
			<div class="ico"><i></i><sub title="订单总数"><span><em id="statistics_order"><?php echo ($totals_trans["order_num_total"]); ?></em></span></sub></div>
			<h3>订单</h3>
			<h5></h5>
			</dt>
			<dd>
				<ul>
					<li class="w50pre normal"><a href="javascript:void(0)" onclick="openWin({name:'100693980',title:'订单管理',url:'/Ordersshop',rootid:'43'})">本周新增<sub><em id="statistics_refund"><?php echo ($totals_trans_week["week_order_success"]); ?></em></sub></a></li>
					<li class="w50pre normal"><a href="javascript:void(0)" onclick="openWin({name:'100693980',title:'订单管理',url:'/Ordersshop',rootid:'43'})">昨天新增<sub><em id="statistics_return"><?php echo ($totals_trans["order_success"]); ?></em></sub></a></li>
					<li class="w25pre normal hide"><a href="javascript:void(0)" onclick="openWin({name:'100693999',title:'退货退款',url:'/Refund',rootid:'43'})">退款中<sub><em id="statistics_vr_refund"><?php echo ($totals_trans["refund"]); ?></em></sub></a></li>
					<li class="w18pre normal hide"><a href="javascript:void(0)" onclick="openWin({name:'100693999',title:'退货退款',url:'/Refund',rootid:'43'})">退货退款中<sub><em id="statistics_complain_new_list"><?php echo ($totals_trans["refund2"]); ?></em></sub></a></li>
					<li class="w20pre normal hide"><a href="#">待仲裁<sub><em id="statistics_complain_handle_list">0</em></sub></a></li>
				</ul>
			</dd>
		</dl>
		
		
		<dl class="operation hide">
			<dt>
			<div class="ico"><i></i></div>
			<h3>运营</h3>
			<h5>系统运营类设置及审核</h5>
			</dt>
			<dd>
				<ul>
					<li class="w15pre none"><a href="#">抢购<sub><em id="statistics_groupbuy_verify_list">0</em></sub></a></li>
					<li class="w17pre none"><a href="#">积分订单<sub><em id="statistics_points_order">0</em></sub></a></li>
					<li class="w17pre none"><a href="#">账单审核<sub><em id="statistics_check_billno">0</em></sub></a></li>
					<li class="w17pre none"><a href="#">账单支付<sub><em id="statistics_pay_billno">0</em></sub></a></li>
					<li class="w17pre none"><a href="#">平台客服<sub><em id="statistics_mall_consult">0</em></sub></a></li>
					<li class="w17pre none"><a href="#">服务站<sub><em id="statistics_delivery_point">0</em></sub></a></li>
				</ul>
			</dd>
		</dl>

		<dl class="cms hide">
			<dt>
			<div class="ico"><i></i><sub title="广告金额"><span><em id="statistics_order"><?php echo ($totals_promotion["ad_total_num"]); ?></em></span></sub></div>
			<h3>广告</h3>
			<h5></h5>
			</dt>
			<dd>
				<ul>
					<li class="w33pre normal"><a href="javascript:void(0)" onclick="openWin({name:'124',title:'广告投放管理',url:'/Ad',rootid:'68'})">本周新增<sub><em id="statistics_cms_article_verify"><?php echo ($totals_promotion_week["week_ad_num"]); ?></em></sub></a></li>
					<li class="w33pre normal"><a href="javascript:void(0)" onclick="openWin({name:'124',title:'广告投放管理',url:'/Ad',rootid:'68'})">昨天新增<sub><em id="statistics_cms_picture_verify"><?php echo ($totals_promotion["ad_num"]); ?></em></sub></a></li>
					<li class="w34pre normal"><a href="javascript:void(0)" onclick="openWin({name:'100693994',title:'素材审核',url:'/Adsucai',rootid:'68'})">待审核素材<sub><em id="statistics_cms_picture_verify"><?php echo ($totals_promotion["sucai"]); ?></em></sub></a></li>
				</ul>
			</dd>
		</dl>
		<dl class="circle hide">
			<dt>
			<div class="ico"><i></i></div>
			<h3>圈子</h3>
			<h5>申请开通/圈内话题及举报</h5>
			</dt>
			<dd>
				<ul>
					<li class="w33pre none"><a href="#">圈子申请<sub><em id="statistics_circle_verify">0</em></sub></a></li>
					<li class="w33pre none"><a href="#">话题</a></li>
					<li class="w34pre none"><a href="#">举报</a></li>
				</ul>
			</dd>
		</dl>
		<dl class="microshop hide">
			<dt>
			<div class="ico"><i></i></div>
			<h3>微商城</h3>
			<h5>随心看/个人秀/店铺街</h5>
			</dt>
			<dd>
				<ul>
					<li class="w33pre none"><a href="#">随心看</a></li>
					<li class="w33pre none"><a href="#">个人秀</a></li>
					<li class="w34pre none"><a href="#">店铺街</a></li>
				</ul>
			</dd>
		</dl>
		<dl class="system">
			<dt>
			<div class="ico"><i></i></div>
			<h3>关于</h3>

			</dt>
			<dd>
				<ul>
					<li class="w50pre none"><a href="<?php echo DM('user');?>" target="_blank">乐兑365商城<sub></sub></a></li>
					<li class="w50pre none"><a href="https://www.ledui365.cn" target="_blank">乐兑365</a></li>
				</ul>
			</dd>
		</dl>
		<div class="clear"></div>
		<div class="system-info"></div>
	</div>

	<div class="clearfix md20"></div>	
	
<div class="m20">
	<div class="row">
		<div class="col-xs-4">
			<div class="box text-center p20">
				<h1 style="font-size:2em;" class="md10"><?php echo ($totals_trans["success_order_total"]); ?></h1>
				<div>累计成交金额(元)</div>
			</div>
		</div>
		<div class="col-xs-4">
			<div class="box text-center p20">
				<h1 style="font-size:2em;" class="md10"><?php echo ($totals_basic["online_goods_num"]); ?></h1>
				<div>在售商品</div>
			</div>
		</div>
		<div class="col-xs-3 hide">
			<div class="box text-center p20">
				<h1 style="font-size:2em;" class="md10"><?php echo ($totals_basic["normal_store"]); ?></h1>
				<div>店铺数量</div>
			</div>
		</div>
		<div class="col-xs-3">
			<div class="box text-center p20">
				<h1 style="font-size:2em;" class="md10"><?php echo ($totals_basic["total_member"]); ?></h1>
				<div>会员总数</div>
			</div>
		</div>
	</div>
	
	<div class="clearfix md20"></div>
	
	<div class="box p20 md20">
		<div id="main" style="width: 100%;height:400px;"></div>
	</div>
			<script type="text/javascript">
				// 基于准备好的dom，初始化echarts实例
				var myChart = echarts.init(document.getElementById('main'));

				// 指定图表的配置项和数据
				<?php echo W('Widget/echart_total');?>


				// 使用刚指定的配置项和数据显示图表。
				myChart.setOption(option);
			</script>	
	
	<div class="box p20">
		<div id="money" style="width: 100%;height:400px;"></div>
	</div>
			<script type="text/javascript">
				// 基于准备好的dom，初始化echarts实例
				var myChart = echarts.init(document.getElementById('money'));

				// 指定图表的配置项和数据
				<?php echo W('Widget/echart_money');?>


				// 使用刚指定的配置项和数据显示图表。
				myChart.setOption(option);
			</script>
</div>

<div class="m20 box ">
	<div class="row p20">
		<div class="p20 col-xs-6">
			<div id="day_shop_date" style="width: 100%;height:450px;"></div>
		</div>
		<script type="text/javascript">
			// 基于准备好的dom，初始化echarts实例
			var myChart = echarts.init(document.getElementById('day_shop_date'));
			
			option = {
				title: {
					text: '昨日店铺销售金额排行',
				},
				tooltip: {
					trigger: 'axis',
					axisPointer: {
						type: 'shadow'
					}
				},
				legend: {
					data: ['前天销售金额', '昨天销售金额']
				},
				grid: {
					left: '3%',
					right: '4%',
					bottom: '3%',
					containLabel: true
				},
				xAxis: {
					type: 'value',
					boundaryGap: [0, 0.01]
				},
				yAxis: {
					type: 'category',
					data: ['<?php echo ($day_shop_data1["0"]["shop_name"]); ?>','<?php echo ($day_shop_data1["1"]["shop_name"]); ?>','<?php echo ($day_shop_data1["2"]["shop_name"]); ?>','<?php echo ($day_shop_data1["3"]["shop_name"]); ?>','<?php echo ($day_shop_data1["4"]["shop_name"]); ?>','<?php echo ($day_shop_data1["5"]["shop_name"]); ?>','<?php echo ($day_shop_data1["6"]["shop_name"]); ?>','<?php echo ($day_shop_data1["7"]["shop_name"]); ?>','<?php echo ($day_shop_data1["8"]["shop_name"]); ?>','<?php echo ($day_shop_data1["9"]["shop_name"]); ?>']
				},
				series: [
					{
						name: '昨天销售金额',
						type: 'bar',
						data: [<?php echo ($day_shop_data1["0"]["money_pay"]); ?>, <?php echo ($day_shop_data1["1"]["money_pay"]); ?>, <?php echo ($day_shop_data1["2"]["money_pay"]); ?>, <?php echo ($day_shop_data1["3"]["money_pay"]); ?>, <?php echo ($day_shop_data1["4"]["money_pay"]); ?>, <?php echo ($day_shop_data1["5"]["money_pay"]); ?>,<?php echo ($day_shop_data1["6"]["money_pay"]); ?>,<?php echo ($day_shop_data1["7"]["money_pay"]); ?>,<?php echo ($day_shop_data1["8"]["money_pay"]); ?>,<?php echo ($day_shop_data1["9"]["money_pay"]); ?>]
					},
					{
						name: '前天销售金额',
						type: 'bar',
						data: [<?php echo ($day_shop_data2["0"]["money_pay"]); ?>, <?php echo ($day_shop_data2["1"]["money_pay"]); ?>, <?php echo ($day_shop_data2["2"]["money_pay"]); ?>, <?php echo ($day_shop_data2["3"]["money_pay"]); ?>, <?php echo ($day_shop_data2["4"]["money_pay"]); ?>, <?php echo ($day_shop_data2["5"]["money_pay"]); ?>,<?php echo ($day_shop_data2["6"]["money_pay"]); ?>,<?php echo ($day_shop_data2["7"]["money_pay"]); ?>,<?php echo ($day_shop_data2["8"]["money_pay"]); ?>,<?php echo ($day_shop_data2["9"]["money_pay"]); ?>]
					}
				]
			};


			// 使用刚指定的配置项和数据显示图表。
			myChart.setOption(option);
		</script>
		<div class="p20 col-xs-5">
			<div id="total_shop_date" style="width: 100%;height:450px;"></div>
		</div>
		<script type="text/javascript">
			// 基于准备好的dom，初始化echarts实例
			var myChart = echarts.init(document.getElementById('total_shop_date'));
				option = {
					title: {
						text: '店铺累计销售金额排行',
					},
					tooltip: {
						trigger: 'axis',
						axisPointer: {
							type: 'shadow'
						}
					},
					legend: {
						data: ['累计销售金额']
					},
					grid: {
						left: '3%',
						right: '4%',
						bottom: '3%',
						containLabel: true
					},
					xAxis: {
						type: 'value',
						boundaryGap: [0, 0.01]
					},
					yAxis: {
						type: 'category',
						data: ['<?php echo ($total_shop_data["0"]["shop_name"]); ?>','<?php echo ($total_shop_data["1"]["shop_name"]); ?>','<?php echo ($total_shop_data["2"]["shop_name"]); ?>','<?php echo ($total_shop_data["3"]["shop_name"]); ?>','<?php echo ($total_shop_data["4"]["shop_name"]); ?>','<?php echo ($total_shop_data["5"]["shop_name"]); ?>','<?php echo ($total_shop_data["6"]["shop_name"]); ?>','<?php echo ($total_shop_data["7"]["shop_name"]); ?>','<?php echo ($total_shop_data["8"]["shop_name"]); ?>','<?php echo ($total_shop_data["9"]["shop_name"]); ?>']
					},
					series: [
						{
							name: '累计销售金额',
							type: 'bar',
							data: [<?php echo ($total_shop_data["0"]["total_money_pay"]); ?>, <?php echo ($total_shop_data["1"]["total_money_pay"]); ?>, <?php echo ($total_shop_data["2"]["total_money_pay"]); ?>, <?php echo ($total_shop_data["3"]["total_money_pay"]); ?>, <?php echo ($total_shop_data["4"]["total_money_pay"]); ?>, <?php echo ($total_shop_data["5"]["total_money_pay"]); ?>,<?php echo ($total_shop_data["6"]["total_money_pay"]); ?>,<?php echo ($total_shop_data["7"]["total_money_pay"]); ?>,<?php echo ($total_shop_data["8"]["total_money_pay"]); ?>,<?php echo ($total_shop_data["9"]["total_money_pay"]); ?>]
						},
					]
				};
			// 使用刚指定的配置项和数据显示图表。
			myChart.setOption(option);
		</script>	
	</div>
</div>

<div class="m20 box ">
	<div class = "b_list">
		<!--
		<div class="flex flex-fdr" >
			<div class="flex-f1" id="fare1" style="height:400px;"></div>
			<div class="flex-f1" id="fare2" style="height:400px;"></div>
			<div class="flex-f1" id="fare3" style="height:400px;"></div>
		</div>
		<div class="flex flex-fdr" >
			<div class="flex-f1" id="flow1" style="height:400px;"></div>
			<div class="flex-f1" id="flow2" style="height:400px;"></div>
			<div class="flex-f1" id="flow3" style="height:400px;"></div>
		</div>

		<script type="text/javascript">
			// 基于准备好的dom，初始化echarts实例
			var myChart1 = echarts.init(document.getElementById("fare1"));
			// 指定图表的配置项和数据
			<?php echo W('Echart/pie_echart',array('param' => $type1));?>
			// 使用刚指定的配置项和数据显示图表。
			myChart1.setOption(option);
		</script>
		<script type="text/javascript">
			// 基于准备好的dom，初始化echarts实例
			var myChart2 = echarts.init(document.getElementById("fare2"));
			// 指定图表的配置项和数据
			<?php echo W('Echart/pie_echart',array('param' => $type2));?>
			// 使用刚指定的配置项和数据显示图表。
			myChart2.setOption(option);
		</script>
		<script type="text/javascript">
			// 基于准备好的dom，初始化echarts实例
			var myChart3 = echarts.init(document.getElementById("fare3"));
			// 指定图表的配置项和数据
			<?php echo W('Echart/pie_echart',array('param' => $type3));?>
			// 使用刚指定的配置项和数据显示图表。
			myChart3.setOption(option);
		</script>
		<script type="text/javascript">
			// 基于准备好的dom，初始化echarts实例
			var myChart1 = echarts.init(document.getElementById("flow1"));
			// 指定图表的配置项和数据
			<?php echo W('Echart/pie_echart',array('param' => $type4));?>
			// 使用刚指定的配置项和数据显示图表。
			myChart1.setOption(option);
		</script>
		<script type="text/javascript">
			// 基于准备好的dom，初始化echarts实例
			var myChart2 = echarts.init(document.getElementById("flow2"));
			// 指定图表的配置项和数据
			<?php echo W('Echart/pie_echart',array('param' => $type5));?>
			// 使用刚指定的配置项和数据显示图表。
			myChart2.setOption(option);
		</script>
		<script type="text/javascript">
			// 基于准备好的dom，初始化echarts实例
			var myChart3 = echarts.init(document.getElementById("flow3"));
			// 指定图表的配置项和数据
			<?php echo W('Echart/pie_echart',array('param' => $type6));?>
			// 使用刚指定的配置项和数据显示图表。
			myChart3.setOption(option);
		</script>
		-->
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

	
</body>
</html>