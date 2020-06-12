class CartWidget {
    constructor(widgetContainer) {
        this.widgetContainer = widgetContainer;
        this.widget = null;
        this.cartItemRemoveClass = 'cartItemRemove';
        this.cartItemClass = 'cartItem';
        this.cartItemQuantityClass = 'cartItemQuantity';
        this.cartItems = null;
        this.cartItemsRemoves = null;
        this.cartItemsQuantities = null;
    }

    withWidget() {
        if(this.widget) {
            this.unbindEvents();
        }

        this.widget = this.widgetContainer.find('.cartWidget');
        this.cartItems = this.widget.find('.cartItem');
        this.cartItemsRemoves = this.widget.find('.cartItemRemove');
        this.cartItemsQuantities = this.widget.find('.cartItemQuantity');
        this.addRemoveItemButton();
        this.addQuantityChangeInput();
    }

    loadWidgetAjax() {
        const _this = this;
        const data = {
            action: 'loadCartWidgetAjax'
        }, beforeSendFunc = (response) => {
            _this.widgetContainer.addClass('loadingScreen')
        }, errorFunc = (response) => {
            _this.widgetContainer.removeClass('loadingScreen')

        }, successFunc = (response) => {
            _this.widgetContainer.removeClass('loadingScreen')
            _this.widgetContainer.empty().append(response);
            _this.withWidget();
        }

        ajaxCall(data, beforeSendFunc, errorFunc, successFunc);
    }

    unbindEvents() {
        this.cartItemsRemoves.each(function () {
            $(this).off('click');
        });

        this.cartItemsQuantities.each(function () {
            $(this).off('keyup focusout');
        });
    }



    reloadWidgetAjax(data) {
        let _this = this;
        let beforeSendFunc = (response) => {
            _this.widgetContainer.addClass('loadingScreen')
        }, errorFunc = (response) => {
            _this.widgetContainer.removeClass('loadingScreen')

        }, successFunc = (response) => {
            _this.widgetContainer.removeClass('loadingScreen')
            _this.widgetContainer.empty().append(response);
            _this.withWidget();
        }
        ajaxCall(data, beforeSendFunc, errorFunc, successFunc);
    }

    addRemoveItemButton() {
        let _this = this;
        this.cartItemsRemoves.each(function () {
            $(this).on('click', function(e) {
                e.preventDefault();
                let cartItem = $(this).closest('.' + _this.cartItemClass);
                let data = {
                    productKey: cartItem.data('product_key'),
                    action: 'removeProductFromCart'
                }
                _this.reloadWidgetAjax(data);
            });
        });
    }

    addQuantityChangeInput() {
        let _this = this;
        this.cartItemsQuantities.each(function() {
            $(this).on('keyup focusout', function(e) {
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
                        let cartItem = input.closest('.' + _this.cartItemClass);
                        let data = {
                            productKey: cartItem.data('product_key'),
                            quantity: input.val(),
                            action: 'changeProductQuantityInCart',
                            notices: notices
                        }
                        _this.reloadWidgetAjax(data);
                    })
                }
            })
        })
    }
}