$j('#instconf_socialprofile_container input[type="radio"][value="0"]').on("click", function() {
    $j('#instconf_socialprofileids_header').addClass('d-none');
    $j('#instconf_socialprofileids_container').addClass('d-none');
});
$j('#instconf_socialprofile_container input[type="radio"][value="1"]').on("click", function() {
    $j('#instconf_socialprofileids_header').removeClass('d-none');
    $j('#instconf_socialprofileids_container').removeClass('d-none');
});
