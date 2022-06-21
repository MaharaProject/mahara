{include file="export:html:header.tpl"}

{if $newlayout}
<script>
$(function () {
    var options = {
        margin: 1,
        cellHeight: 10,
        float: true,
        ddPlugin: false,
        disableDrag: true,
        disableResize: true
    };
    var grid = GridStack.init(options);
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
     | <li{if $item.id == $viewid} class="selected"{/if}><a href="{$rootpath}{$htmldir}views/{$item.url}">{$item.text}</a></li>
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

{if $viewfeedback || $viewartefactsfeedback}
  <div class="viewfooter">

    {* View comments *}
    {if $viewfeedback && $viewfeedback->count && $viewfeedback->position == 'base'}
      <div id="feedbacktable" class="feedbacktable fullwidth table">
        <h2 class="title">
          {str tag="viewcomments" section="artefact.comment"}
        </h2>
          {$viewfeedback->tablerows|safe}
      </div>
      {$viewfeedback->pagination|safe}
    {/if}

    {* Artefact comments *}
    {if $viewartefactsfeedback}
      {foreach from=$viewartefactsfeedback key=artefactid item=artefactobj}
        {if $artefactobj->commentcount > 0}
          <h2 class="title">
            {str tag="artefactcomments1" section="artefact.comment"} {$artefactobj->heading} '{$artefactobj->title}'
          </h2>
          <div id="feedbacktable" class="feedbacktable fullwidth table">
            {foreach from=$artefactobj->comments item=comment}
              {* display an image reference for image artefacts *}
              {if $artefactobj->type == image}
                <div class="push-left-for-usericon">
                  <img width="200" alt="{$artefactobj->file}" src="../export_info/files/{$artefactobj->file}">
                </div>
              {/if}
              {$comment->tablerows|safe}
            {/foreach}
          </div>
          {$comment->pagination|safe}
        {/if}
      {/foreach}
    {/if}
  </div>
{/if}

{include file="export:html:footer.tpl"}
