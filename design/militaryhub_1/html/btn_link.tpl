<a href="{$link|default:'#'}" class="mh-btn {$classes|default:''}" {$attr|default:''}>
    {if isset($text) && $text}
        <span>{$text}</span>
    {/if}

    {if isset($icon_id) && $icon_id}
        {include file="svg.tpl" svgId=$icon_id}
    {/if}
</a>
