<?php
namespace Seller\Controller;
class OpensController extends AuthController {
    protected $_account =   [];
    protected $_step    =   [];
    public function _initialize() {
		//该入口已停用，直接跳至招商频道
		redirect(DM('zhaoshang'));
		exit();
		
        parent::_initialize();
        if (isset($_GET['up']) && I('get.up') == 'yes') {
            $this->setSessions();   //更新session
        }
        //0个人，1企业
        $this->_account['type'] =   session('user.type');
        //$this->_account['type'] =   1;
        //认证状态，0为未认证，1为已认证，-1认证失败
        $this->_account['auth'] =   session('user.is_auth');
        //用户等级3为创客会员
        $this->_account['level']=   session('user.level_id');
        //记录步骤
        $this->_step =   M('shop_join_step')->where(['uid' => getUid()])->field('id,step')->find();
    }
    
    /**
     * 检测能否进入下一步
     * @param unknown $now
     */
    private function checkAccount($now) {
        if ($this->_step['step'] == 8) {
            $this->redirect('/opens');
        }
        if (session('opensNext') < $now) {
            $this->redirect('/opens/step' . ($now - 1));
        }
    }
    
    /**
     * 开店欢迎界面
     *  1、未开店显示welcome模板
     *  2、开店中显示logs模板
     *  3、开店成功显示success模板
     */
    public function index() {
        $data['type']   =   $this->_account['type'];
        $data   =   M('shop_join_step')->where(['uid' => getUid()])->find();
        //$data['step']   =   7;
        if ($data['step'] == 10) {
            $shop           =   M('shop')->cache(true)->where(['uid' => getUid()])->field('id,domain,shop_name,category_id,type_id')->find();
            $shop['domain'] =   shop_url($shop['id'], $shop['domain']);
            $shop['cate']   =   $this->getCategoryName($shop['category_id']);
            $shop['type']   =   M('shop_type')->cache(true)->where(['id' => $shop['type_id']])->getField('type_name');
            $this->assign('shop', $shop);
        } elseif ($data['step'] == 8) {
            $this->opensShopSteps($data['step']);
        } else {
            $notPass    =   [];
            if ($data['step'] == 9) {
                $notPass    =   D('ShopJoinInfoLogsView')->where(['uid' => getUid()])->order('shop_join_logs.id desc')->find();
                $this->assign('notPass', $notPass);
            }
            session('opensNext', $data['step']);
            $this->opensShopSteps($data['step'], $notPass['not_pass']);
        }
        $this->assign('account', $this->_account);
		C('seo', ['title' => '我要开店']);
        $this->assign('data', $data);
        $this->display();
    }
    
    /**
     * 开店协议
     */
    public function protocol() {
        $this->api('/OpenShop/agreement')->with();
        C('seo', ['title' => '开店协议']);
        $this->display();
    }
    
    
    /**
     * 资质认证，是否为乐兑会员，是否已经实名认证，是否已升级为创客会员
     */
    public function step1() {
        $this->authApi('/Erp/account');
        $this->_account['pay'] =   $this->_data['data']['a_payPwd'] ? 1 : 0;
        $this->assign('data', $this->_account);
        //是否可以进入下一步
        $next   =   false;
        if ($this->_account['auth'] == 1 && $this->_account['pay'] == 1 && $this->_account['level'] > 2) {
            $next = true;
        }
        C('seo', ['title' => '资质认证']);
        $this->stepArr(1, $next);
        $this->display();
    }
    
    /**
     * 选择店铺类型
     * 分三步
     * 第一步  选择店铺类型
     * 第二部  选择品牌类目 - 品牌 - 资质证明
     */
    public function step2() {
        $this->checkAccount(2);
        if (IS_POST) {
            C('TOKEN_ON', false);
            $data   =   I('post.');
            $id     =   M('shop_join_contact')->where(['uid' => getUid()])->getField('id');
            unset($data['id']);
            $model  =   D('ShopJoinContact');
            if (!$data = $model->create()) {
                $this->ajaxReturn(['code' => 0, 'msg' => $model->getError()]);
            }
            $model->startTrans();
            if ($id > 0) {
                $flag    =   $model->where(['id' => $id, 'uid' => getUid()])->save();
                if (false === $flag) goto error;
                if (false === M('shop_join_info')->where(['uid' => getUid()])->save(['type_id' => $data['type_id']])) {
                    goto error;
                }
            } else {
                $flag    =   $model->add();
            }
            
            if ($flag) {
                session('opensNext', 2);
                $model->commit();
                $this->ajaxReturn(['code' => 1, 'msg' => '操作成功']);
            } else {
                goto error;
            }
            
            error:
            $model->rollback();
            $this->ajaxReturn(['code' => 0, 'msg' => '无任何修改']);
        } else {
            $next   =   1;
            $this->api('/OpenShop/shop_type');      //获取店铺开店类型
            $type   =   $this->_data['data'];
            //if ($this->_account['type'] == 0) { //如果是个人开店，则只有一个类型可供选
                $type   =   [];
                foreach ($this->_data['data'] as $val) {
                    if ($this->_account['type'] == 0) {
                        if ($val['id'] == 6) {
                            $type[$val['id']]  =   $val['type_name'];
                        }
                    } else {
                        $type[$val['id']]  =   $val['type_name'];
                    }
                }
            //}
            $this->assign('type', $type);
            $data   =   M('shop_join_contact')->where(['uid' => getUid()])->field('id,linkname,email,mobile,tel,type_id,rf_linkname,rf_mobile,rf_tel,rf_province,rf_city,rf_district,rf_town,rf_street,rf_postcode')->find();
            $this->builderForm()
            ->keyId()
            ->keySelect('type_id', '店铺类型', $type, 1, '<div id="typeContent"></div>')
            ->keyText('linkname', '店铺负责人姓名', 1)
            ->keyText('mobile', '店铺负责人手机', 1)
            ->keyText('tel', '店铺负责人电话', '', '店铺负责人电话,格式020-88888888')
            ->keyText('email', '店铺负责人邮箱', 1)
            ->keyText('rf_linkname', '退货联系人姓名', 1)
            ->keyText('rf_mobile', '退货联系人手机', 1)
            ->keyText('rf_tel', '退货联系人电话', 0, '退货联系人电话电话,格式020-88888888')
            ->keyCity(array('province' => 'rf_province', 'city' => 'rf_city', 'district' => 'rf_district', 'town' => 'rf_town'), '退货地址', 1)
            ->keyTextArea('rf_street', '退货详细地址', 1)
            ->keyText('rf_postcode', '退货邮编')
            ->data($data)
            ->view();
            
            if (empty($data) || empty($data['type_id'])) {
                $next   =   0;
            }
            $this->assign('data', $data);
            C('seo', ['title' => '选择店铺类型']);
            $this->assign('next', $next);
            $this->stepArr(2, $next);
            $this->display();
        }
    }
    
    /**
     * 联系人信息
     */
    public function step3() {
        $next   =   1;
        $this->checkAccount(3);
        $typeId =   M('shop_join_contact')->where(['uid' => getUid()])->getField('type_id');
        if (!$typeId) {
            $this->redirect('/opens/step2');
            exit;
        }
        $product    =   M('shop_join_products')->where(['uid' => getUid()])->field('atime,etime', true)->find();
        $info       =   null;
        if (!$product) {
            $this->authApi('/OpenShop/brand')->with('brand');   //品牌
            if (!empty($this->_data['data'])) $info =   'brand';
        } else {
            $info   =   'product';
            $this->assign('product', $product);
        }
        $cate   =   explode(',', M('shop_join_category')->where(['uid' => getUid()])->getField('cates'));
        if ((empty($this->_data['data']) && !$product) || empty($cate)) {
            $next   =   false;
        }
        //如果是企业会员则需要添加银行信息
//         if ($this->_account['type'] == 1) {
//             $data   =   M('shop_join_bank')->where(['uid' => getUid()])->find();
//             $this->builderForm()
//             ->keyId()
//             ->keySelect('bank_id', '开户银行', getBank(), 1)
//             ->keySingleImages('bank_license', '银行开户许可证', 1, '<span class="text_blue">图片尺寸800px*800px以内，大小800kb以内，支持png/jpg/jpeg格式图片</span>')
//             ->keyText('bank_account', '开户银行账号', 1)
//             ->keyText('bank_name', '开户名', 1)
//             ->keyText('bank_no', '支行联号', 1)
//             ->keyCity(['province' => 'province', 'city' => 'city', 'district' => 'district', 'town' => 'town'], '支行所在地', 1)
//             ->keySday(['sday', 'eday'], '纳税有效期限', [], '<span class="text_blue">不填则为长期</span>')
//             ->keySingleImages('tax_cert', '纳税资格证', 1, '<span class="text_blue">图片尺寸800px*800px以内，大小800kb以内，支持png/jpg/jpeg格式图片</span>')
//             ->data($data)
//             ->view();
//             if (empty($data)) {
//                 $next   =   false;
//             }
//         }
        $this->assign('info', $info);
        $this->assign('account', $this->_account);
        $shopType   =   getShopType($typeId);
        $this->assign('shopType', $shopType);
        $this->assign('cate', $this->getCategoryName($cate));
        C('seo', ['title' => '设置主营类目']);
        $this->stepArr(3, $next);
        $this->display();
    }
    
    /**
     * 开店认证
     * 分两步
     * 第一步填写商标信息
     * 上传商标资质证明
     *  一般纳税人
     *  开户银行许可证
     *  化妆品卫生许可证
     *  进口化妆品卫生许可批件
     *  化妆品检测报告复印件
     */
    public function step4() {
        $this->checkAccount(4);
        $next   =   true;
        $cates  =   M('shop_join_category')->where(['uid' => getUid()])->getField('cates');
        //$cert   =   D('ShopJoinCategoryCertView')->where(['category_id' => ['in', $cates], 'uid' => getUid()])->select();
        $cert = M('goods_category_cert')->alias('c')->where(['c.status' => 1, 'c.category_id' => ['in', $cates]])->select();
        
        foreach ($cert as $k => $v) {
            $cert[$k]['child']  =   M('shop_join_category_cert')->where(['uid' => getUid(), 'cert_id' => $v['id']])->find();
        }
        foreach ($cert as $val) {
            if (is_null($val['child'])) {
                $next   =   false;
                break;
            }
        }
        $this->assign('cert', $cert);
        C('seo', ['title' => '开店认证']);
        $trademark   =   D('ShopJoinBrandView')->where(['uid' => getUid()])->order('id asc')->select();
        if ($next == true) {
            unset($val);
            foreach ($trademark as $val) {
                if (is_null($val['c_id'])) {
                    $next   =   false;
                    break;
                }
            }
        }
        $this->assign('trademark', $trademark);
        $this->stepArr(4, $next);
        $this->display();
    }
    
    /**
     * 缴纳保证金，个人会员开店时
     */
    public function step5() {
        if (session('opensNext') > 4) {
            session('opensNext', 6);
            $this->redirect('/opens/step6');
        } else {
            $this->redirect('/opens/step' . session('opensNext'));
        }
        exit;
        $this->checkAccount(5);
        if ($this->_account['type'] == 1) { //如果是企业会员，则直接跳转到下一步
            $this->redirect('/opens/step6');
            exit;
        }
        if (!$data = M('shop_join_orders')->where(['uid' => getUid()])->find()) {
            $model  =   D('Common/ShopJoinOrders');
            if (!$aData = $model->token(false)->create(['uid' => getUid()])) {
                
            }
            if ($model->add($aData)) {
                $data['pay_status'] =   0;  //设置为未支付状态
            }
        }
        
        $this->authApi('/Erp/account')->with('account');    //账户信息
        $pays   =   [
            1   =>  [
                'name'  =>  '使用余额支付',
                'val'   =>  '账户余额：' . $this->_data['data']['a_freeMoney'],
            ],
            /*2   =>  [
                'name'  =>  '使用唐宝支付',
                'val'   =>  '唐宝余额：' . $this->_data['data']['a_tangBao'],
            ],*/
            //3   =>  '使用支付宝支付',
            //4   =>  '使用微信支付',
        ];
        if ($data['pay_status'] == 0 && $this->_data['data']) {
            $this->builderForm()
            ->keyId('paytype')
            ->keyPass('password_pay', '安全密码', 1)
            ->view();
        }
        $this->assign('data', $data);
        $this->assign('pays', $pays);
        C('seo', ['title' => '缴纳保证金']);
        $this->stepArr(5, $data['pay_status']);
        $this->display();
    }
    
    /**
     * 完善店铺信息
     */
    public function step6() {
        $this->checkAccount(6);
        $this->authApi('/OpenShop/shop_info');
        $data   =   M('shop_join_info')->where(['uid' => getUid()])->field('shop_name,id,about,province,city,district,town,street')->find();
        $data['inventory_type'] =   1;
        $this->builderForm()
        ->keyId()
        ->keyText('shop_name', '店铺名称', 1)
        ->keyTextArea('about', '店铺介绍', 1)
        //->keyText('qq', '腾讯QQ', 1)
        ->keyCity(['province' => 'province', 'city' => 'city', 'district' => 'district', 'town' => 'town'], '所在地区', 1)
        ->keyTextArea('street', '详细地址', 1)
        ->data($data)
        ->view();
        $this->assign('data', $data);
        C('seo', ['title' => '完善店铺信息']);
        $this->stepArr(6, $data['shop_name']);
        $this->display();
    }
    
    /**
     * 设置结算方式
     *  库存积分分发
     *  扣除贷款
     */
    public function step7() {
        $this->checkAccount(7);
        $data           =   M('shop_join_info')->where(['uid' => getUid()])->field('id,inventory_type')->find();
        $data['mobile'] =   M('user')->where(['id' => getUid()])->getField('mobile');
        $this->builderForm()
        ->keyId('inventory_type')
        ->keyId('mobile')
        ->keyId()
        //->keySelect('inventory_type', '结算方式', [1 => '库存积分分发方式'], 1)
        ->keyVcode('vcode', '图形验证码', 1)
        ->keyHtmltext('手机号码', $data['mobile'])
        ->keySmsCode('smscode', '短信验证码')
        ->data($data)
        ->view();
        C('seo', ['title' => '设置结算方式']);
        $this->stepArr(7);
        $this->assign('data', $data);
        $this->display();
    }
    
    /**
     * 资质，类目，品牌添加
     */
    public function addCheckInfo() {
        $type   =   I('get.type');
        $typeArr=   ['category', 'cert', 'brand', 'trademark', 'product'];
        if (!in_array($type, $typeArr)) {
            return;
        }
    
        if ($type == 'category' || $type == 'cert') {   //获取经验类目
            $options    =   [];
            $this->api('/OpenShop/category_list');
            foreach ($this->_data['data'] as $v) {
                $options[$v['id']]  =   $v['category_name'];
            }
        }
    
        switch ($type) {
            case 'category':
                $this->builderForm()
                ->keySelect('category_id', '一级类目', $options, 1)
                ->view();
                break;
            case 'cert':    //添加证书
                $data['cert_id']    =   I('get.certId');
                if (isset($_GET['id'])) {
                    $id     =   I('get.id');
                    $data   =   M('shop_join_category_cert')->where(['id' => $id, 'uid' => getUid(), 'cert_id' => $data['cert_id']])->find();
                }
                $this->builderForm()
                ->keyId()
                ->keyId('cert_id')
                ->keySday(['sday', 'eday'], '资格证书效期限', [], '<span class="text_yellow">不填写则为长期</sapn>')
                ->keySingleImages('cert_images', '资格证照片', 1)
                ->data($data)
                ->view();
                $run    =   '/opens/addCert';
                break;
            case 'brand':       //添加品牌
                if (isset($_GET['id'])) {
                    $id     =   I('get.id');
                    $data   =   M('shop_join_brand')->where(['id' => $id, 'uid' => getUid()])->find();
                }
                $this->builderForm()
                ->keyId()
                ->keyText('b_name', '品牌中文名', 1)
                ->keyText('b_ename', '品牌英文名')
                ->keySingleImages('b_logo', '品牌LOGO', 1)
                ->keySingleImages('b_images', '品牌商标证书照片', 1, '<span class="text_yellow">当没有商标授理书照片不能为空</span>')
                ->keySingleImages('b_images2', '商标授理书照片', '', '<span class="text_yellow">当没有品牌商标证书照片时不能为空</span>')
                ->keyText('b_master', '品牌所有者', 1)
                //->keyText('b_code', '品牌商标注册号', 0, '当有品牌商标证书照片的时候不能为空')
                //->keyText('b_type', '品牌类型', 1)
                //->keyText('b_scope', '经营类型', 1)
                ->data($data)
                ->view();
                $run    =   '/opens/addBrand';
                break;
            case 'trademark' :  //添加资质
                $run    =   '/opens/addTrademark';
                $data   =   [];
                if (isset($_GET['id'])) {
                    $id     =   I('get.id');
                    $data   =   M('shop_join_cert')->where(['uid' => getUid(), 'id' => $id])->find();
                }
                $data['brand_id']   =   I('get.b_id');
                $this->builderForm()
                ->keyId()
                ->keyId('brand_id')
                ->keyHtmltext('品牌名称', M('shop_join_brand')->where(['id' => $data['brand_id'], 'uid' => getUid()])->getField('b_name'))
                ->keyRate('reg_type', '注册类型', [1 => 'R标', 2 => 'TM标'], 1, '<span class="text_yellow">R：已获得《商标注册证》TM：未获得《商标注册证》，仅有《注册申请受理通知书》</span>')
                ->keyText('reg_people', '商标注册人')
                ->keyText('apply_people', '商标申请人')
                ->keyText('reg_no', '商标注册号')
                ->keyText('apply_no', '商标申请号')
                ->keySday(['sday', 'eday'], '商标注册有效期限', [], '<span class="text_yellow">不填写则为长期</sapn>')
                ->keyDate('reg_date', '商标注册时间', 1)
                ->keyRate('is_import', '商标原产地', [1 => '进口', 0 => '非进口'], 1)
                ->keySingleImages('license_images', '商标证', 1)
                ->keyRate('is_proxy', '是否为代理', [0 => '否', 1 => '是'], 1)
                ->keySday(['psday', 'peday'], '代理有效期限', [], '<span class="text_yellow">不填写则为长期</sapn>')
                ->keySingleImages('proxy_cert', '代理有效资格证')
                ->data($data)
                ->view();
                break;
            case 'product':
                $run    =   '/opens/addProduct';
                $data   =   [];
                if (isset($_GET['id'])) {
                    $id     =   intval(I('get.id'));
                    $data   =   M('shop_join_products')->where(['id' => $id, 'uid' => getUid()])->find();
                }
                $this->builderForm()
                ->keyId()
                ->keyMultiImages('pro_images', '产品图片', 1, '最多可上传5张图片')
                ->keyMultiImages('cert_images', '相关资质', 1, '最多可上传5张图片')
                ->keyTextArea('intro', '产品说明', 1, '产品说明长度在10-300个字符之间')
                ->data($data)
                ->view();
                break;
        }
        $run    =   $run ? $run : '/run/authRun';
        $this->assign('run', $run);
        $this->assign('uploadAction', enCryptRestUri('/Upload/curlUpload'));
        $this->assign('header', enCryptRestUri('/addCheckInfo/' . $type));
        $this->display();
    }
    
    /**
     * 添加资质
     */
    public function addTrademark() {
        if (IS_POST) {
            C('TOKEN_ON', false);
            $data   =   I('post.');
            $id     =   intval($data['id']);
            $model  =   D('ShopJoinTrademark');
            if (!empty($data['sday']) && !empty($data['eday'])) {
                $data['reg_expire'] =   $data['sday'] . ' 至 ' . $data['eday'];
            }
            if (!$data = $model->token(false)->create($data)) {
                $msg    =   null;
                if (function_exists($model->getError())) {
                    if (I('post.reg_type') == 1) {
                        $msg    =   call_user_func_array($model->getError(), [I('post.reg_type'), [I('post.reg_people'), I('post.reg_no')]]);
                    } else {
                        $msg    =   call_user_func_array($model->getError(), [I('post.reg_type'), [I('post.apply_no'), I('post.apply_no')]]);
                    }
                }
                $this->ajaxReturn(['code' => 0, 'msg' => !is_null($msg) ? $msg : $model->getError()]);
            }
            if ($id > 0) {
                $flag   =   $model->where(['uid' => getUid(), 'id' => $id])->save();
            } else {
                $flag   =   $model->add();
            }
            if ($flag) {
                session('opensNext', 4);
                $this->ajaxReturn(['code' => 1, 'msg' => '操作成功']);
            }
            $this->ajaxReturn(['code' => 0, 'msg' => '无任何修改']);
        }
    }
    
    /**
     * 添加产品信息
     */
    public function addProduct() {
        if(IS_POST) {
            C('TOKEN_ON', false);
            $data           =   I('post.');
            $data['uid']    =   session('user.id');
            $id             =   intval($data['id']);
            $model          =   D('ShopJoinProducts');
            if (!$data = $model->token(false)->create($data)) {
                $this->ajaxReturn(['code' => 0, 'msg' => $model->getError()]);
            }
            if ($id > 0) {
                $flag   =   $model->where(['uid' => getUid(), 'id' => $id])->save();
            } else {
                $flag   =   $model->add();
            }
            if ($flag) {
                session('opensNext', 3);
                $this->ajaxReturn(['code' => 1, 'msg' => '操作成功']);
            }
            $this->ajaxReturn(['code' => 0, 'msg' => '无任何修改']);
        }
    }
    
    /**
     * 店铺信息
     */
    public function shop() {
        if (IS_POST) {
            C('TOKEN_ON', false);
            $data   =   I('post.');
            $model  =   D('ShopJoinInfo');
            $id     =   M('shop_join_info')->where(['uid' => getUid()])->getField('id');
            if (!$data = $model->token(false)->create()) {
                $this->ajaxReturn(['code' => 0, 'msg' => $model->getError()]);
            }
            if ($id > 0) {
                $flag   =   $model->where(['id' => $id, 'uid' => getUid()])->save();
            } else {
                $flag   =   $model->add();
            }
            if ($flag) {
                session('opensNext', 7);
                $this->ajaxReturn(['code' => 1, 'msg' => '操作成功']);
            }
            $this->ajaxReturn(['code' => 0, 'msg' => '无任何修改']);
        }
    }
    
    /**
     * 设置结算方式
     */
    public function setInventory() {
        if (IS_POST) {
            C('TOKEN_ON', false);
            $data   =   I('post.');
            $this->authApi('/Erp/checkSmsCode', ['mobile' => I('post.mobile'), 'smscode' => I('post.smscode')]);
            if ($this->_data['code'] == 1) {
                $model  =   D('ShopInventory');
                if (!$data = $model->token(false)->create()) {
                    $this->ajaxReturn(['code' => 0, 'msg' => $model->getError()]);
                }
                if ($model->where(['uid' => getUid()])->save()) {
                    if ($this->_step['step'] < 8) { //不为审核通过的情况下才能够设置第八步
                        M('shop_join_step')->where(['uid' => getUid(), 'id' => $this->_step['id']])->save(['step' => 8]);   //设置步骤
                    }
                    $this->ajaxReturn(['code' => 1, 'msg' => '操作成功']);
                }
                $this->ajaxReturn(['code' => 0, 'msg' => '无任何修改']);
            }
            $this->ajaxReturn(['code' => 0, 'msg' => $this->_data['msg']]);
        }
    }
    
    /**
     * 添加分类
     */
    public function category() {
        if(IS_POST) {
            C('TOKEN_ON', false);
            $data   =   I('post.');
            $data['cates']  =   trim($data['category_id'], ',');
            unset($data['category_id']);
            $model  =   D('ShopJoinCategory');
            $id     =   M('shop_join_category')->where(['uid' => getUid()])->getField('id');
            if (!$data = $model->token(false)->create($data)) {
                $msg    =   null;
                if (function_exists($model->getError())) {
                    $msg    =   call_user_func_array($model->getError(), []);
                }
                $this->ajaxReturn(['code' => 0, 'msg' => !is_null($msg) ? $msg : $model->getError()]);
            }
            if ($id > 0) {
                $flag   =   $model->where(['id' => $id, 'uid' => getUid()])->save();
            } else {
                $flag   =   $model->add();
            }
            if ($flag) {
                session('opensNext', 3);
                $this->ajaxReturn(['code' => 1, 'msg' => '操作成功']);
            }
            $this->ajaxReturn(['code' => 0, 'msg' => '无任何修改']);
        } else {
            $data   =   $this->getCategory();
            $this->assign('data', $data);
            $cate   =   M('shop_join_category')->where(['uid' => getUid()])->field('id,cates')->find();
            $typeId =   M('shop_join_contact')->where(['uid' => getUid()])->getField('type_id');
            if (!$typeId) {
                $this->redirect('/opens/step2');
                exit;
            }
            $shopType   =   getShopType($typeId);
            $this->assign('shopType', $shopType);
            $this->assign('cate', $cate);
            $this->display();
        }
    }
    
    /**
     * 获取分类
     * @return mixed
     */
    private function getCategory() {
        $data   =   S('seller_opens_getCategory');
        if (!$data) {
            $field  =   'id,category_name';
            $data   =   M('goods_category')->where(['status' => 1, 'sid' => 0])->order('sort asc')->field($field)->select();
            foreach($data as &$val) {
                $val['child']  =   (M('goods_category')->where(['status' => 1, 'sid' => $val['id']])->order('sort asc')->getField($field));
            }
            $data   =   serialize($data);
            S('seller_opens_getCategory', $data);
        }
        return unserialize($data);
    }
    
    /**
     * 获取分类名称
     * @param unknown $cate
     */
    private function getCategoryName($cate) {
        $cates  =   '';
        if (is_string($cate)) {
            $cate   =   explode(',', $cate);
        }
        foreach ($this->getCategory() as $k => $v) {
            foreach ($v as $key => $val) {
                foreach ($val as $keys => $vals) {
                    if (in_array($keys, $cate)) {
                        $cates .= $vals . ',';
                    }
                }
            }
        }
        unset($k,$v,$val,$key,$vals,$keys,$cate);
        return trim($cates, ',');
    }
    
    
    /**
     * 支付
     */
    public function pays() {
        if (IS_POST) {
            C('TOKEN_ON', false);
            $data   =   I('post.');
            $data['pay_status'] =   1;
            $data['pay_money']  =   1000.00;
            $id     =   M('shop_join_orders')->where(['uid' => getUid()])->getField('id');
            $model  =   D('ShopJoinOrders');
            if (!$data = $model->token(false)->create($data)) {
                $this->ajaxReturn(['code' => 0, 'msg' => $model->getError()]);
            }
            if ($id > 0) {
                $flag   =   $model->where(['id' => $id, 'uid' => getUid()])->save();
            } else {
                $flag   =   $model->add();
            }
            
            if ($flag) {
                session('opensNext', 6);
                $this->ajaxReturn(['code' => 1, 'msg' => '支付成功']);
            }
            $this->ajaxReturn(['code' => 0, 'msg' => '支付失败']);
        }
    }
    
    /**
     * 添加证书
     */
    public function addCert() {
        if (IS_POST) {
            C('TOKEN_ON', false);
            $data   =   I('post.');
            $id     =   $data['id'];
            if (!empty($data['sday']) && !empty($data['eday'])) {
                $data['expire'] =   $data['sday'] . '至' . $data['eday'];
            }
            $model  =   D('ShopJoinCategoryCert');
            if (!$data = $model->token(false)->create($data)) {
                $this->ajaxReturn(['code' => 0, 'msg' => $model->getError()]);
            }
            if ($id > 0) {
                $flag   =   $model->where(['id' => $id, 'uid' => getUid()])->save();
            } else {
                $flag   =   $model->add();
            }
            if ($flag) {
                session('opensNext', 4);
                $this->ajaxReturn(['code' => 1, 'msg' => '操作成功']);
            }
            $this->ajaxReturn(['code' => 0, 'msg' => '无任何修改']);
        }
    }
    
    /**
     * 添加品牌
     */
    public function addBrand() {
        if (IS_POST) {
            C('TOKEN_ON', false);
            $data   =   I('post.');
            $id     =   intval($data['id']);
            $model  =   D('ShopJoinBrand');
            if (!$data = $model->token(false)->create()) {
                $msg    =   null;
                if (function_exists($model->getError())) {
                    $msg    =   call_user_func_array($model->getError(), []);
                }
                $this->ajaxReturn(['code' => 0, 'msg' => !is_null($msg) ? $msg : $model->getError()]);
            }
            if ($id > 0) {
                $flag   =   $model->where(['id' => $id, 'uid' => getUid()])->save();
            } else {
                $flag   =   $model->add();
            }
            if ($flag) {
                session('opensNext', 3);
                $this->ajaxReturn(['code' => 1, 'msg' => '操作成功']);
            }
            $this->ajaxReturn(['code' => 0, 'msg' => '无任何修改']);
        }
    }
    
    /**
     * 获取开店logs步骤
     * @param array $status
     */
    private function opensShopSteps($current,$status = null) {
        $data['step']   =   [
            1   =>  [
                'title' =>  '资质认证',
                'intro' =>  '检测用户是否符合开店资格：1、实名认证 2、会员等级3、安全密码',
                'url'   =>  '/opens/step1' . C('TMPL_TEMPLATE_SUFFIX'),
            ],
            2   =>  [
                'title' =>  '选择店铺类型',
                'intro' =>  '选择店铺类型及设置联系方式',
                'url'   =>  '/opens/step2' . C('TMPL_TEMPLATE_SUFFIX'),
            ],
            3   =>  [
                'title' =>  '添加主营类目',
                'intro' =>  '设置店铺主营类目及品牌信息',
                'url'   =>  '/opens/step3' . C('TMPL_TEMPLATE_SUFFIX'),
            ],
            4   =>  [
                'title' =>  '开店认证',
                'intro' =>  '上传商标信息及商标资质证明',
                'url'   =>  '/opens/step4' . C('TMPL_TEMPLATE_SUFFIX'),
            ],
            5   =>  [
                'title' =>  '缴纳保证金',
                'intro' =>  '缴纳保证金',
                'url'   =>  '/opens/step5' . C('TMPL_TEMPLATE_SUFFIX'),
            ],
            6   =>  [
                'title' =>  '完善店铺信息',
                'intro' =>  '完善店铺基本信息',
                'url'   =>  '/opens/step6' . C('TMPL_TEMPLATE_SUFFIX'),
            ],
            7   =>  [
                'title' =>  '设置结算方式',
                'intro' =>  '设置结算方式',
                'url'   =>  '/opens/step7' . C('TMPL_TEMPLATE_SUFFIX'),
            ],
            8   =>  [
                'title' =>  '资料提交',
                'intro' =>  '全部资料已提交成功，耐心等待审核。',
            ]
        ];
        if ($status) {
            if (is_string($status)) {
                $status =   explode(',', $status);
            }
            foreach ($data['step'] as $key => $val) {
                if (in_array($key, $status)) {
                    $data['step'][$key]['status']   =   -1;
                }
            }
        }
        $data['current']    =   $current;   //当前步骤
        //if ($this->_account['type'] == 1) {
            unset($data['step'][5]);
        //}
        $this->assign('logs', $data);
    }
    
    /**
     * 所有步骤
     */
    private function stepArr($curret = 1, $next = false) {
        //if ($this->_account['type'] == 1 && $curret == 4) {
        if ($curret == 4) {
            session('opensNext', $next == true ? ($curret + 2) : $curret);
        } else {
            session('opensNext', $next == true ? ($curret + 1) : $curret);
        }
        $data['step']   =   [
            1   =>  '资质认证',
            2   =>  '选择店铺类型',
            3   =>  '设置主营类目',
            4   =>  '开店认证',
            5   =>  '缴纳保证金',
            6   =>  '完善店铺信息',
            7   =>  '设置结算方式',
        ];
        //$data['class']  =   'col_7';
    
        //type 0个人，1企业
        //if ($this->_account['type'] == 1) {
            unset($data['step'][5]);
            $data['class']  =   'col-xs-2';
        //}
        $data['type']   =   $this->_account['type'];
        $data['curret'] =   $curret;
        //记录步骤
        if ($this->_step) { //如果有步骤
            $url    =   null;
            if ($this->_step['step'] == 10) {   //审核已通过则跳转到开店成功页
                $url    =   '/opens';
            } elseif ($this->_step['step'] == 8) {  //等等审核则为可以去任何一个步骤
    
            } else {    //若是跨步骤则跳回到记录页
                $url    =   '/opens/step' . $this->_step['step'];
            }
            if ($url && ($curret > session('opensNext'))) {   //如果当前步骤大于记录步骤则跳回记录步骤
                $this->redirect($url);
                exit;
            }
            if (($curret < 8 && $curret > $this->_step['step'])) {
                M('shop_join_step')->where(['uid' => getUid(), 'id' => $this->_step['id']])->save(['step' => $curret]);
            }
        } elseif ($curret == 1) {   //如果没有步骤并且当前步骤在第一步则写入步骤
            M('shop_join_step')->add(['uid'=>getUid(),'step'=>$curret]);
        } else {    //如果没有记录步骤则跳回步骤1
            $this->redirect('/opens/step1');
            exit;
        }
        $this->assign('next', $next);
        $this->assign('step', $data);
    }
    
    /**
     * 检测域名
     */
    public function check() {
        $name   =   I('get.shop_name');
        $this->authApi('/OpenShop/check_shop_name', ['shop_name' => $name]);
        if ($this->_data['code'] == 1) {
            echo 'true';
        } else {
            echo 'false';
        }
    }
    
    /**
     * 添加银行信息
     */
    public function addBank() {
        if (IS_POST) {
            C('TOKEN_ON', false);
            $data   =   I('post.');
            $id     =   M('shop_join_bank')->where(['uid' => getUid()])->getField('id');
            $model  =   D('ShopJoinBank');
            if (!$data = $model->create()) {
                $this->ajaxReturn(['code' => 0, 'msg' => $model->getError()]);
            }
            if ($id > 0) {
                $flag    =   $model->where(['uid' => getUid()])->save();
            } else {
                $flag    =   $model->add();
            }
            if ($flag) {
                session('opensNext', 3);
                $this->ajaxReturn(['code' => 1, 'msg' => '操作成功']);
            }
            $this->ajaxReturn(['code' => 0, 'msg' => '无任何修改']);
        }
    }
    
    /**
     * 已修复
     */
    public function clearErr() {
        if (IS_POST) {
            $model  =   M('shop_join_step');
            $model->startTrans();
            $sw1    =   $model->where(['id' => $this->_step['id'], 'uid' => getUid()])->save(['step' => 8, 'etime' => date('Y-m-d H:i:s')]);
            if (!$sw1) goto error;
            $sw2    =   M('shop_join_info')->where(['uid' => getUid()])->save(['status' => 0, 'etime' => date('Y-m-d H:i:s')]);
            if (!$sw2) goto error;
            $model->commit();
            $this->ajaxReturn(['code' => 1, 'msg' => '操作成功，请等待审核']);
            error:
                $model->rollback();
                $this->ajaxReturn(['code' => 0, 'msg' => '操作失败']);
            
        }
    }
    
    public function getShopType() {
        if (IS_POST) {
            $id = I('post.id', 0, 'int');
            if ($this->_account['type'] == 0) {
                $typeArr = [6];
            } else {
                $typeArr = [2,3,4,6];
            }
            if (!in_array($id, $typeArr)) return false;
            $data = M('shop_type')->where(['id' => $id])->getField('content');
            if ($data) {
                $this->ajaxReturn(['code' => 1, 'msg' => '获取成功', 'data' => html_entity_decode($data)]);
            }
            $this->ajaxReturn(['code' => 0, 'msg' => '获取失败']);
        }
    }
} 