/**
 * Hooks in a stylesheet that is only loaded when javascript is enabled
 * @source: http://gitorious.org/mahara/mahara
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 *
 */

var styleNode = createDOM('link', {
    'rel' : 'stylesheet',
    'type': 'text/css',
    'href': config['theme']['style/js.css']
});
appendChildNodes(getFirstElementByTagAndClassName('head'), styleNode);
