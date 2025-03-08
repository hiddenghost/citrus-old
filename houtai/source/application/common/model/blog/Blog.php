<?php

namespace app\common\model\blog;

use app\common\model\BaseModel;

/**
 * Blog模型
 * Class Blog
 * @package app\common\model
 */
class Blog extends BaseModel
{
    protected $name = 'blog';

    /**
     * 追加字段
     * @var array
     */
    protected $append = [
        'publish_month',   // 发布的月份
        'publish_day',   // 发布的日期
    ];

    /**
     * 发布的月份
     * @param $value
     * @param $data
     * @return string
     */
    public function getPublishMonthAttr($value, $data)
    {
        $month = date('m', $data['create_time']);
        $month = $month < 10 ? '0' . $month : $month;

        return $month . '月';
    }

    /**
     * 发布的日期
     * @param $value
     * @param $data
     * @return string
     */
    public function getPublishDayAttr($value, $data)
    {
        $day = date('d', $data['create_time']);

        return $day < 10 ? '0' . $day : $day;
    }

    /**
     * 关联用户表
     * @return \think\model\relation\BelongsTo
     */
    public function user()
    {
        $module = self::getCalledModule() ?: 'common';
        return $this->belongsTo("app\\{$module}\\model\\User");
    }

    /**
     * 关联分类
     * @return \think\model\relation\HasOne
     */
    public function category()
    {
        $module = self::getCalledModule() ?: 'common';
        return $this->hasOne("app\\{$module}\\model\\DictData", 'data_id', 'data_id');
    }

    /**
     * 关联评论信息
     * @return \think\model\relation\HasMany
     */
    public function commentList()
    {
        $module = self::getCalledModule() ?: 'common';
        return $this->hasMany("app\\{$module}\\model\\blog\\Comment")->order(['blog_comment_id' => 'desc'])->where('is_delete', '=', 0);
    }

    /**
     * 获取Im信息详情
     * @param $blog_id
     * @return GameUsers|null
     * @throws \think\exception\DbException
     */
    public static function detail($blog_id)
    {
        return static::get($blog_id, ['user', 'category', 'commentList' => ['user']]);
    }

}
