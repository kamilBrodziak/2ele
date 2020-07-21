class MobileNav {
    constructor(nav, showClass, closeButton) {
        this.nav = nav;
        this.showClass = showClass;
        this.closeButton = closeButton;
        this.addCloseButton();
    }

    addCloseButton() {
        const _this = this;
        this.closeButton.on('click', () => {
            if(_this.nav.hasClass(_this.showClass)) {
                _this.shrinkNav();
            } else {
                _this.expandNav();
            }
        })
    }

    addNavSubListExpandButton(button, subListClass, expandClass) {
        button.each( () => {
            const b = $(this);
            b.on('click', () => {
                const subList = b.parent().children('.' + subListClass);
                if(subList.hasClass(expandClass)) {
                    b.removeClass(expandClass);
                    subList.removeClass(expandClass);
                } else {
                    b.addClass(expandClass);
                    subList.addClass(expandClass);
                }
            })
        });
    }

    addCloseOnResizeEvent() {
        const _this = this;
        $(window).on('resize',() => {
            _this.shrinkNav();
        });
    }

    expandNav() {
        this.nav.addClass(this.showClass);
        $('body').addClass(this.showClass);
    }

    shrinkNav() {
        this.nav.removeClass(this.showClass);
        $('body').removeClass(this.showClass);
    }
}