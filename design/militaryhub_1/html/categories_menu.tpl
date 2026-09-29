<div class="rmbt-popup mh-catalog-popup-header" id="catalog-popup-header">

    <div class="rmbt-popup__overlay"></div>
    <div class="rmbt-popup__container mh-catalog-popup-header__container">
        <div class="mh-categories-tabs">

            <nav data-tabs-titles class="mh-categories-tabs__nav">
                {if isset($categories) && $categories|@is_array}
                    {foreach $categories as $category}
                        {if isset($category)}
                            <div class="mh-categories-tabs__title{if $category@first} mh-categories-tabs__title-active{/if}" data-tab="{$category->name|default:''}">
                                <a href="{$category->url|default:''}">
                                    {$category->name|default:''}
                                </a>
                                <svg id="rmbt-mh-catalog-button-open">
                                    {* <use href="./catalog/view/theme/militaryhub/image/sprite.svg#09_arrow"></use> *}
                                </svg>
                            </div>
                        {/if}
                    {/foreach}
                {/if}
            </nav>

            <div class="mh-categories-tabs__content">
                {if isset($categories) && $categories|@is_array}
                    {foreach $categories as $category}
                        {if $category|@is_array}
                            <div class="mh-categories-tabs__body{if $category@first} mh-categories-tabs__body-active{/if}" data-tab-name="{$category.name|default:''}">

                                {if isset($category.children) && $category.children|@is_array}
                                    {foreach $category.children as $child}
                                        {if $child|@is_array}
                                            <div class="mh-categories-tabs__category-child-level-3">

                                                <a href="{$child.href|default:''}" class="mh-categories-tabs__parent-category">{$child.name|default:''}</a>

                                                {if isset($child.children_data_level_3)
                                                    && $child.children_data_level_3|@is_array
                                                }
                                                    {foreach $child.children_data_level_3 as $child_data_level_3}
                                                        {if $child_data_level_3|@is_array}
                                                            <a href="{$child.href|default:''}" class="mh-categories-tabs__child-category">{$child_data_level_3.name|default:''}</a>
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
            {$mh_slider_2|default:''}
        </div>

    </div>
</div>
