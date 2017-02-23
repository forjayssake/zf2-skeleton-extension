"use strict";

$(function(){
    $('#template-events').on('change', function(event){
        event.preventDefault();
        $('#parameters-wrapper').html('');
        if ($(this).val() !== '') {
            var $eventname = $(this).val();
            fetchEventParameters($eventname);
        }
    });

    $('#template-events').trigger('change');
});

function fetchEventParameters(eventName)
{
    hideWarning();

    var $action = $('#template-parameters-form').attr('action');

    $.ajax({
        url: $action,
        type: 'POST',
        dataType: 'json',
        async: true,
        data: {'eventname': eventName},
        success: function (data) {
            renderParameters(data);
        },
        error: function (data) {
            showWarning();
        }
    });

    function renderParameters(data)
    {
        var keys = Object.keys(data.parameters);
        var $template = '';
        for (var i = 0; i < keys.length; i++) {

            $.each(data.parameters[keys[i]], function(index, element) {
                $template = $('#parameterOption').html();
                $template = $template.replace('{id}', 'parameter_' + i);
                $template = $template.replace('{param}', '%' + keys[i] + '%');
                $template = $template.replace('{buttonValue}', element);
            });

            $('#parameters-wrapper').append($template);
        }

    }

    function showWarning()
    {
        $('#zero-parameters-warning').show();
    }

    function hideWarning()
    {
        $('#zero-parameters-warning').hide();
    }

    $('body').on('click', '.btn-template-parameter', function(){
        var $value = $(this).data('param');
        var $editor = $('.templates-ckeditor').attr('id');
        CKEDITOR.instances[$editor].insertText($value);
    });
}