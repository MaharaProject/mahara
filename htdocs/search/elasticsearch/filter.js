jQuery(document).ready(function() {

    jQuery(document).on("change", "#universalsearchresult select", function() {
        var selectid = new String(jQuery(this).attr('id'));
        var tmp = selectid.split('-');
        var link = jQuery("#"+selectid+"-url").val() + '&' + tmp[2] + '=' + jQuery("#"+selectid).val();
        window.location.href = link;
    });
});