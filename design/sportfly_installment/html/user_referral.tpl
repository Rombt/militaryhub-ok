{* Реферал пользователя *}
<div class="flex_row-jb">
    <span class="referral_url fn_url" data-url="{$config->root_url}/{$lang_link}r/{$referral->code}">
        {include file='svg.tpl' svgId='copy'}{$referral->name}
        <span class="referral_success" style="display: none" data-language="referral_success">{$lang->referral_success}</span>
    </span>
    <span>
        {$lang->referral_condit_ttl|escape} +{$referral->value*1}{if $referral->type == 'absolute'}{$currency->sign|escape}{else}%{/if}
        <br/>
        {$lang->referral_condit_referrer_ttl|escape}+{$referral->value_referrer*1}{if $referral->type == 'absolute'}{$currency->sign|escape}{else}%{/if}
        <br/>
        {if $referral->single}{$lang->user_referral_infinity_no}{else}{$lang->user_referral_infinity}{/if}
    </span>
    <span>{$lang->referral_pushed_bonuses_ttl|escape} {$referral->bonuses|round} {$referral->bonuses|plural:$lang->order_bonus:$lang->order_bonuses:$lang->order_of_bonuses}</span>
    <span>
        {if $referral->valid}
            {if $referral->expire}
                {if $smarty.now|date_format:'%Y%m%d' <= $referral->expire|date_format:'%Y%m%d'}
                    {$lang->referrals_valid_until|escape} {$referral->expire|date}
                {else}
                    {$lang->referrals_expired|escape} {$referral->expire|date}
                {/if}
            {else}
                {$lang->referrals_infinity|escape}
            {/if}
        {else}
            {if $referral->usages > 0}
                {$lang->referrals_usages|escape} {$referral->usages|intval}
            {else}
                {$lang->referrals_expired|escape} {$referral->expire|date}
            {/if}
        {/if}
    </span>
</div>