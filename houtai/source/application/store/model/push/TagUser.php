<?php

namespace app\store\model\push;

use app\common\model\push\TagUser as TagUserModel;
use app\common\service\Push as Push;

/**
 * 标签用户模型
 * Class TagUser
 * @package app\store\model\store
 */
class TagUser extends TagUserModel
{

    /**
     * 根据标签得到用户列表
     * @param $push_tag_id
     */
    public static function getListByTag($push_tag_id)
    {
        $self = new static;

        return $self->with(['user'])
            ->where('push_tag_id', '=', $push_tag_id)
            ->where('is_delete', '=', 0)
            ->select();
    }



    /**
     * 批量新增标签用户
     * @param $data
     * @return bool
     * @throws \Exception
     */
    public function addlist($name, $list)
    {
        $res = (new Push)->addPushLabelUser($name, $list);
        if($res['rs']) {
            return $res['rs'];
        }
        $this->error = $res['message'];
        return false;
    }

    /**
     * 批量删除标签用户
     * @param $data
     * @return bool
     * @throws \Exception
     */
    public function deletelist($name, $list)
    {
        $res = (new Push)->deletePushLabelUsers($name, $list);
        if($res['rs']) {
            return $res['rs'];
        }
        $this->error = $res['message'];
        return false;
    }

}