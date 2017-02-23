"use strict";

$(function(){
	
	$('button.generic-modal-confirm').on('click', function(){
		$(this).find('i').addClass('fa fa-refresh fa-spin');
		$(this).prop('disabled', true);
		$(this).parents('div.modal-footer').find('button.generic-modal-cancel').prop('disabled', true);
	});
	
	$('button.generic-modal-cancel').on('click', function(){
		$(this).parents('div.modal').modal('hide');
	});
	
});