{* Payment form *}
<form class="form" action="{$ipn_url|escape}" method="{$ipn_method|escape}">
    {if $error}
        <div class="message_error">
            {if $error == 'empty_parts_count'}
                <span data-language="form_enter_parts_count">{$lang->form_enter_parts_count}</span>
            {else}
                <span>{$error}</span>
            {/if}
        </div>
    {/if}
    {if $ipn_method == 'post'}
        <div class="form__body">
            {* User's parts count *}
            <div class="form__group">
                <select class="form__input form__placeholder--focus" name="payment_details[parts_count]" required>
                    <option value=""{if !$smarty.post && !$parts_count} selected{/if} disabled>{$lang->form_enter_parts_count}</option>
                    {$value = $ipn_parts_count.min}
                    {while $value <= $ipn_parts_count.max}
                        <option value="{$value|escape}"{if ($smarty.post && $smarty.post.payment_details.parts_count == $value) || (!$smarty.post && $parts_count == $value)} selected{/if}>{$value} {$value|plural:$lang->part_count:$lang->parts_counts:$lang->part_counts}</option>
                        {$value = $value + 1}
                    {/while}
                </select>
                <span class="form__placeholder" data-language="receipt_parts_count">{$lang->receipt_parts_count} *</span>
            </div>
        </div>
    {else}
        {foreach $ipn_params as $name => $value}
            <input type="hidden" name="{$name|escape}" value="{$value|escape}" />
        {/foreach}
    {/if}
    <input type="submit" class="button" value="{$lang->form_to_pay|escape}" />
</form>