<?php
/**
+--------------------------------
+ 商家-商品管理
+-------------------------------
*/
namespace Seller\Controller;
use Common\Builder\Activity;
use Common\Form\FormGroup;

class GoodsController extends AuthController {
    protected $first_category;  //一级类目
    protected $second_category; //二级类目
    public function _initialize() {
        parent::_initialize();
    }
    
    /**
     * 商品管理
     */
    public function index() {
        $this->authApi('/SellerGoods/category')->with('shop_cate');
        $data   = [
            'pagesize'          => 15,
            'q'                 =>I('get.q'),
            'code'              =>I('get.code'),
            'category_id'       =>I('get.category_id'),
            'shop_category_id'  =>I('get.shop_category_id'),
            's_price'           =>I('get.s_price'),
            'e_price'           =>I('get.e_price'),
            's_sale'            =>I('get.s_sale'),
            'e_sale'            =>I('get.e_sale'),
            'is_best'           =>I('get.is_best')
        ];
        $this->authApi('/SellerGoods/goods_online',$data,'p,pagesize,action,q,code,category_id,shop_category_id,s_price,e_price,s_sale,e_sale,is_best')->with();
        C('seo', ['title' => '第' . I('p', 1, 'int') . '页_商品管理']);
        $this->display();
    }


    public function group() {
        $form = FormGroup::getInstance()
            ->text(['name' => 'name', 'title' => '第一个', 'validate' => ['required']])
            ->text(['name' => 'name1', 'title' => '第二个', 'validate' => ['required']])
            ->group(['title' => '基本信息'])
            ->text(['name' => 'name2', 'title' => '第三个', 'validate' => ['required']])
            ->text(['name' => 'name3', 'title' => '第四个', 'validate' => ['required']])
            ->submit(['title' => '创建商品'])
            ->group(['title' => '设置属性'])
            ->create();
        $this->assign('form', $form);
        $this->display();
    }


	/**
     * 商品管理（库存为0）
     */
    public function warehouse() {
        $this->authApi('/SellerGoods/category')->with('shop_cate');
        $data   = [
            'pagesize'          => 15,
            'q'                 =>I('get.q'),
            'code'              =>I('get.code'),
            'category_id'       =>I('get.category_id'),
            'shop_category_id'  =>I('get.shop_category_id'),
            's_price'           =>I('get.s_price'),
            'e_price'           =>I('get.e_price'),
            's_sale'            =>I('get.s_sale'),
            'e_sale'            =>I('get.e_sale'),
            'is_best'           =>I('get.is_best'),
            'num'          		=>0
        ];
        $this->authApi('/SellerGoods/goods_online',$data,'p,pagesize,action,q,code,category_id,shop_category_id,s_price,e_price,s_sale,e_sale,is_best,num')->with();
        C('seo', ['title' => '第' . I('p', 1, 'int') . '页_商品管理']);
        $this->display();
    }
    
    /**
     * 待上架
     */
    public function noshelves() {
        $this->authApi('/SellerGoods/category')->with('shop_cate');
        $data   = [
            'pagesize'          => 15,
            'q'                 =>I('get.q'),
			'code'              =>I('get.code'),
            'category_id'       =>I('get.category_id'),
            'shop_category_id'  =>I('get.shop_category_id'),
            's_price'           =>I('get.s_price'),
            'e_price'           =>I('get.e_price'),
            's_sale'            =>I('get.s_sale'),
            'e_sale'            =>I('get.e_sale'),
            'is_best'           =>I('get.is_best')
        ];
        $this->authApi('/SellerGoods/goods_offline', $data,'p,pagesize,action,q,code,category_id,shop_category_id,s_price,e_price,s_sale,e_sale,is_best')->with();
        //dump($this->_data);
        C('seo', ['title' => '第' . I('p', 1, 'int') . '页_待上架商品']);
        $this->display();
    }
    
    /**
     * 橱窗商品
     */
    public function recommend() {
        $this->authApi('/SellerGoods/category')->with('shop_cate');
        $data   = [
            'pagesize'          => 15,
            'q'                 =>I('get.q'),
            'code'              =>I('get.code'),
            'category_id'       =>I('get.category_id'),
            'shop_category_id'  =>I('get.shop_category_id'),
            's_price'           =>I('get.s_price'),
            'e_price'           =>I('get.e_price'),
            's_sale'            =>I('get.s_sale'),
            'e_sale'            =>I('get.e_sale'),
            'is_best'           =>I('get.is_best')
        ];
        $this->authApi('/SellerGoods/recommend',$data,'p,pagesize,action,q,code,category_id,shop_category_id,s_price,e_price,s_sale,e_sale,is_best')->with();
        C('seo', ['title' => '第' . I('p', 1, 'int') . '页_橱窗商品']);
        $this->display();
    }
    
    /**
     * 添加商品第一步
     */
    public function addStep1() {
        $this->authApi('/SellerGoods/first_category')->with('cate'); //店铺绑定的分类
        C('seo', ['title' => '发布商品']);
        $this->display();
    }
    
    /**
     * 添加商品第二步
     */
    public function add() {
        $this->check_goods_permissions();
        C('seo', ['title' => '发布商品']);
        $this->display();
    }
	
	/**
     *选择分类 
     */
    public function change_category() {
		$this->check_goods_permissions();
        $this->display();
    }
	
    /**
     * 添加商品第二步
     */
    public function add_next() {
        $category_id=I('get.category');
		
		//店铺ID
        $shop=M('shop')->where(['uid' => session('user.id')])->field('id,type_id')->find();
		$daigou = getSiteConfig('daigou');
		$daigou_shop = explode(',',$daigou['daigou_goods_id']);
		if(in_array($shop['id'],$daigou_shop)){
			$this->assign('daigou',1);
			$this->assign('daigou_cost_ratio',$daigou["daigou_cost_ratio"]);
			$this->assign('daigou_min_cost',$daigou['daigou_min_cost']);
			$this->assign('daigou_max_cost',$daigou['daigou_max_cost']);
		}
		
        $this->assign('category_id',$category_id);
        $this->check_goods_permissions();
		$serviceDays = M('goods_category')->cache(true)->where(['id' => $category_id])->getField('cate_service_days');
		$this->assign('serviceDays', $serviceDays);
        $category = nav_sort(['table' => 'goods_category','id' => I('get.category'),'key' => 'category_name','field' => 'id,sid,category_name']);
        $this->assign('category',$category);
        C('seo', ['title' => '发布商品']);
		$this->display();
    }
    /**
    * 保存商品
    */
    public function add_save(){
        C('TOKEN_ON',false);
        //商品参数
        $goods_param=$this->_param_post();
        $attr=$this->_attr_post(I('post.id'));
        //商品库存

        //店铺ID
        $shop=M('shop')->where(['uid' => session('user.id')])->field('id,type_id')->find();
        $_POST['shop_id']=$shop['id'];
        if($shop['type_id']==1) $_POST['is_self']=1;

        $_POST['uptime']    = date('Y-m-d H:i:s');
        $_POST['seller_id'] = session('user.id');
        $_POST['shop_category_id']  = implode(',',I('post.shop_category_id'));
		$daigou = getSiteConfig('daigou');
		$daigou_shop = explode(',',$daigou['daigou_goods_id']);
		if(in_array($shop['id'],$daigou_shop)){
			$_POST['is_daigou'] = 1;
		}
        
		//检查商家推荐商品数量上限
		if($_POST['is_best'] == 1){
			$max_best=M('shop')->where(['uid' => session('user.id')])->getField('max_best');
			$count=M('goods')->where(['seller_id' => session('user.id'),'status' => 1,'num' => ['gt',0],'is_best' =>1])->count();
			if($count+1 > $max_best) $this->ajaxReturn(['code' => 4, 'msg' => '橱窗推荐已经超出上限！']);
		}
		
        //是否包邮
        $_POST['free_express']  =M('express_tpl')->where(['uid'=>session('user.id'),'id'=>I('post.express_tpl_id')])->getField('is_free');

        $do=M();
        $do->startTrans();

        $do=D('Admin/Goods86');
        if(!$do->create()) {
            $msg=$do->getError();
            goto error;
        }
        if(!$do->add()) goto error;
        $insid=$do->getLastInsID();



        $attr=$this->_attr_add_goods_id($attr,$insid);

        //商品属性值
        //var_dump($attr);
        $attr_value_id=array();
        foreach($attr['attr'] as $val){
            if(!D('Admin/Goodsattrvalue96')->create($val)){
                $msg=D('Admin/Goodsattrvalue96')->getError();
                goto error;
            }
            if(!D('Admin/Goodsattrvalue96')->add()) goto error;
        }
        
        //库存
        $attr_list_id=array();
        foreach($attr['attr_list'] as $val){
            if(!D('Admin/Goodsattrlist97')->create($val)){
                $msg=D('Admin/Goodsattrlist97')->getError();
                goto error;
            }
            if(!D('Admin/Goodsattrlist97')->add()) goto error;
        }

        if(!M('goods')->where('id='.$insid)->save(array('price'=>$attr['price']['min'],'price_max'=>$attr['price']['max'],'num'=>$attr['num']))) goto error;

        //商品参数
        if(!empty($goods_param)){
            $goods_param=$this->_param_item_add($goods_param['data'],array('goods_id'=>$insid));
            if(!M('goods_param')->addAll($goods_param)) goto error;
        }

        //商品详情
        $do=D('Admin/Goodscontent90');
        $data=array();
        $data['goods_id']=$insid;
        $data['content']=I('post.content');

        if(!$do->create($data)){
            $msg=$do->getError();
            goto error;
        }

        if(!$do->add()) goto error;

        if(false===M('shop')->where(['id' => $shop['id']])->setInc('goods_num')) goto error;

        $do->commit();

        goods_pr($insid); //更新商品PR
        shop_pr(I('post.shop_id')); //店铺PR

        $this->ajaxReturn(['code' => 1,'msg' => '发布成功！','id' => $insid]);

        error:
            $do->rollback();
            $this->ajaxReturn(['code' => 0,'msg' => '发布失败！'.$msg]);

    }    

    /**
    * 宝贝修改
    */
    public function edit(){
        $this->check_goods_permissions();


        $this->authApi('/SellerGoods/first_category')->with('cate'); //店铺绑定的分类
        C('seo', ['title' => '第' . I('p', 1, 'int') . '页_修改商品']);        

        $rs=M('goods')->where(['seller_id' => session('user.id'),'id' => I('get.id')])->find();

		$daigou = getSiteConfig('daigou');
		$daigou_shop = explode(',',$daigou['daigou_goods_id']);
		if(in_array($rs['shop_id'],$daigou_shop)){
			$this->assign('daigou',1);
			$this->assign('daigou_min_cost',$daigou['daigou_min_cost']);
			$this->assign('daigou_max_cost',$daigou['daigou_max_cost']);
		}
        
        $rs['content']=M('goods_content')->where(['goods_id' => I('get.id')])->getField('content');
		if($rs['daigou_ratio']<=0){
			$rs['daigou_ratio'] = $daigou["daigou_cost_ratio"];
		}

        $category_id=upsid(['table' => 'goods_category','id' => $rs['category_id']]);
        $this->assign('category_id',$category_id);
        
        $category['second'] =M('goods_category')->cache(true)->where(['status' => 1,'sid' => $category_id[0]])->order('sort asc')->select();
        $category['three']  =M('goods_category')->cache(true)->where(['status' => 1,'sid' => $category_id[1]])->order('sort asc')->select();
        $category['title']  =nav_sort(['table' => 'goods_category','field' => 'id,sid,category_name','id' => $rs['category_id'],'key' => 'category_name']);
		//去掉第一级分类的名称
		$tmp_category_title = explode('<i class="fa fa-angle-right"></i>',$category['title']);
		unset($tmp_category_title[0]);
		$category['title']=implode('<i class="fa fa-angle-right"></i>',$tmp_category_title);
		//售后天数
		$serviceDays = M('goods_category')->where(['id' => $category_id[2]])->getField('cate_service_days');
		$this->assign('serviceDays', $serviceDays);
		
        $this->assign('category',$category);

        $this->assign('rs',$rs);
        $this->display();
    }

    public function edit_save(){
        C('TOKEN_ON',false);

        //商品是否参与了官方活动
        $rs = M('goods')->where(['id' => I('post.id'),'officialactivity_join_id' => ['gt',0]])->field('officialactivity_join_id')->find();
        if($rs) $this->ajaxReturn(['code' => 0,'msg' =>'活动商品不充许编辑！']);

        //商品参数
        $goods_param=$this->_param_post();

        //商品库存
        $attr=$this->_attr_post(I('post.id'));
        $attr=$this->_attr_add_goods_id($attr,I('post.id'));

        if(!in_array(I('post.status_old'), [1,2,3])) unset($_POST['status']);

        //可去掉
        $shop=M('shop')->where(['uid' => session('user.id')])->field('id,type_id')->find();
        //$_POST['shop_id']=$shop['id'];
        if($shop['type_id']==1) $_POST['is_self']=1;       

        //是否包邮
        $_POST['free_express']  =M('express_tpl')->where(['uid'=>session('user.id'),'id'=>I('post.express_tpl_id')])->getField('is_free');
        if ($_POST['free_express'] == 1) {
            if (false == Activity::isExpressFree(I('post.id'), getShopId())) {
                $this->ajaxReturn(['code' => 0, 'msg' => '参与促销活动的商品不能包邮！']);
            }
        }

		//检查商家推荐商品数量上限
		if($_POST['is_best'] == 1){
			$max_best=M('shop')->where(['uid' => session('user.id')])->getField('max_best');
			$count=M('goods')->where(['seller_id' => session('user.id'),'status' => 1,'num' => ['gt',0],'is_best' =>1,'id'=>['neq',I('post.id')]])->count();
			if($count+1 > $max_best) $this->ajaxReturn(['code' => 4, 'msg' => '橱窗推荐已经超出上限！']);
		}
		
		
        //$data=I('post.');
        $_POST['num']        =$attr['num'];
        $_POST['price_max']  =$attr['price']['max'];
        $_POST['price']      =$attr['price']['min'];
        $_POST['shop_category_id']  = implode(',',I('post.shop_category_id'));
		
		//代购商店修改商品
		$daigou = getSiteConfig('daigou');
		$daigou_shop = explode(',',$daigou['daigou_goods_id']);
		if(in_array($shop['id'],$daigou_shop)){
			$_POST['is_daigou'] = 1;
		}
        //$data['content']    =array('content'=>I('post.content'));
        //$data['package']  =array('content'=>I('post.package'));
        //$data['protection']   =array('content'=>I('post.protection'));
        $do=M();
        $do->startTrans();
        $do=D('Admin/Goods86');
        if(!$do->create()) {
            $msg=$do->getError();
            goto error;
        }
        if(false===$do->save()) goto error;

        if(false===M('goods_content')->where(['goods_id' => I('post.id')])->save(['content' => I('post.content')])) goto error;
        
        /**
        *------------------------
        * 商品库存
        *-----------------------
        */
        //商品属性值
        //var_dump($attr);
        $attr_value_id=array();
        foreach($attr['attr'] as $val){
            if(!D('Admin/Goodsattrvalue96')->create($val)){
                $msg=D('Admin/Goodsattrvalue96')->getError();
                goto error;
            }

            if($val['id']){
                $attr_value_id[]=$val['id'];
                if(false===D('Admin/Goodsattrvalue96')->save()) goto error;
            }else{
                if(!D('Admin/Goodsattrvalue96')->add()) goto error; 
                $attr_value_id[]=D('Admin/Goodsattrvalue96')->getLastInsID();
            }

            //echo D('Goodsattrvalue96')->getLastSQL().'<br>';
        }
        //print_r(I('post.'));
        //清除不相关的旧属性值
        //print_r($attr_value_id);
        if(!empty($attr_value_id) && false===M('goods_attr_value')->where(array('goods_id'=>I('post.id'),'id'=>array('not in',$attr_value_id)))->delete()) goto error;

        //库存
        $attr_list_id=array();
        foreach($attr['attr_list'] as $val){
            if(!D('Admin/Goodsattrlist97')->create($val)){
                $msg=D('Admin/Goodsattrlist97')->getError();
                goto error;
            }
            if($val['id']){
                $attr_list_id[]=$val['id'];
                if(false===D('Admin/Goodsattrlist97')->save()) goto error;
            }else{
                if(!D('Admin/Goodsattrlist97')->add()) goto error;
                $attr_list_id[]=D('Admin/Goodsattrlist97')->getLastInsID();
            }

            //echo D('Goodsattrlist97')->getLastSQL().'<br>';
        }
        //清除不相关的库存记录
        if(!empty($attr_list_id) && false===M('goods_attr_list')->where(array('goods_id'=>I('post.id'),'id'=>array('not in',$attr_list_id)))->delete()) goto error;


        //商品参数
        if(!empty($goods_param)){
            $param_item=M('goods_param')->where(array('goods_id'=>I('post.id'),'option_id'=>array('in',$goods_param['key'])))->getField('option_id',true);
            foreach($goods_param['data'] as $val){
                if(in_array($val['option_id'],$param_item)){
                    if(false===M('goods_param')->where(array('goods_id'=>I('post.id'),'option_id'=>$val['option_id']))->save($val)) goto error;
                }else{
                    $val['goods_id']=I('post.id');
                    if(!M('goods_param')->add($val)) goto error;
                }
            }

            if(false===M('goods_param')->where(array('goods_id'=>I('post.id'),'option_id'=>array('not in',$goods_param['key'])))->delete()) goto error;
        }else{
            if(false===M('goods_param')->where('goods_id='.I('post.id'))->delete()) goto error;
        }    

        if(I('post.status_old')==1 && I('post.status')!=1){
            if(false===M('shop')->where(['id' => $shop['id']])->setDec('goods_num')) goto error;
        }
        


        # 是否是违规的状态
        if(I('post.status_old') == 4){
            # 设置为审核
            $this->authApi('/SellerGoods/goods_illegl_add',['id'=>I('post.illegl_id')]);
            # M('goods_illegl')->where(['uid'=>session('user.id'),'status'=>1,'id'=>I('post.illegl_id')])->save(['status'=>2,'dotime'=>date('Y-m-d H:i:s')]);
        }

        $do->commit();

        goods_pr(I('post.id')); //更新商品PR
        shop_pr(I('post.shop_id')); //店铺PR
        $this->ajaxReturn(['code' => 1,'msg' => '修改成功！']);

        error:
            $do->rollback();
            $this->ajaxReturn(['code' => 0,'msg' => '修改失败！'.$msg]);

    }
    
    /**
     * 主图异常的商品
     */
    public function nopic() {
        $this->authApi('/SellerGoods/goods_bad_images')->with();
        C('seo', ['title' => '第' . I('p', 1, 'int') . '页_主图异常商品']);
        $this->display();
    }
    
    /**
     * 违规的商品
     */
    public function violation() {
        $this->authApi('/SellerGoods/goods_illegl', ['pagesize' => 10])->with();
        //dump($this->_data['data']['list']);
        C('seo', ['title' => '第' . I('p', 1, 'int') . '页_违规商品']);
        $this->display();
    }
    
    /**
     * 修改分类
     */
    public function changeCate() {
        $this->display();
    }
    
    /**
     * 修改sku
     */
    public function sku() {
        $id =   I('get.id');
        $this->authApi('/SellerGoods/goods_sku', ['goods_id' => $id])->with();
        $this->display();
    }

    /**
    * 商品类目子类
    */
    public function sub_category(){
        $this->api('/Goods/sub_category', ['sid' => I('get.sid')],'sid');
        $this->ajaxReturn($this->_data);
    }

    /**
    * 添加商品库存
    */
    public function attr_set(){
        if(I('get.goods_id')){
            $attr_value_tmp=M('goods_attr_value')->where('goods_id='.I('get.goods_id'))->field('id,attr_id,option_id,attr_value,attr_album,concat(attr_id,":",option_id) as attr')->select();
            foreach($attr_value_tmp as $val){
                $attr_value['attr'][]=$val['attr'];
                $attr_value[$val['option_id']]['attr_value']=$val['attr_value'];
                $attr_value[$val['attr']]=$val['id'];
                
                if($val['attr_album']) $attr_value[$val['option_id']]['attr_album']=@explode(',',$val['attr_album']);
            }
            $this->assign('attr_value',$attr_value);
        }

        //dump($attr_value);

        $do=M('goods_attr');
        //$list=$do->where(array('status'=>1,'category_id'=>I('get.cid'),'sid'=>0))->field('id,attr_name')->order('sort asc')->select();
        $list=$this->get_goods_attr(I('get.cid'));

        foreach($list as $key=>$val){
            $list[$key]['attr_options']=$do->where(array('status'=>1,'sid'=>$val['id']))->order('sort asc')->getField('id,attr_name',true);
            $list[$key]['count']=count($list[$key]['attr_options']);
        }



        $this->assign('list',$list);
        $this->display();
    }


    /**
    ** 设置商品参数
    */
    public function param_set(){
        //$do=D('Admin/GoodsParamOptionRelation');
        //$list=$do->relation(true)->where(array('status'=>1,'category_id'=>I('get.cid')))->field('id,group_name')->select();
        $list=$this->get_goods_param(I('get.cid'));

        if($list && I('get.goods_id')){
            $param_item=M('goods_param')->where('goods_id='.I('get.goods_id'))->getField('option_id,param_value',true);
            foreach($param_item as $key=>$val){
                $param_value['param_'.$key]=$val;
            }
            $this->assign('param_value',$param_value);
        }

        //dump($param_value);

        $this->assign('list',$list);
        $this->assign('count',count($list));
        $this->display();
    }

    /**
    * 创建商品库存表单
    * 多种属性组合，相当于多维数组
    */
    public function attr_create_form(){
        //var_dump(I('post.'));exit;
        if(I('get.goods_id')){
            $list=M('goods_attr_list')->where('goods_id='.I('get.goods_id'))->getField('attr_id,weight,id,price,price_market,price_purchase,num,code,barcode',true);
        }
        $data=array();
        foreach(I('post.data') as $key=>$val){
            $v=explode(':',$val);
            $data[$v[0]][]=$val;
        }

        $ndata=array();
        $n=0;
        foreach($data as $val){
            $ndata[$n]=$val;
            $n++;
        }

        //分解多维数组组合成一维数组
        $attr_list=$ndata[0];
        $n=0;
        while ($n<count($ndata)-1) {
            $n++;
            $attr_list=$this->array_2($attr_list,$ndata[$n],'<-->');

        }

        //dump($attr_list);

        //格式化数据
        foreach($attr_list as $key=>$val){
            $val=explode('<-->',$val);
            //foreach($val as $vkey=>$v){
                //$val[$vkey]=explode(':',$v);
            //}

            $tmp=array();
            foreach($val as $v){
                $tmp['attr'][]=$v;
                $v=explode(':',$v);
                $tmp['attr_name'][]=$v[2];
                array_pop($v);
                $tmp['attr_id'][]=implode(':',$v);
            }
            $attr_list[$key]=array('attr'=>@implode(',',$tmp['attr']),'attr_name'=>@implode(',', $tmp['attr_name']),'attr_id'=>@implode(',', $tmp['attr_id']));
            //var_dump(implode(',',$tmp['attr_value_id']));

            if($list[$attr_list[$key]['attr_id']]){
                //dump($list[$attr_list[$key]['attr_id']]);
                $attr_list[$key]=array_merge($attr_list[$key],$list[$attr_list[$key]['attr_id']]);
            }

        }

        if(!empty($attr_list)){
            $this->assign('attr_set_list',$attr_list);
            $result['html']=$this->fetch('attr_set_list');
            $result['code']=1;
        }else{
            $result['code']=0;
        }

        //dump($attr_list);
        $this->ajaxReturn($result);

    }    
	
	/**
     * 商品导入
     */
    public function import() {
        $this->check_goods_permissions();

		//if(IS_POST){
			//echo 1;
			//print_r(C('cfg'));
			/*
			$url = '/Shop/get_list';
			$data['appid'] = C('cfg')['api']['appid'];
			$data['access_key'] = C('cfg')['api']['access_key'];
			$data['secret_key'] = C('cfg')['api']['secret_key'];
			$data['sign_code'] = C('cfg')['api']['sign_code'];
			$data['type'] = 1;
			$data['name'] = '555';
			$data['sign'] = _sign($data);
			$res = $this->curl($url,$data);
			print_r($this->_data);
			*/
		//}else{
            C('seo', ['title' => '商品导入']);
			$this->display();
		//}
    }
    
    /**
     * 商品上架时间管理
     */
    public function uptime() {
        $do = M('goods_uptime');
        $goods = M('goods');
        
        if ($_POST){
            $num = 0;
            if (I('post.is_best0')==""){
                $ref[0] = 0;
            }else{
                $ref[0] = I('post.is_best0');
                $num++;
            }
            if (I('post.is_best1')==""){
                $ref[1] = 0;
            }else{
                $ref[1] = I('post.is_best1');
                $num++;
            }
            if (I('post.is_best2')==""){
                $ref[2] = 0;
            }else{
                $ref[2] = I('post.is_best2');
                $num++;
            }
            if (I('post.is_best3')==""){
                $ref[3] = 0;
            }else{
                $ref[3] = I('post.is_best3');
                $num++;
            }
            if (I('post.is_best4')==""){
                $ref[4] = 0;
            }else{
                $ref[4] = I('post.is_best4');
                $num++;
            }
            if (I('post.is_best5')==""){
                $ref[5] = 0;
            }else{
                $ref[5] = I('post.is_best5');
                $num++;
            }
            if (I('post.is_best6')==""){
                $ref[6] = 0;
            }else{
                $ref[6] = I('post.is_best6');
                $num++;
            }
            if (I('post.is_best7')==""){
                $ref[7] = 0;
            }else{
                $ref[7] = I('post.is_best7');
                $num++;
            }
            if (I('post.is_best8')==""){
                $ref[8] = 0;
            }else{
                $ref[8] = I('post.is_best8');
                $num++;
            }
            if (I('post.is_best9')==""){
                $ref[9] = 0;
            }else{
                $ref[9] = I('post.is_best9');
                $num++;
            }
            if (I('post.is_best10')==""){
                $ref[10] = 0;
            }else{
                $ref[10] = I('post.is_best10');
                $num++;
            }
            if (I('post.is_best11')==""){
                $ref[11] = 0;
            }else{
                $ref[11] = I('post.is_best11');
                $num++;
            }
            if (I('post.is_best12')==""){
                $ref[12] = 0;
            }else{
                $ref[12] = I('post.is_best12');
                $num++;
            }
            if (I('post.is_best13')==""){
                $ref[13] = 0;
            }else{
                $ref[13] = I('post.is_best13');
                $num++;
            }
            if (I('post.is_best14')==""){
                $ref[14] = 0;
            }else{
                $ref[14] = I('post.is_best14');
                $num++;
            }
            if (I('post.is_best15')==""){
                $ref[15] = 0;
            }else{
                $ref[15] = I('post.is_best15');
                $num++;
            }
            if (I('post.is_best16')==""){
                $ref[16] = 0;
            }else{
                $ref[16] = I('post.is_best16');
                $num++;
            }
            if (I('post.is_best17')==""){
                $ref[17] = 0;
            }else{
                $ref[17] = I('post.is_best17');
                $num++;
            }
            if (I('post.is_best18')==""){
                $ref[18] = 0;
            }else{
                $ref[18] = I('post.is_best18');
                $num++;
            }
            if (I('post.is_best19')==""){
                $ref[19] = 0;
            }else{
                $ref[19] = I('post.is_best19');
                $num++;
            }
            if (I('post.is_best20')==""){
                $ref[20] = 0;
            }else{
                $ref[20] = I('post.is_best20');
                $num++;
            }
            if (I('post.is_best21')==""){
                $ref[21] = 0;
            }else{
                $ref[21] = I('post.is_best21');
                $num++;
            }
            if (I('post.is_best22')==""){
                $ref[22] = 0;
            }else{
                $ref[22] = I('post.is_best22');
                $num++;
            }
            if (I('post.is_best23')==""){
                $ref[23] = 0;
            }else{
                $ref[23] = I('post.is_best23');
                $num++;
            }
			$avg = ceil(100/$num);
			$times = array();
			$total = 0;
			$avg_total = 0;
			foreach($ref as $key=>$val){
				if($val){
					$total += $avg;
					if($total<=100){
						$times[] = [$val,$avg];
					}else{
						$times[] = [$val,100-$avg_total];
					}
					$avg_total += $avg;
				}
			}
			//商品数量
			$goods_num = M('goods')->where(['shop_id' => session('user.shop_id'),'status' => 1])->count();
			//ID
			$ids = M('goods')->where(['shop_id' => session('user.shop_id'),'status' => 1])->getField('id',true);

			//dump($goods_num);
			//$day = date('Y-m-d',time() - 86400);
			$n=0;
			foreach($times as $k => $v){
				$num = ceil(($v[1] / 100) * $goods_num);
				$n +=$num;
				if($n > $goods_num || $num == 0) break;
				//dump($num);
				$ids_day = array_slice($ids,$k * $num,$num);
				//dump(implode(',',$ids_day));

				//7天同个时间段均匀分布
				$num7 = ceil($num / 7);
				for($i=1;$i<8;$i++){
					if($num7 * $i > $num) {
						$tmp = $num - ($num7 * ($i-1));
						if($tmp < 1) break;

						$ids_time = array_slice($ids_day,($i-1) * $num7,$tmp);
						$num7 = $tmp;
					}else{
						$ids_time = array_slice($ids_day,($i-1) * $num7,$num7);
					}

					//dump(implode(',',$ids_time));

					//1小时中均匀分布
					$sec = intval(3600 / $num7);    //每隔$sec秒上架一款商品
					//dump($sec);
					foreach($ids_time as $key => $vl){
						$day = strtotime(date('Y-m-d',time() - (86400 * $i)) . ' '.$v[0]);
						$day = date('Y-m-d H:i:s',$day + $key * $sec);
						//dump($day);
						M('goods')->where(['id' => $vl])->save(['uptime' => $day]);
						usleep(rand(5,20));
					}

				}

				//echo '<br>-------------------------------<br>';
			}
            //更新 goods_uptime表
            $data['uptime_cfg'] = serialize($ref);
            $data['shop_id'] = $this->shop_info['id'];
            $data['uid'] =  $this->shop_info['uid'];
            $data['ip'] = get_client_ip() ;
            
            $d = $do->data($data)->add();
            if ($d){
                $this->ajaxReturn(array('code'=>'1','msg'=>'更新成功！'));
            }else{
                $this->ajaxReturn(array('code'=>'0','msg'=>'更新失败！'));
            }
        }else{
            $result = $do->where(['uid' => session('user.id')])-> field('uptime_cfg')->order('atime desc')->find();
            if ($result){
                $result = unserialize($result['uptime_cfg']);
            }
            C('seo', ['title' => '商品上架时间管理']);
            $this->assign("result",$result);
            $this->authApi('/SellerGoods/goods_online')->with();
            $this->display();
        }
    }
    
	
    //获取天猫宝贝
    public function get_tmall_item(){
        
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

        if(!M('goods_param')->add(['param_value'=>'other','goods_id'=>$insid,'option_id'=>8975])) goto error;

        $do->commit();

        goods_pr($insid); //更新商品PR

        $this->ajaxReturn(array('status'=>'success','msg'=>'导入成功！','url'=>$insid));
        error:
            $do->rollback();
            $this->ajaxReturn(array('status'=>'warning','msg'=>'导入失败！'));
        
    }


    //获取淘宝宝贝
    public function get_taobao_item(){
        
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

        if(!M('goods_param')->add(['param_value'=>'other','goods_id'=>$insid,'option_id'=>8975])) goto error;

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
    * 拼二维数组
    * @param array $arr1,$arr2 两个要进行拼值的数组
    * @param string $icon 分隔符
    */
    public function array_2($arr1,$arr2,$icon=','){
        foreach($arr1 as $key=>$val){
            foreach($arr2 as $v){
                $result[]=$val.$icon.$v;
            }
        }
        return $result;
    }



    /**
    * 库存属性 $_POST数据处理
    */
    public function _attr_post(){
        $attr_list=array();
        //$price=array();
        $price_min = 0;
        $price_max = 0;
        $num=0;
        foreach(I('post.attr_sku_attr_id') as $key=>$val){
            //属性名称
            $attr_name=array();
            $attr_name_tmp=explode(',',I('post.attr_sku_attr')[$key]);
            foreach($attr_name_tmp as $v){
                $v=explode(':',$v);
                $attr_name[]=end($v);
            }
            $tmp=array(
                    'attr_id'           =>$val,
                    'attr'              =>I('post.attr_sku_attr')[$key],
                    'attr_name'         =>implode(',',$attr_name),
                    'images'            =>I('post.images'),
                    'id'                =>I('post.attr_sku_id')[$key],
                    'seller_id'         =>I('post.seller_id'),
                    'price'             =>I('post.attr_sku_price')[$key],
                    'price_purchase'    =>I('post.attr_sku_price_purchase')[$key],
                    'price_market'      =>I('post.attr_sku_price_market')[$key],
                    'num'               =>I('post.attr_sku_num')[$key],
                    'code'              =>I('post.attr_sku_code')[$key],
                    'barcode'           =>I('post.attr_sku_barcode')[$key],
                    'weight'            =>I('post.attr_sku_weight')[$key]
                );

            //验证数据合法性
            if(checkform($tmp['price'],array('egt',0.1))==false) {
                $this->ajaxReturn(array('code'=>0,'msg'=>'【库存第'.($key+1).'条记录】<br>销售价格格式错误或价格低于0.1!'));
            }
            if(checkform($tmp['price_purchase'],array('egt',0.1))==false && $tmp['price_purchase']>0) {
                $this->ajaxReturn(array('code'=>0,'msg'=>'【库存第'.($key+1).'条记录】<br>成本价格格式错误或价格低于0.1!'));
            }
            if(checkform($tmp['price_market'],array('egt',0.1))==false && $tmp['price_market']>0) {
                $this->ajaxReturn(array('code'=>0,'msg'=>'【库存第'.($key+1).'条记录】<br>市场价格格式错误或价格低于0.1!'));
            }
            if(checkform($tmp['num'],array('is_positive_number'))==false) {
                $this->ajaxReturn(array('code'=>0,'msg'=>'【库存第'.($key+1).'条记录】<br>库存数量格式错误，必须为正整数，如果缺货请填0'));
            }

            if($tmp['price_purchase']>0 && $tmp['price_purchase']>$tmp['price']) {
                $this->ajaxReturn(array('code'=>0,'msg'=>'【库存第'.($key+1).'条记录】<br>成本价不得大于销售价！'));
            }

            if($tmp['price_market']>0 && $tmp['price_market']<$tmp['price']) {
                $this->ajaxReturn(array('code'=>0,'msg'=>'【库存第'.($key+1).'条记录】<br>市场价不得小于销售价！'));
            }

            //$price[]=$tmp['price'];
            $price_min = $price_min == 0 ? $tmp['price'] : min($price_min,$tmp['price']);
            $price_max = $price_max == 0 ? $tmp['price'] : max($price_max,$tmp['price']);
            $num+=$tmp['num'];

            $attr_list[]=$tmp;
        }

        $result['price']['min']=$price_min;
        $result['price']['max']=$price_max;

        $price_dif=$result['price']['max']-$result['price']['min'];
        if(count($attr_list)>1 && ($result['price']['min']*1.3 < $result['price']['max'] || $result['price']['max']*0.7>$result['price']['min'])){
            //$this->ajaxReturn(array('code'=>0,'msg'=>'最低价和最高价格区间差价不得超过彼此的30%！'));
        }

        $result['num']=$num;

        $attr=array();
        foreach(I('post.') as $key=>$val){
            if(substr($key,0,5)=='attr_'){
                if(substr($key,0,8)=='attr_id_'){
                    $tkey=substr($key,8);
                    $tval=explode(':',$val);
                    $attr[]=array(
                            'id'            =>I('post.attr_aid_'.$tkey),
                            'attr_id'       =>$tval[0],
                            'option_id'     =>$tval[1],
                            'attr_value'    =>I('post.attr_value_'.$tkey),
                            'attr_images'   =>I('post.attr_images_'.$tkey)[0],
                            'attr_album'    =>@implode(',',I('post.attr_images_'.$tkey))
                        );

                    //库存主图
                    if(I('post.attr_images_'.$tkey)[0]){
                        foreach($attr_list as $vkey=>$v){
                            $v['attr_id']=explode(',',$v['attr_id']);
                            if(in_array($val,$v['attr_id'])){
                                $attr_list[$vkey]['images']=I('post.attr_images_'.$tkey)[0];
                            }
                        }
                    }

                }

                unset($_POST[$key]);  //清除
            }
        }

        $result['attr']=$attr;
        $result['attr_list']=$attr_list;
        return $result;
    }

    /**
    * 新增商品时处理$_POST数据中的商品参数
    */
    public function _param_post(){
        //必填项验证
        
        $param=array();
        foreach($_POST as $key=>$val){
            if(substr($key,0,6)=='param_'){
                if(!empty($val)){
                    $param['data'][]=array(             
                            'param_value'   =>is_array($val)?implode(',', $val):$val,
                            'option_id'     =>substr($key,6)
                        );
                    $param['key'][]=substr($key,6);
                }
                unset($_POST[$key]);
            }
            
        }
        return $param;
    }

    /**
    * 商品参数添加元素
    */
    public function _param_item_add($param,$arr){
        foreach($param as $key=>$val){
            $param[$key]=array_merge($val,$arr);
        }
        return $param;
    }    
    /**
    * 给库存数组添加商品ID
    * @param integer $goods_id 添加商品ID
    * 配合 _attr_post方法使用
    */
    public function _attr_add_goods_id($attr,$goods_id){
        if(!empty($attr['attr'])){
            foreach($attr['attr'] as $key=>$val){
                $attr['attr'][$key]['goods_id']=$goods_id;
            }
        }
        if(!empty($attr['attr_list'])){
            foreach($attr['attr_list'] as $key=>$val){
                $attr['attr_list'][$key]['goods_id']    =   $goods_id;
                $attr['attr_list'][$key]['seller_id']   =   session('user.id');
            }
        }

        return $attr;
    }


    /**
    * 根据类目取属性
    * @param int    $_POST['cid']   类目ID
    */
    public function get_goods_attr($cid){
        $do=M('goods_attr');
        $list=$do->where(array('status'=>1,'category_id'=>$cid,'sid'=>0))->field('id,attr_name')->order('sort asc')->select();


        if(empty($list)){
            $rs=M('goods_category')->where(['id' => $cid])->field('id,sid')->find();
            //dump($rs);
            if($rs['sid']>0) $list=$this->get_goods_attr($rs['sid']);
            else return false;
        }

        return $list;
    }

    /**
    * 根据类目取参数
    * @param int    $_POST['cid']   类目ID
    */
    public function get_goods_param($cid){
        $do=D('Admin/GoodsParamOptionRelation');
        $list=$do->relation(true)->where(array('status'=>1,'category_id'=>$cid))->field('id,group_name')->select();     
        if(empty($list)){
            $rs=M('goods_category')->where(['id' => $cid])->field('id,sid')->find();
            //dump($rs);
            if($rs['sid']>0) $list=$this->get_goods_param($rs['sid']);
            else return false;
        }

        return $list;       
    }    

    public function upload_save(){
        $res=$this->_upload('imageData');
        $this->ajaxReturn($res);
    }

    /**
    * 文件上传
    */
    public function _upload($field){
        if (empty($_FILES)) {
            $result['code']=0;
            $result['msg']=C('error_code')[53];
            return $result;
        }

        //充许上传格式
        $ext_arr    =array('gif','jpg','jpeg','png');
        $file_ext   =strtolower(pathinfo($_FILES[$field]['name'], PATHINFO_EXTENSION));
        if(!in_array($file_ext,$ext_arr)){
            $result['code']=0;
            $result['msg']=C('error_code')[52];
            return $result;
        }
        //充许上传文件大小，限制3M
        $maxsize=1024*1024*3;
        $filesize=filesize($_FILES[$field]['tmp_name']);
        if($filesize>$maxsize){
            $result['code']=0;
            $result['msg']=C('error_code')[51];
            return $result;
        }

        $res=$this->api('/Upload/upload2',array('openid'=>session('user.openid'),'content'=>file_get_contents($_FILES[$field]['tmp_name'])),'content,openid');
        $result['code']=$res->_data['code'];
        $result['msg']=$res->_data['msg'];
        $result['url']=$res->_data['data']['url'];

        return $result;   
    }       

	
	/**
    * 更新包装模板和售后模板
    * @param int    $_POST['type']   类别 1.包装模板 2.售后模板
    */
	public function update_goods_p(){
		$table = I('get.type');
		if(I('get.type') == 1){
			$table = 'goods_package';
			$field = 'id,package_name';
		}else if(I('get.type') == 2){
			$table = 'goods_protection';
			$field = 'id,protection_name';
		}else{
			exit();
		}
		
		$res = M($table)->field($field)->where(['uid' => getUid()])->select();
		$this->ajaxReturn($res);
	}
	
	/**
    * 更新店内分类
    */
	public function update_category(){
		$result = M('shop_goods_category')->field('id,sid,category_name')->where('status=1 and uid='.session('user.id'))->order('sid asc,sort asc')->select();
		foreach($result as $k => $v){
			if($v['sid'] == 0){
				$v['son'] = array();
				$res[$v['id']] = $v;
			}else{
				$res[$v['sid']]['son'][] = $v;
			}
		}
		if($res){
			$data['code'] = 1;
			$data['msg'] = '已更新店内分类，请重新勾选分类！';
			$data['data'] = $res;
			
		}else{
			$data['code'] = 0;
			$data['msg'] = '当前没有分类，请先去设置分类！';
			$data['data'] = $res;
		}
		$this->ajaxReturn($data);
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
	        'is_best'           =>I('get.is_best')
	    ];
	    if (isset($_GET['id']) && !empty(I('get.id'))) {
	        $chooseIds =   M('activity')->where(['id' => I('get.id'), 'shop_id' => $this->_map['id']])->getField(I('get.field'));
	        $this->assign('chooseIds', $chooseIds);
	    }
	    
	    $this->authApi('/SellerGoods/goods_online',$data,'p,pagesize,action,q,code,category_id,shop_category_id,s_price,e_price,s_sale,e_sale,is_best')->with();
	    $this->display();
	}

    /**
    * 检查发布商品权限
    */
    public function check_goods_permissions(){
        $countPackage       =   M('goods_package')->where(['uid' => session('user.id')])->count();    //包装模板
        if ($countPackage <= 0) {
            $type['package']    =1;
        }
        $countProtection    =   M('goods_protection')->where(['uid' => session('user.id')])->count();     //售后模板
        if ($countProtection <= 0) {
            $type['protection'] =1;
        }

        /*
        $this->authApi('/SellerGoods/first_category')->with('cate'); //店铺绑定的分类
        if ($this->_data['code'] == 3) {
            $type['category']   =1;
        }
        */

        $countExpressTpl       = M('express_tpl')->where(['uid' => session('user.id')])->count();    //运费模板
        if ($countExpressTpl <= 0) {
            $type['express_tpl'] =1;
        }


        //取商品类目权限
        $shop = M('shop')->where(['id' => session('user.shop_id')])->field('category_id,category_second')->find();

        //dump(M('shop')->getLastSQL());
		if(!$shop['category_id'] || !$shop['category_second']){
			$this->display('error');
			exit;
		}
        $goods_category = get_category([
            'table'         => 'goods_category',
            'level'         => 3,
            'sql'           => 'status=1',
            'field'         => 'id,sid,status,category_name',
            'map'           => [['id' => ['in',$shop['category_id']]],['id' => ['in',$shop['category_second']]]],
        ]);

        //dump($goods_category);
        $this->assign('goods_category',$goods_category);

        if($type){
            $this->assign('type',$type);
            $this->display('check');
            exit;
        }
        
    }
}