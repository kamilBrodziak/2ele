class LoginWidget {
    constructor(widget, registerForm) {
        this.widget = widget;
        this.registerForm = registerForm;
        this.registerFormInputs = {
            'username': registerForm.find('.registerFormUsernameInput'),
            'email': registerForm.find('.registerFormEmailInput'),
            'confirmEmail': registerForm.find('.registerFormConfirmEmailInput'),
            'password': registerForm.find('.registerFormPasswordInput'),
            'confirmPassword': registerForm.find('.registerFormConfirmPasswordInput'),
            'submit': registerForm.find('.registerFormSubmit')
        }
        this.emailUnique = false;
        this.usernameUnique = false;
        this.emailMatch = false;
        this.passwordMatch = false;
    }

    addNav(navListItemClass, navListClass) {
        const navList = $('.' + navListClass);
        let sections = {};
        $('.' + navListItemClass).each(function () {
            let listItem = $(this);
            let sectionClass = listItem.data('section');
            sections[sectionClass] = $('.' + sectionClass);
        })

        $('body').on('click', '.' + navListItemClass, function (e) {
            e.preventDefault();
            let newActive = $(this);
            let currentActive = navList.find('.active');
            currentActive.removeClass('active');
            currentActive.removeAttr('disabled');
            sections[currentActive.data('section')].addClass('hide');
            newActive.addClass('active');
            newActive.attr('disabled', true);
            sections[newActive.data('section')].removeClass('hide');
        })
    }

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
        this.validatePassword(password, confirmPassword, 'Wprowadzone hasła się różnią!');
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
                    ' z linkiem aktywacyjnym dla konta.')
            } else {
                _this.registerForm.find('.registerFormNotices').html(response);
            }
        };

        this.registerForm.on('submit', function (e) {
            e.preventDefault();
            data.username = username.val();
            data.email = email.val();
            data.password = password.val();

        })
    }

    validateUsername(username) {
        this.ajaxUniquenessValidation(username, 'username', 'Na tą nazwę użytkownika jest już założone konto!');
    }

    validateEmail(email, confirmEmail) {
        // const re = /^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
        const re = /^(([^<>()\[\]\.,;:\s@\"]+(\.[^<>()\[\]\.,;:\s@\"]+)*)|(\".+\"))@(([^<>()[\]\.,;:\s@\"]+\.)+[^<>()[\]\.,;:\s@\"]{2,})$/i;
        this.ajaxUniquenessValidation(email, 'email', 'Na ten email jest już założone konto!', re);
        this.validateMatch(email, confirmEmail, 'Wprowadzone adresy mailowe się różnią!', 'email');
    }

    validatePassword(password, confirmPassword, message) {
        this.validateMatch(password, confirmPassword, message, 'password');
    }

    ajaxUniquenessValidation(input, type, message, regex= null) {
        let data = {
            'action': 'isUserUnique',
            'type': type
        }
        let _this = this;
        let inputContainer = input.parent();
        let beforeSendFunc = (response) => {
            inputContainer.addClass('loadingRight');
        }, errorFunc = (response) => {
            inputContainer.removeClass('loadingRight');
        }, successFunc = (response) => {
            inputContainer.removeClass('loadingRight');
            if(response === "false") {
                _this.inputFrontendResult(input, '', 'approvedInput');
                if(type === 'email') {
                    _this.emailUnique = true;
                } else {
                    _this.usernameUnique = true;
                }
            } else {
                _this.inputFrontendResult(input, message, 'wrongInput');
                if(type === 'email') {
                    _this.emailUnique = false;
                } else {
                    _this.usernameUnique = false;
                }
            }
            _this.validateRegisterForm();
        }

        input.on('keyup', function () {
            data.value = input.val();
            if((regex !== null && regex.test(data.value)) || regex === null) {
                ajaxCall(data, beforeSendFunc, errorFunc, successFunc);
            } else {
                if(type === 'email') {
                    _this.inputFrontendResult(input, 'Podany adres email jest nieprawidłowy.' +
                        ' Proszę wprowadź poprawny email w formacie przyklad@przyklad.pl', 'wrongInput');
                } else {
                    _this.inputFrontendResult(input, '', 'wrongInput');
                }
            }
        })
    }

    validateMatch(firstInput, secondInput, message, type) {
        let inputs = [firstInput, secondInput];
        let _this = this;
        $.each(inputs, function () {
            $(this).on('keyup', function (e) {
                let firstInputVal = firstInput.val();
                let secondInputVal = secondInput.val();
                if(firstInputVal === secondInputVal && firstInputVal !== '') {
                    _this.inputFrontendResult(secondInput, '', 'approvedInput');
                    if(type === 'email') {
                        _this.emailMatch = true;
                    } else {
                        _this.passwordMatch = true;
                    }
                } else if(firstInputVal !== '') {
                    _this.inputFrontendResult(secondInput, message, 'wrongInput');
                    if(type === 'email') {
                        _this.emailMatch = false;
                    } else {
                        _this.passwordMatch = false;
                    }
                }
                _this.validateRegisterForm();
            })
        })
    }

    validateRegisterForm() {
        if(this.registerForm && this.emailUnique && this.usernameUnique && this.emailMatch && this.passwordMatch) {
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