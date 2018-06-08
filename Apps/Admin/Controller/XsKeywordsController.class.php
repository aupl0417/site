<?php
/**
 * -------------------------------------------------
 * 关键词添加索引
 * -------------------------------------------------
 * Autho: Lazycat <673090083@qq.com>
 * -------------------------------------------------
 * 2016-11-05
 * -------------------------------------------------
*/
namespace Admin\Controller;
use Think\Controller;
use Xs\xs;
use Xs\XSDocument;
import('Vendor.Xs.xs');
class XsKeywordsController extends CommonController {
    protected $xs;  //迅搜句柄
    protected $index; //迅搜索引句柄
    protected $search; //迅搜搜索句柄
    protected $project = 'keywords'; //项目

    public function _initialize()
    {
        parent::_initialize();

        $this->xs = new xs($this->project);
        $this->index = $this->xs->index;
        $this->search = $this->xs->search;
    }

    /**
     * 将关键词加入索引
     * 如何需要全部重建索引，请先清除已有索引
     */
    public function build_index(){
        if(empty($_GET['p'])) $this->clean(1);

        $do = M('keywords_lib');
        $count = $do->where($map)->count();
        $pagesize = 1000;    //每页提取记录数
        $page = ceil($count / $pagesize);

        $p = I('get.p') ? I('get.p') : 1;
        if($p > $page) {
            echo '创建索引完成！';
            exit();
        }
        //$this->index->stopRebuild(); //清除重建出错状态
        $doc = new XSDocument();
        $this->index->openBuffer(128); //缓冲大小200M

        $list = $do->where($map)->page($p)->limit($pagesize)->field('id,keyword,pr')->order('id desc')->select();
        foreach($list as $val){
            $doc->setFields($val);
            $res = $this->index->add($doc);
        }

        $this->index->closeBuffer();
        //sleep(1);
        gourl(__CONTROLLER__.'/build_index/p/' . ($p+1));
    }

    /**
     * 更新关键词索引
     */
    public function update_index(){
        $do = M('keywords_lib');
        $count = $do->where($map)->count();
        $pagesize = 1000;    //每页提取记录数
        $page = ceil($count / $pagesize);

        $p = I('get.p') ? I('get.p') : 1;
        if($p > $page) {
            echo '创建索引完成！';
            exit();
        }
        //$this->index->stopRebuild(); //清除重建出错状态
        $doc = new XSDocument();
        $this->index->openBuffer(128); //缓冲大小200M

        $list = $do->where($map)->page($p)->limit($pagesize)->field('id,keyword,pr')->order('id desc')->select();
        foreach($list as $val){
            $doc->setFields($val);
            $res = $this->index->update($doc);
        }

        $this->index->closeBuffer();
        //sleep(1);
        gourl(__CONTROLLER__.'/update_index/p/' . ($p+1));
    }


    /**
     * 清空索引
     */
    public function clean($exit=''){
        $this->xs = new xs('keywords');
        $this->index = $this->xs->index;

        $this->index->stopRebuild(); //清除重建出错状态
        $res = $this->index->clean();

        if($exit == 1) return;

        if($res) $this->ajaxReturn(['status' => 'success','msg' => '清空成功！']);
        else $this->ajaxReturn(['status' => 'warning','msg' => '清空失败！']);
    }
}