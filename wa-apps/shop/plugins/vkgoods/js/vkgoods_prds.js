$(document).ready(function () {
	$("#vkgoods_prds").click(function () {
        var products = $.product_list.getSelectedProducts(true);
        if (!products.count) {
            alert('Выберите хотя бы один товар');
            return false;
        }
        $.post('?plugin=vkgoods&action=tmpltdlg', $(this).serializeArray().concat(products.serialized), function(mydialog) {
			$(mydialog).waDialog({
				'height' : '500px',
				'width' : '660px',
				'onLoad' : function() {
				},
				'onClose' : function(f) {
					$(this).remove;
				},
				'esc' : false,
			});
		});
        return false;
	});
	
	$('#vkgoods-unpublished').click(function(){
	    var products = $.product_list.getSelectedProducts(true);
	    if (!products.count) {
		$('.s-select-all').attr('checked','checked');
	        products = $.product_list.getSelectedProducts(true);
	        $('.s-select-all').attr('checked',false);
	    }
	    $.post('?plugin=vkgoods&action=small&event=unpublished', $(this).serializeArray().concat(products.serialized), function(data){
		    $('.vkgoods-li').attr('id', 'vkgoods-'+data.hash+'-');
		    $('#s-vkgoods-unpublished-a').attr('href', '#/products/hash=vkgoods-'+data.hash);
		    $('.vkgoods-li').show();
		    window.location.href = "?action=products#/products/hash=vkgoods-" + data.hash;
	    }, 'json');
	    return false;
	});
});