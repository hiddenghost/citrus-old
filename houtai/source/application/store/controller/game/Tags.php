<?php

namespace app\store\controller\game;
use app\store\controller\Controller;
use app\store\model\Tags as TagsModel;

/**
 * 标签管理
 * Class Tags
 * @package app\store\controller\store
 */
class Tags extends Controller
{
    /**
     * 门店列表
     * @return mixed
     * @throws \think\exception\DbException
     */
    public function index()
    {
        $model = new TagsModel;
        $list = $model->getList();
        return $this->fetch('index', compact('list'));
    }

    /**
     * 添加门店
     * @return array|bool|mixed
     * @throws \Exception
     */
    public function add()
    {
        $model = new TagsModel;
        if (!$this->request->isAjax()) {
            return $this->fetch('add');
        }
        // 新增记录
        if ($model->add($this->postData('tags'))) {
            return $this->renderSuccess('添加成功', url('game.tags/index'));
        }
        return $this->renderError($model->getError() ?: '添加失败');
    }

    /**
     * 编辑门店
     * @param $shop_id
     * @return array|bool|mixed
     * @throws \think\exception\DbException
     */
    public function edit($tags_id)
    {
        // 门店详情
        $model = TagsModel::detail($tags_id);
        if (!$this->request->isAjax()) {
            return $this->fetch('edit', compact('model'));
        }
        // 新增记录
        if ($model->edit($this->postData('tags'))) {
            return $this->renderSuccess('更新成功', url('game.tags/index'));
        }
        return $this->renderError($model->getError() ?: '更新失败');
    }

    /**
     * 删除门店
     * @param $tags_id
     * @return array
     * @throws \think\exception\DbException
     */
    public function delete($tags_id)
    {
        // 门店详情
        $model = TagsModel::detail($tags_id);
        if (!$model->setDelete()) {
            return $this->renderError($model->getError() ?: '删除失败');
        }
        return $this->renderSuccess('删除成功');
    }

}