{* Title *}
{$meta_title=$btr->general_rozetka_categories scope=parent}

{*Название страницы*}
<div class="row">
    <div class="col-lg-7 col-md-7">
        <div class="wrap_heading">
            <div class="box_heading heading_page">
                {$btr->general_rozetka_categories|escape}
            </div>
        </div>
    </div>
</div>

{*Главная форма страницы*}
<div class="boxed fn_toggle_wrap">
    {if $features_values}
        <form method="post" class="fn_form_list fn_fast_button">
            <input type="hidden" name="session_id" value="{$smarty.session.id}" />
            <div class="main_list products_list fn_sort_list">
                {*Шапка таблицы*}
                <div class="main_list_head">
                    <div class="main_list_heading main_list_drag"></div>
                    <div class="main_list_heading main_list_subicon">ID</div>
                    <div class="main_list_heading main_list_categories_name">{$btr->general_name|escape}</div>
                    <div class="main_list_heading main_list_categories_name">{$btr->category_rozetka_xml_name|escape}</div>
                    {* <div class="main_list_heading main_list_status">{$btr->general_enable|escape}</div> *}
                    {* <div class="main_list_heading main_list_setting">{$btr->general_activities|escape}</div> *}
                    <div class="main_list_heading main_list_close"></div>
                </div>

                {*Параметры элемента*}
                <div class="main_list_body categories_wrap sortable ">
                {if $features_values}
                    {foreach $features_values as $category}
                        <div class="fn_row main_list_body_item fn_sort_item">
                            <div class="main_list_row">
                                <div class="main_list_boding main_list_drag move_zone">
                                    {* {include file='svg_icon.tpl' svgId='drag_vertical'} *}
                                </div>
                                
                                <div class="main_list_heading main_list_subicon">
                                    <span>{$category->id}</span>
                                </div>

                                <div class="main_list_boding main_list_categories_name">
                                    <input class="form-control" type="hidden" name="categories[id][{$category->id}]" value="{$category->id}" />
                                    <input class="form-control" type="hidden" name="categories[translit][{$category->id}]" value="{$category->translit}" />
                                    {$category->value|escape}
                                </div>

                                <div class="main_list_boding main_list_categories_name">
                                    <div class="input-group rozetka_list">
                                        <input type="text" class="input_autocomplete rozetka form-control fn_url{if $category->rozetka_name} fn_disabled{/if}" {if $category->rozetka_name} readonly{/if} name="categories[rozetka_name][{$category->id}]" value="{$category->rozetka_name|escape}" placeholder="{$btr->category_select|escape}"/>
                                        <span class="input-group-addon fn_disable_url">
                                            {if $category->rozetka_name}
                                                <i class="fa fa-lock"></i>
                                            {else}
                                                <i class="fa fa-lock fa-unlock"></i>
                                            {/if}
                                        </span>
                                    </div>
                                </div>

                                {* <div class="main_list_boding main_list_status"> *}
                                    {*visible*}
                                    {* <label class="switch switch-default">
                                        <input class="switch-input fn_ajax_action {if $category->visible}fn_active_class{/if}" data-module="features_values" data-action="visible" data-id="{$category->id}" name="visible" value="1" type="checkbox"  {if $category->visible}checked=""{/if}/>
                                        <span class="switch-label"></span>
                                        <span class="switch-handle"></span>
                                    </label> *}
                                {* </div> *}

                                {* <div class=" main_list_setting"> *}
                                    {*open*}
                                    {* <a href="../{$lang_link}catalog/{$category->url|escape}" target="_blank" data-hint="{$btr->general_view|escape}" class="setting_icon setting_icon_open hint-bottom-middle-t-info-s-small-mobile  hint-anim">
                                        {include file='svg_icon.tpl' svgId='icon_desktop'}
                                    </a> *}
                                {* </div> *}
                                <div class="main_list_boding main_list_close">
                                    {*delete*}
                                    {* <button data-hint="{$btr->categories_delete|escape}" type="button" class="btn_close fn_remove hint-bottom-right-t-info-s-small-mobile  hint-anim" data-toggle="modal" data-target="#fn_action_modal" onclick="success_action($(this));">
                                        {include file='svg_icon.tpl' svgId='delete'}
                                    </button> *}
                                </div>
                            </div>
                        </div>
                    {/foreach}
                {/if}
                </div>

                {*Блок массовых действий*}
                <div class="main_list_footer fn_action_block">
                    <div class="main_list_foot_left">
                        <div class="main_list_heading main_list_subicon"></div>
                        <div class="main_list_heading main_list_drag"></div>
                        {* <div class="main_list_heading main_list_check">
                            <input class="hidden_check fn_check_all" type="checkbox" id="check_all_2" name="" value=""/>
                            <label class="main_ckeckbox" for="check_all_2"></label>
                        </div> *}
                        {* <div class="main_list_option">
                            <select name="action" class="selectpicker">
                                <option value="">-</option>
                            </select>
                        </div> *}
                    </div>
                    <button type="submit" class="btn btn_small btn_blue">
                        {include file='svg_icon.tpl' svgId='checked'}
                        <span>{$btr->general_apply|escape}</span>
                    </button>
                </div>
            </div>
        </form>
        <div class="row">
            <div class="col-lg-12 col-md-12 col-sm 12 txt_center">
                {include file='pagination.tpl'}
            </div>
        </div>
    {else}
        <div class="heading_box mt-1">
            <div class="text_grey">{$btr->categories_no|escape}</div>
        </div>
    {/if}
</div>

<div class="row">
    <div class="col-lg-12 col-md-12 col-sm-12">
        <div class="boxed boxed_attention">
            <div class="">
               {$btr->rozetka_categories_message|escape}
            </div>
        </div>
    </div>
</div>

<script src="design/js/autocomplete/jquery.autocomplete-min.js"></script>
{literal}
<script>
$(function() {
    $('.input_autocomplete.rozetka').devbridgeAutocomplete({
        serviceUrl:'ajax/market.php?module=search_rozetka&session_id={/literal}{$smarty.session.id}{literal}',
        minChars:1,
        noCache: false,
        onSelect:
            function(suggestions) {
                $(this).closest('div').find('input[name*="rozetka_name"]').val(suggestions.data.name);
            }
    });
    $(".input_autocomplete").trigger('click');
});
</script>
{/literal}
