<?php

namespace app\common\model;

/**
 * 局图片模型
 * Class GameImage
 * @package app\common\model
 */
class GameImage extends BaseModel
{
    protected $name = 'game_image';
    protected $updateTime = false;

    /**
     * 得到局的封面图
     * @param $game_id
     */
    public function getGameImage($game_id)
    {
        $list = $this->where('game_id', '=', $game_id)
            ->order(['id' => 'asc'])
            ->select()->toArray();
        if(count($list) > 0) {
            return $list[0];
        }else{
            return null;
        }
    }

}
