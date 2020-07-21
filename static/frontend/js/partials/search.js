class Search {
    constructor(searchFormID) {
        this.searchForm = $('#' + searchFormID);
        this.searchResults = this.searchForm.find('.searchResults');
        this.searchInput = this.searchForm.find('.searchField');
    }

    addAjaxSearch() {
        const _this = this, loadingClass = 'loadingRight',
            label = this.searchInput.parent(),
            data = {
                action: 'searchAjax'
            },
            beforeSendFunc = () => {
                _this.searchResults.empty();
                label.addClass(loadingClass);
                _this.searchResultsClose();
            }, errorFunc = () => {
                label.removeClass(loadingClass);
            }, successFunc = (response) => {
                label.removeClass(loadingClass);
                _this.searchResults.empty().append(response);
                _this.searchResultsShow();
            }


        this.searchInput.on('keyup', () => {
            _this.searchResultsClose();
            data['searchPhrase'] = _this.searchInput.val();
            ajaxCall(data, beforeSendFunc, errorFunc, successFunc);
        });
    }

    searchResultsClose() {
        this.searchResults.removeClass('active');
    }

    searchResultsShow() {
        this.searchResults.addClass('active');
    }

    closingSearchResultEvent() {
        const _this = this;
        $('body').on('click', (e) => {
            if(!_this.searchForm.is(e.target) && _this.searchForm.has(e.target).length === 0) {
                _this.searchResultsClose();
            }
        })
    }
}