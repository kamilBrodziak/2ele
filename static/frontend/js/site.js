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

    let ajaxPagination = new AjaxPagination($('.paginationLinks'), $('#shopPageProductListWidget'));
    ajaxPagination.addAjaxPagination();
    ajaxPagination.addAjaxInputPagination();


    let teaseProduct = new TeaseProducts($('.teaseProduct'), 'teaseProductAnchor');
    teaseProduct.addTeaseProductDisplay();
    teaseProduct.addForm('addToCartForm', 'basketButtonSummary');
    // teaseProduct.addClosing();

    let customSelect = new CustomSelect();
    customSelect.showSelect();
    customSelect.hideSelect();
});

class CustomSelect {
    constructor() {

    }

    showSelect() {
        $('body').on('click', '.customSelect', function(e) {
            $('.customSelectItemsContainer').addClass('customSelectHide')
            $(this).find('.customSelectItemsContainer').removeClass('customSelectHide');

        });

        $('body').on('click', '.customSelectItem', function() {
            let customSelectSelected = $(this).parent().parent().find('.customSelectSelected');
            customSelectSelected.html($(this).html());
            customSelectSelected.data('variation_id', $(this).data('variation_id'));
            let teaseProduct = $(this).closest('.teaseProduct');
            teaseProduct.find('.teaseProductPicture').attr('src', $(this).data('image_src'));
            $(this).siblings('.sameAsSelected').removeClass('sameAsSelected');
            $(this).addClass('sameAsSelected');
            // $('.customSelect select option[selected="selected"]').removeAttr('selected');
            // $('.customSelect select option[value="' + $(this).data('variation_id') + '"]').attr('selected', 'selected');
        })
    }

    hideSelect() {
        $('body').on('click', function (e) {
            if(!e.target.className.includes('customSelectSelected')) {
                $('.customSelectItemsContainer').addClass('customSelectHide');
            }
        });
    }
}

class TeaseProducts {
    constructor(products, anchorClass) {
        this.product = products;
        this.anchorClass = anchorClass;
    }

    addTeaseProductDisplay() {
        let _this = this;
        $('body').on('click', "." + this.anchorClass, function (e) {
            e.preventDefault();
            let productID = $(this).data('product_id');

            // $.ajax({
            //     url: ajaxPaginationParams.ajaxUrl,
            //     type: 'POST',
            //     data: {
            //         productID: productID,
            //         action: 'addProductWidget'
            //     },
            //     beforeSend: function(response) {
            //
            //     },
            //     error: function(response) {
            //     },
            //     success: function (response) {
            //         $('#siteContainer').append(response);
            //     }
            // });
        });
    }

    addClosing() {
        $('body').on('click', function(e) {
            if($(e.target).attr('id') !== 'productWidgetContainer') {
                $('#productWidget').remove();
            }
        });
    }

    addForm(formClass, basketID) {
        $('body').on('submit', '.' + formClass, function (e) {
            e.preventDefault();
            let form = $(this);
            let productID = form.data('product_id'),
                quantity = form.find('.addToCartQuantity').val(),
                variationID = form.find('.customSelectSelected').data('variation_id');
            console.log(variationID);
            $.ajax({
                url: ajaxPaginationParams.ajaxUrl,
                type: 'POST',
                data: {
                    productID: productID,
                    quantity: quantity,
                    variationID: variationID,
                    action: 'addProductToCart'
                },
                beforeSend: function(response) {
                },
                error: function(response) {
                    console.log(response);
                },
                success: function (response) {
                    console.log(response);
                    let basket = $('#' + basketID);
                    basket.html(parseInt(basket.html()) + parseInt(quantity));
                }
            });
        });
    }
}

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

class AjaxPagination {
    constructor(buttons, container) {
        this.buttons = buttons;
        this.container = container;
        this.firstPageChange = true;
    }

    addAjaxPagination() {
        let _this = this;
        $('body').on('click', '.paginationLinks', function (e) {
            e.preventDefault();
            let page = $(this).data('page');
            _this.changePage(page);
        });
    }

    addAjaxInputPagination() {
        let _this = this;
        $('body').on('keyup', '.paginationInput', function (e) {
            e.preventDefault();
            if($(this).val() !== "" && e.keyCode === 13) {
                if (parseInt($(this).val()) > parseInt($(this).attr('max'))) {
                    $(this).val($(this).attr('max'));
                }
                if (parseInt($(this).val()) < parseInt($(this).attr('min'))) {
                    $(this).val($(this).attr('min'));
                }
                    let page = $(this).val();
                    _this.changePage(page);
            }
        });
    }

    changePage(page) {
        let _this = this;
        let pageUrl = ajaxPaginationParams.firstPage;
        if(page !== 1) {
            pageUrl = this.updateQueryStringParameter(pageUrl, 'page', page);
        }
        if(this.firstPageChange) {
            window.history.pushState({"html":this.container.html(),"pageTitle":document.title},"");
            this.firstPageChange = false;
        }

        $.ajax({
            url: ajaxPaginationParams.ajaxUrl,
            type: 'POST',
            data: {
                page: page,
                firstPage: ajaxPaginationParams.firstPage,
                query: ajaxPaginationParams.posts,
                pageCount: ajaxPaginationParams.maxPage,
                category: _this.container.data('category'),
                action: 'changePage'
            },
            beforeSend: function(response) {
                _this.container.addClass('paginationLoading');
            },
            error: function(response) {
                _this.container.removeClass('paginationLoading');
                console.log(response);
            },
            success: function (response) {
                _this.container.removeClass('paginationLoading');
                _this.container.empty().append(response);
                window.document.title = window.document.title.toString().replace('-', '- strona ' + page + ' -');
                window.history.pushState({"html":_this.container.html(),"pageTitle":window.document.title},"", pageUrl);
                window.onpopstate = function(e){
                    if(e.state){
                        _this.container.empty().append(e.state.html);
                        document.title = e.state.pageTitle;
                    }
                };

            }
        });
    }

    updateQueryStringParameter(uri, key, value) {
        if(parseInt(value) === 1) {
            return uri;
        }
        let re = new RegExp("([?&])" + key + "=.*?(&|$)", "i");
        let separator = uri.indexOf('?') !== -1 ? "&" : "?";
        if (uri.match(re)) {
            return uri.replace(re, '$1' + key + "=" + value + '$2');
        } else {
            return uri + separator + key + "=" + value;
        }
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
