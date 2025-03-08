<?php

namespace app\common\model;

use app\common\model\user\PointsLog as PointsLogModel;
use app\common\model\City as CityModel;

/**
 * 用户模型类
 * Class User
 * @package app\common\model
 */
class User extends BaseModel
{
    protected $name = 'user';

    protected $append = ['region'];

    // 性别
    private $gender = ['未知', '男', '女'];

    /**
     * 地区名称
     * @param $value
     * @param $data
     * @return array
     */
    public function getRegionAttr($value, $data)
    {
        $country = CityModel::get($data['country']);
        $province = CityModel::get($data['province']);
        $city = CityModel::get($data['city']);

        return [
            'country' => $country ? $country['name'] : '',
            'province' => $province ? $province['name'] : '',
            'city' => $city ? $city['name'] : '',
        ];
    }

    /**
     * 关联会员等级表
     * @return \think\model\relation\BelongsTo
     */
    public function grade()
    {
        $module = self::getCalledModule() ?: 'common';
        return $this->belongsTo("app\\{$module}\\model\\user\\Grade");
    }

    /**
     * 关联收货地址表
     * @return \think\model\relation\HasMany
     */
    public function address()
    {
        return $this->hasMany('UserAddress');
    }

    /**
     * 关联照片
     * @return \think\model\relation\HasMany
     */
    public function images()
    {
        $module = self::getCalledModule() ?: 'common';
        return $this->hasMany("app\\{$module}\\model\\user\\Images")->where('is_delete', '=', 0)->order(['category_id' => 'asc']);
    }

    /**
     * 关联认证信息
     * @return \think\model\relation\HasMany
     */
    public function idcardData()
    {
        $module = self::getCalledModule() ?: 'common';
        return $this->hasMany("app\\{$module}\\model\\user\\Idcard")->where('is_delete', '=', 0);
    }

    /**
     * 关联认证信息
     * @return \think\model\relation\HasMany
     */
    public function schoolData()
    {
        $module = self::getCalledModule() ?: 'common';
        return $this->hasMany("app\\{$module}\\model\\user\\School")->where('is_delete', '=', 0);
    }

    /**
     * 关联label信息
     * @return \think\model\relation\HasMany
     */
    public function labelData()
    {
        $module = self::getCalledModule() ?: 'common';
        return $this->hasMany("app\\{$module}\\model\\user\\Label")->where('is_delete', '=', 0);
    }

    /**
     * 关联Im信息
     * @return \think\model\relation\HasMany
     */
    public function imuser()
    {
        $module = self::getCalledModule() ?: 'common';
        return $this->hasMany("app\\{$module}\\model\\im\\User")->where('is_delete', '=', 0);
    }

    /**
     * 关联收货地址表 (默认地址)
     * @return \think\model\relation\BelongsTo
     */
    public function addressDefault()
    {
        return $this->belongsTo('UserAddress', 'address_id');
    }

    /**
     * 显示性别
     * @param $value
     * @return mixed
     */
    public function getGenderAttr($value)
    {
        return $this->gender[$value];
    }

    /**
     * 显示性别
     * @param $value
     * @return mixed
     */
    public function getRegisterTypeAttr($value)
    {
        $map = [
            '1' => '邮箱注册',
            '2' => '手机号码注册',
            '3' => '苹果快捷登录',
            '4' => '微信快捷登录'
        ];
        return [
            'text' => $map[$value],
            'value' => $value
        ];
    }

    /**
     * 获取用户信息
     * @param $where
     * @param $with
     * @return null|static
     * @throws \think\exception\DbException
     */
    public static function detail($where, $with = ['address', 'images', 'grade' => ['image'], 'idcardData', 'schoolData', 'imuser', 'labelData' => ['label']])
    {
        $filter = ['is_delete' => 0];
        if (is_array($where)) {
            $filter = array_merge($filter, $where);
        } else {
            $filter['user_id'] = (int)$where;
        }
        return static::get($filter, $with);
    }

    /**
     * 累积用户的实际消费金额
     * @param $userId
     * @param $expendMoney
     * @return int|true
     * @throws \think\Exception
     */
    public function setIncUserExpend($userId, $expendMoney)
    {
        return $this->where(['user_id' => $userId])->setInc('expend_money', $expendMoney);
    }

    /**
     * 指定会员等级下是否存在用户
     * @param $gradeId
     * @return bool
     */
    public static function checkExistByGradeId($gradeId)
    {
        $model = new static;
        return !!$model->where('grade_id', '=', (int)$gradeId)->value('user_id');
    }

    /**
     * 累积用户总消费金额
     * @param $money
     * @return int|true
     * @throws \think\Exception
     */
    public function setIncPayMoney($money)
    {
        return $this->setInc('pay_money', $money);
    }

    /**
     * 累积用户实际消费的金额 (批量)
     * @param $data
     * @return array|false
     * @throws \Exception
     */
    public function onBatchIncExpendMoney($data)
    {
        foreach ($data as $userId => $expendMoney) {
            $this->where(['user_id' => $userId])->setInc('expend_money', $expendMoney);
        }
        return true;
    }

    /**
     * 累积用户的可用积分数量 (批量)
     * @param $data
     * @return array|false
     * @throws \Exception
     */
    public function onBatchIncPoints($data)
    {
        foreach ($data as $userId => $expendMoney) {
            $this->where(['user_id' => $userId])->setInc('points', $expendMoney);
        }
        return true;
    }

    /**
     * 累积用户的可用积分
     * @param $points
     * @param $describe
     * @return int|true
     * @throws \think\Exception
     */
    public function setIncPoints($points, $describe)
    {
        // 新增积分变动明细
        PointsLogModel::add([
            'user_id' => $this['user_id'],
            'value' => $points,
            'describe' => $describe,
        ]);
        // 更新用户可用积分
        return $this->setInc('points', $points);
    }

}
