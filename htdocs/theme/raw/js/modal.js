function modal_handler(e) {

    var modal = $('#' + e.target.attributes.getNamedItem('data-bs-target').value);
    modal.addClass('active').removeClass('closed');

    $(modal).find('.deletebutton').on('click', function(e) {
        modal.addClass('closed').removeClass('active');
        dock.hide();
    });

    $(modal).find('.feedbacktable .list-group-lite').addClass('fullwidth');
}

jQuery(window).on('blocksloaded', {}, function() {
"use strict";

    $('[data-bs-target="#configureblock"]').each(function(i, obj) {
        $(obj).attr('data-bs-target', 'modal_' + $(obj).attr('data-artefactid'));
    });

    var deletebutton = $('#configureblock').find('.deletebutton');

    $('.commentlink').on('click', function(e){
        modal_handler(e);
    });

    $('.modal_link').on('click', function(e){
        modal_handler(e);
    });

});
