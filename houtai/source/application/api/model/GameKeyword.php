<?php

namespace app\api\model;

use app\common\model\GameKeyword as GameKeywordModel;

/**
 * 局关键词模型
 * Class GameKeyword
 * @package app\api\model
 */
class GameKeyword extends GameKeywordModel
{
    /**
     * 隐藏字段
     * @var array
     */
    protected $hidden = [
        'wxapp_id',
        'create_time',
        'update_time'
    ];

    /**
     * 添加新记录
     * @param $data
     * @return false|int
     */
    public function add($user_id, $keyword_id)
    {
        $detail = self::detail($user_id, $keyword_id);
        if($detail) {
            return $detail->save(['total' => $detail['total'] + 1]);
        }
        $data['user_id'] = $user_id;
        $data['keyword_id'] = $keyword_id;
        $data['wxapp_id'] = self::$wxapp_id;
        $data['total'] = 1;
        $data['create_time'] = time();
        return $this->allowField(true)->insertGetId($data);
    }

}