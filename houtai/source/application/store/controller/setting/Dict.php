<?php

namespace app\store\controller\setting;

use app\store\controller\Controller;
use app\store\model\Dict as Model;

/**
 * 字典
 * Class Dict
 * @package app\store\controller\setting
 */
class Dict extends Controller
{
    /**
     * 物流公司列表
     * @return mixed
     * @throws \think\exception\DbException
     */
    public function index()
    {
        $model = new Model;
        $list = $model->getList();
        return $this->fetch('index', compact('list'));
    }

    /**
     * 删除
     * @param $dict_id
     * @return array
     * @throws \think\exception\DbException
     */
    public function delete($dict_id)
    {
        $model = Model::detail($dict_id);
        if (!$model->remove()) {
            $error = $model->getError() ?: '删除失败';
            return $this->renderError($error);
        }
        return $this->renderSuccess('删除成功');
    }

    /**
     * 添加物流公司
     * @return array|mixed
     */
    public function add()
    {
        if (!$this->request->isAjax()) {
            return $this->fetch('add');
        }
        // 新增记录
        $model = new Model;
        if ($model->add($this->postData('post'))) {
            return $this->renderSuccess('添加成功', url('setting.dict/index'));
        }
        return $this->renderError($model->getError() ?: '添加失败');
    }

    /**
     * 编辑物流公司
     * @param $dict_id
     * @return array|mixed
     * @throws \think\exception\DbException
     */
    public function edit($dict_id)
    {
        // 模板详情
        $model = Model::detail($dict_id);
        if (!$this->request->isAjax()) {
            return $this->fetch('edit', compact('model'));
        }
        // 更新记录
        if ($model->edit($this->postData('post'))) {
            return $this->renderSuccess('更新成功', url('setting.dict/index'));
        }
        return $this->renderError($model->getError() ?: '更新失败');
    }



}