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
    window.isGridstackRendering = true;
    gridRemoveEvents();
    // load grid with empty blocks
    $.each(blocks, function(index, block) {
        if (block.content == null) {
            block.content = '';
        }
        else {
            if (!$(block.content).children().hasClass('collapse')) {
              minHeight = block.height;
            }
        }
        var blockContent = $('<div id="block_' + block.id + '"><div class="grid-stack-item-content">'
            + block.content +
            '</div></div>');
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

    jQuery(document).trigger('blocksloaded');

    window.setTimeout(function(){
        updateBlockSizes();
        updateTranslatedGridRows(blocks);
        gridInit();
        $.each(gridElements, function(index, el) {
            el.on('resizestart', resizeStartBlock);
            el.on('resizestop', resizeStopBlock);
        });
        initJs();
        window.isGridstackRendering = false;
    }, 300);
}

function loadGrid(grid, blocks) {
    var minWidth = grid.opts.minCellColumns,
        minHeight, content, draftclass, srelement;
    window.isGridstackRendering = true;
    $.each(blocks, function(index, block) {
        minHeight = null;
        draftclass = '';
        srelement ='';
        if (block.content == null) {
            block.content = '';
        }
        else {
            if (!$(block.content).children().hasClass('collapse')) {
              minHeight = block.height;
            }
        }
        if (typeof(block.draft) != 'undefined' && block.draft) {
            draftclass = 'draft';
            srelement = '<span class="sr-only">' + get_string('draft') + '</span>';
        }
        var blockContent = $('<div id="block_' + block.id + '">'
            + srelement +
            '<div class="grid-stack-item-content ' + draftclass + '">'
            + block.content +
            '</div></div>');
        addNewWidget(blockContent, block.id, block, grid, block.class, minWidth, minHeight);
    });

    jQuery(document).trigger('blocksloaded');

    initJs();

    // images need time to load before height can be properly calculated
    window.setTimeout(function(){
        // no need to update the blocksizes for hidden timeline views
        var id = $(grid.container).attr('id');
        if (typeof id === 'undefined') {
            updateBlockSizes();
        }
        window.isGridstackRendering = false;
    }, 300);

}

function initJs() {

    // initialize js function for edit view
    if (typeof editViewInit !== "undefined" && document.location.pathname.includes("view/blocks.php")) {
        editViewInit();
    }
    // initialize js function for display view
    if (typeof viewmenuInit !== "undefined" && document.location.pathname.includes("view/view.php")) {
        viewmenuInit();
    }

    $(window).on('colresize', function(e) {
        var grid = $(e.target).closest('.grid-stack');
        var id = grid.attr('id');
        //check we are not in timeline view
        if (typeof id === 'undefined') {
            updateBlockSizes();
        }
        else {
            // on timeline view
            grid.gridstack();
            updateBlockSizes(grid);
        }
    });

    $(window).on('hidden.bs.collapse shown.bs.collapse', function(e) {
        var grid = $(e.target).closest('.grid-stack');
        var id = grid.attr('id');
        // ignore if we are not inside a grid
        if (grid.length > 0) {
            // check we are not in timeline view
            if (typeof id === 'undefined') {
                updateBlockSizes();
            }
            else {
                // on timeline view
                grid.gridstack();
                updateBlockSizes(grid);
            }
            if (e.type == 'hidden' && $(e.target).hasClass('block')) {
                var block = $(e.target).closest('.grid-stack-item');
                grid.data('gridstack').resize(block, block.attr('data-gs-width'), 6);
            }
            else {
                updateBlockSizes();
            }
        }
    });

    $(window).on('timelineviewresizeblocks', function() {
        var options = {
            verticalMargin: 5,
            cellHeight: 10,
            disableDrag : true,
            disableResize: true,
        };
        var grid = $('.lineli.selected .grid-stack');
        grid = grid.gridstack(options);
        updateBlockSizes(grid);
    });

    $(window).resize(function() {
        var grid = $('.grid-stack');
        grid = grid.gridstack();
        updateBlockSizes(grid);
    });

}

function updateTranslatedGridRows(blocks) {

      var height = [], maxheight = [], realheight, updatedGrid = [];
      height[0] = [];
      height[0][0] = 0;
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
          if (block.row > 0) {
              // get the actual y value based on the max height of previous rows
              for (var i = 0; i < block.row; i++) {
                  if (typeof(maxheight[i]) != 'undefined' && !isNaN(maxheight[i])) {
                      y += maxheight[i];
                  }
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
          // need to filter values that are not numbers
          var allnumbers = height[block.row].filter(function (el) {
              return Number.isInteger(el);
          });
          maxheight[block.row] = Math.max.apply(null, allnumbers);
      });
      // update all blocks together
      moveBlocks(updatedGrid);
}

function updateBlockSizes(grid) {
    if (typeof grid == 'undefined') {
        grid = $('.grid-stack');
    }
    $.each(grid.children(), function(index, element) {
        var width = $(element).attr('data-gs-width'),
        prevHeight = $(element).attr('data-gs-height'),
        height = 1;
        if ($(element).find('.grid-stack-item-content .gridstackblock').length > 0) {
            height = Math.ceil(
              (
                $(element).find('.grid-stack-item-content .gridstackblock')[0].scrollHeight +
                grid.data('gridstack').opts.verticalMargin
              )
              /
              (
                grid.data('gridstack').cellHeight() +
                grid.data('gridstack').opts.verticalMargin
              )
            );
        }
        if (+prevHeight != height) {
            grid.data('gridstack').resize(element, +width, height);
        }
    });
}

function addNewWidget(blockContent, blockId, dimensions, grid, blocktypeclass, minWidth, minHeight) {
   el = grid.addWidget(
         blockContent,
         dimensions.positionx,
         dimensions.positiony,
         dimensions.width,
         dimensions.height,
         null, minWidth, null, minHeight, null,
         blockId
   );

    $(el).addClass(blocktypeclass);
    el.on('resizestart', resizeStartBlock);
    el.on('resizestop', resizeStopBlock);

    // images need time to load before height can be properly calculated
    window.setTimeout(function(){
        // no need to update sizes for timeline views that are hidden
        var id = $(grid.container).attr('id');
        if (typeof id == 'undefined') {
          updateBlockSizes();
        }
    }, 300);

    return el;
}

function resizeStartBlock(event, data) {
    var grid = $('.grid-stack').data('gridstack');
    grid.minHeight($(this), null);
}

function resizeStopBlock(event, data) {
    var grid = $('.grid-stack').data('gridstack');
    var content = $(this).find('.gridstackblock')[0];
    var heightpx = Math.max(data.size.height, content.scrollHeight),
    widthpx = data.size.width,
    heightgrid = Math.round((heightpx + grid.opts.verticalMargin) / (grid.cellHeight() + grid.opts.verticalMargin)),
    widthgrid = Math.round((widthpx + grid.opts.verticalMargin) / grid.cellWidth()); // horizontalMargin doesn't exist in gridstack yet
    grid.resize($(this), widthgrid, heightgrid);
    grid.minHeight($(this), heightgrid);

    // update dimesions in db
    var id = this.attributes['data-gs-id'].value,
    dimensions = {
      newx: this.attributes['data-gs-x'].value,
      newy: this.attributes['data-gs-y'].value,
      newwidth: widthgrid,
      newheight: heightgrid,
    }
    moveBlock(id, dimensions);
    serializeWidgetMap(grid.grid.nodes);
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

}

function gridRemoveEvents() {
    $('.grid-stack').off('change');
    $(window).off('colresize');
}
