<form class="fn_variants" action="/{$lang_link}cart" data-name="{$product->name|escape}" data-brand="{$brand->name|escape|default:''}" data-categories="{if $category}{foreach $category->path as $path}{$path->name|escape}{if !$path@last};{/if}{/foreach}{/if}">
    <div itemprop="offers" itemscope="" itemtype="http://schema.org/Offer">

        <div class="product_price_wrapper">
            <div class="product_price">
                {* Price *}
                <div class="price">
                    <span class="fn_price" itemprop="price" content="{$product->variant->price|convert:'':false}">{$product->variant->price|convert}</span>
                    <span itemprop="priceCurrency" content="{$currency->code|escape}">{$currency->sign|escape}</span>
                </div>
                {* Old price *}
                <div class="old_price{if !$product->variant->compare_price} hidden{/if}">
                    <span class="fn_old_price">{$product->variant->compare_price|convert}</span>{$currency->sign|escape}
                </div>
            </div>
            {* Product Rating *}
            <div id="product_{$product->id}" class="product_rating"{if $product->rating > 0} itemprop="aggregateRating" itemscope itemtype="http://schema.org/AggregateRating"{/if}>
                <span class="rating_starOff">
                    <span class="rating_starOn" style="width:{$product->rating*90/5|string_format:'%.0f'}px;"></span>
                </span>
                <span class="rating_text"></span>

                {*Вывод количества голосов данного товара, скрыт ради микроразметки*}
                {if $product->rating > 0}
                    <span class="hidden" itemprop="reviewCount">{$product->votes|string_format:"%.0f"}</span>
                    <span class="hidden" itemprop="ratingValue">({$product->rating|string_format:"%.1f"})</span>
                    {*Вывод лучшей оценки товара для микроразметки*}
                    <span class="hidden" itemprop="bestRating" style="display:none;">5</span>
                {else}
                    <span class="hidden">({$product->rating|string_format:"%.1f"})</span>
                {/if}
            </div>
        </div>

        {* Бонусы товара *}
        {* {if $product->variant->bonuses > 0}
            <div class="product_bonuses_wrapp">
                <div class="product_bonuses fn_switch">
                    <div class="icon ">SF</div>
                    {$bonuses = $product->variant->bonuses}
                    <span class="fn_bonuses">+{$bonuses|convert} {$bonuses|convert:null:false|plural:$lang->product_bonus:$lang->product_bonuses:$lang->product_of_bonuses}</span>
                </div>
                <div class="info" data-language="product_bonuses_info">
                    {$lang->product_bonuses_info}
                </div>
            </div>
        {/if} *}

        {* ### ### ### ### ### ### ### ### ### ### ### ### ### ### ### ### ### ### ### ### ### ### ### ### ### ### ### *}
        {* ### ### ### ### ### ### ### ### ### ### ### ### ### ### ### ### ### ### ### ### ### ### ### ### ### ### ### *}
        {* ### ### ### ### ### ### ### ### ### ### ### ### ### ### ### ### ### ### ### ### ### ### ### ### ### ### ### *}

        {if $category->path[0]->id != $settings->global_categories['appliances']}
            {assign var="first_size" value=$product->sizes|first}
            <div class="product_color_wrapper">
                <span class="product_variants_text" data-language="product_color_text">{$lang->product_color_text}</span>
                <div class="product_color">
                    {foreach $product->colors as $translit => $v}
                        {if (!$smarty.get.variant && $v@iteration === 1) || ($smarty.get.variant && $product->variant->color == $v->color)}
                            {assign var="first_color" value=$v}
                        {/if}
                        {if $smarty.get.variant && $product->variant->color == $v->color}
                            {assign var="first_size" value=$product->sizes[$translit]}
                        {/if}
                        <label class="select_color{if (!$smarty.get.variant && $v@first) || ($smarty.get.variant && $product->variant->color == $v->color)} is_active{/if}{if !$v->count_in_stock} no_select_color{/if}" for="product_color_{$product->id}_{$translit|escape}" title="{if $pv_aliases[$translit]}{$pv_aliases[$translit]|escape}{else}{$v->color|escape}{/if}">
                            <input type="radio"
                                    class="fn_change_variant fn_select_color"
                                    name="color"
                                    id="product_color_{$product->id}_{$translit|escape}"
                                    value="{$v->id}"
                                    data-productid="{$v->product_id}"
                                    data-variantid="{$v->id}"
                                    data-color=""
                                    {if $v@first}checked{/if}
                                    required>
                            {if $v->images|count > 0 && $first_color->id == $v->id}
                                {assign var="variant_image" value=$v->images[0]}
                                <picture>
                                    <source type="image/webp" srcset="{$variant_image->filename|resize:70:70:false:null:null:null:true}">
                                    <source type="image/jpeg" srcset="{$variant_image->filename|resize:70:70}">
                                    <img src="{$variant_image->filename|resize:70:70}" alt="{$product->name|escape}">
                                </picture>
                            {elseif $v->images|count > 0}
                                {assign var="variant_image" value=$v->images[0]}
                                <picture>
                                    <source type="image/webp" srcset="{$variant_image->filename|resize:70:70:false:$config->resized_variants_dir:null:null:true}">
                                    <source type="image/jpeg" srcset="{$variant_image->filename|resize:70:70:false:$config->resized_variants_dir}">
                                    <img src="{$variant_image->filename|resize:70:70:false:$config->resized_variants_dir}" alt="{$product->name|escape}">
                                </picture>
                            {else}
                                <img src="design/{$settings->theme|escape}/images/no_image.png" alt="{$product->name|escape}">
                            {/if}
                        </label>
                    {/foreach}
                </div>
            </div>
            {if $product->sizes[$first_color->color_translit]|count > 0}
                <div class="product_variants_wrapper">
                    <span class="product_variants_text" data-language="product_sizes_text">{$lang->product_sizes_text}</span>
                    <div class="product__variants_items">
                        {foreach $product->variants as $v}
                            {if $first_color->color == $v->color}
                                <div class="product__variants_item">
                                    <input type="radio"
                                        name="variant"
                                        id="main_product_radio_{$v->id}_{$v@iteration}"
                                        class="product_radio fn_variant hidden"
                                        value="{$v->id}"
                                        data-price="{$v->price|convert:null:false}"
                                        data-stock="{$v->stock}"
                                        {if $v->compare_price > 0}
                                            data-cprice="{$v->compare_price|convert:null:false}"
                                            {if $v->compare_price > $v->price && $v->price > 0}
                                                data-discount="{round((($v->price - $v->compare_price) / $v->compare_price) * 100, 0)} %"
                                            {/if}
                                        {/if}
                                        {if $v->sku}
                                            data-sku="{$v->sku|escape}"
                                        {/if}
                                        {if $v->units}
                                            data-units="{$v->units}"
                                        {/if}
                                        {if $v->name}
                                            data-name="{$v->name|escape}"
                                        {/if}
                                        {if $product->variant->id == $v->id}checked{/if}>
                                    <label class="product_preview_variant{if $v->stock == 0} disabled{/if}" for="main_product_radio_{$v->id}_{$v@iteration}">
                                        {if $v->size}{$v->size}{/if}
                                    </label>
                                </div>
                            {/if}
                        {/foreach}
                    </div>
                </div>
            {/if}
            {* <div class="">
                <a class="pick_size" href="javascript:void(0);" onclick="openPopup(this)"
                    data-id="#popup-box-select-size"><span
                            data-language="pick_size">{$lang->pick_size}</span></a>
            </div> *}
        {else}
            <input type="hidden" name="variant" value="{$product->variant->id}">
        {/if}

        {* ### ### ### ### ### ### ### ### ### ### ### ### ### ### ### ### ### ### ### ### ### ### ### ### ### ### ### *}
        {* ### ### ### ### ### ### ### ### ### ### ### ### ### ### ### ### ### ### ### ### ### ### ### ### ### ### ### *}
        {* ### ### ### ### ### ### ### ### ### ### ### ### ### ### ### ### ### ### ### ### ### ### ### ### ### ### ### *}

    </div>

    {if $category->path[0]->id != $settings->global_categories['appliances']}
        <button class="fn_size_grid_button info_change size_grid_button" data-change="2" type="button"><span data-language="size_grid">{$lang->size_grid}</span></button>
    {/if}

    <div class="product_buttons">
        <div class="product_buttons_content">
            {if !$settings->is_preorder}
                {* No stock *}
                <div class="fn_not_preorder {if $product->variant->stock > 0} hidden{/if}">
                    <button class="disable_button" type="button" data-language="product_out_of_stock">{$lang->product_out_of_stock}</button>
                </div>
            {else}
                {* Preorder *}
                <div class="fn_is_preorder{if $product->variant->stock > 0} hidden{/if}">
                    <button class="button product_btn" type="button"
                            data-language="product_pre_order" onclick="openPopup(this)"
                            data-id="#popup-box-buy-click">{$lang->product_pre_order}</button>
                </div>
            {/if}
            {* Submit button *}
            <button class="fn_is_stock fn_show_modal button product_btn{if $product->variant->stock < 1} hidden{/if}" type="submit" data-language="product_add_cart">{$lang->product_add_cart}</button>
            {* Quantity *}
            <div class="amount fn_product_amount{if $product->variant->stock < 1} hidden{/if}">
                <span class="minus">{include file="svg.tpl" svgId="minus_icon"}</span>
                <input id="amount_input" class="input_amount" type="text" name="amount" value="1" data-max="5" aria-label="Кількість товару">
                <span class="plus">{include file="svg.tpl" svgId="plus_icon"}</span>
            </div>
        </div>

        {if $settings->is_preorder && $product->variant->stock == 0}
            {* Preorder *}
            <div class="m_btm-30px"></div>
        {else}
            <button type="button" class="fn_btn_fast_order button btn-one-click btn_black" data-id="#popup-box-buy-click">
                <span data-language="buy_one_click">{$lang->buy_one_click}</span>
            </button>
        {/if}
    </div>
</form>