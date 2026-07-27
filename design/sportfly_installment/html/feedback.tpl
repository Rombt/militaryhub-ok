{* Feedback page *}
<div class="feedback_page_wrapper">
    {* The canonical address of the page *}
    {$canonical="/{$page->url}" scope=parent}

    {* The page heading *}
    <h1 class="h1 main_section_title"><span>{if $page->name_h1|escape}{$page->name_h1|escape}{else}{$page->name|escape}{/if}</span></h1>

    <div class="contact_wrap clearfix">
        <div class="contact-info no_padding-left col-md-6 col-lg-6">
            <div class="contact_info_header" data-language="feedback_address_text"><span>{$lang->feedback_address_text}</span></div>
            {* Page body *}
            {if $page->description}
                <div class="feedback_description">
                    {$page->description}
                    {* <a class="instagram" href="https://www.instagram.com/sportfly.com.ua" rel="nofollow" target="_blank"
                        title="Instagram">{include file="svg.tpl" svgId="instagram"}<span>instagram</span></a>
                    <a class="facebook" href="https://www.facebook.com/SportFly.com.ua" rel="nofollow" target="_blank"
                        title="Facebook">{include file="svg.tpl" svgId="facebook"}<span>facebook</span></a> *}
                </div>
            {/if}
        </div>

        <div class="no_padding-right col-md-6 col-lg-6">
            {* Map *}
            {if $settings->iframe_map_code}
                <div class="ya_map">
                    {$settings->iframe_map_code}
                </div>
            {/if}
        </div>
    </div>

    {get_banner var="banner_group2" group="group2"}
    {if $banner_group2->items}
        <div class="feedback__partners">
            <div class="main_section_title"><span data-language="our_partners">{$lang->our_partners}</span></div>
            <div class="fn_brands_slide brand_items swiper">
                <div class="swiper-wrapper">
                    {foreach $banner_group2->items as $bi}
                        <div class="brand_item swiper-slide">
                            {if $bi->url}
                                <a class="brand_link" href="{$bi->url}" target="_blank">
                            {/if}
                                {if $bi->image}
                                    <div class="brand_link">
                                        <img src="{$bi->image|resize:250:80:false:$config->resized_banners_images_dir}" alt="{$bi->alt}" title="{$bi->title}"/>
                                    </div>
                                {/if}
                            {if $bi->url}
                                </a>
                            {/if}
                        </div>
                    {/foreach}
                </div>
            </div>
            <div class="swiper-button-next"></div>
            <div class="swiper-button-prev"></div>
        </div>
    {/if}
</div>

