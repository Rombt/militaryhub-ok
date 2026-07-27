<div class="purchase">
    {foreach $purchases as $purchase}
        <div class="purchase_tr">
            {* Product image *}
            <div class="purchase_image">
                <a href="{$lang_link}products/{$purchase->product->url}">
                    {if $purchase->variant->image}
                        <img src="{$purchase->variant->image->filename|resize:150:150}" alt="{$purchase->product->name|escape}" title="{$purchase->product->name|escape}">
                    {elseif $purchase->product->image}
                        <img src="{$purchase->product->image->filename|resize:150:150}" alt="{$purchase->product->name|escape}" title="{$purchase->product->name|escape}">
                    {else}
                        <img width="150" height="150" src="design/{$settings->theme}/images/no_image.png" alt="{$purchase->product->name|escape}" title="{$purchase->product->name|escape}">
                    {/if}
                </a>
            </div>

            {* Product name *}
            <div class="purchase_details">
                <a class="purchase_name" href="{$lang_link}products/{$purchase->product->url}">{$purchase->product->name|escape}</a>
                <div class="sku{if !$purchase->variant->sku} hidden{/if}">
                    <span data-language="product_sku">{$lang->product_sku}:</span>
                    <span class="fn_sku sku_nubmer" data-variant="{$purchase->variant->id}">{$purchase->variant->sku|escape}</span>
                </div>
                 {if $purchase->brand}
                    <div class="brand_img_wrap center-flex clearfix">
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
                 <div class="purchase_size_step_2">
                    <span data-language="product_sizes_text">{$lang->product_sizes_text}: </span>
                    <span class="nowrap">{$purchase->variant->size}</span>
                </div>
                 <div class="purchase_size_step_2">
                    <span data-language="product_sizes_text">{$lang->product_color_text}: </span>
                    <span class="nowrap">{$purchase->variant->color}</span>
                </div>
                {* Quantity *}
                <div class="purchase_amount_step_2">
                    <span>{$purchase->amount} {$purchase->variant->units|escape}</span>
                    {* Extended price *}
                    <div class="purchase_sum">
                        <span class="nowrap">{($purchase->variant->price*$purchase->amount)|convert} {$currency->sign}</span>
                    </div>
                </div>
                
            </div>
        </div>
    {/foreach}
</div>
