<?php

namespace app\common\model;

/**
 * 局标签模型
 * Class GameTags
 * @package app\common\model
 */
class GameTags extends BaseModel
{
    protected $name = 'game_tags';

    /**
     * 关联商品分类表
     * @return \think\model\relation\BelongsTo
     */
    public function tags()
    {
        return $this->belongsTo('Tags');
    }

    /**
     * 获取tag信息详情
     * @param $gameId
     * @param $tagId
     * @return GoodsSku|null
     * @throws \think\exception\DbException
     */
    public static function detail($gameId, $tagId)
    {
        return static::get(['game_id' => $gameId, 'tags_id' => $tagId]);
    }

}
