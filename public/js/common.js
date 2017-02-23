"use strict";

$(function(){
	
	//$('div.alert-temporary').delay(5000).fadeOut('slow');
	
	$('.datepicker').datetimepicker({
		format : 'YYYY-MM-DD HH:mm',
		showTodayButton : true,
		showClose : true,
		widgetPositioning : { horizontal: 'auto', vertical: 'bottom'}
	});

	$('.tooltip-element').tooltip();

});
