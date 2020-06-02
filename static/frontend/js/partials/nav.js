class MobileNav {
    constructor(nav, showClass, closeButton) {
        this.nav = nav;
        this.showClass = showClass;
        this.closeButton = closeButton;
        this.closeButtonAnimationClass = null;
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

    addNavSubListExpandButton(button, subListClass, showClass, buttonAnimationClass) {
        let _this = this;
        button.each( function() {
            let b = $(this);
            $(this).on('click', function () {
                let subList = b.parent().children('.' + subListClass);
                if(subList.hasClass(showClass)) {
                    b.removeClass(buttonAnimationClass);
                    subList.removeClass(showClass);
                } else {
                    b.addClass(buttonAnimationClass);
                    subList.addClass(showClass);
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

    addCloseButtonAnimation(cssClass) {
        this.closeButtonAnimationClass = cssClass;
    }

    expandNav() {
        this.nav.addClass(this.showClass);
        $('body').addClass(this.showClass);
        if(this.closeButtonAnimationClass && !this.closeButton.hasClass(this.closeButtonAnimationClass)) {
            this.closeButton.addClass(this.closeButtonAnimationClass);
        }
    }

    shrinkNav() {
        this.nav.removeClass(this.showClass);
        $('body').removeClass(this.showClass);
        if(this.closeButtonAnimationClass) {
            this.closeButton.removeClass(this.closeButtonAnimationClass);
        }
    }
}