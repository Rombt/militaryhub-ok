{$meta_title = $btr->settings_novaposhta scope=parent}

{*Название страницы*}
<div class="row">
    <div class="col-lg-6 col-md-6">
        <div class="heading_page">{$btr->settings_novaposhta|escape}</div>
    </div>
    <div class="col-lg-4 col-md-3 text-xs-right float-xs-right"></div>
</div>

{*Вывод успешных сообщений*}
{if $message_success}
    <div class="row">
        <div class="col-lg-12 col-md-12 col-sm-12">
            <div class="boxed boxed_success">
                <div class="heading_box">
                    {if $message_success == 'saved'}
                        {$btr->general_settings_saved|escape}
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

{*Главная форма страницы*}
<form method="post" enctype="multipart/form-data" class="fn_form_list">
    <input type="hidden" name="session_id" value="{$smarty.session.id}">

    <div class="row">
        <div class="col-lg-12 col-md-12">
            <div class="boxed fn_toggle_wrap">
                <div class="heading_box">
                    {$btr->settings_novaposhta_keys|escape}
                    <div class="toggle_arrow_wrap fn_toggle_card text-primary">
                        <a class="btn-minimize" href="javascript:;" ><i class="fa fn_icon_arrow fa-angle-down"></i></a>
                    </div>
                </div>
                <div class="toggle_body_wrap on fn_card">
                    <div class="row">
                        <div class="col-md-6 pr-0">
                            <div class="heading_label">{$btr->settings_novaposhta_token|escape}</div>
                            <div class="mb-1">
                                <input name="np_api_key" class="form-control" type="text" value="{$settings->np_api_key|escape}" />
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-6 col-md-6 col-sm-12 pr-0">
            <div class="boxed fn_toggle_wrap min_height_210px">
                <div class="heading_box">
                    {$btr->settings_novaposhta_sender|escape}
                    <div class="toggle_arrow_wrap fn_toggle_card text-primary">
                        <a class="btn-minimize" href="javascript:;" ><i class="fa fn_icon_arrow fa-angle-down"></i></a>
                    </div>
                </div>
                <div class="toggle_body_wrap on fn_card">
                    <div class="row">
                        <div class="col-md-6 pr-0">
                            <div class="heading_label">{$btr->settings_novaposhta_city|escape}</div>
                            <div class="mb-1">
                                <select class="form-control novaposhta_city" name="novaposhta_city" id="novaposhta_city" data-live-search="true" data-type="1">
                                    {if $novaposhta_address}<option value="{$novaposhta_address->CityRef|escape}" selected>{$novaposhta_address->CityDescription|escape}</option>{/if}
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="heading_label">{$btr->settings_novaposhta_ware|escape}</div>
                            <div class="mb-1">
                                <select class="form-control novaposhta_ware" name="novaposhta_ware" id="novaposhta_ware" data-live-search="true" data-type="2">
                                    {if $novaposhta_address}<option value="{$novaposhta_address->Ref|escape}" selected>{$novaposhta_address->Description|escape}</option>{/if}
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-6 col-md-6 col-sm-12">
            <div class="boxed fn_toggle_wrap min_height_210px">
                <div class="heading_box">
                    {$btr->settings_novaposhta_update|escape}
                    <div class="toggle_arrow_wrap fn_toggle_card text-primary">
                        <a class="btn-minimize" href="javascript:;" ><i class="fa fn_icon_arrow fa-angle-down"></i></a>
                    </div>
                </div>
                <div class="toggle_body_wrap on fn_card">
                    <div class="row">
                        {if $settings->last_novaposhta_update_date}
                            <div class="col-md-6 pr-0">
                                <div class="heading_label">{$btr->settings_novaposhta_last_update|escape}</div>
                                <div class="mb-1">
                                    <input class="form-control" type="text" value="{$settings->last_novaposhta_update_date|escape}" readonly disabled />
                                </div>
                            </div>
                        {/if}
                        <div class="col-xs-12{if $settings->last_novaposhta_update_date} col-md-6{else} col-md-12{/if}">
                            <div class="heading_label">&nbsp;</div>
                            {if !$settings->last_novaposhta_update_date || $settings->last_novaposhta_update_page|intval > 0}
                                <div class="mb-1">
                                    <div class="boxed boxed_warning">
                                        <div class="">
                                            {$btr->settings_novaposhta_processed|escape}
                                        </div>
                                    </div>
                                </div>
                            {else}
                                <div class="mb-1">
                                    <input type="hidden" name="update" value="0" />
                                    <button type="button" class="btn btn_small btn_blue btn-danger fn_update" data-toggle="modal" data-target="#fn_update">
                                        {include file='svg_icon.tpl' svgId='checked'}
                                        <span>{$btr->settings_novaposhta_to_update|escape}</span>
                                    </button>
                                </div>
                            {/if}
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

    {* <div class="row">
        <div class="col-lg-12 col-md-12">
            <div class="boxed fn_toggle_wrap">
                <div class="heading_box">
                    {$btr->settings_novaposhta_settings|escape}
                    <div class="toggle_arrow_wrap fn_toggle_card text-primary">
                        <a class="btn-minimize" href="javascript:;" ><i class="fa fn_icon_arrow fa-angle-down"></i></a>
                    </div>
                </div>

                <div class="toggle_body_wrap on fn_card">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="heading_label">{$btr->settings_novaposhta_deliveryType|escape}</div>
                            <div class="mb-1">
                                <select name="novaposhta_deliveryType" class="selectpicker mb-1">
                                    <option value="WarehouseDoors" disabled {if $settings->novaposhta_deliveryType == 'WarehouseDoors'}selected{/if}>склад-двері</option>
                                    <option value="WarehouseWarehouse" {if $settings->novaposhta_deliveryType == 'WarehouseWarehouse'}selected{/if}>склад-склад</option>
                                    <option value="DoorsWarehouse" disabled {if $settings->novaposhta_deliveryType == 'DoorsWarehouse'}selected{/if}>двері-склад</option>
                                    <option value="DoorsDoors" disabled {if $settings->novaposhta_deliveryType == 'DoorsDoors'}selected{/if}>двері-двері</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="heading_label">{$btr->settings_novaposhta_cargoType|escape}</div>
                            <div class="mb-1">
                                <select name="novaposhta_cargoType" class="selectpicker mb-1">
                                    <option value="Cargo" {if $settings->novaposhta_cargoType == 'Cargo'}selected{/if}>Вантаж</option>
                                    <option value="Documents" {if $settings->novaposhta_cargoType == 'Documents'}selected{/if}>Документи</option>
                                    <option value="TiresWheels" {if $settings->novaposhta_cargoType == 'TiresWheels'}selected{/if}>Шини-диски</option>
                                    <option value="Pallet" {if $settings->novaposhta_cargoType == 'Pallet'}selected{/if}>Палети</option>
                                    <option value="Parcel" {if $settings->novaposhta_cargoType == 'Parcel'}selected{/if}>Посилка</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="heading_label">{$btr->settings_novaposhta_TypeOfPayer|escape}</div>
                            <div class="mb-1">
                                <select name="novaposhta_TypeOfPayer" class="selectpicker mb-1">
                                    <option value="Sender" {if $settings->novaposhta_TypeOfPayer == 'Sender'}selected{/if}>Відправник</option>
                                    <option value="Recipient" {if $settings->novaposhta_TypeOfPayer == 'Recipient'}selected{/if}>Одержувач</option>
                                    <option value="ThirdPerson" {if $settings->novaposhta_TypeOfPayer == 'ThirdPerson'}selected{/if}>Третя особа</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="heading_label">{$btr->settings_novaposhta_PaymentForm|escape}</div>
                            <div class="mb-1">
                                <select name="novaposhta_PaymentForm" class="selectpicker mb-1">
                                    <option value="NonCash" {if $settings->novaposhta_PaymentForm == 'NonCash'}selected{/if}>Безналичный расчет</option>
                                    <option value="Cash" {if $settings->novaposhta_PaymentForm == 'Cash'}selected{/if}>Наличный расчет</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div> *}
</form>

<div id="fn_update" class="modal fade show" role="document">
    <div class="modal-dialog modal-md">
        <div class="modal-content">
            <div class="card-header">
                <div class="heading_modal">{$btr->settings_novaposhta_update_confirm|escape}</div>
            </div>
            <div class="modal-body">
                <button type="submit" class="btn btn_small btn_blue fn_update_confirm mx-h">
                    {include file='svg_icon.tpl' svgId='checked'}
                    <span>{$btr->index_yes|escape}</span>
                </button>

                <button type="button" class="btn btn_small btn-danger fn_update_dismiss mx-h" data-dismiss="modal">
                    {include file='svg_icon.tpl' svgId='delete'}
                    <span>{$btr->index_no|escape}</span>
                </button>
            </div>
        </div>
    </div>
</div>

<link rel="stylesheet" type="text/css" href="design/css/select2.min.css" />
<script src="design/js/select2/select2.full.min.js"></script>
<script src="design/js/select2/i18n/{$manager->lang}.js"></script>

{literal}
<script>
	$(window).on("load", function() {
		let $_novaposhta_city = $(document).find('select[name*=novaposhta_city]');
		let $_novaposhta_ware = $(document).find('select[name*=novaposhta_ware]');

		const initializeNovaPoshta = (e, params) => {
			return {
				type: $(e).data('type'),
				city: $_novaposhta_city.val(),
				ware: $_novaposhta_ware.val(),
				query: params.term
			};
		}

		const objectNovaPoshta = {
			width: '100%',
			ajax: {
                type: 'POST',
				url: '../ajax/novaposhta.php',
				dataType: 'JSON',
				delay: 250,
				data: function (params) {
					return initializeNovaPoshta(this, params);
				},
				processResults: function (r) {
					let result = [];
					if (r.length > 0) {
						$.each(r, function(i, v) {
							let id = value = '';
							if (v.city !== undefined) {
								id = v.city.Ref;
								value = v.city.Description;
							}
							if (v.ware !== undefined) {
								id = v.ware.Ref;
								value = v.ware.Description;
							}
							result.push({
								id : id,
								text: value
							});
						});

					}
					return {
						results: result
					};
				},
				cache: true
			}
		}

		$_novaposhta_city.select2(objectNovaPoshta);
		$_novaposhta_ware.select2(objectNovaPoshta);

		$_novaposhta_city.on('change', function (e) {
			e.preventDefault();
			$_novaposhta_ware.html('');
			$_novaposhta_ware.select2(objectNovaPoshta);
		});

        $(document).on('click', '.fn_update_confirm', function () {
            $('input[name="update"]').val(1);
            $('.fn_form_list').submit();
        });
        
        $(document).on('click', '.fn_update_dismiss', function () {
            $('input[name="update"]').val(0);
        });
	});
</script>
{/literal}
