<div class="row-content am-cf">
    <div class="row">
        <div class="am-u-sm-12 am-u-md-12 am-u-lg-12">
            <div class="widget am-cf">
                <form id="my-form" class="am-form tpl-form-line-form" method="post">
                    <div class="widget-body">
                        <fieldset>
                            <div class="widget-head am-cf">
                                <div class="widget-title am-fl">推送内容</div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require"> 推送标题 </label>
                                <div class="am-u-sm-9 am-u-end">
                                    <input type="text" class="tpl-form-input" name="post[title]"
                                           placeholder="请输入" required>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require">推送内容 </label>
                                <div class="am-u-sm-9 am-u-end">
                                    <input type="text" class="tpl-form-input" name="post[content]"
                                           value="" required>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label"> 关联局 </label>
                                <div class="am-u-sm-9 am-u-md-6 am-u-lg-5 am-u-end">
                                    <select name="post[game_id]"
                                            data-am-selected="{searchBox: 1, btnSize: 'sm',
                                             placeholder:'请选择', maxHeight: 400}">
                                        <option value=""></option>
                                        <?php if (isset($category)): foreach ($category as $key => $first): ?>
                                            <option value="<?= $first['game_id'] ?>"

                                            ><?= $first['name'] ?></option>
                                        <?php endforeach; endif; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="widget-head am-cf">
                                <div class="widget-title am-fl">推送目标</div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require">推送目标 </label>
                                <div class="am-u-sm-9 am-u-end">
                                    <label class="am-radio-inline">
                                        <input type="radio" name="post[type]" value="1" data-am-ucheck checked>
                                        用户名
                                    </label>
                                    <label class="am-radio-inline">
                                        <input type="radio" name="post[type]" value="2" data-am-ucheck>
                                        <span>全部用户</span>
                                    </label>
                                    <label class="am-radio-inline">
                                        <input type="radio" name="post[type]" value="3" data-am-ucheck>
                                        <span>标签用户</span>
                                    </label>
                                </div>
                            </div>

                            <div class="am-form-group" id="type_1">
                                <label class="am-u-sm-3  am-u-lg-2 am-form-label "> 选择用户 </label>
                                <div class="am-u-sm-9 am-u-end">
                                    <div class="widget-become-goods am-form-file am-margin-top-xs">
                                        <button type="button"
                                                class="j-selectUser upload-file am-btn am-btn-secondary am-radius">
                                            <i class="am-icon-cloud-upload"></i> 选择用户
                                        </button>
                                        <div class="user-list uploader-list am-cf">
                                        </div>
                                        <div class="am-block">
                                            <small>选择后不可更改</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="am-form-group hide" id="type_2">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label "> 共计<?= $totalImUser; ?>人 </label>
                            </div>
                            <div class="am-form-group hide" id="type_3">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label "> 推送标签 </label>
                                <div class="am-u-sm-9 am-u-md-6 am-u-lg-5 am-u-end">
                                    <select name="post[push_tag_id]"
                                            data-am-selected="{searchBox: 1, btnSize: 'sm',
                                             placeholder:'请选择', maxHeight: 400}">
                                        <option value=""></option>
                                        <?php if (isset($tagList)): foreach ($tagList as $key => $first): ?>
                                            <option value="<?= $first['push_tag_id'] ?>"

                                            ><?= $first['name'] . '(' . $first['total_nums'] . '人)' ?></option>
                                        <?php endforeach; endif; ?>
                                    </select>
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

<!-- 图片文件列表模板 -->
<script id="tpl-user-item" type="text/template">
    {{ each $data }}
    <div class="file-item">
        <a href="{{ $value.avatarUrl }}" title="{{ $value.nickName }} (ID:{{ $value.user_id }})" target="_blank">
            <img src="{{ $value.avatarUrl }}">
            <div style="font-size: 10px;">{{ $value.nickName }}</div>
        </a>
        <input type="hidden" name="post[user_id][]" value="{{ $value.user_id }}">
    </div>
    {{ /each }}
</script>

<!-- 文件库弹窗 -->
{{include file="layouts/_template/file_library" /}}

<script src="assets/store/js/select.region.js?v=1.2"></script>
<script src="assets/store/js/select.data.js?v=<?= $version ?>"></script>
<script>

    $(function () {

        // 切换单/多规格
        $('input:radio[name="post[type]"]').change(function (e) {
            var $type_1 = $('#type_1')
                , $type_2 = $('#type_2')
                , $type_3 = $('#type_3');
            if (e.currentTarget.value === '1') {
                $type_1.show();
                $type_2.hide();
                $type_3.hide();
            } else if(e.currentTarget.value === '2') {
                $type_1.hide()
                $type_2.show();
                $type_3.hide();
            } else {
                $type_1.hide()
                $type_2.hide();
                $type_3.show();
            }
        });

        // 选择用户
        $('.j-selectUser').click(function () {
            var $userList = $('.user-list');
            $.selectData({
                title: '选择用户',
                uri: 'user/lists',
                dataIndex: 'user_id',
                done: function (data) {
                    console.log(data, 111)
                    var user = data;
                    $userList.html(template('tpl-user-item', user));
                }
            });
        });

        /**
         * 表单验证提交
         * @type {*}
         */
        $('#my-form').superForm();

    });
</script>
