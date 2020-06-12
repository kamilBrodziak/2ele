class OrderWidget {
    constructor(orderWidgetID, widgetDisplayModeClass) {
        this.orderWidgetID = orderWidgetID;
        this.widgetDisplayModeClass = widgetDisplayModeClass;
        this.sectionWidgetID = 'orderWidgetStageContent';
        this.orderWidget = null;
        this.stage = 0;
        this.currentSectionWidget = null;
        this.active = false;
        this.loading = false;
        this.body = $('body');
    }

    orderShow(cartButtonID) {
        let _this = this;
        let data = {
            action: 'addOrderWidget'
        }
        let beforeSendFunc = (response) => {
                console.log('saas');
                $('#' + cartButtonID).addClass('loadingCenter');
            },
            errorFunc = (response) => {
                console.log(response);
            },
            successFunc = (response) => {
                _this.body.append(response);
                _this.body.addClass(_this.widgetDisplayModeClass);
                _this.orderWidget = $('#' + _this.orderWidgetID);
                _this.loading = false;
                _this.active = true;
                let cartWidget = new CartWidget($('#' + _this.sectionWidgetID));
                cartWidget.withWidget();
                $('#' + cartButtonID).removeClass('loadingCenter');
            };
        _this.body.on('click', "#" + cartButtonID, function (e) {
            // _this.stage = 0;
            if(!_this.active && !_this.loading) {
                _this.loading = true;
                e.preventDefault();
                // data.stage = _this.stage;
                ajaxCall(data, beforeSendFunc, errorFunc, successFunc);
            }
        });
    }

    addCloseWhenClickOutside() {
        let _this = this;
        $('body').on('click', function(e) {
            if(_this.active && _this.orderWidget.has(e.target).length === 0) {
                _this.close();
            }
        });
    }

    addCloseButton(closeButtonID) {
        let _this = this;
        $('body').on('click', '#' + closeButtonID, function(e) {
            e.preventDefault();
            _this.close();
        })
    }

    close() {
        this.orderWidget.remove();
        this.body.removeClass(this.widgetDisplayModeClass);
        this.active = false;
    }

    pagination(buttonsClass, stageContainerID) {
        $('body').on('click', '.' + buttonsClass, function (e) {
            e.preventDefault();

        });
    }

    loadStage(stage, stageContainerID) {
        let data = {},
            beforeSend = () => {},
            error = () => {},
            success = (response) => {
                $("#" + stageContainerID).empty().append(response);
            };

        switch (stage) {
            case 0:
                data.action = 'loadCartWidget'
                break
            case 1:
                data.action = 'loadAccountWidget'
                break
            case 3:
                data.action = 'loadDeliveryWidget'
                break
            case 4:
                data.action = 'loadCheckoutWidget'
                break
            case 5:
                data.action = 'loadOrderSummaryWidget'
                break
            case 6:
                data.action = 'loadOrderSuccessWidget'
                break
        }

    }
}