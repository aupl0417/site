<?php
namespace Common\Common;
class Apiurl{
    static public function url($moudle, $action) {
        //用户模块
        $data['User']   =    [
            '/Index/index'  => [
                'action'    =>  '/Shop/shop_info',
                'nosign'    =>  'nosign',
                'run'       =>  'run',
            ],
            '/Login/index'   =>  [   //登录
                'action'    =>  '/Erp/check_login',
                'nosign'    =>  'vcode,remember',
            ],
            '/Register/index' =>    [   //注册
                'action'    =>  '/Erp/register,/Erp/register_company',
            ],
            '/Register/person'=>[
                'action'    =>  '/Erp/register',
                'nosign'    =>  'vcode,ref,country,protocol,password2',
            ],
            '/Register/company'=>[
                'action'    =>  '/Erp/register_company',
                'nosign'    =>  'vcode,ref,protocol,password2',
            ],
            '/Forget/index' =>  [   //找回密码
                'action'    =>  '/Erp/forgot_password_step1',
                'nosign'    =>  'vcode',
                'cache'     =>  'forgetPass',
            ],
            '/Forget/step2' =>  [   //找回密码
                'action'    =>  '/Erp/forgot_password_step2',
                'nosign'    =>  'password2',
            ],
            '/Send/sms'     =>  [   //发送短信
                'action'    =>  '/Erp/sms_code',
            ],
            '/Get/ref'      =>  [   //获取推荐人信息
                'action'    =>  '/Erp/get_user_info',
            ],
			'/Get/check_username'      =>  [   //获取推荐人信息
                'action'    =>  '/Erp/register_username_check',
            ],
            '/Tj/visit'     =>  [ # PC统计访问
                'action'    =>  '/Tj/visit',
            ],
            '/Tj/ad_show'     =>  [ # PC统计广告展示
                'action'    =>  '/Tj/ad_show',
            ],
        ];
        
        //商家模块
        $data['Shop']   =   [
            '/Fav/index'    =>  [   //添加关注
                'action'    =>  '/ShopFav/add',
                'nosign'    =>  '',
            ],
            'favDel'        =>  [   //删除关注
                'action'    =>  '/ShopFav/delete',
                'nosign'    =>  '',
            ],
            '/Tj/visit'     =>  [ # PC统计访问
                'action'    =>  '/Tj/visit',
            ],
            '/Tj/ad_show'     =>  [ # PC统计广告展示
                'action'    =>  '/Tj/ad_show',
            ],
        ];
        
        //我的模块
        $data['My']     =   [
            '/Addr/add'  =>  [  //添加地址，修改地址
                'action'    =>  '/Address/add',
                'nosign'    =>  'postcode,is_default,tel,town,id',
            ],
            '/Addr/create'  =>  [  //添加地址，修改地址
                'action'    =>  '/Address/add',
                'nosign'    =>  'postcode,is_default,tel,town,id',
            ],
            '/Addr/edit' => [
                'action'    =>  '/Address/edit',    //修改地址
                'nosign'    =>  'postcode,is_default,tel,town',
            ],
            '/Addr/del'  =>  [  //删除地址
                'action'    =>  '/Address/delete',
                'nosign'    =>  '',
            ],
            '/Daigou/index'=> [ //代购
                'action'    =>  '/Daigou/add',
                'nosign'    =>  'attr_name,id,remark',
            ],
			'/Daigou/del'  =>  [  //删除申请
                'action'    =>  '/Daigou/delete',
                'nosign'    =>  '',
            ],
            '/Coupon/lists' =>  [   //领取优惠券
                'action'    =>  '/Coupon/get_coupon',
                'nosign'    =>  '',
            ],
            '/Coupon/receive'=> [   
                'action'    =>  '/Coupon/get_coupon',   
            ],
            '/Coupon/index' =>  [
                'action'    =>  '/Coupon/delete',
            ],
            
            '/History/index'=>  [   //删除浏览记录
                'action'    =>  '/Visit/goods_delete',
            ],
            
            '/Fav/index'    =>  [   //删除我的收藏
                'action'    =>  '/Fav/goods_delete',  
            ],

            '/Fav/shop'     =>  [   //收藏店铺
                'action'    =>  '/ShopFav/add',
            ],

            '/Favshop/index'=>  [   //删除收藏的店铺
                'action'    =>  '/ShopFav/delete',
            ],

            '/Fav/goods'    =>  [   //收藏商品
                'action'    =>  '/Fav/goods_add',
            ],
            
            
            '/Change/pass'  =>  [   //修改密码
                'action'    =>  '/Erp/change_password',   
                'nosign'    =>  'repassword,vcode',
            ],
            '/Change/payPass'=> [   //修改安全密码
                'action'    =>  '/Erp/change_pay_password',
            ],
            '/Change/setPayPass'=>  [   //设置安全密码
                'action'    =>  '/Erp/set_pay_password',
            ],
            
            '/Send/sms'     =>  [   //发送短信
                'action'    =>  '/Erp/sms_code',
            ],
            
            '/City'     =>  [   //获取地区
                'action'    =>  '/Tools/city',
                'nosign'    =>  'sid',
            ],
            
            '/Opreat/pay'   =>  [   //操作商家订单支付
                'action'    =>  '/Erp/orders_pay',
            ],
            '/Opreat/close' =>  [   //操作关闭订单
                'action'    =>  '/Orders/orders_shop_close',
            ],
            '/Opreat/receipt'=>  [  //操作收货
                'action'    =>  '/Erp/orders_confirm',
                //'action'    =>  '/SellerOrders/express_send',
            ],
            '/Opreat/delay' =>  [   //操作收货延期
                'action'    =>  '',
            ],
            '/Opreat/service'=> [   //操作售后
                'action'    =>  '',
            ],
            '/Serllser/searchExpressCompany' => [   //搜索快递公司
                'action'    =>  '/Express/search',
            ],
            
            '/Change/forget'=>  [   //找回安全密码
                'action'    =>  '/Erp/forgot_pay_password',
                'nosign'    =>  'vcode,repassword',
            ],
            '/Change/payPass'=> [   //修改安全密码
                'action'    =>  '/Erp/change_pay_password',
                'nosign'    =>  'vcode,repassword',
            ],
            '/Change/setPayPass'=>  [   //设置安全密码
                'action'    =>  '/Erp/set_pay_password',
                'nosign'    =>  'vcode,repassword',
            ],
            '/Change/pass'  =>  [   //修改密码
                'action'    =>  '/Erp/change_password',
                'nosign'    =>  'vcode,repassword',
            ],
            
            //退货
            '/Refund/create'=>  [   //创建退款
                'action'    =>  '/Refund/add',
                'nosign'    =>  'refund_express,num,price'
            ],
            '/Refund2/create'=>  [   //创建退款
                'action'    =>  '/Refund2/add',
				'nosign'    =>  'file,images,refund_express,num,price',
            ],
            '/Refund/create2'=>  [   //创建已发货退款
                'action'    =>  '/Refund2/add',
                'nosign'    =>  'file,images,refund_express,num,price',
            ],
            '/Refund3/create'=>  [   //创建售后
                'action'    =>  '/Refund3/add',
                'nosign'    =>  'file,images',
            ],
			'/Refund/express_create'=>  [   //未发货创建运费退款
                'action'    =>  '/Refund/express_add',
                'nosign'    =>  'file,images',
            ],
			'/Refund2/express_create'=>  [   //已发货创建运费退款
                'action'    =>  '/Refund2/express_add',
                'nosign'    =>  'file,images',
            ],
            '/Refund2/edit'  =>  [   //退款编辑
                'action'    =>  '/Refund2/edit',
				'nosign'	=>	'file,images,refund_express,num,price'
            ],
            '/Refund/edit'  =>  [   //退款编辑
                'action'    =>  '/Refund/edit',
                'nosign'	=>	'file,images,refund_express,num,price'
            ],
            '/Refund2/edit'  =>  [   //退款编辑
                'action'    =>  '/Refund2/edit',
                'nosign'	=>	'file,images,refund_express,num,price'
            ],
			'/Refund2/appeal'  =>  [   //已发货申诉
                'action'    =>  '/Appeal/index',
				'nosign'	=>	'file,images'
            ],
            '/Refund/appeal'  =>  [   //未发货申诉
                'action'    =>  '/Appeal/index',
                'nosign'	=>	'file,images'
            ],
			'/Refund/express'=>  [   //创建退款
                'action'    =>  '/Refund2/send_express',
                'nosign'    =>  'express_company_id_search',
            ],
            '/Refund/confirm'=> [   //确认寄回商品
                'action'    =>  '/Refund/send_back',
            ],
            '/Refund/cancel'=>  [   //取消退款
                'action'    =>  '/Refund/cancel',
            ],
            '/Refund1/cancel'=>  [   //取消退款
                'action'    =>  '/Refund/cancel',
            ],
            '/Refund2/cancel'=>  [   //取消退款
                'action'    =>  '/Refund2/cancel',
            ],
            '/Refund/message'=> [   //添加留言
                'action'    =>  '/Refund/logs',
            ],
            '/Appeal/index' =>  [   //申诉
                'action'    =>  '/Appeal/index',
                'nosign'    =>  'file,images',
            ],
            '/Service/opreat/express'   =>  [   //售后邮寄商品
                'action'    =>  '/Refund3/send_express',
                'nosign'    =>  'express_company_id_search',
            ],
            '/Service/opreat/cancel'    =>  [   //取消售后
                'action'    =>  '/Refund3/cancel',
                'nosign'    =>  'remark',
            ],
            '/Service/opreat/accept'    =>  [   //确认售后
                'action'    =>  '/Refund3/accept',
                'nosign'    =>  '',
            ],
            '/Service/opreat/appeal'    =>  [   //申诉
                'action'    =>  '/Appeal/index',
                'nosign'    =>  'images,file',
            ],
            '/Service/edit' =>  [   //编辑售后
                'action'    =>  '/Refund3/edit',
                'nosign'    =>  'file,images',
            ],
            '/Service/add'  =>  [   //添加售后
                'action'    =>  '/Refund3/add',
                'nosign'    =>  'file,images',
            ],


            '/Comments/reply'=> [   //商家回复评价
                'action'    =>  '/Rate/reply',
                'nosign'    =>  'file,images',
            ],
            '/Comments/edit'=>  [   //修改评价
                'action'    =>  '/Rate/rate_goods_edit',
                'nosign'    =>  'images,file',
            ],
            
            '/Orders/comments'=>    [   //商品评价
                'action'    =>  '/Orders/goods_rate',
                'nosign'    =>  'file,images',
            ],
            
            '/Orders/rateShop'=>    [   //商家评价
                'action'    =>  '/Orders/shop_rate',
            ],
            '/Orders/receipt'=>  [  //操作收货
                'action'    =>  '/Erp/orders_confirm',
                //'action'    =>  '/SellerOrders/express_send',
            ],
            '/Orders/close'=>  [  //操作收货
                'action'    =>  '/Orders/orders_shop_close',
                //'action'    =>  '/SellerOrders/express_send',
            ],
            '/Workorder/create' => [ # 创建工单
                'action'    => '/Workorder/create',
                'nosign'    => 'email,file',
            ],
            '/Workorder/handle' => [ # 处理工单
                'action'    => '/Workorder/user_handle',
            ],
            '/Workorder/delete' => [ # 处理工单
                'action'    => '/Workorder/delete',
            ],
            '/Index/favgoods'  =>  [    //收藏商品
                'action'    =>  '/Fav/goods_add',
            ],
            '/Index/like' =>  [   // 猜你喜欢
                'action'    =>  '/Goods/love_goods',
                'nosign'    =>  'imgsize,limit,score_ratio',
            ],
            '/Index/recom' =>  [   // 热销商品 
                'action'    =>  '/Goods/hot_goods',
                'nosign'    =>  'imgsize,limit',
            ],
            '/Tj/visit'     =>  [ # PC统计访问
                'action'    =>  '/Tj/visit',
            ],
            '/Tj/ad_show'     =>  [ # PC统计广告展示
                'action'    =>  '/Tj/ad_show',
            ],
            '/Advisory/del' =>  [   //删除咨询
                'action'    =>  '/Advisory/del',    
            ],
            '/Complaints/del' =>  [   //删除举报
                'action'    =>  '/GoodsComplaints/del',
            ],
            '/Notice/del'   =>  [   //删除通知
                'action'    =>  '/NoticeMsg/del',
            ],
            '/Notice/read'  =>  [   //把通知设为已读状态
                'action'    =>  '/NoticeMsg/saveRead',
            ],
            '/Notice/clear' =>  [   //清空所有消息
                'action'    =>  '/NoticeMsg/clear',
            ],

            '/Service/appeal'=> [   //售后提起申诉
                'action'    =>  '/Appeal/index',
                'nosign'    =>  'file,images',
            ],

            '/Service/cancel'=> [   //取消售后
                'action'    =>  '/Refund3/cancel',
                'nosign'    =>  'remark',
            ],

            '/Service/express'=>[   //售后邮寄商品
                'action'    =>  '/Refund3/send_express',
                'nosign'    =>  'express_company_id_search',
            ],

            '/Service/accept' => [   //售后确认收货
                'action'    =>  '/Refund3/accept',
            ],

            '/Service/create'=> [   //申请售后
                'action'    =>  '/Refund3/add',
                'nosign'    =>  'file,images',
            ],

//            '/Refund/express'=> [
//
//            ],

//            '/Refund/cancel'=>  [
//
//            ],
//
//            '/Refund/appeal'=>  [
//
//            ],

            //'/Refund/'

            '/Fav/addcart'=>  [
                'action'    =>  '/Cart/add',
            ],

        ];
        
        
        //商品详情
        $data['Item']   =   [
            '/Index/param'  =>  [   //商品参数
                'action'    =>  '/Goods/goods_param',
            ],
            '/Index/complaints' => [    //商品举报
                'action'    =>  '/Goods/complaints',
                'nosign'    =>  'images,file',
            ],
            '/Index/comment'  =>  [ //商品评价
                'action'    =>  '/Goods/goods_rate',
            ],
            '/Index/package'  =>  [ //商品包装
                'action'    =>  '/Goods/goods_package',
            ],
            '/Index/protection'  =>  [  //商品保障
                'action'    =>  '/Goods/goods_protection',
            ],
            '/Index/addcart'  =>  [ //添加到购物车
                'action'    =>  '/Cart/add',
            ],
            '/Index/addGroupCart'=>[    //组合添加到购物车
                'action'    =>  '/Cart/groupAdd',
            ],
            '/Index/buynow'  =>  [  //立即购买
                'action'    =>  '/Cart/add',
                'nosign'    =>  'atonce',
            ],
            '/Index/favshop'  =>  [ //收藏店铺
                'action'    =>  '/ShopFav/add',
            ],
            '/Index/favgoods'  =>  [    //收藏商品
                'action'    =>  '/Fav/goods_add',
            ],
            '/Index/like' =>  [   // 猜你喜欢
                'action'    =>  '/Goods/love_goods',
                'nosign'    =>  'imgsize,limit,score_ratio',
            ],
            '/Index/recom' =>  [   // 热销商品 
                'action'    =>  '/Goods/hot_goods',
                'nosign'    =>  'imgsize,limit',
            ],
            '/Index/city'     =>  [   //获取城市
                'action'    =>  '/Tools/city',
                'nosign'    =>  'sid',
            ],
            '/Receive/coupon'=> [   //领取优惠券
                'action'    =>  '/Coupon/get_coupon'
            ],
            '/Tj/visit'     =>  [ # PC统计访问
                'action'    =>  '/Tj/visit',
            ],
            '/Tj/ad_show'     =>  [ # PC统计广告展示
                'action'    =>  '/Tj/ad_show',
            ],
            '/Item/advisory'=>  [
                'action'    =>  '/GoodsAdvisory/add',    
            ],
            '/Item/advisoryCate'=>  [
                'action'    =>   '/GoodsAdvisory/categoryIntro',     
            ],
            '/ShopNews/info' => [
                'action'    => '/ShopNews/info',
            ],
        ];
        
        //购物车
        $data['Cart']   =   [
            '/Index/favgoods'   =>  [   //移入收藏夹
                'action'    =>  '/Fav/goods_add'
            ],
            '/Index/del'    =>  [   //从购物车删除
                'action'    =>  '/Cart/delete',
            ],
            '/Index/cartadd'=>  [   //增加数量
                'action'    =>  '/Cart/add',
            ],
            '/Index/index'  =>  [   //提交购物车
                'action'    =>  '/Cart/selected',
            ],
            '/Confirm/getEpressPrice' =>   [    //获取快递价格
                'action'    =>  '/CartVer2/express_price',
            ],
            '/Confirm/index'=>  [   //创建订单
                'action'    =>  '/CartVer2/create_orders',
            ],
            '/Confirm/spm'=>  [   //创建0元购、秒杀等订单订单
                'action'    =>  '/CartVer2/create_activity_orders',
            ],
            '/Pay/index'    =>  [   //订单支付
                'action'    =>  '/Erp/orders_group_pay',
                'nosign'    =>  '_password_password_pay',
            ],
            '/Index/like' =>  [   // 猜你喜欢
                'action'    =>  '/Goods/love_goods',
                'nosign'    =>  'imgsize,limit,score_ratio',
            ],
            '/Index/recom' =>  [   // 热销商品 
                'action'    =>  '/Goods/hot_goods',
                'nosign'    =>  'imgsize,limit',
            ],
            '/Tj/visit'     =>  [ # PC统计访问
                'action'    =>  '/Tj/visit',
            ],
            '/Tj/ad_show'     =>  [ # PC统计广告展示
                'action'    =>  '/Tj/ad_show',
            ],
            '/City'     =>  [   //获取地区
                'action'    =>  '/Tools/city',
                'nosign'    =>  'sid',
            ],
            '/Addradd'  =>  [  //添加地址，修改地址
                'action'    =>  '/Address/add',
                'nosign'    =>  'postcode,is_default,tel,town,id',
            ],
        ];
        
        //卖家
        $data['Seller'] =   [
			'/OrderApply/cancel'=> [   //取消申诉
                'action'    =>  '/SellerRate/cancel',
            ],
		    '/OrderApply/appeal'=> [   //商家刷单申诉
                'action'    =>  '/SellerRate/appeal',
                'nosign'    =>  'file,images',
            ],
            '/Comments/reply'=> [   //商家回复评价
                'action'    =>  '/SellerRate/reply',
                'nosign'    =>  'file,images',
            ],
            '/Addr/del'   =>  [ //删除发货地址
                'action'    =>  '/SendAddress/delete',
            ],
            '/Addr/add'   => [  //添加发货地址
                'action'    =>  '/SendAddress/add',
                'nosign'    =>  'id,tel,is_default,postcode,town',
            ],
            '/Addr/create'   => [  //添加发货地址
                'action'    =>  '/SendAddress/add',
                'nosign'    =>  'id,tel,is_default,postcode,town',
            ],
            '/Addr/edit'   => [  //修改发货地址
                'action'    =>  '/SendAddress/edit',
                'nosign'    =>  'tel,is_default,postcode,town',
            ],
            '/Setting/index'=>  [   //店铺设置
                'action'    =>  '/ShopSetting/shop_info_save',
                'nosign'    =>  'town,shop_logo,tel,email,wang,file'
            ],
            '/Setting/inventory_type_save' => [ //设置结算方式
                'action'    =>  '/ShopSetting/inventory_type_save',
                'nosign'    =>  ''
            ],
            '/Setting/inventory' => [   //设置结算方式
                'action'    =>  '/ShopSetting/inventory_type_save',
                'nosign'    =>  ''
            ],
            '/Setting/create' => [
                'action'    =>  '/ShopNews/create',
                'nosign'    =>  ''
            ],
            '/Domain/index' =>  [   //域名设置
                'action'    =>  '/ShopSetting/set_domain',
            ],
            
            '/City'     =>  [   //获取城市
                'action'    =>  '/Tools/city', 
                'nosign'    =>  'sid',
            ],
            
            
            
            '/Open/step3_add'   => [   //等级联系人信息
                'action'    =>  '/OpenShop/contact_info_add',
                'nosign'    =>  'op_linkname,op_mobile,op_tel,op_email,cs_linkname,cs_mobile,cs_tel,cs_email,fc_linkname,fc_mobile,fc_tel,fc_email,tc_linkname,tc_mobile,tc_tel,tc_email,town,rf_postcode,rf_email',
            ],
            '/Open/step3_edit'   => [   //等级联系人信息
                'action'    =>  '/OpenShop/contact_info_edit',
                'nosign'    =>  'op_linkname,op_mobile,op_tel,op_email,cs_linkname,cs_mobile,cs_tel,cs_email,fc_linkname,fc_mobile,fc_tel,fc_email,tc_linkname,tc_mobile,tc_tel,tc_email,town,rf_postcode,rf_email',
            ],
            '/addCheckInfo/category'    =>  [   //添加经验类目
                'action'    =>  '/OpenShop/category_add',
            ],
            
            '/delCheckInfo/category' =>  [
                'action'    =>  '/OpenShop/category_delete',
            ],
            
            '/addCheckInfo/brand'       =>  [   //添加品牌
                'action'    =>  '/OpenShop/brand_add',
                'nosign'    =>  'b_ename,file',
                'callback'  =>  'brandChangeNosign',
            ],
            
            '/delCheckInfo/brand'   =>  [   //删除品牌
                'action'    =>  '/OpenShop/brand_delete',
            ],
            
            '/addCheckInfo/cert'        =>  [   //添加资质
                'action'    =>  '/OpenShop/cert_add',
                'nosign'    =>  'expire_day,file',
            ],
            '/delCheckInfo/cert'    =>  [   //删除资质
                'action'    =>  '/OpenShop/cert_delete',
            ],
            
            '/Open/step5'   =>  [   //第五步添加店铺信息
                'action'    =>  '/OpenShop/shop_info_add',
            ],
            
            
            '/Goods/offline'=>  [   //商品下架
                'action'    =>  '/SellerGoods/set_goods_offline',
            ],
            '/Goods/online' =>  [   //商品上架
                'action'    =>  '/SellerGoods/set_goods_online',
            ],
            '/Goods/delete' =>  [   //商品删除
                'action'    =>  '/SellerGoods/goods_delete',
            ],
            '/Goods/changeGoodsName'    =>  [   //修改商品标题
                'action'    =>  '/SellerGoods/goods_name_edit',
            ],
            '/Goods/changeSku'=>    [   //编辑商品属性
                'action'    =>  '/SellerGoods/goods_sku_edit',
                'nosign'    =>  'barcode,code,price_purchase,price_market,weight',
                'isArr'     =>  ['httpBuilder', ['id','price','num','price_market','price_purchase','weight','code','barcode']],
            ],
            '/Goods/best'   =>  [   //设置橱窗推荐商品
                'action'    =>  '/SellerGoods/set_best',
            ],
            '/Goods/cancelBest'=>   [   //取消橱窗推荐商品
                'action'    =>  '/SellerGoods/cancel_best',
            ],
            '/Goods/import' => [     // 导入商品
                'action'    => '/Shop/get_list',
				'nosign'    => 'openid'
            ],
            
            
            '/Category/delete'=>    [   //删除店铺分类
                'action'    =>  '/SellerGoods/category_delete',
            ],
            '/Category/deletes'=>   [   //删除多个分类
                'action'    =>  '/SellerGoods/category_more_delete',
            ],
            '/Category/add' =>  [   //添加店铺分类
                'action'    =>  '/SellerGoods/category_add',
                'nosign'    =>  'icon,sid,id',
            ],
            '/Category/edit'=>  [   //修改店铺分类
                'action'    =>  '/SellerGoods/category_edit',
                'nosign'    =>  'icon,sid',
            ],
            
            
             //退货商家
            /*'/Refund/agree'=> [   //商家最终退款
                'action'    =>  '/SellerRefund/accept',
            ],*/
            '/Refund/cancel'=>  [   //商家取消退款
                'action'    =>  '/SellerRefund/trigger_cancel',
            ],
            '/Refund/refuse'=>  [   //商家拒绝退款
                'action'    =>  '/SellerRefund/reject',
            ],
            '/Refund/agree' =>  [   //商家同意退款
                'action'    =>  '/SellerRefund/accept',
                'nosign'    =>  'reason',
            ],
			
            '/Refund/nothing'=> [   //商家未收到退货
                'action'    =>  '/SellerRefund/nothing',
            ],
            '/Refund/messages'=>    [   //商家留言
                'action'    =>  '/SellerRefund/logs',
            ],
			'/Refund2/agree' =>  [   //已发货 商家同意退款
                'action'    =>  '/SellerRefund2/accept',
                'nosign'    =>  'address_id,reason',
            ],
            '/Refund/receipt'=>    [   //已收到退货,确认收货
                'action'    =>  '/SellerRefund2/accept2',
                'nosign'    =>  'reason',
            ],
            '/Refund2/refuse'=> [   //已发货拒绝退款
                'action'    =>  '/SellerRefund2/reject',
                'nosign'    =>  'images,file'
            ],
			'/Refund2/notreceipt'=>[
			     'action'    =>  '/SellerRefund2/notreceipt'    
			],
            '/Refund/appeal'    =>  [   //申诉
                'action'    =>  '/SellerAppeal/index',
                'nosign'    =>  'images,file',
            ],
            '/Appeal/index' =>  [   //申诉
                'action'    =>  '/SellerAppeal/index',
                'nosign'    =>  'file,images',
            ],
            '/Service/opreat/reject'    =>  [   //拒绝售后
                'action'    =>  '/SellerRefund3/reject',
                'nosign'    =>  'file,images',
            ],
            '/Service/opreat/accept'    =>  [   //同意售后
                'action'    =>  '/SellerRefund3/accept',
                'nosign'    =>  '',
            ],
            '/Service/opreat/accept1'   =>  [   //已收到商品
                'action'    =>  '/SellerRefund3/accept1',
                'nosign'    =>  '',
            ],
            '/Service/opreat/send_express'  =>  [   //发货
                'action'    =>  '/SellerRefund3/send_express',
                'nosign'    =>  'express_company_id_search',
            ],
            '/Service/opreat/appeal'    =>  [   //申诉
                'action'    =>  '/SellerAppeal/index',
                'nosign'    =>  'images,file',
            ],


            '/Service/reject'=> [   //卖家拒绝售后
                'action'    =>  '/SellerRefund3/reject',
                'nosign'    =>  'file,images',
            ],

            '/Service/agree' => [   //卖家同意售后
                'action'    =>  '/SellerRefund3/accept',
                'nosign'    =>  '',
            ],

            '/Service/accept'=> [   //卖家收到售后商品
                'action'    =>  '/SellerRefund3/accept1',
                'nosign'    =>  '',
            ],
            '/Service/appeal'    =>  [   //申诉
                'action'    =>  '/SellerAppeal/index',
                'nosign'    =>  'images,file',
            ],

            '/Service/express'  =>  [   //卖家邮寄售后商品
                'action'    =>  '/SellerRefund3/send_express',
                'nosign'    =>  'express_company_id_search',
            ],

            '/Orders/priceEdit' => [    //订单修改价格
                'action'    =>  '/SellerOrders/orders_price_edit',
                //'isArr'     =>  ['httpBuilder', ['goods_price','express_price','s_no']],
            ],

            '/Orders/close' => [    //卖家 关闭订单
                'action'    =>  '/SellerOrders/close',
            ],
            '/SupplierOrders/close' => [    //供货商 关闭订单
                'action'    =>  '/SupplierOrders/close',
            ],
            '/Orders/express' => [    //卖家发货
                'action'    =>  '/SellerOrders/send_express',
                'nosign'    =>  'express_remark,express_company_id_search'
            ],
			'/SupplierOrders/express' => [    //卖家发货
                'action'    =>  '/SupplierOrders/send_express',
                'nosign'    =>  'express_remark,express_company_id_search'
            ],
            
            '/Opreat/close' =>  [       //卖家 关闭订单
                'action'    =>  '/SellerOrders/close',
            ],
            '/Opreat/express'=> [       //卖家发货
                'action'    =>  '/SellerOrders/send_express',
                'nosign'    =>  'express_remark,express_company_id_search'
            ],
            '/Serllser/searchExpressCompany' => [   //搜索快递公司
                'action'    =>  '/Express/search',    
            ],
            '/FreightTemplate/create' => [      // 添加运费模板
                'action'    =>  '/SellerExpress/express_add',
                'nosign'    =>  'remark'
            ],
            '/FreightTemplate/delete' => [     // 删除运费模板
                'action'    => '/SellerExpress/express_delete',
            ],
            '/FreightTemplate/edit' => [     // 修改运费模板
                'action'    => '/SellerExpress/express_edit',
                'nosign'    => 'remark'
            ],
            '/FreightTemplate/area1' => [     // 添加指定地区运费模板
                'action'    => '/SellerExpress/express_area_add',
            ],
            '/FreightTemplate/area2' => [     // 修改指定地区运费模板
                'action'    => '/SellerExpress/express_area_edit',
            ],
            '/FreightTemplate/delete_area' => [     // 删除指定地区运费模板
                'action'    => '/SellerExpress/express_area_delete',
            ],
			'/Brand/view' => [     // 更新推广品牌资料
                'action'    => '/SellerBrand/brand_edit',
				'nosign'    => 'file,category_id[],ename'
            ],
            '/Brand/promotion' => [// 更新推广品牌资料
                'action'    => '/SellerBrand/brand_edit',
                'nosign'    => 'file,category_id[],ename'
            ],
            '/Brand/create' => [     // 更新推广品牌资料
                'action'    => '/SellerBrand/my_brand_add',
                'nosign'    => 'file,b_ename,b_code,b_images,b_images2'
            ],
			'/Brand/brand_add' => [     // 更新推广品牌资料
                'action'    => '/SellerBrand/my_brand_add',
				'nosign'    => 'file,b_ename,b_code,b_images,b_images2'
            ],
			'/Brand/brand_edit' => [     // 更新推广品牌资料
                'action'    => '/SellerBrand/my_brand_edit',
				'nosign'    => 'file,b_ename,b_code,b_images,b_images2'
            ],
            '/Brand/edit' => [     // 更新推广品牌资料
                'action'    => '/SellerBrand/my_brand_edit',
                'nosign'    => 'file,b_ename,b_code,b_images,b_images2'
            ],
            '/Brand/promotionEdit' => [
                'action'    => '/SellerBrand/promotionEdit',
                'nosign'    => 'file,category_id[],ename'
            ],
            '/Workorder/create' => [ # 创建工单
                'action'    => '/Workorder/create',
                'nosign'    => 'email'
            ],
            '/Workorder/handle' => [ # 处理工单
                'action'    => '/Workorder/user_handle',
            ],
            '/Workorder/delete' => [ # 处理工单
                'action'    => '/Workorder/delete',
            ],
            '/Send/sms'     =>  [   //发送短信
                'action'    =>  '/Erp/sms_code',
            ],
            '/Opens/step5'  =>  [
                'action'    =>  '/Erp/depositPays',
                'nosign'    =>  'paytype',
            ],
            '/Tj/visit'     =>  [ # PC统计访问
                'action'    =>  '/Tj/visit',
            ],
            '/Tj/ad_show'     =>  [ # PC统计广告展示
                'action'    =>  '/Tj/ad_show',
            ],
            '/Advisory/reply'   =>  [
                'action'    =>  '/SellerGoodsAdvisory/reply', //回复咨询
            ],
            '/Advisory/edit'    =>  [
                'action'    =>  '/SellerGoodsAdvisory/edit', //修改咨询
            ],
        	'/Shopvr/save_appeal'=>[
        		'action'	=>	'/SellerShopvr/save_appeal',//违规申诉提交
        	],
        	
        ];
		$data['Ad'] = [
            // 添加素材
            '/Ad/sucaiCreate' => [
                'action'    => '/SellerAd/sucai_add',
                'nosign'    =>  'file,background_images,bsize',
            ],
            // 修改素材
            '/Ad/sucaiEdit' => [
                'action'    => '/SellerAd/sucai_edit',
                'nosign'    => 'file,background_images,bsize',
            ],
            // 删除素材
            '/Ad/sucaiDelete' => [
                'action'    => '/SellerAd/sucai_delete',
            ],
            // 购买广告 - 创建订单
            '/Ad/createOrders' => [
                'action'    => '/SellerAd/create_orders',
                
            ],
            // 广告订单删除
            '/Ad/ordersDelete' => [
                'action'    => '/SellerAd/orders_delete',
            ],
            // 广告订单付款
            '/Ad/adPay'   => [
                'action'    => '/Erp/ad_pay',
            ],
            '/Tj/visit'     =>  [ # PC统计访问
                'action'    =>  '/Tj/visit',
            ],
            '/Tj/ad_show'     =>  [ # PC统计广告展示
                'action'    =>  '/Tj/ad_show',
            ],
            '/Tj/getAdtjads' =>[ # 广告统计
                'action' => '/Tj/getAdtjads',
            ],
            '/Tj/getAdtjsc' =>[ # 素材统计
                'action' => '/Tj/getAdtjsc',
            ],
            '/Tj/sucaiTop' =>[ # 素材top
                'action' => '/Tj/sucaiTop',
            ],
            '/Tj/goodsTop' =>[ # 商品top
                'action' => '/Tj/goodsTop',
            ],
            '/Tj/adLive' =>[ # 实时数据
                'action' => '/Tj/adLive',
            ],
        ];
        
        //Home模块
        $data['Www']    =   [
            '/Index/getNews'  =>  [ //获取最新新闻
                'action'    =>  '/Erp/top_news',
                'nosign'    =>  'p,pagesize',
            ],
            '/Index/getFaq' =>  [   //获取最新帮助
                'action'    =>  '/Help/getNew',
                'nosign'    =>  'num,sort',
            ],
            '/Index/getNew' =>  [   //获取最新新闻
                'action'    =>  '/News/getNew',
                'nosign'    =>  'num,sort',
            ],
            '/Index/getAd' =>  [   //获取广告
                'action'    =>  '/Ad/ad',
            ],
            '/Index/getAds' =>  [   //获取多个广告
                'action'    =>  '/Ad/ads',
            ],
            '/Index/like' =>  [   // 猜你喜欢
                'action'    =>  '/Goods/love_goods',
                'nosign'    =>  'imgsize,limit,score_ratio',
            ],
            '/Index/recom' =>  [   // 热销商品 
                'action'    =>  '/Goods/hot_goods',
                'nosign'    =>  'imgsize,limit',
            ],
            '/Tj/visit'     =>  [ # PC统计访问
                'action'    =>  '/Tj/visit',
            ],
            '/Tj/ad_show'     =>  [ # PC统计广告展示
                'action'    =>  '/Tj/ad_show',
            ],
            '/Coupon/index' =>  [
                'action'    =>  '/Coupon/get_coupon',
            ],
        

        ];

        # Lists模块
        $data['S']    =   [
            '/Index/like' =>  [   // 猜你喜欢
                'action'    =>  '/Goods/love_goods',
                'nosign'    =>  'imgsize,limit,score_ratio',
            ],
            '/Index/recom' =>  [   // 热销商品 
                'action'    =>  '/Goods/hot_goods',
                'nosign'    =>  'imgsize,limit',
            ],
			'/Index/favgoods'  =>  [    //收藏商品
                'action'    =>  '/Fav/goods_add',
            ],
            '/Tj/visit'     =>  [ # PC统计访问
                'action'    =>  '/Tj/visit',
            ],
            '/Tj/ad_show'     =>  [ # PC统计广告展示
                'action'    =>  '/Tj/ad_show',
            ],
        ];
        
        $data['Click'] = [
            '/Tj/ad_visit' => [
                'action' => '/Tj/ad_visit',
                'nosign' => '',
            ],
        ];

        $data['Sellergoods'] = [
            '/Cate/category_add' => [
                'action' => '/SellerGoodsManage/category_add',
                'nosign' => 'icon,sid',
            ],
            '/Cate/category_edit' => [
                'action' => '/SellerGoodsManage/category_edit',
                'nosign' => 'icon,sid',
            ],
            '/Cate/category_del' => [
                'action' => '/SellerGoodsManage/category_delete',
                'nosign' => 'icon,sid',
            ],
            '/Cate/sort' => [
                'action' => '/SellerGoodsManage/category_sort',
                'nosign' => '',
            ],
            '/Cate/category_more_del' => [
                'action' => '/SellerGoodsManage/category_more_delete',
                'nosign' => '',
            ],
            '/Cate/auto_brand_cate' => [
                'action' => '/SellerGoodsManage/category_auto_brand',
                'nosign' => '',
            ],
			'/Goods/offline'=>  [   //商品下架
                'action'    =>  '/SellerGoods/set_goods_offline',
            ],
            '/Goods/online' =>  [   //商品上架
                'action'    =>  '/SellerGoods/set_goods_online',
            ],
            '/Goods/delete' =>  [   //商品删除
                'action'    =>  '/SellerGoods/goods_delete',
            ],
        ];

        $data['Sell'] = [
            '/Goods/import' => [     // 导入商品
                'action'    => '/Shop/get_list',
                'nosign'    => 'openid'
            ],
            '/Supplier/goods_import' => [     // 供货商导入商品
                'action'    => '/Shop/get_list',
                'nosign'    => 'openid'
            ],
        ];
        return $data[$moudle][$action];
    }
}