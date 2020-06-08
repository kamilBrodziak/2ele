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
            if(e.type === "keyup" && e.keyCode === 13){
                input.blur();
            }
            if(e.type==="focusout") {
                let notices = "";
                if(input.val() !== "" && parseInt(input.val()) > parseInt(input.attr('max'))) {
                    notices = "Obecnie na magazynie nie ma większej ilości niż " + input.attr('max') + " tego produktu.";
                }
                $.when(addInputMinMaxTesting(input)).done(function () {
                    let cartItem = input.closest('.' + cartItemClass);
                    let data = {
                        productKey: cartItem.data('product_key'),
                        quantity: input.val(),
                        action: 'changeProductQuantityInCart',
                        notices: notices
                    }
                    _this.cartReloadingAjax(data);
                })
            }
        })
    }
}