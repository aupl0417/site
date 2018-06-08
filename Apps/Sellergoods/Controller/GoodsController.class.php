<?php
namespace Sellergoods\Controller;
use Common\Builder\Activity;
use Common\Form\FormGroup;
use Think\Exception;

class GoodsController extends ApiController {
    public function _initialize() {
        parent::_initialize();
    }
    
    public function index() {

        $status = I('get.status')?I('get.status'):1;
        if(I('get.is_best') == 1){
            $this->assign('tag_status',9);
        }else{
            $this->assign('tag_status',$status);
        }

		$data   = [
            'pagesize'          => 15,
            'q'                 =>I('get.q'),
            'status'            =>$status,
            's_price'           =>I('get.s_price'),
            'e_price'           =>I('get.e_price'),
			'category_id'       =>I('get.category_id'),
			'is_best'           =>I('get.is_best'),
            'shop_category_id'  =>I('get.shop_category_id'),
			'brand_id'          =>I('get.brand_id'),
            'openid'            =>session('user.openid'),
			'p'					=>I('get.p'),
            'score_type'        =>I('scoreType')
        ];
		
		$res = $this->doApi('/SellerGoodsManage/goods_all',$data,'p,pagesize,action,q,brand_id,category_id,shop_category_id,s_price,e_price,status,is_best,score_type',1);
		$this->assign('data_msg',$res['msg']);
		$this->assign('data',$res['data']);
		$do = M("express_tpl");
		$result = $do->where(['uid'=>session('user.id')])->getField("id,tpl_name");
		$this->assign('tpl',$result);
        $this->assign('scoreType', [1 => '金积分', 4 => '银积分',2=>'现金']);
		//dump($res['data']['list']);
        C('seo', ['title' => '第' . I('p', 1, 'int') . '页_商品管理']);
		$this->display();
    }
    //类目操作
    public function category_control(){
        $res = get_category(['table'=>'shop_goods_category','field'=>'id,sid,category_name','sql'=>'status=1 and uid='.session('user.id'),'level'=>2]);
        $this->assign('data',$res);
        $this->display();
    }
	//修改模板
	public function change_tpl(){
		$do = M("express_tpl");
		$result = $do->where(['uid'=>session('user.id')])->field("id,tpl_name")->select();
		$this->assign('data',$result);
		$this->display();
	}
	
	/**
     * 修改sku
     */
    public function sku() {
        $id =   I('get.id');
		$res = $this->doApi('/SellerGoodsManage/goods_sku',['openid' => session('user.openid'),'goods_id'=>$id],'',1);
		
		$this->assign('data',$res['data']);
		//exit();
        //$this->authApi('/SellerGoods/goods_sku', ['goods_id' => $id])->with();
        $this->display();
    }


    /**
     * 发布商品前先选择分类
     */
    public function chooseCategory() {
        $this->checkCreatePermissions();
        $this->display();
    }


    /**
     * 检测发布权限
     */
    private function checkCreatePermissions() {
        $map = [
            'uid' => session('user.id'),
        ];

        $bool = [];
        $bool['express']    = M('express_tpl')->where($map)->count();    //运费模板
        $bool['package']    = M('goods_package')->where($map)->count();    //包装模板
        $bool['protection'] = M('goods_protection')->where($map)->count();     //售后模板

        //取商品类目权限
        $cates = M('shop')->where(['id' => session('user.shop_id')])->field('category_id,category_second')->find();

        //dump(M('shop')->getLastSQL());
        if(!$cates['category_id'] || !$cates['category_second']){
            $this->display('error');
            exit;
        }

        $shopCategorys = get_category([
            'table'         => 'goods_category',
            'level'         => 3,
            'sql'           => 'status=1',
            'field'         => 'id,sid,status,category_name',
            'map'           => [['id' => ['in',$cates['category_id']]],['id' => ['in',$cates['category_second']]]],
        ]);

        $this->assign('shopCategorys',$shopCategorys);

        //检测包装、售后、快递模板
        $boolean = true;
        foreach ($bool as $v) {
            if ($v == 0) {
                $boolean = false;
                break;
            }
        }

        if($boolean === false){
            $this->assign('bool',$bool);
            $this->display('check');
            exit;
        }
    }


    /**
     * 发布商品
     */
    public function create() {
        $map = [
            'uid' => session('user.id'),
        ];
        $cateId = I('get.cateId', 0, 'int');
        if ($cateId == 0) $this->redirect('/goods/chooseCategory');

        //店铺ID
        $shop=M('shop')->cache(true)->where(['uid' => session('user.id')])->field('id,type_id')->find();
        $daigou = getSiteConfig('daigou');
        $daigou_shop = explode(',',$daigou['daigou_goods_id']);
        $isDaigou   = false;
        if(in_array($shop['id'],$daigou_shop)){
            $isDaigou = true;
        }

        $this->assign('category_id',$cateId);
        $this->checkCreatePermissions();
        $serviceDays = M('goods_category')->cache(true)->where(['id' => $cateId])->getField('cate_service_days');
        $category = nav_sort(['table' => 'goods_category','id' => I('get.category'),'key' => 'category_name','field' => 'id,sid,category_name']);
        $this->assign('category',$category);
        $attr = $this->goodsAttr($cateId);
        //dump($attr);

        $shopCate = M('shop_goods_category')->where(array_merge($map, ['status' => 1]))->field('id,sid,category_name')->select();

        $param = $this->goodsParams($cateId);

        //dump($param);
        $formConfig = [
            'action' => U('/goods/saveCreate'),
            'gourl'  => '"' . U('/goods') . '"',
        ];


        //判断商品是否属于购票
        if(check_ticket($cateId)) {
            $score_types = ['2'=>'现金'];
            $isGoupiao = true;
        }else{
            $score_types = [1 => '金积分',4 => '银积分'];
            $isGoupiao = false;
        }


        $form = FormGroup::getInstance($formConfig)
            ->hidden(['name' => 'category_id', 'value' => $cateId])
            ->text(['name' => 'goods_name', 'title' => '商品名称', 'require' => 1, 'validate' => ['required', 'rangelength' => '[2,80]', 'remote' => U('/goods/filter')]])
            ->textarea(['name' => 'sub_name', 'title' => '商品副标题', 'validate' => ['rangelength' => '[5,200]']])
            ->text(['name' => 'code', 'title' => '商品编号', 'validate' => ['rangelength' => '[1,10]']])
            ->number(['name' => 'service_days', 'title' => '售后天数', 'value' => $serviceDays, 'validate' => ['number', 'max' => 3650, 'min' => $serviceDays]])
            ->callback($isDaigou, ['callback' => 'number', 'title' => '代购手续费(百分比)', 'value' => $daigou['daigou_cost_ratio'] * 100, 'name' => 'daigou_ratio', 'tips' => '<span>代购手续费按 <span class="text_red"><strong>代购金额*'.$daigou['daigou_cost_ratio'].'</strong></span> 计算，最低<span class="text_red"><strong> '.$daigou['daigou_min_cost'].'</strong> </span>元，封顶最高 <span class="text_red"><strong>'.$daigou['daigou_max_cost'].'</strong> </span>元。</span>', 'require' => 1, 'validate' => ['required', 'number', 'digits', 'min' => 0.1, 'max' => 10]])
            ->singleImages(['name' => 'images', 'title' => '商品主图', 'require' => 1, 'validate' => ['required']])
            ->select(['name' => 'score_type', 'title' => '积分类型', 'require' => 1, 'validate' => ['required'], 'options' => $score_types])

            ->callback($isGoupiao, ['callback' => 'text', 'title' => '乐兑宝比例(%)', 'value' => 0, 'name' => 'score_ratio', 'require' => 1, 'validate' => ['required','rangelength' => '[1,3]']])

            //->text(['name' => 'score_ratio', 'title' => '乐兑宝比例(%)', 'validate' => ['required','rangelength' => '[1,3]']])
            ->radio(['name' => 'status', 'title' => '商品状态', 'options' => [1 => '上架状态', 2 => '下架状态'], 'require' => 1, 'validate' => ['required']])
            ->radio(['name' => 'is_best', 'title' => '推荐橱窗', 'options' => [1 => '推荐', 0 => '不推荐'], 'require' => 1, 'validate' => ['required']])
            ->group(['title' => '基本信息'])
            ->categoryCheckbox(['name' => 'shop_category_id', 'title' => '店铺分类', 'tips' => '可选多个店铺分类', 'options' => listToTree($shopCate), 'correspond' => ['id' => 'id', 'name' => 'category_name', 'child' => 'child']])
            ->group(['title' => '店铺分类'])
            //->select(['name' => 'score_ratio', 'title' => '积分比例', 'options' => M('goods_cfg')->cache(true)->where(['status' => 1])->getField('score_ratio,cfg_name', true), 'require' => 1, 'validate' => ['required']])

            //->select(['name' => 'brand_id', 'title' => '商品品牌', 'options' => M('brand')->where($map)->getField('id,b_name', true)])
            ->select(['name' => 'express_tpl_id', 'title' => '运费模板', 'options' => M('express_tpl')->where($map)->getField('id,tpl_name', true), 'require' => 1, 'validate' => ['required']])
            ->select(['name' => 'package_id', 'title' => '包装模板', 'options' => M('goods_package')->where($map)->getField('id,package_name', true), 'require' => 1, 'validate' => ['required']])
            ->select(['name' => 'protection_id', 'title' => '售后模板', 'options' => M('goods_protection')->where($map)->getField('id,protection_name', true), 'require' => 1, 'validate' => ['required']])
            ->checkbox(['name' => 'goods_committed', 'title' => '服务承诺', 'options' => $this->goodsCommitted()])
            ->group(['title' => '模板设置'])
            ->goodsAttr(['options' => $attr])
            ->group(['title' => '设置属性'])
            ->goodsParams($param)
            ->group(['title' => '商品参数'])
            ->goodsCollocation([])
            ->group(['title' => '搭配商品'])
            ->submit(['title' => '创建商品', 'style' => ' position: relative; right: -490px;'])
            ->ueditor(['name' => 'content', 'title' => '商品详情', 'require' => 1, 'validate' => ['required']])
            ->submit(['title' => '创建商品'])
            ->group(['title' => '商品详情'])
            ->create();
        $this->assign('form', $form);
        $this->display();
    }


    /**
     * 创建保存
     */
    public function saveCreate() {
        if (IS_POST) {
            C('TOKEN_ON', false);
            //店铺ID
            $datas = I('post.');
            $cacheName = md5($datas['__hash__']);
            if (S($cacheName)) {
                $this->ajaxReturn(['code' => 0, 'msg' => '请不要重复提交']);
            } else {
                S($cacheName, 1, 10);
            }
            $datas['score_ratio'] = sprintf('%.2f',($datas['score_ratio']/100));
            //writeLog($datas);
            //exit;
            try {
                $flag = filterString($datas['goods_name']);
                if ($flag !== true) throw new Exception('商品名称不可出现' . $flag . '等字词');

                if ($this->checkShopCate($datas['category_id']) == false) throw new Exception('当前类目下您无权限发布商品');

                $shop=M('shop')->where(['uid' => session('user.id')])->field('id,type_id')->find();
                $datas['shop_id']   = $shop['id'];
                if($shop['type_id']==1) $datas['is_self'] = 1;

                $datas['uptime']            = date('Y-m-d H:i:s');
                $datas['seller_id']         = session('user.id');
                $datas['score_ratio']       = 1;
                if (!empty($datas['shop_category_id'])) $datas['shop_category_id']  = implode(',',$datas['shop_category_id']);
                $daigou                     = getSiteConfig('daigou');
                $daigou_shop                = explode(',',$daigou['daigou_goods_id']);
                if(in_array($shop['id'],$daigou_shop)){
                    $datas['is_daigou'] = 1;
                }

                //检查商家推荐商品数量上限
                if($datas['is_best'] == 1){
                    $max_best=M('shop')->where(['uid' => session('user.id')])->getField('max_best');
                    $count=M('goods')->where(['seller_id' => session('user.id'),'status' => 1,'num' => ['gt',0],'is_best' =>1])->count();
                    if($count+1 > $max_best) throw new Exception('橱窗推荐已经超出上限');
                }

                //是否包邮
                $datas['free_express']  =M('express_tpl')->where(['uid'=>session('user.id'),'id'=>$datas['express_tpl_id']])->getField('is_free');
                $goodsContent       = ['content' => htmlentities(strip_tags(html_entity_decode($datas['content']),'<div><span><img><table><tr><td><thead><tbody><font><strong><br><b><hr>'))]; //商品详情
                $goodsAttrList      = $this->parseAttrs($datas['attrs'], $datas['values'], $datas['images']); //商品属性
                $goodsParam         = $this->parseParams($datas['param']); //商品参数

                //判断搭配是不是正确填写
                $collocations = '';
                if (!empty($datas['collocation'])) {
                    $collocations = $this->parseCollocation($datas['collocation']);
                    foreach ($collocations as $k => $v) {
                        foreach ($v as $key => $val) {
                            if ($key == 'name') {
                                if (empty($val)) {
                                    throw new Exception('组名不能为空');
                                }

                                if (strlen($val) > 20) {
                                    throw new Exception('组名长度不能大于10位');
                                }

                            }
                            if ($key == 'sort') {
                                if (!is_numeric($val)) {
                                    throw new Exception('组排序必须为数字类型');
                                }
                                if ($val > 100 || $val < 0) {
                                    throw new Exception('组排序不能大于100且不能小于0');
                                }
                            }
                            if ($key == 'goods' && empty($val)) unset($collocations[$k]);
                        }
                    }
                }
                $goodsCollocation   = ['collocations' => serialize($collocations)]; //商品搭配

                if ($goodsAttrList == false) throw new Exception('请至少选择一种属性');

                $do = D('GoodsCreateRelation');
                $data = $do->create($datas);
                unset($datas);
                if (!$data) {
                    throw new Exception($do->getError());
                }

                $data['price_max']  = $goodsAttrList['data']['price_max'];           //最高价
                $data['price']      = $goodsAttrList['data']['price'];           //最低价格
                $data['num']        = $goodsAttrList['data']['num'];           //总库存
                $data['seller_id']  = getUid();
                $data['shop_id']    = getShopId();
                $data['ip']         = get_client_ip();
                //服务承诺
                if (!empty($data['goods_committed'])) $data['goods_committed'] = join(',', $data['goods_committed']);
//                if ($goodsParam) $data['goods_param']               =   $goodsParam;
//                if ($goodsParam) $data['goods_content']             =   $goodsContent;
//                if ($goodsAttrList) $data['goods_attr_list']        =   $goodsAttrList;
//                if ($goodsCollocation) $data['goods_collocation']   =   $goodsCollocation;


                //商品参数
                if ($goodsParam) {
                    $goodsParamModel = D('GoodsParams');
                    foreach ($goodsParam as $v) {
                        if ($goodsParamModel->create($v) == false) throw new Exception($goodsParamModel->getError());
                    }
                    $data['goods_param'] = $goodsParam;
                }

                //商品介绍
                if ($goodsContent) {
                    $goodsContentModel = D('GoodsContent');
                    if ($goodsContentModel->create($goodsContent) == false) throw new Exception($goodsContentModel->getError());
                    $data['goods_content'] = $goodsContent;
                }

                /**
                 * 商品属性
                 */
                if ($goodsAttrList['attrs']) {
                    $goodsAttrListModel = D('GoodsAttrList');
                    foreach ($goodsAttrList['attrs'] as $k => $v) {
                        //if ($v['price_market'] <= $v['price']) throw new Exception('市场价不能小于销售价');
                        $goodsAttrList['attrs'][$k] = $goodsAttrListModel->create($v);
                        if ($goodsAttrList['attrs'][$k] == false) throw new Exception($goodsAttrListModel->getError());
                    }
                    $data['goods_attr_list'] = $goodsAttrList['attrs'];
                }

                /**
                 * 商品属性值
                 */
                if ($goodsAttrList['values']) {
                    $goodsAttrListValuesModel = D('GoodsAttrValue');
                    foreach ($goodsAttrList['values'] as $k => $v) {
                        $goodsAttrList['values'][$k] = $goodsAttrListValuesModel->create($v);
                        if ($goodsAttrList['values'][$k] == false) throw new Exception($goodsAttrListValuesModel->getError());
                    }
                    $data['goods_attr_value'] = $goodsAttrList['values'];
                }

                /**
                 * 商品搭配
                 */
                if ($goodsCollocation) {
                    $goodsCollocationModel = D('GoodsCollocation');
                    if ($goodsCollocationModel->create($goodsCollocation) == false) throw new Exception($goodsCollocationModel->getError());
                    $data['goods_collocation'] = $goodsCollocation;
                }


                //writeLog($data);
                if ($insrtId = $do->relation(true)->add($data) === false) throw new Exception('添加商品失败');
                //$do->commit();
                if(false===M('shop')->where(['id' => $data['id']])->setInc('goods_num')) throw new Exception('更新商家商品数量失败');

                goods_pr($insrtId); //更新商品PR
                shop_pr($data['shop_id']); //店铺PR
                //S($cacheName, null);
                $this->ajaxReturn(['code' => 1, 'msg' => '操作成功']);
            } catch (Exception $e) {
                S($cacheName, null);
                $this->ajaxReturn(['code' => 0, 'msg' => $e->getMessage()]);
            }

        }
    }


    /**
     * 修改商品
     */
    public function edit() {
        $id = I('get.id', 0, 'int');
        $cateId = I('cateId', 0, 'int');
        $map = [
            'uid' => session('user.id'),
        ];
        if ($id <= 0) $this->redirect('/goods');
        $goods = D('GoodsEditRelation')->relation(true)->where(['seller_id' => session('user.id'),'id' => $id])->find();
        if ($goods['category_id'] == 100845550) {   //如果商品分类为其他分类  则需要设置分类
            //$this->redirect('/goods/editCate', ['id' => $id]);
            //exit;
        }
        //店铺ID
        $shop       = M('shop')->cache(true)->where(['uid' => session('user.id')])->field('id,type_id')->find();
        $daigou     = getSiteConfig('daigou');
        $daigou_shop= explode(',',$daigou['daigou_goods_id']);
        $isDaigou   = false;
        if(in_array($shop['id'],$daigou_shop)){
            $isDaigou = true;
        }

        $this->checkCreatePermissions();

        $cateFlag = false;
        if ($cateId > 0) {
            /**
             * 修改的类目是否与之前的类目一致
             * 如果一致为false，因为下面很多地方无需改动
             * 如果不一致的话则设置true，因为下面很多地方需要改动
             */
            $cateFlag = $cateId == intval($goods['category_id']) ? false : true;
        }

        $attr           = $this->goodsAttr($cateId ? : $goods['category_id']);
        $param          = $this->goodsParams($cateId ? : $goods['category_id']);
        $shopCate       = M('shop_goods_category')->where(array_merge($map, ['status' => 1]))->field('id,sid,category_name')->select();
//        if ($goods['service_days'] > 0) {
//            $serviceDays = $goods['service_days'];
//        } else {
            $serviceDays    = M('goods_category')->cache(true)->where(['id' => $goods['category_id']])->getField('cate_service_days');
//        }

        $formConfig = [
            'action' => U('/goods/saveEdit'),
            'gourl'  => '"' . U('/goods') . '"',
        ];

        $goods['score_ratio'] = $goods['score_ratio']*100;

        //判断商品是否属于购票
        if(check_ticket($goods['category_id'])) {
            $score_types = ['2'=>'现金'];
            $isGoupiao = true;
        }else{
            $score_types = [1 => '金积分',4 => '银积分'];
            $isGoupiao = false;
        }

        $form = FormGroup::getInstance($formConfig)
            ->hidden(['name' => 'id', 'value' => $id])
            ->hidden(['name' => 'old_status', 'value' => $goods['status']])
            ->callback($cateFlag, ['callback' => 'hidden', 'name' => 'category_id', 'value' => $cateId])    //如果有做类目改动的话则传类目ID过去
            ->text(['name' => 'goods_name', 'value' => $goods['goods_name'], 'title' => '商品名称', 'require' => 1, 'validate' => ['required', 'rangelength' => '[2,80]', 'remote' => U('/goods/filter')]])
            ->textarea(['name' => 'sub_name', 'value' => $goods['sub_name'], 'title' => '商品副标题', 'validate' => ['rangelength' => '[5,200]']])
            ->text(['name' => 'code', 'value' => $goods['code'], 'title' => '商品编号', 'validate' => ['rangelength' => '[1,10]']])
            ->number(['name' => 'service_days', 'title' => '售后天数', 'value' => $goods['service_days'] ? : $serviceDays, 'validate' => ['number', 'max' => 3650, 'min' => $serviceDays]])
            ->callback($isDaigou, ['callback' => 'number', 'title' => '代购手续费(百分比)', 'value' => $goods['daigou_ratio'] * 100, 'name' => 'daigou_ratio', 'tips' => '<span>代购手续费按 <span class="text_red"><strong>代购金额*'.$goods['daigou_ratio'].'</strong></span> 计算，最低<span class="text_red"><strong> '.$daigou['daigou_min_cost'].'</strong> </span>元，封顶最高 <span class="text_red"><strong>'.$daigou['daigou_max_cost'].'</strong> </span>元。</span>', 'require' => 1, 'validate' => ['required', 'number', 'digits', 'min' => 0.1, 'max' => 10]])
            ->singleImages(['name' => 'images', 'value' => $goods['images'], 'title' => '商品主图', 'require' => 1, 'validate' => ['required']])
            ->select(['name' => 'score_type', 'title' => '积分类型', 'require' => 1, 'validate' => ['required'], 'options' => $score_types, 'value' => $goods['score_type']])
            ->callback($isGoupiao, ['callback' => 'text', 'title' => '乐兑宝比例(%)', 'value' => $goods['score_ratio'], 'name' => 'score_ratio', 'require' => 1, 'validate' => ['required','rangelength' => '[1,3]']])
            //->text(['name' => 'score_ratio', 'title' => '乐兑宝比例(%)','value'=>$goods['score_ratio'], 'validate' => ['requireed','rangelength' => '[1,3]']])
            ->radio(['name' => 'status', 'value' => $goods['status'], 'title' => '商品状态', 'options' => [1 => '上架状态', 2 => '下架状态'], 'require' => 1, 'validate' => ['required']])
            ->radio(['name' => 'is_best', 'value' => $goods['is_best'], 'title' => '推荐橱窗', 'options' => [1 => '推荐', 0 => '不推荐'], 'require' => 1, 'validate' => ['required']])
            ->group(['title' => '基本信息'])
            ->categoryCheckbox(['name' => 'shop_category_id', 'value' => $goods['shop_category_id'], 'title' => '店铺分类', 'tips' => '可选多个店铺分类', 'options' => listToTree($shopCate), 'correspond' => ['id' => 'id', 'name' => 'category_name', 'child' => 'child']])
            ->group(['title' => '店铺分类'])
            //->select(['name' => 'score_ratio', 'value' => $goods['score_ratio'], 'title' => '积分比例', 'options' => M('goods_cfg')->where(['status' => 1])->cache(true)->getField('score_ratio,cfg_name', true), 'require' => 1, 'validate' => ['required']])
            //->select(['name' => 'brand_id', 'value' => $goods['brand_id'], 'title' => '商品品牌', 'options' => M('brand')->where($map)->getField('id,b_name', true)])
            ->select(['name' => 'express_tpl_id', 'value' => $goods['express_tpl_id'], 'title' => '运费模板', 'options' => M('express_tpl')->where($map)->getField('id,tpl_name', true), 'require' => 1, 'validate' => ['required']])
            ->select(['name' => 'package_id', 'value' => $goods['package_id'], 'title' => '包装模板', 'options' => M('goods_package')->where($map)->getField('id,package_name', true), 'require' => 1, 'validate' => ['required']])
            ->select(['name' => 'protection_id', 'value' => $goods['protection_id'], 'title' => '售后模板', 'options' => M('goods_protection')->where($map)->getField('id,protection_name', true), 'require' => 1, 'validate' => ['required']])
            ->checkbox(['name' => 'goods_committed', 'title' => '服务承诺', 'options' => $this->goodsCommitted(), 'value' => $goods['goods_committed']])
            ->group(['title' => '模板设置'])
            ->goodsAttr(['options' => $attr, 'value' => $cateFlag ? [] : $goods['attr_list'], 'values' => $cateFlag ? [] : $goods['attr_value']])
            ->group(['title' => '设置属性'])
            ->goodsParams($param, $cateFlag ? [] : $goods['params'])
            ->group(['title' => '商品参数'])
            ->goodsCollocation(['value' => $goods['collocations']])
            ->group(['title' => '搭配商品'])
            ->submit(['title' => '修改商品', 'style' => ' position: relative; right: -490px;'])
            ->ueditor(['name' => 'content', 'value' => html_entity_decode($goods['content']), 'title' => '商品详情', 'require' => 1, 'validate' => ['required']])
            ->submit(['title' => '修改商品'])
            ->group(['title' => '商品详情'])
            ->create();
        //dump(json_encode($goods['attr_list']));
        $category_id=upsid(['table' => 'goods_category','id' => $cateId ? : $goods['category_id']]);
        $category['second'] =M('goods_category')->cache(true)->where(['status' => 1,'sid' => $category_id[0]])->order('sort asc')->select();
        $category['three']  =M('goods_category')->cache(true)->where(['status' => 1,'sid' => $category_id[1]])->order('sort asc')->select();
        $category['title']  =nav_sort(['table' => 'goods_category','field' => 'id,sid,category_name','id' => $cateId ? : $goods['category_id'],'key' => 'category_name']);
        //去掉第一级分类的名称
        $tmp_category_title = explode('<i class="fa fa-angle-right"></i>',$category['title']);
        unset($tmp_category_title[0]);
        $category['title']=implode('<i class="fa fa-angle-right"></i>',$tmp_category_title);

        $this->assign('category',$category);
        $this->assign('attrs', json_encode($goods['attr_list']));
        $this->assign('form', $form);
        $this->display();
    }


    /**
     * 解析搭配商品数据
     *
     * @param $datas
     * @return array|bool
     */
    private function parseCollocation($datas) {
        //$datas = [['name' => '', 'sort' => '', 'goods' => '']['name' => '', 'sort' => '', 'goods' => '']];
        //$datas = ['name' => ['组1','组2','组3'], 'sort' => [0,1,2], 'goods' => ['1,2,3', '4,5,6', '7,8,9']];
        if (!empty($datas) && is_array($datas)) {
            $tmp = [];
            foreach ($datas as $k => $v) {
                foreach ($v as $key => $val) {
                    $tmp[$key][$k] = $val;
                }
            }
            return $tmp;
        }
        return false;
    }

    /**
     * 解析商品参数
     *
     * @param $datas
     * @return array|bool
     */
    private function parseParams($datas) {
        //$datas = [['3025' => 'xxx'], ['3028' => [1,2]]];
        //$datas = [['option_id' => '3025', 'param_value' => 'xxx'], ['option_id' => 3028, 'param_value' => '1,2']];
        //'param' =>
//        array (
//            3253 => '640G',
//            3256 => '无外接电源',
//            3257 =>
//                array (
//                    0 => '金属',
//                ),
        if (!empty($datas) && is_array($datas)) {
            $tmp = [];
            $i = 0;
            foreach ($datas as $k => $v) {
                //foreach ($v as $key => $val) {
                $ids = explode('_', $k);
                if ($ids[1]) $tmp[$i]['id']          = $ids[1];
                $tmp[$i]['option_id']   = $ids[0];
                $tmp[$i]['param_value'] = is_array($v) ? join(',', $v) : $v;
                //}
                $i++;
            }
            unset($ids, $datas);
            return $tmp;
        }
        return false;
    }

    /**
     * 解析商品属性数据
     *
     * @param $datas
     * @param $values
     * @param $images 商品图片，若是没有属性图片，则把主图设为属性图片
     * @return array|bool
     */
    public function parseAttrs($datas, $values, $images) {
        //$datas = [ 'attr_sku_attr_id' => [1,2,3], 'attr_sku_attr' => [4,5,6], 'attr_sku_id' => [7,8,9]];
        //$datas = [['attr_sku_attr_id' => 1, 'attr_sku_attr' => 4, 'attr_sku_id' => 7]];
        if (!empty($datas) && is_array($datas)) {
            $tmp = [];
            $i=0;
            foreach ($values['attr_id'] as $k => $v) {
                $attrImages = !empty($values['images'][$k]) ? $values['images'][$k] : $images;
                $tmpId = explode(':', $v);
                $tmp['values'][$i]['id']            = $values['id'][$k];
                $tmp['values'][$i]['attr_id']       = $tmpId[0];
                $tmp['values'][$i]['option_id']     = $tmpId[1];
                $tmp['values'][$i]['attr_value']    = $values['attr_value'][$k];
                $tmp['values'][$i]['attr_album']    = $attrImages;
                $tmp['values'][$i]['attr_images']   = substr($attrImages, 0, strpos($attrImages, ',')) ? : $attrImages;
                $tmp['data']['ids']                .= $values['id'][$k] . ',';  //取出ID
                ++$i;
            }
            unset($k,$v,$tmpId,$i);
            $tmp['data']['num']         = array_sum($datas['num']);
            $tmp['data']['price']       = min($datas['price']);
            $tmp['data']['price_max']   = max($datas['price']);
            foreach ($datas as $k => $v) {
                foreach ($v as $key => $val) {
                    $tmp['attrs'][$key][$k] = $val;
                    foreach ($tmp['values'] as $value) {
                        if (strpos($datas['attr'][$key], $value['option_id']) !== false) {
                            $tmp['attrs'][$key]['images'] = $value['attr_images'];
                        }
                    }
                }
            }
            unset($datas,$values,$k,$key,$v,$val,$value);
            return $tmp;
        }
        return false;
    }

    public function saveEdit() {
        if (IS_POST) {
            C('TOKEN_ON', false);
            $id = I('post.id', 0, 'int'); //商品ID
            if ($id < 0) $this->ajaxReturn(['code' => 0, 'msg' => '非法操作']);
            $datas = I('post.');    //post数据
            //$datas['score_ratio'] = 1;
            //$do->startTrans();  //开启事物
            $datas['score_ratio'] = sprintf('%.2f',($datas['score_ratio']/100));
            try {

                $flag = filterString($datas['goods_name']);
                if ($flag !== true) throw new Exception('商品名称不可出现' . $flag . '等字词');

                if (M('goods')->where(['id' => $id,'officialactivity_join_id' => ['gt',0]])->field('officialactivity_join_id')->find()) {
                    throw new Exception('活动商品不充许编辑！');
                }

                $map = [
                    'uid' => session('user.id'),
                ];

                //可去掉
                $shop = M('shop')->where($map)->field('id,type_id')->find();
                //$_POST['shop_id']=$shop['id'];
                if($shop['type_id']==1) $datas['is_self'] = 1;

                //是否包邮
                $datas['free_express']  = M('express_tpl')->where(['uid'=>session('user.id'),'id'=>$datas['express_tpl_id']])->getField('is_free');
                if ($datas['free_express'] == 1) {
                    if (false == Activity::isExpressFree($datas['id'], getShopId())) {
                        throw new Exception('参与促销活动的商品不能包邮！');
                    }
                }

                //检查商家推荐商品数量上限
                if($datas['is_best'] == 1){
                    $max_best   = M('shop')->where($map)->getField('max_best');
                    $count      = M('goods')->where(['seller_id' => session('user.id'),'status' => 1,'num' => ['gt',0],'is_best' =>1,'id'=>['neq',$datas['id']]])->count();
                    if($count+1 > $max_best) throw new Exception('橱窗推荐已经超出上限！');
                }

                $datas['uptime']            = date('Y-m-d H:i:s');
                if (!empty($datas['shop_category_id'])) $datas['shop_category_id'] = join(',', $datas['shop_category_id']);
                //代购商店修改商品
                $daigou = getSiteConfig('daigou');
                $daigou_shop = explode(',',$daigou['daigou_goods_id']);
                if(in_array($shop['id'],$daigou_shop)){
                    $datas['is_daigou'] = 1;
                }


                $goodsContent       = ['content' => htmlentities(strip_tags(html_entity_decode($datas['content']),'<div><span><img><table><tr><td><thead><tbody><font><strong><br><b><hr>'))]; //商品详情
                $goodsAttrList      = $this->parseAttrs($datas['attrs'], $datas['values'], $datas['images']); //商品属性/值
                $goodsParam         = $this->parseParams($datas['param']); //商品参数
                //writeLog($goodsAttrList);
                //throw new Exception(111);
                //判断搭配是不是正确填写
                $collocations = '';
                if (!empty($datas['collocation'])) {
                    $collocations = $this->parseCollocation($datas['collocation']);
                    foreach ($collocations as $k => $v) {
                        foreach ($v as $key => $val) {
                            if ($key == 'name') {
                                if (empty($val)) {
                                    throw new Exception('组名不能为空');
                                }

                                if (strlen($val) > 20) {
                                    throw new Exception('组名长度不能大于10位');
                                }

                            }
                            if ($key == 'sort') {
                                if (!is_numeric($val)) {
                                    throw new Exception('组排序必须为数字类型');
                                }
                                if ($val > 100 || $val < 0) {
                                    throw new Exception('组排序不能大于100且不能小于0');
                                }
                            }
                            if ($key == 'goods' && empty($val)) unset($collocations[$k]);
                        }
                    }
                }
                $goodsCollocation   = ['collocations' => serialize($collocations)]; //商品搭配
                if ($goodsAttrList == false) throw new Exception('请至少选择一种属性');
                if (!in_array($datas['old_status'], [1,2,3])) {
                    unset($datas['status']);
                    $checkMap = [
                        'uid'       =>  getUid(),
                        'shop_id'   =>  getShopId(),
                        'goods_id'  =>  $datas['id'],
                    ];
                    if (M('goods_illegl')->where($checkMap)->save(['status' => 2]) === false) throw new Exception('修改违规状态失败！');
                }
                $do = D('GoodsCreateRelation');
                $do->startTrans();
                $editCateFlag = false;  //是否有编辑类目
                if ($datas['category_id']) {    //如果有改类目
                    $category_id = M('goods')->where(['id' => $id, 'uid' => session('user.id')])->getField('category_id');
                    //判断是否有认证当前类目
                    if ($this->checkShopCate($datas['category_id']) == false) throw new Exception('当前类目下您无权限发布商品');
                    if ($category_id != $datas['category_id']) {    //如果当前修改类目与之前类目ID不同则删除之前旧的参数及属性
                        if (false === M('goods_attr_value')->where(['goods_id' => $id])->delete()) {
                            $do->rollback();
                            throw new Exception('删除商品属性值失败');
                        }
                        if (false === M('goods_attr_list')->where(['goods_id' => $id])->delete()) {
                            $do->rollback();
                            throw new Exception('删除商品属性失败');
                        }
                        if (false === M('goods_param')->where(['goods_id' => $id])->delete()) {
                            $do->rollback();
                            throw new Exception('删除商品参数失败');
                        }
                        $editCateFlag = true;
                    }
                }
                $data = $do->create($datas);
                unset($datas);
                if (!$data) {
                    throw new Exception($do->getError());
                }

                $data['price_max']  = $goodsAttrList['data']['price_max'];           //最高价
                $data['price']      = $goodsAttrList['data']['price'];           //最低价格
                $data['num']        = $goodsAttrList['data']['num'];           //总库存
                //服务承诺
                if (!empty($data['goods_committed'])) $data['goods_committed'] = join(',', $data['goods_committed']);

                //商品参数
                if ($goodsParam) {
                    $goodsParamModel = D('GoodsParams');
                    foreach ($goodsParam as $k => $v) {
                        $goodsParam[$k] = $goodsParamModel->create($v);
                        if ($goodsParam[$k] == false) throw new Exception($goodsParamModel->getError());
                    }
                    $data['goods_param'] = $goodsParam;
                }

                //商品介绍
                if ($goodsContent) {
                    $goodsContentModel = D('GoodsContent');
                    if (($goodsContent = $goodsContentModel->create($goodsContent)) == false) throw new Exception($goodsContentModel->getError());
                    $data['goods_content'] = $goodsContent;
                }

                /**
                 * 商品属性
                 */
                if ($goodsAttrList['attrs']) {
                    $goodsAttrListModel = D('GoodsAttrList');
                    $goodsAttrListIds   = '';
                    foreach ($goodsAttrList['attrs'] as $k => $v) {
                        if ($v['id']) {
                            $goodsAttrListIds .= $v['id'] . ',';
                        } else {
                            unset($v['id']);
                        }
                        if ($editCateFlag) unset($v['id']); //如果有编辑类目则删除属性ID
                        //if ($v['price_market'] <= $v['price']) throw new Exception('市场价不能小于销售价');
                        $goodsAttrList['attrs'][$k] = $goodsAttrListModel->create($v);
                        if ($goodsAttrList['attrs'][$k] == false) throw new Exception($goodsAttrListModel->getError());
                    }
                    $data['goods_attr_list'] = $goodsAttrList['attrs'];
                    if ($goodsAttrListIds) {    //如果不为空则删除未编辑的
                        $falg = M('goods_attr_list')->where(['seller_id' => session('user.id'), 'goods_id' => $id, 'id' => ['not in', rtrim($goodsAttrListIds, ',')]])->delete();
                        if ($falg === false) throw new Exception('删除属性失败');
                    }
                }


                /**
                 * 商品属性值
                 */
                if ($goodsAttrList['values']) {
                    $goodsAttrListValuesModel = D('GoodsAttrValue');
                    $goodsAttrListValuesIds   = '';
                    foreach ($goodsAttrList['values'] as $k => $v) {
                        if ($v['id']) {
                            $goodsAttrListValuesIds .= $v['id'] . ',';
                        } else {
                            unset($v['id']);
                        }
                        $goodsAttrList['values'][$k] = $goodsAttrListValuesModel->create($v);
                        if ($goodsAttrList['values'][$k] == false) throw new Exception($goodsAttrListValuesModel->getError());
                    }
                    $data['goods_attr_value'] = $goodsAttrList['values'];
                    if ($goodsAttrListValuesIds) {    //如果不为空则删除未编辑的
                        $falg = M('goods_attr_value')->where(['goods_id' => $id, 'id' => ['not in', rtrim($goodsAttrListValuesIds, ',')]])->delete();
                        if ($falg === false) throw new Exception('删除属性值失败');
                    }
                }



                /**
                 * 商品搭配
                 */
                if ($goodsCollocation) {
                    $goodsCollocationModel = D('GoodsCollocation');
                    if (($goodsCollocation = $goodsCollocationModel->create($goodsCollocation)) == false) throw new Exception($goodsCollocationModel->getError());
                    $data['goods_collocation'] = $goodsCollocation;
                }
                //writeLog($data);
                //$do->rollback();
                //throw new Exception(222);
                if ($do->relation(true)->save($data) === false) throw new Exception('保存失败');
                $do->commit();
                $this->ajaxReturn(['code' => 1, 'msg' => '操作成功']);
            } catch (Exception $e) {
                //$do->rollback();
                $this->ajaxReturn(['code' => 0, 'msg' => $e->getMessage()]);
            }
        }
    }


    /**
     * 保存编辑商品
     */
    public function saveEdit1() {
        if (IS_POST) {
            $do=D('Admin/Goods86');
            $do->startTrans();
            try {
                //判断是否有参与官方活动
                $rs = M('goods')->where(['id' => I('post.id'),'officialactivity_join_id' => ['gt',0]])->field('officialactivity_join_id')->find();
                if ($rs) new Exception('活动商品不充许编辑！');

                if(!in_array(I('post.status_old'), [1,2,3])) unset($_POST['status']);


                $map = [
                    'uid' => session('user.id'),
                ];

                //可去掉
                $shop=M('shop')->where($map)->field('id,type_id')->find();
                //$_POST['shop_id']=$shop['id'];
                if($shop['type_id']==1) $_POST['is_self'] = 1;


                //是否包邮
                $_POST['free_express']  =M('express_tpl')->where(['uid'=>session('user.id'),'id'=>I('post.express_tpl_id')])->getField('is_free');
                if ($_POST['free_express'] == 1) {
                    if (false == Activity::isExpressFree(I('post.id'), getShopId())) {
                        new Exception('参与促销活动的商品不能包邮！');
                    }
                }

                //检查商家推荐商品数量上限
                if($_POST['is_best'] == 1){
                    $max_best=M('shop')->where(['uid' => session('user.id')])->getField('max_best');
                    $count=M('goods')->where(['seller_id' => session('user.id'),'status' => 1,'num' => ['gt',0],'is_best' =>1,'id'=>['neq',I('post.id')]])->count();
                    if($count+1 > $max_best) new Exception('橱窗推荐已经超出上限！');
                }


                $_POST['num']        = $attr['num'];
                $_POST['price_max']  = $attr['price']['max'];
                $_POST['price']      = $attr['price']['min'];
                $_POST['shop_category_id']  = implode(',',I('post.shop_category_id'));

                //代购商店修改商品
                $daigou = getSiteConfig('daigou');
                $daigou_shop = explode(',',$daigou['daigou_goods_id']);
                if(in_array($shop['id'],$daigou_shop)){
                    $_POST['is_daigou'] = 1;
                }

                if(!$do->create()) {
                    new Exception($do->getError());
                }
                if(false===$do->save()) new Exception('商品更新失败');

                if(false===M('goods_content')->where(['goods_id' => I('post.id')])->save(['content' => I('post.content')])) new Exception('商品介绍更新失败');

                /**
                 *------------------------
                 * 商品库存
                 *-----------------------
                 */
                //商品属性值
                //var_dump($attr);
                $attr_value_id=array();
                $goodsAttrModel = D('Admin/Goodsattrvalue96');
                foreach($attr['attr'] as $val){
                    if(!$goodsAttrModel->create($val)){
                        new Exception($goodsAttrModel->getError());
                    }

                    if($val['id']){
                        $attr_value_id[]=$val['id'];
                        if(false===$goodsAttrModel->save()) new Exception('商品属性更新失败');
                    }else{
                        if(!$goodsAttrModel->add()) new Exception('商品属性添加失败');
                        $attr_value_id[]=$goodsAttrModel->getLastInsID();
                    }

                    //echo D('Goodsattrvalue96')->getLastSQL().'<br>';
                }

                if(!empty($attr_value_id) && false===M('goods_attr_value')->where(array('goods_id'=>I('post.id'),'id'=>array('not in',$attr_value_id)))->delete()) new Exception('删除旧属性失败');


                //库存
                $goodsAttrListModel = D('Admin/Goodsattrlist97');
                $attr_list_id=array();
                foreach($attr['attr_list'] as $val){
                    if(!$goodsAttrListModel->create($val)){
                        new Exception($goodsAttrListModel->getError());
                    }
                    if($val['id']){
                        $attr_list_id[]=$val['id'];
                        if(false===$goodsAttrListModel->save()) new Exception('修改库存失败');
                    }else{
                        if(!$goodsAttrListModel->add()) new Exception('添加库存失败');
                        $attr_list_id[]=$goodsAttrListModel->getLastInsID();
                    }

                    //echo D('Goodsattrlist97')->getLastSQL().'<br>';
                }
                //清除不相关的库存记录
                if(!empty($attr_list_id) && false===M('goods_attr_list')->where(array('goods_id'=>I('post.id'),'id'=>array('not in',$attr_list_id)))->delete()) new Exception('删除旧库存失败');


                //商品参数
                $goodsParamModel = M('goods_param');
                if(!empty($goods_param)){
                    $param_item=$goodsParamModel->where(array('goods_id'=>I('post.id'),'option_id'=>array('in',$goods_param['key'])))->getField('option_id',true);
                    foreach($goods_param['data'] as $val){
                        if(in_array($val['option_id'],$param_item)){
                            if(false===$goodsParamModel->where(array('goods_id'=>I('post.id'),'option_id'=>$val['option_id']))->save($val)) new Exception('保存商家商品数量失败');
                        }else{
                            $val['goods_id']=I('post.id');
                            if(!$goodsParamModel->add($val)) new Exception('添加商品参数失败');
                        }
                    }

                    if(false===$goodsParamModel->where(array('goods_id'=>I('post.id'),'option_id'=>array('not in',$goods_param['key'])))->delete()) new Exception('删除商品参数失败');
                }else{
                    if(false===$goodsParamModel->where('goods_id='.I('post.id'))->delete()) new Exception('删除商品参数失败');
                }

                if(I('post.status_old')==1 && I('post.status')!=1){
                    if(false===M('shop')->where(['id' => $shop['id']])->setDec('goods_num')) new Exception('修改商家商品数量失败');
                }


                # 是否是违规的状态
                if(I('post.status_old') == 4){
                    # 设置为审核
                    $this->doApi('/SellerGoods/goods_illegl_add',['id'=>I('post.illegl_id'), 'openid' => session('user.openid')]);
                    # M('goods_illegl')->where(['uid'=>session('user.id'),'status'=>1,'id'=>I('post.illegl_id')])->save(['status'=>2,'dotime'=>date('Y-m-d H:i:s')]);
                }

                $do->commit();

                goods_pr(I('post.id')); //更新商品PR
                shop_pr(I('post.shop_id')); //店铺PR
                $this->ajaxReturn(['code' => 1,'msg' => '修改成功！']);

            } catch (Exception $e) {
                $do->rollback();
                $this->ajaxReturn(['code' => 0, 'msg' => $e->getMessage()]);
            }
        }
    }

    /**
     * 商品导入
     */
    public function import() {
        $this->display();
    }

    /**
     * 导入保存
     */
    public function saveImport() {

    }

    //获取天猫宝贝
    public function getTmallItem(){

        set_time_limit(30);

        //查询店铺ID
        $userInfo = M('user')->field('id')->where('openid = "'.session('user')['openid'].'"')->find();
        $shopInfo = M('shop')->field('id')->where('uid = "'.$userInfo['id'].'"')->find();
        $uidMap = [
            'uid'   =>  getUid(),
        ];
        if (M('goods_protection')->where($uidMap)->find() == false) $this->ajaxReturn(['code' => 0, 'msg' => '请先添加售后模板']);//售后
        if (M('goods_package')->where($uidMap)->find() == false) $this->ajaxReturn(['code' => 0, 'msg' => '请先添加包装模板']);//包装
        if (M('express_tpl')->where($uidMap)->find() == false) $this->ajaxReturn(['code' => 0, 'msg' => '请先添加运费模板']);//运费
        //抓取
        $html=$this->get_oburl(I('post.url'));
        $html=mb_convert_encoding($html,'utf8','gbk');
        preg_match("/<img id=\"J_ImgBooth\" alt=\"([\s\S]*?)\" src=\"([\s\S]*?)\"/ies",$html,$out2);
        $images=MidStr($html,'<ul id="J_UlThumb" class="tb-thumb tm-clear">','</ul>');
        preg_match_all("/<img src=\"([\s\S]*?)\"/ies",$images,$img);
        $sku=MidStr($html,'<div class="tb-sku">','</div>');
        $color=MidStr($sku,'<ul data-property="颜色分类"','</ul>');
        preg_match_all("/<span>([\s\S]*?)<\/span>/ies",$color,$cl);
        $size=MidStr($sku,'<ul data-property="尺码"','</ul>');
        preg_match_all("/<span>([\s\S]*?)<\/span>/ies",$size,$sl);

        $images=array();
        if($img[1]){
            foreach($img[1] as $pic){
                $images[]='http:'.str_replace('_60x60q90.jpg','',$pic);
            }
        }else{
            $images[]='http:'.str_replace('_430x430q90.jpg','',$out2[2]);
        }
        $html=MidStr($html,'TShop.Setup(',');');
        $html=json_decode($html,true);

        $data['goods_name']=$html['itemDO']['title'];
        if(empty($images[0])){
            $this->ajaxReturn(array('status'=>'warning','msg'=>'导入失败，没有获取到商品图片！'));
        }
        $data['images']=$images[0];
        $data['images_album']='return '.var_export($images,true).';';

        $desc=curl_file(array('url'=>'http:'.$html['api']['descUrl']));
        $desc=trim(mb_convert_encoding($desc,'utf8','gbk'));
        $data2['content']=substr($desc,10,-2);
        $data2['content']=strip_tags($data2['content'],'<div><span><img><table><tr><td><thead><tbody><font><strong><br><b><hr>');




        $data['atime']=date('Y-m-d H:i:s',time());
        $data['etime']=date('Y-m-d H:i:s',time());
        $data['ip']=get_client_ip();
        $data['status']=2;
        $data['category_id']=100845550;
        $data['shop_category_id']=0;
        $data['brand_id']=0;
        $data['subtitle']='';
        $data['shop_id']=$shopInfo['id'];
        $data['price'] = I('post.price');
        $data['price_max']=I('post.price');
        $data['num']=10;
        $data['seller_id']=$userInfo['id'];
        $data['is_collection']=2;

        $data['express_tpl_id']=M('express_tpl')->where(['uid' => session('user.id')])->getField('id');



        $do=M();
        $do->startTrans();
        if(!$insid=M('goods')->add($data)) goto error;

        if(!M('goods_content')->add(['goods_id'=>$insid,'content'=>$data2['content']])) goto error;

        if(!M('goods_attr_value')->add([
            'attr_value'    =>'默认',
            'goods_id'      =>$insid,
            'attr_id'       =>12378,
            'option_id'     =>12379
        ])) goto error;

        if(!M('goods_attr_list')->add([
            'seller_id'		=>$userInfo['id'],
            'attr'          =>'12378:12379:默认',
            'attr_id'       =>'12378:12379',
            'attr_name'     =>'默认',
            'goods_id'      =>$insid,
            'images'        =>$data['images'],
            'price'         =>$data['price'],
            'num'           =>10,
        ])) goto error;

        /**
         * 搭配
         */
        if (!M('goods_collocation')->add([
            'goods_id' => $insid,
        ])) goto error;

        if(!M('goods_param')->add(['param_value'=>'other','goods_id'=>$insid,'option_id'=>2862])) goto error;

        $do->commit();

        goods_pr($insid); //更新商品PR

        $this->ajaxReturn(array('status'=>'success','msg'=>'导入成功！','url'=>$insid));
        error:
        $do->rollback();
        $this->ajaxReturn(array('status'=>'warning','msg'=>'导入失败！'));

    }


    //获取淘宝宝贝
    public function getTaoBaoItem(){

        set_time_limit(30);
        $url=I('post.url');

        //查询店铺ID
        $userInfo = M('user')->field('id')->where('openid = "'.session('user')['openid'].'"')->find();
        $shopInfo = M('shop')->field('id')->where('uid = "'.$userInfo['id'].'"')->find();
        $uidMap = [
            'uid'   =>  getUid(),
        ];
        if (M('goods_protection')->where($uidMap)->find() == false) $this->ajaxReturn(['code' => 0, 'msg' => '请先添加售后模板']);//售后
        if (M('goods_package')->where($uidMap)->find() == false) $this->ajaxReturn(['code' => 0, 'msg' => '请先添加包装模板']);//包装
        if (M('express_tpl')->where($uidMap)->find() == false) $this->ajaxReturn(['code' => 0, 'msg' => '请先添加运费模板']);//运费

        ob_start();
        readfile($url);
        $htmls=ob_get_contents();
        $htmls=mb_convert_encoding($htmls,'utf8','gbk');
        ob_clean();
        //获取宝贝名称
        $html=MidStr($htmls,'var g_config =',';');
        preg_match("/<h3([\s\S]*?)>([\s\S]*?)<\/h3>/ies",$htmls,$title);
        $data['goods_name']=trim($title[2]);
        //获取相册
        $pic=json_decode(trim(MidStr($html,'auctionImages    :','},')));
        foreach($pic as $key=>$val){
            $pic[$key]='http:'.$val;
        }
        if(empty($pic[0])){
            $this->ajaxReturn(array('status'=>'warning','msg'=>'导入失败，没有获取到商品图片！'));
        }
        $data['images']=$pic[0];
        $data['images_album']='return '.var_export($pic,true).';';
        //获取详情
        $desc_url='http:'.MidStr($html,"descUrl          : location.protocol==='http:' ? '","'");
        ob_start();
        readfile($desc_url);
        $desc=ob_get_contents();
        $desc=trim(mb_convert_encoding($desc,'utf8','gbk'));
        ob_clean();
        $data2['content']=substr($desc,10,-2);



        $data['atime']=date('Y-m-d H:i:s',time());
        $data['etime']=date('Y-m-d H:i:s',time());
        $data['ip']=get_client_ip();
        $data['status']=2;
        $data['category_id']=100845550;
        $data['shop_category_id']=0;
        $data['brand_id']=0;
        $data['subtitle']='';
        $data['shop_id']=$shopInfo['id'];
        $data['price'] = I('post.price');
        $data['price_max']=I('post.price');
        $data['num']=10;
        $data['seller_id']=$userInfo['id'];
        $data['is_collection']=1;

        $data['express_tpl_id']=M('express_tpl')->where(['uid' => session('user.id')])->getField('id');

        $do=M();
        $do->startTrans();
        if(!$insid=M('goods')->add($data)) goto error;

        if(!M('goods_content')->add(['goods_id'=>$insid,'content'=>$data2['content']])) goto error;

        if(!M('goods_attr_value')->add([
            'attr_value'    =>'默认',
            'goods_id'      =>$insid,
            'attr_id'       =>12378,
            'option_id'     =>12379
        ])) goto error;

        if(!M('goods_attr_list')->add([
            'seller_id'		=>$userInfo['id'],
            'attr'          =>'12378:12379:默认',
            'attr_id'       =>'12378:12379',
            'attr_name'     =>'默认',
            'goods_id'      =>$insid,
            'images'        =>$data['images'],
            'price'         =>$data['price'],
            'num'           =>10,
        ])) goto error;

        /**
         * 搭配
         */
        if (!M('goods_collocation')->add([
            'goods_id' => $insid,
        ])) goto error;

        if(!M('goods_param')->add(['param_value'=>'other','goods_id'=>$insid,'option_id'=>2862])) goto error;

        $do->commit();

        goods_pr($insid); //更新商品PR

        $this->ajaxReturn(array('status'=>'success','msg'=>'导入成功！','url'=>$insid));
        error:
        $do->rollback();
        $this->ajaxReturn(array('status'=>'warning','msg'=>'导入失败！'));
    }

    //利用缓存取数据，不使用CURL，因为CURL可能会因为DNS关系而获取不到数据
    public function get_oburl($url){
        /*
    	ob_start();
    	readfile($url);
    	$html=ob_get_contents();
    	ob_clean();
        */
        $html=curl_file($url);
        return $html;
    }


    /**
     * 为商品修改分类
     */
    public function editCate() {
        $id = I('get.id', 0, 'int');
        if ($id > 0) {
            //'category_id' => 100845550,
            $goods = M('goods')->where(['id' => $id, 'seller_id' => getUid()])->find();
            if ($goods) {
                $this->checkCreatePermissions();
                $this->assign('goods', $goods);
                $this->display();
            } else {
                $this->redirect('/goods/edit', ['id' => $id]);
            }
        } else {
            $this->redirect('/goods');
        }
    }

    /**
     * 保存分类
     */
    public function saveCate() {
        if (IS_POST) {
            $this->ajaxReturn(['code'=> 0, 'msg' => '非法操作']);   //当前方法不可用
            $category_id = I('post.cateId', 0, 'int');
            $id = I('post.id', 0, 'int');
            if ($id == 0 || $category_id == 0) {
                $this->ajaxReturn(['code' => 0, 'msg' => '非法操作']);
            }
            $map = [
                'seller_id' => getUid(),
                'id'        => $id,
                //'category_id' => 100845550,
            ];
            try {
                $model = M('goods');
                $model->startTrans();
                if (false === $model->where($map)->save(['category_id' => $category_id])) throw new Exception('修改分类失败');
                if (false === M('goods_attr_value')->where(['goods_id' => $id])->delete()) throw new Exception('删除商品属性值失败');
                if (false === M('goods_attr_list')->where(['goods_id' => $id])->delete()) throw new Exception('删除商品属性失败');
                if (false === M('goods_param')->where(['goods_id' => $id])->delete()) throw new Exception('删除商品参数失败');
                $model->commit();
                $this->ajaxReturn(['code' => 1, 'msg' => '修改分类成功']);
            } catch (Exception $e) {
                $model->rollback();
                $this->ajaxReturn(['code' => 0, 'msg' => $e->getMessage()]);
            }
        }
    }


    /**
     * 根据类目取属性
     * @param int    $_POST['cid']   类目ID
     */
    protected function goodsAttr($cid){
        $do=M('goods_attr');
        $list=$do->where(array('status'=>1,'category_id'=>$cid,'sid'=>0))->field('id,attr_name')->order('sort asc')->select();


        if(empty($list)){
            $rs=M('goods_category')->where(['id' => $cid])->field('id,sid')->find();
            //dump($rs);
            if($rs['sid']>0) $list=$this->goodsAttr($rs['sid']);
            else return false;
        }


        //$list=$do->where(array('status'=>1,'category_id'=>I('get.cid'),'sid'=>0))->field('id,attr_name')->order('sort asc')->select();

        foreach($list as $key=>$val){
            $list[$key]['attr_options']=$do->where(array('status'=>1,'sid'=>$val['id']))->order('sort asc')->getField('id,attr_name',true);
            $list[$key]['count']=count($list[$key]['attr_options']);
        }

        return $list;
    }


    /**
     * 获取商品参数
     *
     * @return bool  1单选，2多选，3文本
     */
    protected function goodsParams($cid) {
        $do=D('Admin/GoodsParamOptionRelation');
        $list=$do->relation(true)->where(array('status'=>1,'category_id'=>$cid))->field('id,group_name')->select();
        if(empty($list)){
            $cid=M('goods_category')->cache(true)->where(['id' => $cid])->getField('sid');
            if($cid > 0) {
                $list   = $this->goodsParams($cid);
                return $list;
            } else {
                return false;
            }
        }
        if ($list) {
            $tmp = [];
            foreach ($list as $k => $v) {
                foreach ($v['param_option'] as $key => $val) {
                    array_push($tmp, $val);
                }
            }
        }
        unset($k,$key,$v,$val,$list);
        return $tmp;
    }


    /**
     * 选择商品
     */
    public function choose() {
        $data   = [
            'pagesize'          => 10,
            'q'                 =>I('get.q'),
            'code'              =>I('get.code'),
            'category_id'       =>I('get.category_id'),
            'shop_category_id'  =>I('get.shop_category_id'),
            's_price'           =>I('get.s_price'),
            'e_price'           =>I('get.e_price'),
            's_sale'            =>I('get.s_sale'),
            'e_sale'            =>I('get.e_sale'),
            'is_best'           =>I('get.is_best'),
            'openid'            =>session('user.openid'),
            'p'                 =>I('get.p'),
        ];
        if (isset($_GET['id']) && !empty(I('get.id'))) {
            $chooseIds =   M('activity')->where(['id' => I('get.id'), 'shop_id' => $this->_map['id']])->getField(I('get.field'));
            $this->assign('chooseIds', $chooseIds);
        }

        $res = $this->doApi('/SellerGoods/goods_online',$data,'p,pagesize,action,q,code,category_id,shop_category_id,s_price,e_price,s_sale,e_sale,is_best');
        //dump(json_decode(json_encode($res), true));
        $this->assign('data', json_decode(json_encode($res->data), true));
        $this->display();
    }

    /**
     * subject: 获取服务承诺
     * api: goodsCommitted
     * author: Mercury
     * day: 2017-03-31 15:42
     * [字段名,类型,是否必传,说明]
     * @return mixed
     */
    protected function goodsCommitted() {
        $do = M('goods_committed');
        return $do->where(['status' => 1])->cache(true)->order('sort asc, id asc')->getField('id,name');
    }

    /**
     * subject: 判断卖家是否有当前类目的权限
     * api: checkShopCate
     * author: Mercury
     * day: 2017-05-08 17:14
     * [字段名,类型,是否必传,说明]
     * @param $cateId
     * @return bool
     */
    protected function checkShopCate($cateId)
    {
        $cateId= M('goods_category')->cache(true)->where(['id' => $cateId])->getField('sid');
        $cates = M('shop')->where(['id' => getShopId()])->cache(true)->getField('category_second');
        $cates = str_replace(',', '|', $cates);
        preg_match("/$cates/", $cateId, $return);
        if (empty($return[0])) return false;
        return true;
    }

    /**
     * subject: 商品名称检测
     * api: filter
     * author: Mercury
     * day: 2017-04-17 12:00
     * [字段名,类型,是否必传,说明]
     */
    public function filter()
    {
        $flag = filterString(I('get.goods_name'));
        if ($flag === true)
            echo 'true';
        else
            echo 'false';
    }
}