<div class="pageactions" id="toolbar-buttons">
    <div class="btn-group-vertical in-editor">
    {if $ineditor}
        {include file="view/contenteditor.tpl" selected='content'}
        <span>&nbsp;</span>
    {/if}
    <a class="btn btn-secondary first-of-group editviews editlayout {if $selected == 'layout' or $selected == 'editlayout'}active{/if}"
        href="{$WWWROOT}view/editlayout.php?id={$viewid}"
        title="{if $edittitle || $canuseskins}{str tag=settings section=view}{else}{str tag=editlayout section=view}{/if}">
        <span class="icon icon-lg icon-cogs"></span>
        <span class="btn-title sr-only">{if ($edittitle || $canuseskins) }{str tag=settings section=view}{else}{str tag=editlayout section=view}{/if}</span>
    </a>
    <a class="btn btn-secondary editviews editcontent {if $selected == 'content'}active{/if}" href="{$WWWROOT}view/blocks.php?id={$viewid}" title="{str tag=editcontent1 section=view}">
        <span class="icon icon-lg icon-pencil-alt" aria-hidden="true" role="presentation"></span>
        <span class="btn-title sr-only">{str tag=editcontent1 section=view}</span>
    </a>
    {if !$accesssuspended && ($edittitle || $viewtype == 'share') && !$issitetemplate}
        <a class="btn btn-secondary editviews editshare {if $selected == 'share'}active{/if}" href="{$WWWROOT}view/accessurl.php?id={$viewid}{if $collectionid}&collection={$collectionid}{/if}"  title="{str tag=shareview1 section=view}">
            <span class="icon icon-lg icon-unlock" aria-hidden="true" role="presentation"></span>
            <span class="btn-title sr-only">{str tag=shareview1 section=view}</span>
        </a>
    {/if}
    </div>
</div>