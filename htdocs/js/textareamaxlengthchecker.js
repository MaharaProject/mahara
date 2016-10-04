/**
 * Check form elements maxlength attribute and display relevant error.
 *
 * @package    mahara
 * @subpackage core
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 *
 */

jQuery(document).ready(function() {
    jQuery(this).find('textarea').each(function(i, textarea) {
        var textarea = jQuery(textarea);
        var maxlength = textarea.attr('maxlength');
        var elementName = textarea.attr('id');
        var isWysiwyg = textarea.hasClass('wysiwyg');

        // Fields used for tinymce wysiwyg will be ignored here.
        // Please have a look at setup part of tinymce initialisation in web.php
        if (isElementDefined(maxlength) && isWysiwyg == false) {
            textarea.removeAttr('maxlength');
            textarea.on('keyup keypress paste', function () {
                checkTextareaMaxLength(elementName, isWysiwyg, maxlength);
            });
        }
    });
});

/*
 * Check if an element is defined.
 */
function isElementDefined(element) {
    if (typeof element !== typeof undefined) {
        return true;
    }
    else {
        return false;
    }
}

/*
 * Check maxlength of the text area and add/remove error message.
 */
function checkTextareaMaxLength(elementName, isWysiwyg, maxlength) {
    var textareaId = '#' + elementName;
    var textarea = jQuery(textareaId);
    var textareaContainerId = '#' + elementName + '_container';
    var textareaContainer = jQuery(textareaContainerId);
    var errorClass = "errmsg";

    // If container is not exist on the page, then use parent element of the textarea.
    if (!textareaContainer.length) {
        var usingParent = true;
        textareaContainer = textarea.parent();
    }

    isWysiwyg = typeof isWysiwyg !== 'undefined' ? isWysiwyg : true;
    maxlength = typeof maxlength !== 'undefined' ? maxlength : textarea.attr('maxlength');

    if (isElementDefined(maxlength)) {

        var triggerLimit = parseInt(maxlength) + 1;
        var errorMessage = get_string('rule.maxlength.maxlength', 'pieforms', maxlength);

        if (usingParent == true) {
            var errorElementsInContainer = textareaContainer.parent().find('.' + errorClass);
            var isElementsHasError = errorElementsInContainer.length;
        }
        else {
            var errorElementsInContainer =  textareaContainer.find('.' + errorClass);
            var isElementsHasError = errorElementsInContainer.length;
        }

        if (isWysiwyg == true) {
            var body = tinymce.get(elementName).getBody();
            var content = tinymce.trim(body.innerText || body.textContent);
            var textLength = content.length;
            var htmlLength = body.innerHTML.length;
        }
        else {
             var textLength = htmlLength = parseInt(textarea.val().length);
        }

        var charactersLeft = triggerLimit - htmlLength;

        // If a user typed more than the limit and there is no error message related
        // to the field, then set the error.
        if (charactersLeft <= 0 && isElementsHasError == false && textLength > 0) {
            textarea.addClass('error');
            textareaContainer.addClass('has-error');

            var errorElement = jQuery('<div></div>').text(errorMessage).attr('class', errorClass);

            if (usingParent == true) {
                textareaContainer.after(function() {
                    return errorElement
                });
            }
            else {
                textareaContainer.append(function() {
                    return errorElement;
                });
            }
        }

        // If a user removed characters and now the number of them is less than limit,
        // then remove the error.
        if (charactersLeft > 0 && isElementsHasError == true || textLength <= 0) {
            errorElementsInContainer.remove();
            textarea.removeClass('error');
            textareaContainer.removeClass('has-error');
        }
    }
}
