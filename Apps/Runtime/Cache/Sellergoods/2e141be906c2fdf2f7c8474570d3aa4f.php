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
    var ACTION = '<?php echo enCryptRestUri("/Goods/edit");?>';
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


<div class="bg-gray content-wrap" style="background: #F0F0F0; height: 100%; width: 100%; overflow: scroll;">

    <div class="content pd15">
        <div class="bg-warning pd10 letter-spacing1">
            <i class="fa fa-exclamation-circle fs16 fl text_yellow"></i>
            <p class="ml20 mb0">小提示，您当前正在编辑<strong class="text_red"> 商品 </strong>，请输入以下必填项，带 <span class="text_red">*</span> 号的为必填项。</p>
        </div>
        <div class="bg-warning pd10 letter-spacing1 mt20">
            <i class="fa fa-exclamation-circle fs16 fl text_yellow"></i>
            <p class="ml20 mb0">当前商品所属类目：<?php echo ($category['title']); ?>，<a class="text_red strong" href="<?php echo U('/goods/editCate', ['id' => $_GET['id']]);?>">修改类目</a> </p>
        </div>
        <div class="row mt30">
            <div class="col-md-8">
                <?php echo ($form["html"]); ?>
            </div>
            <div class="col-md-4">
                <div id="error-box" style="margin-top: 38px; color: #a94442">

                </div>
            </div>
        </div>
        <p class="hide"><input type="file" id="attrListUploadImagesInput"></p>
    </div>
    <div class="loading hide" style = 'position: fixed;top: 45%;left: 25%;z-index: 1000; background: #F0F0F0;width: 500px; padding: 50px;'>
        <div class="loading-body text-center"><img src="/Public/images/loading.gif">图片正在上传...</div>
    </div>
</div><!--required="true" min="0" number="true" maxlength="8"-->
<?php echo ($form["js"]); ?>
<script>
<?php if(!empty($_GET['cateId'])): ?>//修改类目
    $(document).ready(function () {
            //修改分类的时候使用
            $(window).bind('beforeunload', function () {
                 return '您编辑的信息还未保存，是否继续操作';
            });
        });
    <?php else: ?>
    var formOldData = $("form").serialize();
    $(document).ready(function () {
        //修改分类的时候使用
        $(window).bind('beforeunload', function () {
            var formNewData = $("form").serialize();
            formNewData = formNewData.replace(/\&content=.*/, '');  //去掉content及其值 【因为ueditor加载太慢在旧的数据里面并没有加载成功】
            if (formNewData != formOldData) {
                return '您编辑的信息还未保存，是否继续操作';
            }
        });
    });<?php endif; ?>
</script>
<script>
    var attrList;
    <?php if(!empty($attrs)): ?>attrList = <?php echo ($attrs); ?>;<?php endif; ?>
    var formOldData = $("form").serialize();
    $(document).ready(function () {
        //修改分类的时候使用
        $(window).bind('beforeunload',function(){
                var formNewData = $("form").serialize();
                formNewData = formNewData.replace(/\&content=.*/, '');  //去掉content及其值 【因为ueditor加载太慢在旧的数据里面并没有加载成功】
                if (formNewData != formOldData) {
                    return '您编辑的信息还未保存，是否继续操作';
                }
            }
        );

        $(".btn-form-submit").click(function () {
            var tabId = $("form").find('.has-error').eq(0).closest('.tab-pane').attr('id');
            if (tabId != undefined) {
                $(".nav-tabs a[href='#"+tabId+"']").click();
            }
        });

        $("#error-box").click(function () {
            var tabId = $("form").find('.has-error').eq(0).closest('.tab-pane').attr('id');
            if (tabId != undefined) {
                $(".nav-tabs a[href='#"+tabId+"']").click();
            }
        });

        $(".goods-attr input[type='checkbox']").on('ifChecked ifUnchecked', function(event) {
            createHtml();
        });

        $(".goods-attr input[id^='attr_value_']").change(function () {
            var textLength = $(this).val().length;
            if (textLength > 10) {
                talert({status:0,msg:'文本长度不能大于10位！'})
                return false;
            }
            if ($(this).prev().find('input[type="checkbox"]').is(':checked') == true) {
                createHtml();
            }
        })


        /**
         * 添加分组
         */
        var groupListiItemId = 10;
        $(".btn-group-plus").click(function () {
            var itemLength = $('.group-list-item').size();
            if (itemLength == 5) {
                talert({status:0,msg:'最多只能有5个分组'});
                return false;
            }
            var h = '<div class="row mt20 pt20 group-list-item" id="group-list-item-'+groupListiItemId+'" style="border-top:solid 1px #f0f0f0">'+
                '<div class="col-md-4"><div class="input-group"><span class="input-group-addon">分组名称</span><input type="text" class="form-control group-name" name="collocation[name][]" placeholder="分组名称"></div></div>'+
                '<div class="col-md-4"><div class="input-group"><span class="input-group-addon">分组排序</span><input type="text" class="form-control group-sort" name="collocation[sort][]" value="'+itemLength+'" placeholder="分组排序"></div></div>'+
                '<div class="col-md-4"><input class="group-goods" type="hidden" name="collocation[goods][]" value=""><a href="javascript:;" onclick="chooseGoods($(this), '+groupListiItemId+');" class="btn btn-rad btn-trans btn-primary m0">选商品</a><a href="javascript:;" onclick="removeGroupItem($(this));" title="移除分组" class="btn btn-rad btn-trans btn-primary ml20">移除分组</a></div>'+
                '<div class="col-md-12 group-goods-list"><ul id="images-list-'+groupListiItemId+'" class="images-select-box">'+
                '</ul></div>'+
                '</div>';
            $(".group-goods-box").append(h);
            groupListiItemId++;
        });

    });

    /**
     * 创建html代码
     */
    function createHtml() {
        var h = ''; //html
        var c = 1;  //数量统计 2*2*2
        var t = 0;  //datas下标
        var s = 0;
        var datas = new Array();
        $(".goods-attr-item").each(function (k, v) {
            var i = 0;
            datas[t] = new Array();
            $(this).find('input[type="checkbox"]').each(function (key, val) {
                if ($(this).is(":checked") == true) {
                    var attr_name = $(this).closest('.input-group').find('input[type="text"]').val();
                    var attr_id = $(this).val();
                    var attr_images = $(this).closest('.input-group').next().val();
                    var attr_value = attr_id + ':' + attr_name;
                    datas[t][i] = {'name': attr_name, 'attr_id': attr_id, 'value': attr_value};
                    i++;
                }
            });
            if (i > 0) {
                t++;
                c *= i;
            } else {
                datas.splice(t, 1);
            }
        });
        writeHtml(datas);
    }


    /**
     * 写入HTML
     */
    function writeHtml(datas) {
        var count = datas.length;

        var key = 1;

        var c = 0;

        //attrList

        while (key < count) {
            var tmp = {};
            $.each(datas[key], function (k, v) {
                $.each(datas[(key - 1)], function (keys, val) {
                    tmp[c] = {name: v.name + ',' + val.name, attr_id: val.attr_id + ',' + v.attr_id, value: v.value + ',' + val.value};
                    c++;
                })
            });
            datas[key] = tmp;
            key++;
        }


        var data = datas.pop();
        var i = 1;
        var h = '';
        $.each(data, function (k, v) {
            v.id            = '';
            v.price         = '';
            v.price_market  = '';
            v.price_purchase= '';
            v.num           = '';
            v.code          = '';
            v.barcode       = '';
            v.weight        = '';
            if (attrList != '') {
                $.each(attrList, function (key, val) {
                    if (v.attr_id == val.attr_id) {
                        v.id            = val.id
                        v.price         = val.price;
                        v.price_market  = val.price_market;
                        v.price_purchase= val.price_purchase;
                        v.num           = val.num;
                        v.code          = val.code;
                        v.barcode       = val.barcode;
                        v.weight        = val.weight;
                        return true;
                    }
                });
            }


            h += '<tr>' +
                '<td class="text-center" nowrap="">'+i+'</td>' +
                '<td class="attr-item-name" style="width:100px;" nowrap="">' +
                v.name + '<input type="hidden" id="attr_sku_attr_id_'+i+'" name="attrs[attr_id][]" value="'+v.attr_id+'">' +
                '<input type="hidden" id="attr_sku_attr_'+i+'" name="attrs[attr][]" value="'+v.value+'">' +
                '<input type="hidden" id="attr_sku_attr_name_'+i+'" name="attrs[attr_name][]" value="'+v.name+'">' +
                '<input type="hidden" id="attr_sku_id_'+i+'" name="attrs[id][]" value="'+v.id+'">' +
                '</td>' +
                '<td><div class="form-group" style="margin:0;"><div class="input-group" style="margin:0;"><input onchange="goodsAttrValue($(this));" type="text" id="attr_sku_price_'+i+'" required="true" min="0.1" number="true" name="attrs[price][]" value="'+v.price+'" class="form-control"><a href="javascript:;" onclick="copySome($(this));" class="input-group-addon" style="padding-left:5px;padding-right:5px;"><i class="fa fa-arrow-down"></i></a></div></div></td>' +
                '<td><div class="form-group" style="margin:0;"><div class="input-group" style="margin:0;"><input onchange="goodsAttrValue($(this));" type="text" id="attr_sku_price_market_'+i+'" min="0" number="true" name="attrs[price_market][]" value="'+v.price_market+'" class="form-control"><a href="javascript:;" onclick="copySome($(this));" class="input-group-addon" style="padding-left:5px;padding-right:5px;"><i class="fa fa-arrow-down"></i></a></div></div></td>' +
                '<td><div class="form-group" style="margin:0;"><div class="input-group" style="margin:0;"><input onchange="goodsAttrValue($(this));" type="text" id="attr_sku_price_purchase_'+i+'" min="0" number="true" name="attrs[price_purchase][]" value="'+v.price_purchase+'" class="form-control"><a href="javascript:;" onclick="copySome($(this));" class="input-group-addon" style="padding-left:5px;padding-right:5px;"><i class="fa fa-arrow-down"></i></a></div></div></td>' +
                '<td><div class="form-group" style="margin:0;"><div class="input-group" style="margin:0;"><input onchange="goodsAttrValue($(this));" type="text" id="attr_sku_num_'+i+'" name="attrs[num][]" min="0" number="true" value="'+v.num+'" class="form-control"><a href="javascript:;" onclick="copySome($(this));" class="input-group-addon" style="padding-left:5px;padding-right:5px;"><i class="fa fa-arrow-down"></i></a></div></div></td>' +
                '<td><div class="form-group" style="margin:0;"><div class="input-group" style="margin:0;"><input onchange="goodsAttrValue($(this));" type="text" id="attr_sku_code_'+i+'" name="attrs[code][]" value="'+v.code+'" class="form-control"><a href="javascript:;" onclick="copySome($(this));" class="input-group-addon" style="padding-left:5px;padding-right:5px;"><i class="fa fa-arrow-down"></i></a></div></div></td>' +
                '<td><div class="form-group" style="margin:0;"><div class="input-group" style="margin:0;"><input onchange="goodsAttrValue($(this));" type="text" id="attr_sku_barcode_'+i+'" name="attrs[barcode][]" value="'+v.barcode+'" class="form-control"><a href="javascript:;" onclick="copySome($(this));" class="input-group-addon" style="padding-left:5px;padding-right:5px;"><i class="fa fa-arrow-down"></i></a></div></div></td>' +
                '<td><div class="form-group" style="margin:0;"><div class="input-group" style="margin:0;"><input onchange="goodsAttrValue($(this));" type="text" id="attr_sku_weight_'+i+'" required="true" min="0" number="true" name="attrs[weight][]" value="'+v.weight+'" class="form-control"><a href="javascript:;" onclick="copySome($(this));" class="input-group-addon" style="padding-left:5px;padding-right:5px;"><i class="fa fa-arrow-down"></i></a></div></div></td>' +
                '</tr>';
            i++;
        });
        $("#goods-attr-list-box").html(h);
    }

    function goodsAttrValue(that) {
        //attr = {price:that.val()}
    }

    /**
     * 移除group item
     *
     * @param item
     */
    function removeGroupItem(item) {
        vmodal({
            title:'移除提示',
            msg:'您真的要移除分组吗?',
            class:'text-center',
            confirm:1,
            footer:false,
            width:'600px',
        },function() {
            $(".modal-ok").click(function() {
                item.closest(".group-list-item").remove();
                $("#ajax-modal").modal('hide');
            })
        })
    }

    /**
     * 选择商品
     *
     * @param item
     */
    function chooseGoods(item, id) {
        vmodal({
            title:'选择商品',
            url:'<?php echo U("/goods/choose");?>?item=' + id,
            footer:false,
            width:'1000px',
        });
    }

    /**
     * 删除搭配商品
     *
     * @param obj
     */
    function reMoveGroupGoods(obj) {
        var d = obj.closest('li').data();
        var goods = obj.closest('.group-list-item').find('input[type="hidden"]').val();
        var goodsArr = goods.split(',');
        var index = $.inArray(d.id.toString(), goodsArr);
        if (index >= 0) goodsArr.splice(index, 1);
        obj.closest('.group-list-item').find('.group-goods').val(goodsArr.toString());
        obj.closest('li').remove();
    }

    /**
     * 图片上传
     *
     * @param obj
     */
    function attrUploadImages(obj) {
        var handle = $('#attrListUploadImagesInput');
        handle.after(handle.clone().val(""));
        handle.remove();
        var images = obj.parent().next().find('.attrAlbum').val();
        console.log(images);
        var imagesArr = images.split(',');
        if (imagesArr.length > 3) {
            talert({status:0,msg:'单个属性最多可上传4张图片'});
            return false;
        }
        //handle.
        handle.click().change(function () {
            $(".loading").removeClass('hide');
            var data = new FormData();
            //为FormData对象添加数据
            $.each(handle[0].files, function(i, file) {
                data.append('file', file);
            });

            $.ajax({
                url:'<?php echo U("/run/upload");?>',
                type:'POST',
                data:data,
                cache: false,
                contentType: false,    //不可缺
                processData: false,    //不可缺
                success:function(res){
                    $(".loading").addClass('hide');
                    var data = res.data;
                    var html = '<li data-path="'+data.url+'" class="text-center" style="width: 70px;height: 70px; padding: 0; margin-right: 5px">'+
                                    '<div class="li-img-box" style="width: 70px; height: 70px;">'+
                                        '<a href="javascript:;" onclick="bigImage($(this));" data-url="'+data.url+'" data-title="大图" class="image-zoom" title="大图">'+
                                            '<img style="width: 55px; height: 55px;" src="'+data.url+'">'+
                                        '</a>'+
                                    '</div>'+
                                    '<div data-name="images" class="delete-images" onclick="reMoveAttrImages($(this));">'+
                                        '<div class="selected-icon"><i class="fa fa-times"></i></div>'+
                                    '</div>'+
                                '</li>';
                    obj.closest('.col-md-4').find('.images-select-box').append(html);
                    if (images != '') {
                        obj.parent().next().find('.attrAlbum').val(images+','+data.url);
                    } else {
                        obj.parent().next().find('.attrAlbum').val(data.url);
                    }
                }
            });
            handle.unbind();
        });
    }


    /**
     * 移除属性图片
     *
     * @param that
     */
    function reMoveAttrImages(that) {
        var path = that.parent().data('path');
        var images = that.closest('ul').find('.attrAlbum').val();
        var imagesArr = images.split(',');
        var index = $.inArray(path, imagesArr);
        if (index >= 0) imagesArr.splice(index, 1);
        that.closest('ul').find('.attrAlbum').val(imagesArr.toString());
        that.parent().remove();
    }


    /**
     * 列拷贝
     *
     * @param sort
     */
    function copySome(that) {
        var row = that.closest('tr').prevAll().length+1;  //当前行
        var col = that.closest('td').prevAll().length+1;  //当前列
        var rowCnt = that.closest('table').find('tr').size();
        var vals= that.prev().val();
        for (var i = row; i < rowCnt; i++) {
            that.closest('table').find('tr').eq(i).find('td').eq(col-1).find('input').val(vals);
        }
    }

</script>
</body>
</html>