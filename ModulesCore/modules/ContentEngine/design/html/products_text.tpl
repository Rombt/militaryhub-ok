<div class="row">
    <div class="col-lg-12 col-md-7">
        <div class="wrap_heading rmbt-heading">
            <div class="box_heading heading_page">
                {$title}
            </div>

            <button id="send_to_bd" class="btn btn_small btn_blue" type="button" disabled>
                {include file='svg_icon.tpl' svgId='checked'}
                Отправить результаты в базу данных
            </button>

        </div>
        <div id="rmbt_success_massage" class="col-lg-12 col-md-12 col-sm-12 rmbt-hide">
            <div class="boxed boxed_success">
                <div class="heading_box">
                    Описания товаров добавлены в базу данных
                    <a class="btn btn_return float-xs-right"
                        href="/backend/index.php?module=ProductsAdmin&amp;limit=100&amp;page=411">
                        <svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" width="20px"
                            height="20px" viewBox="0 0 459 459">
                            <path
                                d="M178.5,140.25v-102L0,216.75l178.5,178.5V290.7c127.5,0,216.75,40.8,280.5,130.05C433.5,293.25,357,165.75,178.5,140.25z"
                                fill="currentColor"></path>
                        </svg>
                        <span>Вернуться к списку</span>
                    </a>
                </div>
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

<div class="rmbt-tabs boxed">
    <form id="rmbt-gpt-description" class="fn_form_list" method="post" action="">
        <input type=hidden name="session_id" value="{$smarty.session.id}">
        <input type="hidden" name="lang_id" value="{$lang_id}" />
        <ul class="rmbt-tab-list" role="tablist">
            <li class="rmbt-tab" role="tab" aria-selected="true" aria-controls="panel1" id="tab1">Импорт данных</li>
            <li class="rmbt-tab" role="tab" aria-selected="false" aria-controls="panel2" id="tab2">Генерация данных</li>
        </ul>
        <div class="rmbt-tab-panels">
            <div role="tabpanel" id="panel1" aria-labelledby="tab1" data-action="import">
                <div class="row">
                    <div class="col-xs-12">
                        <div class="boxed rmbt-boxed">

                            <div id="rmbt-file-upload-heading" class="heading_box rmbt-gpt-results-heading">
                                <label class="file-upload">
                                    <span id="rmbt_file-upload_btn" class="btn btn_small btn-info">
                                        Выберите файл
                                    </span>
                                    <input id="rmbt_file-upload" type="file" name="file" accept=".json,application/json"
                                        hidden>
                                </label>

                                <div id="rmbt-file-upload-report" class="heading_label rmbt-notes"> </div>
                            </div>



                            <div id="rmbt_file-upload-row-wrap"> </div>

                        </div>
                    </div>
                </div>
            </div>
            <div role="tabpanel" id="panel2" aria-labelledby="tab2" data-action="generation">
                <div class="row">
                    <div class="col-xs-12">
                        <div class="boxed rmbt-boxed">
                            <div class="heading_box">
                                Параметры генерации
                                <div class="heading_label rmbt-notes">
                                    Зверніть увагу! За одну генерацію може бути оброблено <span> не більше ніж
                                        {$limit_products}
                                        товарів! </span>
                                </div>
                            </div>
                            <div class="rmbt-field-wrap col-lg-4 col-md-12">
                                <label class="rmbt-date" for="rmbt-date">
                                    <span class="heading_label">Продукты созданные до этой даты обработаны не
                                        будут</span>
                                    <input id="rmbt_date" class="form-control" type="date" name="rmbt-date">
                                </label>
                                <label class="rmbt-field-wrap rmbt-products-id" for="rmbt-date">
                                    <span class="heading_label">
                                        Введите id продуктов для которых нужно сгенерировать описание
                                    </span>
                                    <textarea id="rmbt-products-id" class="form-control"
                                        name="rmbt-products-id"></textarea>
                                </label>
                            </div>
                            <label class="col-lg-8 col-md-12 rmbt-prompt" for="rmbt-prompt">
                                <span class="heading_label">Введите промпт вместо названия продукта вставте шаблон
                                    %product_name%
                                </span>
                                <textarea class="form-control" name="rmbt-prompt" id="rmbt-prompt"></textarea>
                            </label>
                            <button id="start_generation" class="btn btn_small btn-info rmbt-submit" type="submit"
                                form="rmbt-gpt-description" disabled> Запустить генерацию описаний
                            </button>
                            <div id="rmbt-loading-box" class="rmbt-loading rmbt-hide">
                                <div class="rmbt-spinner"></div>
                                <div class="rmbt-loading-text">
                                    Генерация выполняется... <span id="rmbt-timer">0</span> сек
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-xs-12">
                        <div class="boxed rmbt-boxed">
                            <div id="rmbt-gpt-results-heading" class="heading_box rmbt-gpt-results-heading"> Результат
                                генерации:
                                <div class="heading_label rmbt-notes">
                                    Орієнтовна вартість 10 000 токенів 0.1$
                                </div>
                            </div>
                            <div id="rmbt-gpt-results"> </div>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-xs-12">
                        <div id="rmbt-gpt-logs" class="boxed rmbt-boxed">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>



<script>
    document.addEventListener('DOMContentLoaded', function() {

        /* Отправка формы*/
        const btnSendToBd = document.getElementById('send_to_bd');
        btnSendToBd.addEventListener('click', e => {
            const activePanel = document.querySelector('[role="tabpanel"]:not([hidden])');
            const activePanelAction = activePanel.dataset.action

            switch (activePanelAction) {
                case 'import':
                    sendToResultImport();
                    break;
                case 'generation':
                    sendToResultGeneration();
                    break;
            }
        });


        /* Загрузка описаний начало*/
        const fileUploadInput = document.getElementById('rmbt_file-upload');
        const fileUploadRowWrap = document.getElementById('rmbt_file-upload-row-wrap');
        const fileUploadReport = document.getElementById('rmbt-file-upload-report');
        let dataFileUploadRowJson = '';

        const fileInput = document.querySelector('.file-upload input');
        const fileBtn = document.getElementById('rmbt_file-upload_btn');
        const fileImportName = fileInput.files[0]?.name;

        fileInput.addEventListener('change', () => {
            fileBtn.textContent = fileImportName || 'Выберите файл';
        });

        fileUploadInput.addEventListener('change', async (e) => {
            const file = e.target.files[0];
            if (!file) return;

            dataFileUploadRowJson = JSON.parse(await file.text());
            if (dataFileUploadRowJson && dataFileUploadRowJson.length > 0) {
                fileUploadRowWrap.innerHTML = tableFileUploadRow();

                const table = fileUploadRowWrap.querySelector('#table-file-upload-row');
                const tbody = table.querySelector('tbody');

                let successCount = 0;
                let errorCount = 0;

                dataFileUploadRowJson.forEach(product => {
                    const newRow = tbody.insertRow();

                    if (product.id !== false) {
                        successCount++;

                        const values = {
                            id: product.id,
                            sku: product.sku,
                            description: product.content?.descriptionHtml ?? '',
                            attributes: product.content?.attributesHtml ?? '',
                        };

                        Object.values(values).forEach(value => {
                            const cell = newRow.insertCell();
                            cell.innerHTML = value;
                        });

                    } else {
                        errorCount++;

                        const cell = newRow.insertCell();
                        cell.colSpan = 4;
                        cell.textContent = product.errors || 'Ошибка';
                    }
                });

                // вывод счётчика
                if (fileUploadReport) {
                    fileUploadReport.textContent = 'Успешно: ' + successCount + ' | Ошибки: ' +
                        errorCount + ' | Всего: ' + dataFileUploadRowJson.length;
                }

            } else {
                fileUploadRowWrap.innerHTML = 'В даній генерації відсутні результати';
            }
        });

        function tableFileUploadRow() {
            const tableHTML = `
                <table id="table-file-upload-row" class="table-result" border="1">
                    <thead>
                        <tr>
                            <th id="id">id</th>
                            <th id="sku">sku</th>
                            <th id="description">Описание</th>
                            <th id="attributes">Атрибуты</th>
                        </tr>
                    </thead>
                    <tbody>
                    </tbody>
                </table>
                `;
            return tableHTML;
        }

        function sendToResultImport() {
            if (dataFileUploadRowJson != '') {
                const formData = new FormData();
                formData.append(
                    'session_id',
                    document.querySelector('input[name="session_id"]').value
                );

                formData.append(
                    'lang_id',
                    document.querySelector('input[name="lang_id"]').value
                );
                formData.append('import_name', fileImportName); 
                formData.append('data_import', JSON.stringify(dataFileUploadRowJson));

                return fetch(window.location.href, {
                        method: 'POST',
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        body: formData,
                    })
                    .then(res => res.json())
                    .then(data => {
                        if (data.errors.length > 0) {
                            errorMassageBox.textContent = data.errors;
                            rmbtErrorMassage.classList.remove('rmbt-hide');
                        }
                        if (data.data_import) {
                            rmbtSuccessMassage.classList.remove('rmbt-hide');
                        }

                    });
            }
        };
        /* Загрузка описаний конец*/


        /* Генерация описаний начало */
        const latest_generation = {$latest_generation};
        const generations = {$generations};
        let latestGenerationName = '';
        const form = document.getElementById('rmbt-gpt-description');
        const result = document.getElementById('rmbt-gpt-results');
        const resultHeading = document.getElementById('rmbt-gpt-results-heading');
        const headingNotes = resultHeading.querySelector('.rmbt-notes');
        const logs = document.getElementById('rmbt-gpt-logs');
        const startGeneration = document.getElementById('start_generation');
        const rmbtPrompt = document.getElementById('rmbt-prompt');
        const rmbtDate = document.getElementById('rmbt_date');
        const rmbtProductsId = document.getElementById('rmbt-products-id');
        const rmbtSuccessMassage = document.getElementById('rmbt_success_massage');
        const rmbtErrorMassage = document.getElementById('rmbt_error_massage');
        const errorMassageBox = rmbtErrorMassage.querySelector('#rmbt_error_massage_box');
        const formatter = new Intl.NumberFormat('ru-RU');

        const loadingBox = document.getElementById('rmbt-loading-box');
        const timerEl = document.getElementById('rmbt-timer');

        let timerInterval = null;
        let seconds = 0;


        if (latest_generation) {
            showResult(latest_generation);
        }

        showGenerations(generations);

        // btnSendToBd.addEventListener('click', e => {
        // if (latestGenerationName != '') {
        //     const formData = new FormData();
        //     formData.append(
        //         'session_id',
        //         document.querySelector('input[name="session_id"]').value
        //     );

        //     formData.append(
        //         'lang_id',
        //         document.querySelector('input[name="lang_id"]').value
        //     );
        //     formData.append('generation_name', latestGenerationName);
        //     formData.append('save_generation', 1);

        //     return fetch(window.location.href, {
        //             method: 'POST',
        //             headers: {
        //                 'X-Requested-With': 'XMLHttpRequest'
        //             },
        //             body: formData,
        //         })
        //         .then(res => res.json())
        //         .then(data => {
        //             if (data.errors.length > 0) {
        //                 errorMassageBox.textContent = data.errors;
        //                 rmbtErrorMassage.classList.remove('rmbt-hide');
        //             }
        //             if (data.save_generation) {
        //                 rmbtSuccessMassage.classList.remove('rmbt-hide');
        //             }

        //         });
        // }
        // });


        logs.addEventListener('click', function(e) { // download generation

            const btn = e.target.closest('button');
            if (!btn) return;
            const generationTr = btn.closest('tr');

            if (btn.hasAttribute('data-rmbt-download')) {
                changeGeneration(generationTr.firstElementChild.textContent, 'download')
                    .then(data => {
                        if (data) {
                            if (data.errors.length > 0) {
                                errorMassageBox.textContent = data.errors;
                                rmbtErrorMassage.classList.remove('rmbt-hide');
                            }
                            showResult(data.generation);
                        }
                    });
            }

            if (btn.hasAttribute('data-rmbt-del')) {
                changeGeneration(generationTr.firstElementChild.textContent, 'del')
                    .then(data => {
                        if (data.errors.length > 0) {
                            errorMassageBox.textContent = data.errors;
                            rmbtErrorMassage.classList.remove('rmbt-hide');
                        }
                        if (data.generation == 'is_del') {
                            generationTr.remove();
                        }
                    });
            }
        });


        const updateState = () => {
            const hasProducts = rmbtProductsId.value.trim();
            const hasPrompt = rmbtPrompt.value.trim();
            const hasDate = rmbtDate.valueAsDate;

            rmbtDate.disabled = hasProducts;

            startGeneration.disabled = hasProducts ?
                false :
                !(hasPrompt && hasDate);
        };

        updateState();

        ['input', 'change'].forEach(ev => {
            rmbtPrompt.addEventListener(ev, updateState);
            rmbtDate.addEventListener(ev, updateState);
            rmbtProductsId.addEventListener(ev, updateState);
        });

        form.addEventListener('submit', function(e) {
            e.preventDefault();

            startLoading(); // ← запускаем индикатор

            const formData = new FormData(form);
            formData.append('start_generation', 1);

            fetch(form.action || window.location.href, {
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
                .then(json => {

                    stopLoading(); // ← останавливаем

                    if (json) {
                        if (json.errors.length > 0) {
                            errorMassageBox.textContent =
                                "Під час генерації деяких описів виникли помилки";
                            rmbtErrorMassage.classList.remove('rmbt-hide');
                        }

                        showResult(json);
                        showGenerations(json.meta.generations);
                    }
                })
                .catch(err => {
                    stopLoading(); // ← обязательно останавливаем при ошибке
                    console.error(err);

                    errorMassageBox.textContent = "Ошибка соединения или сервер не отвечает";
                    rmbtErrorMassage.classList.remove('rmbt-hide');
                });
        });

        function changeGeneration(generation, event) {
            const formData = new FormData();
            formData.append(
                'session_id',
                document.querySelector('input[name="session_id"]').value
            );

            formData.append(
                'lang_id',
                document.querySelector('input[name="lang_id"]').value
            );
            formData.append('generation_name', generation);

            if (event == 'del') {
                formData.append('del_generation', 1);
            } else if (event == 'download') {
                formData.append('download_generation', 1);
            }

            return fetch(window.location.href, {
                    method: 'POST',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: formData,
                })
                .then(res => res.json())
                .then(data => {
                    return data;
                });
        }

        function tableResult() {
            const tableHTML = `
                <table id="table-result" class="table-result" border="1">
                    <thead>
                        <tr>
                            <th id="id">id товара</th>
                            <th id="name">Название товара</th>
                            <th id="description">Сгенерированный промпт</th>
                            <th id="total_tokens">Потраченные токены</th>
                        </tr>
                    </thead>
                    <tbody>
                    </tbody>
                </table>
                `;
            return tableHTML;
        }

        function showResult(generation) {

            let data;
            if (isPlainObject(generation.data)) {
                data = Object.values(generation.data)
            } else {
                data = generation.data;
            }

            const meta = generation.meta;
            let generationName = document.getElementById('generation_name');

            if (!generationName) {
                generationName = document.createElement('div');
                generationName.classList.add('generation-name', 'heading_label');
                generationName.id = 'generation_name';
            }

            generationName.textContent = generation.generated_at;
            // resultHeading.append(generationName);
            resultHeading.insertBefore(generationName, headingNotes);

            // здесь для того что бы гарантировать что в БД попадёт именно та генерация которая показана в таблице
            latestGenerationName = generation.generated_at;
            btnSendToBd.disabled = false;

            if (data && data.length > 0) {
                result.innerHTML = tableResult();
                const table = result.querySelector('#table-result');
                const tbody = result.querySelector('table tbody');
                const ths = table.querySelectorAll('th');
                Object.values(data).forEach(product => {
                    const newRow = tbody.insertRow();
                    if (product.id !== false) {
                        ths.forEach(th => {
                            const cell = newRow.insertCell();
                            cell.textContent = product[th.id] ?? '';
                        });
                    } else {
                        const cell = newRow.insertCell();
                        cell.colSpan = ths.length;
                        cell.textContent = product.errors || 'Ошибка';
                    }
                });
            } else {
                result.innerHTML =
                    'В даній генерації відсутні результати';
            }

            if (meta) {
                const metaData = document.createElement('div');
                let totalTokens = document.getElementById('rmbt-total-token');

                if (meta.total_tokens) {
                    if (!totalTokens) {
                        totalTokens = document.createElement('div');
                        totalTokens.classList.add('heading_label', 'rmbt-total-token');
                        totalTokens.id = 'rmbt-total-token';
                    }
                    if (meta.total_tokens === false) {
                        totalTokens.textContent = 'Кількість витрачених токенів невідома';
                    } else {
                        totalTokens.textContent = 'Всього витрачено ' + formatter.format(meta.total_tokens) +
                            ' токенів';
                    }
                }

                if (totalTokens) {
                    resultHeading.append(totalTokens);
                }
            }
        }

        function tableGenerations(title = true) {
            if (title) {
                return `<div id="rmbt-gpt-logs-heading" class="heading_box rmbt-gpt-logs-heading"> Все генерации </div>
                    <div id="rmbt-gpt-logs-table">
                        <table id="table-logs" class="table-logs" border="1">
                            <thead>
                                <tr>
                                    <th id="id">Файл генерации</th>
                                    <th id="name">Операции с файлом</th>
                                </tr>
                            </thead>
                            <tbody>  </tbody>
                        </table>
                    </div>`;
            } else {
                return `<div id="rmbt-gpt-logs-heading" class="heading_box rmbt-gpt-logs-heading"> У вас ещё нет генераций </div>`;
            }
        }

        function showGenerations(generations) {
            if (Array.isArray(generations) && generations.length > 0) {

                logs.innerHTML = tableGenerations();
                const table = logs.querySelector('#table-logs');
                const tbody = table.querySelector('tbody');
                const ths = table.querySelectorAll('th');

                generations.forEach((generation) => {
                    const newRow = tbody.insertRow();
                    if (generation !== false) {
                        ths.forEach((th, i) => {
                            const cell = newRow.insertCell();
                            if (i == 0) {
                                cell.textContent = generation ?? '';
                            } else if (i == 1) {
                                cell.innerHTML = '<button data-rmbt-download class="btn btn_mini btn-info"><span class="fn_plus"> {include file="svg_icon.tpl" svgId="plus"} </span></button>';
                                cell.innerHTML += '<button data-rmbt-del class="btn btn_mini btn-danger"><span class="fn_minus">{include file="svg_icon.tpl" svgId="minus"}</span></button>';
                            }
                        });
                    }
                })
            } else {
                logs.innerHTML = tableGenerations(false);
            }
        }

        function isPlainObject(value) {
            return typeof value === 'object' && value !== null && !Array.isArray(value);
        }

        function startLoading() {
            seconds = 0;
            timerEl.textContent = 0;
            loadingBox.classList.remove('rmbt-hide');

            startGeneration.disabled = true;

            timerInterval = setInterval(() => {
                seconds++;
                timerEl.textContent = seconds;

            }, 1000);
        }

        function stopLoading() {
            clearInterval(timerInterval);
            loadingBox.classList.add('rmbt-hide');
            startGeneration.disabled = false;
        }

        function sendToResultGeneration() {
            if (latestGenerationName != '') {
                const formData = new FormData();
                formData.append(
                    'session_id',
                    document.querySelector('input[name="session_id"]').value
                );

                formData.append(
                    'lang_id',
                    document.querySelector('input[name="lang_id"]').value
                );
                formData.append('generation_name', latestGenerationName);
                formData.append('save_generation', 1);

                return fetch(window.location.href, {
                        method: 'POST',
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        body: formData,
                    })
                    .then(res => res.json())
                    .then(data => {
                        if (data.errors.length > 0) {
                            errorMassageBox.textContent = data.errors;
                            rmbtErrorMassage.classList.remove('rmbt-hide');
                        }
                        if (data.save_generation) {
                            rmbtSuccessMassage.classList.remove('rmbt-hide');
                        }

                    });
            }
        }


        /* Генерация описаний конец */


    });
</script>