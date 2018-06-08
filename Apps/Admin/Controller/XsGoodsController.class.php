<?php
/**
 * -------------------------------------------------
 * 商品添加索引
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
class XsGoodsController extends CommonController {
    protected $xs;  //迅搜句柄
    protected $index; //迅搜索引句柄
    protected $search; //迅搜搜索句柄
    protected $project = 'goodscfgbyledui'; //项目


    public function _initialize()
    {
        parent::_initialize();
        $this->xs = new xs($this->project);
        $this->index = $this->xs->index;
        $this->search = $this->xs->search;
    }

    public function index(){
        $this->display();
    }

    /**
     * 将商品加入索引
     * 如何需要全部重建索引，请先清除已有索引
     */
    public function build_index(){
        //set_time_limit(0);
        if(empty($_GET['p'])) $this->clean(1);

        $do = M('goods');
        $map['status']  = 1;
        //$map['num']     = ['gt',0];
        $map['_string'] = 'shop_id in (select id from '.C('DB_PREFIX').'shop where status=1 and is_test=0)';

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
            $item = $this->goods_item($val);
            if(isset($item['error']) && $item['error'] == 0){

            }else {
                //dump($item);
                $doc->setFields($item);
                $res = $this->index->add($doc);
            }
        }

        $this->index->closeBuffer();
        //sleep(1);
        gourl(__CONTROLLER__.'/build_index/p/' . ($p+1));
    }


    /**
     * 更新商品索引
     */
    public function update_index(){
        //set_time_limit(0);

        $do = M('goods');
        $map['status']  = 1;
        //$map['num']     = ['gt',0];
        $map['_string'] = 'shop_id in (select id from '.C('DB_PREFIX').'shop where status=1 and is_test=0)';

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
            $item = $this->goods_item($val);
            if(isset($item['error']) && $item['error'] == 0){
                $this->index->del($item['id']);
            }else {
                //dump($item);
                $doc->setFields($item);
                $res = $this->index->update($doc);
            }
        }

        $this->index->closeBuffer();
        //sleep(1);
        gourl(__CONTROLLER__.'/update_index/p/' . ($p+1));
    }

    /**
     * 将商品加入索引
     * 平滑重建索引，只适合数据不是特别大的情况下(数据太大执行时间较久且可能出现内存不足等情况)
     */
    public function rebuild_index(){
        set_time_limit(0);

        $do = M('goods');
        $map['status']  = 1;
        //$map['num']     = ['gt',0];

        $count = $do->where($map)->count();
        $pagesize = 100;    //每页提取记录数
        $page = ceil($count / $pagesize);

        $this->index->stopRebuild(); //清除重建出错状态
        $doc = new XSDocument();
        $res = $this->index->beginRebuild();
        $this->index->openBuffer(200); //缓冲大小200M

        for($i=0; $i<$page; $i++){
            $list = $do->where($map)->page($i)->limit($pagesize)->getField('id',true);
            foreach($list as $val){
                $item = $this->goods_item($val);
                //dump($item);
                $doc->setFields($item);
                $res = $this->index->add($doc);
            }
        }

        $this->index->closeBuffer();
        $res = $this->index->endRebuild();

        if($res) $this->ajaxReturn(['status' => 'success','msg' => '重建索引成功！']);
        else $this->ajaxReturn(['status' => 'warning','msg' => '重建索引失败！']);
    }

    public function test(){
        //dump($this->goods_item(8992));
        /*
        $total = $this->search->getDbTotal();
        dump($total);
        $count = $this->search->setQuery('status:1')->count();
        dump($count);
        $res = $this->search->setQuery('city:揭阳')->setLimit(100)->setMultiSort(['pr'])->search();

        dump($res);

        $this->xs = new xs('keywords');
        $this->search = $this->xs->search;
        $res = $this->search->setQuery('高压锅')->setLimit(10)->search();
        dump($res);
        */

        $data =[
            'id'    =>'8988',
            'pr'    => 200,
        ];
        $doc = new XSDocument;
        $doc->setFields($data);

        $this->index->update($doc);
    }

    /**
     * 获取商品信息用于加入索引
     */
    public function goods_item($id){
        $do = D('Common/GoodsRelation');
        $field = 'id,atime,status,category_id,goods_name,sub_name,brand_id,images,shop_id,uptime,price,price_max,num,sale_num,rate_num,fav_num,view,seller_id,fraction,score_ratio,is_best,is_love,code,free_express,is_self,express_tpl_id,activity_id,officialactivity_join_id,officialactivity_price,pr,pr_extra,is_daigou,is_display,score_type';
        $rs = $do->relation(true)->where(['id' => $id])->field($field)->find();

        if($rs['status'] != 1 || $rs['is_display'] != 1 || $rs['num'] <1 ) return ['error' => 0,'id' => $rs['id']];
        //dump($rs);

        $field = explode(',',$field);
        foreach($field as $val){
            $data[$val] = $rs[$val];
        }
        $data['pr'] += $rs['pr_extra'];
        $data['pr'] += (time() - strtotime($data['uptime']))/86400;
        //$data['atime']  = strtotime($data['atime']);
        $data['category_name']  = nav_sort(['table' => 'goods_category','icon' => ',','field' => 'id,sid,category_name','id' => $rs['category_id'],'key' => 'category_name','cache_name' => 'nav_sort_goods_category_'.$rs['category_id']]);

        if($rs['brand_id'] > 0) $data['brand_name'] = M('brand')->where(['id' => $rs['brand_id']])->getField('b_name');
        else $data['brand_name'] = '其它';

        //$area = $this->cache_table('area');
        $express = M('express_tpl')->where(['id' => $rs['express_tpl_id']])->field('province,city')->find();
        $area = M('area')->cache(true)->where(['id' => ['in',[$express['province'],$express['city']]]])->getField('id,a_name',true);
        $data['city']   = $area[$express['province']] . ' ' . $area[$express['city']];
        $data['city_id']= $express['city'];

        //类目
        $upsid = upsid(['table' => 'goods_category','id' => $rs['category_id']]);
        $data['first_category_id']  = $upsid[0];
        $data['second_category_id'] = $upsid[1];
        //$data['three_category_id']  = $upsid[2];

        //属性
        foreach ($rs['attr_list'] as $val){
            $data['attr'][]         = $val['attr'];
            $data['attr_id'][]      = $val['attr_id'];
            $data['attr_name'][]    = $val['attr_name'];
        }
        $data['attr']       = implode(',',$data['attr']);
        $data['attr_id']    = implode(',',$data['attr_id']);
        $data['attr_name']  = implode(',',$data['attr_name']);
        $data['attr_list']  = serialize($rs['attr_list']);

        $data['url']        = C('sub_domain.item').'/goods/' . $rs['attr_list'][0]['id'] . '.html';

        //参数
        $data['option']     = M('goods_param')->cache(true)->where(['goods_id' => $id])->getField('param_value',true);
        $data['option']     = @implode(' ',$data['option']);

        //店铺信息
        $shop_type = $this->cache_table('shop_type');
        $data['shop_id']    = $rs['shop']['id'];
        $data['nick']       = $rs['seller']['nick'];
        $data['shop_name']  = $rs['shop']['shop_name'];
        $data['shop_url']   = shop_url($rs['shop']['id'],$rs['shop']['domain']);
        $data['qq']         = $rs['shop']['qq'];
        $data['type_id']    = $rs['shop']['type_id'];
        $data['type_name']  = $shop_type[$data['type_id']];

        //个人店铺商品PR降15分
        if($data['type_id'] == 6) $data['pr']   -= 15;

        //dump($data);
        return $data;

    }



    /**
     * 清空索引
     */
    public function clean($exit=2){
        $this->index->stopRebuild(); //清除重建出错状态
        $res = $this->index->clean();
        if($exit == 1) return false;

        if($res) $this->ajaxReturn(['status' => 'success','msg' => '清空成功！']);
        else $this->ajaxReturn(['status' => 'warning','msg' => '清空失败！']);
    }


}