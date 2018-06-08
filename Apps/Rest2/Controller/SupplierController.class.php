<?php
/**
 * ----------------------------------------------------------
 * RestFull API V2.0
 * ----------------------------------------------------------
 * 供货商相关接口
 * ----------------------------------------------------------
 * Author:liangfeng
 * ----------------------------------------------------------
 * 2017-09-01
 * ----------------------------------------------------------
 */
namespace Rest2\Controller;
use Think\Controller\RestController;
use Think\Exception;
use Common\Builder\F;
class SupplierController extends ApiController {
    protected $action_logs = array();


	
	
	/**
     * subject: 获取供货商信息
     * api: /Supplier/get_info
     * author: liangfeng
     * day: 2017-09-01
     *
     * [字段名,类型,是否必传,说明]
     * param: username,string,1,用户名
     * param: password,string,1,密码
     */
    public function get_info(){
		$this->check('openid',false);
        $res = $this->_get_supplier_info($this->post);
        $this->apiReturn($res);
	}
	public function _get_info($param){
		try{
			$info = M('supplier_user')->where(['uid'=>$this->user['id']])->find();
			if(!$info) throw new Exception('获取信息失败');
			
			$package_info = M('supplier_package')->where(['uid'=>$this->user['id']])->order('id asc')->select();
			
			if($package_info){
				foreach($info['package'] as $v){
					$tmp = explode(',',$v['package']);
					foreach($tmp as $va){
						$info['packages'][] = $va;
					}
				}
			}
			
			$info['package'] = $package_info;
			
			$res = ['code'=>1,'data'=>$info];
			
        }catch (Exception  $e){
            $res = ['code'=>0,'msg' => $e->getMessage()];
        }
        return $res;
	}
	
	
    /**
     * subject: 注册供货商账号
     * api: /Supplier/register
     * author: liangfeng
     * day: 2017-09-01
     *
     * [字段名,类型,是否必传,说明]
     * param: username,string,1,用户名
     * param: password,string,1,密码
     */
    public function register(){
        $this->check('username,password,mobile',false);
        $res = $this->_register($this->post);
        $this->apiReturn($res);
    }
    public function _register($param){
        $data=[
            'openid'			=>$this->create_id(),
            'nick'				=>$param['username'],
            'password'			=>$param['password'],
            'mobile'			=>$param['mobile'],
            'level_id'			=>9,
            'ip'				=>get_client_ip(),
            'atime'				=>date('Y-m-d H:i:s')
        ];
        if(false !== M('user')->add($data)){
            $res = ['code'=>1,'msg'=>'注册成功！'];
        }else{
            $res = ['code'=>0];
        }
        return $res;
    }

	/**
     * subject: 入驻-获取供货商信息
     * api: /Supplier/get_supplier_info
     * author: liangfeng
     * day: 2017-09-01
     *
     * [字段名,类型,是否必传,说明]
     * param: username,string,1,用户名
     * param: password,string,1,密码
     */
	public function get_supplier_info(){
		$this->check('openid',false);
        $res = $this->_get_supplier_info($this->post);
        $this->apiReturn($res);
	}
	public function _get_supplier_info($param){
		try{
			$info = M('supplier_user')->where(['uid'=>$this->user['id']])->find();
			if(!$info) throw new Exception('获取信息失败');
			
			$package_info = M('supplier_package')->where(['uid'=>$this->user['id']])->order('id asc')->find();
			
			if($package_info){
				$package_info['package_select'] = explode(',',$package_info['package']);
			}
			
			$info['package'] = $package_info;
			
			$res = ['code'=>1,'data'=>$info];
			
        }catch (Exception  $e){
            $res = ['code'=>0,'msg' => $e->getMessage()];
        }
        return $res;
	}



    /**
     * subject: 入驻申请 - 个人
     * api: /Supplier/settled_personal
     * author: liangfeng
     * day: 2017-09-01
     *
     * [字段名,类型,是否必传,说明]
     * param: openid,int,1,用户openid
     */
    public function settled_personal(){
        $this->check('openid,name,id_no,mobile,province,city,street,id_pic1,id_pic2,id_pic3,bank,bank_province,bank_city,bank_open,bank_user,bank_no,type',false);
        $res = $this->_settled($this->post);
        $this->apiReturn($res);
    }

    /**
     * subject: 入驻申请 - 企业
     * api: /Supplier/settled_enterprise
     * author: liangfeng
     * day: 2017-09-01
     *
     * [字段名,类型,是否必传,说明]
     * param: openid,int,1,用户openid
     */
    public function settled_enterprise(){
        $this->check('openid,company_name,name,id_no,mobile,province,city,street,credit_code,id_pic1,id_pic2,id_pic3,business_pic,bank,bank_province,bank_city,bank_open,bank_user,bank_no,type',false);
        $res = $this->_settled($this->post);
        $this->apiReturn($res);
    }

    public function _settled($param){
        try{
			$data = $param;
			$info = M('supplier_user')->where(['uid'=>$this->user['id']])->find();
			if($info){
				
				if(in_array($info['status'],['2','3'])) throw new Exception('入驻申请正在审核或审核成功，不能修改！');
				
				$data['id'] = $info['id'];
				
			}
			
            $data['uid'] = $this->user['id'];
			$data['no'] = 'NO'.date('YmdHis');
            $db = D('Common/SupplierUser');
            if(!$res = $db->create($data)) throw new Exception($db->getError());
			
            
			if($info){
				if(false === $db->save($data)) throw new Exception('修改失败');
			}else{
				if(!$db->add($data)) throw new Exception('添加失败');
			}
			$res = ['code'=>1];
			
        }catch (Exception  $e){
            $res = ['code'=>0,'msg' => $e->getMessage()];
        }
        return $res;
    }

    /**
     * subject: 入驻申请 - 选择套餐
     * api: /Supplier/package_save
     * author: liangfeng
     * day: 2017-09-11
     *
     * [字段名,类型,是否必传,说明]
     * param: openid,int,1,用户openid
     * param: package,string,1,选择的套餐 1.金积分 2.现金交易 4.银积分
     */
    public function package_save(){
        $this->check('openid,package',false);
        $res = $this->_package_save($this->post);
        $this->apiReturn($res);
    }
    public function _package_save($param){
        try{
			$package_info = M('supplier_package')->where(['uid'=>$this->user['id']])->find();
			if($package_info){
				$data['id'] = $package_info['id'];
                if(in_array($package_info['status'],['2','3'])) throw new Exception('入驻申请正在审核或审核成功，不能修改！');
			}
			
			
            $data['package'] = $param['package'];
            $data['uid'] = $this->user['id'];

            $supplier_config = C('cfg.supplier');
            $package = explode(',',$data['package']);
            $data['money'] = 0;
            foreach($package as $v){
                if($v == 1){
                    $data['money'] += $supplier_config['gold_price'];
                    $data['gold_num'] = $supplier_config['gold_num'];
                }else if($v == 2){
                    $data['money'] += $supplier_config['cash_price'];
                    $data['cash_num'] = $supplier_config['cash_num'];
                }else if($v == 4){
                    $data['money'] += $supplier_config['silver_price'];
                    $data['silver_num'] = $supplier_config['silver_num'];
                }
            }

            $db = D('Common/SupplierPackage');
            if(!$res = $db->create($data)) throw new Exception($db->getError());
			
			if($package_info){
				if(false === $db->save($data)) throw new Exception('修改失败');
			}else{
				if(!$id = $db->add($data)) throw new Exception('添加失败');
			}
			
            
            $res = ['code'=>1];
        }catch (Exception  $e){
            $res = ['code'=>0,'data'=>$data,'msg' => $e->getMessage()];
        }
        return $res;
    }

	/**
     * subject: 入驻申请 - 上传付款凭证
     * api: /Supplier/package_save
     * author: liangfeng
     * day: 2017-09-12
     *
     * [字段名,类型,是否必传,说明]
     * param: openid,int,1,用户openid
     * param: id,int,1,套餐id
     * param: pay_type,string,1,付款方式 1.银行卡 2.支付宝 3.微信
     * param: pay_price,string,1,付款金额
     * param: voucher,string,1,付款凭证
     * param: bank,string,0,付款银行
     * param: bank_user_name,string,0,户名
     * param: bank_account,string,0,卡号
     */
    public function upload_voucher(){
        $this->check('openid,id,pay_type,pay_price,voucher',false);
        $res = $this->_upload_voucher($this->post);
        $this->apiReturn($res);
    }
    public function _upload_voucher($param){
        try{
			
			$info = M('supplier_package')->where(['id'=>$param['id'],'uid'=>$this->user['id']])->find();
			if(!$info) throw new Exception('ID错误');

            if(in_array($info['status'],['2','3'])) throw new Exception('入驻申请正在审核或审核成功，不能修改！');
            
			$data = $param;
			$data['uid'] = $this->user['id'];
			

            $db = D('Common/SupplierPackage');
            if(!$res = $db->create($data)) throw new Exception($db->getError());
            if(false === $db->save($data)) throw new Exception('修改失败');
            $res = ['code'=>1,'id'=>$id];
        }catch (Exception  $e){
            $res = ['code'=>0,'data'=>$data,'msg' => $e->getMessage()];
        }
        return $res;
    }
	
	/**
     * subject: 入驻申请 - 申请审核
     * api: /Supplier/examine
     * author: liangfeng
     * day: 2017-09-12
     *
     * [字段名,类型,是否必传,说明]
     * param: openid,int,1,用户openid
     */
    public function examine(){
        $this->check('openid',false);
        $res = $this->_examine($this->post);
        $this->apiReturn($res);
    }
    public function _examine($param){
        try{
			$res = $this->_get_supplier_info($param);
			if($res['code'] != 1) throw new Exception($res['msg']);
			if(in_array($res['data']['status'],['2','3']))  throw new Exception('正在审核中或已经通过审核，请勿重复提交申请');
			
			$do = M();
			$do->startTrans();
			
			if(false === M('supplier_user')->where(['id'=>$res['data']['id']])->data(['status'=>2])->save()){
				$do->rollback();
				throw new Exception('提交失败');
			}
			
			if(false === M('supplier_package')->where(['id'=>$res['data']['package']['id']])->data(['status'=>2])->save()){
				$do->rollback();
				throw new Exception('提交失败！');
			}
			
            $do->commit();
			
            $res = ['code'=>1,'data'=>$res,'msg'=>'提交成功，请耐心等待！'];
        }catch (Exception  $e){
			
            $res = ['code'=>0,'msg' => $e->getMessage()];
        }
        return $res;
    }
    /**
     * subject: 修改资料
     * api: /Supplier/save_edit_info
     * author: liangfeng
     * day: 2017-09-12
     *
     * [字段名,类型,是否必传,说明]
     * param: openid,int,1,用户openid
     */
    public function save_edit_info(){
        $this->check('openid',false);
        $res = $this->_save_edit_info($this->post);
        $this->apiReturn($res);
    }
    public function _save_edit_info($param){
        try{
            $res = $this->_get_supplier_info($param);
            if($res['code'] != 1) throw new Exception($res['msg']);
            if($res['data']['status'] != 3) throw new Exception('请先通过审核！');

            $data['about'] = $param['about'];
            $data['logo'] = $param['logo'];
            $data['qq'] = $param['qq'];
            $data['tel'] = $param['tel'];
            $data['wang'] = $param['wang'];
            $data['email'] = $param['email'];

            if(false === M('supplier_user')->where(['id'=>$res['data']['id']])->data($data)->save()){
               throw new Exception('提交失败');
            }
            $res = ['code'=>1,'data'=>$param,'msg'=>'修改成功'];
        }catch (Exception  $e){

            $res = ['code'=>0,'msg' => $e->getMessage()];
        }
        return $res;
    }
	/**
     * subject: 营业额明细
     * api: /Supplier/sale_list
     * author: liangfeng
     * day: 2017-09-18
     *
     * [字段名,类型,是否必传,说明]
     * param: openid,int,1,用户openid
     */
	 
    public function sale_list(){
        $this->check('openid',false);
        $res = $this->_sale_list($this->post);
        $this->apiReturn($res);
    }
    public function _sale_list($param){
        try{
			//M('orders_shop')->where(['supplier_id'=>$this->user['id']])->select();
			
			$map['supplier_id'] = $this->user['id'];
			$map['status'] = ['in','4,5'];
			
			$pagesize=$param['pagesize']?$param['pagesize']:2;
			$pagelist=pagelist(array(
                'table'     =>'Common/OrdersShopSellerRelation',
                'do'        =>'D',
                'pagesize'  =>$pagesize,
                'map'       =>$map,
                'order'     =>'atime desc',
                'relation'  =>true,
                'action'    =>$param['action'],
                'query'     =>$param['query'],
                'p'         =>$param['p'],
            ));
			if(!$pagelist) throw new Exception('没有记录');
			
			foreach($pagelist['list'] as $k => $v){
				//支付类别名称
				$pagelist['list'][$k]['score_type_name'] = [1=>'金积分',2=>'现金',4=>'银积分'][$v['score_type']];
				
				//赠送积分
				//$pagelist['list'][$k]['score'] = $va['orders_goods'][0]['score'];
				
				
				//商家结算额
				$settlement = 0;
				if($v['score_type'] == 2){
					//计算商品价格的返还金额
					$settlement = 0;
					//现金计算商品价格
					foreach($v['orders_goods'] as $va){
						//价格比例
						$ratio = (($va['score_ratio']*12)*0.01);
						$settlement += $va['total_price_edit']*(1-$ratio);
					}
					$settlement += $v['express_price'];
				}else{
					//所有成本价相加
					foreach($v['orders_goods'] as $va){
						$settlement += $va['price_purchase'];
					}
				}
				
				$pagelist['list'][$k]['settlement'] = $settlement;
			}
			
            $res = ['code'=>1,'data'=>$pagelist];
        }catch (Exception  $e){
			
            $res = ['code'=>0,'msg' => $e->getMessage()];
        }
        return $res;
    }
	
	/**
     * subject: 提现列表
     * api: /Supplier/withdrawals_list
     * author: liangfeng
     * day: 2017-09-19
     *
     * [字段名,类型,是否必传,说明]
     * param: openid,int,1,用户openid
     */
	 
    public function withdrawals_list(){
        $this->check('openid',false);
        $res = $this->_withdrawals_list($this->post);
        $this->apiReturn($res);
    }
    public function _withdrawals_list($param){
        try{
			$map['uid'] = $this->user['id'];
			
			$pagesize=$param['pagesize']?$param['pagesize']:10;
			$pagelist=pagelist(array(
                'table'     =>'supplier_turnover',
                'do'        =>'M',
                'pagesize'  =>$pagesize,
                'map'       =>$map,
                'order'     =>'atime desc',
                
                'action'    =>$param['action'],
                'query'     =>$param['query'],
                'p'         =>$param['p'],
            ));
			if(!$pagelist) throw new Exception('没有记录');
			
			foreach($pagelist['list'] as $k => $v){
				//支付类别名称
				$pagelist['list'][$k]['status_name'] = [1=>'待审核',2=>'已通过',3=>'已驳回'][$v['status']];
				$pagelist['list'][$k]['charge'] = $v['money']-$v['real_money'];
			}
			
            $res = ['code'=>1,'data'=>$pagelist];
        }catch (Exception  $e){
			
            $res = ['code'=>0,'msg' => $e->getMessage()];
        }
        return $res;
    }
	/**
     * subject: 提现申请
     * api: /Supplier/withdrawals
     * author: liangfeng
     * day: 2017-09-12
     *
     * [字段名,类型,是否必传,说明]
     * param: openid,int,1,用户openid
     */
	 
    public function withdrawals(){
        $this->check('openid,money',false);
        $res = $this->_withdrawals($this->post);
        $this->apiReturn($res);
    }
    public function _withdrawals($param){
		try{
			
			$supplier_info = M('supplier_user')->field(['sale_money,withdrawals_money,bank,bank_no,bank_user,bank_province,bank_city,bank_open'])->where(['uid'=>$this->user['id']])->find();
			$withdrawals_money = $supplier_info['sale_money'] - $supplier_info['withdrawals_money'];
			
			if($param['money'] < C('cfg.supplier')['withdrawals_money_min']) throw new Exception('每次提现金额必须大于'.C('cfg.supplier')['withdrawals_money_min'].'元');
			
			if($param['money'] > C('cfg.supplier')['withdrawals_money_max']) throw new Exception('每次提现金额不得超过'.C('cfg.supplier')['withdrawals_money_max'].'元');
			
			
			if($param['money'] > $withdrawals_money) throw new Exception('超过可提现金额');

            $month = date('Y-m',time());
            $month_count = M('supplier_turnover')->where(['_string'=>'date_format(atime,"%Y-%m")="'.$month.'" and status in (1,2) and uid = "'.$this->user['id'].'"'])->count();
            if($month_count >= C('cfg.supplier')['withdrawals_month_num']) throw new Exception('每月最多提现'.C('cfg.supplier')['withdrawals_month_num'].'笔');

            $day = date('Y-m-d',time());
            $day_count = M('supplier_turnover')->where(['_string'=>'date_format(atime,"%Y-%m-%d")="'.$day.'" and status in (1,2) and uid = "'.$this->user['id'].'"'])->count();
            if($day_count >= C('cfg.supplier')['withdrawals_day_num']) throw new Exception('每日最多提现'.C('cfg.supplier')['withdrawals_day_num'].'笔');


			$real_money = $param['money'] * (1-C('cfg.supplier')['charge']*0.01);
			
			$do = M();
			$do->startTrans();
			
			$data['money'] = $param['money'];
			$data['real_money'] = $real_money;
			$data['uid'] = $this->user['id'];
			$data['bank_no'] = $supplier_info['bank_no'];
			$data['bank_user'] = $supplier_info['bank_user'];
			$area = $this->cache_table('area');
			$data['bank_open_address'] = $area[$supplier_info['bank_province']].$area[$supplier_info['bank_city']].$supplier_info['bank_open'];
			$banks = F::getBankName();
			foreach($banks as $va){
				if($supplier_info['bank'] = $va['id']){
					$data['bank'] = $va['bank_name'];
					break;
				}
			}
            //writelog($data);
			//新增提现申请
			if(M('supplier_turnover')->data($data)->add() === false){
                $do->rollback();
                throw new Exception('操作失败');
            }
			
			//将金额加到提现金额
			$data2['withdrawals_money'] = $supplier_info['withdrawals_money']+$param['money'];
			
			if(M('supplier_user')->where(['uid'=>$this->user['id']])->data($data2)->save() === false){
                $do->rollback();
                throw new Exception('修改提现金额失败!');
            }
			
			$do->commit();
			
            $res = ['code'=>1];
        }catch (Exception  $e){
            //writelog(M()->getlastsql());
            $res = ['code'=>0,'msg' => $e->getMessage()];
        }
        return $res;
	}
}