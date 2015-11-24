/***** Your javascript can go below here ******/
$(document).ready(function ($) {
    var App = {
        init: function () {
            this.scheduleForm.init();
            this.footerBackCall();
        },
        /* форма заказа обратного звонка в футере */
        footerBackCall: function () {
            var
                link = $('.footer__backcall'),
                form = $('.footer__form'),
                phones = $('.footer__phones'),
                social = $('.footer__soc'),
                back = $('.footer__back')
                ;
            link.click(function () {
                form.fadeIn();
                phones.hide();
                social.hide();
                return false;
            });

            back.click(function(){
                form.hide();
                phones.fadeIn();
                social.fadeIn();
            });

            if ($('.footer__form .simpleform-message').length > 0) {
                form.show();
                phones.hide();
                social.hide();
                back.hide();
            }
        },
        scheduleForm: {
            init: function () {
                this.attachTableItemButtonHandler();
            },
            /* Прилепляем обработчик нажатия на кнопку в строке расписания
             *  название программы должно отправляться в форму*/
            attachTableItemButtonHandler: function () {
                var
                    formField = $('#schedule_course'),
                    buttons = $('.day-table-item__button a'),
                    schedule = $('.day-table'),
                    form = $('.form_course'),
                    backButton = $('.form__back'),
                    formTitle = $('.form__title'),
                    message = $('.form_course .simpleform-message, .form_course .simpleform-error')
                ;

                if (message.length > 0) {
                    schedule.hide();
                    form.show();
                    formTitle.css('opacity', 0);

                    backButton.click(function () {
                        location.replace(location.pathname)
                    });
                    message.click(function () {
                        location.replace(location.pathname)
                    });



                } else {
                    buttons.click(function () {
                        var
                            itemWrapper = $(this).closest('.day-table__item'),
                            time = itemWrapper.attr('data-time'),
                            programName = itemWrapper.attr('data-text'),
                            weekDay = itemWrapper.attr('data-day'),
                            resultText = time + " (" + weekDay + ")" + " " + programName + " "

                            ;
                        formField.val(resultText);
                        schedule.hide();
                        form.fadeIn();
                        return false;
                    });
                    backButton.click(function () {
                        schedule.fadeIn();
                        form.hide();
                    });
                }

            }
        }

    };
    App.init();
});