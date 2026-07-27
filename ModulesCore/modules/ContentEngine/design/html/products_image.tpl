<div class="row">
    <div class="col-lg-12 col-md-7">
        <div class="wrap_heading rmbt-heading">
            <div class="box_heading heading_page rmbt-heading_page">
                {$title}
            </div>

            <button id="del_photo_from_products" class="btn btn_small btn-danger" type="button" disabled>
                {include file='svg_icon.tpl' svgId='checked'}
                Удалить фото из товаров
            </button>

            <button id="add_photo_to_products" class="btn btn_small btn_blue" type="button" disabled>
                {include file='svg_icon.tpl' svgId='checked'}
                Добавить фото в товары
            </button>

        </div>
        <div id="rmbt_success_massage" class="col-lg-12 col-md-12 col-sm-12 rmbt-hide">
            <div class="boxed boxed_success">
                <div class="heading_box">
                    Описания товаров добавлены в базу данных
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
<form id="rmbt-products-image" class="fn_form_list" method="post" action="">
    <input type=hidden name="session_id" value="{$smarty.session.id}">
    <input type="hidden" name="lang_id" value="{$lang_id}" />
    <div class="row">
        <div class="col-xs-12">
            <div class="boxed rmbt-boxed">
                <div class="heading_box">
                    <div class="heading_label rmbt-notes">
                        <span> </span>
                    </div>
                </div>
                <div class="rmbt-field-wrap col-lg-12 col-md-12 rmbt-products-image-data-fields">
                    <label class="col-lg-3 col-md-12  rmbt-date" for="rmbt-date">
                        <span class="heading_label">Продукты созданные до этой даты обработаны не будут</span>
                        <input id="rmbt_date" class="form-control" type="date" name="rmbt-date">
                    </label>
                    <label class="col-lg-3 col-md-12  rmbt-products-id" for="rmbt-date">
                        <span class="heading_label">
                            Введите id продуктов для которых нужно собрать фото
                        </span>
                        <textarea id="rmbt-products-id" class="form-control" name="rmbt-products-id"></textarea>
                    </label>

                    <label class="col-lg-6 col-md-12 rmbt-brands" for="rmbt-brands">
                        <span class="heading_label">Введите названия брендов, на английском, для товаров которых нужно
                            собрать фото</span>
                        <textarea class="form-control" name="rmbt-brands" id="rmbt-brands"></textarea>
                    </label>

                </div>
                <button id="start_generation" class="btn btn_small btn-info rmbt-submit" type="submit"
                    form="rmbt-products-image" disabled> Запустить генерацию файла задания </button>
            </div>
        </div>
    </div>
</form>

<div class="row">
    <div class="col-lg-6 col-md-12 pr-0">
        <div class="boxed rmbt-boxed">
            <div id="rmbt-gpt-results-heading" class="heading_box rmbt-gpt-results-heading"> Задание для сбора фото:
                <div id="rmbt-task-name" class="heading_label rmbt-task-name"></div>
                <div class="heading_label rmbt-notes">
                    <button id="rmbt-copy-clipboard" type="button" class="btn btn_small btn_blue float-md-right">
                        <span>Скопіювати в буфер обміну</span>
                    </button>
                </div>
            </div>
            <div id="rmbt-unique-sku-count" class="heading_label rmbt-notes"></div>
            <div id="rmbt-task-results" class="rmbt-task-results"></div>
        </div>

        <div class="row">
            <div class="col-xs-12">
                <div id="rmbt-tasks-logs" class="boxed rmbt-boxed">
                </div>
            </div>
        </div>

    </div>
    <div class="col-lg-6 col-md-12 pr-0">
        <div class="boxed rmbt-boxed">
            <div id="rmbt-gpt-results-heading" class="heading_box rmbt-gpt-results-heading"> Обработка результаты сбора
                фото:
                <div class="heading_label rmbt-notes">
                    <button id="open-folder" type="button" class="btn btn_small btn_blue float-md-right">
                        <span>Открыть папку с результатами</span>
                    </button>
                    <input type="file" id="folder-input" webkitdirectory directory multiple style="display:none" />
                </div>
            </div>
            <div id="rmbt-tree-results" class="rmbt-task-results"></div>
        </div>


        <div class="row">
            <div class="col-xs-12">

                <div id="rmbt-added_imgs_cont" class="boxed rmbt-boxed rmbt-hide"></div>

                <div id="rmbt-added_imgs_files_cont" class="boxed rmbt-boxed"></div>
            </div>
        </div>
    </div>
</div>




<script>
    document.addEventListener('DOMContentLoaded', function() {

        const latest_task = {$latest_task};
        const generated_at = '{$generated_at}';
        const tasks = {$tasks};

        const errorMassageBox = document.getElementById('rmbt_error_massage');
        const successMassageBox = document.getElementById('rmbt_success_massage');

        const form = document.getElementById('rmbt-products-image');
        const startGeneration = document.getElementById('start_generation');
        const rmbtDate = document.getElementById('rmbt_date');
        const rmbtBrands = document.getElementById('rmbt-brands');
        const rmbtProductIds = document.getElementById('rmbt-products-id');
        const rmbtTaskResults = document.getElementById('rmbt-task-results');
        const rmbtTaskName = document.getElementById('rmbt-task-name');
        const rmbtUniqueSkuCount = document.getElementById('rmbt-unique-sku-count');
        const logs = document.getElementById('rmbt-tasks-logs');
        const btnCopyClipboard = document.getElementById('rmbt-copy-clipboard');
        let rmbtTaskJson = null;

        let TaskName = '';
        let latestTaskName = '';

        if (latest_task) {
            showTask(JSON.stringify(latest_task, null, 2), generated_at);
        }

        showTasks(tasks);


        btnCopyClipboard.addEventListener('click', async () => { // copy task to clipboard
            if (!rmbtTaskJson) {
                rmbtTaskJson = document.getElementById('rmbt-task-json');
                if (!rmbtTaskJson) {
                    return;
                }
            }
            const text = rmbtTaskJson.innerText;

            copyTextToClipboard(text);
        });


        logs.addEventListener('click', function(e) { // download / del task

            const btn = e.target.closest('button');
            if (!btn) return;
            const generationTr = btn.closest('tr');

            ////!!!!!!!!!!!!!!!!!!!!!!   не работает!!  разобраться!!!
            if (btn.hasAttribute('data-rmbt-download')) {
                changeGeneration(generationTr.firstElementChild.textContent, 'download')
                    .then(data => {
                        if (data) {
                            if (data.errors.length > 0) {
                                errorMassageBox.textContent = data.errors;
                                errorMassageBox.classList.remove('rmbt-hide');
                            } else {
                                successMassageBox.classList.remove('rmbt-hide');
                                showTask(JSON.stringify(data.task, null, 2), data.task
                                    .generated_at);
                            }

                        }
                    });
            }

            if (btn.hasAttribute('data-rmbt-del')) {
                changeGeneration(generationTr.firstElementChild.textContent, 'del')
                    .then(data => {

                        if (data.errors.length > 0) {
                            errorMassageBox.textContent = data.errors;
                            errorMassageBox.classList.remove('rmbt-hide');
                        }
                        if (data.task == 'is_del') {
                            generationTr.remove();
                        }
                    });
            }
        });

        const updateState = () => {
            const hasProductIds = rmbtProductIds.value.trim().length > 0;
            const hasBrands = rmbtBrands.value.trim().length > 0;
            const hasDate = !!rmbtDate.valueAsDate;

            rmbtProductIds.disabled = hasBrands;
            rmbtBrands.disabled = hasProductIds;
            rmbtDate.disabled = hasProductIds;

            // startGeneration.disabled = !(hasProductIds || hasDate);
            startGeneration.disabled = !(hasProductIds || hasBrands);
        };

        updateState();

        ['input', 'change'].forEach(ev => {
            rmbtProductIds.addEventListener(ev, updateState);
            rmbtBrands.addEventListener(ev, updateState);
            rmbtDate.addEventListener(ev, updateState);
        });

        form.addEventListener('submit', function(e) { // start tasks
            e.preventDefault();

            const formData = new FormData(form);

            formData.append(
                'session_id',
                document.querySelector('input[name="session_id"]').value
            );

            formData.append(
                'lang_id',
                document.querySelector('input[name="lang_id"]').value
            );

            formData.append('start_task', 1);

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
                            errorMassageBox.textContent =
                                "Під час генерації завдання виникли помилки";
                            errorMassageBox.classList.remove('rmbt-hide');
                        } else {
                            successMassageBox.classList.remove('rmbt-hide');

                            showTask(JSON.stringify(json.data, null, 2),
                                json.generated_at,
                                json.meta.uniqueSkuCount
                            );
                            showTasks(json.meta.tasks);

                        }

                    }
                })
                .catch(err => {
                    console.error(err);
                })
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
            formData.append('task_name', generation);

            if (event == 'del') {
                formData.append('del_task', 1);
            } else if (event == 'download') {
                formData.append('download_task', 1);
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

        function tableTasks(title = true) {
            if (title) {
                return `<div id="rmbt-gpt-logs-heading" class="heading_box rmbt-gpt-logs-heading"> Все задачи </div>
                    <div id="rmbt-gpt-logs-table">
                        <table id="table-logs" class="table-logs" border="1">
                            <thead>
                                <tr>
                                    <th id="id">Файл генерации</th>
                                    <th id="name">Операции с файлом</th>
                                </tr>
                            </thead>
                            <tbody> </tbody>
                        </table>
                    </div>`;
            } else {
                return `<div id="rmbt-gpt-logs-heading" class="heading_box rmbt-gpt-logs-heading"> У вас ещё нет задач </div>`;
            }
        }

        function showTasks(tasks) {
            if (Array.isArray(tasks) && tasks.length > 0) {

                logs.innerHTML = tableTasks();
                const table = logs.querySelector('#table-logs');
                const tbody = table.querySelector('tbody');
                const ths = table.querySelectorAll('th');

                tasks.forEach((generation) => {
                    const newRow = tbody.insertRow();
                    if (generation !== false) {
                        ths.forEach((th, i) => {
                            const cell = newRow.insertCell();
                            if (i == 0) {
                                cell.textContent = generation ?? '';
                            } else if (i == 1) {
                                cell.innerHTML = '<button data-rmbt-download class="btn btn_mini btn-info"><span class="fn_plus">{include file="svg_icon.tpl" svgId="plus"} </span></button>';
                                cell.innerHTML += '<button data-rmbt-del class="btn btn_mini btn-danger"><span class="fn_minus">{include file="svg_icon.tpl" svgId="minus"}</span></button>';
                            }
                        });
                    }
                })
            } else {
                logs.innerHTML = tableTasks(false);
            }
        }

        function showTask(task, generatedAt, qtyUniqueSku = []) {
            rmbtTaskName.innerHTML = generatedAt;
            UniqueSkuShow(qtyUniqueSku);
            rmbtTaskResults.innerHTML = '<pre><code id="rmbt-task-json">' + task + '</code></pre>';
        }

        function UniqueSkuShow(qtyUniqueSku) {

            let uniqueSkuHtml = '';

            for (var brandName in qtyUniqueSku) {
                if (Object.prototype.hasOwnProperty.call(qtyUniqueSku, brandName)) {
                    var qty = qtyUniqueSku[brandName];
                    uniqueSkuHtml += '<li>' + brandName + ' = <strong>' + qty + '</strong> sku</li> ';
                }
            }

            rmbtUniqueSkuCount.innerHTML = '<ul>' + uniqueSkuHtml + '</ul>';
        }

        /*
         * т.к. браузеры запрещают копировать текст на страницах без SSL
         */
        async function copyTextToClipboard(text) {
            if (navigator.clipboard && window.isSecureContext) {
                try {
                    await navigator.clipboard.writeText(text);
                    console.log('Скопировано (clipboard API)');
                    return;
                } catch (e) {
                    console.warn('Clipboard API failed, fallback to execCommand', e);
                }
            }

            const textarea = document.createElement('textarea');
            textarea.value = text;

            textarea.style.position = 'fixed';
            textarea.style.opacity = '0';

            document.body.appendChild(textarea);
            textarea.select();

            document.execCommand('copy');

            document.body.removeChild(textarea);
            console.log('Скопировано (fallback)');
        }

        /*================  обработка результатов   =================*/



        const idTable = 'table-added_imgs';
        const added_imgs = {$added_imgs};
        const treeResults = document.getElementById('rmbt-tree-results');
        const addedImgsFilesCont = document.getElementById('rmbt-added_imgs_files_cont');
        const addedImgsCont = document.getElementById('rmbt-added_imgs_cont');
        const addPhotoToProducts = document.getElementById('add_photo_to_products');
        const delPhotoFromProducts = document.getElementById('del_photo_from_products');
        let displayTree;
        let tree;
        let currentTreeFile;

        AddedImgs(added_imgs);

        document.getElementById('open-folder').onclick = () => {
            document.getElementById('folder-input').click();
        };

        document.getElementById('folder-input').addEventListener('change', (e) => {
            const files = Array.from(e.target.files);
            displayTree = buildDisplayTree(files);
            tree = buildTree(files);
            treeResults.innerHTML = '<pre><code id ="rmbt-tree-results-json">' +
                JSON.stringify(displayTree, null, 2) +
                '</code></pre> ';

            addPhotoToProducts.disabled = false;

        });

        addPhotoToProducts.addEventListener('click', e => {
            const formData = new FormData();

            formData.append(
                'session_id',
                document.querySelector('input[name="session_id"]').value
            );

            formData.append(
                'lang_id',
                document.querySelector('input[name="lang_id"]').value
            );

            formData.append('add_photo_to_products', JSON.stringify(tree));

            fetch(form.action || window.location.href, {
                    method: 'POST',
                    body: formData
                })
                .then(res => res.json())
                .then(data => {
                    // console.log(data);
                });

        });

        delPhotoFromProducts.addEventListener('click', e => {
            const formData = new FormData();

            formData.append(
                'session_id',
                document.querySelector('input[name="session_id"]').value
            );

            formData.append(
                'lang_id',
                document.querySelector('input[name="lang_id"]').value
            );

            formData.append('del_photo_from_products', true);
            formData.append('file_name', currentTreeFile);

            fetch(form.action || window.location.href, {
                    method: 'POST',
                    body: formData
                })
                .then(res => res.json())
                .then(data => {

                    if (data.imgs_was_deleted) {
                        addedImgsCont.classList.add('rmbt-hide');
                        addedImgsCont.innerText = '';

                        const rows = Array.from(document.querySelectorAll('#' + idTable + ' tr'));
                        const delRow = rows.find(row =>
                            Array.from(row.cells).some(cell => cell
                                .textContent
                                .includes(currentTreeFile)));

                        if (delRow) delRow.remove();

                    }

                });

        });

        addedImgsFilesCont.addEventListener('click', function(e) { // download / del added_img

            const btn = e.target.closest('button');
            if (!btn) return;

            const addedImgsIonTr = btn.closest('tr');


            if (btn && btn.hasAttribute('data-rmbt-download')) {
                selectAddedImg(addedImgsIonTr.firstElementChild.textContent, 'download')
                    .then(data => {
                        if (data) {
                            if (data.errors.length > 0) {
                                errorMassageBox.textContent = data.errors;
                                errorMassageBox.classList.remove('rmbt-hide');
                            } else {
                                successMassageBox.classList.remove('rmbt-hide');

                                addedImgsCont.classList.remove('rmbt-hide');
                                showResult(addedImgsCont, JSON.stringify(data.results, null, 2));

                                tree = data.results;
                                currentTreeFile = addedImgsIonTr.firstElementChild.textContent;
                                delPhotoFromProducts.disabled = false;
                            }

                        }
                    });
            }

            if (btn.hasAttribute('data-rmbt-del')) {
                selectAddedImg(addedImgsIonTr.firstElementChild.textContent, 'del')
                    .then(data => {

                        if (data.errors.length > 0) {
                            errorMassageBox.textContent = data.errors;
                            errorMassageBox.classList.remove('rmbt-hide');
                        }
                        if (data.del_addedImgs == 'is_del') {
                            addedImgsIonTr.remove();
                        }
                    });
            }
        });


        function selectAddedImg(addedImgs, event) {
            const formData = new FormData();
            formData.append(
                'session_id',
                document.querySelector('input[name="session_id"]').value
            );

            formData.append(
                'lang_id',
                document.querySelector('input[name="lang_id"]').value
            );
            formData.append('addedImgs_name', addedImgs);

            if (event == 'del') {
                formData.append('del_addedImgs_file', 1);
            } else if (event == 'download') {
                formData.append('download_addedImgs', 1);
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



        function AddedImgs(added_imgs) {
            if (Array.isArray(added_imgs) && added_imgs.length > 0) {

                addedImgsFilesCont.innerHTML = tableAddedImgsFiles(true);
                const table = document.getElementById(idTable);
                const tbody = table.querySelector('tbody');
                const ths = table.querySelectorAll('th');


                table
                added_imgs.forEach((added_img) => {
                    const newRow = tbody.insertRow();
                    if (added_img !== false) {
                        ths.forEach((th, i) => {
                            const cell = newRow.insertCell();
                            if (i == 0) {
                                cell.textContent = added_img ?? '';
                            } else if (i == 1) {
                                cell.innerHTML =
                                    '<button data-rmbt-download class="btn btn_mini btn-info"><span class="fn_plus">{include file="svg_icon.tpl" svgId="plus"} </span></button>';
                                    cell.innerHTML += '<button data-rmbt-del class="btn btn_mini btn-danger disabled"><span class="fn_minus">{include file="svg_icon.tpl" svgId="minus"}</span></button>';
                            }
                        });
                    }
                })
            } else {
                addedImgsFilesCont.innerHTML = tableAddedImgs();
            }
        }

        function tableAddedImgs(title = false, str = '', ) {
            if (title) {
                const extra = (str && str !== '') ? str : '';
                return '<div id="rmbt-added_imgs" class="heading_box rmbt-gpt-logs-heading"> ID ранее добавленных изображений товаров' +
                    extra +
                    '</div>';
            } else {
                return `<div id="rmbt-added_imgs" class="heading_box rmbt-gpt-logs-heading"> У вас ещё нет ранее добавленных изображений товаров </div>`;
            }
        }

        function tableAddedImgsFiles(title = true) {
            if (title) {
                return `<div id="rmbt-added_imgs" class="heading_box rmbt-gpt-logs-heading"> Файлы добавленных изображений товаров </div>
                    <div id="rmbt-gpt-logs-table">
                        <table id="` + idTable + `" class="table-logs" border="1">
                            <thead>
                                <tr>
                                    <th id="id">Файл добавленных изображений</th>
                                    <th id="name">Операции с файлом</th>
                                </tr>
                            </thead>
                            <tbody> </tbody>
                        </table>
                    </div>`;
            } else {
                return `<div id="rmbt-added_imgs" class="heading_box rmbt-gpt-logs-heading"> У вас ещё нет задач </div>`;
            }
        }

        function showResult(cont, result, added_imgs_files = '') {

            let str = '';
            if (added_imgs_files != '') {
                str += '<span class="heading_label rmbt-task-name">' + added_imgs_files + '</span>';
            }

            let html = tableAddedImgs(title = true, str, );
            html += ' <pre><code id = "rmbt-task-json" > ' + result + ' </code></pre> ';
            cont.innerHTML = html;
        }

        function buildDisplayTree(files) {
            const root = {};

            files.forEach(file => {
                const parts = file.webkitRelativePath.split('/');
                const fileName = parts.pop();

                let current = root;

                parts.forEach(folder => {
                    if (!current[folder]) {
                        current[folder] = {};
                    }

                    current = current[folder];
                });

                if (!current.files) {
                    current.files = [];
                }

                current.files.push(fileName);
            });

            return root;
        }

        function buildTree(files) {
            const root = {
                root: {
                    type: 'folder',
                    children: {}
                }
            };

            files.forEach(file => {
                const parts = file.webkitRelativePath.split('/').slice(1); // убираем имя верхней папки

                let current = root.root.children;

                parts.forEach((part, index) => {
                    const isFile = index === parts.length - 1;

                    if (isFile) {
                        current[part] = {
                            type: 'file',
                            size: file.size,
                            path: file.webkitRelativePath
                        };
                    } else {
                        if (!current[part]) {
                            current[part] = {
                                type: 'folder',
                                children: {}
                            };
                        }

                        current = current[part].children;
                    }
                });
            });

            return root;
        }




    })
</script>