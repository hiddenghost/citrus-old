<?php

namespace app\store\controller\user;
use app\store\controller\Controller;
use app\store\model\Label as LabelModel;
use app\store\model\DictData as DictDataModel;

/**
 * 标签管理
 * Class Label
 * @package app\store\controller\store
 */
class Label extends Controller
{
    /**
     * 门店列表
     * @return mixed
     * @throws \think\exception\DbException
     */
    public function index()
    {
        $model = new LabelModel;
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
        $model = new LabelModel;
        if (!$this->request->isAjax()) {
            $category = DictDataModel::getListByDict('user-label');

            return $this->fetch('add', compact( 'category'));
        }
        // 新增记录
        if ($model->add($this->postData('label'))) {
            return $this->renderSuccess('添加成功', url('user.label/index'));
        }
        return $this->renderError($model->getError() ?: '添加失败');
    }

    /**
     * 编辑门店
     * @param $label_id
     * @return array|bool|mixed
     * @throws \think\exception\DbException
     */
    public function edit($label_id)
    {
        // 门店详情
        $model = LabelModel::detail($label_id);
        if (!$this->request->isAjax()) {
            $category = DictDataModel::getListByDict('user-label');

            return $this->fetch('edit', compact('model', 'category'));
        }
        // 新增记录
        if ($model->edit($this->postData('label'))) {
            return $this->renderSuccess('更新成功', url('user.label/index'));
        }
        return $this->renderError($model->getError() ?: '更新失败');
    }

    /**
     * 删除门店
     * @param $label_id
     * @return array
     * @throws \think\exception\DbException
     */
    public function delete($label_id)
    {
        // 门店详情
        $model = LabelModel::detail($label_id);
        if (!$model->setDelete()) {
            return $this->renderError($model->getError() ?: '删除失败');
        }
        return $this->renderSuccess('删除成功');
    }

}