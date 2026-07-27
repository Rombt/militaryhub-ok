{* Title *}
{$meta_title = $btr->referrals_referrals scope=parent}

{*Название страницы*}
<div class="row">
    <div class="col-lg-12 col-md-12">
        <div class="wrap_heading">
            <div class="box_heading heading_page">
                {if $items_count}
                    {$btr->referrals_referrals} - {$items_count|round}
                {/if}
            </div>
            <div class="box_btn_heading fn_add_item">
                <button class="btn btn_small btn-info">
                    {include file='svg_icon.tpl' svgId='plus'}
                    <span>{$btr->referrals_add|escape}</span>
                </button>
            </div>
        </div>
    </div>
</div>

{*Вывод успешных сообщений*}
{if $message_success}
    <div class="row">
        <div class="col-lg-12 col-md-12 col-sm-12">
            <div class="boxed boxed_success">
                <div class="heading_box">
                    {if $message_success == 'added'}
                        {$btr->referrals_added|escape}
                    {elseif $message_success == 'updated'}
                        {$btr->referrals_update|escape}
                    {/if}
                    {if $smarty.get.return}
                        <a class="btn btn_return float-xs-right" href="{$smarty.get.return}">
                            {include file='svg_icon.tpl' svgId='return'}
                            <span>{$btr->general_back|escape}</span>
                        </a>
                    {/if}
                </div>
            </div>
        </div>
    </div>
{/if}

{*Вывод ошибок*}
{if $message_error}
    <div class="row">
        <div class="col-lg-12 col-md-12 col-sm-12">
            <div class="boxed boxed_warning">
                <div class="heading_box">
                    {if $message_error == 'code_exists'}
                        {$btr->referrals_exists|escape}
                    {elseif $message_error=='empty_code'}
                        {$btr->referrals_enter_code|escape}
                    {else}
                        {$message_error|escape}
                    {/if}
                    {if $smarty.get.return}
                        <a class="btn btn_return float-xs-right" href="{$smarty.get.return}">
                            {include file='svg_icon.tpl' svgId='return'}
                            <span>{$btr->general_back|escape}</span>
                        </a>
                    {/if}
                </div>
            </div>
        </div>
    </div>
{/if}

{*Главная форма страницы*}
<div class="boxed fn_toggle_wrap">
    <div class="row">
        <div class="col-lg-12 col-md-12 col-sm-12">
            {if $items}
                <form class="fn_form_list" method="post">
                    <input type="hidden" name="session_id" value="{$smarty.session.id}">
                    <div class="main_list products_list fn_sort_list">
                        {*Шапка таблицы*}
                        <div class="main_list_head">
                            <div class="main_list_heading main_list_check">
                                <input class="hidden_check fn_check_all" type="checkbox" id="check_all_1" name="" value=""/>
                                <label class="main_ckeckbox" for="check_all_1"></label>
                            </div>
                            <div class="main_list_heading main_list_referral_name">{$btr->referrals_name|escape}</div>
                            <div class="main_list_heading main_list_referral_code">{$btr->referrals_code|escape}</div>
                            <div class="main_list_heading main_list_setting main_list_products_setting">{$btr->general_activities|escape}</div>
                            <div class="main_list_heading main_list_referral_sale">{$btr->referrals_discount|escape}</div>
                            <div class="main_list_heading main_list_referral_validity">{$btr->referrals_terms|escape}</div>
                            <div class="main_list_heading main_list_referral_disposable">{$btr->referrals_one_off|escape}</div>
                            <div class="main_list_heading main_list_referral_count">{$btr->referrals_qty_uses|escape}</div>
                            <div class="main_list_heading main_list_close"></div>
                        </div>
                        {*Блок добавления нового элемента*}
                        <div class="main_list_body fn_new_item">
                            <div class="main_list_body_item">
                                <div class="main_list_row ">
                                    <div class="main_list_heading main_list_check"></div>
                                    <div class="main_list_boding main_list_referral_name">
                                        <input class="form-control" name="new_name" type="text" value="" placeholder="{$btr->referrals_enter_name|escape}"/>
                                        <input name="new_id" type="hidden" value=""/>
                                    </div>
                                    <div class="main_list_boding main_list_referral_code">
                                        <div class="input-group">
                                            <span class="input-group-addon">/r/</span>
                                            <input class="form-control fn_url" name="new_code" type="text" value="" placeholder="{$btr->referrals_enter_code|escape}"/>
                                        </div>
                                    </div>
                                    <div class="main_list_heading main_list_setting main_list_products_setting"></div>
                                    <div class="main_list_boding main_list_referral_sale">
                                        <div class="input-group">
                                            <input class="form-control" name="new_value" type="text" value="" />
                                            <select class="selectpicker form-control" name="new_type">
                                                <option value="percentage">%</option>
                                                <option value="absolute">{$currency->sign}</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="main_list_boding main_list_referral_validity">
                                        <div class="input-group">
                                            <input class="form-control" type=text name="new_expire" value="">
                                            <div class="input-group-addon">
                                                <i class="fa fa-calendar"></i>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="main_list_boding main_list_referral_disposable">
                                        <input class="hidden_check" type="checkbox" name="new_single" id="single" value="1" />
                                        <label class="main_ckeckbox" for="single"></label>
                                    </div>
                                    <div class="main_list_heading main_list_referral_count"></div>
                                    <div class="main_list_heading main_list_close"></div>
                                </div>
                            </div>
                        </div>

                        {*Параметры элемента*}
                        <div class="main_list_body fn_coupon_wrap">
                            {foreach $items as $item}
                                <div class="fn_row main_list_body_item">
                                    <div class="main_list_row ">
                                        <div class="main_list_boding main_list_check">
                                            <input class="hidden_check" type="checkbox" id="id_{$item->id}" name="check[]" value="{$item->id}"/>
                                            <label class="main_ckeckbox" for="id_{$item->id}"></label>
                                        </div>
                                        <div class="main_list_boding main_list_referral_name">
                                            <span class="text_dark">
                                                {$item->name}
                                            </span>
                                            <div class="hidden-lg-up mt-q">
                                                {if $item->expire}
                                                    {if $smarty.now|date_format:'%Y%m%d' <= $item->expire|date_format:'%Y%m%d'}
                                                        <span class="tag tag-primary">
                                                            {$btr->referrals_valid_until|escape} {$item->expire|date}
                                                        </span>
                                                    {else}
                                                        <span class="tag tag-danger">
                                                            {$btr->referrals_expired|escape} {$item->expire|date}
                                                        </span>
                                                    {/if}
                                                {else}
                                                    <span class="tag tag-warning">
                                                        {include file='svg_icon.tpl' svgId='infinity'}
                                                    </span>
                                                {/if}
                                                <div class="mt-q">
                                                    {if $item->single}
                                                        {$btr->referrals_one_off|escape}
                                                    {else}
                                                        {$btr->referrals_many|escape}
                                                    {/if}
                                                </div>

                                            </div>
                                        </div>
                                        <div class="main_list_boding main_list_referral_code">
                                            <div class="input-group">
                                                <span class="input-group-addon">/r/</span>
                                                <input class="form-control fn_url fn_disabled" readonly type="text" value="{$item->code|escape}" />
                                            </div>
                                        </div>
                                        <div class="main_list_setting main_list_products_setting">
                                            <button data-hint="{$btr->general_copy|escape}" type="button" class="fn_copy setting_icon hint-bottom-middle-t-info-s-small-mobile hint-anim" data-url="{$config->root_url}/r/{$item->code|escape}">
                                                {include file='svg_icon.tpl' svgId='icon_copy'}
                                            </button>
                                        </div>
                                        <div class="main_list_boding main_list_referral_sale">
                                            {$item->value*1}
                                            {if $item->type=='absolute'}
                                                {$currency->sign|escape}
                                            {else}
                                                %
                                            {/if}
                                        </div>
                                        <div class="main_list_boding main_list_referral_validity">
                                            <div class="">
                                                {if $item->expire}
                                                    {if $smarty.now|date_format:'%Y%m%d' <= $item->expire|date_format:'%Y%m%d'}
                                                        {$btr->referrals_valid_until|escape} {$item->expire|date}
                                                    {else}
                                                        {$btr->referrals_expired|escape} {$item->expire|date}
                                                    {/if}
                                                {else}
                                                    {include file='svg_icon.tpl' svgId='infinity'}
                                                {/if}
                                            </div>
                                        </div>
                                        <div class="main_list_boding main_list_referral_disposable">
                                            {if $item->single}
                                                {$btr->referrals_yes|escape}
                                            {else}
                                                {$btr->referrals_no|escape}
                                            {/if}
                                        </div>
                                        <div class="main_list_boding main_list_referral_count">
                                            {if $item->usages>0}
                                                {$item->usages|escape}
                                            {else}
                                                0
                                            {/if}
                                        </div>
                                        <div class="main_list_boding main_list_close">
                                            {*delete*}
                                            <button data-hint="{$btr->referrals_delete|escape}" type="button" class="btn_close fn_remove hint-bottom-right-t-info-s-small-mobile  hint-anim" data-toggle="modal" data-target="#fn_action_modal" onclick="success_action($(this));">
                                                {include file='svg_icon.tpl' svgId='delete'}
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            {/foreach}
                        </div>

                        {*Блок массовых действий*}
                        <div class="main_list_footer fn_action_block">
                            <div class="main_list_foot_left">
                                <div class="main_list_heading main_list_check">
                                    <input class="hidden_check fn_check_all" type="checkbox" id="check_all_2" name="" value=""/>
                                    <label class="main_ckeckbox" for="check_all_2"></label>
                                </div>
                                <div class="main_list_option">
                                    <select name="action" class="selectpicker">
                                        <option value="">-</option>
                                        <option value="delete">{$btr->general_delete|escape}</option>
                                    </select>
                                </div>
                            </div>
                            <button type="submit" class="btn btn_small btn_blue">
                                {include file='svg_icon.tpl' svgId='checked'}
                                <span>{$btr->general_apply|escape}</span>
                            </button>
                        </div>
                    </div>
                </form>
            {else}
                {*Главная форма страницы*}
                <form method="post" class="clearfix">
                    <input type="hidden" name="session_id" value="{$smarty.session.id}">

                    <div class="main_list products_list fn_sort_list">
                        {*Шапка таблицы*}
                        <div class="main_list_head">
                            <div class="main_list_heading main_list_check"></div>
                            <div class="main_list_heading main_list_referral_name">{$btr->referrals_name|escape}</div>
                            <div class="main_list_heading main_list_referral_code">{$btr->referrals_code|escape}</div>
                            {* <div class="main_list_heading main_list_referral_sale">{$btr->referrals_discount|escape}</div> *}
                            <div class="main_list_heading main_list_setting main_list_products_setting">{$btr->general_activities|escape}</div>
                            <div class="main_list_heading main_list_referral_validity">{$btr->referrals_terms|escape}</div>
                            <div class="main_list_heading main_list_referral_disposable">{$btr->referrals_one_off|escape}</div>
                            <div class="main_list_heading main_list_referral_count">{$btr->referrals_qty_uses|escape}</div>
                            <div class="main_list_heading main_list_close"></div>
                        </div>
                        {*Параметры элемента*}
                        <div class="main_list_body">
                            <div class="main_list_body_item">
                                <div class="main_list_row ">
                                    <div class="main_list_heading main_list_check"></div>
                                    <div class="main_list_boding main_list_referral_name">
                                        <input class="form-control" name="new_name" type="text" value="" placeholder="{$btr->referrals_enter_name|escape}"/>
                                        <input name="new_id" type="hidden" value=""/>
                                    </div>
                                    <div class="main_list_boding main_list_referral_code">
                                        <div class="input-group">
                                            <span class="input-group-addon">/r/</span>
                                            <input class="form-control fn_url" name="new_code" type="text" value="" placeholder="{$btr->referrals_enter_code|escape}"/>
                                        </div>
                                    </div>
                                    <div class="main_list_heading main_list_setting main_list_products_setting"></div>
                                    {* <div class="main_list_boding main_list_referral_sale">
                                        <div class="input-group">
                                            <input class="form-control" name="new_value" type="text" value="" />
                                            <select class="selectpicker form-control" name="new_type">
                                                <option value="percentage">%</option>
                                                <option value="absolute">{$currency->sign}</option>
                                            </select>
                                        </div>
                                    </div> *}
                                    <div class="main_list_boding main_list_referral_validity">

                                        <div class="input-group">
                                            <input class="form-control" type=text name="new_expire" value="">
                                            <div class="input-group-addon">
                                                <i class="fa fa-calendar"></i>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="main_list_boding main_list_referral_disposable">
                                        <input class="hidden_check" type="checkbox" name="new_single" id="single" value="1" />
                                        <label class="main_ckeckbox" for="single"></label>
                                    </div>
                                    <div class="main_list_heading main_list_referral_count"></div>
                                    <div class="main_list_heading main_list_close"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                    <div class="col-lg-12 col-md-12 mt-1">
                            <button type="submit" class="btn btn_small btn_blue float-md-right">
                                {include file='svg_icon.tpl' svgId='checked'}
                                <span>{$btr->general_apply|escape}</span>
                            </button>
                        </div>
                    </div>
                </form>
                <script>
                    $('input[name="new_expire"]').datepicker();
                </script>
            {/if}
        </div>
        <div class="col-lg-12 col-md-12 col-sm 12 txt_center">
            {include file='pagination.tpl'}
        </div>
    </div>
</div>

{literal}
    <script>
        $(function() {
            var new_item = $(".fn_new_item").clone(true);
            $(".fn_new_item").remove();

            $(document).on("click", ".fn_add_item", function () {
                $(this).remove();
                new_item.find("select").selectpicker();
                new_item.find('input[name="new_expire"]').datepicker();
                $(".fn_coupon_wrap").prepend(new_item);
            });

            $(document).on('click', '.fn_copy', function(e) {
                e.preventDefault();

                let text = $(this).data('url');
                let temp = $("<input>");
                $("body").append(temp);
                temp.val(text).select();
                document.execCommand("copy");
                temp.remove();

                toastr.success(text, "Copied!");
            });
        });
    </script>
{/literal}
