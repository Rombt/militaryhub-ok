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


        <input type=hidden name="session_id" value="{$smarty.session.id}">
        <input type="hidden" name="lang_id" value="{$lang_id}" />
    </div>
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
                        <input id="rmbt-feed-discount" class="form-control" type="number" name="rmbt-feed-discount" min="0" max="100" step="1" value="{$feed_prom_discount|default:0}" placeholder="Введите процент">
                        <span class="input-group-addon">%</span>
                    </div>
                </label>
            </div>

            <div class="rmbt-list-row col-lg-4 col-md-6">
                <label for="rmbt-feed-min-discount">
                    <span class="heading_label">
                        Минимальная скидка для товаров в фиде, %
                    </span>
                    <div class="input-group">
                        <input id="rmbt-feed-min-discount" class="form-control" type="number" name="feed_min_discount" min="0" max="100" step="1" value="{$feed_prom_min_discount|default:0}">
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


<script>
    document.addEventListener('DOMContentLoaded', function() {

        const rmbtErrorMassage = document.getElementById('rmbt_error_massage');
        const errorMassageBox = document.getElementById('rmbt_error_massage_box');

        const successMassage = document.getElementById('rmbt_success_massage_box');
        const successMassageBox = document.getElementById('rmbt_success_massage');

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

            console.log(formData);

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


    });
</script>
