<?php

namespace app\api\controller;

use app\api\model\WxUser as WxUserModel;
use app\api\model\Game as GameModel;
use think\Cache;

/**
 * 微信分享
 * Class Wxuser
 * @package app\api\controller
 */
class Wxshare extends Controller
{
    private $appId;
    private $appSecret;
    public $posturl = '';

    public function _initialize() {
        $this->appId = 'wx9c7ab3a4e43ebef0';
        $this->appSecret = '4ed230039aa41d9da8c576d81dc10e1a';
    }

    public function getSignPackage() {
        $jsapiTicket = $this->getJsApiTicket();
        // 注意 URL 一定要动态获取，不能 hardcode.
        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
        $url = "$protocol$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";

        if($this->posturl!=''){
            $url = $this->posturl;
        }

        $timestamp = time();
        $nonceStr = $this->createNonceStr();

        // 这里参数的顺序要按照 key 值 ASCII 码升序排序
        $string = "jsapi_ticket=$jsapiTicket&noncestr=$nonceStr&timestamp=$timestamp&url=$url";

        $signature = sha1($string);

        $signPackage = array(
            "ticket"     => $jsapiTicket,
            "appId"     => $this->appId,
            "nonceStr"  => $nonceStr,
            "timestamp" => $timestamp,
            "url"       => $url,
            "signature" => $signature,
            "rawString" => $string
        );
        return $signPackage;
    }

    private function createNonceStr($length = 16) {
        $chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
        $str = "";
        for ($i = 0; $i < $length; $i++) {
            $str .= substr($chars, mt_rand(0, strlen($chars) - 1), 1);
        }
        return $str;
    }

    private function getJsApiTicket() {
        // jsapi_ticket 应该全局存储与更新，以下代码以写入到文件中做示例
        $data = Cache::get('wx_jsapi_ticket');
        if (!isset($data['expire_time']) || $data['expire_time'] < time()) {
            $accessToken = $this->getAccessToken();
            // 如果是企业号用以下 URL 获取 ticket
            // $url = "https://qyapi.weixin.qq.com/cgi-bin/get_jsapi_ticket?access_token=$accessToken";
            $url = "https://api.weixin.qq.com/cgi-bin/ticket/getticket?type=jsapi&access_token=$accessToken";
            $res = json_decode($this->httpGet($url));
            $ticket = $res->ticket;
            if ($ticket) {
                $data = [];
                $data['expire_time'] = time() + 7000;
                $data['jsapi_ticket'] = $ticket;
                //存到授权的回调到一个php里
                Cache::tag('cache')->set('wx_jsapi_ticket', $data);
            }
        } else {
            $ticket = $data['jsapi_ticket'];
        }

        return $ticket;
    }

    public function getCardTicket(){

        $data = Cache::get('wx_api_ticket');
        if (!isset($data['expire_time']) || $data['expire_time'] < time()) {
            $wx_access_token = Cache::get('wx_access_token');
            $accessToken =  $wx_access_token['access_token'];
            $url = "https://api.weixin.qq.com/cgi-bin/ticket/getticket?access_token=$accessToken&type=wx_card";
            $res = json_decode($this->httpGet($url));
            $ticket = $res->ticket;
            if ($ticket) {
                $data = [];
                $data['expire_time'] = time() + 7000;
                $data['ticket'] = $ticket;
                Cache::tag('cache')->set('wx_api_ticket', $data);
            }
        } else {
            $ticket = $data['ticket'];
        }
        return $ticket;

    }

    private function getAccessToken() {
        // access_token 应该全局存储与更新，以下代码以写入到文件中做示例

        $data = Cache::get('wx_access_token');
        if (!isset($data['expire_time']) || $data['expire_time'] < time()) {
            // 如果是企业号用以下URL获取access_token
            // $url = "https://qyapi.weixin.qq.com/cgi-bin/gettoken?corpid=$this->appId&corpsecret=$this->appSecret";
            $url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=$this->appId&secret=$this->appSecret";
            $res = json_decode($this->httpGet($url));
            $access_token = $res->access_token;
            if ($access_token) {
                $data = [];
                $data['expire_time'] = time() + 7000;
                $data['access_token'] = $access_token;
                Cache::tag('cache')->set('wx_access_token', $data);
            }
        } else {
            $access_token = $data['access_token'];
        }
        return $access_token;
    }

    private function httpGet($url) {
        $curl = curl_init();

        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);

        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        //curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_TIMEOUT, 15);
        // 为保证第三方服务器与微信服务器之间数据传输的安全性，所有微信接口采用https方式调用，必须使用下面2行代码打开ssl安全校验。
        // 如果在部署过程中代码在此处验证失败，请到 http://curl.haxx.se/ca/cacert.pem 下载新的证书判别文件。
        //curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, true);
        //curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, true);
        curl_setopt($curl, CURLOPT_URL, $url);

        $res = curl_exec($curl);
        curl_close($curl);

        return $res;
    }


    /**
     * 得到微信分享的配置的token
     * @return [type] [description]
     */
    public function gettokens($url)
    {
        $this->posturl = urldecode($url);
        $signPackage = $this->GetSignPackage();
        $appId = $signPackage['appId'];
        $timestamp = $signPackage['timestamp'];
        $nonceStr = $signPackage['nonceStr'];
        $signature = $signPackage['signature'];
        $surl = $signPackage['url'];
        $data = array(
            "appId"=>$appId,
            "timestamp"=>$timestamp,
            "nonceStr"=>$nonceStr,
            "signature"=>$signature,
            "surl"=>$surl
        ); //返回给前端的数据

        return $this->renderSuccess([
            'detail' => $data,
        ]);
    }

}