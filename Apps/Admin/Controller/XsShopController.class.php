<?php
/**
 * -------------------------------------------------
 * 店铺添加索引
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
class XsShopController extends CommonController {
    protected $xs;  //迅搜句柄
    protected $index; //迅搜索引句柄
    protected $search; //迅搜搜索句柄
    protected $project = 'shop'; //项目

    public function _initialize()
    {
        parent::_initialize();

        $this->xs = new xs($this->project);
        $this->index = $this->xs->index;
        $this->search = $this->xs->search;
    }

    /**
     * 将店铺加入索引
     * 如何需要全部重建索引，请先清除已有索引
     */
    public function build_index(){
        //set_time_limit(0);
        if(empty($_GET['p'])) $this->clean(1);

        $do = M('shop');
        $map['status']  = 1;
        $map['is_test'] = 0;
        $map['goods_num'] = ['gt',0];

        $count = $do->where($map)->count();
        $pagesize = 200;    //每页提取记录数
        $page = ceil($count / $pagesize);

        $p = I('get.p') ? I('get.p') : 1;
        if($p > $page) {
            echo '创建索引完成！';
            exit();
        }
        //$this->index->stopRebuild(); //清除重建出错状态
        $doc = new XSDocument();
        $this->index->openBuffer(128); //缓冲大小200M

        $list = $do->where($map)->page($p)->limit($pagesize)->order('id desc')->getField('id',true);
        foreach($list as $val){
            $item = $this->shop_item($val);
            //dump($item);
            $doc->setFields($item);
            $res = $this->index->add($doc);
        }

        $this->index->closeBuffer();
        //sleep(1);
        gourl(__CONTROLLER__.'/build_index/p/' . ($p+1));
    }

    /**
     * 更新店铺索引
     */
    public function update_index(){
        //set_time_limit(0);

        $do = M('shop');
        $map['status']  = 1;
        $map['is_test'] = 0;
        $map['goods_num'] = ['gt',0];

        $count = $do->where($map)->count();
        $pagesize = 200;    //每页提取记录数
        $page = ceil($count / $pagesize);

        $p = I('get.p') ? I('get.p') : 1;
        if($p > $page) {
            echo '创建索引完成！';
            exit();
        }
        //$this->index->stopRebuild(); //清除重建出错状态
        $doc = new XSDocument();
        $this->index->openBuffer(128); //缓冲大小200M

        $list = $do->where($map)->page($p)->limit($pagesize)->order('id desc')->getField('id',true);
        foreach($list as $val){
            $item = $this->shop_item($val);
            //dump($item);
            $doc->setFields($item);
            $res = $this->index->update($doc);
        }

        $this->index->closeBuffer();
        //sleep(1);
        gourl(__CONTROLLER__.'/update_index/p/' . ($p+1));
    }


    /**
     * 店铺详情
     */
    public function shop_item($id){
        $do = M('shop');
        $rs = $do->where(['id' => $id])->field('id,uid,shop_name,shop_level,shop_logo,about,scope,type_id,province,city,domain,qq,mobile,fav_num,pr,goods_num,sale_num,fraction_speed,fraction_service,fraction_desc,fraction')->find();

        $area = M('area')->cache(true)->where(['id' => ['in',[$rs['province'],$rs['city']]]])->getField('id,a_name',true);
        $rs['city_name']    = $area[$rs['province']] . ' ' . $area[$rs['city']];

        $shop_type = $this->cache_table('shop_type');
        $rs['type_name']    = $shop_type[$rs['type_id']];

        return $rs;
    }

    public function test(){
        dump($this->shop_item(160));
        $res = $this->search->setQuery('批发')->setLimit(100)->setMultiSort(['pr'])->search();

        dump($res);

    }


    /**
     * 清空索引
     */
    public function clean($exit=1){
        $this->index->stopRebuild(); //清除重建出错状态
        $res = $this->index->clean();

        if($exit == 1) return;

        if($res) $this->ajaxReturn(['status' => 'success','msg' => '清空成功！']);
        else $this->ajaxReturn(['status' => 'warning','msg' => '清空失败！']);
    }


}