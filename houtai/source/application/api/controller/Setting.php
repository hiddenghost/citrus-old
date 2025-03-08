<?php

namespace app\api\controller;

use app\api\model\Setting as SettingModel;
use app\api\model\User as UserModel;
use app\api\model\Article as ArticleModel;
use app\api\model\UploadFile;

/**
 * 文件库管理
 * Class Upload
 * @package app\api\controller
 */
class Setting extends Controller
{
    private $config;
    private $user;

    /**
     * 构造方法
     * @throws \app\common\exception\BaseException
     * @throws \think\exception\DbException
     */
    public function _initialize()
    {
        parent::_initialize();
        // 存储配置信息
        $this->config = SettingModel::getItem('store');
        // 验证用户
        $this->user = $this->getUser(false);
    }

    /**
     * 客服中心
     * @return array
     * @throws \think\exception\DbException
     */
    public function kefu()
    {
        $model = new ArticleModel;
        $category_id = 10001;
        $list = $model->getList($category_id, 100);
        $data = [
            'list' => $list,
            'phone' => $this->config['phone']
        ];

        return $this->renderSuccess($data);
    }

    /**
     * 客服详情
     * @return array
     * @throws \think\exception\DbException
     */
    public function kefudetail($article_id)
    {
        $data = ArticleModel::detail($article_id);

        return $this->renderSuccess($data);
    }

    /**
     * 新消息通知
     */
    public function notice()
    {
        $data = [
            ['name' => '新消息通知', 'checked' => $this->user['notice_status'] > 0 ? true : false],
        ];

        return $this->renderSuccess($data);
    }

    /**
     * 切换新消息通知选中或者未选中
     */
    public function changenotice()
    {
        $detail = UserModel::detail($this->user['user_id']);
        $data = ['notice_status' => $detail['notice_status'] > 0 ? 0 : 1];

        return $detail->save($data) ? $this->renderSuccess() : $this->renderError('操作失败');
    }

    /**
     * 语言情况
     */
    public function language()
    {
        $data = [
            ['name' => '中文简体', 'checked' => true],
            ['name' => '美式英文', 'checked' => false],
        ];

        return $this->renderSuccess($data);
    }

    /**
     * 版本号
     */
    public function version()
    {
        $data = $this->config;
        $data['logo_image'] = '';
        if($data['logo_image_id'] > 0) {
            $file = UploadFile::detail($data['logo_image_id']);
            $data['logo_image'] = $file['file_path'];
        }

        return $this->renderSuccess($data);
    }

    /**
     * 关于我们
     */
    public function about()
    {
        $model = new ArticleModel;
        $category_id = 10002;
        $list = $model->getList($category_id, 1);
        $data = [];
        if(count($list) > 0) {
            $data = ArticleModel::detail($list[0]['article_id']);
        }

        return $this->renderSuccess($data);
    }

    /**
     * 隐私协议
     */
    public function privacy()
    {
        $model = new ArticleModel;
        $category_id = 10003;
        $list = $model->getList($category_id, 1);
        $data = [];
        if(count($list) > 0) {
            $data = ArticleModel::detail($list[0]['article_id']);
        }

        return $this->renderSuccess($data);
    }

    /**
     * 用户协议
     */
    public function userxieyi()
    {
        $data = ArticleModel::detail(10014);
        return $this->renderSuccess($data);
    }

    /**
     * 账号信息
     */
    public function account()
    {
        $data = [
            'register' => [
                'phone' => substr_cut($this->user['phone']),
                'email' => substr_cut($this->user['email']),
            ],
            'other' => [
                ['name' => '微信', 'image' => 'https://' . $_SERVER['HTTP_HOST'] . '/web/uploads/weixin.png', 'checked' => true]
            ]

        ];

        return $this->renderSuccess($data);
    }

}