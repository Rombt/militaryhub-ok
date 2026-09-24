{* The cart page template *}
{* The page title *}
{$meta_title = $lang->cart_title scope=parent}

{if $cart->purchases}
    {* Cart form *}
    <form id="captcha_id" method="post" name="cart" class="fn_validate_cart">
        {* The page heading *}
        <h1 class="h1 main_section_title" {if $error}style="display:  none;"{/if}><span data-language="cart_header">{$lang->cart_header}</span></h1>

        <div class="cart_container" {if $error}style="display:  none;"{/if}>
            {* The list of products in the cart *}
            <div id="fn_purchases">
                {include file='cart_purchases.tpl'}
            </div>
            <div class="cart_right_wrapper">
                <div class="cart_right_container">
                    <span class="cart_right_header" data-language="cart_right_header">{$lang->cart_right_header}</span>
                    <div class="cart_coupon_percent_wrapper">
                        <span data-language="cart_initial_price">{$lang->cart_initial_price}:</span>
                        {foreach $cart->purchases as $purchase}
                           {$total_sum = $total_sum + ($purchase->variant->price*$purchase->amount)}
                        {/foreach}
                        <span class="total_sum nowrap">{$total_sum|convert} {$currency->sign|escape}</span>
                    </div>
                    {* Discount *}
                    {if $user->discount}
                        <div class="cart_coupon_percent_wrapper">
                            <div class="text_left" data-language="cart_discount">{$lang->cart_discount}</div>
                            <div>{$user->discount}%</div>
                        </div>
                    {/if}

                    {* Coupon *}
                    {if $coupon_request}
                        {if $cart->coupon_discount > 0}
                            <div class="cart_coupon_percent_wrapper">
                                <div class="text_left" data-language="cart_coupon">{$lang->cart_coupon} <span class="cart_coupon_percent">{$cart->coupon->coupon_percent|escape} %</span></div>
                                <div>{$cart->coupon_discount|convert} {$currency->sign|escape}</div>
                            </div>
                        {/if}
                    {/if}

                    <div class="purchase_total">
                        <span data-language="cart_total_price">{$lang->cart_total_price}:</span>
                        <span class="total_sum nowrap">{$cart->total_price|convert} {$currency->sign|escape}</span>
                    </div>

                    {if $coupon_request}
                        <div class="coupon_wrapper">
                            <button class="fn_switch show_coupon_btn" type="button" data-language="show_coupon_button">{$lang->show_coupon_button}<span class="dropdown_icon"></span></button>
                            <div class="fn_coupon_show coupon_dropdown">
                                {* Coupon error messages *}
                                {if $coupon_error}
                                    <div class="message_error">
                                        {if $coupon_error == 'invalid'}
                                            {$lang->cart_coupon_error}
                                        {/if}
                                    </div>
                                {/if}
                                {if $cart->coupon->min_order_price > 0}
                                    <div class="message_success">
                                        {$lang->cart_coupon} {$cart->coupon->code|escape} {$lang->cart_coupon_min} {$cart->coupon->min_order_price|convert} {$currency->sign|escape}
                                    </div>
                                {/if}
                                {* Coupon field *}
                                <input class="fn_coupon input_coupon" type="text" name="coupon_code" value="{$cart->coupon->code|escape}" placeholder="{$lang->cart_coupon}">
                                <input class="coupon_button btn_black fn_sub_coupon" type="button" value="{$lang->cart_purchases_coupon_apply}">
                            </div>
                        </div>
                    {/if}

                    <button class="fn_place_order fn_cart_checkout place_order_button" type="button" data-language="place_order_button">{$lang->place_order_button}</button>
                </div>

                <span class="free_delivery" data-language="free_delivery_text">{include file="svg.tpl" svgId="delivery_icon"}{$lang->free_delivery_text}</span>
                <div class="advantages_items">
                    <button class="fn_advantages_item_1 advantages_item" data-language="advantages_text_1" type="button">{include file="svg.tpl" svgId="advantages_icon_1"}{$lang->advantages_text_1}</button>
                    <button class="fn_advantages_item_2 advantages_item" data-language="advantages_text_2" type="button">{include file="svg.tpl" svgId="advantages_icon_2"}{$lang->advantages_text_2}</button>
                    <button class="fn_advantages_item_3 advantages_item" data-language="advantages_text_3" type="button">{include file="svg.tpl" svgId="advantages_icon_3"}{$lang->advantages_text_3}</button>
                    <button class="fn_advantages_item_4 advantages_item" data-language="advantages_text_4" type="button">{include file="svg.tpl" svgId="advantages_icon_4"}{$lang->advantages_text_4}</button>
                </div>
            </div>
        </div>

        <div class="fn_cart_step_show cart_step_2" {if $error}style="display:  block;"{/if}>
            <div class="h1 main_section_title"><span data-language="cart_header_step_2">{$lang->cart_header_step_2}</span></div>
            <div class="cart_step_2_wrapper">
                <div class="cart_step_2_left_wrapper">
                    {* The form heading *}
                    <div class="cart_form_header" data-language="cart_form_header">{$lang->cart_form_header}</div>
                    <div class=""> 
                        {* Form error messages *}
                        {if $error}
                            <div class="message_error">
                            {* {$error|var_dump} *}
                                {if $error == 'empty_name'}
                                    <span data-language="form_enter_name">{$lang->form_enter_name}</span>
                                {/if}
                                {if $error == 'empty_email'}
                                    <span data-language="form_enter_email">{$lang->form_enter_email}</span>
                                {/if}
                                {if $error == 'captcha'}
                                    <span data-language="form_error_captcha">{$lang->form_error_captcha}</span>
                                {/if}
                                {if $error == 'empty_phone'}
                                    <span data-language="form_error_phone">{$lang->form_error_phone}</span>
                                {/if}
                                {if $error == 'empty_store_id'}
                                    <span data-language="form_enter_store">{$lang->form_enter_store}</span>
                                {/if}
                                {if $error == 'empty_bonuses'}
                                    <span data-language="form_error_bonuses">{$lang->form_error_bonuses}</span>
                                {/if}
                                {if $error == 'empty_payment_method'}
                                    <span data-language="form_enter_payment_method">{$lang->form_enter_payment_method}</span>
                                {/if}
                            </div>
                        {/if}

                        <div class="row">
                            {* User's name *}
                            <div class="col-sm-12">
                                <div class="form_group">
                                    <span class="form_placeholder">{$lang->cart_form_name}*</span>
                                    <input class="form_input placeholder_focus" name="name" type="text" value="{$name|escape}" data-language="cart_form_name" >
                                </div>
                            </div>
                            {* User's phone *}
                            <div class="col-sm-6">
                                <div class="form_group">
                                    <span class="form_placeholder">{$lang->form_phone}*</span>
                                    <input class="form_input placeholder_focus" name="phone" type="text" value="{$phone|escape}" data-language="form_phone" >
                                </div>
                            </div>
                            {* User's email *}
                            <div class="col-sm-6">
                                <div class="form_group">
                                    <span class="form_placeholder">{$lang->form_email}*</span>
                                    <input class="form_input placeholder_focus" name="email" type="text" value="{$email|escape}" data-language="form_email" > 
                                </div>
                            </div>
                        </div>

                        <div class="row" style="display:none;">
                            {* User's address *}
                            <div class="col-sm-6">
                                <div class="form_group">
                                    <span class="form_placeholder">{$lang->form_address}</span>
                                    <input class="form_input placeholder_focus" name="address" type="text" value="{$address|escape}" data-language="form_address" >
                                </div>
                            </div>
                        </div>

                        {* User's message *}
                        <div class="form_group" style="display:none;">
                            <span class="form_placeholder">{$lang->cart_order_comment}</span>
                            <textarea class="form_textarea placeholder_focus" rows="5" name="comment" data-language="cart_order_comment" >{$comment|escape}</textarea>
                        </div>

                        {* Captcha *}
                        {if $settings->captcha_cart}
                            {if $settings->captcha_type == "v3"}
                                <div class="captcha row" style="display: none;">
                                    <div class="fn_recaptchav3">
                                        <input type="hidden" name="recaptcha_token"  value="" class="fn_recaptcha_token" />
                                    </div>
                                </div>
                            {elseif $settings->captcha_type == "v2"}
                                <div class="captcha row" style="">
                                    <div id="recaptcha1"></div>
                                </div>
                            {elseif $settings->captcha_type == "default"}
                                {get_captcha var="captcha_cart"}
                                <div class="captcha">
                                    <div class="secret_number">{$captcha_cart[0]|escape} + ? =  {$captcha_cart[1]|escape}</div>
                                    <span class="form_captcha">
                                        <input class="form_input input_captcha placeholder_focus" type="text" name="captcha_code" value="" data-language="form_enter_captcha" >
                                        <span class="form_placeholder">{$lang->form_enter_captcha}*</span>
                                    </span>
                                </div>
                            {/if}
                        {/if}
                    </div> 
                    {* Delivery and Payment *}
                    <div id="fn_ajax_deliveries">
                        {include file='cart_deliveries.tpl'}
                        {* Submit button *}
                        <input class="button btn_black desktop_hidden g-recaptcha" type="submit" name="checkout" data-language="cart_checkout" {if $settings->captcha_type == "invisible"}data-sitekey="{$settings->public_recaptcha_invisible}" data-badge='bottomleft' data-callback="onSubmit"{/if} value="{$lang->cart_checkout}">
                    </div>
                </div>
                <div class="cart_step_2_right_wrapper">
                    <div class="mobile_cart_wrap">
                        <div class="mobile_cart_heading fn_switch">
                            <span data-language="cart_header">{$lang->cart_header} <span class="total_sum nowrap">{$cart->total_price|convert} {$currency->sign|escape}</span></span>
                            <span class="dropdown_icon"></span>
                        </div>
                        <div class="mobile_cart_purchases">
                            {include file='cart_purchases_step_2.tpl'}
                            <div class="cart_coupon_percent_wrapper">
                                <span data-language="cart_head_total">{$lang->cart_head_total}:</span>
                                <span class="total_sum nowrap">{$total_sum|convert} {$currency->sign|escape}</span>
                            </div>
                            {* Discount *}
                            {if $user->discount}
                                <div class="cart_coupon_percent_wrapper">
                                    <div class="text_left" data-language="cart_discount">{$lang->cart_discount}</div>
                                    <div>{$user->discount}%</div>
                                </div>
                            {/if}
                            {* Coupon *}
                            {if $coupon_request}
                                {if $cart->coupon_discount > 0}
                                    <div class="cart_coupon_percent_wrapper">
                                        <div class="text_left" data-language="cart_coupon">{$lang->cart_coupon} <span class="cart_coupon_percent">{$cart->coupon->coupon_percent|escape} %</span></div>
                                        <div>{$cart->coupon_discount|convert} {$currency->sign|escape}</div>
                                    </div>
                                {/if}
                            {/if}
                            <div class="purchase_total">
                                <span data-language="cart_total_price">{$lang->cart_total_price}:</span>
                                <span class="total_sum nowrap"><span class="fn_total_cart"{if $cart->max_bonuses > 0 && $user->bonuses > 0}data-total_bonuses="{($cart->total_price - $cart->max_bonuses|floor)|convert}"{/if} data-total="{$cart->total_price|convert}">{if $cart->max_bonuses > 0 && $user->bonuses > 0}{($cart->total_price - $cart->max_bonuses|floor)|convert} {else}{$cart->total_price|convert} {/if}</span> {$currency->sign|escape}</span>
                            </div>

                            {* Bonuses *}
                            {if $cart->max_bonuses > 0 && $user->bonuses > 0}
                                <div class="bonuses_wrapper">
                                    {* <div class="show_coupon_btn" data-language="form_bonuses_title">{$lang->form_bonuses_title}</div> *}
                                    <div class="form_input_checkbox cart_checkbox">
                                        <label for="user_bonuses" class="checkbox_label">
                                            <input id="user_bonuses" type="checkbox" class="checkbox_input" name="offer" value="1" checked>
                                            <span class="checkbox_box"></span>
                                            <span class="label_description">
                                                <span data-language="cart_bonuses">{$lang->cart_bonuses} ({$cart->max_bonuses|floor|number_format:0:$settings->decimals_point:$settings->thousands_separator} {$cart->max_bonuses|plural:$lang->order_bonus:$lang->order_bonuses:$lang->order_of_bonuses})</span>
                                            </span>
                                        </label>
                                    </div>
                                    <input class="input_coupon hidden" name="bonuses" type="number" min="0" max="{$cart->max_bonuses}" value="{$cart->max_bonuses|floor}" placeholder="{$lang->form_bonuses|escape}" >
                                </div>
                            {/if}
                            <div id="fn_ajax_payments">
                                {if  $is_mobile === false || $is_tablet === true}
                                    {include file="cart_payments.tpl"}
                                {/if}
                            </div>
                        </div> 
                     </div>
                     <input type="hidden" name="checkout" value="1">
                    {* Submit button *}
                    <input class="button btn_black mobile_hidden g-recaptcha" type="submit" name="checkout" data-language="cart_checkout" {if $settings->captcha_type == "invisible"}data-sitekey="{$settings->public_recaptcha_invisible}" data-badge='bottomleft' data-callback="onSubmit"{/if} value="{$lang->cart_checkout}">
                </div>
            </div>
        </div>   
    </form>
{else}
    <div class="block"> 
        {* The page heading *}
        <h1 class="h1 main_section_title"><span data-language="cart_header">{$lang->cart_header}</span></h1>
        <p class="block" data-language="cart_empty">{$lang->cart_empty}</p>
    </div>
{/if}


<div class="hidden">
    <div id="fn_advantages_item_1">
        <div class="advantages_item_wrapper">
            <div class="h3" data-language="clothing_care_title">{$lang->clothing_care_title}</div>
            <img src="design/{$settings->theme|escape}/images/clothing-care.png" alt="{$lang->clothing_care_img_alt}"/>
        </div>
    </div>
</div>

<div class="hidden">
    <div id="fn_advantages_item_2">
        <div class="advantages_item_wrapper">
            <div class="h3" data-language="advantages_item_guarantee_title">{$lang->advantages_item_guarantee_title}</div>
            <p data-language="advantages_item_guarantee_info">{$lang->advantages_item_guarantee_info}</p>
        </div>
    </div>
</div>

<div class="hidden">
    <div id="fn_advantages_item_3">
        <div class="advantages_item_wrapper">
            <div class="h3" data-language="advantages_item_payment_title">{$lang->advantages_item_payment_title}</div>
            <p data-language="advantages_item_payment_info">{$lang->advantages_item_payment_info}</p>
        </div>
    </div>
</div>

<div class="hidden">
    <div id="fn_advantages_item_4">
        <div class="advantages_item_wrapper">
            <div class="h3" data-language="advantages_original_goods_title">{$lang->advantages_original_goods_title}</div>
            <p data-language="advantages_original_goods_info">{$lang->advantages_original_goods_info}</p>
        </div>
    </div>
</div>


{if !$user}
    {get_banner var="banner_cart_popup" group="cart_popup"}
    {if $banner_cart_popup->items}
        <div class="cart_popup_block">
            <button class="cart_popup_content fn_modal_auth" title="{$lang->index_login}" type="button">
                {foreach $banner_cart_popup->items as $bi}
                    {if $bi->image}
                        <img src="{$bi->image|resize:1170:390:false:$config->resized_banners_images_dir}" alt="{$bi->alt}" title="{$bi->title}"/>
                    {/if}
                {/foreach}
            </button>
            <span class="close_informer">{include file="svg.tpl" svgId="close_icon"}</span>
        </div>
    {/if}
{/if}