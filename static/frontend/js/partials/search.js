class Search {
    constructor(searchFormID) {
        this.searchFormID = searchFormID;
        this.searchForm = $('#' + searchFormID);
        this.searchResults = this.searchForm.find('.searchResults');
        this.searchInput = this.searchForm.find('.searchField');
    }

    addAjaxSearch() {
        let _this = this;
        let label = this.searchInput.parent();
        let data = {
            action: 'searchAjax'
        }
        const beforeSendFunc = (response) => {
            _this.searchResults.empty();
            label.addClass('loadingRight');
            _this.searchResultsClose();
        }, errorFunc = (response) => {
            label.removeClass('loadingRight');
        }, successFunc = (response) => {
            label.removeClass('loadingRight');
            _this.searchResults.empty().append(response);
            _this.searchResultsShow();
        }


        this.searchInput.on('keyup', function (e) {
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
        let _this = this;
        $('body').on('click', function (e) {
            if(!_this.searchForm.is(e.target) && _this.searchForm.has(e.target).length === 0) {
                _this.searchResultsClose();
            }
        })
    }
}