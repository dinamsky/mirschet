$(document).ready(function() {
	$('#vkg-add').click(function() {
		$.post('?plugin=vkgoods&action=tmpltdlg', {
			'event' : 'add',
			'pid' : $(this).attr("pid")
		}, function(mydialog) {

			$(mydialog).waDialog({
				'height' : '600px',
				'width' : '660px',
				'onClose' : function(f) {
					$(this).remove;
					if ($('#msg').is(':visible')){
						location.reload();
					}
					
				},
				'esc' : false,
			});

		});
	});

	$('#vkg-del').click(function() {
		$.post('?plugin=vkgoods&action=tmpltdlg', {
			'event' : 'del',
			'pid' : $(this).attr("pid")
		}, function(mydialog) {
			$(mydialog).waDialog({
				'height' : '100px',
				'width' : '380px',
				'onClose' : function(f) {
					$(this).remove;
					if ($('#del_ok').is(':hidden')){
						location.reload();
					}				},
			});

		});
	});

	$('#vkg-upd').click(function() {
		var pid = $(this).attr("pid");
		$.post('?plugin=vkgoods&action=tmpltdlg', {
			'event' : 'upd',
			'pid' : pid
		}, function(mydialog) {
			$(mydialog).waDialog({
				'height' : '150px',
				'width' : '400px',
				'onClose' : function(f) {
					location.reload();
					$(this).remove
				},
				'onLoad' : function() {
					$.get('?plugin=vkgoods&action=small&event=upd', {
						'pid' : pid
					}, function(data) {
						var info = "<ul>";
						if (data['done'] > 0) {
							info += '<li><i class="icon16 yes"></i>Обновлено публикаций: ' + data['done'] + '</li>';
						}
						if (data['actual'] > 0) {
							info += '<li><i class="icon16 info"></i>Не требуется обновления публикаций: ' + data['actual'] + '</li>';
						}
						if (data['del_db'] > 0) {
							info += '<li><i class="icon16 yes"></i>Не найдено публикаций: ' + data['del_db'] + ' (информация о публикациях удалена)' + '</li>';
						}
						if (data['del_vkitem'] > 0) {
							info += '<li><i class="icon16 no"></i>Ошибок удаления публикаций: ' + data['del_vkitem'] + '</li>';
						}
						if (data['get_vkitem'] > 0) {
							info += '<li><i class="icon16 no"></i>Ошибок получения информации о публикациях: ' + data['get_vkitem'] + '</li>';
						}
						if (data['edit'] > 0) {
							info += '<li><i class="icon16 no"></i>Ошибок обновления публикаций: ' + data['edit'] + '</li>';
						}
						if (data['unexp'] > 0) {
							info += '<li><i class="icon16 no"></i>Неизвестных ошибок: ' + data['unexp'] + '</li>';
						}
						info += '</ul>';
						$('#iproc').hide();
						$('#info_txt').hide();
						$('#b_close').prop("disabled", false);
						$(info).appendTo('.dialog-content');

					}, 'json');
				}
			});
		});
	});

});
