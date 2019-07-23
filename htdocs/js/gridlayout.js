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
function loadGridTranslate(grid, blocks) {
    var gridElements = [];
    gridRemoveEvents();
    // load grid with empty blocks
    $.each(blocks, function(index, block) {
        var blockContent = $('<div id="block_' + block.id + '"><div class="grid-stack-item-content">'
            + block.content +
            '<div/><div/>');
        el = grid.addWidget(
              blockContent,
              block.positionx,
              block.positiony,
              block.width,
              block.height,
              null, null, null, null, null,
              block.id
        );
        gridElements.push(el);
    });
    window.setTimeout(function(){
        updateBlockSizes();
        updateTranslatedGridRows(blocks);
        gridInit();
        $.each(gridElements, function(index, el) {
            el.on('resizestop', resizeStopBlock);
        })
    }, 300);
}

function loadGrid(grid, blocks) {

    $.each(blocks, function(index, block) {
        var blockContent = $('<div id="block_' + block.id + '"><div class="grid-stack-item-content">'
            + block.content +
            '<div/><div/>');
        addNewWidget(blockContent, block.id, block, grid, block.class);
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

function updateTranslatedGridRows(blocks) {

      var height = [], maxheight = [], realheight, updatedGrid = [];
      height[0] = 0;
      maxheight[0] = 0;

      $.each(blocks, function(key, block) {
          var el, y;
          if (typeof(height[block.row]) == 'undefined') {
              height[block.row] = [];
              height[block.row][0] = 0;
          }
          if (typeof(height[block.row][block.column]) == 'undefined') {
              height[block.row][block.column] = 0;
          }

          y = 0;
          if (block.row > 1) {
              // get the actual y value based on the max height of previus rows
              for (var i = 1; i < block.row; i++) {
                  y += maxheight[i];
              }
          }
          if (typeof(height[block.row][block.column]) != 'undefined') {
              y += height[block.row][block.column];
          }
          block.positiony = y;

          el = $('#block_' + block.id);

          realheight = parseInt($(el).attr('data-gs-height'));

          $('.grid-stack').data('gridstack').move(
              el,
              block.positionx,
              block.positiony
          );

          var updatedBlock = {};
          updatedBlock.id = block.id;
          updatedBlock.dimensions =  {
              newx: +block.positionx,
              newy: +block.positiony,
              newwidth: +block.width,
              newheight: +realheight,
          };
          updatedGrid.push(updatedBlock);

          if (height[block.row][block.column] == 0) {
              height[block.row][block.column] = realheight;
          }
          else {
              height[block.row][block.column] += realheight;
          }
          maxheight[block.row] = Math.max(...height[block.row]);
      });
      // update all blocks together
      moveBlocks(updatedGrid);
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
    el.on('resizestop', resizeStopBlock);

    // images need time to load before height can be properly calculated
    window.setTimeout(function(){
        updateBlockSizes();
    }, 300);

    return el;
}

function resizeStopBlock(event, data) {
  var grid = $('.grid-stack').data('gridstack');
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
}

function moveBlock(id, whereTo) {
    var pd = {
        'id': $('#viewid').val(),
        'change': 1
    };
    pd['action_moveblockinstance_id_' + id + '_newx_' + whereTo['newx'] + '_newy_' + whereTo['newy'] + '_newheight_' + whereTo['newheight'] + '_newwidth_' + whereTo['newwidth']] = true;

    sendjsonrequest(config['wwwroot'] + 'view/blocks.json.php', pd, 'POST');
}

function moveBlocks(grid) {
    var pd = {
        'id': $('#viewid').val(),
        'blocks': JSON.stringify(grid),
    };

    sendjsonrequest(config['wwwroot'] + 'view/grid.json.php', pd, 'POST');
}

var serializeWidgetMap = function(items) {
    // get the block id
    // json call to update new position and/or dimension
    var i;
    if (typeof(items) != 'undefined') {
        for (i=0; i<items.length; i++) {
            if (typeof(items[i].id) != 'undefined') {

                var blockid = items[i].id,
                    destination = {
                        'newx': items[i].x,
                        'newy': items[i].y,
                        'newheight': items[i].height,
                        'newwidth': items[i].width,
                    }
                moveBlock(blockid, destination);
            }
        }
    }
};

function gridInit() {
    $('.grid-stack').on('change', function(event, items) {
        event.stopPropagation();
        event.preventDefault();
        serializeWidgetMap(items);
    });

    $(window).on('colresize', function() {
        updateBlockSizes();
    });

}

function gridRemoveEvents() {
    $('.grid-stack').off('change');
    $(window).off('colresize');
}
