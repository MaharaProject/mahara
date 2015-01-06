{foreach $files file}
<div class="filedownload-item" title="{$file.title}">
  <div class="fl"><a href="{$file.downloadurl}" target="_blank"><img src="{$file.iconsrc}" alt=""></a></div>
  <div style="margin-left: 30px;">
    <h3 class="title"><a href="{$file.downloadurl}" target="_blank">{$file.title}</a></h3>
    {if $file.description}<div class="detail">{$file.description}</div>{/if}
    <div class="description">{$file.size|display_size} | {$file.ctime|format_date:'strftimedaydate'}
    | <a href="{$WWWROOT}artefact/artefact.php?artefact={$file.id}&view={$viewid}">{str tag=Details section=artefact.file}</a></div>
  </div>
</div>
{/foreach}
