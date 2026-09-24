{if !$user}
<div class="hidden">
    <div id="fn_user_modal">
        <div class="popup-box">
            <div class="popup-box__container">
                <div class="left">
                    <div class="popup-box__top">
                        <div class="no_padding col-xs-6 center">
                        <span class="{if $login_form_sent != 'register' && !$smarty.session.registration_user}is_active{/if}"
                              data-id="#login" data-language="breadcrumbs_enter">{$lang->breadcrumbs_enter}</span>
                        </div>
                        <div class="no_padding col-xs-6 center">
                        <span class="{if $login_form_sent == 'register' || $smarty.session.registration_user}is_active{/if}"
                              data-id="#signup" data-language="login_text">{$lang->login_text}</span>
                        </div>
                    </div>
                    {* Form error messages*}
                    {if $error}
                        <div class="message_error">
                            {if $error == 'empty_name'}
                                <span data-language="form_enter_name">{$lang->form_enter_name}</span>
                            {elseif $error == 'empty_email'}
                                <span data-language="form_enter_email">{$lang->form_enter_email}</span>
                            {elseif $error == 'empty_password'}
                                <span data-language="form_enter_password">{$lang->form_enter_password}</span>
                            {elseif $error == 'user_exists'}
                                <span data-language="register_user_registered">{$lang->register_user_registered}</span>
                            {elseif $error == 'login_incorrect'}
                                <span data-language="login_error_pass">{$lang->login_error_pass}</span>
                            {elseif $error == 'user_disabled'}
                                <span data-language="login_pass_not_active">{$lang->login_pass_not_active}</span>
                            {elseif $error == 'user_must_ip'}
                                <span data-language="user_must_ip">{$lang->user_must_ip}</span>
                            {elseif $error == 'captcha'}
                                <span data-language="form_error_captcha">{$lang->form_error_captcha}</span>
                            {elseif $error == 'empty_code'}
                                <span data-language="form_error_captcha">{$lang->form_error_code}</span>
                            {else}
                                {$error}
                            {/if}
                        </div>
                    {/if}
                    <form method="post" id="login" name="login" class="popup-box__mid fn_validate_login"{if $login_form_sent == 'register' || $smarty.session.registration_user} style="display: none"{/if}>
                        <div class="h3"><span data-language="login_title">{$lang->login_title}</span></div>
                        {* User's phone*}
                        <div class="form_group">
                            <span class="form_placeholder" data-language="form_phone">{$lang->form_phone}</span>
                            <input class="form_input" type="tel" name="phone" value="{$login_phone|escape}">
                        </div>

                        {*User's password*}
                        <div class="form_group">
                            <span class="form_placeholder" data-language="form_password">{$lang->form_password}</span>
                            <input  class="form_input" type="password" name="password" data-notice="{$lang->form_enter_password}" value="">
                        </div>

                        {*Submit button*}
                        <input type="hidden" name="login" value="1">
                        <input class="button btn_black" type="submit" name="login" data-language="login_sign_in" value="{$lang->login_sign_in}">

                        {*Remember pk*}
                        <input id="for_pk" type="checkbox" name="pk">
                        <label class="checkbox checkbox_label" for="for_pk">
                            <span class="inp-check"></span>
                            <span data-language="remind_pc">{$lang->remind_pc}</span>
                        </label>

                        {*Remind password link*}
                        <a class="password_remind" href="{$lang_link}user/password_remind"
                           data-language="login_remind">{$lang->login_remind}</a>
                    </form>

                    <form method="post" id="signup" name="register"
                          class="popup-box__mid fn_validate_register"{if $login_form_sent != 'register' && !$smarty.session.registration_user} style="display: none"{/if}>
                        <div class="h3"><span data-language="login_title">{$lang->login_title}</span></div>

                        {if $smarty.session.registration_user}
                            {*User's name*}
                            <div class="form_group">
                                <span class="form_placeholder" data-language="form_code">{$lang->form_code}</span>
                                <input class="form_input" type="text" name="code" value="" data-language="form_code">
                            </div>
                        {else}
                            {*User's name*}
                            <div class="form_group">
                                <span class="form_placeholder" data-language="form_name">{$lang->form_name}</span>
                                <input class="form_input" type="text" name="name" value="{$registration_name|escape}"
                                    data-language="form_name">
                            </div>
                            {*User's phone*}
                            <div class="form_group">
                                <span class="form_placeholder" data-language="form_phone">{$lang->form_phone}</span>
                                <input class="form_input" type="text" name="phone" value="{$registration_phone|escape}"
                                    data-language="form_phone">
                            </div>
                            {* User's email *}
                            <div class="form_group">
                                <span class="form_placeholder" data-language="form_email">{$lang->form_email}</span>
                                <input class="form_input" type="text" name="email" value="{$registration_email|escape}"
                                    data-language="form_email">
                            </div>
                            {* User's  password *}
                            <div class="form_group">
                                <span class="form_placeholder" data-language="form_password">{$lang->form_password}</span>
                                <input class="form_input" type="password" name="password" value=""
                                    data-language="form_enter_password">
                            </div>
                        {/if}


                        {if $settings->captcha_register}
                            {if $settings->captcha_type == "v3"}
                                <div class="captcha row" style="display: none;">
                                    <div class="fn_recaptchav3">
                                        <input type="hidden" name="recaptcha_token" value=""
                                               class="fn_recaptcha_token">
                                    </div>
                                </div>
                            {elseif $settings->captcha_type == "v2"}
                                <div class="captcha">
                                    <div id="recaptcha1"></div>
                                </div>
                            {elseif $settings->captcha_type == "default"}
                                {get_captcha var="captcha_register"}
                                <div class="captcha">
                                    <div class="secret_number">{$captcha_register[0]|escape} + ?
                                        = {$captcha_register[1]|escape}</div>
                                    <span class="form_captcha">
                                    <input class="form_input input_captcha placeholder_focus" type="text"
                                           name="captcha_code" value="" data-language="form_enter_captcha">
                                    <span class="form_placeholder">{$lang->form_enter_captcha}*</span>
                                </span>
                                </div>
                            {/if}
                        {/if}

                        {* Submit button *}
                        <input name="register" type="hidden" value="1">
                        <input type="submit" class="button btn_black g-recaptcha" name="register"
                               {* //!! *}
                               data-language="register_create_account"{if $settings->captcha_type == "invisible"} data-sitekey="{$settings->public_recaptcha_invisible}" data-badge='bottomleft' data-callback="onSubmitReg"{/if}
                               value="{$lang->register_create_account}">
                    </form>

                    <div class="popup-box__bottom">
                        <div class="h3"><span data-language="sign_in_with">{$lang->sign_in_with}</span></div>
                        <div class="other_log-in js-btn_socials">
                            {if $settings->facebook_enabled || ($settings->facebook_test_mode && $smarty.session.admin)}
                                <a href="javascript:" onclick="checkLoginState()" class="f js-auth">
                                    {include file="svg.tpl" svgId="facebook"}
                                </a>
                            {/if}
                            {if $settings->google_enabled || ($settings->google_test_mode && $smarty.session.admin)}
                                <a href="javascript:" onclick="checkLoginStateGoogle()" class="g js-auth">
                                    {include file="svg.tpl" svgId="google"}
                                </a>
                            {/if}
                        </div>
                    </div>
                </div>
                <div class="right">
                    <div class="info">
                        <div class="info__item">
{*                            <div class="icon"></div>*}
                            <div class="title"
                                 data-language="authorization_popup_item_1_title">{$lang->authorization_popup_item_1_title|escape}</div>
                            <div class="text"
                                 data-language="authorization_popup_item_1_text">{$lang->authorization_popup_item_1_text|escape}</div>
                        </div>
                        <div class="info__item">
{*                            <div class="icon"></div>*}
                            <div class="title"
                                 data-language="authorization_popup_item_2_title">{$lang->authorization_popup_item_2_title|escape}</div>
                            <div class="text"
                                 data-language="authorization_popup_item_2_text">{$lang->authorization_popup_item_2_text|escape}</div>
                        </div>
                        <div class="info__item">
                            <div class="icon">{include 'svg.tpl' svgId='clock'}</div>
                            <div class="title"
                                 data-language="authorization_popup_item_3_title">{$lang->authorization_popup_item_3_title|escape}</div>
                            <div class="text"
                                 data-language="authorization_popup_item_3_text">{$lang->authorization_popup_item_3_text|escape}</div>
                        </div>
                        <div class="info__item">
                            <div class="icon">
                                {include 'svg.tpl' svgId='checkbox_list'}
                            </div>
                            <div class="title"
                                 data-language="authorization_popup_item_4_title">{$lang->authorization_popup_item_4_title|escape}</div>
                            <div class="text"
                                 data-language="authorization_popup_item_4_text">{$lang->authorization_popup_item_4_text|escape}</div>
                        </div>
                        <div class="info__item">
                            <div class="icon">{include 'svg.tpl' svgId='discount_label'}</div>
                            <div class="title"
                                 data-language="authorization_popup_item_5_title">{$lang->authorization_popup_item_5_title|escape}</div>
                            <div class="text"
                                 data-language="authorization_popup_item_5_text">{$lang->authorization_popup_item_5_text|escape}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
{/if}
