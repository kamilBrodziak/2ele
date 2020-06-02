class CustomSelect {
    constructor() {

    }

    showSelect() {
        let _this = this;
        $('body').on('click', '.customSelect', function(e) {
            let isClosed = $(this).find('.customSelectItemsContainer').hasClass('customSelectHide')
            _this.hideSelect();
            if(isClosed) {
                $(this).find('.customSelectItemsContainer').removeClass('customSelectHide');
                $(this).find('.customSelectSelected').addClass('selectArrowActive');
            }

        });

        $('body').on('click', '.customSelectItem', function() {
            let customSelectSelected = $(this).parent().parent().find('.customSelectSelected');
            customSelectSelected.html($(this).html());
            customSelectSelected.data('variation_id', $(this).data('variation_id'));
            let teaseProduct = $(this).closest('.teaseProduct');
            teaseProduct.find('.teaseProductFormSubmit').removeAttr('disabled');
            teaseProduct.find('.teaseProductPicture').attr('src', $(this).data('image_src'));
            $(this).siblings('.sameAsSelected').removeClass('sameAsSelected');
            $(this).addClass('sameAsSelected');
            // $('.customSelect select option[selected="selected"]').removeAttr('selected');
            // $('.customSelect select option[value="' + $(this).data('variation_id') + '"]').attr('selected', 'selected');
        })
    }

    hideSelectWhenClickedOutsideSelect() {
        let _this = this;
        $('body').on('click', function (e) {
            if(!e.target.className.includes('customSelectSelected')) {
                _this.hideSelect();
            }
        });
    }

    hideSelect() {
        $('.customSelectItemsContainer').addClass('customSelectHide');
        $('.selectArrowActive').removeClass('selectArrowActive');
    }
}