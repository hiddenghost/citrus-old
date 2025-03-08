<?php

namespace app\api\model;

use app\common\model\GameCollect as GameCollectModel;

/**
 * 局图片模型
 * Class GoodsImage
 * @package app\api\model
 */
class GameCollect extends GameCollectModel
{
    /**
     * 隐藏字段
     * @var array
     */
    protected $hidden = [
        'wxapp_id',
    ];

    /**
     * 添加局图片
     * @param Order $order
     * @param \think\Collection|OrderGoods $goodsList
     * @param $formJsonData
     * @return boolean
     * @throws \Exception
     */
    public function add($gameId, $user_id)
    {
        return $this->transaction(function () use ($gameId, $user_id) {
            $data = [
                'game_id' => $gameId,
                'user_id' => $user_id,
                'wxapp_id' =>self::$wxapp_id,
                'create_time'=> time()
            ];
            $this->isUpdate(false)->save($data);

            return true;
        });
    }

    /**
     * 软删除
     * @return false|int
     */
    public function setDelete()
    {
        return $this->allowField(true)->save(['is_delete' => 1]);
    }

    /**
     * 得到收藏的局的列表
     * @param $user_id
     */
    public function getList($user_id, $gameIds = [])
    {
        if(count($gameIds) > 0) {
            $this->where('collect.game_id', 'IN', $gameIds);
        }
        //去掉已过期的局
        $curDay = date('Y-m-d', time());
        $curTime = date('H:i', time());
        $this->where(function ($query) use ($curDay, $curTime) {
            $query->whereOr(function ($query2) use ($curDay, $curTime) {
                $query2->where('game.open_date', '=', $curDay)
                    ->where('game.end_time', '>', $curTime);
            });
            $query->whereOr(function ($query2) use ($curDay, $curTime) {
                $query2->where('game.open_date', '>', $curDay);
            });
        });
        return $this->alias('collect')
            ->field(['collect.*'])
            ->join('game', 'collect.game_id = game.game_id')
            ->where('collect.user_id', '=', $user_id)
            ->where('collect.is_delete', '=', 0)
            ->order(['game.open_date' => 'asc', 'game.start_time' => 'asc'])
            ->paginate(15, false, [
                'query' => \request()->request()
            ]);
    }

}
