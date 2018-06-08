<?php if (!defined('THINK_PATH')) exit();?><div class="col-xs-2 mt5 pr0">
	<a target="_blank" class="db fr" href="<?php echo DM('cart');?>">
		<div class="bg_red fl" style="padding:6px 12px"><img src="/Public/new_design/images/cart.png"></div>
		<div class="shop_cart fl text_77 fs14 re">
			我的购物车<i class="fa fa-angle-right ml10 fs16"></i> 
			<span class="ab text_white bg_red ajax_cart_num"><?php echo ($data["style_num"]); ?></span>
		</div>
	</a>
</div>