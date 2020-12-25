/* global ymaps */

(function ($) {
    $.sdekint = $.sdekint || {};

    $.sdekint.Ymaps = {
         load: function() {
        return $.Deferred(function (defer) {
            if ((typeof ymaps !== 'undefined') && (typeof ymaps.Map !== 'undefined')) {
            ymaps.ready(defer.resolve);
        } else {
            $.getScript('https://api-maps.yandex.ru/2.1/?lang=ru_RU', function(){
                ymaps.ready(defer.resolve);
        });
        }
    });
    },

    geocodeSdekPoints: function(points, balloonTpl) {
        var geoCollection = new ymaps.Clusterer({preset: 'islands#darkgreenClusterIcons'});
        $.each(points, function (i, p) {
            geoCollection.add(
                new ymaps.Placemark(
                    [$.sdekint.Utils.formatNumber(p.coord_y), $.sdekint.Utils.formatNumber(p.coord_x)],
                    {
                        point_id: p.code,
                        balloonContentHeader: p.name,
                        balloonContent: balloonTpl.render(p)
                    },
                    {preset: 'islands#darkgreenDotIcon'}
                )
            );
        });
        return geoCollection;
    }
    };
})(jQuery);