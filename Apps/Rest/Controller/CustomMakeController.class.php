<?php
/**
+----------------------------------------------------------------------
| RestFull API 
+----------------------------------------------------------------------
| 频道装修
+----------------------------------------------------------------------
| Author: lazycat <673090083@qq.com>
+----------------------------------------------------------------------
| 2016-11-24
|----------------------------------------------------------------------
*/

namespace Rest\Controller;
use Think\Controller\RestController;
class CustomMakeController extends CommonController {
    public function _initialize() {
        parent::_initialize();

        $action = ACTION_NAME;
        if(!in_array('_'.$action,get_class_methods($this))) $this->apiReturn(1501);  //请求的方法不存在

        $this->_api(['method' => $action]);
    }


    /**
     * 各方法的必签字段
     * @param string $method     方法
     */
    public function _sign_field($method){
        $sign_field = [
            '_layouts'                          => '',    //可用布局
            '_page_layout'                      => 'page,domain',   //取页面布局
            '_cell_add'                         => 'page,domain,id,type,col,name', //添加布局单元
            '_cell_delete'                      => 'id,domain',   //删除布局单元
            '_cell_sort'                        => 'domain,page,ids',  //布局单元排序
            '_layout'                           => 'domain,page', //装修页面布局
            '_modules_lib'                      => 'layout_id,col_index', //装修模块模板库
            '_modules_item_add'                 => 'layout_id,page,domain,col_index,modules_id',   //添加模块
            '_modules_item_sort'                => 'ids',   //模块排序
            '_modules_item_delete'              => 'id',   //删除模块
            '_mod_edit'                         => 'id,index', //更新模块内容
            '_mod_edit_save'                    => 'id,index,data',   //保存模块内容
            '_publish'                          => 'domain,page',    //发布

        ];

        $result=$sign_field[$method];
        return $result;
    }


    public function index(){
    	redirect(C('sub_domain.www'));
    }


    /**
     * 布局
     */
    public function _layouts(){
        $list=M('shop_layout')->where(['id' => ['in','1,4']])->field('atime,etime,ip',true)->select();

        if($list) return ['code' => 1,'data' =>$list];

        return ['code' => 3];
    }

    /**
     * 添加布局单元
     * @param string $_POST['page']    页面
     * @param int    $_POST['domain']  频道域名前缀
     * @param string $_POST['name']    布局标题
     */
    public function _cell_add(){
        $layout = M('shop_layout')->where(['id' => I('post.id')])->field('atime,etime,ip',true)->find();

        $do=D('Common/CustomMakeLayout');

        $data['page']               =I('post.page');
        $data['domain']             =I('post.domain');
        $data['layout_id']          =$layout['id'];
        $data['layout_name']        =I('post.name');
        $data['layout_type']        =$layout['type'];
        $data['col']                =$layout['col'];
        $data['col_0']              =$layout['col_0'];
        $data['col_1']              =$layout['col_1'];
        $data['col_2']              =$layout['col_2'];

        if(!$do->create($data)) return ['code' =>4,'msg' =>$do->getError()];

        if(!$do->add()) return ['code' => 0];

        return ['code' =>1 ];
    }


    /**
     * 删除布局单元
     * @param int    $_POST['id'] 布局ID
     * @param int    $_POST['domain'] 频道域名前缀
     */
    public function _cell_delete(){

        if(M('custom_make_layout')->where(['id' => I('post.id'),'domain' => I('post.domain'),'isys' => 0])->delete()) return ['code' =>1];

        return ['code' =>0];
    }

    /**
     * 布局单元排序
     * @param int    $_POST['ids'] 布局ID
     * @param int    $_POST['domain'] 频道域名前缀
     */
    public function _cell_sort(){
        $ids=explode(',', I('post.ids'));
        foreach($ids as $i => $val){
            M('custom_make_layout')->where(['id' => $val,'domain' => I('post.domain')])->setField('sort',$i);
        }

        return ['code' =>1];
    }

    /**
     * 取页面布局
     * @param string $_POST['page']    页面
     * @param int    $_POST['domain']  频道域名前缀
     */
    public function _page_layout(){

        $rs['layout']   =M('custom_make_layout')->where(['page' => I('post.page'),'domain' => I('post.domain')])->field('atime,etime,ip',true)->order('sort asc')->select();

        if($rs) return ['code' => 1,'data' =>$rs];

        return ['code' => 3];
    }

    /**
     * 取频道页面布局
     * @param int    $_POST['domain'] 频道域名前缀
     * @param string $_POST['page']  装修页面,如:/Index/index
     */
    public function _layout(){

        $do=D('Common/CustomMakeLayoutModulesRelation');
        $list=$do->relation(true)->where(['domain' => I('post.domain'),'page' => I('post.page')])->field('atime,etime,ip',true)->order('sort asc')->select();

        foreach($list as $i => $val){
            foreach($val['modules'] as $j => $v){
                $list[$i]['item'][$v['col_index']] .= '<div class="col-sort" data-id="'.$v['id'].'">'.$this->_modules_item_view($v['id']).'</div>';
            }
            unset($list[$i]['modules']);
        }


        if($list) return ['code' =>1,'data' => $list];

        return ['code' =>3];
    }

    /**
     * 输出模块 ,$id和$data两项必传一项
     * @param int $id        模块id
     * @param array $data    模块数据
     */
    public function _modules_item_view($id='',$data=''){
        $modules = M('custom_make_modules')->where(['id' => $id])->field('atime,etime,ip',true)->find();
        $tpl = './Templates/zh_cn/Channel'.$modules['mod_url'];

        $modules['data'] = eval(html_entity_decode($modules['data']));
        $this->assign('rs',$modules);

        $html = $this->fetch($tpl);

        return $html;
    }

    /**
     * 装修模块模板库
     * @param int $_POST['layout_id'] 布局ID
     * @param int $_POST['col_index'] 单元格
     */
    public function _modules_lib(){
        $layout = M('custom_make_layout')->cache(true)->where(['id' => I('post.layout_id')])->field('col_0,col_1,col_2')->find();
        if(!$layout) return ['code' => 3];
        $col = 'col_'.I('post.col_index');

        $do = M('custom_modules');
        $list = $do->cache(true)->where(['status' => 1,'layout_width' => ['like' ,'%'.$layout[$col].'%']])->field('id,mod_name,images')->select();
        if($list){
            foreach($list as $key => $val){
                $list[$key]['images_'] = myurl($val['images'],150,150);
            }
            return ['code' => 1,'data' => $list];
        }

        return ['code' => 3];
    }

    /**
     * 添加模块
     */
    public function _modules_item_add(){
        $modules = M('custom_modules')->cache(true)->where(['id' => I('post.modules_id')])->field('atime,etime,ip',true)->find();

        $data = [
            'layout_id'     => I('post.layout_id'),
            'mod_name'      => $modules['mod_name'],
            'mod_url'       => $modules['mod_url'],
            'col_index'     => I('post.col_index'),
            'data'          => $modules['cfg'],
            'modules_id'    => I('post.modules_id'),
            'page'          => I('post.page'),
            'domain'        => I('post.domain')
        ];

        if($insid = M('custom_make_modules')->add($data)){
            $html = $this->_modules_item_view($insid);
            $html ='<div class="col-sort" data-id="'.$insid.'">'.$html.'</div>';
            return ['code' => 1,'data' => ['html' => $html,'id' => $insid]];
        }

        return ['code' => 0];
    }

    /**
     * 模块排序
     * @param int    $_POST['ids'] 布局ID
     */
    public function _modules_item_sort(){

        $ids=explode(',', I('post.ids'));
        foreach($ids as $i => $val){
            M('custom_make_modules')->where(['id' => $val])->setField('sort',$i);
        }

        return ['code' =>1];
    }

    /**
     * 删除模块
     * @param int    $_POST['id']        模块ID
     */
    public function _modules_item_delete(){
        $do=M('custom_make_modules');
        if($do->where(['id' =>I('post.id'),'isys' => 0])->delete()) return ['code' =>1];

        return ['code' =>0];
    }

    /**
     * 更改模块局部内容
     * @param int $_POST['id'] //模块ID
     * @param int $_POST['index'] //模块位置（下标值）
     */
    public function _mod_edit(){
        $default_field = array(
            array(
                'formtype'		=>'hidden',
                'name'			=>'goods_id',
            ),
            array(
                'formtype'		=>'hidden',
                'name'			=>'shop_id',
            ),
        );

        $rs = M('custom_make_modules')->where(['id' => I('post.id')])->field('atime,etime,ip',true)->find();
        $rs['data'] = eval(html_entity_decode($rs['data']));
        $rs['data'] = $rs['data']['itemlist'][I('post.index')];
        $modules = M('custom_modules')->where(['id' => $rs['modules_id']])->field('cfg')->find();
        $modules['cfg'] = eval(html_entity_decode($modules['cfg']));
        $modules['cfg'] = $modules['cfg']['itemlist'][I('post.index')];
        $modules['cfg']['default'] = $rs['data']['default'];
        $rs['data'] = $modules['cfg'];
        $rs['data']['field'] = array_merge($rs['data']['field'],$default_field);

        $rs['data']['cfg']['max'] = is_null($rs['data']['cfg']['max']) ? 1 : $rs['data']['cfg']['max'];
        $rs['data']['cfg']['min'] = is_null($rs['data']['cfg']['min']) ? 1 : $rs['data']['cfg']['min'];
        $rs['data']['cfg']['num'] = is_null($rs['data']['cfg']['num']) ? 1 : $rs['data']['cfg']['num'];

        foreach($rs['data']['default'] as $key => $val){
            $item = $rs['data']['field'];
            foreach($item as $k => $v){
                $item[$k]['value'] = $val[$v['name']];
                $item[$k]['name'] .= '[]';
            }
            $rs['form'][] = $item;
        }

        foreach($rs['data']['field'] as $key => $val){
            $rs['data']['field'][$key]['name'] .= '[]';
        }

        return ['code' => 1,'data' => $rs];

    }

    public function _mod_edit_save(){
        $rs = M('custom_make_modules')->where(['id' => I('post.id')])->field('atime,etime,ip',true)->find();
        $rs['data'] = eval(html_entity_decode($rs['data']));
        $index_data = $rs['data']['itemlist'][I('post.index')];

        //获取原始模块参数
        $modules = M('custom_modules')->where(['id' => $rs['modules_id']])->field('cfg')->find();
        $modules['cfg'] = eval(html_entity_decode($modules['cfg']));
        $modules['cfg'] = $modules['cfg']['itemlist'][I('post.index')];

        //最少必须填$min项
        $min = is_null($modules['cfg']['cfg']['min']) ? 1 : $modules['cfg']['cfg']['min'];

        $data = unserialize(html_entity_decode(I('post.data')));
        $default = array();
        foreach($data as $key => $val){
            if(is_array($val)){
                foreach($val as $i => $v){
                    $default[$i][$key] = $v;
                }
            }
        }

        $rs['data']['itemlist'][I('post.index')]['default'] = $default;

        $data = 'return '.var_export($rs['data'],true).';';

        if(M('custom_make_modules')->where(['id' => I('post.id')])->save(['data' => $data])){
            $html = $this->_modules_item_view(I('post.id'));
            return ['code' => 1,'data' => ['html' => $html,'id' => I('post.id')]];
        }

        return ['code' => 0];

    }

    /**
     * 发布装修结果
     * @param string $_POST['domain'] 域名
     * @param string $_POST['page'] 页面
     */
    public function _publish(){
        $layout = M('custom_make_layout')->where(['domain' => I('post.domain'),'page' => I('post.page')])->order('id asc')->select();

        $do = M();
        $do->startTrans();

        //清除旧数据
        $count = M('custom_publish_layout')->where(['domain' => I('post.domain'),'page' => I('post.page')])->count();
        if($count > 0){
            if(!$this->sw[] = M('custom_publish_layout')->where(['domain' => I('post.domain'),'page' => I('post.page')])->delete()){
                goto error;
            }
        }

        foreach($layout as $val){
            $modules = M('custom_make_modules')->where(['layout_id' => $val['id']])->field('id,atime,etime,ip',true)->order('id asc')->select();
            unset($val['id']);
            $val['atime']   = date('Y-m-d H:i:s');
            $val['etime']   = $val['atime'];
            $val['ip']      = get_client_ip();

            //插入布局
            if(!$this->sw[] = $insid = M('custom_publish_layout')->add($val)){
                goto error;
            }


            //格式模块数据
            if($modules) {
                foreach ($modules as $i => $v) {
                    $modules[$i]['atime'] = date('Y-m-d H:i:s');
                    $modules[$i]['etime'] = date('Y-m-d H:i:s');
                    $modules[$i]['ip'] = get_client_ip();
                    $modules[$i]['layout_id'] = $insid;
                }

                //批量插入模块内容
                if (!$this->sw[] = M('custom_publish_modules')->addAll($modules)) {
                    goto error;
                }
            }

        }


        $do->commit();
        return ['code' =>1,'data' => ['url' => '/']];

        error:
            $do->rollback();
            return ['code' => 0];
    }
}