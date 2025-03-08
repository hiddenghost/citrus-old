<?php

namespace app\store\controller\setting;

use app\store\controller\Controller;
use app\store\model\Dict as DictModel;
use app\store\model\DictData as Model;

/**
 * 字典数据
 * Class DictData
 * @package app\store\controller\setting
 */
class Dictdata extends Controller
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
     * @param $data_id
     * @return array
     * @throws \think\exception\DbException
     */
    public function delete($data_id)
    {
        $model = Model::detail($data_id);
        if (!$model->setDelete()) {
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
            $dictList = (new DictModel)->getAllList();
            return $this->fetch('add', compact('dictList'));
        }
        // 新增记录
        $model = new Model;
        if ($model->add($this->postData('post'))) {
            return $this->renderSuccess('添加成功', url('setting.dictdata/index'));
        }
        return $this->renderError($model->getError() ?: '添加失败');
    }

    /**
     * 编辑物流公司
     * @param $data_id
     * @return array|mixed
     * @throws \think\exception\DbException
     */
    public function edit($data_id)
    {
        // 模板详情
        $model = Model::detail($data_id);
        if (!$this->request->isAjax()) {
            $dictList = (new DictModel)->getAllList();
            return $this->fetch('edit', compact('model','dictList'));
        }
        // 更新记录
        if ($model->edit($this->postData('post'))) {
            return $this->renderSuccess('更新成功', url('setting.dictdata/index'));
        }
        return $this->renderError($model->getError() ?: '更新失败');
    }



}