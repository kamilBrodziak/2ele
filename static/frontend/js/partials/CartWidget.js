class CartWidget {
    constructor(widgetContainer) {
        this.widgetContainer = widgetContainer;
        this.widget = null;
        this.cartItemClass = 'cartItem';
        this.cartItemsRemoves = null;
        this.cartItemsQuantities = null;
    }

    withWidget() {
        if(this.widget) {
            this.widget.remove();
        }
        this.widget = this.widgetContainer.find('.cartWidget');
        this.cartItemsRemoves = this.widget.find('.cartItemRemove');
        this.cartItemsQuantities = this.widget.find('.cartItemQuantity');
        this.addRemoveItemButton();
        this.addQuantityChangeInput();
    }

    loadWidgetAjax() {
        this.reloadWidgetAjax(data);
    }

    reloadWidgetAjax(data) {
        const _this = this, loadingCss = 'loadingScreen';
        const beforeSendFunc = () => {
            _this.widgetContainer.addClass(loadingCss)
        }, errorFunc = () => {
            _this.widgetContainer.removeClass(loadingCss)

        }, successFunc = (response) => {
            _this.widgetContainer.removeClass(loadingCss)
            _this.widgetContainer.empty().append(response);
            _this.withWidget();
        }
        ajaxCall(data, beforeSendFunc, errorFunc, successFunc);
    }

    addRemoveItemButton() {
        const _this = this;
        this.cartItemsRemoves.each(() => {
            $(this).on('click', (e) => {
                e.preventDefault();
                const cartItem = $(this).closest('.' + _this.cartItemClass),
                        data = {
                            productKey: cartItem.data('product_key'),
                            action: 'removeProductFromCart'
                        };
                _this.reloadWidgetAjax(data);
            });
        });
    }

    addQuantityChangeInput() {
        const _this = this;
        this.cartItemsQuantities.each(() => {
            $(this).on('keyup focusout', (e) => {
                e.preventDefault();
                const input = $(this);
                if(e.type === "keyup" && e.keyCode === 13){
                    input.blur();
                }
                if(e.type==="focusout") {
                    $.when(addInputMinMaxTesting(input)).done(() => {
                        const cartItem = input.closest('.' + _this.cartItemClass);
                        const data = {
                            productKey: cartItem.data('product_key'),
                            quantity: input.val(),
                            action: 'changeProductQuantityInCart'
                        }
                        _this.reloadWidgetAjax(data);
                    })
                }
            })
        })
    }
}