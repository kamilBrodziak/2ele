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
        if(this.widget) {
            this.widget.remove();
        }
        this.widget = this.container.find('.checkoutWidget');
        this.cartLink = this.widget.find('.checkoutWidgetPreviousLink');
        this.stage = 0;
        this.widget.find('.orderWidgetStage').each(() => {
            _this.stages.push($(this));
        });
        this.previousButton = this.widget.find('.checkoutWidgetPreviousButton');
        this.nextButton = this.widget.find('.checkoutWidgetNextButton');
        this.stagesNames = ['Formularz', 'Podsumowanie i dostawa', 'Płatność'];
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
        const _this = this, buttons = [this.previousButton, this.nextButton];
        $(buttons).each((i, el) => {
            $(el).on('click', (e) => {
                e.preventDefault();
                _this.stage = (i === 0) ? _this.stage - 1 : _this.stage + 1;
                _this.changeStage();
            })
        });
    }


    changeStage() {
        $("html, body").animate({scrollTop:0}, 400);
        const hideClass = 'hide', activeClass = 'active', currentClass = 'current',
            previousContainer = this.previousButton.parent(),
            nextContainer = this.nextButton.parent(),
            cartLinkContainer = this.cartLink.parent();
        if(this.stage === 0) {
            previousContainer.addClass(hideClass);
            if(cartLinkContainer.hasClass(hideClass))
                cartLinkContainer.removeClass(hideClass);
        } else {
            if(previousContainer.hasClass(hideClass))
                previousContainer.removeClass(hideClass);
            this.previousButton.html("<< " + this.stagesNames[this.stage - 1]);
            if(!cartLinkContainer.hasClass(hideClass))
                cartLinkContainer.addClass(hideClass);
        }

        if(this.stage + 1 === this.stagesNames.length) {
            nextContainer.addClass(hideClass);
        } else {
            if(nextContainer.hasClass(hideClass))
                nextContainer.removeClass(hideClass);
            this.nextButton.html(this.stagesNames[this.stage + 1] + " >>");
        }
        for(let i = 0; i < this.stagesNodes.length; ++i)
            if(!this.stagesNodes[i].hasClass(hideClass) && this.stage !== i)
                this.stagesNodes[i].addClass(hideClass);
        const startingStage = this.stagesNames.length === 3 ? 2 : 1;
        $(this.stages).each((i, el) => {$(el).removeClass(activeClass); $(el).removeClass(currentClass)});
        for(let i = 0; i <= this.stage + startingStage; ++i)
            this.stages[i].addClass(activeClass);
        this.stages[this.stage + startingStage].addClass(currentClass);
        this.stagesNodes[this.stage].removeClass(hideClass);
    }
}