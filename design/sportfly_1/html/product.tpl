{* Product page *}

{* The canonical address of the page *}
{$canonical="/products/{$product->url}" scope=parent}

<div class="product_page">
    <div class="fn_product product" itemscope itemtype="http://schema.org/Product">
        <div class="product_top_wrapper">
            <div class="product_top_left_content">
                {* {if $product->variant->images} *}
                    {* Main product image *}
                    <div class="gallery_image product-page__image gallery-top swiper {if $product->images|count == 1}product-page__image--full{/if}" data-no-image="design/{$settings->theme}/images/no-image.svg">
                        <div class="swiper-wrapper">
                        {if $product->variant->images}
                            {foreach $product->variant->images as $i=>$image}
                                <a class="swiper-slide fn_ajax_image" href="{$image->filename|resize:1000:1000}"
                                   data-href-webp="{$image->filename|resize:1000:1000:false:null:null:null:true}"
                                        {*                                   data-fancybox="we2"*}
                                   data-fancybox="gallery"
                                >
                                    <picture>
                                        <source media="(max-width: 480px)" type="image/webp"
                                                {*                                                {if $image@first}*}
                                                {*                                                    srcset="{$image->filename|resize:250:300:false:null:null:null:true}"*}
                                                {*                                                {else}*}
                                                data-srcset="{$image->filename|resize:450:450:false:null:null:null:true}"
                                                srcset="{$rootUrl}/design/{$settings->theme|escape}/images/xloading.gif"
                                                {*                                                {/if}*}
                                        >
                                        <source type="image/webp"
                                                {*                                                {if $image@first}*}
                                                {*                                            srcset="{$image->filename|resize:870:870:false:null:null:null:true}"*}
                                                {*                                                {else}*}
                                                data-srcset="{$image->filename|resize:870:870:false:null:null:null:true}"
                                                srcset="{$rootUrl}/design/{$settings->theme|escape}/images/xloading.gif"
                                                {*                                                {/if}*}
                                        >
                                        <source media="(max-width: 480px)" type="image/jpeg"
                                                {*                                                {if $image@first}*}
                                                {*                                                    srcset="{$image->filename|resize:250:300}"*}
                                                {*                                                {else}*}
                                                data-srcset="{$image->filename|resize:450:450}"
                                                srcset="{$rootUrl}/design/{$settings->theme|escape}/images/xloading.gif"
                                                {*                                                {/if}*}
                                        >
                                        <source type="image/jpeg"
                                                {*                                                {if $image@first}*}
                                                {*                                                    srcset="{$image->filename|resize:870:870}"*}
                                                {*                                                {else}*}
                                                data-srcset="{$image->filename|resize:870:870}"
                                                srcset="{$rootUrl}/design/{$settings->theme|escape}/images/xloading.gif"
                                                {*                                                {/if}*}
                                        >

                                        <img
                                                {*                                                class="{if $image@first}fn_img{/if}{if !$image@first} lazy lazy-bg{/if}"*}
                                                class="{if $image@first}fn_img{/if} lazy lazy-bg"
                                                {*                                                {if $image@first}*}
                                                {*                                                    src="{$image->filename|resize:870:870}"*}
                                                {*                                                {else}*}
                                                data-src="{$image->filename|resize:870:870}"
                                                src="{$rootUrl}/design/{$settings->theme|escape}/images/xloading.gif"
                                                loading="lazy"
                                                {*                                                {/if}*}
                                                {if $image@first}
                                                    itemprop="image"
                                                    fetchpriority="high"
                                                {/if}
                                                alt="{$product->name|escape}{if $image@first} - {$lang->photo}{else} - {$lang->photo} №{$image@iteration}{/if}"
                                                title="{$product->name|escape}">
                                    </picture>
                                    {if $product->special}
                                        <img class="promo_img" src="files/special/{$product->special}"
                                             alt="{$product->special|escape}" title="{$product->special|escape}">
                                    {/if}
                                </a>
                            {/foreach}
                            {else}
                                <div class="product-page__image--full product-page__no_image product-page__image swiper-slide" title="{$product->name|escape}">
                                    {include file="svg.tpl" svgId="no_image"}
                                </div>
                            {/if}
                        </div>
                        {if $product->images|count > 1}
                            <div class="swiper-button-next"></div>
                            <div class="swiper-button-prev"></div>
                            <div class="swiper-pagination"></div>
                        {/if}
                    </div>
                    {* Additional product images *}
                    {if $product->images|count > 1}
                        <div class="product-page__images gallery-thumbs swiper swiper-vertical">
                            <div class="swiper-wrapper">
                                {* cut removes the first image, if you need start from the second - write cut:2 *}
                                {foreach $product->variant->images as $i=>$image}
                                    <div class="product-page__images-item swiper-slide">
                                        <picture>
                                            <source type="image/webp"
                                                    srcset="{$image->filename|resize:100:100:false:null:null:null:true}">
                                            <source type="image/jpeg" srcset="{$image->filename|resize:100:100}">
                                            <img
                                                    class="lazy lazy-bg"
                                                    src="{$rootUrl}/design/{$settings->theme|escape}/images/xloading.gif"
                                                    loading="lazy"
                                                    data-src="{$image->filename|resize:100:100}"
                                                    alt="{$product->name|escape} - {$lang->photo} №{$image@iteration}"
                                                    title="{$product->name|escape}">
                                        </picture>
                                    </div>
                                {/foreach}
                            </div>
                            {if $product->images|count > 4}
                                <div class="swiper-button-next"></div>
                                <div class="swiper-button-prev"></div>
                            {/if}
                        </div>
                    {/if}
                {* {else}
                    <div class="gallery_image product-page__image gallery-top swiper">
                        <div class="swiper-wrapper">
                            <div class="product-page__image--full product-page__no_image product-page__image" title="{$product->name|escape}">
                                {include file="svg.tpl" svgId="no_image"}
                            </div>
                        </div>
                    </div>
                {/if} *}
                {* Wishlist *}
                {if $product->id|in_array:$wished_products}
                    <a href="#" data-id="{$product->id}"
                       class="fn_wishlist selected wishlist_button"
                       title="{$lang->remove_favorite}"
                       data-result-text="{$lang->add_favorite}"></a>
                {else}
                    <a href="#" data-id="{$product->id}" class="fn_wishlist wishlist_button"
                       title="{$lang->add_favorite}"
                       data-result-text="{$lang->remove_favorite}"></a>
                {/if}
            </div>
            <div class="product_top_right_content">
                {* The product name *}
                <h1 class="product_heading"><span data-product="{$product->id}">{$product->name|escape}</span></h1>
                <div class="product_sku_wrapper">
                    <div class="product_sku_container">
                        <div class="sku {if !$product->variant->sku} hidden{/if}">
                            <span data-language="product_sku">{$lang->product_sku}:</span>
                            <span class="fn_sku sku_nubmer" data-variant="{$product->variant->id}"
                                  {if $product->variant->sku}itemprop="sku"{/if}>{$product->variant->sku|escape}</span>
                        </div>
                        {* Stock *}
                        <div class="available">
                            <span class="no_stock fn_not_stock{if $product->variant->stock > 0} hidden{/if}"
                                  data-language="product_out_of_stock">{$lang->product_out_of_stock}</span>
                            <span class="in_stock fn_in_stock{if $product->variant->stock < 1} hidden{/if}"
                                  data-language="product_in_stock">{$lang->product_in_stock}</span>
                        </div>
                    </div>
                    {if $brand}
                        <div class="brand_img_wrap center-flex clearfix">
                            <a href="{$lang_link}catalog/{$category->url}/brand-{$brand->url}" class="fn_brand"
                               data-brand="{$brand->name|escape}">
                                {if $brand->image}
                                    <img class="brand_img"
                                         src="{$brand->image|resize:80:80:false:$config->resized_brands_dir}"
                                         alt="{$brand->name|escape}">
                                {else}
                                    <span>Бренд: {$brand->name}</span>
                                {/if}
                            </a>
                        </div>
                    {/if}
                </div>
                {include file="product_variants.tpl"}
                <span class="free_delivery"
                      data-language="free_delivery_text">{include file="svg.tpl" svgId="delivery_icon"}{$lang->free_delivery_text}</span>
                <div class="advantages_items">
                    <button class="fn_advantages_item_1 advantages_item" data-language="advantages_text_1"
                            type="button">{include file="svg.tpl" svgId="advantages_icon_1"}{$lang->advantages_text_1}</button>
                    <button class="fn_advantages_item_2 advantages_item" data-language="advantages_text_2"
                            type="button">{include file="svg.tpl" svgId="advantages_icon_2"}{$lang->advantages_text_2}</button>
                    <button class="fn_advantages_item_3 advantages_item" data-language="advantages_text_3"
                            type="button">{include file="svg.tpl" svgId="advantages_icon_3"}{$lang->advantages_text_3}</button>
                    <button class="fn_advantages_item_4 advantages_item" data-language="advantages_text_4"
                            type="button">{include file="svg.tpl" svgId="advantages_icon_4"}{$lang->advantages_text_4}</button>
                </div>
            </div>
        </div>

        <div class="product_center_wrapper">
            <div class="product_center_left_wrapper">
                {if $product->description}
                    <div class="product_description_wrap">
                        <div class="main_section_title">
                            <span data-language="product_description">{$lang->product_description}</span>
                            <button class="fn_share_product_btn share_product_btn" type="button"
                                    data-language="share_product">{include file="svg.tpl" svgId="share_product_icon"}{$lang->share_product}</button>
                        </div>
                        <div id="description" class="product_description"
                             itemprop="description">{$product->description}</div>
                    </div>
                {/if}
                <button class="fn_leave_review leave_review_btn btn_black" data-language="leave_review"
                        type="button">{$lang->leave_review}</button>
                {* Comments *}
                <div id="comments">
                    {* <div class="h2">
                        <span data-language="comment">{$lang->comment}</span>
                    </div> *}
                    <div class="comment-wrapper">
                        {if $comments}
                            {function name=comments_tree level=0}
                                {foreach $comments as $comment}
                                    {* Comment anchor *}
                                    <a name="comment_{$comment->id}"></a>
                                    {* Comment list *}
                                    <div class="comment_item{if $level > 0} admin_note{/if}">
                                        <div class="comment_header">
                                            {* Comment img *}
                                            {* <div class="comment_author-img round_img">
                                                {get_user var=user id=$comment->user_id}
                                                {if $user->image}
                                                    <img src="files/user_img/{$user->image}" alt="">
                                                {else}
                                                    {include file="svg.tpl" svgId="avatar" width="24" height="24"}
                                                {/if}
                                            </div> *}
                                            <div class="flex_col flex_jc">
                                                {* Comment name *}
                                                <span class="comment_author">{$comment->name|escape}</span>
                                                {* Comment date *}
                                                <div class="flex-item">
                                                    <span class="comment_date">{$comment->date|date}, {$comment->date|time}</span>
                                                    {if !$comment->approved}
                                                        <span class="approved"
                                                              data-language="post_comment_status">({$lang->post_comment_status})</span>
                                                    {/if}
                                                </div>
                                            </div>
                                        </div>

                                        {* Comment content *}
                                        <div class="comment_content">
                                            {$comment->text|escape|nl2br}
                                        </div>
                                        {if isset($children[$comment->id])}
                                            {comments_tree comments=$children[$comment->id] level=$level+1}
                                        {/if}
                                    </div>
                                {/foreach}
                            {/function}
                            {comments_tree comments=$comments}
                        {else}
                            <div class="no_comments product_no_comments">
                                {include file="svg.tpl" svgId="custom_comment"}
                                <span data-language="product_no_comments">{$lang->product_no_comments}</span>
                            </div>
                        {/if}
                    </div>
                </div>
            </div>

            <div class="product_center_right_wrapper">
                {if $product->features}
                    <div class="features_wrapper">
                        <div class="product_features_title"><span
                                    data-language="product_features">{$lang->product_features}</span></div>
                        <ul class="features">
                            {foreach $product->features as $f}
                                <li>
                                    <span class="features_name"><span>{$f->name|escape}</span></span>
                                    <span class="features_value">
                                    {foreach $f->values as $value}
                                        {if $category && $f->url_in_product && $f->in_filter}
                                            {* {if $category && $f->url_in_product && $f->in_filter && $value->to_index} *}
                                            <a
                                            href="{$lang_link}catalog/{$category->url}/{$f->url}-{$value->translit}">{$value->value|escape}</a>{if !$value@last},{/if}
                                        {else}
                                            {$value->value|escape}{if !$value@last},{/if}
                                        {/if}
                                    {/foreach}
                                </span>
                                </li>
                            {/foreach}
                        </ul>
                    </div>
                {/if}
                <div class="product_info_buttons">
                    <button id="info_change-1" class="info_change is_active" data-change="1" type="button">
                        <span data-language="product_description"
                              itemprop="description">{$lang->product_description}</span>
                    </button>

                    {if $category->path[0]->id != $settings->global_categories['appliances']}
                        <button class="fn_size_grid_button info_change" data-change="2" type="button">
                            <span data-language="size_grid">{$lang->size_grid}</span>
                        </button>
                    {/if}

                    <button class="fn_btn_payment_info info_change" data-change="3" type="button">
                        {if $category->path[0]->id == $settings->global_categories['appliances']}
                            <span data-language="payment">{$lang->payment}</span>
                        {else}
                            <span data-language="shipping_and_payment">{$lang->shipping_and_payment}</span>
                        {/if}
                    </button>
                </div>
                <div class="products_link">
                    <a href="{$lang_link}catalog/{$category->url}/brand-{$brand->url}">{$category->name} {$brand->name}</a>
                    <a href="{$lang_link}catalog/{$category->url}"><span
                                data-language="all_products_category">{$lang->all_products_category}</span></a>
                </div>
            </div>
        </div>
    </div>


    {* Related products *}
    {if $related_products}
        <div class="main_section fn_container">
            <div class="main_section_title">
                <span class="fn_container_name"
                      data-language="product_recommended_products">{$lang->product_recommended_products}</span>
            </div>
            <div class="fn_products_slide main_products swiper">
                <div class="swiper-wrapper">
                    {foreach $related_products as $p}
                        {if $p@iteration <= 10}
                            <div class="products_item swiper-slide">
                                {include "product_list.tpl" product = $p product_type="related"}
                            </div>
                        {/if}
                    {/foreach}
                </div>
                <div class="swiper-button-next"></div>
                <div class="swiper-button-prev"></div>
                <div class="swiper-pagination"></div>
            </div>
        </div>
    {/if}

    {* Browsed products *}
    {get_browsed_products var=browsed_products}
    {if $browsed_products}
        <div class="main_section fn_container">
            <div class="main_section_title" data-language="features_browsed">
                <span class="fn_container_name">{$lang->features_browsed}</span>
            </div>
            <div class="fn_products_slide main_products swiper">
                <div class="swiper-wrapper">
                    {foreach $browsed_products as $p}
                        {if $p@iteration <= 10}
                            <div class="products_item swiper-slide">
                                {include "product_list.tpl" product = $p product_type="browsed"}
                            </div>
                        {/if}
                    {/foreach}
                </div>
                <div class="swiper-button-next"></div>
                <div class="swiper-button-prev"></div>
                <div class="swiper-pagination"></div>
            </div>
        </div>
    {/if}

    {* Featured products *}
    {get_featured_products var=featured_products limit=6}
    {if $featured_products}
        <div class="main_section">
            <div class="main_section_title"><span
                        data-language="main_recommended_products">{$lang->main_recommended_products}</span></div>
            <div class="fn_products_slide main_products swiper">
                <div class="swiper-wrapper">
                    {foreach $featured_products as $p}
                        <div class="products_item swiper-slide">
                            {include "product_list.tpl" product = $p}
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


<div class="hidden">
    <div id="fn_fast_order_form" class="fast_order_form">
        <div class="fast_order_container">
            <form id="fn_buy_click" class="callback_form fn_validate_callback" method="post" name="fastorder">
                {* The form heading *}
                <div class="h3">
                    {if $settings->is_preorder || $product->variant->stock > 0}
                        <span data-language="buy_one_click">{$lang->buy_one_click}</span>
                    {else}
                        {* Preorder *}
                        <span>{$lang->pre_order}</span>
                    {/if}
                </div>
                {if $error}
                    <div class="message_error">
                        {if $error == 'captcha'}
                            <span data-language="form_error_captcha">{$lang->form_error_captcha}</span>
                        {elseif $error == 'empty_name'}
                            <span data-language="form_enter_name">{$lang->form_enter_name}</span>
                        {elseif $error == 'empty_phone'}
                            <span data-language="form_enter_phone">{$lang->form_enter_phone}</span>
                        {elseif $error == 'empty_variant'}
                            <span data-language="form_enter_variant">{$lang->form_enter_variant}</span>
                        {else}
                            <span>{$error}</span>
                        {/if}
                    </div>
                {/if}

                {if $settings->is_preorder || $product->variant->stock == 0}
                    {* Preorder *}
                    <input class="fn_preorder_p" type="hidden" name="variant_id" value="">
                {/if}

                <div class="fast_order_form_content">
                    <div class="form_groups">
                        {* User's name *}
                        <div class="form_group">
                            {* <span>{$lang->form_name}*</span> *}
                            <input class="form_input placeholder_focus" type="text" name="name"
                                   value="{if $order->name}{$order->name|escape}{else}{$user->name|escape}{/if}"
                                   data-language="form_name" placeholder="{$lang->form_name}*">
                        </div>

                        {* User's phone *}
                        <div class="form_group">
                            {* <span>{$lang->form_phone}*</span> *}
                            <input class="form_input placeholder_focus" type="text" name="phone"
                                   value="{if $order->phone}{$order->phone|escape}{else}{$user->phone|escape}{/if}"
                                   data-language="form_phone" placeholder="{$lang->form_phone}*">
                        </div>
                    </div>
                    <div class="fast_order_product">
                        {if $product->images}
                            {* Main product image *}
                            <div class="fast_order_product_img">
                                {foreach $product->images as $i=>$image}
                                    {if $image@first}
                                        <picture>
                                            <source srcset="{$image->filename|resize:100:100}">
                                            <img src="{$image->filename|resize:100:100}"
                                                 alt="{$product->name|escape}{if $image@first} - {$lang->photo}{else} - {$lang->photo} №{$image@iteration} {/if}"
                                                 title="{$product->name|escape}"
                                                 loading="lazy">
                                        </picture>
                                    {/if}
                                {/foreach}
                            </div>
                        {else}
                            <div class="product-page__no_image" title="{$product->name|escape}">
                                {include file="svg.tpl" svgId="no_image"}
                            </div>
                        {/if}
                        <div class="fast_order_product_details">
                            <span class="fast_order_product_name">{$product->name}</span>
                            <span class="fast_order_product_sizes fn_product_fast_size"
                                  data-language="product_sizes_text">
                                {$lang->product_sizes_text}
                            </span>
                            <div class="product_price">
                                {* Price *}
                                <div class="price">
                                    <span class="fn_price" itemprop="price"
                                          content="{$product->variant->price|convert:'':false}">{$product->variant->price|convert}</span>
                                    <span itemprop="priceCurrency"
                                          content="{$currency->code|escape}">{$currency->sign|escape}</span>
                                </div>
                                {* Old price *}
                                <div class="old_price{if !$product->variant->compare_price} hidden{/if}">
                                    <span class="fn_old_price">{$product->variant->compare_price|convert}</span>{$currency->sign|escape}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {* Captcha *}
                {if $settings->captcha_fastorder}
                    {if $settings->captcha_type == "v3"}
                        <div class="captcha row" style="display: none;">
                            <div class="fn_recaptchav3">
                                <input type="hidden" name="recaptcha_token" value=""
                                       class="fn_recaptcha_token">
                            </div>
                        </div>
                    {elseif $settings->captcha_type == "v2"}
                        <div class="captcha row">
                            <div id="recaptcha2"></div>
                        </div>
                    {elseif $settings->captcha_type == "default"}
                        {get_captcha var="captcha_callback"}
                        <div class="captcha">
                            <div class="secret_number">{$captcha_callback[0]|escape} + ?
                                = {$captcha_callback[1]|escape}</div>
                            <span class="form_captcha">
                        <input class="form_input input_captcha placeholder_focus" type="text"
                               name="captcha_code" value="">
                        <span class="form_placeholder">{$lang->form_enter_captcha}*</span>
                    </span>
                        </div>
                    {/if}
                {/if}

                {* Submit button *}
                <div class="center">
                    <input name="fastorder" type="hidden" value="1">
                    <input class="form_button button btn_black g-recaptcha" type="submit" name="fastorder"
                           data-language="callback_order"{if $settings->captcha_type == "invisible"} data-sitekey="{$settings->public_recaptcha_invisible}" data-badge="bottomleft" data-callback="onSubmitCallback"{/if}
                           value="{if $settings->is_preorder || $product->variant->stock > 0}{$lang->callback_order}{else}Повідомити{/if}">
                </div>
            </form>
        </div>
    </div>
</div>


<div class="hidden">
    <div id="fn_comment_form">
        {* Comment form *}
        <form id="captcha_id" class="comment_form fn_validate_product" method="post">
            <div class="h3">
                <span data-language="product_write_comment">{$lang->product_write_comment}</span>
            </div>
            {* <p><span data-language="product_write_need">{$lang->product_write_need}</span></p> *}
            {* Form error messages *}
            {if $error}
                <div class="message_error">
                    {if $error=='captcha'}
                        <span data-language="form_error_captcha">{$lang->form_error_captcha}</span>
                    {elseif $error=='empty_name'}
                        <span data-language="form_enter_name">{$lang->form_enter_name}</span>
                    {elseif $error=='empty_comment'}
                        <span data-language="form_enter_comment">{$lang->form_enter_comment}</span>
                    {elseif $error=='empty_email'}
                        <span data-language="form_enter_email">{$lang->form_enter_email}</span>
                    {/if}
                </div>
            {/if}
            <input type="hidden" name="user_id" value="{$user_id}">
            <div class="comment_form_wrapper">
                <div class="form_groups">
                    <div class="form_group">
                        {* <label><span>{$lang->form_name}*</span></label> *}
                        <input class="form_input placeholder_focus" type="text" name="name"
                               value="{$comment_name|escape}" placeholder="{$lang->form_name}*">
                    </div>
                    <div class="form_group">
                        {* <label><span>{$lang->form_email}</span></label> *}
                        <input class="form_input placeholder_focus" type="text" name="email"
                               value="{$comment_email|escape}" data-language="form_email"
                               placeholder="{$lang->form_email}">
                    </div>
                </div>
                {* User's comment *}
                <div class="form_group">
                    {* <label><span>{$lang->form_enter_comment}*</span></label> *}
                    <textarea class="form_textarea placeholder_focus" rows="3" name="text"
                              placeholder="{$lang->form_enter_comment}*">{$comment_text}</textarea>
                </div>
            </div>
            <input type="hidden" name="user_id" value="{$user_id}">
            {* Captcha *}
            {if $settings->captcha_product}
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
                    {get_captcha var="captcha_product"}
                    <div class="captcha">
                        <div class="secret_number">{$captcha_product[0]|escape} + ?
                            = {$captcha_product[1]|escape}</div>
                        <span class="form_captcha">
                            <input class="form_input input_captcha placeholder_focus" type="text"
                                   name="captcha_code" value="">
                            <span class="form_placeholder">{$lang->form_enter_captcha}*</span>
                        </span>
                    </div>
                {/if}
            {/if}
            <input type="hidden" name="comment" value="1">
            {* Submit button *}
            <input class="form_button button btn_black g-recaptcha" type="submit" name="comment"
                   data-language="form_send_comment"
                   {if $settings->captcha_type == "invisible"}data-sitekey="{$settings->public_recaptcha_invisible}"
                   data-badge="bottomleft" data-callback="onSubmit"{/if}
                   value="{$lang->form_send_comment}">
        </form>
    </div>
</div>

<div class="hidden">
    <div id="fn_size_grid">
        <div class="size_grid_wrapper {if $category->attach_brand->image}size-grid-true{else}size-grid-false{/if}">
            <div class="size_grid_container">
                {if $category->attach_brand->image}
                    <img src="{$category->attach_brand->image|resize:800:800:false:$config->resized_types_dir}"
                         alt="{$lang->size_grid|escape}" loading="lazy">
                {else}
                    <div class="h3" data-language="size_grid_title">{$lang->size_grid_title}</div>
                    <p data-language="size_grid_text">{$lang->size_grid_text}</p>
                {/if}
            </div>
        </div>
    </div>
</div>

<div class="hidden">
    <div id="fn_payment_info">
        <div class="payment_info_wrapper">
            <div class="h3" data-language="payment_info_title">{$lang->payment_info_title}</div>
            <p data-language="payment_info_text">{$lang->payment_info_text}</p>
        </div>
    </div>
</div>

<div class="hidden">
    <div id="fn_advantages_item_1">
        <div class="advantages_item_wrapper">
            <div class="h3" data-language="clothing_care_title">{$lang->clothing_care_title}</div>
            <img class="lazy lazy-bg" loading="lazy"
                 data-src="design/{$settings->theme|escape}/images/clothing-care.png"
                 src="{$rootUrl}/design/{$settings->theme|escape}/images/xloading.gif"
                 alt="{$lang->clothing_care_img_alt}">
        </div>
    </div>
</div>

<div class="hidden">
    <div id="fn_advantages_item_2">
        <div class="advantages_item_wrapper">
            <div class="h3"
                 data-language="advantages_item_guarantee_title">{$lang->advantages_item_guarantee_title}</div>
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
            <div class="h3"
                 data-language="advantages_original_goods_title">{$lang->advantages_original_goods_title}</div>
            <p data-language="advantages_original_goods_info">{$lang->advantages_original_goods_info}</p>
        </div>
    </div>
</div>

<div class="hidden">
    <div id="fn_share_product">
        <div class="share_product_wrapper">
            <div class="h3" data-language="share_product_title">{$lang->share_product_title}</div>
            <p data-language="share_product_info">{$lang->share_product_info}</p>
            {* fn_share *}
            <div class="share_product_items">
                <a class="fn_share_product"
                   href="https://t.me/share/url?url={$config->root_url}{$smarty.server.REQUEST_URI}&text={$product->name|escape}">{include file="svg.tpl" svgId="telegram_icon"}</a>
                <a class="fn_share_product"
                   href="viber://forward?text={$product->name|escape}%20{$config->root_url}{$smarty.server.REQUEST_URI}">{include file="svg.tpl" svgId="viber_icon"}</a>
                {* <a class="fn_share_product" href="#">{include file="svg.tpl" svgId="telegram_icon"}</a> *}
                {* <a class="fn_share_product" href="#">{include file="svg.tpl" svgId="viber_icon"}</a> *}
            </div>
        </div>
    </div>
</div>
