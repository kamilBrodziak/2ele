class Cart {
    constructor(cartWidgetContainerID) {
        this.cartWidgetContainer = $('#' + cartWidgetContainerID);
    }

    cartWidget(cartButtonID) {
        $('body').on('click', "#" + cartButtonID, function (e) {
            e.preventDefault();
            
        });
    }

    cartReloadingAjax(data) {
        let _this = this;
        let beforeSendFunc = (response) => {
            _this.cartWidgetContainer.addClass('loadingScreen')
        }, errorFunc = (response) => {
            _this.cartWidgetContainer.removeClass('loadingScreen')

        }, successFunc = (response) => {
            _this.cartWidgetContainer.removeClass('loadingScreen')
            _this.cartWidgetContainer.empty().append(response);
        }
        ajaxCall(data, beforeSendFunc, errorFunc, successFunc);
    }

    addRemoveItemButton(removeButtonClass, cartItemClass) {
        let _this = this;
        $('body').on('click', '.' + removeButtonClass, function (e) {
            e.preventDefault();
            let cartItem = $(this).closest('.' + cartItemClass);
            let data = {
                productKey: cartItem.data('product_key'),
                action: 'removeProductFromCart'
            }
            _this.cartReloadingAjax(data);
        })
    }

    addQuantityChangeInput(cartItemQuantityClass, cartItemClass) {
        let _this = this;
        $('body').on('keyup focusout', '.' + cartItemQuantityClass, function(e) {
            e.preventDefault();
            let input = $(this);
            if((e.type === "keyup" && input.val() !== "" && e.keyCode === 13) || e.type==="focusout") {
                $.when(addInputMinMaxTesting(input)).done(function () {
                    let cartItem = input.closest('.' + cartItemClass);
                    let data = {
                        productKey: cartItem.data('product_key'),
                        quantity: input.val(),
                        action: 'changeProductQuantityInCart'
                    }
                    _this.cartReloadingAjax(data);
                })
            }
        })
    }
}