(function ($) {$(function () {
	var btn_txt;
	$('.clean-hash-btn').on('click', function () {
		$(this).trigger('send');
		btn_txt = $(this).text();
		$(this).text('Загрузка..');
		$(this).prop('disabled', true);
	}).on('success', function () {
		$(this).prop('disabled', false);
		$(this).text('Сброшено');
		$(this).addClass('green').removeClass('blue');
		var elem = $(this);
		setTimeout(function () {
			elem.removeClass('green').addClass('blue');
			elem.text(btn_txt);
		}, 2000);
	});

	$('.help-btn, .help-sml-btn').on('click', function () {
		$('.help-block-template').contents().clone().waDialog({
			'width': '600px',
			'height': '500px',
			'buttons': $('.help-controls-template').contents().clone(),
			onSubmit: function (d)
			{
				d.trigger('close');
				return false;
			}
		});
	});
})})(jQuery);