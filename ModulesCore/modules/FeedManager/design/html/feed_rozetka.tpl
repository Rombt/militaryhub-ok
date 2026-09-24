<div class="row">
    <div class="col-lg-12 col-md-7 ">
        <div class="wrap_heading">
            <div class="box_heading heading_page">
                {$feed_name}
            </div>
        </div>
        <div id="rmbt_error_massage" class="col-lg-12 col-md-12 col-sm-12{if empty($errors)} rmbt-hide{/if}">
            <div class="boxed boxed_warning">
                <div id="rmbt_error_massage_box">
                    {foreach $errors as $error}
                        <p>{$error}</p>
                    {/foreach}
                </div>
            </div>
        </div>

        <div id="rmbt_success_massage" class="col-lg-12 col-md-12 col-sm-12 rmbt-hide">
            <div class="boxed boxed_success">
                <div id="rmbt_success_massage_box">
                    <p>{$success_massage}</p>
                </div>
            </div>
        </div>

    </div>
</div>

<div class="boxed">
    <div class="heading_box">Ограничения товаров которые попадут в фид</div>
    <form id="rmbt-feed-rozetka-constraints" class="fn_form_list" method="post" action="">
        <input type=hidden name="session_id" value="{$smarty.session.id}">
        <input type="hidden" name="lang_id" value="{$lang_id}" />
        <div class="row">
            <div class="heading_box">
                <div class="heading_label rmbt-notes">
                    <span> </span>
                </div>

            </div>
            <div class="rmbt-field-wrap col-lg-12 col-md-12">

                <div class="rmbt-list-row col-lg-4 col-md-6">
                    <label class="" for="rmbt-date">
                        <span class="heading_label">Продукты созданные до этой даты в фид Розетки не попадут</span>
                        <input id="rmbt_date" class="form-control" type="date" name="rmbt-date">
                    </label>
                </div>

                <div class="rmbt-list-row col-lg-4 col-md-6">

                    <label for="rmbt-names_match">
                        <span class="heading_label">
                            Если у продуктов совпадают названия, то в фид Розетки попадёт только самый новый
                        </span>
                    </label>

                    <div class="main_list_boding main_list_status">
                        <input type="hidden" name="rmbt_names_match" value="0">
                        <label class="switch switch-default">
                            <input id="rmbt-names_match" class="switch-input" type="checkbox" name="rmbt_names_match" value="1">
                            <span class="switch-label"></span>
                            <span class="switch-handle"></span>
                        </label>
                    </div>

                </div>

                <button id="" class="btn btn_small btn-info rmbt-submit" type="submit">
                    <svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" viewBox="0 0 26 26" width="20px" height="20px">
                        <path d="m.3,14c-0.2-0.2-0.3-0.5-0.3-0.7s0.1-0.5 0.3-0.7l1.4-1.4c0.4-0.4 1-0.4 1.4,0l.1,.1 5.5,5.9c0.2,0.2 0.5,0.2 0.7,0l13.4-13.9h0.1v-8.88178e-16c0.4-0.4 1-0.4 1.4,0l1.4,1.4c0.4,0.4 0.4,1 0,1.4l0,0-16,16.6c-0.2,0.2-0.4,0.3-0.7,0.3-0.3,0-0.5-0.1-0.7-0.3l-7.8-8.4-.2-.3z" fill="currentColor">
                        </path>
                    </svg>
                    Применить изменения
                </button>
            </div>
        </div>
    </form>
</div>


<div class="boxed">
    <div class="heading_box">Скидка для товаров в фиде</div>

    <div class="row" id="rmbt-feed-discount-box">
        <div class="rmbt-field-wrap col-lg-12 col-md-12">

            <div class="rmbt-list-row col-lg-4 col-md-6">
                <label for="rmbt-feed-discount">
                    <span class="heading_label">
                        Уменьшить скидку товаров в фиде на
                    </span>

                    <div class="input-group">
                        <input id="rmbt-feed-discount" class="form-control" type="number" name="rmbt-feed-discount" min="0" max="100" step="1" value="{$feed_rozetka_discount|default:0}" placeholder="Введите процент">
                        <span class="input-group-addon">%</span>
                    </div>
                </label>
            </div>

            <div class="rmbt-list-row col-lg-4 col-md-6">
                <label for="rmbt-feed-min-discount">
                    <span class="heading_label">
                        Минимальная скидка для товаров в фиде
                    </span>
                    <div class="input-group">
                        <input id="rmbt-feed-min-discount" class="form-control" type="number" name="feed_min_discount" min="0" max="100" step="1" value="{$feed_rozetka_min_discount|default:0}">
                        <span class="input-group-addon">%</span>
                    </div>
                </label>
            </div>

            <button id="rmbt-feed-discount-submit" class="btn btn_small btn-info rmbt-submit" type="button">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 26 26" width="20px" height="20px">
                    <path d="m.3,14c-0.2-0.2-0.3-0.5-0.3-0.7s.1-.5.3-.7l1.4-1.4c.4-.4,1-.4,1.4,0l.1.1,5.5,5.9c.2.2.5.2.7,0l13.4-13.9h.1v-8.88178e-16c.4-.4,1-.4,1.4,0l1.4,1.4c.4.4.4,1,0,1.4l0,0-16,16.6c-.2.2-.4.3-.7.3-.3,0-.5-.1-.7-.3l-7.8-8.4-.2-.3z" fill="currentColor"></path>
                </svg>
                Применить
            </button>

        </div>
    </div>
</div>



<div class="boxed" id="rmbt-feed-rozetka-abbreviations">
    <div class="rmbt-field-wrap ">
        <div class="heading_box">Сокращения в свойствах товаров
            <button id="save-token-dictionary" class="btn btn_small  btn-info " type="submit">
                <svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" viewBox="0 0 26 26" width="20px" height="20px">
                    <path d="m.3,14c-0.2-0.2-0.3-0.5-0.3-0.7s0.1-0.5 0.3-0.7l1.4-1.4c0.4-0.4 1-0.4 1.4,0l.1,.1 5.5,5.9c0.2,0.2 0.5,0.2 0.7,0l13.4-13.9h0.1v-8.88178e-16c0.4-0.4 1-0.4 1.4,0l1.4,1.4c0.4,0.4 0.4,1 0,1.4l0,0-16,16.6c-0.2,0.2-0.4,0.3-0.7,0.3-0.3,0-0.5-0.1-0.7-0.3l-7.8-8.4-.2-.3z" fill="currentColor">
                    </path>
                </svg>
                Добавить соответствия в БД
            </button>
        </div>
    </div>

    <div class="row">

        <div class="two-col property-values">
            <div class="rmbt-values-without-match">
                <label for="rmbt-values-without-match">
                    <span class="heading_label">
                        Получить значения только тех свойств у которых нет соответствия
                    </span>
                </label>
                <div class="main_list_boding main_list_status">
                    <input type="hidden" name="values-without-match" value="0">
                    <label class="switch switch-default">
                        <input id="rmbt-values-without-match" class="switch-input" type="checkbox" name="rmbt-values-without-match" value="1" checked>
                        <span class="switch-label"></span>
                        <span class="switch-handle"></span>
                    </label>
                </div>
            </div>
            <label class="" for="feature_id">
                <span class="heading_label">
                    Введите feature_id свойств товаров разделяя их запятыми (100 это цвет)
                </span>
                <input id="feature_id" class="form-control" type="text" name="feature_id" value="100">
            </label>
            <div class="rmbt-limitation-elements">
                <div class="heading_box">Если свойств товаров много вы можете их ограничить.</div>
                <label class="heading_label" for="feature_id">
                    Задать смещение от начала итогового массива
                    <input id="offset" class="form-control" type="text" name="offset">
                </label>
                <label class="heading_label" for="feature_id">
                    Задать количество элементов
                    <input id="size_batch" class="form-control" type="text" name="size_batch">
                </label>
            </div>
        </div>

        <div class="two-col property-values" id="property-values">
            <textarea id="property-values" name="property-values"></textarea>
            <button id="get-property-values" class="btn btn_small btn_blue" type="submit">
                Получить значения свойств
            </button>
        </div>
    </div>

</div>



<script>
    document.addEventListener('DOMContentLoaded', function() {

        const form = document.getElementById('rmbt-feed-rozetka-constraints');
        const rmbtErrorMassage = document.getElementById('rmbt_error_massage');
        const errorMassageBox = document.getElementById('rmbt_error_massage_box');

        const successMassage = document.getElementById('rmbt_success_massage_box');
        const successMassageBox = document.getElementById('rmbt_success_massage');

        form.addEventListener('submit', function(e) {
            e.preventDefault();

            rmbtErrorMassage.classList.add('rmbt-hide');
            successMassageBox.classList.add('rmbt-hide');

            const formData = new FormData(form);

            formData.append(
                'session_id',
                document.querySelector('input[name="session_id"]').value
            );

            formData.append(
                'lang_id',
                document.querySelector('input[name="lang_id"]').value
            );

            formData.append('submit', true);


            fetch(form.action || window.location.href, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(response => {
                    if (!response.ok) throw new Error('HTTP error! status: ' + response.status);
                    return response.json();
                })
                .then(json => {
                    if (json) {
                        if (json.errors.length > 0) {
                            errorMassageBox.textContent = json.errors;
                            rmbtErrorMassage.classList.remove('rmbt-hide');
                        } else {
                            successMassageBox.classList.remove('rmbt-hide');
                            successMassage.textContent = json.success_massage;
                            console.log('ok');
                            console.log(json);
                        }
                    }
                })
                .catch(err => {
                    errorMassageBox.textContent = 'Возникла непредвиденная ошибка';
                    rmbtErrorMassage.classList.remove('rmbt-hide');
                })
        });


        /* Discounts */

        const feedDiscountInput = document.getElementById('rmbt-feed-discount');
        const feedDiscountSubmit = document.getElementById('rmbt-feed-discount-submit');

        feedDiscountSubmit.addEventListener('click', function(e) {
            e.preventDefault();

            rmbtErrorMassage.classList.add('rmbt-hide');
            successMassageBox.classList.add('rmbt-hide');

            const formData = new FormData();

            formData.append(
                'session_id',
                document.querySelector('input[name="session_id"]').value
            );

            formData.append(
                'lang_id',
                document.querySelector('input[name="lang_id"]').value
            );

            formData.append( 'feed_discount', feedDiscountInput.value );

            formData.append('save_feed_discount', true);

            formData.append(
                'feed_min_discount',
                document.getElementById('rmbt-feed-min-discount').value
            );


            fetch(window.location.href, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(response => {
                    if (!response.ok) {
                        throw new Error(
                            'HTTP error! status: ' + response.status
                        );
                    }

                    return response.json();
                })
                .then(json => {
                    if (json) {

                        if (json.errors.length > 0) {

                            errorMassageBox.textContent = json.errors;
                            rmbtErrorMassage.classList.remove('rmbt-hide');

                        } else {

                            successMassageBox.classList.remove('rmbt-hide');
                            successMassage.textContent = json.success_massage;

                        }
                    }
                })
                .catch(err => {

                    console.error(err);

                    errorMassageBox.textContent =
                        'Возникла непредвиденная ошибка';

                    rmbtErrorMassage.classList.remove('rmbt-hide');
                });
        });

        /*  Abbreviations   */

        const btnGetPropertyValues = document.getElementById('get-property-values');
        const btnSaveTokenDictionary = document.getElementById('save-token-dictionary');
        const inputValuesWithoutMatch = document.getElementById('rmbt-values-without-match');
        const inputFeatureId = document.getElementById('feature_id');
        const inputOffset = document.getElementById('offset');
        const inputSizeBatch = document.getElementById('size_batch');
        const textareaPropertyValues = document.querySelector('#property-values textarea');

        btnGetPropertyValues.addEventListener('click', function(e) {
            e.preventDefault();

            rmbtErrorMassage.classList.add('rmbt-hide');
            successMassageBox.classList.add('rmbt-hide');

            const formData = new FormData();

            formData.append('session_id', document.querySelector('input[name="session_id"]').value);

            formData.append('lang_id', document.querySelector('input[name="lang_id"]').value);

            formData.append('values_without_match', inputValuesWithoutMatch.checked ? 1 : 0);
            formData.append('feature_Ids', inputFeatureId.value);
            formData.append('offset', inputOffset.value);
            formData.append('size_batch', inputSizeBatch.value);

            formData.append('get_property_values', true);


            fetch(form.action || window.location.href, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(response => {
                    if (!response.ok) throw new Error('HTTP error! status: ' + response.status);
                    return response.json();
                })
                .then(json => {
                    if (json) {

                        if (json.errors.length > 0) {
                            errorMassageBox.textContent = json.errors;
                            rmbtErrorMassage.classList.remove('rmbt-hide');
                        } else {
                            successMassageBox.classList.remove('rmbt-hide');
                            successMassage.textContent = json.success_massage;
                            console.log('ok');
                            console.log(json);

                            textareaPropertyValues.value = JSON.stringify(json.result, null, 2);
                        }
                    }
                })
                .catch(err => {
                    errorMassageBox.textContent = 'Возникла непредвиденная ошибка';
                    rmbtErrorMassage.classList.remove('rmbt-hide');
                })



        });


        btnSaveTokenDictionary.addEventListener('click', function(e) {
            e.preventDefault();

            rmbtErrorMassage.classList.add('rmbt-hide');
            successMassageBox.classList.add('rmbt-hide');

            const formData = new FormData();
            formData.append('session_id', document.querySelector('input[name="session_id"]').value);
            formData.append('lang_id', document.querySelector('input[name="lang_id"]').value);

            const data = textareaPropertyValues.value;
            formData.append('data', data);
            formData.append('save_token_dictionary', true);
            formData.append('feature_Ids', inputFeatureId.value);

            fetch(form.action || window.location.href, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(response => {
                    if (!response.ok) throw new Error('HTTP error! status: ' + response.status);
                    return response.json();
                })
                .then(json => {
                    if (json) {

                        if (json.errors.length > 0) {
                            errorMassageBox.textContent = json.errors;
                            rmbtErrorMassage.classList.remove('rmbt-hide');
                        } else {
                            successMassageBox.classList.remove('rmbt-hide');
                            successMassage.textContent = json.success_massage;
                            textareaPropertyValues.value = JSON.stringify(json.result.duplicates, null, 2);
                        }
                    }
                })
                .catch(err => {
                    errorMassageBox.textContent = 'Возникла непредвиденная ошибка';
                    rmbtErrorMassage.classList.remove('rmbt-hide');
                })



        });



    });
</script>
