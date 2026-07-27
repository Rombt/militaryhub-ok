{* Title *}
{$meta_title=$btr->general_stores scope=parent}

{*Название страницы*}
<div class="row">
    <div class="col-lg-7 col-md-7">
        <div class="wrap_heading">
            <div class="box_heading heading_page">
                {$btr->general_stores|escape}
            </div>
            <div class="box_btn_heading">
                <a class="btn btn_small btn-info" href="{url module=StoreAdmin return=$smarty.server.REQUEST_URI}">
                    {include file='svg_icon.tpl' svgId='plus'}
                    <span>{$btr->store_add|escape}</span>
                </a>
            </div>
        </div>
    </div>
</div>

{*Главная форма страницы*}
<div class="boxed fn_toggle_wrap">
    {if $stores}
        <form class="fn_form_list" method="post">
            <div class="main_list products_list fn_sort_list">
                <input type="hidden" name="session_id" value="{$smarty.session.id}">
                {*Шапка таблицы*}
                <div class="main_list_head">
                    <div class="main_list_boding main_list_drag"></div>
                    <div class="main_list_heading main_list_check">
                        <input class="hidden_check fn_check_all" type="checkbox" id="check_all_1" name="" value=""/>
                        <label class="main_ckeckbox" for="check_all_1"></label>
                    </div>
                    <div class="main_list_heading main_list_delivery_name">{$btr->general_name|escape}</div>
                    <div class="main_list_heading main_list_delivery_name">{$btr->store_address|escape}</div>
                    <div class="main_list_heading main_list_status">{$btr->general_enable|escape}</div>
                    <div class="main_list_heading main_list_close"></div>
                </div>

                {*Параметры элемента*}
                <div class="deliveries_wrap main_list_body sortable">
                    {foreach $stores as $store}
                        <div class="fn_row main_list_body_item fn_sort_item">
                            <div class="main_list_row">
                               <input type="hidden" name="positions[{$store->id}]" value="{$store->position}">

                                <div class="main_list_boding main_list_drag move_zone">
                                    {include file='svg_icon.tpl' svgId='drag_vertical'}
                                </div>

                                <div class="main_list_boding main_list_check">
                                    <input class="hidden_check" type="checkbox" id="id_{$store->id}" name="check[]" value="{$store->id}"/>
                                    <label class="main_ckeckbox" for="id_{$store->id}"></label>
                                </div>

                                <div class="main_list_boding main_list_delivery_name">
                                    <a href="{url module=StoreAdmin id=$store->id return=$smarty.server.REQUEST_URI}">
                                        {$store->name|escape}
                                    </a>
                                </div>
                                <div class="main_list_boding main_list_delivery_name">
                                    <span>
                                        {$store->address|escape}
                                    </span>
                                </div>

                                <div class="main_list_boding main_list_status">
                                    {*visible*}
                                    <label class="switch switch-default">
                                        <input class="switch-input fn_ajax_action {if $store->enabled}fn_active_class{/if}" data-module="store" data-action="enabled" data-id="{$store->id}" name="enabled" value="1" type="checkbox"  {if $store->enabled}checked=""{/if}/>
                                        <span class="switch-label"></span>
                                        <span class="switch-handle"></span>
                                    </label>
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
        <div class="heading_box mt-1">
            <div class="text_grey">{$btr->deliveries_no|escape}</div>
        </div>
    {/if}
</div>
