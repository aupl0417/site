<?php
/**
 * ----------------------------------------------------------
 * RestFull API V2.0
 * ----------------------------------------------------------
 * 广告位管理
 * ----------------------------------------------------------
 * Author:liangfeng 
 * ----------------------------------------------------------
 * 2017-03-21
 * ----------------------------------------------------------
 */
namespace Rest2\Controller;
class SellerAdController extends ApiController{
	protected $action_logs = array();
	/**
     * subject: 广告统计
     * api: /SellerAd/ad_total
     * author: liangfeng
     * day: 2017-03-21
     *
     * [字段名,类型,是否必传,说明]
     * param: openid,int,1,用户openid
     */
    public function ad_total(){
        $this->check('openid',false);
        $res = $this->_ad_total($this->post);
        $this->apiReturn($res);
    }
	public function _ad_total($param){
		$res=A('Total')->user_ad($this->user['id']);
		return ['code' => 1,'data'=>$res];
	}
	/**
     * subject: 广告位列表
     * api: /SellerAd/orders_list
     * author: liangfeng
     * day: 2017-03-21
     *
     * [字段名,类型,是否必传,说明]
     * param: openid,int,1,用户openid
	 * param: pagesize,int,0,每页显示数量
     * param: p,int,0,第p页
     * param: status,int,0,状态，不带则是全部 0待付款，1已付款，2强制下架 3投放中 4带投放 5已过期
     */
    public function orders_list(){
        $this->check('openid',false);
        $res = $this->_orders_list($this->post);
        $this->apiReturn($res);
    }
	public function _orders_list($param){
		$status_name    =['待付款','已付款','强制下架'];
        $type_name      =['商品','店铺','站外链接'];
		
		$map['uid']     =$this->user['id'];
		
		switch ($param['status']) {
            case 3: //投放中
                $map['status']  =1;
                $map['days']    =['like','%'.date('Y-m-d').'%'];
            break;
            case 4: //待投放
                $map['status']  =1;
                $map['sday']    =['gt',date('Y-m-d')];
            break;
            case 5: //已过期
                $map['status']  =1;
                $map['eday']    =['lt',date('Y-m-d')];
            break;
            
            default:
                if($param['status']!='') $map['status']=$param['status'];
            break;
        }
		$pagesize=$param['pagesize']?$param['pagesize']:12;
        $pagelist=pagelist(array(
                'table'         =>'Common/AdRelation',
                'do'            =>'D',
                'map'           =>$map,
                'order'         =>'id desc',
                'fields'        =>'etime,ip,subcontent,uid,is_default,days',
                'fields_type'   =>true,
                'relation'      =>true,
                'pagesize'      =>$pagesize,
                'action'        =>$param['action'],
                'query'         =>$param['query'],
                'p'             =>$param['p'],
            ));
			
		if($pagelist['list']){
            foreach($pagelist['list'] as $i => $val) {
                $pagelist['list'][$i]['status_name']    =$status_name[$val['status']];
                $pagelist['list'][$i]['type_name']      =$type_name[$val['type']];
                switch($val['type']){
                    case 0:
                        $pagelist['list'][$i]['goods']  =M('goods')->where(['goods' => $val['goods_id']])->field('id,goods_name,images')->find();
                    break;
                    case 1:
                        $pagelist['list'][$i]['shop']  =M('shop')->where(['id' => $val['shop_id']])->field('id,shop_name,shop_logo,concat("'.($_SERVER['HTTPS']=='on'?'https://':'http://').'",if(domain!="",domain,id),".'.C('DOMAIN').'") as shop_url')->find();
                    break;
                }
            }
			return ['code' => 1,'data'=>$pagelist];
        }else{
			return ['code' => 3];
        }    
	}
	/**
     * subject: 广告订单详情
     * api: /SellerAd/orders_view
     * author: liangfeng
     * day: 2017-03-21
     *
     * [字段名,类型,是否必传,说明]
     * param: openid,int,1,用户openid
     * param: a_no,int,1,广告订单号
     */
    public function orders_view(){
        $this->check('openid,a_no',false);
        $res = $this->_orders_view($this->post);
        $this->apiReturn($res);
    }
	public function _orders_view($param){
		$status_name    =['待付款','已付款','强制下架'];
        $type_name      =['商品','店铺','站外链接'];
		
		$do=D('Common/AdRelation');
        $rs=$do->relation(true)->where(['uid' => $this->user['id'],'a_no' => I('post.a_no')])->field('etime,ip,subcontent,uid,is_default',true)->find();

        if(!$rs) return ['code' => 3];
		
        $rs['status_name']    =$status_name[$rs['status']];
        $rs['type_name']      =$type_name[$rs['type']];
        switch($rs['type']){
            case 0:
                $rs['goods']  =M('goods')->where(['goods' => $rs['goods_id']])->field('id,goods_name,images')->find();
            break;
            case 1:
                $rs['shop']  =M('shop')->where(['id' => $rs['shop_id']])->field('id,shop_name,shop_logo,concat("'.($_SERVER['HTTPS']=='on'?'https://':'http://').'",if(domain!="",domain,id),".'.C('DOMAIN').'") as shop_url')->find();
            break;
        }
        $calendar=calendar(array('sday'=>$rs['sday'],'eday'=>$rs['eday'],'days'=>$rs['days']));
		return ['code' => 1,'data'=>$rs,'calendar' => $calendar];
	}
	/**
     * subject: 创建广告订单
     * api: /SellerAd/orders_delete
     * author: liangfeng
     * day: 2017-03-21
     *
     * [字段名,类型,是否必传,说明]
     * param: openid,int,1,用户openid
     * param: positon_id,int,1,广告位置号
     * param: sort,int,1,广告位顺序
     * param: days,int,1,投放日期列表
     * param: sucai_id,int,1,素材ID
     * param: name,string,1,广告标题
     * param: type,int,1,类别 0商品 1店铺 2站外连接
     * param: goods_id,int,1,商品ID
     */
    public function create_orders(){
        $this->check('openid,positon_id,sort,days,sucai_id,name,type,goods_id');
        $res = $this->_create_orders($this->post);
        $this->apiReturn($res);
    }
	public function _create_orders($param){
	
        $days=explode(',',$param['days']);
        array_unique($days);
        asort($days);
        $sday=reset($days);
        $eday=end($days);

        //判断购买时间段是否正常
        //您投放的时间段中有部分同其它用户冲突，请重新选择投放时间！
        if(!ad_days_check($param['days'],$param['position_id'],$param['sort'])) return ['code' => 552,'msg'=>C('error_code')[552]];

        $sucai=M('ad_sucai')->where(['uid' => $this->user['id'],'id' => $param['sucai_id']])->field('id,images,background_images')->find();
        $position=M('ad_position')->where(['id' => $param['position_id']])->field('price,device,background_width')->find();


        $data=[
            'a_no'          =>$this->create_orderno('AD',$this->user['id']),
            'name'          =>$param['name'],
            'position_id'   =>$param['position_id'],
            'status'        =>0,
            'sort'          =>$param['sort'],
            'sday'          =>$sday,
            'eday'          =>$eday,
            'days'          =>implode(',',$days),
            'num'           =>count($days),
            'images'        =>$sucai['images'],
            'price'         =>$position['price'],
            'type'          =>$param['type'],
            'sucai_id'      =>$param['sucai_id'],
            'background_images' =>  $sucai['background_images'],
        ];
        
        
        if ($position['background_width'] > 0) {
            if ($sucai['background_images'] == false) return ['code' => 0,'msg'=>'背景图片不能为空！'];
            $data['background_images'] = $sucai['background_images'];
        }
        $data['money']      =$data['price']*$data['num'];
        $data['money_pay']  =$data['money'];
        $data['uid']        =$this->user['id'];

        switch($param['type']){
            case 0:
                # $goods=M('goods_attr_list')->where(['seller_id' => $this->uid,'goods_id' => I('post.goods_id'),'num' => ['gt',0]])->field('id')->find();
                # if(!$goods) $this->apiReturn(554);   //商品不存在
                $data['goods_id']   =$param['goods_id'];
                $attr_id = M('goods_attr_list')->where(['goods_id'=>$data['goods_id'],'num'=>['gt',0]])->getField('id');
                if($position['device'] == 1){
                    $data['url']        = C('sub_domain.item').'/goods/'.$attr_id.'.html';
                }elseif($position['device'] == 2){
                    $data['url']        = '/Goods/view/id/' . $attr_id;
                }else{
                    $data['url']        = '#';
                }
            break;
            case 1:
                $shop=M('shop')->where(['uid' => $this->user['id']])->field('id,domain')->find();
                if(!$shop) return ['code' => 553,'msg'=>C('error_code')[553]];   //店铺不存在
                $data['shop_id']    =$shop['id'];
                if($position['device'] == 1){
                    $data['url']        =shop_url($shop['id'],$shop['domain']);
                }elseif($position['device'] == 2){
                    $data['url']        = '/Shop/index/shop_id/' . $data['shop_id'];
                }else{
                    $data['url']        = '#';
                }
            break;
        }

        if(!$data=D('Common/Ad')->create($data)) return ['code' => 4,'msg'=>D('Common/AdSucai')->getError()];
        if(!$data['id']=D('Common/Ad')->add($data)) return ['code' => 0];
		
		return ['code' => 1,'data'=>$data];
	}
	/**
     * subject: 删除广告订单
     * api: /SellerAd/orders_delete
     * author: liangfeng
     * day: 2017-03-21
     *
     * [字段名,类型,是否必传,说明]
     * param: openid,int,1,用户openid
     * param: a_no,int,1,广告订单号
     */
    public function orders_delete(){
        $this->check('openid,a_no',false);
        $res = $this->_orders_delete($this->post);
        $this->apiReturn($res);
    }
	public function _orders_delete($param){
        $rs=M('ad')->where(['uid' => $this->user['id'],'a_no' => $param['a_no']])->field('id,status')->find();

        if(!$rs) return ['code' => 3];

        //只有未付款的订单才可以删除
        if($rs['status']!=0) return ['code' => 555,'msg'=>C('error_code')[555]];

        if(M('ad')->where(['a_no' => $param['a_no']])->delete()) return ['code' => 1];
        return ['code' => 0];
	}
	/**
     * subject: 我的素材
     * api: /SellerAd/sucai_list
     * author: liangfeng
     * day: 2017-03-21
     *
     * [字段名,类型,是否必传,说明]
     * param: openid,int,1,用户openid
	 * param: pagesize,int,0,每页显示数量
     * param: p,int,0,第p页
     * param: status,int,0,状态，不带则是全部 0待审核 1已审核 2审核未通过
     * param: size,string,0,素材尺寸(宽x高) 例如 74x74 中间符号为小写英文x
     * param: bsize,string,0,背景尺寸(宽x高) 例如 74x74 中间符号为小写英文x
     */
    public function sucai_list(){
        $this->check('openid',false);
        $res = $this->_sucai_list($this->post);
        $this->apiReturn($res);
    }
	public function _sucai_list($param){
		$status_name=array('待审核','审核通过','审核未通过');
        if(isset($param['size'])){
            $size = explode('x', $param['size']);
            $map['width'] = (int) $size[0];
            $map['height'] = (int) $size[1];
        }
        
        if (isset($param['bsize'])) {
            $bsize = explode('x', $param['param']);
            $map['background_width'] = (int) $bsize[0];
            $map['background_height'] = (int) $bsize[1];
        }
        
        if(isset($param['category'])){
            $map['category_id'] = array('in',$param['category']);
        }

        $map['uid']=$this->user['id'];
        if($param['status']!='') $map['status']=$param['status'];
        $pagesize=$param['pagesize']?$param['pagesize']:20;
        $pagelist=pagelist(array(
                'table'         =>'ad_sucai',
                'map'           =>$map,
                'order'         =>'id desc',
                'fields'        =>'etime,ip,uid',
                'fields_type'   =>true,
                'pagesize'      =>$pagesize,
                'action'        =>$param['action'],
                'query'         =>$param['query'],
                'p'             =>$param['p'],
            ));

        if($pagelist['list']){
            foreach($pagelist['list'] as $i => $val){
                $pagelist['list'][$i]['category_name']=nav_sort(['table' => 'goods_category','field' => 'id,sid,category_name','id' => $val['category_id'],'key' => 'category_name']);
                $pagelist['list'][$i]['status_name']=$status_name[$val['status']];
                if($val['status']!=2) $pagelist['list'][$i]['reason']='';
            }
			return ['code' => 1,'data'=>$pagelist];
        }else{
            return ['code' => 3];
        }
	}
	/**
     * subject: 素材详情
     * api: /SellerAd/sucai_view
     * author: liangfeng
     * day: 2017-03-21
     *
     * [字段名,类型,是否必传,说明]
     * param: openid,int,1,用户openid
     * param: id,int,1,素材ID
     */
    public function sucai_view(){
        $this->check('openid,id',false);
        $res = $this->_sucai_view($this->post);
        $this->apiReturn($res);
    }
	public function _sucai_view($param){
		$status_name=array('待审核','审核通过','审核未通过');

        $rs=M('ad_sucai')->where(['uid' => $this->user['id'],'id' => $param['id']])->field('etime,ip,uid',true)->find();
        if($rs){
            //数据格式化
            $rs['category_name']=nav_sort(['table' => 'goods_category','field' => 'id,sid,category_name','id' => $rs['category_id'],'key' => 'category_name']);
            $rs['status_name']=$status_name[$rs['status']];
            if($rs['status']!=2) $rs['reason']='';

            return ['code' => 1,'data'=>$rs];
        }else return ['code' => 3];
	}
	/**
     * subject: 添加素材
     * api: /SellerAd/sucai_add
     * author: liangfeng
     * day: 2017-03-22
     *
     * [字段名,类型,是否必传,说明]
     * param: openid,int,1,用户openid
     * param: sucai_name,string,1,素材标题
     * param: images,string,1,图片地址
     * param: size,string,1,素材尺寸(宽x高) 例如 74x74 中间符号为小写英文x
     * param: category_id,int,1,类目id
     * param: bsize,string,0,背景尺寸(宽x高) 例如 74x74 中间符号为小写英文x
     * param: background_images,string,0,背景地址
     */
    public function sucai_add(){
        $this->check('openid,sucai_name,images,size,category_id',false);
        $res = $this->_sucai_add($this->post);
        $this->apiReturn($res);
    }
	public function _sucai_add($param){
		
		$size=explode('x',$param['size']); 
        //检查图片尺寸是否符合要求
        $check=$this->qn_imgsize($param['images'],$size[0],$size[1]);
        if(!$check) return ['code' => 550,'msg'=>C('error_code')[550]];  //图片尺寸不符合要求！
	
        if (!empty($param['bsize'])) {
            $bsize = explode('x',$param['bsize']);
            $bcheck=$this->qn_imgsize($param['background_images'],$bsize[0],$bsize[1]);
            if(!$bcheck) return ['code' => 550,'msg'=>C('error_code')[550]];  //背景图片尺寸不符合要求！
            $param['background_width'] =$bsize[0];
            $param['background_height']=$bsize[1];
        }

        //检查素材是否重复
        if(M('ad_sucai')->where(['uid' => $this->user['id'],'images' => $param['images'],'category_id' => $param['category_id']])->count()>0) return ['code' => 551,'msg'=>C('error_code')[551]];

        $param['uid']   =$this->user['id'];
        $param['width'] =$size[0];
        $param['height']=$size[1];
        
        if(!$data=D('Common/AdSucai')->create($param)) return ['code' => 4,'msg'=>D('Common/AdSucai')->getError()];
        if(!$data['id']=D('Common/AdSucai')->add($param)) return ['code' => 0];

		return ['code' => 1,'data'=>$data];
	}
	/**
     * subject: 修改素材
     * api: /SellerAd/sucai_edit
     * author: liangfeng
     * day: 2017-03-22
     *
     * [字段名,类型,是否必传,说明]
     * param: openid,int,1,用户openid
     * param: sucai_name,string,1,素材标题
     * param: images,string,1,图片地址
     * param: size,string,1,素材尺寸(宽x高) 例如 74x74 中间符号为小写英文x
     * param: category_id,int,1,类目id
     * param: bsize,string,0,背景尺寸(宽x高) 例如 74x74 中间符号为小写英文x
     * param: background_images,string,0,背景地址
     */
    public function sucai_edit(){
        $this->check('openid,sucai_name,images,size,category_id',false);
        $res = $this->_sucai_edit($this->post);
        $this->apiReturn($res);
    }
	public function _sucai_edit($param){
		
		$size=explode('x',$param['size']);

        //检查图片尺寸是否符合要求
        $check=$this->qn_imgsize($param['images'],$size[0],$size[1]);
        if(!$check) return ['code' => 550,'msg'=>C('error_code')[550]];  //图片尺寸不符合要求！
        
        if (!empty($param['bsize'])) {
            $bsize = explode('x',$param['bsize']);
            $bcheck=$this->qn_imgsize($param['background_images'],$bsize[0],$bsize[1]);
            if(!$bcheck) return ['code' => 550,'msg'=>C('error_code')[550]];  //背景图片尺寸不符合要求！
            $param['background_width'] =$bsize[0];
            $param['background_height']=$bsize[1];
        }
        
        
        //检查素材是否重复
        if(M('ad_sucai')->where(['uid' => $this->uid,'images' => $param['images'],'category_id' => $param['category_id'],'id' => ['neq',$param['id']]])->count()>0) return ['code' => 551,'msg'=>C('error_code')[551]];

        $param['uid']   =$this->user['id'];
        $param['width'] =$size[0];
        $param['height']=$size[1];
        $param['status']=0;
        if(!$data=D('Common/AdSucai')->create($param)) $this->apiReturn(4,'',1,D('Common/AdSucai')->getError());
        if(D('Common/AdSucai')->save($param) === false){
            return ['code' => 0];
        }else{
			return ['code' => 1,'data'=>$data];
        }

	}
	/**
     * subject: 删除素材
     * api: /SellerAd/sucai_delete
     * author: liangfeng
     * day: 2017-03-22
     *
     * [字段名,类型,是否必传,说明]
     * param: openid,int,1,用户openid
     * param: id,string,1,素材ID 多个请用逗号隔开
     */
    public function sucai_delete(){
        $this->check('openid,id',false);
        $res = $this->_sucai_delete($this->post);
        $this->apiReturn($res);
    }
	public function _sucai_delete($param){
		if(M('ad_sucai')->where(['uid' => $this->user['id'],'id' => ['in',$param['id']]])->delete()) return ['code' => 1];
        else return ['code' => 0];

	}
}