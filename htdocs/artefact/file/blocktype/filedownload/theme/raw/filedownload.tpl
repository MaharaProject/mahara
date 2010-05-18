{foreach $files file}
<div title="{$file.title}">
  <div class="fl"><a href="{$WWWROOT}{$file.downloadurl}" target="_blank"><img src="{$file.iconsrc}" alt=""></a></div>
  <div style="margin-left: 30px;">
    <h4><a href="{$file.downloadurl}" target="_blank">{$file.title|str_shorten_text:20|safe}</a></h4>
    {if $file.description}<p style="margin: 0;"><strong>{$file.description}</strong></p>{/if}
    {$file.size|display_size} | {$file.ctime|format_date:'strftimedaydate'}
    | <a href="{$WWWROOT}view/artefact.php?artefact={$file.id}&view={$viewid}">{str tag=Details section=artefact.file}</a>
  </div>
</div>
{/foreach}
