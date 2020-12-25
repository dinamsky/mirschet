// MAILER app email subscribe form
var SubscribeSection = ( function($) {

    SubscribeSection = function(options) {
        var that = this;

        // DOM
        that.$wrapper = options["$wrapper"];
        that.$form = that.$wrapper.find("form");
        that.$emailField = that.$wrapper.find(".js-subscribe-email-field");

        // VARS
        that.request_uri = options["request_uri"];
        that.locales = options["locales"];

        // DYNAMIC VARS

        // INIT
        that.initClass();
    };

    SubscribeSection.prototype.initClass = function() {
        var that = this;

        if (that.request_uri.substr(0,4) === "http") {
            that.request_uri = that.request_uri.replace("http:", "").replace("https:", "");
        }

        var $invisible_captcha = that.$form.find(".wa-invisible-recaptcha");
        if (!$invisible_captcha.length) {
            that.initView();
        }

        that.initSubmit();
    };

    SubscribeSection.prototype.initView = function() {
        var that = this;

        that.$emailField.on("focus", function() {
            toggleView(true);
        });

        $(document).on("click", watcher);

        function watcher(event) {
            var is_exist = $.contains(document, that.$wrapper[0]);
            if (is_exist) {
                var is_target = $.contains(that.$wrapper[0], event.target);
                if (!is_target) {
                    toggleView(false);
                }
            } else {
                $(document).off("click", watcher);
            }
        }

        function toggleView(show) {
            var active_class = "is-extended";
            if (show) {
                that.$wrapper.addClass(active_class);
            } else {
                var email_value = that.$emailField.val();
                if (!email_value.length) {
                    that.$wrapper.removeClass(active_class);
                } else {

                }
            }
        }
    };

    SubscribeSection.prototype.initSubmit = function() {
        var that = this,
            $form = that.$form,
            $errorsPlace = that.$wrapper.find(".js-errors-place"),
            is_locked = false;

        $form.on("submit", onSubmit);

        function onSubmit(event) {
            event.preventDefault();

            var formData = getData();

            if (formData.errors.length) {
                renderErrors(formData.errors);
            } else {
                request(formData.data);
            }
        }

        /**
         * @return {Object}
         * */
        function getData() {
            var result = {
                    data: [],
                    errors: []
                },
                data = $form.serializeArray();

            $.each(data, function(index, item) {
                if (item.value) {
                    result.data.push(item);
                } else {
                    result.errors.push({
                        name: item.name
                    });
                }
            });

            return result;
        }

        /**
         * @param {Array} data
         * */
        function request(data) {
            if (!is_locked) {
                is_locked = true;

                var href = that.request_uri;

                $.post(href, data, "jsonp")
                    .always( function() {
                        is_locked = false;
                    })
                    .done( function(response) {
                        if (response.status === "ok") {
                            renderSuccess();

                        } else if (response.errors) {
                            var errors = formatErrors(response.errors);
                            renderErrors(errors);
                        }
                    });
            }

            /**
             * @param {Object} errors
             * @result {Array}
             * */
            function formatErrors(errors) {
                var result = [];

                $.each(errors, function(text, item) {
                    var name = item[0];

                    if (name === "subscriber[email]") { name = "email"; }

                    result.push({
                        name: name,
                        value: text
                    });
                });

                return result;
            }
        }

        /**
         * @param {Array} errors
         * */
        function renderErrors(errors) {
            var error_class = "error";

            if (!errors || !errors[0]) {
                errors = [];
            }
			//$form.find(".wa-captcha-img").trigger("click");

            $.each(errors, function(index, item) {
                var name = item.name,
                    text = item.value;

                var $field = that.$wrapper.find("[name=\"" + name + "\"]"),
                    $text = $("<span class='c-error' />").addClass("error");

                if ($field.length && !$field.hasClass(error_class)) {
                    if (text) {
                        $field.parent().append($text.text(text));
                    }

                    $field
                        .addClass(error_class)
                        .one("focus click change", function() {
                            $field.removeClass(error_class);
                            $text.remove();
                        });
                } else {
                    $errorsPlace.append($text);

                    $form.one("submit", function() {
                        $text.remove();
                    });
                }
            });
        }

        function renderSuccess() {
            that.$wrapper.addClass("home-subsc_success");
            that.$wrapper.find(".home-subsc__title").text(that.$wrapper.find(".home-subsc__title").data("success"));
            that.$wrapper.find(".home-subsc__text").text(that.$wrapper.find(".home-subsc__text").data("success"));
            $form.closest(".home-subsc__span").hide();
        }
    };

    return SubscribeSection;

})(jQuery);