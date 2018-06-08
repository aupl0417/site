<?php
namespace Seller\Controller;
use Common\Form\Form;

class BrandController extends AuthController {
    public function _initialize() {
        parent::_initialize();
        if (session('user.shop_type') == 6) {   //如果为个人店的话则执行跳转
            redirect(DM('zhaoshang', '/shopup'));
        }
    }
	/**
     * 添加品牌
     */
	public function brand_add(){
		$data['shop_id'] = $this->shop_info['id'];
		$this->builderForm()
		->keyId('shop_id')
		->keyText('b_name', '品牌名称', 1)
		->keyText('b_ename', '品牌英文名称')
		->keySingleImages('b_logo', '品牌logo',1)
		->keyText('b_code', '品牌商标注册号')
		->keyText('b_master', '品牌所有者',1)
		->keySingleImages('b_images', '品牌商标证书照片')
		->keySingleImages('b_images2', '商标授理书照片')
		->data($data)
		->view();
		C('seo', ['title' => '添加品牌']);
		$this->seo(['title' => '添加品牌']);
		$this->display();
	}


    /**
     *
     * 创建品牌
     *
     */
	public function create() {
	    $id = $this->shop_info['id'];
        $config =   [
            'action' => U('/run/authrun'),
            'gourl'  => '"' . U('/brand') . '"',
        ];
	    $form = Form::getInstance($config)
            ->hidden(['name' => 'shop_id', 'value' => $id])
            ->text(['name' => 'b_name', 'title' => '品牌名称', 'require' => 1, 'validate' => ['required']])
            ->text(['name' => 'b_ename', 'title' => '品牌英文名称'])
            ->singleImages(['name' => 'b_logo', 'title' => '品牌logo', 'require' => 1, 'validate' => ['required']])
            ->text(['name' => 'b_code', 'title' => '品牌商标注册号'])
            ->text(['name' => 'b_master', 'title' => '品牌所有者', 'require' => 1, 'validate' => ['required']])
            ->singleImages(['name' => 'b_images', 'title' => '品牌商标证书照片'])
            ->singleImages(['name' => 'b_images2', 'title' => '商标受理书照片'])
            ->submit(['title' => '创建品牌'])
            ->create();

	    $this->assign('form', $form);
        C('seo', ['title' => '创建品牌']);
        $this->display();
    }


    /**
     *
     * 修改品牌
     *
     */
    public function edit() {
	    $id = I('get.id', 0, 'int');

	    if ($id > 0) {
            $this->authApi('/SellerBrand/my_brand_view',['brand_id'=>$id])->with();
            $config =   [
                'action' => U('/run/authrun'),
                'gourl'  => '"' . U('/brand') . '"',
            ];
            $form = Form::getInstance($config)
                ->hidden(['name' => 'id', 'value' => $id])
                ->hidden(['name' => 'shop_id', 'value' => $this->shop_info['id']])
                ->text(['name' => 'b_name', 'title' => '品牌名称', 'value' => $this->_data['data']['b_name'], 'require' => 1, 'validate' => ['required']])
                ->text(['name' => 'b_ename', 'title' => '品牌英文名称', 'value' => $this->_data['data']['b_ename']])
                ->singleImages(['name' => 'b_logo', 'title' => '品牌logo', 'value' => $this->_data['data']['b_logo'], 'require' => 1, 'validate' => ['required']])
                ->text(['name' => 'b_code', 'title' => '品牌商标注册号', 'value' => $this->_data['data']['b_code']])
                ->text(['name' => 'b_master', 'title' => '品牌所有者', 'value' => $this->_data['data']['b_master'], 'require' => 1, 'validate' => ['required']])
                ->singleImages(['name' => 'b_images', 'title' => '品牌商标证书照片', 'value' => $this->_data['data']['b_images']])
                ->singleImages(['name' => 'b_images2', 'title' => '商标受理书照片', 'value' => $this->_data['data']['b_images2']])
                ->submit(['title' => '修改品牌'])
                ->create();

            $this->assign('form', $form);
        }
        $this->display();
    }

	/**
     * 修改品牌
     */
	public function brand_edit(){
		$this->authApi('/SellerBrand/my_brand_view',['brand_id'=>I('get.id')])->with();
		$this->builderForm()
		->keyId('id')
		->keyId('shop_id')
		->keyText('b_name', '品牌名称', 1)
		->keyText('b_ename', '品牌英文名称')
		->keySingleImages('b_logo', '品牌logo',1)
		->keyText('b_code', '品牌商标注册号')
		->keyText('b_master', '品牌所有者',1)
		->keySingleImages('b_images', '品牌商标证书照片')
		->keySingleImages('b_images2', '商标授理书照片')
		->data($this->_data['data'])
		->view();
		C('seo', ['title' => '修改品牌']);
		$this->seo(['title' => '修改品牌']);
		$this->display('brand_add');
	}


    /**
     *
     * 我的品牌
     *
     */
	public function index() {
	    $data = [];
	    $statusArr = [0,1];
	    if (isset($_GET['sid']) && in_array(I('get.sid'), $statusArr)) $data['status'] = I('get.sid');
	    $data['pagesize'] = 15;
        $this->authApi('/Goods/brand', $data, 'status,pagesize')->with();
        C('seo', ['title' => '我的品牌']);
        $this->display();
    }


	/**
     * 我的品牌
     */
	public function my_brand(){
		if(isset($_GET['status'])){
			$brand_status=I('get.status');
		}else{
			$brand_status=0;
		}
		$this->authApi('/Goods/brand',['status'=>$brand_status], 'status')->with();
        C('seo', ['title' => '我的品牌']);
		$this->seo(['title' => '我的品牌']);
        $this->display();
	}
    /**
     * 品牌推广
     */
	public function brand_promotion(){
		$this->authApi('/SellerBrand/brand_list')->with();
        C('seo', ['title' => '品牌推广']);
		$this->seo(['title' => '品牌推广']);
        $this->display();
	}

    /**
     *
     * 推广列表
     *
     */
	public function promotionList() {
	    $data['pagesize'] = 15;
        $this->authApi('/SellerBrand/brand_list', $data, 'pagesize')->with();
        //dump($this->_data);
        C('seo', ['title' => '品牌推广']);
        $this->seo(['title' => '品牌推广']);
        $this->display();
    }

    /**
     *
     * 品牌推广 v2
     *
     */
	public function promotion() {
        //商品类目
        $this->authApi('/ShopSetting/shop_info')->with();

        $categorys = M('goods_category')->field('id,category_name')->where('id IN ('.$this->_data['data']['category_id'].') and status = 1')->order('sort asc')->select();
        foreach($categorys as $k=>$v){
            $categorys[$k]['son'] = M('goods_category')->field('id,category_name')->where('sid = '.$v['id'].' and status = 1')->order('sort asc')->select();
        }
        $this->assign('categorys',$categorys);
        $this->api('/Brand/brand_tags');
        $tags = [];
        foreach ($this->_data['data'] as $v) {
            $tags[$v['id']] = $v['tag_name'];
        }

        $this->authApi('/SellerBrand/view',['brand_id'=>I('get.id')])->with();

        $config = [
            'action'=> U('/brand/savePromotion'),
            'gourl' =>  '"' . U('/brand/promotionList') . '"'
        ];

        $form = Form::getInstance($config)
            ->hidden(['name' => 'brand_id', 'value' => I('get.id')])
            ->text(['name' => 'name', 'title' => '推广名称', 'require' => 1, 'validate' => ['required']])
            ->text(['name' => 'ename', 'title' => '品牌英文名称'])
            ->singleImages(['name' => 'logo', 'title' => 'logo', 'require' => 1])
            ->singleImages(['name' => 'images', 'title' => '品牌形象', 'require' => 1])
            ->textarea(['name' => 'about', 'title' => '品牌简介', 'require' => 1, 'validate' => ['required', 'rangelength' => '[5,300]']])
            ->checkbox(['name' => 'tag', 'title' => '品牌标签', 'require' => 1, 'options' => $tags, 'validate' => ['required']])
            ->categoryCheckbox(['name' => 'category_id', 'title' => '主营类目', 'require' => 1, 'validate' => ['required'], 'options' => $categorys, 'correspond' => ['id' => 'id', 'name' => 'category_name', 'child' => 'son']])
            ->submit(['title' => '申请推广'])
            ->create();
        $this->assign('form', $form);

        C('seo', ['title' => '申请推广']);
        $this->display();
    }

    /**
     * 修改推广
     */
    public function promotionEdit() {
        //商品类目
        $this->authApi('/ShopSetting/shop_info')->with();

        $categorys = M('goods_category')->field('id,category_name')->where('id IN ('.$this->_data['data']['category_id'].') and status = 1')->order('sort asc')->select();
        foreach($categorys as $k=>$v){
            $categorys[$k]['son'] = M('goods_category')->field('id,category_name')->where('sid = '.$v['id'].' and status = 1')->order('sort asc')->select();
        }
        $this->assign('categorys',$categorys);
        $this->api('/Brand/brand_tags');
        $tags = [];
        foreach ($this->_data['data'] as $v) {
            $tags[$v['id']] = $v['tag_name'];
        }

        $this->authApi('/SellerBrand/promotionView', ['id'=>I('get.id')]);
        $data = $this->_data['data'];
        $this->authApi('/SellerBrand/view',['brand_id'=>$data['brand_id']])->with();
        $config = [
            'action' => U('/brand/saveEditPromotion'),
            'gourl'  => '"' . U('/brand/promotionList') . '"',
        ];
        $form = Form::getInstance($config)
            ->hidden(['name' => 'id', 'value' => $data['id']])
            ->hidden(['name' => 'brand_id', 'value' => $data['brand_id']])
            ->text(['name' => 'name', 'title' => '推广名称', 'value' => $data['name'], 'require' => 1, 'validate' => ['required']])
            ->text(['name' => 'ename', 'title' => '品牌英文名称', 'value' => $data['ename']])
            ->singleImages(['name' => 'logo', 'title' => 'logo', 'value' => $data['logo'], 'require' => 1])
            ->singleImages(['name' => 'images', 'title' => '品牌形象', 'value' => $data['images'], 'require' => 1])
            ->textarea(['name' => 'about', 'title' => '品牌简介', 'value' => $data['about'], 'require' => 1, 'validate' => ['required', 'rangelength' => '[5,300]']])
            ->checkbox(['name' => 'tag', 'title' => '品牌标签', 'value' => $data['tag'], 'require' => 1, 'options' => $tags, 'validate' => ['required']])
            ->categoryCheckbox(['name' => 'category_id', 'title' => '主营类目', 'value' => $data['category_id'], 'require' => 1, 'validate' => ['required'], 'options' => $categorys, 'correspond' => ['id' => 'id', 'name' => 'category_name', 'child' => 'son']])
            ->submit(['title' => '立即修改'])
            ->create();
        $this->assign('form', $form);

        C('seo', ['title' => '修改推广']);
        $this->display();
    }

    /**
     *
     * 保存品牌推广
     *
     */
    public function savePromotion() {
	    if (IS_POST) {
	        $data['about'] = I('post.about');
	        $data['brand_id'] = I('post.brand_id', 0, 'int');
	        $data['category_id'] = join(',', I('post.category_id'));
	        $data['ename']  =   I('post.ename');
	        $data['logo']   =   I('post.logo');
	        $data['images'] =   I('post.images');
	        $data['name']   =   I('post.name');
	        $data['tag']    =   join(',', I('post.tag'));
	        $this->authApi('/SellerBrand/brand_edit', $data, 'ename');
            $this->ajaxReturn($this->_data);
        }
    }

    /**
     * 修改推广保存
     */
    public function saveEditPromotion() {
        if (IS_POST) {
            $data['about'] = I('post.about');
            $data['brand_id'] = I('post.brand_id', 0, 'int');
            $data['category_id'] = join(',', I('post.category_id'));
            $data['ename']  =   I('post.ename');
            $data['logo']   =   I('post.logo');
            $data['images'] =   I('post.images');
            $data['name']   =   I('post.name');
            $data['tag']    =   join(',', I('post.tag'));
            $this->authApi('/SellerBrand/brand_edit', $data, 'ename');
            $this->ajaxReturn($this->_data);
        }
    }


	/**
     * 品牌详情
     */
	public function view(){
		//商品类目
		$this->authApi('/ShopSetting/shop_info')->with();
		
		$categorys = M('goods_category')->field('id,category_name')->where('id IN ('.$this->_data['data']['category_id'].') and status = 1')->order('sort asc')->select();
		foreach($categorys as $k=>$v){
			$categorys[$k]['son'] = M('goods_category')->field('id,category_name')->where('sid = '.$v['id'].' and status = 1')->order('sort asc')->select();
		}
		$this->assign('categorys',$categorys);
		
		$this->api('/Brand/brand_tags')->with('tags');
		
		$this->authApi('/SellerBrand/view',['brand_id'=>I('get.id')])->with();
		
        C('seo', ['title' => '品牌信息']);
		$this->seo(['title' => '品牌信息']);
        $this->display();
	}
}