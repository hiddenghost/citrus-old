<?php

namespace app\store\model\push;

use app\common\model\push\Log as LogModel;
use app\common\service\Push as Push;

/**
 * 标签用户模型
 * Class Log
 * @package app\store\model\store
 */
class Log extends LogModel
{
    /**
     * 获取列表数据
     * @param null $status
     * @return \think\Paginator
     * @throws \think\exception\DbException
     */
    public function getList($status = null)
    {
        !is_null($status) && $this->where('status', '=', (int)$status);
        return $this->with(['user', 'task'])
            ->where('is_delete', '=', '0')
            ->order(['create_time' => 'desc'])
            ->paginate(15, false, [
                'query' => \request()->request()
            ]);
    }
}