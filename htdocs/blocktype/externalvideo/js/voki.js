function Old_AC_Voki_Embed(){
    AC_Voki_Embed.apply(this, arguments);
}

jQuery(function($) {
    var xWidth = 0;
    var xHeight = 0;
    var htmldiv;
    var checkExist = setInterval(function() {
       if ($('#initvoki').parent().find('div').length == 1) {
           // When first div created but before others exist
           htmldiv = $('#initvoki').parent().find('div');
           xWidth = htmldiv.width();
       }
       if ($('#initvoki').parent().find('div').length > 1) {
           if (htmldiv.width() > xWidth) {
               // Try and resize it to fit the block
               xHeight = htmldiv.height() * (xWidth / htmldiv.width());
               htmldiv.css('width', xWidth + 'px').css('height', xHeight + 'px');
               htmldiv.find('div.main_container').css('width', xWidth + 'px').css('height', xHeight + 'px');
               htmldiv.find('div.processing-outer').css('width', xWidth + 'px').css('height', xHeight + 'px');
               htmldiv.find('canvas').width(xWidth); // only need to do width as the ratio scales automatically
           }
           clearInterval(checkExist);
       }
    }, 1000); // check every 1000ms to allow for some of the voki js to execute

});
