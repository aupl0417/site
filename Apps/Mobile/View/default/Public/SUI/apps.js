function imgwh(imgurl,w,h){
	var url = imgurl + '?imageMogr2/thumbnail/!'+w+'x'+h+'r/gravity/Center/crop/'+w+'x'+h;
	return url;
}

template.helper('score', function (ratio,price) {
	var res = ratio * price * 100;
	return res.toFixed(0);
});

template.helper('imgwh', function (url,w,h) {
	if(url=='') url = 'http://img.trj.cc/1469193729000511.png';
	return imgwh(url,w,h);
});


//商品筛选条件选择
function search_isset(obj){
	obj.toggleClass('active');
	$('.select_options').html($('.panel').html());
}

//选择分类
function select_options(){
	$('.panel').html($('.select_options').html());
}

function select_category(obj){
	$('.panel').html($('.category').html());
}

function select_category_item(obj){
	var id = obj.data('id');
	var name = obj.find('.item-title').text();	
	$('.select_options .category_option .item-after').data('id',id).html(name);
	select_options();
}

//选择一级属性
function select_attr(obj){
	$('.panel').html($('.goods_attr').html());
}
//选择二级属性
function select_attr_data(obj){
	var id= obj.data('id');
	$('.panel').html($('.goods_attr_data [data-id="'+id+'"]').html());
}
//确认选择属性
function select_attr_item(obj){
	var id = obj.data('id');
	var name = obj.find('.item-title').text();	
	$('.select_options .category_attr .item-after').data('id',id).html(name);
	select_options();
}

//选择价格
function select_price(obj){
	$('.panel').html($('.price').html());
}
//确认价格
function select_price_item(obj){
	var id = obj.data('id');
	var name = obj.find('.item-title').text();	
	$('.select_options .category_price .item-after').data('id',id).html(name);
	select_options();
}

//选择一级城市
function select_province(obj){
	$('.panel').html($('.province').html());
}
//选择二级城市
function search_goods_select_city(obj){
	var id= obj.data('id');
	if(id==''){
		var name = obj.find('.item-title').text();	
		$('.select_options .category_city .item-after').data('id',id).html(name);
		select_options();	
	}else $('.panel').html($('.city [data-id="'+id+'"]').html());
}
//确认选择城市
function search_goods_select_city_item(obj){
	var id = obj.data('id');
	var name = obj.find('.item-title').text();	
	$('.select_options .category_city .item-after').data('id',id).html(name);
	select_options();
}

function search_goods(obj){
    $.closePanel();
	var cid 		= $('#page-search-goods').data('cid');
	var q			= $('#page-search-goods').data('q');
	if($('.select_options .category_city').size()>0) var city_id      = $('.select_options .category_city .item-after').data('id');
	if($('.select_options .category_option').size()>0) var category_id  = $('.select_options .category_option .item-after').data('id');
	if($('.select_options .category_attr').size()>0) var attr_id      = $('.select_options .category_attr .item-after').data('id');
	if($('.select_options .category_price').size()>0) var price_id     = $('.select_options .category_price .item-after').data('id');
	
	var is_self      = $('.btn-box [data-val="is_self"]').hasClass('active');
	var is_daigou    = $('.btn-box [data-val="is_daigou"]').hasClass('active');
	var free_express = $('.btn-box [data-val="free_express"]').hasClass('active');
	var score_ratio  = $('.btn-box [data-val="score_ratio"]').hasClass('active');


	var score_1		 = $('.btn-box [data-val="score_1"]').hasClass('active');
    var score_4		 = $('.btn-box [data-val="score_4"]').hasClass('active');
    var score_2		 = $('.btn-box [data-val="score_2"]').hasClass('active');
    var score_type   = score_1 ? 1 : (score_4 ? 4 : (score_2 ? 2 : false));
	cid = category_id ? category_id : cid;
	
	

	var min_price = "";
	var max_price = "";
	switch(price_id)
	{
	case 1:
	  max_price = 50;
	  break;
	case 2:
	  min_price = 50;
	  max_price = 200;
	  break;
	case 3:
	  min_price = 200;
	  max_price = 500;
	  break;
	case 4:
	  min_price = 500;
	  break;
	}

	var url="/Search/goods"+(cid ? '/cid/'+cid : '')+(city_id ? '/city_id/'+city_id : '')+(attr_id ? '/option/'+attr_id : '')+
	(min_price ? '/min_price/'+min_price : '')+(max_price ? '/max_price/'+max_price : '')+(is_self ? '/is_self/1' : '')+(is_daigou ? '/is_daigou/1': '')+
	(free_express ? '/free_express/1' : '')+(score_ratio ? '/score_ratio/2' : '')+(score_type ? '/score_type/'+score_type : '')+(q ? '/q/'+q : '');
	
	//$.alert(url);
	$.router.load(url,true);
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

/**
 * 猜您喜欢 
 */
function love(tag,obj){
	var where = {};
	if(obj.data('cid')) where.cid = obj.data('cid');
	if(obj.data('num')) where.num = obj.data('num');
	$.get('/Goods/love',where,function(ret){
		if(ret.code == 1){
			var html = template('tpl_love_goods',{data:ret.data});
			$(tag).html(html);
		}
	});
}

/**
 * 选择城市
 */
function select_city(obj){		
	obj.closest('li').addClass('active').siblings().removeClass('active');
	$('.popup-district ul').html('');			
	$.get('/City/city_item/sid/'+obj.data('id'),function(ret){
		if(ret.code == 1){
			var html = template('tpl_district_item',{data:ret.data});
			$('.popup-district ul').html(html);
		}else $.toast("读取城市列表失败！");
	});
}
/**
 * 商品详情选择城市
 */
function select_city2(obj){		
	obj.closest('li').addClass('active').siblings().removeClass('active');
}

function select_district(obj){	
	obj.closest('li').addClass('active').siblings().removeClass('active');
	$('.popup-town ul').html('');			
	$.get('/City/city_item/sid/'+obj.data('id'),function(ret){
		if(ret.code == 1){
			var html = template('tpl_town_item',{data:ret.data});
			$('.popup-town ul').html(html);
		}else $.toast("读取城市列表失败！");
	});
}

/**
 *------------------------------------ 
 * 确定选择地址
 * -----------------------------------
 * create by Lazycat
 * -----------------------------------
 * 2017-01-18
 * -----------------------------------
 */
function select_city_item(obj){
	obj.closest('li').addClass('active').siblings().removeClass('active');
}

function select_city_ok(){
	var city_name = '';
	var popup = '.popup-province,.popup-city,.popup-district';
	$('input[name="province"]').val('');
	$('input[name="city"]').val('');
	$('input[name="district"]').val('');
	$('input[name="town"]').val('');
	if($('.popup-province ul li.active').size()>0){
		$('input[name="province"]').val($('.popup-province ul li.active .item-link').data('id'));
		city_name = $('.popup-province ul li.active .item-title').text();
	}

	if($('.popup-city ul li.active').size()>0){
		$('input[name="city"]').val($('.popup-city ul li.active .item-link').data('id'));
		city_name += ' ' + $('.popup-city ul li.active .item-title').text();
	}
	
	if($('.popup-district ul li.active').size()>0){
		$('input[name="district"]').val($('.popup-district ul li.active .item-link').data('id'));
		city_name += ' ' + $('.popup-district ul li.active .item-title').text();
	}else{
		$.toast('请选择区、县！');
		return false;
	}

	if($('.popup-town ul li.active').size()>0){
		$('input[name="town"]').val($('.popup-town ul li.active .item-link').data('id'));
		city_name += ' ' + $('.popup-town ul li.active .item-title').text();
		popup += ',.popup-town';
	}

	$('input[name="city_name"]').val(city_name);
	
	$.closeModal(popup);
}

/**
 *------------------------------------ 
 * 商品详情确定选择地址2
 * -----------------------------------
 * create by lizuheng
 * -----------------------------------
 * 2017-04-01
 * -----------------------------------
 */

function select_city_ok2(){
	var city_name = '';
	var popup = '.popup-province,.popup-city,.popup-district';
	$('input[name="province"]').attr('value','');
	$('input[name="city"]').attr('value','');
	if($('.popup-province ul li.active').size()>0){
		$('input[name="province"]').val($('.popup-province ul li.active .item-link').data('id'));
		city_name = $('.popup-province ul li.active .item-title').text();
	}

	if($('.popup-city ul li.active').size()>0){
		$('input[name="city"]').val($('.popup-city ul li.active .item-link').data('id'));
		city_name += ' ' + $('.popup-city ul li.active .item-title').text();
	}

	var city_id = $('.popup-city ul li.active .item-link').data('id');
	var express_id = $('#express_tpl_id').val();

	if(!city_id){
		$.toast('请选择城市！');
		return false;
	}
	$.post('/Goods/get_express_price',{city:city_id,tid:express_id},function(ret){
		if(ret.code == 1){
			var express_price;
			if(ret.data.express){
				express_price = '快递：'+ret.data.express+'元<span class="pl05"></span>';
			}
			if(ret.data.ems){
				express_price += 'EMS：'+ret.data.ems+'元';
			}
			$(".popup-express .express_price").html(express_price);
			$(".popup-express .express_address").html(city_name);
			$.closeModal(popup);
		}else $.toast('操作失败！');
	});
}

/**
 *------------------------------------ 
 * 取消商品关注
 * -----------------------------------
 * create by Lazycat
 * -----------------------------------
 * 2017-01-18
 * -----------------------------------
 */
function cancel_fav_goods(obj){    	
		    var buttons1 = [{
		      	text: '请选择',
		      	label: true
		    }, {
		      	text: '浏览商品',
		      	onClick: function() {
		      		$.router.load(obj.prev().find('a').attr('href'));
		      	}
		    }, {
		      	text: '取消关注',
		      	color: 'danger',		      	
		      	onClick: function() {
		      		$.confirm('确定要取消关注吗?', function () {
			        	$.post('/FavGoods/delete',{id:obj.data('id')},function(ret){
			        		if(ret.code == 1){
			        			$.toast('已取消关注！');
			        			$('#fav_goods_'+obj.data('id')).remove();
			        		}else $.toast('操作失败！');
			        	});
			      	});
		      	}
		    }];
		     var buttons2 = [{
		      	text: '取消',
		      	bg: 'danger'
		    }];
		    var groups = [buttons1, buttons2];
		    $.actions(groups);		
}

/**
 *------------------------------------ 
 * 取消店铺关注
 * -----------------------------------
 * create by Lazycat
 * -----------------------------------
 * 2017-01-18
 * -----------------------------------
 */
function cancel_fav_shop(obj){    	
		    var buttons1 = [{
		      	text: '请选择',
		      	label: true
		    }, {
		      	text: '进入店铺',
		      	onClick: function() {
		      		$.router.load(obj.prev().find('a').attr('href'));
		      	}
		    }, {
		      	text: '取消关注',
		      	color: 'danger',		      	
		      	onClick: function() {
		      		$.confirm('确定要取消关注吗?', function () {
			        	$.post('/FavShop/delete',{id:obj.data('id')},function(ret){
			        		if(ret.code == 1){
			        			$.toast('已取消关注！');
			        			$('#fav_shop_id_'+obj.data('id')).remove();
			        		}else $.toast('操作失败！');
			        	});
			      	});
		      	}
		    }];
		     var buttons2 = [{
		      	text: '取消',
		      	bg: 'danger'
		    }];
		    var groups = [buttons1, buttons2];
		    $.actions(groups);		
}

/**
 * 删除评价晒图照片
 * Create by Lazycat
 * 2017-02-21
 */
function rate_images_remove(obj){
	$.confirm('确定要删除照片吗?', function () {
		var upobj = obj.closest('.upload-box');
		obj.remove();
		
		var images = new Array();
		upobj.find('.images-list img').each(function(index){
			images[index] = $(this).data('images');
		});
		upobj.find('#images').val(images.join(','));		
	});	
}


/**
 *------------------------------------ 
 * 商品评价 - 评分选项
 * -----------------------------------
 * create by Lazycat
 * -----------------------------------
 * 2017-02-21
 * -----------------------------------
 */
function rate_point_option(obj){    	
		    var buttons1 = [{
		      	text: '<img src="/Apps/Mobile/View/default/Public/images/rate-5.png" style="padding-top:0.5rem;">',
		      	onClick: function() {
		      		obj.find('.item-after').html('5分');
		      		obj.find(obj.data('tag')).val(5);
		      	}
		    }, {
		      	text: '<img src="/Apps/Mobile/View/default/Public/images/rate-4.png" style="padding-top:0.5rem;">',      	
		      	onClick: function() {
					obj.find('.item-after').html('4分');
					obj.find(obj.data('tag')).val(4);
		      	}
		    }, {
		      	text: '<img src="/Apps/Mobile/View/default/Public/images/rate-3.png" style="padding-top:0.5rem;">',      	
		      	onClick: function() {
					obj.find('.item-after').html('3分');
					obj.find(obj.data('tag')).val(3);
		      	}
		    }, {
		      	text: '<img src="/Apps/Mobile/View/default/Public/images/rate-2.png" style="padding-top:0.5rem;">',	      	
		      	onClick: function() {
					obj.find('.item-after').html('2分');
					obj.find(obj.data('tag')).val(2);
		      	}
		    }, {
		      	text: '<img src="/Apps/Mobile/View/default/Public/images/rate-1.png" style="padding-top:0.5rem;">',      	
		      	onClick: function() {
					obj.find('.item-after').html('1分');
					obj.find(obj.data('tag')).val(1);
		      	}
		    }];
		     var buttons2 = [{
		      	text: '取消',
		      	bg: 'danger'
		    }];
		    var groups = [buttons1, buttons2];
		    $.actions(groups);		
}


/**
 * -------------------------
 * 发验证码倒计时
 * -------------------------
 * Create by Lazycat
 * -------------------------
 * 2017-01-19
 * -------------------------
 * 要先定义变量 var smscode_timer用于清除计时器
 */

function interval_smscode(obj){
	var dec_time = parseInt(obj.find('.dec_time').html());
	if(dec_time > 1){
		dec_time--;
		obj.find('.dec_time').html(dec_time);		
	}else{
		var timer = obj.data('timer');
		clearInterval(timer);	//停止计时器
		obj.addClass('active').html('发送验证码');
	}		
}

//顶部右上角快捷菜单
function top_menu(){
	$('.fixed-menu').toggleClass('hide');
	if($('.category-menu').size() > 0){
		if($('.category-menu').hasClass('hide') == false) $('.category-menu').toggleClass('hide');
	}
}
//商品搜索时候的分类菜单
function category_menu(){
	$('.category-menu').toggleClass('hide');
}
//购物车商品编辑
function edit_goods(obj){
	var type = obj.attr("type");
	if(type==1){
		obj.html("完成");
		obj.attr("type",2);
	}else{
		obj.html("编辑");
		obj.attr("type",1);
	}
	obj.closest("ul").find('li .edit-goods').toggleClass('hide');
}


/**
 * 订购商品数量+-
 * Create by Lazycat
 * 2017-02-13
 */
function sku_num_dec(obj){
	var num = parseInt(obj.closest('.item-input').find('#num').val());
	num--;
	
	if(num<1) num = 1;	
	obj.closest('.item-input').find('#num').val(num);
}

function sku_num_inc(obj){
	var num = parseInt(obj.closest('.item-input').find('#num').val());
	num++;
	
	var max = obj.closest('.item-input').find('#num').data('max_num');
	
	if(num>max) num = max;	
	obj.closest('.item-input').find('#num').val(num);
}
/**
 * 购物车商品数量+-
 * Create by lizuheng
 * 2017-02-20
 */
function cart_num_dec(obj){
	var goods_num = parseInt(obj.closest('li').find('#num').val());
	var attr_id = parseInt(obj.closest('li').find('#attr_list_id').val());

	goods_num--;
	if(goods_num>0){
		$.post('/Cart/dec_num',{id:attr_id},function(ret){
			if(ret.code == 1){
				obj.closest('li').find('.cart_goods_num').html('x'+goods_num);
				obj.closest('li').find('#num').val(goods_num);
				cart_total_price();
			}
		});
	}
}

function cart_num_inc(obj){
	var goods_num = parseInt(obj.closest('li').find('#num').val());
	var attr_id = parseInt(obj.closest('li').find('#attr_list_id').val());
	goods_num++;
	
	$.post('/Cart/add_num',{id:attr_id},function(ret){
		if(ret.code == 1){
			obj.closest('li').find('.cart_goods_num').html('x'+goods_num);
			obj.closest('li').find('#num').val(goods_num);
			cart_total_price();
		}
	});
}
/**
 * 购物车设置商品数量
 * Create by lizuheng
 * 2017-02-20
 */
function cart_set_num(obj){
	var goods_num = parseInt(obj.closest('li').find('#num').val());
	var attr_id = parseInt(obj.closest('li').find('#attr_list_id').val());
	
	$.post('/Cart/set_num',{id:attr_id,num:goods_num},function(ret){
		if(ret.code == 1){
			obj.closest('li').find('.cart_goods_num').html('x'+goods_num);
			obj.closest('li').find('#num').val(goods_num);
			cart_total_price();
		}
	});
}
/**
 * 购物车删除商品
 * Create by lizuheng
 * 2017-02-20
 */
function delete_cart_goods(obj){   
	var id = parseInt(obj.closest('li').find('#cart_id').val()); 	
	$.confirm('确定从购物车删除该商品吗?', function () {
		$.post('/Cart/delete',{ids:id},function(ret){
			if(ret.code == 1){
				var ul = obj.closest('ul').children().length;
				if(ul>2){
					obj.closest('li').remove();	
				}else{
					obj.closest('ul').remove();
				}
				cart_total_price();
			}else $.toast('操作失败！');
		});
	});	
}

/**
 * 购物车价格计算
 * Create by lizuheng
 * 2017-02-20
 */
function cart_total_price(){
	var page = $("#page-cart");
	var style_num = page.find('input[type="checkbox"].check-goods:checked').size();
	$('.cart-footer .num').html(style_num);
	
	var size = page.find('input[type="checkbox"].check-goods:checked').size();
	var total_price = 0;
	var total_score = 0;
	
	if(size > 0){
		page.find('input[type="checkbox"].check-goods:checked').each(function(){
			var data = $(this).closest('li').data();
			var price = $(this).closest('li').find("#price").val();
			var num = $(this).closest('li').find("#num").val();
			
			var total = parseFloat(price*num);

			total_price+=total;
			total_score =total_price * data.score_ratio * 100;
		
			$('.cart-footer .total_price').html(total_price.toFixed(2));
			$('.cart-footer .total_score').html(total_score.toFixed(2));
		});
	}else{
		$('.cart-footer .total_price').html(total_price.toFixed(2));
		$('.cart-footer .total_score').html(total_score.toFixed(2));
	}
}

/**
 * 设置通知为已读和未读
 * Create by lizuheng
 * 2017-02-21
 */
function set_read(obj){
	var notice_id = obj.attr('ids');
	var type      = obj.attr('type');
	$.post('/Notice/set_read',{id:notice_id,is_read:type},function(ret){
		if(ret.code == 1){
			$.toast('设置成功！');
			if(type==1){
				$('[data-id="'+notice_id+'"]').find('.yes_read').html("标记为已读");
				$('[data-id="'+notice_id+'"]').find('.yes_read').attr('type',1);		
				$('[data-id="'+notice_id+'"]').find('.card-header').html("未阅读");	
				$('[data-id="'+notice_id+'"]').find('.yes_read').removeClass('yes_read').addClass('no_read');
			}else{
				$('[data-id="'+notice_id+'"]').find('.no_read').html("标记为未读");
				$('[data-id="'+notice_id+'"]').find('.no_read').attr('type',0);	
				$('[data-id="'+notice_id+'"]').find('.card-header').html("已阅读");	
				$('[data-id="'+notice_id+'"]').find('.no_read').removeClass(no_read).addClass('yes_read');	
			}
		}else $.toast('操作失败！');
	});
}
/**
 * 删除通知
 * Create by lizuheng
 * 2017-02-21
 */
function delete_notice(obj){
	var notice_id = obj.attr('ids');
	$.confirm('确定删除该消息吗?', function () {
		$.post('/Notice/delete_notice',{id:notice_id},function(ret){
			$.toast(ret.msg);
			if(ret.code == 1){	
				$('[data-id="'+notice_id+'"]').remove();
			}
		});
	});
}

/**
 * 售后服务收到商品
 * Create by lizuheng
 * 2017-02-28
 */
function service_accept(obj){
	var rid = obj.attr('data-r_no');
	var sid = obj.attr('data-s_no');

	$.modal({
		title:  '请输入安全密码',
		text: '<input type="password" class="modal-text-input" name="password_pay">',
		buttons: [
			{
				text: '取消',
			},
			{
				text: '确定',
				onClick: function(){
					var value =  $(".modal-text-input").val();

					if(!value){
						$.toast('请输入安全密码！');
						return false;
					}
					$.post('/Service/service_accept',{r_no:rid,s_no:sid,password_pay:value},function(ret){
						$.toast(ret.msg);
						if(ret.code == 1){
							window.location.reload();
						}
					});  
				}
			},
		]
	})
}

/**
 * 选择属性
 * Create by Lazycat
 * 2017-02-13
 */
function change_attr(obj){
	if(obj.hasClass('not')) {
		//$.toast('该属性的商品库存不足！');
		return;
	}
	
	obj.addClass('selected').siblings().removeClass('selected');
	var attr = new Array();
	obj.closest('.sku-box').find('.price-btn .selected').each(function(index){
		attr[index] = $(this).data('attr_id');
	});
	
	//$.alert(attr.join(','));
	var attr_id = attr.join(',');
	var goods_id = obj.data('goods_id');
	//$.alert(attr_id);
	$.post('/Goods/change_attr',{goods_id:goods_id,attr_id:attr_id},function(ret){
		if(ret.code == 1){
			obj.closest('.sku-box').html(ret.html);
		}else{
			$.alert('读取库存属性失败！');
		}
	});
}


/**
 * 加入购物车/立即订购
 * Create by Lazycat
 * 2014-02-14
 */
function add_to_cart(obj){
	var data = obj.data();
	$.post('/Cart/add',data,function(ret){
		$.toast(ret.msg);
		if(ret.code == 1){
			$('.bar-tab .icon-gouwuche1').html('<span class="badge">'+ret.data.style_num+'</span>');
			if(data.atonce==1) $.router.load('/Cart/selected_goods',true);
		}else if(ret.code == 10){
			$.router.load('/Login',true);
		}
	});
}

/**
 * 选择商品属性加入购物车
 * Create by Lazycat
 * 2017-02-18
 */
function select_to_cart(obj){
	var t = obj.data();
	var data = getFormJson('#form_sku_attr');
	//$.alert(data.attr_list_id);
	if(data.num <1){
		$.toast('请输入订购数量！');
		return false;
	}
	
	if(data.attr_list_id == ''){
		$.toast('库存属性有错误！');
		return false;
	}
	
	if(t.atonce == 1) data.atonce = 1;

	//console.log(data);
	$.post('/Cart/add',data,function(ret){
		$.toast(ret.msg);
		if(ret.code == 1){
			$('.bar-tab .icon-gouwuche1').html('<span class="badge">'+ret.data.style_num+'</span>');
			if(data.atonce==1) {
				$.closeModal('.popup-attr');
				$.router.load('/Cart/selected_goods',true);
			}
		}else if(ret.code == 10){
			$.closeModal('.popup-attr');
			$.router.load('/Login',true);
		}		
	});
}



/**
 * 退款商品数量+－
 * Create by Lazycat
 * 2017-02-27
 */
function refund_num_dec(obj){
	var num 		= parseInt(obj.closest('.item-input').find('input').val());
	var max 		= obj.closest('.item-input').find('input').data('max');
	var unit_price 	= obj.closest('.item-input').find('input').data('unit_price');
	
	if(!num) num = 1;
	
	num--;
	if(num < 1) num = 1;
	if(num > max) num = max;
	obj.closest('.item-input').find('input').val(num);	
	
	var price = num * unit_price;
	$('.refund_price').html(price.toFixed(2));
}

function refund_num_inc(obj){
	var num 		= parseInt(obj.closest('.item-input').find('input').val());
	var max 		= obj.closest('.item-input').find('input').data('max');
	var unit_price 	= obj.closest('.item-input').find('input').data('unit_price');
	var can_price	= obj.closest('.item-input').find('input').data('can_price');
	if(!num) num = 1;
	
	num++;
	if(num < 1) num = 1;
	if(num > max) num = max;
	obj.closest('.item-input').find('input').val(num);	
	
	var price =num * unit_price;
	if(num == max && price != can_price) price = can_price;
	$('.refund_price').html(price.toFixed(2));
}
/**
 * 退款商品设置商品数量
 * Create by lizuheng
 * 2017-03-9
 */
function refund_set_num(obj){
	var num 		= obj.val();
	var can_num 	= obj.attr("data-max");
	var unit_price 	= obj.attr("data-unit_price");
	var can_price	= obj.closest('.item-input').find('input').data('can_price');
	if(!parseInt(num)){
		$.toast('商品数量只能是整数');
		return false;
	}
	if(num>can_num){
		$.toast('退回数量大于可退的商品数量');
		obj.val(can_num);
		return false;
	}
	var price =num * unit_price;
	if(num == can_num && price != can_price) price = can_price;
	$('.refund_price').html(price);
}
/**
 * 从cookie中获取历史url
 * Create by Lazycat
 * 2017-03-01
 */

function get_cookie_url(n){
	var urls 	= $.fn.cookie('history');
	if(urls) 	var urls = urls.split(',');
	else return;
	
	var size 	= urls.length;
	if(n == undefined) n = size-2;	
	return urls[n];
}

function goback(nocache){
	if(nocache == undefined){
		nocache = false;
	}
	
	if(nocache){
		var url = get_cookie_url();
		if(url) $.router.load(url,true);
		else $.router.back();
	}else $.router.back();
}


/**
 * 领取优惠券
 * Create by Lazycat
 * 2017-03-13
 */
function get_coupon(obj){
	var data = obj.data();
	$.post('/Coupon/get_coupon',data,function(ret){
		//console.log(ret);
		if(ret.code == 10) {
			$.closeModal();
			$.router.load('/Login');
		} else {
			$.alert(ret.msg);
		}		
	});
}

/**
 * 商家消息
 * Create by Lzy
 * 2017-03-28
 */
function sellerMessageInfo(param){
	
	param.goods_id = param.goods_id || 0;
	param.nick = param.nick || '';
	param.shop_id = param.shop_id || 0;
	
	/*
	var params = new Array();
	$.each(param,function(key,value){
		params.push(key);
		if(value == ''){
			value = '0';
		}
		params.push(value);
	});
	$.router.load('/Message/seller_message_info/' + params.join('/'),true);
	*/
	
	
	$.router.load('/Shop/contact/shop_id/'+param.shop_id);
}

/**
 * 进入店铺
 */
function intoShop(shopid){
	$.router.load('/shop/index/shop_id/' + shopid);
}

/**
 * 秒杀进行中 - 倒计时
 * Create by Lazycat
 * 2017-03-30
 */
function miaosha_in_dec(time_dif){
	time_dif += 86400;
	var twoDaysFromNow = new Date().valueOf() + time_dif * 1000;
	$('#clock').countdown(twoDaysFromNow, function(event) {
		var totalHours = event.offset.totalDays * 24 + event.offset.hours;
		if(event.strftime(totalHours + ' %M %S') == '0 00 00') { //活动结束
			$(this).html('活动结束');
			$('.bar .goods-footer .cart-btn,.popup-attr .goods-footer .cart-btn').html('<div class="btn-buy-over">秒杀结束</div>');
		} else {
			$(this).html(event.strftime('<i class="iconfont icon-miaosha"></i> 还剩  <span class="time-box">' + totalHours + '</span> : <span class="time-box">%M</span> : <span class="time-box">%S</span>'));
		}
	});
}
/**
 * 秒杀即将开始 - 倒计时
 * Create by Lazycat
 * 2017-03-30
 */
function miaosha_wait_dec(time_dif,attr_list_id){
	var twoDaysFromNow = new Date().valueOf() + time_dif * 1000;
	
	var end = 0;	//由于结束后会执行2次，所加上计时器方便跳转
	$('#clock').countdown(twoDaysFromNow, function(event) {
		var totalHours = event.offset.totalDays * 24 + event.offset.hours;
		if(event.strftime(totalHours + ' %M %S') == '0 00 00') { //活动结束
			end++;
			$(this).html('<i class="iconfont icon-miaosha"></i> 还剩  <span class="time-box">23</span> : <span class="time-box">59</span> : <span class="time-box">59</span>');
			$('.bar .goods-footer .cart-btn,.popup-attr .goods-footer .cart-btn').html('<div class="btn-buy" data-attr_list_id="'+attr_list_id+'" data-num="1" data-atonce="1" onclick="add_to_cart($(this))">立即购买</div>');
			if(end == 2) miaosha_in_dec(0);
		} else {
			$(this).html(event.strftime('<i class="iconfont icon-miaosha"></i> 还剩  <span class="time-box">' + totalHours + '</span> : <span class="time-box">%M</span> : <span class="time-box">%S</span>'));
		}
	});	
}

function miaosha_time_dec(time_dif,subject){
	if(subject == undefined) subject = ' 还剩 ';
	var twoDaysFromNow = new Date().valueOf() + time_dif * 1000;
	$('#clock').countdown(twoDaysFromNow, function(event) {
		var totalHours = event.offset.totalDays * 24 + event.offset.hours;
		if(event.strftime(totalHours + ' %M %S') == '0 00 00') { //活动结束
			$(this).html('活动结束');			
		} else {
			$(this).html(event.strftime('<i class="iconfont icon-miaosha"></i> '+subject+'  <span class="time-box">' + totalHours + '</span> : <span class="time-box">%M</span> : <span class="time-box">%S</span>'));
		}
	});
}

/**
 * Iscroll点击兼容
 * Create by Lazycat
 * 2017-04-02
 */
function iScrollClick(){
	if (/iPhone|iPad|iPod|Macintosh/i.test(navigator.userAgent)) return false;
	if (/Chrome/i.test(navigator.userAgent)) return (/Android/i.test(navigator.userAgent));
	if (/Silk/i.test(navigator.userAgent)) return false;
	if (/Android/i.test(navigator.userAgent)) {
	   var s=navigator.userAgent.substr(navigator.userAgent.indexOf('Android')+8,3);
	   return parseFloat(s[0]+s[3]) < 44 ? false : true
    }
}

/**
 * 关闭抽奖结果
 * Create by Mercury
 * 2017-05-10
 */
function closeAlertLuckdraw(that) {
    $("#page-drawluck-item").attr('data-click', 1);
	that.closest('.alert-luckdraw-back').css('display','none');
}

/**
 * 购买次数
 * Create by Mercury
 * 2017-05-10
 */
function luckdrawBuy(id, position) {
	//if(is_stop== true) return;
    $("#page-drawluck-item").attr('data-click', 0);
    $('.alert-luckdraw-back').css('display', 'none');
	$.post('/drawluck/buy', {id:id}, function (ret) {
		if (ret.code == 1) {
			draw.goto(ret.data.sort, ret.data);
		} else if (ret.code == 0) {	//操作失败
            $("#page-drawluck-item").attr('data-click', 1);
			$.toast(ret.msg);
		} else if(ret.code == 401) {
            $.router.load('/Ucenter',true);
        } else if (ret.code == 402) {
            $("#page-drawluck-item").attr('data-click', 1);
            $.toast(ret.msg);
            $.router.load('/User/set_safe_password',true);
        }
	}, 'json');

    var draw = new turntableDraw('.drawBtn',{
        share:position,
        speed:"3s",
        velocityCurve:"ease",
        weeks:6,
        callback:function(num, data)
        {
            luckdrawCallback(data);
        },
    });
}

/**
 * 购买次数
 * Create by Mercury
 * 2017-05-10
 */
function reLuckdraw(id, position) {
    $('.alert-luckdraw-back').css('display', 'none');
    $("#page-drawluck-item").attr('data-click', 0);
    $.post('/drawluck/post', {id:id}, function (ret) {
        if (ret.code == 1) {
            draw.goto(ret.data.sort, ret.data);
        } else if (ret.code == 0) {	//操作失败
            $("#page-drawluck-item").attr('data-click', 1);
            $.toast(ret.msg);
        } else if (ret.code == -1) {	//次数不够
            var h = template('tpl_luckdraw_buy', {
                data: ret.data
            });
            $('.alert-luckdraw-back').css('display','block').html(h);
        } else if(ret.code == 401) {
            $.router.load('/Ucenter',true);
        } else if (ret.code == 402) {
            $("#page-drawluck-item").attr('data-click', 1);
            $.toast(ret.msg);
            $.router.load('/User/set_safe_password',true);
		}
    }, 'json');

    var draw = new turntableDraw('.drawBtn',{
        share:position,
        speed:"3s",
        velocityCurve:"ease",
        weeks:6,
        callback:function(num, data)
        {
            luckdrawCallback(data);
        },
    });
}

/**
 * 抽奖callback
 * Create by Mercury
 * 2017-05-10
 *
 * @param data	数据
 */
function luckdrawCallback(data)
{
    var h = template('tpl_luckdraw_alert', {
        data: data
    });
    switch (data.prize_id) {
        case 1:			//代金券
            break;
        case 2:			//商家优惠券
            break;
        case 3:			//奖励积分
            break;
        case 4:			//未中奖
            break;
    }
    $('.alert-luckdraw-back').css('display','block').html(h);
    //$(".drawBtn").bind('click');
	$("#page-drawluck-item").attr('data-click', 1);
}

/**
 * 领取中奖信息
 * @param id
 * @param that
 */
function receiveScore(id, that) {
    $.post('/drawluck/receiveScore', {id:id}, function (ret) {
        if (ret.code == 1) {
            $.toast(ret.msg);
			that.html('已发放').removeClass('button-success').addClass('disabled');
        } else if (ret.code == 0) {	//操作失败
            $.toast(ret.msg);
        } else if(ret.code == 401) {
            $.router.load('/Ucenter',true);
        }
    }, 'json');
}


/**
 * 返回顶部
 * Create by liangfeng
 * 2017-05-22
 */
function backToTop(){
	$('.content').scrollTop(0);
}

/**
 * 判断手机号运营商
 */
function mobile_operator(mobile){
	//移动号码段：150,151,152,157,158,159,134,135,136,137,138,139,187,188,147
	//联通：130,131,132,155,156,186,185
	//电信：133,153,189,180
	var t1 = /^((150)|(151)|(152)|(157)|(158)|(159)|(134)|(135)|(136)|(137)|(138)|(139)|(187)|(188)|(147)|(182)|(183)|(184)|(178))/i;
	var t2 = /^((130)|(131)|(132)|(155)|(156)|(186)|(185)|(176))/i;
	var t3 = /^((133)|(153)|(189)|(180)|(181)|(177))/i;
	
	var code = "chinamobile"; 
	if(t1.test(mobile)) code = "chinamobile";
	else if(t2.test(mobile)) code = "unicom";
	else if(t3.test(mobile)) code = "telecom";
	
	return code;
}

function pageBack() {
    $.closeModal();
    $.closePanel();
}

$(function () {
  	'use strict';
	
	/**
	 * 记录最近5条历史记录
	 * Create by lazycat
	 * 2017-03-01
	 */
	$(document).on("pageInit", ".page", function(e, id, page) {
		//$.fn.cookie

        window.addEventListener("popstate", function(e) {
            pageBack();
        }, false);

        var url = page[0].baseURI;
		var urls = $.fn.cookie('history');
		if(!urls){
			var urls = new Array();			
		}else{
			urls = urls.split(',');
		}
		
		//只记录最近5条
		var size = urls.length;
		if(size > 5){
			for(var i=0;i<size-5;i++){
				urls.shift();
			}
		}
		
		urls.push(url);
		$.fn.cookie('history',urls.join(','));		
		//$.alert(get_cookie_url());		
		//console.log(urls);
		
		//$('input[type="password"]').val('');
	});
	
	/**
	* -----------------------------------------
	* 首页猜您喜欢
	* -----------------------------------------
	* Create by Lazycat
	* -----------------------------------------
	* 2017-01-21
	* -----------------------------------------
	**/
  	$(document).on("pageInit", "#page-home", function(e, id, page) {	
		var loading = false;
	  	var last_page = 1;
	  	var page_count = page.data('page_num');
	  	var max_page = page_count > 100 ? 100 : page_count; //最多100页 
	  	var url = page.data('url');
	  	var tpl = page.data('tpl');
	  	 	
	  	function addItems(p) {
	  		$.get(url, {p:p},function(ret) {
	  			if(ret.code ==1){	  			
		  			var html = template(tpl, {
		  				data: ret.data.list
		  			});
		  			$('.infinite-scroll .list-container').append(html);	  			
	  			}
	  		});
	
	  	}

        /**
		 * 判断滚动条位置
         */
        $(".content").on('scroll',function(){
			var scrollPosition = $('.content').scrollTop();
			if (scrollPosition < 200) {
				$(".index-bar-nav").css({"background":"rgba(255, 255, 255, 0)"});
			} else {
                $(".index-bar-nav").css({"background":"#fbd439"});
			}
        });

		$('.infinite-scroll-preloader').hide();
	
	  	$(page).on('infinite', function() {
	  		var last_page = parseInt($('#p').val());
	  		// 如果正在加载，则退出
	  		if(loading) return;
	  		// 设置flag
	  		loading = true;
	  		$('.infinite-scroll-preloader').show();
	  		// 模拟1s的加载过程
	  		setTimeout(function() {
	  			// 重置加载flag
	  			loading = false;
	  			if(last_page > max_page) {
	  				// 加载完毕，则注销无限加载事件，以防不必要的加载
	  				$.detachInfiniteScroll($('.infinite-scroll'));
	  				// 删除加载提示符
	  				$('.infinite-scroll-preloader').hide();
	  				return;
	  			}
	  			addItems(last_page);
	  			// 更新最后加载的序号
	  			last_page++;
	  			$('#p').val(last_page);
	  			$.refreshScroller();
	  		}, 1000);
	  	});
	  	
	  	//秒杀
	  	//var data = $('.miaosha-time-box3').data();
		//miaosha_time_dec(data.time_dif,data.active==1?'距本场结束':'距本场开始');
		
		// if($('.iscroll-home-miaosha').size() > 0){
	     //    var myScroll = new IScroll('.iscroll-home-miaosha', {
	     //        scrollX: true,
	     //        scrollY: false,
	     //        click:iScrollClick(),
	     //        mouseWheel: true,
	     //        preventDefault: false
	     //    });
		// }
  	});		
	
	/**
	* -----------------------------------------
	* 会员登录
	* -----------------------------------------
	* Create by Lazycat
	* -----------------------------------------
	* 2017-01-14
	* -----------------------------------------
	**/	
	$(document).on("pageInit", "#page-login", function(e, id, page) {
		$('.btn-login').click(function() {
			var data = getFormJson('#form-login');
			if(data.username == '') {
				$.toast("请输入昵称或手机号码！");
				return false;
			}
			if(data.password == '') {
				$.toast("请输入密码！");
				return false;
			}
			if($(".code").attr("data_id") == 1){
				if(data.code == '') {
					$.toast("请输入验证码！");
					return false;
				}
			}
			
			$.post('/Login/check_login', data, function(ret) {
				if(ret.code == 1) location.href='/Ucenter';
				else if(ret.code == 2){
					$.toast(ret.msg);
				}else{
					$.toast(ret.msg);
					$(".code").removeClass('hide');
					$(".code").attr('data_id',1);
					var d = "/Login/code/h/40";
					$('.verify img').attr('src',d + '?id=' +  + Math.random());
				}
			});
	
		});
	
		$(".verify").click(function() {
			var d = "/Login/code/h/40";
			$('.verify img').attr('src',d + '?id=' +  + Math.random());
		})
	});		
	

	/**
	* -----------------------------------------
	* 关注的商品
	* -----------------------------------------
	* Create by Lazycat
	* -----------------------------------------
	* 2017-01-18
	* -----------------------------------------
	**/
  	$(document).on("pageInit", "#page-fav-goods", function(e, id, page) {	
  		//if($('#p').val() == 2) page.find('.content').scrollTop(0);
  		
		var loading = false;
	  	var last_page = 1;
	  	var page_count = page.data('page_num');
	  	var max_page = page_count > 100 ? 100 : page_count; //最多100页 
	  	var url = page.data('url');
	  	var tpl = page.data('tpl');
	  	
	  	function addItems(p) {
	  		var where = {
	  			p: p
	  		};
	  		$.get(url, where, function(ret) {
	  			var html = template(tpl, {
	  				data: ret.data.list
	  			});
	  			$('.infinite-scroll .list-container').append(html);
	  			//alert(ret);
	  		});
	
	  	}
	
	  	$('.infinite-scroll-preloader').hide();
	  	
	  	$(page).on('infinite', function() {
	  		var last_page = parseInt($('#p').val());
	  		// 如果正在加载，则退出
	  		if(loading) return;
	  		// 设置flag
	  		loading = true;
	  		$('.infinite-scroll-preloader').show();
	  		// 模拟1s的加载过程
	  		setTimeout(function() {
	  			// 重置加载flag
	  			loading = false;
	  			if(last_page > max_page) {
	  				// 加载完毕，则注销无限加载事件，以防不必要的加载
	  				$.detachInfiniteScroll($('.infinite-scroll'));
	  				// 删除加载提示符
	  				$('.infinite-scroll-preloader').hide();
	  				return;
	  			}
	  			addItems(last_page);
	  			// 更新最后加载的序号
	  			last_page++;
	  			$('#p').val(last_page);
	  			$.refreshScroller();
	  		}, 1000);
	  	});
	  	

  	});	
	
	/**
	* -----------------------------------------
	* 关注的店铺
	* -----------------------------------------
	* Create by Lazycat
	* -----------------------------------------
	* 2017-01-18
	* -----------------------------------------
	**/
  	$(document).on("pageInit", "#page-fav-shop", function(e, id, page) {	
  		//if($('#p').val() == 2) page.find('.content').scrollTop(0);
  		
		var loading = false;
	  	var last_page = 1;
	  	var page_count = page.data('page_num');
	  	var max_page = page_count > 100 ? 100 : page_count; //最多100页 
	  	var url = page.data('url');
	  	var tpl = page.data('tpl');
	  	
	  	function addItems(p) {
	  		var where = {
	  			p: p
	  		};
	  		$.get(url, where, function(ret) {
	  			var html = template(tpl, {
	  				data: ret.data.list
	  			});
	  			$('.infinite-scroll .list-container').append(html);
	  			//alert(ret);
	  		});
	
	  	}
	
	  	$('.infinite-scroll-preloader').hide();
	
	  	$(page).on('infinite', function() {
	  		var last_page = parseInt($('#p').val());
	  		// 如果正在加载，则退出
	  		if(loading) return;
	  		// 设置flag
	  		loading = true;
	  		$('.infinite-scroll-preloader').show();
	  		// 模拟1s的加载过程
	  		setTimeout(function() {
	  			// 重置加载flag
	  			loading = false;
	  			if(last_page > max_page) {
	  				// 加载完毕，则注销无限加载事件，以防不必要的加载
	  				$.detachInfiniteScroll($('.infinite-scroll'));
	  				// 删除加载提示符
	  				$('.infinite-scroll-preloader').hide();
	  				return;
	  			}
	  			addItems(last_page);
	  			// 更新最后加载的序号
	  			last_page++;
	  			$('#p').val(last_page);
	  			$.refreshScroller();
	  		}, 1000);
	  	});
	  	

  	});		
	
	/**
	* -----------------------------------------
	* 推荐频道
	* -----------------------------------------
	* Create by Lazycat
	* -----------------------------------------
	* 2017-01-14
	* -----------------------------------------
	**/
  	$(document).on("pageInit", "#page-scroll-hot", function(e, id, page) {	
  		//if($('#p').val() == 2) page.find('.content').scrollTop(0);
  		
		var loading = false;
	  	var last_page = 1;
	  	var page_count = page.data('page_num');
	  	var max_page = page_count > 100 ? 100 : page_count; //最多100页 
	
	  	function addItems(p) {
	  		var where = {
	  			p: p
	  		};
	  		$.get('/Hot/hot_page', where, function(ret) {
	  			var html = template('tpl_hot_item', {
	  				data: ret.data.list
	  			});
	  			$('.infinite-scroll .list-container').append(html);
	  			//alert(ret);
	  		});
	
	  	}
	
	  	$('.infinite-scroll-preloader').hide();
	
	  	$(page).on('infinite', function() {
	  		var last_page = parseInt($('#p').val());
	  		// 如果正在加载，则退出
	  		if(loading) return;
	  		// 设置flag
	  		loading = true;
	  		$('.infinite-scroll-preloader').show();
	  		// 模拟1s的加载过程
	  		setTimeout(function() {
	  			// 重置加载flag
	  			loading = false;
	  			if(last_page > max_page) {
	  				// 加载完毕，则注销无限加载事件，以防不必要的加载
	  				$.detachInfiniteScroll($('.infinite-scroll'));
	  				// 删除加载提示符
	  				$('.infinite-scroll-preloader').hide();
	  				return;
	  			}
	  			addItems(last_page);
	  			// 更新最后加载的序号
	  			last_page++;
	  			$('#p').val(last_page);
	  			$.refreshScroller();
	  		}, 1000);
	  	});
  	});	
	
	
	/**
	* -----------------------------------------
	* 买家中心
	* -----------------------------------------
	* Create by Lazycat
	* -----------------------------------------
	* 2017-01-14
	* -----------------------------------------
	**/	
  	$(document).on("pageInit", "#page-ucenter", function(e, id, page) {	
  		$('.to-account').click(function(){
  			$.toast("账户详情请登录PC端或APP查看！");
  		});
  		
  		//登出
  		$('.btn-logout').click(function(){
  			$.get('/Login/logout',function(ret){
  				if(ret.code == 1){
  					$.toast("已成功登出！");
  					setTimeout(function(){
  						location.href="/Login";
  					},1000);
  				}else $.toast('登出失败！');
  			});
  		});
		//获取方圆的未读消息
		$.post('/Ucenter/ajax_im_count',function(ret){
			if(ret.code != 0){
				var notice = $(".notice-alert").html();
				notice = parseInt(notice)+parseInt(ret.code);
				if(notice > 99){
					notice = 99;
				}
				$(".notice-alert").html(notice);
			}
		});
		
  	});		
  	
  	
	/**
	* -----------------------------------------
	* 买家收货地址管理
	* -----------------------------------------
	* Create by Lazycat
	* -----------------------------------------
	* 2017-01-16
	* -----------------------------------------
	**/
  	$(document).on("pageInit", "#page-shopping-address", function(e, id, page) {	
		var loading = false;
	  	var last_page = 1;
	  	var page_count = page.data('page_num');
	  	var max_page = page_count > 100 ? 100 : page_count; //最多100页 
	
	  	function addItems(p) {
	  		var where = {
	  			p: p
	  		};
	  		$.get('/ShoppingAddress/address_page', where, function(ret) {
	  			var html = template('tpl_address_item', {
	  				data: ret.data.list
	  			});
	  			$('.infinite-scroll .list-container').append(html);
	  			//alert(ret);
	  		});
	
	  	}
	
	  	$('.infinite-scroll-preloader').hide();
	
	  	$(page).on('infinite', function() {
	  		var last_page = parseInt($('#p').val());
	  		// 如果正在加载，则退出
	  		if(loading) return;
	  		// 设置flag
	  		loading = true;
	  		$('.infinite-scroll-preloader').show();
	  		// 模拟1s的加载过程
	  		setTimeout(function() {
	  			// 重置加载flag
	  			loading = false;
	  			if(last_page > max_page) {
	  				// 加载完毕，则注销无限加载事件，以防不必要的加载
	  				$.detachInfiniteScroll($('.infinite-scroll'));
	  				// 删除加载提示符
	  				$('.infinite-scroll-preloader').hide();
	  				return;
	  			}
	  			addItems(last_page);
	  			// 更新最后加载的序号
	  			last_page++;
	  			$('#p').val(last_page);
	  			$.refreshScroller();
	  		}, 1000);
	  	});
	  	
	    $(page).on('click','.action', function () {
	    	var obj=$(this);
	    	
		    var buttons1 = [{
		      	text: '请选择',
		      	label: true
		    }, {
		      	text: '修改',
		      	onClick: function() {
		      		//location.href='/ShoppingAddress/edit/id/'+obj.data('id');
		      		$.router.load('/ShoppingAddress/edit/id/'+obj.data('id'),true);
		      	}
		    }, {
		      	text: '删除',
		      	color: 'danger',		      	
		      	onClick: function() {
		      		$.confirm('确定要删除吗?', function () {
			        	$.post('/ShoppingAddress/delete',{id:obj.data('id')},function(ret){
			        		if(ret.code == 1){
			        			$.toast('删除成功！');
			        			$('#address-'+obj.data('id')).remove();
			        		}
			        	});
			      	});
		      	}
		    }, {
		      	text: '设为默认',
		      	onClick: function() {
		      		//location.href='/ShoppingAddress/edit/id/'+obj.data('id');
		      		$.post('/ShoppingAddress/set_default',{id:obj.data('id')},function(ret){
		      			if(ret.code == 1){
		      				$.toast('设置成功！');
		      				$.router.load('/ShoppingAddress/index/random/'+Math.random(),true);
		      			}else $.toast('设置失败！');
		      		});
		      		
		      	}
		    }];
		     var buttons2 = [{
		      	text: '取消',
		      	bg: 'danger'
		    }];
		    var groups = [buttons1, buttons2];
		    $.actions(groups);
	    });
	
	  	
  	});	  	
  	
  	
	/**
	* -----------------------------------------
	* 买家添加/修改收货地址
	* -----------------------------------------
	* Create by Lazycat
	* -----------------------------------------
	* 2017-01-16
	* -----------------------------------------
	**/	
	$(document).on("pageInit", "#page-shopping-address-add,#page-shopping-address-edit", function(e, id, page) {
		$('.btn-submit').click(function() {
			var data = getFormJson('#form-shopaddress-add');
			if(data.linkname == '') {
				$.toast("请输入联系人！");
				return false;
			}
			if(data.mobile == '' && data.tel == '') {
				$.toast("手机号码或电话号码两项必须填写一项！");
				return false;
			}
			
			if(data.province == '' || data.city == '' || data.district == '') {
				$.toast("请选择城市！");
				return false;
			}		
			
			if(data.street == '') {
				$.toast("请输入详细地址！");
				return false;
			}		
			
			var url = page.find('form').attr('action');

			$.post(url, data, function(ret) {
				if(ret.code == 1) $.router.load('/ShoppingAddress',true);
				else $.toast(ret.msg);
			});
	
		});
		
		//选择省份
		$('.popup-province .open-popup').click(function(){
			$(this).closest('li').addClass('active').siblings().removeClass('active');
			$('.popup-city ul').html('');
			$.get('/City/city_item/sid/'+$(this).data('id'),function(ret){
				if(ret.code == 1){
					var html = template('tpl_city_item',{data:ret.data});
					$('.popup-city ul').html(html);
				}else $.toast("读取城市列表失败！");
			});
		});	
	
	});	
	
	/**
	* -----------------------------------------
	* 个人注册
	* -----------------------------------------
	* Create by Lazycat
	* -----------------------------------------
	* 2017-01-19
	* -----------------------------------------
	**/	
	$(document).on("pageInit", "#page-register-person", function(e, id, page) {	
		$('.smscode.active').click(function(){
			var obj = $(this);
			if(obj.hasClass('active') == false) return; //还在倒计时中，直接退出
			
			var data = getFormJson('#form-register-person');
			if(data.mobile == ''){
				$.toast('请先输入手机号码！');
				return false;
			}
			
			$.post('/Login/smscode',{mobile:data.mobile},function(ret){
				if(ret.code == 1){
					obj.removeClass('active').html('<span class="dec_time">180</span>秒后重新获取');
					var timer = window.setInterval(function(){
						interval_smscode(obj);
					},1000);
					obj.data('timer',timer);
				}else $.toast(ret.msg);
			});
			
			
		});
		
		$('.btn-submit').click(function(){
			var data = getFormJson('#form-register-person');
			if(data.username == ''){
				$.toast('请输入昵称');
				return false;
			}
			if(data.username.length < 6 ){
				$.toast('用户名必须是6~20位,不能以_或数字开头,不能以_结束');
				return false;
			}
			
			if(data.password == ''){
				$.toast('请输入密码！');
				return false;
			}
			if(data.password.length < 8 ){
				$.toast('密码长度不少于8位');
				return false;
			}
			if(data.mobile == ''){
				$.toast('请输入手机号码！');
				return false;
			}
			if(data.smscode == ''){
				$.toast('请输入短信验证码！');
				return false;
			}	
			if(data.is_accept !=1){
				$.toast('请阅读并同意会员注册协议！');
				return false;
			}			
			
			$.post('/Login/register_person_save',data,function(ret){
				if(ret.code == 1){
					$.router.load('/Ucenter',true);
				}else $.toast(ret.msg);
			});				
		});
		
		
		//显示密码/隐藏密码
		$('.switch-eye').click(function(){
			var type = $(this).closest('li').find('input').attr('type');
			if(type == 'password') {
				$(this).find('i').removeClass('icon-Untitled-').addClass('icon-eye');
				$(this).closest('li').find('input').attr('type','text');
			}else{
				$(this).find('i').removeClass('icon-eye').addClass('icon-Untitled-');
				$(this).closest('li').find('input').attr('type','password');
			}
			
		});
		
		//手机号码是否被占用
		$('input[name="mobile"]').blur(function(){
			var data = getFormJson('#form-register-person');
			if(data.mobile == ''){
				//$.toast('请输入手机号码！');
				return false;
			}			
			$.post('/Login/check_mobile',{mobile:$(this).val()},function(ret){
				$.toast(ret.msg);
				$(this).data('status',ret.code);
			});
		});
		
		//用户名是否被占用
		$('input[name="username"]').blur(function(){
			var data = getFormJson('#form-register-person');
			if(data.username == ''){
				$.toast('请输入用户昵称！');
				return false;
			}			
			$.post('/Login/check_username',{username:$(this).val(),referrer:data.ref},function(ret){
				if(ret.code==1){
					$.toast("用户名可用！");
				}else{
					$.toast(ret.msg);
				}
			});
		});		
/*		
		//分享人资料
		$('input[name="ref"]').blur(function(){
			var obj = $(this);
			var data = getFormJson('#form-register-person');
			if(data.ref == ''){
				$.toast('请输入分享人昵称或手机号码！');
				return false;
			}			
			$.post('/Login/check_username',{username:data.username,referrer:data.ref},function(ret){
				if(ret.code == 1){
					var html = '昵称：'+ret.data.u_nick+(ret.data.u_tel ? ' 手机：'+ret.data.u_tel : '');
					$('.ref_user').removeClass('hide').find('.item-inner').html(html);
				}else{
					$.toast('分享人不存在！');
					obj.val('');
					$('.ref_user').addClass('hide').find('.item-inner').html('');
				}
			});
		});			
*/		
	});		
	

	/**
	* -----------------------------------------
	* 企业注册
	* -----------------------------------------
	* Create by Lazycat
	* -----------------------------------------
	* 2017-01-19
	* -----------------------------------------
	**/	
	$(document).on("pageInit", "#page-register-company", function(e, id, page) {	
		$('.smscode.active').click(function(){
			var obj = $(this);
			if(obj.hasClass('active') == false) return; //还在倒计时中，直接退出
			
			var data = getFormJson('#form-register-company');
			if(data.mobile == ''){
				$.toast('请先输入手机号码！');
				return false;
			}
			
			$.post('/Login/smscode',{mobile:data.mobile},function(ret){
				if(ret.code == 1){
					obj.removeClass('active').html('<span class="dec_time">180</span>秒后重新获取');
					var timer = window.setInterval(function(){
						interval_smscode(obj);
					},1000);
					obj.data('timer',timer);
				}else $.toast(ret.msg);
			});			
			
		});
		
		$('.btn-submit').click(function(){
			var data = getFormJson('#form-register-company');
			if(data.username == ''){
				$.toast('请输入昵称');
				return false;
			}
			if(data.username.length < 6 ){
				$.toast('6~20位,不能以_或数字开头,不能以_结束');
				return false;
			}
			if(data.password == ''){
				$.toast('请输入密码！');
				return false;
			}
			
			if(data.password.length < 8 ){
				$.toast('密码长度不少于8位');
				return false;
			}
			if(data.mobile == ''){
				$.toast('请输入手机号码！');
				return false;
			}
			if(data.smscode == ''){
				$.toast('请输入短信验证码！');
				return false;
			}	
			if(data.organize == ''){
				$.toast('请选择组织机构类型！');
				return false;
			}	
			if(data.company == ''){
				$.toast('请输入公司名称！');
				return false;
			}	
			if(data.company_license == ''){
				$.toast('请输入营业执照！');
				return false;
			}				
			if(data.is_accept !=1){
				$.toast('请阅读并同意会员注册协议！');
				return false;
			}	
			
			$.post('/Login/register_company_save',data,function(ret){
				if(ret.code == 1){
					$.router.load('/Ucenter',true);
				}else $.toast(ret.msg);
			});
				
		});
		
		
		//显示密码/隐藏密码
		$('.switch-eye').click(function(){
			var type = $(this).closest('li').find('input').attr('type');
			if(type == 'password') {
				$(this).find('i').removeClass('icon-Untitled-').addClass('icon-eye');
				$(this).closest('li').find('input').attr('type','text');
			}else{
				$(this).find('i').removeClass('icon-eye').addClass('icon-Untitled-');
				$(this).closest('li').find('input').attr('type','password');
			}			
		});
		
		//选择组织机构类型
		$('.popup-stru ul li .item-link').click(function(){
			var id = $(this).data('id');
			$('input[name="organize"]').val(id);
			$('input[name="organize_name"]').val($(this).find('.item-title').text());
			$.closeModal('.popup-stru');
		});

		//手机号码是否被占用
		$('input[name="mobile"]').blur(function(){
			var data = getFormJson('#form-register-company');
			if(data.mobile == ''){
				//$.toast('请输入手机号码！');
				return false;
			}			
			$.post('/Login/check_mobile',{mobile:$(this).val()},function(ret){
				$.toast(ret.msg);
				$(this).data('status',ret.code);
			});
		});
		
		//用户名是否被占用
		$('input[name="username"]').blur(function(){
			var data = getFormJson('#form-register-company');
			if(data.username == ''){
				$.toast('请输入用户昵称！');
				return false;
			}			
			$.post('/Login/check_username',{username:$(this).val()},function(ret){
				if(ret.code==1){
					$.toast("用户名可用！");
				}else{
					$.toast(ret.msg);
				}
			});
		});		
/*		
		//分享人资料
		$('input[name="ref"]').blur(function(){
			var obj = $(this);
			var data = getFormJson('#form-register-company');
			if(data.ref == ''){
				$.toast('请输入分享人昵称或电话号码！');
				return false;
			}			
			$.post('/Login/check_username',{username:data.username,referrer:data.ref},function(ret){
				if(ret.code == 1){
					var html = '昵称：'+ret.data.u_nick+(ret.data.u_tel ? ' 手机：'+ret.data.u_tel : '');
					$('.ref_user').removeClass('hide').find('.item-inner').html(html);
				}else{
					$.toast('分享人不存在！');
					obj.val('');
					$('.ref_user').addClass('hide').find('.item-inner').html('');
				}
			});
		});	
*/	
	});	
	
	
	/**
	* -----------------------------------------
	* 找回密码
	* -----------------------------------------
	* Create by lizuheng
	* -----------------------------------------
	* 2017-01-23
	* -----------------------------------------
	**/	
	$(document).on("pageInit", "#page-forget", function(e, id, page) {	
		$('.smscode.active').click(function(){
			var obj = $(this);
			if(obj.hasClass('active') == false) return; //还在倒计时中，直接退出
			
			var data = getFormJson('#form-forget');
			if(data.username == ''){
				$.toast('请先用户昵称！');
				return false;
			}
			if(data.mobile == ''){
				$.toast('请先输入手机号码！');
				return false;
			}
			
			$.post('/Login/smscode',{mobile:data.mobile,username:data.username},function(ret){
				if(ret.code == 1){
					obj.removeClass('active').html('<span class="dec_time">180</span>秒后重新获取');
					var timer = window.setInterval(function(){
						interval_smscode(obj);
					},1000);
					obj.data('timer',timer);
				}else $.toast(ret.msg);
			});
			
			
		});
		
		$('.btn-submit').click(function(){
			var data = getFormJson('#form-forget');
			if(data.username == ''){
				$.toast('请输入昵称');
				return false;
			}
			if(data.username.length < 6 ){
				$.toast('用户名必须是6~20位之间的中文/字母/数字/下划线组合');
				return false;
			}
			if(data.mobile == ''){
				$.toast('请输入手机号码！');
				return false;
			}
			if(data.smscode == ''){
				$.toast('请输入短信验证码！');
				return false;
			}	
			if(data.smscode.length < 6){
				$.toast('短信验证码只能是6位纯数字！');
				return false;
			}
			
			$.post('/Login/forget_step1',data,function(ret){
				if(ret.code == 1){
					$.toast('请输入您的新密码！');
					$.router.load('/Login/forget_step2/erp_uid/'+ ret.data.userID +'/signcode/'+ ret.data.sign,true);
				}else $.toast(ret.msg);
			});				
		});
	});		
		
		
		/**
	* -----------------------------------------
	* 找回密码第二步
	* -----------------------------------------
	* Create by Lizuheng
	* -----------------------------------------
	* 2017-02-07
	* -----------------------------------------
	**/	
	$(document).on("pageInit", "#page-forget2", function(e, id, page) {	
		
		$('.btn-submit').click(function(){
			var data = getFormJson('#form-forget2');
			if(data.password == ''){
				$.toast('请输入新密码');
				return false;
			}
			if(data.password2 == ''){
				$.toast('请输入确认密码！');
				return false;
			}	
			if(data.password.length < 8 ){
				$.toast('新密码不能少于8位');
				return false;
			}
			if(data.password != data.password2 ){
				$.toast('新密码和确认密码不一致');
				return false;
			}
			$.post('/Login/save_forget_step2',data,function(ret){
				if(ret.code == 1){
					$.toast('密码修改成功！');
					$.router.load('/Login',true);
				}else $.toast(ret.msg);
			});				
		});
		
	
		
	});

	/**
	* -----------------------------------------
	* 商品分类
	* -----------------------------------------
	* Create by Lazycat
	* -----------------------------------------
	* 2017-01-18
	* -----------------------------------------
	**/	
  	$(document).on("pageInit", "#page-category-goods", function(e, id, page) {	
	  	//顶部菜单滚动
        var myScroll = new IScroll('.category-first', {  
            scrollX: true,  
            scrollY: false,
            click:iScrollClick(),
            mouseWheel: true,
            preventDefault: false
        });
  	});		

	/**
	* -----------------------------------------
	* 搜索首页
	* -----------------------------------------
	* Create by Lazycat
	* -----------------------------------------
	* 2017-01-20
	* -----------------------------------------
	**/	
  	$(document).on("pageInit", "#page-search", function(e, id, page) {	
		$('.btn-search').click(function(){
			var q = $('input[name="q"]').val();
			if(q == ''){
				$.toast('请输入关键词！');
				return false;
			}
			
			$.router.load('/Search/goods/q/'+q,true);
			
		});
  	});	

	/**
	* -----------------------------------------
	* 搜索商品
	* -----------------------------------------
	* Create by Lazycat
	* -----------------------------------------
	* 2017-01-18
	* -----------------------------------------
	**/		
	$(document).on("pageInit", "#page-search-goods", function(e, id, page) {		
		var loading = false;
	  	var last_page = 1;
	  	var page_count = page.data('page_num');
	  	var max_page = page_count > 100 ? 100 : page_count; //最多100页 
	
		function addItems(p) {
			var where = {};
			where.p = p;
			if(page.data('cid')) where.cid = page.data('cid');
			if(page.data('sort')) where.sort = page.data('sort');
			if(page.data('city_id')) where.option = page.data('city_id');
			if(page.data('is_self')) where.is_self = page.data('is_self');
			if(page.data('is_daigou')) where.is_daigou = page.data('is_daigou');
			if(page.data('free_express')) where.free_express = page.data('free_express');
			if(page.data('score_ratio')) where.score_ratio = page.data('score_ratio');
			if(page.data('q')) where.q = page.data('q');
			if(page.data('min_price')) where.min_price = page.data('min_price');
			if(page.data('max_price')) where.max_price = page.data('max_price');
			if(page.data('option')) where.option = page.data('option');
			if(page.data('score_type')) where.score_type = page.data('score_type');


			$.get('/Search/goods_page',where,function(ret){
				//console.log(ret);
				var html = template('tpl_goods_item',{data:ret.data.list});
				$('.infinite-scroll .list-container').append(html);
				//alert(ret);
			});

		}
	
	  	$('.infinite-scroll-preloader').hide();
	
	  	$(page).on('infinite', function() {
	  		var last_page = parseInt($('#p').val());
	  		// 如果正在加载，则退出
	  		if(loading) return;
	  		// 设置flag
	  		loading = true;
	  		$('.infinite-scroll-preloader').show();
	  		// 模拟1s的加载过程
	  		setTimeout(function() {
	  			// 重置加载flag
	  			loading = false;
	  			if(last_page > max_page) {
	  				// 加载完毕，则注销无限加载事件，以防不必要的加载
	  				$.detachInfiniteScroll($('.infinite-scroll'));
	  				// 删除加载提示符
	  				$('.infinite-scroll-preloader').hide();
	  				return;
	  			}
	  			addItems(last_page);
	  			// 更新最后加载的序号
	  			last_page++;
	  			$('#p').val(last_page);
	  			$.refreshScroller();
	  		}, 1000);
	  	});

		$(".open-panel").click(function(){
			$('.panel').html($('.select_options').html());			
		});
		
		//顶部菜单
        var myScroll = new IScroll('.iscroll-category', {  
            scrollX: true,  
            scrollY: false,
            click:iScrollClick(),
            mouseWheel: true,
            preventDefault: false
        }); 			
		
	});		
	
	
	/**
	* -----------------------------------------
	* 商品详情
	* -----------------------------------------
	* Create by Lazycat
	* -----------------------------------------
	* 2017-02-06
	* -----------------------------------------
	**/		
	$(document).on("pageInit", "#page-goods-view", function(e, id, page) {		
		var burl = get_cookie_url();
		if(burl.indexOf('/Goods/view') >=0) page.find('.content').scrollTop(0);
		
		/*
		$('.ac-detail').click(function(){
			//var top = page.find('#goods-detail').offset().top;
			page.find('.content').scrollTop(1000);
		});
		*/
		
		$('#ac-fav').click(function(){
			var obj = $(this);
			$.post('/Goods/fav_toggle',{goods_id:$(this).data('goods_id')},function(ret){
				$.toast(ret.msg);
				if(ret.code > 0){
					obj.find('i').toggleClass('text-red');
				}
			});
		});
		
		//选择省份
		$('.popup-province .open-popup').click(function(){
			$(this).closest('li').addClass('active').siblings().removeClass('active');
			$('.popup-city ul').html('');
			$.get('/City/city_item/sid/'+$(this).data('id'),function(ret){
				if(ret.code == 1){
					var html = template('tpl_city_item2',{data:ret.data});
					$('.popup-city ul').html(html);
				}else $.toast("读取城市列表失败！");
			});
		});	
		
		//是否有秒杀
		if($('.miaosha-time-box').size() > 0){			
			var data = $('.miaosha-time-box').data();
			$('.miaosha-time-box').attr('data-init',data.init+1);
			
			if(data.init > 0){	//第二次打开时要重新获取计时时间
				$.post('/Goods/miaosha_goods_view',{goods_id:data.goods_id},function(ret){
					//console.log(ret);
					if(ret.code == 1){
						$('.miaosha-time-box').attr('data-status',ret.data.miaosha_status);
						$('.miaosha-time-box').attr('data-time_dif',ret.data.time_dif);
						if(ret.data.miaosha_status == 1){ //秒杀中倒计时
							miaosha_in_dec(ret.data.time_dif);
						}else{	//未开始，倒计时
							miaosha_wait_dec(ret.data.time_dif,data.attr_list_id);
						}
					}
				});				
			}else{	//第一次打开页面时
				if(data.status == 1){ //秒杀中倒计时
					miaosha_in_dec(data.time_dif);
				}else{	//未开始，倒计时
					miaosha_wait_dec(data.time_dif,data.attr_list_id);
				}
			}
		}
	});		
	
	/**
	* -----------------------------------------
	* 商品图文详情
	* -----------------------------------------
	* Create by Lazycat
	* -----------------------------------------
	* 2017-02-07
	* -----------------------------------------
	**/		
	$(document).on("pageInit", "#page-goods-content", function(e, id, page) {		
		page.find('.content').scrollTop(0);	
	});	

	/**
	* -----------------------------------------
	* 商品评价
	* -----------------------------------------
	* Create by Lazycat
	* -----------------------------------------
	* 2017-02-08
	* -----------------------------------------
	**/		
	$(document).on("pageInit", "#page-goods-rate", function(e, id, page) {		
		//page.find('.content').scrollTop(0);
		var loading = false;
	  	var last_page 			= 1;
	  	var last_page_good 		= 1;
	  	var last_page_middle 	= 1;
	  	var last_page_bad 		= 1;
		
		var goods_id = page.data('goods_id');
	
	  	function addItems(p,tab_index) {	  		
	  		var where = {
	  			p: p,
	  			goods_id:goods_id,
	  		};
	  		
	  		if(tab_index == 1) where.level = 1;
	  		else if(tab_index == 2) where.level = 0;
	  		else if(tab_index == 3) where.level = -1;
	  		
	  		$.get('/Goods/rate_page', where, function(ret) {
	  			var html = template('tpl_goods_rate', {
	  				data: ret.data.list
	  			});
	  			$('.infinite-scroll').eq(tab_index).find('.list-container').append(html);
	  			//alert(ret);
	  		});
	
	  	}
	
	  	$('.infinite-scroll-preloader').hide();

	  	$(page).on('infinite', function() {
		  	var last_page 			= parseInt($('.infinite-scroll').eq(0).find('#p').val());
		  	var last_page_good 		= parseInt($('.infinite-scroll').eq(1).find('#p').val());
		  	var last_page_middle 	= parseInt($('.infinite-scroll').eq(2).find('#p').val());
		  	var last_page_bad 		= parseInt($('.infinite-scroll').eq(3).find('#p').val());	  		
	  		// 如果正在加载，则退出
	  		if(loading) return;
	  		// 设置flag
	  		loading = true;
	  		// 模拟1s的加载过程
	  		
	  		page.find('.infinite-scroll.active .infinite-scroll-preloader').show();	  		
	  		setTimeout(function() {
	  			// 重置加载flag
	  			loading = false;
	  			
	  			var tab_index 	= 0;
	  			var max_page	= page.find('.infinite-scroll.active').data('page_num');
	  			var this_page	= last_page;
	  			
	  			if(page.find('.infinite-scroll.active').attr('id') == "tab2"){
	  				tab_index = 1;
	  				this_page = last_page_good;
	  			}
	  			if(page.find('.infinite-scroll.active').attr('id') == "tab3"){
	  				tab_index = 2;
	  				this_page = last_page_middle;
	  			}
	  			if(page.find('.infinite-scroll.active').attr('id') == "tab4"){
	  				tab_index = 3;
	  				this_page = last_page_bad;
	  			}
	  			
	  			//$.alert(tab_index);
	  			//$.alert(this_page);
	  			
	  			if(this_page > max_page) {
	  				// 加载完毕，则注销无限加载事件，以防不必要的加载,同页面多个分页时此项要注释掉
	  				//$.detachInfiniteScroll(page.find('.infinite-scroll.active'));
	  				// 删除加载提示符
	  				page.find('.infinite-scroll.active .infinite-scroll-preloader').hide();
	  				return;
	  			} 			
	  			
	  			addItems(this_page,tab_index);
	  			
	  			// 更新最后加载的序号	  			
	  			switch(tab_index){
	  				case 1:
	  					last_page_good++;
	  					$('.infinite-scroll').eq(tab_index).find('#p').val(last_page_good);
	  				break;
	  				case 2:
	  					last_page_middle++;
	  					$('.infinite-scroll').eq(tab_index).find('#p').val(last_page_middle);
	  				break;
	  				case 3:
	  					last_page_bad++;
	  					$('.infinite-scroll').eq(tab_index).find('#p').val(last_page_bad);
	  				break;
	  				default:
	  					last_page++;
	  					$('.infinite-scroll').eq(tab_index).find('#p').val(last_sale);
	  			}
	  			$.refreshScroller();
	  		}, 1000);
	  	});
	});	


	/**
	* -----------------------------------------
	* 店铺-促销
	* -----------------------------------------
	* Create by Lazycat
	* -----------------------------------------
	* 2017-02-09
	* -----------------------------------------
	**/
  	$(document).on("pageInit", "#page-shop-best", function(e, id, page) {	
		var loading = false;
	  	var last_page = 1;
	  	var page_count = page.data('page_num');
	  	var max_page = page_count > 100 ? 100 : page_count; //最多100页 
	  	var shop_id  = page.data('shop_id');
	
	  	function addItems(p) {
	  		var where = {
	  			p: p,
	  			shop_id: shop_id,
	  		};
	  		$.get('/Shop/best_page', where, function(ret) {
	  			var html = template('tpl_shop_goods', {
	  				data: ret.data.list
	  			});
	  			$('.infinite-scroll .list-container').append(html);
	  			//alert(ret);
	  		});
	
	  	}
	
	  	$('.infinite-scroll-preloader').hide();
	
	  	$(page).on('infinite', function() {
	  		var last_page = parseInt($('#p').val());
	  		// 如果正在加载，则退出
	  		if(loading) return;
	  		// 设置flag
	  		loading = true;
	  		$('.infinite-scroll-preloader').show();
	  		// 模拟1s的加载过程
	  		setTimeout(function() {
	  			// 重置加载flag
	  			loading = false;
	  			if(last_page > max_page) {
	  				// 加载完毕，则注销无限加载事件，以防不必要的加载
	  				$.detachInfiniteScroll($('.infinite-scroll'));
	  				// 删除加载提示符
	  				$('.infinite-scroll-preloader').hide();
	  				return;
	  			}
	  			addItems(last_page);
	  			// 更新最后加载的序号
	  			last_page++;
	  			$('#p').val(last_page);
	  			$.refreshScroller();
	  		}, 1000);
	  	});
  	});	
  	
  	
	/**
	* -----------------------------------------
	* 商品图文详情
	* -----------------------------------------
	* Create by Lazycat
	* -----------------------------------------
	* 2017-02-07
	* -----------------------------------------
	**/		
	$(document).on("pageInit", "#page-goods-content", function(e, id, page) {		
		page.find('.content').scrollTop(0);	
	});	

	/**
	* -----------------------------------------
	* 店铺商品列表
	* -----------------------------------------
	* Create by Lazycat
	* -----------------------------------------
	* 2017-02-09
	* -----------------------------------------
	**/		
	$(document).on("pageInit", ".shop-goods-list", function(e, id, page) {		
		var shop_id = page.data('shop_id');
		$('.buttons-tab a').eq(3).click(function(){
			var order = $(this).data('order');			
			var status = $(this).data('status');
			if(status == 1){
				if(order == 'price asc') order = 'price desc';
				else order = 'price asc';				
				$(this).data('order',order);
				
	  			var sid = page.find('input[name="sid"]').val();
	  			var q = page.find('input[name="q"]').val();
	  			
	  			var where = {shop_id:shop_id,order:order};
	  			if(sid!='') where.sid = sid;
	  			if(q!='') where.q = q;				
				
		  		$.get('/Shop/goods_page', where , function(ret) {
		  			var html = template('tpl_shop_goods', {
		  				data: ret.data.list
		  			});
		  			$('.infinite-scroll').eq(3).find('.list-container').html(html);
		  			//alert(ret);
		  		});				
				
			}else $(this).data('status',1);
		});
		
		$('.buttons-tab a').not('[href="#tab4"]').click(function(){
			$('.buttons-tab a').eq(3).data('status',0);
		});

		var loading = false;
	  	var last_page 			= 1;
	  	var last_sale 			= 1;
	  	var last_view 			= 1;
	  	var last_price 			= 1;
		
		
	
	  	function addItems(p,tab_index) {	  		
	  		var where = {
	  			p: p,
	  			shop_id:shop_id,
	  		};
	  		
	  		if(tab_index == 1) where.order = 'sale_num desc';
	  		else if(tab_index == 2) where.order = 'view desc';
	  		else if(tab_index == 3) where.order = $('.buttons-tab a').eq(tab_index).data('order');
	  		
	  		var sid = page.find('input[name="sid"]').val();
	  		var q = page.find('input[name="q"]').val();
	  		
	  		if(sid!='') where.sid = sid;
	  		if(q!='') where.q = q;
	  		
	  		$.get('/Shop/goods_page', where, function(ret) {
	  			//console.log(ret);
	  			var html = template('tpl_shop_goods', {
	  				data: ret.data.list
	  			});
	  			$('.infinite-scroll').eq(tab_index).find('.list-container').append(html);
	  			//alert(ret);
	  		});
	
	  	}
	
	  	$('.infinite-scroll-preloader').hide();

	  	$(page).on('infinite', function() {
		  	var last_page 		= parseInt($('.infinite-scroll').eq(0).find('#p').val());
		  	var last_sale 		= parseInt($('.infinite-scroll').eq(1).find('#p').val());
		  	var last_view 		= parseInt($('.infinite-scroll').eq(2).find('#p').val());
		  	var last_price 		= parseInt($('.infinite-scroll').eq(3).find('#p').val());
		  	
	  		// 如果正在加载，则退出
	  		if(loading) return;
	  		// 设置flag
	  		loading = true;
	  		// 模拟1s的加载过程
	  		
	  		page.find('.infinite-scroll.active .infinite-scroll-preloader').show();	  		
	  		setTimeout(function() {
	  			// 重置加载flag
	  			loading = false;
	  			
	  			var tab_index 	= 0;
	  			var max_page	= page.find('.infinite-scroll.active').data('page_num');
	  			var this_page	= last_page;
	  			
	  			if(page.find('.infinite-scroll.active').attr('id') == "tab2"){
	  				tab_index = 1;
	  				this_page = last_sale;
	  			}
	  			if(page.find('.infinite-scroll.active').attr('id') == "tab3"){
	  				tab_index = 2;
	  				this_page = last_view;
	  			}
	  			if(page.find('.infinite-scroll.active').attr('id') == "tab4"){
	  				tab_index = 3;
	  				this_page = last_price;
	  			}
	  			
	  			//$.alert(tab_index);
	  			//$.alert(this_page);
	  			
	  			if(this_page > max_page) {
	  				// 加载完毕，则注销无限加载事件，以防不必要的加载,同页面多个分页时此项要注释掉
	  				//$.detachInfiniteScroll(page.find('.infinite-scroll.active'));
	  				// 删除加载提示符
	  				page.find('.infinite-scroll.active .infinite-scroll-preloader').hide();
	  				return;
	  			} 			
	  			
	  			addItems(this_page,tab_index);
	  			
	  			// 更新最后加载的序号	  			
	  			switch(tab_index){
	  				case 1:
	  					last_sale++;
	  					$('.infinite-scroll').eq(tab_index).find('#p').val(last_sale);
	  				break;
	  				case 2:
	  					last_view++;
	  					$('.infinite-scroll').eq(tab_index).find('#p').val(last_view);
	  				break;
	  				case 3:
	  					last_price++;
	  					$('.infinite-scroll').eq(tab_index).find('#p').val(last_price);
	  				break;
	  				default:
	  					last_page++;
	  					$('.infinite-scroll').eq(tab_index).find('#p').val(last_page);
	  			}
	  			$.refreshScroller();
	  		}, 1000);
	  	});
	});	  	
	
	
	/**
	* -----------------------------------------
	* 店铺首页
	* -----------------------------------------
	* Create by Lazycat
	* -----------------------------------------
	* 2017-02-10
	* -----------------------------------------
	**/		
	$(document).on("pageInit", "#page-shop-index", function(e, id, page) {		
	
		$('#ac-fav').click(function(){
			var obj = $(this);
			$.post('/Shop/fav_toggle',{shop_id:$(this).data('shop_id')},function(ret){
				$.toast(ret.msg);
				if(ret.code > 0){
					obj.find('i').toggleClass('text-red');
				}
			});
		});
	
	});	
	
	/**
	* -----------------------------------------
	* 店铺-联系卖家
	* -----------------------------------------
	* Create by Lazycat
	* -----------------------------------------
	* 2017-02-10
	* -----------------------------------------
	**/		
	$(document).on("pageInit", "#page-shop-contact", function(e, id, page) {		
	
		$('#ac-fav').click(function(){
			var obj = $(this);
			$.post('/Shop/fav_toggle',{shop_id:$(this).data('shop_id')},function(ret){
				$.toast(ret.msg);
				if(ret.code > 0){
					obj.find('i').toggleClass('text-red');
				}
			});
		});
	
	});		
	
	
	/**
	* -----------------------------------------
	* 店铺-用户评价
	* -----------------------------------------
	* Create by Lazycat
	* -----------------------------------------
	* 2017-02-10
	* -----------------------------------------
	**/		
	$(document).on("pageInit", "#page-shop-rate", function(e, id, page) {	
		$('#ac-fav').click(function(){
			var obj = $(this);
			$.post('/Shop/fav_toggle',{shop_id:$(this).data('shop_id')},function(ret){
				$.toast(ret.msg);
				if(ret.code > 0){
					obj.find('i').toggleClass('text-red');
				}
			});
		});
		
		//page.find('.content').scrollTop(0);
		var loading = false;
	  	var last_page 			= 1;
	  	var last_page_good 		= 1;
	  	var last_page_middle 	= 1;
	  	var last_page_bad 		= 1;
		
		var shop_id = page.data('shop_id');
	
	  	function addItems(p,tab_index) {	  		
	  		var where = {
	  			p: p,
	  			shop_id:shop_id,
	  		};
	  		
	  		if(tab_index == 1) where.level = 1;
	  		else if(tab_index == 2) where.level = 0;
	  		else if(tab_index == 3) where.level = -1;
	  		
	  		$.get('/Shop/rate_page', where, function(ret) {
	  			var html = template('tpl_shop_rate', {
	  				data: ret.data.list
	  			});
	  			$('.infinite-scroll').eq(tab_index).find('.list-container').append(html);
	  			//alert(ret);
	  		});
	
	  	}
	
	  	$('.infinite-scroll-preloader').hide();

	  	$(page).on('infinite', function() {
		  	var last_page 			= parseInt($('.infinite-scroll').eq(0).find('#p').val());
		  	var last_page_good 		= parseInt($('.infinite-scroll').eq(1).find('#p').val());
		  	var last_page_middle 	= parseInt($('.infinite-scroll').eq(2).find('#p').val());
		  	var last_page_bad 		= parseInt($('.infinite-scroll').eq(3).find('#p').val());	  		
	  		
	  		// 如果正在加载，则退出
	  		if(loading) return;
	  		// 设置flag
	  		loading = true;
	  		// 模拟1s的加载过程
	  		
	  		page.find('.infinite-scroll.active .infinite-scroll-preloader').show();	  		
	  		setTimeout(function() {
	  			// 重置加载flag
	  			loading = false;
	  			
	  			var tab_index 	= 0;
	  			var max_page	= page.find('.infinite-scroll.active').data('page_num');
	  			var this_page	= last_page;
	  			
	  			if(page.find('.infinite-scroll.active').attr('id') == "tab2"){
	  				tab_index = 1;
	  				this_page = last_page_good;
	  			}
	  			if(page.find('.infinite-scroll.active').attr('id') == "tab3"){
	  				tab_index = 2;
	  				this_page = last_page_middle;
	  			}
	  			if(page.find('.infinite-scroll.active').attr('id') == "tab4"){
	  				tab_index = 3;
	  				this_page = last_page_bad;
	  			}
	  			
	  			//$.alert(tab_index);
	  			//$.alert(this_page);
	  			
	  			if(this_page > max_page) {
	  				// 加载完毕，则注销无限加载事件，以防不必要的加载,同页面多个分页时此项要注释掉
	  				//$.detachInfiniteScroll(page.find('.infinite-scroll.active'));
	  				// 删除加载提示符
	  				page.find('.infinite-scroll.active .infinite-scroll-preloader').hide();
	  				return;
	  			} 			
	  			
	  			addItems(this_page,tab_index);
	  			
	  			// 更新最后加载的序号	  			
	  			switch(tab_index){
	  				case 1:
	  					last_page_good++;
	  					$('.infinite-scroll').eq(tab_index).find('#p').val(last_page_good);
	  				break;
	  				case 2:
	  					last_page_middle++;
	  					$('.infinite-scroll').eq(tab_index).find('#p').val(last_page_middle);
	  				break;
	  				case 3:
	  					last_page_bad++;
	  					$('.infinite-scroll').eq(tab_index).find('#p').val(last_page_bad);
	  				break;
	  				default:
	  					last_page++;
	  					$('.infinite-scroll').eq(tab_index).find('#p').val(last_page);
	  			}
	  			$.refreshScroller();
	  		}, 1000);
	  	});
	});		

	/**
	* -----------------------------------------
	* 店铺 - 商品搜索
	* -----------------------------------------
	* Create by Lazycat
	* -----------------------------------------
	* 2017-02-10
	* -----------------------------------------
	**/	
  	$(document).on("pageInit", "#page-shop-category", function(e, id, page) {	
		$('input[name="q"]').blur(function(){
			var q = $('input[name="q"]').val();
			if(q == ''){
				$.toast('请输入关键词！');
				return false;
			}
			
			var shop_id = page.data('shop_id');
			$.router.load('/Shop/goods/shop_id/'+shop_id+'/q/'+q,true);	
		});
  	});
	
	/**
	* -----------------------------------------
	* 修改登录密码
	* -----------------------------------------
	* Create by lizuheng
	* -----------------------------------------
	* 2017-02-07
	* -----------------------------------------
	**/	
	$(document).on("pageInit", "#page-edit-login-password", function(e, id, page) {	
		$('.smscode.active').click(function(){
			var obj = $(this);
			if(obj.hasClass('active') == false) return; //还在倒计时中，直接退出
			
			$.post('/User/send_code',function(ret){
				if(ret.code == 1){
					obj.removeClass('active').html('<span class="dec_time">180</span>秒后重新获取');
					var timer = window.setInterval(function(){
						interval_smscode(obj);
					},1000);
					obj.data('timer',timer);
				}else $.toast(ret.msg);
			});
			
			
		});
		
		$('.btn-submit').click(function(){
			var data = getFormJson('#form-edit-password');
			if(data.opassword == ''){
				$.toast('请输入登录密码');
				return false;
			}
			if(data.opassword.length < 8){
				$.toast('登录密码不能少于8位');
				return false;
			}
			
			if(data.smscode == ''){
				$.toast('请输入验证码！');
				return false;
			}
			if(data.smscode.length < 6){
				$.toast('验证码只能是6位纯数字');
				return false;
			}
			if(data.password == ''){
				$.toast('请输入新密码！');
				return false;
			}	
			if(data.password2 == ''){
				$.toast('请输入确认密码！');
				return false;
			}
			if(data.password.length < 8 ){
				$.toast('新密码不能少于8位');
				return false;
			}
			if(data.password != data.password2 ){
				$.toast('新密码和确认密码不一致');
				return false;
			}
			$.post('/User/save_login_password',data,function(ret){
				if(ret.code == 1){
					$.toast("修改密码成功！");
					$.router.load('/Setting',true);
				}else $.toast(ret.msg);
			});				
		});
	});	
	
	/**
	* -----------------------------------------
	* 设置安全密码
	* -----------------------------------------
	* Create by lizuheng
	* -----------------------------------------
	* 2017-02-15
	* -----------------------------------------
	**/	
	$(document).on("pageInit", "#page-edit-set-password", function(e, id, page) {	
		$('.smscode.active').click(function(){
			var obj = $(this);
			if(obj.hasClass('active') == false) return; //还在倒计时中，直接退出
			
			$.post('/User/send_code',function(ret){
				if(ret.code == 1){
					obj.removeClass('active').html('<span class="dec_time">180</span>秒后重新获取');
					var timer = window.setInterval(function(){
						interval_smscode(obj);
					},1000);
					obj.data('timer',timer);
				}else $.toast(ret.msg);
			});
			
			
		});
		
		$('.btn-submit').click(function(){
			var data = getFormJson('#form-set-password');
			if(data.smscode == ''){
				$.toast('请输入验证码！');
				return false;
			}
			if(data.smscode.length < 6 ){
				$.toast('验证码只能是6位纯数字');
				return false;
			}
			if(data.password == ''){
				$.toast('请输入新密码！');
				return false;
			}	
			if(data.password2 == ''){
				$.toast('请输入确认密码！');
				return false;
			}
			if(data.password.length < 6 ){
				$.toast('新安全密码只能是6位纯数字');
				return false;
			}
			if(data.password != data.password2 ){
				$.toast('新安全密码和确认安全密码不一致');
				return false;
			}
			
			$.post('/User/save_set_password',data,function(ret){
				if(ret.code == 1){
					$.toast("设置安全密码成功！");
					$.router.load('/Setting',true);
				}else $.toast(ret.msg);
			});				
		});
	});	
	/**
	* -----------------------------------------
	* 修改安全密码
	* -----------------------------------------
	* Create by lizuheng
	* -----------------------------------------
	* 2017-02-07
	* -----------------------------------------
	**/	
	$(document).on("pageInit", "#page-edit-safe-password", function(e, id, page) {	
		$('.smscode.active').click(function(){
			var obj = $(this);
			if(obj.hasClass('active') == false) return; //还在倒计时中，直接退出
			
			$.post('/User/send_code',function(ret){
				if(ret.code == 1){
					obj.removeClass('active').html('<span class="dec_time">180</span>秒后重新获取');
					var timer = window.setInterval(function(){
						interval_smscode(obj);
					},1000);
					obj.data('timer',timer);
				}else $.toast(ret.msg);
			});
			
			
		});
		
		$('.btn-submit').click(function(){
			var data = getFormJson('#form-edit-password');
			if(data.opassword == ''){
				$.toast('请输入安全密码');
				return false;
			}
			if(data.opassword.length < 6 ){
				$.toast('安全密码只能是6位纯数字');
				return false;
			}
			if(data.smscode == ''){
				$.toast('请输入验证码！');
				return false;
			}
			if(data.smscode.length < 6 ){
				$.toast('验证码只能是6位纯数字');
				return false;
			}
			if(data.password == ''){
				$.toast('请输入新密码！');
				return false;
			}	
			if(data.password2 == ''){
				$.toast('请输入确认密码！');
				return false;
			}
			if(data.password.length < 6 ){
				$.toast('新安全密码只能是6位纯数字');
				return false;
			}
			if(data.password != data.password2 ){
				$.toast('新安全密码和确认安全密码不一致');
				return false;
			}
			$.post('/User/save_safe_password',data,function(ret){
				if(ret.code == 1){
					$.toast("修改安全密码成功！");
					$.router.load('/Setting',true);
				}else $.toast(ret.msg);
			});				
		});
	});	
	
	/**
	* -----------------------------------------
	* 忘记安全密码
	* -----------------------------------------
	* Create by lizuheng
	* -----------------------------------------
	* 2017-06-16
	* -----------------------------------------
	**/	
	$(document).on("pageInit", "#page-forget-safe-password", function(e, id, page) {	
		$('.smscode.active').click(function(){
			var obj = $(this);
			if(obj.hasClass('active') == false) return; //还在倒计时中，直接退出
			
			$.post('/User/send_code',function(ret){
				if(ret.code == 1){
					obj.removeClass('active').html('<span class="dec_time">180</span>秒后重新获取');
					var timer = window.setInterval(function(){
						interval_smscode(obj);
					},1000);
					obj.data('timer',timer);
				}else $.toast(ret.msg);
			});
		});
		
		$('.btn-submit').click(function(){
			var data = getFormJson('#form-forget-password');
			if(data.smscode == ''){
				$.toast('请输入验证码！');
				return false;
			}
			if(data.smscode.length < 6 ){
				$.toast('验证码只能是6位纯数字');
				return false;
			}
			if(data.password == ''){
				$.toast('请输入新密码！');
				return false;
			}	
			if(data.password2 == ''){
				$.toast('请输入确认密码！');
				return false;
			}
			if(data.password.length < 6 ){
				$.toast('新安全密码只能是6位纯数字');
				return false;
			}
			if(data.password != data.password2 ){
				$.toast('新安全密码和确认安全密码不一致');
				return false;
			}
			$.post('/User/forgot_pay_password',data,function(ret){
				if(ret.code == 1){
					$.toast("修改安全密码成功！");
					$.router.load('/Setting',true);
				}else $.toast(ret.msg);
			});				
		});
	});	

	/**
	* -----------------------------------------
	* 购物车
	* -----------------------------------------
	* Create by Lazycat
	* -----------------------------------------
	* 2017-02-05
	* -----------------------------------------
	**/		
	$(document).on("pageInit", "#page-cart", function(e, id, page) {
		//检测选择状态
		check_selected();	
		cart_total();
		
		//选择商品
		$('input[name="express_tpl_id"]').change(function(){
			//console.log($(this).is(':checked'));
			if($(this).is(':checked') == true){
				$(this).closest('ul').find('input[type="checkbox"]').not('input[disabled="disabled"]').prop('checked',true);				
				
				var disabled_num = $(this).closest('ul').find('input[disabled="disabled"].check-goods').size();
				var num = $(this).closest('ul').find('input[type="checkbox"].check-goods').size();
				//$.alert(num);
				//$.alert(disabled_num);
				if(num - disabled_num == 0){
					$(this).closest('ul').find('input[type="checkbox"]').prop('checked',false);
				}				
				
			}else{
				$(this).closest('ul').find('input[type="checkbox"]').not('input[disabled="disabled"]').prop('checked',false);
			}
			
			cart_total();
		});
		
		
		$('input[type="checkbox"].check-goods').change(function(){
			var num = $(this).closest('ul').find('input[type="checkbox"].check-goods:checked').size();
			if(num > 0) $(this).closest('ul').find('input[name="express_tpl_id"]').prop('checked',true);
			else $(this).closest('ul').find('input[name="express_tpl_id"]').prop('checked',false);
			
			cart_total();
		});
		
		
		//全选
		$('.check-all').change(function(){
			if($(this).is(':checked') == true){
				page.find('input[type="checkbox"]').not('input[disabled="disabled"]').prop('checked',true);	
			}else{
				page.find('input[type="checkbox"]').not('input[disabled="disabled"]').prop('checked',false);	
			}
			
			//检测选择状态
			check_selected();
			
			cart_total();
		});
		
		
		$('.btn-submit').click(function(){
			var size = page.find('input[type="checkbox"].check-goods:checked').size();
			if(size < 1) {
				$.toast('请至少选择一项商品！');
				return;
			}
			
			var data = getFormJson('#form-cart');
			//console.log(data);
			$.post('/Cart/set_selected',data,function(ret){
				if(ret.code == 1){
					//$.router.load('/Cart/selected_goods',true);
					window.location.href='/Cart/selected_goods';
				}else if(ret.code == 10){
					$.router.load('/Login');
				}else{
					$.alert(ret.msg);
				}
			});
		});
		
		//检测选择状态
		function check_selected(){
			//检查是否有不符合要求的店铺
			$('input[type="checkbox"].check-goods').each(function(){
				var num = $(this).closest('ul').find('input[type="checkbox"].check-goods:checked').size();
				if(num > 0) $(this).closest('ul').find('input[name="express_tpl_id"]').prop('checked',true);
				else $(this).closest('ul').find('input[name="express_tpl_id"]').prop('checked',false);				
			});				
		}				
		
		//金额统计
		function cart_total(){
			var style_num = page.find('input[type="checkbox"].check-goods:checked').size();
			$('.cart-footer .num').html(style_num);
			
			var size = page.find('input[type="checkbox"].check-goods:checked').size();
			var total_price = 0;
			var total_score = 0;
			
			if(size > 0){
				page.find('input[type="checkbox"].check-goods:checked').each(function(){
					var data = $(this).closest('li').data();
					var price = $(this).closest('li').find("#price").val();
					var num = $(this).closest('li').find("#num").val();
		
					var total = parseFloat(price*num);

					total_price+=total;
					total_score =total_price * data.score_ratio * 100;
			
					$('.cart-footer .total_price').html(total_price.toFixed(2));
					$('.cart-footer .total_score').html(total_score.toFixed(2));
				});
			}else{
				$('.cart-footer .total_price').html(total_price.toFixed(2));
				$('.cart-footer .total_score').html(total_score.toFixed(2));
			}
		}
	});	

	/**
	* -----------------------------------------
	* 购物车-选择收货地址
	* -----------------------------------------
	* Create by Lazycat
	* -----------------------------------------
	* 2017-02-16
	* -----------------------------------------
	**/
  	$(document).on("pageInit", "#page-cart-address", function(e, id, page) {
		var loading = false;
	  	var last_page = 1;
	  	var page_count = page.data('page_num');
	  	var max_page = page_count > 100 ? 100 : page_count; //最多100页 
        $.closeModal();
        if($('.popup-overlay').size() > 0) {
            $('.popup-overlay').remove();
        }
	  	function addItems(p) {
	  		var where = {
	  			p: p
	  		};
	  		$.get('/Cart/address_page', where, function(ret) {
	  			var html = template('tpl_address_item', {
	  				data: ret.data.list
	  			});
	  			$('.infinite-scroll .list-container').append(html);
	  			//alert(ret);
	  		});
	
	  	}
	
	  	$('.infinite-scroll-preloader').hide();
	
	  	$(page).on('infinite', function() {
	  		var last_page = parseInt($('#p').val());
	  		// 如果正在加载，则退出
	  		if(loading) return;
	  		// 设置flag
	  		loading = true;
	  		
	  		$('.infinite-scroll-preloader').show();
	  		// 模拟1s的加载过程
	  		setTimeout(function() {
	  			// 重置加载flag
	  			loading = false;
	  			if(last_page > max_page) {
	  				// 加载完毕，则注销无限加载事件，以防不必要的加载
	  				$.detachInfiniteScroll($('.infinite-scroll'));
	  				// 删除加载提示符
	  				$('.infinite-scroll-preloader').hide();
	  				return;
	  			}
	  			addItems(last_page);
	  			// 更新最后加载的序号
	  			last_page++;
	  			$('#p').val(last_page);
	  			$.refreshScroller();
	  		}, 1000);
	  	});
	  	
	  	
	  	page.find('li').click(function(){
	  		var address_id = $(this).data('id');
	  		var html = $(this).find('.item-inner').html();
	  		$.fn.cookie('trj_cart_address_id', address_id);
	  		$.fn.cookie('trj_cart_address',html);
	  		
	  		$(this).addClass('active').siblings().removeClass('active');
	  		goback(true);
	  	});
		  		  	
	  	
  	});	  
  	
  	$(document).on("pageInit", "#page-cart-next", function(e, id, page) {  	
		var address_id = $.fn.cookie('trj_cart_address_id');
		var address = $.fn.cookie('trj_cart_address');
		//$.alert(address);
		if(address_id && address){
			$('#address_id').val(address_id);
			$('.select-address .item-inner').html(address);
			
			$.fn.cookie('trj_cart_address_id',null);
			$.fn.cookie('trj_cart_address',null);
			
			express_price();
		}
		
		
		
		//计算运费
		function express_price(){
			var address_id = $('#address_id').val();
			if(address_id == ''){
				$.alert('请选择收货地址！');
				return false;
			}
			
			var data = {};
			$('.shop-list').each(function(index){
				var tmp = $(this).find('#express').data();
				tmp.address_id = address_id;
				tmp.express_type = $(this).find('.item-after span').data('value');
				data[index] = tmp;
			});
			
			//console.log(data);
			$.post('/Cart/express_price_multi',{data:data},function(ret){
				if(ret.code == 10){
					$.router.load('/Login');
				}else if(ret.code == 1){
					$.each(ret.data,function(key,item){
						var obj = $('[id="'+key+'"]');
						var pay_price = parseFloat(obj.find('#express').data('total_price'));
						obj.find('.express_price').html(item.express_price);
						
						pay_price += item.express_price;
						obj.find('.pay_price').html(pay_price.toFixed(2));
					});
					
					pay_price();
				}else{
					$.alert('更新运费失败！');
				}
			});
		}
		
		function pay_price(){
			var pay_price 	= 0;
			var score		= 0;
			$('.shop-list').each(function(index){
				pay_price 	+= parseFloat($(this).find('.pay_price').html());
				score		+= parseFloat($(this).find('.score').html());
			});
			
			$('.cart-footer .pay_price').html(pay_price.toFixed(2));
			$('.cart-footer .total_score').html(score.toFixed(2));
		}
		
		$('.btn-submit').click(function(){			
			var address_id = $('#address_id').val();
			if(address_id == '') {
				$.alert('请选择收货地址！');
				return;
			}			
			var data = getFormJson('#form-cart');
			//console.log(data);
			
			$.post('/Cart/create_orders',data,function(ret){
				//console.log(ret);
				if(ret.code == 10){
					$.router.load('/Login');
				}else if(ret.code == 1){
					//$.router.load('/Cart/orders_multi_view/o_no/'+ret.data.o_no,true);
					//console.log('/Cart/orders_multi_view/o_no/'+ret.data.o_no);
					location.href = '/Cart/orders_multi_view/o_no/'+ret.data.o_no;
				}else{
					$.alert('创建订单失败！<br />'+ret.msg);
				}
			});
			
		});
		
		//选择配送方式
		$('li[id="express"]').click(function(){
			var obj = $(this);
			var express_type = $(this).find('.item-after span').data('value');
			var html = $(this).closest('ul').find('.express_list').html();
			//$.alert(html);
			$('.popup-express .list-block ul').html(html);
			$('.popup-express .list-block ul li[data-express_type="'+express_type+'"] .item-media').html('<i class="iconfont icon-qidong text-success"></i>');
			
			$('.popup-express .list-block ul li').click(function(){
				var express_type = $(this).data('express_type');
				var express_name = $(this).find('.item-title').html();				
				//$.alert(express_type);
				//console.log(123456);
				//alert(express_type)
				//console.log(express_type);
				$("input[name='express_type']").val(express_type);
				//obj.data('express_type',express_type);
				obj.find('.item-after').html(express_name);
				
				obj.closest('.list-block').find('input[name="express_type"]').val(express_type);
				
				var address_id = $('#address_id').val();
				if(address_id != '') {
					express_price();
				}
				
				$.closeModal();
			});
		});
		
		//优惠
		$('li[id="is_sale"]').click(function(){
			var obj = $(this);
			var coupon_id 	= obj.closest('.list-block').find('#coupon_id').val();
			var odata		= obj.closest('.list-block').data();			
		
			var html = obj.find('.coupon-data').html();				
			$('.popup-sale ul').html(html);
			
			$('.popup-sale .list-block ul li').click(function(){
				$(this).toggleClass('selected');
				obj.find('.coupon-data').html($(this).closest('ul').html());
				
				var ids 		= new Array();
				var coupon_str	= new Array();
				var sale_price 	= 0;
				var use_type;	//优惠券使用场景，1个订单只能有一种使用场景的优惠券
				var goods_ids	= new Array();
				var category_ids= new Array();
				$('.popup-sale .list-block ul li.selected').each(function(){
					var tmp = $(this).data();
					var coupon_tmp 	= tmp.coupon_id + '|' + tmp.price + '|' + tmp.use_type + '|' + tmp.link_id;
					use_type		= tmp.use_type;
					//var coupon_val	= tmp.coupon_id + '|' + tmp.link_id;
					if(use_type == 3 && tmp.link_id) goods_ids.push(tmp.link_id);
					if(use_type == 4 && tmp.link_id) category_ids.push(tmp.link_id);
					ids.push(tmp.coupon_id);
					coupon_str.push(coupon_tmp);					
					sale_price += parseInt($(this).data('price'));
				});
				
				//console.log(use_type);
				//console.log(goods_ids);
				//console.log(category_ids);
				
				//if(sale_price > 0){
					obj.closest('.list-block').find('#coupon_id').val(coupon_str.join(','));
					obj.closest('.list-block').find('#coupon_id').attr('data-price',sale_price);				
					obj.closest('.list-block').find('#coupon_id').attr('data-ids',ids.join(','));				
					//console.log(ids);
					//console.log(sale_price);
					obj.closest('ul').find('.sale_price').html(sale_price);
					obj.closest('ul').find('.pay_price').html((odata.pay_price - sale_price).toFixed(2));
									
					//有优惠时，重计奖励积分
					//当优惠券为指定商品或指定类目时，重计指定商品或类目商品的积分				
					var score 		= 0;
					var items_price = 0;
					var item_size	= obj.closest('ul').find('.goods-item').size();
					obj.closest('ul').find('.goods-item').each(function(index){
						var item = $(this).data();						
						var item_point = item.total_price / odata.goods_price;		//金额在订单中占比
						var item_price = parseInt(sale_price * item_point * 100) / 100;	//不进行四舍五入，最后多出的分给最后一个
											
						if(index == item_size-1 && (sale_price - items_price) != item_price){
							item_price = sale_price - items_price;
						}									
							
						if(item_price < 0) item_price = 0;
						items_price += item_price;		
							
						score += (item.total_price - item_price) * item.score_ratio * 100;    

					});
					
					obj.closest('ul').find('.score').html(score.toFixed(2));					
				//}
				pay_price();
			});
		});

  	});  
  	
	/**
	* -----------------------------------------
	* 合并订单付款界面
	* -----------------------------------------
	* Create by Lazycat
	* -----------------------------------------
	* 2017-02-17
	* -----------------------------------------
	**/	
	$(document).on("pageInit", "#page-cart-orders-multi", function(e, id, page) {
		var timer;
		//选中第一个付款方式
		$('.pay-type li').not('.tips').click(function(){
			$(this).addClass('selected').siblings().removeClass('selected');
			
			var val = $(this).data('value');
			if(val !=1 && val !=2){
				$('.pay_pass').addClass('hide');
			}else $('.pay_pass').removeClass('hide');			
		});
		
		var timer;	//监听订单是否支付
		//支付
		$('.btn-submit').click(function() {
			var data = {};
			data.o_no = $('#o_no').val();
	//		data.pay_password = $('#pay_password').val();
	//		data.paytype = $('.pay-type li.selected').data('value');

			//收银台支付
			$.popup('.popup-cashier');
			var html = '<iframe src="/Cart/multiPay/o_no/' + data.o_no + '" frameborder="0" marginheight="0" marginwidth="0" height="100%" width="100%" scrolling="auto"></iframe>';
			$('.popup-cashier .content').html(html);
			
			timer = setInterval(function(){	//订单状态
				$.post('/Cart/check_orders_status',{o_no:data.o_no},function(ret){
					//console.log(ret);
					if(ret.code == 1){
						clearInterval(timer);
						$.closeModal();
						$.router.load('/Cart/pay_result/o_no/'+data.o_no,true);
					}
				});
			},3000);
		});	
		
/* 		$('.btn-submit').click(function() {	
			var paytype = [1,2];
			var data = {};
			data.o_no = $('#o_no').val();
			data.pay_password = $('#pay_password').val();
			data.paytype = $('.pay-type li.selected').data('value');
			
			if($.inArray(data.paytype,paytype) != -1){		//余额用唐宝支付			
				if(data.pay_password == ''){
					$.toast('请输入安全密码！');
					return;
				}				
				//console.log(data);
				
				$.post('/Cart/orders_multi_pay',data,function(ret){
					//console.log(ret);
					if(ret.code == 10){
						$.router.load('/Login');
					}else if(ret.code != 0){
						$('h1').html('支付结果');
						$('.bar-tab').remove();
						
						var html = template('tpl_orders_multi_pay_result',{data:ret.data});
						$('.content').html(html);
						
					}else{
						$.alert(ret.msg);
					}
				});
			}else{ //使用收银台
				$.popup('.popup-cashier');
				var html = '<iframe src="/Cart/multi_pay/o_no/' + data.o_no + '/paytype/' + data.paytype + '" frameborder="0" marginheight="0" marginwidth="0" height="100%" width="100%" scrolling="no"></iframe>';
				$('.popup-cashier .content').html(html);
				
				timer = setInterval(function(){
					$.post('/Cart/check_orders_status',{o_no:data.o_no},function(ret){
						//console.log(ret);
						if(ret.code == 1){
							clearInterval(timer);
							$.closeModal();
							$.router.load('/Cart/pay_result/o_no/'+data.o_no,true);
						}
					});
				},3000);				
			}
		});	 */			

		
		$('.popup-cashier [data-popup=".popup-cashier"]').click(function(){
			clearInterval(timer);
			$.confirm('是否已支付成功?', function () {
				var o_no = $('#o_no').val();
        		$.closeModal();
				$.router.load('/Cart/pay_result/o_no/'+o_no,true);
      		});	
		});		
	});	

		
  	
	
	 	
  	
  	/*
		$(document).on("beforePageSwitch", "#page-cart-address", function(e, id, page) {
			var obj = page.find('ul li.active');
			var address_id = obj.data('id');
			var html = obj.find('.item-inner').html();
			
			if(address_id){
				//$.alert(tpage.find('.select-address').html());
				console.log(address_id);
				tpage.find('#address_id').val(address_id);
			}
			
		});  	
	*/
	
	/**
	* -----------------------------------------
	* 买家添加/修改收货地址
	* -----------------------------------------
	* Create by Lazycat
	* -----------------------------------------
	* 2017-01-16
	* -----------------------------------------
	**/	
	$(document).on("pageInit", "#page-cart-address-add", function(e, id, page) {
		$('.btn-submit').click(function() {
			var data = getFormJson('#form-shopaddress-add');
			if(data.linkname == '') {
				$.toast("请输入联系人！");
				return false;
			}
			if(data.mobile == '' && data.tel == '') {
				$.toast("手机号码或电话号码两项必须填写一项！");
				return false;
			}
			
			if(data.province == '' || data.city == '' || data.district == '') {
				$.toast("请选择城市！");
				return false;
			}		
			
			if(data.street == '') {
				$.toast("请输入详细地址！");
				return false;
			}		
			
			var url = page.find('form').attr('action');

			$.post(url, data, function(ret) {
				if(ret.code == 1) $.router.load('/Cart/selected_goods/address_id/' + ret.id,true);
				else $.toast(ret.msg);
			});
	
		});
		
		//选择省份
		$('.popup-province .open-popup').click(function(){
			$(this).closest('li').addClass('active').siblings().removeClass('active');
			$('.popup-city ul').html('');
			$.get('/City/city_item/sid/'+$(this).data('id'),function(ret){
				if(ret.code == 1){
					var html = template('tpl_city_item',{data:ret.data});
					$('.popup-city ul').html(html);
				}else $.toast("读取城市列表失败！");
			});
		});	
	
	});	  	
	
	/**
	* -----------------------------------------
	* 关闭订单
	* -----------------------------------------
	* Create by Lazycat
	* -----------------------------------------
	* 2017-02-20
	* -----------------------------------------
	**/	
	$(document).on("pageInit", "#page-orders-close", function(e, id, page) {
		$('.btn-submit').click(function() {
			var data = getFormJson('#form-close-orders');

			if(data.reason == '') {
				$.toast("请输入关闭原因！");
				return false;
			}		
			
			$.post('/Orders/close_save', data, function(ret) {
				if(ret.code == 1) goback(true);
				else $.toast(ret.msg);
			});
	
		});
		
		//选择省份
		$('.popup-province .open-popup').click(function(){
			$(this).closest('li').addClass('active').siblings().removeClass('active');
			$('.popup-city ul').html('');
			$.get('/City/city_item/sid/'+$(this).data('id'),function(ret){
				if(ret.code == 1){
					var html = template('tpl_city_item',{data:ret.data});
					$('.popup-city ul').html(html);
				}else $.toast("读取城市列表失败！");
			});
		});	
	
	});	  	
	
	
	/**
	* -----------------------------------------
	* 订单详情 - 订单付款
	* -----------------------------------------
	* Create by Lazycat
	* -----------------------------------------
	* 2017-02-21
	* -----------------------------------------
	**/	
	$(document).on("pageInit", "#page-orders-view", function(e, id, page) {
		//选中第一个付款方式
		//$('.pay-type li').eq(0).addClass('selected');
	  	//选择付款方式
		$('.pay-type li').not('.tips').click(function(){
			$(this).addClass('selected').siblings().removeClass('selected');
			
			var val = $(this).data('value');
			if(val !=1 && val !=2){
				$('.pay_pass').addClass('hide');
			}else $('.pay_pass').removeClass('hide');
		});

        $('[data-popup=".popup-paytype"]').click(function(){	//付款方式
            var s_no = $(this).attr('data-s_no');
            var price = $(this).attr('data-price');
            var score_type = $(this).attr('data-score_type');
            console.log('<img sec="../images/icon_'+score_type+'.png" />');
            $('#form-pay #order-no').html(s_no);
            $('#form-pay #order-price').html('<img class="icon-price" src="/Apps/Mobile/View/default/Public/images/icon_'+score_type+'.png"/> '+price);
        });

        var timer;	//监听订单是否支付
		//付款
		$('.btn-submit-pay').click(function() {
			var data = getFormJson('#form-pay');

			//收银台支付
			$.popup('.popup-cashier');
			var html = '<iframe src="/Orders/ordersPay/s_no/' + data.s_no +'" frameborder="0" marginheight="0" marginwidth="0" height="100%" width="100%" scrolling="auto"></iframe>';
			$('.popup-cashier .content').html(html);
			
			timer = setInterval(function(){
				$.post('/Orders/check_orders_status',{s_no:data.s_no},function(ret){
					//console.log(ret);
					if(ret.code == 1){
						clearInterval(timer);
						$.closeModal();
						$.router.load('/Orders/pay_result/s_no/'+data.s_no,true);
					}
				});
			},3000);
		});	
		
/* 		$('.btn-submit-pay').click(function() {
			var paytype = [1,2];
			var data = getFormJson('#form-pay');
			data.paytype = $('.pay-type li.selected').data('value');
			if($.inArray(data.paytype,paytype) != -1){		//余额用唐宝支付
				if(data.pay_password == ''){
					$.toast('请输入安全密码！');
					return;
				}
				
				if(data.s_no == ''){
					$.toast('订单号不能为空！');
					return;
				}			
				
				//console.log(data);
				$.post('/Orders/orders_pay',data,function(ret){
					//console.log(ret);
					if(ret.code == 10){
						$.router.load('/Login');
					}else if(ret.code == 1){
						$.alert(ret.msg);
						window.location.reload();
					}else{
						$.alert(ret.msg);
					}
				});
			}else{	//收银台支付
				$.popup('.popup-cashier');
				var html = '<iframe src="/Orders/single_pay/s_no/' + data.s_no + '/paytype/' + data.paytype + '" frameborder="0" marginheight="0" marginwidth="0" height="100%" width="100%" scrolling="no"></iframe>';
				$('.popup-cashier .content').html(html);
				
				timer = setInterval(function(){
					$.post('/Orders/check_orders_status',{s_no:data.s_no},function(ret){
						//console.log(ret);
						if(ret.code == 1){
							clearInterval(timer);
							$.closeModal();
							$.router.load('/Orders/pay_result/s_no/'+data.s_no,true);
						}
					});
				},3000);
			}
		}); */
		
		$('.popup-cashier [data-popup=".popup-cashier"]').click(function(){
			clearInterval(timer);
			$.confirm('是否已支付成功?', function () {
				$.closeModal();
				var s_no = $('#form-pay #s_no').val();
				$.router.load('/Orders/pay_result/s_no/'+s_no,true);
        		//window.location.reload();
      		});			
			
		});
		
	  	
	  	//确认收货
		$('.btn-submit').click(function() {
			//$.alert('kk');
			var data = getFormJson('#form-receive');
			//$.alert(data.pay_password);
			if(data.pay_password == ''){
				// $.toast('请输入安全密码！');
				// return;
			}
			
			if(data.s_no == ''){
				$.toast('订单号不能为空！');
				return;
			}			

			//console.log(data);
			
			$.post('/Orders/receive',data,function(ret){
				if(ret.code == 10){
					$.router.load('/Login');
				}else if(ret.code == 1){
					$.toast(ret.msg);
					setTimeout(function(){
						$('.popup-overlay').remove();
						$.closeModal();
						window.location.reload();
						//$.router.load('/Orders/rate/s_no/'+data.s_no,true);
					},300);
				}else{
					$.alert(ret.msg);
				}				
			});
		});		
	
	});		
	
	
	/**
	* -----------------------------------------
	* 订单列表
	* -----------------------------------------
	* Create by Lazycat
	* -----------------------------------------
	* 2017-02-16
	* -----------------------------------------
	**/
  	$(document).on("pageInit", "#page-orders", function(e, id, page) {	
		var loading = false;
	  	var last_page = 1;
	  	var page_count = page.data('page_num');
	  	var max_page = page_count; //最多100页 
		
	  	function addItems(p) {
	  		var where = {
	  			p: p
	  		};
	  		var status = $('#status').val();
	  		if(status!='') where.status = status;
	  		
	  		$.get('/Orders/orders_page', where, function(ret) {
	  			var html = template('tpl_orders', {
	  				data: ret.data.list
	  			});
	  			$('.infinite-scroll .list-container').append(html);
	  			//alert(ret);
	  		});
	
	  	}
	
	  	$('.infinite-scroll-preloader').hide();
	
	  	$(page).on('infinite', function() {
	  		var last_page = parseInt($('#p').val());
	  		// 如果正在加载，则退出
	  		if(loading) return;
	  		// 设置flag
	  		loading = true;	  		
	  		$('.infinite-scroll-preloader').show();
	  		// 模拟1s的加载过程
	  		setTimeout(function() {
	  			// 重置加载flag
	  			loading = false;
	  			if(last_page > max_page) {
	  				// 加载完毕，则注销无限加载事件，以防不必要的加载
	  				$.detachInfiniteScroll($('.infinite-scroll'));
	  				// 删除加载提示符
	  				$('.infinite-scroll-preloader').hide();
	  				return;
	  			}
	  			addItems(last_page);
	  			// 更新最后加载的序号
	  			last_page++;
	  			$('#p').val(last_page);
	  			$.refreshScroller();
	  		}, 1000);
	  	});
	  	
	  	$('[data-popup=".popup-receive"]').click(function(){	//付款
	  		var s_no = $(this).attr('data-s_no');
	  		$('#form-receive #s_no').val(s_no);
	  	});
	  	
	  	$('[data-popup=".popup-paytype"]').click(function(){	//付款方式
	  		var s_no = $(this).attr('data-s_no');
	  		// var is_tangbao_pay = $(this).data('is_tangbao_pay');
	  		// if(is_tangbao_pay === 0){
	  		// 	$('.popup-paytype li[data-value="2"]').addClass('hide');
	  		// }else{
	  		// 	$('.popup-paytype li[data-value="2"]').removeClass('hide');
	  		// }
            var price = $(this).attr('data-price');
            var score_type = $(this).attr('data-score_type');
            $('#form-pay #order-no').html(s_no);
            $('#form-pay #order-price').html('<img class="icon-price" src="/Apps/Mobile/View/default/Public/images/icon_'+score_type+'.png"/> '+price);
			$('#form-pay #s_no').val(s_no);
			$("#dtpay #s_no").val(s_no);
	  	});	  	
	  	
		
	  	//选择付款方式
		$('.pay-type li').not('.tips').click(function(){
			$(this).addClass('selected').siblings().removeClass('selected');
			
			var val = $(this).data('value');
			if(val !=1 && val !=2){
				$('.pay_pass').addClass('hide');
			}else $('.pay_pass').removeClass('hide');			
		});	  	
		
		var timer;	//监听订单是否支付		
		//付款
/* 		$('.btn-submit-pay').click(function() {
			var paytype = [1,2];
			var data = getFormJson('#form-pay');
			data.paytype = $('.pay-type li.selected').data('value');
			if($.inArray(data.paytype,paytype) != -1){		//余额用唐宝支付
				if(data.pay_password == ''){
					$.toast('请输入安全密码！');
					return;
				}
				
				if(data.s_no == ''){
					$.toast('订单号不能为空！');
					return;
				}			
				
				//console.log(data);
				$.post('/Orders/orders_pay',data,function(ret){
					//console.log(ret);
					if(ret.code == 10){
						$.router.load('/Login');
					}else if(ret.code == 1){						
						//window.location.reload();
						$('.list-container .list-block[data-s_no="'+ data.s_no +'"]').remove();
						$.alert(ret.msg);
					}else{
						$.alert(ret.msg);
					}
				});
			}else{	//收银台支付
				$.popup('.popup-cashier');
				var html = '<iframe src="/Orders/single_pay/s_no/' + data.s_no + '/paytype/' + data.paytype + '" frameborder="0" marginheight="0" marginwidth="0" height="100%" width="100%" scrolling="no"></iframe>';
				$('.popup-cashier .content').html(html);
				
				timer = setInterval(function(){
					$.post('/Orders/check_orders_status',{s_no:data.s_no},function(ret){
						//console.log(ret);
						if(ret.code == 1){
							clearInterval(timer);
							$.closeModal();							
							$('.list-container .list-block[data-s_on="'+ data.s_no +'"]').remove();
							$.alert('支付成功！');
						}
					});
				},3000);
			}
		}); */
		
		$('.btn-submit-pay').click(function() {
			var data = getFormJson('#form-pay');
			//收银台支付
			$.popup('.popup-cashier');
			var html = '<iframe src="/Orders/ordersPay/s_no/' + data.s_no + '" frameborder="0" marginheight="0" marginwidth="0" height="100%" width="100%" scrolling="auto"></iframe>';
			$('.popup-cashier .content').html(html);
			
			timer = setInterval(function(){
				$.post('/Orders/check_orders_status',{s_no:data.s_no},function(ret){
					//console.log(ret);
					if(ret.code == 1){
						clearInterval(timer);
						$.closeModal();							
						$('.list-container .list-block[data-s_on="'+ data.s_no +'"]').remove();
						$.alert('支付成功！');
						window.location.reload();
					}
				});
			},3000);
		});	
		
		$('.popup-cashier [data-popup=".popup-cashier"]').click(function(){
			clearInterval(timer);
			$.confirm('是否已支付成功?', function () {
				$.closeModal();
        		var s_no = $(this).find('#s_no').val();
        		$('.list-container .list-block[data-s_no="'+ data.s_no +'"]').remove();
        		window.location.reload();
      		});	
			
		});		
		
	

	  	
	  	//确认收货
		$('.btn-submit').click(function() {
			var data = getFormJson('#form-receive');
			if(data.pay_password == ''){
				//$.toast('请输入安全密码！');
				//return;
			}
			
			if(data.s_no == ''){
				$.toast('订单号不能为空！');
				return;
			}			
			//console.log(data);
			
			$.post('/Orders/receive',data,function(ret){
				if(ret.code == 10){
					$.router.load('/Login');
				}else if(ret.code == 1){
					$.toast(ret.msg);
					//window.location.reload();
					setTimeout(function(){
						$('.popup-overlay').remove();
						$.closeModal();
                        window.location.reload();
						//$.router.load('/Orders/rate/s_no/'+data.s_no,true);
					},300);
					
				}else{
					$.alert(ret.msg);
				}				
			});
		});		  	
  	});	  	
	
	/**
	* -----------------------------------------
	* C+公告列表
	* -----------------------------------------
	* Create by lizuheng
	* -----------------------------------------
	* 2017-04-01
	* -----------------------------------------
	**/
  	$(document).on("pageInit", "#page-CNews", function(e, id, page) {	

  	
  	});	  		

	/**
	* -----------------------------------------
	* 购物车-选择收货地址
	* -----------------------------------------
	* Create by Lazycat
	* -----------------------------------------
	* 2017-02-16
	* -----------------------------------------
	**/
  	$(document).on("pageInit", "#page-trj-news", function(e, id, page) {	
		var loading = false;
	  	var last_page = 1;
	  	var page_count = page.data('page_num');
	  	var max_page = page_count > 100 ? 100 : page_count; //最多100页 
	
	  	function addItems(p) {
	  		var where = {
	  			p: p
	  		};
	  		$.get('/News/page', where, function(ret) {
	  			var html = template('tpl_news_list', {
	  				data: ret.data.list
	  			});
	  			$('.infinite-scroll .list-container').append(html);
	  			//alert(ret);
	  		});
	
	  	}
	
	  	$('.infinite-scroll-preloader').hide();
	
	  	$(page).on('infinite', function() {
	  		var last_page = parseInt($('#p').val());
	  		// 如果正在加载，则退出
	  		if(loading) return;
	  		// 设置flag
	  		loading = true;
	  		
	  		$('.infinite-scroll-preloader').show();
	  		// 模拟1s的加载过程
	  		setTimeout(function() {
	  			// 重置加载flag
	  			loading = false;
	  			if(last_page > max_page) {
	  				// 加载完毕，则注销无限加载事件，以防不必要的加载
	  				$.detachInfiniteScroll($('.infinite-scroll'));
	  				// 删除加载提示符
	  				$('.infinite-scroll-preloader').hide();
	  				return;
	  			}
	  			addItems(last_page);
	  			// 更新最后加载的序号
	  			last_page++;
	  			$('#p').val(last_page);
	  			$.refreshScroller();
	  		}, 1000);
	  	});	  	
	  	
  	});	 
	
	/**
	* -----------------------------------------
	* 通知中心
	* -----------------------------------------
	* Create by lizuheng
	* -----------------------------------------
	* 2017-02-21
	* -----------------------------------------
	**/
  	$(document).on("pageInit", "#page-notice", function(e, id, page) {	
		var loading = false;
	  	var last_page 			= 1;
	  	var last_page_in 		= 1;
		

		function addItems(p,tab_index) {
	  		var where = {
	  			p: p
	  		};
	  		var status = $('#status').val();
	  		if(status!='') where.status = status;
	  		
	  		$.get('/Notice/notice_page', where, function(ret) {
				if(ret.code==1){
					var html = template('tpl_notice_list', {
						data: ret.data.list
					});
					$('.infinite-scroll').eq(tab_index).find('.list-container').append(html);
				}
	  		});
	  	}
	  	$('.infinite-scroll-preloader').hide();

	  	$(page).on('infinite', function() {	
	  		var last_page1 		= parseInt($('.infinite-scroll').eq(0).find('#p').val());
	  		var last_page2   	= parseInt($('.infinite-scroll').eq(1).find('#p').val());
			var last_page3  	= parseInt($('.infinite-scroll').eq(2).find('#p').val());
	  		// 如果正在加载，则退出
	  		if(loading) return;
	  		// 设置flag
	  		loading = true;
	  		// 模拟1s的加载过程
	  		
	  		page.find('.infinite-scroll.active .infinite-scroll-preloader').show();	  		
	  		setTimeout(function() {
	  			// 重置加载flag
	  			loading = false;
	  			
	  			var tab_index 	= 0;
	  			var max_page	= page.find('.infinite-scroll.active').data('page_num');
	  			var this_page	= last_page1;
	  			
	  			if(page.find('.infinite-scroll.active').attr('id') == "tab2"){
	  				tab_index = 1;
	  				this_page = last_page2;
	  			}else if(page.find('.infinite-scroll.active').attr('id') == "tab3"){
					tab_index = 2;
	  				this_page = last_page3;
				}
	  			
	  			//$.alert(tab_index);
	  			//$.alert(this_page);
	  			
	  			if(this_page > max_page) {
	  				// 加载完毕，则注销无限加载事件，以防不必要的加载,同页面多个分页时此项要注释掉
	  				//$.detachInfiniteScroll(page.find('.infinite-scroll.active'));
	  				// 删除加载提示符
	  				page.find('.infinite-scroll.active .infinite-scroll-preloader').hide();
	  				return;
	  			} 	
	  			
	  			addItems(this_page,tab_index);
	  			
	  			// 更新最后加载的序号	  			
	  			switch(tab_index){
	  				case 1:
	  					last_page2++;
	  					$('.infinite-scroll').eq(tab_index).find('#p').val(last_page2);
	  				break;
					case 2:
	  					last_page3++;
	  					$('.infinite-scroll').eq(tab_index).find('#p').val(last_page3);
	  				break;
	  				default:
	  					last_page1++;
	  					$('.infinite-scroll').eq(tab_index).find('#p').val(last_page1);
	  			}
	  			$.refreshScroller();
	  		}, 1000);
	  	});
	});	


	/**
	* -----------------------------------------
	* 售后中心
	* -----------------------------------------
	* Create by lizuheng
	* -----------------------------------------
	* 2017-02-24
	* -----------------------------------------
	**/
  	$(document).on("pageInit", "#page-service", function(e, id, page) {		
		var loading = false;

	  	function addItems(p,tab_index) {	  		
	  		var where = {
	  			p: p,
	  		};
	  		
	  		if(tab_index == 1) where.status = "1";
	  		
	  		$.get('/Service/service_page', where, function(ret) {
	  			var html = template('tpl_service_list', {
	  				data: ret.data.list
	  			});
	  			$('.infinite-scroll').eq(tab_index).find('.list-container').append(html);
	  			//alert(ret);
	  		});
	
	  	}
	
	  	$('.infinite-scroll-preloader').hide();

	  	$(page).on('infinite', function() {	
	  		var last_page 		= parseInt($('.infinite-scroll').eq(0).find('#p').val());
	  		var last_page_in 	= parseInt($('.infinite-scroll').eq(1).find('#p').val());
	  		// 如果正在加载，则退出
	  		if(loading) return;
	  		// 设置flag
	  		loading = true;
	  		// 模拟1s的加载过程
	  		
	  		page.find('.infinite-scroll.active .infinite-scroll-preloader').show();	  		
	  		setTimeout(function() {
	  			// 重置加载flag
	  			loading = false;
	  			
	  			var tab_index 	= 0;
	  			var max_page	= page.find('.infinite-scroll.active').data('page_num');
	  			var this_page	= last_page;
	  			
	  			if(page.find('.infinite-scroll.active').attr('id') == "tab2"){
	  				tab_index = 1;
	  				this_page = last_page_in;
	  			}

	  			
	  			//$.alert(tab_index);
	  			//$.alert(this_page);
	  			
	  			if(this_page > max_page) {
	  				// 加载完毕，则注销无限加载事件，以防不必要的加载,同页面多个分页时此项要注释掉
	  				//$.detachInfiniteScroll(page.find('.infinite-scroll.active'));
	  				// 删除加载提示符
	  				page.find('.infinite-scroll.active .infinite-scroll-preloader').hide();
	  				return;
	  			} 			
	  			
	  			addItems(this_page,tab_index);
	  			
	  			// 更新最后加载的序号	  			
	  			switch(tab_index){
	  				case 1:
	  					last_page_in++;
	  					$('.infinite-scroll').eq(tab_index).find('#p').val(last_page_in);
	  				break;
	  				default:
	  					last_page++;
	  					$('.infinite-scroll').eq(tab_index).find('#p').val(last_page);
	  			}
	  			$.refreshScroller();
	  		}, 1000);
	  	});
	});	

	
	/**
	* -----------------------------------------
	* 售后详情
	* -----------------------------------------
	* Create by lizuheng
	* -----------------------------------------
	* 2017-02-27
	* -----------------------------------------
	**/
  	$(document).on("pageInit", "#page-service-view", function(e, id, page) {	
		//window.location.reload();
		$('.btn-cancel').click(function(){	
			var rid = $(this).attr('data-r_no');
			var sid = $(this).attr('data-s_no');
			$.confirm('确定取消售后申请吗?<br><span class="text-gray">取消售后后将不可再次发起售后申请，请慎重操作！</span>', function () {
				$.post('/Service/cancel',{r_no:rid,s_no:sid},function(ret){
					if(ret.code == 1){	
						$.toast(ret.msg);
						window.location.reload();
					}else $.toast('操作失败！');
				});
			});
		});
		
	  	//确认收货
		$('.btn-submit-receive').click(function() {
			var data = getFormJson('#form-receive');
			if(data.pay_password == ''){
				$.toast('请输入安全密码！');
				return;
			}
			
			if(data.s_no == ''){
				$.toast('订单号不能为空！');
				return;
			}			
			//console.log(data);
			
			$.post('/Service/buyer_receive',data,function(ret){
				if(ret.code == 10){
					$.router.load('/Login');
				}else if(ret.code == 1){
					$.toast(ret.msg);
					window.location.reload();					
				}else{
					$.alert(ret.msg);
				}				
			});
		});				
  	});
	/**
	* -----------------------------------------
	* 邮寄商品
	* -----------------------------------------
	* Create by lazycat
	* -----------------------------------------
	* 2017-03-27
	* -----------------------------------------
	**/
  	$(document).on("pageInit", "#page-service-send-express", function(e, id, page) {		  	
		$('.popup-company li').click(function(){
			var id = $(this).data('id');
			$('#express_company_id').val(id);

			$('.select-company').html($(this).find('.item-content').html());
			$.closeModal('.popup-company');
		});
		
		$('.btn-submit').click(function(){
			var data = getFormJson('#form-express-goods');
			if(data.express_company_id == ''){
				$.toast('请选择快递公司！');
				return;
			}
			var re =  "/^[0-9a-zA-Z]*$/g";
			if(data.express_company_id !=361){
				if(data.express_code == ''){
					$.toast('请输入快递单号！');
					return;
				}	
/* 				if(!re.test(data.express_code)){
					$.toast('请输入正确的快递单号！');
					return;
				}	 */				
			}

			//console.log(data);
			$.post('/Service/send_express',data,function(ret){

				//console.log(ret);
				if(ret.code == 10){
					$.router.load('/Login');
				}else if(ret.code == 1){
					goback(true);
				}else{
					$.alert(ret.msg);
				}				
			});
		});
  	});	
	
	/**
	* -----------------------------------------
	* 地址选择
	* -----------------------------------------
	* Create by lizuheng
	* -----------------------------------------
	* 2017-02-27
	* -----------------------------------------
	**/
  	$(document).on("pageInit", "#page-service-select-address", function(e, id, page) {	
		var loading = false;
	  	var last_page = 1;
	  	var page_count = page.data('page_num');
	  	var max_page = page_count > 100 ? 100 : page_count; //最多100页 
	
	  	function addItems(last_page) {
	  		var where = {
	  			p: last_page + 1
	  		};
	  		$.get('/Service/select_address_page', where, function(ret) {
	  			var html = template('tpl_address_item', {
	  				data: ret.data.list
	  			});
	  			$('.infinite-scroll .list-container').append(html);
	  			//alert(ret);
	  		});
	
	  	}
	
	  	$('.infinite-scroll-preloader').hide();
	
	  	$(page).on('infinite', function() {
	  		// 如果正在加载，则退出
	  		if(loading) return;
	  		// 设置flag
	  		loading = true;
	  		
	  		$('.infinite-scroll-preloader').show();
	  		// 模拟1s的加载过程
	  		setTimeout(function() {
	  			// 重置加载flag
	  			loading = false;
	  			if(last_page > max_page) {
	  				// 加载完毕，则注销无限加载事件，以防不必要的加载
	  				$.detachInfiniteScroll($('.infinite-scroll'));
	  				// 删除加载提示符
	  				$('.infinite-scroll-preloader').hide();
	  				return;
	  			}
	  			addItems(last_page);
	  			// 更新最后加载的序号
	  			last_page++;
	  			$.refreshScroller();
	  		}, 1000);
	  	});
	  	
	  	
	  	page.find('li').click(function(){
	  		var address_id = $(this).data('id');
	  		var html = $(this).find('.item-inner').html();
	  		$.fn.cookie('trj_service_address_id', address_id);
	  		$.fn.cookie('trj_service_address',html);
	  		
	  		$(this).addClass('active').siblings().removeClass('active');
	  		$.router.back();
	  	});
  	});
	
	/**
	* -----------------------------------------
	* 提出申诉
	* -----------------------------------------
	* Create by lizuheng
	* -----------------------------------------
	* 2017-02-28
	* -----------------------------------------
	**/
  	$(document).on("pageInit", "#page-service-appeal", function(e, id, page) {	
		$('#imageData').each(function(){
			var obj = $(this);

		   	obj.localResizeIMG({
		      	width: 600,
		      	quality: 1,
		      	success: function (result) {
		      		/*
		      		var tmp = result.base64.split(';');
		      		if(tmp[0] != 'data:image/jpeg'){
		      			$.alert('图片格式错误！');
		      			return;
		      		}
		      		*/
		      		
					if(obj.closest('.upload-box').find('.images-list img').size() >=5){
						$.toast('晒图最多只能传5张！');
						return;
					}		      		
					
				  	var submitData={
						base64_string:result.clearBase64, 
					};					
					//console.log(result);
					//console.log(submitData);
					$.post('/Upload/upload',submitData,function(ret){
						if(ret.code == 1){
							if(obj.closest('.upload-box').find('img[data-images="'+ret.data.url+'"]').size() > 0){
								$.toast('该图片已上传过！');
								return;
							}else{				
								var html = '<img src="'+ret.data.url+'" alt="晒图" data-images="'+ret.data.url+'" onclick="rate_images_remove($(this))">';
								obj.closest('.upload-box').find('.images-list').append(html);
								
								var images = new Array();
								obj.closest('.upload-box').find('.images-list img').each(function(index){
									images[index] = $(this).data('images');
								});
								
								obj.closest('.upload-box').find('#images').val(images.join(','));
							}
						}
					});
		      	}
		  	});			
		});
		
		$('.btn-submit').click(function(){
			var remark = $('#remark').val();
			if(remark == '') {
				$.alert('请填写申诉理由！');
				return;
			}
			var data = getFormJson('#form-appeal');
			//console.log(data);
			if(data.num == '') {
				$.alert('请填写申诉商品数量！');
				return;
			}
			if(data.num <= 0) {
				$.alert('请填写正确的售后数量！');
				return;
			}	
			$.post('/Service/appeal',data,function(ret){
				$.toast(ret.msg);
				if(ret.code == 1){
					goback(true);
				}
			});
		});
	  	
  	});	 
	/**
	* -----------------------------------------
	* 编辑申请售后
	* -----------------------------------------
	* Create by lizuheng
	* -----------------------------------------
	* 2017-03-01
	* -----------------------------------------
	**/
  	$(document).on("pageInit", "#page-service-edit", function(e, id, page) {	
		$('#imageData').each(function(){
			var obj = $(this);

		   	obj.localResizeIMG({
		      	width: 600,
		      	quality: 1,
		      	success: function (result) {
		      		/*
		      		var tmp = result.base64.split(';');
		      		if(tmp[0] != 'data:image/jpeg'){
		      			$.alert('图片格式错误！');
		      			return;
		      		}
		      		*/
		      		
					if(obj.closest('.upload-box').find('.images-list img').size() >=5){
						$.toast('晒图最多只能传5张！');
						return;
					}		      		
					
				  	var submitData={
						base64_string:result.clearBase64, 
					};					
					//console.log(result);
					//console.log(submitData);
					$.post('/Upload/upload',submitData,function(ret){
						if(ret.code == 1){
							if(obj.closest('.upload-box').find('img[data-images="'+ret.data.url+'"]').size() > 0){
								$.toast('该图片已上传过！');
								return;
							}else{				
								var html = '<img src="'+ret.data.url+'" alt="晒图" data-images="'+ret.data.url+'" onclick="rate_images_remove($(this))">';
								obj.closest('.upload-box').find('.images-list').append(html);
								
								var images = new Array();
								obj.closest('.upload-box').find('.images-list img').each(function(index){
									images[index] = $(this).data('images');
								});
								
								obj.closest('.upload-box').find('#images').val(images.join(','));
							}
						}
					});
		      	}
		  	});			
		});
		
		$('.btn-submit').click(function(){
			var num = $('#num').val();
			if(num == '') {
				$.alert('请输入申请数量！');
				return;
			}
			if(num <= 0) {
				$.alert('请填写正确的售后数量！');
				return;
			}			
			var reason = $('#reason').val();
			if(reason == '') {
				$.alert('请填写申诉原因！');
				return;
			}
			var data = getFormJson('#form-edit');
			//console.log(data);
			
			$.post('/Service/edit',data,function(ret){
				$.toast(ret.msg);
				if(ret.code == 1){
					$.router.load('/Service/view/r_no/'+data.r_no,true);
				}
			});
		});
	  	
  	});	 

	/**
	* -----------------------------------------
	* 申请售后
	* -----------------------------------------
	* Create by lizuheng
	* -----------------------------------------
	* 2017-03-03
	* -----------------------------------------
	**/
  	$(document).on("pageInit", "#page-service-add", function(e, id, page) {	
		$('#imageData').each(function(){
			var obj = $(this);

		   	obj.localResizeIMG({
		      	width: 600,
		      	quality: 1,
		      	success: function (result) {
		      		/*
		      		var tmp = result.base64.split(';');
		      		if(tmp[0] != 'data:image/jpeg'){
		      			$.alert('图片格式错误！');
		      			return;
		      		}
		      		*/
		      		
					if(obj.closest('.upload-box').find('.images-list img').size() >=5){
						$.toast('晒图最多只能传5张！');
						return;
					}		      		
					
				  	var submitData={
						base64_string:result.clearBase64, 
					};					
					//console.log(result);
					//console.log(submitData);
					$.post('/Upload/upload',submitData,function(ret){
						if(ret.code == 1){
							if(obj.closest('.upload-box').find('img[data-images="'+ret.data.url+'"]').size() > 0){
								$.toast('该图片已上传过！');
								return;
							}else{				
								var html = '<img src="'+ret.data.url+'" alt="晒图" data-images="'+ret.data.url+'" onclick="rate_images_remove($(this))">';
								obj.closest('.upload-box').find('.images-list').append(html);
								
								var images = new Array();
								obj.closest('.upload-box').find('.images-list img').each(function(index){
									images[index] = $(this).data('images');
								});
								
								obj.closest('.upload-box').find('#images').val(images.join(','));
							}
						}
					});
		      	}
		  	});			
		});
		
		$('.btn-submit').click(function(){
			var data = getFormJson('#form-add');
			if(data.num == '') {
				$.alert('请填写售后数量！');
				return;
			}
			if(data.num <= 0) {
				$.alert('请填写正确的售后数量！');
				return;
			}
			if(data.reason == '') {
				$.alert('请填写申请售后理由！');
				return;
			}
			//console.log(data);
			
			$.post('/Service/service_add',data,function(ret){
				$.toast(ret.msg);
				if(ret.code == 1){
					$.router.load('/Service/view/r_no/'+ret.data.r_no,true);
				}
			});
		});
	  	
  	});	
	
	/**
	* -----------------------------------------
	* 售后日志
	* -----------------------------------------
	* Create by lizuheng
	* -----------------------------------------
	* 2017-03-12
	* -----------------------------------------
	**/
  	$(document).on("pageInit", "#page-orders-service-logs", function(e, id, page) {	
  		$('.show-form').click(function(){
  			$('form').toggleClass('hide');
  			$(this).addClass('hide');
  		});
		$('#imageData').each(function(){
			var obj = $(this);

		   	obj.localResizeIMG({
		      	width: 600,
		      	quality: 1,
		      	success: function (result) {
		      		/*
		      		var tmp = result.base64.split(';');
		      		if(tmp[0] != 'data:image/jpeg'){
		      			$.alert('图片格式错误！');
		      			return;
		      		}
		      		*/
		      		
					if(obj.closest('.upload-box').find('.images-list img').size() >=5){
						$.toast('晒图最多只能传5张！');
						return;
					}		      		
					
				  	var submitData={
						base64_string:result.clearBase64, 
					};					
					//console.log(result);
					//console.log(submitData);
					$.post('/Upload/upload',submitData,function(ret){
						if(ret.code == 1){
							if(obj.closest('.upload-box').find('img[data-images="'+ret.data.url+'"]').size() > 0){
								$.toast('该图片已上传过！');
								return;
							}else{				
								var html = '<img src="'+ret.data.url+'" alt="晒图" data-images="'+ret.data.url+'" onclick="rate_images_remove($(this))">';
								obj.closest('.upload-box').find('.images-list').append(html);
								
								var images = new Array();
								obj.closest('.upload-box').find('.images-list img').each(function(index){
									images[index] = $(this).data('images');
								});
								
								obj.closest('.upload-box').find('#images').val(images.join(','));
							}
						}
					});
		      	}
		  	});			
		});
		
	
		
		$('.btn-submit').click(function(){
			var data = getFormJson('#form-logs-add');

		
			if(data.remark == ''){
				$.toast('请填写留言或备注！');
				return false;
			}		
			
			//console.log(data);
			$.post('/Service/logs_add',data,function(ret){
				//console.log(ret);
				if(ret.code == 10){
					$.router.load('/Login');
				}else if(ret.code == 1){
					window.location.reload();
				}else{
					$.alert(ret.msg);
				}
			});				
		});
	  	
  	});	
	/**
	* -----------------------------------------
	* 商品评价
	* -----------------------------------------
	* Create by Lazycat
	* -----------------------------------------
	* 2017-02-21
	* -----------------------------------------
	**/
  	$(document).on("pageInit", "#page-orders-rate", function(e, id, page) {	
		$('#imageData').each(function(){
			var obj = $(this);

		   	obj.localResizeIMG({
		      	width: 600,
		      	quality: 1,
		      	success: function (result) {
		      		/*
		      		var tmp = result.base64.split(';');
		      		if(tmp[0] != 'data:image/jpeg'){
		      			$.alert('图片格式错误！');
		      			return;
		      		}
		      		*/
		      		
					if(obj.closest('.upload-box').find('.images-list img').size() >=5){
						$.toast('晒图最多只能传5张！');
						return;
					}		      		
					
				  	var submitData={
						base64_string:result.clearBase64, 
					};					
					//console.log(result);
					//console.log(submitData);
					$.post('/Upload/upload',submitData,function(ret){
						if(ret.code == 1){
							if(obj.closest('.upload-box').find('img[data-images="'+ret.data.url+'"]').size() > 0){
								$.toast('该图片已上传过！');
								return;
							}else{				
								var html = '<img src="'+ret.data.url+'" alt="晒图" data-images="'+ret.data.url+'" onclick="rate_images_remove($(this))">';
								obj.closest('.upload-box').find('.images-list').append(html);
								
								var images = new Array();
								obj.closest('.upload-box').find('.images-list img').each(function(index){
									images[index] = $(this).data('images');
								});
								
								obj.closest('.upload-box').find('#images').val(images.join(','));
							}
						}
					});
		      	}
		  	});			
		});
		
		$('.btn-submit').click(function(){
			var data = getFormJson('#form-rate');
			//console.log(data);
			
			$.post('/Orders/rate_save',data,function(ret){
				//console.log(ret);
				if(ret.code == 10){
					$.router.load('/Login');
				}else if(ret.code == 1){
					goback(true);
				}else{
					$.alert(ret.msg);
				}
			});
		});
	  	
  	});	 	


	/**
	* -----------------------------------------
	* 退款列表
	* -----------------------------------------
	* Create by Lazycat
	* -----------------------------------------
	* 2017-02-24
	* -----------------------------------------
	**/		
	$(document).on("pageInit", "#page-refund", function(e, id, page) {		
		//page.find('.content').scrollTop(0);
		var loading = false;
	  	var last_page 			= 1;
	  	var last_page_in 		= 1;
		

	  	function addItems(p,tab_index) {	  		
	  		var where = {
	  			p: p,
	  		};
	  		
	  		if(tab_index == 1) where.status = 1;
	  		
	  		$.get('/Refund/refund_page', where, function(ret) {
	  			var html = template('tpl_refund_list', {
	  				data: ret.data.list
	  			});
	  			$('.infinite-scroll').eq(tab_index).find('.list-container').append(html);
	  			//alert(ret);
	  		});
	
	  	}
	
	  	$('.infinite-scroll-preloader').hide();

	  	$(page).on('infinite', function() {	
	  		var last_page 		= parseInt($('.infinite-scroll').eq(0).find('#p').val());
	  		var last_page_in 	= parseInt($('.infinite-scroll').eq(1).find('#p').val());
	  		// 如果正在加载，则退出
	  		if(loading) return;
	  		// 设置flag
	  		loading = true;
	  		// 模拟1s的加载过程
	  		
	  		page.find('.infinite-scroll.active .infinite-scroll-preloader').show();	  		
	  		setTimeout(function() {
	  			// 重置加载flag
	  			loading = false;
	  			
	  			var tab_index 	= 0;
	  			var max_page	= page.find('.infinite-scroll.active').data('page_num');
	  			var this_page	= last_page;
	  			
	  			if(page.find('.infinite-scroll.active').attr('id') == "tab2"){
	  				tab_index = 1;
	  				this_page = last_page_in;
	  			}

	  			
	  			//$.alert(tab_index);
	  			//$.alert(this_page);
	  			
	  			if(this_page > max_page) {
	  				// 加载完毕，则注销无限加载事件，以防不必要的加载,同页面多个分页时此项要注释掉
	  				//$.detachInfiniteScroll(page.find('.infinite-scroll.active'));
	  				// 删除加载提示符
	  				page.find('.infinite-scroll.active .infinite-scroll-preloader').hide();
	  				return;
	  			} 			
	  			
	  			addItems(this_page,tab_index);
	  			
	  			// 更新最后加载的序号	  			
	  			switch(tab_index){
	  				case 1:
	  					last_page_in++;
	  					$('.infinite-scroll').eq(tab_index).find('#p').val(last_page_in);
	  				break;
	  				default:
	  					last_page++;
	  					$('.infinite-scroll').eq(tab_index).find('#p').val(last_page);
	  			}
	  			$.refreshScroller();
	  		}, 1000);
	  	});
	});	


	/**
	* -----------------------------------------
	* 商品退款
	* -----------------------------------------
	* Create by Lazycat
	* -----------------------------------------
	* 2017-02-24
	* -----------------------------------------
	**/
  	$(document).on("pageInit", "#page-orders-refund-goods", function(e, id, page) {	
		$('.btn-submit').click(function(){
			var data = getFormJson('#form-refund-goods');
			//console.log(data);
			var max_num = $('#num').data('max');
			
			if(data.num==''){
				$.toast('请输入退回数量！');
				return false;				
			}
			if(data.num<0 || data.num > max_num){
				$.toast('请输入正确的数量，最多可退'+max_num+'件！');
				return false;				
			}
			
			if($('#express_price').size()>0){
				var max_express_price = $('#express_price').data('max');
				if(data.express_price<0 || data.express_price > max_express_price){
					$.toast('请输入正确的运费金额，最多可退￥'+max_express_price+'！');
					return false;				
				}					
			}
			
			if(data.reason == ''){
				$.toast('请填写退款原因！');
				return false;
			}		
			
			$.post('/Refund/add',data,function(ret){
				//console.log(ret);
				if(ret.code == 10){
					$.router.load('/Login');
				}else if(ret.code == 1){

					$.router.load('/Refund/view/r_no/'+ret.data.r_no,true);
				}else{
					$.alert(ret.msg);
				}
			});				
		});
	  	
  	});	 	

	/**
	* -----------------------------------------
	* 商品退款详情页
	* -----------------------------------------
	* Create by Lazycat
	* -----------------------------------------
	* 2017-02-24
	* -----------------------------------------
	**/
  	$(document).on("pageInit", "#page-refund-view", function(e, id, page) {	
		//未发货取消退款
		$('.refund-cancel').click(function(){
			var r_no = $(this).attr('data-r_no');
			
			$.confirm('确定要取消退款吗？',function(){
				$.post('/Refund/cancel',{r_no:r_no},function(ret){

					//console.log(ret);					
					if(ret.code == 10){
						$.router.load('/Login');
					}else if(ret.code == 1){
						window.location.reload();
					}else{
						$.alert(ret.msg);
					}					
					
				});
			});
		});

		//已发货取消退款
		$('.refund-cancel2').click(function(){
			var r_no = $(this).attr('data-r_no');
			
			$.confirm('确定要取消退款吗？<br><span class="text-gray">申请退款累积金额(含取消)超过订购商品的金额将不可再次发起退款申请！</span>',function(){
				$.post('/Refund2/cancel',{r_no:r_no},function(ret){
					//console.log(ret);					
					if(ret.code == 10){
						$.router.load('/Login');
					}else if(ret.code == 1){
						window.location.reload();
					}else{
						$.alert(ret.msg);
					}					
					
				});
			});
		});
	  	
	  	
  	});	 



	/**
	* -----------------------------------------
	* 已发货商品退款
	* -----------------------------------------
	* Create by Lazycat
	* -----------------------------------------
	* 2017-02-28
	* -----------------------------------------
	**/
  	$(document).on("pageInit", "#page-refund2-goods,#page-refund2-edit", function(e, id, page) {	
  		/*
  		var can_num = $('#num').data('max');
  		if(can_num < 1){
  			$('#li-num').addClass('hide');
  			$('#type').val(2);
  			$('[data-popup=".popup-refundtype"] .item-after').html('只退款');
  		}*/
  		
		$('#imageData').each(function(){
			var obj = $(this);

		   	obj.localResizeIMG({
		      	width: 600,
		      	quality: 1,
		      	success: function (result) {
		      		/*
		      		var tmp = result.base64.split(';');
		      		if(tmp[0] != 'data:image/jpeg'){
		      			$.alert('图片格式错误！');
		      			return;
		      		}
		      		*/
		      		
					if(obj.closest('.upload-box').find('.images-list img').size() >=5){
						$.toast('晒图最多只能传5张！');
						return;
					}		      		
					
				  	var submitData={
						base64_string:result.clearBase64, 
					};					
					//console.log(result);
					//console.log(submitData);
					$.post('/Upload/upload',submitData,function(ret){
						if(ret.code == 1){
							if(obj.closest('.upload-box').find('img[data-images="'+ret.data.url+'"]').size() > 0){
								$.toast('该图片已上传过！');
								return;
							}else{				
								var html = '<img src="'+ret.data.url+'" alt="晒图" data-images="'+ret.data.url+'" onclick="rate_images_remove($(this))">';
								obj.closest('.upload-box').find('.images-list').append(html);
								
								var images = new Array();
								obj.closest('.upload-box').find('.images-list img').each(function(index){
									images[index] = $(this).data('images');
								});
								
								obj.closest('.upload-box').find('#images').val(images.join(','));
							}
						}
					});
		      	}
		  	});			
		});
		
	
		$('.refund-type li').click(function(){
			$(this).addClass('selected').siblings().removeClass('selected');
			$('[data-popup=".popup-refundtype"] .item-after').html($(this).find('.item-title').html());
			
			var type = $(this).data('value');
			$('#type').val(type);
			
			if(type == 1) {
				$('#li-num').removeClass('hide');
				$('#li-money').removeClass('hide');
			}else{
				$('#li-num').addClass('hide');
				$('#num').val('');
			}
			
		});	
		
		$('.btn-submit').click(function(){
			var data = getFormJson('#form-refund-goods');
			//console.log(data);
			var max_num = $('#num').data('max');
			var max_price = $('#price').data('max');
			if($('#express_price').size()>0) var max_express_price = $('#express_price').data('max');
			
			if(!data.num) data.num =0;
			if(!data.price) data.price=0;
			if(!data.express_price) data.express_price=0;
			
			//退货并退款
			if(data.type==1){
				if(data.num<1 || data.num > max_num){
					$.toast('请输入正确的数量，最多可退'+max_num+'件！');
					return false;				
				}	
				
				if(data.price == 0){
					$.toast('请填写退款金额！');
					return false;
				}
				
			}else{	//只退款
				data.num = 0;
				if(data.price == 0 && data.express_price ==0){
					$.toast('退款金额或退运费必须填写一项！');
					return false;
				}
			}
			
			
			if(data.price <0 || data.price > max_price){
				$.toast('请输入正确的退款金额，最多可退'+max_price+'元！');
				return false;
			}		
				
			if(data.express_price <0 || data.express_price > max_express_price){
				$.toast('请输入正确的退运费金额，最多可退'+max_express_price+'元！');
				return false;
			}

			
			if(data.reason == ''){
				$.toast('请填写退款原因！');
				return false;
			}		
			
			var url = $('#form-refund-goods').data('url');
			//console.log(data);
			$.post(url,data,function(ret){

				//console.log(ret);
				if(ret.code == 10){
					$.router.load('/Login');
				}else if(ret.code == 1){
					$.router.load('/Refund/view/r_no/'+ret.data.r_no,true);
				}else{
					$.alert(ret.msg);
				}
			});				
		});
	  	
  	});	


	/**
	* -----------------------------------------
	* 已发货商品退款
	* -----------------------------------------
	* Create by Lazycat
	* -----------------------------------------
	* 2017-02-28
	* -----------------------------------------
	**/
  	$(document).on("pageInit", "#page-orders-refund-logs", function(e, id, page) {	
  		$('.show-form').click(function(){
  			$('form').toggleClass('hide');
  			$(this).addClass('hide');
  		});
		$('#imageData').each(function(){
			var obj = $(this);

		   	obj.localResizeIMG({
		      	width: 600,
		      	quality: 1,
		      	success: function (result) {
		      		/*
		      		var tmp = result.base64.split(';');
		      		if(tmp[0] != 'data:image/jpeg'){
		      			$.alert('图片格式错误！');
		      			return;
		      		}
		      		*/
		      		
					if(obj.closest('.upload-box').find('.images-list img').size() >=5){
						$.toast('晒图最多只能传5张！');
						return;
					}		      		
					
				  	var submitData={
						base64_string:result.clearBase64, 
					};					
					//console.log(result);
					//console.log(submitData);
					$.post('/Upload/upload',submitData,function(ret){
						if(ret.code == 1){
							if(obj.closest('.upload-box').find('img[data-images="'+ret.data.url+'"]').size() > 0){
								$.toast('该图片已上传过！');
								return;
							}else{				
								var html = '<img src="'+ret.data.url+'" alt="晒图" data-images="'+ret.data.url+'" onclick="rate_images_remove($(this))">';
								obj.closest('.upload-box').find('.images-list').append(html);
								
								var images = new Array();
								obj.closest('.upload-box').find('.images-list img').each(function(index){
									images[index] = $(this).data('images');
								});
								
								obj.closest('.upload-box').find('#images').val(images.join(','));
							}
						}
					});
		      	}
		  	});			
		});
		
	
		
		$('.btn-submit').click(function(){
			var data = getFormJson('#form-logs-add');

		
			if(data.remark == ''){
				$.toast('请填写留言或备注！');
				return false;
			}		
			
			//console.log(data);
			$.post('/Refund/logs_add',data,function(ret){
				//console.log(ret);
				if(ret.code == 10){
					$.router.load('/Login');
				}else if(ret.code == 1){
					window.location.reload();
				}else{
					$.alert(ret.msg);
				}
			});				
		});
	  	
  	});	

	/**
	* -----------------------------------------
	* 已发货退货－寄回商品
	* -----------------------------------------
	* Create by Lazycat
	* -----------------------------------------
	* 2017-02-28
	* -----------------------------------------
	**/
  	$(document).on("pageInit", "#page-refund-express-goods", function(e, id, page) {	
		$('.popup-company li').click(function(){
			var id = $(this).data('id');
			$('#express_company_id').val(id);

			$('.select-company').html($(this).find('.item-content').html());
			$.closeModal('.popup-company');
		});
		
		$('.btn-submit').click(function(){
			var data = getFormJson('#form-express-goods');
			if(data.express_company_id == ''){
				$.toast('请选择快递公司！');
				return;
			}
			var re =  "/^[0-9a-zA-Z]*$/g";
			if(data.express_company_id !=361){
				if(data.express_code == ''){
					$.toast('请输入快递单号！');
					return;
				}	
/* 				if(!re.match(data.express_code)){
					$.toast('请输入正确的快递单号！');
					return;
				}	 */				
			}

			//console.log(data);
			$.post('/Refund2/send_express',data,function(ret){

				//console.log(ret);
				if(ret.code == 10){
					$.router.load('/Login');
				}else if(ret.code == 1){
					goback(true);
				}else{
					$.alert(ret.msg);
				}				
			});
		});
	  	
  	});	
		
	/**
	* -----------------------------------------
	* 话费充值
	* -----------------------------------------
	* Create by lizuheng
	* -----------------------------------------
	* 2017-05-10
	* -----------------------------------------
	**/
  	$(document).on("pageInit", "#page-tellrecharge", function(e, id, page) {
		var mobile_type;	
		var par_value;      //充值面值
		var price_value;	//充值金额
		var fare_or_flow;   //充值话费还是流量1=话费，2=流量
		var recharge_style;  //充值方式。1=奖励积分。2=不奖励积分
		var recharge_mobile;//充值的手机号码
		var recharge_s_no;  //订单号
		
		//初始化手机号码判断
		$().ready(function(){
			recharge_mobile = $("input[name='mobile']").val();
			var operator = mobile_operator(recharge_mobile);

			mobile_type = operator;
			$('.flow').addClass("hide");
			$('.flow-'+operator).removeClass('hide');
			
			$('.flow').addClass("hide");
			$('.flow-'+operator).removeClass('hide');
		});
		$('.tellrecharge-tell').click(function(){
			var obj=$(this);
			//充值面值
			par_value = obj.attr('par_data');
			//充值金额
			price_value = obj.attr('price_data');
			//充值话费还是流量1=话费，2=流量
			fare_or_flow = obj.attr('type');

			$('.row a').removeClass('active');
			obj.addClass('active');

			if(par_value == '' || !par_value){
				$.alert('请选择充值金额！');
				return;
			}
			if(fare_or_flow == 1){
				recharge_style = $('.recharge-fare-type .selected').attr("data-value");

				var result1 = "面值"+par_value+"，支付"+price_value*11/10+"元; //，奖励"+price_value*100+"积分"
				var result2 = "面值"+par_value+"，支付"+price_value+"元";
				$(".fare_result1").text(result1);
				$(".fare_result2").text(result2);
				$(".recharge-fare-type").removeClass('hide');
				$(".btn-fare-pay").removeClass('hide');
			}else{
				recharge_style = $('.recharge-flow-type .selected').attr("data-value");
				var result1 = "面值"+par_value+"，支付"+price_value*11/10+"元;	//，奖励"+price_value*100+"积分"
				var result2 = "面值"+par_value+"，支付"+price_value+"元";
				$(".flow_result1").text(result1);
				$(".flow_result2").text(result2);
				$(".recharge-flow-type").removeClass('hide');
				$(".btn-flow-pay").removeClass('hide');
			}
		});
		
	
		$('#tab1 #mobile').on('keydown',function(event){
			var mobile = $(this).val();
			if(mobile.length == 3 && mobile.length <4) {
				var operator = mobile_operator(mobile);
				if($('#tab1 .tellrecharge[data-operator="'+operator+'"]').size() > 0) return;
				if(mobile_type && operator != mobile_type){
					par_value = "";
					$('.row a').removeClass('active');
				}
				mobile_type = operator;

				
				$('.fare').addClass("hide");
				$('.fare-'+operator).removeClass('hide');
			}
		});

		$('#tab2 #mobile').on('keydown',function(event){
			var mobile = $(this).val();
			if(mobile.length > 2 && mobile.length <4) {
				var operator = mobile_operator(mobile);
				//$.alert(operator);
				if($('#tab2 .tellrecharge[data-operator="'+operator+'"]').size() > 0) return;
				if(mobile_type && operator != mobile_type){
					par_value = "";
					$('.row a').removeClass('active');
				}
				mobile_type = operator;
				
				$('.flow').addClass("hide");
				$('.flow-'+operator).removeClass('hide');
			}
		});		

		//话费确认支付	
		$('.btn-fare-pay').click(function(){
			var obj=$(this);
			//充值手机号码
			recharge_mobile = obj.closest('.tab').find("input[name='mobile']").val();
			if(recharge_mobile == ''){
				$.alert('请输入手机号码！');
				return;
			}

			if(par_value == '' || !par_value){
				$.alert('请选择充值金额！');
				return;
			}
			if(!fare_or_flow || fare_or_flow == 2){
				$.alert('请选择话费充值金额！');
				return;
			}
			$.popup('.popup-recharge');
		});

		//流量确认支付	
		$('.btn-flow-pay').click(function(){
			var obj=$(this);
			//充值手机号码
			recharge_mobile = obj.closest('.tab').find("input[name='mobile']").val();
			if(recharge_mobile == ''){
				$.alert('请输入手机号码！');
				return;
			}

			if(par_value == '' || !par_value){
				$.alert('请选择充值金额！');
				return;
			}
			if(!fare_or_flow || fare_or_flow == 1){
				$.alert('请选择流量充值金额！');
				return;
			}
			$.popup('.popup-recharge');
		});		
		//话费选择充值方案
		$('.recharge-fare-type li').not('.tips_type').click(function(){
			$(this).addClass('selected').siblings().removeClass('selected');
			recharge_style = $(this).attr('data-value');		
		});		
		//流量选择充值方案
		$('.recharge-flow-type li').not('.tips_type').click(function(){
			$(this).addClass('selected').siblings().removeClass('selected');
			recharge_style = $(this).attr('data-value');		
		});			
		//选择付款方式
		var pay_type = $('.pay-type .selected').data("value");
		$('.pay-type li').not('.tips').click(function(){
			$(this).addClass('selected').siblings().removeClass('selected');
			pay_type = $(this).data('value');			
		});	

		var timer;	//监听订单是否支付
		$('.btn-submit-pay').click(function(){
			if(!recharge_style){
				$.alert('请选择充值方案！');
				return;
			}
			
			if(!pay_type){
				$.alert('请选择支付方式！');
				return;
			}
			
			//创建充值订单
			$.post("/TellRecharge/recharge",{fare:par_value,type:recharge_style,paytype:pay_type,mobile:recharge_mobile,recharge_type:fare_or_flow},function(ret){
				if(ret.code == 10){
					$.router.load('/Login');
				}else if(ret.code == 1){
					$.popup(".popup-cashier");
					recharge_s_no = ret.data.s_no;
					var html = '<iframe src="/TellRecharge/single_pay/s_no/' + ret.data.s_no + '/paytype/' + pay_type + '" frameborder="0" marginheight="0" marginwidth="0" height="100%" width="100%" scrolling="no"></iframe>';
					$('.popup-cashier .content').html(html);
					
					timer = setInterval(function(){
						$.post('/TellRecharge/check_orders_status',{s_no:ret.data.s_no},function(result){
							if(result.code == 1){
								clearInterval(timer);
								$('.row a').removeClass('active');
								$(".recharge-fare-type").addClass('hide');
								$(".btn-fare-pay").addClass('hide');
								$(".recharge-flow-type").addClass('hide');
								$(".btn-flow-pay").addClass('hide');
								$.closeModal();							
								$.alert(result.msg);
								$.router.load('/TellRecharge/view/s_no/'+ ret.data.s_no,true);
							}
						});
					},3000);
				}else{
					$.alert(ret.msg);
				}
			}); 
		});
		
		//结果返回
		$('.popup-cashier [data-popup=".popup-cashier"]').click(function(){
			clearInterval(timer);
			$.confirm('是否已支付成功?', function () {
				$.router.load('/TellRecharge/pay_result/s_no/'+ recharge_s_no,true);
				$.closeModal();	
/* 				$.post('/TellRecharge/pay_result',{s_no:recharge_s_no},function(result){
					if(result.code == 1){
						window.location.reload();						
					}else{
						$.router.load('/TellRecharge/error/msg/'+result.msg,true);
					}
				}); */
			});	
		});
  	});	
	
	/**
	* -----------------------------------------
	* 充值记录
	* -----------------------------------------
	* Create by lizuheng
	* -----------------------------------------
	* 2017-05-10
	* -----------------------------------------
	**/
  	$(document).on("pageInit", "#page-recharge-list", function(e, id, page) {		
		var loading = false;

	  	function addItems(p,tab_index) {	  		
	  		var where = {
	  			p: p,
	  		};
	  		
	  		if(tab_index == 1) where.status = "1";

	  		$.get('/TellRecharge/recharge_page', where, function(ret) {
	  			var html = template('tpl_recharge_list', {
	  				data: ret.data.list
	  			});
	  			$('.infinite-scroll').eq(tab_index).find('.list-block ul').append(html);
	  			//alert(ret);
	  		});
	
	  	}
	
	  	$('.infinite-scroll-preloader').hide();

	  	$(page).on('infinite', function() {	
	  		var last_page 		= parseInt($('.infinite-scroll').eq(0).find('#p').val());
	  		var last_page_in 	= parseInt($('.infinite-scroll').eq(1).find('#p').val());
	  		// 如果正在加载，则退出
	  		if(loading) return;
	  		// 设置flag
	  		loading = true;
	  		// 模拟1s的加载过程
	  		
	  		page.find('.infinite-scroll.active .infinite-scroll-preloader').show();	  		
	  		setTimeout(function() {
	  			// 重置加载flag
	  			loading = false;
	  			
	  			var tab_index 	= 0;
	  			var max_page	= page.find('.infinite-scroll.active').data('page_num');
	  			var this_page	= last_page;
	  			
	  			if(page.find('.infinite-scroll.active').attr('id') == "tab2"){
	  				tab_index = 1;
	  				this_page = last_page_in;
	  			}

	  			
	  			//$.alert(tab_index);
	  			//$.alert(this_page);
	  			
	  			if(this_page > max_page) {
	  				// 加载完毕，则注销无限加载事件，以防不必要的加载,同页面多个分页时此项要注释掉
	  				//$.detachInfiniteScroll(page.find('.infinite-scroll.active'));
	  				// 删除加载提示符
	  				page.find('.infinite-scroll.active .infinite-scroll-preloader').hide();
	  				return;
	  			} 			
	  			
	  			addItems(this_page,tab_index);
	  			
	  			// 更新最后加载的序号	  			
	  			switch(tab_index){
	  				case 1:
	  					last_page_in++;
	  					$('.infinite-scroll').eq(tab_index).find('#p').val(last_page_in);
	  				break;
	  				default:
	  					last_page++;
	  					$('.infinite-scroll').eq(tab_index).find('#p').val(last_page);
	  			}
	  			$.refreshScroller();
	  		}, 1000);
	  	});
	});	
	
	/**
	* -----------------------------------------
	* 话费充值详情(支付页面,申请退款)
	* -----------------------------------------
	* Create by lizuheng
	* -----------------------------------------
	* 2017-05-13
	* -----------------------------------------
	**/
  	$(document).on("pageInit", "#page-recharge-view", function(e, id, page) {		
		var par_value;     		//充值面值
		var price_value;		//充值金额
		var type; 				//充值方案，1=送积分，2=不送积分
		var order_s_no;           	//订单号
		$('.btn-pay').click(function(){
			var obj=$(this);
			//充值面值
			par_value = obj.attr('data-desc');
			//充值金额
			price_value = obj.attr('data-fare');
			//充值话费还是流量1=话费，2=流量
			type = obj.attr('data-type');
			//充值订单号
			order_s_no = obj.attr('data-s_no');			
			$.popup('.popup-recharge');
		});
		
		//选择付款方式
		var pay_type = $('.pay-type .selected').data("value");
		$('.pay-type li').not('.tips').click(function(){
			$(this).addClass('selected').siblings().removeClass('selected');
			pay_type = $(this).data('value');			
		});	
		
		var timer;	//监听订单是否支付
		$('.btn-submit-pay').click(function(){
			if(!pay_type){
				$.alert('请选择支付方式！');
				return;
			}
			if(!order_s_no){
				$.alert('订单号错误！');
				return;
			}	
			
			//支付充值订单
			$.popup(".popup-cashier");

			var html = '<iframe src="/TellRecharge/single_pay/s_no/' + order_s_no + '/paytype/' + pay_type + '" frameborder="0" marginheight="0" marginwidth="0" height="100%" width="100%" scrolling="no"></iframe>';
			$('.popup-cashier .content').html(html);
			
			timer = setInterval(function(){
				$.post('/TellRecharge/check_orders_status',{s_no:order_s_no},function(result){
					if(result.code == 1){
						clearInterval(timer);
						$.closeModal();							
						$.alert(result.msg);
						window.location.reload();
					}
				});
			},3000);
		});
		
		//结果返回
		$('.popup-cashier [data-popup=".popup-cashier"]').click(function(){
			clearInterval(timer);
			$.confirm('是否已支付成功?', function () {
				$.router.load('/TellRecharge/pay_result/s_no/'+ order_s_no,true);
				$.closeModal();	
			});	
		});

		//关闭订单
		$('.btn-close').click(function(){	
			var sid = $(this).attr('data-s_no');
			$.confirm('确认关闭订单吗?<br><span class="text-gray">关闭订单后将不可再付款，请慎重操作！</span>', function () {
				$.post('/TellRecharge/orders_close',{s_no:sid},function(ret){
					$.alert(ret.msg);
					if(ret.code == 1){	
						window.location.reload();
					}
				});
			});
		});				
		//申请退款
		$('.btn-refund').click(function(){	
			var sid = $(this).attr('data-s_no');
			$.confirm('确认申请退款吗?', function () {
				$.post('/TellRecharge/refund_add',{s_no:sid},function(ret){
					$.alert(ret.msg);
					if(ret.code == 1){	
						window.location.reload();
					}
				});
			});
		});		
		//取消退款
		$('.btn-cancel').click(function(){	
			var rid = $(this).attr('data-r_no');
			$.confirm('确定取消退款申请吗?<br><span class="text-gray">取消申请退款后将不可再次发起申请退款，请慎重操作！</span>', function () {
				$.post('/TellRecharge/refund_cancel',{r_no:rid},function(ret){
					$.alert(ret.msg);
					if(ret.code == 1){	
						window.location.reload();
					}
				});
			});
		});
  	});		
	
	/**
	* -----------------------------------------
	* 支付结果页面
	* -----------------------------------------
	* Create by lizuheng
	* -----------------------------------------
	* 2017-05-15
	* -----------------------------------------
	**/
  	$(document).on("pageInit", "#page-pay-result", function(e, id, page) {	
		//5秒后自动跳转
		var time = setTimeout(function(){
			clearTimeout(time);
			$.router.load('/TellRecharge',true);
		},2000);
		//clearTimeout(time);
		$('.back-stop').click(function(){	
			clearTimeout(time);
			goback(true);
		});		
  	});	
	
	/**
	* -----------------------------------------
	* 退款提交申诉
	* -----------------------------------------
	* Create by 梁丰
	* -----------------------------------------
	* 2017-03-03
	* -----------------------------------------
	**/
  	$(document).on("pageInit", "#page-refund2-appeal", function(e, id, page) {	
		$('#imageData').each(function(){
			var obj = $(this);

		   	obj.localResizeIMG({
		      	width: 600,
		      	quality: 1,
		      	success: function (result) {
		      		/*
		      		var tmp = result.base64.split(';');
		      		if(tmp[0] != 'data:image/jpeg'){
		      			$.alert('图片格式错误！');
		      			return;
		      		}
		      		*/
		      		
					if(obj.closest('.upload-box').find('.images-list img').size() >=5){
						$.toast('晒图最多只能传5张！');
						return;
					}		      		
					
				  	var submitData={
						base64_string:result.clearBase64, 
					};					
					//console.log(result);
					//console.log(submitData);
					$.post('/Upload/upload',submitData,function(ret){
						if(ret.code == 1){
							if(obj.closest('.upload-box').find('img[data-images="'+ret.data.url+'"]').size() > 0){
								$.toast('该图片已上传过！');
								return;
							}else{				
								var html = '<img src="'+ret.data.url+'" alt="晒图" data-images="'+ret.data.url+'" onclick="rate_images_remove($(this))">';
								obj.closest('.upload-box').find('.images-list').append(html);
								
								var images = new Array();
								obj.closest('.upload-box').find('.images-list img').each(function(index){
									images[index] = $(this).data('images');
								});
								
								obj.closest('.upload-box').find('#images').val(images.join(','));
							}
						}
					});
		      	}
		  	});			
		});
		
	
		
		
		$('.btn-submit').click(function(){
			var data = getFormJson('#form-refund-appeal');
			if(data.remark == ''){
				$.toast('请填写申诉原因！');
				return false;
			}		
			var url = $('#form-refund-appeal').data('url');			
			$.post(url,data,function(ret){
				if(ret.code == 10){
					$.router.load('/Login');
				}else if(ret.code == 1){
					goback(true);
				}else{
					$.alert(ret.msg);
				}
			});		
		
		});
	  	
  	});

  	/**
	 * -----------------------------------------
	 * Message index
	 * -----------------------------------------
	 * Create by liangfeng
	 * -----------------------------------------
	 * 2017-05-18
	 * -----------------------------------------
	 */
	$(document).on('pageInit','#Message-index',function(e, id, page){
		$.post('/Message/ajax_im_list',function(ret){
			console.log(ret);
			if(ret.code == 1){
				var html = template('tpl_im_list', {
					data: ret.data
				});
				$('.message_list ul').html(html);
				
				
				$('#Message-index .delete-message').on('click',function(){
				
					var obj = $(this);
					var from_name = obj.data('from_name');
					var to_name = obj.data('to_name');
				
					
					var buttons1 = [{
						  text: '是否删除聊天',
						  label: true
						},
						{
						  text: '删除',
						  bold: true,
						  color: 'danger',
						  onClick: function() {
							$.post('/Message/ajax_del_message',{fromName:from_name,toName:to_name},function(ret){
								if(ret.code == 1){
									$.toast("操作成功");
									if(ret.code == 1){
										obj.closest('.seller-message').remove();
									}
								}else $.toast(ret.msg);
							});
							
						  }
						},
						
					];
					var buttons2 = [{
						text: '取消',bg: 'danger'
					}];
					var groups = [buttons1, buttons2];
					$.actions(groups);
				});
				
				
			}
		});
		
	});
	
	/**
	 * -----------------------------------------
	 * Message info
	 * -----------------------------------------
	 * Create by Lzy
	 * -----------------------------------------
	 * 2017-04-24
	 * -----------------------------------------
	 */
	$(document).on('pageInit','#Seller-Message-info',function(e, id, page){
		$('.back-page').on('click',function(){
			$('#Seller-Message-info iframe').remove();
			$.router.back();
		});
	});


	

	/**
	* -----------------------------------------
	* 我的优惠券
	* -----------------------------------------
	* Create by Lazycat
	* -----------------------------------------
	* 2017-03-14
	* -----------------------------------------
	**/		
	$(document).on("pageInit", "#page-my-coupon", function(e, id, page) {		
		//page.find('.content').scrollTop(0);
		var loading = false;
	  	var last_page 			= 1;
	  	var last_page_in 		= 1;
		

	  	function addItems(p,tab_index) {	  		
	  		var where = {
	  			p: p,
	  		};
	  		
	  		if(tab_index == 1) where.is_expire = 1;
	  		else where.is_use = 1;
	  		
	  		$.get('/Coupon/my_coupon_page', where, function(ret) {
	  			var html = template('tpl_my_coupon', {
	  				data: ret.data.list
	  			});
	  			$('.infinite-scroll').eq(tab_index).find('.list-container ul').append(html);
	  			//alert(ret);
	  		});
	
	  	}
	
	  	$('.infinite-scroll-preloader').hide();

	  	$(page).on('infinite', function() {	
	  		var last_page 		= parseInt($('.infinite-scroll').eq(0).find('#p').val());
	  		var last_page_in 	= parseInt($('.infinite-scroll').eq(1).find('#p').val());
	  		// 如果正在加载，则退出
	  		if(loading) return;
	  		// 设置flag
	  		loading = true;
	  		// 模拟1s的加载过程
	  		
	  		page.find('.infinite-scroll.active .infinite-scroll-preloader').show();	  		
	  		setTimeout(function() {
	  			// 重置加载flag
	  			loading = false;
	  			
	  			var tab_index 	= 0;
	  			var max_page	= page.find('.infinite-scroll.active').data('page_num');
	  			var this_page	= last_page;
	  			
	  			if(page.find('.infinite-scroll.active').attr('id') == "tab2"){
	  				tab_index = 1;
	  				this_page = last_page_in;
	  			}

	  			
	  			//$.alert(tab_index);
	  			//$.alert(this_page);
	  			
	  			if(this_page > max_page) {
	  				// 加载完毕，则注销无限加载事件，以防不必要的加载,同页面多个分页时此项要注释掉
	  				//$.detachInfiniteScroll(page.find('.infinite-scroll.active'));
	  				// 删除加载提示符
	  				page.find('.infinite-scroll.active .infinite-scroll-preloader').hide();
	  				return;
	  			} 			
	  			
	  			addItems(this_page,tab_index);
	  			
	  			// 更新最后加载的序号	  			
	  			switch(tab_index){
	  				case 1:
	  					last_page_in++;
	  					$('.infinite-scroll').eq(tab_index).find('#p').val(last_page_in);
	  				break;
	  				default:
	  					last_page++;
	  					$('.infinite-scroll').eq(tab_index).find('#p').val(last_page);
	  			}
	  			$.refreshScroller();
	  		}, 1000);
	  	});
	});	
	
	
	/**
	* -----------------------------------------
	* 秒杀频道
	* -----------------------------------------
	* Create by Lazycat
	* -----------------------------------------
	* 2017-03-31
	* -----------------------------------------
	**/		
	$(document).on("pageInit", "#page-miaosha-index", function(e, id, page) {		
        var myScroll = new IScroll('.iscroll-time-bar', {  
            scrollX: true,  
            scrollY: false, 
            click:iScrollClick(),
            mouseWheel: true,
            //eventPassthrough: true,
            preventDefault: false
        }); 		
		var data = $('.miaosha-time-box2').data();
		miaosha_time_dec(data.time_dif,data.active==1?'距本场结束':'距本场开始');		
		myScroll.scrollToElement(document.querySelector('[data-daytime="'+data.daytime+'"]'));
		
 		
	});
	
	
	/**
	* -----------------------------------------
	* 合并订单 - 用于APP调用
	* -----------------------------------------
	* Create by Lazycat
	* -----------------------------------------
	* 2017-04-06
	* -----------------------------------------
	**/	
	$(document).on("pageInit", "#page-multi-pay", function(e, id, page) {
		var timer;
		var o_no = page.attr('data-o_no');
		timer = setInterval(function(){
			$.post('/Cart/check_orders_status',{o_no:o_no},function(ret){
				//console.log(ret);
				if(ret.code == 1){
					clearInterval(timer);
					$.router.load('/Cart/pay_result/nobar/1/o_no/'+o_no,true);
				}
			});
		},3000);	
	});	
	
	/**
	* -----------------------------------------
	* 单订单 - 用于APP调用
	* -----------------------------------------
	* Create by Lazycat
	* -----------------------------------------
	* 2017-04-06
	* -----------------------------------------
	**/	
	$(document).on("pageInit", "#page-single-pay", function(e, id, page) {
		var timer;
		var s_no = page.attr('data-s_no');
		timer = setInterval(function(){
			$.post('/Orders/check_orders_status',{s_no:s_no},function(ret){
				//console.log(ret);
				if(ret.code == 1){
					clearInterval(timer);
					$.router.load('/Orders/pay_result/nobar/1/s_no/'+s_no,true);
				}
			});
		},3000);
	});	
	/**
	* -----------------------------------------
	* 活动页 - 母亲节
	* -----------------------------------------
	* Create by liangfeng
	* -----------------------------------------
	* 2017-05-10
	* -----------------------------------------
	**/	
	$(document).on("pageInit", "#page-muqinjie-index", function(e, id, page) {
		$(".top").click(function(){
			$('.content').scrollTop(0);
		});
		$(document).on('refresh', '.pull-to-refresh-content',function(e) {
			location.reload(true);
		});
	});	


    /**
     * -----------------------------------------
     * 转盘 - 首页
     * -----------------------------------------
     * Create by Mercury
     * -----------------------------------------
     * 2017-05-03
     * -----------------------------------------
     **/
    $(document).on("pageInit", "#page-drawluck-index", function (e, id, page) {
		var d = $("#" + id).data('date');
        $("#my-calendar").calendar({
            value: [d]
        });
		$("#" + id + " .btn-sign").click(function () {
			var that = $(this);
			$.post('/drawluck/free', {}, function (ret) {
                $.toast(ret.msg);
				if (ret.code == 1) {
                    that.addClass('disabled').removeClass('btn-sign').removeClass('button-danger').css('display','inline-block');
				}
            }, 'json');
        });
    });

    /**
     * -----------------------------------------
     * 玩法 - 详情页
     * -----------------------------------------
     * Create by Mercury
     * -----------------------------------------
     * 2017-05-03
     * -----------------------------------------
     **/
    $(document).on("pageInit", "#page-drawluck-item", function (e, id, page) {
    	var position = $("#" + id).data('position');
        $(".drawBtn").click(function () {
			var isClick = parseInt($("#" + id).data('click'));
			if (isClick == 0) return;
			$("#" + id).attr('data-click', 0);
        	var _id = $("#" + id).data('id');
            $.post('/drawluck/post', {id:_id}, function (ret) {
                if (ret.code == 1) {
                    draw.goto(ret.data.sort, ret.data);
                } else if (ret.code == 0) {	//操作失败
                    $("#" + id).attr('data-click', 1);
                    $.toast(ret.msg);
				} else if (ret.code == -1) {	//次数不够
                    var h = template('tpl_luckdraw_buy', {
                        data: ret.data
                    });
                    $("#" + id + ' .alert-luckdraw-back').css('display','block').html(h);
				} else if(ret.code == 401) {
                    $("#" + id).attr('data-click', 1);
                    $.router.load('/Ucenter',true);
				} else if (ret.code == 402) {
                    $("#" + id).attr('data-click', 1);
                    $.toast(ret.msg);
                    $.router.load('/User/set_safe_password',true);
                }
            }, 'json');
        })

        var draw = new turntableDraw('.drawBtn',{
            share:position,
            speed:"3s",
            velocityCurve:"ease",
            weeks:6,
            callback:function(num, data)
            {
                luckdrawCallback(data);
            },
        });
    });

    /**
     * -----------------------------------------
     * 玩法 - 中奖列表页
     * -----------------------------------------
     * Create by Mercury
     * -----------------------------------------
     * 2017-05-03
     * -----------------------------------------
     **/
    $(document).on("pageInit", "#page-drawluck-lists", function (e, id, page) {
		// 加载flag
        var loading = false;
        function addItems(p) {
            // 生成新条目的HTML
			var html = '';
			$.get('/drawluck/ajaxLists', {p:p}, function (ret) {
				if (ret.code == 1) {
                    html = template('tpl_luckdraw_lists', {data:ret.data});
                    $('#'+id+' .infinite-scroll-bottom .main').append(html);
				} else {
                    //$('#'+id+' .infinite-scroll-bottom .main').append(ret.msg);
				}
            }, 'json');
            // 添加新条目
        }
        // 注册'infinite'事件处理函数
        $(document).on('infinite', '.infinite-scroll-bottom',function() {
			var p = parseInt($('#' + id + ' input[name="luckdrawNextPage"]').val());
            // 最多可加载的条目
            var maxPages = parseInt($('#' + id).data('count_page'));

            // 如果正在加载，则退出
            if (loading) return;

            // 设置flag
            loading = true;

            $('#'+id+' .infinite-scroll-preloader').show();
            // 模拟1s的加载过程
            setTimeout(function() {
                // 重置加载flag
                loading = false;
                if (p > maxPages) {
                    // 加载完毕，则注销无限加载事件，以防不必要的加载
                    //$.detachInfiniteScroll($('.infinite-scroll'));
                    // 删除加载提示符
                    //$('.infinite-scroll-preloader').remove();
                    $('#'+id+' .infinite-scroll-preloader').hide();
                    return;
                }

                // 进入下一页
                addItems(p);
                //容器发生改变,如果是js滚动，需要刷新滚动
                $('#' + id + ' input[name="luckdrawNextPage"]').val(p+1)
                $.refreshScroller();
            }, 1000);
        });
    });
	
	/**
     * -----------------------------------------
     * 设置-消息接收设置 
     * -----------------------------------------
     * Create by liangfeng
     * -----------------------------------------
     * 2017-05-03
     * -----------------------------------------
     **/
    $(document).on("pageInit", "#page-setting-msg_setting", function (e, id, page) {
		$(".save-btn").click(function(){
			var data = getFormJson('#msg-setting');
			
			$.post('/Setting/ajax_save_msg_setting',data, function (ret) {
                
                $.toast(ret.msg);
				
            }, 'json');
			
		});
    });
	
	/**
	* -----------------------------------------
	* 活动页 - 端午节
	* -----------------------------------------
	* Create by liangfeng
	* -----------------------------------------
	* 2017-05-20
	* -----------------------------------------
	**/	
	$(document).on("pageInit", "#page-duanwu-index", function(e, id, page) {
		$(".top").click(function(){
			$('.content').scrollTop(0);
		});
		$(document).on('refresh', '.pull-to-refresh-content',function(e) {
			location.reload(true);
		});
	});	

	/**
	* -----------------------------------------
	* 活动页 - 父亲节
	* -----------------------------------------
	* Create by lizuheng
	* -----------------------------------------
	* 2017-06-10
	* -----------------------------------------
	**/	
	$(document).on("pageInit", "#page-fuqinjie-index", function(e, id, page) {
		$(".top").click(function(){
			$('.content').scrollTop(0);
		});
		$(document).on('refresh', '.pull-to-refresh-content',function(e) {
			location.reload(true);
		});
	});		
	/**
	* -----------------------------------------
	* 活动页 - 儿童节
	* -----------------------------------------
	* Create by liangfeng
	* -----------------------------------------
	* 2017-05-20
	* -----------------------------------------
	**/	
	$(document).on("pageInit", "#page-ertong-index", function(e, id, page) {
		$(document).on('refresh', '.pull-to-refresh-content',function(e) {
			location.reload(true);
		});
	});	
	$.init();
});