<script>
$(function () {
    {if $newlayout}
        var options = {
            verticalMargin: 10,
            float: true,
            ddPlugin: false,
        };
        var grid = $('.grid-stack');
        grid.gridstack(options);console.log(grid);
        grid = $('.grid-stack').data('gridstack');

        // should add the blocks one by one
        var blocks = {json_encode arg=$blocks};
        loadGrid(grid, blocks);
        jQuery(document).trigger('blocksloaded');
    {/if}
    // Prevent Image Gallery block images from overlapping
    carouselHeight();
});
</script>

<h2>
    {$viewtitle}
    {if $ownername}
    {str tag=by section=view}
    {$ownername}
    {/if}
</h2>
<p class="view-description">
    {$viewdescription|clean_html|safe}
</p>
<p class="view-instructions">
    {$viewinstructions|clean_html|safe}
</p>
<div id="view" class="view-container">
    <div id="bottom-pane">
        <div id="column-container">
          <div class="container-fluid">
              <div class="grid-stack">
              {if $viewcontent}
                  {$viewcontent|safe}
              {/if}
              </div>
          </div>
       </div>
    </div>
    {if $tags}
    <div class="viewfooter">
        <div class="tags">
        <strong>{str tag=tags}:</strong>
        {list_tags owner=0 tags=$tags}
        </div>
    </div>
{/if}
</div>
