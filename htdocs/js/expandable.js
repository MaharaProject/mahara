/**
 * Javascript for showing and hiding attachments or other expandable content
 * @source: http://gitorious.org/mahara/mahara
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 *
 */

/*
 * @todo
 * test in safari and opera
 */
function setupExpanders(element) {
    element.find(".expandable-body").hide();
    element.find(".expandable-head a.toggle:not(.expandable)").addClass('expandable').click(function(event) {
        $j(this).toggleClass('expanded');
        body = $j(this).closest('.expandable-head').next('.expandable-body');
        body.toggle();
        if (body.is(':visible')) {
            body.find('a').first().focus();
        }
        return false;
    });
}

function removeExpanders(element) {
    element.find(".expandable-head a.expandable").removeClass('expandable');
}

jQuery(document).ready(function() {
    setupExpanders($j('body'));
});
