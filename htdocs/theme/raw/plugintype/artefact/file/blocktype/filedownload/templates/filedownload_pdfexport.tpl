<ul class="list-group">
{foreach $files file}
    <li class="filedownload-item list-group-item">
        <a class="inner-link file-icon-link" href="{$WWWROOT}artefact/artefact.php?artefact={$file.id}&view={$viewid}">
        {if $file.iconsrc}
            <img src="{$file.iconsrc}" alt="" class="file-icon text-inline" />
        {else}
            <span class="icon icon-{$file.artefacttype} icon-lg left text-default file-icon" role="presentation" aria-hidden="true"></span>
        {/if}
        </a>

        <h3 class="title list-group-item-heading text-inline">
            <a href="{$WWWROOT}artefact/artefact.php?artefact={$file.id}&view={$viewid}" class="inner-link">
                 {$file.title} [export_info/files/{$file.id}-{$file.title}]
                 <span class="sr-only">
                    {str tag=Details section=artefact.file}
                </span>
            </a>
        </h3>
        <span class="text-small text-midtone"> -
            {$file.ctime|format_date:'strftimedaydate'}
            [{$file.size|display_size}]
        </span>
        {if $file.description}
        <div class="file-description text-small text-midtone">
            {$file.description|safe|clean_html}
        </div>
        {/if}
    </li>
    {/foreach}
</ul>
