{foreach $files file}
<div class="filedownload-item" title="{$file.title}">
  <div class="fl"><a href="{$file.downloadurl}" target="_blank"><img src="{$file.iconsrc}" alt=""></a></div>
  <div style="margin-left: 30px;">
    <h4><a href="{$file.downloadurl}" target="_blank">{$file.title|str_shorten_text:20}</a></h4>
    {if $file.description}<p>{$file.description}</p>{/if}
    <div class="description">{$file.size|display_size} | {$file.ctime|format_date:'strftimedaydate'}
    | <a href="{$WWWROOT}view/artefact.php?artefact={$file.id}&view={$viewid}">{str tag=Details section=artefact.file}</a></div>
  </div>
</div>
{/foreach}
