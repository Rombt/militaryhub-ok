{if $deliveries}
<div class="delivery_wrapper">
    {* Delivery *}
    <div class="cart_delivery_title">
        <span data-language="cart_delivery">{$lang->cart_delivery}</span>
    </div>
    <div class="delivery">
        {foreach $deliveries as $delivery}
            <div class="delivery_item">
                <label class="delivery_label{if $delivery@first} active{/if}" for="deliveries_{$delivery->id}">
                    <i class="filter_indicator"></i>
                    <input class="input_delivery" id="deliveries_{$delivery->id}"
                           onclick="change_payment_method({$delivery->id})" type="radio"
                           name="delivery_id"
                           data-method="{$delivery->method}"
                       value="{$delivery->id}" {if $delivery_id==$delivery->id || $delivery@first} checked{/if} />
                    <span class="delivery_name">{$delivery->name|escape}</span>
                    {if $delivery->id != 2}
                        <a class="delivery_tarif" href="{if $delivery->id == 3}https://novaposhta.ua/privatnim_klientam/ceny_i_tarify{elseif $delivery->id == 4}https://justin.ua/taryfy{/if}" rel="nofollow" target="_blank"><span data-language="look_rates">{$lang->look_rates}</span></a>
                    {/if}
                </label>
                {if $delivery->description}
                    <div class="delivery_description">
                        {$delivery->description}
                    </div>
                {/if}

                {if $delivery->method == 'novaposhta'}
                    <div class="row novaposhta_delivery"{if !$novaposhta->city && !$novaposhta->ware && $f_delivery->method != 'novaposhta' && $delivery@iteration > 1} style="display: none"{/if}>
                        <div class="m_btm-20px form-group col-md-12 delivery-item">
                            <label class="pull-xs-left delivery-text"><span
                                        data-language="novaposhta_city">{$lang->novaposhta_city} <span class="required">*</span></span></label>
                            <select class="form-control novaposhta_city cart-select" name="novaposhta_city"
                                    id="novaposhta_city" data-live-search="true" data-method="{$delivery->method}"
                                    data-type="1">
                                {if $novaposhta_address}
                                    <option value="{$novaposhta_address->CityRef|escape}"
                                            selected>{$novaposhta_address->CityDescription|escape}</option>{else}
                                    <option value="" disabled selected
                                            data-language="novaposhta_ware">{$lang->novaposhta_form_city|escape}</option>{/if}
                            </select>
                        </div>
                        <div class="form-group col-md-12 delivery-flex">
                            {* <div data-language="novaposhta_type">{$lang->novaposhta_type}Спосіб доставки <span class="required">*</span></div> *}

                            <label class="delivery_label novaposhta_type_input{if $novaposhta->type=='WarehouseWarehouse' || !$novaposhta->type} active{/if}" for="types_WarehouseWarehouse">
                                <i class="filter_indicator"></i>

                                <input class="input_delivery" id="types_WarehouseWarehouse"
                                    onclick="change_novaposhta_method('WarehouseWarehouse')"
                                    type="radio"
                                    name="novaposhta_type"
                                    value="WarehouseWarehouse" {if $novaposhta->type=='WarehouseWarehouse' || !$novaposhta->type} checked{/if} />
                                <span class="delivery_name" data-language="novaposhta_type_WarehouseWarehouse">{$lang->novaposhta_type_WarehouseWarehouse}</span>
                            </label>
                            <label class="delivery_label novaposhta_type_input{if $novaposhta->type=='WarehouseDoors'} active{/if}" for="types_WarehouseDoors">
                                <i class="filter_indicator"></i>

                                <input class="input_delivery" id="types_WarehouseDoors"
                                    onclick="change_novaposhta_method('WarehouseDoors')"
                                    type="radio"
                                    name="novaposhta_type"
                                    value="WarehouseDoors" {if $novaposhta->type=='WarehouseDoors'} checked{/if} />
                                <span class="delivery_name" data-language="novaposhta_type_WarehouseDoors">{$lang->novaposhta_type_WarehouseDoors}</span>
                            </label>
                        </div>
                        <div class="novaposhta_types">
                            <div class="form-group col-md-12 delivery-item type_WarehouseWarehouse"{if $novaposhta->type=='WarehouseDoors'} style="display:none;"{/if}>
                                <label class="pull-xs-left delivery-text"><span
                                            data-language="novaposhta_ware">{$lang->novaposhta_ware} <span class="required">*</span></span></label>
                                <select class="form-control novaposhta_ware cart-select" name="novaposhta_ware"
                                        id="novaposhta_ware" data-live-search="true" data-method="{$delivery->method}"
                                        data-type="2">
                                    {if $novaposhta_address}
                                        <option value="{$novaposhta_address->Ref|escape}"
                                                selected>{$novaposhta_address->Description|escape}</option>{else}
                                        <option value="" disabled selected
                                                data-language="novaposhta_ware">{$lang->novaposhta_form_ware|escape}</option>{/if}
                                </select>
                            </div>
                            <div class="form-group col-md-12 type_WarehouseDoors"{if $novaposhta->type!='WarehouseDoors'} style="display:none;"{/if}>
                                <p>
                                    <label class="pull-xs-left"><span
                                                data-language="novaposhta_street">{$lang->form_street} <span class="required">*</span></span></label>
                                    <input class="form_input placeholder_focus" name="novaposhta_street" type="text"
                                            value="{$novaposhta->street|escape}"
                                            data-language="novaposhta_street" />
                                </p>
                                <p>
                                    <label class="pull-xs-left"><span
                                                data-language="novaposhta_build">{$lang->form_build} <span class="required">*</span></span></label>
                                    <input class="form_input placeholder_focus" name="novaposhta_build" type="text"
                                            value="{$novaposhta->build|escape}"
                                            data-language="novaposhta_build" />
                                </p>
                                <p>
                                    <label class="pull-xs-left"><span
                                                data-language="novaposhta_apartment">{$lang->form_apartment}</span></label>
                                    <input class="form_input placeholder_focus" name="novaposhta_apartment" type="text"
                                            value="{$novaposhta->apartment|escape}"
                                            data-language="novaposhta_apartment" />
                                </p>
                            </div>
                        </div>
                    </div>
                {/if}

                {if $delivery->method == 'justin'}
                    <div class="row justin_delivery"{if !$justin_city && !$justin_ware && $f_delivery->method != 'justin' && $delivery@iteration > 1} style="display: none"{/if}>
                        <div class="m_btm-20px form-group col-md-12">
                            <label class="pull-xs-left"><span data-language="justin_city">{$lang->justin_city} <span
                                            class="required">*</span></span></label>
                            <select class="form-control justin_city cart-select" name="justin_city" id="justin_city"
                                    data-live-search="true" data-method="{$delivery->method}" data-type="1">
                                {if $justin_address}
                                    <option value="{$justin_address->CityRef|escape}"
                                            selected>{$justin_address->CityDescription|escape}</option>{else}
                                    <option value="" disabled selected
                                            data-language="justin_ware">{$lang->justin_form_city|escape}</option>{/if}
                            </select>
                        </div>
                        <div class="form-group col-md-12">
                            <label class="pull-xs-left"><span data-language="justin_ware">{$lang->justin_ware} <span
                                            class="required">*</span></span></label>
                            <select class="form-control justin_ware cart-select" name="justin_ware" id="justin_ware"
                                    data-live-search="true" data-method="{$delivery->method}" data-type="2">
                                {if $justin_address}
                                    <option value="{$justin_address->Ref|escape}"
                                            selected>{$justin_address->Description|escape}</option>{else}
                                    <option value="" disabled selected
                                            data-language="justin_ware">{$lang->justin_form_ware|escape}</option>{/if}
                            </select>
                        </div>
                    </div>
                {/if}

                {if $delivery->method == 'pickup' && isset($stores) && !empty($stores)}
                    <div class="row pickup_delivery"{if !$order->store_id && $f_delivery->method != 'pickup' && $delivery@iteration > 1} style="display: none"{/if}>
                        <div class="m_btm-20px form-group col-md-12 delivery-item">
                            <label class="pull-xs-left delivery-text"><span data-language="pickup_store">{$lang->pickup_store} <span class="required">*</span></span></label>
                            <select class="form-control store_id cart-select" name="store_id" id="store_id" data-live-search="true" data-method="{$delivery->method}">
                                {foreach $stores as $s}
                                    <option value="{$s->id}"{if $order->store_id == $s->id} selected{/if}>{$s->address}</option>
                                {/foreach}
                            </select>
                        </div>
                    </div>
                {/if}
            </div>
        {/foreach}
    </div>
</div>
{/if}

 {if  $is_mobile === true && $is_tablet === false}
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
{/if}
