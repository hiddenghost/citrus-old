<div class="row-content am-cf">
    <div class="row">
        <div class="am-u-sm-12 am-u-md-12 am-u-lg-12">
            <div class="widget am-cf">
                <form id="my-form" class="am-form tpl-form-line-form" enctype="multipart/form-data" method="post">
                    <div class="widget-body">
                        <fieldset>
                            <div class="widget-head am-cf">
                                <div class="widget-title am-fl">编辑城市</div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require">名称 </label>
                                <div class="am-u-sm-9 am-u-end">
                                    <input type="text" class="tpl-form-input" name="city[name]"
                                           value="<?= $model['name'] ?>" required>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require">简称 </label>
                                <div class="am-u-sm-9 am-u-end">
                                    <input type="text" class="tpl-form-input" name="city[shortname]"
                                           value="<?= $model['shortname'] ?>" required>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require">所属上级 </label>
                                <div class="am-u-sm-9 am-u-end">
                                    <select name="city[pid]"
                                            data-am-selected="{searchBox: 1, btnSize: 'sm'}">
                                        <option value="0">顶级上级</option>
                                        <?php if (isset($list)): foreach ($list as $first): ?>
                                            <option value="<?= $first['id'] ?>"
                                                <?= $model['pid'] == $first['id'] ? 'selected' : '' ?>>
                                                <?= $first['name'] ?></option>
                                            <?php if (isset($first['child'])): foreach ($first['child'] as $two): ?>
                                                <option value="<?= $two['id'] ?>"
                                                    <?= $model['pid'] == $two['id'] ? 'selected' : '' ?>
                                                >&nbsp;&nbsp;--
                                                    <?= $two['name'] ?></option>
                                                <?php if (isset($two['child'])): foreach ($two['child'] as $three): ?>
                                                    <option value="<?= $three['id'] ?>"
                                                        <?= $model['pid'] == $three['id'] ? 'selected' : '' ?>
                                                    >&nbsp;&nbsp;&nbsp;&nbsp;--
                                                        <?= $three['name'] ?></option>
                                                <?php endforeach; endif; ?>
                                            <?php endforeach; endif; ?>
                                        <?php endforeach; endif; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require">层级 </label>
                                <div class="am-u-sm-9 am-u-end">
                                    <select name="city[level]"
                                            data-am-selected="{searchBox: 1, btnSize: 'sm'}">
                                        <option value="1" <?= $model['level'] == 1 ? 'selected' : '' ?>>国家级</option>
                                        <option value="2" <?= $model['level'] == 2 ? 'selected' : '' ?>>省级</option>
                                        <option value="3" <?= $model['level'] == 3 ? 'selected' : '' ?>>市级</option>
                                    </select>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require">拼音 </label>
                                <div class="am-u-sm-9 am-u-end">
                                    <input type="text" class="tpl-form-input" name="city[pinyin]"
                                           value="<?= $model['pinyin'] ?>" required>
                                    <small>拼音</small>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label ">长途区号 </label>
                                <div class="am-u-sm-9 am-u-end">
                                    <input type="text" class="tpl-form-input" name="city[code]"
                                           value="<?= $model['code'] ?>" >
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label ">邮编 </label>
                                <div class="am-u-sm-9 am-u-end">
                                    <input type="text" class="tpl-form-input" name="city[zip_code]"
                                           value="<?= $model['zip_code'] ?>" >
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require">经度 </label>
                                <div class="am-u-sm-9 am-u-end">
                                    <input type="text" class="tpl-form-input" name="city[lng]"
                                           value="<?= $model['lng'] ?>" required>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require">纬度 </label>
                                <div class="am-u-sm-9 am-u-end">
                                    <input type="text" class="tpl-form-input" name="city[lat]"
                                           value="<?= $model['lat'] ?>" required>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <div class="am-u-sm-9 am-u-sm-push-3 am-margin-top-lg">
                                    <button type="submit" class="j-submit am-btn am-btn-secondary">提交
                                    </button>
                                </div>
                            </div>
                        </fieldset>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- 图片文件列表模板 -->
{{include file="layouts/_template/tpl_file_item" /}}

<!-- 文件库弹窗 -->
{{include file="layouts/_template/file_library" /}}

<script>
    $(function () {
        

        /**
         * 表单验证提交
         * @type {*}
         */
        $('#my-form').superForm();

    });
</script>
