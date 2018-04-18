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
            <a href="{$WWWROOT}artefact/artefact.php?artefact={$file.id}&view={$viewid}" class="inner-link">
                 {$file.title}
                 <span class="sr-only">
                    {str tag=Details section=artefact.file}
                </span>
            </a>
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

