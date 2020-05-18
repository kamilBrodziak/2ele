$( function() {
    // let parallax = new ParallaxBgPicture($('.parallaxBg'));
    // parallax.toggleParallax();
    // let mobileNav = new MobileNav($("#hdrNavbarMobileButton"), $("#hdrNavbar"), $("#siteHeader"));
    // mobileNav.addHeaderFade();
    // let emailFormHandler = new EmailFormHandler($('#ftrContactForm'), "sendUserEmail","ftrContactFormSuccessInfo" );
    // emailFormHandler.submitEvent();
    let vh = window.innerHeight * 0.01;
    document.documentElement.style.setProperty('--vh', `${vh}px`);

    let mobileNav = new MobileNav($('#shopNav'), 'shopNavDisplay', $('#shopNavMobileCloseButton'));
    mobileNav.addCloseButtonAnimation('changeMobileNavCloseButtonState');
    mobileNav.addCloseOnResizeEvent();
    mobileNav.addNavSubListExpandButton($('.shopNavListItemSubListExpandButton'),
        'shopNavListItemSubList', 'showShopNavListItemSubList',
        'shopNavListItemSubListExpandButtonAnimation');

});

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
                    console.log(b);
                    b.addClass(buttonAnimationClass);
                    subList.addClass(showClass);
                }
            })
        });
        // button.on('click', function() {
        //     console.log("pp");
        //     let subList = button.parent();
        //     console.log($('.shopNavListItemSubListExpandButton').className);
        //     if(subList.hasClass(showClass)) {
        //         button.removeClass(buttonAnimationClass);
        //         subList.removeClass(showClass);
        //     } else {
        //         button.addClass(buttonAnimationClass);
        //         subList.addClass(showClass);
        //     }
        //
        // })
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

class MobileNavs {
    constructor(mobileNavButton, navbar, header) {
        this.mobileNavButton = mobileNavButton;
        this.navbar = navbar;
        this.header = header;
        this.lastScrollTop = header.offset().top;
        this.enableMobileNavbarButton();
        this.closeNavByClickEvent();
        this.closeNavWhenResizeEvent();
        this.closeNavWhenScrollEvent();
        this.changeNavMode();
    }

    closeNavByClickEvent() {
        let self = this;
        $(document).mouseup(function(e) {
            if (self.mobileNavButton.hasClass("changeMobileNavButtonState") && !self.mobileNavButton.is(e.target) &&
                !self.navbar.is(e.target) && self.navbar.has(e.target).length === 0) {
                    self.closeNav();
            }
        });
    }

    closeNavWhenResizeEvent() {
        let self = this;
        $(window).resize(function() {
            self.changeNavMode();
        });
    }

    closeNavWhenScrollEvent() {
        let self = this;
        $(window).scroll(function() {
            if(self.mobileNavButton.css("display") !== "none") {
                self.closeNav();
            }
        });
    }

    changeNavMode() {
        let self = this, doneCallback, promise;
        this.mobileNavButton.removeClass("changeMobileNavButtonState");
        if(this.mobileNavButton.css("display") === "none") {
            promise = () => {self.navbar.removeClass("hdrNavbarMobileTransition")};
            doneCallback = () => {self.navbar.css("width","100%")};
        } else {
            promise = () => {self.navbar.css("width", 0)};
            doneCallback = () => {self.navbar.addClass("hdrNavbarMobileTransition")};
        }
        $.when(promise()).then(doneCallback);
    }

    enableMobileNavbarButton() {
        let self = this;
        this.mobileNavButton.on("click", function() {
            if(parseFloat(self.navbar.css("width")) > 0) {
                self.closeNav();
            } else {
                self.openNav();
            }
        });
    }

    headerFade() {
        let scrollTop = $(window).scrollTop();
        if (scrollTop <= parseFloat(this.header.css("height"))) {
            this.header.removeClass(['stickyFade', 'stickyTransition']);
        } else if (this.lastScrollTop >= scrollTop || scrollTop <= parseFloat(this.header.css("height"))) {
            this.header.removeClass('stickyFade');
            this.header.addClass('stickyTransition');
        } else if(scrollTop > parseFloat(this.header.css("height")) ){
            this.header.addClass(['stickyFade', 'stickyTransition']);
        }
        this.lastScrollTop = scrollTop;
    };

    addHeaderFade() {
        this.headerFade();
        let self = this;
        $(window).scroll(function() {
            self.headerFade();
        });
    }

    closeNav() {
        this.navbar.css("width", 0);
        this.mobileNavButton.removeClass("changeMobileNavButtonState");
    }

    openNav() {
        this.mobileNavButton.addClass("changeMobileNavButtonState");
        this.navbar.css("width", "70%");
    }
}

class EmailFormHandler {
    constructor(form, phpHandlerFuncName, successInfoId) {
        this.form = form;
        this.phpHandlerFuncName = phpHandlerFuncName;
        this.successInfoId = successInfoId;
    }

    submitEvent() {
        let self = this;
        self.form.on('submit', function (e) {
            e.preventDefault();
            let form = self.form,
                name = form.find('#ftrContactFormName').val(),
                email = form.find('#ftrContactFormEmail').val(),
                message = form.find('#ftrContactFormMessage').val(),
                ajaxUrl = form.data('url');
            self.sendEmail(name, email, message, ajaxUrl);
        });
    }

    sendEmail(name, email, message, ajaxUrl) {
        let self = this;
        self.form.find("#ftrContactFormSubmit").prop("disabled", true);
        $.ajax({
            url: ajaxUrl,
            type: 'post',
            data: {
                name: name,
                email: email,
                message: message,
                action: self.phpHandlerFuncName
            },
            error: function (response) {
                console.log(response);
                self.form.find("#ftrContactFormSubmit").prop("disabled", false)
            },
            success: function (response) {
                console.log(response);
                console.log(message);
                self.displaySuccessMessage();
                self.form.find("#ftrContactFormSubmit").prop("disabled", false)
            }
        });
    }

    displaySuccessMessage() {
        let successInfoElement = this.form.find("#" + this.successInfoId);
        successInfoElement.css("display", "flex");
        setTimeout(function() {
            successInfoElement.css("display", "none");
        }, 2000);
    }
}

class ParallaxBgPicture {
    constructor(parallaxBgDOM) {
        this.parallaxBgDOM = parallaxBgDOM;
        this.parallaxBgInitialPosY = 0;
        this.adjustParallaxWhenBrowserResize();
    }

    setParallaxBgPosY() {
        this.parallaxBgInitialPosY = parseFloat($("header").css('height'));
    }

    adjustParallaxWhenBrowserResize() {
        let self = this;
        $(window).resize(function() {
            self.setParallaxBgPosY();
            self.scrollParallax(parseFloat($(window).scrollTop()));
        });
    }

    toggleParallax() {
        let self = this;
        this.setParallaxBgPosY();
        this.scrollParallax($(window).scrollTop());
        $(window).scroll(function() {
            self.scrollParallax(this.scrollY);
        });
    }

    scrollParallax(scrollY) {
        let newBgPosY = this.parallaxBgInitialPosY - scrollY > 0 ? this.parallaxBgInitialPosY - scrollY : 0;
        this.parallaxBgDOM.css("background-position-y", newBgPosY);
    }
}
