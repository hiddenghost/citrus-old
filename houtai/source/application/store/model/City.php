<?php

namespace app\store\model;

use think\Cache;
use app\common\model\City as CityModel;
use app\common\model\Game as GameModel;

/**
 * 地区模型
 * Class City
 * @package app\store\model
 */
class City extends CityModel
{
    /**
     * 添加新记录
     * @param $data
     * @return false|int
     */
    public function add($data)
    {
        $data['wxapp_id'] = self::$wxapp_id;
        if($data['pinyin']) {
            $data['first'] = strtoupper(substr($data['pinyin'], 0,1));
        }
        $this->deleteCache();
        return $this->allowField(true)->save($data);
    }

    /**
     * 编辑记录
     * @param $data
     * @return bool|int
     */
    public function edit($data)
    {
        // 验证：一级分类如果存在子类，则不允许移动
        if ($data['pid'] > 0 && static::hasSubCategory($this['id'])) {
            $this->error = '该分类下存在子分类，不可以移动';
            return false;
        }
        if($data['pinyin']) {
            $data['first'] = strtoupper(substr($data['pinyin'], 0,1));
        }
        $this->deleteCache();
        return $this->allowField(true)->save($data) !== false;
    }

    /**
     * 删除商品分类
     * @param $cityId
     * @return bool|int
     */
    public function remove($cityId)
    {
        // 判断是否存在子分类
        if (static::hasSubCategory($cityId)) {
            $this->error = '该分类下存在子分类，请先删除';
            return false;
        }
        $this->deleteCache();
        return $this->delete();
    }

    /**
     * 删除缓存
     * @return bool
     */
    private function deleteCache()
    {
        return Cache::rm('city_' . static::$wxapp_id);
    }
}
