<?php

namespace app\api\model;

use app\common\model\GameTags as GameTagsModel;

/**
 * 局标签模型
 * Class GameTags
 * @package app\api\model
 */
class GameTags extends GameTagsModel
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
     * 添加局标签
     * @param Order $order
     * @param \think\Collection|OrderGoods $goodsList
     * @param $formJsonData
     * @return boolean
     * @throws \Exception
     */
    public function addForGame($gameId, $tagsList)
    {
        return $this->transaction(function () use ($gameId, $tagsList) {
            $data = [];
            foreach($tagsList as $key => $item) {
                $data[] = [
                    'game_id' => $gameId,
                    'tags_id' => $item,
                    'wxapp_id' =>self::$wxapp_id,
                    'create_time'=> time()
                ];
            }
            $this->isUpdate(false)->saveAll($data);

            return true;
        });
    }

    /**
     * 修改局标签
     * @param Order $order
     * @param \think\Collection|OrderGoods $goodsList
     * @param $formJsonData
     * @return boolean
     * @throws \Exception
     */
    public function editForGame($game, $tagsList)
    {
        return $this->transaction(function () use ($game, $tagsList) {
            $data = [];
            $delTagsList = [];
            foreach($game['tags'] as $key => $item) {
                //如果没在新的标签里面则删除
                if(!in_array($item['tags_id'], $tagsList)) {
                    $delTagsList[] = ['data' => ['is_delete' => 1], 'where' => ['game_tag_id' => $item['game_tag_id']]];
                }
            }
            $this->updateAll($delTagsList);
            $oldTagsList = array_column($game['tags'], 'tags_id');
            foreach($tagsList as $key => $item) {
                if(!in_array($item, $oldTagsList)) {
                    $data[] = [
                        'game_id' => $game['game_id'],
                        'tags_id' => $item,
                        'wxapp_id' =>self::$wxapp_id,
                        'create_time'=> time()
                    ];
                }
            }
            $this->isUpdate(false)->saveAll($data);

            return true;
        });
    }

}
