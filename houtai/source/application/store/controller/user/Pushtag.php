<?php

namespace app\store\controller\user;
use app\store\controller\Controller;
use app\store\model\push\Tag as TagModel;
use app\store\model\push\TagUser as TagUserModel;

/**
 * 推送标签管理
 * Class Pushtag
 * @package app\store\controller\store
 */
class Pushtag extends Controller
{
    /**
     * 列表
     * @return mixed
     * @throws \think\exception\DbException
     */
    public function index()
    {
        $model = new TagModel;
        $list = $model->getList();
        $list->each(function($item, $key) {
            $userList = TagUserModel::getListByTag($item['push_tag_id']);
            $total_nums = count($userList);
            $item['userList'] = $userList;
            $item['total_nums'] = $total_nums;
        });

        return $this->fetch('index', compact('list'));
    }

    /**
     * 添加门店
     * @return array|bool|mixed
     * @throws \Exception
     */
    public function add()
    {
        $model = new TagModel;
        if (!$this->request->isAjax()) {

            return $this->fetch('add');
        }
        // 新增记录
        if ($model->addData($this->postData('post'))) {
            return $this->renderSuccess('添加成功', url('user.pushtag/index'));
        }
        return $this->renderError($model->getError() ?: '添加失败');
    }

    /**
     * 编辑门店
     * @param $push_tag_id
     * @return array|bool|mixed
     * @throws \think\exception\DbException
     */
    public function edit($push_tag_id)
    {
        // 详情
        $model = TagModel::detail($push_tag_id);
        if (!$this->request->isAjax()) {

            return $this->fetch('edit', compact('model'));
        }
        // 新增记录
        if ($model->editData($this->postData('post'))) {
            return $this->renderSuccess('更新成功', url('user.pushtag/index'));
        }
        return $this->renderError($model->getError() ?: '更新失败');
    }

    /**
     * 删除门店
     * @param $push_tag_id
     * @return array
     * @throws \think\exception\DbException
     */
    public function delete($push_tag_id)
    {
        // 详情
        $model = TagModel::detail($push_tag_id);
        if (!$model->save(['is_delete' => 1])) {
            return $this->renderError($model->getError() ?: '删除失败');
        }
        return $this->renderSuccess('删除成功');
    }

}