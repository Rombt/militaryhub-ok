<ul class="top-nav _fn_menu_switch">
    <li>

        <div class="fn_menu_switch menu_switcher hidden">
            {include file="svg.tpl" svgId="menu_switcher_icon"}
            <span class="" data-language="index_mobile_menu">{$lang->index_mobile_menu}</span>
            {include file="svg.tpl" svgId="icon_close"}
        </div>

    </li>
</ul>

{function name=categories_four}
    {if $categories}
        <ul class="">
            {foreach $categories as $c}
                {if $c->visible}
                    {if $c->subcategories && $c->count_children_visible}
                        <li class="">
                            <a class="{if $category->id == $c->id} selected{/if}" href="{$lang_link}{$config->catalog_url}{$c->url}" data-category="{$c->id}">
                                {* {if $c->image}
                                    <span class="nav-icon">
                                        <img src="{$c->image|resize:20:20:false:$config->resized_categories_dir}" alt="{$c->name|escape}" />
                                    </span>
                                {/if} *}
                                <span>{$c->name}</span>
                            </a>
                            {categories_four categories=$c->subcategories level=$level + 1}
                        </li>
                    {else}
                        <li class="">
                            <a class="{if $category->id == $c->id} selected{/if}" href="{$lang_link}{$config->catalog_url}{$c->url}" data-category="{$c->id}">
                                {* {if $c->image}
                                    <span class="nav-icon">
                                        <img src="{$c->image|resize:20:20:false:$config->resized_categories_dir}" alt="{$c->name|escape}" />
                                    </span>
                                {/if} *}
                                <span>{$c->name}</span>
                            </a>
                        </li>
                    {/if}
                {/if}
            {/foreach}
        </ul>
    {/if}
{/function}


<ul class="top-nav _mobile-catalog-title">
    <li>
        <div class="mh-catalog-popup-header__title">
            {include file="svg.tpl" svgId="icon_catalog"}
            <span>Каталог товарів</span>
        </div>
        {categories_four categories=$categories level=1}
    </li>
</ul>

<ul class="top-nav">
    <li>
        <div id="cart_informer">
            {include file='cart_informer.tpl'}
        </div>
    </li>
</ul>
<ul class="top-nav">
    <li>
        <div id="wishlist" class="informer">
            {include file="wishlist_informer.tpl"}
        </div>
    </li>
</ul>
<ul>
    <li class="account_item">
        {if $user}
            {* User account *}
            <a class="account_informer" href="{$lang_link}user">{include file="svg.tpl" svgId="account_icon"}<span data-language="user_header">{$lang->user_header}</span></a>
        {else}
            {* Login *}
            <a class="account_informer fn_modal_auth_link" href="#" title="{$lang->index_login}">{include file="svg.tpl" svgId="account_icon"}<span data-language="user_header">{$lang->user_header}</span></a>
        {/if}
    </li>
    {* <li>
        <a class="fn_callback callback btn_black" href="#" data-language="index_back_call">{include file="svg.tpl" svgId="callback_icon"}<span>{$lang->index_back_call}</span></a>
    </li> *}
</ul>

{$menu_mobile}




{* <ul class="contact_ul">
    <li class="contact_li">
        <span class="contact_text" data-language="contact_text">{$lang->contact_text}</span>
    </li>
</ul> *}

<ul class="contact_ul_2">
    <li class="contact_li">
        <a class="contact_mobile_link" href="tel:{preg_replace('~[^0-9\+]~', '',$lang->company_phone_1)}" data-language="company_phone_1">
            {$lang->company_phone_1}
        </a>
        <a class="contact_mobile_link" href="tel:{preg_replace('~[^0-9\+]~', '',$lang->company_phone_2)}" data-language="company_phone_2">
            {$lang->company_phone_2}
        </a>
    </li>
</ul>
<ul class="contact_ul_3">
    <li class="chat_link">
        <a class="contact_link_chat" href="{$lang->chat_telegram_link}" data-language="chat_telegram_link">{include file="svg.tpl" svgId="telegram_icon"}<span data-language="chat_telegram">{$lang->chat_telegram}</span></a>
        <a class="contact_link_chat" href="{$lang->chat_viber_link}" data-language="chat_viber_link">{include file="svg.tpl" svgId="viber_icon"}<span data-language="chat_viber">{$lang->chat_viber}</span></a>
    </li>
</ul>


<ul class="social_link_container">
    <li>
        <a class="social_link" href="https://www.instagram.com/sportfly.com.ua" target="_blank" title="Instagram">{include file="svg.tpl" svgId="inst_icon_black"}</a>
    </li>
    <li>
        <a class="social_link" href="https://www.facebook.com/SportFly.com.ua" target="_blank" title="Facebook">{include file="svg.tpl" svgId="facebook_icon_black"}</a>
    </li>
</ul>
