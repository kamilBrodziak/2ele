class FormValidation {
    addBasicValidation(username, email, confirmEmail, password, confirmPassword, submit) {
        this.submit = submit;
        this.validation = {'username':false,'email':false,'confirmEmail':false,'password':false,'confirmPassword':false};
        this.submit.attr('disabled', true);
        this.usernameValidation(username);
        this.emailValidation(email, confirmEmail);
        this.passwordValidation(password, confirmPassword);
    }

    usernameValidation(username) {
        const re = /(^[^@]+$)/,
            emailProperties = {
                'inputName': 'username',
                'disallowedKeys': [' ', '@'],
                'testMatch' : false,
                'testRegex': {
                    'regex': re,
                    'message': username.data('regex_message')
                },
                'testUnique': {
                    'message': username.data('unique_message')
                }
            }

        this.inputValidation(username, emailProperties);
    }

    emailValidation(email, confirmEmail) {
        const re = /^(([^<>()\[\]\.,;:\s@\"]+(\.[^<>()\[\]\.,;:\s@\"]+)*)|(\".+\"))@(([^<>()[\]\.,;:\s@\"]+\.)+[^<>()[\]\.,;:\s@\"]{2,})$/i,
            emailName = 'email', confirmEmailName = 'confirmEmail',
            getProperties = (isParent) => {
                return {
                    'inputName': isParent ? emailName : confirmEmailName,
                    'disallowedKeys': [' '],
                    'allowPaste': isParent,
                    'testMatch': {
                        'isParent': isParent,
                        'matchingInput': isParent ? confirmEmail : email,
                        'matchingInputName': isParent ? confirmEmailName : emailName,
                        'message': email.data('match_message')
                    },
                    'testRegex': !isParent ? false : {
                        'regex': re,
                        'message': email.data('regex_message')
                    },
                    'testUnique': !isParent ? false : {
                        'message': email.data('unique_message')
                    }
                }
            };
        this.inputValidation(email, getProperties( true));
        this.inputValidation(confirmEmail, getProperties(false));
    }

    passwordValidation(password, confirmPassword) {
        const re = /([^\s])/,
            passwordName = 'password', confirmPasswordName = 'confirmPassword',
            getProperties = (isParent) => {
                return {
                    'inputName': isParent ? passwordName : confirmPasswordName,
                    'disallowedKeys': [' '],
                    'allowPaste': isParent,
                    'testMatch': {
                        'isParent': isParent,
                        'matchingInput': isParent ? confirmPassword : password,
                        'matchingInputName': isParent ? confirmPasswordName : passwordName,
                        'message': password.data('match_message')
                    },
                    'testRegex': !isParent ? false : {
                        'regex': re,
                        'message': password.data('regex_message')
                    },
                    'testUnique': false
                }
            };
        this.inputValidation(password, getProperties(true));
        this.inputValidation(confirmPassword, getProperties(false));
    }

    inputValidation(input, properties) {
        let typingTimer, keyPassed = true;
        const _this = this,
            loadingClass = 'loadingRight',
            inputContainer = input.parent(),
            validateFunc = (passed, message, matchEl = false, matchName = '') => {
                if(matchEl)
                    _this.validate(matchEl, matchName, passed, message)
                else
                    _this.validate(input, properties.inputName, passed, message)
            },
            disKeys = properties.disallowedKeys;
        if(!properties.allowPaste) {
            input.on('paste', (e) => {
                e.preventDefault();
            })
        }
        if(disKeys.length)
            input.on('keydown', (e) => {
                for(let key of disKeys)
                    if(e.key === key) {
                        keyPassed = false;
                        return false;
                    }
                keyPassed = true;
            })
        input.on('keyup', () => {
            if(!keyPassed) return;
            const val = input.val(),
                testRegex = properties.testRegex,
                isRegex = testRegex ? testRegex.regex.test(val) : true;
            if(!isRegex) {
                validateFunc(false, testRegex.message);
                return;
            }
            const testMatch = properties.testMatch,
                isMatch = testMatch ? (testMatch.matchingInput.val() === val) : true;
            if (!isMatch && !testMatch.isParent) {
                validateFunc(false, testMatch.message);
                return;
            }
            const testUnique = properties.testUnique;
            if (testUnique) {
                validateFunc(false, 'Sprawdzanie unikalności...');
                typingTimer = setTimeout(() => {
                    ajaxCall({'action': 'isUserUnique', 'type': properties.inputName,'value':val},
                        () => inputContainer.addClass(loadingClass),
                        inputContainer.removeClass(loadingClass),
                        (response) => {inputContainer.removeClass(loadingClass);
                            validateFunc(response === 'true', response === 'true' ? '' : testUnique.message)})
                }, 800);
            } else {
                validateFunc(true, '');
            }

            if(testMatch.isParent)
                validateFunc(isMatch, isMatch ? '' : testMatch.message, testMatch.matchingInput, testMatch.matchingInputName);

        })
    }

    validate(input, inputName, isValid, message) {
        const inputClass = isValid ? 'approvedInput' : 'wrongInput';
        this.validation[inputName] = isValid;
        this.validateForm();
        this.frontendNotice(input, message, inputClass);
    }

    validateForm() {
        this.submit.attr('disabled', !(this.validation.username && this.validation.email &&
            this.validation.confirmEmail && this.validation.password && this.validation.confirmPassword));
    }

    frontendNotice(input, message, inputClass) {
        const inputNotice = input.parent().siblings('.inputNotice'),
            approvedClass = 'approvedInput', wrongClass = 'wrongInput',
            changeClass = (removeClass, addClass) => {
                if(input.hasClass(removeClass))
                    input.removeClass(removeClass);
                if(!input.hasClass(addClass))
                    input.addClass(addClass);
            };
        inputNotice.html(message);

        if(inputClass === wrongClass)
            changeClass(approvedClass, wrongClass);
        else
            changeClass(wrongClass, approvedClass);
    }

}