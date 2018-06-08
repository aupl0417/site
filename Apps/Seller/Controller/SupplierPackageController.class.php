<?php
namespace Seller\Controller;
use Common\Form\Form;
use Think\Controller;

class SupplierPackageController extends Controller {
	
	private $seller_id;
	protected function _initialize(){
        C('cfg',getSiteConfig());
        //parent::_initialize();

		$this->seller_id = C('cfg.supplier')['seller_id'];
    }
    
    public function index() {
        C('seo', ['title' => '包装模板管理']);
        $data   =   M('goods_package')->where(['uid' => $this->seller_id,'supplier_id'=>getUid()])->field('package_name,atime,id')->order('id desc')->select();
        $this->assign('data', $data);
        $this->display();
    }
    /*
    public function add($id = null) {
        $title  =   '添加';
        if (intval($id) > 0) {
            $title  =   '编辑';
        }
        
        if (IS_POST) {
            $data   =   I('post.');
            $token  =   $data['__hash__'];
            if (S('token_' . md5($token)) || !$_SESSION['__hash__'][strstr($token, '_', true)]) {
                $this->ajaxReturn(['code' => 0, 'msg' => '心急了吃不了热豆腐']);
            }
            $model  =   D('Common/Package');
            if (!$data = $model->token(false)->create($data)) {
                $this->ajaxReturn(['code' => 0, 'msg' => $model->getError()]);
            }
            $flag   =   false;
            if ($id > 0) {
                $flag   =   $model->save($data);
            } else {
                $flag   =   $model->add($data);
            }
            
            S('token_' . md5($token), null);
            if ($flag) {
                unset($_SESSION['__hash__'][strstr($token, '_', true)]);
                $this->ajaxReturn(['code' => 1, 'msg' => $title . '成功']);
            } else {
                $this->ajaxReturn(['code' => 0, 'msg' => $id > 0 ? '无任何修改' : $title . '失败']);
            }
        } else {
            $data   =   [];
            if ($id > 0) {
                $data   =   M('goods_package')->where(['id' => $id, 'uid' => getUid()])->find();
                $data['content']    =   html_entity_decode($data['content']);
            }
            $this->builderForm()
            ->keyId()
            ->keyText('package_name', '包装模板名称', 1)
            ->keyUeditor('content', '包装模板详情', '', 1, '没写过？<a data-type="vmodal" data-title="包装模板" data-url="'.U('/package/template').'" class="text_yellow" href="javascript:;">查看模板</a>')
            ->data($data)
            ->view();
            $this->assign('title', $title);
            C('seo', ['title' => $title . '包装模板']);
            $this->display();
        }
    }
*/

    /**
     *
     * 创建模板
     *
     */
    public function create() {
        $data   =   [];
        $id     =   I('get.id', 0, 'int');
        $title  =   '创建包装模板';
        if ($id > 0) {
            $data   =   M('goods_package')->where(['id' => $id, 'uid' => $this->seller_id,'supplier_id'=>getUid()])->find();
            $data['content']    =   html_entity_decode($data['content']);
            $title = '修改包装模板';
        }
        $config = [
            'action' => U('/SupplierPackage/save'),
            'gourl'  => '"' . U('/SupplierPackage') . '"',
        ];
        $form = Form::getInstance($config)
            ->hidden(['name' => 'id', 'value' => $data['id']])
            ->text(['name' => 'package_name', 'title' => '模板名称', 'value' => $data['package_name'], 'require' => 1, 'validate' => ['required', 'rangelength' => '[1,20]']])
            ->ueditor(['name' => 'content', 'title' => '模板详情', 'value' => $data['content'], 'require' => 1, 'validate' => ['required', 'ranglength' => '[10,1000]']])
            ->submit(['title' => $title])
            ->create();

        $this->assign('form', $form);
        C('seo', ['title' => '创建包装模板']);
        $this->assign('title', $title);
        $this->display();
    }


    /**
     *
     * 保存模板
     *
     */
    public function save() {
        if (IS_POST) {
            $data   =   I('post.');
            $id     =   $data['id'];
			$data['uid'] = $this->seller_id;
			$data['supplier_id'] = getUid();
			
            $model  =   D('Common/Package');
            if (!$data = $model->token(false)->create($data)) {
                $this->ajaxReturn(['code' => 0, 'msg' => $model->getError()]);
            }

            $flag = $id > 0 ? $model->save() : $model->add();
            $flag ? $this->ajaxReturn(['code' => 1, 'msg' => '操作成功']) : $this->ajaxReturn(['code' => 0, 'msg' => $id > 0 ? '无任何修改' : '操作失败']);
        }
    }

    
    public function del() {
        $id =   I('post.id');
        if ($id) {
            $flag   =   M('goods_package')->where(['id' => ['in', $id],'uid'=>$this->seller_id ,'supplier_id' => getUid()])->delete();
            $flag   ?   $this->ajaxReturn(['code' => 1, 'msg' => '删除成功']) : $this->ajaxReturn(['code' => 0, 'msg' => '删除失败']);
        }
    }
    
    /**
     * 模板
     */
    public function template() {
        $this->display();
    }
}