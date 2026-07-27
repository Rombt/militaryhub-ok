/* google tag manager ads */
const sendGoogleTagManagerData = (event, params) => {
    if (typeof (gtag) !== 'undefined') {
        content = {
            'send_to': 'AW-672161650'
        };
        params = typeof (params) === 'undefined' ? {} : params;
        params = Object.assign(content, params);
        console.log('event', event, params);
        gtag('event', event, params);
    }
}
const sendGoogleTagManager = {
    view_search_results: () => {
        let ids = [];
        $('#fn_products_content .fn_product').each((i, item) => {
            if ($(item).find('.fn_variants [name*=variant]:checked').length) {
                ids.push({
                    id: $(item).find('.fn_variants [name*=variant]:checked').val().toString(),
                    google_business_vertical: 'retail'
                });
            }
        });
        sendGoogleTagManagerData('view_search_results', {items: ids});
    },
    view_item_list: () => {
        let ids = [];
        $('#fn_products_content .fn_product').each((i, item) => {
            if ($(item).find('.fn_variants [name*=variant]:checked').length) {
                ids.push({
                    id: $(item).find('.fn_variants [name*=variant]:checked').val().toString(),
                    google_business_vertical: 'retail'
                });
            }
        });
        sendGoogleTagManagerData('view_item_list', {items: ids});
    },
    
    view_item: (item) => {
        sendGoogleTagManagerData('view_item', item);
    },
    add_to_cart: () => {
        let ids = [], amount = 0, price = [];
        $('form[name=cart] .fn_purchase').each((i, item) => {
            ids.push({
                id: $(item).find('input[name*=amounts]').data('id').toString(),
                google_business_vertical: 'retail'
            });
            amount = $(item).find('input[name*=amounts]').val() ? parseInt($(item).find('input[name*=amounts]').val()) : 1;
            price.push(parseFloat($(item).find('.fn_price').first().text()) * amount);
        });

        sendGoogleTagManagerData('add_to_cart', {value: price, items: ids});
    },
    purchase: () => {
        let ids = [], amount = 0, price = [];
        let purchases = $('.fn_purchase');
        purchases.each((i, item) => {
            ids.push({id: $(item).find('.fn_price').data('variant').toString(), google_business_vertical: 'retail'});
            amount = Math.max(1, parseInt($(item).find('.fn_amount').text()));
            price.push(parseFloat($(item).find('.fn_price').data('price').toString()) * amount);
        });
        sendGoogleTagManagerData('purchase', {value: price, items: ids});
    }
}
/* google tag manager ads */

/* google ecommerce */
const sendECommerceData = (event, products, params) => {
    if (typeof (gtag) !== 'undefined') {
        window.dataLayer = window.dataLayer || [];
        let content = {
            items: products
        };
        params = typeof (params) === 'undefined' ? {} : params;
        params = Object.assign(content, params);
        console.log('dataLayer', {
            event: event,
            ecommerce: params
        });
        window.dataLayer.push({
            event: event,
            ecommerce: params
        });
    }
}
const eCommerceProductList = (item) => {
    let container = $(item).closest('.fn_container').find('.fn_container_name'),
        breadcrumb = $('.breadcrumbs li:last'),
        product = $(item).find('.product_name'),
        variant = $(item).find('input[name=variant]:checked'),
        category = product.data('category'),
        result = {};

    variant = variant.length === 0 ? $(item).find('input[name*=amounts]') : variant;
    result = {
        item_name: trimVal(product.text()),
        item_id: trimVal(variant.data('sku')),
        price: variant.data('price'),
        item_brand: trimVal(product.data('brand')),
        item_category: '',
        item_category2: '',
        item_category3: '',
        item_category4: '',
        item_variant: trimVal(variant.data('name')),
        item_list_name: container.length > 0 ? trimVal(container.text()) : (breadcrumb.length > 0 ? trimVal(breadcrumb.text()) : '')
    };
    if (category.length > 0) {
        category.split(';').forEach((v, i) => {
            let key = (i > 0) ? (i + 1).toString() : '';
            result[`item_category${key}`] = trimVal(v);
        });
    }
    return result;
}
const eCommerceProduct = (item) => {
    let product = $(item),
        variant = $(item).find('input[name=variant]:checked'),
        category = $('.breadcrumbs li:not(:first):not(:last)'),
        result = {};

    result = {
        item_name: trimVal(product.find('h1').text()),
        item_id: trimVal(variant.data('sku')),
        price: variant.data('price'),
        item_brand: product.find('.fn_brand').length > 0 ? trimVal(product.find('.fn_brand').data('brand')) : '',
        item_category: '',
        item_category2: '',
        item_category3: '',
        item_category4: '',
        item_variant: trimVal(variant.data('name')),
        index: 1
    };
    if (category.length > 0) {
        category.each((i, v) => {
            let key = (i > 0) ? (i + 1).toString() : '';
            result[`item_category${key}`] = trimVal($(v).text());
        });
    }
    return result;
}
const ECommerce = {
    view_item_list: () => {
        let items = [];
        $('.fn_product:visible').each((i, val) => {
            let canonical = $('link[rel=canonical]').attr('href');
            canonical = typeof (canonical) === 'undefined' ? window.location.href : canonical;
            canonical = canonical.replace(window.location.origin, '');
            if (i === 0 && canonical.indexOf('products') > -1 && canonical.indexOf('products') <= 4) {
                return true;
            }
            item = eCommerceProductList(val);
            item.index = items.length + 1;
            items.push(item);
        });
        if (items.length > 0) {
            sendECommerceData('view_item_list', items, {item_list_name: items[0]['item_list_name']});
        }
    },
    select_item: (item, url) => {
        sendECommerceData('select_item', [item]);
        if (typeof (url) !== 'undefined') {
            setTimeout(() => {
                document.location.href = url
            }, 200);
        }
    },
    view_item: (item, params) => {
        sendECommerceData('view_item', [item], params);
    },
    add_to_cart: (item) => {
        sendECommerceData('add_to_cart', [item], {currency: 'UAH', value: item.price * item.quantity});
    },
    remove_from_cart: (item) => {
        sendECommerceData('remove_from_cart', [item], {currency: 'UAH', value: item.price * item.quantity});
    },
    add_to_wishlist: (item) => {
        sendECommerceData('add_to_wishlist', [item], {currency: 'UAH', value: item.price});
    },
    view_cart: (items, params) => {
        sendECommerceData('view_cart', items, params);
    },
    begin_checkout: (items, params) => {
        sendECommerceData('begin_checkout', items, params);
    },
    add_shipping_info: (items, params) => {
        sendECommerceData('add_shipping_info', items, params);
    },
    add_payment_info: (items, params) => {
        sendECommerceData('add_payment_info', items, params);
    }
}
/* google ecommerce */

const trimVal = (val) => {
    val = typeof (val) === 'undefined' ? '' : val;
    return val.toString().replace(/\r?\n|\r/g, '').trim().substr(0, 100);
}

const getPosition = (needle, search) => {
    let index, i = -1, elements = $(search);
    while (typeof (index) === 'undefined' && ++i < elements.length) {
        let element = elements[i];
        if ($(element).is(needle)) {
            index = i;
        }
    }
    index = typeof (index) === 'undefined' ? 0 : index;
    return index + 1;
}

$(document).ready(function () {
    let canonical = $('link[rel=canonical]').attr('href');
    canonical = typeof (canonical) === 'undefined' ? window.location.href : canonical;
    canonical = canonical.replace(window.location.origin, '');
    if (canonical.indexOf('all-products') >= 1 && canonical.indexOf('all-products') <= 4) {
        sendGoogleTagManager.view_search_results();
    } else if (canonical.indexOf('catalog') >= 1 && canonical.indexOf('catalog') <= 4) {
        sendGoogleTagManager.view_item_list();
    } else if (canonical.indexOf('products') > -1 && canonical.indexOf('products') <= 4) {
        let ids = [];
        let product = $('.fn_product').first(),
            variant,
            category = $('.breadcrumbs li:not(:first):not(:last)');

        ids.push({id: product.find('.fn_sku').data('variant').toString(), google_business_vertical: 'retail'});
        sendGoogleTagManager.view_item({value: parseFloat(product.find('.fn_price').text()), items: ids});

        if (product.find('select[name=variant] option').length > 0) {
            variant = product.find('select[name=variant] option:selected');
        } else {
            variant = product.find('input[name=variant]:first');
        }

        // let items = [], price = 0.00;
        // product.find('select[name=variant] option').each((i, v) => {
        //     price += parseFloat($(v).data('price'));
        // });
        // product.find('select[name=variant] option').each((i, v) => {
        //     let item = {
        //         item_name: trimVal(product.find('h1').text()),
        //         item_id: trimVal($(v).data('sku')),
        //         price: $(v).data('price'),
        //         item_brand: product.find('.fn_brand').length > 0 ? trimVal(product.find('.fn_brand').data('brand')) : '',
        //         item_category: '',
        //         item_category2: '',
        //         item_category3: '',
        //         item_category4: '',
        //         item_variant: trimVal($(v).data('name')),
        //     };
        //     if (category.length > 0) {
        //         category.each((i, v) => {
        //             let key = (i > 0) ? (i + 1).toString() : '';
        //             item[`item_category${key}`] = trimVal($(v).text());
        //         });
        //     }
        //     // items.push(item);
        // });
        let item = {
            item_name: trimVal(product.find('h1').text()),
            item_id: trimVal(variant.data('sku')),
            price: variant.data('price'),
            item_brand: product.find('.fn_brand').length > 0 ? trimVal(product.find('.fn_brand').data('brand')) : '',
            item_category: '',
            item_category2: '',
            item_category3: '',
            item_category4: '',
            item_variant: trimVal(variant.data('name')),
        };
        if (category.length > 0) {
            category.each((i, v) => {
                let key = (i > 0) ? (i + 1).toString() : '';
                item[`item_category${key}`] = trimVal($(v).text());
            });
        }
        ECommerce.view_item(item, {currency: 'UAH', value: item.price});
        // ECommerce.view_item(items, { currency: 'UAH', value: price });
    } else if (canonical.indexOf('cart') >= 1 && canonical.indexOf('cart') <= 4) {
        let price = 0.00, items = [], purchases = $('.fn_purchase');
        purchases.each((i, v) => {
            let variant = $(v).find('input[name*=amounts]');
            let item = eCommerceProductList($(variant).closest('.fn_purchase'));
            item.quantity = parseInt(variant.val());
            price += (parseFloat(item.price) * parseInt(item.quantity));
            items.push(item);
        });
        sendGoogleTagManager.add_to_cart();
        // ECommerce.begin_checkout(items, { currency: 'UAH', value: price });
    } else if (canonical.indexOf('order') >= 1 && canonical.indexOf('order') <= 4) {
        sendGoogleTagManager.purchase();
    }

    ECommerce.view_item_list();

    $(document).on('click', '.fn_product:has(a[href][data-product]) a:not([href*="#"])', function (e) {
        e.preventDefault();
        let product = $(this).closest('.fn_product');
        if (product.length > 0) {
            let url = product.find('a[href]').attr('href');
            let item = eCommerceProductList(product);
            let index = getPosition(product, $('.fn_product:has(a[href][data-product])'));
            if (typeof (ECommerce.select_item) === 'function') {
                item.index = index;
                ECommerce.select_item(item, document.location.origin + '/' + url);
            } else {
                document.location = document.location.origin + '/' + url;
            }
        }
    });

    $(document).on('submit', '.fn_variants', function () {
        let option, ids = [], amount = 0, price = 0.00;
        option = $(this).find('select[name=variant] > option:selected');
        if (option.length === 0) {
            option = $(this).find('input[name=variant]:checked');
        }
        if (option.length > 0 && typeof (option.val()) !== 'undefined') {
            ids.push({id: option.val().toString(), google_business_vertical: 'retail'});
            amount = $(this).find('input[name=amount]').val() ? parseInt($(this).find('input[name=amount]').val()) : 1;
            price = parseFloat(option.data('price')) * amount;
            // sendGoogleTagManagerData('add_to_cart', {value: price, items: ids});

            if (canonical.indexOf('products') > -1 && canonical.indexOf('products') <= 4) {
                let item;
                if ($(this).closest('.fn_product').is('.fn_product:first')) {
                    item = eCommerceProduct($(this).closest('.fn_product'));
                    item.index = 1;
                } else {
                    item = eCommerceProductList($(this).closest('.fn_product'));
                    item.index = getPosition($(this).closest('.fn_product'), $('.fn_product:not(:first)')) + 1;
                }
                item.quantity = amount;
                ECommerce.add_to_cart(item);
            } else {
                let item = eCommerceProductList($(this).closest('.fn_product'));
                item.index = getPosition($(this).closest('.fn_product'), $('.fn_product'));
                item.quantity = amount;
                ECommerce.add_to_cart(item);
            }
        }
    });

    $(document).on('change', 'input[name*=amounts]', function () {
        let amount = parseInt($(this).val()) - parseInt($(this).data('amount'));
        let action = amount > 0 ? 'add_to_cart' : 'remove_from_cart';
        let product = $(this).closest('.fn_purchase');
        let item = eCommerceProductList(product);
        item.quantity = Math.abs(amount);
        if (item.quantity > 0) {
            item.index = getPosition(product, $('#fn_purchases .fn_purchase'));
            if (typeof ECommerce[action] !== 'undefined') {
                ECommerce[action](item);
            }
        }
    });

    $(document).on('click', '.purchase_remove a', function () {
        let product = $(this).closest('.fn_purchase');
        let amount = parseInt(product.find('input[name*=amounts]').val());
        let item = eCommerceProductList(product);
        item.quantity = Math.abs(amount);
        if (item.quantity > 0) {
            item.index = getPosition(product, $('#fn_purchases .fn_purchase'));
            ECommerce.remove_from_cart(item);
        }
    });

    $(document).on('click', '.fn_wishlist:not(.selected)', function () {
        let product = $(this).closest('.fn_product');
        let item;
        if (canonical.indexOf('products') > -1 && canonical.indexOf('products') <= 4) {
            if (product.is('.fn_product:first')) {
                item = eCommerceProduct(product);
                item.index = 1;
            } else {
                item = eCommerceProductList(product);
                item.index = getPosition(product, $('.fn_product:not(:first)')) + 1;
            }
        } else {
            item = eCommerceProductList(product);
            item.index = getPosition(product, $('.fn_product'));
        }
        ECommerce.add_to_wishlist(item);
    });

    if (canonical.indexOf('cart') >= 1 && canonical.indexOf('cart') <= 4) {
        let price = 0.00, items = [], purchases = $('.fn_purchase');
        purchases.each((i, v) => {
            let variant = $(v).find('input[name*=amounts]');
            let item = eCommerceProductList($(variant).closest('.fn_purchase'));
            item.quantity = parseInt(variant.val());
            price += (parseFloat(item.price) * parseInt(item.quantity));
            items.push(item);
        });
        ECommerce.view_cart(items, {currency: 'UAH', value: price});

        $(document).on('click', '.fn_cart_checkout', function () {
            let price = 0.00, items = [], purchases = $('.fn_purchase');
            purchases.each((i, v) => {
                let variant = $(v).find('input[name*=amounts]');
                let item = eCommerceProductList($(variant).closest('.fn_purchase'));
                item.quantity = parseInt(variant.val());
                price += (parseFloat(item.price) * parseInt(item.quantity));
                items.push(item);
            });
            ECommerce.begin_checkout(items, {currency: 'UAH', value: price});

            // $('input[name=delivery_id]:checked').trigger('change');
            // $('input[name=payment_method_id]:checked').trigger('change');
        });

        // $(document).on('change', 'input[name=delivery_id], .delivery input[name*=type]', function () {
        //     let price = 0.00, items = [],
        //         purchases = $('.fn_purchase'),
        //         delivery = $('form[name=cart] input[name=delivery_id]:checked'),
        //         delivery_name = [];
        //     purchases.each((i, v) => {
        //         let variant = $(v).find('input[name*=amounts]');
        //         let item = eCommerceProductList($(variant).closest('.fn_purchase'));
        //         item.quantity = parseInt(variant.val());
        //         price += (parseFloat(item.price) * parseInt(item.quantity));
        //         items.push(item);
        //     });
        //     if (delivery.siblings('.delivery_name').length > 0) {
        //         delivery_name.push(trimVal(delivery.siblings('.delivery_name').text()));
        //     }
        //     if (delivery.closest('.delivery_item').find('input[name*=type]:checked')) {
        //         delivery_name.push(trimVal(delivery.closest('.delivery_item').find('input[name*=type]:checked').siblings('.delivery_name').text().toLowerCase()));
        //     }
        //     ECommerce.add_shipping_info(items, { shipping_tier: trimVal(delivery_name.join(' ')), currency: 'UAH', value: price });
        // });

        // $(document).on('change', 'input[name=payment_method_id]', function () {
        //     let price = 0.00, items = [],
        //         purchases = $('.fn_purchase'),
        //         payment = $('form[name=cart] input[name=payment_method_id]:checked'),
        //         payment_name = [];
        //     purchases.each((i, v) => {
        //         let variant = $(v).find('input[name*=amounts]');
        //         let item = eCommerceProductList($(variant).closest('.fn_purchase'));
        //         item.quantity = parseInt(variant.val());
        //         price += (parseFloat(item.price) * parseInt(item.quantity));
        //         items.push(item);
        //     });
        //     if (payment.siblings('.delivery_name').length > 0) {
        //         payment_name.push(trimVal(payment.siblings('.delivery_name').text()));
        //     }
        //     ECommerce.add_payment_info(items, { payment_type: trimVal(payment_name.join(' ')), currency: 'UAH', value: price });
        // });

        let fakeCheckout = true;
        $(document).on('submit', '.fn_validate_cart', function (e) {
            if (fakeCheckout === true && typeof (ECommerce) === 'object') {
                if (typeof (ECommerce.add_shipping_info) === 'function' || typeof (ECommerce.add_payment_info) === 'function') {
                    e.preventDefault();
                }
                const form = $('.fn_validate_cart');
                if (form.valid()) {
                    if (typeof (ECommerce.add_shipping_info) === 'function' || typeof (ECommerce.add_payment_info) === 'function') {
                        let price = 0.00, items = [],
                            purchases = $(this).find('.fn_purchase'),
                            delivery = $(this).find('input[name=delivery_id]:checked'),
                            delivery_name = [],
                            payment = $(this).find('input[name=payment_method_id]:checked'),
                            payment_name = [];
                        purchases.each((i, v) => {
                            let variant = $(v).find('input[name*=amounts]');
                            let item = eCommerceProductList($(variant).closest('.fn_purchase'));
                            item.quantity = parseInt(variant.val());
                            price += (parseFloat(item.price) * parseInt(item.quantity));
                            items.push(item);
                        });
                        if (delivery.siblings('.delivery_name').length > 0) {
                            delivery_name.push(trimVal(delivery.siblings('.delivery_name').text()));
                        }
                        if (delivery.closest('.delivery_item').find('input[name*=type]:checked')) {
                            delivery_name.push(trimVal(delivery.closest('.delivery_item').find('input[name*=type]:checked').siblings('.delivery_name').text().toLowerCase()));
                        }
                        if (payment.siblings('.delivery_name').length > 0) {
                            payment_name.push(trimVal(payment.siblings('.delivery_name').text()));
                        }
                        if (typeof (ECommerce.add_shipping_info) === 'function') {
                            ECommerce.add_shipping_info(items, {
                                shipping_tier: trimVal(delivery_name.join(' ')),
                                currency: 'UAH',
                                value: price
                            });
                        }
                        if (typeof (ECommerce.add_payment_info) === 'function') {
                            ECommerce.add_payment_info(items, {
                                payment_type: trimVal(payment_name.join(' ')),
                                currency: 'UAH',
                                value: price
                            });
                        }
                        submitted_cart = false;
                        fakeCheckout = false;
                        setTimeout(() => {
                            form.submit()
                        }, 300);
                    }
                }
            }
        });
    }

});
