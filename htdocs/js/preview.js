/**
 * Helper for showing "preview" boxes, which are just modal dialogs
 * Javascript for the views interface
 *
 * @package    mahara
 * @subpackage core
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 *
 */

function showPreview(size, data) {
    if (size === 'small') {
        jQuery('#page-modal .modal-dialog').removeClass('modal-lg');
    }
    else {
        jQuery('#page-modal .modal-dialog').addClass('modal-lg');
    }

    jQuery('#page-modal .modal-body').html(data.html);
    jQuery('#page-modal').modal('show');

}

jQuery(function($) {
"use strict";

    if ($('.js-page-modal') === undefined) {
        return;
    }

    $('.js-page-modal .modal-body').on('click', function(e) {
        e.preventDefault();
    });

    //set modal height when page modal is shown, reset when hidden
    $('.js-page-modal').on('shown.bs.modal', function() {
        var height = $('.js-page-modal .modal-content').height();
        $('.js-page-modal .modal-content').height(height);
    });

    $('.js-page-modal').on('d-none.bs.modal', function() {
        $('.js-page-modal .modal-content').height('auto');
    });

});
