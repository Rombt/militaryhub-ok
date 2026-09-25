<!DOCTYPE html>
<html {if $language->href_lang}lang="{$language->href_lang|escape}" {/if} prefix="og: http://ogp.me/ns#">

<head>
    {* Full base address *}
    <base href="{$config->root_url}/">

    {* Meta data *}
    {include file="head.tpl"}

    {* Favicon *}
    <link href="design/{$settings->theme}/images/favicon.ico" type="image/x-icon" rel="icon">

    <script>
        ut_tracker.start('parsing:page');
    </script>

    {* Fonts *}
    {* {include file="fonts.tpl"} *}

    {* CSS *}
    <script>
        ut_tracker.start('parsing:head:css');
    </script>
    <link href="design/{$settings->theme|escape}/css/libs.css   {if $css_version}?v={$css_version}{/if}" rel="stylesheet">
    <link href="design/{$settings->theme|escape}/css/style.css   {if $css_version}?v={$css_version}{/if}" rel="stylesheet">
    <link href="design/{$settings->theme|escape}/css/militaryhub.css   {if $css_version}?v={$css_version}  {/if}" rel="stylesheet">
    <link href="design/{$settings->theme|escape}/css/fonts.css   {if $css_version}?v={$css_version}  {/if}" rel="stylesheet">
    <link href="design/{$settings->theme|escape}/css/responsive.css   {if $css_version}?v={$css_version}  {/if}" rel="stylesheet">
    <link href="design/{$settings->theme|escape}/css/media.css   {if $css_version}?v={$css_version}  {/if}" rel="stylesheet">

    {* стили и скрипты из ModulesCore *}
    {$modules_head_css nofilter}
    {$modules_head_js nofilter}

    {if $module == 'CartView'}
        <link href="design/{$settings->theme}/css/select2.min.css" rel="stylesheet">
    {/if}
    <script>
        ut_tracker.end('parsing:head:css');
    </script>
    {if $counters['head']}
        <script>
            ut_tracker.start('parsing:head:counters');
        </script>
        {foreach $counters['head'] as $counter}
            {$counter->code}
         {/foreach}
        <script>
            ut_tracker.end('parsing:head:counters');
        </script>
     {/if}

</head>

<body>

    {if $counters['body_top']}
        <script>
            ut_tracker.start('parsing:body_top:counters');
        </script>
        {foreach $counters['body_top'] as $counter}
            {$counter->code}
         {/foreach}
        <script>
            ut_tracker.end('parsing:body_top:counters');
        </script>
     {/if}

    <header class="header">
        <nav class="top_nav">
            <div class="container">
                <div class="top_nav_wrapper">
                    {$menu_header}
                    <div class="header_social social">
                        {if $menu_messengers}
                            <div class="messengers_buttons">
                                {foreach $menu_items as $item}
                                    {if $item->visible == 1}
                                        <a href="{$item->url|escape}" title="{$item->name|escape}" target="_blank" rel="nofollow noopener" class="messengers_buttons__link">{include file='svg.tpl' svgId="{$item->name|lower}_icon"}</a>
                                     {/if}
                                 {/foreach}
                            </div>
                        {/if}
                        <a class="social_link" href="https://www.instagram.com/sportfly.com.ua" target="_blank" title="Instagram">{include file="svg.tpl" svgId="inst_icon"}</a>
                        <a class="social_link" href="https://www.facebook.com/SportFly.com.ua" target="_blank" title="Facebook">{include file="svg.tpl" svgId="facebook_icon"}</a>
                    </div>
                </div>
            </div>
        </nav>
        <div class="header_center">
            <div class="container">
                <div class="header_center_content">
                    <div class="header_left_wrapper">
                        {* Mobile menu button*}
                        <div class="fn_menu_switch menu_switcher hidden">
                            <div class="menu_switcher__heading d-flex align-items-center">

                                {* <span class="" data-language="index_mobile_menu">{$lang->index_mobile_menu}</span> *}
                            </div>
                        </div>
                        {* Logo *}
                        <a class="logo" href="{if $smarty.get.module=='MainView'}javascript:;{else}{$lang_link}{/if}">
                            <img src="design/{$settings->theme|escape}/images/logo.svg" alt="{$settings->site_name|escape}">
                        </a>
                        {*Если вам нужно загружать разные логотипы на разных языках, закомментируйте код выше, и пользуйтесь кодом ниже*}
                        {*<a class="logo" href="{$lang_link}">
                            <img src="design/{$settings->theme|escape}/images/logo   {if $language->label}_{$language->label} {/if}.png" alt="{$settings->site_name|escape}"/>
                        </a>*}

                        <button type="button" class="rmbt-mh-catalog-button" data-rmbt-popup-target="catalog-popup-header" data-rmbt-popup-close="catalog-popup-header">

                            <div class="rmbt-mh-catalog-button__icons-wrap">
                                <i id='rmbt-mh-catalog-button-open' class="catalog_icon">{include file="svg.tpl" svgId="icon_catalog"}</i>
                                <i id='rmbt-mh-catalog-button-close' class="catalog_icon">{include file="svg.tpl" svgId="icon_close"}</i>

                            </div>

                            Каталог товарів
                        </button>

                        <div class="header_second_menu">
                            {$menu_second}      {* !!! проблема - появляется горизонтальный скролл *}
                            {* {if !$smarty.session.admin}
                                {include file='desktop_categories.tpl'}
                             {/if} *}
                        </div>
                    </div>


                    {* Top info block *}
                    <div class="informers">
                        <div class="fn_search_open search_open_button">{include file="svg.tpl" svgId="search_icon"}</div>
                        <div class="informer account_informer_wrapper">
                            {if $user}
                                {* User account *}
                                <a class="account_informer" href="{$lang_link}user">{include file="svg.tpl" svgId="account_icon"}</a>
                            {else}
                                {* Login *}
                                <button class="account_informer fn_modal_auth" title="{$lang->index_login}" type="button">{include file="svg.tpl" svgId="account_icon"}</button>
                             {/if}
                        </div>
                        {* Wishlist informer *}
                        <div id="wishlist" class="informer">
                            {include file="wishlist_informer.tpl"}
                        </div>
                        {* Cart informer*}
                        <div id="cart_informer">
                            {include file='cart_informer.tpl'}
                        </div>
                        {* Callback *}
                        {* <a class="fn_callback callback" href="#fn_callback" data-language="index_back_call">{include file="svg.tpl" svgId="callback_icon"}
                            <span>{$lang->index_back_call}</span></a> *}
                    </div>
                </div>
            </div>
        </div>
    </header>

    {* Тело сайта *}
    <div id="fn_content" class="main{if $module == "MainView"} main_page{elseif $module == "FeedbackView"} feedback_page{/if}">
        {* Banners *}
        {get_banner var="banner_main" group="main"}
        {if $banner_main->items && $module == "MainView"}
            <div class="fn_banner_main swiper">
                <div class="swiper-wrapper">
                    {foreach $banner_main->items as $bi}
                        <div class="swiper-slide">
                            {if $bi->url}
                                <a class='banner_main_link' href="{$bi->url}">
                             {/if}
                            {if $bi->image}
                                <picture>
                                    {* mobile webp *}
                                    <source media="(max-width: 480px)" type="image/webp" {if $bi@first} srcset="{$bi->image|resize:500:200:false:$config->resized_banners_images_dir:null:null:true}" {else} data-srcset="{$bi->image|resize:500:200:false:$config->resized_banners_images_dir:null:null:true}" srcset="{$rootUrl}/design/{$settings->theme|escape}/images/xloading.gif"  {/if}>

                                    {* desktop webp *}
                                    <source type="image/webp" {if $bi@first} srcset="{$bi->image|resize:1920:950:false:$config->resized_banners_images_dir:null:null:true}" {else} data-srcset="{$bi->image|resize:1920:950:false:$config->resized_banners_images_dir:null:null:true}" srcset="{$rootUrl}/design/{$settings->theme|escape}/images/xloading.gif"  {/if}>

                                    {* mobile jpeg *}
                                    <source media="(max-width: 480px)" type="image/jpeg" {if $bi@first} srcset="{$bi->image|resize:500:200:false:$config->resized_banners_images_dir}" {else} data-srcset="{$bi->image|resize:500:200:false:$config->resized_banners_images_dir}" srcset="{$rootUrl}/design/{$settings->theme|escape}/images/xloading.gif"  {/if}>

                                    {* desktop jpeg *}
                                    <source type="image/jpeg" {if $bi@first} srcset="{$bi->image|resize:1920:950:false:$config->resized_banners_images_dir}" {else} data-srcset="{$bi->image|resize:1920:950:false:$config->resized_banners_images_dir}" srcset="{$rootUrl}/design/{$settings->theme|escape}/images/xloading.gif"  {/if}>

                                    {* fallback img *}
                                    <img {if $bi@first} src="{$bi->image|resize:1920:950:false:$config->resized_banners_images_dir}" fetchpriority="high" {else} class="lazy lazy-bg" data-src="{$bi->image|resize:1920:950:false:$config->resized_banners_images_dir}" src="{$rootUrl}/design/{$settings->theme|escape}/images/xloading.gif" {* loading="lazy" *}  {/if}
                                    alt="{$bi->alt}" title="{$bi->title}">
                                </picture>
                             {/if}
                            {if $bi->url}
                                </a>
                            {/if}
                        </div>
                     {/foreach}
                </div>
                <div class="swiper-button-next"></div>
                <div class="swiper-button-prev"></div>
            </div>
         {/if}
        {if $module == "MainView" || $page->url == '404'}
            <div class="fn_ajax_content">
                {$content}
            </div>
        {else}
            <div class="container">
                {include file='breadcrumb.tpl'}
                <div class="fn_ajax_content">
                    {$content}
                </div>
            </div>
         {/if}
    </div>

    <div class="to_top"></div>

    {* Footer *}
    <footer class="footer">
        <div class="footer_bottom">
            <div class="container">
                <div class="row foot_container rmbt-foot_container">
                    <div class="foot rmbt-foot-left-column col-sm-6 col-lg-3">
                        {* Logo *}
                        <a class="logo" href="{if $smarty.get.module=='MainView'}javascript:;{else}{$lang_link}{/if}">
                            <img class="lazy lazy-bg" data-src="design/{$settings->theme|escape}/images/logo-footer.svg" src="{$rootUrl}/design/{$settings->theme|escape}/images/xloading.gif" {* loading="lazy" *} alt="{$settings->site_name|escape}">
                        </a>
                        {* Social buttons *}
                        <div class="foot_social_wrapper">
                            <span class="foot_title" data-language="index_in_networks">{$lang->index_in_networks}</span>
                            <div class="social">
                                <a class="social_link" href="https://www.instagram.com/sportfly.com.ua" title="Instagram">{include file="svg.tpl" svgId="inst_icon"}</a>
                                <a class="social_link" href="https://www.facebook.com/SportFly.com.ua" target="_blank" title="Facebook">{include file="svg.tpl" svgId="fb_icon"}</a>
                            </div>
                        </div>

                        <div class="foot_payments_wrapper">
                            <span class="foot_title" data-language="index_payments">{$lang->index_payments}</span>
                            <div class="payments_wrapper">
                                {include file="svg.tpl" svgId="g_pay_icon"}
                                {include file="svg.tpl" svgId="apple_pay_icon"}
                                {include file="svg.tpl" svgId="visa_icon"}
                                {include file="svg.tpl" svgId="mastercard_icon"}
                            </div>
                        </div>

                    </div>

                    <div class="foot rmbt-foot-right-column col-sm-6 col-lg-9">
                        <div class="subscribe_wrapper rmbt-subscribe_wrapper">
                            {* Subscribing *}
                            <div id="subscribe_container">
                                <span class="subscribe_text" data-language="subscribe_text">{$lang->subscribe_text}</span>
                                <form class="subscribe_form fn_validate_subscribe" method="post">
                                    <input type="hidden" name="subscribe" value="1">
                                    <input class="subscribe_input" type="email" name="subscribe_email" value="" data-format="email" placeholder="{$lang->form_email}">
                                    <button class="subscribe_button" type="submit"><span data-language="subscribe_button">{$lang->subscribe_button}</span></button>
                                    {if $subscribe_error}
                                        <div id="subscribe_error" class="popup">
                                            {if $subscribe_error == 'email_exist'}
                                                <span data-language="subscribe_already">{$lang->index_subscribe_already}</span>
                                             {/if}
                                            {if $subscribe_error == 'empty_email'}
                                                <span data-language="form_enter_email">{$lang->form_enter_email}</span>
                                             {/if}
                                        </div>
                                    {/if}
                                    {if $subscribe_success}
                                        <div id="fn_subscribe_sent" class="popup">
                                            <span data-language="subscribe_sent">{$lang->index_subscribe_sent}</span>
                                        </div>
                                    {/if}
                                </form>
                            </div>
                        </div>
                        {* Main menu *}
                        <div class="foot rmbt-foot-right-column-item foot_line col-sm-6 col-lg-2">
                            <div class="foot_heading fn_switch">
                                <span data-language="index_about_store">{$lang->index_about_store}</span>
                                <span class="footer__title_arrow">{include file="svg.tpl" svgId="arrow_right"}</span>
                            </div>
                            <div class="foot_menu">
                                {$menu_footer}
                            </div>
                        </div>

                        {* Categories menu *}
                        <div class="foot rmbt-foot-right-column-item foot_line col-sm-6 col-lg-3">
                            <div class="foot_heading fn_switch">
                                <span data-language="index_categories">{$lang->index_categories}</span>
                                <span class="footer__title_arrow">{include file="svg.tpl" svgId="arrow_right"}</span>
                            </div>
                            <div class="foot_menu">
                                {foreach $categories as $c}
                                    {if $c->visible}
                                        <div class="foot_item">
                                            <a href="{$lang_link}catalog/{$c->url}">{$c->name|escape}</a>
                                        </div>
                                    {/if}
                                {/foreach}
                            </div>
                        </div>

                        {* Main menu *}
                        <div class="foot rmbt-foot-right-column-item foot_line col-sm-6 col-lg-2">
                            <div class="foot_heading fn_switch">
                                <span data-language="index_about_store_2">{$lang->index_about_store_2}</span>
                                <span class="footer__title_arrow">{include file="svg.tpl" svgId="arrow_right"}</span>
                            </div>
                            <div class="foot_menu">
                                {$menu_footer_2}
                            </div>
                        </div>

                        {* Contacts *}
                        <div class="foot rmbt-foot-right-column-item foot_line foot_contacts col-sm-6 col-lg-4">
                            <div class="foot_heading">
                                <span data-language="index_contacts">{$lang->index_contacts}</span>
                            </div>
                            <div class="footer_contacts">
                                <div class="foot_items">
                                    {include file="svg.tpl" svgId="phone_icon"}
                                    <div class="foot_item">
                                        <a href="tel:{preg_replace('~[^0-9\+]~', '',$lang->company_phone_1)}" data-language="company_phone_1">{$lang->company_phone_1}</a>
                                        <a href="tel:{preg_replace('~[^0-9\+]~', '',$lang->company_phone_2)}" data-language="company_phone_2">{$lang->company_phone_2}</a>
                                    </div>
                                </div>
                                <div class="foot_item">
                                    {include file="svg.tpl" svgId="email_icon"}
                                    <a href="mailto:{$lang->company_email}"><span data-language="company_email">{$lang->company_email}</span></a>
                                </div>
                                <div class="foot_item">
                                    {include file="svg.tpl" svgId="address_icon"}
                                    <span data-language="address_company">{$lang->address_company}</span>
                                </div>
                            </div>
                            <div class="foot_payments_wrapper" style="display:none;">
                                <span class="foot_title" data-language="index_payments">{$lang->index_payments}</span>
                                <div class="payments_wrapper">
                                    {include file="svg.tpl" svgId="g_pay_icon"}
                                    {include file="svg.tpl" svgId="apple_pay_icon"}
                                    {include file="svg.tpl" svgId="visa_icon"}
                                    {include file="svg.tpl" svgId="mastercard_icon"}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {* Copyright *}
                <div class="copyright">
                    <span data-language="index_copyright">© {$lang->index_copyright} 2019 - {$smarty.now|date_format:"%Y"}</span>
                </div>
            </div>
        </div>
    </footer>

    <div class="search" style="display: none;">
        <div class="fn_search_close search_close pointer">
            {include file="svg.tpl" svgId="search_close_icon"}
        </div>
        <div class="search_container">
            {* <div class="search_text" data-language="search_text">{$lang->search_text}</div> *}
            <div class="search_text_mobile" data-language="products_search">{$lang->products_search}</div>
            <form id="fn_search" class="search_form" action="{$lang_link}all-products">
                <input class="fn_search search_input" type="text" name="keyword" value="{$keyword|escape}" aria-label="search" data-language="index_search" placeholder="{$lang->index_search|escape}">
                <button class="search_button" aria-label="search" type="submit">{include file="svg.tpl" svgId="search_icon"}</button>
            </form>
        </div>
    </div>


    {* Форма обратного звонка *}
    {include file='callback.tpl'}

    {* Попап добавления в корзину*}
    {include file='cart_popup.tpl'}

    {* Попап "вхід/реєстрація" користувача*}
    {include file='user_auth_modal.tpl'}

    <div class="fn_mobile_menu hidden">
        {include file="mobile_menu.tpl"}
    </div>

    <script>
        ut_tracker.start('parsing:body_bottom:css');
    </script>
    {if $smarty.get.module == 'ProductView' || $smarty.get.module == "BlogView"}
        <link href="design/{$settings->theme|escape}/css/font-awesome.min.css   {if $css_version}?v={$css_version}
    {/if}"
        rel="stylesheet">
    <link href="design/{$settings->theme|escape}/css/jssocials.css   {if $css_version}?v={$css_version}{/if}"
        rel="stylesheet">
    <link href="design/{$settings->theme|escape}/css/jssocials-theme-flat.css   {if $css_version}?v={$css_version}{/if}"
        rel="stylesheet">
    {/if}
    <script>
        ut_tracker.end('parsing:body_bottom:css');
    </script>

    <link href="design/{$settings->theme|escape}/css/mobile_menu.css   {if $css_version}?v={$css_version}{/if}" rel="stylesheet">

    {*template scripts*}
    {* JQuery UI *}
    {* Библиотека с "Slider", "Transfer Effect" *}
    <script>
        ut_tracker.start('parsing:body_bottom:js');
    </script>
    <script src="design/{$settings->theme}/js/jquery-3.3.1.min.js   {if $js_version} ? v = { $js_version } {/if}">
    </script>

    {* JQuery migrate*}
    {if $module == "ProductsView"}
        <script src="design/{$settings->theme}/js/jquery-migrate-3.0.1.min.js   {if $js_version} ? v = { $js_version } {/if}">
        </script>
    {/if}
    {* Swiper slider *}
    <script src="design/{$settings->theme}/js/swiper-bundle.min.js   {if $js_version} ? v = { $js_version } {/if}">
    </script>
    <script src="design/{$settings->theme}/js/lazyload.min.js   {if $js_version} ? v = { $js_version } {/if}">
    </script>
    <script src="design/{$settings->theme}/js/mobile_menu.js   {if $js_version} ? v = { $js_version } {/if}" defer>
    </script>
    <script src="design/{$settings->theme}/js/jquery-ui.min.js   {if $js_version} ? v = { $js_version }  {/if}">
    </script>

    {* Библиотека touch-punch *}
    <script src="design/{$settings->theme}/js/ui.touch-punch.min.js   {if $js_version} ? v = { $js_version } {/if}">
    </script>

    {* Fancybox *}
    <link href="design/{$settings->theme|escape}/css/jquery.fancybox.min.css   {if $css_version}?v={$css_version}{/if}" rel="stylesheet">
    {*
    <link href="design/{$settings->theme|escape}/css/fancybox.css   {if $css_version}?v={$css_version}{/if}"
    rel="stylesheet"> *}

    <script src="design/{$settings->theme|escape}/js/jquery.fancybox.min.js   {if $js_version}
        ? v = { $js_version } {/if}" defer>
    </script>
    {* <script src="design/{$settings->theme|escape}/js/fancybox.umd.js   {if $js_version}
        ? v = { $js_version } {/if}">
    </script> *}

    {if $smarty.get.module == 'CartView'}
        <script src="design/{$settings->theme}/js/select2.full.min.js   {if $js_version} ? v = { $js_version } {/if}" defer>
        </script>
        <script src="design/{$settings->theme}/js/i18n/{$language->href_lang}.js   {if $js_version} ? v = { $js_version } {/if}" defer>
        </script>
        <script src="design/{$settings->theme}/js/cart.js   {if $js_version} ? v = { $js_version } {/if}" defer>
        </script>
     {/if}

    {* Autocomplete *}
    <script src="design/{$settings->theme}/js/jquery.autocomplete-min.js   {if $js_version} ? v = { $js_version }  {/if}" defer>
    </script>

    {$admintooltip}

    {* JQuery Validation *}
    <script src="design/{$settings->theme}/js/jquery.validate.min.js   {if $js_version} ? v = { $js_version }  {/if}">
    </script>
    <script src="design/{$settings->theme}/js/additional-methods.min.js   {if $js_version} ? v = { $js_version }  {/if}" defer>
    </script>

    {* //!! *}
    {* стили и скрипты модулей из ModulesCore *}
    {$modules_footer_css nofilter}
    {$modules_footer_js nofilter}



    {* Social share buttons *}
    {if $smarty.get.module == 'ProductView' || $smarty.get.module == "BlogView"}
        <script src="design/{$settings->theme|escape}/js/modernizr-custom.js   {if $js_version} ? v = { $js_version }  {/if}">
        </script>
        <script src="design/{$settings->theme|escape}/js/jssocials.min.js   {if $js_version} ? v = { $js_version }  {/if}">
        </script>
    {/if}

    {* Okay *}
    {include file="scripts.tpl"}
    <script src="design/{$settings->theme}/js/okay.js   {if $js_version} ? v = { $js_version } {/if}">
    </script>
    <script src="design/{$settings->theme}/js/analytics.js   {if $js_version} ? v = { $js_version } {/if}" defer>
    </script>
    {*template scripts*}
    <script>
        ut_tracker.end('parsing:body_bottom:js');
    </script>

    {* Автоматичне відкриття попапу входу/реєстрації для промо-джерела *}
    {if $show_promo_register_popup}
        <script>
            (function() {
                function openPromoAuthPopup() {
                    var btn = document.querySelector('.account_informer.fn_modal_auth');
                    if (btn && typeof jQuery !== 'undefined') {
                        jQuery(btn).trigger('click');
                    } else if (btn) {
                        btn.click();
                    }
                }

                if (document.readyState === 'complete' || document.readyState === 'interactive') {
                    setTimeout(openPromoAuthPopup, 300);
                } else {
                    document.addEventListener('DOMContentLoaded', function() {
                        setTimeout(openPromoAuthPopup, 300);
                    });
                }
            })();
        </script>
    {/if}

    {if $counters['body_bottom']}
        <script>
            ut_tracker.start('parsing:body_bottom:counters');
        </script>
        {foreach $counters['body_bottom'] as $counter}
            {$counter->code}
        {/foreach}
        <script>
            ut_tracker.end('parsing:body_bottom:counters');
        </script>
    {/if}

</body>

</html>
