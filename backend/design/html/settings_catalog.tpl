{$meta_title = $btr->settings_catalog_catalog scope=parent}

{*Название страницы*}
<div class="row">
    <div class="col-lg-6 col-md-6">
        <div class="heading_page">{$btr->settings_catalog_catalog|escape}</div>
    </div>
    <div class="col-lg-4 col-md-3 text-xs-right float-xs-right"></div>
</div>

{*Вывод успешных сообщений*}
{if $message_success}
    <div class="row">
        <div class="col-lg-12 col-md-12 col-sm-12">
            <div class="boxed boxed_success">
                <div class="heading_box">
                    {if $message_success == 'saved'}
                        {$btr->settings_catalog_catalog|escape}
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
<form method="post" enctype="multipart/form-data">
    <input type=hidden name="session_id" value="{$smarty.session.id}">

    <div class="row">
        <div class="col-lg-12 col-md-12">
            <div class="boxed fn_toggle_wrap ">
                <div class="heading_box">
                    {$btr->settings_catalog_catalog|escape}
                    <div class="toggle_arrow_wrap fn_toggle_card text-primary">
                        <a class="btn-minimize" href="javascript:;" ><i class="fa fn_icon_arrow fa-angle-down"></i></a>
                    </div>
                </div>
                {*Параметры элемента*}
                <div class="toggle_body_wrap on fn_card">
                    <div class="row">
                        <div class="col-lg-4 col-md-6">
                            <div class="heading_label">{$btr->settings_catalog_products_on_page|escape}</div>
                            <div class="mb-1">
                               <input name="products_num" class="form-control" type="text" value="{$settings->products_num|escape}" />
                            </div>
                        </div>
                        <div class="col-lg-4 col-md-6">
                            <div class="heading_label">{$btr->settings_catalog_products_max|escape}</div>
                            <div class="mb-1">
                                <input name="max_order_amount" class="form-control" type="text" value="{$settings->max_order_amount|escape}" />
                            </div>
                        </div>
                        <div class="col-lg-4 col-md-6">
                            <div class="heading_label">{$btr->settings_catalog_posts_on_page|escape}</div>
                            <div class="mb-1">
                                <input name="posts_num" class="form-control" type="text" value="{$settings->posts_num|escape}" />
                            </div>
                        </div>
                        <div class="col-lg-4 col-md-6">
                            <div class="heading_label">{$btr->settings_catalog_products_comparcion|escape}</div>
                            <div class="mb-1">
                                <input name="comparison_count" class="form-control" type="text" value="{$settings->comparison_count|escape}" />
                            </div>
                        </div>
                        <div class="col-lg-4 col-md-6">
                            <div class="heading_label">{$btr->settings_catalog_units|escape}</div>
                            <div class="mb-1">
                               <input name="units" class="form-control" type="text" value="{$settings->units|escape}" />
                            </div>
                        </div>
                        <div class="col-lg-4 col-md-6">
                            <div class="heading_label">{$btr->settings_catalog_cents|escape}</div>
                            <div class="mb-1">
                               <select name="decimals_point" class="selectpicker">
                                    <option value='.' {if $settings->decimals_point == '.'}selected{/if}>{$btr->settings_catalog_dot|escape} 12.45 {$currency->sign|escape}</option>
                                    <option value=',' {if $settings->decimals_point == ','}selected{/if}>{$btr->settings_catalog_comma|escape} 12,45 {$currency->sign|escape}</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-lg-4 col-md-6">
                            <div class="heading_label">{$btr->settings_catalog_thousands|escape}</div>
                            <div class="mb-1">
                               <select name="thousands_separator" class="selectpicker">
                                    <option value='' {if $settings->thousands_separator == ''}selected{/if}>{$btr->settings_catalog_without|escape} 1245678 {$currency->sign|escape}</option>
                                    <option value=' ' {if $settings->thousands_separator == ' '}selected{/if}>{$btr->settings_catalog_space|escape} 1 245 678 {$currency->sign|escape}</option>
                                    <option value=',' {if $settings->thousands_separator == ','}selected{/if}>{$btr->settings_catalog_comma|escape} 1,245,678 {$currency->sign|escape}</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-lg-8 col-md-6 mt-2">
                            <div class="heading_label boxes_inline">{$btr->settings_catalog_not_in_stock|escape}</div>
                            <div class="boxes_inline">
                               <div class="main_switch clearfix">
                                    <label class="switch switch-default">
                                        <input class="switch-input" name="is_preorder" value='1' type="checkbox" {if $settings->is_preorder}checked=""{/if}/>
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

    <div class="row">
        <div class="col-lg-12 col-md-12">
            <div class="boxed fn_toggle_wrap ">
                <div class="heading_box">
                    {$btr->settings_catalog_bonuses|escape}
                    <div class="toggle_arrow_wrap fn_toggle_card text-primary">
                        <a class="btn-minimize" href="javascript:;" ><i class="fa fn_icon_arrow fa-angle-down"></i></a>
                    </div>
                </div>
                {*Параметры элемента*}
                <div class="toggle_body_wrap on fn_card">
                    <div class="row">
                        <div class="col-lg-3 col-md-6">
                            <div class="heading_label">{$btr->settings_products_bonuses|escape}</div>
                            <div class="mb-1">
                                <div class="input-group">
                                    <input name="products_bonuses" class="form-control" type="number" min="1" max="100" value="{$settings->products_bonuses|escape}" />
                                    <span class="input-group-addon">%</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-3 col-md-6">
                            <div class="heading_label">{$btr->coupons_order_price|escape}</div>
                            <div class="mb-1">
                                <div class="input-group">
                                    <input name="orders_total_price_bonuses" class="form-control" type="number" min="0" step="0.1" value="{$settings->orders_total_price_bonuses|escape}" />
                                    <span class="input-group-addon">{$currency->sign}</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-3 col-md-6">
                            <div class="heading_label">{$btr->settings_orders_first_bonuses|escape}</div>
                            <div class="mb-1">
                               <input name="orders_first_bonuses" class="form-control" type="number" min="0" step="0.01" value="{$settings->orders_first_bonuses|escape}" />
                            </div>
                        </div>
                        <div class="col-lg-3 col-md-6">
                            <div class="heading_label">{$btr->settings_birthday_bonuses|escape}</div>
                            <div class="mb-1">
                               <input name="birthday_bonuses" class="form-control" type="number" min="0" step="0.01" value="{$settings->birthday_bonuses|escape}" />
                            </div>
                        </div>
                        <div class="col-lg-3 col-md-6">
                            <div class="heading_label">{$btr->settings_birthday_bonuses_on|escape}</div>
                            <div class="mb-1">
                                <div class="input-group">
                                    <input name="birthday_bonuses_on" class="form-control" type="number" min="0" value="{$settings->birthday_bonuses_on|escape}" />
                                    <span class="input-group-addon">{$btr->settings_birthday_bonuses_days|escape}</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-3 col-md-6">
                            <div class="heading_label">{$btr->settings_birthday_bonuses_off|escape}</div>
                            <div class="mb-1">
                                <div class="input-group">
                                    <input name="birthday_bonuses_off" class="form-control" type="number" min="1" value="{$settings->birthday_bonuses_off|escape}" />
                                    <span class="input-group-addon">{$btr->settings_birthday_bonuses_days|escape}</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-3 col-md-6">
                            <div class="heading_label">{$btr->settings_users_bonuses_delete}</div>
                            <div class="mb-1">
                                <div class="input-group">
                                    <input name="users_bonuses_delete" class="form-control" type="number" min="1" value="{$settings->users_bonuses_delete|escape}" />
                                    <span class="input-group-addon">{$btr->settings_birthday_bonuses_days|escape}</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-3 col-md-6">
                            <div class="heading_label">{$btr->settings_users_bonuses_delete_notify}</div>
                            <div class="mb-1">
                                <div class="input-group">
                                    <input name="users_bonuses_delete_notify" class="form-control" type="number" min="1" value="{$settings->users_bonuses_delete_notify|escape}" />
                                    <span class="input-group-addon">{$btr->settings_birthday_bonuses_days|escape}</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-3 col-md-6">
                            <div class="heading_label">{$btr->settings_max_bonuses_orders}</div>
                            <div class="mb-1">
                                <div class="input-group">
                                    <input name="max_bonuses_orders" class="form-control" type="number" value="{$settings->max_bonuses_orders|escape}" />
                                    <span class="input-group-addon">%</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-3 col-md-4">
                            <div class="heading_label">{$btr->settings_restricted_categories_bonuses}</div>
                            <div class="mb-1">
                                <select name="restricted_bonuses_category" class="selectpicker" data-live-search="true" data-size="10">
                                    <option value=""{if !$settings->restricted_bonuses_category} selected{/if}>{$btr->general_not_set}</option>
                                    {function name=bonuses_categories level=0}
                                        {foreach $categories as $category}
                                            <option value="{$category->id}"{if $settings->restricted_bonuses_category == $category->id} selected{/if}>{section name=sp loop=$level}-- {/section}{$category->name}</option>
                                            {bonuses_categories categories=$category->subcategories level=$level+1}
                                        {/foreach}
                                    {/function}
                                    {bonuses_categories categories=$categories}
                                </select>
                            </div>
                        </div>
                        <div class="col-lg-3 col-md-6 mt-1">
                            <div class="heading_label boxes_inline">{$btr->settings_users_bonuses_on|escape}</div>
                            <div class="boxes_inline">
                               <div class="main_switch clearfix">
                                    <label class="switch switch-default">
                                        <input class="switch-input" name="users_bonuses_on" value="1" type="checkbox"{if $settings->users_bonuses_on} checked{/if}/>
                                        <span class="switch-label"></span>
                                        <span class="switch-handle"></span>
                                    </label>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-3 col-md-6 mt-1">
                            <div class="heading_label boxes_inline">{$btr->settings_cart_bonuses_on|escape}</div>
                            <div class="boxes_inline">
                               <div class="main_switch clearfix">
                                    <label class="switch switch-default">
                                        <input class="switch-input" name="cart_bonuses_on" value="1" type="checkbox"{if $settings->cart_bonuses_on} checked{/if}/>
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

    <div class="row">
        <div class="col-lg-12 col-md-12">
            <div class="boxed fn_toggle_wrap ">
                <div class="heading_box">
                    {$btr->settings_catalog_referrals|escape}
                    <div class="toggle_arrow_wrap fn_toggle_card text-primary">
                        <a class="btn-minimize" href="javascript:;" ><i class="fa fn_icon_arrow fa-angle-down"></i></a>
                    </div>
                </div>
                {*Параметры элемента*}
                <div class="toggle_body_wrap on fn_card">
                    <div class="row">
                        <div class="col-lg-3 col-md-6">
                            <div class="heading_label">{$btr->settings_user_referral_value|escape}</div>
                            <div class="mb-1">
                                <div class="input-group">
                                    <input name="user_referral_value" class="form-control" type="number" min="0" max="100" value="{$settings->user_referral_value|escape}" />
                                    <span class="input-group-addon">{$currency->sign}</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-3 col-md-6">
                            <div class="heading_label">{$btr->settings_user_referral_value_referrer|escape}</div>
                            <div class="mb-1">
                                <div class="input-group">
                                    <input name="user_referral_value_referrer" class="form-control" type="number" min="0" max="100" value="{$settings->user_referral_value_referrer|escape}" />
                                    <span class="input-group-addon">{$currency->sign}</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-3 col-md-6">
                            <div class="heading_label">{$btr->settings_user_referral_expire|escape}</div>
                            <div class="mb-1">
                                <div class="input-group">
                                    <input name="user_referral_expire" class="form-control" type="number" min="0" value="{$settings->user_referral_expire|escape}" />
                                    <span class="input-group-addon">{$btr->settings_birthday_bonuses_days|escape}</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-3 col-md-6">
                            <div class="heading_label">{$btr->settings_orders_labels_referral|escape}</div>
                            <div class="mb-1">
                                <select name="orders_labels[referral]" class="selectpicker">
                                    <option value=""{if !$settings->orders_labels['referral']} selected{/if}>{$btr->orders_labels_referral_no}</option>
                                    {foreach $labels as $label}
                                        <option value="{$label->id}"{if $settings->orders_labels['referral'] == $label->id} selected{/if}>{$label->name}</option>
                                    {/foreach}
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-12 col-md-12">
            <div class="boxed fn_toggle_wrap ">
                <div class="heading_box">
                    {$btr->settings_orders_orders|escape}
                    <div class="toggle_arrow_wrap fn_toggle_card text-primary">
                        <a class="btn-minimize" href="javascript:;" ><i class="fa fn_icon_arrow fa-angle-down"></i></a>
                    </div>
                </div>
                {*Параметры элемента*}
                <div class="toggle_body_wrap on fn_card">
                    <div class="row">
                        <div class="col-lg-4 col-md-6">
                            <div class="heading_label">{$btr->settings_orders_status_fast|escape}</div>
                            <div class="mb-1">
                                <select name="orders_status[fast]" class="selectpicker">
                                    {foreach $statuses as $status}
                                        <option value="{$status->id}"{if $settings->orders_status['fast'] == $status->id} selected{/if}>{$status->name}</option>
                                    {/foreach}
                                </select>
                            </div>
                        </div>
                        <div class="col-lg-4 col-md-6">
                            <div class="heading_label">{$btr->settings_orders_status_preorder|escape}</div>
                            <div class="mb-1">
                                <select name="orders_status[preorder]" class="selectpicker">
                                    {foreach $statuses as $status}
                                        <option value="{$status->id}"{if $settings->orders_status['preorder'] == $status->id} selected{/if}>{$status->name}</option>
                                    {/foreach}
                                </select>
                            </div>
                        </div>
                        <div class="col-lg-4 col-md-6">
                            <div class="heading_label">{$btr->settings_orders_label_advance|escape}</div>
                            <div class="mb-1">
                                <select name="orders_labels[advance]" class="selectpicker">
                                    {foreach $labels as $label}
                                        <option value="{$label->id}"{if $settings->orders_labels['advance'] == $label->id} selected{/if}>{$label->name}</option>
                                    {/foreach}
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-12 col-md-12">
            <div class="boxed fn_toggle_wrap ">
                <div class="heading_box">
                    {$btr->settings_features|escape}
                    <div class="toggle_arrow_wrap fn_toggle_card text-primary">
                        <a class="btn-minimize" href="javascript:;" ><i class="fa fn_icon_arrow fa-angle-down"></i></a>
                    </div>
                </div>
                {*Параметры элемента*}
                <div class="toggle_body_wrap on fn_card">
                    <div class="row">
                        <div class="col-lg-4 col-md-6">
                            <div class="heading_label">{$btr->settings_features_size|escape}</div>
                            <div class="mb-1">
                                <select name="feature_id[size]" class="selectpicker">
                                    <option value=""{if !$settings->feature_id['size']} selected{/if}>-</option>
                                    {foreach $features as $feature}
                                        <option value="{$feature->id}"{if $settings->feature_id['size'] == $feature->id} selected{/if}>{$feature->name}</option>
                                    {/foreach}
                                </select>
                            </div>
                        </div>
                        <div class="col-lg-4 col-md-6">
                            <div class="heading_label">{$btr->settings_features_color|escape}</div>
                            <div class="mb-1">
                                <select name="feature_id[color]" class="selectpicker">
                                    <option value=""{if !$settings->feature_id['color']} selected{/if}>-</option>
                                    {foreach $features as $feature}
                                        <option value="{$feature->id}"{if $settings->feature_id['color'] == $feature->id} selected{/if}>{$feature->name}</option>
                                    {/foreach}
                                </select>
                            </div>
                        </div>
                        <div class="col-lg-4 col-md-6">
                            <div class="heading_label">{$btr->settings_features_collection|escape}</div>
                            <div class="mb-1">
                                <select name="feature_id[collection]" class="selectpicker">
                                    <option value=""{if !$settings->feature_id['collection']} selected{/if}>-</option>
                                    {foreach $features as $feature}
                                        <option value="{$feature->id}"{if $settings->feature_id['collection'] == $feature->id} selected{/if}>{$feature->name}</option>
                                    {/foreach}
                                </select>
                            </div>
                        </div>
                        <div class="col-lg-4 col-md-6">
                            <div class="heading_label">{$btr->settings_features_category_rozetka|escape}</div>
                            <div class="mb-1">
                                <select name="feature_id[category_rozetka]" class="selectpicker" data-live-search="true" data-size="10">
                                    <option value=""{if !$settings->feature_id['category_rozetka']} selected{/if}>-</option>
                                    {foreach $features as $feature}
                                        <option value="{$feature->id}"{if $settings->feature_id['category_rozetka'] == $feature->id} selected{/if}>{$feature->name}</option>
                                    {/foreach}
                                </select>
                            </div>
                        </div>
                        <div class="col-lg-4 col-md-6">
                            <div class="heading_label">{$btr->settings_features_category_epicentrk|escape}</div>
                            <div class="mb-1">
                                <select name="feature_id[category_epicentrk]" class="selectpicker" data-live-search="true" data-size="10">
                                    <option value=""{if !$settings->feature_id['category_epicentrk']} selected{/if}>-</option>
                                    {foreach $features as $feature}
                                        <option value="{$feature->id}"{if $settings->feature_id['category_epicentrk'] == $feature->id} selected{/if}>{$feature->name}</option>
                                    {/foreach}
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-12 col-md-12">
            <div class="boxed fn_toggle_wrap ">
                <div class="heading_box">
                    {$btr->settings_registration}
                    <div class="toggle_arrow_wrap fn_toggle_card text-primary">
                        <a class="btn-minimize" href="javascript:;" ><i class="fa fn_icon_arrow fa-angle-down"></i></a>
                    </div>
                </div>
                {*Параметры элемента*}
                <div class="toggle_body_wrap on fn_card">
                    <div class="row">
                        <div class="col-lg-4 col-md-6">
                            <div class="heading_label">{$btr->settings_registration_group}</div>
                            <div class="mb-1">
                                <select name="registration_group" class="selectpicker">
                                    <option value=""{if !$settings->registration_group} selected{/if}>-</option>
                                    {foreach $groups as $group}
                                        <option value="{$group->id}"{if $settings->registration_group == $group->id} selected{/if}>{$group->name}</option>
                                    {/foreach}
                                </select>
                            </div>
                        </div>
                        <div class="col-lg-4 col-md-6">
                            <div class="heading_label">{$btr->settings_registration_promo_group}</div>
                            <div class="mb-1">
                                <select name="registration_promo_group" class="selectpicker">
                                    <option value=""{if !$settings->registration_promo_group} selected{/if}>-</option>
                                    {foreach $groups as $group}
                                        <option value="{$group->id}"{if $settings->registration_promo_group == $group->id} selected{/if}>{$group->name}</option>
                                    {/foreach}
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-12 col-md-12">
            <div class="boxed fn_toggle_wrap ">
                <div class="heading_box">
                    {$btr->settings_global_categories}
                    <div class="toggle_arrow_wrap fn_toggle_card text-primary">
                        <a class="btn-minimize" href="javascript:" ><i class="fa fn_icon_arrow fa-angle-down"></i></a>
                    </div>
                </div>
                {*Параметры элемента*}
                <div class="toggle_body_wrap on fn_card">
                    <div class="row">
                        {$global_categories = ['clothes', 'appliances']}
                        {foreach $global_categories as $global_category}
                            <div class="col-lg-3 col-md-4">
                                <div class="heading_label">{$btr->get_translation("settings_global_categories_{$global_category}")}</div>
                                <div class="mb-1">
                                    <select name="global_categories[{$global_category}]" class="selectpicker" data-live-search="true">
                                        <option value=""{if !$settings->global_categories[{$global_category}]} selected{/if}>{$btr->settings_global_category_no}</option>
                                        {function name=global_categories level=0}
                                            {foreach $categories as $category}
                                                <option value="{$category->id}"{if $settings->global_categories[{$global_category}] == $category->id} selected{/if}>{section name=sp loop=$level}--{/section}{$category->name}</option>
                                                {global_categories categories=$category->subcategories level=$level+1}
                                            {/foreach}
                                        {/function}
                                        {global_categories categories=$categories}
                                    </select>
                                </div>
                            </div>
                        {/foreach}
                    </div>
                </div>
            </div>
        </div>
    </div>

    {*Параметры элемента*}
    <div class="row">
        <div class="col-lg-12 col-md-12">
            <div class="boxed fn_toggle_wrap ">
                <div class="heading_box">
                    {$btr->settings_catalog_watermark|escape}
                    <div class="toggle_arrow_wrap fn_toggle_card text-primary">
                        <a class="btn-minimize" href="javascript:;" ><i class="fa fn_icon_arrow fa-angle-down"></i></a>
                    </div>
                </div>
                <div class="toggle_body_wrap on fn_card">
                    <div class="row">
                        <div class="col-lg-6 col-md-6">
                            <div class="boxed">
                                {if $config->watermark_file}
                                    <div class="fn_parent_image">
                                        <input class="fn_accept_delete" name="delete_watermark" value="" type="hidden" />
                                        <div class="banner_image fn_image_wrapper text-xs-center">
                                            <a href="javascript:;" class="fn_delete_item remove_image"></a>
                                            <img class="watermark_image" src="{$config->root_url}/{$config->watermark_file}" alt="" />
                                        </div>
                                    </div>
                                {else}
                                    <div class="fn_parent_image"></div>
                                {/if}

                                <div class="fn_upload_image dropzone_block_image text-xs-center {if $config->watermark_file} hidden{/if}">
                                    <i class="fa fa-plus font-5xl" aria-hidden="true"></i>
                                    <input class="dropzone_image" name="watermark_file" type="file" />
                                </div>
                                <div class="image_wrapper fn_image_wrapper fn_new_image text-xs-center">
                                    <a href="javascript:;" class="fn_delete_item delete_image remove_image"></a>
                                    <img src="" alt="" />
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-6 col-md-6">
                            <div class="row">
                                <div class="col-xs-12 fn_range_wrap">
                                    <div class="heading_label">
                                        {$btr->settings_catalog_watermark_position|escape}
                                        <span class="font-weight-bold fn_show_range">{$settings->watermark_offset_x|escape}</span>
                                    </div>
                                    <div class="raiting_boxed">
                                        <input class="fn_range_value" type="hidden" value="{$settings->watermark_offset_x|escape}" name="watermark_offset_x" />
                                        <input class="fn_rating range_input" type="range" min="0" max="100" step="1" value="{$settings->watermark_offset_x|escape}" />
                                        <div class="raiting_range_number">
                                            <span class="float-xs-left">1</span>
                                            <span class="float-xs-right">100</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-xs-12 fn_range_wrap">
                                    <div class="heading_label">
                                        {$btr->settings_catalog_watermark_position_y|escape}
                                        <span class="font-weight-bold fn_show_range">{$settings->watermark_offset_y|escape}</span>
                                    </div>
                                    <div class="raiting_boxed">
                                        <input class="fn_range_value" type="hidden" value="{$settings->watermark_offset_y|escape}" name="watermark_offset_y" />
                                        <input class="fn_rating range_input" type="range" min="0" max="100" step="1" value="{$settings->watermark_offset_y|escape}" />
                                        <div class="raiting_range_number">
                                            <span class="float-xs-left">1</span>
                                            <span class="float-xs-right">100</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-xs-12 fn_range_wrap">
                                    <div class="heading_label">
                                        {$btr->settings_catalog_quality|escape}
                                        <span class="font-weight-bold fn_show_range">{$settings->image_quality}</span>
                                    </div>
                                    <div class="raiting_boxed">
                                        <input class="fn_range_value" type="hidden" value="{$settings->image_quality}" name="image_quality" />
                                        <input class="fn_rating range_input" type="range" min="0" max="100" step="1" value="{$settings->image_quality|escape}" />
                                        <div class="raiting_range_number">
                                            <span class="float-xs-left">1</span>
                                            <span class="float-xs-right">100</span>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-lg-12 col-md-12 col-sm-12">
                                            <div class="boxed boxed_attention">
                                                <div class="">
                                                    <div class="">
                                                        <span class="text_600">{$btr->settings_catalog_message1|escape}</span>
                                                    </div><br>
                                                    <span class="text_600">
                                                        {$btr->settings_catalog_message2|escape}
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-lg-12 col-md-12 ">
                            <button type="submit" class="btn btn_small btn_blue float-md-right">
                                {include file='svg_icon.tpl' svgId='checked'}
                                <span>{$btr->general_apply|escape}</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>
<script>
    $(document).on("click", ".fn_truncate_table", function () {
        $('.fn_truncate_table_confirm').fadeIn(500);
        $('[name="truncate_table_password"]').prop('disabled', false);
    });
    $(document).on("input", ".fn_rating", function () {
        $(this).closest(".fn_range_wrap").find(".fn_show_range").html($(this).val());
        $(this).closest(".fn_range_wrap").find(".fn_range_value").val($(this).val());
    });
</script>
