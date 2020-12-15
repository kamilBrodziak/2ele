class Gallery {
    constructor(container) {
        this.container = container;
        this.nextButton = container.find('.teaseProductGalleryNext');
        this.prevButton = container.find('.teaseProductGalleryPrev');
        this.picturesObjs = [];
        this.leftClass = 'left';
        this.rightClass = 'right';
        this.moving = false;
        const _this = this;
        container.find('.teaseProductGalleryPicture').each((i, el) => {
            _this.picturesObjs.push($(el));
        })
        this.imagesObjs = [];
        container.find('.teaseProductGalleryPicture img').each((i, el) => {
            _this.imagesObjs.push($(el));
        })
        this.ind = parseInt(_this.picturesObjs.length / 2);
        this.images = container.data('imgs');
        this.buttonClick(this.nextButton, true);
        this.buttonClick(this.prevButton, false);
    }

    move(el, className, shouldAdd) {
        const _this = this;
        el.css('transition', 'all 0.2s ease');
        if(shouldAdd)
            el.addClass(className);
        else
            el.removeClass(className);
        setTimeout(() => {
            el.css('transition', 'none');
            _this.moving = false;
        }, 200);
    }

    buttonClick(button, isNext) {
        const _this = this;
        button.on('click', () => {
            if(!_this.moving) {
                _this.moving = true;
                const prevClass = isNext ? _this.leftClass : _this.rightClass;
                const nextClass = isNext ? _this.rightClass : _this.leftClass;
                _this.move(_this.picturesObjs[_this.ind], prevClass, true);
                _this.move(_this.picturesObjs[_this.ind + (isNext ? 1 : -1)], nextClass, false);
                let changing;
                if(isNext) {
                    changing = _this.picturesObjs.shift();
                    _this.picturesObjs.push(changing);
                } else {
                    changing = _this.picturesObjs.pop();
                    _this.picturesObjs.unshift(changing);
                }
                changing.removeClass(prevClass);
                changing.addClass(nextClass);
            }
        })
    }

    next() {
        const _this = this;
        _this.nextButton.on('click', () => {
            if(!_this.moving) {
                _this.moving = true;
                _this.move(_this.picturesObjs[_this.ind], _this.leftClass, true);
                _this.move(_this.picturesObjs[_this.ind + 1], _this.rightClass, false);
                const first = _this.picturesObjs.shift();
                first.removeClass(_this.leftClass);
                first.addClass(_this.rightClass);
                _this.picturesObjs.push(first);
            }
        });
    }

    prev() {
        const _this = this;
        _this.prevButton.on('click', () => {
            if(!_this.moving) {
                _this.moving = true;
                _this.move(_this.picturesObjs[_this.ind], _this.rightClass, true);
                _this.move(_this.picturesObjs[_this.ind - 1], _this.leftClass, false);
                const last = _this.picturesObjs.pop();
                last.removeClass(_this.rightClass);
                last.addClass(_this.leftClass);
                _this.picturesObjs.unshift(last);
            }
        });
    }

}