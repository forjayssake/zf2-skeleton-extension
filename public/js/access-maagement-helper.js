"use strict";

$(function(){

    /**
     * create headers and section styling
     */
    $('.header-element').each(function(){
        var sectionTitle = $(this).data('section-title');
        $(this).parents('div.form-group').prepend('<div class="access-form-section-title"><h4>' + sectionTitle + '</h4></div>');
    });

    /**
     * pre-populate messages for currently stored
     *  access config on page load
     */
    $('.access-message').each(function(){
        populatePreviewMessage($(this));
    });

    /**
     * generate or remove alert message preview
     */
    $('.access-message').on('keyup', function(){
        populatePreviewMessage($(this));
    });

    function populatePreviewMessage(element)
    {
        var strLen = element.val().length;
        var name = element.attr('name');
        var typeList = name + 'type';
        var type = $('select[name=' + typeList + ']').val();

        // does this warning already exist ?
        if ($('#access-preview-wrapper .' + name).length == 0)
        {
            $('#access-preview-wrapper').append('<div class="' + name + '"><h4>' + element.data('name') + '</h4></div>');
            $('#access-preview-wrapper div.' + name).append('<div id="alert_' + name + '" class="alert ' + type + '"></div>');
        }

        if (strLen > 0)
        {
            var icon = fetchMessageIcon(type);
            var content = element.val();
            $('div#alert_' + name).html('<i class="fa ' + icon + '"></i> ' + stripHTML(content));
        } else {
            $('#access-preview-wrapper div.' + name).remove();
        }
    }

    /**
     * remove tags from text string
     * @param inString
     * @returns {*|jQuery}
     */
    function stripHTML(inString) {
        var outString = $("<script/>").html(inString).text();
        outString = $("<script/>").html(inString).text();

        return outString;
    };

    /**
     * change preview message alert class to match type selection
     */
    $('.access-message-type').on('change', function(){
        var name = $(this).attr('name');
        var type = $(this).val();
        var icon = fetchMessageIcon(type);
        var alertDiv = name.replace('type', '');

        if ($('#access-preview-wrapper div.' + alertDiv).length > 0) {
            $('#alert_' + alertDiv).removeClass().addClass('alert ' + type);
            $('#alert_' + alertDiv).find('i').removeClass().addClass('fa ' + icon);
        }
    });

    /**
     * clear and re-save the access management config form - no confirmation
     */
    $('#reset_access_config').on('click', function(){
        $('textarea').each(function(){
           $(this).val('');
        });
        $('.datepicker').each(function(){
            $(this).val('');
        });
        $(':checkbox').prop('checked', false);

        $('button[name=submit]').trigger('click');
    });

    /**
     * return an icon class for a given alert type [alert-info, alert-warning, alert-danger]
     * @param string type
     * @returns string
     */
    function fetchMessageIcon(type)
    {
        switch (type)
        {
            case 'alert-warning' :
                return 'fa-warning';
                break;
            case 'alert-danger' :
                return 'fa-exclamation-circle'
                break;
            case 'alert-info' :
            default:
                return 'fa-info-circle'
                break;
        }
    }

});