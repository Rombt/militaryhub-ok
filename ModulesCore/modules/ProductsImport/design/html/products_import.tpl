<div class="row">
    <div class="col-lg-12 col-md-7">
        <div class="wrap_heading rmbt-heading">
            <div class="box_heading heading_page rmbt-heading_page">
                {$title}
            </div>

            <button id="sync_products" class="btn btn_small btn_blue" type="button">
                {include file='svg_icon.tpl' svgId='checked'}
                Импортировать товары
            </button>

            <button id="transfer_images" class="btn btn_small btn_blue" type="button">
                {include file='svg_icon.tpl' svgId='checked'}
                Перенести изображения
            </button>

            <button id="reset_import" class="btn btn_small btn_red" type="button">
                Очистить таблицы импорта
            </button>

            <input type="hidden" name="session_id" value="{$smarty.session.id}">
            <input type="hidden" name="lang_id" value="{$lang_id}" />
        </div>

        <div id="rmbt_success_massage_box" class="col-lg-12 col-md-12 col-sm-12 rmbt-hide">
            <div class="boxed boxed_success">
                <div id="rmbt_success_massage_text" class="heading_box"></div>
            </div>
        </div>

        <div id="rmbt_error_massage" class="col-lg-12 col-md-12 col-sm-12{if empty($errors)} rmbt-hide{/if}">
            <div class="boxed boxed_warning">
                <div id="rmbt_error_massage_box" class="heading_box">
                    {foreach $errors as $error}
                        <p>{$error}</p>
                    {/foreach}
                </div>
            </div>
        </div>
    </div>

</div>

<div id="rmbt_product_import_status_box" class="row">
    <div class="col-xs-12">
        <div class="boxed rmbt-boxed">
            <div class="heading_box">Состояние Импорта</div>

            {if !$product_import_status}
                <p>Импорт ещё не выполнялся.</p>

            {elseif $product_import_status->product_import_status == 'running'
                    && !$product_import_status->finished_at
            }
                <p>Предыдущий импорт был прерван. Для того что бы его завершить запустите импорт ещё раз</p>
            {elseif $product_import_status->product_import_status == 'completed_with_errors'}
                <p>Импорт товаров завершён с ошибками.</p>
            {elseif $product_import_status->product_import_status == 'success'
                    && $product_import_status->image_transfer_status == 'pending'
            }
                <p>Импорт товаров выполнен. Необходимо перенести изображения.</p>
            {elseif $product_import_status->image_transfer_status == 'completed_with_errors'}
                <p>Перенос изображений завершён с ошибками.</p>
            {elseif $product_import_status->product_import_status == 'success'
                    && $product_import_status->image_transfer_status == 'success'
            }
                <p>Импорт товаров и изображений завершён.</p>
            {/if}
        </div>
    </div>
</div>

<div class="row">
    <div class="col-xs-12">
        <div class="boxed rmbt-boxed">
            <div class="heading_box">
                Результаты импорта товаров
            </div>

            <div id="rmbt_task_status" class="rmbt-hide">
                Імпорт виконується, очікуйте...
            </div>
            <div id="sync_products_results" class="rmbt-task-results"> </div>
        </div>
    </div>

</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {

        const syncProducts = document.getElementById('sync_products');
        const transferImages = document.getElementById('transfer_images');
        const resetImport = document.getElementById('reset_import');

        const syncProductsResults = document.getElementById('sync_products_results');
        const taskStatus = document.getElementById('rmbt_task_status');

        const errorMassageBox = document.getElementById('rmbt_error_massage');
        const errorMassageText = document.getElementById('rmbt_error_massage_box');

        const successMassageBox = document.getElementById('rmbt_success_massage_box');
        const successMassageText = document.getElementById('rmbt_success_massage_text');
        const productImportStatusBox = document.getElementById('rmbt_product_import_status_box');


        if (syncProducts) {
            syncProducts.addEventListener('click', function() {

                const formData = createFormData('sync_products');

                if (!formData) {
                    return;
                }

                hideMessages();
                showTaskStatus('Імпорт товарів виконується, очікуйте...');

                syncProducts.disabled = true;

                sendRequest(formData, syncProducts);
            });
        }


        if (transferImages) {
            transferImages.addEventListener('click', function() {

                const formData = createFormData('transfer_images');

                if (!formData) {
                    return;
                }

                hideMessages();
                showTaskStatus('Перенесення зображень виконується, очікуйте...');

                transferImages.disabled = true;

                sendRequest(formData, transferImages);
            });
        }


        if (resetImport) {
            resetImport.addEventListener('click', function() {

                if (!confirm('Удалить все данные импорта из Fly?')) {
                    return;
                }

                const formData = createFormData('reset_import');

                if (!formData) {
                    return;
                }

                hideMessages();
                showTaskStatus('Очищення таблиць імпорту виконується, очікуйте...');

                resetImport.disabled = true;

                sendRequest(formData, resetImport);
            });
        }


        function createFormData(action) {

            const sessionInput = document.querySelector('input[name="session_id"]');
            const langInput = document.querySelector('input[name="lang_id"]');

            if (!sessionInput || !langInput) {
                console.error('Не найдены обязательные поля session_id или lang_id');
                return null;
            }

            const formData = new FormData();

            formData.append('session_id', sessionInput.value);
            formData.append('lang_id', langInput.value);
            formData.append(action, 1);

            return formData;
        }


        function sendRequest(formData, button) {

            productImportStatusBox.classList.add('rmbt-hide');

            fetch(window.location.href, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(response => {

                    if (!response.ok) {
                        throw new Error('HTTP error! status: ' + response.status);
                    }

                    return response.json();
                })
                .then(data => {

                    hideTaskStatus();

                    if (!data.success) {

                        if (!errorMassageBox || !errorMassageText) {
                            return;
                        }

                        errorMassageText.innerHTML = '';

                        if (data.errors && data.errors.length > 0) {

                            data.errors.forEach(error => {

                                const p = document.createElement('p');
                                p.textContent = error;

                                errorMassageText.appendChild(p);
                            });
                        }

                        if (
                            data.results &&
                            data.results.error_messages &&
                            data.results.error_messages.length > 0
                        ) {

                            const p = document.createElement('p');
                            p.textContent = data.success_message || '';

                            errorMassageText.appendChild(p);
                        }

                        errorMassageBox.classList.remove('rmbt-hide');

                    } else {

                        if (!successMassageBox || !successMassageText) {
                            return;
                        }

                        successMassageText.textContent = data.success_message || '';
                        successMassageBox.classList.remove('rmbt-hide');
                    }


                    if (
                        syncProductsResults &&
                        data.results !== undefined
                    ) {

                        syncProductsResults.innerHTML =
                            '<pre><code>' +
                            JSON.stringify(data.results, null, 2) +
                            '</code></pre>';
                    }
                })
                .catch(error => {

                    console.error(error);

                    hideTaskStatus();

                    if (!errorMassageBox || !errorMassageText) {
                        return;
                    }

                    errorMassageText.innerHTML = '';

                    const p = document.createElement('p');
                    p.textContent = 'Ошибка соединения или сервер не отвечает';

                    errorMassageText.appendChild(p);

                    errorMassageBox.classList.remove('rmbt-hide');
                })
                .finally(() => {

                    if (button) {
                        button.disabled = false;
                    }
                });
        }


        function showTaskStatus(message) {

            if (!taskStatus) {
                return;
            }

            taskStatus.textContent = message;
            taskStatus.classList.remove('rmbt-hide');
        }


        function hideTaskStatus() {

            if (!taskStatus) {
                return;
            }

            taskStatus.classList.add('rmbt-hide');
        }


        function hideMessages() {

            if (errorMassageBox) {
                errorMassageBox.classList.add('rmbt-hide');
            }

            if (successMassageBox) {
                successMassageBox.classList.add('rmbt-hide');
            }
        }

    });
</script>
