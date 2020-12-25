/**
 * Created by snark on 9/28/15.
 */
(function ($) {
    $.Vkshop_settings = {
        row: null,
        n: null,
        confirm_string: null,
        init: function () {
            var self = this;
            self.initTabs();
        },
        initTabs: function () {
            var self = this, tabs = $("#vkshop-tabs").children(), cnt = $("#vkshop-tabs-content").children();
            cnt.hide().first().show();
            tabs.click(
                function (e) {
                    if (!$(this).hasClass('selected')) {
                        $(this).addClass("selected").siblings().removeClass("selected");
                        var tab = $("a", this).attr("href");
                        $(tab).fadeToggle().siblings().hide();
                    }
                    return false;
                }
            );
        },
        plus: function () {
            var self = this;
            //var n = $('.vkshop-group-row').length;
            //var $row = self.row.clone();
            self.row.find('.vkshop-row-input-id').attr('name', 'shop_vkshop[groups][' + self.n + '][id]').val('');
            self.row.find('.vkshop-row-input-name').attr('name', 'shop_vkshop[groups][' + self.n + '][name]').val('');
            self.row.find('.vkshop-row-input-app_id').attr('name', 'shop_vkshop[groups][' + self.n + '][app_id]').val('');
            self.row.find('.vkshop-row-input-app_secret').attr('name', 'shop_vkshop[groups][' + self.n + '][app_secret]').val('');
            self.row.find('.vkshop-row-select-settlement').attr('name', 'shop_vkshop[groups][' + self.n + '][settlement]').val('');

            $('.vkshop-group-row:last').after(self.row);
            self.n++;
        },
        minus: function (e) {
            if (confirm(this.confirm_string)) {
                var n = $('.vkshop-group-row').length;
                if (n > 1) {
                    $(e).closest('.vkshop-group-row').remove();
                }
            }
        },
        groupselect: function (e) {
            var group_id = $(e).val();
            $.post(
                '?plugin=vkshop&action=getalbums', {
                    'group_id': group_id
                }, function (d) {
                    if (d.status === 'ok') {
                        $('#s-plugin-cron-album').html(d.data.albums);
                    }
                }, 'json'
            );
        }
    }
})(jQuery);


