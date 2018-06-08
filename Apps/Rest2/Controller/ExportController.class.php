<?php
/**
 * ----------------------------------------------------------
 * RestFull API V2.0
 * ----------------------------------------------------------
 * 导出
 * ----------------------------------------------------------
 * Author:liangfeng 
 * ----------------------------------------------------------
 * 2017-07-01
 * ----------------------------------------------------------
 */
namespace Rest2\Controller;

class ExportController extends ApiController {
    //protected $action_logs = array('ad');
	private $time_interval = [1,2,3,4,5,6];
	private $day_field = ['pay_time','atime','express_time','receipt_time','rate_time'];
    private $pagesize = 500;
    private $sleep_time = 100;

    private $dbs = ['','orders_shop','refund','mobile_orders'];

    private $debug = true;

    /**
     * subject: 获取方案需要多少次导出
     * api: /Export/get_page_count
     * author: liangfeng
     * day: 2017-07-01
     *
     * [字段名,类型,是否必传,说明]
     * param: id,int,1,导出方案ID
     */
    public function get_programme_count(){
        $this->check('id',false);
        $res = $this->_get_programme_count($this->post);
        $this->apiReturn($res);
    }
    public function _get_programme_count($param){
		S('export_programme_'.$param['id'].'_total',null);
        if(S('export_programme_'.$param['id'].'_total') != null){
            return ['code'=>1,'data'=>S('export_programme_'.$param['id'].'_total')];
        }
        $rs = M('export_programme')->field('id,category_id,condition,condition_select,field,field_select')->find($param['id']);
        if(!$rs){
            if($this->debug) log_add('export_log',['function'=>__FUNCTION__,'atime'=>date('Y-m-d H:i:s'),'param'=>$param,'res'=>['code'=>0,'sql'=>M()->getlastsql(),'msg'=>'没有此导出方案！']]);
            return ['code'=>0,'msg'=>'没有此导出方案！'];
        }

        $map = $this->_assemble_condition($rs['category_id'],$rs['condition_select']);
        $db = M($this->dbs[$rs['category_id']]);

        $loop = true;
        $p      = 1;
        while($loop){
            $limit = (($p - 1) * $this->pagesize).',1';
            $list = $db->field('id')->where($map)->limit($limit)->select();
            $sql = M()->getlastsql();
            if(empty($list)){
                $loop = false;
                break;
            }

            usleep($this->sleep_time);
            $p++;
        }
        $p = $p-1;
        S('export_programme_'.$param['id'].'_total',$p,3600);
		
		$return = ['code'=>1,'sql'=>$sql,'data'=>$p];
        if($this->debug) log_add('export_log',['function'=>__FUNCTION__,'atime'=>date('Y-m-d H:i:s'),'param'=>$param,'res'=>$return]);
        return $return;
    }

    /**
     * subject: 生成Excel文件
     * api: /Export/create
     * author: liangfeng
     * day: 2017-07-01
     *
     * [字段名,类型,是否必传,说明]
     * param: id,int,1,导出方案ID
     * param: p,int,1,分页
     * param: total,int,1,总页数
     */
    public function create(){
        $this->check('id,p,total',false);
        $res = $this->_create($this->post);
        $this->apiReturn($res);
    }
    public function _create($param=null){
		if(S('export_programme_'.$param['id'].'_'.$param['p']) == 'running'){
			if($this->debug) log_add('export_log',['function'=>__FUNCTION__,'atime'=>date('Y-m-d H:i:s'),'param'=>$param,'res'=>['code'=>99,'msg'=>'excel正在导出，请稍后再试！']]);
			return ['code'=>99,'msg'=>'excel正在导出，请稍后再试！'];
		}

		$rs = M('export_programme')->field('id,category_id,condition,condition_select,field,field_select')->find($param['id']);
		if(!$rs){
            if($this->debug) log_add('export_log',['function'=>__FUNCTION__,'atime'=>date('Y-m-d H:i:s'),'param'=>$param,'res'=>['code'=>0,'msg'=>'没有此导出方案！']]);
			return ['code'=>0,'msg'=>'没有此导出方案！'];
		}
		//导出开始
		S('export_programme_'.$param['id'].'_'.$param['p'],'running',15);
		if($rs['category_id'] == 1){
			$res = $this->_export_orders_shop($rs,$param['p']);
		}else if($rs['category_id'] == 2){
            $res = $this->_export_refund($rs,$param['p']);
        }else if($rs['category_id'] == 3){
            $res = $this->_export_mobile_orders($rs,$param['p']);
        }
		//导出结束
		S('export_programme_'.$param['id'].'_'.$param['p'],NULL);

        if($this->debug) log_add('export_log',['function'=>__FUNCTION__,'atime'=>date('Y-m-d H:i:s'),'param'=>$param,'res'=>$res]);
        return $res;
        /*if($res['code'] == 1){
            $res = $this->_check_is_finish($param['id'],$param['total']);
        }
        if($this->debug) log_add('export_log',['function'=>__FUNCTION__,'atime'=>date('Y-m-d H:i:s'),'param'=>$param,'res'=>$res]);
        return $res;*/
    }

    /**
     * subject: 压缩文件
     * api: /Export/programme_zip
     * author: liangfeng
     * day: 2017-07-01
     *
     * [字段名,类型,是否必传,说明]
     * param: id,int,1,导出方案ID
     */
    public function programme_zip(){
        set_time_limit(0);
        $this->check('id',false);
        $res = $this->_programme_zip($this->post);
        $this->apiReturn($res);
    }
    public function _programme_zip($param){
        $res = $this->_get_programme_count($param);
        if($res['code'] == 1){
            $total = $res['data'];
            $res = $this->_check_is_finish($param['id'],$total);
        }
        if($this->debug) log_add('export_log',['function'=>__FUNCTION__,'atime'=>date('Y-m-d H:i:s'),'param'=>$param,'res'=>$res]);
        return $res;

    }

    /**
     * subject: 检查是否已经全部导出
     * author: liangfeng
     * day: 2017-07-08
     */
    private function _check_is_finish($id,$total){
        for($i=1;$i<=$total;$i++){
            $excel_file =$this->_get_path().$id.'_'.$i.'.xlsx';
            if(!file_exists($excel_file)){
                if($this->debug) log_add('export_log',['function'=>__FUNCTION__,'time'=>date('Y-m-d H:i:s'),'id'=>$id,'total'=>$total,'res'=>['code'=>0,'msg'=>$excel_file.'不存在！，共'.$total.'份Excel文件！']]);
                return ['code'=>0,'msg'=>'第'.$i.'份文件未完成，共'.$total.'份Excel文件！'];
            }else{
                $excel_patch[] = $excel_file;
            }
        }
        //全部已经导出excel

        $zip_file_name = $this->_get_path().$id.'.zip';
        $res = $this->_zip_file($zip_file_name,$excel_patch);
        if($res['code'] == 1){

            $zip_file = $res['zip_file_name'];
            $res = $this->_upload_qiniu($zip_file);
            if($res['code'] == 1){

                @unlink($zip_file);
                $data['download_path'] =  C('cfg.qiniu_export')['domain'].'/'.$res['key'];
                $data['download_atime'] = date('Y-m-d H:i:s',time());
                M('export_programme')->where(['id'=>$id])->data($data)->save();
                $res['msg']= '已经上传完毕，请刷新页面后下载';
            }
        }

        if($this->debug) log_add('export_log',['function'=>__FUNCTION__,'time'=>date('Y-m-d H:i:s'),'id'=>$id,'total'=>$total,'res'=>$res]);
        return $res;
    }
    /**
     * subject: 检查是否已经全部导出
     * author: liangfeng
     * day: 2017-07-08
     */
    /*private function _check_is_finish($id,$total){
        $is_finish = true;
        for($i=1;$i<=$total;$i++){
            $rs = S('export_programme_'.$id.'_'.$i.'_finish');
            if($rs !== 1){
                $is_finish = false;
                break;
            }else{
                $excel_patch[] = $this->_get_path().$id.'_'.$i.'.xlsx';
            }
        }
        //全部已经导出excel
        if($is_finish === true){
            $zip_file_name = $this->_get_path().$id.'.zip';
            $res = $this->_zip_file($zip_file_name,$excel_patch);
            if($res['code'] == 1){
                S('export_programme_' . $id . '_zip',1,3600);
                $zip_file = $res['zip_file_name'];
                $res = $this->_upload_qiniu($zip_file);
                if($res['code'] == 1){
                    S('export_programme_' . $id . '_qiniu',1,3600);
                    @unlink($zip_file);
                    $data['download_path'] =  C('cfg.qiniu_export')['domain'].'/'.$res['key'];
                    $data['download_atime'] = date('Y-m-d H:i:s',time());
                    M('export_programme')->where(['id'=>$id])->data($data)->save();
                }
            }
        }else{
            $res = ['code'=>1,'msg'=>'文件完成，但没有全部完成，共'.$total.'份文件'];
        }
        if($this->debug) log_add('export_log',['function'=>__FUNCTION__,'time'=>date('Y-m-d H:i:s'),'id'=>$id,'total'=>$total,'res'=>$res]);
        return $res;
    }*/

    /**
     * subject: 退款导出
     * author: liangfeng
     * day: 2017-07-05
     */
	private function _export_refund($param,$p){
        $field_status_name = ['','退款','卖家拒绝','修改','同意','买家寄出商品','卖家寄出商品',10=>'买家可申诉',11=>'卖家未收到退货',12=>'买家未收到货',20=>'退款已取消',100=>'退款已完成'];
        $field_type_name = ['','退货退款','只退款','退运费'];
        $field_orders_status_name = ['已删除','已拍下','已付款','已发货','已收货','已评价','已归档','','','','已关闭','已关闭'];
        $field_pay_type_name = ['','余额','唐宝','微信','','支付宝','','银联'];

        $field_select = json_decode($param['field_select'],true);
        $map = $this->_assemble_condition($param['category_id'],$param['condition_select']);


        //组装Excel字段
        $out_option_orders = 'A';
        //主表不读取字段
        $notable_field = ['refund_total','orders_pay_time','orders_pay_type'];
        //需要后期计算字段
        $last_sum = array();
        $fields = 'id,';
        foreach($field_select as $k => $v){
            $out_excel_option[$out_option_orders]['descript'] = $v['field_label'];
            $out_excel_option[$out_option_orders]['field'] = $v['field_value'];
            $out_option_orders++;
            if(!in_array($v['field_value'],$notable_field)){
                $fields .= $v['field_value'].',';
            }else{
                $last_sum[] = $v['field_value'];
            }
        }
        $fields = substr($fields,0,strlen($fields)-1);


        //导出数据
        //$list	=	M('refund')->field($fields)->where($map)->order('id desc')->select();
        $export_list = array();
        $limit = (($p - 1) * $this->pagesize).','.$this->pagesize;
        $list = M('refund')->field($fields)->where($map)->limit($limit)->order('id desc')->select();

        //对导出数据进行转换
        foreach($list as $k=>$v){
            foreach($v as $ke => $va) {
                if(in_array($ke,['status','type','orders_status'])){
                    $v[$ke] = ${'field_'.$ke.'_name'}[$va];
                }
                if(in_array($ke,['seller_id','uid'])){
                    $v[$ke] = M('user')->cache(true)->where(['id'=>$va])->getField('nick');
                }
                if(in_array($ke,['shop_id'])){
                    $v[$ke] = M('shop')->cache(true)->where(['id'=>$va])->getField('shop_name');
                }
                if(in_array($ke,['s_no'])){
                    //后期计算的数据
                    if(in_array('orders_pay_time',$last_sum) || in_array('orders_pay_type',$last_sum)){
                        $order_info = M('orders_shop')->field('pay_type,pay_time')->where(['s_no'=>$v['s_no']])->find();
                        $v['orders_pay_time'] = $order_info['pay_time'];
                        $v['orders_pay_type'] = $field_pay_type_name[$order_info['pay_type']];
                    }
                }
            }
            //后期计算的数据
            if(in_array('refund_total',$last_sum)){
                $v['refund_total'] = $v['money']+$v['refund_express'];
            }

            $export_list[] = $v;
        }

        $file_path =  D('Admin/Excel')->outExcel($export_list,$out_excel_option,'退款订单信息',$this->_get_path().$param['id'].'_'.$p.'.xlsx',2);
        if(file_exists($file_path)){
            S('export_programme_'.$param['id'].'_'.$p.'_finish',1,3600);
            $res = ['code'=>1,'file_path'=>$file_path,'msg'=>'生成Excel文件'.$file_path.'成功'];
        }else{
            $res = ['code'=>0,'file_path'=>$file_path,'msg'=>'生成Excel文件'.$file_path.'失败'];
        }
        if($this->debug) log_add('export_log',['function'=>__FUNCTION__,'atime'=>date('Y-m-d H:i:s'),'param'=>$param,'res'=>$res]);
        return $res;
    }
    /**
     * subject: 话费订单导出
     * author: liangfeng
     * day: 2017-07-05
     */
    private function _export_mobile_orders($param,$p){
        $field_terminal_name = ['PC','WAP','IOS','ANDROID'];
        $field_pay_type_name = ['','余额','唐宝','微信','','支付宝','','银联'];
        $field_recharge_type_name = ['','话费','流量'];
        $field_status_name = ['已删除','已拍下','已付款','已发货','已收货','已评价','已归档','','','','已关闭','已关闭'];
        $field_operator_name = ['','移动','联通','电信'];
        $field_type_name = ['','奖励积分','不奖励积分'];


        $field_select = json_decode($param['field_select'],true);
        $map = $this->_assemble_condition($param['category_id'],$param['condition_select']);

        //组装Excel字段
        $out_option_orders = 'A';
        //主表不读取字段
        $notable_field = [''];

        $fields = 'id,';
        foreach($field_select as $k => $v){
            $out_excel_option[$out_option_orders]['descript'] = $v['field_label'];
            $out_excel_option[$out_option_orders]['field'] = $v['field_value'];
            $out_option_orders++;
            if(!in_array($v['field_value'],$notable_field)){
                $fields .= $v['field_value'].',';
            }
        }
        $fields = substr($fields,0,strlen($fields)-1);


        //导出数据
        //$list	=	M('mobile_orders')->field($fields)->where($map)->order('id desc')->select();
        $export_list = array();
        $limit = (($p - 1) * $this->pagesize).','.$this->pagesize;
        $list = M('mobile_orders')->field($fields)->where($map)->limit($limit)->order('id desc')->select();
        if(!$list) return ['code'=>3];
        //对导出数据进行转换
        foreach($list as $k=>$v){
            foreach($v as $ke => $va) {
                if(in_array($ke,['terminal','status','pay_type','recharge_type','operator','type'])){
                    $v[$ke] = ${'field_'.$ke.'_name'}[$va];
                }
                if(in_array($ke,['return_status','transtat'])){
                    $v[$ke] = $this->mobile_return_code($va,1)[$ke];
                }
                if(in_array($ke,['seller_id','uid'])){
                    $v[$ke] = M('user')->cache(true)->where(['id'=>$va])->getField('nick');
                }
                if(in_array($ke,['shop_id'])){
                    $v[$ke] = M('shop')->cache(true)->where(['id'=>$va])->getField('shop_name');
                }
            }
            $export_list[] = $v;
        }


        //return ['code'=>0,'list'=>$export_list];

        $file_path =  D('Admin/Excel')->outExcel($export_list,$out_excel_option,'退款订单信息',$this->_get_path().$param['id'].'_'.$p.'.xlsx',2);
        if(file_exists($file_path)){
            S('export_programme_'.$param['id'].'_'.$p.'_finish',1,3600);
            $res = ['code'=>1,'file_path'=>$file_path,'msg'=>'生成Excel文件'.$file_path.'成功'];
        }else{
            $res = ['code'=>0,'file_path'=>$file_path,'msg'=>'生成Excel文件'.$file_path.'失败'];
        }
        if($this->debug) log_add('export_log',['function'=>__FUNCTION__,'atime'=>date('Y-m-d H:i:s'),'param'=>$param,'res'=>$res]);
        return $res;
    }

    /**
     * subject: 订单导出
     * author: liangfeng
     * day: 2017-07-01
     */
    private function _export_orders_shop($param,$p){
        $field_status_name = ['已删除','已拍下','已付款','已发货','已收货','已评价','已归档','','','','已关闭','已关闭'];
        $field_pay_type_name = ['','余额','唐宝','微信','','支付宝','','银联'];
        $field_is_pay_name = ['未付款','已付款'];
        $field_terminal_name = ['PC','WAP','IOS','ANDROID'];
        $field_express_type_name = ['','快递','EMS'];
        $field_inventory_type_name = ['扣除货款模式','扣除库存积分模式'];

        $field_select = json_decode($param['field_select'],true);
		//组装条件语句
        $map = $this->_assemble_condition($param['category_id'],$param['condition_select']);



        //组装Excel字段
        $out_option_orders = 'A';
        //所有表都读取的字段
        $common_field = ['total_price','score'];
        //所有表都不读取的字段（后期计算）
        $common_no_field = ['inventory_monry'];
        //订单商品表独有字段
        $orders_goods_field = ['goods_name','price','profit_price','refund_express_price','refund_totals_price','score_ratio','cost_price','purchase_time','total_price_edit'];
        //主表不读取字段
        $notable_field = array_merge($common_no_field,$orders_goods_field);

        //return ['code'=>0,'notable_field'=>$notable_field];

        $fields = 'id,';
        $orders_goods_fields = 'id,';
        foreach($field_select as $k => $v){
            $out_excel_option[$out_option_orders]['descript'] = $v['field_label'];
            $out_excel_option[$out_option_orders]['field'] = $v['field_value'];
            $out_option_orders++;
            if(!in_array($v['field_value'],$notable_field)){
                $fields .= $v['field_value'].',';
            }
            if(in_array($v['field_value'],$orders_goods_field) || in_array($v['field_value'],$common_field)){
                $orders_goods_fields .= $v['field_value'].',';
            }

        }
        $fields = substr($fields,0,strlen($fields)-1);
        $orders_goods_fields = substr($orders_goods_fields,0,strlen($orders_goods_fields)-1);



        //导出数据
        //$list	=	M('orders_shop')->field($fields)->where($map)->order('id desc')->select();

        $export_list = array();
        $limit = (($p - 1) * $this->pagesize).','.$this->pagesize;
        $list = M('orders_shop')->field($fields)->where($map)->limit($limit)->order('id desc')->select();
        if(!$list) return ['code'=>3];
        //对导出数据进行转换
        foreach($list as $k=>$v){
            foreach($v as $ke => $va) {
                if(in_array($ke,['status','pay_type','terminal','is_pay','express_type','inventory_type'])){
                    $v[$ke] = ${'field_'.$ke.'_name'}[$va];
                }
                if(in_array($ke,['seller_id','uid'])){
                    $v[$ke] = M('user')->cache(true)->where(['id'=>$va])->getField('nick');
                }
                if(in_array($ke,['shop_id'])){
                    $v[$ke] = M('shop')->cache(true)->where(['id'=>$va])->getField('shop_name');
                }
            }
            $orders_goods = M('orders_goods')->field($orders_goods_fields)->where(['s_id'=>$v['id']])->select();
            foreach($orders_goods as $ke=>$va){
                $orders_goods[$ke]['profit_price'] = (string)round(($va['profit_price']+$va['refund_express_price']+$va['refund_totals_price']),2);
                //$orders_goods[$ke]['inventory_monry'] = (string)round("%.2f",substr(sprintf("%.3f", 0.08*$va['score']/100), 0, -1),2);
                $orders_goods[$ke]['inventory_monry'] = (string)round((($va['score'])*0.08/100),2);
                //订单累计真正的利润
                $v['profit_price'] += $orders_goods[$ke]['profit_price'];
                //订单累计分账金额
                $v['inventory_monry'] += $orders_goods[$ke]['inventory_monry'];
            }
            $v['profit_price'] = (string)round($v['profit_price'],2);
            $v['inventory_monry'] = (string)round($v['inventory_monry'],2);
            $export_list[] = $v;
            foreach($orders_goods as $va){
                $export_list[] = $va;
            }
        }

        //分批导入excel
        $file_path =  D('Admin/Excel')->outExcel($export_list,$out_excel_option,'订单信息',$this->_get_path().$param['id'].'_'.$p.'.xlsx',2);
        if(file_exists($file_path)){
            S('export_programme_'.$param['id'].'_'.$p.'_finish',1,3600);
            $res = ['code'=>1,'file_path'=>$file_path,'msg'=>'生成Excel文件'.$file_path.'成功'];
        }else{
            $res = ['code'=>0,'file_path'=>$file_path,'msg'=>'生成Excel文件'.$file_path.'失败'];
        }
        if($this->debug) log_add('export_log',['function'=>__FUNCTION__,'atime'=>date('Y-m-d H:i:s'),'param'=>$param,'res'=>$res]);
        return $res;

	}
    /**
     * subject: 压缩文件
     * api: /Export/zip_file
     * author: liangfeng
     * day: 2017-07-06
     *
     * [字段名,类型,是否必传,说明]
     * param: file_paths,array,1,文件路径
     */
	private function _zip_file($zip_file_name,$file_paths){

        $zip=new \ZipArchive;

        if(file_exists($zip_file_name)){
            $res = $zip->open($zip_file_name,\ZipArchive::OVERWRITE);
        }else{
            $res = $zip->open($zip_file_name,\ZipArchive::CREATE);
        }


        if($res===true) {
            foreach ($file_paths as $v) {
                if(file_exists($v) === true){
                    $new_file_name = str_replace($this->_get_path(),'',$v);
                    if($zip->addFile($v,$new_file_name) == false){
                        if($this->debug) log_add('export_log',['function'=>__FUNCTION__,'atime'=>date('Y-m-d H:i:s'),'new_file_name'=>$new_file_name,'file_paths'=>$v,'res'=>['code'=>0,'msg'=>'添加压缩文件失败，中断压缩']]);
                        return ['code'=>0,'msg'=>'添加压缩文件失败，中断压缩'];
                    }
                }else{
                    $zip->close();
                    if($this->debug) log_add('export_log',['function'=>__FUNCTION__,'atime'=>date('Y-m-d H:i:s'),'file_paths'=>$v,'res'=>['code'=>0,'msg'=>$v.'文件不存在，中断压缩。']]);
                    return ['code'=>0,'msg'=>$v.'文件不存在，中断压缩。'];
                }
            }
        }else{
            if($this->debug) log_add('export_log',['function'=>__FUNCTION__,'atime'=>date('Y-m-d H:i:s'),'zip_file_name'=>$zip_file_name,'file_paths'=>$file_paths,'res'=>['code'=>0,'msg'=>'生成压缩文件失败！']]);
            return ['code'=>0,'msg'=>'生成压缩文件失败！'];
        }
        $zip->close();
        //删除文件
        foreach ($file_paths as $v) {
            @unlink($v);
        }
        if($this->debug) log_add('export_log',['function'=>__FUNCTION__,'atime'=>date('Y-m-d H:i:s'),'zip_file_name'=>$zip_file_name,'file_paths'=>$file_paths,'res'=>['code'=>1,'zip_file_name'=>$zip_file_name,'msg'=>'生成压缩文件成功！']]);
        return ['code'=>1,'zip_file_name'=>$zip_file_name];
    }


    /**
     * subject: 组装条件
     * api: /Export/_assemble_condition
     * author: liangfeng
     * day: 2017-07-04
     *
     * [字段名,类型,是否必传,说明]
     * param: category_id,string,1,分类id
     * param: condition_select,string,1,条件json格式
     */
    private function _assemble_condition($category_id=0,$condition_select){
        if($category_id==0) return false;
        //将条件转为数组
        $condition_select = json_decode($condition_select,true);
        if($category_id == 1){
            //组装条件语句
            if(!empty($condition_select['inventory_type'])) $map['inventory_type'] = ['in',implode(',',$condition_select['inventory_type'])];
            if(!empty($condition_select['shop_type'])) $map['shop_type'] = ['in',implode(',',$condition_select['shop_type'])];
            if(!empty($condition_select['is_daigou'])) $map['is_daigou'] = ['in',implode(',',$condition_select['is_daigou'])];
            if(!empty($condition_select['status'])) $map['status'] = ['in',implode(',',$condition_select['status'])];
            if(!empty($condition_select['pay_type'])) $map['pay_type'] = ['in',implode(',',$condition_select['pay_type'])];
            if(!empty($condition_select['score_type'])) $map['score_type'] = ['in',implode(',',$condition_select['score_type'])];
            if(!empty($condition_select['terminal'])) $map['terminal'] = ['in',implode(',',$condition_select['terminal'])];

            switch($condition_select['time_interval']){
                case '1':
                    $sday = $condition_select['sday']?$condition_select['sday']:"2016-07-01";
                    $eday = $condition_select['eday']?$condition_select['eday']:date('Y-m-d',time());
                    break;
                case '2':
                    $sday = date('Y-m-d',time()-86400);
                    $eday = date('Y-m-d',time());
                    break;
                case '3':
                    $week_day = date('w',time());
                    $sday = $week_day == 1 ? date('Y-m-d',strtotime('last monday')) : date('Y-m-d',strtotime('-1 week last monday'));
                    $eday = date('Y-m-d',strtotime("last sunday"));
                    break;
                case '4':
                    $eday = date('Y-m-01',time());
                    $sday = date('Y-m-01',strtotime("$eday -1 month"));
                    break;
                case '5':
                    $eday = date('Y-01-01',time());
                    $sday = date('Y-m-01',strtotime("$eday -1 year"));
                    break;
            }
            $map[$condition_select['day_field']]	=	['between',[$sday,$eday]];

            $smoney = $condition_select['smoney']?$condition_select['smoney']:0;
            $emoney = $condition_select['emoney']?$condition_select['emoney']:100000000;
            $map[$condition_select['money_field']]	=	['between',[$smoney,$emoney]];

            $sql = array();
            if($condition_select['seller_nick']) $sql[]	=	' seller_id in (select id from '.C('DB_PREFIX').'user where nick="'.$condition_select['seller_nick'].'") ';
            if($condition_select['shop_name']) $sql[]	=	' shop_id in (select id from '.C('DB_PREFIX').'shop where shop_name="'.$condition_select['shop_name'].'") ';
            if(!empty($sql))	$map['_string']	=	implode(' and ',$sql);
        }else if($category_id == 2){
            //组装条件语句
            if(!empty($condition_select['terminal'])) $map['terminal'] = ['in',implode(',',$condition_select['terminal'])];
            if(!empty($condition_select['status'])) $map['status'] = ['in',implode(',',$condition_select['status'])];
            if(!empty($condition_select['pay_type'])) $map['pay_type'] = ['in',implode(',',$condition_select['pay_type'])];
            if(!empty($condition_select['score_type'])) $map['score_type'] = ['in',implode(',',$condition_select['score_type'])];
            if(!empty($condition_select['recharge_type'])) $map['recharge_type'] = ['in',implode(',',$condition_select['recharge_type'])];
            if(!empty($condition_select['operator'])) $map['operator'] = ['in',implode(',',$condition_select['operator'])];
            if(!empty($condition_select['return_status_sum'])) $map['return_status_sum'] = ['in',implode(',',$condition_select['return_status_sum'])];

            switch($condition_select['time_interval']){
                case '1':
                    $sday = $condition_select['sday']?$condition_select['sday']:"2016-07-01";
                    $eday = $condition_select['eday']?$condition_select['eday']:date('Y-m-d',time());
                    break;
                case '2':
                    $sday = date('Y-m-d',time()-86400);
                    $eday = date('Y-m-d',time());
                    break;
                case '3':
                    $week_day = date('w',time());
                    $sday = $week_day == 1 ? date('Y-m-d',strtotime('last monday')) : date('Y-m-d',strtotime('-1 week last monday'));
                    $eday = date('Y-m-d',strtotime("last sunday"));
                    break;
                case '4':
                    $eday = date('Y-m-01',time());
                    $sday = date('Y-m-01',strtotime("$eday -1 month"));
                    break;
                case '5':
                    $eday = date('Y-01-01',time());
                    $sday = date('Y-m-01',strtotime("$eday -1 year"));
                    break;
            }
            $map[$condition_select['day_field']]	=	['between',[$sday,$eday]];
        }else if($category_id == 3){
            //组装条件语句
            if(!empty($condition_select['terminal'])) $map['terminal'] = ['in',implode(',',$condition_select['terminal'])];
            if(!empty($condition_select['status'])) $map['status'] = ['in',implode(',',$condition_select['status'])];
            if(!empty($condition_select['pay_type'])) $map['pay_type'] = ['in',implode(',',$condition_select['pay_type'])];
            if(!empty($condition_select['score_type'])) $map['score_type'] = ['in',implode(',',$condition_select['score_type'])];
            if(!empty($condition_select['recharge_type'])) $map['recharge_type'] = ['in',implode(',',$condition_select['recharge_type'])];
            if(!empty($condition_select['operator'])) $map['operator'] = ['in',implode(',',$condition_select['operator'])];
            if(!empty($condition_select['return_status_sum'])) $map['return_status_sum'] = ['in',implode(',',$condition_select['return_status_sum'])];

            switch($condition_select['time_interval']){
                case '1':
                    $sday = $condition_select['sday']?$condition_select['sday']:"2016-07-01";
                    $eday = $condition_select['eday']?$condition_select['eday']:date('Y-m-d',time());
                    break;
                case '2':
                    $sday = date('Y-m-d',time()-86400);
                    $eday = date('Y-m-d',time());
                    break;
                case '3':
                    $week_day = date('w',time());
                    $sday = $week_day == 1 ? date('Y-m-d',strtotime('last monday')) : date('Y-m-d',strtotime('-1 week last monday'));
                    $eday = date('Y-m-d',strtotime("last sunday"));
                    break;
                case '4':
                    $eday = date('Y-m-01',time());
                    $sday = date('Y-m-01',strtotime("$eday -1 month"));
                    break;
                case '5':
                    $eday = date('Y-01-01',time());
                    $sday = date('Y-m-01',strtotime("$eday -1 year"));
                    break;
            }
            $map[$condition_select['day_field']]	=	['between',[$sday,$eday]];
        }


        return $map;
    }



    /**
     * subject: 将文件上传到七牛
     * api: /Export/upload_qiniu
     * author: liangfeng
     * day: 2017-07-04
     *
     * [字段名,类型,是否必传,说明]
     * param: file_path,string,1,文件路径
     */
	private function _upload_qiniu($file_path){
        //return ['code'=>0,'file_path'=>$file_path,'msg'=>'没有此文件可以上传'];
        if(!file_exists($file_path)){
            if($this->debug) log_add('export_log',['function'=>__FUNCTION__,'atime'=>date('Y-m-d H:i:s'),'param'=>$file_path,'res'=>['code'=>0,'msg'=>'没有此文件可以上传']]);
            return ['code'=>0,'msg'=>'没有此文件可以上传'];
        }
        auto_load('./ThinkPHP/Library/Vendor/Qiniu','listpath.php');
        C('qiniu',C('cfg.qiniu'));
        $auth = new \Qiniu\Auth(C('qiniu.ak'), C('qiniu.sk'));
        $token = $auth->uploadToken(C('cfg.qiniu_export')['bucket']);
        $Config=new \Qiniu\Config();

        $qn = new \Qiniu\Storage\FormUploader();

        $filesize=abs(filesize($file_path));
        if($filesize>=(1024*1024*20)){    //大于20M
            //使用分片式上传
            if($this->debug) log_add('export_log',['function'=>__FUNCTION__,'atime'=>date('Y-m-d H:i:s'),'param'=>$file_path,'res'=>['code'=>0,'filesize'=>$filesize,'msg'=>'文件超出大小，未能上传七牛']]);
            return ['code'=>0,'filesize'=>$filesize,'msg'=>'文件超出大小，未能上传七牛'];
        }else{
            $file_content = file_get_contents($file_path);
			$file_content = mb_convert_encoding($file_content,"UTF-8", "ASCII");
            $res =  $qn->put($token, null, $file_content,$Config);
			
            //$res =  $qn->putFile($token, null, $file_path,$Config);
        }


        if($res[0]['key'] !=''){
            if($this->debug) log_add('export_log',['function'=>__FUNCTION__,'time'=>date('Y-m-d H:i:s'),'param'=>$file_path,'res'=>['code'=>1,'msg'=>'上传七牛成功','key'=>$res[0]['key']]]);
            return ['code'=>1,'key'=>$res[0]['key']];
        }else{
            if($this->debug) log_add('export_log',['function'=>__FUNCTION__,'time'=>date('Y-m-d H:i:s'),'param'=>$file_path,'res'=>['code'=>0,'msg'=>'上传七牛失败','res'=>$res]]);
            return ['code'=>0,'msg'=>'上传七牛失败','res'=>$res];
        }

    }
    /**
     * subject: 获取文件存放
     * api: /Export/_get_root_path
     * author: liangfeng
     * day: 2017-07-04
     */
    private function _get_path(){
        //$path = str_ireplace(str_replace("/","\\",$_SERVER['PHP_SELF']),'',__FILE__)."\\";
        //$path = dirname(dirname(dirname(dirname($path))));
        //return $path;
        //return RUNTIME_PATH;
		return './file/';
    }

    /**
     * 话费充值状态码
     * Create by lazycat
     * 2017-05-13
     */
    private function mobile_return_code($val,$notips=0){
        $error_code = array(  //错误代码
            0   => '-',
            1   => '成功',
            2   => '重复订单， 与原交易不一致',
            3   => '单号重复， 交易已经接受',
            4   => '交易正在处理中',
            5   => '错误的交易指令',
            6   => '接口版本错',
            7   => '代理商校验错',
            8   => '不存在的代理商',
            9   => '其他错误',
            10  => '未定义(保留)',
            13  => '面值不正确',
            14  => '交易已经过期',
            17  => '超过约定交易限额',
            18  => '交易结果不能确定',
            20  => '校验失败',
            21  => '代理商已经暂停交易',
            22  => '交易品种没有定义',
            23  => '暂不支持指定号码充值',
            24  => '不能为该用户充值',
            25  => '指定充值号码与指定类别不一致',
            26  => '该代理商未开通该品种',
            28  => '成功金额小于申报金额',
            29  => '成功金额大于申报金额',
            30  => '充值号码错误',
            31  => '交易信息不存在',
            32  => '代理商错误率太高， 暂停',
            33  => '代理商余额不足',
            34  => '扣代理商款项失败',
            36  => '充值金额与交易金额不符',
            50  => '退款中',
            51  => '退款成功',
        );

        $post_success       = [1,3,4,10,18];    //受理成功编码！
        $recharge_success   = [1,29];           //充值成功编码！

        $res['transtat']        = $error_code[$val['transtat']];
        $res['return_status']   = $error_code[$val['return_status']];
        if($notips == 0){
            if(in_array($val['transtat'],$post_success))            $res['transtat']        = '<span class="text-success">已受理</span>';
            if(in_array($val['return_status'],$recharge_success))   $res['return_status']   = '<span class="text-success">充值成功</span>';
        }
        return $res;
    }
}