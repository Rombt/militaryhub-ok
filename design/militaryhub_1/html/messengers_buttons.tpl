{if $menu_messengers}
    <div class="messengers_buttons">
        {foreach $menu_items_messengers as $item}

            {if $item->visible == 1}
                <a href="{$item->url|escape}" title="{$item->name|escape}" target="_blank" rel="nofollow noopener" class="messengers_buttons__link">{include file='svg.tpl' svgId="{$item->name|lower}_icon"}</a>
            {/if}
        {/foreach}
    </div>
{/if}
