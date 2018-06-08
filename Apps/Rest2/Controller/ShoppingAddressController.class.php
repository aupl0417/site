<?php
/**
 * ----------------------------------------------------------
 * RestFull API V2.0
 * ----------------------------------------------------------
 * 买家收货地址管理
 * ----------------------------------------------------------
 * Author:Lazycat <673090083@qq.com>
 * ----------------------------------------------------------
 * 2017-01-16
 * ----------------------------------------------------------
 */
namespace Rest2\Controller;
use Think\Controller\RestController;
class ShoppingAddressController extends ApiController {
    protected $action_logs = array('add','edit','delete','set_default');

    /**
     * subject: 买家收货地址列表
     * api: /ShoppingAddress/address
     * author: Lazycat
     * day: 2017-01-16
     *
     * [字段名,类型,是否必传,说明]
     * param: openid,string,1,用户openid
     * param: pagesize,int,0,每页读取记录数量，默认12条
     */
    public function address(){
        $this->check('openid,sign',false);

        $res = $this->_address($this->post);
        $this->apiReturn($res);
    }

    public function _address($param=null){
        $map['uid'] = $this->user['id'];
        $pagesize = $param['pagesize'] ? $param['pagesize'] : 10;
        $list = pagelist(array(
            'table'     => 'shopping_address',
            'map'       => $map,
            'fields'    => 'id,atime,linkname,mobile,tel,postcode,province,city,district,town,street,is_default',
            'pagesize'  => $pagesize,
            'order'     => 'is_default desc',
            'p'         => $param['p']
        ));

        if($list['list']){
            $area = $this->cache_table('area');
            //格式化数据
            foreach($list['list'] as $key => $val){
                $list['list'][$key]['province']     = $area[$val['province']];
                $list['list'][$key]['city']         = $area[$val['city']];
                $list['list'][$key]['district']     = $area[$val['district']];
                $list['list'][$key]['town']         = $area[$val['town']];

                $list['list'][$key]['default']      = $val['is_default'] == 1 ? '默认' : '';
            }
            return ['code' => 1,'data' => $list];
        }

        return ['code' => 3,'msg' => '找不到记录！'];
    }

    /**
     * subject: 添加收货地址
     * api: /ShoppingAddress/add
     * author: Lazycat
     * day: 2017-01-16
     *
     * [字段名,类型,是否必传,说明]
     * param: openid,string,1,用户openid
     * param: linkname,string,1,联系人姓名
     * param: mobile,string,0,手号码，电话和手机号必须填一项
     * param: tel,string,0,电话号码
     * param: province,int,1,省份ID
     * param: city,int,1,城市ID
     * param: district,int,1,区县ID
     * param: town,int,0,街道ID
     * param: postcode,string,0,邮编
     * param: is_default,int,0,是否设为默认
     * param: street,string,1,详细地址
     */
    public function add(){
        $field = ['linkname','province','city','district','street'];
        if(empty($this->post['tel'])) $field[] = 'mobile';
        $this->check($field);
        $res = $this->_add($this->post);
        $this->apiReturn($res);
    }

    public function _add($param=null){
        $param['uid'] = $this->user['id'];
        $do=D('Common/ShoppingAddress');

        if(!$data=$do->create($param)) return ['code' => 4,'msg' => $do->getError()];
        if($do->where($data)->find()) return ['code' => 4,'msg' => '地址已存在！'];
        if($insid = $do->add($data)){
            if($param['is_default'] == 1){
                $do->where(['uid' => $param['uid'],'id' => ['neq',$insid]])->setField('is_default',0);
            }else $this->_set_default();
            return ['code' => 1,'id' => $insid];
        }

        return ['code' => 0,'msg' => '保存失败！'];
    }

    /**
     * subject: 修改收货地址
     * api: /ShoppingAddress/edit
     * author: Lazycat
     * day: 2017-01-17
     *
     * [字段名,类型,是否必传,说明]
     * param: openid,string,1,用户openid
     * param: linkname,string,1,联系人姓名
     * param: mobile,string,0,手号码，电话和手机号必须填一项
     * param: tel,string,0,电话号码
     * param: province,int,1,省份ID
     * param: city,int,1,城市ID
     * param: district,int,1,区县ID
     * param: town,int,0,街道ID
     * param: postcode,string,0,邮编
     * param: is_default,int,0,是否设为默认
     * param: id,int,1,收货地址ID
     * param: street,string,1,详细地址
     */
    public function edit(){
        $field = ['linkname','province','city','district','street','id'];
        if(empty($this->post['tel'])) $field[] = 'mobile';
        $this->check($field);

        if($this->post['mobile']) {
            if(checkform($this->post['mobile'],'is_mobile') == false) $this->apiReturn(['code' => 4,'msg' => '手机号码格式错误！']);
        }
        if($this->post['tel']) {
            if(checkform($this->post['tel'],'is_phone') == false) $this->apiReturn(['code' => 4,'msg' => '电话号码格式错误！']);
        }
        if($this->post['postcode']) {
            if(checkform($this->post['postcode'],'is_zip') == false) $this->apiReturn(['code' => 4,'msg' => '邮政编码格式错误！']);
        }

        $res = $this->_edit($this->post);
        $this->apiReturn($res);
    }

    public function _edit($param=null){
        $param['uid'] = $this->user['id'];
        $do=D('Common/ShoppingAddress');

        if(!$data=$do->create($param)) return ['code' => 4,'msg' => $do->getError()];
        if($do->where($data)->find()) return ['code' => 4,'msg' => '地址已存在！'];

        if($do->save($data)){
            if($param['is_default'] == 1){
                $do->where(['uid' => $param['uid'],'id' => ['neq',$param['id']]])->setField('is_default',0);
            }else $this->_set_default();
            return ['code' => 1,'id' => $insid];
        }

        return ['code' => 0,'msg' => '保存失败！'];
    }


    /**
     * subject: 删除收货地址
     * api: /ShoppingAddress/delete
     * author: Lazycat
     * day: 2017-01-16
     *
     * [字段名,类型,是否必传,说明]
     * param: openid,string,1,用户openid
     * param: id,int,1,收货地址ID
     */
    public function delete(){
        $field = 'openid,id';
        $this->check($field);

        $res = $this->_delete($this->post);
        $this->apiReturn($res);
    }

    public function _delete($param=null){
        if(M('shopping_address')->where(['uid' => $this->user['id'],'id' => $param['id']])->delete()){
            $this->_set_default();
            return ['code' => 1,'msg' => '删除成功！'];
        }

        return ['code' => 0,'msg' => '删除失败！'];
    }


    /**
     * subject: 设置默认收货地址
     * api: /ShoppingAddress/set_default
     * author: Lazycat
     * day: 2017-01-17
     *
     * [字段名,类型,是否必传,说明]
     * param: openid,string,1,用户openid
     * param: id,int,1,收货地址ID
     */
    public function set_default(){
        $field = 'openid,id';
        $this->check($field);

        $do = M('shopping_address');
        $do->startTrans();

        M('shopping_address')->where(['uid' => $this->user['id']])->setField('is_default',0);
        if(!M('shopping_address')->where(['uid' => $this->user['id'],'id' => $this->post['id']])->setField('is_default',1)) goto error;

        $do->commit();
        $this->apiReturn(['code' => 1,'msg' => '设置成功！']);

        error:
        $do->rollback();
        $this->apiReturn(['code' => 0,'msg' => '设置失败！']);
    }

    /**
     * 设置默认收货地址
     */
    public function _set_default(){
        $count = M('shopping_address')->where(['uid' => $this->user['id'],'is_default' => 1])->count();
        if($count == 0){
            M('shopping_address')->where(['uid' => $this->user['id']])->order('id desc')->limit(1)->setField('is_default',1);
        }

        //return; 无须返回值
    }

    /**
     * subject: 获取收货地址详情
     * api: /ShoppingAddress/view
     * author: Lazycat
     * day: 2017-01-16
     *
     * [字段名,类型,是否必传,说明]
     * param: openid,string,1,用户openid
     * param: id,int,1,收货地址ID
     */

    public function view(){
        $field = 'openid,id';
        $this->check($field,false);

        $res = $this->_view($this->post);
        $this->apiReturn($res);
    }

    public function _view($param=null){
        $rs = M('shopping_address')->where(['uid' => $this->user['id'],'id' => $param['id']])->field('etime,ip',true)->find();
        if(empty($rs)) goto error;

        $area = $this->cache_table('area');
        $rs['city_name'][]      = $area[$rs['province']];
        $rs['city_name'][]      = $area[$rs['city']];
        $rs['city_name'][]      = $area[$rs['district']];
        $rs['city_name'][]      = $area[$rs['town']];
        $rs['city_name']        = @implode(' ',$rs['city_name']);
        $rs['default']          = $rs['is_default'] == 1 ? '默认' : '';

        return ['code' => 1,'data' => $rs];

        error:
        return ['code' => 3];
    }


}