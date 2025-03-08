<?php

namespace app\api\model\im;

use app\common\model\im\ChatFile as ChatFileModel;

/**
 * 关系模型
 * Class ChatFile
 * @package app\api\model\im
 */
class ChatFile extends ChatFileModel
{
    /**
     * 隐藏字段
     * @var array
     */
    protected $hidden = [
        'update_time',
        'wxapp_id'
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
        $data['is_read'] = 0;
        $data['status'] = 1;

        return $this->allowField(true)->save($data);
    }

}