<?php

function local_expected_account_preferences() {
    return array(
        'apcstatusactive' => false,
        'apcstatusdate' => null,
        'apcstatusdateend' => null,
        'registerstatus' => 0,
    );
}

/**
 * PCNZ Customisation 3.7 (WR 349177) to display apcstatusdate in display author
 * below the display name
 */
function local_display_author($viewinstance) {
    $view = null;
    $apcinfo = '';
    if (!empty($viewinstance->get('owner'))) {
        require_once(get_config('docroot') . 'local/lib/cron.php');
        $userobj = new User();
        $userobj->find_by_id($viewinstance->get('owner'));
        $view = $userobj->get_profile_view();

        // Hide author if profile isn't visible to account holder
        if (!$view || !can_view_view($view)) {
            return null;
        }
        $registerstatus = get_account_preference($viewinstance->get('owner'), 'registerstatus');
        $apcinfo = '';
        if ($registerstatus == PCNZ_REGISTEREDCURRENT) {
            $apcinfo = get_account_preference($viewinstance->get('owner'), 'apcstatusactive') ? get_string('apcperiod', 'view', date('j F Y', strtotime(get_account_preference($viewinstance->get('owner'), 'apcstatusdate')))) : '';
        }
        else if ($registerstatus == PCNZ_REGISTEREDINACTIVE) {
            $apcinfo = get_string('registerinactive', 'view');
        }
        // If this is an old collection show the old apc status / date
        if ($collection = $viewinstance->get_collection()) {
            if ($oldapcinfo = get_field('collection_template', 'registrationstatus', 'collection', $collection->get('id'))) {
                $apcinfo = $oldapcinfo;
            }
        }
    }

    $ownername = hsc($viewinstance->formatted_owner());
    $ownerlink = hsc($viewinstance->owner_link());

    return get_string('viewauthor', 'view', $ownerlink, $ownername) . '<div></div>' . $apcinfo;
}

function local_get_collection_author($collectionid) {
    require_once(get_config('docroot') . 'local/lib/cron.php');
    require_once(get_config('docroot') . 'lib/collection.php');
    $collection = new Collection($collectionid);
    $registerstatus = get_account_preference($collection->get('owner'), 'registerstatus');
    $apcinfo = '';
    if ($registerstatus == PCNZ_REGISTEREDCURRENT) {
        $apcinfo = get_account_preference($collection->get('owner'), 'apcstatusactive') ? get_string('apcperiod', 'view', date('j F Y', strtotime(get_account_preference($collection->get('owner'), 'apcstatusdate')))) : '';
    }
    else if ($registerstatus == PCNZ_REGISTEREDINACTIVE) {
        $apcinfo = get_string('registerinactive', 'view');
    }
    // If this is an old collection show the old apc status / date
    if ($oldapcinfo = get_field('collection_template', 'registrationstatus', 'collection', $collection->get('id'))) {
        $apcinfo = $oldapcinfo;
    }
    return $apcinfo;
}
