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

<div id="commentfiles" class="folder-card">
    {if (isset($children))}
    <h4 class="sr-only">
        {str tag=foldercontents section=artefact.file}:
    </h4>

    <div class="fullwidth file-download-list">
        <ul class="list-group">
            {foreach from=$children item=child}
            <li class="filedownload-item list-group-item">
                <a class="modal_link" title="{$child->hovertitle}" data-toggle="modal-docked" data-target="#configureblock" href="#" data-blockid="{$blockid}" data-artefactid="{$child->id}">
                {if $child->iconsrc}
                    <img src="{$child->iconsrc}" alt="{$child->artefacttype}" class="file-icon text-inline {if $modal}file-icon-render-in-modal{/if}">
                {else}
                    <span class="icon icon-{$child->artefacttype} icon-lg left {if $modal}file-icon-render-in-modal{/if}" role="presentation" aria-hidden="true"></span>
                {/if}
                </a>
                <h4 class="title list-group-item-heading">
                    <a class="modal_link" title="{$child->hovertitle}" data-toggle="modal-docked" data-target="#configureblock" href="#" data-blockid="{$blockid}" data-artefactid="{$child->id}">
                        {$child->title}
                    </a>
                </h4>
                <a href="{$WWWROOT}artefact/file/download.php?file={$child->id}&amp;view={$viewid}">
                    <span class="icon icon-download icon-lg float-right text-watermark icon-action" role="presentation" aria-hidden="true" data-toggle="tooltip" title="{str tag=downloadfilesize section=artefact.file arg1=$child->title arg2=$child->size}"></span>
                    <span class="sr-only">{str tag=downloadfilesize section=artefact.file arg1=$child->title arg2=$child->size}</span>
                </a>
                {if $child->description}
                <div class="file-description text-small text-midtone">
                    {$child->description|clean_html|safe}
                </div>
                {/if}
            </li>
            {/foreach}
        </ul>
    </div>
    {if $downloadfolderzip}
        <a href="{$WWWROOT}artefact/file/downloadfolder.php?folder={$folderid}&view={$viewid}" class="link-blocktype">
            <span class="icon icon-download left" role="presentation" aria-hidden="true"></span>
            {str tag=downloadfolderziplink section=artefact.file}
        </a>
    {/if}
    {else}
        <span class="lead text-small">
            {str tag=emptyfolder section=artefact.file}
        </span>
    {/if}
</div>

<script>
    jQuery('.modal_link').off('click');
    jQuery('.modal_link').on('click', function(e) {
        open_modal(e);
        toggleDetailsBtn();
    });
</script>
