<p id="{$prefix}_empty_selectlist"{if !$selectedlist} class="hidden"{/if}>{str tag=nofilesfound section=artefact.file}</p>
<table id="{$prefix}_selectlist"  class="fullwidth {if !$selectedlist} hidden{/if}">
 <thead>
  <tr>
   <th></th>
   <th>{str tag=Name section=artefact.file}</th>
   <th>{str tag=Description section=artefact.file}</th>
   <th></th>
  </tr>
 </thead>
 <tbody>
  {foreach from=$selectedlist item=file}
    {assign var=displaytitle value=$file->title|str_shorten_text:34|safe}
  <tr class="{cycle values='r0,r1'}{if $highlight && $highlight == $file->id} highlight-file{/if}">
    <td class="iconcell">
      <img src="{if $file->artefacttype == 'image'}{$WWWROOT}artefact/file/download.php?file={$file->id}&size=20x20{else}{theme_url filename=images/`$file->artefacttype`.gif}{/if}">
    </td>
    <td class="valign">
      <a href="{$WWWROOT}artefact/file/download.php?file={$file->id}" target="_blank" title="{str tag=downloadfile section=artefact.file arg1=$displaytitle}">{$displaytitle}</a>
    </td>
    <td class="valign">{$file->description}</td>
    <td>
       <input type="submit" class="button submit s unselect" name="{$prefix}_unselect[{$file->id}]" value="{str tag=remove}" />
       <input type="hidden" name="{$prefix}_selected[{$file->id}]" value="{$file->id}">
    </td>
  </tr>
  {/foreach}
 </tbody>
</table>
