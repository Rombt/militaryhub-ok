{* The Categories page *}

{* The canonical address of the page *}
{if $set_canonical || $self_canonical}
    {if $category}
        {$canonical="/catalog/{$category->url}" scope=parent}
    {elseif $brand}
        {$canonical="/brands/{$brand->url}" scope=parent}
    {elseif $page->url=='discounted'}
        {$canonical="/discounted" scope=parent}
    {elseif $page->url=='bestsellers'}
        {$canonical="/bestsellers" scope=parent}
    {elseif $keyword}
        {$canonical="/all-products" scope=parent}
    {else}
        {$canonical="/all-products" scope=parent}
    {/if}
{/if}

{* Sidebar with filters *}
<div class="products_page">
    {* The page heading *}
    {if $keyword}
        <h1 class="h1 main_section_title"><span
                    data-language="products_search">{$lang->products_search}</span> {$keyword|escape}</h1>
    {elseif $page}
        <h1 class="h1 main_section_title">
            <span data-page="{$page->id}">{if $page->name_h1|escape}{$page->name_h1|escape}{else}{$page->name|escape}{/if}</span>
        </h1>
    {elseif $seo_filter_pattern->h1}
        <h1 class="h1 main_section_title">{$seo_filter_pattern->h1|escape}</h1>
        {*//!!*}
    {elseif $clearance_sale_h1}
    {else}
        <h1 class="h1 main_section_title"><span
                    data-category="{$category->id}">{if $category->name_h1|escape}{$category->name_h1|escape}{else}{$category->name|escape}{/if}</span> {$brand->name|escape} {$filter_meta->h1|escape}
        </h1>
    {/if}

    <div class="products_wrapper">
        <div class="filters_heading fn_switch lg-hidden">
            <span data-language="filters">{$lang->filters}</span>
            <i class="filters_icon">{include file="svg.tpl" svgId="filters_icon"}</i>
        </div>
        <div class="sidebar">
            <div class="fn_selected_features">
                {include 'selected_features.tpl'}
            </div>
            <div class="sidebar_top fn_features">
                {include file='features.tpl'}
            </div>
        </div>

        <div class="products_container">
            {if $current_page_num == 1 && ($category->annotation || $brand->annotation) && !$is_filter_page && !$smarty.get.page && !$smarty.get.sort}
                <div class="block padding">
                    {* Краткое описание категории *}
                    {$category->annotation}

                    {* Краткое описание бренда *}
                    {$brand->annotation}
                </div>
            {/if}

            {if $products}
                {* Product Sorting *}
                <div class="fn_products_sort products_sort_wrapper">
                    {include file="products_sort.tpl"}
                    <div class="show_products">
                        <span class="sort_title" data-language="show_title">{$lang->show_title}:</span>
                        <div class="product_sort__select-box">
                            <form action="" method="post">
                                <select class="product_sort__select" name="show_count"
                                        onchange="$(this).closest('form').submit()">
                                    {if !$products_per_page|in_array:[25,35,40]}
                                        <option value="{$products_per_page}" selected>{$products_per_page}</option>
                                    {/if}
                                    <option value="25" {if $products_per_page==25}selected{/if}>25</option>
                                    <option value="35" {if $products_per_page==35}selected{/if}>35</option>
                                    <option value="40" {if $products_per_page==40}selected{/if}>40</option>
                                </select>
                            </form>
                        </div>
                    </div>
                </div>
            {/if}

            {* Product list *}
            <div id="fn_products_content" class="fn_categories products clearfix">
                {include file="products_content.tpl"}
            </div>

            {if $products}
                {* Friendly URLs Pagination *}
                <div class="fn_pagination">
                    {include file='chpu_pagination.tpl'}
                </div>
            {/if}

            {if $current_page_num == 1 && $page->description}
                <div class="block padding">
                    {$page->description}
                </div>
            {/if}

            {if $current_page_num == 1}
                {*SEO шаблон описания страницы фильтра*}
                {if $seo_filter_pattern->description}
                    <div class="block padding">
                        {$seo_filter_pattern->description}
                    </div>
                {elseif (!$category || !$brand) && ($category->description || $brand->description) && !$is_filter_page && !$smarty.get.page && !$smarty.get.sort}
                    <div class="block padding">
                        {* Описание категории *}
                        {$category->description}

                        {* Описание бренда *}
                        {$brand->description}
                    </div>
                {/if}
            {/if}

        </div>
    </div>

    {* Browsed products *}
    {get_browsed_products var=browsed_products}
    {if $browsed_products}
        <div class="main_section">
            <div class="main_section_title" data-language="features_browsed"><span>{$lang->features_browsed}</span>
            </div>
            <div class="fn_products_slide main_products swiper">
                <div class="swiper-wrapper">
                    {foreach $browsed_products as $p}
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
