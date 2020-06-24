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
    }

    withWidget() {
        const _this = this;
        this.widget = this.container.find('.checkoutWidget');
        this.cartLink = this.widget.find('.checkoutWidgetPreviousLink');
        this.stage = 0;
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
        } else {
            this.stages[1].addClass('passed');
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

        if(this.stage === 0) {
            this.previousButton.addClass('hide');
            if(this.cartLink.hasClass('hide')) {
                this.cartLink.removeClass('hide');
            }
        } else {
            if(this.previousButton.hasClass('hide'))
                this.previousButton.removeClass('hide');
            this.previousButton.html("<< " + this.stagesNames[this.stage - 1]);
            if(!this.cartLink.hasClass('hide'))
                this.cartLink.addClass('hide');
        }

        if(this.stage + 1 === this.stagesNames.length) {
            this.nextButton.addClass('hide');
        } else {
            if(this.nextButton.hasClass('hide'))
                this.nextButton.removeClass('hide');
            this.nextButton.html(this.stagesNames[this.stage + 1] + " >>");
        }


        for(let i = 0; i < this.stagesNodes.length; ++i) {
            if(!this.stagesNodes[i].hasClass('hide') && this.stage !== i) {
                this.stagesNodes[i].addClass('hide');
            }
        }
        const startingStage = this.stagesNames.length === 3 ? 2 : 1;
        for(let i = startingStage; i < this.stages.length; ++i) {
            if(this.stages[i].hasClass('current')) {
                this.stages[i].removeClass('current');
            }
            if(this.stages[i].hasClass('passed')) {
                this.stages[i].removeClass('passed');
            }
            if(this.stage + startingStage > i) {
                this.stages[i].addClass('passed');
            } else if(this.stage + startingStage === i){
                this.stages[i].addClass('current');
            }
        }

        this.stagesNodes[this.stage].removeClass('hide');
    }
}