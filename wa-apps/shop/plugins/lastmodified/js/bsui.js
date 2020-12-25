(function ($) {
	'use strict';

	$(function () {
		$.bsui = {
			init: function () {
				$('.bsui-smarty-textarea:not(.bsui-smarty-textarea_init)').each(function () {
					var $this = $(this);
					$this.addClass('bsui-smarty-textarea_init');
					var $textarea = $this.find('.bsui-smarty-textarea__control');

					var cm = CodeMirror.fromTextArea($textarea[0],
						{
							mode: 'smartymixed',
							tabSize: 4,
							indentUnit: 4,
							indentWithTabs: true,
							height: "dynamic",
							viewportMargin: 2,
							lineWrapping: true,
							onChange: function (cm) {
								$textarea.val(cm.getValue());
							}
						});

					$this.on('refresh', function () {
						cm.refresh();
					});

					setTimeout(function () {
						$this.trigger('refresh');
					}, 0);
				});

				$('.bsui-redactor-textarea:not(.bsui-redactor-textarea_init)').each(function () {
					var $this = $(this);
					$this.addClass('bsui-redactor-textarea_init');
					var $textarea = $this.find('.bsui-redactor-textarea__control');

					var r = $textarea.redactor();
				});

				$('.bsui-checkbox:not(.bsui-checkbox_init)').each(function () {
					var $this = $(this);
					$this.addClass('bsui-checkbox_init');
					var $control = $this.find('.bsui-checkbox__control');
					var checked = $control.prop('checked');

					if (checked)
					{
						$this.addClass('bsui-checkbox_checked');
					}
					else
					{
						$this.removeClass('bsui-checkbox_checked');
					}

					$control.on('change', function (e) {
						var checked = $control.prop('checked');

						if (checked)
						{
							$this.addClass('bsui-checkbox_checked');
						}
						else
						{
							$this.removeClass('bsui-checkbox_checked');
						}

						$this.trigger('change');
						e.stopPropagation();
					});

					$this.on('change', function (e) {
						e.stopPropagation();
					});
				});

				$('.bsui-radio:not(.bsui-radio_init)').each(function () {
					var $this = $(this);
					$this.addClass('bsui-radio_init');
					var $control = $this.find('.bsui-radio__control');
					var checked = $control.prop('checked');

					if (checked)
					{
						$this.addClass('bsui-radio_checked');
					}
					else
					{
						$this.removeClass('bsui-radio_checked');
					}

					$control.on('change', function (e) {
						var name = $control.attr('name');
						var checked = $control.prop('checked');

						if (checked)
						{
							$('.bsui-radio__control[name="'+name+'"]').closest('.bsui-radio').removeClass('bsui-radio_checked');
							$this.addClass('bsui-radio_checked');
						}
						else
						{
							$this.removeClass('bsui-radio_checked');
						}

						$this.trigger('change');
						e.stopPropagation();
					});

					$this.on('change', function (e) {
						e.stopPropagation();
					});
				});

				$('.bsui-toggle-block:not(.bsui-toggle-block_init)').each(function () {
					var $this = $(this);
					$this.addClass('bsui-toggle-block_init');
					var $checkbox = $this.find('.bsui-toggle-block__control');
					var checked = $checkbox.hasClass('bsui-checkbox_checked');

					if (checked)
					{
						$this.addClass('bsui-toggle-block_open');
						$.bsui.viewRefresh($this);
					}
					else
					{
						$this.removeClass('bsui-toggle-block_open');
					}

					$checkbox.on('change', function () {
						var checked = $checkbox.hasClass('bsui-checkbox_checked');

						if (checked)
						{
							$this.addClass('bsui-toggle-block_open');
							$.bsui.viewRefresh($this);
						}
						else
						{
							$this.removeClass('bsui-toggle-block_open');
						}
					});
				});

				$('.bsui-ajax:not(.bsui-ajax_init)').each(function () {
					var $this = $(this);
					$this.addClass('bsui-ajax_init');
					var success_timeout;

					$this.on('send', function () {
						var data = {
							data: {}
						};
						$this.trigger('handleData', data);

						$.ajax({
							url: $this.data('action'),
							method: $this.data('method'),
							dataType: $this.data('dataType'),
							data: data.data,
							beforeSend: function (jqXHR) {
								clearTimeout(success_timeout);
								$this.removeClass('bsui-ajax_status_success');
								$this.removeClass('bsui-ajax_status_error');
								$this.addClass('bsui-ajax_status_loading');
								var options = {
									jqXHR: jqXHR, return: true
								};
								$this.trigger('beforeSend', options);

								if (!options.return)
								{
									$this.removeClass('bsui-ajax_status_loading');
									$this.trigger('cancel', {
										jqXHR: jqXHR
									});

									return false;
								}
							},
							complete: function (jqXHR, textStatus) {
								$this.removeClass('bsui-ajax_status_loading');
								$this.trigger('complete', {
									jqXHR: jqXHR, textStatus: textStatus
								});
							},
							success: function (data, textStatus, jqXHR) {
								$this.addClass('bsui-ajax_status_success');
								$this.trigger('success', {
									data: data, textStatus: textStatus, jqXHR: jqXHR
								});

								success_timeout = setTimeout(function () {
									$this.removeClass('bsui-ajax_status_success');
								}, 2000);
							},
							error: function (jqXHR, textStatus, errorThrown) {
								$this.addClass('bsui-ajax_status_error');
								$this.trigger('error', {
									jqXHR: jqXHR, textStatus: textStatus, errorThrown: errorThrown
								});
							}
						});

						return false;
					});

					$this.on('beforeSend complete success error cancel handleData', function (e) {
						e.stopPropagation();
					});
				});

				$('.bsui-form:not(.bsui-form_init)').each(function () {
					var $this = $(this);
					$this.addClass('bsui-form_init');

					if ($this.hasClass('bsui-ajax'))
					{
						$this.on('handleData', function (e, data) {
							$this.data('action', $this.attr('action'));
							$this.data('method', $this.attr('method'));
							data.data = $this.serialize();
						});

						$this.on('submit', function (e) {
							e.preventDefault();

							// Пропуск вперёд CodeMirror обработчиков
							setTimeout(function () {
								$this.trigger('send');
							}, 0);
						});

						$this.on('beforeSend', function () {
							var $submit = $this.find('.bsui-form__submit');
							$submit.attr('disabled', 'disabled');
							$submit.prop('disabled', true);
						});

						$this.on('cancel complete', function () {
							var $submit = $this.find('.bsui-form__submit');
							$submit.removeAttr('disabled');
							$submit.prop('disabled', false);
						});
					}
				});

				$('.bsui-select:not(.bsui-select_init)').each(function () {
					var $this = $(this);
					var $control = $this.find('.bsui-select__control');
					$this.addClass('bsui-select_init');

					if ($this.hasClass('bsui-ajax'))
					{
						$control.on('change', function () {
							$this.trigger('send');
						});

						$this.on('handleData', function (e, data) {
							data.data = {value: $control.val()};
						});

						$this.on('beforeSend', function () {
							$control.attr('disabled', 'disabled');
							$control.prop('disabled', true);
						});

						$this.on('cancel complete', function () {
							$control.removeAttr('disabled');
							$control.prop('disabled', false);
						});
					}
				});
			},
			viewRefresh: function (context) {
				context = context ? context : document;
				$('.bsui-smarty-textarea.bsui-smarty-textarea_init:visible', context).trigger('refresh');
			}
		};

		$.bsui.init();
	})
})(jQuery);