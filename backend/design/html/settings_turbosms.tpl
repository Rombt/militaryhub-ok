{$meta_title = $btr->settings_turbosms scope=parent}

{*Название страницы*}
<div class="row">
    <div class="col-lg-6 col-md-6">
        <div class="heading_page">{$btr->settings_turbosms|escape}</div>
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
                        {$btr->general_settings_saved|escape}
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
            <div class="boxed fn_toggle_wrap">
                <div class="heading_box">
                    {$btr->settings_turbosms_keys|escape}
                    <div class="toggle_arrow_wrap fn_toggle_card text-primary">
                        <a class="btn-minimize" href="javascript:;" ><i class="fa fn_icon_arrow fa-angle-down"></i></a>
                    </div>
                </div>
                <div class="toggle_body_wrap on fn_card">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="heading_label">{$btr->settings_turbosms_sender|escape}</div>
                            <div class="mb-1">
                                <input name="turbosms_sender" class="form-control" type="text" value="{$settings->turbosms_sender|escape}" />
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="heading_label">{$btr->settings_turbosms_login|escape}</div>
                            <div class="mb-1">
                                <input name="turbosms_login" class="form-control" type="text" value="{$settings->turbosms_login|escape}" />
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="heading_label">{$btr->settings_turbosms_pass|escape}</div>
                            <div class="mb-1">
                                <input name="turbosms_pass" class="form-control" type="text" value="{$settings->turbosms_pass|escape}" />
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="heading_label boxes_inline">{$btr->settings_turbosms_enabled|escape}</div>
                            <div class="boxes_inline">
                               <div class="main_switch clearfix">
                                    <label class="switch switch-default">
                                        <input class="switch-input" name="turbosms_enabled" value='1' type="checkbox"{if $settings->turbosms_enabled} checked{/if}/>
                                        <span class="switch-label"></span>
                                        <span class="switch-handle"></span>
                                    </label>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="heading_label boxes_inline">{$btr->settings_turbosms_test_mode|escape}</div>
                            <div class="boxes_inline">
                               <div class="main_switch clearfix">
                                    <label class="switch switch-default">
                                        <input class="switch-input" name="turbosms_test_mode" value='1' type="checkbox"{if $settings->turbosms_test_mode} checked{/if}/>
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
            <div class="boxed fn_toggle_wrap">
                <div class="heading_box">
                    {$btr->settings_turbosms_settings|escape}
                    <div class="toggle_arrow_wrap fn_toggle_card text-primary">
                        <a class="btn-minimize" href="javascript:;" ><i class="fa fn_icon_arrow fa-angle-down"></i></a>
                    </div>
                </div>

                <div class="toggle_body_wrap on fn_card">
                    <div class="row">
                        {$messages = ['order', 'register', 'birthday', 'bonuses_removed']}
                        {foreach $messages as $message}
                            <div class="col-lg-6 col-xs-12{if $message@iteration % 2} pr-0{/if}">
                                <div class="heading_label">{$btr->get_translation("settings_turbosms_{$message}")|escape}</div>
                                <div class="mb-1">
                                    <textarea class="form-control main_textarea" name="turbosms_messages[{$message}]" cols="100" rows="5">{$settings->turbosms_messages[$message]}</textarea>
                                </div>
                            </div>
                        {/foreach}
                    </div>
                </div>

                {* <div class="toggle_body_wrap on fn_card">
                    <div class="row">
                        <div class="col-lg-6 col-xs-12">
                            <div class="heading_label">{$btr->settings_turbosms_register|escape}</div>
                            <div class="mb-1">
                                <textarea class="form-control main_textarea" name="turbosms_messages[register]" cols="100" rows="5">{$settings->turbosms_messages['register']}</textarea>
                            </div>
                        </div>
                    </div>
                </div> *}

            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-12 col-md-12">
            <div class="boxed fn_toggle_wrap">
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
</form>

<div class="row">
    <div class="col-lg-6 col-md-6 col-xs-12 pr-0">
        <div class="boxed fn_toggle_wrap">
            <div class="heading_box">
                {$btr->settings_turbosms_fields|escape}
                <div class="toggle_arrow_wrap fn_toggle_card text-primary">
                    <a class="btn-minimize" href="javascript:;"><i class="icon-arrow-down"></i></a>
                </div>
            </div>
            <div class="toggle_body_wrap on fn_card">
                <div class="main_list">
                    <div class="main_list_body">
                        {foreach $sms_orders_fields as $param => $value}
                            <div class="main_list_body_item min_height_10px">
                                <div class="d_flex">
                                    <div class="main_list_boding">
                                        <div class="text_600 text_dark"><code>{$param|escape}</code></div>
                                    </div>
                                    <div class="main_list_boding">{$value|escape}</div>
                                </div>
                            </div>
                        {/foreach}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>