/* global _, ymaps */
(function ($) {
    $.sdekint = $.sdekint || {};
    $.extend($.sdekint, {
        Office: function (data) {
            this.map = null;
            this.points = [];
            this.geoCollection = null;
            this.balloon_content = $.templates('#BalloonContent');
            this.list_items_tpl = $.templates('#ListItems');
            this.wa_url = this.wa_url = data.wa_root_url || '/';
            $.fn.select2.defaults.set('theme', 'sdekint');
            this.$city = $('#s-sdekint-city').select2({
                ajax: {
                    url: '',
                    data: function (params) {
                        return {
                            plugin: 'sdekint',
                            module: 'cities',
                            action: 'index',
                            with_stocks: 1,
                            country: this.$country.val(),
                            term: params.term
                        };
                    }.bind(this),
                    processResults: $.sdekint.Select2.formatCities
                },
                minimumInputLength: 2
            });
            this.$country = $('#s-sdekint-country').select2({
                data: $.sdekint.Select2.countryOptions(data.countries || []),
                minimumResultsForSearch: Infinity,
                templateResult: function (c) {
                    return $.sdekint.Select2.formatCountry(c, this.wa_url);
                }.bind(this)
            });

            this.$city.on('change', function () {
                if (_.isEmpty(this.$city.val())) {
                    return; // todo: clear point if any
                }
                $.when(
                    $.sdekint.Ymaps.load(),
                    $.sdekint.Http.loadPointsByCity(
                        this.$city.val(),
                        false,
                        function (r) {
                            this.points = r.data.office_list;
                        }.bind(this))
                ).done(function () {
                    ymaps.ready(function () {
                        if (_.isEmpty(this.map)) {
                            this.map = new ymaps.Map('s-sdekint-map', {
                                center: [55.76, 37.64],
                                zoom: 7,
                                controls: ['geolocationControl', 'trafficControl', 'typeSelector', 'zoomControl']
                            });
                        }
                        this.geoCollection = $.sdekint.Ymaps.geocodeSdekPoints(this.points, this.balloon_content);
                        this.map.geoObjects.add(this.geoCollection);
                        this.map.setBounds(this.geoCollection.getBounds(), {checkZoomRange: true});
                        var $sdekint_list = $('#s-sdekint-list');
                        $sdekint_list.html(this.list_items_tpl.render({points: this.points}));
                        if (this.points.length) {
                            $sdekint_list.addClass('bordered');
                        } else {
                            $sdekint_list.removeClass('bordered');
                        }
                    }.bind(this));
                }.bind(this));
            }.bind(this));

            var $map_div = $('#s-sdekint-map');
            var w = $map_div.width(), h = $map_div.height(), top = $map_div.offset().top, wh = $(window).height();
            var height = Math.max(h, w * 0.75);

            $.shop.trace('Offices map', {height: height, sum: top + height});
            if (top + height > wh - 40) {
                height = Math.max(h, wh - 40 - top);
            }

            $map_div.css('height', height + 'px');
            $('#s-sdekint-list').css('height', height + 'px');


        }
    });
})(jQuery);