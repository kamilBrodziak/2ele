class CartWidget {
    constructor(widgetContainer) {
        this.widgetContainer = widgetContainer;
        this.widget = null;
        this.cartItemClass = 'cartItem';
        this.cartItemsRemoves = null;
        this.cartItemsQuantities = null;
        this.couponForm = widgetContainer.find('#cartCouponForm');

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
        this.addCouponCode();
    }

    loadWidgetAjax() {
        this.reloadWidgetAjax(data);
    }

    addCouponCode() {
        console.log('test');
        const couponSection = this.widgetContainer.find('#cartCoupon');
        const couponForm = couponSection.find('#cartCouponForm');
        const couponInput = couponForm.find('#cartCouponInput');
        const couponSubmit = couponForm.find('#cartCouponSubmit');
        const couponMessage = couponSection.find('#cartCouponMessage');
        const cartTotals = this.widgetContainer.find('#cartTotals');
        couponSubmit.attr('disabled', true);
        couponInput.on('input', () => {
            const disabled = couponInput.val() === '';
            couponSubmit.attr('disabled', disabled);
        })

        couponForm.on('submit', (e) => {
            e.preventDefault();
            ajaxCall(
                {
                    action: 'addCoupon',
                    coupon: couponInput.val()
                },
                () => {
                    couponSection.addClass('loadingScreen');
                }, (response) => {
                    couponSection.removeClass('loadingScreen');
                    console.log(response);
                }, (response) => {
                    couponSection.removeClass('loadingScreen');
                    const result = JSON.parse(response);
                    if(result.result) {
                        cartTotals.empty();
                        cartTotals.append(result.cartTotals);
                    }
                    couponMessage.html(result.message);
                });
        })

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
        this.cartItemsRemoves.each((i, el) => {
            $(el).on('click', (e) => {
                e.preventDefault();
                const cartItem = $(el).closest('.' + _this.cartItemClass),
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
        this.cartItemsQuantities.each((i, el) => {
            $(el).on('keyup focusout', (e) => {
                e.preventDefault();
                const input = $(el);
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