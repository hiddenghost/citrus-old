<?php

namespace app\api\model;

use app\common\exception\BaseException;
use app\common\model\GameActive as ActiveModel;

/**
 * 拼团拼单模型
 * Class Active
 * @package app\api\model\sharing
 */
class GameActive extends ActiveModel
{
    /**
     * 隐藏字段
     * @var array
     */
    protected $hidden = [
        'wxapp_id',
        'create_time',
        'update_time'
    ];

    /**
     * 新增拼单记录
     * @param $data
     * @return false|int
     */
    public function add($data)
    {
        return $this->save($data);
    }

    /**
     * 根据商品id获取进行中的拼单列表
     * @param $goods_id
     * @param int $limit
     * @return false|\PDOStatement|string|\think\Collection
     */
    public static function getActivityListByGoods($game_id, $limit = 15)
    {
        return (new static)->with(['user'])
            ->where('game_id', '=', $game_id)
            ->where('status', '=', 10)
            ->limit($limit)
            ->select();
    }

}
