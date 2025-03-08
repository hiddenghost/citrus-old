<div class="row-content am-cf">
    <div class="row">
        <div class="am-u-sm-12 am-u-md-12 am-u-lg-12">
            <div class="widget am-cf">
                <div class="widget-head am-cf">
                    <div class="widget-title am-cf">城市</div>
                </div>
                <div class="widget-body am-fr">
                    <div class="am-u-sm-12 am-u-md-6 am-u-lg-6">
                        <div class="am-form-group">
                            <div class="am-btn-toolbar">
                                <?php if (checkPrivilege('game.city/add')): ?>
                                    <div class="am-btn-group am-btn-group-xs">
                                        <a class="am-btn am-btn-default am-btn-success am-radius"
                                           href="<?= url('game.city/add') ?>">
                                            <span class="am-icon-plus"></span> 新增
                                        </a>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <div class="am-u-sm-12">
                        <table width="100%" class="am-table am-table-compact am-table-striped tpl-table-black ">
                            <thead>
                            <tr>
                                <th>城市ID</th>
                                <th>名称</th>
                                <th>简称</th>
                                <th>层级</th>
                                <th>拼音</th>
                                <th>首字母</th>
                                <th>经度</th>
                                <th>纬度</th>
                                <th>添加时间</th>
                                <th>操作</th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php if (!empty($list)): foreach ($list as $first): ?>
                                <tr>
                                    <td class="am-text-middle"><?= $first['id'] ?></td>
                                    <td class="am-text-middle"><?= $first['name'] ?></td>
                                    <td class="am-text-middle"><?= $first['shortname'] ?></td>
                                    <td class="am-text-middle"><?= $first['level'] ?></td>
                                    <td class="am-text-middle"><?= $first['pinyin'] ?></td>
                                    <td class="am-text-middle"><?= $first['first'] ?></td>
                                    <td class="am-text-middle"><?= $first['lng'] ?></td>
                                    <td class="am-text-middle"><?= $first['lat'] ?></td>
                                    <td class="am-text-middle"><?= $first['create_time'] ?></td>
                                    <td class="am-text-middle">
                                        <div class="tpl-table-black-operation">
                                            <?php if (checkPrivilege('game.city/edit')): ?>
                                                <a href="<?= url('game.city/edit',
                                                    ['id' => $first['id']]) ?>">
                                                    <i class="am-icon-pencil"></i> 编辑
                                                </a>
                                            <?php endif; ?>
                                            <?php if (checkPrivilege('game.city/delete')): ?>
                                                <a href="javascript:;" class="item-delete tpl-table-black-operation-del"
                                                   data-id="<?= $first['id'] ?>">
                                                    <i class="am-icon-trash"></i> 删除
                                                </a>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                                <?php if (isset($first['child'])): foreach ($first['child'] as $two): ?>
                                    <tr>
                                        <td class="am-text-middle"><?= $two['id'] ?></td>
                                        <td class="am-text-middle">　-- <?= $two['name'] ?></td>
                                        <td class="am-text-middle"><?= $two['shortname'] ?></td>
                                        <td class="am-text-middle"><?= $two['level'] ?></td>
                                        <td class="am-text-middle"><?= $two['pinyin'] ?></td>
                                        <td class="am-text-middle"><?= $two['first'] ?></td>
                                        <td class="am-text-middle"><?= $two['lng'] ?></td>
                                        <td class="am-text-middle"><?= $two['lat'] ?></td>
                                        <td class="am-text-middle"><?= $two['create_time'] ?></td>
                                        <td class="am-text-middle">
                                            <div class="tpl-table-black-operation">
                                                <?php if (checkPrivilege('game.city/edit')): ?>
                                                    <a href="<?= url('game.city/edit',
                                                        ['id' => $two['id']]) ?>">
                                                        <i class="am-icon-pencil"></i> 编辑
                                                    </a>
                                                <?php endif; ?>
                                                <?php if (checkPrivilege('game.city/delete')): ?>
                                                    <a href="javascript:;"
                                                       class="item-delete tpl-table-black-operation-del"
                                                       data-id="<?= $two['id'] ?>">
                                                        <i class="am-icon-trash"></i> 删除
                                                    </a>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php if (isset($two['child'])): foreach ($two['child'] as $three): ?>
                                        <tr>
                                            <td class="am-text-middle"><?= $three['id'] ?></td>
                                            <td class="am-text-middle">　　　-- <?= $three['name'] ?></td>
                                            <td class="am-text-middle"><?= $three['shortname'] ?></td>
                                            <td class="am-text-middle"><?= $three['level'] ?></td>
                                            <td class="am-text-middle"><?= $three['pinyin'] ?></td>
                                            <td class="am-text-middle"><?= $three['first'] ?></td>
                                            <td class="am-text-middle"><?= $three['lng'] ?></td>
                                            <td class="am-text-middle"><?= $three['lat'] ?></td>
                                            <td class="am-text-middle"><?= $three['create_time'] ?></td>
                                            <td class="am-text-middle">
                                                <div class="tpl-table-black-operation">
                                                    <?php if (checkPrivilege('game.city/edit')): ?>
                                                        <a href="<?= url('game.city/edit',
                                                            ['id' => $three['id']]) ?>">
                                                            <i class="am-icon-pencil"></i> 编辑
                                                        </a>
                                                    <?php endif; ?>
                                                    <?php if (checkPrivilege('game.city/delete')): ?>
                                                        <a href="javascript:;"
                                                           class="item-delete tpl-table-black-operation-del"
                                                           data-id="<?= $three['id'] ?>">
                                                            <i class="am-icon-trash"></i> 删除
                                                        </a>
                                                    <?php endif; ?>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; endif; ?>
                                <?php endforeach; endif; ?>
                            <?php endforeach; else: ?>
                                <tr>
                                    <td colspan="10" class="am-text-center">暂无记录</td>
                                </tr>
                            <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
    $(function () {
        // 删除元素
        var url = "<?= url('game.city/delete') ?>";
        $('.item-delete').delete('id', url);

    });
</script>

