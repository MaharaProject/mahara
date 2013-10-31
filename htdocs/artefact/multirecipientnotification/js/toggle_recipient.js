/**
 * Multi-Recipient Notification Toggle Recipients
 * @source: http://gitorious.org/mahara/mahara
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 *
 */

function toggleMe(a, b){
    var on = document.getElementById(a);
    var off = document.getElementById(b);
    if (!on || !off) {
        return true;
    }
    on.style.display = "block";
    off.style.display = "none";
    return true;
}
