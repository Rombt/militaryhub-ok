        {* //!! *}
        {assign var="created_ts" value=$product->created|strtotime}
        {assign var="target_ts" value="2021-12-31"|strtotime}

        {if $created_ts < $target_ts}
            {get_banner var="banner_before2021" group="before2021"}
            {if $banner_before2021->items}
                <div class="container hidden-md-down">
                    <div class="fn_banner_before2021 slick-banner">
                        {foreach $banner_before2021->items as $bi}
                            <div>
                                {if $bi->url}
                                    <a href="{$bi->url}" target="_blank">
                                {/if}
                                {if $bi->image}
                                    <img src="{$bi->image|resize:1170:390:false:$config->resized_banners_images_dir}" alt="{$bi->alt}" title="{$bi->title}">
                                {/if}
                                <span class="slick-name">
                                    {$bi->title}
                                </span>
                                {if $bi->description}
                                    <span class="slick-description">
                                        {$bi->description}
                                    </span>
                                {/if}
                                {if $bi->url}
                                    </a>
                                 {/if}
                            </div>
                        {/foreach}
                    </div>
                </div>
            {/if}
        {/if}

        <script>
            console.log({$product|json_encode nofilter});
        </script>
        {* Product preview *}
        <div class="preview fn_product">
            <div class="fn_transfer clearfix">
                {if $smarty.get.module == "WishlistView"}
                    <a href="#" class="fn_wishlist selected remove_link" data-id="{$product->id}">{include file='svg.tpl' svgId='remove_icon'} </a>
                 {/if}

                <div class="product_labels_container">
                
                    {if $product->featured}
                        <div class="featured_product_label">{$lang->featured_product_label}</div>
                    {/if}

                    {if $product->new_arrival}
                        <div class="new_arrival_label">{$lang->new_arrival_label}</div>
                    {/if}
    
                    {if {($product->variant->compare_price - $product->variant->price) > 0}}
                        <div class="discount_product_label">{$lang->discount_product_label}</div>
                    {/if}
    
                </div>

                {$is_lazyload = (!isset($is_products_content) || (isset($is_products_content) && $product_iteration|default:0 > 6))}

                {* Product image *}
                <a class="preview_image" href="{if $smarty.get.module=='ComparisonView'}{$product->image->filename|resize:800:600:w}{else}{$lang_link}products/{$product->url}{/if}" {if $smarty.get.module=='ComparisonView'}data-fancybox="group" data-caption="{$product->name|escape}"{/if}>
                    {if $product->image->filename}
                        <picture>
                            {if $is_lazyload}
                                <source media="(max-width: 480px)" type="image/webp" data-srcset="{$product->image->filename|resize:210:210:false:null:null:null:true}" srcset="{$rootUrl}/design/{$settings->theme|escape}/images/xloading.gif">
                                <source type="image/webp" data-srcset="{$product->image->filename|resize:270:270:false:null:null:null:true}" srcset="{$rootUrl}/design/{$settings->theme|escape}/images/xloading.gif">
                                <source media="(max-width: 480px)" type="image/jpeg" data-srcset="{$product->image->filename|resize:210:210}" srcset="{$rootUrl}/design/{$settings->theme|escape}/images/xloading.gif">
                                <source type="image/jpeg" data-srcset="{$product->image->filename|resize:270:270}" srcset="{$rootUrl}/design/{$settings->theme|escape}/images/xloading.gif">
                            {else}
                                <source media="(max-width: 480px)" type="image/webp" srcset="{$product->image->filename|resize:210:210:false:null:null:null:true}">
                                <source type="image/webp" srcset="{$product->image->filename|resize:270:270:false:null:null:null:true}">
                                <source media="(max-width: 480px)" type="image/jpeg" srcset="{$product->image->filename|resize:210:210}">
                                <source type="image/jpeg" srcset="{$product->image->filename|resize:270:270}">
                            {/if}

                            <img class="fn_img preview_img{if $is_lazyload} lazy lazy-bg{/if}" {if $is_lazyload} src="{$rootUrl}/design/{$settings->theme|escape}/images/xloading.gif" data-src="{$product->image->filename|resize:270:270}" {else} src="{$product->image->filename|resize:270:270}" {/if}
                            alt="{$product->name|escape}"
                            title="{$product->name|escape}"
                            {* loading="lazy" *}
                            fetchpriority="high">
                        </picture>
                    {else}
                        <img class="fn_img preview_img" src="design/{$settings->theme}/images/no_image.png" width="270" height="270" alt="{$product->name|escape}">
                    {/if}
                    {if $product->special}
                        <img class="promo_img" src="files/special/{$product->special}" alt="{$product->special|escape}" title="{$product->special|escape}">
                    {/if}
                </a>

                {* Wishlist *}
                {if $smarty.get.module != "WishlistView"}
                    {if $product->id|in_array:$wished_products}
                        <a href="#" data-id="{$product->id}" class="fn_wishlist wishlist_button selected" title="{$lang->remove_favorite}" data-result-text="{$lang->add_favorite}"></a>
                    {else}
                        <a href="#" data-id="{$product->id}" class="fn_wishlist wishlist_button" title="{$lang->add_favorite}" data-result-text="{$lang->remove_favorite}"></a>
                    {/if}
                {/if}

                {if !($product->variant->compare_price && ((($product->variant->compare_price - $product->variant->price) * 100)/$product->variant->compare_price) > 10)}
                    <div class="installments_icon_info">
                        {include file="svg.tpl" svgId="installment_icon"}
                    </div>
                {/if}

                <form class="fn_variants preview_form" action="/{$lang_link}cart" data-name="{$product->name|escape}" data-brand="{$product->brand->name|escape|default:''}" data-categories="     {if $product->category}
                    {foreach $product->category->path as $path}{$path->name|escape}{if !$path@last}; {/if}
                    {/foreach}
                    {/if}">
                    {* {if !$settings->is_preorder}
                        <p class="fn_not_preorder {if $product->variant->stock > 0} hidden{/if}" style="display:none;">
                            <span data-language="out_of_stock">{$lang->out_of_stock}</span>
                        </p>
                        {else}
                        <button class="button buy fn_is_preorder{if $product->variant->stock > 0} hidden{/if}" type="submit" data-language="pre_order" style="display:none;">{$lang->pre_order}</button>
                    {/if} *}

                    {* Submit cart button *}
                    <button class="button product_add_to_cart fn_is_stock  {* {if $product->variant->stock < 1} hidden {/if} *}" type="submit">{include file="svg.tpl" svgId="rmbt-mh-cart_icon"}</button>

                    {* Product name *}
                    <a class="product_name" data-product="{$product->id}" data-brand="     {if $product->brand}{$product->brand->name|escape}{/if}"
                        data-category="{if $product->category}
                            {foreach $product->category->path as $path}{$path->name|escape}{if !$path@last};{/if}
                            {/foreach}
                        {/if}"
                        href="{$lang_link}products/{$product->url}">{$product->name|escape}</a>
                    <div class="price_container">
                        {* Old price *}
                        <div class="old_price{if !$product->variant->compare_price} hidden{/if}">
                            <span class="fn_old_price">{$product->variant->compare_price|convert}</span>
                            <span>{$currency->sign|escape}</span>
                        </div>
                        {* Price *}
                        <div class="price">
                            <span class="fn_price">{$product->variant->price|convert}</span>
                            <span>{$currency->sign|escape}</span>
                        </div>
                    </div>

                </form>
            </div>
        </div>
