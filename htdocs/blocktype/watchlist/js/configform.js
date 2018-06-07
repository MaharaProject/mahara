function toggle_watchlist_type_class(value, state) {
    $j('#instconf_settings_header').toggleClass(value, state);
    $j('#instconf_settings_container').toggleClass(value, state);
    $j('#instconf_settings_header fieldset').toggleClass(value, state);
    $j('#instconf_settings_container fieldset').toggleClass(value, state);
}
$j('#instconf_mode_container input[type="radio"][value="watchlist"]').on('click', function() {
    toggle_watchlist_type_class('d-none', true);
});
$j('#instconf_mode_container input[type="radio"][value="follower"]').on('click', function() {
    toggle_watchlist_type_class('d-none', false);
});
$j(function() {
    if ($j('#instconf_mode_container input[type="radio"]:checked').val() == 'watchlist') {
        toggle_watchlist_type_class('d-none', true);
    }
});
