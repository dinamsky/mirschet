/* global _, ymaps */
(function ($) {
    $.sdekint = $.sdekint || {};
    $.sdekint.Calculator = function (data) {
        function boxSizeCss(current_width, min_width, min_height, max_width, max_height, keep_ratio) {
            if (!current_width) {
                throw 'Current width and height must be positive integers!';
            }

            min_width = _.toNumber(min_width);
            current_width = Math.min(Math.max(_.toNumber(current_width), _.toNumber(min_width)), max_width);

            var ratio_height = current_width * _.toNumber(keep_ratio);
            var current_height = ratio_height;
            if ((ratio_height < min_height || ratio_height > max_height) && current_width > min_width) {

                ratio_height = Math.min(Math.max(ratio_height, min_height), max_height);
                var ratio_width = ratio_height / keep_ratio;
                if (ratio_width >= min_width && ratio_width <= max_width) {
                    current_width = ratio_width;
                    current_height = ratio_height;
                }
            }

            return {width: _.toInteger(current_width), height: _.toInteger(current_height)};
        }

        var cityFromQuery = function (params) {
            return {
                plugin: 'sdekint',
                module: 'cities',
                action: 'index',
                country: this.$countryFrom.val(),
                term: params.term
            };
        }.bind(this);
        var cityToQuery = function (params) {
            return {
                plugin: 'sdekint',
                module: 'cities',
                action: 'index',
                country: this.$countryTo.val(),
                term: params.term
            };
        }.bind(this);
        var displayMap = function () {
            var map = new ymaps.Map('sdekint-map-control', {
                center: [55.76, 37.64],
                zoom: 7,
                controls: ['geolocationControl', 'trafficControl', 'typeSelector', 'zoomControl']
            });
            this.geoCollection = $.sdekint.Ymaps.geocodeSdekPoints(this.point_list, this.balloon_content);
            map.geoObjects.add(this.geoCollection);
            map.setBounds(this.geoCollection.getBounds(), {checkZoomRange: true});
        }.bind(this);
        var calculate = function () {
            var $form = $('#s-sdekint-calculator-form');
            var params = {
                params: {
                    from_city: _.toNumber($('#s-sdekint-city-from').val()),
                    to_city: _.toNumber($('#s-sdekint-city-to').val()),
                    packages: [
                        {
                            "length": $.sdekint.Utils.formatIntNumber($form.find(':input[name=sizeX]').val(), 5),
                            width: $.sdekint.Utils.formatIntNumber($form.find(':input[name=sizeY]').val(), 5),
                            height: $.sdekint.Utils.formatIntNumber($form.find(':input[name=sizeZ]').val(), 5),
                            weight: $.sdekint.Utils.formatNumber($form.find(':input[name=weight]').val(), 0.5)
                        }
                    ]
                }
            };

            return $.shop.getJSON(
                '?plugin=sdekint&module=tariffs&action=get',
                params,
                function (r) {
                    var data = {
                        tariffs: r.data.tariffs || [],
                        cityTo: $('#s-sdekint-city-to').select2('data')[0]
                    };

                    data.hasPoints = data.tariffs.findIndex(function (t) {
                        return t.to === 'stock';
                    }) !== -1;

                    var html = this.template_result.render(data, {
                        stringify: function (v) {
                            return JSON.stringify(v, null, 2);
                        },
                        daysOfDelivery: function (deliveryPeriodMin, deliveryPeriodMax) {
                            return deliveryPeriodMin === deliveryPeriodMax ? deliveryPeriodMin : deliveryPeriodMin + '&ndash;' + deliveryPeriodMax;
                        },
                        humanizeDelivery: function (v) {
                            switch (v) {
                                case 'door':
                                    return 'дверь';
                                case 'stock':
                                    return 'склад';
                            }
                            return '??';
                        }
                    });
                    $('#s-sdekint-tariffs-container').html(html);

                    //this.showPoints(data.tariffs.findIndex(t => t.to === 'stock') !== -1);
                }.bind(this),
                function (e) {
                    var html = this.template_errors.render(e);
                    $('#s-sdekint-tariffs-container').html(html);
                    $('#city-to-info').hide();
                    return false;
                }.bind(this)
            );
        }.bind(this);

        this.data = data || {};
        this.wa_url = data.wa_root_url || '/';
        var cityTo = {};
        this.point_list = [];
        this.geoCollection = false;

        $.views.helpers('monetary', function (v) {
            return $.sdekint.Utils.monetary($.sdekint.Utils.formatNumber(v));
        });

        this.template_result = $.templates('#CalcResult');
        this.template_to_city = $.templates('#CityToTemplate');
        this.template_errors = $.templates('#CalcErrorsTemplate');
        this.$form = $('#s-sdekint-calculator-form');
        this.balloon_content = $.templates('#BalloonContent');

        $.fn.select2.defaults.set('theme', 'sdekint');

        this.$countryFrom = $('#s-sdekint-country-from').select2({
            data: $.sdekint.Select2.countryOptions(data.countries),
            minimumResultsForSearch: Infinity,
            templateResult: function (c) {
                return $.sdekint.Select2.formatCountry(c, this.wa_url);
            }.bind(this)
        });

        this.$cityFrom = $('#s-sdekint-city-from').select2({
            ajax: {
                url: '',
                data: cityFromQuery,
                delay: 250,
                processResults: $.sdekint.Select2.formatCities
            },
            minimumInputLength: 2
        });

        this.$countryTo = $('#s-sdekint-country-to').select2({
            data: $.sdekint.Select2.countryOptions(data.countries),
            minimumResultsForSearch: Infinity,
            templateResult: function (c) {
                return $.sdekint.Select2.formatCountry(c, this.wa_url);
            }.bind(this)
        });

        this.$cityTo = $('#s-sdekint-city-to').select2({
            ajax: {
                url: '',
                data: cityToQuery,
                delay: 250,
                processResults: $.sdekint.Select2.formatCities
            },
            minimumInputLength: 2
        }).change(
            function () {
                this.cityTo = this.$cityTo.select2('data')[0];
                var html = this.template_to_city.render({city: this.cityTo}, {humanize: $.sdekint.Select2.readableCity});
                $('#city-to-info').html(html).show();
                $('#s-sdekint-tariffs-container').empty();
            }.bind(this)
        );

        this.$form.on('change', function () {
            this.$form.find(':input[type=submit]').prop('disabled', !this.$cityFrom.val() || !this.$cityTo.val());
        }.bind(this));

        $('#s-sdekint-content')
            .off()
            .on('submit', '#s-sdekint-calculator-form', function () {
                calculate();
                return false;
            }.bind(this))
            .on('click', '.sdekint-point-map-btn', function (e) {
                var $loading = $('<i class="icon16 loading"></i>').insertAfter(e.target),
                    d = new $.sdekint.Dialog({
                        html: $('#MapDialogTemplate').render(),
                        onOpen: function ($wrapper, dialog) {
                            $.when(
                                $.sdekint.Ymaps.load(),
                                $.sdekint.Http.loadPointsByCity(
                                    this.cityTo.id,
                                    null,
                                    function (r) {
                                        this.point_list = r.data.office_list;
                                    }.bind(this)
                                )
                            ).done(function () {
                                dialog.$block.find('.sdekint-dialog-content:first').html('<div id="sdekint-map-control" style="width: 100%; height: 100%;"></div>');
                                dialog.resize(boxSizeCss($(window).width() * 0.6, 480, 360, $(window).width() * 0.75, $(window).height() * 0.75, 0.75));
                                setTimeout(displayMap, 150);
                            });
                            dialog.$block.on('click', '.sdekint-dialog-close-button a', function () {
                                dialog.close();
                            });
                        }.bind(this),
                        onResize: function($wrapper, dialog){
                            setTimeout(function () {
                                var $content = dialog.$block.find('.sdekint-dialog-content:first');
                                $content.css({height: dialog.$block.height() - 24 + 'px'});
                            }, 120);
                        },
                        onClose: function () {
                            $loading.remove();
                        }
                    });

                return false;
            }.bind(this));
    };
})(jQuery);