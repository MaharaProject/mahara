<div class="card-body flush">
    {$text|clean_html|safe}

    {if $artefact->get('tags')}
    <div class="tags">
        <strong>{str tag=tags}:</strong> {list_tags owner=$artefact->get('owner') tags=$artefact->get('tags')}
    </div>
    {/if}
</div>

{if $attachments}
{* @TODO: this could probably be a reusable template *}
<div class="has-attachment card collapsible">
    <div class="card-header">
        <a class="text-left collapsed" aria-expanded="false" href="#note-attach-{$blockid}" data-toggle="collapse">
            <span class="icon icon-paperclip left icon-sm" role="presentation" aria-hidden="true"></span>
            <span class="text-small">{str tag=attachedfiles section=artefact.blog}</span>
            <span class="metadata">({$count})</span>
            <span class="icon icon-chevron-down float-right collapse-indicator" role="presentation" aria-hidden="true"></span>
        </a>
    </div>
    {* Attachment list with view and download link *}
    <div id="note-attach-{$blockid}" class="collapse">
        <ul class="list-unstyled list-group">
            {foreach from=$attachments item=item}
            <li class="list-group-item">
            {if !$editing}
                <a class="modal_link file-icon-link" data-toggle="modal-docked" data-target="#configureblock" href="#" data-blockid="{$blockid}" data-artefactid="{$item->id}">
                {if $item->iconpath}
                    <img class="file-icon" src="{$item->iconpath}" alt="">
                {else}
                    <span class="icon icon-{$item->artefacttype} left icon-lg text-default file-icon" role="presentation" aria-hidden="true"></span>
                {/if}
                </a>
            {else}
                <span class="file-icon-link">
                {if $item->iconpath}
                    <img class="file-icon" src="{$item->iconpath}" alt="">
                {else}
                    <span class="icon icon-{$item->artefacttype} left icon-lg text-default file-icon" role="presentation" aria-hidden="true"></span>
                {/if}
                </span>
            {/if}
                <span class="title">
                {if !$editing}
                    <a class="modal_link" data-toggle="modal-docked" data-target="#configureblock" href="#" data-blockid="{$blockid}" data-artefactid="{$item->id}">
                {/if}
                        <span class="text-small">{$item->title}</span>
                {if !$editing}
                    </a>
                {/if}
                </span>
                <a href="{$item->downloadpath}" class="download-link">
                    <span class="icon icon-download icon-lg float-right text-watermark icon-action" role="presentation" aria-hidden="true" data-toggle="tooltip" title="{str tag=downloadfilesize section=artefact.file arg1=$item->title arg2=$item->size|display_size}"></span>
                </a>
            {if $item->description}
                <div class="file-description text-small text-midtone">
                    {$item->description|clean_html|safe}
                </div>
            {/if}
            </li>
            {/foreach}
        </ul>
    </div>
</div>
{/if}
