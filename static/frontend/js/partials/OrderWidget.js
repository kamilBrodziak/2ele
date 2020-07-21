// NOT FULLY WORKING, PREPARED FOR FUTURE FULL MULTISTEP CHECKOUT BUILT FROM SCRATCH
class OrderWidget {
    constructor(widgetContainer = null) {
        // ORDER WIDGET
        this.widgetContainer = (widgetContainer) ? widgetContainer : $('body');
        this.widgetActiveClass = 'orderWidgetActive';
        this.contentWrapper = null;
        this.widget = null;
        this.isPopup = false;
        this.closeButton = null;
        this.cartButton = null
        // SECTION FOR OTHER WIDGETS IN ORDER WIDGET
        this.widgetSection = null;
        this.currentSectionWidget = null;
        this.previousButton = null;
        this.nextButton = null;
        this.stage = 0;
        this.active = false;
        this.loading = false;
        this.nav = null;
    }

    withWidget() {
        if(this.widget) {
            this.unbindEvents();
        }

        this.widget = this.widgetContainer.find('.orderWidget');
        this.widgetSection = this.widget.find('.orderWidgetStageContent');
        this.loading = false;
        this.active = true;
        this.contentWrapper = this.widgetContainer.find('.orderWidgetContentWrapper');
        if(this.isPopup) {
            const body = $('body');
            body.addClass(this.widgetActiveClass);
            body.append(this.widgetContainer);
            this.closeButton = this.widget.find('.orderWidgetCloseButton');
            this.addCloseButton();
            this.addCloseWhenClickOutside();
        }

        if(this.previousButton) {
            this.previousButton.off('click');
            this.previousButton = null
        }

        if(this.nextButton) {
            this.nextButton.off('click');
            this.nextButton = null;
        }

        const previousButton = this.widget.find('.orderWidgetPreviousButton');
        if(previousButton) {
            this.previousButton = previousButton
        }

        const nextButton = this.widget.find('.orderWidgetNextButton');
        if(nextButton) {
            this.nextButton = nextButton;
        }

        if(this.currentSectionWidget) {
            this.currentSectionWidget.unbindEvents();
        }
        switch (this.stage) {
            case 0:
                this.currentSectionWidget = new CartWidget(this.widgetSection);
                break;
            case 1:
                this.currentSectionWidget = new LoginWidget(this.widgetSection);
                break;
            default:
                this.currentSectionWidget = new CartWidget(this.widgetSection);
        }
        this.currentSectionWidget.withWidget();
        this.addPagination();
    }

    loadWidgetAjax(isReload = false) {
        const _this = this, loadingWidgetClass = 'loadingScreen', loadingButtonClass = 'loadingCenter';
        const data = {
            action: 'loadOrderWidgetAjax',
            isPopup: _this.isPopup,
            stage: _this.stage
        }, beforeSendFunc = () => {
            if(isReload) {
                _this.contentWrapper.addClass(loadingWidgetClass);
            } else if(_this.cartButton) {
                _this.cartButton.addClass(loadingButtonClass);
            }

        }, errorFunc = (response) => {
            console.log(response);
            if(isReload) {
                _this.contentWrapper.removeClass(loadingWidgetClass)
            } else if(_this.cartButton) {
                _this.cartButton.removeClass(loadingButtonClass);
            }
        }, successFunc = (response) => {
            if(isReload) {
                _this.contentWrapper.removeClass(loadingWidgetClass)
            } else if(_this.cartButton) {
                _this.cartButton.removeClass(loadingButtonClass);
            }
            _this.widgetContainer.empty().append(response);
            _this.widgetContainer.addClass(_this.widgetActiveClass);
            _this.withWidget();
            if(!_this.isPopup) {
                $('html, body').animate({ scrollTop: 0 }, 300);
            }

        };
        ajaxCall(data, beforeSendFunc, errorFunc, successFunc);
    }

    unbindEvents() {
        if(this.isPopup) {
            this.closeButton.off('click');
            this.widgetContainer.off('click');
        }
    }

    loadWidgetAjaxViaButton(cartButton) {
        const _this = this;
        this.isPopup = true;
        this.cartButton = cartButton;
        this.cartButton.on('click', (e) => {
            // _this.stage = 0;
            e.preventDefault();
            if(!_this.active && !_this.loading) {
                _this.loading = true;
                _this.stage = 0;
                _this.loadWidgetAjax();
            }
        });
    }

    addCloseWhenClickOutside() {
        const _this = this;
        $('body').on('click', (e) => {
            if(_this.active && _this.widget.has(e.target).length === 0) {
                _this.close();
            }
        });
    }

    addCloseButton() {
        const _this = this;
        this.closeButton.on('click', (e) => {
            e.preventDefault();
            _this.close();
        })
    }

    close() {
        this.widgetContainer.remove();
        $('body').removeClass(this.widgetActiveClass);
        this.active = false;
    }

    addPagination() {
        const _this = this;
        if(this.previousButton) {
            this.previousButton.on('click', (e) => {
                e.preventDefault();
                _this.stage = _this.stage - 1;
                _this.changeStage();
            })
        }
        if(this.nextButton) {
            this.nextButton.on('click', (e) => {
                e.preventDefault();
                _this.stage = _this.stage + 1;
                _this.changeStage();
            })
        }
    }


    changeStage() {
        this.loadWidgetAjax(true);
    }
}