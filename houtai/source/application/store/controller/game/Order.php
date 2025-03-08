<?php

namespace app\store\controller;

use app\store\model\GameOrder as OrderModel;
use app\store\model\Express as ExpressModel;
use app\store\model\store\shop\Clerk as ShopClerkModel;
use app\store\model\store\Shop as ShopModel;

/**
 * 订单管理
 * Class Order
 * @package app\store\controller
 */
class Order extends Controller
{
    /**
     * 待收货订单列表
     * @return mixed
     * @throws \think\exception\DbException
     */
    public function receipt_list()
    {
        return $this->getList('待完成订单列表', 'run');
    }

    /**
     * 待付款订单列表
     * @return mixed
     * @throws \think\exception\DbException
     */
    public function pay_list()
    {
        return $this->getList('待付款订单列表', 'pay');
    }

    /**
     * 已完成订单列表
     * @return mixed
     * @throws \think\exception\DbException
     */
    public function complete_list()
    {
        return $this->getList('已完成订单列表', 'complete');
    }

    /**
     * 已取消订单列表
     * @return mixed
     * @throws \think\exception\DbException
     */
    public function cancel_list()
    {
        return $this->getList('已取消订单列表', 'cancel');
    }

    /**
     * 全部订单列表
     * @return mixed
     * @throws \think\exception\DbException
     */
    public function all_list()
    {
        return $this->getList('全部订单列表', 'all');
    }

    /**
     * 订单详情
     * @param $order_id
     * @return mixed
     * @throws \think\exception\DbException
     */
    public function detail($order_id)
    {
        // 订单详情
        $detail = OrderModel::detail($order_id);

        // 门店店员列表
        $shopClerkList = (new ShopClerkModel)->getList(true);
        return $this->fetch('detail', compact(
            'detail',
            'shopClerkList'
        ));
    }

    /**
     * 修改订单价格
     * @param $order_id
     * @return array
     * @throws \think\exception\DbException
     */
    public function updatePrice($order_id)
    {
        $model = OrderModel::detail($order_id);
        if ($model->updatePrice($this->postData('order'))) {
            return $this->renderSuccess('修改成功');
        }
        return $this->renderError($model->getError() ?: '修改失败');
    }

    /**
     * 订单列表
     * @param string $title
     * @param string $dataType
     * @return mixed
     * @throws \think\exception\DbException
     */
    private function getList($title, $dataType)
    {
        // 订单列表
        $model = new OrderModel;
        $list = $model->getList($dataType, $this->request->param());
        // 自提门店列表
        $shopList = ShopModel::getAllList();
        return $this->fetch('index', compact('title', 'dataType', 'list', 'shopList'));
    }

}
