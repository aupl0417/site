<?php
/**
+----------------------------------------------------------------------
| RestFull API 
+----------------------------------------------------------------------
| 意见或建议
+----------------------------------------------------------------------
| Author: lazycat <673090083@qq.com>
+----------------------------------------------------------------------
*/
namespace Rest\Controller;
use Think\Controller\RestController;
class FeedbackController extends CommonController {
    public function index(){
    	redirect(C('sub_domain.www'));
    }

    /**
    * 添加意见反馈
    * @param string $_POST['content']   内容
    * @param stirng $_POST['email']     邮件
    * @param string $_POST['images']    图片，多张用逗号隔开
    */
    public function add(){
        //频繁请求限制
        $this->_request_check();

        //必传参数检查
        $this->need_param=array('content','email','sign');
        $this->_need_param();
        $this->_check_sign();

        //检查是否重复提交内容
        if(M('feedback')->where(array('email'=>I('post.email'),'content'=>I('post.content'),'atime'=>array('gt',date('Y-m-d H:i:s',time()-86400))))->find()){
            $this->apiReturn(4,'',1,'您今天已提交过此反馈意见，我们将会尽快处理，请耐心等待！');
        }

        $data['content']=I('post.content');
        $data['uid']    =$this->uid;
        $data['email']  =I('post.email');
        $data['images'] =I('post.images');

        $do=D('Common/Feedback');
        if($do->create($data)){
            if($do->add()){
                //添加成功
                $this->apiReturn(1);
            }else{
                //添加失败
                $this->apiReturn(0);
            }
        }else{
            //数据验证失败
            $this->apiReturn(4,'',1,$do->getError());
        }


    }

    /**
    * 文件上传
    */
    public function upload(){
        $res=$this->_upload('imageData');
        $this->ajaxReturn($res);   
    }
}