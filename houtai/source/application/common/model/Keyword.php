<?php

namespace app\common\model;

/**
 * 关键词模型
 * Class Dict
 * @package app\common\model
 */
class Keyword extends BaseModel
{
    protected $name = 'keyword';

    /**
     * 详情
     * @param $keyword_id
     * @return null|static
     * @throws \think\exception\DbException
     */
    public static function detail($keyword_id)
    {
        return self::get($keyword_id);
    }

}
