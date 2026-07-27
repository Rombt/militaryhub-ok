{$meta_title = $btr->settings_autorize scope=parent}

{*Название страницы*}
<div class="row">
    <div class="col-lg-6 col-md-6">
        <div class="heading_page">{$btr->settings_autorize|escape}</div>
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
                        {$btr->settings_autorize|escape}
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
                    {$btr->settings_autorize_google|escape}
                    <div class="toggle_arrow_wrap fn_toggle_card text-primary">
                        <a class="btn-minimize" href="javascript:;" ><i class="fa fn_icon_arrow fa-angle-down"></i></a>
                    </div>
                </div>
                <div class="toggle_body_wrap on fn_card">
                    <div class="row">
                        <div class="col-lg-6 col-md-6">
                            <div class="heading_label">{$btr->settings_autorize_google_client_id|escape}</div>
                            <div class="mb-1">
                                <input name="google_client_id" class="form-control" type="text" value="{$settings->google_client_id}" />
                            </div>
                        </div>
                        <div class="col-lg-6 col-md-6">
                            <div class="heading_label">{$btr->settings_autorize_google_client_secret|escape}</div>
                            <div class="mb-1">
                                <input name="google_client_secret" class="form-control" type="text" value="{$settings->google_client_secret}" />
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="row">
                                <div class="col-xs-12">
                                    <div class="heading_label boxes_inline">{$btr->settings_autorize_google_enabled|escape}</div>
                                    <div class="boxes_inline">
                                    <div class="main_switch clearfix">
                                            <label class="switch switch-default">
                                                <input class="switch-input" name="google_enabled" value='1' type="checkbox"{if $settings->google_enabled} checked{/if}/>
                                                <span class="switch-label"></span>
                                                <span class="switch-handle"></span>
                                            </label>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-xs-12">
                                    <div class="heading_label boxes_inline">{$btr->settings_autorize_google_test_mode|escape}</div>
                                    <div class="boxes_inline">
                                    <div class="main_switch clearfix">
                                            <label class="switch switch-default">
                                                <input class="switch-input" name="google_test_mode" value='1' type="checkbox"{if $settings->google_test_mode} checked{/if}/>
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
        </div>
    </div>

    <div class="row">
        <div class="col-lg-12 col-md-12">
            <div class="boxed fn_toggle_wrap ">
                <div class="heading_box">
                    {$btr->settings_autorize_facebook|escape}
                    <div class="toggle_arrow_wrap fn_toggle_card text-primary">
                        <a class="btn-minimize" href="javascript:;" ><i class="fa fn_icon_arrow fa-angle-down"></i></a>
                    </div>
                </div>
                <div class="toggle_body_wrap on fn_card">
                    <div class="row">
                        <div class="col-lg-6 col-md-6">
                            <div class="heading_label">{$btr->settings_autorize_facebook_app_id|escape}</div>
                            <div class="mb-1">
                                <input name="facebook_app_id" class="form-control" type="text" value="{$settings->facebook_app_id}" />
                            </div>
                        </div>
                        <div class="col-lg-6 col-md-6">
                            <div class="heading_label">{$btr->settings_autorize_facebook_app_secret|escape}</div>
                            <div class="mb-1">
                                <input name="facebook_app_secret" class="form-control" type="text" value="{$settings->facebook_app_secret}" />
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="row">
                                <div class="col-xs-12">
                                    <div class="heading_label boxes_inline">{$btr->settings_autorize_facebook_enabled|escape}</div>
                                    <div class="boxes_inline">
                                    <div class="main_switch clearfix">
                                            <label class="switch switch-default">
                                                <input class="switch-input" name="facebook_enabled" value='1' type="checkbox"{if $settings->facebook_enabled} checked{/if}/>
                                                <span class="switch-label"></span>
                                                <span class="switch-handle"></span>
                                            </label>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-xs-12">
                                    <div class="heading_label boxes_inline">{$btr->settings_autorize_facebook_test_mode|escape}</div>
                                    <div class="boxes_inline">
                                    <div class="main_switch clearfix">
                                            <label class="switch switch-default">
                                                <input class="switch-input" name="facebook_test_mode" value='1' type="checkbox"{if $settings->facebook_test_mode} checked{/if}/>
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
        </div>
    </div>

    <div class="row">
        <div class="col-lg-12 col-md-12">
            <div class="boxed fn_toggle_wrap ">
                <div class="toggle_body_wrap on fn_card">
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
