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
        $('body').on('keyup focusout', '.paginationInput', function (e) {
            e.preventDefault();
            let input = $(this);
            if((e.type === "keyup" && input.val() !== "" && e.keyCode === 13) || e.type==="focusout") {
                $.when(addInputMinMaxTesting(input)).done(function () {
                    _this.changePage(input.val());
                })
            }
        });
    }

    reloadPageAjax(data, page) {
        let _this = this;
        let pageUrl = ajaxPaginationParams.firstPage;
        if(page !== 1) {
            pageUrl = this.updateQueryStringParameter(pageUrl, 'page', page);
        }
        let beforeSendFunc = (response) => {
            _this.container.addClass('loadingScreen');
        }, errorFunc = (response) => {
            _this.container.removeClass('loadingScreen');
            console.log(response);
        }, successFunc = (response) => {
            _this.container.removeClass('loadingScreen');
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
        ajaxCall(data, beforeSendFunc, errorFunc, successFunc);
    }

    changePage(page) {
        let _this = this;

        if(this.firstPageChange) {
            window.history.pushState({"html":this.container.html(),"pageTitle":document.title},"");
            this.firstPageChange = false;
        }

        let data = {
            page: page,
            firstPage: ajaxPaginationParams.firstPage,
            query: ajaxPaginationParams.posts,
            pageCount: ajaxPaginationParams.maxPage,
            category: _this.container.data('category'),
            action: 'changePage'
        }
        this.reloadPageAjax(data, page);
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