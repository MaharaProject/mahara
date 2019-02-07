<?php
/**
 *
 * @package    mahara
 * @subpackage admin
 * @author     Hugh Davenport <hugh@catalyst.net.nz>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 *
 */

define('INTERNAL', 1);
define('INSTITUTIONALSTAFF', 1);
define('MENUITEM', 'reports');

require(dirname(dirname(dirname(__FILE__))).'/init.php');
require(get_config('libroot') . 'statistics.php');
require_once('institution.php');

if (!is_logged_in()) {
    throw new AccessDeniedException();
}

$institution = param_alphanum('institution', null);
$notallowed = false;

$allstaffstats = get_config('staffstats');
$userstaffstats = get_config('staffreports'); // The old 'Users/access list/masquerading' reports from users section

if (!$USER->get('admin') && !$USER->is_institutional_admin($institution) &&
    (!$USER->is_institutional_staff($institution) ||
     ($USER->is_institutional_staff($institution) && empty($allstaffstats) && empty($userstaffstats)))) {
    $notallowed = true;
}

if (!$notallowed) {
    // Get the institution selector to work out what institutions they are allowed to see
    $institutionelement = get_institution_selector(true, false, true, ($allstaffstats || $userstaffstats), ($USER->get('admin') || $USER->get('staff')));
}

if (empty($institutionelement) || $notallowed) {
    $smarty = smarty();
    $smarty->assign('CANCREATEINST', $USER->get('admin'));
    $smarty->display('admin/users/noinstitutionsstats.tpl');
    exit;
}

if (!$institution || !$USER->can_edit_institution($institution, true)) {
    $institution = empty($institutionelement['value']) ? $institutionelement['defaultvalue'] : $institutionelement['value'];
}
else if (!empty($institution)) {
    $institutionelement['defaultvalue'] = $institution;
}

if ($usersparam = param_variable('users', null)) {
    $newuserids = is_array($usersparam) ? array_map('intval', $usersparam) : null;
    $SESSION->set('usersforstats', $newuserids);
}

define('PAGEHEADINGARROW', get_string('reports', 'statistics'));
$type = param_alpha('type', 'information');
$subtype = param_alpha('subtype', '');

if (isset($institution)) {
    if (!$USER->get('admin') && !$USER->is_institutional_admin($institution) &&
        $USER->is_institutional_staff($institution) && empty(get_config('staffstats')) && !empty(get_config('staffreports'))) {
        // we need to give them the correct default report
        $subtype = !empty($subtype) ? $subtype : 'information';
    }
}

// Work out the title for the report
$reporttype = get_string('peoplereports', 'statistics');
if ($subtype && $subtype !== $type) {
    if (string_exists($type . $subtype . 'reports', 'statistics')) {
        $reporttype = get_string($type . $subtype . 'reports', 'statistics');
    }
}
else {
    if (string_exists($type . 'reports', 'statistics')) {
        $reporttype = get_string($type . 'reports', 'statistics');
    }
}

define('SUBSECTIONHEADING', $reporttype);
$subtype = !empty($subtype) ? $subtype : $type;

$showall = ($institution == 'all') ? true : false;
if ($showall) {
    define('TITLE', get_string('Allinstitutions', 'mahara'));
}
else {
    define('TITLE', get_field('institution', 'displayname', 'name', $institution));
}

$start = param_variable('start', null);
$end = param_variable('end', null);
$start = $start ? format_date(strtotime($start), 'strftimew3cdate') : null;
$end = $end ? format_date(strtotime($end), 'strftimew3cdate') : null;

$activecolumns = $SESSION->get('columnsforstats');
$activecolumns = !empty($activecolumns) ? $activecolumns : array();

$extraparams = new stdClass();
$extraparams->type = $type;
$extraparams->subtype = $subtype;
$extraparams->institution = $institution;
$extraparams->offset = param_integer('offset', 0);
$extraparams->limit  = param_integer('limit', 10);
$extraparams->extra = array('sort' => param_alphanumext('sort', ''),
                            'sortdesc' => param_boolean('sortdesc'),
                            'start' => $start,
                            'end' => $end,
                            'columns' => $activecolumns,
                      );

$jsondatestart = !empty($start) ? "'" . $start ."'" : 'null';
$jsondateend = !empty($end) ? "'" . $end . "'" : 'null';
$extrajson = json_encode($extraparams->extra);
$wwwroot = get_config('wwwroot');

// Need to handle the pieform submission for the 'configure report' here
// This also populates the needed 'calendar' element headdata
// TODO - look to see if we need the ajax fetching of the config form or can we prepopulate the page with it?
$pieform = pieform_instance(report_config_form($extraparams, $institutionelement));


$js = <<<JS

var opts = {'id':'statistics_table_container',
            'type':'{$type}',
            'subtype':'{$subtype}',
            'extradata':'{$extrajson}',
            'institution':'{$institution}',
            'offset':{$extraparams->offset},
            'limit':{$extraparams->limit},
            'start':{$jsondatestart},
            'end':{$jsondateend},
           };

function show_stats_config() {
    sendjsonrequest(config['wwwroot'] + 'admin/users/statsconfig.json.php', opts, 'POST', function(data) {
        $('#modal-configs .modal-body').empty();
        $('#modal-configs .modal-body').append(data.data.html);
        $("#cancel_reportconfigform_submit").on('click', function(e) {
            e.preventDefault();
            $("#modal-configs").modal("hide");
        });
        // The institution selector can be a hidden field if only 1 choice
        // So we need to make sure the field is a select field and not a hidden one
        var instselect = $('#reportconfigform_institution');
        if (instselect.is('select')) {
            instselect.select2({
                dropdownParent: $("#modal-configs"),
                width: '100%'
            });
        }
    });
}

function update_table_headers(data) {
    var headers = (data) ? data.tableheadings : null;
    var activeheaders = (data) ? data.data.activeheadings : null;
    var limit = (data) ? data.extraparams.limit : null;
    if (headers) {
        var newhtml = '';
        $.each(headers, function(i, heading) {
            if (heading.selected) {
                newhtml += heading.html;
            }
        });

        $('#statistics_table thead tr').html(newhtml);
    }
    $('#statistics_table thead tr').find('a.col_head_link').each(function (i, a) {
        $(a).off('click');
        $(a).on('click', function(e) {
            e.preventDefault();
            var loc = a.href.indexOf('?');
            var queryData = [];
            var extraData = {};
            if (loc != -1) {
                queryData = parseQueryString(a.href.substring(loc + 1, a.href.length));
                queryData.limit = limit;
                queryData.offset = 0;
                // move the ones we need in extradata to there
                extraData.sort = queryData.sort;
                extraData.sortdesc = queryData.sortdesc || false;
                extraData.columns = [];
                if (activeheaders) {
                    for (x in activeheaders) {
                        extraData.columns.push(x);
                    }
                }
                queryData.extradata = JSON.stringify(extraData);
            }
            p.sendQuery(queryData, true);
        });
    });
}

$(document).on('pageupdated', function(e, data) {
    // Update the table header links
    $('#statistics_table thead tr').find('a').off('click');
    update_table_headers(data);
});

jQuery(function ($) {
    // JS Code to deal with the report configuration modal
    // This fetches the form for choosing the results with filters, eg time period

    // We need to show/hide modal explicitly so the on 'show.bs.modal' fires allowing
    // us to do ajax call for form as modal opens
    $('#configbtn').on("click", function() {
        $("#modal-configs").modal("show");
    });
    $("#modal-configs").on('show.bs.modal', function () {
        show_stats_config();
    });
    $("#modal-configs .close").on('click', function () {
        $("#modal-configs").modal("hide");
    });

    $('.btn.filter').on('click', function() {
        var filteropt = $(this);
        var filteroptid = filteropt.prop('id');
        sendjsonrequest(config['wwwroot'] + 'json/stats_setting.php', {'setting':filteroptid}, 'POST', function(data) {
            filteropt.parent().hide();
            $('#statistics_table th a:first').trigger('click');
        });
    });

    $('#messages .alert-success').delay(1000).hide("slow");
    update_table_headers(null);
});

JS;

if ($type == 'information' && (empty($subtype) || $subtype == 'information')) {
    if ($institution == 'all') {
        $institutiondata = site_statistics(true);
    }
    else {
        $institutiondata = institution_statistics($institution, true);
    }
    $subpagedata = false;
    $subpages = false;
    $subpagination = '';
}
else {
    list($subpages, $subpagedata) = display_statistics($institution, $type, $extraparams);
    $subpagination = (!empty($subpagedata['table']) && !empty($subpagedata['table']['pagination_js'])) ? $subpagedata['table']['pagination_js'] : false;
    $institutiondata = false;
    if ($subpagination) {
        $js .= <<<JS
jQuery(function ($) {
    // JS Code to deal with the download CSV button
    // We want the CSV to return all results for time period rather than the current paginated page
    // So we want to do this asynchronistically
    $('#csvdownload').on('click', function(e) {
        e.preventDefault();
        var obj = JSON.parse(opts.extradata);
        obj['csvdownload'] = true;
        opts.extradata = JSON.stringify(obj);
        sendjsonrequest(config.wwwroot + 'admin/users/statistics.json.php', opts, 'POST', function (data) {
            window.location = config.wwwroot + 'download.php';
        });
    });

    p = {$subpagination}
    p.extraData = $extrajson;
});
JS;
    }
}

$smarty = smarty(array('paginator','js/chartjs/dist/Chart.min.js'));
setpageicon($smarty, 'icon-pie-chart');
$smarty->assign('INLINEJAVASCRIPT', $js);
$smarty->assign('institutiondata', $institutiondata);
$smarty->assign('type', $type);
$smarty->assign('subpages', $subpages);
$smarty->assign('showall', ($showall ? '_all' : ''));
$smarty->assign('subpagedata', $subpagedata);
if (isset($subpagedata['table']) && isset($subpagedata['table']['settings'])) {
    $smarty->assign('reportsettings', get_report_settings($subpagedata['table']['settings']));
}
$smarty->display('admin/users/statistics.tpl');
