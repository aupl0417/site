<?php
namespace Wap\Controller;
use Think\Controller;
use Common\Controller\OrdersExpireController;
use Common\Controller\OrdersExpireActionController;
use Common\Controller\TotalController;
class ToolsController extends CommonController {


	public function test123(){
	
		C('DEBUG_API',true);
		/*$data = [
		'openid'  =>'cbae6ef4494696e047cf44260adc26b6',
		'uid' =>700100,
		'no' =>'2016120607402162241645',
		];*/
		//$res = $this->doApi('https://rest.trj.cc/LuckdrawTest/award_winning',$data);
		//dump('************************************');
		//$res = $this->doApi('http://www.dtmall2.com/Totals/total_date',$data);
		
		$orders=new \Common\Controller\TotalsController();
		$res=$orders->total_date();
		//$res=$orders->shop_totals(395);
		//$res=$orders->shop_totals_bat();
		
		
	}
 
	public function t2(){

        C('MEMCACHED_HOST','10.0.0.55');
        C('MEMCACHED_PORT',12000);
        $c = new \Think\Cache\Driver\Memcached();

        dump($c);


	}

	public function import_keyword(){
        @ini_set('memory_limit','2048M');
	    set_time_limit(0);
	    $file = file_get_contents('./Runtime/key.txt');
        $file = explode(chr(10),$file);
        $do =M('keywords_lib');
        foreach($file as $val){
            if(trim($val)){
                if(!$do->where(['keyword' => trim($val)])->find()){
                    $do->add(['keyword' => trim($val)]);
                }
            }
        }
    }

    public function enhong(){
        $shop_id = 243;
        if(I('get.shop_id')) $shop_id = I('get.shop_id');
	    $do = M('orders_shop');
	    $list = $do->where(['shop_id'=>$shop_id,'status' => 2])->order('pay_time desc,id desc')->select();
	    if(empty($list)) {
	        echo 'no orders';
	        exit();
        }

        $area = $this->cache_table('area');

	    foreach($list as $key => $val){
	        $order = M('orders')->where(['id'=> $val['o_id']])->find();
            $order['province']    =$area[$order['province']];
            $order['city']        =$area[$order['city']];
            $order['district']    =$area[$order['district']];
            $order['town']        =$area[$order['town']];

            $goods = M('orders_goods')->where(['s_id' => $val['id']])->select();
            foreach($goods as $k => $v){
                $goods[$k]['taobao'] = M('goods_tmall')->where(['goods_id' => $v['goods_id']])->find();
            }

            $list[$key]['address'] = $order;
            $list[$key]['goods'] = $goods;
        }

        $this->assign('orders',$list);
        $this->display();
    }

	public function tt(){
		set_time_limit(0);
		//dump(C('cfg.orders'));
		$o=new OrdersExpireController();
		$res=$o->goods_images();
		//dump($res);

		$res2=$o->goods_attr_list_images();


		$o=new OrdersExpireActionController();
		foreach($res as $val){
			$res=$o->_goods_images($val);
			usleep(1000);
		}

		foreach($res2 as $val){
			$res=$o->_goods_attr_list_images($val);
			usleep(1000);
		}		

		//
		//$res=$o->_goods_attr_list_images(13877);
		//dump($res);		
	
		//dump(1 %200);
		//log_add('cron_orders',['atime'=>date('Y-m-d H:i:s'),'r_no'=>$r_no,'res'=>$res['code'],'function'=>__FUNCTION__]);
		//C('DEBUG_API',ture);
		//$res=$this->doApi('/Upload/upload_remote',array('url' => 'https://www.dttx.com/app/www/template/cn/share/themes/default/images/erp_logo.png'));
		//dump($res);
	}

	public function tt2(){
        set_time_limit(0);
        $count = M('goods')->count();
        $page = ceil($count/200);

        $p=I('get.p') ? I('get.p') : 1;

        $list =M('goods')->page($p)->limit(200)->order('pr desc')->getField('id',true);

        if(empty($list)) exit;
        foreach($list as $val){
            dump(goods_pr($val));
            usleep(5000);
        }

        //sleep(2);
        gourl('/Tools/tt2/p/'.($p+1));
    }

	public function scws(){
		  Vendor('phpanalysis.phpanalysis#class');
		  $pa=new \PhpAnalysis();
		  $pa->SetSource("卓梵 阿玛尼男士真皮手拎包单肩公文包牛皮14寸手提包");
		  $pa->resultType=2;
		  $pa->differMax=true;
		  $pa->StartAnalysis();
		  $arr=$pa->GetFinallyKeywords();
		  echo "<pre>";
		  print_r($arr);
		  echo "</pre>";		
	}
    public function index(){	
    	/*
    	$list=M('goods')->field('id')->select();
    	foreach($list as $val){
    		goods_pr($val['id']);
    	}

    	$list=M('shop')->field('id')->select();
    	foreach($list as $val){
    		M('shop')->where('id='.$val['id'])->setField('goods_num',M('goods')->where(['shop_id' => $val['id'],'status' =>1])->count());
    		shop_pr($val['id']);
    	}






    	$apiurl='/Erp/check_login';
		$data['username']	='ranqin001';
		$data['password']	=$this->password('88888888');

		C('DEBUG_API',true);
		$res=$this->doApi($apiurl,$data);
		dump($res);


    	$apiurl='/Erp/user_info';
		$data['openid']	='01c93216e451e6ceb057ba75c3309492';

		C('DEBUG_API',true);
		$res=$this->doApi($apiurl,$data);
		dump($res);


		$apiurl='/Erp/sms_code';
		$data['mobile']	='13710356176';

		C('DEBUG_API',true);
		$res=$this->doApi($apiurl,$data);
		dump($res);


		$apiurl='/Erp/check_mobile';
		$data['mobile']	='13710356076';

		C('DEBUG_API',true);
		$res=$this->doApi($apiurl,$data);
		dump($res);



		$apiurl='/Erp/sms_code';
		$data['mobile']	='13710356170';

		C('DEBUG_API',true);
		$res=$this->doApi($apiurl,$data);
		dump($res);






		$apiurl='/Erp/account';
		$data['openid']='01c93216e451e6ceb057ba75c3309492';

		C('DEBUG_API',true);
		$res=$this->doApi($apiurl,$data);
		dump($res);






		$apiurl='/Erp/check_password';
		$data['openid']='51c0e072e3f72ff3893be72cb478b9b4';
		$data['password']=$this->password('123456');

		C('DEBUG_API',true);
		$res=$this->doApi($apiurl,$data);
		dump($res);


		$apiurl='/Erp/get_user_info';
		$data['username']='tel13710356170';

		C('DEBUG_API',true);
		$res=$this->doApi($apiurl,$data);
		dump($res);


		$apiurl='/Erp/register_company';
        $data=[
            'organize'          =>1,
            'company'      		=>'广州张古大有限公司10216',
            'company_license'   =>'4414525855455',
            'mobile'            =>'13710356171',
            'smscode'           =>'913250',
            'ref'          		=>0,
            'username'          =>'dt20160309',
            'password'          =>$this->password('123456')
        ];

		C('DEBUG_API',true);
		$res=$this->doApi($apiurl,$data,'ref');
		dump($res);


		$apiurl='/Erp/sms_code';
		$data['mobile']	='13710356172';

		C('DEBUG_API',true);
		$res=$this->doApi($apiurl,$data);
		dump($res);


		$apiurl='/Erp/register';
        $data=[
            'mobile'        =>'13710356172',
            'smscode'       =>'264536',
            'password'      =>$this->password('123456'),
        ];

		C('DEBUG_API',true);
		$res=$this->doApi($apiurl,$data,'ref,country');
		dump($res);


		$apiurl='/Erp/sms_code';
		$data['mobile']	='13710356172';

		C('DEBUG_API',true);
		$res=$this->doApi($apiurl,$data);
		dump($res);



		$apiurl='/Erp/forgot_password';
		$data['mobile']	='13710356172';
		$data['username']	='tel13710356172';
		$data['smscode']	='241382';

		C('DEBUG_API',true);
		$res=$this->doApi($apiurl,$data);
		dump($res);


		$apiurl='/Erp/change_password';
		$data['openid']	='ba18b288a815c848fa08bb08db0c814d';
		$data['opassword']	=$this->password('123456');
		$data['password']	=$this->password('654321');

		C('DEBUG_API',true);
		$res=$this->doApi($apiurl,$data);
		dump($res);


		$apiurl='/Erp/admin_login';
		$data['username']	='dttx00001';
		$data['password']	='88888888';

		C('DEBUG_API',true);
		$res=$this->doApi($apiurl,$data);


		$apiurl='/Erp/admin_sync';

		C('DEBUG_API',true);
		$res=$this->doApi($apiurl,$data);
		dump($res);
		*/

		/*
		$apiurl='/Erp/top_news';

		C('DEBUG_API',true);
		$res=$this->doApi($apiurl,$data);
		dump($res);		
		*/

		/*					
    	$apiurl='/Erp/check_login';
		$data['username']	='ranqin001';
		$data['password']	=$this->password('88888888');

		C('DEBUG_API',true);
		$res=$this->doApi($apiurl,$data);
		dump($res);	
		*/

		/*
		$apiurl='/Erp/forgot_pay_password';
		$data['mobile']	='13710356172';
		$data['username']	='tel13710356172';
		$data['smscode']	='241382';
		$data['password']	=$this->password('565858');

		C('DEBUG_API',true);
		$res=$this->doApi($apiurl,$data);
		dump($res);		
		*/

		/*
		$apiurl='/Erp/get_user_info';
		$data['code']='liangdw';
        
		C('DEBUG_API',true);
		$res=$this->doApi($apiurl,$data);
		dump($res);		
		

		$apiurl='/Erp/orders_group_pay';
		$data['openid']='ba18b288a815c848fa08bb08db0c814d';
		$data['o_no']='2016063011574687524475';
        
		C('DEBUG_API',true);
		$res=$this->doApi($apiurl,$data);
		dump($res);		

			

		$apiurl='/Erp/check_orders_status';
		//$data['openid']='ba18b288a815c848fa08bb08db0c814d';
		$data['s_no']='2016062411542140740227';
        
		C('DEBUG_API',true);
		$res=$this->doApi($apiurl,$data);
		dump($res);
		

		$apiurl='/Erp/check_pay_password';
		$data['openid']='f2cffee3235e9eb5d1d021217832da4f';
		$data['password']=$this->password('456789');

      
		C('DEBUG_API',true);
		$res=$this->doApi($apiurl,$data);
		dump($res);		
		

		$apiurl='/Erp/orders_group_pay';
		$data['openid']='01c93216e451e6ceb057ba75c3309492';
		$data['o_no']='2016062418445947776153';
		$data['password_pay']='123456';
		$data['paytype']=1;
        
		C('DEBUG_API',true);
		$res=$this->doApi($apiurl,$data);
		dump($res);		
		

		C('DEBUG_API',true);
    	$data=$this->api_cfg;
        $data['sign']=$this->_sign($data,'limit,imgsize');
    	$res=$this->curl_post(C('cfg.api')['apiurl'].'/Goods/love_goods',$data);
    	print_r($res);
    	$res=json_decode($res);
    	//print_r($res);

        if($res->code!=1){
            //$this->err($res->msg);
        }	

       

		
		$apiurl='/Erp/sms_code';
		$data['mobile']	='13710356177';

		C('DEBUG_API',true);
		$res=$this->doApi($apiurl,$data);
		dump($res); 


		$apiurl='/Erp/register_company';
        $data=[
            'organize'          =>1,
            'company'      		=>'广州大千有限公司10216',
            'company_license'   =>'441444554455',
            'mobile'            =>'13710356177',
            'smscode'           =>'553335',
            'ref'          		=>0,
            'username'          =>'dt201610000',
            'password'          =>$this->password('123456')
        ];
        
		C('DEBUG_API',true);
		$res=$this->doApi($apiurl,$data,'ref');
		dump($res);	    
		
		

		C('DEBUG_API',true);
		$data['shop_id']=1;
		$res=$this->doApi('/Shop/rate',$data);
    	print_r($res);
    	$res=json_decode($res);
    	//print_r($res);

        if($res->code!=1){
            //$this->err($res->msg);
        }

        $data=$this->api_cfg;
        $data['openid']='01c93216e451e6ceb057ba75c3309492';
        $data['orders_goods_id']=292;
        $data['rate']='1';
        $data['content']='非常好1';
        $data['images']='http://mall.yunkan.com/Public/images/logo.png';
        $data['sign']=$this->_sign($data,'images');


        $res=$this->curl_post(C('cfg.api')['apiurl'].'/Orders/goods_rate',$data);
        dump($res);
        $res=json_decode($res);
        dump($res);
        if($res->code!=1){
            //$this->err($res->msg);
        }   
          

		C('DEBUG_API',true);
		$data['openid']='01c93216e451e6ceb057ba75c3309492';
		$data['shop_id']=1;
		$res=$this->doApi('/ShopFav/delete',$data);
    	print_r($res);
    	$res=json_decode($res);
    	//print_r($res);

        if($res->code!=1){
            //$this->err($res->msg);
        }
        

		C('DEBUG_API',true);
		$data['shop_id']=1;
		$res=$this->doApi('/Shop/goods_rand',$data);
    	print_r($res);
    	$res=json_decode($res);
    	//print_r($res);

        if($res->code!=1){
            //$this->err($res->msg);
        } 

		C('DEBUG_API',true);
		$data['shop_id']=1;
		$res=$this->doApi('/Shop/category',$data);
    	print_r($res);
    	$res=json_decode($res);
    	//print_r($res);

        if($res->code!=1){
            //$this->err($res->msg);
        }    
        

		C('DEBUG_API',true);
		$data['openid']='e80582abe3013bcb305d731add59d9c3';
		$data['id']=357;
		$res=$this->doApi('/Cart/cart_item',$data);
    	print_r($res);
    	$res=json_decode($res);
    	//print_r($res);

        if($res->code!=1){
            //$this->err($res->msg);
        }     
         
		C('DEBUG_API',true);
		$data['openid']='e80582abe3013bcb305d731add59d9c3';
		$res=$this->doApi('/Orders/wait_rate_goods',$data);
    	print_r($res);
    	$res=json_decode($res);
    	//print_r($res);

        if($res->code!=1){
            //$this->err($res->msg);
        }   
        

		C('DEBUG_API',true);
		$data['openid']='e80582abe3013bcb305d731add59d9c3';
		$data['s_no']	='2016062813521661727502';
		$res=$this->doApi('/Orders/shop_from_sno',$data);
    	print_r($res);
    	$res=json_decode($res);
    	//print_r($res);

        if($res->code!=1){
            //$this->err($res->msg);
        }    
        

		C('DEBUG_API',true);
		$data['openid']='e80582abe3013bcb305d731add59d9c3';
		$data['orders_goods_id']	='317';
		$data['content']	='不错哦';
		$data['rate']=1;
		$res=$this->doApi('/Orders/goods_rate',$data);
    	print_r($res);
    	$res=json_decode($res);
    	//print_r($res);

        if($res->code!=1){
            //$this->err($res->msg);
        }   

        
		C('DEBUG_API',true);
		$res=$this->doApi('/Goods/shop_list',$data);
    	print_r($res);
    	$res=json_decode($res);
    	//print_r($res);

        if($res->code!=1){
            //$this->err($res->msg);
        }   

		$apiurl='/Erp/sms_code';
		$data['mobile']	='13710356176';

		C('DEBUG_API',true);
		$res=$this->doApi($apiurl,$data);
		dump($res);
 

		$apiurl='/Erp/forgot_password_step1';
		$data['username']='tel13710356176';
		$data['smscode']='985782';
		$data['mobile']	='13710356176';

      
		C('DEBUG_API',true);
		$res=$this->doApi($apiurl,$data);
		dump($res);	
		

		$apiurl='/Erp/check_open_shop';
		$data['openid']='e80582abe3013bcb305d731add59d9c3';
      
		C('DEBUG_API',true);
		$res=$this->doApi($apiurl,$data);
		dump($res);			
		

		$apiurl='/Cart/cart_total';
		$data['openid']='e80582abe3013bcb305d731add59d9c3';
      
		C('DEBUG_API',true);
		$res=$this->doApi($apiurl,$data);
		dump($res);
				                                       

		$apiurl='/Erp/orders_pay';
		$data['openid']='e80582abe3013bcb305d731add59d9c3';
		$data['s_no']='2016063012103806191318';
		$data['password_pay']=$this->password('654321');
		$data['paytype']=1;
        
		C('DEBUG_API',true);
		$res=$this->doApi($apiurl,$data);
		dump($res);	
		

		$apiurl='/Orders/rate_shop_info';
		$data['openid']='e80582abe3013bcb305d731add59d9c3';
		$data['s_no']='2016063012103806191318';
        
		C('DEBUG_API',true);
		$res=$this->doApi($apiurl,$data);
		dump($res);	

		$apiurl='/Orders/shop_rate';
		$data['openid']='e80582abe3013bcb305d731add59d9c3';
		$data['s_no']='2016063012103806191318';
		$data['fraction_speed']='5';
		$data['fraction_service']='4';
		$data['fraction_desc']='3';
		$data['content']='不错哦！';
        
		C('DEBUG_API',true);
		$res=$this->doApi($apiurl,$data);
		dump($res);			
		

				

		$apiurl='/Erp/company_type';
       
		C('DEBUG_API',true);
		$res=$this->doApi($apiurl,$data);
		dump($res);	

		$apiurl='/OpenShop/check_domain';
		$data['domain']='user';
		$data['openid']='e80582abe3013bcb305d731add59d9c3';
       
		C('DEBUG_API',true);
		$res=$this->doApi($apiurl,$data);
		dump($res);		

	

		$apiurl='/OpenShop/check_shop_name';
		$data['shop_name']='enhong专店';
		$data['openid']='e80582abe3013bcb305d731add59d9c3';
       
		C('DEBUG_API',true);
		$res=$this->doApi($apiurl,$data);
		dump($res);		
		

		$apiurl='/OpenShop/shop_type_view';
		$data['id']=1;
       
		C('DEBUG_API',true);
		$res=$this->doApi($apiurl,$data);
		dump($res);		
		

		$apiurl='/OpenShop/notice';
       
		C('DEBUG_API',true);
		$res=$this->doApi($apiurl,$data);
		dump($res);	
		

		$apiurl='/Erp/check_mobile';
		$data['mobile']	='13710356179';

		C('DEBUG_API',true);
		$res=$this->doApi($apiurl,$data);
		dump($res);	


		$apiurl='/OpenShop/contact_info';
		$data['openid']='e80582abe3013bcb305d731add59d9c3';
		$data['linkname']='张三丰';
		$data['mobile']='13825698588';
		$data['tel']='13710236585';
		$data['email']='enhong@126.com';
		$data['rf_linkname']='张大千';
		$data['rf_mobile']='13825698588';
		$data['rf_tel']='020-3652366';
		$data['rf_province']='1';
		$data['rf_city']='1';
		$data['rf_district']='1';
		$data['rf_street']='天河路108号';

		C('DEBUG_API',true);
		$res=$this->doApi($apiurl,$data);
		dump($res);					
		



		$apiurl='/OpenShop/brand_add';
		$data['openid']='e80582abe3013bcb305d731add59d9c3';
		$data['b_name']='guttuso';
		$data['b_logo']='http://img13.360buyimg.com/vclist/jfs/t2821/270/2836752025/35037/7d99603e/5774e620Nace55455.jpg';
		$data['b_master']='张三丰';
		$data['b_type']='18';
		$data['b_scope']='皮具箱包';
		$data['b_code']='440125685';
		$data['b_images']='http://img20.360buyimg.com/da/jfs/t2959/136/1081579881/81220/ee192a71/57753716Nf2237408.jpg';

		C('DEBUG_API',true);
		$res=$this->doApi($apiurl,$data);
		dump($res);	
		

		$apiurl='/OpenShop/brand_delete';
		$data['openid']='e80582abe3013bcb305d731add59d9c3';
		$data['id']=166;
		C('DEBUG_API',true);
		$res=$this->doApi($apiurl,$data);
		dump($res);	

		

		$apiurl='/OpenShop/brand';
		$data['openid']='e80582abe3013bcb305d731add59d9c3';
		C('DEBUG_API',true);
		$res=$this->doApi($apiurl,$data);
		dump($res);	
		

		$apiurl='/OpenShop/shop_info';
		$data['openid']='e80582abe3013bcb305d731add59d9c3';
		$data['shop_name']='guttuso';
		$data['type_id']=2;
		$data['about']='皮具箱包批发零售';

		C('DEBUG_API',true);
		$res=$this->doApi($apiurl,$data);
		dump($res);		
		

		$apiurl='/OpenShop/category_list';
		$data['sid']=100841621;
		C('DEBUG_API',true);
		$res=$this->doApi($apiurl,$data,'sid');
		dump($res);			
		

		$apiurl='/ShopSetting/shop_info_save';
		$data['openid']='e80582abe3013bcb305d731add59d9c3';
		$data=[
			'openid'		=>'e80582abe3013bcb305d731add59d9c3',
			'shop_logo'		=>'',
			'about'			=>'批发',
			'province'		=>1,
			'city'			=>1,
			'district'		=>1,
			'town'			=>1,
			'street'		=>'龙口西咱',
			'qq'			=>'12568885',
			'mobile'		=>'13752569858',
			'tel'			=>'020-38203200',
			'email'			=>'enhong@126.com'
		];

		C('DEBUG_API',true);
		$res=$this->doApi($apiurl,$data,'shop_logo,town,tel,email');
		dump($res);						
		
		$apiurl='/ShopSetting/set_domain';
		$data['openid']='e80582abe3013bcb305d731add59d9c3';
		$data['domain']='guttuso';
		C('DEBUG_API',true);
		$res=$this->doApi($apiurl,$data,'sid');
		dump($res);	
		

		$apiurl='/SellerGoods/category';
		$data['openid']='f2cffee3235e9eb5d1d021217832da4f';
		C('DEBUG_API',true);
		$res=$this->doApi($apiurl,$data,'pagesize,p');
		dump($res);		

		$apiurl='/SellerGoods/goods_online';
		$data['openid']='f2cffee3235e9eb5d1d021217832da4f';
		$data['q']='钱包';
		C('DEBUG_API',true);
		$res=$this->doApi($apiurl,$data,'pagesize,p,price_s,price_e,q');
		dump($res);	
			
		$apiurl='/OpenShop/step';
		$data['openid']='e80582abe3013bcb305d731add59d9c3';
		C('DEBUG_API',true);
		$res=$this->doApi($apiurl,$data);
		dump($res);	

			
		$apiurl='/SellerExpress/express_company';
		C('DEBUG_API',true);
		$res=$this->doApi($apiurl,$data);
		dump($res);		
				

		$apiurl='/SellerExpress/express_area_edit';
		$data['openid']='f2cffee3235e9eb5d1d021217832da4f';
		$data['express_id']=23;
		$data['first_unit']=1;
		$data['first_price']=8;
		$data['next_price']=15;
		$data['next_unit']=1;
		$data['city_ids']='380,381,392,403,408,422,432';
		$data['id']	=25;


		C('DEBUG_API',true);
		$res=$this->doApi($apiurl,$data);
		dump($res);	
			

		$apiurl='/SellerExpress/express_area';
		$data['openid']='f2cffee3235e9eb5d1d021217832da4f';
		$data['express_id']=23;
		C('DEBUG_API',true);
		$res=$this->doApi($apiurl,$data);
		dump($res);	
		

		$apiurl='/SellerExpress/express_delete';
		$data['openid']='f2cffee3235e9eb5d1d021217832da4f';
		$data['id']=23;
		C('DEBUG_API',true);
		$res=$this->doApi($apiurl,$data);
		dump($res);	
		

		C('DEBUG_API',true);
    	$apiurl='/SellerGoods/goods_name_edit';
    	$data['openid']='f2cffee3235e9eb5d1d021217832da4f';
        $data['goods_id']=43;
        $data['goods_name']='紫唯依梦2016春夏新款四季百搭纯棉牛仔衬衫女中长款牛仔衬衣长袖大码女装上衣 深蓝色 XXL建议125-120斤左右';
    	$res=$this->doApi($apiurl,$data);
    	dump($res);
    	$res=json_decode($res);
        if($res->code!=1){
            //$this->err($res->msg);
        }


		C('DEBUG_API',true);
    	$apiurl='/SellerGoods/goods_sku';
    	$data['openid']='f2cffee3235e9eb5d1d021217832da4f';
        $data['goods_id']=43;
    	$res=$this->doApi($apiurl,$data);
    	dump($res);
    	$res=json_decode($res);   
    	

		C('DEBUG_API',true);
    	$apiurl='/SellerGoods/goods_sku_edit';
    	$data['openid']='f2cffee3235e9eb5d1d021217832da4f';
    	$data['goods_id']=43;
    	$data['id']	=[63,64];
    	$data['price']=[100,102];
    	$data['num']=[100,200];

    	$data['price_market']=[150,200];

    	//CURL不支持二维数组提交，只能将数组用http_build_query转成字符串提交
    	$field=['id','price','num','price_market','price_purchase','weight','code','barcode'];
    	foreach($field as $val){
    		if(isset($data[$val])) $data[$val]=http_build_query($data[$val]);
    	}

    	$res=$this->doApi($apiurl,$data,'price_market,price_purchase');
    	dump($res);
    	$res=json_decode($res); 

    	  	  

		C('DEBUG_API',true);
    	$apiurl='/SellerRate/rate_list';
    	$data['openid']='f2cffee3235e9eb5d1d021217832da4f';

    	$res=$this->doApi($apiurl,$data);
    	dump($res);
    	$res=json_decode($res);     
    	
		C('DEBUG_API',true);
    	$apiurl='/Rate/rate_goods_list';
    	$data['openid']='e80582abe3013bcb305d731add59d9c3';

    	$res=$this->doApi($apiurl,$data);
    	dump($res);
    	$res=json_decode($res);     
    	

		C('DEBUG_API',true);
    	$apiurl='/Rate/rate_goods_view';
    	$data['openid']='e80582abe3013bcb305d731add59d9c3';
    	$data['id']=20;

    	$res=$this->doApi($apiurl,$data);
    	dump($res);
    	$res=json_decode($res);     
    	

		C('DEBUG_API',true);
    	$apiurl='/Rate/rate_goods_edit';
    	$data['openid']='e80582abe3013bcb305d731add59d9c3';
    	$data['id']=20;
    	$data['content']='不错，很好！';

    	$res=$this->doApi($apiurl,$data);
    	dump($res);
    	$res=json_decode($res);   
    	
		
		$apiurl='/Erp/sms_code';
		$data['mobile']	='13710356176';

		C('DEBUG_API',true);
		$res=$this->doApi($apiurl,$data);
		dump($res);    
		

		
		$apiurl='/Erp/forgot_password_step1';
		$data['mobile']	='13710356176';
		$data['username']='tel13710356176';
		$data['smscode']=802550;

		C('DEBUG_API',true);
		$res=$this->doApi($apiurl,$data);
		dump($res); 

		

		$apiurl='/Erp/forgot_password_step2';
		$data['erp_uid']	='1b843e87049d362ce1a7c9ce8901362e';
		$data['password']=$this->password('12345678');
		$data['signcode']='6bbd9340270ccacdd36e17dccfe207d3';

		C('DEBUG_API',true);
		$res=$this->doApi($apiurl,$data);
		dump($res);
		

		$apiurl='/SellerExpress/express_view';
		$data['openid']='f2cffee3235e9eb5d1d021217832da4f';
		$data['id']=18;

		C('DEBUG_API',true);
		$res=$this->doApi($apiurl,$data);
		dump($res);

		
		$apiurl='/SellerOrders/orders_list';
		$data['openid']='f2cffee3235e9eb5d1d021217832da4f';

		C('DEBUG_API',true);
		$res=$this->doApi($apiurl,$data);
		dump($res);
		

		$apiurl='/SellerOrders/view';
		$data['openid']='f2cffee3235e9eb5d1d021217832da4f';
		$data['s_no']='2016070416543317423430';

		C('DEBUG_API',true);
		$res=$this->doApi($apiurl,$data);
		dump($res);


		$apiurl='/SellerGoods/category_delete';
		$data['openid']='f2cffee3235e9eb5d1d021217832da4f';
		$data['id']=100845555;
		C('DEBUG_API',true);
		$res=$this->doApi($apiurl,$data);
		dump($res);
		
		
		$apiurl='/SellerOrders/orders_goods';
		$data['openid']='f2cffee3235e9eb5d1d021217832da4f';
		$data['s_no']='2016062813344283063868';

		C('DEBUG_API',true);
		$res=$this->doApi($apiurl,$data);
		dump($res);	
		


		


		$apiurl='/SellerOrders/orders_price_edit';
		$data['openid']='f2cffee3235e9eb5d1d021217832da4f';
		$data['s_no']='2016062916235508438095';
		$data['express_price']=20;
		$data['orders_goods_id']=[324,325,326];
		$data['total_price_edit']=[80,125,50];

		$data['orders_goods_id']=http_build_query($data['orders_goods_id']);
		$data['total_price_edit']=http_build_query($data['total_price_edit']);

		C('DEBUG_API',true);
		$res=$this->doApi($apiurl,$data);
		dump($res);	
		


		$apiurl='/SellerOrders/orders_goods';
		$data['openid']='f2cffee3235e9eb5d1d021217832da4f';
		$data['s_no']='2016062916235508438095';

		C('DEBUG_API',true);
		$res=$this->doApi($apiurl,$data);
		dump($res);
		

		$apiurl='/SellerGoods/cancel_best';
		$data['openid']='f2cffee3235e9eb5d1d021217832da4f';
		$data['goods_id']=48;

		C('DEBUG_API',true);
		$res=$this->doApi($apiurl,$data);
		dump($res);		
		

		$apiurl='/Ad/sucai_list';
		$data['openid']='f2cffee3235e9eb5d1d021217832da4f';
		C('DEBUG_API',true);
		$res=$this->doApi($apiurl,$data);
		dump($res);	

		

		$apiurl='/Ad/sucai_edit';
		$data['openid']='f2cffee3235e9eb5d1d021217832da4f';
		$data['size']='692x172';
		$data['sucai_name']='连衣裙夏天';
		$data['category_id']=100841782;
		$data['images']='http://7xvbop.com1.z0.glb.clouddn.com/Fh66y6toLl_AfXr8CluCVU1OjJFC';
		$data['id']=100597530;

		C('DEBUG_API',true);
		$res=$this->doApi($apiurl,$data);
		dump($res);	
		

		$apiurl='/SellerAd/resource_list';
		C('DEBUG_API',true);
		$res=$this->doApi($apiurl,$data);
		dump($res);	




		$apiurl='/SellerAd/create_orders';
		$data['openid']='f2cffee3235e9eb5d1d021217832da4f';
		$data['position_id']=18;
		$data['sort']=0;
		$data['days']='2016-07-08,2016-07-09,2016-07-10,2016-07-07';
		$data['sucai_id']=100597530;
		$data['name']='test 123';
		$data['type']=0;
		$data['info_id']=48;


		C('DEBUG_API',true);
		$res=$this->doApi($apiurl,$data);
		dump($res);			
		

	
		$apiurl='/Erp/ad_pay';
		$data['openid']='f2cffee3235e9eb5d1d021217832da4f';
		$data['a_no']='2016071111003340193116';
		$data['password_pay']=$this->password('654321');

		C('DEBUG_API',true);
		$res=$this->doApi($apiurl,$data);
		dump($res);		
		

		$apiurl='/Goods/brand';
		$data['openid']='e80582abe3013bcb305d731add59d9c3';
		C('DEBUG_API',true);
		$res=$this->doApi($apiurl,$data);
		dump($res);	
		

		$apiurl='/SellerBrand/brand_edit';
		
        $data=[
            'brand_id'      =>181,
            'name'          =>'古托索',
            'ename'         =>'guttuso',
            'logo'          =>'http://pic-cdn.35pic.com/58pic/12/18/27/81858PICnVa.jpg',
            'images'        =>'http://img5.imgtn.bdimg.com/it/u=2608692705,3436922922&fm=21&gp=0.jpg',
            'about'         =>'批发街GUTTUSO古托索品牌包包频道提供古托索包包品牌新闻、古托索包包新品动态、古托索钱包、古托索男包、古托索皮带、古托索女包批发、男包代理、包包订做等业务。',
            'category_id'   =>'100841621,100841624',
            'tag'           =>'男包,钱包,手世,皮包'
        ];
        $data['openid']='e80582abe3013bcb305d731add59d9c3';

		C('DEBUG_API',true);
		$res=$this->doApi($apiurl,$data);
		dump($res);	

						

		

		$apiurl='/SellerBrand/brand_list';
		$data['openid']='e80582abe3013bcb305d731add59d9c3';

		C('DEBUG_API',true);
		$res=$this->doApi($apiurl,$data);
		dump($res);	
		

		$apiurl='/SellerBrand/view';
		$data['openid']='e80582abe3013bcb305d731add59d9c3';
		$data['brand_id']=181;
		C('DEBUG_API',true);
		$res=$this->doApi($apiurl,$data);
		dump($res);		
		
		$apiurl='/SellerBrand/view';
		$data['openid']='ef1b3a030f1d539acde9c57459b193b2';
		$data['brand_id']=182;
		C('DEBUG_API',true);
		$res=$this->doApi($apiurl,$data);
		dump($res);		

		

		$apiurl='/Goods/hot_keywords';

		C('DEBUG_API',true);
		$res=$this->doApi($apiurl,$data);
		dump($res);	
		



		$apiurl='/SellerGoods/first_category';
		$data['openid']='f2cffee3235e9eb5d1d021217832da4f';
		C('DEBUG_API',true);
		$res=$this->doApi($apiurl,$data);
		dump($res);	
		

		$apiurl='/SellerGoods/goods_online';
		$data['openid']='f2cffee3235e9eb5d1d021217832da4f';
		C('DEBUG_API',true);
		$res=$this->doApi($apiurl,$data);
		dump($res);		
		
			
		//dump(upsid(['table' => 'goods_category','id' => 100845229]));

		$apiurl='/Seller/total';
		$data['openid']='f2cffee3235e9eb5d1d021217832da4f';
		C('DEBUG_API',true);
		$res=$this->doApi($apiurl,$data);
		dump($res);	
		

		$apiurl='/SellerOrders/orders_list';
		$data['openid']='f2cffee3235e9eb5d1d021217832da4f';
		//$data['s_no']='2016070911074329875402';
		$data['goods_name']='紫唯依梦';

		C('DEBUG_API',true);
		$res=$this->doApi($apiurl,$data,'s_no,goods_name');
		dump($res);	
		

		//$list=D('Common/GoodsCategoryUpRelation')->relation(true)->where(['id' =>100842045])->select();
		//dump($list);

array (
  'access_key' => 'e807f1fcf82d132f9bb018ca6738a19f',
  'action' => '/Addr/index',
  'appid' => '4',
  'openid' => 'f2cffee3235e9eb5d1d021217832da4f',
  'p' => '1',
  'pagesize' => '3',
  'secret_key' => 'f4b89fd253b7d2082906ffb46f5e4793',
  'sign_code' => '3baa7d716b0fd789a2e91e89b07c50da',
  'sign' => 'd425eb31bbfcd758e5088816072a9d81',
)

		$apiurl='/Refund/goods';
		$data['openid']='529f41febec21f547960a3b10ca41691';
		$data['s_no']	='2016072214021935171862';
		$data['imgsize']	=100;

		C('DEBUG_API',true);
		$res=$this->doApi($apiurl,$data,'imgsize');
		dump($res);


		$apiurl='/Refund/add';
		$data['openid']='529f41febec21f547960a3b10ca41691';
		$data['s_no']	='2016072214021935171862';
		$data['orders_goods_id']	=397;
		$data['price']				=0.2;
		$data['num']				=1;
		$data['reason']				='不想要了';

		C('DEBUG_API',true);
		$res=$this->doApi($apiurl,$data);
		dump($res);


		

		$apiurl='/Refund/cancel';
		$data['openid']='529f41febec21f547960a3b10ca41691';
		$data['s_no']	='2016072214021935171862';
		$data['r_no']	='2016072217395029853776';

		C('DEBUG_API',true);
		$res=$this->doApi($apiurl,$data);
		dump($res);

		$apiurl='/Refund/add';
		$data['openid']='529f41febec21f547960a3b10ca41691';
		$data['s_no']	='2016072214021935171862';
		$data['orders_goods_id']	=397;
		$data['price']				=0.2;
		$data['num']				=1;
		$data['reason']				='不想要了';

		C('DEBUG_API',true);
		$res=$this->doApi($apiurl,$data);
		dump($res);		
		

		

		$apiurl='/Refund/express_add';
		$data['openid']='529f41febec21f547960a3b10ca41691';
		$data['s_no']	='2016072214021935171862';
		$data['price']				=1;
		$data['reason']				='不想要了';

		C('DEBUG_API',true);
		$res=$this->doApi($apiurl,$data);
		dump($res);	
		

		$apiurl='/SellerRefund/accept';
		$data['openid']='302b292cfa30e13c76a50ac4e829a7ef';
		$data['s_no']	='2016072214021935171862';
		$data['r_no']	='2016072219331794857828';
		$data['password_pay']	=$this->password('123456');

		C('DEBUG_API',true);
		$res=$this->doApi($apiurl,$data);
		dump($res);		
		


		$apiurl='/SellerRefund/accept';
		$data['openid']='302b292cfa30e13c76a50ac4e829a7ef';
		$data['s_no']	='2016072221233291257412';
		$data['r_no']	='2016072221303453450899';
		$data['password_pay']	=$this->password('123456');

		C('DEBUG_API',true);
		$res=$this->doApi($apiurl,$data);
		dump($res);			
		
		$apiurl='/SellerRefund/item_list';
		$data['openid']='302b292cfa30e13c76a50ac4e829a7ef';
		$data['s_no']	='2016072221233291257412';

		C('DEBUG_API',true);
		$res=$this->doApi($apiurl,$data);
		dump($res);	

		

		$apiurl='/Refund2/goods';
		$data['openid']='529f41febec21f547960a3b10ca41691';
		$data['s_no']	='2016072311172817189222';
		$data['orders_goods_id']=422;
		$data['imgsize']	=100;

		C('DEBUG_API',true);
		$res=$this->doApi($apiurl,$data,'imgsize,orders_goods_id');
		dump($res);		
		

		$apiurl='/Refund2/add';
		$data['openid']='529f41febec21f547960a3b10ca41691';
		$data['s_no']	='2016072311172817189222';
		$data['orders_goods_id']	=422;
		$data['price']				=0.2;
		$data['num']				=1;
		$data['reason']				='不想要了';
		$data['type']				=2;

		C('DEBUG_API',true);
		$res=$this->doApi($apiurl,$data);
		dump($res);		
		

		$apiurl='/Refund2/cancel';
		$data=[];
		$data['openid']='529f41febec21f547960a3b10ca41691';
		$data['s_no']	='2016072311172817189222';
		$data['r_no']	='2016072314250583680941';

		C('DEBUG_API',true);
		$res=$this->doApi($apiurl,$data);
		dump($res);
		

		$apiurl='/Refund2/express_add';
		$data['openid']='529f41febec21f547960a3b10ca41691';
		$data['s_no']	='2016072311172817189222';
		$data['price']				=1;
		$data['reason']				='不想要了';

		C('DEBUG_API',true);
		$res=$this->doApi($apiurl,$data);
		dump($res);			

		$apiurl='/Refund2/cancel';
		$data=[];
		$data['openid']='529f41febec21f547960a3b10ca41691';
		$data['s_no']	='2016072311172817189222';
		$data['r_no']	='2016072314340676458841';

		C('DEBUG_API',true);
		$res=$this->doApi($apiurl,$data);
		dump($res);
		


		$apiurl='/SellerRefund2/accept';
		$data=[];
		$data['openid']='302b292cfa30e13c76a50ac4e829a7ef';
		$data['s_no']	='2016072311172817189222';
		$data['r_no']	='2016072314353955784234';
		$data['password_pay']	=$this->password('123456');

		C('DEBUG_API',true);
		$res=$this->doApi($apiurl,$data);
		dump($res);		

		
		$apiurl='/Refund2/add';
		$data['openid']='529f41febec21f547960a3b10ca41691';
		$data['s_no']	='2016072316424720979785';
		$data['orders_goods_id']	=431;
		$data['price']				=0.4;
		$data['num']				=2;
		$data['reason']				='不想要了';
		$data['type']				=2;

		C('DEBUG_API',true);
		$res=$this->doApi($apiurl,$data);
		dump($res);		

		
		$apiurl='/SellerRefund2/reject';
		$data=[];
		$data['openid']='302b292cfa30e13c76a50ac4e829a7ef';
		$data['s_no']	='2016072316424720979785';
		$data['r_no']	='2016072316482578198157';
		$data['reason']	='恶意';

		C('DEBUG_API',true);
		$res=$this->doApi($apiurl,$data);
		dump($res);	
		

		
		$apiurl='/Refund2/edit';
		$data['openid']='529f41febec21f547960a3b10ca41691';
		$data['s_no']	='2016072316424720979785';
		$data['r_no']	='2016072316482578198157';
		$data['price']				=0.4;
		$data['num']				=2;
		$data['reason']				='不想要了easdfasf';
		$data['type']				=2;

		C('DEBUG_API',true);
		$res=$this->doApi($apiurl,$data);
		dump($res);

		
		$apiurl='/Refund2/add';
		$data['openid']='529f41febec21f547960a3b10ca41691';
		$data['s_no']	='2016072316424720979785';
		$data['orders_goods_id']	=433;
		$data['price']				=0.2;
		$data['num']				=1;
		$data['reason']				='不想要了';
		$data['type']				=1;

		C('DEBUG_API',true);
		$res=$this->doApi($apiurl,$data);
		dump($res);		
		

		$apiurl='/SellerRefund2/accept';
		$data=[];
		$data['openid']='302b292cfa30e13c76a50ac4e829a7ef';
		$data['s_no']	='2016072316424720979785';
		$data['r_no']	='2016072317571668305666';
		$data['password_pay']	=$this->password('123456');

		C('DEBUG_API',true);
		$res=$this->doApi($apiurl,$data);
		dump($res);		
		

		$apiurl='/Refund2/send_express';
		$data['openid']='529f41febec21f547960a3b10ca41691';
		$data['s_no']	='2016072316424720979785';
		$data['r_no']	='2016072317571668305666';
		$data['express_company_id']			=326;
		$data['express_code']				='12365859859';


		C('DEBUG_API',true);
		$res=$this->doApi($apiurl,$data);
		dump($res);			
		

		$apiurl='/SellerRefund2/accept2';
		$data=[];
		$data['openid']='302b292cfa30e13c76a50ac4e829a7ef';
		$data['s_no']	='2016072316424720979785';
		$data['r_no']	='2016072317571668305666';
		$data['password_pay']	=$this->password('123456');

		C('DEBUG_API',true);
		$res=$this->doApi($apiurl,$data);
		dump($res);	
		

		$apiurl='/Refund2/add';
		$data['openid']='529f41febec21f547960a3b10ca41691';
		$data['s_no']	='2016072321213761725679';
		$data['orders_goods_id']	=458;
		$data['price']				=0.2;
		$data['num']				=1;
		$data['reason']				='不想要了';
		$data['type']				=1;

		C('DEBUG_API',true);
		$res=$this->doApi($apiurl,$data);
		dump($res);		


		$apiurl='/SellerRefund2/accept';
		$data=[];
		$data['openid']='302b292cfa30e13c76a50ac4e829a7ef';
		$data['s_no']	='2016072321213761725679';
		$data['r_no']	='2016072321234350138704';
		$data['password_pay']	=$this->password('123456');

		C('DEBUG_API',true);
		$res=$this->doApi($apiurl,$data);
		dump($res);		
		
		$apiurl='/Refund2/send_express';
		$data=[];
		$data['openid']='529f41febec21f547960a3b10ca41691';
		$data['s_no']	='2016072321213761725679';
		$data['r_no']	='2016072321234350138704';
		$data['express_company_id']			=326;
		$data['express_code']				='12365859859';


		C('DEBUG_API',true);
		$res=$this->doApi($apiurl,$data);
		dump($res);			

		$apiurl='/SellerRefund2/accept2';
		$data=[];
		$data['openid']='302b292cfa30e13c76a50ac4e829a7ef';
		$data['s_no']	='2016072321213761725679';
		$data['r_no']	='2016072321234350138704';
		$data['password_pay']	=$this->password('123456');

		C('DEBUG_API',true);
		$res=$this->doApi($apiurl,$data);
		dump($res);		
		

		$apiurl='/Refund/goods';
		$data['openid']='529f41febec21f547960a3b10ca41691';
		$data['s_no']	='2016072213381860379342';
		$data['imgsize']	=100;

		C('DEBUG_API',true);
		$res=$this->doApi($apiurl,$data,'imgsize');
		dump($res);		
				

		$do=D('Common/GoodsAttrListUpRelation');
		dump($do);
        $goods=$do->relation(true)->where(['id' => 466])->field('id,goods_id,price,num')->select();
        dump($goods);	
        

		$apiurl='/Recharge/add';
		$data['openid']='529f41febec21f547960a3b10ca41691';
		$data['money']	=0.1;
		$data['paytype']=1;

		C('DEBUG_API',true);
		$res=$this->doApi($apiurl,$data);
		dump($res);	 
		

   
		$apiurl='/Recharge/update_status';
		$data['openid']='529f41febec21f547960a3b10ca41691';
		$data['r_no']	='2016072622443123389681';

		C('DEBUG_API',true);
		$res=$this->doApi($apiurl,$data);
		dump($res);	
			 

		$apiurl='/Refund/express_add';
		$data['openid']='529f41febec21f547960a3b10ca41691';
		$data['s_no']	='2016072719485348978348';
		$data['price']				=0.99;
		$data['reason']				='不想要了';

		C('DEBUG_API',true);
		$res=$this->doApi($apiurl,$data);
		dump($res);	

		


		$apiurl='/Cart/express_price';
        $data['openid']='529f41febec21f547960a3b10ca41691';
        $data['address_id']='404';
        $data['seller_id']='686935';
        $data['express_type']=2;

		C('DEBUG_API',true);
		$res=$this->doApi($apiurl,$data);
		dump($res);	  
		
		$apiurl='/Orders/view';
		$data=array (
		  'o_no' => '2016080115323560033938',
		  'openid' => '529f41febec21f547960a3b10ca41691',
		);
		C('DEBUG_API',true);
		$res=$this->doApi($apiurl,$data);
		dump($res);	   

		

		$apiurl='/Make/api/method/templates_active';
		$data=array (
		  'openid' => '4262044fa61bfba49717baabfe19b30b',
		);
		C('DEBUG_API',true);
		$res=$this->doApi($apiurl,$data);	

		$apiurl='/Make/api/method/templates';
		$data=array (
		  'openid' => '4262044fa61bfba49717baabfe19b30b',
		  'templates_id'	=>100445748,
		);
		C('DEBUG_API',true);
		$res=$this->doApi($apiurl,$data);
		 		

		$apiurl='/Make/api/method/templates';
		$data=array (
		  'openid' => '4262044fa61bfba49717baabfe19b30b',
		  'templates_id'	=>100445748,
		  'is'
		);
		C('DEBUG_API',true);
		$res=$this->doApi($apiurl,$data);

		$list=get_category(['table' => 'shop_modules_category','level' => 2,'field'=>'id,sid,category_name,ac,images','sql' => 'status=1']);
		dump($list);

		 

		$apiurl='/Erp/check_orders_status';
		$data['s_no']	='2016080909144755970455';

		C('DEBUG_API',true);
		$res=$this->doApi($apiurl,$data);
		dump($res);	


		$apiurl='/Sitemap/sitemap';

		C('DEBUG_API',true);
		$res=$this->doApi($apiurl);
		dump($res);


        C('DEBUG_API',true);
        $res=$this->doApi('/ExpressPrint/company');
        dump($res);


        C('DEBUG_API',true);
        $res=$this->doApi('/ExpressPrint/get_cfg',['openid' => 'cfe1fb7818e62ffd52adea41ec77dd9b']);
        dump($res);


        C('DEBUG_API',true);
        $res=$this->doApi('/ExpressPrint/cfg_save',['openid' => 'cfe1fb7818e62ffd52adea41ec77dd9b','is_come' =>0,'is_send' =>1,'default_company_id' =>321]);
        dump($res);


        C('DEBUG_API',true);
        $res=$this->doApi('/ExpressPrint/orders',['openid' => 'cfe1fb7818e62ffd52adea41ec77dd9b'],'pagesize,p');
        dump($res);

        C('DEBUG_API',true);
        $res=$this->doApi('/ExpressPrint/express_bill',['openid' => 'cfe1fb7818e62ffd52adea41ec77dd9b','s_no'=>'2016101517004054454977','express_company_id'=>320,'is_send'=>1,'is_come'=>1]);
        dump($res);



		$orders['s_no']=['2016101517071291270213','2016101517004054454977'];
        $orders['express_company_id']=[321,320];
        $orders['express_code']=['209883458354','x984509345345'];
        C('DEBUG_API',true);
        $res=$this->doApi('/ExpressPrint/batch_send_express',['openid' => 'cfe1fb7818e62ffd52adea41ec77dd9b','orders'=>serialize($orders)]);
        dump($res);



        C('DEBUG_API',true);
        $res=$this->doApi('/Luckdraw/about',['s_no'=>'2016101517004054454977'],'s_no');
        dump($res);


        C('DEBUG_API',true);
        $res=$this->doApi('/App/ad');
        dump($res);



        C('DEBUG_API',true);
        $res=$this->doApi('/App/auth',['erp_uid' => 'a2931b4ec99554377b8d7315a40b70e2']);
        dump($res);


        //C('DEBUG_API',true);
        $data = [
            'appid'         => 6,
            'access_key'    => '0546734d7b599e802e9e2f3d701de851',
            'secret_key'    => 'b850ee6e7546377dad23cb87885c2711',
            'sign_code'     => '5e64fe04bfd8363b6c74ea86f5c867f1',
        ];
        $this->api_cfg=$data;
        dump($this->api_cfg);
        $res=$this->doApi('/App/ad');
        dump($res);


        C('DEBUG_API',true);
        $data = [
            'appid'         => 6,
            'access_key'    => '0546734d7b599e802e9e2f3d701de851',
            'secret_key'    => 'b850ee6e7546377dad23cb87885c2711',
            'sign_code'     => '5e64fe04bfd8363b6c74ea86f5c867f1',
            'erp_uid'       => '6dc65f2773ff1475f8c8f6a634be5af9',
            'redirect_url'  => 'https://www.trj.cc',
        ];
        $this->api_cfg=$data;

        $res=$this->doApi('/App/token',$data,'redirect_url');
        dump($res);


        C('DEBUG_API',true);
        $res=$this->doApi('/CustomMake/publish',['domain'=>'fashion','page'=>'/Index/index']);
        //$res = $this->doApi('/SellerGoods/goods_online',['openid' => 'ebcb96aa78e54329e1d2de4aeb460829']);
        dump($res);
		*/

        C('DEBUG_API',true);
        $data=[
            'openid' =>'f6e4b1ddb643ed6907b41adf71315e03',
            'address_id'=>3549,
            'seller_id'=>705333,
            'express_type'=>2,
            'express_tpl_id'=>338

        ];
        $res=$this->doApi('http://rest.dtshop.com/CartVer2/express_price',$data);
        dump($res);
    }
	
	public function test(){

    	$apiurl='/Erp/check_login';
		$data['username']	='ranqin001';
		$data['password']	=$this->password('88888888');

		C('DEBUG_API',true);
		$res=$this->doApi($apiurl,$data);
		dump($res);			
	}
	
    /**
    * 根据类目取属性
    * @param int    $_POST['cid']   类目ID
    */
    public function get_goods_attr($cid){
        $do=M('goods_attr');
        $list=$do->cache(true,C('CACHE_LEVEL.OneDay'))->where(array('status'=>1,'category_id'=>$cid,'sid'=>0))->field('id,attr_name')->order('sort asc')->select();


        if(empty($list)){
            $rs=M('goods_category')->cache(true,C('CACHE_LEVEL.OneDay'))->where(['id' => $cid])->field('id,sid')->find();
            //dump($rs);
            if($rs['sid']>0) $list=$this->get_goods_attr($rs['sid']);
            else return false;
        }

        return $list;
    }

    /**
    * 根据类目取参数
    * @param int    $_POST['cid']   类目ID
    */
    public function get_goods_param($cid){
        $do=D('Admin/GoodsParamOptionRelation');
        $list=$do->relation(true)->cache(true,C('CACHE_LEVEL.OneDay'))->where(array('status'=>1,'category_id'=>$cid))->field('id,group_name')->select();     
        if(empty($list)){
            $rs=M('goods_category')->cache(true,C('CACHE_LEVEL.OneDay'))->where(['id' => $cid])->field('id,sid')->find();
            //dump($rs);
            if($rs['sid']>0) $list=$this->get_goods_param($rs['sid']);
            else return false;
        }

        return $list;       
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
    dump($shopId);
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
        ];
        $model  =   M('shop_make_templates');
        $model->startTrans();
        if($model->add($data)) {
            $data['cfg']        =   unserialize($data['cfg']);
            $data['cfg_box']    =   unserialize($data['cfg_box']);
            $model->commit();
            return $data;
        }
        $model->rollback();
        return false;
    }

    public function excel(){
        vendor('PHPExcel.Classes.PHPExcel.IOFactory');

        $reader = \PHPExcel_IOFactory::createReader('Excel5');
        $PHPExcel = $reader->load("./Runtime/1.xls"); // 载入excel文件
        $sheet = $PHPExcel->getSheet(0); // 读取第一個工作表
        $highestRow = $sheet->getHighestRow(); // 取得总行数
        $highestColumm = $sheet->getHighestColumn(); // 取得总列数

        /** 循环读取每个单元格的数据 */
        for ($row = 1; $row <= $highestRow; $row++){//行数是以第1行开始
            for ($column = 'A'; $column <= $highestColumm; $column++) {//列数是以A列开始
                $dataset[] = $sheet->getCell($column.$row)->getValue();
                //echo $column.$row.":".$sheet->getCell($column.$row)->getValue()."<br />";
                $data[$row][$column] = $sheet->getCell($column.$row)->getValue();
            }
        }


    }


    public function qt(){
        $no = '2017011210544440621726
2017011210213863946074
2017011209235851502484
2017011209195886754990
2017011209164707193150
2017011209055733912060
2017011209033089977863
2017011208573748224909
2017011208491324457230
2017011207084671193256
2017011207054070461994
2017011120043544954092
2017011119222871440431
2017011118340389498319
2017011118323851372198
2017011118143870059008
2017011116190759615176
2017011116332827165529
2017011116313518249484
2017011116303868831401
2017011116200273736461
2017011116153891846029
2017011116124481355363
2017011116080999851003
2017011113571242885000
2017011112292730485049
2017011111530110399988
2017011111052386472828
2017011109552442456259
2017011109443095631162
2017011109383444245587
2017011109400222975610
2017011109313179744695
2017011109151371296803
2017011109065846458968
2017011109070927054422
2017011108215478742694
2017011105592624872197
2017011020232041364253
2017011100424437898392
2017011100333175923303
2017011100273337165790
2017011023071734644546
2017011021020873537933
2017011016571875310272
2017011016414350373440
2017011015081710301763
2017011015050391954732
2017011015010312980841
2017011012421264125813
2017010915421983594747
2017011011555314965775
2017011010050705586644
2017011009544405060514
2017011009213523566197
2017011007114520939794
2017011002460572670242
2017010922515996316879
2017010921302802248434
2017010921173440938305
2017010920391138260064
2017010920140669734066
2017010918592672936236
2017010917073901518859
2017010916482011203349
2017010915010067512867
2017010914061695621766
2017010913244412289381
2017010910530141283146
2017010910352116798831
2017010910255606770430
2017010909323033283427
2017010821532347575020
2017010821473637096659
2017010821200796783476
2017010820185476457273
2017010819572496783642
2017010819114538810568
2017010819121853290739
2017010818073505589200
2017010816142826522285
2017010815373308770635
2017010815243489527637
2017010814232428845564
2017010811050654450719
2017010811043704235798
2017010801104931276236
2017010722355559591437
2017010711451198791675
2017010621055923305951
2017010620500797249104
2017010619562538619684
2017010616185463203003
2017010600580002389840
2017010514101464501221
2017010511082665111471
2017010417460259276754
2017010412300075901269
2017010209094071484658
2017010113282213366140
2016123115071720359448
2016123116490141351060
2016122616562099113505
2016122612164716887226
2016122521141455044262
2016122514491407661175
2016122316245378957363';

        $no = explode(chr(10),trim($no));
        $nos = array();
        foreach($no as $val){
            $val = trim($val);
            $nos[] = $val;
        }

        $list = M('orders_shop')->where(['s_no' => ['in',$nos]])->field('s_no,refund_price,refund_express,pay_price,score,refund_score')->select();

        foreach($list as $val){
            $item = array();
            $item = ['T'.(string)$val['s_no'],$val['pay_price'],($val['refund_price']+ $val['refund_express']),$val['score'],$val['refund_score']];
            echo implode(',',$item).PHP_EOL;
        }


    }
}

