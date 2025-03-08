<?php

namespace app\store\controller\game;

use app\store\controller\Controller;
use app\store\model\City as CityModel;

/**
 * 城市
 * Class City
 * @package app\store\controller\goods
 */
class City extends Controller
{
    /**
     * 城市列表
     * @return mixed
     */
    public function index()
    {
        $model = new CityModel;
        $list = $model->getCacheTree();
        return $this->fetch('index', compact('list'));
    }

    /**
     * 删除商品分类
     * @param $id
     * @return array|bool
     * @throws \think\Exception
     * @throws \think\exception\DbException
     */
    public function delete($id)
    {
        $model = CityModel::get($id);
        if (!$model->remove($id)) {
            return $this->renderError($model->getError() ?: '删除失败');
        }
        return $this->renderSuccess('删除成功');
    }

    /**
     * 添加商品分类
     * @return array|mixed
     */
    public function add()
    {
        $model = new CityModel;
        if (!$this->request->isAjax()) {
            // 获取所有地区
            $list = $model->getCacheTree();
            return $this->fetch('add', compact('list'));
        }
        // 新增记录
        if ($model->add($this->postData('city'))) {
            return $this->renderSuccess('添加成功', url('game.city/index'));
        }
        return $this->renderError($model->getError() ?: '添加失败');
    }

    /**
     * 编辑商品分类
     * @param $id
     * @return array|mixed
     * @throws \think\exception\DbException
     */
    public function edit($id)
    {
        // 模板详情
        $model = CityModel::get($id);
        if (!$this->request->isAjax()) {
            // 获取所有地区
            $list = $model->getCacheTree();
            return $this->fetch('edit', compact('model', 'list'));
        }
        // 更新记录
        if ($model->edit($this->postData('city'))) {
            return $this->renderSuccess('更新成功', url('game.city/index'));
        }
        return $this->renderError($model->getError() ?: '更新失败');
    }

}
