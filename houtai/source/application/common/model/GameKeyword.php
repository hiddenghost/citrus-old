<?php

namespace app\common\model;

/**
 * 局热搜关键词模型
 * Class GameKeyword
 * @package app\common\model
 */
class GameKeyword extends BaseModel
{
    protected $name = 'game_keyword';

    /**
     * 关联商品分类表
     * @return \think\model\relation\BelongsTo
     */
    public function keyword()
    {
        return $this->belongsTo('Keyword');
    }

    /**
     * 获取tag信息详情
     * @param $gameId
     * @param $keywordId
     * @return GoodsSku|null
     * @throws \think\exception\DbException
     */
    public static function detail($userId, $keywordId)
    {
        $filter = ['keyword_id' => $keywordId, 'user_id' => $userId, 'is_delete' => 0];

        return static::get($filter);
    }

}
