<?php

namespace app\api\model;

use app\common\model\GameComment as GameCommentModel;

/**
 * 局投诉模型
 * Class GameComment
 * @package app\api\model
 */
class GameComment extends GameCommentModel
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
    public function add($gameId, $user_id, $content, $image_id)
    {
        return $this->transaction(function () use ($gameId, $user_id, $content, $image_id) {
            $data = [
                'game_id' => $gameId,
                'user_id' => $user_id,
                'content' => $content,
                'image_id' => $image_id,
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
            $this->where('game_id', 'IN', $gameIds);
        }
        return $this->with(['game'])
            ->where('user_id', '=', $user_id)
            ->where('is_delete', '=', 0)
            ->order(['create_time' => 'desc'])
            ->paginate(15, false, [
                'query' => \request()->request()
            ]);
    }

}
