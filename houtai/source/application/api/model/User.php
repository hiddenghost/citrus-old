<?php

namespace app\api\model;

use think\Cache;
use app\common\library\wechat\WxUser;
use app\common\exception\BaseException;
use app\common\model\User as UserModel;
use app\api\model\dealer\Referee as RefereeModel;
use app\api\model\dealer\Setting as DealerSettingModel;
use app\api\model\user\Email as EmailModel;
use app\api\service\Im as ImService;

/**
 * 用户模型类
 * Class User
 * @package app\api\model
 */
class User extends UserModel
{
    private $token;

    /**
     * 隐藏字段
     * @var array
     */
    protected $hidden = [
        'open_id',
        'is_delete',
        'wxapp_id',
        'create_time',
        'update_time'
    ];

    /**
     * 获取指定商品评价列表
     * @param $goods_id
     * @param int $scoreType
     * @return \think\Paginator
     * @throws \think\exception\DbException
     */
    public function getList($keyword = '', $user_id)
    {
        // 筛选条件
        $filter = [
            'user_id' => ['<>', $user_id],
            'is_delete' => 0,
        ];
        // 评分
        $keyword && $filter['nickName'] = ['like', '%' . $keyword . '%'];
        return $this->field(['user_id', 'nickName', 'avatarUrl', 'country', 'province', 'city'])
            ->where($filter)
            ->order(['user_id' => 'asc', 'create_time' => 'desc'])
            ->paginate(15, false, [
                'query' => request()->request()
            ]);
    }

    /**
     * 过滤一下已删除的用户
     * @param $list
     */
    public function getFilterUserList($list)
    {
        $filter = [
            'user_id' => ['IN', $list],
            'is_delete' => 0,
        ];
        return $this->where($filter)->select()->toArray();
    }

    /**
     * 得到我的城市一样的好友列表
     * @param $city
     * @param $userList
     */
    public function getMyCityFirends($city, $userList)
    {
        // 获取列表数据
        return $this->where('user_id', 'IN', $userList)
            ->where('is_delete', '=', 0)
            ->where('city', '=', $city)
            ->select()->toArray();
    }

    /**
     * 获取用户信息
     * @param $token
     * @return null|static
     * @throws \think\exception\DbException
     */
    public static function getUser($token)
    {
        $openId = Cache::get($token)['openid'];
        return self::detail(['open_id' => $openId], ['address', 'images', 'grade', 'idcardData', 'schoolData', 'imuser']);
    }

    /**
     * 用户登录
     * @param array $post
     * @return string
     * @throws BaseException
     * @throws \think\Exception
     * @throws \think\exception\DbException
     */
    public function login($post)
    {
        // 微信登录 获取session_key 和 userinfo
        $wxloginUser = $this->wxlogin($post['code']);
        $session = $wxloginUser['session'];
        // 自动注册用户
        $refereeId = 0;

        $userInfo = [
            'nickName' => $wxloginUser['userInfo']['nickname'],
            'gender' => $wxloginUser['userInfo']['sex'],
            'avatarUrl' => $wxloginUser['userInfo']['headimgurl'],
        ];
        $user_id = $this->register($session['openid'], $userInfo, $refereeId);
        // 生成token (session3rd)
        $this->token = $this->token($session['openid']);
        // 记录缓存, 7天
        Cache::set($this->token, $session, 86400 * 7);
        return $user_id;
    }

    /**
     * iphone用户登录
     * @param array $post
     * @return string
     * @throws BaseException
     * @throws \think\Exception
     * @throws \think\exception\DbException
     */
    public function iphonelogin($post)
    {
        try {
            if(isEmpty($post['userIdentifier'])) {
                throw new BaseException(['msg' => '用户标识不能为空']);
            }
            $open_id = md5($post['userIdentifier']);
            log_write('userIdentifier-' . $post['userIdentifier'] . ',open_id-' . $open_id);
            $user = self::detail(['open_id' => $open_id]);
            if(!$user) {
                // 保存/更新用户记录
                $password = md5('juzi.123123456');
                $data = [
                    'userIdentifier' => $post['userIdentifier'],
                    'email' => $post['email'],
                    'password' => $password,
                    'register_type' => 3,
                    'name' => $post['familyName'] . $post['givenName'],
                    'status' => 1,
                    'gender' => 1,
                ];

                    !$data['name'] && $data['email'] && $data['name'] = $data['email'];
                    !$data['name'] &&  $data['name'] = '苹果用户';

                if (!$this->allowField(true)->save(array_merge($data, [
                    'open_id' => $open_id,
                    'wxapp_id' => self::$wxapp_id
                ]))) {
                    throw new BaseException(['msg' => '用户注册失败']);
                }
                // 生成token (session3rd)
                $session = md5($post['userIdentifier']);
                $this->token = $this->token($session);
                Cache::set($this->token, ['openid' => $session], 86400 * 7);
                (new ImService)->createUser($this['user_id']);

                return $this['user_id'];
            }else{
                $data = [
                    'email' => $post['email'],
                    'register_type' => 3,
                    'name' => $post['familyName'] . $post['givenName'],
                ];
                !$data['name'] && $user['email'] && $data['name'] = $user['email'];
                !$data['name'] && $user['nickName'] && $data['name'] = $user['nickName'];
                !$data['name'] && $data['name'] = '苹果用户';
                $user->save($data);
                // 生成token (session3rd)
                $session = md5($post['userIdentifier']);
                $this->token = $this->token($session);
                // 记录缓存, 7天
                if(isset($post['is_remeber']) && $post['is_remeber']) {
                    Cache::set($this->token, ['openid' => $session], 86400 * 30);
                }else{
                    Cache::set($this->token, ['openid' => $session], 86400 * 7);
                }
                (new ImService)->createUser($user['user_id']);

                return $user['user_id'];
            }


        } catch (\Exception $e) {
            throw new BaseException(['msg' => $e->getMessage()]);
        }


    }

    /**
     * app用户登录 用户名/手机号码 密码
     * @param array $post
     * @return string
     * @throws BaseException
     * @throws \think\Exception
     * @throws \think\exception\DbException
     */
    public function applogin($post)
    {
        try {
            if(isEmpty($post['name'])) {
                throw new BaseException(['msg' => '邮箱/手机号码不能为空']);
            }
            $passwordVerify = isStrLen($post['password'], '密码',6, 20);
            if(!$passwordVerify['rs']) {
                throw new BaseException(['msg' => $passwordVerify['msg']]);
            }
            $password = md5('juzi.123' . $post['password']);
            if(isEmail($post['name'])) {
                $user = self::detail(['email' => $post['name'], 'password' => $password]);
            }else{
                $user = self::detail(['phone' => $post['name'], 'password' => $password]);
            }
            if(!$user) {
                throw new BaseException(['msg' => '账号密码错误']);
            }
            // 生成token (session3rd)
            $session = md5($post['name']);
            $this->token = $this->token($session);
            // 记录缓存, 7天
            if(isset($post['is_remeber']) && $post['is_remeber']) {
                Cache::set($this->token, ['openid' => $session], 86400 * 30);
            }else{
                Cache::set($this->token, ['openid' => $session], 86400 * 7);
            }

        } catch (\Exception $e) {
            throw new BaseException(['msg' => $e->getMessage()]);
        }

        (new ImService)->createUser($user['user_id']);

        return $user['user_id'];
    }

    /**
     * 获取token
     * @return mixed
     */
    public function getToken()
    {
        return $this->token;
    }

    /**
     * 微信登录
     * @param $code
     * @return array|mixed
     * @throws BaseException
     * @throws \think\exception\DbException
     */
    private function wxlogin($code)
    {
        // 获取当前小程序信息
        $wxConfig = Wxapp::getWxappCache();
        // 验证appid和appsecret是否填写
        if (empty($wxConfig['app_id']) || empty($wxConfig['app_secret'])) {
            throw new BaseException(['msg' => '请到 [后台-小程序设置] 填写appid 和 appsecret']);
        }
        // 微信登录 (获取session_key)
        $WxUser = new WxUser($wxConfig['app_id'], $wxConfig['app_secret']);
        if (!$session = $WxUser->sessionKey($code)) {
            throw new BaseException(['msg' => $WxUser->getError()]);
        }
        //获取用户信息
        $userInfo = $WxUser->getUserInfo($session['access_token'], $session['openid']);

        return ['session' => $session, 'userInfo' => $userInfo];
    }

    /**
     * 生成用户认证的token
     * @param $openid
     * @return string
     */
    private function token($openid)
    {
        $wxapp_id = self::$wxapp_id;
        // 生成一个不会重复的随机字符串
        $guid = \getGuidV4();
        // 当前时间戳 (精确到毫秒)
        $timeStamp = microtime(true);
        // 自定义一个盐
        $salt = 'token_salt';
        return md5("{$wxapp_id}_{$timeStamp}_{$openid}_{$guid}_{$salt}");
    }

    /**
     * 微信自动注册用户
     * @param $open_id
     * @param $data
     * @param int $refereeId
     * @return mixed
     * @throws \Exception
     * @throws \think\exception\DbException
     */
    private function register($open_id, $data, $refereeId = null)
    {
        // 查询用户是否已存在
        $user = self::detail(['open_id' => $open_id]);
        if($user) {
            return $user['user_id'];
        }
        $model = $user ?: $this;
        $this->startTrans();
        try {
            // 保存/更新用户记录
            if (!$model->allowField(true)->save(array_merge($data, [
                'open_id' => $open_id,
                'register_type' => 4,
                'wxapp_id' => self::$wxapp_id
            ]))) {
                throw new BaseException(['msg' => '用户注册失败']);
            }
            // 记录推荐人关系
            if (!$user && $refereeId > 0) {
                RefereeModel::createRelation($model['user_id'], $refereeId);
            }
            $this->commit();
        } catch (\Exception $e) {
            $this->rollback();
            throw new BaseException(['msg' => $e->getMessage()]);
        }
        (new ImService)->createUser($model['user_id']);

        return $model['user_id'];
    }

    /**
     * 自动注册用户
     * @param $open_id
     * @param $data
     * @param int $refereeId
     * @return mixed
     * @throws \Exception
     * @throws \think\exception\DbException
     */
    public function appregister($post)
    {
        $model = $this;
        $this->startTrans();
        try {
            //加验证
            $nameVerify = isStrLen($post['name'], '用户名',2, 20);
            if(!$nameVerify['rs']) {
                throw new BaseException(['msg' => $nameVerify['msg']]);
            }
            if(!isEmail($post['email'])) {
                throw new BaseException(['msg' => '邮箱格式不对']);
            }
            $passwordVerify = isStrLen($post['password'], '密码',6, 20);
            if(!$passwordVerify['rs']) {
                throw new BaseException(['msg' => $passwordVerify['msg']]);
            }
            // 查询用户是否已存在
            $open_id = md5($post['email']);
            $user = self::detail(['open_id' => $open_id]);
            if($user) {
                throw new BaseException(['msg' => '用户邮箱已注册']);
            }
            // 保存/更新用户记录
            $password = md5('juzi.123' . $post['password']);
            $data = [
                'name' => $post['name'],
                'email' => $post['email'],
                'password' => $password,
                'register_type' => 1,
            ];
            if (!$model->allowField(true)->save(array_merge($data, [
                'open_id' => $open_id,
                'wxapp_id' => self::$wxapp_id
            ]))) {
                throw new BaseException(['msg' => '用户注册失败']);
            }
            // 生成token (session3rd)
            $session = md5($post['email']);
            $this->token = $this->token($session);
            Cache::set($this->token, ['openid' => $session], 86400 * 7);
            $this->commit();
        } catch (\Exception $e) {
            $this->rollback();
            throw new BaseException(['msg' => $e->getMessage()]);
        }

        (new ImService)->createUser($model['user_id']);

        return $model['user_id'];
    }

    /**
     * 手机号码自动注册用户
     * @param $open_id
     * @param $data
     * @param int $refereeId
     * @return mixed
     * @throws \Exception
     * @throws \think\exception\DbException
     */
    public function phoneregister($post)
    {
        $model = $this;
        $this->startTrans();
        try {
            //加验证
            $nameVerify = isStrLen($post['name'], '用户名',2, 20);
            if(!$nameVerify['rs']) {
                throw new BaseException(['msg' => $nameVerify['msg']]);
            }
            if(isEmpty($post['phone'])) {
                throw new BaseException(['msg' => '手机号码格式不对']);
            }
            $passwordVerify = isStrLen($post['password'], '密码',6, 20);
            if(!$passwordVerify['rs']) {
                throw new BaseException(['msg' => $passwordVerify['msg']]);
            }
            if(isEmpty($post['code'])) {
                throw new BaseException(['msg' => '验证码不能为空']);
            }
            // 查询用户是否已存在
            $open_id = md5($post['phone']);
            $user = self::detail(['open_id' => $open_id]);
            if($user) {
                throw new BaseException(['msg' => '用户手机号码已注册']);
            }
            //验证码验证
            $email = EmailModel::detail(['phone' => $post['phone'], 'name' => $post['code'], 'type' => 2]);
            if(!$email) {
                throw new BaseException(['msg' => '验证码错误']);
            }
            if($email['status']) {
                throw new BaseException(['msg' => '验证码已使用']);
            }
            if($email['expire_time'] < time()) {
                throw new BaseException(['msg' => '验证码已过期']);
            }
            // 保存/更新用户记录
            $password = md5('juzi.123' . $post['password']);
            $data = [
                'name' => $post['name'],
                'phone' => $post['phone'],
                'password' => $password,
                'register_type' => 2,
                'status' => 1
            ];
            if (!$model->allowField(true)->save(array_merge($data, [
                'open_id' => $open_id,
                'wxapp_id' => self::$wxapp_id
            ]))) {
                throw new BaseException(['msg' => '用户注册失败']);
            }
            // 生成token (session3rd)
            $session = md5($post['phone']);
            $this->token = $this->token($session);
            Cache::set($this->token, ['openid' => $session], 86400 * 7);
            $email->save(['status' => 1]);
            $this->commit();
        } catch (\Exception $e) {
            $this->rollback();
            throw new BaseException(['msg' => $e->getMessage()]);
        }

        (new ImService)->createUser($model['user_id']);

        return $model['user_id'];
    }

    /**
     * app用户登录  用户名/手机号码 密码
     * @param array $post
     * @return string
     * @throws BaseException
     * @throws \think\Exception
     * @throws \think\exception\DbException
     */
    public function appforgetpass($post)
    {
        try {
            $passwordVerify = isStrLen($post['password'], '密码',6, 20);
            if(!$passwordVerify['rs']) {
                throw new BaseException(['msg' => $passwordVerify['msg']]);
            }
            if(!isEmail($post['email'])) {
                throw new BaseException(['msg' => '邮箱格式不对']);
            }
            if(isEmpty($post['code'])) {
                throw new BaseException(['msg' => '验证码不能为空']);
            }
            $password = md5('juzi.123' . $post['password']);
            $user = self::detail(['email' => $post['email']]);
            if(!$user) {
                throw new BaseException(['msg' => '此邮箱注册的用户不存在']);
            }
            $email = EmailModel::detail(['email' => $post['email'], 'name' => $post['code'], 'type' => 1]);
            if(!$email) {
                throw new BaseException(['msg' => '验证码错误']);
            }
            if($email['status']) {
                throw new BaseException(['msg' => '验证码已使用']);
            }
            if($email['expire_time'] < time()) {
                throw new BaseException(['msg' => '验证码已过期']);
            }
            $user->save(['password' => $password]);
            $email->save(['status' => 1]);
            return true;

        } catch (\Exception $e) {
            throw new BaseException(['msg' => $e->getMessage()]);
        }

        return true;
    }

    /**
     * 安卓用户登录  手机号码忘记密码
     * @param array $post
     * @return string
     * @throws BaseException
     * @throws \think\Exception
     * @throws \think\exception\DbException
     */
    public function phoneforgetpass($post)
    {
        try {
            $passwordVerify = isStrLen($post['password'], '密码',6, 20);
            if(!$passwordVerify['rs']) {
                throw new BaseException(['msg' => $passwordVerify['msg']]);
            }
            if(isEmpty($post['phone'])) {
                throw new BaseException(['msg' => '手机号码格式不对']);
            }
            if(isEmpty($post['code'])) {
                throw new BaseException(['msg' => '验证码不能为空']);
            }
            $password = md5('juzi.123' . $post['password']);
            $user = self::detail(['phone' => $post['phone']]);
            if(!$user) {
                throw new BaseException(['msg' => '此手机号码注册的用户不存在']);
            }
            $email = EmailModel::detail(['phone' => $post['phone'], 'name' => $post['code'], 'type' => 2]);
            if(!$email) {
                throw new BaseException(['msg' => '验证码错误']);
            }
            if($email['status']) {
                throw new BaseException(['msg' => '验证码已使用']);
            }
            if($email['expire_time'] < time()) {
                throw new BaseException(['msg' => '验证码已过期']);
            }
            $user->save(['password' => $password]);
            $email->save(['status' => 1]);
            return true;

        } catch (\Exception $e) {
            throw new BaseException(['msg' => $e->getMessage()]);
        }

        return true;
    }

    /**
     * app验证邮箱
     * @param array $post
     * @return string
     * @throws BaseException
     * @throws \think\Exception
     * @throws \think\exception\DbException
     */
    public function verifyemail($post)
    {
        try {
            if(!isEmail($post['email'])) {
                throw new BaseException(['msg' => '邮箱格式不对']);
            }
            if(isEmpty($post['code'])) {
                throw new BaseException(['msg' => '验证码不能为空']);
            }
            $email = EmailModel::detail(['email' => $post['email'], 'name' => $post['code'], 'type' => 1]);
            if(!$email) {
                throw new BaseException(['msg' => '验证码错误']);
            }
            if($email['status']) {
                throw new BaseException(['msg' => '验证码已使用']);
            }
            if($email['expire_time'] < time()) {
                throw new BaseException(['msg' => '验证码已过期']);
            }
            $user = self::detail(['email' => $post['email']]);
            if(!$user) {
                throw new BaseException(['msg' => '用户不存在']);
            }
            $user->save(['status' => 1]);
            $email->save(['status' => 1]);

            return true;
        } catch (\Exception $e) {
            throw new BaseException(['msg' => $e->getMessage()]);
        }
    }

    /**
     * app验证手机号码
     * @param array $post
     * @return string
     * @throws BaseException
     * @throws \think\Exception
     * @throws \think\exception\DbException
     */
    public function verifyphone($post, $user)
    {
        try {
            if(isEmpty($post['phone'])) {
                throw new BaseException(['msg' => '手机格式不对']);
            }
            if(isEmpty($post['code'])) {
                throw new BaseException(['msg' => '验证码不能为空']);
            }
            $email = EmailModel::detail(['phone' => $post['phone'], 'name' => $post['code'], 'type' => 2]);
            if(!$email) {
                throw new BaseException(['msg' => '验证码错误']);
            }
            if($email['status']) {
                throw new BaseException(['msg' => '验证码已使用']);
            }
            if($email['expire_time'] < time()) {
                throw new BaseException(['msg' => '验证码已过期']);
            }
            if(!$user) {
                $user = self::detail(['phone' => $post['phone']]);
            }
            if(!$user) {
                throw new BaseException(['msg' => '用户不存在']);
            }
            $user->save(['status' => 1]);
            $email->save(['status' => 1]);

            return true;
        } catch (\Exception $e) {
            throw new BaseException(['msg' => $e->getMessage()]);
        }
    }


    /**
     * 个人中心菜单列表
     * @return array
     */
    public function getMenus()
    {
        $menus = [
            'address' => [
                'name' => '收货地址',
                'url' => 'pages/address/index',
                'icon' => 'map'
            ],
            'coupon' => [
                'name' => '领券中心',
                'url' => 'pages/coupon/coupon',
                'icon' => 'lingquan'
            ],
            'my_coupon' => [
                'name' => '我的优惠券',
                'url' => 'pages/user/coupon/coupon',
                'icon' => 'youhuiquan'
            ],
            'sharing_order' => [
                'name' => '拼团订单',
                'url' => 'pages/sharing/order/index',
                'icon' => 'pintuan'
            ],
            'my_bargain' => [
                'name' => '我的砍价',
                'url' => 'pages/bargain/index/index?tab=1',
                'icon' => 'kanjia'
            ],
            'dealer' => [
                'name' => '分销中心',
                'url' => 'pages/dealer/index/index',
                'icon' => 'fenxiaozhongxin'
            ],
            'help' => [
                'name' => '我的帮助',
                'url' => 'pages/user/help/index',
                'icon' => 'help'
            ],
        ];
        // 判断分销功能是否开启
        if (DealerSettingModel::isOpen()) {
            $menus['dealer']['name'] = DealerSettingModel::getDealerTitle();
        } else {
            unset($menus['dealer']);
        }
        return $menus;
    }

}
