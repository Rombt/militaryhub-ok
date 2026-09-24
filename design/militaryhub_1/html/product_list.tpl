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
                                        <img src="{$bi->image|resize:1170:390:false:$config->resized_banners_images_dir}"
                                             alt="{$bi->alt}" title="{$bi->title}">
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


{* Product preview *}
<div class="preview fn_product">
    <div class="fn_transfer clearfix">
        {if $smarty.get.module == "WishlistView"}
            <a href="#" class="fn_wishlist selected remove_link"
               data-id="{$product->id}">{include file='svg.tpl' svgId='remove_icon'} </a>
        {/if}


        {$is_lazyload = (!isset($is_products_content) || (isset($is_products_content) && $product_iteration|default:0 > 6))}

        {* Product image *}
        <a class="preview_image"
           href="{if $smarty.get.module=='ComparisonView'}{$product->image->filename|resize:800:600:w}{else}{$lang_link}products/{$product->url}{/if}"
           {if $smarty.get.module=='ComparisonView'}data-fancybox="group" data-caption="{$product->name|escape}"{/if}>
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

                    <img
                        class="fn_img preview_img{if $is_lazyload} lazy lazy-bg{/if}"
                        {if $is_lazyload}
                            src="{$rootUrl}/design/{$settings->theme|escape}/images/xloading.gif"
                            data-src="{$product->image->filename|resize:270:270}"
                        {else}
                            src="{$product->image->filename|resize:270:270}"
                        {/if}
                        alt="{$product->name|escape}"
                        title="{$product->name|escape}"
                        {* loading="lazy" *}
                        fetchpriority="high">
                </picture>
            {else}
                <img class="fn_img preview_img" src="design/{$settings->theme}/images/no_image.png" width="270"
                     height="270" alt="{$product->name|escape}">
            {/if}
            {if $product->special}
                <img class="promo_img" src="files/special/{$product->special}" alt="{$product->special|escape}"
                     title="{$product->special|escape}">
            {/if}
        </a>

        {* Wishlist *}
        {if $smarty.get.module != "WishlistView"}
            {if $product->id|in_array:$wished_products}
                <a href="#" data-id="{$product->id}" class="fn_wishlist wishlist_button selected"
                   title="{$lang->remove_favorite}" data-result-text="{$lang->add_favorite}"></a>
            {else}
                <a href="#" data-id="{$product->id}" class="fn_wishlist wishlist_button" title="{$lang->add_favorite}"
                   data-result-text="{$lang->remove_favorite}"></a>
            {/if}
        {/if}

        {if !($product->variant->compare_price && ((($product->variant->compare_price - $product->variant->price) * 100)/$product->variant->compare_price) > 10)}
            <div class="installments_icon_info">
                {include file="svg.tpl" svgId="installment_icon"}
            </div>
        {/if}

        <form class="fn_variants preview_form" action="/{$lang_link}cart" data-name="{$product->name|escape}" data-brand="{$product->brand->name|escape|default:''}" data-categories="{if $product->category}{foreach $product->category->path as $path}{$path->name|escape}{if !$path@last};{/if}{/foreach}{/if}">
            {* {if !$settings->is_preorder}
                <p class="fn_not_preorder {if $product->variant->stock > 0} hidden{/if}" style="display:none;">
                    <span data-language="out_of_stock">{$lang->out_of_stock}</span>
                </p>
            {else}
                <button class="button buy fn_is_preorder{if $product->variant->stock > 0} hidden{/if}" type="submit" data-language="pre_order" style="display:none;">{$lang->pre_order}</button>
            {/if} *}

            {* Submit cart button *}
            <button class="button product_add_to_cart btn_black fn_is_stock{if $product->variant->stock < 1} hidden{/if}"
                    type="submit"><span data-language="add_to_cart">{$lang->add_to_cart}</span></button>
            {* Product variants *}
            {* <select name="variant" class="fn_variant variant_select {if $product->variants|count == 1}hidden{/if}">
                {foreach $product->variants as $v}
                    <option value="{$v->id}" data-price="{$v->price|convert}" data-stock="{$v->stock}"{if $v->compare_price > 0} data-cprice="{$v->compare_price|convert}"{/if}{if $v->sku} data-sku="{$v->sku|escape}"{/if}>{if $v->name}{$v->name|escape}{else}{$product->name|escape}{/if}</option>
                {/foreach}
            </select> *}
            {* Product variants *}
           <div class="product_preview__variants">
                <div class="product__variants_slider swiper">
                    <div class="swiper-wrapper">
                       {assign var="first_color" value=$product->colors|first}
                        {foreach $product->sizes as $color => $size}
                            {foreach $size as $translit => $v}
                                {if $first_color->color == $v->color}
                                     <div class="swiper-slide">
                                        {if $v@iteration == 8 && $size|count > 8}
                                            <input class="product_radio fn_variant hidden" type="radio" name="variant" id="product_radio_{$v->id}_{$v@iteration}{if $product_type}_{$product_type}{/if}" value="{$v->id}"{if $v@first && $v->stock > 0} checked{/if} required data-productid="{$v->product_id}" data-variantid="{$v->id}" data-name="{$v->name|escape}" data-sku="{$v->sku|escape}" data-price="{$v->price|convert:$currency->id:false}" {if $v->compare_price > 0} data-cprice="{$v->compare_price|convert}" {if $v->compare_price > $v->price && $v->price > 0} data-discount="{round((($v->price - $v->compare_price) / $v->compare_price) * 100, 0)} %" {/if}{/if} data-bonuses="{$v->bonuses|convert} {$v->bonuses|convert:null:false|plural:$lang->product_bonus:$lang->product_bonuses:$lang->product_of_bonuses}">
                                            {if $v->size}
                                                <label class="product_preview_variant{if $v->stock == 0} disabled{/if}"
                                                    for="product_radio_{$v->id}_{$v@iteration}{if $product_type}_{$product_type}{/if}">
                                                    {$feature_id = $settings->feature_id['size']}
                                                    {if $pv_aliases.$feature_id && $pv_aliases.$feature_id.$translit}{$pv_aliases.$feature_id.$translit}{else}{$v->size}{/if}
                                                </label>
                                            {/if}
                                        {elseif $v@last && $size|count > 8}
                                            <input class="product_radio fn_variant hidden" type="radio" name="variant" id="product_radio_{$v->id}_{$v@iteration}{if $product_type}_{$product_type}{/if}" value="{$v->id}"{if $v@first && $v->stock > 0} checked{/if} required data-productid="{$v->product_id}" data-variantid="{$v->id}" data-name="{$v->name|escape}" data-sku="{$v->sku|escape}" data-price="{$v->price|convert:$currency->id:false}" {if $v->compare_price > 0} data-cprice="{$v->compare_price|convert}" {if $v->compare_price > $v->price && $v->price > 0} data-discount="{round((($v->price - $v->compare_price) / $v->compare_price) * 100, 0)} %" {/if} {/if} data-bonuses="{$v->bonuses|convert} {$v->bonuses|convert:null:false|plural:$lang->product_bonus:$lang->product_bonuses:$lang->product_of_bonuses}">
                                            {if $v->size}
                                                <label class="product_preview_variant{if $v->stock == 0} disabled{/if}"
                                                   for="product_radio_{$v->id}_{$v@iteration}{if $product_type}_{$product_type}{/if}">
                                                    {if $pv_aliases.$feature_id && $pv_aliases.$feature_id.$translit}{$pv_aliases.$feature_id.$translit}{else}{$v->size}{/if}
                                                </label>
                                            {/if}
                                            <div class="hidden_size" title="Сховати розміри"><span>▲</span></div>
                                        {else}
                                            <input class="product_radio fn_variant hidden" type="radio" name="variant" id="product_radio_{$v->id}_{$v@iteration}{if $product_type}_{$product_type}{/if}" value="{$v->id}"{if $v@first && $v->stock > 0} checked{/if} required data-productid="{$v->product_id}" data-variantid="{$v->id}" data-name="{$v->name|escape}" data-sku="{$v->sku|escape}" data-price="{$v->price|convert:$currency->id:false}" {if $v->compare_price > 0} data-cprice="{$v->compare_price|convert}" {if $v->compare_price > $v->price && $v->price > 0} data-discount="{round((($v->price - $v->compare_price) / $v->compare_price) * 100, 0)} %" {/if} {/if}data-bonuses="{$v->bonuses|convert} {$v->bonuses|convert:null:false|plural:$lang->product_bonus:$lang->product_bonuses:$lang->product_of_bonuses}">
                                            {if $v->size}
                                                <label class="product_preview_variant{if $v->stock == 0} disabled{/if}"
                                                    for="product_radio_{$v->id}_{$v@iteration}{if $product_type}_{$product_type}{/if}">
                                                    {if $pv_aliases[$translit]}{$pv_aliases[$translit]}{else}{$v->size}{/if}
                                                </label>
                                            {/if}
                                        {/if}
                                    </div>
                                {/if}
                            {/foreach}
                            {break}
                        {/foreach}
                    </div>
                </div>
            </div>
            {* Product name *}
            <a class="product_name" data-product="{$product->id}"
               data-brand="{if $product->brand}{$product->brand->name|escape}{/if}"
               data-category="{if $product->category}{foreach $product->category->path as $path}{$path->name|escape}{if !$path@last};{/if}{/foreach}{/if}"
               href="{$lang_link}products/{$product->url}">{$product->name|escape}</a>
            <div class="price_container">
                {* Price *}
                <div class="price">
                    <span class="fn_price">{$product->variant->price|convert}</span>
                    <span>{$currency->sign|escape}</span>
                </div>
                {* Old price *}
                <div class="old_price{if !$product->variant->compare_price} hidden{/if}">
                    <span class="fn_old_price">{$product->variant->compare_price|convert}</span>
                    <span>{$currency->sign|escape}</span>
                </div>
            </div>

            {if $category->path[0]->id != $settings->global_categories['appliances']}
                <div class="product_images">
                    <div class="product_images_content">
                        {* {if $is_mobile && !$is_tablet}id="more_color_{$product->id}"{/if} *}
                        {foreach $product->colors as $translit => $v}
                            {if $v@iteration === 1}
                                {assign var="first_color" value=$v}
                            {/if}
                            <div class="color_cart_wrap">
                                <label class="select_color_cart{if $v@first} is_active{/if}{if !$v->count_in_stock} no_select_color{/if}"
                                       for="color_{$product->id}_{$translit|escape}{if $product_type}_{$product_type}{/if}"
                                       title="{if $pv_aliases[$translit]}{$pv_aliases[$translit]|escape}{else}{$v->color|escape}{/if}">
                                    <input class="fn_change_variant_cart fn_select_color" type="radio"
                                           name="color" id="color_{$product->id}_{$translit|escape}{if $product_type}_{$product_type}{/if}"
                                           value="{$v->id}"{if $v@first} checked{/if} required
                                           data-productid="{$v->product_id}" data-variantid="{$v->id}"
                                           data-bonuses="{$v->bonuses|convert} {$v->bonuses|convert:null:false|plural:$lang->product_bonus:$lang->product_bonuses:$lang->product_of_bonuses}">
                                    {if $v->images|count > 0 && $first_color->id == $v->id}
                                        {assign var="variant_image" value=$v->images[0]}
                                        <picture>
                                            <source type="image/webp" data-srcset="{$variant_image->filename|resize:45:45:false:null:null:null:true}" srcset="{$rootUrl}/design/{$settings->theme|escape}/images/xloading.gif">
                                            <source type="image/jpeg" data-srcset="{$variant_image->filename|resize:45:45}" srcset="{$rootUrl}/design/{$settings->theme|escape}/images/xloading.gif">
                                            <img class="lazy lazy-bg" data-src="{$variant_image->filename|resize:45:45}" src="{$rootUrl}/design/{$settings->theme|escape}/images/xloading.gif" alt="{$product->name|escape}" {* loading="lazy" *} width="45" height="45">
                                        </picture>
                                    {elseif $v->images|count > 0}
                                        {assign var="variant_image" value=$v->images[0]}
                                        <picture>
                                            <source
                                                type="image/webp"
                                                data-srcset="{$variant_image->filename|resize:45:45:false:$config->resized_variants_dir:null:null:true}"
                                                srcset="{$rootUrl}/design/{$settings->theme|escape}/images/xloading.gif"
                                            >
                                            <source
                                                type="image/jpeg"
                                                data-srcset="{$variant_image->filename|resize:45:45:false:$config->resized_variants_dir}"
                                                srcset="{$rootUrl}/design/{$settings->theme|escape}/images/xloading.gif"
                                            >
                                            <img
                                                class="lazy lazy-bg"
                                                data-src="{$variant_image->filename|resize:45:45:false:$config->resized_variants_dir}"
                                                alt="{$product->name|escape}" {* loading="lazy" *}
                                                width="45" height="45" src="{$rootUrl}/design/{$settings->theme|escape}/images/xloading.gif">
                                        </picture>
                                    {else}
                                        <img src="design/{$settings->theme|escape}/images/no_image.png"
                                             alt="{$product->name|escape}"
                                             width="45"
                                             height="45"
                                             loading="lazy">
                                        {* {if $pv_aliases[$translit]}{$pv_aliases[$translit]}{else}{$v->color}{/if} *}
                                    {/if}
                                </label>
                            </div>
                        {/foreach}
                    </div>
                </div>
            {/if}
        </form>
    </div>
</div>
