<?php

namespace app\api\model;

use app\common\model\Label as LabelModel;
use app\common\model\DictData as DictDataModel;
use app\common\model\user\Label as UserLabelModel;
/**
 * 标签模型
 * Class Label
 * @package app\common\model
 */
class Label extends LabelModel
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
     * 获取所有的标签列表
     * @param int $category_id
     * @param int $limit
     * @return \think\Paginator
     * @throws \think\exception\DbException
     */
    public function getList($user_id)
    {
        $categoryList = DictDataModel::getListByDict('user-label');
        foreach($categoryList as $key => &$item) {
            $lebelList = $this->where('data_id', '=', $item['data_id'])
                ->where('is_delete', '=', 0)
                ->order(['sort' => 'asc', 'create_time' => 'desc'])
                ->select();
            foreach($lebelList as $labelkey => $labelItem) {
                $labelItem['is_select'] = false;
                $detail = UserLabelModel::detail(['user_id' => $user_id, 'label_id' => $labelItem['label_id'], 'status' => 1, 'is_delete' => 0]);
                if($detail) {
                    $labelItem['is_select'] = true;
                }
            }
            $item['labelList'] = $lebelList;
        }

        return $categoryList;
    }

}
