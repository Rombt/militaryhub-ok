{if $user->id}
    {$meta_title = $user->name|escape scope=parent}
{/if}

{*Название страницы*}
<div class="row">
    <div class="col-lg-12 col-md-12">
        <div class="wrap_heading">
            <div class="box_heading heading_page">
                {$btr->user_user|escape} {$user->name|escape}
            </div>
        </div>
    </div>
    <div class="col-md-12 col-lg-12 col-sm-12 float-xs-right"></div>
</div>

{*Вывод успешных сообщений*}
{if $message_success}
    <div class="row">
        <div class="col-lg-12 col-md-12 col-sm-12">
            <div class="boxed boxed_success">
                <div class="heading_box">
                    {if $message_success == 'updated'}
                        {$btr->user_updated|escape}
                    {elseif $message_success == 'bonuses_added'}
                        {$btr->user_bonuses_added|escape}
                    {elseif $message_success == 'bonuses_deleted'}
                        {$btr->user_bonuses_deleted|escape}
                    {else}
                        {$message_success|escape}
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
                    {if $message_error=='login_exists'}
                        {$btr->user_already_registered|escape}
                    {elseif $message_error=='empty_name'}
                        {$btr->user_name|escape}
                    {elseif $message_error=='empty_email'}
                        {$btr->user_email|escape}
                    {else}
                        {$message_error|escape}
                    {/if}
                </div>
            </div>
        </div>
    </div>
{/if}

{*Главная форма страницы*}
<form method="post" enctype="multipart/form-data" class="clearfix">
    <input type=hidden name="session_id" value="{$smarty.session.id}">
    <input type="hidden" name="lang_id" value="{$lang_id}" />

    <div class="row">
        <div class="col-md-12">
            <div class="boxed fn_toggle_wrap min_height_230px">
                <div class="heading_box">
                     {$btr->user_options|escape}
                    <div class="toggle_arrow_wrap fn_toggle_card text-primary">
                        <a class="btn-minimize" href="javascript:;" ><i class="fa fn_icon_arrow fa-angle-down"></i></a>
                    </div>
                </div>
                {*Параметры элемента*}
                <div class="toggle_body_wrap on fn_card">
                    <div class="row">
                        <div class="col-lg-6 col-md-6 col-sm-12">
                            <div class="mb-1">
                                <div class="heading_label">{$btr->index_name|escape}</div>
                                <div class="">
                                    <input class="form-control mb-h" name="name" type="text" value="{$user->name|escape}"/>
                                    <input name="id" type="hidden" value="{$user->id|escape}"/>
                                </div>
                            </div>
                            <div class="mb-1">
                                <div class="heading_label">{$btr->general_phone|escape}</div>
                                <div class="">
                                    <input class="form-control mb-h" name="phone" type="text" value="{$user->phone|escape}"/>
                                    <input name="id" type="hidden" value="{$user->id|escape}"/>
                                </div>
                            </div>
                            <div class="mb-1">
                                <div class="heading_label">{$btr->general_adress|escape}</div>
                                <div class="">
                                    <input name="address" class="form-control" type="text" value="{$user->address|escape}" />
                                </div>
                            </div>
                            <div class="mb-1">
                                <div class="heading_label">{$btr->general_birthday_date|escape}</div>
                                <div class="">
                                    {if $is_mobile || $is_tablet}
                                        <input name="birthday" class="form-control fn_date" type="date" value="{if $user->birthday}{$user->birthday}{/if}" autocomplete="off" />
                                    {else}
                                        <input name="birthday" class="form-control fn_date" type="text" value="{if $user->birthday}{$user->birthday|date}{/if}" autocomplete="off" />
                                    {/if}
                                </div>
                            </div>
                            <div class="mb-1">
                                <div class="heading_label">{$btr->general_registration_date|escape}</div>
                                <div class="">
                                    <input name="" class="form-control" type="text" disabled value="{$user->created|date}" />
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-6 col-md-6 col-sm-12">
                            <div class="mb-1">
                                <div class="heading_label">{$btr->general_group|escape}</div>
                                <div class="">
                                    <select name="group_id" class="selectpicker">
                                        <option value="0">{$btr->user_not_in_group|escape}</option>
                                        {foreach $groups as $g}
                                            <option value="{$g->id}" {if $user->group_id == $g->id}selected{/if}>{$g->name|escape}</option>
                                        {/foreach}
                                    </select>
                                </div>
                            </div>
                            <div class="mb-1">
                                <div class="heading_label">E-mail</div>
                                <div class="">
                                    <input name="email" class="form-control" type="text" value="{$user->email|escape}" />
                                </div>
                            </div>
                            <div class="mb-1">
                                <div class="heading_label">{$btr->user_last_ip|escape}</div>
                                <div class="">
                                    <input name="" class="form-control" type="text" disabled value="{$user->last_ip|escape}" />
                                </div>
                            </div>
                            <div class="mb-1">
                                <div class="heading_label">{$btr->user_bonuses|escape}</div>
                                <div class="">
                                    <input class="form-control" type="text" disabled value="{$user->bonuses|number_format:2:'.':''}" />
                                </div>
                            </div>
                            <div class="mb-1">
                                <div class="heading_label">{$btr->user_bonuses_add_delete|escape}</div>
                                <div class="input-group">
                                    <input name="bonuses" class="form-control" type="number" step="0.01" min="{-1*($user->bonuses|round)}" value="" />
                                    <span class="input-group-addon">{$btr->general_bonuses_sign}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-lg-12 col-md-12 mt-2">
                            <button type="submit" class="btn btn_small btn_blue float-md-right">
                                {include file='svg_icon.tpl' svgId='checked'}
                                <span>{$btr->general_apply|escape}</span>
                            </button>
                            <button type="button"
                                    class="fn_bonuses btn btn_small btn_blue float-md-right hidden"
                                    data-toggle="modal" data-target="#fn_action_modal"
                                    onclick="success_action($(this));">
                                {include file='svg_icon.tpl' svgId='checked'}
                                <span>{$btr->general_apply|escape}</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {*История покупок*}
    {if $orders}
        <div class="row">
            <div class="col-md-12">
                <div class="boxed fn_toggle_wrap min_height_230px">
                    <div class="heading_box">
                        {$btr->user_orders|escape}
                        <div class="toggle_arrow_wrap fn_toggle_card text-primary">
                            <a class="btn-minimize" href="javascript:;" ><i class="icon-arrow-down"></i></a>
                        </div>
                    </div>
                    <div class="toggle_body_wrap on fn_card">
                        <div class="">
                            <div class="main_list products_list">
                                <div class="main_list_head">
                                    <div class="main_list_heading main_list_user_number">№ </div>
                                    <div class="main_list_heading main_list_user_name">{$btr->general_full_name|escape}</div>
                                    <div class="main_list_heading main_list_user_date">{$btr->general_date|escape}</div>
                                    <div class="main_list_heading main_list_user_price">{$btr->coupons_order_price|escape}</div>
                                </div>
                                <div class="main_list_body">
                                    {foreach $orders as $order}
                                        <div class="fn_row main_list_body_item">
                                            <div class="main_list_row">
                                                <div class="main_list_boding main_list_user_number">
                                                    <a href="{url module=OrderAdmin id=$order->id return=$smarty.server.REQUEST_URI}">{$btr->general_order_number|escape} {$order->id}</a>
                                                </div>
                                                <div class="main_list_boding main_list_user_name">
                                                    <span>{$order->name|escape}</span>
                                                    {if $order->note}
                                                        <div class="note">{$order->note|escape}</div>
                                                    {/if}
                                                    {if $order->paid}
                                                        <div class="order_paid">
                                                            <span class="tag tag-success">{$btr->general_paid|escape}</span>
                                                        </div>
                                                    {/if}
                                                </div>
                                                <div class="main_list_boding main_list_user_date">
                                                    <div>{$order->date|date} | {$order->date|time}</div>
                                                </div>

                                                <div class="main_list_boding main_list_user_price">
                                                    <div class="input-group">
                                                        <span class="form-control">
                                                            {$order->total_price|escape}
                                                        </span>
                                                        <span class="input-group-addon">
                                                            {$currency->sign|escape}
                                                        </span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    {/foreach}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    {/if}
</form>

{literal}
    <script>
        if ($(window).width() >= 1199) {
            $(".fn_date").datepicker({
                dateFormat: 'dd.mm.yy'
            });
        }

        if ($('.fn_bonuses').size() > 0) {
            $(document).on('submit', 'form', function () {
                let confirm_text = '';
                let bonuses = $(this).find('input[name*=bonuses]').val();
                if (bonuses > 0) {
                    confirm_text = `{/literal}{$btr->user_bonuses_action_add_1|escape}{literal} ${bonuses} {/literal}{$btr->user_bonuses_action_add_2|escape}{literal} "{/literal}{$user->name|escape}{literal}".`;
                } else if (bonuses < 0) {
                    confirm_text = `{/literal}{$btr->user_bonuses_action_delete_1|escape}{literal} ${bonuses} {/literal}{$btr->user_bonuses_action_delete_2|escape}{literal} "{/literal}{$user->name|escape}{literal}".`;
                }
                let modal = $('#fn_action_modal');
                if (bonuses.length > 0 && modal.find('.modal-body .fn_confirm').length === 0) {
                    modal.find('.modal-body').prepend(`<div class="fn_confirm mb-2">${confirm_text}</div>`);
                    $('.fn_bonuses').trigger('click');
                    return false;
                }
            });
            function success_action(el) {
                $(document).on('click', '.fn_submit_delete', function (e) {
                    e.preventDefault();

                    let form = el.closest('form');
                    form.closest('form').submit();
                });
                $(document).on('click', '.fn_dismiss_delete', function (e) {
                    e.preventDefault();

                    let modal = $('#fn_action_modal');
                    modal.find('.modal-body .fn_confirm').remove();
                });
            }
        }
    </script>
{/literal}