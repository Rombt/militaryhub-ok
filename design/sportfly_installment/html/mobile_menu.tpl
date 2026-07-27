<ul class="top-nav">
    <li>
        <div class="">
            {if !empty({$settings->site_logo})}
                <a class="mobile__link" href="{if $smarty.get.module == 'MainView'}javascript:;{else}{str_replace('/', '', $lang_link)}{/if}">
                    <img  class="lazy lazy-bg" data-src="design/{$settings->theme|escape}/images/mobile-logo.svg" src="{$rootUrl}/design/{$settings->theme|escape}/images/xloading.gif" alt="{$settings->site_name|escape}" loading="lazy">
                    <span class="mobile_menu_close">{include file='svg.tpl' svgId='remove_icon'}</span>
                </a>
            {/if}
        </div>
    </li>
</ul>
<ul><li class="mobile_menu_sub_header"><span data-language="mobile_menu_category">{$lang->mobile_menu_category}</span></li></ul>

{* {function name=categories_four}
{if $categories}
    <ul class="">
        {foreach $categories as $c}
            {if $c->visible}
                {if $c->subcategories && $c->count_children_visible}
                    <li class="">
                        <a class="{if $category->id == $c->id} selected{/if}" href="{$lang_link}{$config->catalog_url}{$c->url}" data-category="{$c->id}">
                            {if $c->image}
                                <span class="nav-icon">
                                    <img src="{$c->image|resize:20:20:false:$config->resized_categories_dir}" alt="{$c->name|escape}" />
                                </span>
                            {/if}
                            <span>{$c->name}</span>
                        </a>
                        {categories_four categories=$c->subcategories level=$level + 1}
                    </li>
                {else}
                    <li class="">
                        <a class="{if $category->id == $c->id} selected{/if}" href="{$lang_link}{$config->catalog_url}{$c->url}" data-category="{$c->id}">
                            {if $c->image}
                                <span class="nav-icon">
                                    <img src="{$c->image|resize:20:20:false:$config->resized_categories_dir}" alt="{$c->name|escape}" />
                                </span>
                            {/if}
                            <span>{$c->name}</span>
                        </a>
                    </li>
                {/if}
            {/if}
        {/foreach}
    </ul>
{/if}
{/function}
{categories_four categories=$categories level=1} *}
{* {if $smarty.session.admin} *}
	{*old $menu_categories*}
    {$menu_mobile_categories}
{* {/if} *}

<ul><li class="mobile_menu_sub_header"><span data-language="mobile_menu_header">{$lang->mobile_menu_header}</span></li></ul>
{$menu_mobile}
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
    <li>
        <a class="fn_callback callback btn_black" href="#" data-language="index_back_call">{include file="svg.tpl" svgId="callback_icon"}<span>{$lang->index_back_call}</span></a>
    </li>
</ul>

<ul class="contact_ul">
    <li class="contact_li">
        <span class="contact_text" data-language="contact_text">{$lang->contact_text}</span>
    </li>
</ul>
 <ul class="contact_ul_2">
    <li class="contact_li">
        <a class="contact_mobile_link" href="tel:{preg_replace('~[^0-9\+]~', '',$lang->company_phone_1)}" data-language="company_phone_1" >{include file="svg.tpl" svgId="phone_icon"}{$lang->company_phone_1}</a>
        <a class="contact_mobile_link" href="tel:{preg_replace('~[^0-9\+]~', '',$lang->company_phone_2)}" data-language="company_phone_2" >{$lang->company_phone_2}</a>
    </li>
</ul>
<ul class="contact_ul_3">
    <li class="chat_link">
        <a class="contact_link_chat" href="{$lang->chat_telegram_link}" data-language="chat_telegram_link">{include file="svg.tpl" svgId="telegram_icon"}<span data-language="chat_telegram">{$lang->chat_telegram}</span></a>
        <a class="contact_link_chat" href="{$lang->chat_viber_link}" data-language="chat_viber_link">{include file="svg.tpl" svgId="viber_icon"}<span data-language="chat_viber">{$lang->chat_viber}</span></a>
    </li>
</ul>

{$menu_header}