<?php

namespace app\store\model;
use app\common\model\Keyword as KeywordModel;
use think\Model;


/**
 * 标签模型
 * Class Keyword
 * @package app\store\model
 */
class Keyword extends KeywordModel
{
    /**
     * 获取优惠券列表
     * @return \think\Paginator
     * @throws \think\exception\DbException
     */
    public function getList($keyword = '')
    {
        $keyword && $this->where('name', 'like', '%' . $keyword . '%');
        return $this->where('is_delete', '=', 0)
            ->order(['total' => 'desc', 'keyword_id' => 'asc'])
            ->paginate(15, false, [
                'query' => request()->request()
            ]);
    }

    /**
     * 添加新记录
     * @param $data
     * @return false|int
     */
    public function add($data)
    {
        $data['wxapp_id'] = self::$wxapp_id;
        $data['create_time'] = time();
        return $this->allowField(true)->insertGetId($data);
    }

    /**
     * 更新记录
     * @param $data
     * @return bool|int
     */
    public function edit($data)
    {
        return $this->allowField(true)->save($data) !== false;
    }

    /**
     * 删除记录 (软删除)
     * @return bool|int
     */
    public function setDelete()
    {
        return $this->save(['is_delete' => 1]) !== false;
    }

}
