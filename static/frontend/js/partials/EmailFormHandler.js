class EmailFormHandler {
    constructor(form) {
        this.form = form;
    }

    submitEvent() {
        const self = this;
        self.form.on('submit', function (e) {
            e.preventDefault();
            const form = self.form,
                name = form.find('.contactFormName').val(),
                email = form.find('.contactFormEmail').val(),
                message = form.find('.contactFormMessage').val();
            self.sendEmail(name, email, message);
        });
    }

    sendEmail(name, email, message) {
        const _this = this,
            beforeSend = () => {
                _this.form.addClass('loadingScreen');
                _this.form.find("#ftrContactFormSubmit").prop("disabled", true);
            },
            error = (response) => {
                console.log(response);
                _this.form.removeClass('loadingScreen');
                _this.displayMessage("contactFormFailInfo");
                _this.form.find("#ftrContactFormSubmit").prop("disabled", false)
            },
            success = () => {
                _this.form.removeClass('loadingScreen');
                _this.displayMessage("contactFormSuccessInfo");
                _this.form.find("#ftrContactFormSubmit").prop("disabled", false)
            };
        ajaxCall({'action': 'sendUserEmail' ,'name': name, 'email': email, 'message': message},
            beforeSend, error, success);
    }

    displayMessage(messageClass) {
        const successInfoElement = this.form.find("." + messageClass);
        successInfoElement.css("display", "flex");
        setTimeout(function() {
            successInfoElement.css("display", "none");
        }, 2000);
    }
}