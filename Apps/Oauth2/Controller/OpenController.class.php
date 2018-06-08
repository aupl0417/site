<?php
/**
 * Created by PhpStorm.
 * User: dttx
 * Date: 2017/6/5
 * Time: 10:55
 */

namespace Oauth2\Controller;


use Common\Builder\R;
use Think\Controller;
use Think\Exception;

class OpenController extends Controller
{

    protected $needParams = [
        'time',
        'appid',
        'sign',
        'state'
    ];

    protected $writeRedis, $readRedis;

    const __CACHE_PREFIX__ = 'cache_by_open_';

    /**
     * subject: 构造函数
     * api: _initialize
     * author: Mercury
     * day: 2017-06-05 17:11
     * [字段名,类型,是否必传,说明]
     */
    protected function _initialize()
    {
        $this->readRedis = redisRead();
        $this->writeRedis= redisWrite();
    }

    /**
     * subject: 获取code
     * api: code
     * author: Mercury
     * day: 2017-06-05 10:55
     * [字段名,类型,是否必传,说明]
     */
    public function code()
    {
        //appid,secret,sign,state,callback_url
        try {
            $data = I('get.');
            $ret  = $this->checkData($data);
            if ($ret['code'] == 0) throw new Exception($ret['msg']);
            $data['code'] = md5(uniqid($data['appid'] . $data['state'] . microtime(true) . rand(), true));
            $flag = S(self::createCacheName(['appid' => $data['appid'], 'key' => 'code']), $data['code'], ['expire' => 600]);
            if ($flag == false) throw new Exception('缓存出错');
            $linkStr  = '?';
            $linkStr .= http_build_query($data);
            redirect(urldecode($data['retUrl']).$linkStr);  //执行跳转
        } catch (Exception $e) {
            $this->display(T('Home@Empty:404'));
        }
    }

    /**
     * subject: 获取token
     * api: token
     * author: Mercury
     * day: 2017-06-05 10:56
     * [字段名,类型,是否必传,说明]
     */
    public function token()
    {
        //appid,secret,sign,state,code
        //单个app token or 单用户token
        try {
            $data = IS_POST ? I('post.') : I('get.');
            if (S(self::createCacheName(['appid' => $data['appid'], 'key' => 'code'])) == false) throw new Exception('code不存在');
            $ret  = $this->checkData($data);
            if ($ret['code'] == 0) throw new Exception($ret['msg']);
            $token = S(self::createCacheName(['appid' => $data['appid'], 'key' => 'token']));
            if ($token == false) {
                $token= md5(uniqid($data['appid'] . $data['state'] . microtime(true) . rand() . $data['code'], true));
                $flag = S(self::createCacheName(['appid' => $data['appid'], 'key' => 'token']), $token, ['expire' => 1200]);
                if ($flag == false) throw new Exception('缓存出错');
            }
            $this->ajaxReturn(['code' => 1, 'msg' => '操作成功', 'data' => ['token' => $token]]);
        } catch (Exception $e) {
            $this->ajaxReturn(['code' => 0, 'msg' => $e->getMessage()]);
        }
    }

    /**
     * subject: 获取openID
     * api: openid
     * author: Mercury
     * day: 2017-06-05 10:57
     * [字段名,类型,是否必传,说明]
     */
    public function openid()
    {
        //appid,secret,sign,state,token
        try {
            $data = IS_POST ? I('post.') : I('get.');
            if (S(self::createCacheName(['appid' => $data['appid'], 'key' => 'token'])) == false) throw new Exception('token错误');
            $ret  = $this->checkData($data);
            if ($ret['code'] == 0) throw new Exception($ret['msg']);
            //判断是否存在当前appid下面的当前用户
            $cacheName = self::createCacheName(['appid' => $data['appid'], 'state' => $data['state']]);
            $uid       = redisRead()->hGet($cacheName, 'id');
            $map  = [
                'appid' =>  $data['appid'],
                'uid'   =>  $uid,
            ];
            $model  = M('open_user');
            $openid = $model->where($map)->getField('openid');
            $res    = ['code' => 1, 'msg' => '操作成功'];
            if (!$openid) {
                $openid = str_replace(['+', '/', '='], ['-', '_', rand(0,9)], base64_encode(md5($data['appid'] . $uid) . microtime(true))); //生成64位字符串并且打乱
                $iData  = [
                    'appid' =>  $data['appid'],
                    'openid'=>  $openid,
                    'ip'    =>  get_client_ip(),
                    'uid'   =>  $uid, //curl取不到session
                    'dev_uid' => M('open_app')->where(['id' => $data['appid']])->cache(true)->getField('uid'),
                ];
                if ($model->add($iData) == false) throw new Exception('生成openID失败');
            }
            $res['data']['openid'] = $openid;
            $this->ajaxReturn($res);
        } catch (Exception $e) {
            $this->ajaxReturn(['code' => 0, 'msg' => $e->getMessage()]);
        }
    }

    /**
     * subject: 获取用户信息
     * api: user
     * author: Mercury
     * day: 2017-06-05 10:57
     * [字段名,类型,是否必传,说明]
     */
    public function user()
    {
        //appid,secret,sign,state,openid,time,token
        if (IS_POST) {
            try {
                $data  = I('post.');
                $field = 'nick,face,sex,level_id,id';
                $uid   = M('open_user')->where(['openid' => $data['openid'], 'appid' => $data['appid']])->getField('uid');
                if (!$uid) throw new Exception('openid不存在');
                $user  = M('user')->where(['id' => $uid])->field($field)->cache(true)->find();
                $this->ajaxReturn(['code' => 1, 'msg' => '操作成功', 'data' => $user]);
            } catch (Exception $e) {
                $this->ajaxReturn(['code' => 0, 'msg' => $e->getMessage()]);
            }
        }
    }

    /**
     * subject: 登陆/授权
     * api: login
     * author: Mercury
     * day: 2017-06-05 10:59
     * [字段名,类型,是否必传,说明]
     */
    public function index()
    {
        //如果有session的话可以直接选择当前用户
        try {
            $data = I('get.');
            $ret  = $this->checkData($data);
            if ($ret['code'] == 0) throw new Exception($ret['msg']);
            $data['app'] = $ret['data'];    //app数据
            $this->assign('data', $data);
            unset($data['app']);
            if (session('user.id')) {
                $cacheName = self::createCacheName(['appid' => $data['appid'], 'state' => $data['state']]);
                $this->writeRedis->hMSet($cacheName, array_keys(session('user')), array_values(session('user')));
                $this->writeRedis->expire($cacheName, 600);  //10分钟缓存
            }
            $this->assign('codeUrl', session('user.id') ? http_build_query($data) : null);
            $this->display();
        } catch (Exception $e) {
            $this->display(T('Home@Empty:404'));
        }
    }

    /**
     * subject: 用户登录
     * api: login
     * author: Mercury
     * day: 2017-06-05 15:14
     * [字段名,类型,是否必传,说明]
     */
    public function login()
    {
        if (IS_POST) {
            try {
                $data   = I('post.');
                if (strlen($data['username']) < 6) throw new Exception('用户名不正确');
                if (strlen($data['password']) < 6) throw new Exception('密码不正确');
                $config = [
                    'url'   =>  '/erp/check_login',
                    'data'  =>  $data,
                    'isAjax'=> false,
                ];
                $ret = R::getInstance($config)->run();
                if ($ret['code'] == 1) session('user', $ret['data']);
                $this->ajaxReturn($ret);
            } catch (Exception $e) {
                $this->ajaxReturn(['code' => 0, 'msg' => $e->getMessage()]);
            }
        }
    }

    /**
     * subject: 数据检测
     * api: checkData
     * author: Mercury
     * day: 2017-06-05 16:22
     * [字段名,类型,是否必传,说明]
     * @param $data
     * @return array
     */
    private function checkData($data) {
        try {
            foreach ($this->needParams as $v) {
                if (array_key_exists($v, $data) == false) throw new Exception($v . '参数不能为空');
            }
            $appid= $data['appid'];         //appid
            $sign = $data['sign'];          //签名
            $time = $data['time'];          //时间戳
            $app  = M('open_app')->where(['id' => $appid])->cache(true)->field('atime,etime,ip', true)->find();
            if (!$app) throw new Exception('应用不存在');
            $data['secret'] = $app['secret'];   //眼
            if (NOW_TIME > $time+600) throw new Exception('链接已过期');
            if (self::sign($data) != $sign) throw new Exception('签名错误');
            return ['code' => 1, 'data' => $app];
        } catch (Exception $e) {
            return ['code' => 0, 'msg' => $e->getMessage()];
        }
    }

    /**
     * subject: 生成缓存名称
     * api: getCacheName
     * author: Mercury
     * day: 2017-06-05 17:18
     * [字段名,类型,是否必传,说明]
     * @param $params
     * @return string
     */
    private static function createCacheName($params)
    {
        if (is_array($params)) $params = http_build_query($params);
        return self::__CACHE_PREFIX__ . md5($params);
    }

    /**
     * subject: 数据签名校验
     * api: sign
     * author: Mercury
     * day: 2017-06-06 13:40
     * [字段名,类型,是否必传,说明]
     * @param $data
     * @return string
     */
    private static function sign($data)
    {
        if (array_key_exists('sign', $data)) unset($data['sign']);
        ksort($data);
        return md5(http_build_query($data));
    }
}