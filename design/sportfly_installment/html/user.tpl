{* Account page *}
{* The page title *}
{$meta_title = $lang->user_title scope=parent}
<div class="user_page">
    {* The page heading*}
    <div class="account_header_page">
        <h1 class="main_section_title"><span data-language="user_header">{$lang->user_header}</span></h1>
        {* Logout *}
        <a href="{$lang_link}user/logout" class="logout" data-language="user_logout">{$lang->user_logout}{include file="svg.tpl" svgId="logout" width="24" height="24"}</a>
    </div>


    <div class="profile_top_info">
        <div class="col-sm-12 col-md-6 col-lg-8">
            <div class="personal_info">
                <form enctype="multipart/form-data" method="post" class="fn_validate_register">
                    <div class="h3">
                        <span data-language="user_info">{$lang->user_info}</span>
                        <div class="edit-change_wrapper">
                            {include file="svg.tpl" svgId="edit_profile" width="16" height="16"}
                            <span class="edit-change" data-language="edit">{$lang->edit}</span>
                            <label class="hidden" for="user_save">
                                {$lang->form_save}
                                <input class="hidden" type="submit" id="user_save" name="user_save"
                                       data-language="form_save"
                                       value="{$lang->form_save}">
                            </label>
                            
                        </div>
                    </div>
                    <div class="user_info_wrapper">
                        <div class="user_info_container">
                            <div class="profile_img round_img">
                                {if $image}
                                    <img src="files/user_img/{$image}" alt="">
                                {else}
                                    {include file="svg.tpl" svgId="avatar" width="24" height="24"}
                                {/if}
                            </div>
                            <div>
                                <div class="edit_group user_img">
                                    <label class="hidden" for="image">
                                        <span data-language="load_photo">{$lang->load_photo}</span>
                                    </label>
                                    <input class="edit_input hidden" value="{$image|escape}" id="image" name="image"
                                           type="file"
                                           disabled/>
                                </div>
                            </div>
                        </div>
                        
                        <div class="user_info_groups">
                            <div class="user_info_group_items">
                                <div class="edit_group prof_name">
                                    <span class="form_placeholder" data-language="form_name">{$lang->form_name}</span>
                                    <input class="edit_input"
                                            value="{if $name|escape}{$name|escape}{else}Ваше ім'я{/if}" name="name"
                                            type="text"
                                            disabled/>
                                </div>
                                <div class="edit_group">
                                    <span class="form_placeholder" data-language="form_email">{$lang->form_email}</span>
                                    <input class="edit_input" value="{$email|escape}" name="email" type="text"
                                            disabled/>
                                </div>
                                <div class="user_info_group">
                                    <div class="edit_group">
                                        <span class="form_placeholder" data-language="form_phone">{$lang->form_phone}</span>
                                        <input class="edit_input" value="{if $phone}{$phone|escape}{else}-{/if}"
                                                name="phone" type="text"
                                                disabled/>
                                    </div>
                                    {* User's birthday *}
                                    <div class="edit_group">
                                        <span class="form_placeholder" data-language="user_birthday">{$lang->user_birthday}</span>
                                        <input class="edit_input" value="{if $birthday}{$birthday|escape}{else}-{/if}"
                                            name="birthday"
                                            type="date"
                                            disabled{if $birthday} readonly{/if}/>
                                    </div>
                                </div>
                            </div>
                            <div class="user_info_group_right">
                                {* User's address *}
                                <div class="edit_group">
                                    <span class="form_placeholder" data-language="form_address">{$lang->form_address}</span>
                                    <textarea class="edit_input" value="{if $address}{$address|escape}{else}-{/if}"
                                        name="address"
                                        type="text"
                                        disabled>{if $address}{$address|escape}{else}-{/if}</textarea>
                                </div>
                                <div class="lk_discount">
                                    <div class="discount_title"><span data-language="user_sale">{$lang->user_sale}</span></div>
                                    <div class="discount"><span>{$user->discount}%</span></div>
                                </div>
                                <div class="lk_discount">
                                    <div class="discount_title"><span data-language="user_bonuses">{$lang->user_bonuses}</span></div>
                                    <div class="discount"><span>{$user->bonuses|number_format:2:$settings->decimals_point:$settings->thousands_separator} {$currency->sign}</span></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
        <div class="col-sm-12 col-md-4 col-lg-4">
            <div class="lk_order">
                <div class="h3">
                    <span data-language="orders_title">{$lang->orders_title} ({$orders|count})</span>
                    {* <a href="{$lang_link}orders"><span data-language="main_look_all">{$lang->main_look_all}</span></a> *}
                </div>
                <div class="scroll_block">
                    {if $orders}
                        {foreach $orders as $order}
                            <div class="flex_row-jb">
                                <a href='{$language->label}/order/{$order->url}'>
                                    <span data-language="user_order_number">{$lang->user_order_number}</span>{$order->id}
                                </a>
                                <span>{$order->bonuses} {$order->bonuses|plural:$lang->order_bonus:$lang->order_bonuses:$lang->order_of_bonuses}</span>
                                <span>{$order->date|date}</span>
                            </div>
                        {/foreach}
                    {/if}
                </div>
                <div class="lk_bottom">
                    <span data-language="orders_info">{$lang->orders_info}</span>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-lg-7" style="display:none!important;">
            <div class="tech_help">
                <div class="h3"><span data-language="tech_help">{$lang->tech_help}</span></div>
                {* Page body *}
                {if $page->description}
                    <div class="col-lg-6 no_padding">
                        {* The page heading *}
                        <h1 class="h1">{if $page->name_h1|escape}{$page->name_h1|escape}{else}{$page->name|escape}{/if}</h1>

                        <div class="block padding">
                            {$page->description}
                        </div>
                    </div>
                {/if}

                {if $message_sent}
                    <div class="padding">
                        <div class="message_success">
                            <b>{$name|escape},</b> <span
                                    data-language="feedback_message_sent">{$lang->feedback_message_sent} {$user->email}.</span>
                        </div>
                    </div>
                {else}
                    {* Feedback form *}
                    <form id="captcha_id" method="post" class="fn_validate_feedback">
                        <div class="">
                            {* Form error messages *}
                            {if $error}
                                <div class="message_error">
                                    {if $error=='captcha'}
                                        <span data-language="form_error_captcha">{$lang->form_error_captcha}</span>
                                    {elseif $error=='empty_name'}
                                        <span data-language="form_enter_name">{$lang->form_enter_name}</span>
                                    {elseif $error=='empty_email'}
                                        <span data-language="form_enter_email">{$lang->form_enter_email}</span>
                                    {elseif $error=='empty_text'}
                                        <span data-language="form_enter_message">{$lang->form_enter_message}</span>
                                    {/if}
                                </div>
                            {/if}

                            {* User's name *}
                            <div class="form_group">
                                <input class="form_input placeholder_focus hidden"
                                       value="{if $user->name}{$user->name|escape}{else}{$name|escape}{/if}"
                                       name="name" type="text" data-language="form_name"/>
                            </div>

                            {* User's email *}
                            <div class="form_group">
                                <input class="form_input placeholder_focus hidden"
                                       value="{if $user->email}{$user->email|escape}{else}{$email|escape}{/if}"
                                       name="email" type="text" data-language="form_email"/>
                            </div>

                            {* User's message *}
                            <div class="form_group" style="display:none!important;">
                                    <textarea class="form_textarea placeholder_focus" rows="3" name="message"
                                              data-language="form_enter_message">{$message|escape}</textarea>
                            </div>

                            {* Captcha *}
                            {if $settings->captcha_feedback}
                                {if $settings->captcha_type == "v3"}
                                    <div class="captcha row" style="display: none;">
                                        <div class="fn_recaptchav3">
                                            <input type="hidden" name="recaptcha_token" value=""
                                                   class="fn_recaptcha_token"/>
                                        </div>
                                    </div>
                                {elseif $settings->captcha_type == "v2"}
                                    <div class="captcha row" style="">
                                        <div id="recaptcha1"></div>
                                    </div>
                                {elseif $settings->captcha_type == "default"}
                                    {get_captcha var="captcha_feedback"}
                                    <div class="captcha form_group">
                                        <div class="secret_number">{$captcha_feedback[0]|escape} + ?
                                            = {$captcha_feedback[1]|escape}</div>
                                        <span class="form_captcha">
                                    <input class="form_input input_captcha placeholder_focus" type="text"
                                           name="captcha_code" value="" data-language="form_enter_captcha"/>
                                    <span class="form_placeholder">{$lang->form_enter_captcha}*</span>
                                </span>
                                    </div>
                                {/if}
                            {/if}
                            <input type="hidden" name="feedback" value="1">

                            <div class="row">
                                <div class="col-sm-4 col-md-12 col-lg-4">
                                    {* Submit button *}
                                    <input class="button g-recaptcha" type="submit" name="feedback"
                                           data-language="form_send"
                                           {if $settings->captcha_type == "invisible"}data-sitekey="{$settings->public_recaptcha_invisible}"
                                           data-badge='bottomleft' data-callback="onSubmit"{/if}
                                           value="{$lang->form_send}"/>
                                </div>
                                <div class="col-sm-8 col-md-12 col-lg-8">
                                    <span class="tech_help_text"
                                          data-language="tech_help_text">{$lang->tech_help_text}</span>
                                </div>
                            </div>

                        </div>
                    </form>
                {/if}
            </div>
        </div>
    </div>

    <div class="profile_bottom_info">
     <div class="col-sm-12 col-md-8 col-lg-8">
            <div class="lk_order">
                <div class="h3">
                    <span data-language="referrals">{$lang->referrals} (<span class="count">{$referrals|count}</span>)</span>
                    <div class="lk_order__buttons">
                        <a href="javascript:" class="fn_referral_add" data-infinity="0"><span data-language="referrals_add">{$lang->referrals_add}</span></a>
                        {if $request_infinity_referral}
                            <a href="javascript:" class="fn_referral_add" data-infinity="1"><span data-language="referrals_add_infinity">{$lang->referrals_add_infinity}</span></a>
                        {/if}
                    </div>
                </div>
                <div class="scroll_block">
                    {if $referrals}
                        {foreach $referrals as $referral}
                            {include file="user_referral.tpl" referral=$referral}
                        {/foreach}
                    {/if}
                </div>
                <div class="lk_bottom">
                    <span data-language="referrals_info">{$lang->referrals_info}</span>
                </div>
            </div>
        </div>
        <div class="hidden">
            <div id="fn_popup_referral">
                <div class="popup-box">
                    <div class="popup-box__container popup_referral">
                        <div class="popup_heading text-center">
                            <div class="success hidden">{$lang->user_referral_created}</div>
                            <div class="failure hidden">{$lang->user_referral_error}</div>
                        </div>
                        <div class="referral_url">{include file='svg.tpl' svgId='copy'}<span class="fn_url"></span><span
                                    class="referral_success" style="display: none"
                                    data-language="referral_success">{$lang->referral_success}</span></div>
                        <div class="fn_error"></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-12 col-md-4 col-lg-4">
            <div class="lk_feedback">
                <div class="h3"><span data-language="main_reviews_title">{$lang->main_reviews_title} ({$user->q_comment})</span></div>
                {if {$user->q_comment} > 0}
                    <span class="thx" data-language="thx_comment">{$lang->thx_comment}</span>
                {/if}
                <div class="lk_bottom">
                    <span data-language="comment_info">{$lang->comment_info}</span>
                </div>
            </div>
        </div>
    </div>

    {if $wished_products|count}
        <div class="main_section">
            <div class="main_section_title">
                <span data-language="favorite">{$lang->favorite}</span>
                <a class="main_section_link" href="{$lang_link}wishlist" data-language="main_look_all">{$lang->main_look_all}</a>
            </div>
            <div class="fn_products_slide main_products swiper">
                <div class="swiper-wrapper">
                    {foreach $wished_products as $product}
                        <div class="products_item swiper-slide">
                            {include "product_list.tpl" product = $product}
                        </div>
                    {/foreach}
                </div>
                <div class="swiper-button-next"></div>
                <div class="swiper-button-prev"></div>
                <div class="swiper-pagination"></div>
            </div>
        </div>
    {/if}
</div>