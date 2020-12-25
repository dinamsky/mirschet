(function ($) {
    $.sdekint = $.sdekint || {};
    $.sdekint.CourierCalls = function () {
        this.$table = $.sdekint.$content.find('.couriercalls__list');

        this.$table.off('.sdekint-couriercalls')
            .on('click', 'a.js-action', function (evt) {
                var $this = $(evt.currentTarget);
                var action = $this.data('action'), confirm_msg = $this.data('js-confirm');
                if (!action) {
                    return false;
                }
                var fn_name = 'perform' + action.substring(0, 1).toUpperCase() + action.substring(1) + 'JsAction';
                if (typeof this[fn_name] !== 'function') {
                    return false;
                }

                if (confirm_msg) {
                    if (!window.confirm(confirm_msg)) {
                        return false;
                    }
                }

                var $row = $this.closest('.couriercalls__data-row');

                this[fn_name]($row);
                return false;
            }.bind(this));

        this.performDeleteJsAction = function ($row) {
            var dispatch_number = $row.data('dispatch-number'), order_no = $row.data('order-no'),
                number_ttn = $row.data('number-ttn');

            if (!dispatch_number || !order_no || !number_ttn) {
                console.error('Invalid data in the row');
                return;
            }
            $.sdekint.Http.deletePickup(order_no, number_ttn,
                function () {
                    window.location.reload();
                },
                function () {
                    return false;
                }
            );
        };
    };
})(jQuery);