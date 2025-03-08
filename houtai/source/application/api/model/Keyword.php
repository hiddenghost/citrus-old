<?php

namespace app\api\model;

use app\common\exception\BaseException;
use app\common\model\Keyword as KeywordModel;

/**
 * 关键词模型
 * Class Keyword
 * @package app\api\model
 */
class Keyword extends KeywordModel
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
    public function add($name)
    {
        $detail = self::detail(['name' => $name]);
        if($detail) {
            $detail->save(['total' => $detail['total'] + 1]);
            return $detail['keyword_id'];
        }
        $data['name'] = $name;
        $data['wxapp_id'] = self::$wxapp_id;
        $data['total'] = 1;
        $data['create_time'] = time();

        return $this->allowField(true)->insertGetId($data);
    }

    /**
     * 获取列表
     * @param int $category_id
     * @param int $limit
     * @return \think\Paginator
     * @throws \think\exception\DbException
     */
    public function getList($limit = 15)
    {
        return $this->where('is_delete', '=', 0)
            ->order(['total' => 'desc', 'create_time' => 'desc'])
            ->paginate($limit, false, [
                'query' => \request()->request()
            ]);
    }

}