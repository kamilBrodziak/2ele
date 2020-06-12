class LoginWidget {
    constructor(widgetContainer) {
        this.widgetContainer = widgetContainer;
        this.widget = null;
        this.emailValidation = false;
        this.usernameValidation = false;
        this.passwordValidation = false;
        this.confirmEmailValidation = false;
        this.confirmPasswordValidation = false;
    }

    withWidget() {
        if(this.widget) {
            this.unbindEvents();
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
    }

    loadWidgetAjax() {
        let data = {
            'action': 'loadLoginWidgetAjax'
        };
        const _this = this;
        const beforeSend = (response) => {
            _this.widgetContainer.addClass('loadingScreen');
        }, error = (response) => {
            _this.widgetContainer.removeClass('loadingScreen');
            console.log(response);
        }, success = (response) => {
            _this.widgetContainer.removeClass('loadingScreen');
            _this.widgetContainer.empty().append(response);
            const widget = this.widgetContainer.find('.loginWidget');
            if(widget) {
                _this.withWidget();
            }
        }
        ajaxCall(data, beforeSend, error, success);
    }

    unbindEvents() {
        this.registerForm.off('submit');
        this.registerFormInputs.confirmPassword.off('keyup');
        this.registerFormInputs.confirmEmail.off('keyup');
        this.registerFormInputs.email.off('keyup');
        this.registerFormInputs.password.off('keyup');
        this.registerFormInputs.username.off('keyup');
        this.registerFormInputs.submit.off('keyup');
        this.loginForm.off('submit');
    }

    addNav(navListItemClass, navListClass) {
        const _this = this;
        const navList = this.widget.find('.' + navListClass);
        let sections = {};
        const navListItems = navList.find('.' + navListItemClass);
        navListItems.each(function () {
            let listItem = $(this);
            let sectionClass = listItem.data('section');
            sections[sectionClass] = _this.widget.find('.' + sectionClass);
            $(this).on('click', function(e) {
                e.preventDefault();
                let newActive = $(this);
                let currentActive = navList.find('.active');
                currentActive.removeClass('active');
                currentActive.removeAttr('disabled');
                console.log(currentActive);
                sections[currentActive.data('section')].addClass('hide');
                newActive.addClass('active');
                newActive.attr('disabled', true);
                sections[newActive.data('section')].removeClass('hide');
            });
        })
    }

    addRegisterAjax() {
        const username = this.registerFormInputs.username,
            email = this.registerFormInputs.email,
            password = this.registerFormInputs.password,
            _this = this;
        let data = {
            'action': 'userRegisterAjax',
        }

        const beforeSendFunc = (response) => {
            _this.registerForm.addClass('loadingScreen');
        }, errorFunc = (response) => {
            _this.registerForm.removeClass('loadingScreen');
        }, successFunc = (response) => {
            _this.registerForm.removeClass('loadingScreen');
            if(response === 'success') {
                username.val('');
                email.val('');
                _this.registerFormInputs.confirmEmail.val('');
                password.val('');
                _this.registerFormInputs.confirmPassword.val('');
                _this.registerForm.find('.registerFormNotices').html('Rejestracja przebiegła pomyślnie!' +
                ' Przed zalogowaniem proszę sprawdzić swój adres email, na który została wysłana wiadomość' +
                    ' z linkiem aktywacyjnym dla konta.');
            } else {
                _this.registerForm.find('.registerFormNotices').html(response);
            }
            $("html, body").animate({ scrollTop: 0 }, 600);
        };
        this.registerForm.on('submit', function (e) {
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
            _this = this;
        let data = {
            'action': 'userLoginAjax',
            'signOn': !submit
        }

        const beforeSendFunc = (response) => {
            _this.loginForm.addClass('loadingScreen');
        }, errorFunc = (response) => {
            _this.loginForm.removeClass('loadingScreen');
        }, successFunc = (response) => {
            _this.loginForm.removeClass('loadingScreen');
            if(response === 'success') {
                if(submit) {
                    username.val('');
                    password.val('');
                    location.reload();
                } else {
                    _this.loadLogoutWidget();
                }
                _this.unbindEvents();
            } else {
                _this.loginForm.find('.loginFormNotices').html(response);
            }
            this.widgetContainer.animate({ scrollTop: 0 }, 600);
        };
        this.loginForm.on('submit', function (e) {
            e.preventDefault();
            data.username = username.val();
            data.password = password.val();
            data.remember = remember.is(':checked');
            ajaxCall(data, beforeSendFunc, errorFunc, successFunc);
        })
    }

    loadLogoutWidget() {
        const _this = this;
        let data = {
            'action': 'userLogoutAjax',
        }

        const beforeSendFunc = (response) => {
            _this.widget.addClass('loadingScreen');
        }, errorFunc = (response) => {
            _this.widget.removeClass('loadingScreen');
        }, successFunc = (response) => {
            _this.widget.removeClass('loadingScreen');
            _this.loadWidgetAjax();
            this.widgetContainer.animate({ scrollTop: 0 }, 600);
        };
        this.widget.empty();
        const logoutButton = $('<button value="Wyloguj się">');
        logoutButton.on('click', function (e) {
            ajaxCall(data, beforeSendFunc, errorFunc, successFunc);
        })
    }


    //
    // ==============================
    //  VALIDATION
    // ==============================
    //

    addRegisterValidation() {
        const username = this.registerFormInputs.username,
            email = this.registerFormInputs.email,
            confirmEmail = this.registerFormInputs.confirmEmail,
            password = this.registerFormInputs.password,
            confirmPassword = this.registerFormInputs.confirmPassword,
            submit = this.registerFormInputs.submit;
        submit.attr('disabled', true);
        this.validateRegisterForm();
        this.validateUsername(username);
        this.validateEmail(email, confirmEmail);
        this.validatePassword(password, confirmPassword);
    }

    validateUsername(username) {
        const re = /([^\s])/;
        const emailProperties = {
            'inputName': 'username',
            'allowSpace': false,
            'allowPaste': false,
            'testMatch' : false,
            'testRegex': {
                'regex': re,
                'message': 'Nazwa użytkownika nie może być pusta'
            },
            'testUnique': {
                'message': 'Konto o podanej nazwie użytkownika już istnieje'
            }
        }

        this.inputValidation(username, emailProperties);
    }

    validateEmail(email, confirmEmail) {
        // const re = /^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
        const re = /^(([^<>()\[\]\.,;:\s@\"]+(\.[^<>()\[\]\.,;:\s@\"]+)*)|(\".+\"))@(([^<>()[\]\.,;:\s@\"]+\.)+[^<>()[\]\.,;:\s@\"]{2,})$/i;
        const emailProperties = {
            'inputName': 'email',
            'allowSpace': false,
            'allowPaste': true,
            'testMatch' : {
                'isParent' : true,
                'matchingInput': confirmEmail,
                'matchingInputName': 'confirmEmail',
                'message': 'Wprowadzone adresy mailowe różnią się!'
            },
            'testRegex': {
                'regex': re,
                'message': 'Podaj poprawny adres mailowy. Musi spełniać format: przyklad@przyklad.przyklad'
            },
            'testUnique': {
                'message': 'Konto o podanym adresie mailowym już istnieje'
            }
        }

        const confirmEmailProperties = {
            'inputName': 'confirmEmail',
            'allowSpace': false,
            'allowPaste': false,
            'testMatch' : {
                'isParent' : false,
                'matchingInput': email,
                'message': 'Wprowadzone adresy mailowe różnią się!'
            },
            'testRegex': {
                'regex': /([^\s])/,
                'message': 'Wprowadzone adresy mailowe różnią się!'
            },
            'testUnique': false
        }

        this.inputValidation(email, emailProperties);
        this.inputValidation(confirmEmail, confirmEmailProperties);
    }

    validatePassword(password, confirmPassword) {
        const re = /([^\s])/;
        const passwordProperties = {
            'inputName': 'password',
            'allowSpace': false,
            'allowPaste': false,
            'testMatch' : {
                'isParent' : true,
                'matchingInput': confirmPassword,
                'matchingInputName': 'confirmPassword',
                'message': 'Wprowadzone hasła różnią się!'
            },
            'testRegex': {
                'regex': re,
                'message': 'Hasło nie może być puste'
            },
            'testUnique': false
        }

        const confirmPasswordProperties = {
            'inputName': 'confirmPassword',
            'allowSpace': false,
            'allowPaste': false,
            'testMatch' : {
                'isParent' : false,
                'matchingInput': password,
                'message': 'Wprowadzone hasła różnią się!'
            },
            'testRegex': {
                'regex': /([^\s])/,
                'message': 'Wprowadzone hasła różnią się!'
            },
            'testUnique': false
        }

        this.inputValidation(password, passwordProperties);
        this.inputValidation(confirmPassword, confirmPasswordProperties);
    }

    inputValidation(input, properties) {
        const _this = this;
        let data = {
            'action': 'isUserUnique',
            'type': properties.inputName
        }

        let ajaxUniqueResult = false;
        let inputContainer = input.parent();
        const beforeSendFunc = (response) => {
            inputContainer.addClass('loadingRight');
        }, errorFunc = (response) => {
            inputContainer.removeClass('loadingRight');
        }, successFunc = (response) => {
            inputContainer.removeClass('loadingRight');
            ajaxUniqueResult = response === "true";
        }

        if(!properties.allowSpace) {
            input.on('keydown', function(e) {
                if(e.keyCode === 32) {
                    return false;
                }
            })
        }
        if(!properties.allowPaste) {
            input.on('paste', function(e) {
                e.preventDefault();
            })
        }

        input.on('keyup', function (e) {
            const isSpace = !properties.allowSpace ? e.keyCode === 32 : false;
            if(!isSpace) {
                const val = input.val();
                data.value = val;
                const testRegex = properties.testRegex;
                const isRegex = testRegex ? testRegex.regex.test(val) : true;
                if (isRegex) {
                    const testMatch = properties.testMatch;
                    const isMatch = testMatch ? (testMatch.matchingInput.val() === val) : true;
                    if (isMatch || testMatch.isParent) {
                        if (!isMatch && testMatch.isParent) {
                            _this.validate(testMatch.matchingInput, testMatch.matchingInputName, false, testMatch.message);
                        }
                        const testUnique = properties.testUnique;
                        if (testUnique) {
                            $.when(
                                $.ajax({
                                    url: ajaxPaginationParams.ajaxUrl,
                                    type: 'POST',
                                    data: data,
                                    beforeSend: beforeSendFunc,
                                    error: errorFunc,
                                    success: successFunc
                                })
                            ).done(function () {
                                if (ajaxUniqueResult) {
                                    _this.validate(input, properties.inputName, true, '');
                                } else {
                                    _this.validate(input, properties.inputName, false, testUnique.message);
                                }
                            });
                        } else {
                            _this.validate(input, properties.inputName, true, '');
                        }
                    } else {
                        _this.validate(input, properties.inputName, false, testMatch.message);
                        if (testMatch.isParent) {
                            _this.validate(testMatch.matchingInput, testMatch.matchingInputName, false, testMatch.message);
                        } else {
                            _this.validate(input, properties.inputName, false, testMatch.message);
                        }
                    }
                } else {
                    _this.validate(input, properties.inputName, false, testRegex.message);
                }
            }
        })
    }

    validate(input, inputName, isValid, message) {
        const inputClass = isValid ? 'approvedInput' : 'wrongInput';
        this.setInputValidatingField(inputName, isValid);
        this.inputFrontendResult(input, message, inputClass);
    }

    setInputValidatingField(fieldName, value) {
        if(fieldName === 'email') {
            this.emailValidation = value;
        } else if(fieldName === 'password') {
            this.passwordValidation = value;
        } else if(fieldName === 'username') {
            this.usernameValidation = value;
        } else if(fieldName === 'confirmEmail') {
            this.confirmEmailValidation = value;
        } else if(fieldName === 'confirmPassword') {
            this.confirmPasswordValidation = value;
        }
        this.validateRegisterForm();
    }

    validateRegisterForm() {
        if(this.registerForm && this.usernameValidation && this.emailValidation && this.confirmEmailValidation &&
            this.passwordValidation && this.confirmPasswordValidation) {
            this.registerFormInputs.submit.attr('disabled', false);
        } else {
            this.registerFormInputs.submit.attr('disabled', true);
        }
    }

    inputFrontendResult(input, message, inputClass) {
        let inputNotice = input.parent().siblings('.inputNotice');
        inputNotice.html(message);
        if(inputClass === 'wrongInput') {
            input.removeClass('approvedInput');
        } else {
            input.removeClass('wrongInput')
        }
        input.addClass(inputClass);
    }
}