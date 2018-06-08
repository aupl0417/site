<?php
/**
+----------------------------------------------------------------------
| RestFull API 
+----------------------------------------------------------------------
| 店铺装修
+----------------------------------------------------------------------
| Author: lazycat <673090083@qq.com>
+----------------------------------------------------------------------
*/
namespace Rest\Controller;
use Think\Controller\RestController;
class MakeController extends CommonController {
    protected $shop_id          ='';        //店铺ID
    protected $templates_id     ='';        //API接口配置
    protected $shop_info        =array();   //店铺信息
    protected $templates        =array();   //当前正在使用的模板

    public function _initialize() {
        parent::_initialize();

        //只充许从api方法入进入
        if(ACTION_NAME!='api') $this->apiReturn(1501);

        C('API_LOG',fasle); //关闭日志记录

        $tmp    = $this->_shop_info();
        if($tmp['code'] != 1) $this->apiReturn(1515,['data' =>$tmp]);   //店铺不存在！

        $this->shop_info    =   $tmp['data'];
        $this->shop_id      =   $this->shop_info['id'];
        $this->seller_id    =   $this->shop_info['uid'];

        //取当前正在使用的装修模板
        $tmp    = $this->_templates_active();
        //if($tmp['code'] != 1) $this->apiReturn(1514);   //店铺模板不存在！

        $this->templates    =   $tmp['data'];
        $this->templates_id =   $this->templates['id'];
    }

    /**
    * 请求入口
    */
    public function api(){
        //缺少要执行的方法
        if(empty($_GET['method'])) $this->apiReturn(1500);
        $this->_api(['method' => I('get.method')]);
    }

    /**
    * 各方法的必签字段
    * @param string $method     方法
    */
    public function _sign_field($method){

        $sign_field = [
            '_templates_active'                 => 'openid',    //当前正在使用的装修模板
            '_templates'                        => 'openid,templates_id',   //取模板资料
            '_templates_backgroup_save'         => 'openid,id,bgcolor,fixed,style', //取模板资料
            '_templates_box_save'               => 'openid,id,img_b,img_m,img_s,img_xs',    //单元设置
            '_pages'                            => 'openid',    //装修页面
            '_page_layout'                      => 'openid,id,make_templates_id',   //取页面布局
            '_layouts'                          => 'openid',    //可用布局
            '_cell_add'                         => 'openid,make_templates_id,page_id,id,type,col,name', //添加布局单元
            '_cell_delete'                      => 'openid,id,make_templates_id',   //删除布局单元
            '_cell_sort'                        => 'openid,make_templates_id,ids',  //布局单元排序
            '_layout'                           => 'openid,make_templates_id,page', //装修页面布局
            '_modules_menu'                     => 'openid,page_id',    //模块菜单
            '_modules_item_tpl'                 => 'openid,type,templates_id,layout_id,col_index',  //取某类型模块模板
            '_modules_item_add'                 => 'openid,make_layout_id,mod_name,show_title,col_index,type,page_id,make_templates_id,data',   //添加模块
            '_modules_item_edit'                => 'openid,id,make_layout_id,mod_name,show_title,col_index,type,page_id,make_templates_id,data',    //添加模块
            '_modules_item_delete'              => 'openid,id,make_templates_id',   //删除模块
            '_modules_item_sort'                => 'openid,ids',   //模块排序
            '_modules_item'                     => 'openid,id',  //取模块内容
            '_publish'                          => 'openid',  //发布店铺
            '_import_tpl'                       => 'openid',  //模板导入
            '_apply_market_tpl'                 => 'openid,templates_id',  //应用模板市场中的模板
            '_apply_mytemplates'                => 'openid,templates_id',  //从我的模板中应用

            '_mytemplates'                      => 'openid',    //我的模板
            '_market_templates'                 => 'openid',    //模板市场
            '_create_shop'                      => 'openid',    //开店创建店铺模板

            '_plugins_lib'                      => 'openid,make_layout_id,col_index',    //插件市场
            '_plugins_item'                     => 'openid,plugins_id', //插件
        ];
        $result=$sign_field[$method];

        return $result;
    }

    /**
    * 店铺资料
    * @param int $_POST['openid']  用户openid
    */
    public function _shop_info(){
        $status_name=['暂停营业','营业中','强制关闭'];

        $do=D('Common/ShopRelation');
        $rs=$do->relation(true)->where(['uid' => $this->uid])->field('id,atime,status,uid,shop_name,shop_logo,shop_level,about,type_id,category_id,province,city,district,city,town,street,domain,qq,mobile,tel,email,fav_num,wang,goods_num,sale_num,fraction_speed,fraction_service,fraction_desc,fraction')->find();

        if($rs) {
            $shop_type  =   $this->cache_table('shop_type');
            $area       =   $this->cache_table('area');
            $rs['province_name']    =$area[$rs['province']];
            $rs['city_name']        =$area[$rs['city']];
            $rs['district_name']    =$area[$rs['district']];
            $rs['town_name']        =$area[$rs['town']];  
    
            $rs['status_name']      =$status_name[$rs['status']];
            $rs['shop_url']         =shop_url($rs['id'],$rs['domain']);
            $rs['type_name']        =$shop_type[$rs['type_id']];
            $rs['shop_logo']        =myurl($rs['shop_logo'],100);
            
            if($rs['category_id']){
                $goods_category     =   $this->cache_table('goods_category');
                $category_id=explode(',',$rs['category_id']);
                foreach($category_id as $val){
                    $rs['category_name'][]  =   $goods_category[$val];
                }
            }

            return ['code' =>1,'data' => $rs];
        }else return ['code' => 3];
    }

    /**
    * 模板资料
    * @param string $_POST['openid']            用户openid
    * @param int    $_POST['templates_id'] 模板ID
    */
    public function _templates(){
        $rs=D('Common/ShopMakeTemplates')->where(['id' => I('post.templates_id'),'uid' => $this->uid])->field('atime,etime,ip',true)->find();

        if($rs) $result=['code' => 1,'data' => $rs];
        else $result=['code' =>3];

        return $result;
    }

    /**
    * 取当前模板
    * @param string $_POST['openid']            用户openid
    */
    public function _templates_active(){
        $rs=D('Common/ShopMakeTemplates')->where(['uid' => $this->uid,'status' => 1])->field('atime,etime,ip',true)->find();

        if($rs) {
            //模板风格
            if($rs['styles']){
                $style_tmp  =explode(',', $rs['styles']);
                foreach($style_tmp as $val){
                    $val=explode('|',$val);
                    $styles[]=['name' => $val[0],'value' => $val[1]];
                }
                $rs['styles']=$styles;
            }

            $rs['css']  = $rs['bgcolor']?'body{background-color:'.$rs['bgcolor'].' !important;}':'';

            //模板CSS
            if($rs['is_css']==1){   //是否启用自定义样式
                $tmp  = $rs['cell_is_border']?'border: 1px solid '.$rs['cell_border_color'].';':'';
                $tmp  .= $rs['cell_bgcolor']?'background-color:'.$rs['cell_bgcolor'].';':'';
                $tmp  .= $rs['cell_bgimages']?'background-image:url('.$rs['cell_bgimages'].');':'';
                $tmp  .= $rs['cell_margin_top']?'margin-top:'.$rs['cell_margin_top'].'px;':'';
                $tmp  .= $rs['cell_margin_bottom']?'margin-bottom:'.$rs['cell_margin_bottom'].'px;':'';

                $tmp2  = $rs['cell_title_bgcolor']?'background-color:'.$rs['cell_title_bgcolor'].';':'';
                $tmp2  .= $rs['cell_title_bgimages']?'background-image:url('.$rs['cell_title_bgimages'].');':'';            
                $tmp2  = $rs['cell_title_color']?'color:'.$rs['cell_title_color'].';':'';

                $tmp3  = $rs['cell_text_color']?'color:'.$rs['cell_text_color'].';':'';
                $tmp3  .= $rs['cell_text_size']?'font-size:'.$rs['cell_text_size'].'px;line-height:'.($rs['cell_text_size']+10).'px;':'';

                $rs['css'] .= $rs['cell_style'];
                $rs['css'] .= $tmp ? '.layout .col .col-item{'.$tmp.'}':'';
                $rs['css'] .= $tmp2 ? '.layout .col .col-item .col-item-title{'.$tmp2.'}':'';
                $rs['css'] .= $tmp3 ? '.layout .col .col-item .col-item-content{'.$tmp3.'}':'';
            }

            //模板背景
            if($rs['bgimages']){
                $bgimages = explode(',',$rs['bgimages']);
                if(count($bgimages) == 1) {
                    $rs['css'] .= 'body{background-image:url('.$bgimages[0].');}';
                    $rs['css'] .= $rs['fixed']==1?'body{background-size:cover;background-attachment:fixed;}':'';
                }else{
                    $rs['js']   = 'var RandBG = function () {';
                    $rs['js']   .= 'var pic=new Array();';
                    foreach($bgimages as $key=>$val){
                        $rs['js']   .='pic['.$key.']="'.$val.'";';
                    }
                    $rs['js']   .='var rand_pic=randomSort(pic);
                                    return {
                                            init: function () {
                                                $.backstretch(rand_pic, {
                                                    fade: 1000,
                                                    duration: 10000
                                                });
                                            }
                                        };
                                    }();
                                    RandBG.init();';                        
                }
            }

            //插件CSS            
            $plugins = M('shop_plugins')->where('find_in_set ("'.$rs['templates_id'].'",templates_id)')->field('path')->select();
            
            foreach($plugins as $val){
                if(file_exists('./Templates/zh_cn/plugins'.$val['path'].'/css/css.css')){
                    $rs['css'] .= file_get_contents('./Templates/zh_cn/plugins'.$val['path'].'/css/css.css');
                }
                if(file_exists('./Templates/zh_cn/plugins'.$val['path'].'/js/js.js')){
                    $rs['js'] .= file_get_contents('./Templates/zh_cn/plugins'.$val['path'].'/js/js.js');
                }
            }
            

            $result=['code' => 1,'data' => $rs];
        } else {
            $result=['code' =>3];
        }

        return $result;
    }
    
    /**
     * 为用户复制一套默认模板
     * @param integer $uid
     * @return boolean
     */
    public function copyTemplates($uid) {
        //去除默认模板
        $cacheName  =   md5('shop_make_default_template_mercury');
        $template   =   S($cacheName);
        if (!$template) {
            $template   =   M('shop_templates')->where(['status' => 1])->order('id asc')->find();
            S($cacheName, serialize($template));
        } else {
            $template   =   unserialize($template);
        }
        
        $shopId =   M('shop')->where(['uid' => $uid])->getField('id');  //获取店铺id
        
        $data   =   [
            'uid'           =>  $uid,
            'shop_id'       =>  $shopId,
            'templates_id'  =>  $template['id'],
            'tpl_name'      =>  $template['tpl_name'],
            'tpl_url'       =>  $template['tpl_url'],
            'cfg'           =>  $template['cfg'],
            'cfg_box'       =>  $template['cfg_box'],
            'images'        =>  $template['images'],
            'plugins_id'    =>  $template['plugins_id'],
            'styles'        =>  $template['styles'],
            'ip'            =>  get_client_ip(),
        ];
        $model  =   M('shop_make_templates');
        $model->startTrans();
        if (false == $model->add($data)) goto error;
        if (false == M('shop_publish_templates')->add($data)) goto error;
        
        $data['cfg']        =   unserialize($data['cfg']);
        $data['cfg_box']    =   unserialize($data['cfg_box']);
        $model->commit();
        return $data;
        
        error:
        $model->rollback();
        return false;
    }
    
    /**
    * 保存背景设置
    * @param string $_POST['openid']    用户openid
    */
    public function _templates_backgroup_save(){
        $do=D('Common/ShopMakeTemplates');

        $data               =$do->create();
        $data['bgcolor']    =I('post.bgcolor');
        $data['bgimages']   =I('post.bgimages');
        $data['fixed']      =I('post.fixed');
        $data['style']      =I('post.style');

        if(false!==$this->sw[]=$do->save($data)) return ['code' => 1];

        return ['code' => 0];
    }


    /**
    * 保存单元设置
    * @param string $_POST['openid']    用户openid
    */
    public function _templates_box_save(){
        $do=D('Common/ShopMakeTemplates');

        //$data               =$do->create();

        $data=I('post.');

        if(false!==$this->sw[]=$do->save($data)) return ['code' => 1];

        return ['code' => 0];
    }    

    /**
    * 获取可装修页面
    * @param string $_POST['openid']    用户openid
    */
    public function _pages(){
        $list=M('shop_page')->where(['id' => ['not in','3,5']])->field('id,page_name,page,type')->select();

        if($list) return ['code' =>1, 'data' =>$list];

        return ['code' => 3];
    }

    /**
    * 取页面布局
    * @param string $_POST['openid']    用户openid
    * @param int    $_POST['id']        页面ID
    * @param int    $_POST['make_templates_id'] 装修模板ID
    */
    public function _page_layout(){
        $rs=M('shop_page')->where(['id' => I('post.id')])->field('id,page_name,page,type')->find();

        $rs['layout']   =M('shop_make_layout')->where(['page_id' => $rs['id'],'make_templates_id' => I('post.make_templates_id')])->field('atime,etime,ip',true)->order('sort asc')->select();

        if($rs) return ['code' => 1,'data' =>$rs];

        return ['code' => 3];
    }


    /**
    * 布局
    */
    public function _layouts(){
        $list=M('shop_layout')->field('atime,etime,ip',true)->select();

        if($list) return ['code' => 1,'data' =>$list];

        return ['code' => 3];
    }

    /**
    * 添加布局单元
    * @param string $_POST['openid']    用户openid
    * @param int    $_POST['page_id']   页面ID
    * @param string $_POST['name']      布局标题
    * @param int    $_POST['make_templates_id'] 装修模板ID
    */
    public function _cell_add(){
        $layout = M('shop_layout')->where(['id' => I('post.id')])->field('atime,etime,ip',true)->find();

        $do=D('Common/ShopMakeLayout');

        $data['uid']                =$this->uid;
        $data['page_id']            =I('post.page_id');
        $data['layout_id']          =$layout['id'];
        $data['layout_name']        =I('post.name');
        $data['layout_type']        =$layout['type'];   
        $data['col']                =$layout['col'];
        $data['col_0']              =$layout['col_0'];
        $data['col_1']              =$layout['col_1'];
        $data['col_2']              =$layout['col_2'];
        $data['make_templates_id']  =I('post.make_templates_id');

        if(!$do->create($data)) return ['code' =>4,'msg' =>$do->getError()];

        if(!$do->add()) return ['code' => 0];

        return ['code' =>1 ];
    }

    /**
    * 删除布局单元
    * @param string $_POST['openid']    用户openid
    * @param int    $_POST['id'] 布局ID
    * @param int    $_POST['make_templates_id'] 装修模板ID
    */
    public function _cell_delete(){
        $count=M('shop_make_templates')->where(['uid' => $this->uid,'id' =>I('post.make_templates_id')])->count();

        if($count<1) return ['code' => 0];

        if(M('shop_make_layout')->where(['id' => I('post.id'),'make_templates_id' => I('post.make_templates_id'),'isys' => 0])->delete()) return ['code' =>1];

        return ['code' =>0];
    }

    /**
    * 布局单元排序
    * @param string $_POST['openid']    用户openid
    * @param int    $_POST['ids'] 布局ID
    * @param int    $_POST['make_templates_id'] 装修模板ID
    */
    public function _cell_sort(){
        $count=M('shop_make_templates')->where(['uid' => $this->uid,'id' =>I('post.make_templates_id')])->count();

        if($count<1) return ['code' => 0];

        $ids=explode(',', I('post.ids'));
        foreach($ids as $i => $val){
            M('shop_make_layout')->where(['id' => $val,'make_templates_id' => I('post.make_templates_id')])->setField('sort',$i);
        }

        return ['code' =>1];
    }

    /**
    * 取某页面布局
    * @param string $_POST['openid']    用户openid
    * @param int    $_POST['make_templates_id'] 装修模板ID
    * @param string $_POST['page']  装修页面,如:/Index/index
    */
    public function _layout(){
        $count=M('shop_make_templates')->where(['uid' => $this->uid,'id' =>I('post.make_templates_id')])->count();
        if($count<1) return ['code' => 0];

        $page_id=M('shop_page')->where(['page' => I('post.page')])->getField('id');
        if(empty($page_id)) return ['code' => 0];

        $do=D('Common/ShopMakeLayoutModulesRelation');
        $list=$do->relation(true)->where(['make_templates_id' => I('post.make_templates_id'),'_string' => 'page_id = '.$page_id.' or layout_type = 9'])->field('atime,etime,ip',true)->order('sort asc')->select();

        //dump($list);

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
    * 功能模块菜单
    * @param string $_POST['openid']    用户openid
    * @param int    $_POST['page_id']   页面ID
    */
    public function _modules_menu(){
        $map['status']  = 1;
        $map['_string'] = 'find_in_set ('.I('post.page_id').',page_id)';

        $do = M('shop_modules_category');
        $list = $do->cache(true)->where($map)->order('sort asc')->field('id,sid,category_name,ac,images')->select();
        foreach($list as $i => $val){
            $list[$i]['dlist'] = $do->cache(true)->where(['sid' => $val['id'],'status' =>1])->order('sort asc')->field('id,sid,category_name,ac,images')->select();
        }
        if($list) return ['code' =>1,'data' => $list];

        return ['code' =>3];

    }

    /**
    * 取某类型模块模板
    * @param string $_POST['openid']    用户openid
    * @param string $_POST['type']  类型
    * @param int    $_POST['templates_id'] 模板ID
    */
    public function _modules_item_tpl(){
        //取单元参数
        $cell       =M('shop_make_layout')->cache(true)->where(['id' => I('post.layout_id')])->field('col_0,col_1,col_2')->find();
        $cell_width =$cell['col_'.I('post.col_index')];

        $sid    =M('shop_modules_category')->cache(true)->where(['ac' => I('post.type')])->getField('id');
        $list   =M('shop_modules')->cache(true)->where(['status' => 1,'sid' => $sid,'_string' =>'find_in_set ('.I('post.templates_id').',templates_id) and find_in_set ("'.$cell_width.'",layout_width)'])->field('atime,etime,ip',true)->select();

        if($list) return ['code' =>1,'data' => $list];

        return ['code' =>3];
        
    }

    /** 
    * 添加模块
    * @param string $_POST['openid']    用户openid
    */
    public function _modules_item_add(){
        $do=D('Common/ShopMakeModules');
        $_POST['uid']   = $this->uid;
        if(!$do->create()) return ['code' => 4,'msg' => $do->getError()];

        //取单元参数
        $cell       =M('shop_make_layout')->cache(true)->where(['id' => I('post.make_layout_id')])->field('col_0,col_1,col_2')->find();
        $cell_width =$cell['col_'.I('post.col_index')];

        //验证数据
        $tmp = unserialize(html_entity_decode(I('post.data')));
        switch(I('post.type')){
            case 'slide':
                if(count($tmp['url'])>10) return ['code' => 1512];  //最多只能传10张图
                //链接地址格式错误！
                foreach($tmp['url'] as $i => $val){
                    if(!checkform($val,'is_url')) return ['code' =>4,'msg' => str_replace('{i}', ($i+1), C('error_code.1510'))];
                    break;
                }
            break;
            case 'links':
                if(count($tmp['url'])>20) return ['code' => 1513];  //最多只能添加20个链接
                //链接地址格式错误！
                foreach($tmp['url'] as $i => $val){
                    if(!checkform($val,'is_url')) return ['code' =>4,'msg' => str_replace('{i}', ($i+1), C('error_code.1510'))];
                    break;
                }
            break;
            case 'sale_order':
                //最多只能显示30行
                if($tmp['row'] > 30) return ['code' => 1511];
            break;
            case 'hot':
                switch ($cell_width) {
                    case '235px':
                        if($tmp['col'] >1 ) return ['code' => 4,'msg' =>'此单元格布局只能选择一列的展示方式'];
                    break;
                    
                    default:
                        if($tmp['col'] <2 ) return ['code' => 4,'msg' =>'此单元格布局请选择两列以上的展示方式'];
                    break;
                }
            break;
            case 'header':
                //店铺只能创建一个页头
                if(M('shop_make_modules')->where(['make_templates_id' => I('post.make_templates_id'),'type' => 'header'])->field('id')->find()) return ['code' => 1516 ];
            break;
            case 'menu':
                //店铺只能创建一个主菜单
                if(M('shop_make_modules')->where(['make_templates_id' => I('post.make_templates_id'),'type' => 'menu'])->field('id')->find()) return ['code' => 1517 ];
            break;

        }
        //if($tmp['plugins_id'])  $data['plugins_id'] = $tmp['plugins_id'];
        if(!$do->add()) return ['code' => 0];

        $data   = [
            'id'                =>$do->getLastInsID(),
            'make_layout_id'    =>I('post.make_layout_id'),
            'mod_name'          =>I('post.mod_name'),
            'show_title'        =>I('post.show_title'),
            'col_index'         =>I('post.col_index'),
            'type'              =>I('post.type'),
            'page_id'           =>I('post.page_id'),
            'make_templates_id' =>I('post.make_templates_id'),
        ];
        

        $data['html']   = '<div class="col-sort" data-id="'.$data['id'].'">'.$this->_modules_item_view($data['id']).'</div>';

        return ['code' => 1,'data' =>$data];
    }

    /** 
    * 修改模块
    * @param string $_POST['openid']    用户openid
    */
    public function _modules_item_edit(){
        $do=D('Common/ShopMakeModules');
        $_POST['uid']   = $this->uid;
        if(!$do->create()) return ['code' => 4,'msg' => $do->getError()];

        //取单元参数
        $cell       =M('shop_make_layout')->cache(true)->where(['id' => I('post.make_layout_id')])->field('col_0,col_1,col_2')->find();
        $cell_width =$cell['col_'.I('post.col_index')];

        //验证数据
        $tmp = unserialize(html_entity_decode(I('post.data')));
        switch(I('post.type')){
            case 'slide':
                if(count($tmp['url'])>10) return ['code' => 1512];  //最多只能传10张图
                //链接地址格式错误！
                foreach($tmp['url'] as $i => $val){
                    if(!checkform($val,'is_url')) return ['code' =>4,'msg' => str_replace('{i}', ($i+1), C('error_code.1510'))];
                    break;
                }
            break;
            case 'links':
                if(count($tmp['url'])>20) return ['code' => 1513];  //最多只能添加20个链接
                //链接地址格式错误！
                foreach($tmp['url'] as $i => $val){
                    if(!checkform($val,'is_url')) return ['code' =>4,'msg' => str_replace('{i}', ($i+1), C('error_code.1510'))];
                    break;
                }
            break;
            case 'sale_order':
                //最多只能显示30行
                if($tmp['row'] > 30) return ['code' => 1511];
            break;
            case 'hot':
                switch ($cell_width) {
                    case '235px':
                        if($tmp['col'] >1 ) return ['code' => 4,'msg' =>'此单元格布局只能选择一列的展示方式'];
                    break;
                    
                    default:
                        if($tmp['col'] <2 ) return ['code' => 4,'msg' =>'此单元格布局请选择两列以上的展示方式'];
                    break;
                }

            break;
        }

        if(!$do->save()) return ['code' => 0];

        $data   = [
            'id'                =>I('post.id'),
            'make_layout_id'    =>I('post.make_layout_id'),
            'mod_name'          =>I('post.mod_name'),
            'show_title'        =>I('post.show_title'),
            'col_index'         =>I('post.col_index'),
            'type'              =>I('post.type'),
            'page_id'           =>I('post.page_id'),
            'make_templates_id' =>I('post.make_templates_id'),
        ];

        $data['html']   = $this->_modules_item_view($data['id']);

        return ['code' => 1,'data' =>$data];
    }



    /**
    * 删除模块
    * @param string $_POST['openid']    用户openid
    * @param int    $_POST['id']        模块ID
    * @param int    $_POST['make_templates_id'] 装修模板ID
    */
    public function _modules_item_delete(){
        $do=M('shop_make_modules');
        if($do->where(['uid' =>$this->uid,'id' =>I('post.id'),'make_templates_id' => I('post.make_templates_id'),'isys' => 0])->delete()) return ['code' =>1];

        return ['code' =>0];
    }

    /**
    * 获取模块内容
    * @param string $_POST['openid']    用户openid
    * @param int    $_POST['id']        模块ID
    */
    public function _modules_item(){
        $data=M('shop_make_modules')->where(['uid' => $this->uid,'id' => I('post.id')])->field('atime,etime,ip',true)->find();

        $data['data'] = unserialize(html_entity_decode($data['data']));
        $cfg=$data['data'];
        switch($data['type']){
            case 'slide':   //轮播图
                $ads = [];
                foreach($cfg['images'] as $i => $val){
                    $ads[]  = [
                        'title'     => $cfg['title'][$i],
                        'url'       => $cfg['url'][$i],
                        'images'    => $val
                    ];
                }
                $data['ads']    = $ads;
            break;
            case 'links':   //友情链接
                $links = [];
                foreach($cfg['title'] as $i => $val){
                    $links[]  = [
                        'title'     => $cfg['title'][$i],
                        'url'       => $cfg['url'][$i],
                        'images'    => $cfg['images'][$i]
                    ];
                }
                $data['links']    = $links;
            break;
            case 'hot': //热卖宝贝
                if($cfg['goods_id']){   //自定义宝贝
                    $data['goods'] = M('goods')->where(['id' => ['in',$cfg['goods_id']],'seller_id' => $this->uid])->field('id,images,goods_name,price')->order('find_in_set (id,"'.implode(',',$cfg['goods_id']).'")')->select();
                }
            break;
            case 'plugins_lib':
                $goods_id=array_unique($cfg['goods_id']);
                if(false!== $key = array_search('', $goods_id)){
                    array_splice($goods_id, $key, 1);
                }

                if($goods_id){
                    $do=D('Common/GoodsRelation');
                    $goods_tmp = $do->relation('attr_list')->relationLimit('goods_attr_list',1)->relationField('goods_attr_list','id,images,price')->where(['id' => ['in',$goods_id]])->field('id,shop_id,goods_name,price,score_ratio,(price*score_ratio*100) as score,sale_num,fav_num,rate_num')->select();
                    foreach($goods_tmp as $val){
                        $goods[$val['id']]  = $val;
                    }
                }

                foreach($cfg['goods_id'] as $key => $val){
                    $item[$key]['target']       = '_blank';
                    $item[$key]['subject']      = $cfg['subject'][$key];
                    $item[$key]['sub_subject']  = $cfg['sub_subject'][$key];                    
                    $item[$key]['goods']        = $goods[$cfg['goods_id'][$key]];
                    $item[$key]['pic']          = $cfg['pic'][$key];
                }

                //$data['plugins_items'] = $item;

                $plugins = M('shop_plugins')->cache(true)->where(['id' => $data['plugins_id']])->field('id,setting')->find();
                $plugins = unserialize(html_entity_decode($plugins['setting']));

                $n=0;
                foreach ($plugins as $key => $val) {                    
                    foreach ($val['item'] as $k => $v) {
                        for($i = 0; $i < $v['num'];$i++){
                            $plugins[$key]['item'][$k]['item'][] = $item[$n];
                            $n++;
                        }
                    }
                }
                

                $data['plugins']=$plugins;


                $tpl_url = './Templates/zh_cn/plugins'.$cfg['tpl_path'].$cfg['tpl_url'];
            break;             
        }


        if($data) return ['code' =>1,'data' => $data];
        return ['code' => 0];
    }

    /**
    * 模块排序
    * @param string $_POST['openid']    用户openid
    * @param int    $_POST['ids'] 布局ID
    * @param int    $_POST['make_templates_id'] 装修模板ID
    */
    public function _modules_item_sort(){

        $ids=explode(',', I('post.ids'));
        foreach($ids as $i => $val){
            M('shop_make_modules')->where(['id' => $val,'uid' => $this->uid])->setField('sort',$i);
        }

        return ['code' =>1];
    }    

    /**
    * 输出模块 ,$id和$data两项必传一项
    * @param int $id        模块id
    * @param array $data    模块数据
    */
    public function _modules_item_view($id='',$data=''){
        if($id=='' && $data=='') return ['code' =>0];

        if($data==''){
            $data = M('shop_make_modules')->where(['id' => $id])->field('atime,etime,ip',true)->find();
        }
        if(empty($data)) return ['code' => 3];

        $tpl=M('shop_make_templates')->cache(true)->where(['id' => $data['make_templates_id']])->field('tpl_url,cfg_box')->find();
        $tpl['cfg_box'] = unserialize(html_entity_decode($tpl['cfg_box']));

        $data['data'] = unserialize(html_entity_decode($data['data']));
        $cfg=$data['data'];      

        switch($data['type']){
            case 'slide':   //轮播图
                $ads = [];
                foreach($cfg['images'] as $i => $val){
                    $ads[]  = [
                        'title'     => $cfg['title'][$i],
                        'url'       => $cfg['url'][$i],
                        'images'    => $val
                    ];
                }
                $data['ads']    = $ads;
            break;
            case 'links':   //友情链接
                $links = [];
                foreach($cfg['title'] as $i => $val){
                    $links[]  = [
                        'title'     => $cfg['title'][$i],
                        'url'       => $cfg['url'][$i],
                        'images'    => $cfg['images'][$i]
                    ];
                }
                $data['links']    = $links;
            break;            
            case 'shop_info':   //店铺信息
                
            break;
            case 'sale_order':  //宝贝排行
                $do=D('Common/GoodsRelation');
                $map['seller_id']   =   $this->uid;
                $map['num']         =   ['gt',0];
                $map['status']      =   1;

                if($cfg['keyword']) $map['goods_name'] = ['like' , '%'.trim($cfg['keyword']).'%'];

                if($cfg['category_id']){
                    $ids = sortid(['table' => 'shop_goods_category','sid' => $cfg['category_id']]);
                    $tmp = [];
                    foreach($ids as $val){
                        $tmp[] = 'find_in_set ('.$val.',shop_category_id)';
                    }
                    $map['_string'] = implode(' or ',$tmp);
                }

                if(max($cfg['s_price'],$cfg['e_price'])>0){
                    if(empty($cfg['s_price'])) $cfg['s_price'] = 0;
                    if(empty($cfg['e_price'])) $cfg['e_price'] = 100000000;
                    $map['price']   =   ['between',[$cfg['s_price'],$cfg['e_price']]];
                }

                $limit              =   intval($cfg['row'])?intval($cfg['row']):5;
                $result['sale']     =   $do->relation('attr_list')->relationLimit('goods_attr_list',1)->relationField('goods_attr_list','id,images,price')->where($map)->field('id,shop_id,goods_name,price,sale_num,fav_num')->limit($limit)->order('sale_num desc')->select();

                
                $result['fav']     =   $do->relation('attr_list')->relationLimit('goods_attr_list',1)->relationField('goods_attr_list','id,images,price')->where($map)->field('id,shop_id,goods_name,price,sale_num,fav_num')->limit($limit)->order('fav_num desc')->select();

                $result=imgsize_list($result,'images',80);

                $this->assign('sale_order',$result);
            break;

            case 'search':  //搜索
            case 'category':    //分类
                $shop_category = get_category(['table' => 'shop_goods_category','field' => 'id,sid,category_name','level' => 2,'sql' => 'uid='.$this->uid,'cache_name' => 'shop_category_'.$this->uid,'cache_time' => C('CACHE_LEVEL.S')]);

                $this->assign('shop_category',$shop_category);            
            break;
            case 'hot': //推荐宝贝
                $col = [2 => 'ccol-50',3 => 'ccol-33',4 => 'ccol-25',5 => 'ccol-20'];
                $do=D('Common/GoodsRelation');

                if($cfg['goods_id'] && $cfg['set_goods']==1){   //自定义宝贝
                    $result['list'] = $do->relation('attr_list')->relationLimit('goods_attr_list',1)->relationField('goods_attr_list','id,images,price')->where(['id' => ['in',$cfg['goods_id']],'seller_id' => $this->uid])->field('id,shop_id,goods_name,price,score_ratio,(price*score_ratio*100) as score,sale_num,fav_num,rate_num')->order('find_in_set (id,"'.implode(',',$cfg['goods_id']).'")')->select();

                }else{
                    
                    $map['seller_id']   =   $this->uid;
                    $map['num']         =   ['gt',0];
                    $map['status']      =   1;

                    if($cfg['keyword']) $map['goods_name'] = ['like' , '%'.trim($cfg['keyword']).'%'];

                    if($cfg['category_id']){
                        $ids = sortid(['table' => 'shop_goods_category','sid' => $cfg['category_id']]);
                        $tmp = [];
                        foreach($ids as $val){
                            $tmp[] = 'find_in_set ('.$val.',shop_category_id)';
                        }
                        $map['_string'] = implode(' or ',$tmp);
                    }

                    if(max($cfg['s_price'],$cfg['e_price'])>0){
                        if(empty($cfg['s_price'])) $cfg['s_price'] = 0;
                        if(empty($cfg['e_price'])) $cfg['e_price'] = 100000000;
                        $map['price']   =   ['between',[$cfg['s_price'],$cfg['e_price']]];
                    }

                    $limit              =   $cfg['row'] * $cfg['col'];
                    $result['list']     =   $do->relation('attr_list')->relationLimit('goods_attr_list',1)->relationField('goods_attr_list','id,images,price')->where($map)->field('id,shop_id,goods_name,price,score_ratio,(price*score_ratio*100) as score,sale_num,fav_num,rate_num')->limit($limit)->order($cfg['order'])->select();

                }

                $result=imgsize_list($result,'images',$this->templates[$cfg['imgsize']]);

                $result['other']    = [
                    'imgsize'       => $this->templates[$cfg['imgsize']],
                    'count'         => count($result['list']),
                    'col'           => $col[$cfg['col']],
                ];


                $this->assign('list',$result);
            break;            

            case 'plist': //宝贝列表
                $col = [2 => 'ccol-50',3 => 'ccol-33',4 => 'ccol-25',5 => 'ccol-20'];

                $map['seller_id']   =   $this->uid;
                $map['num']         =   ['gt',0];
                $map['status']      =   1;

                $pagesize           =   $cfg['row'] * $cfg['col'];
                $result['list']     =  pagelist([
                        'table'             => 'Common/GoodsRelation',
                        'do'                => 'D',
                        'pagesize'          => $pagesize,
                        'map'               => $map,
                        'fields'            => 'id,shop_id,goods_name,price,score_ratio,(price*score_ratio*100) as score,sale_num,fav_num,rate_num',
                        'order'             => $cfg['order'],
                        'relation'          => 'attr_list',
                        'relationLimit'     => ['goods_attr_list',1],
                        'relationField'     => ['goods_attr_list','id,images,price'],
                        'action'            => '/index/goods',
                    ]); 

               
                $result=imgsize_list($result,'images',$this->templates[$cfg['imgsize']]);

                $result['other']    = [
                    'imgsize'       => $this->templates[$cfg['imgsize']],
                    'count'         => count($result['list']),
                    'col'           => $col[$cfg['col']],
                ];

                $this->assign('list',$result);
            break;  
            case 'rate':
                $rate_name=[0 => '中评',1 => '好评','-1' => '差评'];
                $status_name=['未生效','已生效'];

                $map['seller_id']     =   $this->uid;
                $map['status']  =   1;

                $pagesize=20;
                $pagelist=pagelist(array(
                        'table'     => 'OrdersGoodsCommentRelation',
                        'do'        => 'D',
                        'map'       => $map,
                        'order'     => 'id desc',
                        'fields'    => 'id,atime,status,like_num,s_no,orders_goods_id,goods_id,shop_id,attr_list_id,uid,seller_id,rate,reply_count,content,images,is_anonymous',
                        'pagesize'  => $pagesize,
                        'relation'  => true,
                        'action'    => '/index/rate',
             
                    ));

                $pagelist   =   imgsize_list($pagelist,['images','face'],[80,60]);

                $this->assign('rate',$pagelist);
            break;
            case 'header':
                if($data['data']['banner']) $data['data']['banner'] = myurl($data['data']['banner'],1200,100);

                $data['data']['header_style'] = $cfg['bgimages']?'background-image:url('.$cfg['bgimages'].') !important;':'';
                $data['data']['header_style'] .= $cfg['bgcolor']?'background-color:'.$cfg['bgcolor'].' !important;':'';
            break;
            case 'menu':
                if($cfg['category_id']){
                    $category   = M('shop_goods_category')->where(['id' => ['in',$cfg['category_id']]])->field('id,category_name')->select();
                    $this->assign('category',$category);
                }

                $data['data']['header_style'] = $cfg['bgimages']?'background-image:url('.$cfg['bgimages'].') !important;':'';
                $data['data']['header_style'] .= $cfg['bgcolor']?'background-color:'.$cfg['bgcolor'].' !important;':'';
            break;  
            case 'plugins_lib':
                $goods_id=array_unique($cfg['goods_id']);
                if(false!== $key = array_search('', $goods_id)){
                    array_splice($goods_id, $key, 1);
                }

                if($goods_id){
                    $do=D('Common/GoodsRelation');
                    $goods_tmp = $do->relation('attr_list')->relationLimit('goods_attr_list',1)->relationField('goods_attr_list','id,images,price')->where(['id' => ['in',$goods_id]])->field('id,shop_id,goods_name,price,score_ratio,(price*score_ratio*100) as score,sale_num,fav_num,rate_num')->select();
                    foreach($goods_tmp as $val){
                        $goods[$val['id']]  = $val;
                    }
                }

                foreach($cfg['goods_id'] as $key => $val){
                    $item[$key]['target']       = '_blank';
                    $item[$key]['subject']      = $cfg['subject'][$key];
                    $item[$key]['sub_subject']  = $cfg['sub_subject'][$key];                    
                    $item[$key]['goods']        = $goods[$cfg['goods_id'][$key]];
                    $item[$key]['pic']          = $cfg['pic'][$key]?$cfg['pic'][$key]:$item[$key]['goods']['attr_list'][0]['images'];
                    if(empty($item[$key]['pic']))   $item[$key]['is_null']  =1;
                }

                //$data['plugins_items'] = $item;

                $plugins = M('shop_plugins')->cache(true)->where(['id' => $data['plugins_id']])->field('id,setting')->find();
                $plugins = unserialize(html_entity_decode($plugins['setting']));

                $n=0;
                foreach ($plugins as $key => $val) {                    
                    foreach ($val['item'] as $k => $v) {
                        for($i = 0; $i < $v['num'];$i++){
                            $item[$n]['pic']    = myurl($item[$n]['pic'],$v['width'],$v['height']);
                            if($item[$n]['goods']){
                                $item[$n]['url'] = DM('item','/goods/'.$item[$n]['goods']['attr_list'][0]['id']);
                            }else $item[$n]['url'] = '#';
                            $plugins[$key]['item'][$k]['item'][] = $item[$n];
                            $n++;
                        }
                    }
                }
                

                $data['plugins']=$plugins;


                $tpl_url = './Templates/zh_cn/plugins'.$cfg['tpl_path'].$cfg['tpl_url'];
            break;          
        }

        //自定样式
        $data['css']['content']     =$cfg['content_padding']== -1?'':'padding:'.$cfg['content_padding'].'px;';

        $data['css']['box']         =$cfg['height']>50?'height:'.$cfg['height'].'px;overflow:hidden;':'';
        $data['css']['box']         .= $data['data']['header_style'];


        if($cfg['is_setting']==1){
            $data['css']['box']   .=$cfg['is_border']==0?'border:0;':'';
            $data['css']['box']   .=$cfg['bgcolor']?'background-color:'.$cfg['bgcolor'].' !important;':'';
            $data['css']['box']   .=$cfg['transparent']?'background-color: transparent !important;':'';
            $data['css']['box']   .=$cfg['bgimages']?'background-image:url('.$cfg['bgimages'].') !important;':'';
            $data['css']['box']   .=$cfg['text_color']?'color:'.$cfg['text_color'].';':'';
            $data['css']['box']   .=$cfg['margin_top']!=-1?'margin-top:'.$cfg['margin_top'].'px;':'';
            $data['css']['box']   .=$cfg['margin_bottom']!=-1?'margin-bottom:'.$cfg['margin_bottom'].'px;':'';

            $data['css']['box']   .=$cfg['show_title']?'border-bottom: 1px solid '.$cfg['border_color'].';':'';
            $data['css']['box']   .=$cfg['title_color']?'color:'.$cfg['title_color'].';':'';
            $data['css']['box']   .=$cfg['title_bgcolor']?'background-color:'.$cfg['title_bgcolor'].';':'';
            $data['css']['box']   .=$cfg['title_bgimages']?'background-image:url('.$cfg['title_bgimages'].');':'';

            $data['css']['box']   .=$cfg['style']?$cfg['style']:'';
        }
        


        $this->assign('rs',$data);
        $this->assign('seller_id',$this->uid);

        $this->assign('shop_info',$this->shop_info);

        $tpl_url=$tpl_url?$tpl_url:'.'.$tpl['tpl_url'].'/'.$cfg['tpl_url'];
        $html=$this->fetch($tpl_url);


        return $html;
    }


    /**
    * 装修完成后发布
    * @param string $_POST['openid']    用户openid
    */
    public function _publish(){
        $templates  = M('shop_make_templates')->where(['id' => $this->templates_id])->field('id',true)->find();

        $do=M();
        $do->startTrans();

        //删除旧模板
        if(false === M('shop_publish_templates')->where(['uid' => $this->uid])->delete()) goto error;
        if(false === M('shop_publish_layout')->where(['uid' => $this->uid])->delete()) goto error;
        if(false === M('shop_publish_modules')->where(['uid' => $this->uid])->delete()) goto error;

        if(!$this->sw[] = $templates_id= M('shop_publish_templates')->add($templates)) goto error;

        $list = M('shop_make_layout')->where(['make_templates_id' => $this->templates_id])->select();
        foreach($list as $val){
            $modules    =   M('shop_make_modules')->where(['make_layout_id' => $val['id']])->field('id',true)->select();

            $val['make_templates_id']   =   $templates_id;
            unset($val['id']);
            if(!$this->sw[] = $layout_id =   M('shop_publish_layout')->add($val)) goto error;

            foreach($modules as $v){
                $v['make_templates_id'] = $templates_id;
                $v['make_layout_id']    = $layout_id;

                if(!$this->sw[] =   M('shop_publish_modules')->add($v)) goto error;
            }
        }

        $do->commit();
        //清除缓存
        S('shop_init_'.md5(str_replace(array('http://','https://'),'',$this->shop_info['shop_url'])),null);

        return ['code' => 1,'data' => ['shop_url' => $this->shop_info['shop_url']]];

        error:
            $do->rollback();
            return ['code' => 0,'data' =>implode(',',$this->sw)];
    }

    /**
    * 模板导入
    * @param string $_POST['openid']    用户openid
    */
    public function _import_tpl(){
        $templates  = M('shop_make_templates')->where(['id' => $this->templates_id])->field('id',true)->find();
        $templates['tpl_name']          .='_copy';
        $Templates['templatesid_copy']  = $this->templates['templates_id'];
        $templates['status']            =0;

        $do=M();
        $do->startTrans();


        if(!$this->sw[] = $templates_id= M('shop_templates')->add($templates)) goto error;
        $tpl_url        = '/Templates/zh_cn/'.$templates_id;
        if(!$this->sw[] = M('shop_templates')->where(['id' => $templates_id])->save(['tpl_url'=>$tpl_url])) goto error;

        $list = M('shop_make_layout')->where(['make_templates_id' => $this->templates_id])->select();
        foreach($list as $val){
            $modules    =   M('shop_make_modules')->where(['make_layout_id' => $val['id']])->field('id',true)->select();

            $val['make_templates_id']   =   $templates_id;
            unset($val['id']);
            if(!$this->sw[] = $layout_id =   M('shop_lib_layout')->add($val)) goto error;

            foreach($modules as $v){
                $v['make_templates_id'] = $templates_id;
                $v['make_layout_id']    = $layout_id;

                if(!$this->sw[] =   M('shop_lib_modules')->add($v)) goto error;
            }
        }

        //复制模块
        $modules_lib=M('shop_modules')->where(['templates_id' => $this->templates['templates_id']])->field('id',true)->select();
        foreach($modules_lib as $i => $val){
            $modules_lib[$i]['templates_id']    = $templates_id;
        }

        if(!M('shop_modules')->addAll($modules_lib)) goto error;

        $do->commit();

        $dir=new \Org\Util\Dir();
        $dir->copyDir('.'.$templates['tpl_url'],'.'.$tpl_url);

        return ['code' => 1,'data' => ['shop_url' => $this->shop_info['shop_url']]];

        error:
            $do->rollback();
            return ['code' => 0,'data' =>implode(',',$this->sw)];
    }    

    /**
    * 我的模板
    * @param string $_POST['openid']    用户openid
    */
    public function _mytemplates(){
        $do = M('shop_make_templates');

        $list = $do->where(['uid' => $this->uid])->field('id,atime,status,tpl_name,price,images')->order('id desc')->select();

        if($list){
            $list = imgsize_list($list,'images',220,300,2,'',1);
            return ['code' =>1,'data' => $list];
        }

        return ['code' => 3];

    }

    /**
    * 模板市场
    * @param string $_POST['openid']    用户openid
    */
    public function _market_templates(){
        $map['status']  = 1;
        $pagelist = pagelist([
                'table'         =>'shop_templates',
                'map'           =>$map,
                'pagesize'      =>24,
                'fields'        =>'id,atime,tpl_name,price,images',
                'action'        =>'/Index/index',
            ]);

        if($pagelist['list']){
            $pagelist = imgsize_list($pagelist,'images',220,300,2,'',1);
            return ['code' =>1,'data' => $pagelist];
        }

        return ['code' =>3];
    }

    /**
    * 应用模板市场中模板
    * @param string $_POST['openid']        用户openid
    * @param int    $_POST['templates_id']  模板ID
    */
    public function _apply_market_tpl($is_create=''){
        $templates  = M('shop_templates')->where(['id' => I('post.templates_id')])->field('id',true)->find();
        if(!$templates) return ['code' => 3]; //模板不存在

        //判断模板是否已在使用中
        if(M('shop_make_templates')->where(['uid'=>$this->uid,'templates_id' => I('post.templates_id')])->field('id')->find()) return ['code' => 1518];


        $templates['templates_id']  = I('post.templates_id');
        $templates['uid']           = $this->uid;
        $templates['shop_id']       = $this->shop_id;
        $templates['status']        = 1;

        $data = D('Common/ShopMakeTemplates2')->create($templates);
        $data['cfg']    = html_entity_decode($data['cfg']);
        $data['cfg_box']= html_entity_decode($data['cfg_box']);

        $do=M();
        $do->startTrans();
        
        //如果用户没有模板则为创建
        if (M('shop_make_templates')->where(['uid' => $this->uid,'status' =>1])->find() == false) {
            $is_create  =   1;
        }
        if($is_create!=1) if(!$this->sw[] = M('shop_make_templates')->where(['uid' => $this->uid,'status' =>1])->setField('status',0)) goto error;
        
        if(!$this->sw[] = $templates_id= M('shop_make_templates')->add($data)) goto error;
        $list = M('shop_lib_layout')->where(['make_templates_id' => I('post.templates_id')])->select();
        foreach($list as $val){
            $modules    =   M('shop_lib_modules')->where(['make_layout_id' => $val['id']])->field('id',true)->select();

            $val['make_templates_id']   =   $templates_id;
            $val['uid']                 =   $this->uid;
            unset($val['id']);
            if(!$this->sw[] = $layout_id =   M('shop_make_layout')->add($val)) goto error;
            if($modules){
                foreach($modules as $i => $v){
                    $modules[$i]['make_templates_id'] = $templates_id;
                    $modules[$i]['make_layout_id']    = $layout_id;
                    $modules[$i]['uid']               = $this->uid;                
                }
                if(!$this->sw[] =   M('shop_make_modules')->addAll($modules)) goto error;
            }
        }

        $do->commit();
        return ['code' => 1,'data' => ['shop_url' => $this->shop_info['shop_url'],'templates_id' => $templates_id]];

        error:
            $do->rollback();
            return ['code' => 0,'data' =>implode(',',$this->sw)];     
    }

    /**
    * 从我的模板中应用
    * @param string $_POST['openid']        用户openid
    * @param int    $_POST['templates_id']  模板ID
    */

    public function _apply_mytemplates(){
        $do=M('shop_make_templates');
        $do->startTrans();

        if(false=== $do->where(['uid' => $this->uid,'status' =>1,'id' => ['neq',I('post.templates_id')]])->setField('status',0)) goto error;
        if(false=== $do->where(['uid' => $this->uid,'id' => I('post.templates_id')])->setField('status',1)) goto error;

        $do->commit();
        return ['code' => 1,'data' => ['shop_url' => $this->shop_info['shop_url']]];

        error:
            $do->rollback();
            return ['code' => 0]; 
    }

    /**
    * 开店时创建店铺
    * @param string $_POST['openid']        用户openid   
    */
    public function _create_shop(){
        $_POST['templates_id'] = 100445752; //默认使用ID为100445752的模板
        
        if($tpl=M('shop_make_templates')->where(['uid' => $this->uid,'status' => 1])->field('id')->find()){
            $this->templates_id = $tpl['id'];   
        }else{
            $res = $this->_apply_market_tpl(1);
            if($res['code']!=1) return $res;
            $this->templates_id = $res['data']['templates_id'];            
        }

        if(M('shop_publish_templates')->where(['uid' => $this->uid])->field('id')->find()){
            //模板已存在，不可再次创建
            return ['code' => 1519];
        }else{
           $res = $this->_publish();
           return $res;
        }

    }

    /**
    * 可用插件列表
    * @param int    $_POST['make_layout_id']    布局ID
    */
    public function _plugins_lib(){
        //取单元参数
        $cell       =M('shop_make_layout')->cache(true)->where(['id' => I('post.make_layout_id')])->field('col_0,col_1,col_2')->find();
        $cell_width =$cell['col_'.I('post.col_index')];

        $map['status']          =1;
        $map['layout_width']    = $cell_width;
        $map['_string']         = 'find_in_set ('.$this->templates['templates_id'].',templates_id)';
        $list   = M('shop_plugins')->where($map)->field('atime,etime,ip',true)->order('id desc')->select();

        if($list){
            $list = imgsize_list($list,'images',300,200,2,'',1);
            return ['code' =>1,'data' =>$list];
        }

        return ['code' =>3];
    }

    /**
    * 插件详情
    * @param int $_POST['plugins_id']   插件ID
    */
    public function _plugins_item(){
        $rs = M('shop_plugins')->cache(true)->where(['id' => $_POST['plugins_id']])->field('atime,etime,ip',true)->find();

        if($rs){
            $rs['setting'] = unserialize(html_entity_decode($rs['setting']));
            return ['code' => 1,'data' => $rs];
        }

        return ['code' =>3];
    }

}