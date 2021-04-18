function verification_js_setup() {
    $('#instconf_availableto').select2();
    $('#instconf_resetstatement').select2();
}

$(function() {
    verification_js_setup();
});

$(window).on('maharagetconfigureform', function() {
    verification_js_setup();
});
