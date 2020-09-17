<div class="file-download-list">
    <ul class="list-group">
    {foreach $files file}
        {if !$file.allowcomments}
            {assign var="justdetails" value=true}
        {/if}
        {include
            file='header/block-comments-details-header.tpl'
            artefactid=$file.id
            blockid=$blockid
            commentcount=$file.commentcount
            allowcomments=$file.allowcomments
            justdetails=$justdetails
            displayiconsonly = true}
        <li class="filedownload-item list-group-item flush">
            {if !$editing}
            <a class="modal_link file-icon-link" data-toggle="modal-docked" data-target="#configureblock" href="#" data-artefactid="{$file.id}" data-blockid="{$blockid}" title="{$file.title}">
                {if $file.iconsrc}
                    <img src="{$file.iconsrc}" alt="" class="file-icon text-inline" />
                {else}
                    <span class="icon icon-{$file.artefacttype} icon-lg left text-default file-icon" role="presentation" aria-hidden="true"></span>
                {/if}
            </a>
            {else}
            <span class="file-icon-link">
                {if $file.iconsrc}
                    <img src="{$file.iconsrc}" alt="" class="file-icon text-inline" />
                {else}
                    <span class="icon icon-{$file.artefacttype} icon-lg left text-default file-icon" role="presentation" aria-hidden="true"></span>
                {/if}
            </span>
            {/if}

            <h3 class="title list-group-item-heading">
            {if !$editing}
            <a class="modal_link" title="{$child->hovertitle}" data-toggle="modal-docked" data-target="#configureblock" href="#" data-blockid="{$blockid}" data-artefactid="{$file.id}">
                {$file.title}
                <span class="sr-only">{str tag=Details section=artefact.file}</span>
            </a>
            {else}
                <span class="inner-link">{$file.title}</span>
            {/if}
            </h3>

            <a href="{$file.downloadurl}" class="download-link">
                <span class="icon icon-download icon-lg float-right text-watermark icon-action" role="presentation" aria-hidden="true" data-toggle="tooltip" title="{str tag=downloadfilesize section=artefact.file arg1=$file.title arg2=$file.size|display_size}"></span>
                <span class="sr-only">{str tag=downloadfilesize section=artefact.file arg1=$file.title arg2=$file.size|display_size}}</span>
            </a>

            {if $file.description}
            <div class="file-description text-small text-midtone">
                {$file.description|clean_html|safe}
            </div>
            {/if}
        </li>
        {/foreach}
    </ul>
</div>
