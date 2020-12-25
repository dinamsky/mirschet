$(document).ready(function() {
//Выбор сообщества в диалоге Менеджера удаления	
	$('#group_del').change(function() {
		switch ($("select#group_del").val()) {
		case 'no_group':
			$('#type_del').attr('disabled', 'disabled');
			$('#album_del').attr('disabled', 'disabled');
			$('#type_del option:first').attr('selected', 'yes');
			$('#album_del option:first').attr('selected', 'yes');
			$('#b_del').attr('disabled', 'disabled');
			break;
		default:
			$.get('?plugin=vkgoods&action=getvkdata&event=sg&gid=' + $("select#group_del").val(), function(response) {
				if (response.data.error != undefined) {
					if (response.data.error.error_code = 7) {
						$('#type_del').attr('disabled', 'disabled');
						$('#album_del').attr('disabled', 'disabled');
						$('#type_del option:first').attr('selected', 'yes');
						$('#album_del option:first').attr('selected', 'yes');
						$('#b_del').attr('disabled', 'disabled');
						$('#nogoods_del').show();
					} else {
						$('#type_del').attr('disabled', 'disabled');
						$('#album_del').attr('disabled', 'disabled');
						alert("При получении данных с сервера ВКонтакте произошли ошибки. Подробнее см. файл vkgoods.log");
					}

					return false;
				} else {
					$('#nogoods_del').hide();
					$("#album_del").empty();
					var albums = response.data.response.items;
					$('#album_del').append("<option value='no_album'>Выберите подборку</option>");
					for (var i in albums) {
						if (albums[i]['title'] != undefined && albums[i]['title'] != '') {
							$('#album_del').append("<option value=" + albums[i]['id'] + ">" + albums[i]['title'] + "</option>");
						}
					}
					$('#type_del').attr('disabled', false);
				}
			});

			break;
		}	
	});

//Кнопка Удалить в диалоге Менедера удаления
	$('#b_del').click(function(){
		var form = $('#vkgoods-prod-del');
		form.find('.js-progressbar-container').show();
		form.find('.progressbar .progressbar-inner').css('width', '0%');
		form.find('.progressbar-description').text('Собираются данные о публикациях для удаления...');
		form.find('.progressbar').show();
		$('#b_del').hide();
		$('#b_close').attr('disabled', 'disabled');
		$("#s-reindex-report").hide();
		var url = form.attr('action');
		var c_f = form.find('.progressbar-description');
		var exp_process = $.post(url, form.serialize(), function callback(data) {
			form.find('select').attr('disabled', 'disabled');
			if (data.error) {
				$('#preinit_error').show();
				$('#preinit_error_txt').text(data.error);
				$('.js-progressbar-container').hide();
				$('#vkgoods_link').hide();
				$('#b_close').attr('disabled', false);
				return false;
			}
			
			var procId = data.processId;
			var step = setInterval(function() {
                $.wa.errorHandler = function (xhr) {
                    return !((xhr.status >= 500) || (xhr.status == 0));
                };
				$.post(url, {
					processId : procId
				}, function fromstep(r) {
					if (r.ready == true) {
						console.log('Ответ: '+r);
						clearInterval(step);
						form.find('.progressbar .progressbar-inner').css({
							width : '100%'
						});
						form.find('#exp_time').text(r.time);
						form.find('#total').text(r.total);
						form.find('#done').text(r.done);
						$('#b_close').attr('disabled', false);
						$('#reload').attr('need', 'yes');

						if (r.errors != undefined && r.errors != 0) {
							form.find('#li_errors').show();
							form.find('#errors').text(r.errors);
						}

						form.find('#vkgoods-delete-report').show();
						form.find('.js-progressbar-container').hide();
						form.find('#exp_err').hide();
						form.find('#public_info').hide();
					} else {
						var progress = parseFloat(r.progress.replace(/,/, '.'));
						form.find('.progressbar-description').text(r.progress);
						form.find('.progressbar .progressbar-inner').animate({
							'width' : progress + '%'
						});
					};
				}, 'json');

			}, 1000);

		}, 'json');
		return false;		
	});
	
	
	
	
//Выбор Что удалять в диалоге Менеджера удаления	
	$('#type_del').change(function() {
		switch ($("select#type_del").val()) {
		case 'album':
			$('#album_del').attr('disabled', false);
			$('#b_del').attr('disabled', 'disabled');
			break;
		case 'no_del':
			$('#b_del').attr('disabled', 'disabled');
			$('#album_del').attr('disabled', 'disabled');
			$('#album_del option:first').attr('selected', 'yes');

			break;
		default:
			$('#album_del').attr('disabled', 'disabled');
			$('#b_del').attr('disabled', false);
			$('#album_del option:first').attr('selected', 'yes');
			break;
		}
	});
	
	$('#album_del').change(function(){
		switch ($("select#album_del").val()) {
		case 'no_album':
			$('#b_del').attr('disabled', 'disabled');
			break;
		default:
			$('#b_del').attr('disabled', false);
			break;
		}
	});
	
	$('#b_del').attr('disabled', 'disabled');

	
	
});