/* global _ */
(function ($) {
    $.sdekint = $.sdekint || {};

    $.sdekint.calcruleAction = function (tail) {
        tail = tail || '0';
        var params = tail.split('/');
        var rule_id = params[0] || 0;

        /**
         * Блок с условием в зависимости от типа
         *
         * @param {String} type
         * @param {jQuery} $el
         * @returns {jQuery}
         */
        function conditionBlock(type, $el) {
            return $el.find('.condition-type-' + type).first();
        }

        /**
         * Тип сравнения в условии
         *
         * @param {jQuery} $el
         * @returns {*}
         */
        function conditionComparison($el) {
            return $el.find('.condition-type-comparison').val();
        }

        /**
         * Условие в объект
         *
         * @param {jQuery} $el
         * @returns {*}
         */
        function getCondition($el) {
            var type = $el.find(':input.sdekint-rule-condition-select').val();
            var condition = {type: type};
            var $block = conditionBlock(type, $el);

            if ($block.hasClass('sdekint-has-comparison')) {
                $.extend(condition, {comparison: conditionComparison($block)});
            }
            switch (type) {
                case 'country' :
                    return $.extend(condition, {
                        value: $block.find('.condition-type-country-country-select').val()
                    });
                case 'region' :
                    return $.extend(
                        condition, {
                            value: {
                                country: $block.find('.condition-type-region-country-select').val(),
                                region: $block.find('.condition-type-regions-region:visible').val()
                            }
                        });
                case 'city_name' :
                    var country = $block.find('.condition-type-city_name-country-select').val();
                    var region = $block.find('.condition-type-city_name-region-select').val();
                    return $.extend(condition, {
                        value: {
                            country: country === '0' ? null : country,
                            region: country === '0' || region === '0' || !region.length ? null : region,
                            city: $block.find('.condition-type-city_name-city_input').val()
                        }
                    });
                case 'city_sdek':
                    return $.extend(condition, {value: $block.find('.condition-type-city_sdek-city-select').val()});
                case 'order_weight':
                    return $.extend(condition, {value: $block.find('.condition-type-order_weight-value').val()});
                case 'order_price':
                    return $.extend(condition, {value: $block.find('.condition-type-order_price-value').val()});
            }
        }

        /**
         * Возвращает объект с правилом для курьера/ПВЗ
         *
         * @param {String} type 'courier' or 'point'
         * @param {jQuery} $el
         */
        function getRuleBlockFor(type, $el) {
            return $el.find('.sdekint-' + type + '-rule').first();
        }

        function getRuleGrid($el) {
            var grid = {unit: $el.find('.sdekint-grid-unit-select').val(), grid: []};
            $el.find('tbody tr').each(function () {
                var $this = $(this);
                grid.grid.push({
                    condition: $this.find(':input.sdekint-grid-condition').val(),
                    price: $this.find(':input.sdekint-grid-price').val()
                });
            });
            return grid;
        }

        function getRule(type, $el) {
            var $rule = getRuleBlockFor(type, $el);
            var rule = {
                disabled: $rule.find('.sdekint-disable-variant-cbx').is(':checked') ? 1 : 0,
                type: 'asis',
                setting: {}
            };
            if (!!rule.disabled) {
                return rule;
            }
            rule.type = $rule.find('.sdekint-price-type-select').val();

            if (rule.type == 'asis') {
                return rule;
            }

            switch (rule.type) {
                case 'fixed':
                    rule.setting = {value: $rule.find('.value.sdekint-price-type-fixed input').val()};
                    return rule;
                case 'formula' :
                    rule.setting = {value: $rule.find('.value.sdekint-price-type-formula input').val()};
                    return rule;
                case 'grid':
                    rule.setting = getRuleGrid($rule.find('.sdekint-price-type-grid').first());
            }
            return rule;
        }

        $.shop.getJSON(
            '?plugin=sdekint&module=shipping&action=rule',
            {id: rule_id},
            function (r) {
                this.$content.html(r.data.html);
                this.$content.off()
                    .on('click', 'a.back', function () {
                        window.history.go(-1);
                        return false;
                    });
                $('.sdekint-rule-form')
                    .off()
                    .on('change', '.sdekint-price-type-select', function (e) {
                        var $el = $(e.currentTarget);
                        var $field = $el.closest('div.field');
                        $field.find('div.value.sdekint-price-type').hide();
                        $field.find('div.value.sdekint-price-type-' + this.value).show();
                    })
                    .on('change', '.sdekint-disable-variant-cbx', function (e) {
                        var $el = $(e.currentTarget);
                        $el.closest('div.fields').find('>div.field:not(.sdekint-status)').toggle(!$el.is(':checked'));
                    })
                    .on('change', '.sdekint-grid-unit-select', function (e) {
                        var $el = $(e.currentTarget);
                        var unit = $el.val() === 'price' ? {name: 'Стоимость', value: 'р.'} : {
                            name: 'Вес',
                            value: 'кг.'
                        };
                        var $container = $el.closest('div.value.sdekint-price-type-grid');
                        $container.find('.unit-label').text(unit.name);
                        $container.find('.unit-value').text(unit.value);
                    })
                    .on('click', '.sdekint-grid-add-row', function (e) {
                        var $el = $(e.currentTarget);
                        var $container = $el.closest('div.value.sdekint-price-type-grid');
                        var $table = $container.find('table.sdekint-tariff-grid');
                        var unit = $container.find(':input.sdekint-grid-unit-select').val();
                        var $tbody = $table.find('tbody');
                        var $new_row = $tbody.find('tr').last().clone();
                        var $grid_cond = $new_row.find(':input.sdekint-grid-condition');
                        $grid_cond.val((_.toNumber($grid_cond.val().replace(',', '.')) || 0) + (unit === 'weight' ? 0.5 : 500));
                        $tbody.append($new_row);
                        $table.trigger('row-count-change');
                        return false;
                    })
                    .on('row-count-change', '.sdekint-tariff-grid', function (e) {
                        var $tbody = $(e.currentTarget).find('tbody');
                        $tbody.find('.sdekint-grid-delete-row').toggle($tbody.find('tr').length > 1);
                        return false;
                    })
                    .on('click', 'table.sdekint-tariff-grid tbody .sdekint-grid-delete-row', function (e) {
                        var $el = $(e.currentTarget);
                        var $tbody = $el.closest('tbody');
                        if ($tbody.find('tr').length > 1) {
                            $el.closest('tr').remove();
                        }
                        $tbody.trigger('row-count-change');
                        return false;
                    })
                    .on('change', '.sdekint-rule-condition-select', function (e) {
                        var $el = $(e.currentTarget);
                        var $container = $el.closest('.sdekint-rule-conditions-list-item');
                        $container.find('.condition-type').hide();
                        var $current_condition_container = $container.find('.condition-type-' + $el.val());
                        $current_condition_container.show();
                        if ($el.val() === 'city_sdek') {
                            var $s2 = $current_condition_container.find('select.condition-type-city_sdek-city-select').first();
                            if ($s2.hasClass('select2-hidden-accessible')) {
                                return;
                            }
                            var $country_select = $current_condition_container.find('.condition-type-city_sdek-country-select');
                            $s2.select2({
                                theme: 'sdekint',
                                ajax: {
                                    url: '?plugin=sdekint&module=cities&action=index',
                                    data: function (params) {
                                        return {country: $country_select.val(), term: params.term};
                                    },
                                    delay: 350,
                                    processResults: $.sdekint.Select2.formatCities
                                },
                                minimumInputLength: 2
                            }).on('select2:opening', function () {
                                setTimeout(function () {
                                    $('html,body').trigger('scroll');
                                }, 1);
                            });
                        }
                    })
                    .on('click', '.sdkint-add-rule-condition', function () {
                        var $new_item = $($('#ConditionItem').render());
                        var selected = $new_item.find('.sdekint-rule-condition-select option').first().attr('value');
                        $new_item.find('.condition-type').hide();
                        $new_item.find('.condition-type-' + selected).show();
                        $('.sdekint-rule-conditions-list').append($new_item).trigger('conditions-list-change');

                        return false;
                    })
                    .on('conditions-list-change', '.sdekint-rule-conditions', function (e) {
                        var $el = $(e.currentTarget);
                        var $join_type_select = $el.find('.sdekint-rule-conditions-join-type-select');
                        var $conditions_container = $el.find('.sdekint-rule-conditions-list-container');
                        var conditions_count = $conditions_container.find('.sdekint-rule-conditions-list .sdekint-rule-conditions-list-item').length;
                        $join_type_select.closest('div.value').toggle(conditions_count > 1);
                        $conditions_container.toggleClass('multiple-conditions', conditions_count > 1);

                    })
                    .on('click', '.sdekint-rule-condition-delete', function (e) {
                        var $el = $(e.currentTarget);
                        var $list = $el.closest('.sdekint-rule-conditions-list');
                        $el.closest('.sdekint-rule-conditions-list-item').remove();
                        $list.trigger('conditions-list-change');

                        return false;
                    })
                    .on('change', '.sdekint-rule-conditions-join-type-select', function (e) {
                        var $el = $(e.currentTarget);
                        $el.closest('.sdekint-rule-conditions')
                            .find('.sdekint-rule-conditions-list-container .sdekint-rule-conditions-list')
                            .toggleClass('or', $el.val() === 'or');
                    })
                    .on('change', '.condition-type-region-country-select', function (e) {
                        var $el = $(e.currentTarget);
                        var $region_wrapper = $el.closest('.condition-type-region').find('.condition-type-regions-region-wrapper');
                        var $region_select = $region_wrapper.find('select.condition-type-regions-region');
                        var $region_input = $region_wrapper.find('input.condition-type-regions-region');
                        var $loading = $region_wrapper.find('.loading');
                        $region_select.empty();
                        $loading.show();
                        $.sdekint.Http.loadRegions($el.val(), function (r) {
                            if (r.data.length) {
                                $region_select.show();
                                $region_input.hide();
                                $.each(r.data, function (i, v) {
                                    $region_select.append(new Option(v.name, v.code));
                                });
                            } else {
                                $region_select.hide();
                                $region_input.show();
                            }
                        }).always(function () {
                            $loading.hide();
                        });
                    })
                    .on('change', '.condition-type-city_name-country-select', function (e) {
                        var $el = $(e.currentTarget);
                        var $block = $el.closest('.condition-type-city_name');
                        var $region_wrapper = $block.find('.condition-type-city_name-region-wrapper');
                        var $region_select = $block.find('.condition-type-city_name-region-select');
                        var $loading = $region_wrapper.find('.loading');
                        if ($el.val() == '0') {
                            $region_wrapper.hide();
                            return;
                        }
                        $loading.show();
                        $.sdekint.Http.loadRegions($el.val(), function (r) {
                            if (r.data.length) {
                                $region_select.empty();
                                $region_select.append(new Option('неважно', ''));
                                $.each(r.data, function (i, v) {
                                    $region_select.append(new Option(v.name, v.code));
                                });
                                $region_wrapper.show();
                            } else {
                                $region_wrapper.hide();
                            }
                        }).always(function () {
                            $loading.hide();
                        });
                    })
                    .on('change', ':input[name=methods_select]', function () {
                        var $this = $(this);
                        $this.closest('.field').find('.methods-list').toggle($this.val() !== 'all');
                    })
                    .on('submit', function () {
                        var $form = $(this);
                        var data = {conditions: []};
                        var id = $form.find(':input[name=id]').val();
                        if (!!id) {
                            data.id = id;
                        }
                        data.status = $form.find(':input[name=status]').is(':checked') ? 1 : 0;
                        data.name = $form.find(':input[name=name]').val();

                        $form.find('.sdekint-rule-conditions-list .sdekint-rule-conditions-list-item').each(function () {
                            data.conditions.push(getCondition($(this)));
                        });
                        data.condition_join_type = $form.find('.sdekint-rule-conditions-join-type-select').val();
                        $.each(['courier', 'point'], function (i, v) {
                            data[v] = getRule(v, $form);
                        });
                        data.methods = 'all';
                        if ($form.find(':input[name=methods_select]').val() === 'selected') {
                            data.methods = [];
                            $form.find('input[type=checkbox][name="selected_method[]"]:checked').each(function () {
                                data.methods.push(this.value);
                            });
                        }
                        $.shop.trace('Condition save', data);

                        var $submit_div = $form.find('div.field div.submit.value');
                        $submit_div.append('<span><i class="icon16 loading"></i>Сохранение</span>');
                        $submit_div.find(':input[type=submit]').prop('disabled', true);
                        $.shop.jsonPost(
                            '?plugin=sdekint&module=shipping&action=save',
                            {data: JSON.stringify(data)},
                            function (r) {
                                if(r.data.id) {
                                    $form.find(':input[name=id]').val(r.data.id);
                                }
                                $submit_div.append('<span class="success green">Сохранено</span>');
                                setTimeout(function () {
                                    $submit_div.find('span.success').remove();
                                }, 3000);
                            }
                        ).always(function () {
                            $submit_div.find('i.loading').closest('span').remove();
                            $submit_div.find(':input[type=submit]').prop('disabled', false);
                        });

                        return false;
                    });
                $('.condition-type-city_sdek:visible').each(function () {

                    var $current_condition_container = $(this);
                    var $s2 = $current_condition_container.find('.condition-type-city_sdek-city-select').first();
                    if ($s2.hasClass('select2-hidden-accessible')) {
                        return;
                    }
                    var $country_select = $current_condition_container.find('.condition-type-city_sdek-country-select');
                    $s2.select2({
                        theme: 'sdekint',
                        ajax: {
                            url: '?plugin=sdekint&module=cities&action=index',
                            data: function (params) {
                                return {country: $country_select.val(), term: params.term};
                            },
                            delay: 350,
                            processResults: $.sdekint.Select2.formatCities
                        },
                        minimumInputLength: 2
                    }).on('select2:opening', function () {
                        setTimeout(function () {
                            $('html,body').trigger('scroll');
                        }, 1);
                    });

                });
            }.bind(this)
        );
    };
})(jQuery);