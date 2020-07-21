$( function() {
    let vh = window.innerHeight * 0.01;
    document.documentElement.style.setProperty('--vh', `${vh}px`);
    $(window).on('resize', () => {
        vh = window.innerHeight * 0.01;
        document.documentElement.style.setProperty('--vh', `${vh}px`);
    });
    const mobileNav = new MobileNav($('#shopNavWrapper'), 'shopNavDisplay', $('#shopNavMobileCloseButton'));
    mobileNav.addCloseOnResizeEvent();
    mobileNav.addNavSubListExpandButton($('.shopNavListItemSubListExpandButton'),'shopNavListItemSubList', 'expand');
    const search = new Search('shopSearch');
    search.addAjaxSearch();
    search.closingSearchResultEvent();

    
    const contactPageForm = $('#pageContactForm');
    if(contactPageForm.length) {
        const emailFormHandler = new EmailFormHandler(contactPageForm );
        emailFormHandler.submitEvent();
    }


    const shopProductListWidget = $('#shopPageProductListWidget');
    if(shopProductListWidget.length) {
        const ajaxPagination = new AjaxPagination(shopProductListWidget, 'shopPageProductListWidget');
        ajaxPagination.addAjaxPagination();
        // ajaxPagination.addAjaxInputPagination();
    }


    const teaseProductEl = $('.teaseProduct');
    if(teaseProductEl.length) {
        const teaseProduct = new TeaseProducts(teaseProductEl, 'teaseProductAnchor');
        // teaseProduct.addTeaseProductDisplay();
        teaseProduct.addForm('teaseProductForm', 'cartButtonQuantity');
        // teaseProduct.addClosing();
        const customSelect = new CustomSelect();
        customSelect.showSelect();
        customSelect.hideSelectWhenClickedOutsideSelect();
    }


    const loginPageLoginWidgetContainer = $('#accountPageLoginWidgetContainer');
    if(loginPageLoginWidgetContainer.length) {
        const loginPageLoginWidget = new LoginWidget(loginPageLoginWidgetContainer);
        loginPageLoginWidget.withWidget(true);
    }


    const cartPageOrderWidgetContainer  = $('#cartPageCartWidgetContainer');
    if(cartPageOrderWidgetContainer.length) {
        const cartPageOrderWidget = new OrderWidget(cartPageOrderWidgetContainer);
        cartPageOrderWidget.withWidget();
    }


    const cartButton = $('#cartButton');
    if(cartButton.length) {
        const orderWidget = new OrderWidget($('<div id="#orderWidgetContainer" class="widget">'));
        orderWidget.loadWidgetAjaxViaButton(cartButton);
    }


    const checkoutPage = $('#checkoutPage');
    if(checkoutPage.length) {
        const checkoutPageCheckoutWidget = new CheckoutWidget(checkoutPage);
        checkoutPageCheckoutWidget.withWidget();
    }

});

function addInputMinMaxTesting(input) {
    const min = input.attr('min'), max = input.attr('max'), val = input.val();
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