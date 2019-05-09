/**
 * Javascript for the views interface
 *
 * @package    mahara
 * @subpackage core
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 * @copyright  (C) 2013 Mike Kelly UAL m.f.kelly@arts.ac.uk
 *
 */

function loadGrid(grid, blocks) {
    $.each(blocks, function(blockId, block) {
        var blockContent = $('<div id="block_' + blockId + '"><div class="grid-stack-item-content">'
            + block.content +
            '<div/><div/>');
        addNewWidget(blockContent, blockId, block, grid, block.class);
    });

    jQuery(document).trigger('blocksloaded');

    // initialize js function for edit view
    if (typeof editViewInit !== "undefined") {
        editViewInit();
    }
    // initialize js function for display view
    if (typeof viewmenuInit !== "undefined") {
        viewmenuInit();
    }

    // images need time to load before height can be properly calculated
    window.setTimeout(function(){
        updateBlockSizes();
    }, 300);
}

function updateBlockSizes() {
    $.each($('.grid-stack').children(), function(index, element) {
        if (!$(element).hasClass('staticblock')) {
            $('.grid-stack').data('gridstack').resize(
                $('.grid-stack-item')[index],
                $($('.grid-stack-item')[index]).attr('data-gs-width'),
                Math.ceil(
                  (
                    $('.grid-stack-item-content')[index].scrollHeight +
                    $('.grid-stack').data('gridstack').opts.verticalMargin
                  )
                  /
                  (
                    $('.grid-stack').data('gridstack').cellHeight() +
                    $('.grid-stack').data('gridstack').opts.verticalMargin
                  )
                )
            );
        }
    });
}

function addNewWidget(blockContent, blockId, dimensions, grid, blocktypeclass) {
   el = grid.addWidget(
         blockContent,
         dimensions.positionx,
         dimensions.positiony,
         dimensions.width,
         dimensions.height,
         null, null, null, null, null,
         blockId
   );
   $(el).addClass(blocktypeclass);
   el.on('resizestop', function(event, data) {
       var content = $(this).find('.grid-stack-item-content')[0];
       var heightpx = Math.max(data.size.height, content.scrollHeight),
           widthpx = data.size.width,
           heightgrid = Math.ceil((heightpx + grid.opts.verticalMargin) / (grid.cellHeight() + grid.opts.verticalMargin)),
           widthgrid = Math.ceil((widthpx + grid.opts.verticalMargin) / (grid.cellWidth() + grid.opts.verticalMargin)); // horizontalMargin doesn't exist in gridstack yet

       grid.resize($(this), widthgrid, heightgrid);

       // update dimesions in db
       var id = this.attributes['data-gs-id'].value,
          dimensions = {
              newx: this.attributes['data-gs-x'].value,
              newy: this.attributes['data-gs-y'].value,
              newwidth: widthgrid,
              newheight: heightgrid,
          }
       moveBlock(id, dimensions);
   });

   // images need time to load before height can be properly calculated
   window.setTimeout(function(){
       updateBlockSizes();
   }, 300);
   return false;
}


function moveBlock(id, whereTo) {
    var pd = {
        'id': $('#viewid').val(),
        'change': 1
    };
    pd['action_moveblockinstance_id_' + id + '_newx_' + whereTo['newx'] + '_newy_' + whereTo['newy'] + '_newheight_' + whereTo['newheight'] + '_newwidth_' + whereTo['newwidth']] = true;

    sendjsonrequest(config['wwwroot'] + 'view/blocks.json.php', pd, 'POST');
}


$(window).on('colresize', function() {
    updateBlockSizes();
});
