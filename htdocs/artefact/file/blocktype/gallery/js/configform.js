$j('#instconf_select_container input[type="radio"][value="0"]').click(function() {
    $j('#instconf_images_header').addClass('hidden');
    $j('#instconf_images_container').addClass('hidden');
    $j('#instconf_folder_header').removeClass('hidden');
    $j('#instconf_folder_container').removeClass('hidden');
});
$j('#instconf_select_container input[type="radio"][value="1"]').click(function() {
    $j('#instconf_folder_header').addClass('hidden');
    $j('#instconf_folder_container').addClass('hidden');
    $j('#instconf_images_header').removeClass('hidden');
    $j('#instconf_images_container').removeClass('hidden');
});
$j('#instconf_style_container input[type="radio"][value="0"]').click(function () {
    $j('#instconf_width').val('75');
});
$j('#instconf_style_container input[type="radio"][value="1"]').click(function () {
    $j('#instconf_width').val('400');
});
