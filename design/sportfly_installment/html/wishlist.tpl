{* page title *}
{$meta_title = $lang->wishlist_title scope=parent}

{* Page heading *}
<h1 class="main_section_title"><span data-language="wishlist_header">{$lang->wishlist_header}</span></h1>

{if $page->description}
    <div class="block">
        {$page->description}
    </div>
{/if}

{if $wished_products|count}
    <div class="fn_wishlist_page wish_products clearfix">
        {* Список избранных товаров *}
        {foreach $wished_products as $product}
            <div class="products_item">
                {include "product_list.tpl"}
            </div>
        {/foreach}
    </div>
{else}
    <div class="block">
        <span data-language="wishlist_empty">{$lang->wishlist_empty}</span>
    </div>
{/if}