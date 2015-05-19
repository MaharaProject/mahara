/**
 * Support file for the adduser admin page in Mahara
 *
 * @package    mahara
 * @subpackage core
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 *
 */

var current;

function move_step(i) {
    var selected = getFirstParentByTagAndClassName(i, 'td', 'step');
    if (selected != current) {
        addElementClass(selected, 'current');
        if (current) {
            removeElementClass(current, 'current');
        }
        current = selected;
    }
}

function change_quota(i) {
    var quota = document.getElementById('adduser_quota');
    var quotaUnits = document.getElementById('adduser_quota_units');
    var params = {};
    params.instid = i.value;
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

addLoadEvent(function() {
    var step1_spans = getElementsByTagAndClassName('span', 'requiredmarker', 'step1');
    var step1_inputs = getElementsByTagAndClassName('input', 'required', 'step1');
    var leap2a_input = $('adduser_leap2afile');
    var leap2a_label = $('leap2a_label');

    leap2a_input.disabled = true;
    addElementClass('step1', 'current');

    /**
     * state = true if the user selects the leap2a radio button, else false
     */
    function set_step1_requiredfields(state) {
        if (state) {
            forEach(step1_spans, function(span) {
                setStyle(span, {'visibility': 'hidden'});
            });
            forEach(step1_inputs, function(input) {
                removeElementClass(input, 'required');
            });
        }
        else {
            forEach(step1_spans, function(span) {
                setStyle(span, {'visibility': 'visible'});
            });
            forEach(step1_inputs, function(input) {
                addElementClass(input, 'required');
            });
        }

        $('adduser_firstname').disabled = state;
        $('adduser_lastname').disabled = state;
        $('adduser_email').disabled = state;
        $('adduser_leap2afile').disabled = !state;
    }


    forEach(getElementsByTagAndClassName('input', 'ic', 'adduser'), function(i) {
        connect(i, 'onclick', function(e) {
            set_step1_requiredfields(i.id == 'uploadleap');
        });
        if (i.checked) {
            set_step1_requiredfields(i.id == 'uploadleap');
        }
    });


    current = getFirstElementByTagAndClassName('td', 'step1', 'adduser');
    forEach(getElementsByTagAndClassName('input', null, 'adduser'), function(i) {
        connect(i, 'onfocus', partial(move_step, i));
        connect(i, 'onclick', partial(move_step, i));
    });

    select = document.getElementById('adduser_authinstance');
    if (select != null) {
        connect(select, 'onchange', partial(change_quota, select));
    }
    else {
        select = document.getElementsByName('authinstance')[0];
    }
    change_quota(select);
});

