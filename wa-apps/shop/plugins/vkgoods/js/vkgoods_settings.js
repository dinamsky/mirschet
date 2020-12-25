$(document).ready(function() {
	$('#vkgoods_shop_vkgoods_prd_set').click(function(){
		if($('#vkgoods_shop_vkgoods_prd_set').prop('checked')){
			$('#vkgoods_shop_vkgoods_set_id').prop('disabled', false);
		}else{
			$('#vkgoods_shop_vkgoods_set_id').prop('disabled', 'disabled');
		}
	});
	
	if($('#vkgoods_shop_vkgoods_prd_set').prop('checked')){
		$('#vkgoods_shop_vkgoods_set_id').prop('disabled', false);
	}else{
		$('#vkgoods_shop_vkgoods_set_id').prop('disabled', 'disabled');
	}
	
	
});