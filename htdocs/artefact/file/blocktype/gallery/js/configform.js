$j('#instconf_select_container input[type="radio"][value="0"]').click(function() {
    $j('#instconf_artefactids_header').hide();
    $j('#instconf_artefactids_container').hide();
});
$j('#instconf_select_container input[type="radio"][value="1"]').click(function() {
    $j('#instconf_artefactids_header').show();
    $j('#instconf_artefactids_container').show();
});
$j('#instconf_style_container input[type="radio"][value="0"]').click(function () {
    $j('#instconf_width').val('75');
});
$j('#instconf_style_container input[type="radio"][value="1"]').click(function () {
    $j('#instconf_width').val('400');
});
if($j('#instconf_select_container input[type="radio"][value="0"]').attr('checked')) {
    $j('#instconf_artefactids_header').hide();
    $j('#instconf_artefactids_container').hide();
}

