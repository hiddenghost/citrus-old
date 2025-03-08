<?php

namespace app\store\controller\user;

use app\store\controller\Controller;
use app\store\model\user\Images as ImagesModel;

/**
 * 照片
 * Class Images
 * @package app\store\controller\user
 */
class Images extends Controller
{
    /**
     * 会员等级列表
     * @return mixed
     * @throws \think\exception\DbException
     */
    public function index($user_id)
    {
        $model = new ImagesModel;
        $list = $model->getList($user_id);

        return $this->fetch('index', compact('list'));
    }

}