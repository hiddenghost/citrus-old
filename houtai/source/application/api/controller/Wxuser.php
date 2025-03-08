<?php

namespace app\api\controller;

use app\api\model\WxUser as WxUserModel;
use app\api\model\Game as GameModel;

/**
 * 微信H5用户
 * Class Wxuser
 * @package app\api\controller
 */
class Wxuser extends Controller
{
    public function index()
    {
        $data = [
            'a' => 123
        ];
        return $this->renderSuccess($data);
    }

    //private $login_page_url = "https://open.weixin.qq.com/connect/qrconnect?";//微信登录界面
    private $login_page_url = "https://open.weixin.qq.com/connect/oauth2/authorize?";//微信登录界面
    private $get_accessToken_url = "https://api.weixin.qq.com/sns/oauth2/access_token?";//后去token的url
    //private $get_openId_url = 'https://graph.qq.com/oauth2.0/me';//获取openid的url
    private $get_user_info = "https://api.weixin.qq.com/sns/userinfo?";//获取用户信息的url
    private $app_id = 'wx9c7ab3a4e43ebef0';
    private $app_key = '4ed230039aa41d9da8c576d81dc10e1a';
    public $redirect_url = '';
    private $access_token;
    private $_code;
    private $_isFocusUrl = 'https://api.weixin.qq.com/cgi-bin/user/info';
    //QQ登录页面
    private function get_wx_login_page()
    {
        $this->redirect_url = urlencode('http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']);
        HLog::write('http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']);
        $state = 'STATE';
        $query = [
            'appid' => $this->app_id,
            'redirect_uri' => $this->redirect_url,
            'response_type' => 'code',
            'scope' => 'snsapi_userinfo',
            'state' => $state,
        ];
        $_SESSION['state'] = $state;//保存state验证

        $url= $this->login_page_url.http_build_query($query).'#wechat_redirect';
        $url = 'https://open.weixin.qq.com/connect/oauth2/authorize?appid=' . $this->app_id . '&redirect_uri=' . $this->redirect_url . '&response_type=code&scope=snsapi_userinfo&state=STATE#wechat_redirect';

        header("Location:$url");
        exit;
    }

    //获取access_token
    public function get_code()
    {
        //获取code
        @$code = $_GET['code'];
        if($this->_code) {
            $code = $this->_code;
        }
        if(!$code){
            $this->get_wx_login_page();
        }
//        $state = $_GET['state'];
        /*
        if($state != $_SESSION['state']){
            echo "state is wrong!";
            exit;
        }
        */
        $_SESSION['state'] = null;
        $query = [
            'grant_type' => 'authorization_code',
            'code'       => $code,
            'secret' => $this->app_key,
            'appid' => $this->app_id,
        ];

        return $this->get_curl($this->get_accessToken_url, http_build_query($query));

    }

    //获取token
    private function get_token_info()
    {
        //获取access_token
        /* {
            "access_token":"ACCESS_TOKEN",
            "expires_in":7200,
            "refresh_token":"REFRESH_TOKEN",
            "openid":"OPENID",
            "scope":"SCOPE"
        } */
        $data = json_decode($this->get_code(),true);
        //参数组装数组


        $this->access_token = isset($data["access_token"]) ? $data["access_token"] : '';

        $array = array(
            'access_token' => isset($data["access_token"]) && $data["access_token"] ? $data["access_token"] : '',
            'openid'       => isset($data['openid']) && $data['openid'] ? $data['openid'] : '',
        );
        //是否关注公众号
//        $subscribe = $this->get_curl($this->_isFocusUrl, http_build_query($array));
//        $isFocus = $subscribe['subscribe']; //值为1是关注，其他的不是关注
//        $_SESSION['is_focus'] = $isFocus;

        return $this->get_curl($this->get_user_info, http_build_query($array));
    }

    //获取openid&&获取用户信息
    public function getUserInfo($code)
    {
        $this->_code = $code;
        $data = $this->get_token_info();


        $data = json_decode($data, true);
        $data['access_token'] = $this->access_token;

        return $data;
    }

    //curl GET请求
    public function get_curl($url,$query)
    {
        $url_request = $url.$query;
        $curl = curl_init();

        //设置抓取的url
        curl_setopt($curl, CURLOPT_URL, $url_request);
        //设置头文件的信息作为数据流输出
        curl_setopt($curl, CURLOPT_HEADER, 0);
        //设置获取的信息以文件流的形式返回,而不是直接输出.
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        //执行命令
        $data = curl_exec($curl);
        //关闭URL请求
        curl_close($curl);
        return $data;

    }

    /**
     * 微信公众号手机端授权登录
     * @return [type] [description]
     */
    public function wxalogin($code)
    {
        $userinfo = $this->getUserInfo($code);
        if(!$userinfo['openid']) {
            return $this->renderError($userinfo['errmsg']);
        }
        $record = WxUserModel::get(['open_id' => $userinfo['openid'], 'wxapp_id' => 10001, 'is_delete' => 0]);
        if($record) {
            return $this->renderSuccess([
                'detail' => $record,
            ]);
        }else{
            $data = array(
                'name' => $userinfo['nickname'],
                'open_id' => $userinfo['openid'],
                'sex' => $userinfo['sex'],
                'city' => $userinfo['city'],
                'province' => $userinfo['province'],
                'avatarUrl' => $userinfo['headimgurl'],
                'create_time' => time(),
                'wxapp_id' => 10001,
                'token' => md5(uniqid())
            );
            $model = new WxUserModel;
            $id = $model->save($data);
            if(1 > $id) {
                return $this->renderError('操作失败');
            }

            return $this->renderSuccess([
                'detail' => $model,
            ]);
        }
    }

    /**
     * 来自微信免认证的详情
     * @param $game_id
     */
    public function detailforwx($game_id)
    {
        //验证微信端
        if(!$game_id) {
            return $this->renderError('编号不存在');
        }
        $model = new GameModel;
        $game = $model->getDetails($game_id, $this->getUser(false));
        unset($game['user']);

        foreach($game['users'] as $key => &$item) {
            $item['avatarUrl'] = $item['user']['avatarUrl'];
            unset($item['user']);
        }
        return $this->renderSuccess([
            'detail' => $game,
        ]);
    }

}
