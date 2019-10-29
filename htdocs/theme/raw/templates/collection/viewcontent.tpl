<h2>{$collectiontitle}{if $ownername} {str tag=by section=collection} {$ownername}{/if}</h2>

<p class="collection-description">{$collectiondescription|clean_html|safe}</p>

<!-- include a modified navigation bar -->
    {include file=previewcollectionnav.tpl}

<div id="view" class="cb">
        <div id="bottom-pane">
            <div id="column-container">
            {if $newlayout}
            <script>
            $(function () {
                var options = {
                    verticalMargin: 10,
                    float: true,
                    ddPlugin: false,
                };
                var grid = $('.grid-stack');
                grid.gridstack(options);
                grid = $('.grid-stack').data('gridstack');

                // should add the blocks one by one
                var blocks = {json_encode arg=$blocks};
                loadGrid(grid, blocks);
                jQuery(document).trigger('blocksloaded');
            });
            </script>
                <div class="container-fluid">
                    <div class="grid-stack">
                    </div>
                </div>
            {else}
               {$viewcontent|safe}
            {/if}
                <div class="cb"></div>
            </div>
        </div>
{if $tags}
  <div class="viewfooter cb">
    <div class="tags"><strong>{str tag=tags}:</strong> {list_tags owner=0 tags=$tags}</div>
  </div>
{/if}
</div>
