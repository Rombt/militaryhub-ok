{$meta_title = $btr->settings_products_feed|default:'Products feed settings' scope=parent}

<div class="row">
    <div class="col-lg-6 col-md-6">
        <div class="heading_page">{$btr->settings_products_feed|escape|default:'Products feed settings'}</div>
    </div>
    <div class="col-lg-4 col-md-3 text-xs-right float-xs-right"></div>
</div>

{if $message_success}
    <div class="row">
        <div class="col-lg-12 col-md-12 col-sm-12">
            <div class="boxed boxed_success">
                <div class="heading_box">
                    {if $message_success == 'saved'}
                        {$btr->settings_catalog_catalog|escape}
                    {/if}
                    {if $smarty.get.return}
                        <a class="btn btn_return float-xs-right" href="{$smarty.get.return}">
                            {include file='svg_icon.tpl' svgId='return'}
                            <span>{$btr->general_back|escape}</span>
                        </a>
                    {/if}
                </div>
            </div>
        </div>
    </div>
{/if}

<form method="post" enctype="multipart/form-data">
    <input type=hidden name="session_id" value="{$smarty.session.id}">

    <div class="row">
        <div class="col-lg-12 col-md-12">
            <div class="boxed fn_toggle_wrap ">
                <div class="heading_box">
                    {$btr->settings_feed_rozetka_prom|escape|default:'Prom and Rozetka relations'}
                    <div class="toggle_arrow_wrap fn_toggle_card text-primary">
                        <a class="btn-minimize" href="javascript:;" ><i class="fa fn_icon_arrow fa-angle-down"></i></a>
                    </div>
                </div>
                {*Параметры элемента*}
                <div class="toggle_body_wrap on fn_card">
                    <div class="row">
                        <div class="col-lg-12">
                            <div class="radio">
                                <label>
                                    <input type="radio" name="feed_prom_rozetka_relations" value="from" {if $settings->feed_prom_rozetka_relations == 'from'}checked{/if}>
                                    {$btr->settings_feed_rozetka_prom_from|escape|default:'From Prom to Rozetka'}
                                </label>
                            </div>
                            <div class="radio">
                                <label>
                                    <input type="radio" name="feed_prom_rozetka_relations" value="equal" {if $settings->feed_prom_rozetka_relations == 'equal'}checked{/if}>
                                    {$btr->settings_feed_rozetka_prom_equal|escape|default:'Equal for Prom and Rozetka'}
                                </label>
                            </div>
                            <div class="radio">
{*                                <label>*}
{*                                    <input type="radio" name="feed_prom_rozetka_relations" value="to" {if $settings->feed_prom_rozetka_relations == 'to'}checked{/if}>*}
{*                                    {$btr->settings_feed_rozetka_prom_to|escape|default:'To Prom from Rozetka'}*}
{*                                </label>*}
{*                            </div>*}
                            <div class="radio">
                                <label>
                                    <input type="radio" name="feed_prom_rozetka_relations" value="" {if !$settings->feed_prom_rozetka_relations}checked{/if}>
                                    {$btr->settings_feed_rozetka_prom_not|escape|default:'Don\'t mix'}
                                </label>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-lg-12 col-md-12 mt-1">
                            <button type="submit" class="btn btn_small btn_blue float-md-right">
                                {include file='svg_icon.tpl' svgId='checked'}
                                <span>{$btr->general_apply|escape}</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>