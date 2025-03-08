<?php

namespace app\common\model;

use think\Hook;
use app\common\model\BaseModel;
use app\common\service\Order as OrderService;
use app\common\enum\order\PayType as PayTypeEnum;
use app\common\enum\order\PayStatus as PayStatusEnum;
use app\common\library\helper;

/**
 * 局订单模型
 * Class GameOrder
 * @package app\common\model
 */
class GameOrder extends BaseModel
{
    protected $name = 'game_order';

    /**
     * 追加字段
     * @var array
     */
    protected $append = [
        'state_text',   // 售后单状态文字描述
    ];

    /**
     * 关联局表
     * @return \think\model\relation\BelongsTo
     */
    public function game()
    {
        return $this->belongsTo('Game');
    }


    /**
     * 关联自提门店表
     * @return \think\model\relation\BelongsTo
     */
    public function extractShop()
    {
        $module = self::getCalledModule() ?: 'common';
        return $this->belongsTo("app\\{$module}\\model\\store\\Shop", 'extract_shop_id');
    }

    /**
     * 关联门店店员表
     * @return \think\model\relation\BelongsTo
     */
    public function extractClerk()
    {
        $module = self::getCalledModule() ?: 'common';
        return $this->belongsTo("app\\{$module}\\model\\store\\shop\\Clerk", 'extract_clerk_id');
    }

    /**
     * 关联用户表
     * @return \think\model\relation\BelongsTo
     */
    public function user()
    {
        $module = self::getCalledModule() ?: 'common';
        return $this->belongsTo("app\\{$module}\\model\\User");
    }

    /**
     * 拼团订单状态文字描述
     * @param $value
     * @param $data
     * @return string
     */
    public function getStateTextAttr($value, $data)
    {
        if (!isset($data['active_status'])) {
            $data['active_status'] = '';
        }
        // 订单状态：已完成
        if ($data['order_status'] == 30) {
            return '已完成';
        }
        // 订单状态：已取消
        if ($data['order_status'] == 20) {
            // 拼单未成功
            if ($data['active_status'] == 30) {
                return $data['is_refund'] ? '组局未成功，已退款' : '组局未成功，待退款';
            }
            return '已取消';
        }
        // 付款状态
        if ($data['pay_status'] == 10) {
            return '待付款';
        }

        // 拼单未成功
        if ($data['active_status'] == 30) {
            return $data['is_refund'] ? '组局未成功，已退款' : '组局未成功，待退款';
        }
        // 拼单中
        if ($data['active_status'] == 10) {
            return '已付款，待组局';
        }
        // 拼单成功
        if ($data['active_status'] == 20) {
            return '组局成功，待参加';
        }
        return $value;
    }

    /**
     * 获取器：拼单状态
     * @param $value
     * @return array|bool
     */
    public function getActiveStatusAttr($value)
    {
        if (is_null($value)) {
            return false;
        }
        $state = [
            0 => '未组局',
            10 => '组局中',
            20 => '组局成功',
            30 => '组局失败',
        ];
        return ['text' => $state[$value], 'value' => $value];
    }

    /**
     * 获取器：订单金额(含优惠折扣)
     * @param $value
     * @param $data
     * @return string
     */
    public function getOrderPriceAttr($value, $data)
    {
        // 兼容旧数据：订单金额
        if ($value == 0) {
            return helper::bcadd(helper::bcsub($data['total_price'], $data['coupon_money']), $data['update_price']);
        }
        return $value;
    }

    /**
     * 改价金额（差价）
     * @param $value
     * @return array
     */
    public function getUpdatePriceAttr($value)
    {
        return [
            'symbol' => $value < 0 ? '-' : '+',
            'value' => sprintf('%.2f', abs($value))
        ];
    }

    /**
     * 订单类型
     * @param $value
     * @return array
     */
    public function getOrderTypeAttr($value)
    {
        $status = [10 => '免费', 20 => 'AA制'];
        return ['text' => $status[$value], 'value' => $value];
    }

    /**
     * 付款状态
     * @param $value
     * @return array
     */
    public function getPayTypeAttr($value)
    {
        return ['text' => PayTypeEnum::data()[$value]['name'], 'value' => $value];
    }

    /**
     * 付款状态
     * @param $value
     * @return array
     */
    public function getPayStatusAttr($value)
    {
        return ['text' => PayStatusEnum::data()[$value]['name'], 'value' => $value];
    }

    /**
     * 收货状态
     * @param $value
     * @return array
     */
    public function getReceiptStatusAttr($value)
    {
        $status = [10 => '待完成', 20 => '已完成'];
        return ['text' => $status[$value], 'value' => $value];
    }

    /**
     * 收货状态
     * @param $value
     * @return array
     */
    public function getOrderStatusAttr($value)
    {
        $status = [10 => '进行中', 20 => '已取消', 21 => '待取消', 30 => '已完成', 40 => '组局失败'];
        return ['text' => $status[$value], 'value' => $value];
    }

    /**
     * 生成订单号
     * @return string
     */
    public function orderNo()
    {
        return OrderService::createOrderNo();
    }

    /**
     * 订单详情
     * @param int|array $where
     * @param array $with
     * @return null|static
     * @throws \think\exception\DbException
     */
    public static function detail($where, $with = [
        'game',
        'extract',
        'extract_shop.logo',
        'extract_clerk'
    ])
    {
        is_array($where) ? $filter = $where : $filter['order_id'] = (int)$where;
        return self::get($filter, $with);
    }

    /**
     * 批量获取订单列表
     * @param $orderIds
     * @param array $with 关联查询
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getListByIds($orderIds, $with = [])
    {
        $data = $this->getListByInArray('order_id', $orderIds, $with);
        return helper::arrayColumn2Key($data, 'order_id');
    }

    /**
     * 批量获取订单列表
     * @param string $field
     * @param array $data
     * @param array $with
     * @return false|\PDOStatement|string|\think\Collection
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    private function getListByInArray($field, $data, $with = [])
    {
        return $this->with($with)
            ->alias('order')
            ->field('order.*, game.status as active_status')
            ->join('game', 'order.game_id = game.game_id', 'LEFT')
            ->where($field, 'in', $data)
            ->where('order.is_delete', '=', 0)
            ->select();
    }

    /**
     * 根据订单号批量查询
     * @param $orderNos
     * @param array $with
     * @return false|\PDOStatement|string|\think\Collection
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getListByOrderNos($orderNos, $with = [])
    {
        return $this->getListByInArray('order_no', $orderNos, $with);
    }

    /**
     * 批量更新订单
     * @param $orderIds
     * @param $data
     * @return false|int
     */
    public function onBatchUpdate($orderIds, $data)
    {
        return $this->isUpdate(true)->save($data, ['order_id' => ['in', $orderIds]]);
    }


}
