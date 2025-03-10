<?php

namespace app\common\library\storage\engine;

use Qiniu\Auth;
use Qiniu\Storage\UploadManager;
use Qiniu\Storage\BucketManager;

/**
 * 七牛云存储引擎
 * Class Qiniu
 * @package app\common\library\storage\engine
 */
class Qiniu extends Server
{
    private $config;

    /**
     * 构造方法
     * Qiniu constructor.
     * @param $config
     */
    public function __construct($config)
    {
        parent::__construct();
        $this->config = $config;
    }

    /**
     * 执行上传
     * @return bool|mixed
     * @throws \Exception
     */
    public function upload()
    {
        // 要上传图片的本地路径
        $realPath = $this->getRealPath();

        // 构建鉴权对象
        $auth = new Auth($this->config['access_key'], $this->config['secret_key']);

        // 要上传的空间
        $token = $auth->uploadToken($this->config['bucket']);

        // 初始化 UploadManager 对象并进行文件的上传
        $uploadMgr = new UploadManager();

        // 调用 UploadManager 的 putFile 方法进行文件的上传
        list(, $error) = $uploadMgr->putFile($token, $this->fileName, $realPath);

        /* @var $error \Qiniu\Http\Error */
        if ($error !== null) {
            $this->error = $error->message();
            return false;
        }
        return true;
    }

    /**
     * 执行上传
     * @return bool|mixed
     * @throws \Exception
     */
    public function token()
    {
        // 要上传图片的本地路径
        $realPath = $this->getRealPath();

        // 构建鉴权对象
        $auth = new Auth($this->config['access_key'], $this->config['secret_key']);

        // 要上传的空间
        $token = $auth->uploadToken($this->config['bucket']);

        return $token;
    }


    /**
     * 删除文件
     * @param $fileName
     * @return bool|mixed
     */
    public function delete($fileName)
    {
        // 构建鉴权对象
        $auth = new Auth($this->config['access_key'], $this->config['secret_key']);
        // 初始化 UploadManager 对象并进行文件的上传
        $bucketMgr = new BucketManager($auth);
        /* @var $error \Qiniu\Http\Error */
        $error = $bucketMgr->delete($this->config['bucket'], $fileName);
        if ($error !== null) {
            $this->error = $error->message();
            return false;
        }
        return true;
    }

    /**
     * 返回文件路径
     * @return mixed
     */
    public function getFileName()
    {
        return $this->fileName;
    }

}
