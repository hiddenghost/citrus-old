<?php

namespace app\api\controller\user;

use app\api\controller\Controller;
use app\api\model\user\Images as ImagesModel;
use app\api\model\Category as CategoryModel;
use app\api\model\UploadFile as UploadFileModel;

/**
 * 订单售后服务
 * Class service
 * @package app\api\controller\user\order
 */
class Images extends Controller
{
    /* @var \app\api\model\User $user */
    private $user;

    /**
     * 构造方法
     * @throws \app\common\exception\BaseException
     * @throws \think\exception\DbException
     */
    public function _initialize()
    {
        parent::_initialize();
        $this->user = $this->getUser(true);   // 用户信息
    }

    /**
     * 照片墙首页
     * @return array
     * @throws \think\exception\DbException
     */
    public function index()
    {
        // 详情
        $model = new ImagesModel;
        $list = $model->getList($this->user['user_id']);
        $category = CategoryModel::getSubCategoryByIdentifier('picture-cat');
        foreach($category as $key => &$item) {
            if($item['image_id']) {
                $detail = UploadFileModel::detail($item['image_id']);
                $item['image_id'] = $detail['file_url'] .'/'. $detail['file_name'];
            }
        }

        return $this->renderSuccess(compact('list', 'category'));
    }

    /**
     * 图片墙图片上传
     * @return array
     * @throws \think\exception\DbException
     * @throws \Exception
     */
    public function submit()
    {
        $param = $this->request->post();
        if(!$param['category_id']) {
            return $this->renderError('图片分类必填');
        }
        if(!$param['image_id']) {
            return $this->renderError('图片必填');
        }
        $model = ImagesModel::detail(['user_id' => $this->user['user_id'], 'category_id' => $param['category_id']]);
        if ($model) {
            if($model->save($param)) {
                return $this->renderSuccess([], '提交成功');
            }
        }else{
            $model = new ImagesModel;
            $param['user_id'] = $this->user['user_id'];
            if($model->add($param)) {
                return $this->renderSuccess([], '提交成功');
            }
        }

        return $this->renderError('提交失败');
    }

    /**
     * 图片墙图片上传
     * @return array
     * @throws \think\exception\DbException
     * @throws \Exception
     */
    public function delete()
    {
        $param = $this->request->post();
        if(!$param['category_id']) {
            return $this->renderError('图片分类必填');
        }
        $model = ImagesModel::detail(['user_id' => $this->user['user_id'], 'category_id' => $param['category_id']]);
        if(!$model) {
            return $this->renderSuccess([], '删除成功');
        }
        if($model->setDelete()) {
            return $this->renderSuccess([], '删除成功');
        }
        return $this->renderError('删除失败');
    }

}