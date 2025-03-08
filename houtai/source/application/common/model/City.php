<?php

namespace app\common\model;

use think\Cache;
use app\common\library\helper;

/**
 * 地区模型
 * Class Region
 * @package app\common\model
 */
class City extends BaseModel
{
    protected $name = 'city';
    protected $createTime = false;
    protected $updateTime = false;

    /**
     * 类型自动转换
     * @var array
     */
    protected $type = [
        'id' => 'integer',
        'pid' => 'integer',
        'level' => 'integer',
    ];

    /**
     * 所有分类
     * @return mixed
     */
    public static function getALL()
    {
        $model = new static;
        if (!Cache::get('city_' . $model::$wxapp_id)) {
            $data = $model->order(['create_time' => 'asc'])->select();
            $all = !empty($data) ? $data->toArray() : [];
            $tree = [];
            foreach ($all as $first) {
                if ($first['pid'] != 0) continue;
                $twoTree = [];
                foreach ($all as $two) {
                    if ($two['pid'] != $first['id']) continue;
                    $threeTree = [];
                    foreach ($all as $three)
                        $three['pid'] == $two['id']
                        && $threeTree[$three['id']] = $three;
                    !empty($threeTree) && $two['child'] = $threeTree;
                    $twoTree[$two['id']] = $two;
                }
                if (!empty($twoTree)) {
                    array_multisort(array_column($twoTree, 'id'), SORT_ASC, $twoTree);
                    $first['child'] = $twoTree;
                }
                $tree[$first['id']] = $first;
            }
            Cache::tag('cache')->set('city_' . $model::$wxapp_id, compact('all', 'tree'));
        }
        return Cache::get('city_' . $model::$wxapp_id);
    }

    /**
     * 获取所有分类
     * @return mixed
     */
    public static function getCacheAll()
    {
        return self::getALL()['all'];
    }

    /**
     * 获取所有分类(树状结构)
     * @return mixed
     */
    public static function getCacheTree()
    {
        return self::getALL()['tree'];
    }

    /**
     * 获取所有分类(树状结构)
     * @return string
     */
    public static function getCacheTreeJson()
    {
        return json_encode(static::getCacheTree());
    }

    /**
     * 获取指定分类下的所有子分类id
     * @param $parent_id
     * @param array $all
     * @return array
     */
    public static function getSubCategoryId($parent_id, $all = [])
    {
        $arrIds = [$parent_id];
        empty($all) && $all = self::getCacheAll();
        foreach ($all as $key => $item) {
            if ($item['pid'] == $parent_id) {
                unset($all[$key]);
                $subIds = self::getSubCategoryId($item['id'], $all);
                !empty($subIds) && $arrIds = array_merge($arrIds, $subIds);
            }
        }
        return $arrIds;
    }

    /**
     * 指定的分类下是否存在子分类
     * @param $parentId
     * @return bool
     */
    protected static function hasSubCategory($parentId)
    {
        $all = self::getCacheAll();
        foreach ($all as $item) {
            if ($item['pid'] == $parentId) {
                return true;
            }
        }
        return false;
    }

    /**
     * 从数据库中获取所有地区
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    private function getAllList()
    {
        $list = self::useGlobalScope(false)
            ->field('id, pid, name, level')
            ->select()
            ->toArray();
        return helper::arrayColumn2Key($list, 'id');
    }

    /**
     * 获取用户信息
     * @param $where
     * @param $with
     * @return null|static
     * @throws \think\exception\DbException
     */
    public static function detail($name)
    {
        if(intval($name)) {
            return static::get(intval($name));
        }
        $filter['name'] = $name;
        $filter['level'] = 3;
        $detail = static::get($filter);
        if($detail) {
            return $detail;
        }
        $filter['shortname'] = $name;
        $filter['level'] = 3;
        $detail = static::get($filter);
        return $detail;
    }

    /**
     * 得到某个级别下的所有数据
     * @param $level
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getListByLevel($level = 3)
    {
        $list = self::useGlobalScope(false)
            ->field('id, pid, name, level')
            ->where('level', $level)
            ->select()
            ->toArray();

        return $list;
    }

}
