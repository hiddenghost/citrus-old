<?php

namespace app\common\library\wechat;

/**
 * 微信小程序用户管理类
 * Class WxUser
 * @package app\common\library\wechat
 */
class WxUser extends WxBase
{
    /**
     * 获取session_key
     * @param $code
     * @return array|mixed
     */
    public function sessionKey($code)
    {
        /**
         * code 换取 session_key
         * ​这是一个 HTTPS 接口，开发者服务器使用登录凭证 code 获取 session_key 和 openid。
         * 其中 session_key 是对用户数据进行加密签名的密钥。为了自身应用安全，session_key 不应该在网络上传输。
         */
//        $url = 'https://api.weixin.qq.com/sns/jscode2session';
        $url = 'https://api.weixin.qq.com/sns/oauth2/access_token';
        $result = json_decode(curl($url, [
            'appid' => $this->appId,
            'secret' => $this->appSecret,
            'grant_type' => 'authorization_code',
            'code' => $code
//            'js_code' => $code
        ]), true);
        if (isset($result['errcode'])) {
            $this->error = $result['errmsg'];
            // 记录日志
            $this->doLogs([
                'describe' => '获取access_token',
                'url' => $url,
                'appId' => $this->appId,
                'result' => $result
            ]);
            return false;
        }
        return $result;
    }

    /**
     * 获取用户基本信息
     * @param $access_token
     * @param $openid
     * @return array|mixed
     */
    public function getUserInfo($access_token, $openid)
    {
        $url = 'https://api.weixin.qq.com/sns/userinfo';
        $result = json_decode(curl($url, [
            'access_token' => $access_token,
            'openid' => $openid
        ]), true);
        if (isset($result['errcode'])) {
            $this->error = $result['errmsg'];
            // 记录日志
            $this->doLogs([
                'describe' => '获取用户基本信息',
                'url' => $url,
                'appId' => $this->appId,
                'result' => $result
            ]);
            return false;
        }
        return $result;
    }

}