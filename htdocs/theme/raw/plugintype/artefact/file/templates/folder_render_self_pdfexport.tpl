{if $description || $tags}
<div class="details-before-list-group">
{/if}
{if $description}
    <div class="content-text">
        {$description}
    </div>
{/if}
{if $tags}
    <div class="tags">
        <strong>{str tag=tags}</strong>:
        {list_tags owner=$owner tags=$tags view=$viewid}
    </div>
{/if}
{if $description || $tags}
</div>
{/if}

<div id="commentfiles" class="folder-card">
    {if (isset($children))}
    <h3 class="sr-only">
        {str tag=foldercontents section=artefact.file}:
    </h3>

    <div class="fullwidth">
        <ul class="list-group">
            {foreach from=$children item=child}
            <li class="filedownload-item list-group-item">
                <a class="inner-link file-icon-link" href="{$WWWROOT}artefact/artefact.php?artefact={$child->id}&amp;view={$viewid}" title="{$child->hovertitle}">
                {if $child->iconsrc}
                    <img src="{$child->iconsrc}" alt="{$child->artefacttype}" class="file-icon text-inline">
                {else}
                    <span class="icon icon-{$child->artefacttype} icon-lg left text-default file-icon" role="presentation" aria-hidden="true"></span>
                {/if}
                </a>
                <h3 class="title list-group-item-heading text-inline">
                    <a href="{$WWWROOT}artefact/artefact.php?artefact={$child->id}&amp;view={$viewid}" class="inner-link" title="{$child->hovertitle}">
                        {$child->title}
                    </a>
                    <span class="filedate metadata">
                        {$child->date}
                    </span>
                </h3>
                {if $child->description}
                <div class="file-description text-small text-midtone">
                    {$child->description|safe|clean_html}
                </div>
                {/if}
            </li>
            {/foreach}
        </ul>
    </div>
    {else}
        <span class="lead text-small">
            {str tag=emptyfolder section=artefact.file}
        </span>
    {/if}
</div>
