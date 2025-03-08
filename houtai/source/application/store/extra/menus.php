<?php
/**
 * 后台菜单配置
 *    'home' => [
 *       'name' => '首页',                // 菜单名称
 *       'icon' => 'icon-home',          // 图标 (class)
 *       'index' => 'index/index',         // 链接
 *     ],
 */
return [
    'index' => [
        'name' => '首页',
        'icon' => 'icon-home',
        'index' => 'index/index',
    ],
    'store' => [
        'name' => '管理员',
        'icon' => 'icon-guanliyuan',
        'index' => 'store.user/index',
        'submenu' => [
            [
                'name' => '管理员列表',
                'index' => 'store.user/index',
                'uris' => [
                    'store.user/index',
                    'store.user/add',
                    'store.user/edit',
                    'store.user/delete',
                ],
            ],
            [
                'name' => '角色管理',
                'index' => 'store.role/index',
                'uris' => [
                    'store.role/index',
                    'store.role/add',
                    'store.role/edit',
                    'store.role/delete',
                ],
            ],
        ]
    ],
    'game' => [
        'name' => '局管理',
        'icon' => 'icon-goods',
        'index' => 'game/index',
        'submenu' => [
            [
                'name' => '局列表',
                'index' => 'game/index',
                'uris' => [
                    'game/index',
                    'game/detail',
                    'game/users',
                    'goods/delete'
                ],
            ],
            [
                'name' => '局分类',
                'index' => 'game.category/index',
                'uris' => [
                    'game.category/index',
                    'game.category/add',
                    'game.category/edit',
                ],
            ],
            [
                'name' => '局标签管理',
                'index' => 'game.tags/index',
                'uris' => [
                    'game.tags/index',
                    'game.tags/add',
                    'game.tags/edit',
                    'game.tags/delete',
                ],
            ],
            [
                'name' => '开通城市',
                'index' => 'game.city/index',
                'uris' => [
                    'game.city/index',
                    'game.city/add',
                    'game.city/edit',
                    'game.city/delete',
                ],
            ],
//            [
//                'name' => '局评价',
//                'index' => 'game.comment/index',
//                'uris' => [
//                    'game.comment/index',
//                    'game.comment/detail',
//                ],
//            ]
        ],
    ],
    'order' => [
        'name' => '局订单管理',
        'icon' => 'icon-order',
        'index' => 'game.order/all_list',
        'submenu' => [
            [
                'name' => '全部订单',
                'index' => 'game.order/all_list',
            ],
            [
                'name' => '待完成',
                'index' => 'game.order/receipt_list',
            ],
            [
                'name' => '待付款',
                'index' => 'game.order/pay_list',
            ],
            [
                'name' => '已完成',
                'index' => 'game.order/complete_list',

            ],
            [
                'name' => '已取消',
                'index' => 'game.order/cancel_list',
            ],
        ]
    ],
    'goods' => [
        'name' => '商品管理',
        'icon' => 'icon-goods',
        'index' => 'goods/index',
        'submenu' => [
            [
                'name' => '商品列表',
                'index' => 'goods/index',
                'uris' => [
                    'goods/index',
                    'goods/add',
                    'goods/edit',
                    'goods/copy'
                ],
            ],
            [
                'name' => '商品分类',
                'index' => 'goods.category/index',
                'uris' => [
                    'goods.category/index',
                    'goods.category/add',
                    'goods.category/edit',
                ],
            ],
            [
                'name' => '商品评价',
                'index' => 'goods.comment/index',
                'uris' => [
                    'goods.comment/index',
                    'goods.comment/detail',
                ],
            ]
        ],
    ],
    'order' => [
        'name' => '订单管理',
        'icon' => 'icon-order',
        'index' => 'order/all_list',
        'submenu' => [
            [
                'name' => '全部订单',
                'index' => 'order/all_list',
            ],
            [
                'name' => '待发货',
                'index' => 'order/delivery_list',
            ],
            [
                'name' => '待收货',
                'index' => 'order/receipt_list',
            ],
            [
                'name' => '待付款',
                'index' => 'order/pay_list',
            ],
            [
                'name' => '已完成',
                'index' => 'order/complete_list',

            ],
            [
                'name' => '已取消',
                'index' => 'order/cancel_list',
            ],
            [
                'name' => '售后管理',
                'index' => 'order.refund/index',
                'uris' => [
                    'order.refund/index',
                    'order.refund/detail',
                ]
            ],
        ]
    ],
    'user' => [
        'name' => '用户管理',
        'icon' => 'icon-user',
        'index' => 'user/index',
        'submenu' => [
            [
                'name' => '用户列表',
                'index' => 'user/index',
            ],
            [
                'name' => '用户标签',
                'index' => 'user.label/index',
                'uris' => [
                    'user.label/index',
                    'user.label/add',
                    'user.label/edit',
                    'user.label/delete',
                ]
            ],
            [
                'name' => '会员等级',
                'active' => true,
                'submenu' => [
                    [
                        'name' => '等级管理',
                        'index' => 'user.grade/index',
                        'uris' => [
                            'user.grade/index',
                            'user.grade/add',
                            'user.grade/edit',
                            'user.grade/delete',
                        ]
                    ],
                ]
            ],
            [
                'name' => '余额记录',
                'active' => true,
                'submenu' => [
                    [
                        'name' => '充值记录',
                        'index' => 'user.recharge/order',
                    ],
                    [
                        'name' => '余额明细',
                        'index' => 'user.balance/log',
                    ],
                ]
            ],
            [
                'name' => '认证中心',
                'active' => true,
                'submenu' => [
                    [
                        'name' => '身份认证',
                        'index' => 'user.idcard/index',
                        'uris' => [
                            'user.idcard/index',
                            'user.idcard/check',
                            'user.idcard/delete',
                        ]
                    ],
                    [
                        'name' => '学历认证',
                        'index' => 'user.school/index',
                        'uris' => [
                            'user.school/index',
                            'user.school/check',
                            'user.school/delete',
                        ]
                    ],
                ]
            ],
            [
                'name' => '推送中心',
                'active' => true,
                'submenu' => [
                    [
                        'name' => '推送标签',
                        'index' => 'user.pushtag/index',
                        'uris' => [
                            'user.pushtag/index',
                            'user.pushtag/add',
                            'user.pushtag/edit',
                            'user.pushtag/delete',
                        ]
                    ],
                    [
                        'name' => '推送任务',
                        'index' => 'user.pushtask/index',
                        'uris' => [
                            'user.pushtask/index',
                            'user.pushtask/add',
                            'user.pushtask/edit',
                            'user.pushtask/delete',
                        ]
                    ],
                ]
            ],
            [
                'name' => '热搜关键词',
                'index' => 'user.keyword/index',
                'uris' => [
                    'user.keyword/index',
                    'user.keyword/add',
                    'user.keyword/edit',
                    'user.keyword/delete',
                ]
            ],
        ]
    ],
//    'shop' => [
//        'name' => '门店管理',
//        'icon' => 'icon-shop',
//        'index' => 'shop/index',
//        'submenu' => [
//            [
//                'name' => '门店管理',
//                'active' => true,
//                'index' => 'shop/index',
//                'submenu' => [
//                    [
//                        'name' => '门店列表',
//                        'index' => 'shop/index',
//                        'uris' => [
//                            'shop/index',
//                            'shop/add',
//                            'shop/edit',
//                        ]
//                    ],
//                    [
//                        'name' => '店员管理',
//                        'index' => 'shop.clerk/index',
//                        'uris' => [
//                            'shop.clerk/index',
//                            'shop.clerk/add',
//                            'shop.clerk/edit',
//                        ]
//                    ],
//                ]
//            ],
//            [
//                'name' => '订单核销记录',
//                'index' => 'shop.order/index',
//            ]
//        ]
//    ],
    'content' => [
        'name' => '内容管理',
        'icon' => 'icon-wenzhang',
        'index' => 'content.article/index',
        'submenu' => [
            [
                'name' => '文章管理',
                'active' => true,
                'submenu' => [
                    [
                        'name' => '文章列表',
                        'index' => 'content.article/index',
                        'uris' => [
                            'content.article/index',
                            'content.article/add',
                            'content.article/edit',
                        ]
                    ],
                    [
                        'name' => '文章分类',
                        'index' => 'content.article.category/index',
                        'uris' => [
                            'content.article.category/index',
                            'content.article.category/add',
                            'content.article.category/edit',
                        ]
                    ],
                ]
            ],
            [
                'name' => '文件库管理',
                'submenu' => [
                    [
                        'name' => '文件分组',
                        'index' => 'content.files.group/index',
                        'uris' => [
                            'content.files.group/index',
                            'content.files.group/add',
                            'content.files.group/edit',
                        ]
                    ],
                    [
                        'name' => '文件列表',
                        'index' => 'content.files/index'
                    ],
                    [
                        'name' => '回收站',
                        'index' => 'content.files/recycle',
                    ],
                ]
            ],
        ]
    ],
//    'market' => [
//        'name' => '营销管理',
//        'icon' => 'icon-marketing',
//        'index' => 'market.coupon/index',
//        'submenu' => [
//            [
//                'name' => '礼品券',
////                'active' => true,
//                'submenu' => [
//                    [
//                        'name' => '礼品券列表',
//                        'index' => 'market.gift/index',
//                        'uris' => [
//                            'market.gift/index',
//                            'market.gift/add',
//                            'market.gift/edit',
//                        ]
//                    ],
//
//                ]
//            ],
//            [
//                'name' => '优惠券',
////                'active' => true,
//                'submenu' => [
//                    [
//                        'name' => '优惠券列表',
//                        'index' => 'market.coupon/index',
//                        'uris' => [
//                            'market.coupon/index',
//                            'market.coupon/add',
//                            'market.coupon/edit',
//                        ]
//                    ],
//                    [
//                        'name' => '领取记录',
//                        'index' => 'market.coupon/receive'
//                    ],
//                ]
//            ],
//            [
//                'name' => '用户充值',
//                'submenu' => [
//                    [
//                        'name' => '充值套餐',
//                        'index' => 'market.recharge.plan/index',
//                        'uris' => [
//                            'market.recharge.plan/index',
//                            'market.recharge.plan/add',
//                            'market.recharge.plan/edit',
//                        ]
//                    ],
//                    [
//                        'name' => '充值设置',
//                        'index' => 'market.recharge/setting'
//                    ],
//                ]
//            ],
//            [
//                'name' => '积分管理',
//                'submenu' => [
//                    [
//                        'name' => '积分设置',
//                        'index' => 'market.points/setting'
//                    ],
//                    [
//                        'name' => '积分明细',
//                        'index' => 'market.points/log'
//                    ],
//                ]
//            ],
//            [
//                'name' => '消息推送',
//                'submenu' => [
//                    [
//                        'name' => '发送消息',
//                        'index' => 'market.push/send',
//                    ],
//                    [
//                        'name' => '活跃用户',
//                        'index' => 'market.push/user',
//                    ],
////                    [
////                        'name' => '发送日志',
////                        'index' => 'market.push/log',
////                    ],
//                ]
//            ],
//            [
//                'name' => '满额包邮',
//                'index' => 'market.basic/full_free',
//            ],
//        ],
//    ],
    'wxapp' => [
        'name' => '微信',
        'icon' => 'icon-wxapp',
        'color' => '#36b313',
        'index' => 'wxapp/setting',
        'submenu' => [
            [
                'name' => '微信设置',
                'index' => 'wxapp/setting',
            ],
//            [
//                'name' => '页面管理',
//                'active' => true,
//                'submenu' => [
//                    [
//                        'name' => '页面设计',
//                        'index' => 'wxapp.page/index',
//                        'uris' => [
//                            'wxapp.page/index',
//                            'wxapp.page/add',
//                            'wxapp.page/edit',
//                        ]
//                    ],
//                    [
//                        'name' => '分类模板',
//                        'index' => 'wxapp.page/category'
//                    ],
//                    [
//                        'name' => '页面链接',
//                        'index' => 'wxapp.page/links'
//                    ]
//                ]
//            ],
//            [
//                'name' => '帮助中心',
//                'index' => 'wxapp.help/index',
//                'uris' => [
//                    'wxapp.help/index',
//                    'wxapp.help/add',
//                    'wxapp.help/edit'
//                ]
//            ],
        ],
    ],
//    'apps' => [
//        'name' => '应用中心',
//        'icon' => 'icon-application',
//        'is_svg' => true,   // 多色图标
//        'index' => 'apps.dealer.apply/index',
//        'submenu' => [
//            [
//                'name' => '分销中心',
//                'submenu' => [
//                    [
//                        'name' => '入驻申请',
//                        'index' => 'apps.dealer.apply/index',
//                    ],
//                    [
//                        'name' => '分销商用户',
//                        'index' => 'apps.dealer.user/index',
//                        'uris' => [
//                            'apps.dealer.user/index',
//                            'apps.dealer.user/fans',
//                        ]
//                    ],
//                    [
//                        'name' => '分销订单',
//                        'index' => 'apps.dealer.order/index',
//                    ],
//                    [
//                        'name' => '提现申请',
//                        'index' => 'apps.dealer.withdraw/index',
//                    ],
//                    [
//                        'name' => '分销设置',
//                        'index' => 'apps.dealer.setting/index',
//                    ],
//                    [
//                        'name' => '分销海报',
//                        'index' => 'apps.dealer.setting/qrcode',
//                    ],
//                ]
//            ],
//            [
//                'name' => '拼团管理',
//                'submenu' => [
//                    [
//                        'name' => '商品分类',
//                        'index' => 'apps.sharing.category/index',
//                        'uris' => [
//                            'apps.sharing.category/index',
//                            'apps.sharing.category/add',
//                            'apps.sharing.category/edit',
//                        ]
//                    ],
//                    [
//                        'name' => '商品列表',
//                        'index' => 'apps.sharing.goods/index',
//                        'uris' => [
//                            'apps.sharing.goods/index',
//                            'apps.sharing.goods/add',
//                            'apps.sharing.goods/edit',
//                            'apps.sharing.goods/copy',
//                            'apps.sharing.goods/copy_master',
//                        ]
//                    ],
//                    [
//                        'name' => '拼单管理',
//                        'index' => 'apps.sharing.active/index',
//                        'uris' => [
//                            'apps.sharing.active/index',
//                            'apps.sharing.active/users',
//                        ]
//                    ],
//                    [
//                        'name' => '订单管理',
//                        'index' => 'apps.sharing.order/index',
//                        'uris' => [
//                            'apps.sharing.order/index',
//                            'apps.sharing.order/detail',
//                            'apps.sharing.order.operate/batchdelivery'
//                        ]
//                    ],
//                    [
//                        'name' => '售后管理',
//                        'index' => 'apps.sharing.order.refund/index',
//                        'uris' => [
//                            'apps.sharing.order.refund/index',
//                            'apps.sharing.order.refund/detail',
//                        ]
//                    ],
//                    [
//                        'name' => '商品评价',
//                        'index' => 'apps.sharing.comment/index',
//                        'uris' => [
//                            'apps.sharing.comment/index',
//                            'apps.sharing.comment/detail',
//                        ],
//                    ],
//                    [
//                        'name' => '拼团设置',
//                        'index' => 'apps.sharing.setting/index'
//                    ]
//                ]
//            ],
//            [
//                'name' => '砍价活动',
//                'index' => 'apps.bargain.active/index',
//                'submenu' => [
//                    [
//                        'name' => '活动列表',
//                        'index' => 'apps.bargain.active/index',
//                        'uris' => [
//                            'apps.bargain.active/index',
//                            'apps.bargain.active/add',
//                            'apps.bargain.active/edit',
//                            'apps.bargain.active/delete',
//                        ],
//                    ],
//                    [
//                        'name' => '砍价记录',
//                        'index' => 'apps.bargain.task/index',
//                        'uris' => [
//                            'apps.bargain.task/index',
//                            'apps.bargain.task/add',
//                            'apps.bargain.task/edit',
//                            'apps.bargain.task/delete',
//                            'apps.bargain.task/help',
//                        ],
//                    ],
//                    [
//                        'name' => '砍价设置',
//                        'index' => 'apps.bargain.setting/index',
//                    ]
//                ]
//            ],
//            [
//                'name' => '整点秒杀',
//                'index' => 'apps.sharp.goods/index',
//                'submenu' => [
//                    [
//                        'name' => '秒杀商品',
//                        'index' => 'apps.sharp.goods/index',
//                        'uris' => [
//                            'apps.sharp.goods/index',
//                            'apps.sharp.goods/add',
//                            'apps.sharp.goods/select',
//                            'apps.sharp.goods/edit',
//                            'apps.sharp.goods/delete',
//                        ],
//                    ],
//                    [
//                        'name' => '活动会场',
//                        'index' => 'apps.sharp.active/index',
//                        'uris' => [
//                            'apps.sharp.active/index',
//                            'apps.sharp.active/add',
//                            'apps.sharp.active/edit',
//                            'apps.sharp.active/state',
//                            'apps.sharp.active/delete',
//
//                            'apps.sharp.active_time/index',
//                            'apps.sharp.active_time/add',
//                            'apps.sharp.active_time/edit',
//                            'apps.sharp.active_time/state',
//                            'apps.sharp.active_time/delete',
//                        ],
//                    ],
//                    [
//                        'name' => '基础设置',
//                        'index' => 'apps.sharp.setting/index',
//                    ]
//                ]
//            ],
//            [
//                'name' => '好物圈',
//                'index' => 'apps.wow.order/index',
//                'submenu' => [
//                    [
//                        'name' => '商品收藏',
//                        'index' => 'apps.wow.shoping/index',
//                    ],
//                    [
//                        'name' => '订单信息',
//                        'index' => 'apps.wow.order/index',
//                    ],
//                    [
//                        'name' => '基础设置',
//                        'index' => 'apps.wow.setting/index',
//                    ]
//                ]
//            ],
//        ]
//    ],
    'setting' => [
        'name' => '设置',
        'icon' => 'icon-setting',
        'index' => 'setting/store',
        'submenu' => [
            [
                'name' => 'app设置',
                'index' => 'setting/store',
            ],
            [
                'name' => '交易设置',
                'index' => 'setting/trade',
            ],
//            [
//                'name' => '运费模板',
//                'index' => 'setting.delivery/index',
//                'uris' => [
//                    'setting.delivery/index',
//                    'setting.delivery/add',
//                    'setting.delivery/edit',
//                ],
//            ],
//            [
//                'name' => '物流公司',
//                'index' => 'setting.express/index',
//                'uris' => [
//                    'setting.express/index',
//                    'setting.express/add',
//                    'setting.express/edit',
//                ],
//            ],
            [
                'name' => '短信通知',
                'index' => 'setting/sms'
            ],
            [
                'name' => '模板消息',
                'index' => 'setting/tplmsg',
                'uris' => [
                    'setting/tplmsg',
                    'setting.help/tplmsg'

                ],
            ],
//            [
//                'name' => '退货地址',
//                'index' => 'setting.address/index',
//                'uris' => [
//                    'setting.address/index',
//                    'setting.address/add',
//                    'setting.address/edit',
//                ],
//            ],
            [
                'name' => '上传设置',
                'index' => 'setting/storage',
            ],
            [
                'name' => '小票打印机',
                'submenu' => [
                    [
                        'name' => '打印机管理',
                        'index' => 'setting.printer/index',
                        'uris' => [
                            'setting.printer/index',
                            'setting.printer/add',
                            'setting.printer/edit'
                        ]
                    ],
                    [
                        'name' => '打印设置',
                        'index' => 'setting/printer'
                    ]
                ]
            ],
            [
                'name' => '字典',
                'submenu' => [
                    [
                        'name' => '字典',
                        'index' => 'setting.dict/index',
                        'uris' => [
                            'setting.dict/index',
                            'setting.dict/add',
                            'setting.dict/edit'
                        ]
                    ],
                    [
                        'name' => '字典数据',
                        'index' => 'setting.dictdata/index',
                        'uris' => [
                            'setting.dictdata/index',
                            'setting.dictdata/add',
                            'setting.dictdata/edit'
                        ]
                    ]
                ]
            ],
            [
                'name' => '其他',
                'submenu' => [
                    [
                        'name' => '清理缓存',
                        'index' => 'setting.cache/clear'
                    ]
                ]
            ]
        ],
    ],
];
