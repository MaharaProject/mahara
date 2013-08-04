$j('#instconf_select_container input[type="radio"][value="0"]').click(function() {
    $j('#instconf_images_header').addClass('hidden');
    $j('#instconf_images_container').addClass('hidden');
    $j('#instconf_external_header').addClass('hidden');
    $j('#instconf_external_container').addClass('hidden');
    $j('#instconf_external').addClass('hidden');
    $j('#externalgalleryhelp').addClass('hidden');
    $j('#instconf_folder_header').removeClass('hidden');
    $j('#instconf_folder_container').removeClass('hidden');
    // Recalculate the width of config block
    var width = getElementDimensions($('instconf_folder_container')).w;
    updateBlockConfigWidth(getFirstParentByTagAndClassName($('instconf_folder_container'), 'div', 'blockinstance'), width);
});
$j('#instconf_select_container input[type="radio"][value="1"]').click(function() {
    $j('#instconf_folder_header').addClass('hidden');
    $j('#instconf_folder_container').addClass('hidden');
    $j('#instconf_external_header').addClass('hidden');
    $j('#instconf_external_container').addClass('hidden');
    $j('#instconf_external').addClass('hidden');
    $j('#externalgalleryhelp').addClass('hidden');
    $j('#instconf_images_header').removeClass('hidden');
    $j('#instconf_images_container').removeClass('hidden');
    // Recalculate the width of config block
    var width = getElementDimensions($('instconf_images_container')).w;
    updateBlockConfigWidth(getFirstParentByTagAndClassName($('instconf_images_container'), 'div', 'blockinstance'), width);
});
$j('#instconf_select_container input[type="radio"][value="2"]').click(function() {
    $j('#instconf_images_header').addClass('hidden');
    $j('#instconf_images_container').addClass('hidden');
    $j('#instconf_folder_header').addClass('hidden');
    $j('#instconf_folder_container').addClass('hidden');
    $j('#instconf_external_header').removeClass('hidden');
    $j('#instconf_external_container').removeClass('hidden');
    $j('#instconf_external').removeClass('hidden');
    $j('#externalgalleryhelp').removeClass('hidden');
    // Recalculate the width of config block
    var width = getElementDimensions($('instconf_external_container')).w;
    updateBlockConfigWidth(getFirstParentByTagAndClassName($('instconf_external_container'), 'div', 'blockinstance'), width);
});
$j('#instconf_style_container input[type="radio"][value="0"]').click(function () {
    $j('#instconf_width').val('75');
});
$j('#instconf_style_container input[type="radio"][value="1"]').click(function () {
    $j('#instconf_width').val('400');
});
$j('#instconf_style_container input[type="radio"][value="2"]').click(function () {
    $j('#instconf_width').val('75');
});
