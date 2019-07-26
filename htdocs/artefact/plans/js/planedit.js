/**
 *
 * @package    mahara
 * @subpackage artefact-plans
 * @author     Alexander Del Ponte
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 *
 */

$(function () {
    $('#addplan_template').change(function() {
        var checked = $(this).prop('checked');
        $('#addplan_selectionplan').prop('checked', checked);
        $('#addplan_selectionplan').prop('disabled', !checked);
    });
});
