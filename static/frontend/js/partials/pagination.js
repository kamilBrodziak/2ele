class AjaxPagination {
    constructor(container, containerID) {
        this.containerID = containerID;
        this.container = container;
        this.firstPageChange = true;
    }

    addAjaxPagination() {
        const _this = this;
        $('body').on('click', '#' + _this.containerID + ' .paginationLink', (e) => {
            e.preventDefault();
            const page = parseInt($(this).data('page'));
            _this.changePage(page);
        });
    }
    // input page change, currently commented in html
    // addAjaxInputPagination() {
    //     const _this = this;
    //     $('body').on('keyup focusout','#' + _this.containerID +  ' .paginationInput', (e) => {
    //         e.preventDefault();
    //         const input = $(this);
    //         if((e.type === "keyup" && input.val() !== "" && e.keyCode === 13) || e.type==="focusout") {
    //             $.when(addInputMinMaxTesting(input)).done(() => {
    //                 _this.changePage(input.val());
    //             })
    //         }
    //     });
    // }

    changePage(page) {
        const _this = this,
            data = {
                page: page,
                maxPage: ajaxPaginationParams.maxPage,
                action: 'changePage'
            },
            category = _this.container.data('category'),
            search = _this.container.data('search');

        if(this.firstPageChange) {
            window.history.pushState({"html":this.container.html(),"pageTitle":document.title},"");
            this.firstPageChange = false;
        }
        if (category) {
            data.category = category;
        }
        if(search) {
            data.search = _this.container.data('search');
        }
        this.reloadPageAjax(data, page);
    }

    reloadPageAjax(data, page) {
        const _this = this, loadingClass = 'loadingScreen';
        let pageUrl = window.location.href.split('?')[0],
            queryParams = window.location.href.split('?')[1];
        queryParams = !queryParams ? "" : "?" + queryParams;
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

        const beforeSendFunc = () => {
            _this.container.addClass(loadingClass);
        }, errorFunc = (response) => {
            _this.container.removeClass(loadingClass);
            console.log(response);
        }, successFunc = (response) => {
            _this.container.removeClass(loadingClass);
            _this.container.empty().append(response);
            window.document.title = window.document.title.toString().replace('-', '- strona ' + page + ' -');
            window.history.pushState({"html":_this.container.html(),"pageTitle":window.document.title},"", pageUrl);
            window.onpopstate = (e) => {
                if(e.state){
                    _this.container.empty().append(e.state.html);
                    document.title = e.state.pageTitle;
                }
            };
        }
        ajaxCall(data, beforeSendFunc, errorFunc, successFunc);
    }
}