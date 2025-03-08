<div class="row-content am-cf">
    <div class="row">
        <div class="am-u-sm-12 am-u-md-12 am-u-lg-12">
            <div class="widget am-cf">
                <div class="widget-head am-cf">
                    <div class="widget-title am-cf">局管理</div>
                </div>
                <div class="widget-body am-fr">
                    <!-- 工具栏 -->
                    <div class="page_toolbar am-margin-bottom-xs am-cf">
                        <form class="toolbar-form" action="">
                            <input type="hidden" name="s" value="/<?= $request->pathinfo() ?>">
                            <div class="am-u-sm-12 am-u-md-12">
                                <div class="am fr">
                                    <div class="am-form-group am-fl">
                                        <?php $category_id = $request->get('category_id') ?: null; ?>
                                        <select name="category_id"
                                                data-am-selected="{searchBox: 1, btnSize: 'sm',  placeholder: '局分类', maxHeight: 400}">
                                            <option value=""></option>
                                            <?php if (isset($catgory)): foreach ($catgory as $first): ?>
                                                <option value="<?= $first['category_id'] ?>"
                                                    <?= $category_id == $first['category_id'] ? 'selected' : '' ?>>
                                                    <?= $first['name'] ?></option>
                                                <?php if (isset($first['child'])): foreach ($first['child'] as $two): ?>
                                                    <option value="<?= $two['category_id'] ?>"
                                                        <?= $category_id == $two['category_id'] ? 'selected' : '' ?>>
                                                        　　<?= $two['name'] ?></option>
                                                <?php endforeach; endif; ?>
                                            <?php endforeach; endif; ?>
                                        </select>
                                    </div>
                                    <div class="am-form-group am-fl">
                                        <?php $status = $request->get('status') ?: null; ?>
                                        <select name="status"
                                                data-am-selected="{btnSize: 'sm', placeholder: '局状态'}">
                                            <option value=""></option>
                                            <option value="10"
                                                <?= $status == 10 ? 'selected' : '' ?>>上架
                                            </option>
                                            <option value="20"
                                                <?= $status == 20 ? 'selected' : '' ?>>下架
                                            </option>
                                        </select>
                                    </div>
                                    <div class="am-form-group am-fl">
                                        <div class="am-input-group am-input-group-sm tpl-form-border-form">
                                            <input type="text" class="am-form-field" name="search"
                                                   placeholder="请输入局名称"
                                                   value="<?= $request->get('search') ?>">
                                            <div class="am-input-group-btn">
                                                <button class="am-btn am-btn-default am-icon-search"
                                                        type="submit"></button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>

                    <div class="am-scrollable-horizontal am-u-sm-12">
                        <table width="100%" class="am-table am-table-compact am-table-striped
                         tpl-table-black am-text-nowrap">
                            <thead>
                            <tr>
                                <th>局ID</th>
                                <th>局图片</th>
                                <th>局名称</th>
                                <th>局分类</th>
                                <th>组局人数</th>
                                <th>已入局人员</th>
                                <th>发起人</th>
                                <th>活动状态</th>
                                <th>发起时间</th>
                                <th>操作</th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php if (!$list->isEmpty()): foreach ($list as $item): ?>
                                <tr>
                                    <td class="am-text-middle"><?= $item['game_id'] ?></td>
                                    <td class="am-text-middle">
                                        <a href="<?= $item['image'][0]['image_id'] ?>"
                                           title="点击查看大图" target="_blank">
                                            <img src="<?= $item['image'][0]['image_id'] ?>"
                                                 width="50" height="50" alt="局图片">
                                        </a>
                                    </td>
                                    <td class="am-text-middle">
                                        <p class="item-title"><?= $item['name'] ?></p>
                                    </td>
                                    <td class="am-text-middle"><?= $item['category']['name'] ?></td>
                                    <td class="am-text-middle"><?= $item['people'] > 0 ? $item['people'] . '人' : '不限' ?></td>
                                    <td class="am-text-middle"><?= $item['actual_people'] ?>人</td>
                                    <td class="am-text-middle">
                                        <p class=""><?= $item['user']['nickName'] ?></p>
                                        <p class="am-link-muted">(用户ID：<?= $item['user']['user_id'] ?>)</p>
                                    </td>
                                    <td class="am-text-middle">
                                        <?php if ($item['status']['value'] == 10): ?>
                                            <p>
                                                <span class="am-badge am-badge-secondary"><?= $item['status']['text'] ?></span>
                                            </p>
                                            <?php if ($item['actual_people'] < $item['people']): ?>
                                                <p>
                                                <span class="am-badge am-badge-warning">
                                                    还差 <?= $item['people'] - $item['actual_people'] ?>人
                                                </span>
                                                </p>
                                            <?php endif; ?>
                                        <?php elseif ($item['status']['value'] == 20): ?>
                                            <span class="am-badge am-badge-success"><?= $item['status']['text'] ?></span>
                                        <?php elseif ($item['status']['value'] == 30): ?>
                                            <span class="am-badge am-badge-danger"><?= $item['status']['text'] ?></span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="am-text-middle"><?= $item['create_time'] ?></td>
                                    <td class="am-text-middle">
                                        <div class="tpl-table-black-operation">
                                            <?php if (checkPrivilege('game/users')): ?>
                                                <a class="tpl-table-black-operation-default"
                                                   href="<?= url('game/users', ['game_id' => $item['game_id']]) ?>">
                                                    <i class="iconfont icon-chengyuan"></i> 局成员
                                                </a>
                                            <?php endif; ?>
                                            <!--
                                            <?php if (checkPrivilege('game.order/index')): ?>
                                                <a class="tpl-table-black-operation-default"
                                                   href="<?= url('game.order/index', ['game_id' => $item['game_id']]) ?>">
                                                    <i class="iconfont icon-order-o"></i> 局订单
                                                </a>
                                            <?php endif; ?>
                                            <?php if (checkPrivilege('game/detail')): ?>
                                                <a class="tpl-table-black-operation-default"
                                                   href="<?= url('game/detail', ['game_id' => $item['game_id']]) ?>">
                                                    <i class="iconfont icon-chengyuan"></i> 局详情
                                                </a>
                                            <?php endif; ?>
                                            -->
                                            <?php if (checkPrivilege('game/delete')): ?>
                                                <a href="javascript:;" class="item-delete tpl-table-black-operation-del"
                                                   data-id="<?= $item['game_id'] ?>">
                                                    <i class="am-icon-trash"></i> 删除
                                                </a>
                                            <?php endif; ?>

                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; else: ?>
                                <tr>
                                    <td colspan="9" class="am-text-center">暂无记录</td>
                                </tr>
                            <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                    <div class="am-u-lg-12 am-cf">
                        <div class="am-fr"><?= $list->render() ?> </div>
                        <div class="am-fr pagination-total am-margin-right">
                            <div class="am-vertical-align-middle">总记录：<?= $list->total() ?></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
    $(function () {

        // 局状态
        $('.j-state').click(function () {
            // 验证权限
            if (!"<?= checkPrivilege('game/state')?>") {
                return false;
            }
            var data = $(this).data();
            layer.confirm('确定要' + (parseInt(data.state) === 10 ? '下架' : '上架') + '该局吗？'
                , {title: '友情提示'}
                , function (index) {
                    $.post("<?= url('game/state') ?>"
                        , {
                            game_id: data.id,
                            state: Number(!(parseInt(data.state) === 10))
                        }
                        , function (result) {
                            result.code === 1 ? $.show_success(result.msg, result.url)
                                : $.show_error(result.msg);
                        });
                    layer.close(index);
                });

        });

        // 删除元素
        var url = "<?= url('game/delete') ?>";
        $('.item-delete').delete('game_id', url);

    });
</script>

