<?php

namespace app\api\model;

use app\common\model\GameApply as GameApplyModel;


/**
 * 局模型
 * Class GameApply
 * @package app\api\model
 */
class GameApply extends GameApplyModel
{
    /**
     * 隐藏字段
     * @var array
     */
    protected $hidden = [
        'is_delete',
        'wxapp_id',
        'update_time'
    ];

    /**
     * 添加新记录
     * @param $data
     * @return false|int
     */
    public function add($data)
    {
        $data['wxapp_id'] = self::$wxapp_id;
        $data['create_time'] = time();
        $data['order_no'] = date('YmdHis', time()) . rand(10000, 99999);

        return $this->allowField(true)->save($data);;
    }

    /**
     *得到用户所有加入分页列表 包括待审核
     * @param $user_id
     */
    public function getJoinList($user_id, $gameIds = [])
    {
        if(count($gameIds) > 0) {
            $this->where('apply.game_id', 'IN', $gameIds);
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
        return $this->alias('apply')
            ->field(['apply.*'])
            ->join('game', 'apply.game_id = game.game_id')
            ->where('apply.user_id', '=', $user_id)
            ->where('apply.is_delete', '=', 0)
            ->where('apply.apply_status', '<>', 3)
            ->order(['game.open_date' => 'asc', 'game.start_time' => 'asc'])
            ->paginate(15, false, [
                'query' => \request()->request()
            ]);
    }

    /**
     *得到对应用户所有加入列表
     * @param $user_id
     */
    public function getJoinAll($userList)
    {
        return $this->where('user_id', 'IN', $userList)
            ->where('is_delete', '=', 0)
            ->where('apply_status', '=', 1)
            ->order(['create_time' => 'desc'])
            ->select()->toArray();
    }

    /**
     * 得到待审核的入局的列表
     * @param $user_id
     */
    public function getList($game_id, $limit = 15)
    {
        return $this->with(['user'])
            ->where('game_id', '=', $game_id)
            ->where('apply_status', '=', 0)
            ->where('is_delete', '=', 0)
            ->order(['create_time' => 'desc'])
            ->paginate($limit, false, [
                'query' => \request()->request()
            ]);
    }

    /**
     * 得到入局数量
     * @param $game_id
     * @param int $apply_status
     */
    public function getCount($game_id, $apply_status = 0)
    {
        return $this->where('game_id', '=', $game_id)
            ->where('apply_status', '=', $apply_status)
            ->where('is_delete', '=', 0)
            ->count();
    }

}
