{if $products|count > 0}
   <div class="fn_ajax_buttons sort">
        <span class="fn_sort_pagination_link sort_title" data-language="products_sort_by">{$lang->products_sort_by}:</span>
        <div class="product_sort__select-box">
            {* {include file="svg.tpl" svgId="sort_icon"} *}
            <select class="product_sort__select" name="prg_seo_hide" onchange="window.location.href = this.value;">
                <option value="{furl sort=position page=null absolute=1}" data-language="products_by_default"{if $sort == 'position'} selected{/if}>{$lang->products_by_default}</option>
                <option value="{furl sort=price page=null absolute=1}" data-language="products_by_price"{if $sort == 'price'} selected{/if}>{$lang->products_by_price}</option>
                {* <option value="{furl sort=price_desc page=null absolute=1}" data-language="products_by_price_desc"{if $sort == 'price_desc'} selected{/if}>{$lang->products_by_price_desc}</option> *}
                <option value="{furl sort=name page=null absolute=1}" data-language="products_by_name"{if $sort == 'name'} selected{/if}>{$lang->products_by_name}</option>
                {* <option value="{furl sort=name_desc page=null absolute=1}" data-language="products_by_name_desc"{if $sort == 'name_desc'} selected{/if}>{$lang->products_by_name_desc}</option> *}
                <option value="{furl sort=rating page=null absolute=1}" data-language="products_by_rating"{if $sort == 'rating'} selected{/if}>{$lang->products_by_rating}</option>
            </select>
            {* {include file="svg.tpl" svgId="arrow_down_icon"} *}
        </div>
    </div>

    {* <div class="fn_ajax_buttons sort clearfix">
        <span class="fn_sort_pagination_link sort_title" data-language="products_sort_by">{$lang->products_sort_by}:</span>

        <form method="post">
            <button type="submit" name="prg_seo_hide" class="fn_sort_pagination_link sort_link{if $sort=='position'} active_up{/if} no_after" value="{furl sort=position page=null}">
                <span data-language="products_by_default">{$lang->products_by_default}</span>
            </button>
        </form>

        <form method="post">
            <button type="submit" name="prg_seo_hide" class="fn_sort_pagination_link sort_link{if $sort=='price'} active_up{elseif $sort=='price_desc'} active_down{/if}" value="{if $sort=='price'}{furl sort=price_desc page=null}{else}{furl sort=price page=null}{/if}">
                <span data-language="products_by_price">{$lang->products_by_price}</span>
            </button>
        </form>

        <form method="post">
            <button type="submit" name="prg_seo_hide" class="fn_sort_pagination_link sort_link{if $sort=='name'} active_up{elseif $sort=='name_desc'} active_down{/if}" value="{if $sort=='name'}{furl sort=name_desc page=null}{else}{furl sort=name page=null}{/if}">
                <span data-language="products_by_name">{$lang->products_by_name}</span>
            </button>
        </form>

        <form method="post">
            <button type="submit" name="prg_seo_hide" class="fn_sort_pagination_link sort_link {if $sort=='rating'} active_up{elseif $sort=='rating_desc'} active_down{/if}" value="{if $sort=='rating'}{furl sort=rating_desc page=null}{else}{furl sort=rating page=null}{/if}">
                <span data-language="products_by_rating">{$lang->products_by_rating}</span>
            </button>
        </form>
    </div> *}
{/if}
