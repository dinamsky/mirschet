$(document).ready(function()
{    
    $('#region_form .one li').click(function(event) {
        event.preventDefault();
        $('#region_form .tab-content div.element').hide();
        $('#region_form .one li').removeClass("selected");
        $(this).addClass('selected');
                 
        var clicked = $(this).find('a:first').attr('href');
        $('#region_form  .tab-content ' + clicked).fadeIn('fast');
    });
	
	 $('#region_form .two li').click(function(event) {
        event.preventDefault();
        $('#region_form .two li').removeClass("selected");
        $(this).addClass('selected');
                 
        var clicked = $(this).find('a:first').attr('href').replace('#','');
		if(clicked == 'en')
		{
			$('.noten').hide();
			$('.notru').show();
			$('#region_form .uarr').click();
		}
		else
		{
			$('.notru').hide();
			$('.noten').show();
			$('#region_form .uarr').click();
		}
        //$('#region_form  .tab-content ' + clicked).fadeIn('fast');
    });
    
    $("#region_form  #sortable").sortable({
        placeholder: "ui-state-highlight",
        items: "li:not(.no-sortable)",
        handle: ".portlet",
        update: function( event, ui ) {$('.submit input').click();}
    });
    //$("#region_form  #sortable").disableSelection();        
});

function toogleRegionblock(node, element)
{
    var close = $(node).hasClass("uarr");
    
    $('#region_form  .uarr').addClass("darr").removeClass('uarr');   
    $('#region_form  .codehead').hide();
	
    if(!close)
    {
        if($('.two li:first').hasClass('selected'))
		{
			$('#region_form  .codehead' + element + '.ru').show();
		}
		else
		{
			$('#region_form  .codehead' + element + '.en').show();
		}
		
        $(node).addClass("uarr").removeClass('darr');
    }
    
}    
function removeRegionBlock(id)
{
    $('#region_form  #region' + id).remove();
    $('#region_form  .submit input').click();
}	
function autoSaveRegionSetting()
{	
	if($('#onOffAutoSave').attr('checked') == 'checked' && checkRegionDublicate())
        $('.submit input').click();
}

function addField()
{
	var reg = /^[a-z0-9]+$/;
    do{
		var input = prompt('Название нового поля (только прописные латинские буквы, цифры)', '');
		if(input != null)
			input = input.replace(/\s+/g, '');
		var result=reg.test(input) ? true : false;
	} while (input == '' || !result)
		
	if(input != '' && input != null && result)
	{
		if($('tr.codehead.field' + input).length > 0)
		{
			alert('Поле с таким названием уже есть!');
		}
		else
		{
			$.get('?plugin=region&module=settings&action=addField&name='+input, function( data ) {
				if(data.status == 'ok')
				{
					var fieldhtml = $('#templateNewField tbody').html();
					var fieldtemplate = fieldhtml.replace(/#field#/g, input);
					$('.templateRegionLi #lastcodehead').before(fieldtemplate);
					
					$('.codeheadplus').not(':last').each(function()
					{
						var trId = $(this).parent().parent().parent().attr('id').replace('region','');
						$('.codehead' + trId + '.codeheadplus').before(fieldtemplate.replace(/#id#/g, trId));
					});
					$('.uarr').click().click();
				}
				else
					alert('Ошибка добавления поля');
			});
		}
	}
}

function deleteField(name)
{
	if(confirm('Удалить поле у всех поддоменов?'))
	{
		$.getJSON('?plugin=region&module=settings&action=dropField&name='+name, function( data ) {
			if(data.status == 'ok')
				$('tr.field'+name).remove();
			else
				alert('Ошибка удаления поля');
		});		
	}
}