class MobileNav {
    constructor(nav, showClass, closeButton) {
        this.nav = nav;
        this.showClass = showClass;
        this.closeButton = closeButton;
        this.addCloseButton();
    }

    addCloseButton() {
        let _this = this;
        this.closeButton.on('click', function () {
            if(_this.nav.hasClass(_this.showClass)) {
                _this.shrinkNav();
            } else {
                _this.expandNav();
            }
        })
    }

    addNavSubListExpandButton(button, subListClass, expandClass) {
        let _this = this;
        button.each( function() {
            let b = $(this);
            $(this).on('click', function () {
                let subList = b.parent().children('.' + subListClass);
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
        let _this = this;
        $(window).on('resize', function() {
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