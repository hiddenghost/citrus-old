<?php

namespace app\store\controller;

use app\store\model\Game as GameModel;
use app\store\model\GameCategory as CategoryModel;
use app\store\model\GameUsers as GameUsersModel;

/**
 * 局管理控制器
 * Class Goods
 * @package app\store\controller
 */
class Game extends Controller
{
    /**
     * 局列表(出售中)
     * @return mixed
     * @throws \think\exception\DbException
     */
    public function index()
    {
        // 获取全部商品列表
        $model = new GameModel;
        $list = $model->getList(array_merge(['status' => -1], $this->request->param()));
        $list->each(function($item, $key) {
            $item['actual_people'] = count($item['users']);

            return $item;
        });
        // 商品分类
        $catgory = CategoryModel::getCacheTree();
        return $this->fetch('index', compact('list', 'catgory'));
    }

    /**
     * 局详情
     * @return mixed
     * @throws \think\exception\DbException
     */
    public function detail($game_id)
    {
        // 获取全部商品列表
        $model = GameModel::detail($game_id);

        return $this->fetch('detail', compact('model'));
    }

    /**
     * 修改局状态
     * @param $game_id
     * @param boolean $state
     * @return array
     */
    public function state($game_id, $state)
    {
        // 商品详情
        $model = GameModel::detail($game_id);
        if (!$model->setStatus($state)) {
            return $this->renderError('操作失败');
        }
        return $this->renderSuccess('操作成功');
    }

    /**
     * 删除局
     * @param $game_id
     * @return array
     */
    public function delete($game_id)
    {
        // 商品详情
        $model = GameModel::detail($game_id);
        if (!$model->setDelete()) {
            return $this->renderError($model->getError() ?: '删除失败');
        }
        return $this->renderSuccess('删除成功');
    }

    /**
     *
     * @param $game_id
     * @return mixed
     * @throws \think\exception\DbException
     */
    public function users($game_id)
    {
        $model = new GameUsersModel;
        $list = $model->getList($game_id);
        return $this->fetch('users', compact('list'));
    }

}
