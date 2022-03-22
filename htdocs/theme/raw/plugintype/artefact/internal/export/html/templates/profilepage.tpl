{include file="export:html:header.tpl"}

{if $newlayout}
<script>
$(function () {
    var options = {
        margin: 1,
        cellHeight: 10,
        float: true,
        ddPlugin: false,
    };
    var grid = GridStack.init(options);
    if (grid) {
        // should add the blocks one by one
        var blocks = {json_encode arg=$blocks};
        loadGrid(grid, blocks);
        jQuery(document).trigger('blocksloaded');
    }
});
</script>
<div class="container-fluid">
    <div class="grid-stack">
    </div>
</div>
{else}
{$view|safe}
{/if}
{include file="export:html:footer.tpl"}
