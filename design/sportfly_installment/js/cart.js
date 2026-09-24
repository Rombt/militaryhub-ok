$(function() {
    $(document).on('change', 'input[name=delivery_id]', function(e) {
        e.preventDefault();
        
        let $_delivery = $(this).data('method');
        $(document).find(".delivery [class*=_delivery]").slideUp();
        $(document).find('.'+$_delivery+'_delivery').slideDown();
    });

        /* novaposhta */
        if ($('.novaposhta_delivery').length > 0) {        
            let $_novaposhta_city = $(document).find('select[name*=novaposhta_city]');
            let $_novaposhta_ware = $(document).find('select[name*=novaposhta_ware]');
        
            const initializeNovaPoshtaData = (e, params) => {
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
                        return initializeNovaPoshtaData(this, params);
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
        
            const initializeNovaPoshta = () => {
                let $_novaposhta_city = $(document).find('select[name*=novaposhta_city]');
                let $_novaposhta_ware = $(document).find('select[name*=novaposhta_ware]');
                $_novaposhta_city.select2(objectNovaPoshta);
                $_novaposhta_ware.select2(objectNovaPoshta);
                $_novaposhta_city.on('change', function (e) {
                    e.preventDefault();
                    $_novaposhta_ware.html('');
                    $_novaposhta_ware.select2(objectNovaPoshta);
                });
            }
        
            $(document).on('load', '.novaposhta_delivery select', function(e) {
                e.preventDefault();
                console.log('load np');
                initializeNovaPoshta();
            }).on('click', '.fn_cart_checkout', function(e) {
                e.preventDefault();
                
                let checkNovaPoshta = () => {
                    let checkNovaPoshtaVisible;
                    if ($('.novaposhta_delivery select').is(':visible')) {
                        console.log('ok');
                        clearTimeout(checkNovaPoshtaVisible);
                        setTimeout(() => {
                            if ($(document).find('.novaposhta_delivery select').data('select2')) {
                                $(document).find('.novaposhta_delivery select').select2('destroy');
                            }
                            initializeNovaPoshta();
                        }, 500);
                    } else {
                        console.log('hidden');
                        checkNovaPoshtaVisible = setTimeout(checkNovaPoshta, 100);
                    }
                };
                checkNovaPoshta();
        
                // setTimeout(() => {
                //     if ($(document).find('.novaposhta_delivery select').data('select2')) {
                //         $(document).find('.novaposhta_delivery select').select2('destroy');
                //     }
                //     initializeNovaPoshta();
                // }, 1000);
            });
    
            initializeNovaPoshta();
            window.initializeNovaPoshta = initializeNovaPoshta;
        }
        /* novaposhta */

    /* justin */
    if ($('.justin_delivery').length > 0) {
        let $_justin_city = $(document).find('select[name*=justin_city]');
        let $_justin_ware = $(document).find('select[name*=justin_ware]');
    
        const initializeJustInData = (e, params) => {
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
                url: '../ajax/justin.php',
                dataType: 'JSON',
                delay: 250,
                data: function (params) {
                    return initializeJustInData(this, params);
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
    
        const initializeJustIn = () => {
            let $_justin_city = $(document).find('select[name*=justin_city]');
            let $_justin_ware = $(document).find('select[name*=justin_ware]');
            $_justin_city.select2(objectJustIn);
            $_justin_ware.select2(objectJustIn);
            $_justin_city.on('change', function (e) {
                e.preventDefault();
                $_justin_ware.html('');
                $_justin_ware.select2(objectJustIn);
            });
        }
    
        $(document).on('onload', '.justin_delivery select', function(e) {
            e.preventDefault();
            initializeJustIn();
        }).on('click', '.fn_cart_checkout', function(e) {
            e.preventDefault();
            let checkJustIn = () => {
                let checkJustInVisible;
                if ($('.justin_delivery select').is(':visible')) {
                    console.log('ok');
                    clearTimeout(checkJustInVisible);
                    initializeJustIn();
                } else {
                    console.log('hidden');
                    checkJustInVisible = setTimeout(checkJustIn, 100);
                }
            };
            checkJustIn();
            // setTimeout(() => {
            //     if ($(document).find('.justin_delivery select').data('select2')) {
            //         $(document).find('.justin_delivery select').select2('destroy');
            //     }
            //     initializeJustIn();
            // }, 2000);
        });

        window.initializeJustIn = initializeJustIn;
    }
    /* justin */

    /* stores */
    if ($('.pickup_delivery').length > 0) {
        $(document).find('.pickup_delivery select').select2({width: '100%'});
    }
    /* stores */
});

function change_novaposhta_method($id) {
    $('#types_' + $id).closest('.novaposhta_delivery').find('.delivery_label').removeClass('active');
    $('#types_' + $id).closest('.novaposhta_delivery').find('.novaposhta_types div').hide();
    $('#types_' + $id).parent().addClass('active');
    $('#types_' + $id).closest('.novaposhta_delivery').find('.type_' + $id).show();
}