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

    <div class="fullwidth file-download-list has-attachments">
        <ul class="list-group">
            {foreach from=$children item=child}
            {if !$child->allowcomments}
                {assign var="justdetails" value=true}
            {/if}
            {include
                file='header/block-comments-details-header.tpl'
                artefactid=$child->id
                blockid=$blockid
                commentcount=$child->commentcount
                allowcomments=$child->allowcomments
                justdetails=$justdetails
                displayiconsonly=true}
            <li class="filedownload-item list-group-item flush">
                {if !$editing}
                  <a class="modal_link file-icon-link" title="{$child->hovertitle}" data-toggle="modal-docked" data-target="#configureblock" href="#" data-blockid="{$blockid}" data-artefactid="{$child->id}">
                  {if $child->iconsrc}
                      <img src="{$child->iconsrc}" alt="{$child->artefacttype}" class="file-icon text-inline">
                  {else}
                      <span class="icon icon-{$child->artefacttype} icon-lg left text-default file-icon" role="presentation" aria-hidden="true"></span>
                  {/if}
                  </a>
                {else}
                  <span class="file-icon-link">
                  {if $child->iconsrc}
                      <img src="{$child->iconsrc}" alt="{$child->artefacttype}" class="file-icon text-inline">
                  {else}
                      <span class="icon icon-{$child->artefacttype} icon-lg left text-default file-icon" role="presentation" aria-hidden="true"></span>
                  {/if}
                  </span>
                {/if}
                <span class="title">
                  {if !$editing}
                    <a class="modal_link inner-link" title="{$child->hovertitle}" data-toggle="modal-docked" data-target="#configureblock" href="#" data-blockid="{$blockid}" data-artefactid="{$child->id}">
                  {/if}
                      <span class="text-small">{$child->title}</span>
                  {if !$editing}
                    </a>
                  {/if}
                </span>
                {if $child->artefacttype != 'folder'}
                <a href="{$WWWROOT}artefact/file/download.php?file={$child->id}&amp;view={$viewid}" class="download-link">
                    <span class="icon icon-download icon-lg float-right text-watermark icon-action" role="presentation" aria-hidden="true" data-toggle="tooltip" title="{str tag=downloadfilesize section=artefact.file arg1=$child->title arg2=$child->size}"></span>
                </a>
                {/if}
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
        <a href="{$WWWROOT}artefact/file/downloadfolder.php?folder={$folderid}&view={$viewid}" class="link-blocktype download-link">
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
