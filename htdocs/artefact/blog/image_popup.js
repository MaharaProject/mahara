function insertImage() {
	var src = document.forms[0].src.value;
	var alt = document.forms[0].alt.value;
	var border = document.forms[0].border.value;
	var vspace = document.forms[0].vspace.value;
	var hspace = document.forms[0].hspace.value;
	var width = document.forms[0].width.value;
	var height = document.forms[0].height.value;
	var align = document.forms[0].align.options[document.forms[0].align.selectedIndex].value;

	tinyMCEPopup.restoreSelection();
	tinyMCE.themes['advanced']._insertImage(src, alt, border, hspace, vspace, width, height, align);
	tinyMCEPopup.close();
}

function imageSelectorHTML(src) {
    var imageid = tinyMCEPopup.windowOpener.imageIdFromSrc(src);
    var imagefiles = tinyMCEPopup.windowOpener.imageList;
    if (imagefiles.length == 0) {
        return '';
    }
    else {
        var sel = '<select class="select" name="image_list" id="image_list" onchange="this.form.src.value=this.options[this.selectedIndex].value;resetImageData();getImageData(this.form.src.value);">';
        sel += '<option value="">--</option>';
        for (var i = 0; i < imagefiles.length; i++) {
            sel += '<option value="' + imagefiles[i].id + '"';
            if (imageid == imagefiles[i].id) {
                sel += ' selected';
            }
            sel += '>' + imagefiles[i].name + '</option>';
        }
        return sel;
    }
}

function init() {
	tinyMCEPopup.resizeToInnerSize();

	var formObj = document.forms[0];

	for (var i=0; i<document.forms[0].align.options.length; i++) {
		if (document.forms[0].align.options[i].value == tinyMCE.getWindowArg('align'))
			document.forms[0].align.options.selectedIndex = i;
	}

	formObj.src.value = tinyMCE.getWindowArg('src');
	formObj.alt.value = tinyMCE.getWindowArg('alt');
	formObj.border.value = tinyMCE.getWindowArg('border');
	formObj.vspace.value = tinyMCE.getWindowArg('vspace');
	formObj.hspace.value = tinyMCE.getWindowArg('hspace');
	formObj.width.value = tinyMCE.getWindowArg('width');
	formObj.height.value = tinyMCE.getWindowArg('height');
	formObj.insert.value = tinyMCE.getLang('lang_' + tinyMCE.getWindowArg('action'), 'Insert', true); 

	// Handle file browser
	if (isVisible('srcbrowser'))
		document.getElementById('src').style.width = '180px';

        // Get image list from calling window
        document.getElementById('image_list_container').innerHTML = imageSelectorHTML(formObj.src.value);

}

var preloadImg = new Image();

function resetImageData() {
	var formObj = document.forms[0];
	formObj.width.value = formObj.height.value = "";	
}

function updateImageData() {
	var formObj = document.forms[0];

	if (formObj.width.value == "")
		formObj.width.value = preloadImg.width;

	if (formObj.height.value == "")
		formObj.height.value = preloadImg.height;
}

function getImageData(imageid) {
    preloadImg = new Image();
    tinyMCE.addEvent(preloadImg, "load", updateImageData);
    tinyMCE.addEvent(preloadImg, "error", function () {var formObj = document.forms[0];formObj.width.value = formObj.height.value = "";});
    var imgsrc = tinyMCEPopup.windowOpener.imageSrcFromId(imageid);
    var f = document.forms[0];
    f.src.value = imgsrc;
    if (f.image_list.options[f.image_list.selectedIndex].childNodes[0].nodeValue &&
        typeof(f.image_list.options[f.image_list.selectedIndex].childNodes[0].nodeValue) == 'string') {
        f.alt.value = f.image_list.options[f.image_list.selectedIndex].childNodes[0].nodeValue;
    }
    f.imgid.value = imageid;
    preloadImg.src = imgsrc;
}

