class LoginWidget {
    constructor(widgetContainer) {
        this.widgetContainer = widgetContainer;
        this.widget = null;
        this.emailUnique = false;
        this.usernameUnique = false;
        this.emailMatch = false;
        this.passwordMatch = false;
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
                    ' z linkiem aktywacyjnym dla konta.');
            } else {
                _this.registerForm.find('.registerFormNotices').html(response);
            }
            $("html, body").animate({ scrollTop: 0 }, 600);
        };
        console.log('pp');
        this.registerForm.on('submit', function (e) {
            e.preventDefault();
            console.log('pp2');
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
            let isApproved = response === "true";
            let userMessage = isApproved ? '' : message,
                inputClass = isApproved ? 'approvedInput' : 'wrongInput';
            _this.setValidatingField(type + 'Unique', isApproved);
            _this.inputFrontendResult(input, userMessage, inputClass);
            _this.validateRegisterForm();
        }

        input.on('keyup', function () {
            data.value = input.val();
            if(((regex !== null && regex.test(data.value)) || regex === null) && data.value !== "") {
                ajaxCall(data, beforeSendFunc, errorFunc, successFunc);
            } else {
                let userMessage = (type === 'email') ? 'Podany adres email jest nieprawidłowy. Proszę wprowadź poprawny email w formacie przykład@przykład.pl'
                    : '';
                _this.inputFrontendResult(input, userMessage, 'wrongInput');
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
                if(firstInputVal === '') {
                    _this.inputFrontendResult(firstInput, '', 'wrongInput');
                } else {
                    let isMatch = firstInputVal === secondInputVal;
                    let userMessage = isMatch ? '' : message,
                        inputClass = isMatch ? 'approvedInput' : 'wrongInput';
                    _this.setValidatingField(type + 'Match', isMatch);
                    _this.inputFrontendResult(secondInput, userMessage, inputClass);
                }

                _this.validateRegisterForm();
            })
        })
    }

    setValidatingField(fieldName, value) {
        if(fieldName === 'emailMatch') {
            this.emailMatch = value;
        } else if(fieldName === 'passwordMatch') {
            this.passwordMatch = value;
        } else if(fieldName === 'emailUnique') {
            this.emailUnique = value;
        } else if(fieldName === 'usernameUnique') {
            this.usernameUnique = value;
        }
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