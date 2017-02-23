"use strict";

$(function(){
	
	$('#table-select-all').on('change', function(){
		var isChecked = $(this).is(':checked');
		$(this).parents('table').find('input.table-checkbox').each(function(){
			$(this).prop('checked', isChecked);
			setRowClass($(this));
		});
	});
	
	$('.table-checkbox').on('change', function(){
		
		setRowClass($(this));
		
		if ($('.table-checkbox:checked').length > 0)
		{
			$('.propel-table-checkbox-form').fadeIn();
		} else {
			$('.propel-table-checkbox-form').fadeOut();
		}
	});
	
});

function setRowClass(elem)
{
	if (elem.is(':checked'))
	{
		elem.parents('tr').find('td').each(function(){
			$(this).addClass('propel-row-selected');
		});
	} else {
		elem.parents('tr').find('td').each(function(){
			$(this).removeClass('propel-row-selected');
		});
	}
}

function tableModal(modalId)
{
	var $modal = $('#' + modalId);
	
	$('#' + modalId + ' button.generic-modal-cancel').on('click', function(event) {
		event.preventDefault();
		$modal.modal('hide');
	});
	
	$('div.propel-table-wrapper').prepend('<div class="trigger-wrapper"><button class="btn btn-warning bulk-options-trigger">Bulk Options</button></div>');
	
	$('.table-checkbox').on('change', function(){
		if ($('.table-checkbox:checked').length > 0)
		{
			$('button.bulk-options-trigger').fadeIn();
		} else {
			$('button.bulk-options-trigger').fadeOut();
		}
	});
	
	$('button.bulk-options-trigger').click(function(){
		$modal.modal('show');
	});
}