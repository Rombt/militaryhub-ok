{* Ajax пагинация *}
{if $total_pages_num > 1 && $current_page_num < $total_pages_num}
    {assign var=next_page value=$current_page_num+1}
    <button id="more_post" class="show_products_btn btn-2" type="button" data-url="{furl page=$next_page}" data-page="{$next_page}" data-language="show_products_btn">{$lang->show_products_btn}</button>
{/if}