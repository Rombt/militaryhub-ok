{* selected filters *}
{if $is_filter_page}
<div class="sidebar_top">
    <div class="filters">
        <div class="filter_name">
            <span data-language="selected_features_heading">{$lang->selected_features_heading}</span>
        </div>
        <div class="filter_group">
            <div class="selected_filter_boxes">

                {if $prices->current->min != '' && $prices->current->max != ''}
                    <div class="selected_filter_box">
                        <form class="selected_filter_item" method="post">
                            <button type="submit" name="prg_seo_hide" class="fn_filter_reset s_filter_link checked" value="{furl}">
                                {include file="svg.tpl" svgId="reset_icon"}
                                {$lang->features_price}: {$prices->current->min|escape} - {$prices->current->max|escape}
                            </button>
                        </form>
                    </div>
                {/if}
                
                {* Other filters *}
                {if $other_filters && $smarty.get.filter}
                    {foreach $other_filters as $f}
                        {if in_array($f->url, $smarty.get.filter)}
                            {$furl = {furl params=[filter=>$f->url, page=>null]}}
                            <div class="selected_filter_box">
                                <form class="selected_filter_item" method="post">
                                    <button type="submit" name="prg_seo_hide" class="s_filter_link checked" value="{$furl|escape}">
                                        {include file="svg.tpl" svgId="reset_icon"}
                                        <span data-language="{$f->translation}">{$f->name}</span>
                                    </button>
                                </form>
                            </div>
                        {/if}
                    {/foreach}
                {/if}
        
                {* Brand filter *}
                {if $category->brands && $smarty.get.b}
                    {foreach $category->brands as $b}
                        {if $brand->id == $b->id || in_array($b->id,$smarty.get.b)}
                            {$furl = {furl params=[brand=>$b->url, page=>null]}}
                            <div class="selected_filter_box">
                                <form class="selected_filter_item" method="post">
                                    <button type="submit" name="prg_seo_hide" class="s_filter_link checked" value="{$furl|escape}">
                                        {include file="svg.tpl" svgId="reset_icon"}
                                        <span>{$b->name|escape}</span>
                                    </button>
                                </form>
                            </div>
                        {/if}
                    {/foreach}
                {/if}
                
                {* Features filter *}
                {if $features}
                    {foreach $features as $key=>$f}
                        {if $smarty.get.{$f@key}}
                            {foreach $f->features_values as $fv}
                                {if in_array($fv->translit,$smarty.get.{$f@key},true)}
                                    {$furl = {furl params=[$f->url=>$fv->translit, page=>null]}}
                                    <div class="selected_filter_box">
                                        <form class="selected_filter_item" method="post">
                                            <button type="submit" name="prg_seo_hide" class="s_filter_link checked" value="{$furl|escape}">
                                                {include file="svg.tpl" svgId="reset_icon"}
                                                <span>{$f->name|escape}: {$fv->value|escape}</span>
                                            </button>
                                        </form>
                                    </div>
                                {/if}
                            {/foreach}
                        {/if}
                    {/foreach}
                {/if}
            </div>
    
            {if $category}
                <form method="post">
                    <button type="submit" name="prg_seo_hide" class="fn_filter_reset filter_reset" value="{$config->root_url}/{$lang_link}catalog/{$category->url}">
                        {include file="svg.tpl" svgId="reset_icon"}
                        {$lang->selected_features_reset}
                    </button>
                </form>
            {elseif $brand}
                <form method="post">
                    <button type="submit" name="prg_seo_hide" class="fn_filter_reset filter_reset" value="{$config->root_url}/{$lang_link}brands/{$brand->url}">
                        {include file="svg.tpl" svgId="reset_icon"}
                        {$lang->selected_features_reset}
                    </button>
                </form>
            {/if}
        </div>
    </div>
</div>
{/if}
