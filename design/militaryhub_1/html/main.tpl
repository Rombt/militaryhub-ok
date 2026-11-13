{* The canonical address of the page *}
{$canonical="" scope=parent}

{get_banner var="banner_second_main" group="second_main"}
{if $banner_second_main->items}
    <div class="container">
        <div class="main_section_title">
            <h2 data-language="popular_categories_title">{$lang->popular_categories_title}</h2>
        </div>
        <div class="banner_second_main_items swiper">
            <div class="swiper-wrapper">
                {foreach $banner_second_main->items as $bi}
                    {if $bi@iteration > 5}{break}{/if}

                    <div class="banner_second_main_item swiper-slide">
                        {if $bi->url}
                            <a href="{$bi->url}">
                        {/if}
                        {if $bi->image}
                            <picture>
                                {* mobile webp *}
                                <source media="(max-width: 480px)" type="image/webp" {if $is_mobile} srcset="{$bi->image|resize:480:200:false:$config->resized_banners_images_dir:null:null:true}" {elseif !$is_mobile && $bi@iteration <= 2} srcset="{$bi->image|resize:480:200:false:$config->resized_banners_images_dir:null:null:true}" {else} data-srcset="{$bi->image|resize:480:200:false:$config->resized_banners_images_dir:null:null:true}" srcset="{$rootUrl}/design/{$settings->theme|escape}/images/xloading.gif" {/if}>

                                {* desktop webp *}
                                <source type="image/webp" {if $is_mobile} srcset="{$bi->image|resize:1170:390:false:$config->resized_banners_images_dir:null:null:true}" {elseif !$is_mobile && $bi@iteration <= 2} srcset="{$bi->image|resize:1170:390:false:$config->resized_banners_images_dir:null:null:true}" {else} data-srcset="{$bi->image|resize:1170:390:false:$config->resized_banners_images_dir:null:null:true}" srcset="{$rootUrl}/design/{$settings->theme|escape}/images/xloading.gif" {/if}>

                                {* mobile jpeg *}
                                <source media="(max-width: 480px)" type="image/jpeg" {if $is_mobile} srcset="{$bi->image|resize:480:200:false:$config->resized_banners_images_dir}" {elseif !$is_mobile && $bi@iteration <= 2} srcset="{$bi->image|resize:480:200:false:$config->resized_banners_images_dir}" {else} data-srcset="{$bi->image|resize:480:200:false:$config->resized_banners_images_dir}" srcset="{$rootUrl}/design/{$settings->theme|escape}/images/xloading.gif" {/if}>

                                {* desktop jpeg *}
                                <source type="image/jpeg" {if $is_mobile} srcset="{$bi->image|resize:1170:390:false:$config->resized_banners_images_dir}" {elseif !$is_mobile && $bi@iteration <= 2} srcset="{$bi->image|resize:1170:390:false:$config->resized_banners_images_dir}" {else} data-srcset="{$bi->image|resize:1170:390:false:$config->resized_banners_images_dir}" srcset="{$rootUrl}/design/{$settings->theme|escape}/images/xloading.gif" {/if}>

                                {* fallback img *}
                                <img fetchpriority="high" {if $is_mobile} src="{$bi->image|resize:480:100:false:$config->resized_banners_images_dir}" {elseif !$is_mobile && $bi@iteration <= 2} src="{$bi->image|resize:1170:390:false:$config->resized_banners_images_dir}" {else} class="lazy lazy-bg" data-src="{$bi->image|resize:1170:390:false:$config->resized_banners_images_dir}" src="{$rootUrl}/design/{$settings->theme|escape}/images/xloading.gif" {* loading="lazy" *} {/if}
                                alt="{$bi->alt}" title="{$bi->title}" data-x="{$bi@iteration}">
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

        {include
            file="btn_link.tpl"
            text=$lang->popular_categories_text_button|default:''
            link=""|default:''
            icon_id='long_arrow'
        }
    </div>
{/if}

{* Discount products *}
{get_discounted_products var=discounted_products limit=8}
{if $discounted_products}
    <div class="main_section">
        {* <div class="container fn_container"> *}
            <div class="fn_products_slide_main_discount main_products rmbt-main_discount_products swiper">
                <div class="swiper-wrapper">
                    {foreach $discounted_products as $product}
                        <div class="products_item swiper-slide">
                            {include "mh_product_list.tpl" product_type="discounted"}
                        </div>
                    {/foreach}
                </div>
            </div>
            {*
        </div> *}
    </div>
{/if}

{* Featured products *}
{get_featured_products var=featured_products limit=8}
{* New products *}
{get_new_products var=latest_sizes_products max_stock=3 sort=position limit=8}
<div class="main_section">
    <div class="container fn_container">
        <div class="main_section_title">
            <h2 data-language="main_recommended_products">{$lang->main_recommended_products}</h2>
        </div>
        <div class="rmbt-mh-featured-products-tabs">
            <nav>
                {if $featured_products}
                    <div class="rmbt-mh-featured-products-tabs__title rmbt-mh-featured-products-tabs__title-active" data-tab="{$lang->featured_products_tabs_label_1}">
                        {$lang->featured_products_tabs_label_1}
                    </div>
                {/if}
                {if $latest_sizes_products}
                    <div class="rmbt-mh-featured-products-tabs__title" data-tab="{$lang->featured_products_tabs_label_2}">
                        {$lang->latest_sizes_title}
                    </div>
                {/if}
                {if $discounted_products}
                    <div class="rmbt-mh-featured-products-tabs__title" data-tab="{$lang->featured_products_tabs_label_3}">
                        {$lang->featured_products_tabs_label_3}
                    </div>
                {/if}
            </nav>
            <div class="rmbt-mh-featured-products-tabs__content">
                <div class="rmbt-mh-featured-products-tabs__body rmbt-mh-featured-products-tabs__body-active" data-tab-name="Хіти">
                    {if $featured_products}
                        <div class="fn_products_slide main_products rmbt-main_featured_products swiper">
                            <div class="swiper-wrapper">
                                {foreach $featured_products as $product}
                                    <div class="products_item swiper-slide">
                                        {include "product_list.tpl" product_type="featured"}
                                    </div>
                                {/foreach}
                            </div>
                        </div>
                        {include
                            file="btn_link.tpl"
                            text=$lang->main_look_all|default:''
                            link="{$lang_link}bestsellers"|default:''
                            icon_id='long_arrow'
                        }

                    {/if}
                </div>
                <div class="rmbt-mh-featured-products-tabs__body" data-tab-name="Новинки">
                    {if $latest_sizes_products}
                        <div class="fn_products_slide main_products swiper">
                            <div class="swiper-wrapper">
                                {foreach $latest_sizes_products as $product}
                                    <div class="products_item swiper-slide">
                                        {include "product_list.tpl" product_type="new"}
                                    </div>
                                {/foreach}
                            </div>
                        </div>
                        {include
                            file="btn_link.tpl"
                            text=$lang->main_look_all|default:''
                            link="{$lang_link}bestsellers"|default:''
                            icon_id='long_arrow'
                        }
                    {/if}
                </div>
                <div class="rmbt-mh-featured-products-tabs__body" data-tab-name="Акції">
                    {if $discounted_products}
                        <div class="fn_products_slide main_products rmbt-main_featured_products swiper">
                            <div class="swiper-wrapper">
                                {foreach $discounted_products as $product}
                                    <div class="products_item swiper-slide">
                                        {include "product_list.tpl" product_type="featured"}
                                    </div>
                                {/foreach}
                            </div>
                        </div>
                        {include
                            file="btn_link.tpl"
                            text=$lang->main_look_all|default:''
                            link="{$lang_link}bestsellers"|default:''
                            icon_id='long_arrow'
                        }
                    {/if}
                </div>
            </div>
        </div>
    </div>
</div>

{* Offers block *}
<div class="main_section">
    <div class="container fn_container">
        {get_banner var="banner_offers_block" group="offers_block"}
        {if $banner_offers_block->items}
            <div class="container hidden-md-down">
                <div class="fn_banner_offers_block main_offers_block">
                    {foreach $banner_offers_block->items as $bi}
                        <div>
                            {if $bi->url}
                                <a href="{$bi->url}" target="_blank">
                            {/if}
                            {if $bi->image}
                                <img src="{$bi->image|resize:1170:390:false:$config->resized_banners_images_dir}" alt="{$bi->alt}" title="{$bi->title}" />
                            {/if}
                            <span class="offers-banner-name">
                                {$bi->title}
                            </span>
                            {if $bi->description}
                                <span class="offers-banner-description">
                                    {$bi->description}
                                </span>
                            {/if}
                            {if $bi->url}

                                <span class="offers-banner-button">
                                    Переглянути
                                    {include file="svg.tpl" svgId="long_arrow"}
                                </span>

                                </a>
                            {/if}


                        </div>
                    {/foreach}
                </div>
            </div>
        {/if}

    </div>
</div>

{* Blog block *}
{get_posts var=last_posts limit=4 type_post="blog"}
{if $last_posts}
    <div class="main_section">
        <div class="container">
            <div class="main_section_title">
                <span data-language="main_news">{$lang->main_news}</span>
            </div>

            <div class="fn_news_items swiper">
                <div class="swiper-wrapper">
                    {foreach $last_posts as $post}
                        <a href="{$lang_link}{$post->type_post}/{$post->url}" data-post="{$post->id}" class="news_item swiper-slide">
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
                                {* News name *}
                                <div class="news_name">
                                    <span>{$post->name|escape}</span>
                                </div>
                                {* News description *}
                                <div class="news_description">
                                    <span>{$post->description|trim_words:20:' ...'}</span>
                                </div>

                                <div class="news_footer">
                                    <div class="news_reed-more">
                                        {$lang->news_reed_more|default:''}
                                        {include file="svg.tpl" svgId="long_arrow"}
                                    </div>
                                    {* News date *}
                                    <div class="news_date">{include file="svg.tpl" svgId="date_icon"}
                                        <span>{$post->date|date}</span>
                                    </div>
                                </div>
                            </div>
                        </a>
                    {/foreach}
                </div>
            </div>
            {include
                file="btn_link.tpl"
                text={$lang->main_all_news}|default:''
                link="{$lang_link}blog"|default:''
                icon_id='long_arrow'
            }
        </div>
    </div>
{/if}


{* about us block *}
{get_discounted_products var=discounted_products limit=8}
{if $discounted_products}
    <div class="main_section">
        {* <div class="container fn_container"> *}
            <div class="main_about_us">
                <div class="main_about_us__img">

                    {get_banner var="banner_about_us" group="about_us"}
                    {if $banner_about_us->items}
                        {foreach $banner_about_us->items as $bi}
                            <div>
                                {if $bi->url}
                                    <a href="{$bi->url}" target="_blank">
                                {/if}
                                {if $bi->image}
                                    <img src="{$bi->image|resize:1170:390:false:$config->resized_banners_images_dir}" alt="{$bi->alt}" title="{$bi->title}" />
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
                    {/if}


                </div>
                <div class="main_about_us__text">
                </div>
            </div>
            {*
        </div> *}
    </div>
{/if}
