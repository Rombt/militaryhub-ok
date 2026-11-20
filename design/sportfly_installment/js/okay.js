let galleryTop = null;
let galleryThumbs = null;

/* Начальное кол-во для смены в карточке и корзине */
okay.amount = 1;

/* Аяксовая корзина */
$(document).on('submit', '.fn_variants', function (e) {
    e.preventDefault();
    var variant,
        amount,
        variantName,
        variantPrice;
    /* Вариант */
    if ($(this).find('input[name=variant]:checked').length > 0) {
        variantName = $(this).find('input[name=variant]:checked').data('name');
        variantPrice = $(this).find('input[name=variant]:checked').data('price');
        variant = $(this).find('input[name=variant]:checked').val();
    } else if ($(this).find('input[name=variant]').length > 0) {
        variantName = $(this).find('input[name=variant]').data('name');
        variantPrice = $(this).find('input[name=variant]:checked').data('price');
        variant = $(this).find('input[name=variant]').val();
    } else if ($(this).find('select[name=variant]').length > 0) {
        variantName = $(this).find('select[name=variant] option:selected').text();
        variantPrice = $(this).find('select[name=variant] option:selected').data('price');
        variant = $(this).find('select[name=variant]').val();
    }
    /* Кол-во */
    if ($(this).find('input[name=amount]').length > 0) {
        amount = $(this).find('input[name=amount]').val();
    } else {
        amount = 1;
    }

    let dataLayerItem = {
        item_name: $(this).data('name'),
        item_id: variant,
        price: variantPrice,
        item_brand: $(this).data('brand'),
        item_variant: variantName,
        quantity: amount,
        discount: "0",
        coupon: ""
    };

    if ($(this).data('categories')){
        $(this).data('categories').split(';').forEach((productCategory, index) => {
            let key = index > 0 ? 'item_category'+(index+1) : 'item_category';
            dataLayerItem[key] = productCategory;
        });
    }

    window.dataLayer = window.dataLayer || [];
    window.dataLayer.push({
        event: "add_to_cart",
        ecommerce: {
            value: variantPrice * amount,
            currency: okay.currency || 'UAH',
            items: [
                dataLayerItem
            ],
        }
    });

    /* ajax запрос */
    $.ajax({
        url: "ajax/cart.php",
        data: {
            variant: variant,
            amount: amount
        },
        dataType: 'json',
        success: function (data) {
            $('#cart_informer').html(data);
        }
    });
    /* Улеталка */
    transfer($('#cart_informer'), $(this));
});


/* Смена варианта в превью товара и в карточке */
$(document).on('change', '.fn_variant', function () {
    var selected = $(this).hasClass('variant_select') ? $(this).children(":selected") : $(this),
        parent = selected.closest('.fn_product'),
        price = parent.find('.fn_price'),
        cprice = parent.find('.fn_old_price'),
        sku = parent.find('.fn_sku'),
        stock = parseInt(selected.data('stock')),
        amount = parent.find('input[name="amount"]'),
        camoun = parseInt(amount.val()),
        units = selected.data('units');
    price.html(formatNumber(selected.data('price')));
    amount.data('max', stock);
    /* Количество товаров */
    if (stock < camoun) {
        amount.val(stock);
    } else if (okay.amount > camoun) {
        amount.val(okay.amount);
    } else if (isNaN(camoun)) {
        amount.val(okay.amount);
    }
    /* Цены */
    if (selected.data('cprice')) {
        cprice.html(formatNumber(selected.data('cprice')));
        cprice.parent().removeClass('hidden');
    } else {
        cprice.parent().addClass('hidden');
    }
    /* Артикул */
    if (typeof (selected.data('sku')) != 'undefined') {
        sku.text(selected.data('sku'));
        sku.parent().removeClass('hidden');
    } else {
        sku.text('');
        sku.parent().addClass('hidden');
    }
    /* Наличие на складе */
    if (stock == 0) {
        parent.find('.fn_not_stock').removeClass('hidden');
        parent.find('.fn_in_stock').addClass('hidden');
        parent.find('.fn_product_amount').addClass('hidden');
    } else {
        parent.find('.fn_in_stock').removeClass('hidden');
        parent.find('.fn_not_stock').addClass('hidden');
        parent.find('.fn_product_amount').removeClass('hidden');
    }
    /* Предзаказ */
    if (stock == 0 && okay.is_preorder) {
        parent.find('.fn_is_preorder').removeClass('hidden');
        parent.find('.fn_is_stock, .fn_not_preorder').addClass('hidden');
    } else if (stock == 0 && !okay.is_preorder) {
        parent.find('.fn_not_preorder').removeClass('hidden');
        parent.find('.fn_is_stock, .fn_is_preorder').addClass('hidden');
    } else {
        parent.find('.fn_is_stock').removeClass('hidden');
        parent.find('.fn_is_preorder, .fn_not_preorder').addClass('hidden');
    }

    if (typeof (units) != 'undefined') {
        parent.find('.fn_units').text(', ' + units);
    } else {
        parent.find('.fn_units').text('');
    }
});

/* Количество товара в карточке и корзине */
$(document).on('click', '.fn_product_amount span', function () {
    var input = $(this).parent().find('input'),
        action;
    if ($(this).hasClass('plus')) {
        action = 'plus';
    } else if ($(this).hasClass('minus')) {
        action = 'minus';
    }
    amount_change(input, action);
});
$(()=>{
    if ($('#fn_purchases')) {
        $('#fn_purchases').on('change', '.fn_product_amount input', (e)=>{
            let input = $(e.currentTarget);
            let prevAmount = input.data('amount');
            let newAmount = input.val();
            let diff = newAmount - prevAmount;
            if (diff <= 0) return;
            let purchaseItem = input.closest('.purchase_tr');
            let purchaseNameEl = purchaseItem.find('.purchase_details .purchase_name');

            let dataLayerItem = {
                item_name: purchaseNameEl.text(),
                item_id: input.data('id'),
                price: input.data('price'),
                item_brand: purchaseNameEl.data('brand') || '',
                item_variant: input.data('variantName') || '',
                quantity: diff,
                discount: "0",
                coupon: ""
            };

            if (purchaseNameEl.data('category')){
                purchaseNameEl.data('category').split(';').forEach((productCategory, index) => {
                    let key = index > 0 ? 'item_category'+(index+1) : 'item_category';
                    dataLayerItem[key] = productCategory;
                });
            }


            window.dataLayer = window.dataLayer || [];
            window.dataLayer.push({
                event: "add_to_cart",
                ecommerce: {
                    value: input.data('price') * diff,
                    currency: okay.currency || 'UAH',
                    items: [
                        dataLayerItem
                    ],
                }
            });
        });
    }
});

/* Функция добавления / удаления в папку сравнения */
$(document).on('click', '.fn_comparison', function (e) {
    e.preventDefault();
    var button = $(this),
        action = $(this).hasClass('selected') ? 'delete' : 'add',
        product = parseInt($(this).data('id'));
    /* ajax запрос */
    $.ajax({
        url: "ajax/comparison.php",
        data: {product: product, action: action},
        dataType: 'json',
        success: function (data) {
            $('#comparison').html(data);
            /* Смена класса кнопки */
            if (action == 'add') {
                button.addClass('selected');
            } else if (action == 'delete') {
                button.removeClass('selected');
            }
            /* Смена тайтла */
            if (button.attr('title')) {
                var text = button.data('result-text'),
                    title = button.attr('title');
                button.data('result-text', title);
                button.attr('title', text);
            }
            /* Если находимся на странице сравнения - перезагрузить */
            if ($('.fn_comparison_products').length) {
                window.location = window.location;
            }
        }
    });
    /* Улеталка */
    if (!button.hasClass('selected')) {
        transfer($('#comparison'), $(this));
    }
});

/* Функция добавления / удаления в папку избранного */
$(document).on('click', '.fn_wishlist', function (e) {
    e.preventDefault();
    var button = $(this),
        action = $(this).hasClass('selected') ? 'delete' : '';
    /* ajax запрос */
    $.ajax({
        url: "ajax/wishlist.php",
        data: {id: $(this).data('id'), action: action},
        dataType: 'json',
        success: function (data) {
            $('#wishlist').html(data.info);
            /* Смена класса кнопки */
            if (action == '') {
                button.addClass('selected');
            } else {
                button.removeClass('selected');
            }
            /* Смена тайтла */
            if (button.attr('title')) {
                var text = button.data('result-text'),
                    title = button.attr('title');
                button.data('result-text', title);
                button.attr('title', text);
            }
            /* Если находимся на странице сравнения - перезагрузить */
            if ($('.fn_wishlist_page').length) {
                window.location = window.location;
            }
        }
    });
    /* Улеталка */
    if (!button.hasClass('selected')) {
        transfer($('#wishlist'), $(this));
    }
});

/* Отправка купона по нажатию на enter */
$(document).on('keypress', '.fn_coupon', function (e) {
    if (e.keyCode == 13) {
        e.preventDefault();
        ajax_coupon();
    }
});

/* Отправка купона по нажатию на кнопку */
$(document).on('click', '.fn_sub_coupon', function (e) {
    ajax_coupon();
});

function change_currency(currency_id) {
    $.ajax({
        url: "ajax/change_currency.php",
        data: {currency_id: currency_id},
        dataType: 'json',
        success: function (data) {
            if (data == true) {
                document.location.reload()
            }
        }
    });
    return false;
}

function price_slider_init() {
    var slider_all = $('#fn_slider_min, #fn_slider_max'),
        slider_min = $('#fn_slider_min'),
        slider_max = $('#fn_slider_max'),
        current_min = slider_min.val(),
        current_max = slider_max.val(),
        range_min = slider_min.data('price'),
        range_max = slider_max.data('price'),
        link = window.location.href.replace(/\/page-(\d{1,5})/, ''),
        ajax_slider = function () {
            $.ajax({
                url: link,
                data: {
                    ajax: 1,
                    'p[min]': slider_min.val(),
                    'p[max]': slider_max.val()
                },
                dataType: 'json',
                success: function (data) {
                    $('#fn_products_content').html(data.products_content);
                    $('.fn_pagination').html(data.products_pagination);
                    $('.fn_products_sort').html(data.products_sort);
                    $('.fn_features').html(data.features);
                    $('.fn_selected_features').html(data.selected_features);
                    // Выпадающие блоки
                    $('.fn_features .fn_switch').click(function (e) {
                        e.preventDefault();

                        $(this).next().slideToggle(300);

                        if ($(this).hasClass('active')) {
                            $(this).removeClass('active');
                        } else {
                            $(this).addClass('active');
                        }
                    });

                    price_slider_init();

                    $('.fn_ajax_wait').remove();
                }
            });
        };
    link = link.replace(/\/sort-([a-zA-Z_]+)/, '');

    $('#fn_slider_price').slider({
        range: true,
        min: range_min,
        max: range_max,
        values: [current_min, current_max],
        slide: function (event, ui) {
            slider_min.val(ui.values[0]);
            slider_max.val(ui.values[1]);
        },
        stop: function (event, ui) {
            slider_min.val(ui.values[0]);
            slider_max.val(ui.values[1]);
            $('.fn_categories').append('<div class="fn_ajax_wait"></div>');
            ajax_slider();
        }
    });

    slider_all.on('change', function () {
        $("#fn_slider_price").slider('option', 'values', [slider_min.val(), slider_max.val()]);
        ajax_slider();
    });
}

/* Document ready */
$(function () {

    $(document).on("click", ".fn_menu_toggle", function () {
        $(this).next(".fn_menu_list").first().slideToggle(300);
        return false;
    });

    // $(function(){
    //     if ($(window).width() > 1024){
    //         $(window).scroll(function() {
    //             var screen = $(document);
    //             if (screen.scrollTop() < 100) {
    //                 $(".header_center_fixed").removeClass('fixed');
    //             } else {
    //                 $(".header_center_fixed").addClass('fixed');
    //             }
    //         });
    //     }
    // });

    /* Обратный звонок */
    // $('.fn_callback').fancybox();
    $(document).on('click', '.fn_callback', function (e) {
        e.preventDefault();
        $.fancybox.open({
            src: '#fn_callback',
            type: 'inline',
            opts: {
                clickSlide: false,
                touch: false
            },
        });
    })
    // $('.fn_modal_auth_link').fancybox();
    $(document).on('click', '.fn_modal_auth_link', function (e) {
        e.preventDefault();
        $.fancybox.open({
            src: '#fn_user_modal',
            type: 'inline',
            opts: {
                clickSlide: false,
                touch: false
            },
        });
    })

    // Выпадающие блоки
    $('.fn_switch').click(function (e) {
        e.preventDefault();
        $(this).next().slideToggle(300);
        if ($(this).hasClass('active')) {
            $(this).removeClass('active');
        } else {
            $(this).addClass('active');
        }
    });

    /* Главное меню для мобильных */
    $('.fn_menu_switch').on("click", function () {
        $('.menu').toggle("normal");
        $('body').toggleClass('openmenu');
    })

    //Фильтры мобильные, каталог мобильные
    $('.subswitch').click(function () {
        $(this).parent().next().slideToggle(500);

        if ($(this).hasClass('down')) {
            $(this).removeClass('down');
        } else {
            $(this).addClass('down');
        }
    });
    $('.catalog_menu .selected').parents('.parent').addClass('opened').find('> .switch').addClass('active');


    //Табы в карточке товара
    var nav = $('.tabs').find('.tab_navigation');
    var tabs = $('.tabs').find('.tab_container');

    if (nav.children('.selected').length > 0) {
        $(nav.children('.selected').attr("href")).show();
    } else {
        nav.children().first().addClass('selected');
        tabs.children().first().show();
    }

    $('.tab_navigation a').click(function (e) {
        e.preventDefault();
        if ($(this).hasClass('selected')) {
            return true;
        }
        tabs.children().hide();
        nav.children().removeClass('selected');
        $(this).addClass('selected');
        $($(this).attr("href")).fadeIn(200);
    });

    //Кнопка вверх
    $(window).scroll(function () {
        var scroll_height = $(window).height();

        if ($(this).scrollTop() >= scroll_height) {
            $('.to_top').fadeIn();
        } else {
            $('.to_top').fadeOut();
        }
    });

    $('.to_top').click(function () {
        $("html, body").animate({scrollTop: 0}, 500);
    });


    // Проверка полей на пустоту для плейсхолдера
    $('.placeholder_focus').on('blur', function () {
        if ($(this).val().trim().length > 0) {
            $(this).parent().addClass('filled');
        } else {
            $(this).parent().removeClass('filled');
        }
    });

    $('.placeholder_focus').each(function () {
        if ($(this).val().trim().length > 0) {
            $(this).parent().addClass('filled');
        }
    });

    let slidesErrorRetries = new Map();
    /* Зум картинок в карточке
    Fancybox.bind("[data-fancybox]", {
        Hash: false,
        on: {
            error: (fancybox, eventName, s) => {
                // console.log([fancybox, eventName, s]);

                let slide = fancybox.getSlide();
                let imageEl = slide.imageEl.outerHTML;

                // Забезпечуємо, що лічильник існує
                let errorRetries = 0;
                if (slidesErrorRetries.has(slide.index)) {
                    errorRetries = slidesErrorRetries.get(slide.index);
                }


                // if ($(slide.el).hasClass('has-error')) {
                if (errorRetries < maxRetries) {
                    slidesErrorRetries.set(slide.index, errorRetries + 1);

                    console.log(`[FancyBox] Слайд #${slide.index} (${slide.src}) не завантажився. Повторна спроба №${errorRetries}...`);


                    // fancybox.showLoading(slide);


                    // 2. Ініціюємо повторне завантаження контенту через затримку
                    setTimeout(() => {
                        // 1. Очищуємо контент слайда
                        fancybox.clearContent(slide);

                        fancybox.setContent(slide, imageEl, false);
                    }, 500);


                    fancybox.setError(slide, '');
                    // Зупиняємо стандартну обробку помилки
                    return false;
                } else {
                    // Після вичерпання спроб:
                    console.error(`[FancyBox] Слайд #${slide.index} (${slide.src}) не завантажився після ${maxRetries} спроб.`);

                    // Встановлюємо кастомний контент через DOM-маніпуляцію (найнадійніший спосіб)
                    const errorHtml = "<div><p>На жаль, завантажити вміст не вдалося.</p><button onclick='window.location.reload()'>Оновити сторінку</button></div>";

                    // Вставляємо HTML-контент безпосередньо в контейнер слайда
                    // fancybox.setError(errorHtml);
                    // Дозволяємо стандартній логіці FancyBox завершитися
                    return true;
                }
                // }

            },
        }
    });*/

    /* Аяксовый фильтр по цене */
    if ($('#fn_slider_price').length) {

        price_slider_init();

        // Если после фильтрации у нас осталось товаров на несколько страниц, то постраничную навигацию мы тоже проведем с помощью ajax чтоб не сбить фильтр по цене
        $(document).on('click', 'a.fn_sort_pagination_link', function (e) {
            e.preventDefault();
            var link = $(this).data('href') ? $(this).data('href') : $(this).attr('href');

            if ($(this).closest('.fn_ajax_buttons').hasClass('fn_is_ajax')) {

                $('.fn_categories').append('<div class="fn_ajax_wait"></div>');
                var send_min = $("#fn_slider_min").val();
                send_max = $("#fn_slider_max").val();
                $.ajax({
                    url: link,
                    data: {ajax: 1, 'p[min]': send_min, 'p[max]': send_max},
                    dataType: 'json',
                    success: function (data) {
                        $('#fn_products_content').html(data.products_content);
                        $('.fn_pagination').html(data.products_pagination);
                        $('.fn_products_sort').html(data.products_sort);
                        $('.fn_features').html(data.features);
                        $('.fn_selected_features').html(data.selected_features);
                        price_slider_init();

                        $('.fn_ajax_wait').remove();
                    }
                });
            } else {
                document.location.href = link;
            }
        });
    }

    /* Автозаполнитель поиска */
    $(".fn_search").autocomplete({
        serviceUrl: 'ajax/search_products.php',
        minChars: 1,
        appendTo: "#fn_search",
        noCache: true,
        onSearchStart: function (params) {
            ut_tracker.start('search_products');
        },
        onSearchComplete: function (params) {
            ut_tracker.end('search_products');
        },
        onSelect: function (suggestion) {
            $("#fn_search").submit();
        },
        transformResult: function (result, query) {
            var data = JSON.parse(result);
            $(".fn_search").autocomplete('setOptions', {triggerSelectOnValidInput: data.suggestions.length == 1});
            return data;
        },
        formatResult: function (suggestion, currentValue) {
            var reEscape = new RegExp('(\\' + ['/', '.', '*', '+', '?', '|', '(', ')', '[', ']', '{', '}', '\\'].join('|\\') + ')', 'g');
            var pattern = '(' + currentValue.replace(reEscape, '\\$1') + ')';
            return "<div>" + (suggestion.data.image ? "<img align=absmiddle src='" + suggestion.data.image + "'> " : '') + "</div>" + "<a href=" + suggestion.lang + "products/" + suggestion.data.url + '>' + suggestion.value.replace(new RegExp(pattern, 'gi'), '<strong>$1<\/strong>') + '<\/a>' + "<span>" + suggestion.price + " " + suggestion.currency + "</span>";
        }
    });

    /* Рейтинг товара */
    $('.product_rating').rater({postHref: 'ajax/rating.php'});

    /* Переключатель способа оплаты */
    $(document).on('click', '[name="payment_method_id"]', function () {
        $('[name="payment_method_id"]').parent().removeClass('active');
        $(this).parent().addClass('active');
    });
});

/* Обновление блоков: cart_informer, cart_purchases, cart_deliveries */
function ajax_set_result(data) {
    $('#cart_informer').html(data.cart_informer);
    $('#fn_purchases').html(data.cart_purchases);
    $('#fn_ajax_deliveries').html(data.cart_deliveries);
    $('#fn_ajax_payments').html(data.cart_payments);
    if (typeof (initializeNovaPoshta) === 'function') {
        initializeNovaPoshta();
    }
    if (typeof (initializeJustIn) === 'function') {
        initializeJustIn();
    }
}

/* Аяксовое изменение кол-ва товаров в корзине */
function ajax_change_amount(object, variant_id) {
    var amount = $(object).val(),
        coupon_code = $('input[name="coupon_code"]').val(),
        delivery_id = $('input[name="delivery_id"]:checked').val(),
        payment_id = $('input[name="payment_method_id"]:checked').val();
    /* ajax запрос */
    $.ajax({
        url: 'ajax/cart_ajax.php',
        data: {
            coupon_code: coupon_code,
            action: 'update_citem',
            variant_id: variant_id,
            amount: amount
        },
        dataType: 'json',
        success: function (data) {
            if (data.result == 1) {
                ajax_set_result(data);
                $('#deliveries_' + delivery_id).trigger('click');
                $('#payment_' + delivery_id + '_' + payment_id).trigger('click');
            } else {
                $('#cart_informer').html(data.cart_informer);
                $(".fn_ajax_content").html(data.content);
            }
        }
    });
}

/* Функция изменения количества товаров */
function amount_change(input, action) {
    var max_val,
        curr_val = parseFloat(input.val()),
        step = 1,
        id = input.data('id');
    if (isNaN(curr_val)) {
        curr_val = okay.amount;
    }

    /* Если включен предзаказ макс. кол-во товаров ставим максимально количество товаров в заказе */
    if (input.parent().hasClass('fn_is_preorder')) {
        max_val = okay.max_order_amount;
    } else {
        max_val = parseFloat(input.data('max'));
    }
    /* Изменение кол-ва товара */
    if (action == 'plus') {
        input.val(Math.min(max_val, Math.max(1, curr_val + step)));
        input.trigger('change');
    } else if (action == 'minus') {
        input.val(Math.min(max_val, Math.max(1, (curr_val - step))));
        input.trigger('change');
    } else if (action == 'keyup') {
        input.val(Math.min(max_val, Math.max(1, curr_val)));
        input.trigger('change');
    }
    okay.amount = parseInt(input.val());
    /* в корзине */
    if ($('div').is('#fn_purchases') && ((max_val != curr_val && action == 'plus') || (curr_val != 1 && action == 'minus'))) {
        ajax_change_amount(input, id);
    }
}

/* Функция анимации добавления товара в корзину */
function transfer(informer, thisEl) {
    var o1 = thisEl.offset(),
        o2 = informer.offset(),
        dx = o1.left - o2.left,
        dy = o1.top - o2.top,
        distance = Math.sqrt(dx * dx + dy * dy);

    thisEl.closest('.fn_transfer').find('.fn_img').effect("transfer", {
        to: informer,
        className: "transfer_class"
    }, distance);

    var container = $('.transfer_class');
    container.html(thisEl.closest('.fn_transfer').find('.fn_img').parent().html());
    container.find('*').css('display', 'none');
    container.find('.fn_img').css({
        'display': 'block',
        'height': '100%',
        'z-index': '2',
        'position': 'relative'
    });
}

/* Аяксовый купон */
function ajax_coupon() {
    var coupon_code = $('input[name="coupon_code"]').val(),
        delivery_id = $('input[name="delivery_id"]:checked').val(),
        payment_id = $('input[name="payment_method_id"]:checked').val();
    /* ajax запрос */
    $.ajax({
        url: 'ajax/cart_ajax.php',
        data: {
            coupon_code: coupon_code,
            action: 'coupon_apply'
        },
        dataType: 'json',
        success: function (data) {
            if (data.result == 1) {
                ajax_set_result(data);
                $('#deliveries_' + delivery_id).trigger('click');
                $('#payment_' + delivery_id + '_' + payment_id).trigger('click');
            } else {
                $('#cart_informer').html(data.cart_informer);
                $(".fn_ajax_content").html(data.content);
            }
        }
    });
}

/* Изменение способа доставки */
function change_payment_method($id) {
    $("#fn_delivery_payment_" + $id + " [name='payment_method_id']").first().trigger('click');
    $(".fn_delivery_payment").hide();
    $("#fn_delivery_payment_" + $id).show();
    $('input[name="delivery_id"]').parent().removeClass('active');
    $('#deliveries_' + $id).parent().addClass('active');
}

/* Аяксовое удаление товаров в корзине */
function ajax_remove(variant_id) {
    var coupon_code = $('input[name="coupon_code"]').val(),
        delivery_id = $('input[name="delivery_id"]:checked').val(),
        payment_id = $('input[name="payment_method_id"]:checked').val();
    /* ajax запрос */
    $.ajax({
        url: 'ajax/cart_ajax.php',
        data: {
            coupon_code: coupon_code,
            action: 'remove_citem',
            variant_id: variant_id
        },
        dataType: 'json',
        success: function (data) {
            if (data.result == 1) {
                ajax_set_result(data);
                $('#deliveries_' + delivery_id).trigger('click');
                $('#payment_' + delivery_id + '_' + payment_id).trigger('click');
            } else {
                $('#cart_informer').html(data.cart_informer);
                $(".fn_ajax_content").html(data.content);
            }
        }
    });
}

/* Формирование ровных строчек для характеристик */
function resize_comparison() {
    var minHeightHead = 0;
    $('.fn_resize').each(function () {
        if ($(this).height() > minHeightHead) {
            minHeightHead = $(this).height();
        }
    });
    $('.fn_resize').height(minHeightHead);
    if ($('[data-use]').length) {
        $('[data-use]').each(function () {
            var use = '.' + $(this).data('use');
            var minHeight = $(this).height();
            if ($(use).length) {
                $(use).each(function () {
                    if ($(this).height() >= minHeight) {
                        minHeight = $(this).height();
                    }
                });
                $(use).height(minHeight);
            }
        });
    }
}

/* В сравнении выравниваем строки */
if ($('.fn_comparison_products').length) {
    $(window).on('load', resize_comparison);
}

/* Звёздный рейтинг товаров */
$.fn.rater = function (options) {
    var opts = $.extend({}, $.fn.rater.defaults, options);
    return this.each(function () {
        var $this = $(this);
        var $on = $this.find('.rating_starOn');
        var $off = $this.find('.rating_starOff');
        opts.size = $on.height();
        if (opts.rating == undefined) opts.rating = $on.width() / opts.size;

        $off.mousemove(function (e) {
            var left = e.clientX - $off.offset().left;
            var width = $off.width() - ($off.width() - left);
            width = Math.ceil(width / (opts.size / opts.step)) * opts.size / opts.step;
            $on.width(width);
        }).hover(function (e) {
            $on.addClass('rating_starHover');
        }, function (e) {
            $on.removeClass('rating_starHover');
            $on.width(opts.rating * opts.size);
        }).click(function (e) {
            var r = Math.round($on.width() / $off.width() * (opts.units * opts.step)) / opts.step;
            $off.unbind('click').unbind('mousemove').unbind('mouseenter').unbind('mouseleave');
            $off.css('cursor', 'default');
            $on.css('cursor', 'default');
            opts.id = $this.attr('id');
            $.fn.rater.rate($this, opts, r);
        }).css('cursor', 'pointer');
        $on.css('cursor', 'pointer');
    });
};

$.fn.rater.defaults = {
    postHref: location.href,
    units: 5,
    step: 1
};

$.fn.rater.rate = function ($this, opts, rating) {
    var $on = $this.find('.rating_starOn');
    var $off = $this.find('.rating_starOff');
    $off.fadeTo(600, 0.4, function () {
        $.ajax({
            url: opts.postHref,
            type: "POST",
            data: 'id=' + opts.id + '&rating=' + rating,
            complete: function (req) {
                if (req.status == 200) { /* success */
                    opts.rating = parseFloat(req.responseText);

                    if (opts.rating > 0) {
                        opts.rating = parseFloat(req.responseText);
                        $off.fadeTo(200, 0.1, function () {
                            $on.removeClass('rating_starHover').width(opts.rating * opts.size);
                            var $count = $this.find('.rating_count');
                            $count.text(parseInt($count.text()) + 1);
                            $this.find('.rating_value').text(opts.rating.toFixed(1));
                            $off.fadeTo(200, 1);
                        });
                    } else if (opts.rating == -1) {
                        $off.fadeTo(200, 0.6, function () {
                            $this.find('.rating_text').text('Ошибка');
                        });
                    } else {
                        $off.fadeTo(200, 0.6, function () {
                            $this.find('.rating_text').text('Вы уже голосовали!');
                        });
                    }
                } else { /* failure */
                    alert(req.responseText);
                    $on.removeClass('rating_starHover').width(opts.rating * opts.size);
                    $this.rater(opts);
                    $off.fadeTo(2200, 1);
                }
            }
        });
    });
};

/**
 * Коли отримано помилку при завантаженні зображень,
 * виконаємо повторний запит на отримання картинки
 * (необхідно у випадку коли сервер не встиг підготувати ресайз)
 * максимальна кількість запитів 3
 * @param img
 */
const maxRetries = 3;
let ll;
let onError = (img) => {
    let currentRetries = 0;

    console.log(`Помилка для:`, $(img).attr('src'));

    if ($(img).is('[data-retries]')) {
        currentRetries = parseInt($(img).attr('data-retries'));
    }
    $(img).attr('data-retries', currentRetries + 1);
    if (currentRetries < maxRetries) {
        console.log(`Повторна спроба #${currentRetries} для:`, $(img).attr('src'));
        $(img).removeClass(['error', 'entered']);
        $(img).removeAttr('data-ll-status');

        setTimeout(ll.update(), 200);
    }
};

function lazy() {
    if ($('.lazy').length) {

        ll = new LazyLoad({
            elements_selector: '.lazy',
            load_delay: 100,
            callback_error: onError
        });


    }
}

$(function () {
    lazy();

    if ($(".fn_product .fn_ajax_image").length) {
        let webpSupported = Modernizr.webp; // Змінна для збереження результату перевірки WebP

        $(".fn_product .fn_ajax_image").each(function () {
            let a = $(this);
            if (webpSupported && $(a).is('[data-href-webp]')) {
                $(a).attr('href', $(a).data('href-webp'));
            }
        });
    }
    $(this).on('click', '.fn_search_open', function () {
        $('.search').fadeIn(400, function () {
            $('.fn_search').focus();
        });
        return false;
    });

    $(this).on('click', '.fn_search_close', function () {
        $('.search').fadeOut(400);
        return false;
    });

    $(this).on('keydown', function (e) {
        if (e.which === 27) {
            $('.search').fadeOut(400);
            return false;
        }
    });

    if ($('.fn_banner_main').length > 0) {
        let swiper = new Swiper('.fn_banner_main', {
            direction: 'horizontal',
            slidesPerView: 1,
            spaceBetween: 0,
            loop: true,
            slideShadows: false,
            watchOverflow: true,
            autoplay: {
                delay: 3500,
            },
            navigation: {
                nextEl: '.swiper-button-next',
                prevEl: '.swiper-button-prev',
            },
            breakpoints: {
                320: {
                    slidesPerView: 1,
                },
                1025: {
                    slidesPerView: 1,
                },
            },
        });
    }


    if ($('.fn_brands_slide').length) {
        $('.fn_brands_slide').each(function () {
            var swiper = new Swiper(this, {
                slidesPerView: 5,
                loop: true,
                // autoplay: {
                //     delay: 3500,
                // },
                spaceBetween: 24,
                navigation: {
                    nextEl: $(this).closest('.container').find('.swiper-button-next').get(0),
                    prevEl: $(this).closest('.container').find('.swiper-button-prev').get(0),
                },
                // pagination: {
                //     el: '.swiper-pagination',
                //     type: 'bullets',
                //     dynamicBullets: true,
                //     clickable: true,
                // },
                // on: {
                //      slideChange: function() {
                //      lazy_load_init();
                // }},
                watchOverflow: true,
                breakpoints: {
                    320: {
                        slidesPerView: 2,
                        spaceBetween: 16,
                    },
                    360: {
                        slidesPerView: 2,
                        spaceBetween: 16,
                    },
                    576: {
                        slidesPerView: 2,
                        spaceBetween: 16,
                    },
                    767: {
                        slidesPerView: 3,
                        spaceBetween: 16,
                    },
                    768: {
                        slidesPerView: 4,
                        spaceBetween: 16,
                    },
                    1200: {
                        slidesPerView: 5,
                    },
                }
            });
        });
    }

});


if ($('.fn_products_slide').length) {
    $('.fn_products_slide').each(function () {
        var swiper = new Swiper(this, {
            slidesPerView: 5,
            loop: true,
            // autoplay: {
            //     delay: 3500,
            // },
            spaceBetween: 8,
            navigation: {
                nextEl: '.swiper-button-next',
                prevEl: '.swiper-button-prev',
            },
            pagination: {
                el: '.swiper-pagination',
                type: 'bullets',
                // dynamicBullets: true,
                clickable: true,
            },
            watchOverflow: true,
            noSwiping: true,
            noSwipingClass: 'product__variants_slider',
            breakpoints: {
                320: {
                    slidesPerView: 2,
                },
                360: {
                    slidesPerView: 2,
                },
                576: {
                    slidesPerView: 3,
                },
                767: {
                    slidesPerView: 3,
                },
                768: {
                    slidesPerView: 3,
                },
                1025: {
                    slidesPerView: 4,
                },
                1200: {
                    slidesPerView: 5,
                },
            }
        });
    });
}

function sliderInit() {
    if ($('.product__variants_slider').length > 0) {
        $('.product__variants_slider:not(.swiper-initialized)').each(function (i, e) {
            let productVariantsSwiper = new Swiper(e, {
                slidesPerView: 'auto',
                spaceBetween: 4,
                watchOverflow: true,
                nested: true,
                preventClicks: false,
                preventClicksPropagation: false,
                preventInteractionOnTransition: false,
            });
        });
    }
}

sliderInit();
if ($('.fn_reviews_slide').length) {
    $('.fn_reviews_slide').each(function () {
        var swiper = new Swiper(this, {
            slidesPerView: 4,
            loop: true,
            spaceBetween: 20,
            navigation: {
                nextEl: $(this).closest('.container').find('.swiper-button-next').get(0),
                prevEl: $(this).closest('.container').find('.swiper-button-prev').get(0),
            },
            watchOverflow: true,
            breakpoints: {
                320: {
                    slidesPerView: 1,
                },
                360: {
                    slidesPerView: 1,
                    spaceBetween: 16,
                },
                576: {
                    slidesPerView: 2,
                    spaceBetween: 16,
                },
                767: {
                    slidesPerView: 2,
                    spaceBetween: 16,
                },
                768: {
                    slidesPerView: 2,
                    spaceBetween: 16,
                },
                991: {
                    slidesPerView: 3,
                    spaceBetween: 16,
                },
                1200: {
                    slidesPerView: 4,
                },
            }
        });
    });
}

if ($('.fn_categories_slide').length) {
    $('.fn_categories_slide').each(function () {
        var swiper = new Swiper(this, {
            slidesPerView: 6,
            loop: true,
            spaceBetween: 8,
            navigation: {
                nextEl: $(this).closest('.container').find('.swiper-button-next').get(0),
                prevEl: $(this).closest('.container').find('.swiper-button-prev').get(0),
            },
            watchOverflow: true,
            breakpoints: {
                320: {
                    slidesPerView: 2,
                    spaceBetween: 8,
                },
                360: {
                    slidesPerView: 2,
                    spaceBetween: 8,
                },
                576: {
                    slidesPerView: 2,
                    spaceBetween: 8,
                },
                767: {
                    slidesPerView: 3,
                    spaceBetween: 8,
                },
                768: {
                    slidesPerView: 4,
                    spaceBetween: 8,
                },
                1200: {
                    slidesPerView: 6,
                },
            }
        });
    });
}

/* Gallery images for product */
if ($('.gallery-thumbs').length) {
    galleryThumbs = new Swiper('.gallery-thumbs', {
        spaceBetween: 20,
        direction: 'vertical',
        slidesPerView: 4,
        loop: false,
        mousewheel: true,
        navigation: {
            nextEl: '.swiper-button-next',
            prevEl: '.swiper-button-prev',
        },
        breakpoints: {
            768: {
                slidesPerView: 3,
            },
            992: {
                slidesPerView: 4,
            }
        }
    });
    galleryTop = new Swiper('.gallery-top', {
        spaceBetween: 0,
        loop: false,
        navigation: {
            nextEl: '.swiper-button-next',
            prevEl: '.swiper-button-prev',
        },
        pagination: {
            el: '.swiper-pagination',
            type: 'bullets',
            // dynamicBullets: true,
            clickable: true,
        },
        thumbs: {
            swiper: galleryThumbs,
        },
    });
}
$(document).on('click', '.fn_leave_review', function (e) {
    $.fancybox.open({
        src: '#fn_comment_form',
        type: 'inline'
    });
});

$(document).on('click', '.fn_btn_fast_order', function (e) {
    let product = $(this).parents('.fn_product');
    let selectedVariant = product.find('.product__variants_item .product_radio:checked');
    let size = selectedVariant.next('label').text();
    let price = selectedVariant.find('.product__variants_item .product_radio:checked').data('price');

    let variantForm = $('#fn_content .fn_product .fn_variants');
    let dataLayerDataItem = {
        item_name: variantForm.data('name'),
        item_id: selectedVariant.val(),
        price: selectedVariant.data('price'),
        item_brand: variantForm.data('brand'),
        item_variant: selectedVariant.data('name'),
        quantity: 1,
        discount: "0",
        coupon: ""
    };

    if (variantForm.data('categories')){
        variantForm.data('categories').split(';').forEach((productCategory, index) => {
            let key = index > 0 ? 'item_category'+(index+1) : 'item_category';
            dataLayerDataItem[key] = productCategory;
        });
    }

    let dataLayerData = {
        event: "begin_checkout",
        ecommerce: {
            value: price,
            currency: okay.currency || 'UAH',
            items: [
                dataLayerDataItem
            ],
        }
    };

    window.dataLayer = window.dataLayer || [];
    window.dataLayer.push(dataLayerData);

    $('.fn_product_fast_size').text(size);
    $.fancybox.open({
        src: '#fn_fast_order_form',
        type: 'inline'
    });
});

$(document).on('click', '.fn_size_grid_button', function () {
    $.fancybox.open({
        src: '#fn_size_grid',
        type: 'inline'
    });
});

$(document).on('click', '.fn_btn_payment_info', function () {
    $.fancybox.open({
        src: '#fn_payment_info',
        type: 'inline'
    });
});

$(document).on('click', '.fn_advantages_item_1', function () {
    $.fancybox.open({
        src: '#fn_advantages_item_1',
        type: 'inline'
    });
});

$(document).on('click', '.fn_advantages_item_2', function () {
    $.fancybox.open({
        src: '#fn_advantages_item_2',
        type: 'inline'
    });
});

$(document).on('click', '.fn_advantages_item_3', function () {
    $.fancybox.open({
        src: '#fn_advantages_item_3',
        type: 'inline'
    });
});

$(document).on('click', '.fn_advantages_item_4', function () {
    $.fancybox.open({
        src: '#fn_advantages_item_4',
        type: 'inline'
    });
});

$(document).on('click', '.fn_place_order', function (e) {

    let totalValue = $('#fn_content .fn_cart_step_show .mobile_cart_wrap .purchase_total .total_sum').data('total');

    let dataLayerData = {
        event: "begin_checkout",
        ecommerce: {
            value: totalValue,
            currency: okay.currency || 'UAH',
        }
    };

    window.dataLayer = window.dataLayer || [];
    window.dataLayer.push(dataLayerData);

    $('.cart_container').hide();
    $('.fn_cart_step_show').show();
    $('h1').hide();
});

$(document).on('click', '.fn_show_modal', function () {
    let product = $(this).parents('.fn_product');
    let price = product.find('.fn_price').text();
    let old_price = product.find('.fn_old_price').text();
    let img = product.find('.fn_img').attr('src');
    let product_name = product.find('.product_heading').text();
    let product_sku = product.find('.fn_sku').text();
    let size = product.find('.product__variants_item .product_radio:checked').next('label').text();
    let color = product.find('.product_color .fn_select_color:checked').closest('label').attr('title');


    $('.fn_cart_price').text(price + "грн");
    $('.fn_cart_old_price').text(old_price + "грн");
    $('.fn_cart_size').text(size);
    $('.fn_product_name').text(product_name);
    $('.fn_sku_nubmer').text(product_sku);
    $('.fn_cart_color').text(color);

    $('.cart_pp_img').attr('src', img);
    $.fancybox.open({
        src: '#fn_pop_up_cart',
        type: 'inline'
    });
})
$(document).on('click', '.fn_close_modal', function () {
    $.fancybox.close();
})

$(document).on('click', '.fn_modal_auth', function () {
    $.fancybox.open({
        src: '#fn_user_modal',
        type: 'inline',
        opts: {
            clickSlide: false,
            touch: false
        },
    });
});

$(document).on('click', '#fn_user_modal .popup-box__top span', function (e) {
    e.preventDefault();

    var id = $(this).data('id');
    var id_active = $('#fn_user_modal .popup-box__top .is_active').data('id');

    $($('#fn_user_modal .popup-box__top .is_active')).removeClass('is_active');
    $(this).addClass('is_active');

    $(id_active).slideUp('slow');
    $(id).slideDown('slow');
});

$(document).on('click', '.edit-change', function (e) {
    e.preventDefault();

    $(this).next().removeClass('hidden');
    let $personalInfo = $('.personal_info .edit_input');
    $personalInfo.prop('disabled', false);
    $personalInfo.addClass('is_active');
    $(this).addClass('hidden');
    $('.personal_info .user_img  label').removeClass('hidden');
});
$(document).on('click', '.contact-place li', function (e) {
    e.preventDefault();

    $('.contact-place li').removeClass('is_active');
    $(this).addClass('is_active');

    var src = $(this).data('src');
    $('.ya_map iframe').attr('src', src);
});

const b = 'body';
$(document).on('click', '.info_change', function (e) {
    var id_change = $(this).data('change');
    if (id_change === $('.block-info').data('info')) ;
    {
        if (id_change == '2') {
            $('.overlay_popup').fadeIn('slow');
            $('#popup-box-size-grid').css({opacity: 0, display: 'flex'}).animate({
                opacity: 1
            }, 'slow');
            $(b).addClass('no-scroll');
        } else {
            $('.info_change.is_active').removeClass('is_active');
            $(this).addClass('is_active');
            $('.block-info.is_active').fadeOut('slow');
            setTimeout(function () {
                $('.block-info[data-id=' + id_change + ']').fadeIn('slow');
            }, 500);
            setTimeout(function () {
                $('.block-info.is_active').removeClass('is_active');
                $('.block-info[data-id=' + id_change + ']').addClass('is_active');
            }, 1000);
        }
    }
})


if (window.location.pathname.indexOf('user') > -1) {
    $(document).on('click', '.fn_referral_add', function (e) {
        e.preventDefault();
        let btn = $(this);
        let infinity = parseInt(btn.data('infinity'));

        $.post(window.location.href, {ajax: 1, referral: 1, infinity: infinity}, (r) => {
            let id = '#fn_popup_referral';
            let row = btn.closest('.lk_order');
            $(id).find('.popup_heading > div:not(.hidden)').addClass('hidden');
            $(id).find('.fn_url').data('url', '').text('');
            $(id).find('.fn_error').text('');
            $(id).find('.referral_url.hidden').removeClass('hidden');
            if (r.success) {
                $(id).find('.popup_heading > .success').removeClass('hidden');
                $(id).find('.fn_url').data('url', r.data[0].url);
                $(id).find('.fn_url').text(r.data[0].url);
                row.find('.scroll_block').prepend(r.data[0].html);
                row.find('.order_q .count').text(parseInt(row.find('.order_q .count').text()) + 1);
                if (infinity === 1) {
                    btn.remove();
                }
            } else {
                $(id).find('.popup_heading > .failure').removeClass('hidden');
                $(id).find('.fn_error').text(r.error);
                $(id).find('.referral_url').addClass('hidden');
            }
            $.fancybox.open({
                src: '#fn_popup_referral',
                type: 'inline'
            });
            // $('.overlay_popup').fadeIn('slow');
            // $(id).css({opacity: 0, display: 'flex'}).animate({opacity: 1}, 'slow');
            // $(id).addClass('no-scroll');
        });
    });

    $(document).on('click', '.fn_url', function (e) {
        e.preventDefault();

        let text = $(this).data('url');
        let temp = $("<input>");
        $("body").append(temp);
        temp.val(text).select();
        document.execCommand("copy");
        temp.remove();
        $(this).parent().find('.referral_success').fadeIn(300);
        setTimeout(() => {
            $(this).parent().find('.referral_success').fadeOut(300);
        }, 1500)
    });
}
if ($(".ya_map").length) {
    let src = $('.contact-place .is_active').data('src');
    $('.ya_map iframe').attr('src', src);
}

$(document).on('click', '.contact-place li', function (e) {
    e.preventDefault();

    $('.contact-place li').removeClass('is_active');
    $(this).addClass('is_active');

    let src = $(this).data('src');
    $('.ya_map iframe').attr('src', src);
});

$(document).on("click", ".fn_live_pagination button", function (a) {
    a.preventDefault();

    $('.fn_categories').append('<div class="fn_ajax_wait"></div>');
    let e = $(this).data("url"),
        p = $(this).data("page"),
        t = $("#fn_slider_min").val(),
        h = Math.max(0, $(this).offset().top - 100),
        send_max = $("#fn_slider_max").val();
    $.post(e, {ajax: 1, page: p, "p[min]": t, "p[max]": send_max}, function (a) {
        $("#fn_products_content").append(a.products_content);
        $(".fn_pagination").html(a.products_pagination);
        $(".fn_live_pagination").html(a.live_pagination);
        $(".fn_products_sort").html(a.products_sort);
        $('.fn_features').html(a.features);
        $('.fn_selected_features').html(a.selected_features);
        price_slider_init();

        $('.fn_ajax_wait').remove();
        (typeof (a.furl) !== 'undefined') ? (a.furl.indexOf("stock") === -1) ? history.pushState(null, null, a.furl) : "" : "";
        $('html, body').animate({scrollTop: h}, 1000);
        sliderInit();

    });
    $(this).closest('.products_item').removeClass('show_product');
    $(this).closest('.fn_live_pagination').addClass('hidden');

});

$(document).on('click', '.select_color', function (e) {
    $('.select_color').removeClass('is_active');
    $(this).addClass('is_active');
});

$(document).on('click', '.select_color_cart', function (e) {
    $(this).parent().parent().find('.select_color_cart').removeClass('is_active');
    $(this).addClass('is_active');
});

$(document).on('click', '.fn_share_product_btn', function () {
    $.fancybox.open({
        src: '#fn_share_product',
        type: 'inline'
    });
});


// FORMAT NUMBER [START]
function formatNumber(n) {
    return String(n).replace(/(\d)(?=(\d{3})+([^\d]|$))/g, "$1 ")
}

// FORMAT NUMBER [END]


// PRODUCT: CHANGE VARIANT (AJAX) [START]
$(function () {

    $(this).on('change', '.fn_change_variant', function () {

        let form = $(this).closest('.fn_variants');
        let product_id = $(this).data('productid');
        let variant_id = $(this).data('variantid');

        console.log('⏳ VARIANT: INIT');

        let ajax = $.ajax({
            method: 'POST',
            url: 'ajax/variant.php',
            data: {
                product_id: product_id,
                variant_id: variant_id,
            },
            dataType: 'json',
        });

        ajax.done(function (data) {
            if (data.success && data.data && data.data.length > 0) {
                let variants = data.data;
                let variantFirst = {};
                let variantActive = {};
                let variantActiveCheck = true;
                let variantRender = '';

                // CHANGE VARIANTS [START]
                variants.forEach(function (variant, i) {
                    if (variant.enabled) {
                        // THE FIRST PRODUCT
                        if (i == 0) {
                            variantFirst = variant;
                        }

                        // PRODUCT IN STOCK
                        if (variantActiveCheck && variant.stock > 0) {
                            variantActive = variant;
                            variantActiveCheck = false;
                        }

                        variantRender += '<div class="product__variants_item">';
                        variantRender += '<input';
                        variantRender += ' type="radio"';
                        variantRender += ' name="variant"';
                        variantRender += ' id="main_product_radio_' + variant.id + '_' + i + '"';
                        variantRender += ' class="product_radio fn_variant hidden"';
                        variantRender += ' value="' + variant.id + '"';
                        variantRender += ' data-price="' + variant.price + '"';
                        variantRender += ' data-stock="' + variant.stock + '"';
                        if (variant.compare_price > 0) {
                            variantRender += ' data-cprice="' + variant.compare_price + '"';
                            if (variant.compare_price > variant.price && variant.price > 0) {
                                variantRender += ' data-discount="' + Math.round(((variant.price - variant.compare_price) / variant.compare_price) * 100) + ' %"';
                            }
                        }
                        if (variant.sku) {
                            variantRender += ' data-sku="' + variant.sku + '"';
                        }
                        if (variant.units) {
                            variantRender += ' data-units="' + variant.units + '"';
                        }
                        if (variant.name) {
                            variantRender += ' data-name="' + variant.name + '"';
                        }
                        if (typeof variant.id !== 'undefined' && variant.id == variantActive.id) {
                            variantRender += ' checked';
                        }
                        variantRender += '>';
                        variantRender += '<label class="product_preview_variant ' + (variant.stock == 0 ? 'disabled' : '') + '" for="main_product_radio_' + variant.id + '_' + i + '">';
                        variantRender += variant.size;
                        variantRender += '</label>';
                        variantRender += '</div>';
                    }
                });

                $(form).find('.product__variants_items').html(variantRender);
                // CHANGE VARIANTS [END]


                console.log("debug - variantFirst",variantFirst);

                // CHANGE IMAGES [START]
                if (variantFirst && typeof variantFirst.images !== 'undefined' && variantFirst.images.length > 0) {
                    let galleryTopRender = [];
                    let galleryThumbsRender = [];
                    

                    for (let i = 0; i < variantFirst.images.length; i++) {
                        let image = variantFirst.images[i];
                        let image_sm = variantFirst.images_sm[i];
                        let image_lg = variantFirst.images_lg[i];

                        galleryTopRender.push(
                            '<a href="' + image_lg + '" data-fancybox="we2" class="swiper-slide fn_ajax_image">' +
                            '<picture>' +
                            '<img src="' + image + '" class="fn_img" loading="lazy" alt="" title="">' +
                            '</picture>' +
                            '</a>'
                        );

                        galleryThumbsRender.push(
                            '<div class="product-page__images-item swiper-slide">' +
                            '<picture>' +
                            '<img src="' + image_sm + '" loading="lazy" alt="" title="">' +
                            '</picture>' +
                            '</div>'
                        );
                    }
                    if ($('.gallery-thumbs').length) {
                        galleryThumbs.removeAllSlides();
                        galleryThumbs.appendSlide(galleryThumbsRender);
                        galleryTop.removeAllSlides();
                        galleryTop.appendSlide(galleryTopRender);
                    } else {
                        document.querySelector('.gallery-top .swiper-wrapper').innerHTML = galleryTopRender.join('');
                    }
                } else if (variantFirst && typeof variantFirst.images !== 'undefined' && variantFirst.images.length == 0) {
                    let galleryTopRender = [];

                    const noImage = document.querySelector('.gallery-top').dataset.noImage;

                    galleryTopRender.push(
                        '<div  class="product-page__image--full product-page__no_image product-page__image">' +
                        '<img src="design/sportfly_1/images/no-image.svg" class="fn_img" loading="lazy" alt="" title="">' +
                        '</div>'
                    );
                    document.querySelector('.gallery-top .swiper-wrapper').innerHTML = galleryTopRender.join('');
                    galleryThumbs.removeAllSlides();
                }
                // CHANGE IMAGES [END]

                // CHANGE PRICE [START]
                if (!variantActiveCheck && typeof variantActive.price !== 'undefined') {
                    $(form).find('.fn_price').html(formatNumber(variantActive.price));
                }

                if (!variantActiveCheck && typeof variantActive.compare_price !== 'undefined') {
                    $(form).find('.fn_old_price').html(formatNumber(variantActive.compare_price));
                }

                if (variantActive.compare_price > variantActive.price && variantActive.price > 0) {
                    $(form).find('.old_price').removeClass('hidden');
                } else {
                    $(form).find('.old_price').addClass('hidden');
                }
                // CHANGE PRICE [END]

                if (!variantActiveCheck) {
                    form.find('.fn_not_stock').addClass('hidden');
                    form.find('.fn_is_preorder').addClass('hidden');
                    form.find('.fn_in_stock').removeClass('hidden');
                    form.find('.fn_is_stock').removeClass('hidden');
                    form.find('.btn-one-click').removeClass('hidden');
                } else {
                    form.find('.fn_not_stock').removeClass('hidden');
                    form.find('.fn_is_preorder').removeClass('hidden');
                    form.find('.fn_in_stock').addClass('hidden');
                    form.find('.fn_is_stock').addClass('hidden');
                    form.find('.btn-one-click').addClass('hidden');
                }

                console.log('✅ VARIANT: LOADED');
            } else {
                console.log('❌ VARIANT: ERROR');
            }
        });

        ajax.fail(function () {
            console.log('❌ VARIANT: ERROR');
        });

    });

});
// PRODUCT: CHANGE VARIANT (AJAX) [END]


// PRODUCT LIST: CHANGE VARIANT (AJAX) [START]
$(function () {

    $(this).on('change', '.fn_change_variant_cart', function () {

        let self = this;
        let form = $(this).closest('.fn_variants');
        let product_id = $(this).data('productid');
        let variant_id = $(this).data('variantid');
        let variantSlider = $(form).find('.product__variants_slider').get(0).swiper;

        console.log('⏳ VARIANT: INIT');

        let ajax = $.ajax({
            method: 'POST',
            url: 'ajax/variant.php',
            data: {
                product_id: product_id,
                variant_id: variant_id,
            },
            dataType: 'json',
        });

        ajax.done(function (data) {
            if (data.success && data.data && data.data.length > 0) {
                let variants = data.data;
                let variantFirst = {};
                let variantActive = {};
                let variantActiveCheck = true;
                let variantRender = [];

                console.log(data);

                // CHANGE VARIANTS [START]
                variants.forEach(function (variant, i) {
                    if (variant.enabled) {
                        // THE FIRST PRODUCT
                        if (i == 0) {
                            variantFirst = variant;
                        }

                        // PRODUCT IN STOCK
                        if (variantActiveCheck && variant.stock > 0) {
                            variantActive = variant;
                            variantActiveCheck = false;
                        }

                        let variantSlide = '';
                        variantSlide += '<div class="swiper-slide">';
                        variantSlide += '<input';
                        variantSlide += ' type="radio"';
                        variantSlide += ' name="variant"';
                        variantSlide += ' id="product_radio_' + variant.id + '_' + i + '"';
                        variantSlide += ' class="product_radio fn_variant hidden"';
                        variantSlide += ' value="' + variant.id + '"';
                        variantSlide += ' data-price="' + variant.price + '"';
                        variantSlide += ' data-stock="' + variant.stock + '"';
                        if (variant.compare_price > 0) {
                            variantSlide += ' data-cprice="' + variant.compare_price + '"';
                            if (variant.compare_price > variant.price && variant.price > 0) {
                                variantSlide += ' data-discount="' + Math.round(((variant.price - variant.compare_price) / variant.compare_price) * 100) + ' %"';
                            }
                        }
                        if (variant.sku) {
                            variantSlide += ' data-sku="' + variant.sku + '"';
                        }
                        if (variant.units) {
                            variantSlide += ' data-units="' + variant.units + '"';
                        }
                        if (variant.name) {
                            variantSlide += ' data-name="' + variant.name + '"';
                        }
                        if (typeof variant.id !== 'undefined' && variant.id == variantActive.id) {
                            variantSlide += ' checked';
                        }
                        variantSlide += '>';
                        variantSlide += '<label class="product_preview_variant ' + (variant.stock == 0 ? 'disabled' : '') + '" for="product_radio_' + variant.id + '_' + i + '">';
                        variantSlide += variant.size;
                        variantSlide += '</label>';
                        variantSlide += '</div>';

                        variantRender.push(variantSlide);
                    }
                });

                variantSlider.removeAllSlides();
                variantSlider.appendSlide(variantRender);
                // CHANGE VARIANTS [END]

                // CHANGE IMAGE [START]
                if (variantFirst && typeof variantFirst.image !== 'undefined' && variantFirst.image.length > 0) {
                    let productImage = $(self).closest('.fn_product').find('.fn_img');
                    let proudctImageName = $(productImage).attr('alt');
                    let productImageRender = '<img class="fn_img preview_img" src="' + variantFirst.image + '" alt="' + proudctImageName + '" title="' + proudctImageName + '">';

                    $(productImage).replaceWith(productImageRender);
                }
                // CHANGE IMAGE [END]

                // CHANGE PRICE [START]
                if (!variantActiveCheck && typeof variantActive.price !== 'undefined') {
                    $(form).find('.fn_price').html(formatNumber(variantActive.price));
                }

                if (!variantActiveCheck && typeof variantActive.compare_price !== 'undefined') {
                    $(form).find('.fn_old_price').html(formatNumber(variantActive.compare_price));
                }

                if (variantActive.compare_price > variantActive.price && variantActive.price > 0) {
                    $(form).find('.old_price').removeClass('hidden');
                } else {
                    $(form).find('.old_price').addClass('hidden');
                }
                // CHANGE PRICE [END]

                if (!variantActiveCheck) {
                    form.find('.buy.fn_is_preorder').addClass('hidden');
                    form.find('.buy.fn_is_stock').removeClass('hidden');
                } else {
                    form.find('.buy.fn_is_preorder').removeClass('hidden');
                    form.find('.buy.fn_is_stock').addClass('hidden');
                }

                console.log('✅ VARIANT: LOADED');
            } else {
                console.log('❌ VARIANT: ERROR');
            }
        });

        ajax.fail(function () {
            console.log('❌ VARIANT: ERROR');
        });

    });

});
// PRODUCT LIST: CHANGE VARIANT (AJAX) [END]


/*Перемикач активувати знижку у кошику */
$('#user_bonuses').on('change', function (e) {
    e.preventDefault();
    let total1 = $('.fn_total_cart').attr('data-total');
    let total2 = $('.fn_total_cart').attr('data-total_bonuses');

    console.log(total1);
    console.log(total2);
    if ($(this).is(':checked')) {
        $('.fn_total_cart').text(total2);
    } else {
        $('.input_coupon').val(0);
        $('.fn_total_cart').text(total1);
    }
})

$(document).on("click", ".close_informer", function () {
    $('.cart_popup_block').addClass('hidden');
})

$(document).on('click', '.fn_show_installment', function (e) { 
    $.fancybox.open({
        src: '#fn_installment_form',
        type: 'inline'
    });
})


$('input[name="payments_num"]').on("change", function () {
    let input = this;
    let monthNum = $(input).val();
    let monthField = $(input).closest('.installment_flex').find('.fn_month_num');
    let monthPayment = $(input).closest('.installment_flex').find('.fn_monthly_payment');
    let productPrice = $(input).closest('.installment_flex').data('price').toString().replace(/\s/g, '');
    $(monthField).html(monthNum);
    console.log((productPrice/monthNum));
    $(monthPayment).html((productPrice/monthNum).toFixed(0));
});