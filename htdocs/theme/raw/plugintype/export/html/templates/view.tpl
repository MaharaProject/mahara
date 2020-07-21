{include file="export:html:header.tpl"}

{if $newlayout}
<script>
$(function () {
    var options = {
        verticalMargin: 5,
        cellHeight: 10,
        float: true,
        ddPlugin: false,
    };
    var grid = $('.grid-stack');
    grid.gridstack(options);
    grid = $('.grid-stack').data('gridstack');


    if (grid) {
        // should add the blocks one by one
        var blocks = {json_encode arg=$blocks};
        loadGrid(grid, blocks);
        jQuery(document).trigger('blocksloaded');
    }
    carouselHeight();

});
</script>
{/if}

{if $collectionmenu}
<div class="breadcrumbs collection">
   <ul>
     <li class="collectionname">{$collectionname}</li>
{foreach from=$collectionmenu item=item}
     | <li{if $item.id == $viewid} class="selected"{/if}><a href="{$rootpath}HTML/views/{$item.url}">{$item.text}</a></li>
{/foreach}
   </ul>
</div>
<div class="cb"></div>
{/if}

{if $viewinstructions}
    <div>{str tag='instructions' section='view'}</div>
    <div class="viewinstruction-export">
        {$viewinstructions|clean_html|safe}
    </div>
{/if}

{if $view}
    {$view|safe}
{else}
    <div class="container-fluid">
      <div class="grid-stack">
      </div>
    </div>
{/if}

{if $feedback && $feedback->count && $feedback->position == 'base'}
<div class="viewfooter">
    <table id="feedbacktable" class="feedbacktable fullwidth table">
      <thead><tr><th>{str tag="feedback" section="artefact.comment"}</th></tr></thead>
      <tbody>
        {$feedback->tablerows|safe}
      </tbody>
    </table>
    {$feedback->pagination|safe}
</div>
{/if}

{include file="export:html:footer.tpl"}
