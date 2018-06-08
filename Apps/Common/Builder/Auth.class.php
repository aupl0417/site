<?php
/**
 * Created by PhpStorm.
 * User: dttx
 * Date: 2017/3/27
 * Time: 15:15
 */

namespace Common\Builder;

/**
 * 权限认证类
 *
 * Class Auth
 * @package Common\Builder
 *
 * //模块，控制器，方法
 *
 */

class Auth
{
    protected $module;
    protected $key = 'concat';  //数组KEY concat|mapping_rest

    protected static $notAuthController = [    //无需认证的控制器及方法
        '/Api/api',     //所有模块的api
        '/Run/authRun', //所有模块的authRun
        '/Run/authrun', //所有模块的authRun
        '/Run/index',   //所有模块的run
        '/Index/index', //所有模块的首页
//        '/Make/api/method/modules_item_tpl', //店铺装修的模块
//        '/Make/api/method/publish',          //装修后发布
        '/Main/index',  //店铺装修main页面
        '/Main/top',    //店铺装修的top页面
        '/Run/upload',  //图片上传
        '/Tool/expressCompany', //获取快递公司
        '/Tool/express', //获取物流信息
        '/Goods/choose',    //商品选择
        '/Upload/upload_save',  //图片上传
    ];

    protected static $authModule = [    //需要验证的模块
        'Seller',           //卖家中心
        'Make',             //店铺装修
        'Sellergoods',      //商品管理
        'Ad',               //广告管理
        'Expressprint',     //物流工具
    ];


    /**
     * Auth constructor.
     * @param null $key concat|mapping_rest
     * @param null $module  自定义的module
     */
    function __construct($key = null, $module = null)
    {
        if ($module) {
            $this->module = $module;
        } else {
            $this->module = MODULE_NAME . __ACTION__;
        }
        if ($key) $this->key = $key;
    }

    /**
     * subject: 取得单例
     * api: getInstance
     * author: Mercury
     * day: 2017-03-27 17:09
     * [字段名,类型,是否必传,说明]
     * @return Auth
     */
    public static function getInstance($key = null, $module = null)
    {
        return new self($key, $module);
    }
    
    /**
     * subject: 检测
     * api: check
     * author: Mercury
     * day: 2017-03-27 17:07
     * [字段名,类型,是否必传,说明]
     * @return bool
     */
    public function check() {
        $access = $this->getAccess();
        //writeLog($this->module);
        //if ($this->key == 'mapping_rest') writeLog($access);
        if ($this->key == 'mapping_rest') { //如果为mapping_rest则删除authrun
            unset(self::$notAuthController[0],self::$notAuthController[1],self::$notAuthController[2],self::$notAuthController[3]);
            //array_pop(self::$notAuthController);
        }  //如果为rest映射时则验证apiurl
        if (in_array(__ACTION__, self::$notAuthController) || !in_array(MODULE_NAME, self::$authModule)) return true;
        if ($access == false) return false;
        if (array_key_exists($this->module, $access) == false) {
            return false;
        }
        return true;
    }

    /**
     * subject: 获取节点
     * api: getAccess
     * author: Mercury
     * day: 2017-03-27 17:07
     * [字段名,类型,是否必传,说明]
     * @return bool|mixed
     */
    public function getAccess()
    {
        $model = D('ShopAuthAccessView');
        $funIds= M('shop_auth_group')->where(['id' => session('user.shop_auth_group_id'), 'status' => 1])->getField('fun_ids');
        //$field = 'function_name,mapping_rest,controller_name,module_name,concat(\'drop view\',\' \', table_name,\';\') as actions';
        $field = 'function_name,mapping_rest,controller_name,module_name';
        $access= $model->where(['id' => ['in', $funIds]])->field($field)->select();
        if ($access) {
            foreach ($access as $k => &$v) {
                $v['concat'] = ucfirst($v['module_name']) . '/' . ucfirst($v['controller_name']) . '/' . $v['function_name'];
                if ($this->key != 'concat' && $v['mapping_rest'] == '') unset($access[$k]);
            }
            unset($v);
            $tmpAccess = array_reduce($access, function (&$tmp, $val) {
                $tmp[$val[$this->key]] = $val;
                return $tmp;
            });
            return $tmpAccess;
        }
        return false;
    }
}