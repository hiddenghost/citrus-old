<?php

namespace app\api\model;

use app\common\model\GameUsers as GameUsersModel;
use app\common\model\Game as GameModel;

/**
 * 局用户模型
 * Class GoodsImage
 * @package app\api\model
 */
class GameUsers extends GameUsersModel
{
    /**
     * 隐藏字段
     * @var array
     */
    protected $hidden = [
        'wxapp_id',
        'create_time',
    ];

    /**
     * 得到已入局的局成员列表
     */
    public function getList($game_id, $limit = 15)
    {
        return $this->with(['user'])
            ->where('game_id', '=', $game_id)
            ->where('is_delete', '=', 0)
            ->order(['is_creator' => 'desc', 'create_time' => 'desc'])
            ->paginate($limit, false, [
                'query' => \request()->request()
            ]);
    }

    /**
     * 得到已入局的所有局成员列表
     */
    public function getAllList($game_id)
    {
        return $this->with(['user'])
            ->where('game_id', '=', $game_id)
            ->where('is_delete', '=', 0)
            ->order(['is_creator' => 'desc', 'create_time' => 'desc'])
            ->select()->toArray();
    }

    /**
     * 新增记录
     * @param $data
     * @return bool
     * @throws \Exception
     */
    public function add($data)
    {
        $data['wxapp_id'] = self::$wxapp_id;

        return $this->allowField(true)->save($data);
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
     * 得到两个人相同的局数
     * @param $user_id
     * @param $rel_user_id
     */
    public static function getCommonGame($user_id, $rel_user_id)
    {
        return self::field(['game_id'])
            ->where('user_id', 'IN', [$user_id, $rel_user_id])
            ->where('is_delete', '=', 0)
            ->group('game_id')
            ->count();
    }

    /**
     * 得到两个人相同的已结束的次局数
     * @param $user_id
     * @param $rel_user_id
     */
    public function getCiGame($user_id, $rel_user_id, $isCount = false)
    {
        $list = $this->getCreateAndJoinGame($user_id, false);
        $list2 = $this->getCreateAndJoinGame($rel_user_id, false);
        $res = [];
        foreach($list as $key => $item) {
            foreach($list2 as $key2 => $item2) {
               if($item['game_id'] == $item2['game_id']) {
                   $res[] = $item;
               }
            }
        }
        if($isCount) {
            return count($res);
        }

        return $res;
    }

    /**
     * 得到自己创建的参加的已结束的局数
     * @param $user_id
     */
    public function getCreateAndJoinGame($user_id, $isCount = false)
    {
        //加上已完成的局
        $curDay = date('Y-m-d', time());
        $curTime = date('H:i', time());
        $this->where(function ($query) use ($curDay, $curTime) {
            $query->whereOr(function ($query2) use ($curDay, $curTime) {
                $query2->where('game.open_date', '=', $curDay)
                    ->where('game.end_time', '<', $curTime);
            });
            $query->whereOr(function ($query2) use ($curDay, $curTime) {
                $query2->where('game.open_date', '<', $curDay);
            });
        });
        $list = $this->alias('user')
            ->field(['game.*'])
            ->join('game', 'user.game_id = game.game_id')
            ->where('user.user_id', '=', $user_id)
            ->where('user.is_delete', '=', 0)
            ->where('game.is_delete', '=', 0)
            ->group('game.game_id')
            ->select()->toArray();
        $res = [];
        foreach($list as $key => $item) {
            $game = GameModel::detail($item['game_id']);
            if(count($game['users']) > 1) {
                $res[] = $item;
            }
        }
        if($isCount) {
            return count($res);
        }

        return $res;
    }

}