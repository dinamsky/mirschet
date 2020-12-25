(function ($) {
    $.sdekint = $.sdekint || {};
    $.extend($.sdekint, {
        Select2: {
            formatCities: function (r) {
                if (r && r.status && r.status === 'ok' && r.data && $.isArray(r.data)) {
                    return {
                        results: $.map(r.data, function (i) {
                            i.text = $.sdekint.Select2.readableCity(i, true);
                            i.id = i.sdek_id;
                            return i;
                        })
                    };
                }
                return {results: []};
            },
            readableCity: function (c, short) {
                if (!c || !c.name || !c.Country) {
                    return '';
                }
                var chunks = [];
                chunks.push(c.name);
                if (c.Region && c.Region.name && (c.name.toLowerCase() !== c.Region.name.toLowerCase())) {
                    chunks.push(c.Region.name);
                }

                if (!short) {
                    chunks.push(c.Country.name);
                }

                return chunks.join(', ');
            },
            countryOptions: function (countries) {
                return $.map(countries, function (c) {
                    return {
                        id: c.iso3letter,
                        text: c.name
                    };
                });
            },

            /**
             * @param {{id:String, text:String}} c
             * @param wa_url String
             * @returns string
             */
            formatCountry: function (c, wa_url) {
                if (!c.id) {
                    c.id = '--';
                }
                return $(
                    '<span><img src="' + wa_url + 'wa-content/img/country/' + c.id + '.gif" class="img-flag" /> ' + c.text + '</span>'
                );
            }
        }
    });
})(jQuery);