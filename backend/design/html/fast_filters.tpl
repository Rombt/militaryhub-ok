{* Title *}
{$meta_title = $btr->general_fastfilters scope=parent}

{*Название страницы*}
<div class="row">
    <div class="col-lg-7 col-md-7">
        <div class="wrap_heading">
            <div class="box_heading heading_page">
                {$btr->general_fastfilters|escape}
            </div>
            <div class="box_btn_heading">
                <a class="btn btn_small btn-info" href="{url module=FastFilterAdmin}">
                    {include file='svg_icon.tpl' svgId='plus'}
                    <span>{$btr->fastfilters_add|escape}</span>
                </a>
            </div>
        </div>
    </div>
</div>

{*Вывод ошибок*}
{if $message_error}
    {*<div class="row">
        <div class="col-lg-12 col-md-12 col-sm-12">
            <div class="boxed boxed_warning">
                <div class="heading_box">
                    {if $message_error == 'url_system'}
                        {$btr->pages_delete_error_url|escape}
                    {else}
                        {$message_error|escape}
                    {/if}
                </div>
            </div>
        </div>
    </div>*}
{/if}

{*Главная форма страницы*}
<div class="boxed fn_toggle_wrap">
    {if $fast_filters}
        <div class="row">
            <div class="col-lg-12 col-md-12 col-sm-12">
                <form id="list_form" method="post" class="fn_form_list fn_fast_button">
                    <input type="hidden" name="session_id" value="{$smarty.session.id}">

                    <div class="pages_wrap main_list">
                        {*Шапка таблицы*}
                        <div class="main_list_head">
                            <div class="main_list_boding main_list_drag"></div>
                            <div class="main_list_heading main_list_check">
                                <input class="hidden_check fn_check_all" type="checkbox" id="check_all_1" name="" value=""/>
                                <label class="main_ckeckbox" for="check_all_1"></label>
                            </div>
                            <div class="main_list_heading main_list_page_name">{$btr->fastfilters_name|escape}</div>
                            <div class="main_list_heading main_list_pages_group"></div>
                            <div class="main_list_heading main_list_setting main_list_pages_setting">{$btr->general_activities|escape}</div>
                            <div class="main_list_heading main_list_close"></div>
                        </div>

                        {*Параметры элемента*}
                        <div id="sortable" class="main_list_body sortable">
                            {foreach $fast_filters as $fast_filter}
                                <div class="fn_row main_list_body_item">
                                    <div class="main_list_row">
                                        <input type="hidden" name="positions[{$fast_filter->id}]" value="{$fast_filter->id}">

                                        <div class="main_list_boding main_list_drag move_zone">
                                            {include file='svg_icon.tpl' svgId='drag_vertical'}
                                        </div>

                                        <div class="main_list_boding main_list_check">
                                            <input class="hidden_check" type="checkbox" id="id_{$fast_filter->id}" name="check[]" value="{$fast_filter->id}"/>
                                            <label class="main_ckeckbox" for="id_{$fast_filter->id}"></label>
                                        </div>

                                        <div class="main_list_boding main_list_page_name">
                                            <a href="{url module=FastFilterAdmin id=$fast_filter->id return=$smarty.server.REQUEST_URI}">
                                                {$fast_filter->url|escape}
                                            </a>
                                        </div>

                                        <div class="main_list_boding main_list_pages_group">
                                        </div>

                                        <div class="main_list_setting main_list_pages_setting">
                                            {*open*}
                                            <a href="../{$lang_link}{$fast_filter->url|escape}" target="_blank" data-hint="{$btr->general_view|escape}" class="setting_icon setting_icon_open hint-bottom-middle-t-info-s-small-mobile  hint-anim">
                                                {include file='svg_icon.tpl' svgId='icon_desktop'}
                                            </a>
                                        </div>

                                        <div class="main_list_boding main_list_close">
                                            {*delete*}
                                            <button data-hint="{$btr->fastfilters_delete|escape}" type="button" class="btn_close fn_remove hint-bottom-right-t-info-s-small-mobile  hint-anim" data-toggle="modal" data-target="#fn_action_modal" onclick="success_action($(this));">
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
                                <div class="main_list_boding main_list_drag"></div>
                                <div class="main_list_heading main_list_check">
                                    <input class="hidden_check fn_check_all" type="checkbox" id="check_all_2" name="" value=""/>
                                    <label class="main_ckeckbox" for="check_all_2"></label>
                                </div>
                                <div class="main_list_option">
                                    <select name="action" class="selectpicker">
                                        <option value="enable">{$btr->general_do_enable|escape}</option>
                                        <option value="disable">{$btr->general_do_disable|escape}</option>
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
            </div>
        </div>
    {else}
        <div class="heading_box mt-1">
            <div class="text_grey">{$btr->fastfilters_no|escape}</div>
        </div>
    {/if}
</div>