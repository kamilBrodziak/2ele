class CustomSelect {
    constructor() {

    }

    showSelect() {
        const _this = this, body = $('body');
        body.on('click', '.customSelect', function(e) {
            const isClosed = $(this).find('.customSelectItemsContainer').hasClass('hide')
            _this.hideSelect();
            if(isClosed) {
                $(this).find('.customSelectItemsContainer').removeClass('hide');
                $(this).find('.customSelectSelected').addClass('selectArrowActive');
            }

        });

        body.on('click', '.customSelectItem', function(e) {
            const teaseProduct = $(this).closest('.teaseProduct'), selectedClass = 'sameAsSelected';
            const customSelectSelected = teaseProduct.find('.customSelectSelected'),
                teaseProductQuantity = teaseProduct.find('.teaseProductFormQuantity'),
                quantity = parseInt($(this).data('max_quantity'));
            customSelectSelected.html($(this).html());
            customSelectSelected.data('variation_id', $(this).data('variation_id'));
            teaseProduct.find('.teaseProductFormSubmit').removeAttr('disabled');
            teaseProduct.find('.teaseProductPicture').attr('src', $(this).data('image_src'));
            if(quantity <= 99) {
                teaseProductQuantity.attr('max', quantity);
            }
            if(parseInt(teaseProductQuantity.val()) > quantity) {
                teaseProductQuantity.val(quantity);
            }
            $(this).siblings('.' + selectedClass).removeClass(selectedClass);
            $(this).addClass(selectedClass);
        })
    }

    hideSelectWhenClickedOutsideSelect() {
        const _this = this;
        $('body').on('click', (e) => {
            if(!e.target.className.includes('customSelectSelected')) {
                _this.hideSelect();
            }
        });
    }

    hideSelect() {
        $('.customSelectItemsContainer').addClass('hide');
        $('.selectArrowActive').removeClass('selectArrowActive');
    }
}