{* The canonical address of the page *}
{$canonical="" scope=parent}

{* {get_categories var='featured_categories' category_visible=1 category_featured=1} *}
{get_banner var="banner_second_main" group="second_main"}
{if $banner_second_main->items}
    <div class="container">
        <div class="banner_second_main_items">
            {foreach $banner_second_main->items as $bi}
                <div class="banner_second_main_item">
                    {if $bi->url}
                        <a href="{$bi->url}">
                    {/if}
                  {if $bi->image}
                    <picture>
                        {* mobile webp *}
                        <source media="(max-width: 480px)" type="image/webp"
                            {if $is_mobile}
                                srcset="{$bi->image|resize:480:200:false:$config->resized_banners_images_dir:null:null:true}"
                            {elseif !$is_mobile && $bi@iteration <= 2}
                                srcset="{$bi->image|resize:480:200:false:$config->resized_banners_images_dir:null:null:true}"
                            {else}
                                data-srcset="{$bi->image|resize:480:200:false:$config->resized_banners_images_dir:null:null:true}"
                                srcset="{$rootUrl}/design/{$settings->theme|escape}/images/xloading.gif"
                            {/if}>

                        {* desktop webp *}
                        <source type="image/webp"
                            {if $is_mobile}
                                srcset="{$bi->image|resize:1170:390:false:$config->resized_banners_images_dir:null:null:true}"
                            {elseif !$is_mobile && $bi@iteration <= 2}
                                srcset="{$bi->image|resize:1170:390:false:$config->resized_banners_images_dir:null:null:true}"
                            {else}
                                data-srcset="{$bi->image|resize:1170:390:false:$config->resized_banners_images_dir:null:null:true}"
                                srcset="{$rootUrl}/design/{$settings->theme|escape}/images/xloading.gif"
                            {/if}>

                        {* mobile jpeg *}
                        <source media="(max-width: 480px)" type="image/jpeg"
                            {if $is_mobile}
                                srcset="{$bi->image|resize:480:200:false:$config->resized_banners_images_dir}"
                            {elseif !$is_mobile && $bi@iteration <= 2}
                                srcset="{$bi->image|resize:480:200:false:$config->resized_banners_images_dir}"
                            {else}
                                data-srcset="{$bi->image|resize:480:200:false:$config->resized_banners_images_dir}"
                                srcset="{$rootUrl}/design/{$settings->theme|escape}/images/xloading.gif"
                            {/if}>

                        {* desktop jpeg *}
                        <source type="image/jpeg"
                            {if $is_mobile}
                                srcset="{$bi->image|resize:1170:390:false:$config->resized_banners_images_dir}"
                            {elseif !$is_mobile && $bi@iteration <= 2}
                                srcset="{$bi->image|resize:1170:390:false:$config->resized_banners_images_dir}"
                            {else}
                                data-srcset="{$bi->image|resize:1170:390:false:$config->resized_banners_images_dir}"
                                srcset="{$rootUrl}/design/{$settings->theme|escape}/images/xloading.gif"
                            {/if}>

                        {* fallback img *}
                        <img fetchpriority="high"
                            {if $is_mobile}
                                src="{$bi->image|resize:480:100:false:$config->resized_banners_images_dir}"
                            {elseif !$is_mobile && $bi@iteration <= 2}
                                src="{$bi->image|resize:1170:390:false:$config->resized_banners_images_dir}"
                            {else}
                                class="lazy lazy-bg"
                                data-src="{$bi->image|resize:1170:390:false:$config->resized_banners_images_dir}"
                                src="{$rootUrl}/design/{$settings->theme|escape}/images/xloading.gif"
                                {* loading="lazy" *}
                            {/if}
                            alt="{$bi->alt}"
                            title="{$bi->title}"
                            data-x="{$bi@iteration}">
                    </picture>
                    {/if}
                    {if $bi->title}
                        <span class="banner_second_main_name">
                            {$bi->title}
                            {if $bi->annotation}
                                <span>{$bi->annotation}</span>
                            {/if}
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

{* Brand list *}
{get_brands var=all_brands visible_brand=1}
{if $all_brands}
    <div class="main_section section_bg main_brands">
        <div class="container brands_container">
            <div class="main_section_title">
                <span data-language="main_brands">{$lang->main_brands}</span>
                <a class="main_section_link" href="{$lang_link}brands"
                   data-language="all_brands_link">{$lang->all_brands_link}</a>
            </div>
            <div class="fn_brands_slide brand_items swiper">
                <div class="swiper-wrapper">
                    {foreach $all_brands as $b}
                        {if $b@iteration <= 10}
                            <div class="brand_item swiper-slide">
                                {if $b->image}
                                    <a class="brand_image" href="{$lang_link}brands/{$b->url}" data-brand="{$b->id}">
                                        <picture>
                                            <source media="(max-width: 480px)" type="image/webp"  data-srcset="{$b->image|resize:80:80:false:$config->resized_brands_dir:null:null:true}" srcset="{$rootUrl}/design/{$settings->theme|escape}/images/xloading.gif">
                                            <source type="image/webp"  data-srcset="{$b->image|resize:250:80:false:$config->resized_brands_dir:null:null:true}" srcset="{$rootUrl}/design/{$settings->theme|escape}/images/xloading.gif">

                                            <source media="(max-width: 480px)" type="image/jpeg" data-srcset="{$b->image|resize:80:80:false:$config->resized_brands_dir}" srcset="{$rootUrl}/design/{$settings->theme|escape}/images/xloading.gif">
                                            <source type="image/jpeg" data-srcset="{$b->image|resize:250:80:false:$config->resized_brands_dir}" srcset="{$rootUrl}/design/{$settings->theme|escape}/images/xloading.gif">

                                            <img class="lazy lazy-bg brand_img" {* loading="lazy" *} data-src="{$b->image|resize:250:80:false:$config->resized_brands_dir}" src="{$rootUrl}/design/{$settings->theme|escape}/images/xloading.gif" alt="{$b->name|escape}" title="{$b->name|escape}">
                                        </picture>
                                    </a>
                                {else}
                                    <a class="brand_name" href="{$lang_link}brands/{$b->url}" data-brand="{$b->id}">

                                        <span>{$b->name|escape}</span>
                                    </a>
                                {/if}
                            </div>
                        {/if}
                    {/foreach}
                </div>
            </div>
            <div class="swiper-button-next"></div>
            <div class="swiper-button-prev"></div>
        </div>
    </div>
{/if}

{* Featured products *}
{get_featured_products var=featured_products limit=8}
{if $featured_products}
    <div class="main_section">
        <div class="container fn_container">
            <div class="main_section_title">
                <span class="fn_container_name"
                      data-language="main_recommended_products">{$lang->main_recommended_products}</span>
                <a class="main_section_link" href="{$lang_link}bestsellers"
                   data-language="main_look_all">{$lang->main_look_all}</a>
            </div>
            <div class="fn_products_slide main_products swiper">
                <div class="swiper-wrapper">
                    {foreach $featured_products as $product}
                        <div class="products_item swiper-slide">
                            {include "product_list.tpl" product_type="featured"}
                        </div>
                    {/foreach}
                </div>
                <div class="swiper-button-next"></div>
                <div class="swiper-button-prev"></div>
                <div class="swiper-pagination"></div>
            </div>
        </div>
    </div>
{/if}

{* Discount products *}
{get_discounted_products var=discounted_products limit=8}
{if $discounted_products}
    <div class="main_section">
        <div class="container fn_container">
            <div class="main_section_title">
                <span class="fn_container_name"
                      data-language="main_discount_products">{$lang->main_discount_products}</span>
                <a class="main_section_link" href="{$lang_link}discounted"
                   data-language="main_look_all">{$lang->main_look_all}</a>
            </div>

            <div class="fn_products_slide main_products swiper">
                <div class="swiper-wrapper">
                    {foreach $discounted_products as $product}
                        <div class="products_item swiper-slide">
                            {include "product_list.tpl" product_type="discounted"}
                        </div>
                    {/foreach}
                </div>
                <div class="swiper-button-next"></div>
                <div class="swiper-button-prev"></div>
                <div class="swiper-pagination"></div>
            </div>
        </div>
    </div>
{/if}

{get_banner var="banner_military_hub" group="military_hub"}
{if $banner_military_hub->items}
    <div class="military_hub_wrapper">
        {foreach $banner_military_hub->items as $bi}
            <div class="military_hub_item">
                <picture class="military_hub_image">
                    <source media="(max-width: 480px)" type="image/webp" data-srcset="{$bi->image|resize:480:200:false:$config->resized_banners_images_dir:null:null:true}" srcset="{$rootUrl}/design/{$settings->theme|escape}/images/xloading.gif">
                    <source type="image/webp" data-srcset="{$bi->image|resize:1920:700:false:$config->resized_banners_images_dir:null:null:true}" srcset="{$rootUrl}/design/{$settings->theme|escape}/images/xloading.gif">

                    <source media="(max-width: 480px)" type="image/jpeg" data-srcset="{$bi->image|resize:480:200:false:$config->resized_banners_images_dir}" srcset="{$rootUrl}/design/{$settings->theme|escape}/images/xloading.gif">
                    <source type="image/jpeg" data-srcset="{$bi->image|resize:1920:700:false:$config->resized_banners_images_dir}" srcset="{$rootUrl}/design/{$settings->theme|escape}/images/xloading.gif">

                    <img class="lazy lazy-bg" data-src="{$bi->image|resize:1920:700:false:$config->resized_banners_images_dir}" src="{$rootUrl}/design/{$settings->theme|escape}/images/xloading.gif" {* loading="lazy" *} alt="{$bi->alt}" title="{$bi->title}">
                </picture>
                {if $bi->url}
                    <a class="military_hub_link btn_black" href="{$bi->url}" data-language="military_hub_link">{$lang->military_hub_link}</a>
                {/if}
            </div>
        {/foreach}
    </div>
{/if}

{if $categories}
    <div class="main_section">
        <div class="container categories_container">
            <div class="fn_categories_slide categories__list swiper">
                <div class="swiper-wrapper">
                    {function name=categories_tree3}
                        {if $categories}
                            {foreach $categories as $c}
                                {if $c->visible && $c->featured}
                                    <div class="categories__item swiper-slide">
                                        <a class="categories__link" href="{$lang_link}catalog/{$c->url}"
                                           data-category="{$c->id}">
                                            {if $c->image}
                                                <div class="categories__image">
                                                    <picture>
                                                        <source media="(max-width: 480px)" type="image/webp" data-srcset="{$c->image|resize:80:80:false:$config->resized_categories_dir:null:null:true}" srcset="{$rootUrl}/design/{$settings->theme|escape}/images/xloading.gif">
                                                        <source type="image/webp" data-srcset="{$c->image|resize:112:112:false:$config->resized_categories_dir:null:null:true}" srcset="{$rootUrl}/design/{$settings->theme|escape}/images/xloading.gif">

                                                        <source media="(max-width: 480px)" type="image/jpeg" data-srcset="{$c->image|resize:80:80:false:$config->resized_categories_dir}" srcset="{$rootUrl}/design/{$settings->theme|escape}/images/xloading.gif">
                                                        <source type="image/jpeg" data-srcset="{$c->image|resize:112:112:false:$config->resized_categories_dir}" srcset="{$rootUrl}/design/{$settings->theme|escape}/images/xloading.gif">
                                                        <img class="lazy lazy-bg" data-src="{$c->image|resize:112:112:false:$config->resized_categories_dir}" src="{$rootUrl}/design/{$settings->theme|escape}/images/xloading.gif" {* loading="lazy" *} alt="{$c->name|escape}">
                                                    </picture>
                                                </div>
                                            {else}
                                                <div class="categories__no_image" title="{$c->name|escape}">
                                                    {include file="svg.tpl" svgId="no_image"}
                                                </div>
                                            {/if}
                                            <span class="categories__name">{$c->name}</span>
                                        </a>
                                    </div>
                                    {categories_tree3 categories=$c->subcategories level=$level + 1}
                                {/if}
                            {/foreach}
                        {/if}
                    {/function}
                    {categories_tree3 categories=$categories level=1}
                </div>
            </div>
            <div class="swiper-button-next"></div>
            <div class="swiper-button-prev"></div>
        </div>
    </div>
{/if}
{* New products *}
{get_new_products var=latest_sizes_products max_stock=3 sort=position limit=8}
{if $latest_sizes_products}
    <div class="main_section">
        <div class="container fn_container">
            <div class="main_section_title">
                <span class="fn_container_name" data-language="latest_sizes_title">{$lang->latest_sizes_title}</span>
            </div>
            <div class="fn_products_slide main_products swiper">
                <div class="swiper-wrapper">
                    {foreach $latest_sizes_products as $product}
                        <div class="products_item swiper-slide">
                            {include "product_list.tpl" product_type="new"}
                        </div>
                    {/foreach}
                </div>
                <div class="swiper-button-next"></div>
                <div class="swiper-button-prev"></div>
                <div class="swiper-pagination"></div>
            </div>
        </div>
    </div>
{/if}

{get_comments var='comments'}
{if $comments}
    <div class="main_section section_bg reviews_section">
        <div class="container reviews_container">
            <div class="main_section_title">
                <span data-language="main_reviews_title">{$lang->main_reviews_title}</span>
                <a class="main_section_link" href="{$lang_link}reviews"
                   data-language="main_all_reviews">{$lang->main_all_reviews}</a>
            </div>
            <div class="fn_reviews_slide reviews_items swiper">
                <div class="swiper-wrapper">
                    {foreach $comments as $comment}
                        <div class="reviews_item swiper-slide">
                            {$type = ''}
                            {if $comment->type == 'product'}
                                {$type = "products/"}
                            {elseif $comment->type|in_array:['blog', 'news']}
                                {$type = "`$comment->type`/"}
                            {/if}
                            <a href="{$lang_link}{$type}{$comment->object->url}">
                                <div class="reviews_item_author">
                                    <div class="reviews_item_author_image">{include file="svg.tpl" svgId="user_icon"}</div>
                                    <div class="reviews_item_author_info">
                                        <span class="reviews_item_author_name">{$comment->name}</span>
                                        <span class="reviews_item_date">{$comment->date|date:'d.m.Y, H:i'}</span>
                                    </div>
                                </div>
                                <div class="reviews_item_text">{$comment->text}</div>
                            </a>
                        </div>
                    {/foreach}
                </div>
            </div>
            <div class="swiper-button-next"></div>
            <div class="swiper-button-prev"></div>
        </div>
    </div>
{/if}

<div class="social_line">
    <div class="social">
        <a class="social_link" href="https://www.instagram.com/sportfly.com.ua" target="_blank"
           title="Instagram">{include file="svg.tpl" svgId="inst_icon"}</a>
        <a class="social_link" href="https://www.facebook.com/SportFly.com.ua" target="_blank"
           title="Facebook">{include file="svg.tpl" svgId="fb_icon"}</a>
    </div>
    <span class="social_line_text" data-language="social_line_text">{$lang->social_line_text}</span>
</div>

    <div class="section_bg">
        <div class="container">
            <div class="our_shops_wrapper">
                <div class="our_shops_map">
                    <picture>
                        <source type="image/webp" data-srcset="design/{$settings->theme|escape}/images/map.webp" srcset="{$rootUrl}/design/{$settings->theme|escape}/images/xloading.gif">
                        <source type="image/jpeg" data-srcset="design/{$settings->theme|escape}/images/map.png" srcset="{$rootUrl}/design/{$settings->theme|escape}/images/xloading.gif">

                        <img class="lazy" data-src="design/{$settings->theme|escape}/images/map.png" src="{$rootUrl}/design/{$settings->theme}/images/xloading.gif" {* loading="lazy" *} alt="Our shops map image">
                    </picture>
                </div>
                <div class="our_shops_content">
                    <span class="our_shops_content_text" data-language="our_shops_content_text">{$lang->our_shops_content_text}</span>
                    <div class="our_shops_items">
                        <span class="our_shops_item" data-language="our_shop_item_1">{include file="svg.tpl" svgId="location_icon"}{$lang->our_shop_item_1}</span>
                        <span class="our_shops_item" data-language="our_shop_item_2">{include file="svg.tpl" svgId="location_icon"}{$lang->our_shop_item_2}</span>
                        <span class="our_shops_item" data-language="our_shop_item_3">{include file="svg.tpl" svgId="location_icon"}{$lang->our_shop_item_3}</span>
                        <span class="our_shops_item" data-language="our_shop_item_4">{include file="svg.tpl" svgId="location_icon"}{$lang->our_shop_item_4}</span>
                        <span class="our_shops_item" data-language="our_shop_item_5">{include file="svg.tpl" svgId="location_icon"}{$lang->our_shop_item_5}</span>
                    </div>
                    <a class="our_shops_link" href="{$lang_link}contact" data-language="link_to_shops">{$lang->link_to_shops}</a>
                </div>
            </div>
        </div>
    </div>


<div class="main_section">
    <div class="container">
        <div class="main_section_title">
            <span data-language="sportfly_offers_title">{$lang->sportfly_offers_title}</span>
        </div>
        <div class="sportfly_offers_items">
            {get_banner var="banner_sportfly_offers" group="sportfly_offers"}
            {foreach $banner_sportfly_offers->items as $bi}
                <div class="sportfly_offers_item lazy"{if $bi->image} data-bg="{$bi->image|resize:500:500:false:$config->resized_banners_images_dir}"{/if}>
                    {if $bi->url}<a class="sportfly_offers_item_link" href="{$bi->url}" target="_blank">{/if}
                        {* {if $bi->image}
                            <picture class="sportfly_offers_item_image">
                                <source  media="(max-width: 480px)" type="image/webp" data-srcset="{$bi->image|resize:460:300:false:$config->resized_banners_images_dir:null:null:true}" srcset="{$rootUrl}/design/{$settings->theme|escape}/images/xloading.gif">
                                <source  type="image/webp" data-srcset="{$bi->image|resize:500:500:false:$config->resized_banners_images_dir:null:null:true}" srcset="{$rootUrl}/design/{$settings->theme|escape}/images/xloading.gif">

                                <source  media="(max-width: 480px)" type="image/jpeg" data-srcset="{$bi->image|resize:460:300:false:$config->resized_banners_images_dir}" srcset="{$rootUrl}/design/{$settings->theme|escape}/images/xloading.gif">
                                <source  type="image/jpeg" data-srcset="{$bi->image|resize:500:500:false:$config->resized_banners_images_dir}" srcset="{$rootUrl}/design/{$settings->theme|escape}/images/xloading.gif">
                                <img class="lazy lazy-bg" data-src="{$bi->image|resize:500:500:false:$config->resized_banners_images_dir}" src="{$rootUrl}/design/{$settings->theme|escape}/images/xloading.gif" loading="lazy"  alt="{$bi->alt}" title="{$bi->title}">
                            </picture>
                        {/if} *}
                        <div class="sportfly_offers_item_content">
                                <div class="sportfly_offers_item_name">
                                    {$bi->title}
                                </div>
                            {if $bi->description}
                                <div class="sportfly_offers_item_description">
                                    {$bi->description}
                                </div>
                            {/if}
                        </div>
                    {if $bi->url}</a>{/if}
                </div>
            {/foreach}
        </div>
    </div>
</div>

{get_posts var=last_posts limit=4 type_post="blog"}
{if $last_posts}
    <div class="main_section">
        <div class="container">
            <div class="main_section_title">
                <span data-language="main_news">{$lang->main_news}</span>
                <a class="main_section_link" href="{$lang_link}blog"
                   data-language="main_all_news">{$lang->main_all_news}</a>
            </div>
            <div class="news_items">
                {foreach $last_posts as $post}
                    <a href="{$lang_link}{$post->type_post}/{$post->url}" data-post="{$post->id}" class="news_item">
                        <div class="news_image">
                            {if $post->image}
                                <picture>
                                    <source media="(max-width: 480px)" type="image/webp" data-srcset="{$post->image|resize:250:150:false:$config->resized_blog_dir:null:null:true}" srcset="{$rootUrl}/design/{$settings->theme|escape}/images/xloading.gif">
                                    <source type="image/webp" data-srcset="{$post->image|resize:350:250:false:$config->resized_blog_dir:null:null:true}" srcset="{$rootUrl}/design/{$settings->theme|escape}/images/xloading.gif">

                                    <source media="(max-width: 480px)" type="image/jpeg" data-srcset="{$post->image|resize:250:150:false:$config->resized_blog_dir}" srcset="{$rootUrl}/design/{$settings->theme|escape}/images/xloading.gif">
                                    <source type="image/jpeg" data-srcset="{$post->image|resize:350:250:false:$config->resized_blog_dir}" srcset="{$rootUrl}/design/{$settings->theme|escape}/images/xloading.gif">

                                    <img class="lazy lazy-bg news_img" data-src="{$post->image|resize:350:250:false:$config->resized_blog_dir}" src="{$rootUrl}/design/{$settings->theme|escape}/images/xloading.gif" {* loading="lazy" *} alt="{$post->name|escape}" title="{$post->name|escape}">
                                </picture>
                            {/if}
                        </div>
                        <div class="news_content">
                            {* News date *}
                            <div class="news_date">{include file="svg.tpl" svgId="date_icon"}
                                <span>{$post->date|date}</span></div>
                            {* News name *}
                            <div class="news_name">
                                <span>{$post->name|escape}</span>
                            </div>
                        </div>
                    </a>
                {/foreach}
            </div>
        </div>
    </div>
{/if}