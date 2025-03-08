<?php

namespace app\api\model\im;

use app\common\model\im\Group as GroupModel;
use app\api\model\im\GroupUser as GroupUserModel;
use app\api\model\Game as GameModel;
use app\api\model\GameImage as GameImageModel;

/**
 * 群组模型
 * Class Group
 * @package app\api\model\dealer
 */
class Group extends GroupModel
{
    /**
     * 隐藏字段
     * @var array
     */
    protected $hidden = [
        'update_time',
    ];

    /**
     * 新增记录
     * @param $data
     * @return bool
     * @throws \Exception
     */
    public function add($data)
    {
        $data['wxapp_id'] = self::$wxapp_id;
        $data['status'] = 1;

        return $this->allowField(true)->save($data);
    }

    /**
     * 获取用户所有的群组
     * @return \think\Paginator
     * @throws \think\exception\DbException
     */
    public function getAll($user_id)
    {
        $groupUser = new GroupUserModel;
        $userList = $groupUser->getAll($user_id);
        $gameImage = new GameImageModel;

        $list = $this->with(['game'])
            ->where('im_group_id', 'IN', array_column($userList, 'im_group_id'))
            ->where('is_delete', '=', 0)
            ->select()->toArray();
        foreach($list as $key => &$item) {
            $item['join_peoples'] = $groupUser->getUserNumsByGroup($item['im_group_id']);
            $item['image'] = $gameImage->getGameImage($item['game_id']);
        }

        return $list;
    }

    /**
     * 获取我的群组列表
     * @return \think\Paginator
     * @throws \think\exception\DbException
     */
    public function getList($user_id, $keyword, $limit = 30)
    {
        $groupUser = new GroupUserModel;
        $userList = $groupUser->getAll($user_id);
        if(isset($keyword) && $keyword) {
            $this->where('group.name', 'like', '%' . $keyword . '%');
        }
        //局没有结束或者局已经结束但是创建周期未超过一周
        $curDay = date('Y-m-d', time());
        $curTime = date('H:i', time());
        $this->where(function ($query) use ($curDay, $curTime) {
            $query->whereOr(function ($query2) use ($curDay, $curTime) {
                $query2->whereOr(function ($query3) use ($curDay, $curTime) {
                    $query3->where('game.open_date', '=', $curDay)
                        ->where('game.end_time', '>', $curTime);
                });
                $query2->whereOr(function ($query3) use ($curDay, $curTime) {
                    $query3->where('game.open_date', '>', $curDay);
                });
            });
            $query->whereOr(function ($query2) use ($curDay, $curTime) {
                $query2->whereOr(function ($query3) use ($curDay, $curTime) {
                    $query3->where('game.open_date', '=', $curDay)
                        ->where('game.end_time', '<', $curTime)
                        ->where('game.create_time', '>', time() - 3600*24*7);
                });
                $query2->whereOr(function ($query3) use ($curDay, $curTime) {
                    $query3->where('game.open_date', '<', $curDay)
                        ->where('game.create_time', '>', time() - 3600*24*7);
                });
            });

        });
        return $this->alias('group')
            ->field(['group.*'])
            ->join('game', 'group.game_id = game.game_id')
            ->where('group.im_group_id', 'IN', array_column($userList, 'im_group_id'))
            ->where('group.is_delete', '=', 0)

            ->order(['group.create_time' => 'desc'])
            ->paginate($limit, false, [
                'query' => request()->request()
            ]);
    }

}