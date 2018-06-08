<?php
namespace Seller\Controller;
use Common\Form\Form;

class AdvisoryController extends AuthController {
    
    /**
     * 列表
     */
    public function index() {
        $this->api('/GoodsAdvisory/category')->with('cate');//类型
        $sidArr = [];
        if ($this->_data['code'] == 1) {
            $sidArr = $this->_data['data'];
        }
        $statusArr = [1 => '未回复', 2 => '已回复'];
        $timeArr = ['advisory', 'reply'];
        if (isset($_GET['goods_name']) && !empty(I('get.goods_name'))) $data['goods'] = I('get.goods_name');   //商品
        if (isset($_GET['nick']) && !empty(I('get.nick'))) $data['nick'] = I('get.nick');   //用户
        if (isset($_GET['type']) && array_key_exists(I('get.type'), $sidArr))  $data['type'] = I('get.type');  //类型
        if (isset($_GET['time_area']) && in_array(I('get.time_area'), $timeArr)) $data['time_area'] = I('get.time_area');   //时间类型
        if (isset($_GET['sday']) && !empty(I('get.sday'))) $data['sday'] = I('get.sday');   //咨询开始时间
        if (isset($_GET['eday']) && !empty(I('get.eday'))) $data['eday'] = I('get.eday');   //咨询结束时间
//         if (isset($_GET['rsday']) && !empty(I('get.rsday'))) $data['rsday'] = I('get.rsday');   //回复开始时间
//         if (isset($_GET['reday']) && !empty(I('get.reday'))) $data['reday'] = I('get.reday');   //回复结束时间
        if (isset($_GET['sid']) && array_key_exists(I('get.sid'), $statusArr))  $data['status'] = I('get.sid');  //状态
        $data['pagesize'] = 15;
        $this->authApi('/SellerGoodsAdvisory/index', $data, 'goods,status,nick,type,sday,eday,time_area,pagesize')->with();
        $this->assign('advisoryReplyHeader', enCryptRestUri('/Advisory/reply'));
        $this->assign('advisoryEditReplyHeader', enCryptRestUri('/Advisory/edit'));
        C('seo', ['title' => '第' . I('p', 1, 'int') . '页_咨询列表']);
        $this->display();
    }
    
    /**
     * 回复
     */
    public function reply() {
        $id = I('get.id', 0, 'int');
        if ($id > 0) {
            $this->findData($id);

            if (C('DEFAULT_THEME') == 'default') {
                $this->builderForm()->keyId()
                    ->keyTextArea('content', '回复内容', 1)
                    ->data(['id' => $this->_data['data']['id']])
                    ->view();
                $this->assign('advisoryReplyHeader', enCryptRestUri('/Advisory/reply'));
            } else {
                $config['action'] = U('/run/authrun');
                $config['gourl'] = "'".U('/advisory/detail', ['id' => $id])."'";
                $form = Form::getInstance($config)
                    ->hidden(['name' => 'id', 'value' => $id])
                    ->textarea(['name' => 'content', 'title' => '回复内容', 'require' => 1, 'validate' => ['required', 'rangelength' => '[5,500]']])
                    ->submit(['title' => '立即回复'])
                    ->create();
                $this->assign('form', $form);
            }
        }
        C('seo', ['title' => '回复咨询']);
        $this->display();
    }
    
    /**
     * 详情
     */
    public function detail() {
        $id = I('get.id', 0, 'int');
        if ($id > 0) {
            $this->findData($id);
        }
        C('seo', ['title' => '咨询详情']);
        $this->display();
    }
    
    /**
     * 修改
     */
    public function edit() {
        $id = I('get.id', 0, 'int');
        if ($id > 0) {
            $this->findData($id);
            if (C('DEFAULT_THEME') == 'default') {
                $this->builderForm()->keyId()
                    ->keyTextArea('content', '回复内容', 1)
                    ->data(['id' => $this->_data['data']['id']])
                    ->view();
                $this->assign('advisoryEditReplyHeader', enCryptRestUri('/Advisory/edit'));
            } else {
                $config['action'] = U('/run/authrun');
                $config['gourl'] = "'".U('/advisory/detail', ['id' => $id])."'";
                $form = Form::getInstance($config)
                    ->hidden(['name' => 'id', 'value' => $id])
                    ->textarea(['name' => 'content', 'title' => '回复内容', 'require' => 1, 'validate' => ['required', 'rangelength' => '[5,500]']])
                    ->submit(['title' => '修改回复'])
                    ->create();
                $this->assign('form', $form);
            }

        }
        C('seo', ['title' => '咨询编辑']);
        $this->display();
    }
    
    /**
     * 获取数据
     * @param integer $id
     */
    private function findData($id) {
        $this->authApi('/SellerGoodsAdvisory/view', ['id' => $id])->with();
    }
}