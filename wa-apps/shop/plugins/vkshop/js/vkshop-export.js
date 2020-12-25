$.extend(
    $.importexport.plugins, {
        vkshop: {
            $form: null,
            progress: false,
            ajax_pull: {},
            data: {
                params: {}
            },
            debug: {
                'memory': 0.0,
                'memory_avg': 0.0
            },

            init: function (data) {
                $.shop.trace('init data', data);
                this.$form = $('#s-plugin-vkshop');
                $.extend(this.data, data);
            },

            action: function () {

            },
            GetcodeAction: function () {

            },
            getcodeAction: function () {

            },
            onInit: function () {
                $.importexport.products.init(this.$form);

                this.$form.unbind('submit.vkshop').bind(
                    'submit.vkshop', function (evt) {
                        $.shop.trace('submit.vkshop ' + evt.namespace, evt);
                        $.importexport.plugins.vkshop.vkshopHandler(this);
                        return false;
                    }
                );
            },

            actionHandler: function (elm) {

                $.shop.trace('actionHadler arg', elm);
                return false;
            },

            vkshopHandler: function (elm) {
                var self = this;
                self.progress = true;
                self.form = $(elm);
                var data = self.form.serialize();
                var url = $(elm).attr('action');
                $.shop.trace('elm', elm);
                self.form.find('.errormsg').text('');
                self.form.find(':input').prop('disabled', true);
                self.form.find(':submit').hide();
                self.form.find('.progressbar .progressbar-inner').css('width', '0');
                self.form.find('.progressbar').show();
                self.form.find('#plugin-vkshop-report').hide();

                $.ajax(
                    {
                        url: url,
                        data: data,
                        dataType: 'json',
                        type: 'post',
                        success: function (response) {
                            if (response.error) {
                                self.form.find(':input').prop('disabled', false);
                                self.form.find(':submit').show();
                                self.form.find('.js-progressbar-container').hide();
                                self.form.find('.shop-ajax-status-loading').remove();
                                self.progress = false;
                                self.form.find('.errormsg').text(response.error);
                            } else {
                                self.form.find('.progressbar').attr('title', '0.00%');
                                self.form.find('.progressbar-description').text('0.00%');
                                self.form.find('.js-progressbar-container').show();

                                self.ajax_pull[response.processId] = [];
                                self.ajax_pull[response.processId].push(
                                    setTimeout(
                                        function () {
                                            $.wa.errorHandler = function (xhr) {
                                                return !((xhr.status >= 500) || (xhr.status == 0))
                                            };
                                            self.progressHandler(url, response.processId, response);
                                        }, 100
                                    )
                                );
                                self.ajax_pull[response.processId].push(
                                    setTimeout(
                                        function () {
                                            self.progressHandler(url, response.processId, null);
                                        }, 2000
                                    )
                                );
                            }
                        },
                        error: function () {
                            self.form.find(':input').attr('disabled', false);
                            self.form.find(':submit').show();
                            self.form.find('.js-progressbar-container').hide();
                            self.form.find('.shop-ajax-status-loading').remove();
                            self.form.find('.progressbar').hide();
                        }
                    }
                );
            },

            progressHandler: function (url, processId, response) {
                // display progress
                // if not completed do next iteration
                var self = $.importexport.plugins.vkshop;
                var $bar;
                if (response && response.ready) {
                    $.wa.errorHandler = null;
                    var timer;
                    while (timer = self.ajax_pull[processId].pop()) {
                        if (timer) {
                            clearTimeout(timer);
                        }
                    }
                    $bar = self.form.find('.progressbar .progressbar-inner');
                    $bar.css(
                        {
                            'width': '100%'
                        }
                    );
                    $.shop.trace('cleanup', response.processId);


                    $.ajax(
                        {
                            url: url,
                            data: {
                                'processId': response.processId,
                                'cleanup': 1
                            },
                            dataType: 'json',
                            type: 'post',
                            success: function (response) {
                                $.shop.trace('report', response);
                                self.form.find('.js-progressbar-container').hide();
                                var $report = $("#plugin-vkshop-report");
                                $report.show();
                                if (response.report) {
                                    $report.find(".value:first").html(response.report);
                                }
                                self.form.find(':input').prop('disabled', false);
                                self.form.find(':submit').show();
                                //$.storage.del('shop/hash');
                            }
                        }
                    );

                } else if (response && response.error) {

                    self.form.find(':input').attr('disabled', false);
                    self.form.find(':submit').show();
                    self.form.find('.js-progressbar-container').hide();
                    self.form.find('.shop-ajax-status-loading').remove();
                    self.form.find('.progressbar').hide();
                    self.form.find('.errormsg').text(response.error);

                } else {
                    var $description;
                    if (response && (typeof(response.progress) != 'undefined')) {
                        $bar = self.form.find('.progressbar .progressbar-inner');
                        var progress = parseFloat(response.progress.replace(/,/, '.'));
                        $bar.animate(
                            {
                                'width': progress + '%'
                            }
                        );
                        self.debug.memory = Math.max(0.0, self.debug.memory, parseFloat(response.memory) || 0);
                        self.debug.memory_avg = Math.max(0.0, self.debug.memory_avg, parseFloat(response.memory_avg) || 0);

                        var title = 'Memory usage: ' + self.debug.memory_avg + '/' + self.debug.memory + 'MB';
                        title += ' (' + (1 + response.stage_num) + '/' + (0 + response.stage_count) + ')';

                        //var message = response.progress + ' — ' + response.stage_name;
                        var message = response.progress;

                        $bar.parents('.progressbar').attr('title', response.progress);
                        $description = self.form.find('.progressbar-description');
                        $description.text(message);
                        $description.attr('title', title);
                    }
                    if (response && (typeof(response.warning) != 'undefined')) {
                        $description = self.form.find('.progressbar-description');
                        $description.append('<i class="icon16 exclamation"></i><p>' + response.warning + '</p>');
                    }

                    var ajax_url = url;
                    var id = processId;

                    self.ajax_pull[id].push(
                        setTimeout(
                            function () {
                                $.ajax(
                                    {
                                        url: ajax_url,
                                        data: {
                                            'processId': id
                                        },
                                        dataType: 'json',
                                        type: 'post',
                                        success: function (response) {
                                            self.progressHandler(url, response ? response.processId || id : id, response);
                                        },
                                        error: function () {
                                            self.progressHandler(url, id, null);
                                        }
                                    }
                                );
                            }, 1000
                        )
                    );
                }
            }
        }
    }
);

(function ($) {
    $.Vkshop = {
        init: function () {
            var self = this,
                hash = self.getHashParams(),
                location = window.location;
            if (hash.code) {
                window.location.hash = '#/vkshop/';
                $.post(
                    '?plugin=vkshop&action=vklogin', {
                        'group_id': hash['/vkshop/getcode/?group'],
                        'code':     hash.code,
                        'logout':   0
                    }, function (d) {
                        if (d.status === 'ok') {
                            $('#vkshop-login-' + hash['/vkshop/getcode/?group']).html(d.data.login_template);
                            $('#vkshop-export-groups').html(d.data.groups_template);
                            $('#vkshop-albums').html(d.data.albums_template);
                        }
                    }, 'json'
                );
            } else {

            }
        },
        logout: function (group_id) {
            $.post(
                '?plugin=vkshop&action=vklogin', {
                    'logout':   1,
                    'group_id': group_id
                }, function (d) {
                    if (d.status === 'ok') {
                        $('#vkshop-login-' + group_id).html(d.data.login_template);
                        //$('#vkshop-export').hide();
                    }
                }, 'json'
            );
        },
        radio: function (e) {
            var value = $(e).val();
            $('.vkshop-select').hide();
            if (value == 'type') {
                $('#vkshop-types').show();
            }
            if (value == 'set') {
                $('#vkshop-sets').show();
            }
            if (value == 'queue') {
                $('#vkshop-vk-album').show();
                $('#vkshop-vk-new-album').show();
            }
            if (value == 'all') {
                $('#vkshop-vk-album').show();
                $('#vkshop-vk-new-album').show();
            }
        },
        getHashParams: function () {
            var hashParams = {};
            var e,
                a = /\+/g,  // Regex for replacing addition symbol with a space
                r = /([^&;=]+)=?([^&;]*)/g,
                d = function (s) {
                    return decodeURIComponent(s.replace(a, " "));
                },
                q = window.location.hash.substring(1);

            while (e = r.exec(q)) {
                hashParams[d(e[1])] = d(e[2]); }
            return hashParams;
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
