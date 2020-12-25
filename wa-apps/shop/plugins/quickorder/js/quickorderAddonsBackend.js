var QuickorderPluginAddons = (function ($) {

    QuickorderPluginAddons = function (options) {
        var that = this;

        /* DOM */
        that.$wrap = options.wrap;

        /* INIT */
        that.bindEvents();
    };

    QuickorderPluginAddons.prototype.bindEvents = function () {
        var that = this;

        /* Переключение блока "Информация свернута" */
        that.$wrap.find('.f-collapse-fl').change(function () {
            var checkbox = this;
            if (!checkbox.checked) {
                $(checkbox).next().hide();
            } else {
                $(checkbox).next().css('display', 'inline-block');
            }
        });

        /* Появление шаблона для редактирования остатков во всплывающем окне артикулов */
        that.$wrap.find('.js-show-sku-tocks-template').click(function () {
            $(this).hide().next().show();
            var c = CodeMirror.fromTextArea(that.$wrap.find('.f-sku-stocks-tmpl')[0], {
                tabMode: "indent",
                height: "dynamic",
                lineWrapping: true
            });
            that.$wrap.find('.f-sku-stocks-tmpl').data('codemirror', c);
        });

        /* Восстановление шаблона к оригиналу */
        that.$wrap.find('.js-revert-sku-stocks-template').click(function () {
            var btn = $(this);
            if (!btn.next('i').length) {
                btn.after('<i class="icon16 loading"></i>');
                $.post("?plugin=quickorder&action=revertSkuStocksTmpl", function (response) {
                    btn.next('i').remove();
                    if (response.status == 'ok' && response.data) {
                        var $tmpl = that.$wrap.find('.f-sku-stocks-tmpl');
                        $tmpl.val(response.data);
                        $tmpl.data('codemirror') && $tmpl.data('codemirror').setValue(response.data);
                    }
                });
            }
        });
    };

    return QuickorderPluginAddons;

})(jQuery);