<?php
/**
* 此文件为根据表单模板创建
*/
namespace Admin\Controller;
use Think\Controller;
//Vendor('Taobao.TopSdk');
class GoodstmallController extends CommonModulesController {
	protected $name 			='淘宝商品管理';	//控制器名称
    protected $formtpl_id		=196;			//表单模板ID
    protected $fcfg				=array();		//表单配置
    protected $do 				='';			//实例化模型
    protected $map				=array();		//where查询条件
    private $private            =['dttx10037', 'dttx10009','dttx10006'];

    /**
    * 初始化
    */
    public function _initialize() {
        if (!in_array(session('admin.username'), $this->private)) exit();
    	parent::_initialize();

    	//初始化表单模板
    	$this->_initform();

    	//dump($this->fcfg);

    }

    /**
    * 列表
    */
    public function index($param=null){
        if (empty(I('get.goods_name')))
            $param['map']['uid'] = I('get.type', 0, 'int');
        $btn=array('title'=>'操作','type'=>'html','html'=>'<a href="'.__CONTROLLER__.'/edit/id/[id]" class="btn btn-sm btn-info btn-rad btn-trans btn-block m0">修改</a> <a href="'.__CONTROLLER__.'/lookgoods/id/[id]" target="_blank" class="btn btn-sm btn-default btn-rad btn-trans btn-block m0 btn-view">查看商品</a>','td_attr'=>'width="100" class="text-center"','norder'=>1);
        $this->_index($param);
        $this->assign('fields',$this->plist(null,$btn));
		$this->display();
    }

    /**
     * 查看商品
     */
    public function lookgoods() {
        $id = I('get.id');
        $goods_id = M('goods_tmall')->where(['id' => $id])->getField('goods_id');
        if ($goods_id > 0) {
            $attrId = M('goods_attr_list')->where(['goods_id' => $goods_id])->getField('id');
            if ($attrId > 0) {
                redirect(DM('item') . '/goods/' . $attrId);
            }
        }
        $this->redirect('/goodsmall/index');
    }

    /**
    * 添加记录
    */
    public function add($param=null){
    	$this->display();
    }
	
	/**
	* 保存新增记录
	*/
	public function add_save($param=null){
		$result=$this->_add_save();

		$this->ajaxReturn($result);
	}

	/**
	* 修改记录
	*/
	public function edit($param=null){
		$this->_edit();
		$this->display();
	}

	/**
	* 保存修改记录
	*/
	public function edit_save($param=null){
		$result=$this->_edit_save();

		$this->ajaxReturn($result);
	}

	/**
	* 删除选中记录
	*/
	public function delete_select($param=null){
		$result=$this->_delete_select();
		$this->ajaxReturn($result);
	}

	/**
	* 批量更改状态
	*/
	public function active_change_select($param=null){
		$result=$this->_active_change_select();

		$this->ajaxReturn($result);		
	}

	/**
     * 上传文件
     */
	public function excel_upload(){
        if(empty($_POST['group_name'])) {
            echo '请输入分组名称！';
            exit();
        }
        if(!in_array($_POST['uid'], [0,1])) {
            echo '类型错误！';
            exit();
        }
        $info = pathinfo($_FILES['file']['name']);
        $ext = array('xls');
        if(!in_array($info['extension'],$ext)) {
            echo '只充许上传.xls格式的EXCEL文件！';
            exit();
        }

        //集群上传文件将无法同步，所以考虑从临时文件中获取
        /*
        if(!is_dir('./Runtime/Excel')) {
            @mkdir('./Runtime/Excel');
        }
        */

        //if(move_uploaded_file($_FILES['file']['tmp_name'],$filename)){
            $data = excel_parse($_FILES['file']['tmp_name']);
            //dump($data);
            $field = array(
                'A' => 'item_id',
                'B' => 'goods_name',
                'C' => 'images',
                'D' => 'detail_url',
                'E' => 'shop_name',
                'F' => 'price',
                'G' => 'sale_num',
                'H' => 'ratio',
                'I' => 'yongjin',
                'J' => 'wang',
                'K' => 'tk_url',
                'L' => 'tk_url2',
                'M' => 'tkey',
                'N' => 'coupon_num',
                'O' => 'coupon_limit',
                'P' => 'coupon_price',
                'Q' => 'coupon_sday',
                'R' => 'coupon_eday',
                'S' => 'coupon_url',
                'T' => 'coupon_key'
            );
            foreach($data as $key => $val){
                if($key > 1){
                    $item = array();
                    foreach($field as $key => $v){
                        if(is_null($val[$key])) $val[$key]='';
                        $item[$v] = $val[$key];
                    }
                    $item['group_name'] = I('post.group_name');
                    $item['uid']        = I('post.uid', 0, 'int');

                    if($rs = M('goods_tmall')->where(['item_id' => $item['item_id']])->field('id')->find()){
                        M('goods_tmall')->where(['id' => $rs['id']])->save($item);
                    }else{
                        M('goods_tmall')->add($item);
                    }
                }
            }
            //@unlink($filename);
            gourl('/Goodstmall');
        //}else{
        //    echo '上传失败！';
        //}

    }

    public function get_item(){
        //$this->_tmall_item(171);

        $c = new \TopClient;
        $c->appkey = '23568996';
        $c->secretKey = '6091917bfc54fe8d2da296ed67b3c3ab';


        /*
        $req = new \TbkItemGetRequest;
        $req->setFields("num_iid,title,pict_url,small_images,reserve_price,zk_final_price,user_type,provcity,item_url,seller_id,volume,nick");
        $req->setQ("女装");
        $resp = $c->execute($req);
        dump($resp);
        */

        $resp = $this->taoke_item('526262213732');

        dump($resp);

    }

    public function taoke_item($item_id){
        $cache_name = 'taoke_'.$item_id;
        $result = S($cache_name);
        if(empty($result)){
            $c = new \TopClient;
            $c->appkey = '23568996';
            $c->secretKey = '6091917bfc54fe8d2da296ed67b3c3ab';
            $req = new \TbkItemInfoGetRequest;
            $req->setFields("num_iid,title,pict_url,small_images,reserve_price,zk_final_price,user_type,provcity,item_url");
            $req->setPlatform("1");
            $req->setNumIids($item_id);
            $result = $c->execute($req);
            $result = objectToArray($result);
            if($result) S($cache_name,$result);
        }

        return $result;
    }


    public function import_to_shop_save(){
        if(empty($_POST['cid'])) $this->ajaxReturn(['status' => 'warning','msg' => '请选择类目！']);
        if(empty($_POST['id'])) $this->ajaxReturn(['status' => 'warning','msg' => '请选择要导入的商品！']);
        if($_POST['ratio']<1) $this->ajaxReturn(['status' => 'warning','msg' => '价格比例不得小于1！']);

        $ids = explode(',',I('post.id'));
        $shop_category_id = implode(',',I('post.shop_category_id'));
        $shop_category_id2 = implode(',',I('post.shop_category_id2'));
        foreach($ids as $val){
            $this->_tmall_item($val,I('post.shop_id'),I('post.cid'),I('post.ratio'),I('post.status'),I('post.price_end'),$shop_category_id,$shop_category_id2,I('post.redo'));
            sleep(5);
        }

        $this->ajaxReturn(['status' => 'success','msg' => '导入成功！']);
    }

    /**
     * 天猫商品采集
     * @param int $id
     * @param boolean $redo 是否重新采集
     */
    public function _tmall_item($id,$shop_id,$cid,$ratio=1.08,$status=2,$price_end=0,$shop_category_id='',$shop_category_id2='',$redo=0){
        //$shop_id = 243;
        $cfg[243]['seller_id']      = 692355;
        $cfg[243]['express_tpl_id'] = 180;

        $cfg[4500]['seller_id']     = 798859;
        $cfg[4500]['express_tpl_id']= 6969;

        $cfg[2600]['seller_id']     = 695622;
        $cfg[2600]['express_tpl_id']= 4639;

        $cfg[3687]['seller_id']     = 690505;
        $cfg[3687]['express_tpl_id']= 5578;
        //$cfg[2600]['uid']           = 1;

        $seller_id = $cfg[$shop_id]['seller_id'];
        //$cid = 100845898;
        $do = M('goods_tmall');
        $rs = $do->where(['id' => $id])->field('atime,etime,ip',true)->find();
        if($rs['goods_id'] > 0 && $redo == 0) return false;

        /*
        if(!strstr($rs['shop_name'],'旗舰店') || !strstr($rs['shop_name'],'专卖店') || !strstr($rs['shop_name'],'专营店')){
            $this->_taobao_item($id,$cid,$ratio,$status,$shop_category_id,$redo);
            exit();
        }
        */


        $url = 'https://detail.tmall.com/item.htm?id='.$rs['item_id'];
        $html=$this->get_oburl($url);
        $html=mb_convert_encoding($html,'utf8','gbk');

        //S('tmall_98',$html,86400);
        //$html=S('tmall_98');
        //dump($html);

        preg_match("/<img id=\"J_ImgBooth\" alt=\"([\s\S]*?)\" src=\"([\s\S]*?)\"/ies",$html,$out2);
        if(empty($out2[2])) return false;    //取不到主图

        //$images=MidStr($html,'<ul id="J_UlThumb" class="tb-thumb tm-clear">','</ul>');
        //preg_match_all("/<img src=\"([\s\S]*?)\"/ies",$images,$img);

        //发货地
        $express_city = trim(MidStr($html,'<input type="hidden" name="region" value="','"'));

        preg_match_all("/<ul data-property=([\s\S]*?)<\/ul>/ies",$html,$sku);
        //dump($sku);
        foreach($sku[0] as $val){
            $skutmp[] = array('name' => trim(MidStr($val,'<ul data-property="','"')));
        }

        foreach($sku[1] as $key => $val){
            preg_match_all("/<li([\s\S]*?)<\/li>/ies",$val,$li);
            //dump($li);
            foreach($li[1] as $v){
                $skutmp[$key]['item'][] = array(
                    'name'  => trim(strip_tags('<a'.$v)),
                    'code'  => trim(MidStr($v,'data-value="','"'))
                );
            }
        }


        //脚本
        /*
        $script = MidStr($html,'TShop.Setup(',');
})();');
        */
        $script = MidStr($html,'TShop.Setup(','</script>');
        $script = explode(PHP_EOL, $script);
        $script = $script[0];
        $script = json_decode($script,true);
        //dump($script);exit();

        //运费模板
        if($express_tpl = M('express_tpl')->where(['tpl_name' => 't_'.$rs['wang'],'uid' => $seller_id])->field('id')->find()){
            $data['express_tpl_id'] = $express_tpl['id'];
        }else{
            $express_tpl = M('express_tpl')->where(['id' => $cfg[$shop_id]['express_tpl_id']])->find();
            $express_area = M('express_area')->where(['tpl_id' => $express_tpl['id']])->field('id,atime,etime',true)->select();
            unset($express_tpl['id']);
            $express_tpl['tpl_name'] = 't_'.$rs['wang'];

            $province = M('area')->where(['sid' => 0,'a_name' => ['like','%'.$script['itemDO']['prov'].'%']])->field('id,a_name')->find();
            $city = M('area')->where(['sid' => $province['id'],'a_name' => ['like','%'.substr($express_city,strlen($script['itemDO']['prov'])).'%']])->field('id,a_name')->find();
            $express_tpl['province']    = $province['id'];
            $express_tpl['city']        = $city['id'];
            $express_tpl['district']    = M('area')->where(['sid' => $express_tpl['city']])->getField('id');
            $express_tpl['town']        = 0;

            $data['express_tpl_id'] = M('express_tpl')->add($express_tpl);

            foreach($express_area as $key => $val){
                $express_area[$key]['ip']       = get_client_ip();
                $express_area[$key]['tpl_id']   = $data['express_tpl_id'];
            }
            M('express_area')->addAll($express_area);

            //dump($province);
            //dump($city);
        }

        $data['images']         = 'https:'.$out2[2];
        $data['goods_name']     = $script['itemDO']['title'];
        $data['atime']          =date('Y-m-d H:i:s',time());
        $data['etime']          =date('Y-m-d H:i:s',time());
        $data['ip']             =get_client_ip();
        $data['status']         =$status;
        $data['category_id']    =$cid;
        $data['shop_category_id']=0;
        $data['brand_id']       =0;
        $data['subtitle']       ='';
        $data['shop_id']        =$shop_id;
        $data['price']          =$rs['price'];
        $data['price_max']      =$rs['price'];
        $data['num']            =0;
        $data['seller_id']      =$seller_id;
        $data['view']           =rand(100,1000);
        $data['package_id']     = M('goods_package')->where(['uid' => $seller_id])->getField('id');
        $data['protection_id']  = M('goods_protection')->where(['uid' => $seller_id])->getField('id');
        if($shop_category_id && $shop_id==243)   $data['shop_category_id']   = $shop_category_id;
        elseif($shop_category_id2 && $shop_id==4500)   $data['shop_category_id']   = $shop_category_id2;

        //$data['pr_extra']       = rand(2,5);

        $do = M();
        $do->startTrans();

        if(!$insid = M('goods')->add($data)){
            goto error;
        }


        //dump($skutmp);


        $price      = $rs['price'];
        $max_price  = $price;
        $num        = 0;
        $attr = $this->get_goods_attr($cid);
        //dump($attr);

        if(!empty($skutmp)) {
            //补全商品属性
            foreach ($skutmp as $key => $val) {
                if (!isset($attr[$key])) {
                    $tmp = [
                        'ip' => get_client_ip(),
                        'status' => 1,
                        'sid' => 0,
                        'attr_name' => $val['name'],
                        'category_id' => $attr[0]['category_id']
                    ];
                    if (!$attr[$key]['id'] = M('goods_attr')->add($tmp)) {
                        goto error;
                    }
                    $attr[$key]['name'] = $val['name'];
                    $attr[$key]['category_id'] = $attr[0]['category_id'];
                    //dump($tmp);
                }
                if (count($attr[$key]['dlist']) < count($val['item'])) {
                    for ($i = count($attr[$key]['dlist']); $i < count($val['item']); $i++) {
                        $tmp = [
                            'ip' => get_client_ip(),
                            'status' => 1,
                            'sid' => $attr[$key]['id'],
                            'attr_name' => $val['item'][$i]['name'],
                            'sort' => 100
                        ];

                        if (!M('goods_attr')->add($tmp)) {
                            goto error;
                        }
                    }
                }
            }

            //重新获取属性
            $attr = $this->get_goods_attr($cid);
            //dump($attr);
            foreach ($skutmp as $key => $val) {
                foreach ($val['item'] as $k => $v) {
                    $skutmp[$key]['item'][$k]['attr_id'] = $attr[$key]['id'];
                    $skutmp[$key]['item'][$k]['option_id'] = $attr[$key]['dlist'][$k]['id'];
                    //属性图片
                    //if($key == 0){
                        if($script['propertyPics'][';'.$v['code'].';']) $skutmp[$key]['item'][$k]['images'] = 'http:'.$script['propertyPics'][';'.$v['code'].';'][0];
                    //}

                    $tmp = [
                        'ip' => get_client_ip(),
                        'attr_value'    => $v['name'],
                        'goods_id'      => $insid,
                        'attr_id'       => $attr[$key]['id'],
                        'option_id'     => $attr[$key]['dlist'][$k]['id'],
                        'attr_images'   => !is_null($skutmp[$key]['item'][$k]['images']) ? $skutmp[$key]['item'][$k]['images'] : '',
                        'attr_album'    => !is_null($skutmp[$key]['item'][$k]['images']) ? $skutmp[$key]['item'][$k]['images'] : ''
                    ];

                    //dump($tmp);

                    if (!M('goods_attr_value')->add($tmp)) {
                        goto error;
                    }


                    //$skutmp[$key]['item'][$k]['attr_value_id'] = M('goods_attr_value')->add($tmp);
                }
            }

            //dump($skutmp);
            //$do->rollback();exit();

            //拼库存
            $tmplist = array();
            for($i=0;$i<count($skutmp);$i++){
                $tmp = array();
                if($i==0){
                    foreach($skutmp[$i]['item'] as $val){
                        $tmp[] = [
                            'name'      => $val['name'],
                            'code'      => $val['code'],
                            'images'    => $val['images'] ? $val['images'] : '',
                            'attr'      => $val['attr_id'].':'.$val['option_id'].':'.$val['name'],
                            'attr_id'   => $val['attr_id'].':'.$val['option_id'],
                        ];
                    }
                }else {
                    foreach ($tmplist as $val) {
                        foreach ($skutmp[$i]['item'] as $v) {
                            $tmp[] = [
                                'name'      => $val['name'].','.$v['name'],
                                'code'      => $val['code'].';'.$v['code'],
                                'images'    => $val['images'] ? $val['images'] : '',
                                'attr'      => $val['attr'].','.$v['attr_id'].':'.$v['option_id'].':'.$v['name'],
                                'attr_id'   => $val['attr_id'].','.$v['attr_id'].':'.$v['option_id'],
                            ];
                        }
                    }

                }
                $tmplist = $tmp;

            }

            //dump($tmplist);
            //$do->rollback();exit();

            foreach($tmplist as $val){
                $price_cfg = $script['valItemInfo']['skuMap'][';' . $val['code'] . ';'];
                if($price_cfg['price'] <=0) {
                    $price_cfg['stock'] = 0;
                    //$price_cfg['price'] = $rs['price'];
                }
                $price_cfg['price'] = $rs['price'];     //$price_cfg['price']为吊牌价，所以用$rs['price'];
                $price_cfg['price'] = round($price_cfg['price'] * $ratio) + $price_end;
                $price = min($price, $price_cfg['price']);
                $max_price = max($max_price, $price_cfg['price']);

                $attr_list = [
                    'ip'        => get_client_ip(),
                    'seller_id' => $seller_id,
                    'attr'      => $val['attr'],
                    'attr_id'   => $val['attr_id'],
                    'attr_name' => $val['name'],
                    'goods_id'  => $insid,
                    'price'     => $price_cfg['price'],
                    'weight'    => 0.25,
                    'num'       => $price_cfg['stock'] > 0 ? 100 : 0,
                    'images'    => $val['images'] ? $val['images'] : $data['images'],
                ];

                //dump($attr_list);
                if (!M('goods_attr_list')->add($attr_list)) {
                    goto error;
                }

                $num += $attr_list['num'];
            }



            /*
            foreach ($skutmp[0]['item'] as $val) {
                $tmp = array();
                $tmp[] = $val['name'];

                $code = array();
                $code[] = $val['code'];

                $attr_tmp = array();
                $attr_tmp[] = $val['attr_id'] . ':' . $val['option_id'] . ':' . $val['name'];

                $attr_id_tmp = array();
                $attr_id_tmp[] = $val['attr_id'] . ':' . $val['option_id'];

                $attr_name_tmp = array();
                $attr_name_tmp[] = $val['name'];

                for ($i = 1; $i < count($skutmp); $i++) {
                    foreach ($skutmp[$i]['item'] as $v) {
                        $tmp[] = $v['name'];
                        $code[] = $v['code'];
                        $attr_tmp[] = $v['attr_id'] . ':' . $v['option_id'] . ':' . $v['name'];
                        $attr_id_tmp[] = $v['attr_id'] . ':' . $v['option_id'];
                        $attr_name_tmp[] = $v['name'];
                    }
                }


                //dump(implode(',',$tmp));
                dump(implode(';',$code));
                //dump(implode(',',$attr_tmp));
                //dump(implode(',',$attr_id_tmp));
                dump(implode(',',$attr_name_tmp));

                $price_cfg = $script['valItemInfo']['skuMap'][';' . implode(';', $code) . ';'];

                $price_cfg['price'] = round($price_cfg['price'] * $ratio) + $price_end;
                $price = min($price, $price_cfg['price']);
                $max_price = max($max_price, $price_cfg['price']);

                $attr_list = [
                    'ip' => get_client_ip(),
                    'seller_id' => $seller_id,
                    'attr' => implode(',', $attr_tmp),
                    'attr_id' => implode(',', $attr_id_tmp),
                    'attr_name' => implode(',', $attr_name_tmp),
                    'goods_id' => $insid,
                    'price' => $price_cfg['price'],
                    'weight' => 0.25,
                    'num' => 100,
                    'images' => $data['images'],
                ];

                //dump($attr_list);
                if (!M('goods_attr_list')->add($attr_list)) {
                    goto error;
                }

                $num += 100;
            }
            */

            //$do->rollback();exit();
        }else{
            //dump($attr);
            $tmp = [
                'ip' => get_client_ip(),
                'attr_value' => '看详情介绍',
                'goods_id' => $insid,
                'attr_id' => $attr[0]['id'],
                'option_id' => $attr[0]['dlist'][0]['id']
            ];
            //dump($tmp);

            if (!M('goods_attr_value')->add($tmp)) {
                goto error;
            }

            //库存
            $attr_list = [
                'ip' => get_client_ip(),
                'seller_id' => $seller_id,
                'attr' => $attr[0]['id'].':'.$attr[0]['dlist'][0]['id'].':'.$attr[0]['dlist'][0]['attr_name'],
                'attr_id' => $attr[0]['id'].':'.$attr[0]['dlist'][0]['id'],
                'attr_name' => $attr[0]['dlist'][0]['attr_name'],
                'goods_id' => $insid,
                'price' => round($rs['price'] * $ratio) + $price_end,
                'weight' => 0.25,
                'num' => 100,
                'images' => $data['images'],
            ];

            //dump($attr_list);

            //dump($attr_list);
            if (!M('goods_attr_list')->add($attr_list)) {
                goto error;
            }
            $price = $attr_list['price'];
            $max_price = $attr_list['price'];
            $num += 100;
        }



        if(false === M('goods')->where(['id' => $insid])->save(['price' => $max_price,'max_price' => $max_price,'num' => $num])){
            goto error;
        }


        //详情
        $desc=curl_file(array('url'=>'http:'.$script['api']['descUrl']));
        $desc=trim(mb_convert_encoding($desc,'utf8','gbk'));
        $data2['content']=substr($desc,10,-2);
        $data2['content']=strip_tags($data2['content'],'<div><span><img><table><tr><td><thead><tbody><font><strong><br><b><hr><center>');

        if(!M('goods_content')->add(['goods_id' => $insid,'content' => $data2['content']])){
            goto error;
        }

        if(false === M('goods_tmall')->where(['id' => $rs['id']])->save(['goods_id' => $insid])){
            goto error;
        }


        $do->commit();
        goods_pr($insid); //更新商品PR
        return true;

        error:
            $do->rollback();
            return false;

    }

    public function _taobao_item($id,$cid,$ratio=1.08,$status=2,$shop_category_id='',$redo=0){

    }

    //利用缓存取数据，不使用CURL，因为CURL可能会因为DNS关系而获取不到数据
    public function get_oburl($url){
        /*
    	ob_start();
    	readfile($url);
    	$html=ob_get_contents();
    	ob_clean();
        */
        $html=curl_file($url);
        return $html;
    }

    /**
     * 获取类目属性
     */
    /**
     * 根据类目取属性
     * @param int    $_POST['cid']   类目ID
     */
    public function get_goods_attr($cid){
        $do=M('goods_attr');
        $list=$do->where(array('status'=>1,'category_id'=>$cid,'sid'=>0))->field('id,attr_name,category_id')->order('sort asc')->select();

        if(empty($list)){
            $rs=M('goods_category')->where(['id' => $cid])->field('id,sid')->find();
            //dump($rs);
            if($rs['sid']>0) $list=$this->get_goods_attr($rs['sid']);
            else return false;
        }

        foreach($list as $key => $val){
            $list[$key]['dlist'] = M('goods_attr')->where(['sid' => $val['id']])->field('id,attr_name')->order('sort asc,id asc')->select();
        }

        return $list;
    }


    public function city(){
        $list = M('goods_category')->where(['sid' => 0,'status' =>1])->field('id,sid,category_name')->order('sort asc,id asc')->select();
        $this->assign('city',$list);
        $this->display();
    }

    public function get_city(){
        $list = M('goods_category')->where(['sid' => I('get.sid'),'status' =>1])->field('id,sid,category_name')->order('sort asc,id asc')->select();
        if($list){
            $res = ['code' =>1,'data' => $list];
        }else{
            $res = ['code' =>0,'msg' =>'获取失败！'];
        }
        $this->ajaxReturn($res);
    }



    public function get_tmall_list(){
        if(empty($_GET['q'])) {
            echo '请输入关键词！';
            exit();
        }
        $url = 'https://list.tmall.com/m/search_items.htm?page_size=20&page_no=[p]&q=[q]&type=p&tmhkh5=&spm=875.7403452.a2227oh.d100&from=mallfp..m_1_searchbutton';
        for($i=1;$i<=5;$i++){
            $furl = str_replace(array('[p]','[q]'),array($i,I('get.q')),$url);
            $html = $this->get_oburl($furl);
            //S('test',$html,86400);
            //$html = S('test');
            $html = json_decode($html,true);
            if(empty($html['item'])){
                echo 'end';
                exit();
            }

            foreach($html['item'] as $val){
                //dump($val);
                $data = array();
                $data = [
                    'item_id'       => $val['item_id'],
                    'goods_name'    => $val['title'],
                    'images'        => 'http:'.$val['img'],
                    'detail_url'    => 'https:'.$val['url'],
                    'shop_name'     => $val['shop_name'],
                    'wang'          => $val['nick'],
                    'price'         => $val['price'],
                    'sale_num2'     => $val['sold'],
                    'group_name'    => $val['shop_name'],
                ];

                if(!M('goods_tmall')->where(['item_id' => $val['item_id']])->find()){
                    M('goods_tmall')->add($data);
                }
                dump($data);
            }
            sleep(rand(1,5));
            //dump($html);
        }
    }
}