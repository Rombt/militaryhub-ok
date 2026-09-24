{if $category->id}
    {$meta_title = $category->name scope=parent}
{else}
    {$meta_title = $btr->category_new  scope=parent}
{/if}

{*Название страницы*}
<div class="row">
    <div class="col-lg-12 col-md-12">
        <div class="wrap_heading">
            <div class="box_heading heading_page">
                {if !$category->id}
                    {$btr->category_add|escape}
                {else}
                    {$category->name|escape}
                {/if}
            </div>
            {if $category->id}
                <div class="box_btn_heading">
                    <a class="btn btn_small btn-info add" target="_blank" href="../{$lang_link}catalog/{$category->url}">
                        {include file='svg_icon.tpl' svgId='icon_desktop'}
                        <span>{$btr->general_open|escape}</span>
                    </a>
                </div>
            {/if}
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
                    {if $message_success=='added'}
                        {$btr->category_added|escape}
                    {elseif $message_success=='updated'}
                        {$btr->category_updated|escape}
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
                    {if $message_error=='url_exists'}
                        {$btr->category_exists|escape}
                    {elseif $message_error == 'empty_name'}
                        {$btr->general_enter_title|escape}
                    {elseif $message_error == 'empty_url'}
                        {$btr->general_enter_url|escape}
                    {elseif $message_error == 'url_wrong'}
                        {$btr->general_not_underscore|escape}
                    {else}
                        {$message_error|escape}
                    {/if}
                </div>
            </div>
        </div>
    </div>
{/if}

{*Главная форма страницы*}
<form method="post" enctype="multipart/form-data" class="fn_fast_button">
    <input type=hidden name="session_id" value="{$smarty.session.id}">
    <input type="hidden" name="lang_id" value="{$lang_id}" />

    <div class="row">
        <div class="col-xs-12">
            <div class="boxed match_matchHeight_true">
                {*Название элемента сайта*}
                <div class="row d_flex">
                    <div class="col-lg-10 col-md-9 col-sm-12">
                        <div class="heading_label">
                            {$btr->general_name|escape}
                        </div>
                        <div class="form-group">
                            <input class="form-control" name="name" type="text" value="{$category->name|escape}"/>
                            <input name="id" type="hidden" value="{$category->id|escape}"/>
                        </div>
                        <div class="row">
                            <div class="col-xs-12 col-lg-6 col-md-10">
                                <label class="heading_label">URL</label>
                                <div class="">
                                    <div class="input-group">
                                        <span class="input-group-addon">URL</span>
                                        <input name="url" class="fn_meta_field form-control fn_url {if $category->id}fn_disabled{/if}" {if $category->id}readonly=""{/if} type="text" value="{$category->url|escape}" />
                                        <input type="checkbox" id="block_translit" class="hidden" value="1" {if $category->id}checked=""{/if}>
                                        <span class="input-group-addon fn_disable_url">
                                            {if $category->id}
                                                <i class="fa fa-lock"></i>
                                            {else}
                                                <i class="fa fa-lock fa-unlock"></i>
                                            {/if}
                                        </span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-xs-12 col-lg-6 col-md-10">
                                <div class="">
                                    <label class="heading_label">{$btr->catalog_parent_menu_name}</label>
                                    <div>
                                        <input name="parent_menu_name" class="form-control" type="text" value="{$category->parent_menu_name|escape}" />
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-2 col-md-3 col-sm-12">
                        <div class="activity_of_switch">
                            <div class="activity_of_switch_item"> {* row block *}
                                <div class="main_switch clearfix">
                                    <label class="switch_label">{$btr->general_enable|escape}</label>
                                    <label class="switch switch-default">
                                        <input class="switch-input" name="visible" value='1' type="checkbox" {if $category->visible}checked=""{/if}/>
                                        <span class="switch-label"></span>
                                        <span class="switch-handle"></span>
                                    </label>
                                </div>
                            </div>
                            <div class="activity_of_switch_item"> {* row block *}
                                <div class="main_switch clearfix">
                                    <label class="switch_label">{$btr->general_featured_category|escape}</label>
                                    <label class="switch switch-default">
                                        <input class="switch-input" name="featured" value='1' type="checkbox" {if $category->featured}checked=""{/if}/>
                                        <span class="switch-label"></span>
                                        <span class="switch-handle"></span>
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {*Параметры элемента*}
    <div class="row">
        <div class="col-lg-4 col-md-12 pr-0 hidden-sm-down">
            <div class="boxed fn_toggle_wrap min_height_230px">
                <div class="heading_box">
                    {$btr->general_image|escape}
                    <div class="toggle_arrow_wrap fn_toggle_card text-primary">
                        <a class="btn-minimize" href="javascript:;" ><i class="fa fn_icon_arrow fa-angle-down"></i></a>
                    </div>
                </div>
                <div class="toggle_body_wrap on fn_card">
                    <ul class="category_images_list">
                        <li class="category_image_item">
                            {if $category->image}
                            <input type="hidden" class="fn_accept_delete" name="delete_image" value="">
                                <div class="fn_parent_image">
                                    <div class="category_image image_wrapper fn_image_wrapper text-xs-center">
                                        <a href="javascript:;" class="fn_delete_item remove_image"></a>
                                        <img src="{$category->image|resize:300:120:false:$config->resized_categories_dir}" alt="" />
                                    </div>
                                </div>
                            {else}
                                <div class="fn_parent_image"></div>
                            {/if}
                            <div class="fn_upload_image dropzone_block_image {if $category->image} hidden{/if}">
                                <i class="fa fa-plus font-5xl" aria-hidden="true"></i>
                                <input class="dropzone_image" name="image" type="file" />
                            </div>
                            <div class="category_image image_wrapper fn_image_wrapper fn_new_image text-xs-center">
                                <a href="javascript:;" class="fn_delete_item remove_image"></a>
                                <img src="" alt="" />
                            </div>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
        <div class="col-lg-8 col-md-12">
            <div class="boxed fn_toggle_wrap min_height_230px">
                <div class="heading_box">
                    {$btr->category_parameters|escape}
                    <div class="toggle_arrow_wrap fn_toggle_card text-primary">
                        <a class="btn-minimize" href="javascript:;" ><i class="fa fn_icon_arrow fa-angle-down"></i></a>
                    </div>
                </div>
                <div class="row">
                    <div class="col-lg-12 toggle_body_wrap on fn_card">
                        <div class="row">
                            <div class="col-lg-6 pr-0">
                                <div class="form-group clearfix">
                                    <label class="heading_label">{$btr->category_h1|escape}</label>
                                    <div>
                                        <input name="name_h1" class="form-control" type="text" value="{$category->name_h1|escape}" />
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-6 pr-0">
                                <div class="form-group clearfix yandex_list">
                                    <label class="heading_label">{$btr->category_xml_name|escape}</label>
                                    <div>
                                        <input type="text" class="input_autocomplete yandex form-control" name="yandex_name" value="{$category->yandex_name|escape}" placeholder="{$btr->category_select|escape}"/>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div id="product_categories">
                            <div class="heading_box">{$btr->general_category|escape}</div>
                            <select name="parent_id" class="selectpicker mb-1" data-live-search="true" data-size="10">
                                <option value='0'>{$btr->category_root|escape}</option>
                                {function name=category_select level=0}
                                    {foreach $cats as $cat}
                                        {if $category->id != $cat->id}
                                            <option value='{$cat->id}' {if $category->parent_id == $cat->id}selected{/if}>{section name=sp loop=$level}--{/section}{$cat->name}</option>
                                            {category_select cats=$cat->subcategories level=$level+1}
                                        {/if}
                                    {/foreach}
                                {/function}
                                {category_select cats=$categories}
                            </select>
                        </div>

                        <div class="row">
                            <div class="col-lg-12">
                                <div class="form-group clearfix">
                                    <label class="heading_box" for="attach_brand_id">{$btr->category_attach_brand|escape}</label>
                                    <select name="attach_brand_id" id="attach_brand_id"  class="selectpicker mb-1" data-live-search="true" data-size="10">
                                        <option value='0'>{$btr->category_attach_brand_no|escape}</option>
                                        {foreach $products_types as $type}
                                            <option value="{$type->url}"{if $category->attach_brand_id == $type->url} selected{/if}>{$type->name}</option>
                                        {/foreach}
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-12 col-md-12">
            <div class="boxed fn_toggle_wrap min_height_230px">
                <div class="heading_box">
                    {$btr->category_parameters|escape}
                    <div class="toggle_arrow_wrap fn_toggle_card text-primary">
                        <a class="btn-minimize" href="javascript:;" ><i class="fa fn_icon_arrow fa-angle-down"></i></a>
                    </div>
                </div>
                <div class="row">
                    <div class="col-lg-12 toggle_body_wrap on fn_card">
                        <div class="row">
                            <div class="col-lg-6">
                                <div class="form-group clearfix rozetka_list {if !$category->rozetka_category_value_id || !$rozetka_feature_value_valid}has_error{/if}">
                                    <label class="heading_label">{$btr->category_rozetka_xml_name|escape}</label>
                                    {if $category->rozetka_category_value_id && $rozetka_feature_value_valid}
                                        {$category_rozetka_tooltip = {$btr->category_rozetka_feature_value_right|escape|default:'Right value'}}
                                    {elseif $category->rozetka_category_value_id}
                                        {$category_rozetka_tooltip = {$btr->category_rozetka_feature_value_wrong|escape|default:'Need to update'}}
                                    {else}
                                        {$category_rozetka_tooltip = {$btr->category_rozetka_feature_value_empty|escape|default:'Need to add feature value'}}
                                    {/if}
                                    <div class="input-group" title="{$category_rozetka_tooltip}">
                                        <input type="hidden" class="rozetka_category_value_id" name="rozetka_category_value_id" value="{$category->rozetka_category_value_id}"/>
                                        <input type="text" class="input_autocomplete rozetka form-control" name="rozetka_name" value="{$category->rozetka_name|escape}" placeholder="{$btr->category_select|escape}"/>
                                        <div class="input-group-addon">
                                            {if $category->rozetka_category_value_id && $rozetka_feature_value_valid}
                                                <i class="fa fa-plus font-2xl text-success"></i>
                                            {elseif $category->rozetka_category_value_id}
                                                <i class="fa fa-times font-2xl text_warning"></i>
                                            {else}
                                                <i class="fa fa-times font-2xl text_warning"></i>
                                            {/if}
                                        </div>
                                    </div>
                                    {if $category->rozetka_category_value_id}
                                        <div class="mt-1">
                                            <a target="_blank" href="{url module=RozetkaCategoriesAdmin feature_value_id=$category->rozetka_category_value_id}">{$btr->category_rozetka_feature_value_link|escape|default:'Link to feature'}</a>
                                        </div>
                                    {/if}
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="activity_of_switch_item"> {* row block *}
                                    <div class="main_switch clearfix">
                                        <label class="switch_label">{$btr->category_rozetka_exclude|escape}</label>
                                        <label class="switch switch-default">
                                            <input class="switch-input" name="rozetka_exclude" value='1' type="checkbox" {if $category->rozetka_exclude}checked=""{/if}/>
                                            <span class="switch-label"></span>
                                            <span class="switch-handle"></span>
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-lg-12 toggle_body_wrap on fn_card">
                        <div class="row">
                            <div class="col-lg-6 pr-0">
                                <div class="form-group clearfix google_list">
                                    <label class="heading_label">{$btr->category_google_xml_name|escape}Категория для Google</label>
                                    <div>
                                        <input type="text" class="input_autocomplete google form-control" name="google_name" value="{$category->google_name|escape}" placeholder="{$btr->category_select|escape}"/>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-lg-12 toggle_body_wrap on fn_card">
                        <div class="row">
                            <div class="col-lg-6">
                                <div class="form-group clearfix prom_list">
                                    <label class="heading_label">{$btr->category_prom_xml_name|escape}Категория для Prom.ua</label>
                                    <div>
                                        <input type="text" class="input_autocomplete prom form-control" name="prom[name]" value="{$category->prom_category['name']|escape}" placeholder="{$btr->category_select|escape}"/>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-3">
                                <div class="form-group clearfix">
                                    <label class="heading_label">{$btr->category_prom_xml_id|escape}ID категории для Prom.ua</label>
                                    <div>
                                        <input type="text" class="form-control" name="prom[id]" value="{$category->prom_category['id']|escape}" placeholder="{$btr->category_select|escape}"/>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-3">
                                <div class="form-group clearfix">
                                    <label class="heading_label">{$btr->category_prom_xml_url|escape}URL категории для Prom.ua</label>
                                    <div>
                                        <input type="text" class="form-control" name="prom[url]" value="{$category->prom_category['url']|escape}" placeholder="{$btr->category_select|escape}"/>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-lg-12 toggle_body_wrap on fn_card">
                        <div class="row">
                            <div class="col-lg-6 pr-0">
                                <div class="form-group clearfix epicentrk_list">
                                    <label class="heading_label">{$btr->category_epicentrk_xml_name|escape}</label>
                                    <div>
                                        <input type="text" class="input_autocomplete epicentrk form-control" name="epicentrk_name" value="{$category->epicentrk_name|escape}" placeholder="{$btr->category_epicentrk_xml_enter|escape}"/>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {*Параметры элемента*}
    <div class="row">
        <div class="col-lg-12 col-md-12">
            <div class="boxed match fn_toggle_wrap">
                <div class="heading_box">
                    {$btr->general_metatags|escape}
                    <div class="toggle_arrow_wrap fn_toggle_card text-primary">
                        <a class="btn-minimize" href="javascript:;" ><i class="fa fn_icon_arrow fa-angle-down"></i></a>
                    </div>
                </div>
                <div class="toggle_body_wrap on fn_card row">
                    <div class="col-lg-6 col-md-6">
                        <div class="heading_label" >Meta-title <span id="fn_meta_title_counter"></span></div>
                        <input name="meta_title" class="form-control fn_meta_field mb-h" type="text" value="{$category->meta_title|escape}" />
                        <div class="heading_label" >Meta-keywords</div>
                        <input name="meta_keywords" class="form-control fn_meta_field mb-h" type="text" value="{$category->meta_keywords|escape}" />
                    </div>
                    <div class="col-lg-6 col-md-6 pl-0">
                        <div class="mb-q" >Meta-description <span id="fn_meta_description_counter"></span></div>
                        <textarea name="meta_description" class="form-control main_textarea fn_meta_field">{$category->meta_description|escape}</textarea>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {*Описание элемента*}
    <div class="row">
        <div class="col-lg-12 col-md-12">
            <div class="boxed match fn_toggle_wrap tabs">
                <div class="heading_tabs">
                    <div class="tab_navigation">
                        <a href="#tab1" class="tab_navigation_link">{$btr->general_short_description|escape}</a>
                        <a href="#tab2" class="tab_navigation_link">{$btr->general_full_description|escape}</a>
                    </div>
                    <div class="toggle_arrow_wrap fn_toggle_card text-primary">
                        <a class="btn-minimize" href="javascript:;" ><i class="icon-arrow-down"></i></a>
                    </div>
                </div>
                <div class="toggle_body_wrap on fn_card ">
                    <div class="tab_container">
                        <div id="tab1" class="tab">
                            <textarea name="annotation" id="fn_editor" class="editor_small">{$category->annotation|escape}</textarea>
                        </div>
                        <div id="tab2" class="tab">
                            <textarea name="description" class="editor_large fn_editor_class">{$category->description|escape}</textarea>
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
            </div>
        </div>
    </div>
</form>


{* Подключаем Tiny MCE *}
{include file='tinymce_init.tpl'}
{* On document load *}

<script src="design/js/autocomplete/jquery.autocomplete-min.js"></script>
{literal}
<script>
$(function() {
    $('.input_autocomplete.yandex').devbridgeAutocomplete({
        serviceUrl:'ajax/market.php?module=search_market&session_id={/literal}{$smarty.session.id}{literal}',
        minChars:1,
        noCache: false,
        appendTo: '.yandex_list',
        onSelect:
            function(suggestions) {
                $(this).closest('div').find('input[name*="yandex_name"]').val(suggestions.data);
            }
    });
    $('.input_autocomplete.rozetka').devbridgeAutocomplete({
        serviceUrl:'ajax/get_features.php?rozetka=1&session_id={/literal}{$smarty.session.id}{literal}',
        minChars:1,
        noCache: false,
        appendTo: '.rozetka_list',
        dataType: 'json',
        transformResult: (response,originalQuery) => {
            let featureVariants = [];
            if (response[0].values) {
                featureVariants = response[0].values.map((fv)=>{
                    return {
                        value: fv.value,
                        data: fv
                    }
                });
            }
            return {
                query: originalQuery,
                suggestions: featureVariants,
            }
        },
        onSelect:
            function(suggestions) {
                $(this).closest('div').find('input[name*="rozetka_name"]').val(suggestions.data.value);
                $(this).closest('div').find('input[name*="rozetka_category_value_id"]').val(suggestions.data.id);
            }
    });
    $('.input_autocomplete.google').devbridgeAutocomplete({
        serviceUrl:'ajax/market.php?module=search_rss&session_id={/literal}{$smarty.session.id}{literal}',
        minChars:1,
        noCache: false,
        appendTo: '.google_list',
        onSelect:
            function(suggestions) {
                $(this).closest('div').find('input[name*="google_name"]').val(suggestions.data);
            }
    });
    $('.input_autocomplete.prom').devbridgeAutocomplete({
        serviceUrl:'ajax/market.php?module=search_prom&session_id={/literal}{$smarty.session.id}{literal}',
        minChars:1,
        noCache: false,
        appendTo: '.prom_list',
        onSelect:
            function(suggestions) {
                $(this).closest('div.row').find('input[name*="prom[name]"]').val(suggestions.data.name);
                $(this).closest('div.row').find('input[name*="prom[id]"]').val(suggestions.data.id);
                $(this).closest('div.row').find('input[name*="prom[url]"]').val(suggestions.data.url);
            }
    });
    $(".input_autocomplete").trigger('click');
});
</script>
{/literal}
