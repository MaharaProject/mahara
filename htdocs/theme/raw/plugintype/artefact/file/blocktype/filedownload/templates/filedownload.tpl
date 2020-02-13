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
        <li class="filedownload-item list-group-item">
            <a class="modal_link" data-toggle="modal-docked" data-target="#configureblock" href="#" data-artefactid="{$file.id}" data-blockid="{$blockid}" title="{$file.title}">
            {if $file.iconsrc}
                <img src="{$file.iconsrc}" alt="" class="file-icon text-inline" />
            {else}
                <span class="icon icon-{$file.artefacttype} icon-lg left" role="presentation" aria-hidden="true"></span>
            {/if}
            </a>
            <h4 class="title list-group-item-heading">
            {if !$editing}
                <a class="modal_link" data-toggle="modal-docked" data-target="#configureblock" href="#" data-artefactid="{$file.id}" data-blockid="{$blockid}" title="{$file.title}">
                    {$file.title}
                    <span class="sr-only">{str tag=Details section=artefact.file}</span>
                </a>
            {else}
                {$file.title}
            {/if}
            </h4>
            <a href="{$file.downloadurl}">
                <span class="icon icon-download icon-lg float-right text-watermark icon-action" role="presentation" aria-hidden="true"></span>
                <span class="sr-only">{str tag=Download section=artefact.file} {$file.title}</span>
            </a>
            <div class="text-small text-midtone">
                {$file.ctime|format_date:'strftimedaydate'}
                [{$file.size|display_size}]
            </div>
            {if $file.description}
            <div class="file-description text-small">
                {$file.description|safe|clean_html}
            </div>
            {/if}
        </li>
        {/foreach}
    </ul>
</div>
