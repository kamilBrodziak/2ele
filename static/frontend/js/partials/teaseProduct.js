class TeaseProducts {
    constructor(products, anchorClass) {
        this.product = products;
        this.anchorClass = anchorClass;
    }

    addTeaseProductDisplay() {
        let _this = this;
        $('body').on('click', "." + this.anchorClass, function (e) {
            e.preventDefault();
            let productID = $(this).data('product_id');

            // $.ajax({
            //     url: ajaxPaginationParams.ajaxUrl,
            //     type: 'POST',
            //     data: {
            //         productID: productID,
            //         action: 'addProductWidget'
            //     },
            //     beforeSend: function(response) {
            //
            //     },
            //     error: function(response) {
            //     },
            //     success: function (response) {
            //         $('#siteContainer').append(response);
            //     }
            // });
        });
    }

    addClosing() {
        $('body').on('click', function(e) {
            if($(e.target).attr('id') !== 'productWidgetContainer') {
                $('#productWidget').remove();
            }
        });
    }

    addForm(formClass, basketID) {
        $('body').on('submit', '.' + formClass, function (e) {
            e.preventDefault();
            let form = $(this);
            let productID = form.data('product_id'),
                quantity = form.find('.teaseProductFormQuantity').val(),
                variationID = form.find('.customSelectSelected').data('variation_id'),
                teaseProduct = form.closest('.teaseProduct');
            $.ajax({
                url: ajaxPaginationParams.ajaxUrl,
                type: 'POST',
                data: {
                    productID: productID,
                    quantity: quantity,
                    variationID: variationID,
                    action: 'addProductToCart'
                },
                beforeSend: function(response) {
                    teaseProduct.addClass('loadingScreen')
                },
                error: function(response) {
                    console.log(response);
                    teaseProduct.removeClass('loadingScreen')
                },
                success: function (response) {
                    teaseProduct.removeClass('loadingScreen');
                    let basket = $('#' + basketID);
                    basket.html(parseInt(basket.html()) + parseInt(quantity));
                    teaseProduct.addClass('successScreen');
                    window.setTimeout(function () {
                        teaseProduct.removeClass('successScreen');
                    }, 2000)
                }
            });
        });
    }
}