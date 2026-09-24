<nav class="categories_nav">
    {function name=categories_tree}
        {if $categories}
            <div class="level_{$level} {if $level == 1}categories_nav__menu{else}categories_nav__subcategory{/if}">
                <ul class="{if $level == 1}categories_menu {elseif  $level == 3}categories_menu subcategory {else}subcategory {/if}">
                    {foreach $categories as $c}
                        {if $c->visible}
                            {if $c->subcategories && $c->count_children_visible}
                                <li class="category_item has_child">
                                    <a class="category_link{if $category->id == $c->id} selected{/if}" href="{$lang_link}catalog/{$c->url}" data-category="{$c->id}">
                                        <span>{$c->name|escape}</span>
                                    </a>
                                    {categories_tree categories=$c->subcategories level=$level + 1}
                                </li>
                            {else}
                                <li class="category_item">
                                    <a class="category_link{if $category->id == $c->id} selected{/if}" href="{$lang_link}catalog/{$c->url}" data-category="{$c->id}">{$c->name|escape}</a>
                                </li>
                            {/if}
                        {/if}
                    {/foreach}
                </ul>
            </div>
        {/if}
    {/function}
    {categories_tree categories=$categories level=1}
</nav>
