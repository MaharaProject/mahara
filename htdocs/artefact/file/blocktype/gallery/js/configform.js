$j('#instconf_select_container input[type="radio"][value="0"]').on("click", function() {
    $j('#instconf_images_header').addClass('d-none');
    $j('#instconf_images_container').addClass('d-none');
    $j('#instconf_external_header').addClass('d-none');
    $j('#instconf_external_container').addClass('d-none');
    $j('#instconf_external').addClass('d-none');
    $j('#externalgalleryhelp').addClass('d-none');
    $j('#instconf_folder_header').removeClass('d-none');
    $j('#instconf_folder_container').removeClass('d-none');
});
$j('#instconf_select_container input[type="radio"][value="1"]').on("click", function() {
    $j('#instconf_folder_header').addClass('d-none');
    $j('#instconf_folder_container').addClass('d-none');
    $j('#instconf_external_header').addClass('d-none');
    $j('#instconf_external_container').addClass('d-none');
    $j('#instconf_external').addClass('d-none');
    $j('#externalgalleryhelp').addClass('d-none');
    $j('#instconf_images_header').removeClass('d-none');
    $j('#instconf_images_container').removeClass('d-none');
});
$j('#instconf_select_container input[type="radio"][value="2"]').on("click", function() {
    $j('#instconf_images_header').addClass('d-none');
    $j('#instconf_images_container').addClass('d-none');
    $j('#instconf_folder_header').addClass('d-none');
    $j('#instconf_folder_container').addClass('d-none');
    $j('#instconf_external_header').removeClass('d-none');
    $j('#instconf_external_container').removeClass('d-none');
    $j('#instconf_external').removeClass('d-none');
    $j('#externalgalleryhelp').removeClass('d-none');
});
$j('#instconf_style_container input[type="radio"][value="0"]').on("click", function () {
    $j('#instconf_width').val('75');
});
$j('#instconf_style_container input[type="radio"][value="1"]').on("click", function () {
    $j('#instconf_width').val('400');
});
$j('#instconf_style_container input[type="radio"][value="2"]').on("click", function () {
    $j('#instconf_width').val('75');
});
