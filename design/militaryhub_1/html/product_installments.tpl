{* Submit button *}
<div class="fn_show_installment button installment_btn {if $product->variant->compare_price && ((($product->variant->compare_price - $product->variant->price) * 100)/$product->variant->compare_price) > 10}hidden{/if}" data-language="product_installment">{include file="svg.tpl" svgId="installment_icon"}{$lang->product_installment}</div>

<div class="hidden">
    <div id="fn_installment_form" class="installment_form">
        <div class="installment_container">
            <div class="h3"><span data-language="calculate_payments">{$lang->calculate_payments}</span></div>
            <div class="installment_flex" data-price="{$product->variant->price|convert}">
                <div class="installment_header">
                    <div class="installment_header_left">
                        <div class="installment_header_icon">{include file="svg.tpl" svgId="installment_icon"}</div>
                        <div class="installment_header_title">
                            <div class="installment_title" data-language="product_installment">{$lang->product_installment}</div>
                            <div class="installment_subtitle" data-language="installment_subtitle">{$lang->installment_subtitle}</div>
                        </div>
                    </div>
                    <div class="installment_header_right">
                        <div class="installment_header_value"><span class="fn_monthly_payment">{(($product->variant->price)/2)|convert}</span> {$currency->sign|escape}</div>
                        <div class="installment_header_month" data-language="month_ttl">x <span class="fn_month_num">2</span> {$lang->month_ttl}</div>
                    </div>
                </div>
                <div class="installment_variant">
                    <label class="input-installment" for="installment_2">
                        <input type="radio" id="installment_2" name="payments_num" value="2" checked >
                        <span class="installment_tab" data-language="installment_2">{$lang->installment_2}</span>
                    </label>
                    <label class="input-installment" for="installment_3">
                        <input class="entityBtn" type="radio" id="installment_3" name="payments_num" value="3">
                        <span class="installment_tab" data-language="installment_3">{$lang->installment_3}</span>
                    </label>
                    <label class="input-installment" for="installment_4">
                        <input type="radio" id="installment_4" name="payments_num" value="4">
                        <span class="installment_tab" data-language="installment_4">{$lang->installment_4}</span>
                    </label>
                    <label class="input-installment" for="installment_5">
                        <input type="radio" id="installment_5" name="payments_num" value="5">
                        <span class="installment_tab" data-language="installment_5">{$lang->installment_5}</span>
                    </label>
                    <label class="input-installment" for="installment_6">
                        <input type="radio" id="installment_6" name="payments_num" value="6">
                        <span class="installment_tab" data-language="installment_6">{$lang->installment_6}</span>
                    </label>
                </div>
                <div class="installment_values">
                    <div class="installment_value_item">
                        <div class="installment_value_title" data-language="credit_amount">{$lang->credit_amount}</div>
                        <div class="installment_value_value">{$product->variant->price|convert} {$currency->sign|escape}</div>
                    </div>
                    <div class="installment_value_item">
                        <div class="installment_value_title" data-language="num_of_payments">{$lang->num_of_payments}</div>
                        <div class="installment_value_value fn_month_num">2</div>
                    </div>
                    <div class="installment_value_item">
                        <div class="installment_value_title" data-language="monthly_payment">{$lang->monthly_payment}</div>
                        <div class="installment_value_value"><span class="fn_monthly_payment">{(($product->variant->price)/2)|convert}</span> {$currency->sign|escape}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>