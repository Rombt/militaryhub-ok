<div class="purchase">
    <div class="purchase_tr_head">
        <div class="purchase_th" data-language="cart_head_img">{$lang->cart_head_img}</div>
        <div class="purchase_th" data-language="cart_head_name">{$lang->cart_head_name}</div>
        <div class="purchase_th" data-language="product_sizes_text">{$lang->product_sizes_text}</div>
        <div class="purchase_th" data-language="cart_head_price">{$lang->cart_head_price}</div>
        <div class="purchase_th" data-language="cart_head_total">{$lang->cart_head_total}</div>
        <div class="purchase_th"></div>
    </div>

    {foreach $cart->purchases as $purchase}
        <div class="purchase_tr fn_purchase">
            {* Product image *}
            <div class="purchase_image">
                <a href="{$lang_link}products/{$purchase->product->url}">
                    {if $purchase->variant->image}
                        <img src="{$purchase->variant->image->filename|resize:150:150:false:$config->resized_variants_dir}" alt="{$purchase->product->name|escape}" title="{$purchase->product->name|escape}">
                    {elseif $purchase->product->image}
                        <img src="{$purchase->product->image->filename|resize:150:150}" alt="{$purchase->product->name|escape}" title="{$purchase->product->name|escape}">
                    {else}
                        <img width="150" height="150" src="design/{$settings->theme}/images/no_image.png" alt="{$purchase->product->name|escape}" title="{$purchase->product->name|escape}">
                    {/if}
                </a>
            </div>

            {* Product name *}
            <div class="purchase_details">
                <a class="purchase_name product_name"
                    data-brand="{if $purchase->product->brand}{$purchase->product->brand->name|escape}{/if}"
                    data-category="{if $purchase->product->category}{foreach $purchase->product->category->path as $path}{$path->name|escape}{if !$path@last};{/if}{/foreach}{/if}"
                    href="{$lang_link}products/{$purchase->product->url}">{$purchase->product->name|escape}</a>
                {if $purchase->variant->stock == 0}<span class="preorder_label">{$lang->product_pre_order}</span>{/if}
                <div class="sku {if !$purchase->variant->sku} hidden{/if}">
                    <span data-language="product_sku">{$lang->product_sku}:</span>
                    <span class="fn_sku sku_nubmer" data-variant="{$purchase->variant->id}">{$purchase->variant->sku|escape}</span>
                </div>
                 {if $purchase->brand}
                    <div class="brand_img_wrap center-flex clearfix hidden">
                        <a href="{$lang_link}catalog/{$category->url}/brand-{$brand->url}" class="fn_brand" data-brand="{$brand->name|escape}">
                            {if $purchase->brand->image}
                                <img class="brand_img"
                                    src="{$purchase->brand->image|resize:80:80:false:$config->resized_brands_dir}"
                                    alt="{$purchase->brand->name|escape}">
                            {else}
                                <span>Бренд: {$purchase->brand->name}</span>
                            {/if}
                        </a>
                    </div>
                {/if}
                 <div class="purchase_size_step_2 hidden">
                    <span data-language="product_sizes_text">{$lang->product_sizes_text}: </span>
                    <span class="nowrap">{$purchase->variant->size}</span>
                </div>
                 <div class="purchase_size_step_2 hidden">
                    <span data-language="product_sizes_text">{$lang->product_color_text}: </span>
                    <span class="nowrap">{$purchase->variant->color}</span>
                </div>
                {* Quantity *}
                <div class="purchase_amount">
                    <div class="fn_product_amount{if $settings->is_preorder} fn_is_preorder{/if} amount">
                        <span class="minus">{include file="svg.tpl" svgId="minus_icon"}</span>
                        <input class="input_amount" type="text" data-id="{$purchase->variant->id}"
                            name="amounts[{$purchase->variant->id}]" value="{$purchase->amount}"
                            onblur="ajax_change_amount(this, {$purchase->variant->id});"
                            data-price="{$purchase->variant->price|convert:$currency->id:false}"
                            data-max="{$purchase->variant->stock}"
                            data-sku="{$purchase->variant->sku|escape}"
                            data-amount="{$purchase->amount}"
                            data-variant-name="{$purchase->variant->name|escape}"
                        >
                        <span class="plus">{include file="svg.tpl" svgId="plus_icon"}</span>
                    </div>
                    <div class="purchase_sum hidden">
                        <span class="nowrap">{($purchase->variant->price*$purchase->amount)|convert} {$currency->sign}</span>
                    </div>
                </div>

            </div>

            <div class="purchase_size">
                <span class="nowrap">{$purchase->variant->size}</span>
            </div>

            {* Price per unit *}
            <div class="purchase_price_per_unit">
                <span class="nowrap">{($purchase->variant->price)|convert} {$currency->sign} {if $purchase->variant->units}/ {$purchase->variant->units|escape}{/if}</span>
            </div>

            {* Extended price *}
            <div class="purchase_sum">
                <span class="nowrap">{($purchase->variant->price*$purchase->amount)|convert} {$currency->sign}</span>
            </div>

            {* Remove button *}
            <div class="purchase_remove">
                <a href="{$lang_link}cart/remove/{$purchase->variant->id}" onclick="ajax_remove({$purchase->variant->id});return false;" title="{$lang->cart_remove}">
                    {include file='svg.tpl' svgId='remove_icon'}
                </a>
            </div>
        </div>
    {/foreach}
</div>
