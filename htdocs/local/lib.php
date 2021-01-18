<?php

function local_expected_account_preferences() {
    return array(
        'apcstatusactive' => false,
        'apcstatusdate' => null,
        'apcstatusdateend' => null,
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
        $userobj = new User();
        $userobj->find_by_id($viewinstance->get('owner'));
        $view = $userobj->get_profile_view();

        // Hide author if profile isn't visible to account holder
        if (!$view || !can_view_view($view)) {
            return null;
        }
        $apcinfo = get_account_preference($viewinstance->get('owner'), 'apcstatusactive') ? get_string('apcperiod', 'view', date('j F Y', strtotime(get_account_preference($viewinstance->get('owner'), 'apcstatusdate')))) : '';
    }

    $ownername = hsc($viewinstance->formatted_owner());
    $ownerlink = hsc($viewinstance->owner_link());

    return get_string('viewauthor', 'view', $ownerlink, $ownername) . '<div></div>' . $apcinfo;
}
