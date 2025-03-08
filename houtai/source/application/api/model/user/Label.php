<?php

namespace app\api\model\user;

use app\common\model\user\Label as LabelModel;

/**
 * 用户Label模型
 * Class Label
 * @package app\api\model\user
 */
class Label extends LabelModel
{
    /**
     * 获取列表
     * @param $userId
     * @return \think\Paginator
     * @throws \think\exception\DbException
     */
    public function getList($userId)
    {
        // 获取列表数据
        return $this->with(['label' => ['category']])
            ->where('user_id', '=', $userId)
            ->where('status', '=', 1)
            ->order(['user_label_id' => 'asc'])
            ->select();
    }

    /**
     * 新增记录
     * @param $data
     * @return bool
     * @throws \Exception
     */
    public function add($user_id, $list)
    {
        $datas = [];
        $userList = $this->getList($user_id); //已经存在的列表
        if($userList) {
            foreach($userList as $key => $item) {
                if(!in_array($item['label_id'], $list)) {
                    $detail = LabelModel::detail(['user_label_id' => $item['user_label_id']]);
                    $detail->save(['status' => 0]);
                }
            }
        }
        foreach($list as $key => $label_id) {
            $detail = LabelModel::detail(['label_id' => $label_id, 'user_id' => $user_id]);
            $data = [
                'label_id' => $label_id,
                'user_id' => $user_id,
                'wxapp_id' =>self::$wxapp_id
            ];
            if($detail) {
                if($detail['status'] < 1) {
                    $detail->save(['status' => 1]);
                }
            }else{
                $data['status'] = 1;
                $datas[] = $data;
            }
        }

        return $this->allowField(true)->saveAll($datas);
    }


}