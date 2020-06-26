class CheckoutWidget {
    constructor(container) {
        this.container = container;
        this.widget = null;
        this.stagesNames = [];
        this.stage = 0;
        this.previousButton = null;
        this.nextButton = null;
        this.stagesNodes = [];
        this.cartLink = null;
        this.loginWidget = null;
        this.stages = [];
        this.orderWidgetStages = null;
    }

    withWidget() {
        const _this = this;
        this.widget = this.container.find('.checkoutWidget');
        this.cartLink = this.widget.find('.checkoutWidgetPreviousLink');
        this.stage = 0;
        this.orderWidgetStages = this.widget.find('.orderWidgetStages');
        this.widget.find('.orderWidgetStage').each(function () {
            _this.stages.push($(this));
        });
        this.stages[0].addClass('passed');
        this.previousButton = this.widget.find('.checkoutWidgetPreviousButton');
        this.nextButton = this.widget.find('.checkoutWidgetNextButton');
        this.stagesNames = ['Formularz', 'Podsumowanie i dostawa', 'Płatność'];
        // this.stagesNodes = [this.widget.find('#customer_details'), this.widget.find('#order_review')];
        this.stagesNodes = [this.widget.find('.checkoutWidgetBilling'), this.widget.find('.checkoutWidgetSummary'),
            this.widget.find('.checkoutWidgetPayment')];
        const loginWidgetContainer = $('.checkoutWidgetLoginContainer');
        if(loginWidgetContainer.length) {
            this.loginWidget = new LoginWidget(loginWidgetContainer);
            this.loginWidget.withWidget(true);
            this.stagesNodes.unshift(this.widget.find('.loginWidget'));
            this.stagesNames.unshift('Logowanie');
        }

        this.changeStage();
        this.addPagination();
    }

    addPagination() {
        const _this = this;
        if(this.previousButton) {
            this.previousButton.on('click', function(e) {
                e.preventDefault();
                _this.stage = _this.stage - 1;
                _this.changeStage();
            })
        }
        if(this.nextButton) {
            this.nextButton.on('click', function(e) {
                e.preventDefault();
                _this.stage = _this.stage + 1;
                _this.changeStage();
            })
        }
    }


    changeStage() {
        $("html, body").animate({scrollTop:0}, 400);
        const previousContainer = this.previousButton.parent(),
            nextContainer = this.nextButton.parent(),
            cartLinkContainer = this.cartLink.parent();
        if(this.stage === 0) {
            previousContainer.addClass('hide');
            if(cartLinkContainer.hasClass('hide')) {
                cartLinkContainer.removeClass('hide');
            }
        } else {
            if(previousContainer.hasClass('hide'))
                previousContainer.removeClass('hide');
            this.previousButton.html("<< " + this.stagesNames[this.stage - 1]);
            if(!cartLinkContainer.hasClass('hide'))
                cartLinkContainer.addClass('hide');
        }

        if(this.stage + 1 === this.stagesNames.length) {
            nextContainer.addClass('hide');
        } else {
            if(nextContainer.hasClass('hide'))
                nextContainer.removeClass('hide');
            this.nextButton.html(this.stagesNames[this.stage + 1] + " >>");
        }


        for(let i = 0; i < this.stagesNodes.length; ++i) {
            if(!this.stagesNodes[i].hasClass('hide') && this.stage !== i) {
                this.stagesNodes[i].addClass('hide');
            }
        }
        const startingStage = this.stagesNames.length === 3 ? 2 : 1;
        for(let i = startingStage; i < this.stages.length; ++i) {
            if(this.stages[i].hasClass('current'))
                this.stages[i].removeClass('current');
            if(this.orderWidgetStages.hasClass('stage' + i))
                this.orderWidgetStages.removeClass('stage' + i);
            if(this.stage + startingStage === i) {
                this.orderWidgetStages.addClass('stage' + i);
                this.stages[i].addClass('current');
            }
        }

        this.stagesNodes[this.stage].removeClass('hide');
    }
}