<div class="pageactions" id="toolbar-buttons">
    <div class="btn-group-vertical in-editor">
    {if $ineditor}
        {include file="view/contenteditor.tpl" selected='content'}
        <span>&nbsp;</span>
    {/if}
    {if ($edittitle || $canuseskins) }
        <button
            class="btn btn-secondary first-of-group editviews editlayout {if $selected == 'editlayout'}active{/if}"
            data-url="{$WWWROOT}view/editlayout.php?id={$viewid}"
            title="{str tag=settings section=view}">
            <span class="icon icon-cogs icon-lg"></span>
            <span class="btn-title sr-only">{str tag=settings section=view}</span>
        </button>
    {/if}
    {if $selected == 'content'}
        {if $viewurl}
            <button
                data-url="{$WWWROOT}{if $collectionurl}{$collectionurl}{else}view/view.php?id={$viewid}{/if}"
                id='displaypagebtn' type="button" class="btn btn-secondary editviews displaycontent" title="{str tag=displayview section=view}">
                <span class="icon icon-tv icon-lg left" role="presentation" aria-hidden="true"></span>
                <span class="sr-only">{str tag=displayview section=view}</span>
            </button>
        {/if}
    {else}
        <button
            data-url="{$WWWROOT}{if $collectionurl}{$collectionurl}{else}view/blocks.php?id={$viewid}{/if}"
            class="btn btn-secondary editviews editcontent {if $selected == 'content'}active{/if}" title="{str tag=editcontent1 section=view}">
            <span class="icon icon-pencil-alt icon-lg" aria-hidden="true" role="presentation"></span>
            <span class="btn-title sr-only">{str tag=editcontent1 section=view}</span>
        </button>
    {/if}
    {if !$accesssuspended && ($edittitle || $viewtype == 'share') && !$issitetemplate}
        <button
            data-url="{$WWWROOT}view/accessurl.php?id={$viewid}{if $collectionid}&collection={$collectionid}{/if}"
            class="btn btn-secondary editviews editshare {if $selected == 'share'}active{/if}" title="{str tag=shareview1 section=view}">
            <span class="icon icon-unlock icon-lg" aria-hidden="true" role="presentation"></span>
            <span class="btn-title sr-only">{str tag=shareview1 section=view}</span>
        </button>
    {/if}

    <button class="btn btn-secondary editviews returntolocation"
        data-url="{$url}"
        title="{$title}">
        <span class="icon icon-step-backward icon-lg" aria-hidden="true" role="presentation"></span>
        <span class="btn-title sr-only">{$title}</span>
    </button>
    </div>
</div>
