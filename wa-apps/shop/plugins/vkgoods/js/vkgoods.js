$(document).ready(function() {

	$('#clrbtn').click(function() {
		$.get('?plugin=vkgoods&action=small', {
			'event' : 'exitvk'
		});
		location.reload();
	});

	//кнопка "Опубликовать" в диалоге "Опубликовать" на стр.товара
	$('.prod-goexport').click(function() {

		if ($('#storefront').val() == 'no_storefront') {
			alert("Необходимо выбрать витрину магазина");
			return false;
		}		
		if ($('#vkalbums').val() == 'new' && $('#title').val() == '') {
			alert("Не указано название для новой подборки");
			return false;
		}
		if ($('#vkcat').val() == 0) {
			alert("Не выбрана категория ВК к которой относится товар");
			return false;
		}

		$('#b_close').attr('disabled', 'disabled');
		$('#prod-goexport').hide();
		$('#iproc').show();
		var form = $('#vkgoods-prod-post');
		var url = form.attr('action');
		$.post(url, form.serialize(), function callback(data) {
			if (data.status == 'fail') {
				$('#iproc').hide();
				$('#proc_no').show();
				$('#msg').text(data.errors['message']).show();
				$('#b_close').attr('disabled', false);
			} else {
				$('#iproc').hide();
				$('#proc_yes').show();
				$('#msg').text(data.data['data']).show();
				$('#b_close').attr('disabled', false);
			}
		}, 'json');
		return false;
	});

	//кнопка Экспортировать на странице импорт/экспорт
	$('#goexport').click(function() {
		
		if ($('#storefront').val() == 'no_storefront') {
			alert("Необходимо выбрать витрину магазина");
			return false;
		}		
		if ($('#vkalbums').val() == 'new' && $('#title').val() == '') {
			alert("Не указано название для новой подборки");
			return false;
		}
		if ($('#vkcat').val() == 0) {
			alert("Не выбрана категория ВК к которой относится товар");
			return false;
		}		
		
		var form = $('#vk-expwait-form');
		$('.js-progressbar-container').show();

		form.find('.progressbar .progressbar-inner').css('width', '0%');
		form.find('.progressbar-description').text('0.000%');
		form.find('.progressbar').show();
		form.find('.button').hide();
		$("#s-reindex-report").hide();
		var url = form.attr('action');
		var c_f = form.find('.progressbar-description');
		var exp_process = $.post(url, form.serialize(), function callback(data) {
			if (data.error) {
				$('.s-csv-importexport-stats').show();
				$('#exp_done').hide();
				$('#exp_err').show();
				$('#err_txt').text(data.error);
				$('.js-progressbar-container').hide();
				$('#vkgoods-export-report').show();
				$('#vkgoods_link').hide();
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
						clearInterval(step);
						form.find('.progressbar .progressbar-inner').css({
							width : '100%'
						});
						form.find('#exp_time').text(r.time);
						form.find('#exp_prd').text(r.exp_prd);
						form.find('#txt_link').attr("href", r.link_url);
						if (r.exp_full != undefined && r.exp_full != 0) {
							form.find('#li_exp_full').show();
							form.find('#val_exp_full').text(r.exp_full);
						}
						if (r.exp_aid != undefined && r.exp_aid != 0) {
							form.find('#li_exp_aid').show();
							form.find('#val_exp_aid').text(r.exp_aid);
						}
						if (r.exp_actual != undefined && r.exp_actual != '0') {
							form.find('#li_exp_actual').show();
							form.find('#val_exp_actual').text(r.exp_actual);
						}
						if (r.err_prd != undefined && r.err_prd != '0') {
							form.find('#err_vkgoods_message').show();
						}
						if (r.err_hidden != undefined && r.err_hidden != 0) {
							form.find('#li_err_hidden').show();
							form.find('#val_err_hidden').text(r.err_hidden);
						}
						if (r.err_noimg != undefined && r.err_noimg != 0) {
							form.find('#li_err_noimg').show();
							form.find('#val_err_noimg').text(r.err_noimg);
						}
						if (r.err_delete != undefined && r.err_delete != 0) {
							form.find('#li_err_delete').show();
							form.find('#val_err_delete').text(r.err_delete);
						}
						if (r.err_vkpid != undefined && r.err_vkpid != 0) {
							form.find('#li_err_vkpid').show();
							form.find('#val_err_vkpid').text(r.err_vkpid);
						}
						if (r.err_imgupload != undefined && r.err_imgupload != 0) {
							form.find('#li_err_imgupload').show();
							form.find('#val_err_imgupload').text(r.err_imgupload);
						}
						if (r.exp_err_prd != undefined && r.exp_err_prd != 0) {
							form.find('#li_err_prd').show();
							form.find('#val_err_prd').text(r.exp_err_prd);
						}
						if (r.err_album != undefined && r.err_album != 0) {
							form.find('#li_err_album').show();
							form.find('#val_err_album').text(r.err_album);
						}
						if (r.null_count != undefined && r.null_count != 0) {
							form.find('#li_null_count').show();
							form.find('#val_null_count').text(r.null_count);
						}
						

						form.find('#vkgoods-export-report').show();
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
	

	//Кнопка Опубликовать в диалоге вызванном со страницы со списками товаров 	
	$('.prods-goexport').click(function() {
		
		if ($('#storefront').val() == 'no_storefront') {
			alert("Необходимо выбрать витрину магазина");
			return false;
		}		
		if ($('#vkalbums').val() == 'new' && $('#title').val() == '') {
			alert("Не указано название для новой подборки");
			return false;
		}
		if ($('#vkcat').val() == 0) {
			alert("Не выбрана категория ВК к которой относится товар");
			return false;
		}
		
		var form = $('#vkgoods-prod-post');
		$('.js-progressbar-container').show();

		form.find('#hide_prds_export').hide();
		form.find('.progressbar .progressbar-inner').css('width', '0%');
		form.find('.progressbar-description').text('0.000%');
		form.find('.progressbar').show();
		form.find('#prod-goexport').hide();
		form.find('#iproc').hide();
		form.find('#wait-export').hide();
		form.find('#b_close').attr('disabled', 'disabled');
		$("#s-reindex-report").hide();
		var url = form.attr('action');
		var c_f = form.find('.progressbar-description');
		var exp_process = $.post(url, form.serialize(), function callback(data) {
			form.find('input,select,textarea').attr('disabled', 'disabled');
			if (data.error) {
				$('.s-csv-importexport-stats').show();
				$('#exp_done').hide();
				$('#exp_err').show();
				$('#err_txt').text(data.error);
				$('.js-progressbar-container').hide();
				$('#vkgoods-export-report').show();
				$('#vkgoods_link').hide();
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
						clearInterval(step);
						form.find('.progressbar .progressbar-inner').css({
							width : '100%'
						});
						form.find('#exp_time').text(r.time);
						form.find('#exp_prd').text(r.exp_prd);
						form.find('#txt_link').attr("href", r.link_url);
						if (r.exp_full != undefined && r.exp_full != 0) {
							form.find('#li_exp_full').show();
							form.find('#val_exp_full').text(r.exp_full);
						}
						if (r.exp_aid != undefined && r.exp_aid != 0) {
							form.find('#li_exp_aid').show();
							form.find('#val_exp_aid').text(r.exp_aid);
						}
						if (r.exp_actual != undefined && r.exp_actual != '0') {
							form.find('#li_exp_actual').show();
							form.find('#val_exp_actual').text(r.exp_actual);
						}
						if (r.err_prd != undefined && r.err_prd != '0') {
							form.find('#err_vkgoods_message').show();
						}
						if (r.err_hidden != undefined && r.err_hidden != 0) {
							form.find('#li_err_hidden').show();
							form.find('#val_err_hidden').text(r.err_hidden);
						}
						if (r.err_noimg != undefined && r.err_noimg != 0) {
							form.find('#li_err_noimg').show();
							form.find('#val_err_noimg').text(r.err_noimg);
						}
						if (r.err_delete != undefined && r.err_delete != 0) {
							form.find('#li_err_delete').show();
							form.find('#val_err_delete').text(r.err_delete);
						}
						if (r.err_vkpid != undefined && r.err_vkpid != 0) {
							form.find('#li_err_vkpid').show();
							form.find('#val_err_vkpid').text(r.err_vkpid);
						}
						if (r.err_imgupload != undefined && r.err_imgupload != 0) {
							form.find('#li_err_imgupload').show();
							form.find('#val_err_imgupload').text(r.err_imgupload);
						}
						if (r.exp_err_prd != undefined && r.exp_err_prd != 0) {
							form.find('#li_err_prd').show();
							form.find('#val_err_prd').text(r.exp_err_prd);
						}
						if (r.err_album != undefined && r.err_album != 0) {
							form.find('#li_err_album').show();
							form.find('#val_err_album').text(r.err_album);
						}
						if (r.null_count != undefined && r.null_count != 0) {
							form.find('#li_null_count').show();
							form.find('#val_null_count').text(r.null_count);
						}

						form.find('#before-export').hide();
						form.find('#vkgoods-export-report').show();
						form.find('.js-progressbar-container').hide();
						form.find('#exp_err').hide();
						form.find('#after-export').show();
						form.find('#b_close').attr('disabled', false);
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
	
	
	
	
	//Обработка выбора сообщества ВК
	$('#group').change(function() {
		switch ($("select#group").val()) {
		case 'no_group':
			$('#extsett').hide();
			$('#goexport').hide();
			$('#prod-goexport').hide();
			$('#wait-export').hide();
			$('#nogoods').hide();
			break;
		default:
			$.get('?plugin=vkgoods&action=getvkdata&event=sg&gid=' + $("select#group").val(), function(response) {
				if (response.data.error != undefined) {
					if (response.data.error.error_code = 7) {
						$('#extsett').hide();
						$('#goexport').hide();
						$('#prod-goexport').hide();
						$('#wait-export').hide();
						$('#nogoods').show();
					} else {
						$('#extsett').hide();
						$('#goexport').hide();
						$('#prod-goexport').hide();
						$('#wait-export').hide();
						alert("При получении данных с сервера ВКонтакте произошли ошибки. Подробнее см. файл vkgoods.log");
					}

					return false;
				} else {
					$("#vkalbums").empty();
					var albums = response.data.response.items;
					$('#vkalbums').append("<option value='new'>Новая подборка</option>");
					$('#vkalbums').append("<option value='0'>Не помещать в подборку</option>");
					for (var i in albums) {
						if (albums[i]['title'] != undefined && albums[i]['title'] != '') {
							$('#vkalbums').append("<option value=" + albums[i]['id'] + ">" + albums[i]['title'] + "</option>");
						}

					}
					$('#nogoods').hide();
					$('#new_vkalbum_title').show();
					$('#extsett').show();
					$('#goexport').show();
					$('#prod-goexport').show();
					$('#wait-export').show();
				}
			});

			break;
		}
	});

	//Обработка кнопки "Продолжить" в диалоге удаления товара
	$('#del_ok').click(function(data) {
		var form = $('#f_del');
		var url = form.attr('action');
		$('#del_ok').hide();
		$('#or').hide();
		$('#del_proc').show();
		$.post(url, form.serialize(), function callback(data) {
			$('#del_proc').hide();
			$('#del_info').hide();
			if ( data = 1) {
				form.find('#result_yes').text("Публикации товара удалены");
				$('#res_yes').show();
			} else {
				form.find('#result_no').text("Во время удаления произошли ошибки");
				$('#res_no').show();
			}
		});
		return false;
	});

	//Обработка кнопки Отложенная публикация в диалоге
	$('#wait-export').click(function(){
		if ($('#vkalbums').val() == 'new') {
			alert("Отложенные публикации можно делать только в уже существующую подборку\r\nВыберите подборку из существующих");
			return false;
		}
		if ($('#vkcat').val() == 0) {
			alert("Не выбрана категория к которой относится товар");
			return false;
		}
		if ($('#storefront').val() == 'no_storefront') {
			alert("Необходимо выбрать витрину магазина");
			return false;
		}
		$('#prod-goexport').hide();
		$('#wait-export').hide();
		$('#iproc').show();
		var form = $('#vkgoods-prod-post');
		var exp_process = $.post('?plugin=vkgoods&action=waitproduct', form.serialize(), function callback(data) {
			if (data.status == 'fail') {
				$('#iproc').hide();
				$('#proc_no').show();
				$('#msg').text('Непредвиденная ошибка');
				$('#msg').show();
			} else {
				$('#iproc').hide();
				$('#proc_yes').show();
				$('#msg').text('Успешно добавлено. Отложенных публикаций: '+data.data['count']);
				$('#msg').show();
			}
		}, 'json');
		return false;
	});
	
	//вызов диаолога Менеджера удалений
	$('#delman').click(function(){
		$.post('?plugin=vkgoods&action=tmpltdlg', {'event':'delman'}, function(deldialog){
			$(deldialog).waDialog({
				'height' : '380px',
				'width' : '660px',
				'onLoad' : function() {
				},
				'onClose' : function(f) {
					if($('#reload').attr('need') == 'yes'){
						location.reload();
					}
					$(this).remove;
				},
				'esc' : false,
			});
		});
	});
	
	//кнопка Очистить отложенные публикации
	$('#clearwait').click(function(){
		$.get('?plugin=vkgoods&action=small&event=clearwait', function(){
			location.reload();
			return false;
		});
	});
	
	
	//Обработка выбора подборки товаров
	$('#vkalbums').change(function() {
		if ($('select#vkalbums').val() == "new") {
			$('#new_vkalbum_title').show();
		} else {
			$('#new_vkalbum_title').hide();
		}
	});
	

});