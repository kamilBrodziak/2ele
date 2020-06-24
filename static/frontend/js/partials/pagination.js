class AjaxPagination {
    constructor(buttons, containerID) {
        this.buttons = buttons;
        this.containerID = containerID;
        this.container = $('#' + containerID);
        this.firstPageChange = true;
    }

    addAjaxPagination() {
        let _this = this;
        $('body').on('click', '#' + _this.containerID + ' .paginationLinks', function (e) {
            e.preventDefault();
            let page = parseInt($(this).data('page'));
            _this.changePage(page);
        });
    }

    addAjaxInputPagination() {
        let _this = this;
        $('body').on('keyup focusout','#' + _this.containerID +  ' .paginationInput', function (e) {
            e.preventDefault();
            let input = $(this);
            if((e.type === "keyup" && input.val() !== "" && e.keyCode === 13) || e.type==="focusout") {
                $.when(addInputMinMaxTesting(input)).done(function () {
                    _this.changePage(input.val());
                })
            }
        });
    }

    changePage(page) {
        let _this = this;

        if(this.firstPageChange) {
            window.history.pushState({"html":this.container.html(),"pageTitle":document.title},"");
            this.firstPageChange = false;
        }

        let data = {
            page: page,
            maxPage: ajaxPaginationParams.maxPage,
            action: 'changePage'
        }
        let category = _this.container.data('category');
        let search = _this.container.data('search');
        if (category) {
            data.category = category;
        }
        if(search) {
            data.search = _this.container.data('search');
        }
        this.reloadPageAjax(data, page);
    }

    reloadPageAjax(data, page) {
        let _this = this;
        let pageUrl = window.location.href.split('?')[0];
        let queryParams = window.location.href.split('?')[1];
        if(!queryParams) {
            queryParams = "";
        }else {
            queryParams = "?" + queryParams;
        }
        if(page === 1) {
            pageUrl = pageUrl.replace(/page\/[0-9]+(\/)?/, "");
        } else {
            if(pageUrl.match(/page\/[0-9]+/)) {
                pageUrl = pageUrl.replace(/page\/[0-9]+/, "page/" + page);
            } else {
                pageUrl += "page/" + page + "/";
            }
        }
        pageUrl += queryParams;

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
}