<div class="panel-body">
    {if !$simpledisplay}
    <h3 class="title">
        {$title}
    </h3>
    {/if}
    
    <p class="detail">
        {$description}
    </p>
    
    {if $tags}
    <div class="tags">
        <span class="lead text-small">{str tag=tags}</span>: {list_tags owner=$owner tags=$tags}
    </div>
    {/if}
    
    <div id="commentfiles">
        {if (isset($children))}
        <h4 class="lead mbm">
            {str tag=foldercontents section=artefact.file}:
        </h4>
        
        <div class="fullwidth">
            <ul class="list-group mb0 pl0">
                {foreach from=$children item=child}
                <li class="{cycle values='r0,r1'} list-group-item">
                    <a href="{$WWWROOT}artefact/artefact.php?artefact={$child->id}&amp;view={$viewid}" title="{$child->hovertitle}" class="outer-link">
                        <span class="sr-only">
                            {str tag=Details section=artefact.file}
                            {$child->title}
                        </span>
                    </a>
                    
                    {if $child->iconsrc}
                        <img src="{$child->iconsrc}" alt="{$child->artefacttype}" class="inline prm">
                    {else}
                        <span class="fa fa-{$child->artefacttype} fa-lg prm"></span>
                    {/if}
                    <h5 class="title list-group-item-heading inline">
                        <a href="{$WWWROOT}artefact/artefact.php?artefact={$child->id}&amp;view={$viewid}" title="{$child->hovertitle}">
                            {$child->title}
                        </a>
                        {if !$simpledisplay}
                        <span class="filedate metadata">
                            {$child->date}
                        </span>
                        {/if}
                    </h5>
                    {if $child->description}
                    <p class="filedescription text-small">
                        {$child->description}
                    </p>
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
            <span class="text-thin">
                {str tag=emptyfolder section=artefact.file}
            </span>
        {/if}
    </div>
</div>

