<?php

namespace app\store\model;

use app\common\model\GameOrder as OrderModel;

use app\store\model\User as UserModel;
use app\store\model\UserCoupon as UserCouponModel;
use app\store\service\order\Export as Exportservice;
use app\common\library\helper;
use app\common\service\order\Refund as RefundService;

/**
 * 拼团订单模型
 * Class Order
 * @package app\store\model\sharing
 */
class GameOrder extends OrderModel
{
    /**
     * 订单列表
     * @param string $dataType
     * @param array $query
     * @return \think\Paginator
     * @throws \think\exception\DbException
     */
    public function getList($dataType, $query = [])
    {
        // 检索查询条件
        !empty($query) && $this->setWhere($query);
        // 获取数据列表
        return $this->with(['game', 'user'])
            ->alias('order')
            ->field('order.*, game.status as active_status')
            ->join('user', 'user.user_id = order.user_id', 'LEFT')
            ->join('game', 'order.game_id = game.game_id', 'LEFT')
            ->where($this->transferDataType($dataType))
            ->where('order.is_delete', '=', 0)
            ->order(['order.create_time' => 'desc'])
            ->paginate(10, false, [
                'query' => \request()->request()
            ]);
    }

    /**
     * 订单列表(全部)
     * @param $dataType
     * @param array $query
     * @return false|\PDOStatement|string|\think\Collection
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getListAll($dataType, $query = [])
    {
        // 检索查询条件
        !empty($query) && $this->setWhere($query);
        // 获取数据列表
        return $this->with(['game', 'user'])
            ->alias('order')
            ->field('order.*, game.status as active_status')
            ->join('user', 'user.user_id = order.user_id', 'LEFT')
            ->join('game', 'order.game_id = game.game_id', 'LEFT')
            ->where($this->transferDataType($dataType))
            ->where('order.is_delete', '=', 0)
            ->order(['order.create_time' => 'desc'])
            ->select();
    }

    /**
     * 订单导出
     * @param $dataType
     * @param $query
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function exportList($dataType, $query)
    {
        // 获取订单列表
        $list = $this->getListAll($dataType, $query);
        // 导出csv文件
        return (new Exportservice)->orderList($list);
    }



    /**
     * 设置检索查询条件
     * @param $query
     */
    private function setWhere($query)
    {
        if (isset($query['search']) && !empty($query['search'])) {
            $this->where('order_no|user.nickName', 'like', '%' . trim($query['search']) . '%');
        }
        if (isset($query['start_time']) && !empty($query['start_time'])) {
            $this->where('order.create_time', '>=', strtotime($query['start_time']));
        }
        if (isset($query['end_time']) && !empty($query['end_time'])) {
            $this->where('order.create_time', '<', strtotime($query['end_time']) + 86400);
        }
        if (isset($query['game_id']) && $query['game_id'] > 0) {
            $this->where('order.game_id', '=', (int)$query['game_id']);
        }
        if (isset($query['extract_shop_id']) && !empty($query['extract_shop_id'])) {
            $query['extract_shop_id'] > -1 && $this->where('extract_shop_id', '=', $query['extract_shop_id']);
        }
    }

    /**
     * 转义数据类型条件
     * @param $dataType
     * @return array
     */
    private function transferDataType($dataType)
    {
        // 数据类型
        $filter = [];
        switch ($dataType) {
            case 'all':
                // 全部
                $filter = [];
                break;
            case 'pay':
                // 待支付
                $filter = ['pay_status' => 10, 'order_status' => 10];
                break;
            case 'sharing';
                // 拼团中
                $filter['active.status'] = 10;
                break;
            case 'sharing_succeed';
                // 拼团成功
                $filter['active.status'] = 20;
                break;
            case 'sharing_fail';
                // 拼团失败
                $filter['active.status'] = 30;
                break;
            case 'run':
                // 进行中
                $filter = [
                    'pay_status' => 20,
                    'receipt_status' => 10
                ];
                break;
            case 'complete':
                // 已完成
                $filter = ['order_status' => 30];
                break;
            case 'cancel':
                // 已取消
                $filter = ['order_status' => 20];
                break;
        }
        return $filter;
    }

    /**
     * 修改订单价格
     * @param $data
     * @return bool
     */
    public function updatePrice($data)
    {
        if ($this['pay_status']['value'] != 10) {
            $this->error = '该订单不合法';
            return false;
        }
        // 实际付款金额
        $payPrice = bcadd($data['update_price'], $data['update_express_price'], 2);
        if ($payPrice <= 0) {
            $this->error = '订单实付款价格不能为0.00元';
            return false;
        }
        return $this->save([
                'order_no' => $this->orderNo(), // 修改订单号, 否则微信支付提示重复
                'order_price' => $data['update_price'],
                'pay_price' => $payPrice,
                'update_price' => helper::bcsub($data['update_price'], helper::bcsub($this['total_price'], $this['coupon_money'])),
                'express_price' => $data['update_express_price']
            ]) !== false;
    }

    /**
     * 审核：用户取消订单
     * @param $data
     * @return bool
     */
    public function confirmCancel($data)
    {
        // 判断订单是否有效
        if ($this['pay_status']['value'] != 20) {
            $this->error = '该订单不合法';
            return false;
        }
        // 订单取消事件
        return $this->transaction(function () use ($data) {
            if ($data['is_cancel'] == true) {
                // 执行退款操作
                (new RefundService)->execute($this);
                // 回退商品库存
                (new OrderGoods)->backGoodsStock($this['goods'], true);
                // 回退用户优惠券
                $this['coupon_id'] > 0 && UserCouponModel::setIsUse($this['coupon_id'], false);
                // 回退用户积分
                $User = UserModel::detail($this['user_id']);
                $describe = "订单取消：{$this['order_no']}";
                $this['points_num'] > 0 && $User->setIncPoints($this['points_num'], $describe);
            }
            // 更新订单状态
            return $this->save(['order_status' => $data['is_cancel'] ? 20 : 10]);
        });
    }

    /**
     * 拼团失败手动退款
     * @return bool|false|int
     * @throws \app\common\exception\BaseException
     * @throws \think\Exception
     * @throws \think\exception\DbException
     */
    public function refund()
    {
        if (
            $this['order_type']['value'] != 20
            || $this['pay_status']['value'] != 20
            || $this['active']['status']['value'] != 30
            || $this['is_refund'] == 1
        ) {
            $this->error = '该订单不合法';
            return false;
        }
        // 执行退款操作
        (new RefundService)->execute($this);
        // 更新订单状态
        return $this->save(['order_status' => 20, 'is_refund' => 1]);
    }

    /**
     * 获取已付款订单总数 (可指定某天)
     * @param null $day
     * @return int|string
     * @throws \think\Exception
     */
    public function getPayOrderTotal($day = null)
    {
        $filter = ['pay_status' => 20];
        if (!is_null($day)) {
            $startTime = strtotime($day);
            $filter['pay_time'] = [
                ['>=', $startTime],
                ['<', $startTime + 86400],
            ];
        }
        return $this->getOrderTotal($filter);
    }

    /**
     * 获取订单总数量
     * @param array $filter
     * @return int|string
     * @throws \think\Exception
     */
    public function getOrderTotal($filter = [])
    {
        return $this->where($filter)
            ->where('is_delete', '=', 0)
            ->count();
    }

    /**
     * 获取某天的总销售额
     * @param $day
     * @return float|int
     */
    public function getOrderTotalPrice($day)
    {
        $startTime = strtotime($day);
        return $this->where('pay_time', '>=', $startTime)
            ->where('pay_time', '<', $startTime + 86400)
            ->where('pay_status', '=', 20)
            ->where('is_delete', '=', 0)
            ->sum('pay_price');
    }

    /**
     * 获取某天的下单用户数
     * @param $day
     * @return float|int
     */
    public function getPayOrderUserTotal($day)
    {
        $startTime = strtotime($day);
        $userIds = $this->distinct(true)
            ->where('pay_time', '>=', $startTime)
            ->where('pay_time', '<', $startTime + 86400)
            ->where('pay_status', '=', 20)
            ->where('is_delete', '=', 0)
            ->column('user_id');
        return count($userIds);
    }

}
