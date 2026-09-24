{* Payment methods *}
{foreach $deliveries as $delivery}
    {if $delivery->payment_methods}
        <div class="fn_delivery_payment" id="fn_delivery_payment_{$delivery->id}"{if $delivery@iteration != 1} style="display:none"{/if}>
            <div class="delivery payment_wrapper">
                {foreach $delivery->payment_methods as $payment_method}
                    <div class="delivery_item">
                        <label class="delivery_label{if $payment_method@first} active{/if}" for="payment_{$delivery->id}_{$payment_method->id}">
                            <i class="filter_indicator"></i>
                            <input class="input_delivery" id="payment_{$delivery->id}_{$payment_method->id}" type="radio" name="payment_method_id" value="{$payment_method->id}"{if $delivery@first && $payment_method@first} checked{/if} />
                            <span class="delivery_name">
                                {* {if $payment_method->image}
                                    <img src="{$payment_method->image|resize:50:50:false:$config->resized_payments_dir}" />
                                {/if} *}
                                {$total_price_with_delivery = $cart->total_price}
                                {if !$delivery->separate_payment && $cart->total_price < $delivery->free_from}
                                    {$total_price_with_delivery = $cart->total_price + $delivery->price}
                                {/if}
                                {$payment_method->name|escape}
                                {if $payment_method->advance_payment > 0}
                                    ({$lang->payment_info_advance}
                                    {if $cart->total_price > $payment_method->advance_sum_delimiter}
                                        {$payment_method->advance_payment_2|convert}
                                    {else}
                                        {$payment_method->advance_payment|convert}
                                    {/if}
                                    {$currency->sign|escape})
                                {/if}
                                {* {$lang->cart_deliveries_to_pay} *}
                                {* <span class="nowrap">{$total_price_with_delivery|convert:$payment_method->currency_id} {$all_currencies[$payment_method->currency_id]->sign|escape}</span> *}
                            </span>
                        </label>
                        <div class="delivery_description">
                            {$payment_method->description}
                        </div>
                    </div>
                {/foreach}
            </div>
        </div>
    {/if}
{/foreach}
