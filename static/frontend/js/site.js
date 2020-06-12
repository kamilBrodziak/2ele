$( function() {
    // let parallax = new ParallaxBgPicture($('.parallaxBg'));
    // parallax.toggleParallax();
    let emailFormHandler = new EmailFormHandler($('#pageContactForm'), "sendUserEmail","pageContactFormSuccessInfo" );
    emailFormHandler.submitEvent();
    let vh = window.innerHeight * 0.01;
    document.documentElement.style.setProperty('--vh', `${vh}px`);
    $(window).on('resize', function (e) {
        vh = window.innerHeight * 0.01;
        document.documentElement.style.setProperty('--vh', `${vh}px`);
    });

    let mobileNav = new MobileNav($('#shopNav'), 'shopNavDisplay', $('#shopNavMobileCloseButton'));
    mobileNav.addCloseButtonAnimation('changeMobileNavCloseButtonState');
    mobileNav.addCloseOnResizeEvent();
    mobileNav.addNavSubListExpandButton($('.shopNavListItemSubListExpandButton'),
        'shopNavListItemSubList', 'showShopNavListItemSubList',
        'shopNavListItemSubListExpandButtonAnimation');

    let ajaxPagination = new AjaxPagination($('.paginationLinks'), 'shopPageProductListWidget');
    ajaxPagination.addAjaxPagination();
    ajaxPagination.addAjaxInputPagination();


    let teaseProduct = new TeaseProducts($('.teaseProduct'), 'teaseProductAnchor');
    // teaseProduct.addTeaseProductDisplay();
    teaseProduct.addForm('teaseProductForm', 'basketButtonSummary');
    // teaseProduct.addClosing();

    let customSelect = new CustomSelect();
    customSelect.showSelect();
    customSelect.hideSelectWhenClickedOutsideSelect();

    let cartWidget = new CartWidget($('#cartPageCartWidgetContainer'));
    cartWidget.withWidget();
    let search = new Search('shopSearch');
    search.addAjaxSearch();
    search.closingSearchResultEvent();
    let loginPageLoginWidgetContainer = $('#accountPageLoginWidgetContainer');
    let loginPageLoginWidget = new LoginWidget(loginPageLoginWidgetContainer);
    loginPageLoginWidget.withWidget();
    loginPageLoginWidget.addLoginAjax(true);
    // loginPageLoginWidget.addNav('loginWidgetNavListItem', 'loginWidgetNavList');
    // loginPageLoginWidget.addRegisterValidation();
    // loginPageLoginWidget.addRegisterAjax();

    let orderWidget = new OrderWidget($('<div id="#orderWidgetContainer" class="widget">'));
    orderWidget.loadWidgetAjaxViaButton($('#basketButton'));

    let cartPageOrderWidget = new OrderWidget($('#cartPageCartWidgetContainer'));
    cartPageOrderWidget.withWidget();
    // orderWidget.orderShow('basketButton');
    // orderWidget.addCloseWhenClickOutside();
    // orderWidget.addCloseButton('orderWidgetCloseButton');

});

function addInputMinMaxTesting(input) {
    let min = input.attr('min'), max = input.attr('max'), val = input.val();
    if (max && parseInt(val) > parseInt(max)) {
        input.val(max);
    } else if (min && parseInt(val) < parseInt(min)) {
        input.val(min);
    } else if(val === '') {
        if(min) {
            input.val(min);
        } else {
            input.val(1);
        }
    }
}

function ajaxCall(data, beforeSendFunc, errorFunc, successFunc) {
    $.ajax({
        url: ajaxPaginationParams.ajaxUrl,
        type: 'POST',
        data: data,
        beforeSend: beforeSendFunc,
        error: errorFunc,
        success: successFunc
    });
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
                name = form.find('.contactFormName').val(),
                email = form.find('.contactFormEmail').val(),
                message = form.find('.contactFormMessage').val(),
                ajaxUrl = form.data('url');
            self.sendEmail(name, email, message, ajaxUrl);
        });
    }

    sendEmail(name, email, message, ajaxUrl) {
        let self = this;
        $.ajax({
            url: ajaxUrl,
            type: 'post',
            data: {
                name: name,
                email: email,
                message: message,
                action: self.phpHandlerFuncName
            },
            beforeSend: function(response) {
                self.form.addClass('loadingScreen');
                self.form.find("#ftrContactFormSubmit").prop("disabled", true);
            },
            error: function (response) {
                console.log(response);
                self.form.removeClass('loadingScreen');
                self.displayMessage("contactFormFailInfo");
                self.form.find("#ftrContactFormSubmit").prop("disabled", false)
            },
            success: function (response) {
                console.log(response);
                console.log(message);
                self.form.removeClass('loadingScreen');
                self.displayMessage("contactFormSuccessInfo");
                self.form.find("#ftrContactFormSubmit").prop("disabled", false)
            }
        });
    }

    displayMessage(messageClass) {
        let successInfoElement = this.form.find("." + messageClass);
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
