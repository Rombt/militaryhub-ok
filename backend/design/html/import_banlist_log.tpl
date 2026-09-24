{* Title *}
{$meta_title=$btr->import_log_products scope=parent}


{*Название страницы*}
<div class="row">
    <div class="col-lg-12 col-md-12">
        <div class="wrap_heading">
            <div class="heading_page">{$btr->import_blacklist_log|escape}</div>
        </div>
    </div>
</div>

<div class="boxed fn_toggle_wrap">
    <div class="tabs">
        <div class="tab_navigation mb-1">
            <a href="#categories_banlist_logs"
               class="heading_box tab_navigation_link">{$btr->import_banlist_log_categories|escape|default:'Categories logs'}</a>
            <a href="#brands_banlist_logs"
               class="heading_box tab_navigation_link">{$btr->import_banlist_log_brads|escape|default:'Brands logs'}</a>
        </div>
        <div class="tab_container">
            <div id="categories_banlist_logs" class="tab">
                {if $categories_logs}
                    <div class="main_list">
                        {*Шапка таблицы*}
                        <div class="main_list_head">
                            <div class="main_list_heading main_list_check">№</div>
                            <div class="main_list_heading main_list_photo">{$btr->general_photo|escape}</div>
                            <div class="main_list_heading main_list_log_name">{$btr->general_name|escape} </div>
                            <div class="main_list_heading main_list_log_status">
                                <span>{$btr->general_status|escape}</span>
                            </div>
                        </div>
                        <div class="main_list_body">
                            {foreach $categories_logs as $log}
                                <div class="fn_row main_list_body_item">
                                    <div class="main_list_row">
                                        <div class="main_list_boding main_list_check">{$log@iteration}</div>
                                        <div class="main_list_boding main_list_photo">
                                            {if $log->category->image}
                                                <a href="{url module=CategoryAdmin id=$log->category->id return=$smarty.server.REQUEST_URI}" target="_blank">
                                                    <img src="{$log->category->image|resize:55:55:false:$config->resized_categories_dir}"/>
                                                </a>
                                            {else}
                                                <img height="55" width="55" src="design/images/no_image.png"/>
                                            {/if}
                                        </div>

                                        <div class="main_list_boding main_list_log_name">
                                            <a class="link" href="{url module=CategoryAdmin id=$log->category->id return=$smarty.server.REQUEST_URI}" target="_blank">{$log->category->name|escape}</a>
                                        </div>
                                        <div class="main_list_boding main_list_log_status">
                                            {if $log->data->ban == 0}
                                                {$btr->import_blacklist_log_item_removed|escape|default:'Item removed from banlist'}
                                            {else}
                                                {$btr->import_blacklist_log_item_added|escape|default:'Item added to banlist'}
                                            {/if}
                                        </div>
                                    </div>
                                </div>
                            {/foreach}
                        </div>
                    </div>
                {else}
                    <div class="heading_box my-1">
                        <div class="text_grey">{$btr->import_banlist_log_no_logs|escape|default:'No logs to show'}</div>
                    </div>
                {/if}
            </div>
            <div id="brands_banlist_logs" class="tab">
                {if $brands_logs}
                    <div class="main_list">
                        {*Шапка таблицы*}
                        <div class="main_list_head">
                            <div class="main_list_heading main_list_check">№</div>
                            <div class="main_list_heading main_list_photo">{$btr->general_photo|escape}</div>
                            <div class="main_list_heading main_list_log_name">{$btr->general_name|escape} </div>
                            <div class="main_list_heading main_list_log_status">
                                <span>{$btr->general_status|escape}</span>
                            </div>
                        </div>
                        {*Параметры элемента*}
                        <div class="main_list_body">
                            {foreach $brands_logs as $log}
                                <div class="fn_row main_list_body_item">
                                    <div class="main_list_row">
                                        <div class="main_list_boding main_list_check">{$log@iteration}</div>
                                        <div class="main_list_boding main_list_photo">
                                            {if $log->brand->image}
                                                <a href="{url module=BrandAdmin id=$log->brand->id return=$smarty.server.REQUEST_URI}" target="_blank">
                                                    <img src="{$log->brand->image|resize:55:55:false:$config->resized_brands_dir}"/>
                                                </a>
                                            {else}
                                                <img height="55" width="55" src="design/images/no_image.png"/>
                                            {/if}
                                        </div>

                                        <div class="main_list_boding main_list_log_name">
                                            <a class="link" href="{url module=BrandAdmin id=$log->brand->id return=$smarty.server.REQUEST_URI}" target="_blank">{$log->brand->name|escape}</a>
                                        </div>
                                        <div class="main_list_boding main_list_log_status">
                                            {if $log->data->ban == 0}
                                                {$btr->import_blacklist_log_item_removed|escape|default:'Item removed from banlist'}
                                            {else}
                                                {$btr->import_blacklist_log_item_added|escape|default:'Item added to banlist'}
                                            {/if}
                                        </div>
                                    </div>
                                </div>
                            {/foreach}
                        </div>
                    </div>
                {else}
                    <div class="heading_box mt-1">
                        <div class="text_grey">{$btr->import_banlist_log_no_logs|escape|default:'No logs to show'}</div>
                    </div>
                {/if}
            </div>
        </div>
    </div>
</div>
