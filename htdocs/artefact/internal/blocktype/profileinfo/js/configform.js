$j('#instconf_socialprofile_container input[type="radio"][value="0"]').on("click", function() {
    $j('#instconf_socialprofileids_header').addClass('hidden');
    $j('#instconf_socialprofileids_container').addClass('hidden');
});
$j('#instconf_socialprofile_container input[type="radio"][value="1"]').on("click", function() {
    $j('#instconf_socialprofileids_header').removeClass('hidden');
    $j('#instconf_socialprofileids_container').removeClass('hidden');
});
