<div class="hidden">
    <div id="fn_pop_up_cart">
        <div id="fn_pop_up_cart_wrap" class="pop_up_cart">
            <div class="cart_popup_inner">
                {* The form heading *}
                <div class="popup_heading">
                    <div class="cart_check">{include file="svg.tpl" svgId="cart_check"}</div>
                    <span data-language="cart_pop_up_header">{$lang->cart_pop_up_header}</span>
                </div>
                <div class="cart_step_2_right_wrapper">
                    <div class="purchase_tr_popup">
                        {* Product image *}
                        <div class="purchase_image">
                            <a href="{$lang_link}products/{$purchase->product->url}">
                                <img class="cart_pp_img lazy lazy-bg" width="150" height="150" src="design/{$settings->theme}/images/no_image.png" loading="lazy">
                            </a>
                        </div>
                        {* Product name *}
                        <div class="purchase_details">
                            <a class="purchase_name fn_product_name" href="{$lang_link}products/{$purchase->product->url}">{$purchase->product->name|escape}</a>
                            <div class="sku">
                                <span data-language="product_sku">{$lang->product_sku}:</span>
                                <span class="fn_sku fn_sku_nubmer sku_nubmer"></span>
                            </div>
                            <div class="purchase_size_step_2">
                                <span data-language="product_sizes_text">{$lang->product_sizes_text}: </span>
                                <span class="fn_cart_size nowrap"></span>
                            </div>
                            <div class="purchase_size_step_2">
                                <span data-language="product_sizes_text">{$lang->product_color_text}: </span>
                                <span class="fn_cart_color nowrap">{$purchase->variant->color}</span>
                            </div>
                            
                            
                        </div>
                        {* Quantity *}
                            <div class="purchase_amount_step_2">
                                {* Extended price *}
                                <div class="purchase_sum">
                                    <span class="fn_cart_price nowrap"></span>
                                </div>
                            </div>
                    </div>
                </div>
                <div class="modal_cart_buttons">
                    <a class="button btn_black" href="{$lang_link}cart" data-language="to_cart">{$lang->to_cart}</a>
                    <button class="fn_close_modal button disable_button" data-language="back_to_shop" type="button">{$lang->back_to_shop}</button>
                </div> 
            </div>
        </div>
    </div>
</div>