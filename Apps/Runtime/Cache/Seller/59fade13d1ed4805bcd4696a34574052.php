<?php if (!defined('THINK_PATH')) exit(); if(!empty($name["province"])): ?><div class="col-sm-3" style="padding-left:0">
		<div class="form-group" style="margin:0;">
			<div class="col-xs-12" style="padding-right:0;padding-left:0;">
			<select data-child="2" data-level="1" id="<?php echo ($name['province']); ?>" name="<?php echo ($name['province']); ?>"
				class="select2_category form-control form-filter btn-sm chinaCity" data-placeholder="" required>
				<option value="">--请选择--</option>
				<?php if(is_array($data)): $i = 0; $__LIST__ = $data;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?><option <?php if(($val["province"]) == $vo["id"]): ?>selected<?php endif; ?> value="<?php echo ($vo["id"]); ?>"><?php echo ($vo["a_name"]); ?></option><?php endforeach; endif; else: echo "" ;endif; ?>
			</select>
			</div>
			<div class="clear"></div>
		</div>
	</div><?php endif; ?>
<?php if(!empty($name["city"])): ?><div class="col-sm-3" style="padding-left:0">
		<div class="form-group" style="margin:0;">
		<div class="col-xs-12" style="padding-right:0;padding-left:0;">
			<select data-child="3" data-level="2" id="<?php echo ($name['city']); ?>" name="<?php echo ($name['city']); ?>"
				class="select2_category form-control form-filter btn-sm chinaCity"
				data-placeholder="请选择省市区" required>
				<option value="">--请选择--</option>
				<?php if(!empty($city)): if(is_array($city)): $i = 0; $__LIST__ = $city;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?><option <?php if(($val["city"]) == $vo["id"]): ?>selected<?php endif; ?> value="<?php echo ($vo["id"]); ?>"><?php echo ($vo["a_name"]); ?></option><?php endforeach; endif; else: echo "" ;endif; endif; ?>
			</select>
			</div>
			<div class="clear"></div>
		</div>
	</div><?php endif; ?>
<?php if(!empty($name["district"])): ?><div class="col-sm-3" style="padding-left:0">
	<div class="form-group" style="margin:0;">
	<div class="col-xs-12" style="padding-right:0;padding-left:0;">
		<select data-child="4" data-level="3" id="<?php echo ($name['district']); ?>" name="<?php echo ($name['district']); ?>"
			class="select2_category form-control form-filter btn-sm chinaCity"
			data-placeholder="请选择省市区" required>
			<option value="">--请选择--</option>
			<?php if(!empty($district)): if(is_array($district)): $i = 0; $__LIST__ = $district;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?><option <?php if(($val["district"]) == $vo["id"]): ?>selected<?php endif; ?> value="<?php echo ($vo["id"]); ?>"><?php echo ($vo["a_name"]); ?></option><?php endforeach; endif; else: echo "" ;endif; endif; ?>
		</select>
		</div>
		<div class="clear"></div>
		</div>
	</div><?php endif; ?>
<?php if(!empty($name["town"])): ?><div class="col-sm-3" style="padding-left:0">
	<div class="form-group" style="margin:0;">
	<div class="col-xs-12" style="padding-right:0;padding-left:0;">
		<select data-level="4" id="<?php echo ($name['town']); ?>" name="<?php echo ($name['town']); ?>"
			class="select2_category form-control form-filter btn-sm chinaCity"
			data-placeholder="请选择省市区">
			<option value="">--请选择--</option>
			<?php if(!empty($town)): if(is_array($town)): $i = 0; $__LIST__ = $town;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?><option <?php if(($val["town"]) == $vo["id"]): ?>selected<?php endif; ?> value="<?php echo ($vo["id"]); ?>"><?php echo ($vo["a_name"]); ?></option><?php endforeach; endif; else: echo "" ;endif; endif; ?>
		</select>
		</div>
		<div class="clear"></div>
		</div>
	</div><?php endif; ?>
<div style="clear:both"></div>
<script>
	$(document).ready(function(){
		var res = '';
		$(".chinaCity").change(function() {
			var data	=	$(this).data();
			var val		=	$(this).val();
			var html = '<option value="">--请选择--</option>';
			
			
			//清除当前选择以下的下拉菜单
			for(var l = data.child; l < 5; l ++) {
				$('select[data-level="'+l+'"]').html(html);
			}
			//如果已在最底级或者当前选择值为0时，则不再执行下面的代码
			if(data.level == 4 || val == 0) {
				return;
			}
			//请求服务器返回下级数据
			ajax_post({
				url:'<?php echo U("/run");?>',
				data:{sid:val},
				headers : {Action : '<?php echo enCryptRestUri("/City");?>'},
			},function(ret) {
				if(ret.code == 1 && ret.data != '') {
					res	 = ret.data;
					for(i in res) {
						html += '<option value="'+res[i]['id']+'">'+res[i]['a_name']+'</option>';
					}
					$("select[data-level='"+data.child+"']").removeClass('hide').html(html);
					$(this).unbind();
				} else {
					$("select[data-level='"+data.child+"']").addClass('hide');
					$("select[data-level='"+data.child+"']").empty();
					$("select[data-level='"+data.child+"']").parent().find(".help-block").empty();
				}
			})
		})
	})
</script>