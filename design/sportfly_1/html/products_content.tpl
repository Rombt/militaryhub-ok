{if $products}
    {foreach $products as $product}
        {* <div class="products_item{if $product@last} show_product{/if}"> *}
        <div class="products_item">
            {include file="product_list.tpl" product_iteration=$product@iteration is_products_content=true}
            {* {if $product@last}
                <div class="fn_live_pagination">
                    {include file='pagination_live.tpl'}
                </div>
            {/if} *}
        </div>
    {/foreach}
{else}
    <span data-language="products_not_found">{$lang->products_not_found}</span>
{/if}