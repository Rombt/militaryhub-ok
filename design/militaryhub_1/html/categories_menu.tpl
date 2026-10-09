<div class="rmbt-popup mh-catalog-popup-header" id="catalog-popup-header">

    <div class="rmbt-popup__overlay"></div>
    {* <div class="rmbt-popup__container mh-catalog-popup-header__container container-full"> *}
        <div class="rmbt-popup__container mh-catalog-popup-header__container container-compact">

            <div class="mh-catalog-popup-header__title">
                {include file="svg.tpl" svgId="icon_catalog"}
                <span>Каталог товарів</span>
                {include file="svg.tpl" svgId="icon_close"}
            </div>
            <div class="rmbt-categories-tabs">
                <nav data-tabs-titles class="rmbt-categories-tabs__nav">
                    {if isset($categories) && $categories|@is_array}
                        {foreach $categories as $category}
                            {if isset($category)}
                                <div class="rmbt-categories-tabs__title{if $category@first} rmbt-categories-tabs__title-active{/if}" data-tab="{$category->name|default:''}">
                                    <a href="{$category->url|default:''}">
                                        {$category->name|default:''}
                                    </a>
                                    {include file="svg.tpl" svgId="rmbt-mh-catalog-button-open"}
                                    {* <svg id="rmbt-mh-catalog-button-open"> *}
                                        {* <use href="./catalog/view/theme/militaryhub/image/sprite.svg#09_arrow"></use> *}
                                        {* </svg> *}
                                </div>
                            {/if}
                        {/foreach}
                    {/if}
                </nav>
                <div class="rmbt-categories-tabs__content">
                    {if isset($categories) && $categories|@is_array}
                        {foreach $categories as $category}
                            {if $category}
                                <div class="rmbt-categories-tabs__body{if $category@first} rmbt-categories-tabs__body-active{/if}" data-tab-name="{$category->name|default:''}">

                                    {if isset($category->subcategories) && $category->subcategories|@is_array}
                                        {foreach $category->subcategories as $child}
                                            {if $child}
                                                <div class="rmbt-categories-tabs__category-child-level-3">

                                                    <a href="{$child->url|default:''}" class="rmbt-categories-tabs__parent-category">{$child->name|default:''}</a>

                                                    {if isset($child->subcategories)
                                                        && $child->subcategories|@is_array
                                                    }
                                                        {foreach $child->subcategories as $child_data_level_3}
                                                            {if $child_data_level_3}
                                                                <a href="{$child_data_level_3->url|default:''}" class="rmbt-categories-tabs__child-category">{$child_data_level_3->name|default:''}</a>
                                                            {/if}
                                                        {/foreach}
                                                    {/if}

                                                </div>
                                            {/if}
                                        {/foreach}
                                    {/if}

                                </div>
                            {/if}
                        {/foreach}
                    {/if}
                </div>
            </div>
            <div class="mh-catalog-popup-header__right-column">
                {get_banner var="banner_catalog_categories_menu" group="catalog_categories_menu"}
                {if $banner_catalog_categories_menu->items}
                    <div class="fn_banner_catalog_categories_menu swiper">
                        <div class="swiper-wrapper">
                            {foreach $banner_catalog_categories_menu->items as $bi}
                                <div class="swiper-slide">
                                    {if $bi->url}
                                        <a href="{$bi->url}" target="_blank">
                                    {/if}

                                    {if $bi->image}
                                        <img src="{$bi->image|resize:1170:390:false:$config->resized_banners_images_dir}" alt="{$bi->alt}" title="{$bi->title}" />
                                    {/if}

                                    <div class="swiper-slide-text">

                                        <span class="swiper-slide-title">
                                            {$bi->title}
                                        </span>

                                        {if $bi->description}
                                            <span class="swiper-slide-description">
                                                {$bi->description}
                                            </span>
                                        {/if}
                                    </div>
                                    {if $bi->url}
                                        </a>
                                    {/if}
                                </div>
                            {/foreach}
                        </div>
                    </div>
                {/if}
            </div>

        </div>
    </div>
