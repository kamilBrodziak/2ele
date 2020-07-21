class LoginWidget {
    constructor(widgetContainer) {
        this.widgetContainer = widgetContainer;
        this.widget = null;
        this.loadingClass = 'loadingScreen';
    }

    withWidget(submitWhenLogin = false) {
        if(this.widget) {
            this.widget.remove();
        }
        this.widget = this.widgetContainer.find('.loginWidget');
        this.registerForm = this.widget.find('.registerForm');
        this.registerFormInputs = {
            'username': this.registerForm.find('.registerFormUsernameInput'),
            'email': this.registerForm.find('.registerFormEmailInput'),
            'confirmEmail': this.registerForm.find('.registerFormConfirmEmailInput'),
            'password': this.registerForm.find('.registerFormPasswordInput'),
            'confirmPassword': this.registerForm.find('.registerFormConfirmPasswordInput'),
            'submit': this.registerForm.find('.registerFormSubmit')
        }

        this.loginForm = this.widget.find('.loginForm');
        this.loginFormInputs = {
            'username': this.loginForm.find('.loginFormUsernameInput'),
            'password': this.loginForm.find('.loginFormPasswordInput'),
            'remember': this.loginForm.find('.loginFormRememberCheckbox')
        }
        this.addNav('loginWidgetNavListItem', 'loginWidgetNavList');
        this.addRegisterValidation();
        this.addRegisterAjax();
        this.addLoginAjax(submitWhenLogin);
    }

    loadWidgetAjax(submitWhenLogin = false) {
        const data = {
                'action': 'loadLoginWidgetAjax'
            },
            _this = this,
            widget = this.widgetContainer.find('.loginWidget'),
            beforeSend = () => {
                _this.widgetContainer.addClass(_this.loadingClass);
            }, error = (response) => {
                _this.widgetContainer.removeClass(_this.loadingClass);
                console.log(response);
            }, success = (response) => {
                _this.widgetContainer.removeClass(_this.loadingClass);
                _this.widgetContainer.empty().append(response);
                if(widget) {
                    _this.withWidget(submitWhenLogin);
                }
            }
        ajaxCall(data, beforeSend, error, success);
    }

    addNav(navListItemClass, navListClass) {
        const _this = this, navList = this.widget.find('.' + navListClass),
            sections = {},
            navListItems = navList.find('.' + navListItemClass),
            activeClass = 'active',
            hideClass = 'hide';
        navListItems.each((i, el) => {
            const listItem = $(el),
                sectionClass = listItem.data('section');
            sections[sectionClass] = _this.widget.find('.' + sectionClass);
            $(el).on('click', (e) => {
                e.preventDefault();
                const newActive = $(el),
                    currentActive = navList.find('.' + activeClass);
                currentActive.removeClass(activeClass);
                currentActive.removeAttr('disabled');
                sections[currentActive.data('section')].addClass(hideClass);
                newActive.addClass(activeClass);
                newActive.attr('disabled', true);
                sections[newActive.data('section')].removeClass(hideClass);
            });
        })
    }

    addRegisterAjax() {
        const username = this.registerFormInputs.username,
            email = this.registerFormInputs.email,
            password = this.registerFormInputs.password,
            notices = this.registerForm.find('.registerFormNotices'),
            _this = this,
            data = {
                'action': 'userRegisterAjax',
            };
        const beforeSendFunc = () => {
            _this.registerForm.addClass(_this.loadingClass);
        }, errorFunc = () => {
            _this.registerForm.removeClass(_this.loadingClass);
        }, successFunc = (response) => {
            _this.registerForm.removeClass(_this.loadingClass);
            if(response === 'success') {
                username.val('');
                email.val('');
                _this.registerFormInputs.confirmEmail.val('');
                password.val('');
                _this.registerFormInputs.confirmPassword.val('');
                notices.html('Rejestracja przebiegła pomyślnie! Możesz teraz zalogować się.');
            } else {
                notices.html(response);
            }
            $("html, body").animate({ scrollTop: 0 }, 600);
        };
        this.registerForm.on('submit', (e) => {
            e.preventDefault();
            data.username = username.val();
            data.email = email.val();
            data.password = password.val();
            ajaxCall(data, beforeSendFunc, errorFunc, successFunc);
        })
    }

    addLoginAjax(submit = false) {
        const username = this.loginFormInputs.username,
            password = this.loginFormInputs.password,
            remember = this.loginFormInputs.remember,
            _this = this,
            beforeSendFunc = () => {
                _this.loginForm.addClass(_this.loadingClass);
            }, errorFunc = () => {
                _this.loginForm.removeClass(_this.loadingClass);
            }, successFunc = (response) => {
                _this.loginForm.removeClass(_this.loadingClass);
                if(response === 'success') {
                    if(submit) {
                        location.reload();
                    } else {
                        _this.loadLogoutWidget();
                    }
                } else {
                    _this.loginForm.find('.loginFormNotices').html(response);
                }
                this.widgetContainer.animate({ scrollTop: 0 }, 600);
            };

        const data = {
            'action': 'userLoginAjax',
            'signOn': !submit
        }
        this.loginForm.on('submit',(e) => {
            e.preventDefault();
            data.username = username.val();
            data.password = password.val();
            data.remember = remember.is(':checked');
            ajaxCall(data, beforeSendFunc, errorFunc, successFunc);
        })
    }

    loadLogoutWidget() {
        const _this = this,
            data = {
                'action': 'userLogoutAjax',
            },
            logoutButton = $('<button class="buttonBlackAnimated" value="Wyloguj się">'),
            beforeSendFunc = () => {
                _this.widget.addClass(_this.loadingClass);
            }, errorFunc = () => {
                _this.widget.removeClass(_this.loadingClass);
            }, successFunc = () => {
                _this.widget.removeClass(_this.loadingClass);
                _this.loadWidgetAjax();
                this.widgetContainer.animate({ scrollTop: 0 }, 600);
            };
        this.widget.empty();
        this.widget.append(logoutButton);
        logoutButton.on('click', () => {
            ajaxCall(data, beforeSendFunc, errorFunc, successFunc);
        })
    }

    addRegisterValidation() {
        const validator = new FormValidation();
        validator.addBasicValidation(this.registerFormInputs.username, this.registerFormInputs.email, this.registerFormInputs.confirmEmail,
            this.registerFormInputs.password, this.registerFormInputs.confirmPassword, this.registerFormInputs.submit);
    }
}