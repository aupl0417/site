<?php
namespace Sellergoods\Controller;

class RunController extends InitController {
    protected $_run;
    public function _initialize() {
        $this->_run =   A('Home/Run');
    }
    
    public function index() {
        $this->_run->index();
    }
    
    public function authRun() {
        $this->_run->authRun();
    }
    
    public function upload() {
        $this->_run->upload();
    }

    /**
     * 编辑器图片上传
     * */
    public function ueditorUpload(){
        if(empty($_SESSION['user'])){
            echo json_encode(array('state'=> '您还未登录!'));exit;
        }
        $result = $this->_run->ueditorUpload();

        /* 输出结果 */
        if (isset($_GET["callback"])) {
            if (preg_match("/^[\w_]+$/", $_GET["callback"])) {
                echo htmlspecialchars($_GET["callback"]) . '(' . $result . ')';
            } else {
                echo json_encode(array(
                    'state'=> 'callback参数不合法'
                ));
            }
        } else {
            echo $result;
        }
    }

}