/**
 * Support file for the uploadcsv admin page in Mahara
 *
 * @package    mahara
 * @subpackage core
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 */

jQuery(function($) {
    function change_quota(input) {
        var quota = document.getElementById('uploadcsv_quota');
        var quotaUnits = document.getElementById('uploadcsv_quota_units');
        var params = {};
        params.instid = $(input).val();
        if (quotaUnits == null) {
            params.disabled = true;
        }
        sendjsonrequest('quota.json.php', params, 'POST', function(data) {
            if (quotaUnits == null) {
                quota.value = data.data;
            }
            else {
                quota.value = data.data.number;
                quotaUnits.value = data.data.units;
            }
        });
    }

    select = document.getElementById('uploadcsv_authinstance');
    if (select != null) {
        $(select).on('change', change_quota(null, select));
    }
    else {
        select = document.getElementsByName('authinstance')[0];
    }
    if (select != null) {
        change_quota(select);
    }
});
