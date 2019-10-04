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
            <a href="{$file.downloadurl}" class="outer-link icon-on-hover">
                <span class="sr-only">
                    {str tag=Download section=artefact.file} {$file.title}
                </span>
            </a>

            {if $file.iconsrc}
                <img src="{$file.iconsrc}" alt="" class="file-icon text-inline" />
            {else}
                <span class="icon icon-{$file.artefacttype} icon-lg left" role="presentation" aria-hidden="true"></span>
            {/if}
            <span class="icon icon-download icon-lg float-right text-watermark icon-action" role="presentation" aria-hidden="true"></span>
            <h4 class="title list-group-item-heading">
                {if !$editing}
                <a class="modal_link inner-link"
                    data-toggle="modal-docked"
                    data-target="#configureblock"
                    href="#"
                    data-artefactid="{$file.id}"
                    data-blockid="{$blockid}"
                    title="{$file.title}">
                     {$file.title}
                     <span class="sr-only">
                        {str tag=Details section=artefact.file}
                    </span>
                </a>
                {else}
                    {$file.title}
                    <span class="sr-only">
                       {str tag=Details section=artefact.file}
                   </span>
                {/if}
            </h4>
            <br />
            <span class="text-small text-midtone">
                {$file.ctime|format_date:'strftimedaydate'}
                [{$file.size|display_size}]
            </span>
            {if $file.description}
            <div class="file-description">
                <p class="text-small">
                    {$file.description|safe|clean_html}
                </p>
            </div>
            <script>
            jQuery("div.file-description a").addClass('inner-link');
            </script>
            {/if}
        </li>
        {/foreach}
    </ul>
</div>
