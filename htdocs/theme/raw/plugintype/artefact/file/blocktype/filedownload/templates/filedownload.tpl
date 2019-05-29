<ul class="list-group">
{foreach $files file}
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

        <h4 class="title list-group-item-heading text-inline">
            {if !$editing}
            <a class="modal_link inner-link"
                data-toggle="modal-docked"
                data-target="#configureblock"
                href="#"
                data-artefactid="{$file.id}"
                data-blockid="{$blockid}"
                {if $file.commentcount > 0}
                    title="{str tag=commentsanddetails section=artefact.file arg1=$file.commentcount}">
                {else}
                    title={str tag=Details section=artefact.file}>
                {/if}
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
        <span class="text-small text-midtone"> -
            {$file.ctime|format_date:'strftimedaydate'}
            [{$file.size|display_size}]
        </span>
        <span class="icon icon-download icon-lg float-right text-watermark icon-action" role="presentation" aria-hidden="true"></span>
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
