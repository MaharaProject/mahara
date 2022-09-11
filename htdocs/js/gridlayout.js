/**
 * Javascript for the views interface
 *
 * @package    mahara
 * @subpackage core
 * @author     Catalyst IT Limited <mahara@catalyst.net.nz>
 * @license    https://www.gnu.org/licenses/gpl-3.0.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 * @copyright  (C) 2013 Mike Kelly UAL m.f.kelly@arts.ac.uk
 *
 */

const gridoptions = {
    margin: 1,
    cellHeight: 10,
    disableDrag: true,
    disableResize: true,
};

function gridOnAdded(el) {
    el.forEach(function(item) {
        // Now that gridstack is not connected with jQuery the
        // the inline javascript doesn't get executed as page is already loaded in jQuery land
        // so we need to find the inline js and enable it to run at this point
        // - similar code to what we use in js/views.js
        if (item.el.id) {
            const html = $('#' + item.el.id).prop('innerHTML');
            const temp = $('<div>').append(html);
            let inlinejs = '';
            temp.find('*').each(function() {
                if ($(this).prop('nodeName') === 'SCRIPT' && $(this).prop('src') === '') {
                    inlinejs += $(this).prop('innerHTML');
                }
            });
            if (inlinejs) {
                eval(inlinejs);
            }
        }
    });
}

function loadGridTranslate(grid, blocks) {
    window.isGridstackRendering = true;
    gridRemoveEvents();
    grid.on('added', function(event, el) {
        gridOnAdded(el);
    });

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
        var blockContent = '<div id="block_' + block.id + '"><div class="grid-stack-item-content">'
            + block.content +
            '</div></div>';

        let options = {
            x: block.positionx,
            y: block.positiony,
            w: block.width,
            h: block.height,
            autoPosition: null,
            minW: null,
            maxW: null,
            minH: null,
            maxH: null,
            id: block.id
        }
        let el = grid.addWidget(
              blockContent,
              options
        );

        $(el).addClass(block.class);

        grid.on('resizestart', function(event, el) {
            grid.update(el, {minH: null});
        });

        grid.on('resizestop', function(event, el) {
            resizeStopBlock(event, el);
        });

    });

    jQuery(document).trigger('blocksloaded');

    window.setTimeout(function(){
        updateBlockSizes();
        updateTranslatedGridRows(blocks);
        gridInit();
        initJs();
        window.isGridstackRendering = false;
    }, 300);
}

function loadGrid(grid, blocks) {
    var minWidth = grid.opts.minCellColumns;
    var minHeight, content, draftclass, srelement;
    window.isGridstackRendering = true;

    grid.on('added', function(event, el) {
        gridOnAdded(el);
    });

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
            srelement = '<span class="visually-hidden">' + get_string('draft') + '</span>';
        }
        var blockContent = '<div id="block_' + block.id + '" class="grid-stack-item" >'
            + srelement +
            '<div class="grid-stack-item-content ' + draftclass + '">'
            + block.content +
            '</div></div>';
        addNewWidget(blockContent, block.id, block, grid, block.class, minWidth, minHeight);
    });

    jQuery(document).trigger('blocksloaded');

    initJs();

    // images need time to load before height can be properly calculated
    window.setTimeout(function(){
        // no need to update the blocksizes for hidden timeline views
        const id = $(grid.el).attr('id');
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
        var gridElement = $(e.target).closest('.grid-stack');
        var id = gridElement.attr('id');
        //check we are not in timeline view
        if (typeof id === 'undefined') {
            updateBlockSizes();
        }
        else {
            // on timeline view
            GridStack.init(gridoptions, gridElement[0]);
            updateBlockSizes(gridElement);
        }
    });

    $(window).on('hidden.bs.collapse shown.bs.collapse', function(e) {
        var gridElement = $(e.target).closest('.grid-stack');
        var id = gridElement.attr('id');
        // ignore if we are not inside a grid
        if (gridElement.length > 0) {
            // check we are not in timeline view
            if (typeof id === 'undefined') {
                updateBlockSizes();
            }
            else {
                // on timeline view
                GridStack.init(gridoptions, gridElement[0]);
                updateBlockSizes(gridElement);
            }
        }
    });

    $(window).on('timelineviewresizeblocks', function() {
        let el = document.querySelector('.lineli.selected .container-fluid .grid-stack');
        GridStack.init(gridoptions, el);
        var gridElement = $('#' + el.id);
        updateBlockSizes(gridElement);
    });

    $(window).resize(function() {
        var gridElement = $('.grid-stack');
        updateBlockSizes(gridElement);
    });

}

function updateTranslatedGridRows(blocks) {
      var height = [], maxheight = [], realheight, updatedGrid = [];
      height[0] = [];
      height[0][0] = 0;
      maxheight[0] = 0;

      var grid = document.querySelector('.grid-stack').gridstack;
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

          realheight = parseInt($(el).attr('gs-h'));
          grid.update(el);

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
      moveBlocks(updatedGrid, grid);
}

function updateBlockSizes(gridElement) {
    if (typeof gridElement == 'undefined') {
        gridElement = $('.grid-stack');
    }
    var grid = gridElement[0].gridstack;
    $.each(gridElement.children(), function(index, element) {
        var width = $(element).attr('gs-w'),
        prevHeight = $(element).attr('gs-h'),
        height = 1;
        if ($(element).find('.grid-stack-item-content .gridstackblock').length > 0) {
            height = Math.ceil(($(element).find('.grid-stack-item-content .gridstackblock')[0].scrollHeight + grid.opts.margin) /
                                grid.getCellHeight() + grid.opts.margin);
        }
        if (+prevHeight != height) {
            grid.update(element, {w: width, h: height});
        }
    });
}

function addNewWidget(blockContent, blockId, dimensions, grid, blocktypeclass, minWidth, minHeight) {
    let options = {
        x: dimensions.positionx,
        y: dimensions.positiony,
        w: dimensions.width,
        h: dimensions.height,
        autoPosition: null,
        minW: minWidth,
        maxW: null,
        minH: minHeight,
        maxH: null,
        id: blockId
    }

    let el = grid.addWidget(
        blockContent,
        options
    );

    $(el).addClass(blocktypeclass);

    grid.on('resizestart', function(event, el) {
        grid.update(el, {minH: null});
    });

    grid.on('resizestop', function(event, el) {
        resizeStopBlock(event, el);
    });

    grid.on('dragstop', function(event, el) {
        moveBlockEnd(event, el);
    });

    // images need time to load before height can be properly calculated
    window.setTimeout(function(){
        // no need to update sizes for timeline views that are hidden
        const id = $(grid.el).attr('id');
        if (typeof id == 'undefined') {
          updateBlockSizes();
        }
    }, 300);
}

function moveBlockEnd(event, data) {
    let grid = document.querySelector('.grid-stack').gridstack;
    serializeWidgetMap(grid);
}

function resizeStopBlock(event, data) {
    let grid = document.querySelector('.grid-stack').gridstack;
    let widthgrid = $(data).attr('gs-w');
    let heightgrid = $(data).attr('gs-h');
    grid.update(data, {w: widthgrid, h: heightgrid, minH: heightgrid});

    // update dimensions in db
    var id = $(data).attr('gs-id'),
    dimensions = {
      newx: $(data).attr('gs-x'),
      newy: $(data).attr('gs-y'),
      newwidth: widthgrid,
      newheight: heightgrid,
    }
    moveBlock(id, dimensions, grid);
    serializeWidgetMap(grid);
}

function moveBlock(id, whereTo, grid) {
    let isOneColumn = false;
    if (grid._widthOrContainer() <= grid.opts.minWidth) {
        isOneColumn = true;
    }
    var pd = {
        'id': $('#viewid').val(),
        'change': 1,
        'gridonecolumn': isOneColumn
    };
    pd['action_moveblockinstance_id_' + id + '_newx_' + whereTo['newx'] + '_newy_' + whereTo['newy'] + '_newheight_' + whereTo['newheight'] + '_newwidth_' + whereTo['newwidth']] = true;

    sendjsonrequest(config['wwwroot'] + 'view/blocks.json.php', pd, 'POST');
}

function moveBlocks(gridblocks, grid) {
    let isOneColumn = false;
    if (grid._widthOrContainer() <= grid.opts.minWidth) {
        isOneColumn = true;
    }
    var pd = {
        'id': $('#viewid').val(),
        'blocks': JSON.stringify(gridblocks),
        'gridonecolumn': isOneColumn
    };

    sendjsonrequest(config['wwwroot'] + 'view/grid.json.php', pd, 'POST');
}

var serializeWidgetMap = function(grid) {
    // get the block id
    // json call to update new position and/or dimension
    var i;
    let items = grid.engine.nodes;
    if (typeof(items) != 'undefined') {
        for (i=0; i<items.length; i++) {
            if (typeof(items[i].id) != 'undefined') {

                var blockid = items[i].id,
                    destination = {
                        'newx': items[i].x,
                        'newy': items[i].y,
                        'newheight': items[i].h,
                        'newwidth': items[i].w,
                    }
                moveBlock(blockid, destination, grid);
            }
        }
    }
};

function gridInit() {
    var grid = document.querySelector('.grid-stack').gridstack;
    grid.on('change', function(event, items) {
        event.stopPropagation();
        event.preventDefault();
        serializeWidgetMap(grid);
    });

}

function gridRemoveEvents() {
    $('.grid-stack').off('change');
    $(window).off('colresize');
}
