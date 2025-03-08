<?php

namespace app\common\model\user;

use app\common\model\BaseModel;

/**
 * 用户邮件模型
 * Class UserEmail
 * @package app\common\model\sharing
 */
class Email extends BaseModel
{
    protected $name = 'user_email';
    protected $updateTime = false;

    /**
     * 获取详情信息
     * @param $where
     * @param $with
     * @return null|static
     * @throws \think\exception\DbException
     */
    public static function detail($where)
    {
        $filter = ['is_delete' => 0];
        if (is_array($where)) {
            $filter = array_merge($filter, $where);
        } else {
            $filter['user_id'] = (int)$where;
        }
        return static::get($filter);
    }

}