<div id="toolbar-buttons" class="btn-group btn-toolbar btn-group-top">
        <a class="btn btn-default editviews editlayout {if $selected == 'layout' or $selected == 'editlayout'}active{/if}"
            href="{$WWWROOT}view/editlayout.php?id={$viewid}{if $new}&new=1{/if}"
            title="{if $edittitle}{str tag=settings section=view}{else}{str tag=editlayout section=view}{/if}">
            <span class="icon icon-lg icon-cogs"></span>
            <span class="btn-title">{if $edittitle}{str tag=settings section=view}{else}{str tag=editlayout section=view}{/if}</span>
        </a>
    <a class="btn btn-default editviews editcontent {if $selected == 'content'}active{/if}" href="{$WWWROOT}view/blocks.php?id={$viewid}{if $new}&new=1{/if}" title="{str tag=editcontent1 section=view}">
        <span class="icon icon-lg icon-pencil" aria-hidden="true" role="presentation"></span>
        <span class="btn-title">{str tag=editcontent1 section=view}</span>
    </a>

    {if $edittitle || $viewtype == 'share'}
        <a class="btn btn-default editviews editshare {if $selected == 'share'}active{/if}" href="{$WWWROOT}view/accessurl.php?id={$viewid}{if $collectionid}&collection={$collectionid}{/if}{if $new}&new=1{/if}"  title="{str tag=shareview1 section=view}">
            <span class="icon icon-lg icon-unlock-alt" aria-hidden="true" role="presentation"></span>
           <span class="btn-title">{str tag=shareview1 section=view}</span>
        </a>
    {/if}
</div>

{if !$issitetemplate}
<div id="view-wizard-controls" class="with-heading">
<a href="{$displaylink}" id="display_page" class="">
    {str tag=displayview section=view}
    <span class="icon icon-arrow-circle-right right" role="presentation" aria-hidden="true"></span>
</a>
</div>
{else}
    &nbsp;
{/if}
