/* global _ */
(function ($) {
    $.sdekint = $.sdekint || {};
    $.sdekint.Pickup = function (data, order) {
        this.order = order || {};
        this.$form = $('#s-sdekint-pickup-form');
        this.$city = $('#s-sdekint-city');
        this.$points = $('#s-sdekint-office-code');
        this.$pickup_date = $('#s-sdekint-pickup-date');

        var buildOrder = function () {
            var data = {
                number: this.$form.find('input[name="data[order][act_number]"]').val(),
                order : {
                    number: this.$form.find('input[name="data[order][number]"]').val(),
                    send_city_code: this.$city.val(),
                    pvz_code: $('#s-sdekint-office-code').val(),
                    recipient_name: this.$form.find('input[name="data[order][recipient][name]"]').val(),
                    recipient_phone: this.$form.find('input[name="data[order][recipient][phone]"]').val(),
                    package: []
                },
                call_courier: {
                    date: this.$pickup_date.val(),
                    time_beg: $('#s-sdekint-from-time').val(),
                    time_end: $('#s-sdekint-to-time').val(),
                    send_phone: $('input[name="data[contact][phone]"]').val(),
                    send_address: {
                        sender_name: $('input[name="data[contact][name]"]').val(),
                        street: $('input[name="data[call][send_address][street]"]').val(),
                        house: $('input[name="data[call][send_address][house]"]').val(),
                        flat: $('input[name="data[call][send_address][flat]"]').val()
                    }
                },
                profile: {
                    id: $(':input[name="profile[sender][id]"]').val(),
                    save: $('input[name="profile[sender][action][save]"]').is(':checked'),
                    set_default: $('input[name="profile[sender][action][set_default]"]').is(':checked')
                }
            };

            $.each(this.order.packages, function (i, p) {
                var pkg = {
                    number: p.number || '',
                    bar_code: p.barcode || '',
                    weight: _.toNumber((p.weight + '').replace(',','.')),
                    size_a: _.toNumber(p.size.a),
                    size_b: _.toNumber(p.size.b),
                    size_c: _.toNumber(p.size.c),
                    item: []
                };

                $.each(p.items, function (ii, item) {
                    pkg.item.push({
                        ware_key: item.sku || '',
                        cost: _.toNumber((item.price+'').replace(',', '.')),
                        payment: _.toNumber((item.payment+'').replace(',', '.')),
                        weight: _.toNumber((item.weight+'').replace(',', '.')),
                        amount: _.toInteger(item.amount),
                        comment: item.name || ''
                    });
                }.bind(this));

                data.order.package.push(pkg);

            }.bind(this));

            // same city
            data.call_courier.send_city_code = data.order.rec_city_code = data.order.send_city_code;

            return data;
        }.bind(this);

        var showErrors = function (r) {
            if (r.errors) {
                if (!_.isArray(r.errors)) {
                    r.errors = ['Ошибка'];
                }
                var $errors = this.$form.find('div.field.errors').first();
                $errors.html($('#ErrorsBlock').render({errors: r.errors}));
                setTimeout(function () {
                    $errors.slideUp(400, function () {
                        $errors.empty();
                    });
                }, 5000);
            }
            return false;
        }.bind(this);

        var init = function () {
            $.fn.select2.defaults.set('theme', 'sdekint');

            this.$city.select2({
                ajax: {
                    url: '',
                    data: function (params) {
                        return {
                            plugin: 'sdekint',
                            module: 'cities',
                            action: 'index',
                            with_stocks: 1,
                            country: 'rus',
                            term: params.term
                        };
                    },
                    processResults: $.sdekint.Select2.formatCities
                },
                minimumInputLength: 2
            });
            this.$points.select2();
            this.$city.on('change', function () {
                $.sdekint.Http.loadPointsByCity(
                    this.$city.val(),
                    false,
                    function (r) {
                        this.$points.empty();
                        $.each(r.data.office_list, function (i, o) {
                            this.$points.append(new Option(o.name, o.code));
                        }.bind(this));
                    }.bind(this)
                );
            }.bind(this));
            this.$pickup_date.datepicker({dateFormat: 'yy-mm-dd', minDate: this.$pickup_date.data('min-date')});
            this.$form.find('input.s-sdekint-timepicker').each(function (i, p) {
                p = $(p);
                p.timepicker({
                    format: p.data('format'),
                    minTime: p.data('min-time'),
                    maxTime: p.data('max-time'),
                    step: p.data('step')
                });
            });
            this.$form.off('.sdekint')
                .on('change.sdekint', 'select[name="profile[sender][id]"]', function (evt) {
                    var $select = $(evt.currentTarget);
                    var $selected_id = $select.val();
                    var selected_option = _.find($select.find('option'), function (o) {
                        return o.value == $selected_id;
                    });
                    $('span.sender-data-new').toggle(!$selected_id);
                    $('span.sender-data-existing').toggle(!!$selected_id);
                    if(selected_option) {
                        var $selected_option = $(selected_option);
                        $('input[name="data[contact][name]"]').val($selected_option.data('name'));
                        $('input[name="data[contact][phone]"]').val($selected_option.data('phone'));
                        $('input[name="data[call][send_address][street]"]').val($selected_option.data('street'));
                        $('input[name="data[call][send_address][house]"]').val($selected_option.data('house'));
                        $('input[name="data[call][send_address][flat]"]').val($selected_option.data('flat'));
                        $('input[name="profile[sender][action][save]"]').prop('checked', false);
                        $('input[name="profile[sender][action][set_default]"]').prop('checked', !!$selected_option.data('is-default'));
                    }
                });
            this.$form.find('select[name="profile[sender][id]"]').trigger('change');
        }.bind(this);

        init();

        $.templates('#PackagesFieldTmpl').link('#s-sdekint-packages', this.order);

        this.$form
            .on('click', '.js-action.add-package-item', function (e) {
                $.observable($.view(this).parent.data.items)
                    .insert(
                        {
                            sku: '',
                            name: 'Товары интернет-магазина',
                            'price': 0.0,
                            'payment': 0.0,
                            'weight': 0.5,
                            'amount': 1
                        }
                    );
                return false;
            })
            .on('click', '.js-action.delete-package-item', function () {
                var view = $.view(this);
                $.observable(view.parent.parent.data).remove(view.getIndex(), 1);
                return false;
            })
            .on('click', '.js-action.add-package', function () {
                $.observable($.view(this).data.packages).insert({
                    'number': '1',
                    'barcode': '',
                    'weight': '0.5',
                    'size': {'a': 5, 'b': 5, 'c': 5},
                    'items': [{
                        'sku': '',
                        'name': 'Товары интернет-магазина',
                        'price': 0.0,
                        'payment': 0.0,
                        'weight': 0.5,
                        'amount': 1
                    }]
                });
                return false;
            })
            .on('click', '.js-action.delete-package', function (e) {
                var view = $.view(e.currentTarget);
                $.observable(view.parent.parent.data).remove(view.getIndex(), 1);
                return false;
            })
            .on('submit', function () {
                this.$form.find('.submit.field .submit.value').append('<span class="loading_message"><i class="icon16 loading"></i>Отправка запроса...</span>')
                $.sdekint.Http.pickup.save(
                    buildOrder(),
                    function () {
                        $.wa.setHash('couriercalls');
                    },
                    showErrors
                ).always(function () {
                    this.$form.find('span.loading_message').remove();
                }.bind(this));
                return false;
            }.bind(this));
    };
})(jQuery);