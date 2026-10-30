
{* Product preview *}
<div class="preview fn_product">
    <div class="fn_transfer clearfix rmbt_wrap_preview_image">

        {$is_lazyload = (!isset($is_products_content) || (isset($is_products_content) && $product_iteration|default:0 > 6))}

        {* Product image *}
        <a class="preview_image" href="{if $smarty.get.module=='ComparisonView'}{$product->image->filename|resize:800:600:w}{else}{$lang_link}products/{$product->url}{/if}" {if $smarty.get.module=='ComparisonView'}data-fancybox="group" data-caption="{$product->name|escape}"{/if}>
            {if $product->image->filename}
                <picture>
                    {if $is_lazyload}
                        <source media="(max-width: 480px)" type="image/webp" data-srcset="{$product->image->filename|resize:230:230:false:null:null:null:true}" srcset="{$rootUrl}/design/{$settings->theme|escape}/images/xloading.gif">
                        <source type="image/webp" data-srcset="{$product->image->filename|resize:375:375:false:null:null:null:true}" srcset="{$rootUrl}/design/{$settings->theme|escape}/images/xloading.gif">
                        <source media="(max-width: 480px)" type="image/jpeg" data-srcset="{$product->image->filename|resize:230:230}" srcset="{$rootUrl}/design/{$settings->theme|escape}/images/xloading.gif">
                        <source type="image/jpeg" data-srcset="{$product->image->filename|resize:375:375}" srcset="{$rootUrl}/design/{$settings->theme|escape}/images/xloading.gif">
                    {else}
                        <source media="(max-width: 480px)" type="image/webp" srcset="{$product->image->filename|resize:230:230:false:null:null:null:true}">
                        <source type="image/webp" srcset="{$product->image->filename|resize:375:375:false:null:null:null:true}">
                        <source media="(max-width: 480px)" type="image/jpeg" srcset="{$product->image->filename|resize:230:230}">
                        <source type="image/jpeg" srcset="{$product->image->filename|resize:375:375}">
                    {/if}

                    <img class="fn_img preview_img{if $is_lazyload} lazy lazy-bg{/if}" {if $is_lazyload} src="{$rootUrl}/design/{$settings->theme|escape}/images/xloading.gif" data-src="{$product->image->filename|resize:375:375}" {else} src="{$product->image->filename|resize:375:375}" {/if}
                    alt="{$product->name|escape}"
                    title="{$product->name|escape}"
                    {* loading="lazy" *}
                    fetchpriority="high">
                </picture>
            {else}
                <img class="fn_img preview_img" src="design/{$settings->theme}/images/no_image.png" alt="{$product->name|escape}">
            {/if}
            {if $product->special}
                <img class="promo_img" src="files/special/{$product->special}" alt="{$product->special|escape}" title="{$product->special|escape}">
            {/if}
        </a>

        {* Wishlist *}
        {* {if $smarty.get.module != "WishlistView"}
            {if $product->id|in_array:$wished_products}
                <a href="#" data-id="{$product->id}" class="fn_wishlist wishlist_button selected" title="{$lang->remove_favorite}" data-result-text="{$lang->add_favorite}"></a>
            {else}
                <a href="#" data-id="{$product->id}" class="fn_wishlist wishlist_button" title="{$lang->add_favorite}" data-result-text="{$lang->remove_favorite}"></a>
            {/if}
        {/if} *}

    </div>

    <div class="rmbt_wrap_text">
        <div class="product_discount">{$lang->main_discount_products} - {(($product->variant->compare_price - $product->variant->price) / $product->variant->compare_price * 100)|round}%</div>
        <div class="product_name">{$product->name}</div>
        <div class="product_old_price">{$product->variant->compare_price|convert}<span itemprop="priceCurrency" content="{$currency->code|escape}">{$currency->sign|escape}</span></div>
        <div class="product_price">{$product->variant->price|convert}<span itemprop="priceCurrency" content="{$currency->code|escape}">{$currency->sign|escape}</span></div>
        {include
            file="btn_link.tpl"
            text=$lang->product_add_cart|default:''
            link=""|default:''
            icon_id='long_arrow'
        }
    </div>
</div>
