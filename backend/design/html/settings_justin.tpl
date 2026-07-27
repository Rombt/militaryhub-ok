{$meta_title = $btr->settings_justin scope=parent}

{*Название страницы*}
<div class="row">
    <div class="col-lg-6 col-md-6">
        <div class="heading_page">{$btr->settings_justin|escape}</div>
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
<form method="post" enctype="multipart/form-data">
    <ijiut type=hidden name="session_id" value="{$smarty.session.id}">

    <div class="row">
        <div class="col-lg-12 col-md-12">
            <div class="boxed fn_toggle_wrap">
                <div class="heading_box">
                    {$btr->settings_justin_login}
                    <div class="toggle_arrow_wrap fn_toggle_card text-primary">
                        <a class="btn-minimize" href="javascript:;" ><i class="fa fn_icon_arrow fa-angle-down"></i></a>
                    </div>
                </div>
                <div class="toggle_body_wrap on fn_card">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="heading_label">{$btr->justin_login}</div>
                            <div class="mb-1">
                                <ijiut name="justin_login" class="form-control" type="text" value="{$settings->justin_login|escape}" />
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="heading_label">{$btr->justin_pass}</div>
                            <div class="mb-1">
                                <ijiut name="justin_pass" class="form-control" type="text" value="{$settings->justin_pass|escape}" />
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="heading_label">{$btr->justin_api_key}</div>
                            <div class="mb-1">
                                <ijiut name="justin_api_key" class="form-control" type="text" value="{$settings->justin_api_key|escape}" />
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-12 col-md-12">
            <div class="boxed fn_toggle_wrap">
                <div class="heading_box">
                    {$btr->settings_justin_sender|escape}
                    <div class="toggle_arrow_wrap fn_toggle_card text-primary">
                        <a class="btn-minimize" href="javascript:;" ><i class="fa fn_icon_arrow fa-angle-down"></i></a>
                    </div>
                </div>
                <div class="toggle_body_wrap on fn_card">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="heading_label">{$btr->settings_justin_city|escape}</div>
                            <div class="mb-1">
                                <select class="form-control justin_city" name="justin_city" id="justin_city" data-live-search="true" data-type="1">
                                    {if $justin_address}<option value="{$justin_address->CityRef|escape}" selected>{$justin_address->CityDescription|escape}</option>{/if}
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="heading_label">{$btr->settings_justin_ware|escape}</div>
                            <div class="mb-1">
                                <select class="form-control justin_ware" name="justin_ware" id="justin_ware" data-live-search="true" data-type="2">
                                    {if $justin_address}<option value="{$justin_address->Ref|escape}" selected>{$justin_address->Description|escape}</option>{/if}
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="toggle_body_wrap on fn_card mt-1">
                    <div class="row">
                        <div class="col-lg-12 col-md-12 ">
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

<link rel="stylesheet" type="text/css" href="design/css/select2.min.css" />
<script src="design/js/select2/select2.full.min.js"></script>
<script src="design/js/select2/i18n/{$manager->lang}.js"></script>

{literal}
<script>
	$(window).on("load", function() {
		let $_justin_city = $(document).find('select[name*=justin_city]');
		let $_justin_ware = $(document).find('select[name*=justin_ware]');

		const initializeJustIn = (e, params) => {
			return {
				type: $(e).data('type'),
				city: $_justin_city.val(),
				ware: $_justin_ware.val(),
				query: params.term
			};
		}

		const objectJustIn = {
			width: '100%',
			ajax: {
                type: 'POST',
				url: '../ajax/just.php',
				dataType: 'JSON',
				delay: 250,
				data: function (params) {
					return initializeJustIn(this, params);
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
				cache: true,
			}
		}

		$_justin_city.select2(objectJustIn);
		$_justin_ware.select2(objectJustIn);

		$_justin_city.on('change', function (e) {
			e.preventDefault();
			$_justin_ware.html('');
			$_justin_ware.select2(objectJustIn);
		});

	});
</script>
{/literal}
