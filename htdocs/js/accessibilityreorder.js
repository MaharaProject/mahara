/*
 * Creates the new dragon drop object and sets the events to move the blocks
 * up and down the grid, and to include messages for the screen reader
 */
function accessibilityReorder() {
    var list = $('.grid-stack')[0];
    // creating dragon drop object and setting the screen reader event handlers
    window.dragonDrop = new DragonDrop(list, {
        item: '.grid-stack-item',
        handle: '.access-drop-handle',
        announcement: {
            grabbed: function(el) {
                var title = $(el).find('h2 .blockinstance-header')[0].innerText;
                return get_string('itemgrabbed', 'view', title);
            },
            dropped: function(el) {
                var title = $(el).find('h2 .blockinstance-header')[0].innerText;
                return get_string('itemdropped', 'view', title);
            },
            reorder: function(el, items) {
                const pos = items.indexOf(el) + 1;
                var title = $(el).find('h2 .blockinstance-header')[0].innerText;
                return get_string('itemreorder', 'view', title, pos, items.length);
            },
            cancel: get_string('reordercancelled', 'view'),
        }
    });

    // setting event handlers to update gridstack values and save them on the db
    window.dragonDrop.on('grabbed', function (container, item) {
        var title = $(item).find('h2 .blockinstance-header')[0].innerText;
        console.log(get_string('itemgrabbed', 'view', title));
    })
    .on('dropped', function (container, item) {
        var title = $(item).find('h2 .blockinstance-header')[0].innerText;
        console.log(get_string('itemdropped', 'view', title));
    })
    .on('reorder', function (container, item) {
        // dragon drop will swap the nodes in the DOM,
        // but we still need to update the gridstack values
        var newpos = this.items.indexOf(item);
        var prevEl, nextEl, prevY, nextY, itemY, prevHeight;

        prevEl = this.items[newpos - 1];
        nextEl = this.items[newpos +1];
        itemY = item.getAttribute('gs-y');
        if (typeof(prevEl) != 'undefined' || typeof(nextEl) !== 'undefined') {
            // we have at least one more element in the list
            if (typeof(prevEl) === 'undefined') {
                // moving element up the layout, to the first position
                nextY = nextEl.getAttribute('gs-y');
                if (+itemY > +nextY) {
                    swapBlocks(item, nextEl, nextY);
                }
            }
            else if (typeof(nextEl) === 'undefined') {
                // moving the element down in the layout, to the last position
                swapBlocks(prevEl, item, itemY);
            }
            else {
                prevY = prevEl.getAttribute('gs-y');
                if (+prevY > +itemY) {
                    // moving the element down in the layout
                    swapBlocks(prevEl, item, itemY);
                }
                else {
                    // moving the element up in the layout
                    nextY = nextEl.getAttribute('gs-y');
                    if (+itemY > +nextY) {
                        swapBlocks(item, nextEl, nextY);
                    }
                }
            }
        }
    });
}

/*
 * Updates the gridstack dimensions in the DOM elements and saves the new dimensions to the DB
 *
 * Note: the use of unary plus operator in this function see
 * https://developer.mozilla.org/en-US/docs/Web/JavaScript/Reference/Operators/Unary_plus
 * for more information.
 *
 * @param topBlock node that will be on top of the other block after the swap
 * @param bottomBlock node that will be below the other block after the swap
 * @param topBlockNewY int is the new gridstack value y for the block that will be on the top
 */
function swapBlocks(topBlock, bottomBlock, topBlockNewY) {
    const topHeight = topBlock.getAttribute('gs-h');
    const bottomY = +topBlockNewY + +topHeight;
    let grid = document.querySelector('.grid-stack').gridstack;
    grid.update(topBlock, {x: +topBlock.getAttribute('gs-x'), y: +topBlockNewY});
    grid.update(bottomBlock, {x: +bottomBlock.getAttribute('gs-x'), y: bottomY});

    // save to DB new dimension values
    var id = topBlock.getAttribute('gs-id'),
    dimensions = {
        newx: "0",
        newy: topBlock.getAttribute('gs-y'),
        newwidth: "12",
        newheight: topBlock.getAttribute('gs-h'),
    }
    moveBlock(id, dimensions, grid);

    id = bottomBlock.getAttribute('gs-id');
    dimensions = {
        newx: "0",
        newy: bottomBlock.getAttribute('gs-y'),
        newwidth: "12",
        newheight: bottomBlock.getAttribute('gs-h'),
    }
    moveBlock(id, dimensions, grid);
}
