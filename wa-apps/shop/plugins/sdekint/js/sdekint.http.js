$.extend($.sdekint, {
    Http: {
        deleteOrderAction: function (id, success, fail) {
            return $.shop.jsonPost(
                '?plugin=sdekint&module=orderactions&action=delete',
                {id: id},
                success,
                fail
            );
        },

        deleteCalcRule: function (id, success, fail) {
            return $.shop.jsonPost(
                '?plugin=sdekint&module=shipping&action=delete',
                {id: id},
                success,
                fail
            );
        },

        deleteWidgetConfig: function (id, success, fail) {
            return $.shop.jsonPost(
                '?plugin=sdekint&module=widget&action=delete',
                {id: id},
                success,
                fail
            );
        },

        deletePickup: function (order_no, number_ttn, success, fail) {
            return $.shop.jsonPost(
                '?plugin=sdekint&module=courier&action=dismissPickup',
                {data: JSON.stringify({order_no: order_no, number_ttn: number_ttn})},
                success,
                fail
            );
        },

        loadAvailableWorkflowActions: function (state_id, success, fail) {
            return $.shop.getJSON(
                '?plugin=sdekint&module=orderactions&action=availableActions',
                {state_id: state_id},
                success,
                fail
            );
        },

        loadPointsByCity: function (city_id, limit, success, fail) {
            var params = {
                city_code: city_id
            };

            if (limit) {
                params.page = 1;
            } else {
                params.nolimit = 1;
            }

            return $.shop.getJSON(
                '?plugin=sdekint&module=office&action=index',
                params,
                success ? success : function (r) {
                },
                fail ? fail : function (r) {
                }
            );
        },
        loadRegions: function (country, success, fail) {
            return $.shop.getJSON('?plugin=sdekint&module=geography&action=regions', {country: country}, success, fail);
        },
        saveOrderAction: function (data, success, fail) {
            return $.shop.jsonPost(
                '?plugin=sdekint&module=orderactions&action=save',
                data,
                success,
                fail
            );
        },
        pickup: {
            save: function (data, success, fail) {
                return $.shop.jsonPost(
                    '?plugin=sdekint&module=courier&action=pickup',
                    {data: JSON.stringify(data)},
                    success,
                    fail
                );
            }
        }
    }
});