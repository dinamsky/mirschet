/* global _, prettyPrint */
(function ($) {
    $.sdekint = $.sdekint || {};
    $.extend($.sdekint, {
        data: null,
        stopDispatchIndex: 0,
        previousHash: '',

        init: function (data) {
            this.data = data || {};
            this.$sidebar = $('#sdekint-sidebar');
            this.$content = $('#s-sdekint-content');
            $('#mainmenu').find('li.s-plugin-sdekint').removeClass('no-tab').addClass('selected');

            if (typeof ($.History) !== "undefined") {
                $.History.bind(function () {
                    this.dispatch();
                }.bind(this));
            }

            var hash = window.location.hash;
            if (hash === '#/' || !hash) {
                this.dispatch();
            } else {
                $.wa.setHash(hash);
            }
        },

        _selectSidebarAction: function (action) {
            var $li = this.$sidebar.find('ul.sdekint-menu li[data-action]');
            $li.removeClass('selected');
            $li.filter('[data-action="' + action + '"]').addClass('selected');
        },

        dispatch: function (hash) {
            function parsePath(path) {
                if (!path) {
                    throw {message: 'Invalid path', data: path};
                }
                path = path.replace(/^.*#\//, '');
                if (path.indexOf('/') === -1) {
                    path = path + '/';
                }
                return {
                    action: path.replace(/\/.*$/, '') || null,
                    tail: path.replace(/^[^\/]+\//, '').replace(/[\w_\-]+=.*$/, '').replace(/\/$/, '') || null,
                    raw: path
                };
            }

            if (this.stopDispatchIndex > 0) {
                this.stopDispatchIndex--;
                return false;
            }

            if (hash === undefined) {
                hash = window.location.hash;
            }

            if (!hash) {
                var $menuitem = this.$sidebar.find('ul.sdekint-menu:first').find('li:first > a:first');

                if ($menuitem.length) {
                    window.location.hash = hash = $menuitem.attr('href');
                }
            }

            if (this.previousHash === hash) {
                return;
            }
            this.previousHash = hash;
            hash = hash.replace(/^[^#]*#\/*/, '');
            try {
                var path = parsePath(hash);
                if (path.action && path.action.length && this[path.action + 'Action']) {
                    this[path.action + 'Action'](path.tail);
                } else {
                    throw {
                        message: 'Invalid action name: ' + (path.action || '{empty}') + 'Action',
                        data: (path.action || null)
                    };
                }
            } catch (e) {
                $.shop.trace(e.message, e.data);
            }
        },

        calculatorAction: function () {
            this._selectSidebarAction('calculator');
            $.shop.getJSON(
                '?plugin=sdekint&module=calculator',
                function (r) {
                    this.$content.html(r.data.html);
                    var calc = new $.sdekint.Calculator(this.data);
                }.bind(this)
            );
        },

        officesAction: function () {
            this._selectSidebarAction('offices');
            $.shop.getJSON(
                '?plugin=sdekint&module=office',
                function (r) {
                    this.$content.html(r.data.html);
                    var office = new $.sdekint.Office(this.data);
                }.bind(this)
            );
        },

        couriercallsAction: function (tail) {
            this._selectSidebarAction('couriercalls');
            tail = tail || '';
            var params = {};
            tail.split('/').forEach(function (c) {
                var kv = c.split(':');
                params[kv[0]] = kv[1] || null;
            });

            $.shop.getJSON(
                '?plugin=sdekint&module=courier&action=calls',
                params,
                function (r) {
                    this.$content.html(r.data.html);
                    var cc = new $.sdekint.CourierCalls();
                }.bind(this)
            );
        },

        pickupAction: function () {
            this._selectSidebarAction('pickup');
            var stored = $.storage.get('shop/sdekint/courier-call') || {};
            $.shop.getJSON(
                '?plugin=sdekint&module=courier&action=pickup',
                {stored: stored},
                function (r) {
                    this.$content.html(r.data.html);
                    var pickup = new $.sdekint.Pickup(this.data, r.data.order);
                }.bind(this)
            );
        },

        orderactionsAction: function () {
            this._selectSidebarAction('orderactions');

            function RulesReload() {
                return $.shop.getJSON(
                    '?plugin=sdekint&module=orderactions',
                    function (r) {
                        var $current_list = $('#s-plugin-sdekint-rule-list'), content, $new_html = $(r.data.html);
                        if ($current_list.length) {
                            var $table = $new_html.find('#s-plugin-sdekint-rule-list');
                            if($table.find('tbody tr').length) {
                                content = $table.html();
                                $current_list.html(content);
                            } else {
                                content = $new_html.find('#s-plugin-sdekint-rule-list-container').html();
                                $('#s-plugin-sdekint-rule-list-container').html(content);
                            }
                        } else {
                            content = $new_html.find('#s-plugin-sdekint-rule-list-container').html();
                            $('#s-plugin-sdekint-rule-list-container').html(content);
                        }
                    }
                );
            }

            function EditDialog(rule_id) {
                var d = new $.sdekint.Dialog({
                    html: $('#EditOrderactionDialog').render({rule_id: rule_id}),
                    onOpen: function ($wrapper, dialog) {
                        $.shop.getJSON(
                            '?plugin=sdekint&module=orderactions&action=edit',
                            {id: rule_id || 0},
                            function (r) {
                                dialog.$block.find('.sdekint-dialog-content').html(r.data.html);
                                dialog.resize({width: 550, height: 300});
                                dialog.$block.find('footer button, footer input[type=button], footer a[disabled]').prop('disabled', false);
                                var $submit_btn = dialog.$block.find('footer button[type=submit], footer input[type=submit], footer button.submit');
                                dialog.$block
                                    .on('change', '#s-plugin-sdekint-rule-current-store-state-select', function (e) {
                                        var $el = $(e.currentTarget);
                                        var state = $el.val(),
                                            $wf_action_select = $('#s-plugin-sdekint-rule-action-select');
                                        var $loading_span = $wf_action_select.closest('div.field').find('span.loading');
                                        $wf_action_select.empty().val(0).prop('disabled', true);
                                        $loading_span.show();
                                        $.sdekint.Http.loadAvailableWorkflowActions(
                                            state,
                                            function (r) {
                                                if (r.data.actions.length) {
                                                    $.each(r.data.actions, function (i, v) {
                                                        $wf_action_select.append($('<option></option>').val(v.id).text(v.name));
                                                    });
                                                    $wf_action_select.prop('disabled', false);
                                                }
                                            }
                                        ).always(function () {
                                            $loading_span.hide();
                                            $el.trigger('check_submit');
                                        });
                                    })
                                    .on('check_submit', function () {
                                        var valid = true;
                                        $('#s-plugin-sdekint-rule-current-store-state-select, #s-plugin-sdekint-rule-current-sdek-state-select, #s-plugin-sdekint-rule-action-select')
                                            .each(function () {
                                                var $this = $(this);
                                                if (!$this.val() || !$this.find('option[value="' + $this.val() + '"]').length) {
                                                    valid = false;
                                                }
                                            });
                                        $submit_btn.prop('disabled', !valid);
                                    })
                                    .on('click', 'footer button[type=submit], footer input[type=submit], footer button.submit', function () {
                                        $.sdekint.Http.saveOrderAction(dialog.$block.find('form').serialize())
                                            .always(function () {
                                                RulesReload()
                                                    .done(function () {
                                                        dialog.close();
                                                    });
                                            });

                                    })
                                    .trigger('check_submit');
                            }
                        );
                    }
                });
            }

            $.shop.getJSON(
                '?plugin=sdekint&module=orderactions',
                function (r) {
                    this.$content.html(r.data.html);
                    this.$content
                        .off()
                        .on('click', 'a.js-action[data-action=edit]', function (e) {
                            EditDialog($(e.currentTarget).data('id'));
                            return false;
                        })
                        .on('click', 'a.js-action[data-action=delete]', function (e) {
                            var $el = $(e.currentTarget);
                            var $tbody = $el.closest('tbody');
                            var d = new $.sdekint.Dialog({
                                html: $('#DeleteOrderactionDialog').render({message: $el.data('js-confirm') || 'Вы уверены?'}),
                                onOpen: function ($wrapper, dialog) {
                                    dialog.$block
                                        .on('click', '.red.submit', function () {
                                            var $tr = $el.closest('tr[data-id]');
                                            $.sdekint.Http.deleteOrderAction($tr.data('id'), function () {
                                                $tr.remove();
                                                if (!$tbody.find('tr').length) {
                                                    RulesReload();
                                                }
                                                dialog.close();
                                            });
                                        });
                                }
                            });
                            return false;
                        });
                }.bind(this)
            );
        },

        calccontrolAction: function () {
            this._selectSidebarAction('calccontrol');
            $.shop.getJSON(
                '?plugin=sdekint&module=shipping',
                function (r) {
                    this.$content.html(r.data.html);
                    this.$content.off()
                        .on('click', 'a[data-js-action=delete]', function (evt) {
                            var $this = $(evt.currentTarget);
                            var $row = $this.closest('tr[data-id]');
                            var d = new $.sdekint.Dialog({
                                html: $('#DeleteRuleDialog').render({message: $this.data('js-confirm') || 'Вы уверены?'}),
                                onOpen: function ($wrapper, dialog) {
                                    dialog.$block.on('click', '.red.submit', function () {
                                        $.sdekint.Http.deleteCalcRule($row.data('id'), function () {
                                            var $table = $this.closest('table');
                                            $row.remove();
                                            var $list_container = $('#sdekint-rules-list');
                                            if ($list_container.find('.pagination ul li').length) {
                                                window.location.reload();
                                            } else {
                                                dialog.close();
                                                if(!$table.find('tr[data-id]').length) {
                                                    $list_container.empty();
                                                }
                                            }
                                        });
                                    });
                                }
                            });
                            return false;
                        }.bind(this));
                }.bind(this)
            );
        },

        widgetcontrolAction: function (tail) {
            this._selectSidebarAction('widgetcontrol');
            var args = {};
            if (tail) {
                args = tail.split('/').reduce(function (o, v) {
                    var val = v.split(':', 2);
                    if (val.length < 2) {
                        val[1] = val[0];
                    }
                    o[val[0]] = val[1];
                    return o;
                }, {});
            }
            $.shop.getJSON('?plugin=sdekint&module=widget',
                args,
                function (r) {
                    this.$content.html(r.data.html);
                    this.$content.off()
                        .on('click', 'a[data-js-action=delete]', function (evt) {
                            var $this = $(evt.currentTarget);
                            var $row = $this.closest('tr[data-id]');
                            var d = new $.sdekint.Dialog({
                                html: $('#DeleteWidgetConfigDialog').render({message: $this.data('js-confirm') || 'Вы уверены?'}),
                                onOpen: function ($wrapper, dialog) {
                                    dialog.$block.on('click', '.red.submit', function () {
                                        $.sdekint.Http.deleteWidgetConfig($row.data('id'), function () {
                                            $row.remove();
                                            if ($('#widget_list').find('.pagination ul li').length) {
                                                window.location.reload();
                                            } else {
                                                dialog.close();
                                            }
                                        });
                                    });
                                }
                            });
                            return false;
                        }.bind(this));
                }.bind(this)
            );
        }
        ,

        widgetEditAction: function (tail) {
            this._selectSidebarAction('widgetcontrol');
            var showCode = function () {
                var $form = this.$content.find('.sdekint-widget-edit-form');
                var id = $form.find('input[name="widget[id]"]').val();
                var $code_wrapper = this.$content.find('#sdekint-code-sample');
                if ($code_wrapper.find('pre').length || !id) {
                    return;
                }
                $code_wrapper.html($('#CodeBlock').render({id: id}));
                prettyPrint();
            }.bind(this);
            $.shop.getJSON(
                '?plugin=sdekint&module=widget&action=edit',
                {id: tail},
                function (r) {
                    this.$content.html(r.data.html);
                    var $content = this.$content;
                    showCode();
                    this.$content.off()
                        .on('click', 'a.back', function () {
                            window.history.go(-1);
                            return false;
                        })
                        .on('submit', '.sdekint-widget-edit-form', function (evt) {
                            var $el = $(evt.currentTarget);
                            var $form = $(this);
                            var $loading = $form.find('.field.submit .value i.loading');
                            $loading.show();
                            $.shop.jsonPost(
                                $form.attr('action'),
                                $form.serialize(),
                                function (r) {
                                    if (r.data && r.data.id) {
                                        $form.find('input[name="widget[id]"]').val(r.data.id);
                                    }
                                    showCode();
                                    $loading.closest('div').append('<span class="success state successmsg" style="display: inline-block;">Сохранено</span>');
                                    setTimeout(function () {
                                        var $state = $loading.closest('div').find('span.state');
                                        $state.fadeOut(400).always(function () {
                                            $state.remove();
                                        });
                                    }, 3000);
                                }
                            ).always(function () {
                                $loading.hide();
                            });

                            return false;
                        });
                }.bind(this)
            );
        }
    })
    ;
})
(jQuery);