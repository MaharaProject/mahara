/**
 * Javascript for the hidden author template
 * @source: http://gitorious.org/mahara/mahara
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 *
 */

addLoadEvent(function () {
    function callbackHandler(i) {
        return function(e) {
            e.stop();
            addElementClass('hidden_author_' + i, 'js-safe-hidden');
            addElementClass('hidden_author_' + i, 'hidden');
            removeElementClass('real_author_' + i, 'js-safe-hidden');
            removeElementClass('real_author_' + i, 'hidden');
        }
    };

    index = 1;
    while ($('show_real_author_' + index)) {
        connect('show_real_author_' + index, 'onclick', callbackHandler(index));
        index += 1;
    }
});
