{foreach $files file}
<div class="filedownload-item" title="{$file.title}">
  <div class="fl"><a href="{$file.downloadurl}" target="_blank"><img src="{$file.iconsrc}" alt=""></a></div>
  <div style="margin-left: 30px;">
    <h3 class="title"><a href="{$file.downloadurl}" target="_blank">{$file.title}</a></h3>
    {if $file.description}<p>{$file.description}</p>{/if}
    <div class="description">{$file.size|display_size} | {$file.ctime|format_date:'strftimedaydate'}
    | <a href="{$WWWROOT}view/artefact.php?artefact={$file.id}&view={$viewid}">{if $file.is_image}{str tag=View section=artefact.file}{else}{str tag=Details section=artefact.file}{/if}</a></div>
  </div>
</div>
{/foreach}
