/**
 * Created by snark on 10/2/15.
 */
(function ($) {
    $.Vkshop = {
        product: null,
        product_id: null,
        app_id: null,
        init: function (options) {
            var _this = this;
            $.extend(_this, options);
            $("#vkshop-product-images").imagepicker({limit: 5});
        },
        prepare: function (product_id) {
            var _this = this;
            var $images = $('#vkshop-product-images');
            //            console.log($images.val().join('&images[]='));

            if (!$images.val()) { return false; }

            $('<div id="s-plugin-vkshop-dialog"></div>').waDialog(
                {
                    width: '720px',
                    height: '530px',
                    url: '?plugin=vkshop&action=dialog&product_id=' + product_id + '&images[]=' + $images.val().join('&images[]='),
                    onLoad: function () {
                        var dialog = this;
                        $('.dialog-buttons', this).on(
                            'click', 'button.green', function () {
                                var $process_info = $('#s-plugin-vkshop-posting-in-process'),
                                $all_inputs = $(':input', $(dialog));

                                $all_inputs.prop('disabled', true);
                                $process_info.show();
                                $.post(
                                    '?plugin=vkshop&action=send', $.param(
                                        {
                                            data: {
                                                product_id: product_id,
                                                images: $images.val(),
                                                caption: $('textarea', '#s-plugin-vkshop-cropimage-description').val(),
                                                name: $('input', '#s-plugin-vkshop-vk-name').val(),
                                                price: $('input', '#s-plugin-vkshop-vk-price').val(),
                                                vk_cat_id: $('select', '#s-plugin-vkshop-vk-cat').val(),
                                                vk_album_id: $('select', '#s-plugin-vkshop-vk-album').val(),
                                                vk_new_album: $('input', '#s-plugin-vkshop-vk-new-album').val(),
                                                group_id: $('#s-plugin-vkshop-vk-group_id').val()
                                            }
                                        }
                                    )
                                ).done(
                                    function (response) {
                                        if (response.status && response.status == 'ok') {
                                            $(dialog).trigger('close');
                                        }
                                        else {
                                            var errors = '';
                                            for (var key in response.errors) {
                                                if (response.errors[key]) {
                                                    errors += response.errors[key];
                                                }
                                            }
                                            $('#s-plugin-vkshop-error').html(errors);
                                        }
                                    }
                                ).always(
                                    function () {
                                        $process_info.hide();
                                        $all_inputs.prop('disabled', false);
                                    }
                                );
                            }
                        );
                    },
                    onClose: function () {
                        $('#s-plugin-vkshop-dialog').remove();
                    }
                }
            );

        },
        disable: function () {
            var products = $.product_list.getSelectedProducts();
            if (!products.count) {
                alert($_('Please select at least one product'));
                return false;
            }
            $.post(
                '?plugin=vkshop&action=disable', {
                    'products': products,
                    'action': 'disable'
                }, function (d) {
                    if (d.status === 'ok') {
                        var product_list = $('#product-list');
                        product_list.find('.s-select-all:first').trigger('select', false);
                        $('form').find('input:checked').attr('checked', false);
                        $('#vkshop-disabled').html(d.data.disabled);
                        $('#vkshop-queued').html(d.data.queued);
                    }
                }, 'json'
            );
        },
        undisable: function () {
            var products = $.product_list.getSelectedProducts();
            if (!products.count) {
                alert($_('Please select at least one product'));
                return false;
            }
            $.post(
                '?plugin=vkshop&action=disable', {
                    'products': products,
                    'action': 'undisable'
                }, function (d) {
                    if (d.status === 'ok') {
                        var product_list = $('#product-list');
                        product_list.find('.s-select-all:first').trigger('select', false);
                        $('form').find('input:checked').attr('checked', false);
                        $('#vkshop-disabled').html(d.data.disabled);
                        $('#vkshop-queued').html(d.data.queued);
                    }
                }, 'json'
            );
        },
        queue: function () {
            var products = $.product_list.getSelectedProducts();
            //var $group_selector = $('#vkshop-group-selector');
            //var group_id = 0;
            //if ($group_selector) {
//                group_id = parseInt($group_selector.val());
  //          }
            if (!products.count) {
                alert($_('Please select at least one product'));
                return false;
            }
            /*
            if (group_id == 0) {
                alert($_('Please select at least one product'));
                return false;
            }
            */
            $.post(
                '?plugin=vkshop&action=queue', {
                    'products': products
                    //'group_id': group_id
                }, function (d) {
                    if (d.status === 'ok') {
                        var product_list = $('#product-list');
                        product_list.find('.s-select-all:first').trigger('select', false);
                        $('form').find('input:checked').attr('checked', false);
                        $('#vkshop-disabled').html(d.data.disabled);
                        $('#vkshop-queued').html(d.data.queued);
                    }
                }, 'json'
            );
        },
        clearqueue: function () {
            $.post(
                '?plugin=vkshop&action=clearqueue', function (d) {
                    if (d.status === 'ok') {
                        var product_list = $('#product-list');
                        product_list.find('.s-select-all:first').trigger('select', false);
                        $('form').find('input:checked').attr('checked', false);
                        $('#vkshop-disabled').html(d.data.disabled);
                        $('#vkshop-queued').html(d.data.queued);
                    }
                }, 'json'
            );
        },
        groupselect: function (e) {
            var group_id = $(e).val();
            $.post(
                '?plugin=vkshop&action=getalbums', {
                    'group_id': group_id
                }, function (d) {
                    if (d.status === 'ok') {
                        $('#vkshop-albums').html(d.data.albums);
                    }
                }, 'json'
            );
        }
    }

})(jQuery);

