{if $simpledisplay}
<div class="panel-body">
{/if}
    <p class="text-small description">
        {$description}
    </p>

    {if $tags}
    <div class="tags">
        <span class="lead text-small">{str tag=tags}</span>:
        {list_tags owner=$owner tags=$tags}
    </div>
    {/if}
{if $simpledisplay}
</div>
{/if}
<div id="commentfiles" class="folder-panel">
    {if (isset($children))}
    <h4 class="sr-only">
        {str tag=foldercontents section=artefact.file}:
    </h4>

    <div class="fullwidth">
        <ul class="list-group mb0 pl0">
            {foreach from=$children item=child}
            <li class="{cycle values='r0,r1'} filedownload-item list-group-item">
                {if $child->artefacttype != 'folder'}
                <a href="{$WWWROOT}artefact/file/download.php?file={$child->id}&amp;view={$viewid}" class="outer-link icon-on-hover">
                    <span class="sr-only">
                        {str tag=Details section=artefact.file}
                        {$child->title}
                    </span>
                </a>
                {/if}

                {if $child->iconsrc}
                    <img src="{$child->iconsrc}" alt="{$child->artefacttype}" class="text-inline prm">
                {else}
                    <span class="icon icon-{$child->artefacttype} icon-lg prm"></span>
                {/if}
                <h5 class="title list-group-item-heading text-inline">
                    <a class="inner-link" href="{$WWWROOT}artefact/artefact.php?artefact={$child->id}&amp;view={$viewid}" title="{$child->hovertitle}">
                        {$child->title}
                    </a>
                    {if !$simpledisplay}
                    <span class="filedate metadata">
                        {$child->date}
                    </span>
                    {/if}
                    {if $child->description}
                    <span class="filedate metadata">
                        {$child->description}
                    </span>
                    {/if}
                </h5>
                {if $child->artefacttype != 'folder'}
                <span class="icon icon-download icon-lg pull-right pts text-watermark icon-action"></span>
                {/if}
            </li>
            {/foreach}
        </ul>
    </div>
    {if $downloadfolderzip}
        <a href="{$WWWROOT}artefact/file/downloadfolder.php?folder={$folderid}&view={$viewid}">
            {str tag=downloadfolderziplink section=artefact.file}
        </a>
    {/if}
    {else}
        <span class="lead text-small">
            {str tag=emptyfolder section=artefact.file}
        </span>
    {/if}
</div>
