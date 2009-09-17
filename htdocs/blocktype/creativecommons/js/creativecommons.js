function toggle_seal() {
    freeculture = true;
    sealimage = $("freecultureseal");

    nc_checkboxes = getElementsByTagAndClassName("input", null, $("instconf_noncommercial_container"));
    if (!nc_checkboxes[0].checked) {
        freeculture = false;
    }

    nd_checkboxes = getElementsByTagAndClassName("input", null, $("instconf_noderivatives_container"));
    if (nd_checkboxes[2].checked) {
        freeculture = false;
    }

    if (freeculture) {
        removeElementClass(sealimage, "hidden");
    }
    else {
        addElementClass(sealimage, "hidden");
    }
}
