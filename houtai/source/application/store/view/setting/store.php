<?php

use app\common\enum\DeliveryType as DeliveryTypeEnum;

?>
<div class="row-content am-cf">
    <div class="row">
        <div class="am-u-sm-12 am-u-md-12 am-u-lg-12">
            <div class="widget am-cf">
                <form id="my-form" class="am-form tpl-form-line-form" method="post">
                    <div class="widget-body">
                        <fieldset>
                            <div class="widget-head am-cf">
                                <div class="widget-title am-fl">app设置</div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-form-label form-require"> app名称 </label>
                                <div class="am-u-sm-9">
                                    <input type="text" class="tpl-form-input" name="store[name]"
                                           value="<?= $values['name'] ?>" required>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-form-label form-require"> 最新版本号 </label>
                                <div class="am-u-sm-9">
                                    <input type="text" class="tpl-form-input" name="store[version]"
                                           value="<?= $values['version'] ?>" required>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-form-label form-require"> 官方电话 </label>
                                <div class="am-u-sm-9">
                                    <input type="text" class="tpl-form-input" name="store[phone]"
                                           value="<?= $values['phone'] ?>" required>
                                    <small>多个电话用英文逗号隔开,</small>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-form-label form-require"> app Logo </label>
                                <div class="am-u-sm-9 am-u-end">
                                    <div class="am-form-file">
                                        <div class="am-form-file">
                                            <button type="button"
                                                    class="logo-upload upload-file am-btn am-btn-secondary am-radius">
                                                <i class="am-icon-cloud-upload"></i> 选择图片
                                            </button>
                                            <div class="uploader-list am-cf">
                                                <?php if(isset($values['logo']) && $values['logo']): ?>
                                                    <div class="file-item">
                                                        <a href="<?= $values['logo']['file_path'] ?>"
                                                           title="点击查看大图" target="_blank">
                                                            <img src="<?= $values['logo']['file_path'] ?>">
                                                        </a>
                                                        <input type="hidden" name="shop[logo_image_id]"
                                                               value="<?= $values['logo_image_id'] ?>">
                                                        <i class="iconfont icon-shanchu file-item-delete"></i>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="am-form-group" style="display: none;">
                                <label class="am-u-sm-3 am-form-label form-require"> 配送方式 </label>
                                <div class="am-u-sm-9">
                                    <?php foreach (DeliveryTypeEnum::data() as $item): ?>
                                        <label class="am-checkbox-inline">
                                            <input type="checkbox" name="store[delivery_type][]"
                                                   value="<?= $item['value'] ?>" data-am-ucheck
                                                <?= in_array($item['value'], $values['delivery_type']) ? 'checked' : '' ?>>
                                            <?= $item['name'] ?>
                                        </label>
                                    <?php endforeach; ?>
                                    <div class="help-block">
                                        <small>注：配送方式至少选择一个</small>
                                    </div>
                                </div>
                            </div>
                            <div class="widget-head am-cf" style="display: none">
                                <div class="widget-title am-fl"> 物流查询API</div>
                            </div>
                            <div class="am-form-group" style="display: none">
                                <label class="am-u-sm-3 am-form-label"> 快递100 Customer </label>
                                <div class="am-u-sm-9">
                                    <input type="text" class="tpl-form-input" name="store[kuaidi100][customer]"
                                           value="<?= $values['kuaidi100']['customer'] ?>">
                                    <small>用于查询物流信息，<a href="https://www.kuaidi100.com/openapi/"
                                                       target="_blank">快递100申请</a></small>
                                </div>
                            </div>
                            <div class="am-form-group" style="display: none">
                                <label class="am-u-sm-3 am-form-label"> 快递100 Key </label>
                                <div class="am-u-sm-9">
                                    <input type="text" class="tpl-form-input" name="store[kuaidi100][key]"
                                           value="<?= $values['kuaidi100']['key'] ?>">
                                </div>
                            </div>
                            <div class="widget-head am-cf" >
                                <div class="widget-title am-fl"> 个人信息标签背景颜色</div>
                            </div>
                            <div class="am-form-group" >
                                <label class="am-u-sm-3 am-form-label"> 生日标签背景 </label>
                                <div class="am-u-sm-9">
                                    <input type="text" class="tpl-form-input" name="store[color][birthday]"
                                           value="<?= $values['color']['birthday'] ?>" id="color-birthday-text">
                                    <input type="color" class="tpl-form-input color-picker" dataid="color-birthday-text">
                                </div>
                            </div>
                            <div class="am-form-group" >
                                <label class="am-u-sm-3 am-form-label"> 身高标签背景 </label>
                                <div class="am-u-sm-9">
                                    <input type="text" class="tpl-form-input" name="store[color][height]"
                                           value="<?= $values['color']['height'] ?>" id="color-height-text">
                                    <input type="color" class="tpl-form-input color-picker" dataid="color-height-text">
                                </div>
                            </div>
                            <div class="am-form-group" >
                                <label class="am-u-sm-3 am-form-label"> 工作标签背景 </label>
                                <div class="am-u-sm-9">
                                    <input type="text" class="tpl-form-input" name="store[color][work]"
                                           value="<?= $values['color']['work'] ?>" id="color-work-text">
                                    <input type="color" class="tpl-form-input color-picker" dataid="color-work-text">
                                </div>
                            </div>
                            <div class="am-form-group" >
                                <label class="am-u-sm-3 am-form-label"> 学校标签背景 </label>
                                <div class="am-u-sm-9">
                                    <input type="text" class="tpl-form-input" name="store[color][school]"
                                           value="<?= $values['color']['school'] ?>" id="color-school-text">
                                    <input type="color" class="tpl-form-input color-picker" dataid="color-school-text">
                                </div>
                            </div>
                            <div class="am-form-group" >
                                <label class="am-u-sm-3 am-form-label"> mbti标签背景 </label>
                                <div class="am-u-sm-9">
                                    <input type="text" class="tpl-form-input" name="store[color][mbti]"
                                           value="<?= $values['color']['mbti'] ?>" id="color-mbti-text">
                                    <input type="color" class="tpl-form-input color-picker" dataid="color-mbti-text">
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
<script id="tpl-file-item" type="text/template">
    {{ each list }}
    <div class="file-item">
        <a href="{{ $value.file_path }}" title="点击查看大图" target="_blank">
            <img src="{{ $value.file_path }}">
        </a>
        <input type="hidden" name="{{ name }}" value="{{ $value.file_id }}">
        <i class="iconfont icon-shanchu file-item-delete"></i>
    </div>
    {{ /each }}
</script>

<!-- 文件库弹窗 -->
{{include file="layouts/_template/file_library" /}}
<script src="assets/store/js/select.data.js?v=<?= $version ?>"></script>
<script>
    $(function () {

        // 选择图片
        $('.logo-upload').selectImages({
            name: 'store[logo_image_id]'
        });

        $('.color-picker').change(function() {
            $('#' + $(this).attr('dataid')).val($(this).val());
        });

        /**
         * 表单验证提交
         * @type {*}
         */
        $('#my-form').superForm();

    });
</script>
